@extends('layouts.app')

@section('title', 'Peringatan Stok Menipis - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Daftar Produk Stok Menipis</h4>
            <p class="text-muted mb-0 small">Daftar inventaris barang yang jumlah stoknya telah mencapai atau berada di bawah batas minimum pengamanan.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-cart-plus me-1"></i> + Buat Pesanan Beli (Restock)
            </a>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog Lengkap
            </a>
        </div>
    </div>

    <!-- Alert Info -->
    <div class="alert alert-danger d-flex align-items-center border-0 shadow-sm rounded-3 mb-4">
        <i class="bi bi-bell-fill fs-4 me-3 text-danger"></i>
        <div>
            <div class="fw-bold">Pemberitahuan Tim Gudang & Pengadaan</div>
            <div class="small">
                Terdapat <strong>{{ $products->total() }} item produk</strong> yang membutuhkan pengadaan ulang (restock) segera agar operasional penjualan tetap berjalan lancar.
            </div>
        </div>
    </div>

    <!-- Low Stock Table Card -->
    <div class="card">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">Daftar Barang Kritis</h6>
            <span class="badge bg-danger-subtle text-danger">{{ $products->total() }} Item Butuh Restock</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
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
                    @forelse($products as $index => $prod)
                    <tr class="table-danger-subtle">
                        <td>{{ $products->firstItem() + $index }}</td>
                        <td><code>{{ $prod->sku }}</code></td>
                        <td>
                            <a href="{{ route('products.show', $prod) }}" class="fw-semibold text-dark text-decoration-none">
                                {{ $prod->name }}
                            </a>
                        </td>
                        <td><span class="badge bg-light text-secondary border">{{ $prod->category->name }}</span></td>
                        <td class="text-center">
                            <span class="badge bg-danger fs-6 px-3 py-1">{{ $prod->stock }} Unit</span>
                        </td>
                        <td class="text-center fw-semibold text-secondary">{{ $prod->minimum_stock }} Unit</td>
                        <td class="text-end text-muted">Rp {{ number_format($prod->purchase_price, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <a href="{{ route('products.show', $prod) }}" class="btn btn-sm btn-outline-secondary" title="Lihat Detail & Riwayat">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-success">
                            <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
                            <h5 class="fw-bold mb-1">Seluruh Stok Produk Aman</h5>
                            <p class="text-muted small mb-0">Tidak ada produk yang berada di bawah atau sama dengan batas minimum pengamanan stok.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-end">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
