<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchases = Purchase::query()
            ->with(['supplier:id,name,company', 'user:id,name', 'purchaseDetails.product:id,name'])
            ->withCount('purchaseDetails')
            ->when(request('search'), function($query, $search) {
                $query->where('reference_no', 'like', "%{$search}%");
            })
            ->when(request('status'), function($query, $status) {
                $query->where('payment_status', $status);
            })
            ->when(request('date_range'), function($query, $date_range) {
                $query->whereBetween('created_at', $date_range);
            })
            ->latest()
            ->paginate(5)
            ->withQueryString();

        // Get summary statistics
        $summary = [
            'total' => Purchase::sum('grand_total'),
            'count' => Purchase::count(),
            'paid' => Purchase::where('payment_status', 'paid')->sum('grand_total'),
            'paid_count' => Purchase::where('payment_status', 'paid')->count(),
            'pending' => Purchase::where('payment_status', 'partial')->sum('grand_total'),
            'pending_count' => Purchase::where('payment_status', 'partial')->count(),
            'this_month' => Purchase::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('grand_total'),
            'this_month_count' => Purchase::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return Inertia::render('Purchases/Index', [
            'purchases' => $purchases,
            'suppliers' => Supplier::select('id', 'name', 'company', 'balance')->get(),
            'products' => Product::select('id', 'name', 'code', 'cost_price')->get(),
            'filters' => request()->only(['search', 'status']),
            'summary' => $summary
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Purchases/Create', [
            'suppliers' => Supplier::select('id', 'name', 'company', 'balance')->get(),
            'products' => Product::select('id', 'name', 'code', 'cost_price')->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cost_price' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'shipping' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'note' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Calculate totals
            $total = collect($validated['items'])->sum(fn($item) => $item['quantity'] * $item['cost_price']);
            $taxAmount = ($total * $validated['tax']) / 100;
            $grandTotal = $total + $taxAmount + $validated['shipping'] - $validated['discount'];
            $dueAmount = $grandTotal - $validated['paid_amount'];

            // Create purchase
            $purchase = Purchase::create([
                'reference_no' => 'PUR-' . time(),
                'supplier_id' => $validated['supplier_id'],
                'user_id' => Auth::id(),
                'total' => $total,
                'tax' => $taxAmount,
                'discount' => $validated['discount'],
                'shipping' => $validated['shipping'],
                'grand_total' => $grandTotal,
                'paid_amount' => $validated['paid_amount'],
                'due_amount' => $dueAmount,
                'payment_status' => $dueAmount > 0 ? 'partial' : 'paid',
                'payment_method' => $validated['payment_method'],
                'note' => $validated['note']
            ]);

            // Create purchase details and update stock
            foreach ($validated['items'] as $item) {
                $purchase->purchaseDetails()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['cost_price'],
                    'subtotal' => $item['quantity'] * $item['cost_price']
                ]);

                // Update product stock
                Product::where('id', $item['product_id'])->increment('stock', $item['quantity']);
            }

            // Update supplier balance
            if ($dueAmount > 0) {
                Supplier::where('id', $validated['supplier_id'])->increment('balance', $dueAmount);
            }

            DB::commit();

            return redirect()->route('purchases.index')
                ->with('message', 'Purchase created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'purchaseDetails.product']);

        return Inertia::render('Purchases/Show', [
            'purchase' => $purchase
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        //
    }

    public function updatePayment(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0|max:' . $purchase->due_amount,
            'payment_method' => 'required|string',
            'note' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Update purchase payment details
            $purchase->paid_amount += $validated['amount'];
            $purchase->due_amount -= $validated['amount'];
            $purchase->payment_status = $purchase->due_amount > 0 ? 'partial' : 'paid';
            $purchase->save();

            // Update supplier balance
            $purchase->supplier->decrement('balance', $validated['amount']);

            DB::commit();

            return back()->with('message', 'Payment updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
