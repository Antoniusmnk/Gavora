@extends('layouts.app')

@section('title', (auth()->user()->isCustomer() ? 'Katalog Produk' : 'Katalog & Stok Produk') . ' - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">{{ auth()->user()->isCustomer() ? 'Katalog Produk' : 'Katalog & Stok Produk' }}</h4>
            <p class="text-muted mb-0 small">
                {{ auth()->user()->isCustomer() ? 'Jelajahi berbagai pilihan produk berkualitas, cek ketersediaan stok, dan informasi harga.' : 'Daftar seluruh item barang inventaris, status ketersediaan stok fisik, dan patokan harga.' }}
            </p>
        </div>
        @if(!auth()->user()->isCustomer())
        <div class="d-flex gap-2">
            <a href="{{ route('products.low-stock') }}" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-exclamation-triangle me-1"></i> Stok Menipis
                <span class="badge bg-danger ms-1 rounded-pill">{{ \App\Models\Product::lowStock()->count() }}</span>
            </a>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> + Tambah Produk Baru
                </a>
            @endif
        </div>
        @endif
    </div>

    <!-- Kategori Quick Filter untuk Customer -->
    @if(auth()->user()->isCustomer())
    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
        <span class="small fw-semibold text-secondary me-1"><i class="bi bi-tags me-1"></i> Kategori:</span>
        <a href="{{ route('products.index', ['search' => request('search')]) }}" 
           class="btn btn-sm rounded-pill px-3 {{ !request('category_id') ? 'btn-primary' : 'btn-outline-secondary bg-white' }}">
            Semua Kategori
        </a>
        @foreach($categories as $c)
            <a href="{{ route('products.index', ['category_id' => $c->id, 'search' => request('search')]) }}" 
               class="btn btn-sm rounded-pill px-3 {{ request('category_id') == $c->id ? 'btn-primary' : 'btn-outline-secondary bg-white' }}">
                {{ $c->name }}
            </a>
        @endforeach
    </div>
    @endif

    <!-- Filter & Search Card -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <form action="{{ route('products.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="{{ auth()->user()->isCustomer() ? 'col-md-6' : 'col-md-4' }}">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-secondary"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Cari SKU atau nama barang..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="{{ auth()->user()->isCustomer() ? 'col-md-4' : 'col-md-3' }}">
                    <select name="category_id" class="form-select">
                        <option value="">-- Semua Kategori --</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if(!auth()->user()->isCustomer())
                <div class="col-md-3">
                    <select name="stock_status" class="form-select">
                        <option value="">-- Semua Status Stok --</option>
                        <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>
                            Hanya Stok Menipis (Kritis)
                        </option>
                    </select>
                </div>
                @endif
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                    @if(request()->filled('search') || request()->filled('category_id') || request()->filled('stock_status'))
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table Card -->
    <div class="card">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">{{ auth()->user()->isCustomer() ? 'Katalog & Daftar Produk' : 'Daftar Inventaris Produk' }}</h6>
            <span class="badge bg-light text-secondary border">Total: {{ $products->total() }} Produk</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Kode SKU</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        @if(!auth()->user()->isCustomer())
                            <th class="text-end">Harga Beli</th>
                        @endif
                        <th class="text-end">Harga Jual</th>
                        <th class="text-center">{{ auth()->user()->isCustomer() ? 'Ketersediaan' : 'Sisa Stok' }}</th>
                        @if(!auth()->user()->isCustomer())
                            <th class="text-center">Batas Min</th>
                        @endif
                        <th class="text-center" style="width: {{ auth()->user()->isCustomer() ? '110px' : '150px' }};">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $index => $prod)
                    <tr class="{{ (!auth()->user()->isCustomer() && $prod->isLowStock()) ? 'table-danger-subtle' : '' }}">
                        <td>{{ $products->firstItem() + $index }}</td>
                        <td>
                            <code class="fw-semibold text-dark">{{ $prod->sku }}</code>
                        </td>
                        <td>
                            <a href="{{ route('products.show', $prod) }}" class="fw-semibold text-decoration-none text-dark d-block">
                                {{ $prod->name }}
                            </a>
                            @if(!auth()->user()->isCustomer() && $prod->isLowStock())
                                <span class="badge bg-danger-subtle text-danger" style="font-size: 0.7rem;">
                                    Stok Menipis
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-secondary border">{{ $prod->category->name }}</span>
                        </td>
                        @if(!auth()->user()->isCustomer())
                        <td class="text-end text-muted">
                            Rp {{ number_format($prod->purchase_price, 0, ',', '.') }}
                        </td>
                        @endif
                        <td class="text-end fw-semibold text-dark">
                            Rp {{ number_format($prod->selling_price, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            @if(auth()->user()->isCustomer())
                                @if($prod->stock > 0)
                                    <span class="badge bg-success-subtle text-success fs-6 px-2 py-1">Tersedia ({{ $prod->stock }})</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary fs-6 px-2 py-1">Habis</span>
                                @endif
                            @else
                                @if($prod->isLowStock())
                                    <span class="badge bg-danger-subtle text-danger fs-6 px-2 py-1">{{ $prod->stock }}</span>
                                @else
                                    <span class="badge bg-success-subtle text-success fs-6 px-2 py-1">{{ $prod->stock }}</span>
                                @endif
                            @endif
                        </td>
                        @if(!auth()->user()->isCustomer())
                        <td class="text-center text-muted">{{ $prod->minimum_stock }}</td>
                        @endif
                        <td class="text-center">
                            @if(auth()->user()->isCustomer())
                                <a href="{{ route('products.show', $prod) }}" class="btn btn-outline-primary btn-sm px-2">
                                    <i class="bi bi-eye me-1"></i> Rincian
                                </a>
                            @else
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('products.show', $prod) }}" class="btn btn-outline-secondary" title="Lihat Rincian">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('products.edit', $prod) }}" class="btn btn-outline-primary" title="Edit Produk">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        @if($prod->hasTransactions())
                                            <button class="btn btn-outline-secondary" onclick="alert('Produk {{ $prod->name }} (SKU: {{ $prod->sku }}) tidak dapat dihapus karena telah memiliki riwayat transaksi pembelian atau penjualan.')" title="Terkunci karena memiliki transaksi">
                                                <i class="bi bi-lock-fill text-muted"></i>
                                            </button>
                                        @else
                                            <form action="{{ route('products.destroy', $prod) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ $prod->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Hapus Produk">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isCustomer() ? 7 : 9 }}" class="text-center py-4 text-muted">
                            <i class="bi bi-boxes fs-1 d-block mb-2 text-secondary"></i>
                            Tidak ada data produk yang sesuai dengan kriteria pencarian atau filter.
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
