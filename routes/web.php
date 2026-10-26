<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard jika admin/staff, ke katalog jika customer, atau ke login jika belum login
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->isCustomer()
        ? redirect()->route('products.index')
        : redirect()->route('dashboard');
});

// Autentikasi Pengguna (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/login/customer', [AuthController::class, 'loginAsCustomer'])->name('login.customer');
});

// Area Terproteksi (Login Diperlukan)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Katalog Produk & Rincian (Dapat Diakses Seluruh Role: Admin, Staff, dan Customer)
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');

    // Fitur Khusus Internal Toko & Operasional Gudang (Role: Admin & Staff)
    Route::middleware('role:admin,staff')->group(function () {
        // Dashboard Utama dengan Data Agregat Finansial & Omzet
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Monitoring Stok Menipis Gudang
        Route::get('/products/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');

        // Transaksi Pembelian (Stock-In)
        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');

        // Transaksi Penjualan (Kasir / Stock-Out)
        Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
        Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
        Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    });

    // Khusus Role Admin (Server-Side Authorization via RoleMiddleware)
    Route::middleware('role:admin')->group(function () {
        // Manajemen Pengguna (Users - Kecuali Show)
        Route::resource('users', UserController::class)->except(['show']);

        // Master Data Categories & Suppliers (Kecuali Show)
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('suppliers', SupplierController::class)->except(['show']);

        // Produk Mutasi (Create, Store, Edit, Update, Destroy) Khusus Admin
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::match(['put', 'patch'], '/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        // Pembatalan Transaksi Khusus Admin (Mendukung PATCH dan POST)
        Route::match(['post', 'patch'], '/purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
        Route::match(['post', 'patch'], '/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
    });

    // Detail Produk (Diletakkan setelah route statis create & low-stock agar tidak tertimpa)
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
});
