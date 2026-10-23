@extends('layouts.app')

@section('title', 'Dashboard Utama - Gavora ERP')

@section('content')
<div class="container-fluid">
    <!-- Header Welcome -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Dashboard Ikhtisar Operasional</h3>
            <p class="text-muted mb-0">
                Selamat datang kembali, <strong>{{ auth()->user()->name }}</strong>! Anda masuk dengan peran 
                <span class="badge {{ auth()->user()->isAdmin() ? 'badge-role-admin' : 'badge-role-staff' }}">{{ strtoupper(auth()->user()->role) }}</span>.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales.create') }}" class="btn btn-success shadow-sm">
                <i class="bi bi-cart-check me-1"></i> Transaksi Penjualan
            </a>
            <a href="{{ route('purchases.create') }}" class="btn btn-warning text-dark fw-semibold shadow-sm">
                <i class="bi bi-cart-plus me-1"></i> Transaksi Pembelian
            </a>
        </div>
    </div>

    <!-- Ringkasan Agregat Angka Nyata Database (Cards) -->
    <div class="row g-3 mb-4">
        <!-- Penjualan Hari Ini -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 me-3">
                        <i class="bi bi-cash-coin fs-2"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Penjualan Hari Ini</div>
                        <h4 class="fw-bold text-success mb-0">Rp {{ number_format($todaySalesTotal, 0, ',', '.') }}</h4>
                        <small class="text-muted">{{ $todaySalesCount }} Transaksi Sukses</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Penjualan Bulan Ini -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 me-3">
                        <i class="bi bi-graph-up-arrow fs-2"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Omzet Penjualan ({{ now()->translatedFormat('F Y') }})</div>
                        <h4 class="fw-bold text-primary mb-0">Rp {{ number_format($thisMonthSalesTotal, 0, ',', '.') }}</h4>
                        <small class="text-muted">Total Pembayaran Diterima</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pembelian Bulan Ini -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 me-3">
                        <i class="bi bi-truck fs-2"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Pengadaan Stok ({{ now()->translatedFormat('F Y') }})</div>
                        <h4 class="fw-bold text-dark mb-0">Rp {{ number_format($thisMonthPurchasesTotal, 0, ',', '.') }}</h4>
                        <small class="text-muted">Total Pembelian ke Supplier</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Katalog & Kategori -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 me-3">
                        <i class="bi bi-boxes fs-2"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Inventaris Produk</div>
                        <h4 class="fw-bold text-dark mb-0">{{ $totalProducts }} Produk</h4>
                        <small class="text-muted">Dalam {{ $totalCategories }} Kategori</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Banner Stok Kritis -->
    @if($lowStockCount > 0)
    <div class="card border-0 shadow-sm rounded-3 mb-4 border-start border-danger border-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-3 me-3"></i>
                    <div>
                        <h5 class="fw-bold text-danger mb-0">Peringatan Kritis: Produk Stok Menipis</h5>
                        <p class="text-muted small mb-0">Terdapat <strong>{{ $lowStockCount }} produk</strong> dengan kuantitas stok &le; batas minimum pengamanan.</p>
                    </div>
                </div>
                <a href="{{ route('products.low-stock') }}" class="btn btn-danger btn-sm px-3">
                    Lihat Semua ({{ $lowStockCount }})
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>SKU</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th class="text-center">Sisa Stok</th>
                            <th class="text-center">Batas Minimum</th>
                            <th class="text-end">Harga Beli Standar</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @foreach($lowStockProducts as $lp)
                        <tr class="table-danger-subtle">
                            <td><code>{{ $lp->sku }}</code></td>
                            <td class="fw-bold text-dark">{{ $lp->name }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $lp->category->name }}</span></td>
                            <td class="text-center">
                                <span class="badge bg-danger fs-6 px-2 py-1">{{ $lp->stock }}</span>
                            </td>
                            <td class="text-center text-muted fw-semibold">{{ $lp->minimum_stock }}</td>
                            <td class="text-end text-muted">Rp {{ number_format($lp->purchase_price, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <a href="{{ route('products.show', $lp) }}" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- 5 Transaksi Penjualan Terakhir & Info Sistem -->
    <div class="row g-4">
        <!-- 5 Transaksi Terakhir -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-clock-history me-1 text-primary"></i> 5 Transaksi Penjualan Terakhir
                    </h6>
                    <a href="{{ route('sales.index') }}" class="small text-decoration-none">Lihat Semua Riwayat &rarr;</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>No. Faktur</th>
                                <th>Kasir / Petugas</th>
                                <th class="text-end">Total Pembayaran</th>
                                <th class="text-center">Status</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($recentSales as $sale)
                            <tr>
                                <td>
                                    <a href="{{ route('sales.show', $sale) }}" class="fw-bold text-decoration-none">
                                        <code>{{ $sale->sale_number }}</code>
                                    </a>
                                </td>
                                <td>{{ $sale->user->name }}</td>
                                <td class="text-end fw-bold text-success">
                                    Rp {{ number_format($sale->total, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($sale->status === 'completed')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Completed</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Cancelled</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $sale->sold_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada transaksi penjualan tercatat.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Info Otorisasi & Integritas Sistem -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-check me-1 text-success"></i> Integritas Sistem & Otorisasi</h6>
                </div>
                <div class="card-body p-3">
                    <div class="p-2 mb-2 bg-light rounded-2 border small">
                        <div class="fw-bold text-dark"><i class="bi bi-cpu text-primary me-1"></i> Laravel 12 Engine</div>
                        <div class="text-muted">Middleware alias terdaftar di <code>bootstrap/app.php</code> via <code>withMiddleware()</code>.</div>
                    </div>

                    <div class="p-2 mb-2 bg-light rounded-2 border small">
                        <div class="fw-bold text-dark"><i class="bi bi-lock-fill text-warning me-1"></i> Pessimistic Locking</div>
                        <div class="text-muted">Mencegah *race condition* dengan <code>Product::lockForUpdate()</code> dalam <code>DB::transaction()</code>.</div>
                    </div>

                    <div class="p-2 bg-light rounded-2 border small">
                        <div class="fw-bold text-dark"><i class="bi bi-arrow-repeat text-success me-1"></i> Atomisitas Mutasi</div>
                        <div class="text-muted">Jika stok kurang, seluruh transaksi otomatis di-rollback tanpa menyimpan data header.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
