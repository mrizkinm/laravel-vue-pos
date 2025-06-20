<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::query()
            ->with(['customer:id,name', 'user:id,name', 'saleDetails.product:id,name'])
            ->withCount('saleDetails')
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

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
            'customers' => Customer::select('id', 'name')->get(),
            'products' => Product::select('id', 'name', 'code', 'selling_price', 'stock', 'image', 'category_id', 'alert_quantity')->where('stock', '>', 0)->get(),
            'categories' => Category::select('id', 'name')->get(),
            'filters' => request()->only(['search', 'status', 'date_range'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Sales/Create', [
            'customers' => Customer::select('id', 'name')->get(),
            'products' => Product::select('id', 'name', 'code', 'selling_price', 'stock')->where('stock', '>', 0)->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.selling_price' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'shipping' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card,bank_transfer',
            'note' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Create sale
            $total = collect($validated['items'])->sum(fn($item) => $item['quantity'] * $item['selling_price']);
            $taxAmount = ($total * $validated['tax']) / 100;
            $grandTotal = $total + $taxAmount + $validated['shipping'] - $validated['discount'];
            $dueAmount = $grandTotal - $validated['paid_amount'];

            $sale = Sale::create([
                'reference_no' => 'SALE-' . time(),
                'customer_id' => $validated['customer_id'],
                'user_id' => Auth::id(),
                'tax' => $taxAmount,
                'discount' => $validated['discount'],
                'shipping' => $validated['shipping'],
                'total' => $total,
                'grand_total' => $grandTotal,
                'paid_amount' => $validated['paid_amount'],
                'due_amount' => $dueAmount,
                'payment_status' => $dueAmount > 0 ? 'partial' : 'paid',
                'payment_method' => $validated['payment_method'],
                'note' => $validated['note']
            ]);

            // Create sale details and update stock
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $saleDetail = $sale->saleDetails()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['selling_price'],
                    'subtotal' => $item['quantity'] * $item['selling_price']
                ]);

                $product->decrement('stock', $item['quantity']);
            }

            // Update customer balance if exists
            if ($sale->customer_id && $sale->due_amount > 0) {
                $sale->customer->increment('balance', $sale->due_amount);
            }

            DB::commit();

            return redirect()->route('sales.index')
                ->with('message', 'Sale created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'saleDetails.product']);

        return Inertia::render('Sales/Show', [
            'sale' => $sale
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        //
    }

    public function updatePayment(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0|max:' . $sale->due_amount,
            'payment_method' => 'required|string',
            'note' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $sale->paid_amount += $validated['amount'];
            $sale->due_amount -= $validated['amount'];
            $sale->payment_status = $sale->due_amount > 0 ? 'partial' : 'paid';
            $sale->save();

            if ($sale->customer) {
                $sale->customer->decrement('balance', $validated['amount']);
            }

            DB::commit();

            return back()->with('message', 'Payment updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
