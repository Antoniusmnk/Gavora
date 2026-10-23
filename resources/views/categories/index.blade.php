@extends('layouts.app')

@section('title', 'Kategori Produk - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Master Kategori Produk</h3>
            <p class="text-muted mb-0">Kelola pengelompokan produk dan klasifikasi inventaris barang.</p>
        </div>
        <div>
            <a href="{{ route('categories.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-tag-fill me-1"></i> Tambah Kategori Baru
            </a>
        </div>
    </div>

    <!-- Search Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('categories.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Cari nama kategori..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-filter me-1"></i> Cari
                    </button>
                    @if(request()->filled('search'))
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary" title="Reset Pencarian">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Categories Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Daftar Kategori</h6>
            <span class="badge bg-light text-dark border">Total: {{ $categories->total() }} Kategori</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Nama Kategori</th>
                        <th class="text-center">Jumlah Produk Terkait</th>
                        <th>Dibuat Pada</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $index => $cat)
                    <tr>
                        <td>{{ $categories->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-semibold text-dark">
                                <i class="bi bi-folder2-open text-primary me-2"></i>{{ $cat->name }}
                            </div>
                        </td>
                        <td class="text-center">
                            @if($cat->products_count > 0)
                                <a href="{{ route('products.index', ['category_id' => $cat->id]) }}" class="badge bg-primary text-decoration-none px-2 py-1">
                                    {{ $cat->products_count }} Produk
                                </a>
                            @else
                                <span class="badge bg-light text-muted border px-2 py-1">0 Produk</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $cat->created_at->format('d M Y, H:i') }}</td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('categories.edit', $cat) }}" class="btn btn-outline-primary" title="Edit Kategori">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                @if($cat->products_count > 0)
                                    <button class="btn btn-outline-secondary" onclick="alert('Kategori {{ $cat->name }} tidak dapat dihapus karena masih menampung {{ $cat->products_count }} produk terkait. Sesuai aturan integritas referensial, produk harus dipindahkan atau dihapus terlebih dahulu.')" title="Kategori terkunci karena memiliki produk">
                                        <i class="bi bi-lock-fill text-muted"></i>
                                    </button>
                                @else
                                    <form action="{{ route('categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori {{ $cat->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus Kategori">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="bi bi-tags fs-1 d-block mb-2 text-secondary"></i>
                            Tidak ada kategori yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-end">
                {{ $categories->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
