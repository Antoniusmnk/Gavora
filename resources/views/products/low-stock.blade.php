@extends('layouts.app')

@section('title', 'Peringatan Stok Menipis (Low Stock) - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Produk Stok Menipis (Low Stock)</h3>
            <p class="text-muted mb-0">Daftar produk yang jumlah stoknya telah mencapai atau di bawah batas minimum pengamanan.</p>
        </div>
        <div>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog Lengkap
            </a>
        </div>
    </div>

    <!-- Alert Info -->
    <div class="alert alert-danger d-flex align-items-center border-0 shadow-sm rounded-3 mb-4">
        <i class="bi bi-bell-fill fs-3 me-3 text-danger"></i>
        <div>
            <h6 class="fw-bold mb-1">Perhatian Tim Gudang & Pengadaan</h6>
            <div class="small">
                Terdapat <strong>{{ $products->total() }} produk</strong> yang membutuhkan pengadaan ulang (restock) segera agar operasional penjualan tidak terhenti karena kehabisan stok (*out of stock*).
            </div>
        </div>
    </div>

    <!-- Low Stock Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-danger">Daftar Barang Kritis</h6>
            <span class="badge bg-danger">{{ $products->total() }} Item Butuh Restock</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>SKU</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th class="text-center">Sisa Stok</th>
                        <th class="text-center">Batas Minimum</th>
                        <th class="text-end">Harga Beli Standar</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $index => $prod)
                    <tr class="table-danger-subtle">
                        <td>{{ $products->firstItem() + $index }}</td>
                        <td><code>{{ $prod->sku }}</code></td>
                        <td>
                            <a href="{{ route('products.show', $prod) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $prod->name }}
                            </a>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $prod->category->name }}</span></td>
                        <td class="text-center">
                            <span class="badge bg-danger fs-6 px-3 py-1">{{ $prod->stock }}</span>
                        </td>
                        <td class="text-center fw-semibold text-secondary">{{ $prod->minimum_stock }}</td>
                        <td class="text-end text-muted">Rp {{ number_format($prod->purchase_price, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <a href="{{ route('products.show', $prod) }}" class="btn btn-sm btn-outline-primary" title="Lihat Detail & Riwayat">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-success">
                            <i class="bi bi-check-circle-fill fs-1 d-block mb-2"></i>
                            <h5 class="fw-bold mb-1">Seluruh Stok Produk Aman!</h5>
                            <p class="text-muted small mb-0">Tidak ada produk yang berada di bawah atau sama dengan batas minimum stok.</p>
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
