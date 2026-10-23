@extends('layouts.app')

@section('title', 'Detail Produk: ' . $product->name . ' - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Rincian Informasi Produk</h3>
            <p class="text-muted mb-0">Informasi spesifikasi barang, performa margin, dan riwayat pergerakan stok.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
            </a>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
                    <i class="bi bi-pencil-square me-1"></i> Edit Data Produk
                </a>
            @endif
        </div>
    </div>

    <!-- Overview Grid -->
    <div class="row g-3 mb-4">
        <!-- Main Spec Card -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-box me-1"></i> Spesifikasi & Kategori</h6>
                    <span class="badge bg-light text-dark border">{{ $product->category->name }}</span>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-start mb-3">
                        <div class="p-3 rounded-3 bg-primary bg-opacity-10 text-primary me-3">
                            <i class="bi bi-upc-scan fs-2"></i>
                        </div>
                        <div>
                            <span class="text-muted small">Kode SKU:</span>
                            <h4 class="fw-bold text-dark mb-1"><code>{{ $product->sku }}</code></h4>
                            <h5 class="text-secondary fw-semibold">{{ $product->name }}</h5>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <div class="text-muted small">Harga Beli Standar</div>
                                <div class="fw-bold fs-6 text-dark mt-1">
                                    Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <div class="text-muted small">Harga Jual Standar</div>
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
                            <div class="p-3 bg-light rounded-3 text-center">
                                <div class="text-muted small">Estimasi Margin</div>
                                <div class="fw-bold fs-6 text-success mt-1">
                                    +Rp {{ number_format($margin, 0, ',', '.') }}
                                    <small class="d-block" style="font-size: 0.75rem;">({{ number_format($marginPct, 1) }}%)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Status Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 {{ $product->isLowStock() ? 'border-start border-danger border-4' : 'border-start border-success border-4' }}">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-stack me-1"></i> Status Ketersediaan Stok</h6>
                </div>
                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                    <div class="text-muted small mb-1">Kuantitas Stok Gudang</div>
                    <div class="display-5 fw-bold {{ $product->isLowStock() ? 'text-danger' : 'text-success' }} mb-2">
                        {{ $product->stock }} <span class="fs-6 text-muted">Unit</span>
                    </div>

                    @if($product->isLowStock())
                        <div class="alert alert-danger py-2 small mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Peringatan:</strong> Stok berada di bawah/sama dengan batas minimum ({{ $product->minimum_stock }} unit). Segera lakukan pembelian (Stock-In).
                        </div>
                    @else
                        <div class="alert alert-success py-2 small mb-3">
                            <i class="bi bi-check-circle-fill me-1"></i> Stok aman (Batas minimum: {{ $product->minimum_stock }} unit).
                        </div>
                    @endif

                    <div class="d-grid gap-2">
                        <a href="{{ url('/purchases') }}" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-cart-plus me-1"></i> Buat Pesanan Beli (Restock)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Movement Tabs / Cards -->
    <div class="row g-3">
        <!-- Riwayat Pembelian (Stock-In) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-arrow-down-left-circle text-warning me-1"></i> Riwayat Masuk (Pembelian)
                    </h6>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                        {{ $product->purchaseItems->count() }} Kali Masuk
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>No. PO</th>
                                <th>Supplier</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga Beli</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($product->purchaseItems as $pi)
                            <tr>
                                <td><code>{{ $pi->purchase->purchase_number }}</code></td>
                                <td>{{ $pi->purchase->supplier->name ?? '-' }}</td>
                                <td class="text-center fw-bold text-success">+{{ $pi->quantity }}</td>
                                <td class="text-end">Rp {{ number_format($pi->unit_cost, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($pi->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">Belum ada riwayat pembelian untuk produk ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Riwayat Penjualan (Stock-Out) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-arrow-up-right-circle text-success me-1"></i> Riwayat Keluar (Penjualan)
                    </h6>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        {{ $product->saleItems->count() }} Kali Terjual
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>No. Invoice</th>
                                <th>Kasir / Staf</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga Jual</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($product->saleItems as $si)
                            <tr>
                                <td><code>{{ $si->sale->sale_number }}</code></td>
                                <td>{{ $si->sale->user->name ?? '-' }}</td>
                                <td class="text-center fw-bold text-danger">-{{ $si->quantity }}</td>
                                <td class="text-end">Rp {{ number_format($si->unit_price, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($si->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">Belum ada riwayat penjualan untuk produk ini.</td>
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
