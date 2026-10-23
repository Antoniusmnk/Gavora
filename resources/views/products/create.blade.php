@extends('layouts.app')

@section('title', 'Tambah Produk Baru - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Registrasi Produk Baru</h4>
            <p class="text-muted mb-0 small">Daftarkan item barang baru ke dalam katalog inventaris perusahaan.</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
        </a>
    </div>

    <form action="{{ route('products.store') }}" method="POST" novalidate>
        @csrf

        <div class="row g-4">
            <!-- Kolom Form Input (Kiri) -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header py-3">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-box-seam me-2 text-primary"></i>Formulir Data Produk</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label for="sku" class="form-label small fw-semibold text-secondary">Kode SKU <span class="text-danger">*</span></label>
                                <input type="text" name="sku" id="sku" 
                                       class="form-control text-uppercase @error('sku') is-invalid @enderror" 
                                       value="{{ old('sku') }}" 
                                       placeholder="Misal: PRD-ACC-005" required autofocus>
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-7">
                                <label for="category_id" class="form-label small fw-semibold text-secondary">Kategori Produk <span class="text-danger">*</span></label>
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
                            <label for="name" class="form-label small fw-semibold text-secondary">Nama Lengkap Produk <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" 
                                   placeholder="Misal: Monitor LED LG 24 Inch IPS FHD" required>
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
                                           value="{{ old('purchase_price', 0) }}" required>
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
                                           value="{{ old('selling_price', 0) }}" required>
                                    @error('selling_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="stock" class="form-label small fw-semibold text-secondary">Stok Awal Barang</label>
                                <input type="number" min="0" name="stock" id="stock" 
                                       class="form-control @error('stock') is-invalid @enderror" 
                                       value="{{ old('stock', 0) }}">
                                @error('stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="minimum_stock" class="form-label small fw-semibold text-secondary">Batas Minimum Pengamanan Stok <span class="text-danger">*</span></label>
                                <input type="number" min="0" name="minimum_stock" id="minimum_stock" 
                                       class="form-control @error('minimum_stock') is-invalid @enderror" 
                                       value="{{ old('minimum_stock', 5) }}" required>
                                @error('minimum_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-3">
                            <a href="{{ route('products.index') }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                                <i class="bi bi-save me-1"></i> Simpan Produk
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Panduan Inventaris (Kanan) -->
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header py-3">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-info-circle me-2 text-primary"></i>Panduan Inventaris</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <div class="fw-semibold small text-dark mb-1">Format Standar SKU:</div>
                            <p class="small text-muted mb-0">
                                Gunakan kode unik seperti <code>PRD-[KATEGORI]-[NOMOR]</code> untuk memudahkan pencarian di kasir dan gudang.
                            </p>
                        </div>
                        <div class="mb-3">
                            <div class="fw-semibold small text-dark mb-1">Batas Minimum Stok:</div>
                            <p class="small text-muted mb-0">
                                Sistem secara otomatis menampilkan peringatan stok menipis pada dashboard jika sisa fisik produk mencapai atau di bawah batas minimum ini.
                            </p>
                        </div>
                        <div>
                            <div class="fw-semibold small text-dark mb-1">Mutasi Kuantitas:</div>
                            <p class="small text-muted mb-0">
                                Kuantitas stok awal hanya diisi saat registrasi produk baru. Seluruh mutasi berikutnya akan dicatat otomatis melalui faktur Pembelian dan Penjualan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
