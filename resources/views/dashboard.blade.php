@extends('layouts.app')

@section('title', 'Dashboard Utama - Gavora ERP')

@section('content')
<div class="container-fluid">
    <!-- Header Welcome -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Ikhtisar Operasional Bisnis</h4>
            <p class="text-muted mb-0 small">
                Ringkasan performa penjualan harian, pengadaan inventaris, dan status ketersediaan barang di gudang.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales.create') }}" class="btn btn-success shadow-sm btn-sm px-3">
                <i class="bi bi-cart-check me-1"></i> + Penjualan Baru
            </a>
            <a href="{{ route('purchases.create') }}" class="btn btn-primary shadow-sm btn-sm px-3">
                <i class="bi bi-arrow-down-left-square me-1"></i> + Pembelian Stok
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- Penjualan Hari Ini -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-3 bg-success-subtle text-success p-3 me-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-cash-stack fs-3"></i>
                    </div>
                    <div>
                        <div class="text-secondary small fw-medium">Penjualan Hari Ini</div>
                        <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($todaySalesTotal, 0, ',', '.') }}</h5>
                        <small class="text-muted" style="font-size: 0.75rem;">{{ $todaySalesCount }} Transaksi Berhasil</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Omzet Bulan Ini -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-graph-up-arrow fs-3"></i>
                    </div>
                    <div>
                        <div class="text-secondary small fw-medium">Omzet Bulan Ini</div>
                        <h5 class="fw-bold text-primary mb-0">Rp {{ number_format($thisMonthSalesTotal, 0, ',', '.') }}</h5>
                        <small class="text-muted" style="font-size: 0.75rem;">Total {{ $totalTransactions }} Transaksi ({{ now()->translatedFormat('M Y') }})</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pengadaan Bulan Ini -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-3 bg-warning-subtle text-warning-emphasis p-3 me-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-truck fs-3"></i>
                    </div>
                    <div>
                        <div class="text-secondary small fw-medium">Pengadaan Stok (Bulan Ini)</div>
                        <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($thisMonthPurchasesTotal, 0, ',', '.') }}</h5>
                        <small class="text-muted" style="font-size: 0.75rem;">Total Pembelian Supplier</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Katalog Produk -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-3 bg-info-subtle text-info-emphasis p-3 me-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-boxes fs-3"></i>
                    </div>
                    <div>
                        <div class="text-secondary small fw-medium">Total Item Produk</div>
                        <h5 class="fw-bold text-dark mb-0">{{ $totalProducts }} Produk</h5>
                        <small class="text-muted" style="font-size: 0.75rem;">Terbagi dalam {{ $totalCategories }} Kategori</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Peringatan Stok Menipis (Hanya tampil jika ada stok kritis) -->
    @if($lowStockCount > 0)
    <div class="card mb-4 border-danger-subtle">
        <div class="card-header bg-danger-subtle py-3 d-flex justify-content-between align-items-center border-bottom border-danger-subtle">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill text-danger fs-5 me-2"></i>
                <h6 class="fw-bold text-danger mb-0">Peringatan: Stok Barang Menipis (Perlu Pengadaan Ulang)</h6>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('purchases.create') }}" class="btn btn-danger btn-sm">
                    <i class="bi bi-cart-plus me-1"></i> Buat Pesanan Beli (Restock)
                </a>
                <a href="{{ route('products.low-stock') }}" class="btn btn-outline-danger btn-sm">
                    Lihat Semua ({{ $lowStockCount }})
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kode SKU</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th class="text-center">Sisa Stok</th>
                        <th class="text-center">Batas Minimum</th>
                        <th class="text-end">Harga Beli Standar</th>
                        <th class="text-center" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockProducts as $lp)
                    <tr>
                        <td><code>{{ $lp->sku }}</code></td>
                        <td class="fw-semibold text-dark">{{ $lp->name }}</td>
                        <td><span class="badge bg-light text-secondary border">{{ $lp->category->name }}</span></td>
                        <td class="text-center">
                            <span class="badge bg-danger-subtle text-danger fs-6 px-2 py-1">{{ $lp->stock }} Unit</span>
                        </td>
                        <td class="text-center text-muted fw-semibold">{{ $lp->minimum_stock }} Unit</td>
                        <td class="text-end text-muted">Rp {{ number_format($lp->purchase_price, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <a href="{{ route('products.show', $lp) }}" class="btn btn-sm btn-outline-secondary" title="Lihat Rincian">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- 5 Transaksi Terakhir & Akses Operasional Cepat -->
    <div class="row g-4">
        <!-- Tabel Transaksi Penjualan Terakhir -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-clock-history me-2 text-primary"></i>
                        <h6 class="fw-bold mb-0 text-dark">5 Transaksi Penjualan Terakhir</h6>
                    </div>
                    <a href="{{ route('sales.index') }}" class="small text-decoration-none fw-semibold">
                        Lihat Seluruh Riwayat &rarr;
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No. Faktur</th>
                                <th>Kasir</th>
                                <th class="text-end">Total Pembayaran</th>
                                <th class="text-center">Status</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSales as $sale)
                            <tr>
                                <td>
                                    <a href="{{ route('sales.show', $sale) }}" class="fw-bold text-decoration-none">
                                        <code>{{ $sale->sale_number }}</code>
                                    </a>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $sale->user->name }}</span>
                                </td>
                                <td class="text-end fw-bold text-dark">
                                    Rp {{ number_format($sale->total, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($sale->status === 'completed')
                                        <span class="badge bg-success-subtle text-success">Selesai</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Dibatalkan</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $sale->sold_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada transaksi penjualan yang tercatat.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Menu Akses Cepat Operasional (Business Oriented) -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-lightning-charge me-1 text-warning"></i> Menu Akses Cepat
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-grid gap-2">
                        <a href="{{ route('sales.create') }}" class="btn btn-outline-success text-start py-2 d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bi bi-cart-plus me-2 fs-5 align-middle"></i>
                                <span class="fw-semibold">Kasir Penjualan Baru</span>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>

                        <a href="{{ route('purchases.create') }}" class="btn btn-outline-primary text-start py-2 d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bi bi-truck me-2 fs-5 align-middle"></i>
                                <span class="fw-semibold">Faktur Pembelian Baru</span>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>

                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('products.create') }}" class="btn btn-outline-secondary text-start py-2 d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bi bi-plus-circle me-2 fs-5 align-middle"></i>
                                <span class="fw-semibold">Registrasi Produk Baru</span>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        @endif

                        <a href="{{ route('products.index') }}" class="btn btn-light border text-start py-2 d-flex align-items-center justify-content-between mt-1">
                            <div>
                                <i class="bi bi-boxes me-2 text-secondary"></i>
                                <span>Katalog Inventaris Lengkap</span>
                            </div>
                            <span class="badge bg-secondary rounded-pill">{{ $totalProducts }}</span>
                        </a>

                        <a href="{{ route('products.low-stock') }}" class="btn btn-light border text-start py-2 d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bi bi-exclamation-triangle me-2 text-danger"></i>
                                <span>Daftar Stok Menipis</span>
                            </div>
                            <span class="badge bg-danger rounded-pill">{{ $lowStockCount }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
