<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard dengan metrik agregat riil dari database.
     */
    public function index(): View
    {
        // 1. Metrik Produk & Kategori
        $totalProducts = Product::count();
        $totalCategories = Category::count();

        // 2. Metrik Penjualan Hari Ini
        $todaySalesTotal = Sale::where('status', 'completed')
            ->whereDate('sold_at', today())
            ->sum('total');

        $todaySalesCount = Sale::where('status', 'completed')
            ->whereDate('sold_at', today())
            ->count();

        // 3. Metrik Penjualan Bulan Ini
        $thisMonthSalesTotal = Sale::where('status', 'completed')
            ->whereMonth('sold_at', now()->month)
            ->whereYear('sold_at', now()->year)
            ->sum('total');

        // 4. Metrik Pembelian Bulan Ini
        $thisMonthPurchasesTotal = Purchase::where('status', 'completed')
            ->whereMonth('purchased_at', now()->month)
            ->whereYear('purchased_at', now()->year)
            ->sum('total');

        // 5. Total dan Daftar Produk Stok Rendah (Low-Stock)
        $lowStockCount = Product::lowStock()->count();
        $lowStockProducts = Product::with('category')
            ->lowStock()
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get();

        // 6. 5 Transaksi Penjualan Terakhir
        $recentSales = Sale::with('user')
            ->latest('sold_at')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalProducts',
            'totalCategories',
            'todaySalesTotal',
            'todaySalesCount',
            'thisMonthSalesTotal',
            'thisMonthPurchasesTotal',
            'lowStockCount',
            'lowStockProducts',
            'recentSales'
        ));
    }
}
