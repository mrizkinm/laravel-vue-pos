<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Get monthly sales data for the last 6 months
        $monthlySales = Sale::select(
            DB::raw('EXTRACT(MONTH FROM created_at) as month'),
            DB::raw('EXTRACT(YEAR FROM created_at) as year'),
            DB::raw('SUM(grand_total) as total')
        )
            ->whereYear('created_at', '>=', now()->subMonths(6)->year)
            ->whereMonth('created_at', '>=', now()->subMonths(6)->month)
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Format monthly sales data for chart
        $salesData = [
            'labels' => $monthlySales->map(fn ($sale) => Carbon::createFromDate($sale->year, $sale->month, 1)->format('M Y')),
            'datasets' => [[
                'label' => 'Monthly Sales',
                'data' => $monthlySales->pluck('total'),
                'borderColor' => '#4F46E5',
                'tension' => 0.4
            ]]
        ];

        // Get top 5 selling products
        $topProducts = SaleDetail::select(
            'products.name',
            DB::raw('SUM(sale_details.quantity) as total_quantity')
        )
            ->join('products', 'products.id', '=', 'sale_details.product_id')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        $topProductsData = [
            'labels' => $topProducts->pluck('name'),
            'datasets' => [[
                'data' => $topProducts->pluck('total_quantity'),
                'backgroundColor' => ['#4F46E5', '#7C3AED', '#EC4899', '#F59E0B', '#10B981']
            ]]
        ];

        // Get daily transactions for the last 7 days
        $dailyTransactions = Sale::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('created_at', [now()->subDays(6), now()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyTransactionsData = [
            'labels' => $dailyTransactions->map(fn ($transaction) => Carbon::parse($transaction->date)->format('D')),
            'datasets' => [[
                'label' => 'Daily Transactions',
                'data' => $dailyTransactions->pluck('count'),
                'backgroundColor' => '#4F46E5'
            ]]
        ];

        // Get summary statistics
        $stats = [
            'totalSales' => 'Rp ' . number_format(Sale::sum('grand_total'), 0, ',', '.'),
            'totalTransactions' => number_format(Sale::count(), 0, ',', '.'),
            'averageTransaction' => 'Rp ' . number_format(Sale::avg('grand_total') ?? 0, 0, ',', '.'),
            'totalProducts' => number_format(Product::count(), 0, ',', '.')
        ];

        // Get recent transactions
        $recentTransactions = Sale::with(['customer'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->reference_no,
                    'date' => $sale->created_at->format('Y-m-d'),
                    'amount' => 'Rp ' . number_format($sale->grand_total, 0, ',', '.'),
                    'status' => $sale->payment_status
                ];
            });

        return Inertia::render('Dashboard', [
            'salesData' => $salesData,
            'topProducts' => $topProductsData,
            'dailyTransactions' => $dailyTransactionsData,
            'stats' => $stats,
            'recentTransactions' => $recentTransactions
        ]);
    }
} 