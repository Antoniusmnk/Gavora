@extends('layouts.app')

@section('title', 'Edit Produk: ' . $product->name . ' - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Edit Master Produk</h4>
            <p class="text-muted mb-0 small">Perbarui data spesifikasi, kategori, dan patokan harga jual/beli untuk <strong>{{ $product->name }}</strong>.</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
        </a>
    </div>

    <form action="{{ route('products.update', $product) }}" method="POST" novalidate>
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Kolom Form (Kiri) -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header py-3">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-pencil-square me-2 text-primary"></i>Formulir Pembaruan Produk</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label for="sku" class="form-label small fw-semibold text-secondary">Kode SKU <span class="text-danger">*</span></label>
                                <input type="text" name="sku" id="sku" 
                                       class="form-control text-uppercase @error('sku') is-invalid @enderror" 
                                       value="{{ old('sku', $product->sku) }}" required autofocus>
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-7">
                                <label for="category_id" class="form-label small fw-semibold text-secondary">Kategori Produk <span class="text-danger">*</span></label>
                                <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold text-secondary">Nama Lengkap Produk <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $product->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="purchase_price" class="form-label small fw-semibold text-secondary">Harga Beli Standar (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary">Rp</span>
                                    <input type="number" step="100" min="0" name="purchase_price" id="purchase_price" 
                                           class="form-control @error('purchase_price') is-invalid @enderror" 
                                           value="{{ old('purchase_price', $product->purchase_price) }}" required>
                                    @error('purchase_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="selling_price" class="form-label small fw-semibold text-secondary">Harga Jual Standar (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary">Rp</span>
                                    <input type="number" step="100" min="0" name="selling_price" id="selling_price" 
                                           class="form-control @error('selling_price') is-invalid @enderror" 
                                           value="{{ old('selling_price', $product->selling_price) }}" required>
                                    @error('selling_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="minimum_stock" class="form-label small fw-semibold text-secondary">Batas Minimum Pengamanan Stok <span class="text-danger">*</span></label>
                            <input type="number" min="0" name="minimum_stock" id="minimum_stock" 
                                   class="form-control @error('minimum_stock') is-invalid @enderror" 
                                   value="{{ old('minimum_stock', $product->minimum_stock) }}" required>
                            @error('minimum_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-3">
                            <a href="{{ route('products.index') }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                                <i class="bi bi-check-lg me-1"></i> Perbarui Data Produk
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Informasi Inventaris (Kanan) -->
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header py-3">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-stack me-2 text-primary"></i>Status Stok Gudang</h6>
                    </div>
                    <div class="card-body p-3 text-center">
                        <div class="text-muted small mb-1">Kuantitas Stok Saat Ini</div>
                        <div class="display-6 fw-bold {{ $product->isLowStock() ? 'text-danger' : 'text-success' }} mb-2">
                            {{ $product->stock }} <span class="fs-6 text-muted">Unit</span>
                        </div>
                        @if($product->isLowStock())
                            <span class="badge bg-danger-subtle text-danger px-3 py-1 mb-3">Status: Stok Menipis</span>
                        @else
                            <span class="badge bg-success-subtle text-success px-3 py-1 mb-3">Status: Stok Aman</span>
                        @endif

                        <hr class="my-2">

                        <div class="text-start p-2 bg-light rounded-2 mt-2">
                            <div class="small fw-semibold text-dark mb-1">
                                <i class="bi bi-shield-check text-primary me-1"></i> Ketentuan Mutasi Stok:
                            </div>
                            <p class="small text-muted mb-0">
                                Kuantitas stok tidak dapat diubah manual pada formulir ini. Setiap penambahan stok dicatat melalui menu Pembelian, dan pengurangan stok tercatat melalui menu Penjualan untuk memastikan laporan pembukuan akurat.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
