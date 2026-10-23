@extends('layouts.app')

@section('title', 'Detail Produk: ' . $product->name . ' - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Rincian Data Produk</h4>
            <p class="text-muted mb-0 small">Informasi spesifikasi barang, estimasi margin keuntungan, dan histori mutasi stok fisik.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
            </a>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil-square me-1"></i> Edit Data Produk
                </a>
            @endif
        </div>
    </div>

    <!-- Overview Grid -->
    <div class="row g-4 mb-4">
        <!-- Main Spec Card -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-box me-2 text-primary"></i>Informasi & Penetapan Harga</h6>
                    <span class="badge bg-light text-secondary border">{{ $product->category->name }}</span>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-start mb-3">
                        <div class="p-3 rounded-3 bg-primary-subtle text-primary me-3">
                            <i class="bi bi-upc-scan fs-3"></i>
                        </div>
                        <div>
                            <span class="text-secondary small fw-medium">Kode SKU Barang:</span>
                            <h5 class="fw-bold text-dark mb-1"><code>{{ $product->sku }}</code></h5>
                            <h6 class="text-secondary fw-semibold">{{ $product->name }}</h6>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <div class="text-secondary small">Harga Beli Standar</div>
                                <div class="fw-bold fs-6 text-dark mt-1">
                                    Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <div class="text-secondary small">Harga Jual Standar</div>
                                <div class="fw-bold fs-6 text-primary mt-1">
                                    Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            @php
                                $margin = $product->selling_price - $product->purchase_price;
                                $marginPct = $product->purchase_price > 0 ? ($margin / $product->purchase_price) * 100 : 0;
                            @endphp
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <div class="text-secondary small">Estimasi Margin Laba</div>
                                <div class="fw-bold fs-6 text-success mt-1">
                                    +Rp {{ number_format($margin, 0, ',', '.') }}
                                    <small class="d-block text-muted" style="font-size: 0.75rem;">({{ number_format($marginPct, 1) }}%)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Status Card -->
        <div class="col-lg-4">
            <div class="card h-100 {{ $product->isLowStock() ? 'border-danger-subtle' : '' }}">
                <div class="card-header py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-stack me-2 text-primary"></i>Ketersediaan Stok Fisik</h6>
                </div>
                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                    <div class="text-secondary small mb-1">Kuantitas di Gudang</div>
                    <div class="display-6 fw-bold {{ $product->isLowStock() ? 'text-danger' : 'text-success' }} mb-2">
                        {{ $product->stock }} <span class="fs-6 text-muted">Unit</span>
                    </div>

                    @if($product->isLowStock())
                        <div class="alert alert-danger py-2 small mb-3 border-0">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Stok saat ini berada di bawah batas minimum ({{ $product->minimum_stock }} unit). Perlu pengadaan ulang.
                        </div>
                    @else
                        <div class="alert alert-success py-2 small mb-3 border-0">
                            <i class="bi bi-check-circle-fill me-1"></i> Stok aman (Batas minimum pengamanan: {{ $product->minimum_stock }} unit).
                        </div>
                    @endif

                    <div class="d-grid gap-2">
                        <a href="{{ route('purchases.create') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-cart-plus me-1"></i> Buat Pesanan Beli (Restock)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Movement Tabs / Cards -->
    <div class="row g-4">
        <!-- Riwayat Pembelian (Stock-In) -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-arrow-down-left-circle text-primary me-2"></i>Riwayat Penerimaan (Pembelian)
                    </h6>
                    <span class="badge bg-light text-secondary border">
                        {{ $product->purchaseItems->count() }} Kali Masuk
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No. PO</th>
                                <th>Pemasok</th>
                                <th class="text-center">Kuantitas</th>
                                <th class="text-end">Harga Beli</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($product->purchaseItems as $pi)
                            <tr>
                                <td><code>{{ $pi->purchase->purchase_number }}</code></td>
                                <td>{{ $pi->purchase->supplier->name ?? '-' }}</td>
                                <td class="text-center fw-bold text-success">+{{ $pi->quantity }}</td>
                                <td class="text-end text-muted">Rp {{ number_format($pi->unit_cost, 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($pi->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted small">Belum ada riwayat penerimaan pembelian untuk produk ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Riwayat Penjualan (Stock-Out) -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-arrow-up-right-circle text-success me-2"></i>Riwayat Pengeluaran (Penjualan)
                    </h6>
                    <span class="badge bg-light text-secondary border">
                        {{ $product->saleItems->count() }} Kali Terjual
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No. Faktur</th>
                                <th>Kasir</th>
                                <th class="text-center">Kuantitas</th>
                                <th class="text-end">Harga Jual</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($product->saleItems as $si)
                            <tr>
                                <td><code>{{ $si->sale->sale_number }}</code></td>
                                <td>{{ $si->sale->user->name ?? '-' }}</td>
                                <td class="text-center fw-bold text-danger">-{{ $si->quantity }}</td>
                                <td class="text-end text-muted">Rp {{ number_format($si->unit_price, 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($si->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted small">Belum ada riwayat transaksi penjualan untuk produk ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
