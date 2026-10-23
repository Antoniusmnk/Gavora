@extends('layouts.app')

@section('title', 'Tambah Produk Baru - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1">Tambah Produk Baru</h3>
                    <p class="text-muted mb-0">Daftarkan item barang baru ke dalam katalog inventaris.</p>
                </div>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-box-seam-fill me-1"></i> Form Produk Baru</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('products.store') }}" method="POST" novalidate>
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label for="sku" class="form-label fw-semibold">Kode SKU <span class="text-danger">*</span></label>
                                <input type="text" name="sku" id="sku" 
                                       class="form-control text-uppercase @error('sku') is-invalid @enderror" 
                                       value="{{ old('sku') }}" 
                                       placeholder="Contoh: PRD-ACC-005" required autofocus>
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small">SKU harus unik di seluruh sistem.</div>
                            </div>

                            <div class="col-md-7">
                                <label for="category_id" class="form-label fw-semibold">Kategori Produk <span class="text-danger">*</span></label>
                                <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
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
                            <label for="name" class="form-label fw-semibold">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" 
                                   placeholder="Contoh: Monitor LG 24 Inch IPS FHD" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="purchase_price" class="form-label fw-semibold">Harga Beli Standar (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" step="100" min="0" name="purchase_price" id="purchase_price" 
                                           class="form-control @error('purchase_price') is-invalid @enderror" 
                                           value="{{ old('purchase_price', 0) }}" required>
                                    @error('purchase_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text small">Harga modal pembelian dari supplier.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="selling_price" class="form-label fw-semibold">Harga Jual Standar (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" step="100" min="0" name="selling_price" id="selling_price" 
                                           class="form-control @error('selling_price') is-invalid @enderror" 
                                           value="{{ old('selling_price', 0) }}" required>
                                    @error('selling_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text small">Harga patokan penjualan ke pelanggan.</div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="stock" class="form-label fw-semibold">Stok Awal Inventaris</label>
                                <input type="number" min="0" name="stock" id="stock" 
                                       class="form-control @error('stock') is-invalid @enderror" 
                                       value="{{ old('stock', 0) }}">
                                @error('stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small">Hanya diinput sekali saat pembuatan produk baru.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="minimum_stock" class="form-label fw-semibold">Batas Minimum Stok (Alert) <span class="text-danger">*</span></label>
                                <input type="number" min="0" name="minimum_stock" id="minimum_stock" 
                                       class="form-control @error('minimum_stock') is-invalid @enderror" 
                                       value="{{ old('minimum_stock', 5) }}" required>
                                @error('minimum_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small">Peringatan otomatis muncul jika stok &le; angka ini.</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-3">
                            <a href="{{ route('products.index') }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Simpan Produk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
