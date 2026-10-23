@extends('layouts.app')

@section('title', 'Edit Produk - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1">Edit Master Produk</h3>
                    <p class="text-muted mb-0">Perbarui spesifikasi dan patokan harga untuk <strong>{{ $product->name }}</strong>.</p>
                </div>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
                </a>
            </div>

            <!-- Kartu Info Status Stok & Integritas Mutasi -->
            <div class="alert alert-info d-flex align-items-center mb-4 border-0 shadow-sm rounded-3">
                <i class="bi bi-info-circle-fill fs-3 me-3 text-info"></i>
                <div>
                    <div class="fw-bold">Stok Saat Ini: <span class="badge {{ $product->isLowStock() ? 'bg-danger' : 'bg-success' }} fs-6">{{ $product->stock }} Unit</span></div>
                    <div class="small text-muted mt-1">
                        Sesuai aturan integritas inventaris, kuantitas stok <strong>tidak dapat diubah langsung secara manual</strong> pada form edit. Perubahan stok hanya terjadi melalui transaksi <em>Pembelian (Stock-In)</em> atau <em>Penjualan (Stock-Out)</em>.
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-pencil-square me-1"></i> Form Edit Produk</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('products.update', $product) }}" method="POST" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label for="sku" class="form-label fw-semibold">Kode SKU <span class="text-danger">*</span></label>
                                <input type="text" name="sku" id="sku" 
                                       class="form-control text-uppercase @error('sku') is-invalid @enderror" 
                                       value="{{ old('sku', $product->sku) }}" required autofocus>
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-7">
                                <label for="category_id" class="form-label fw-semibold">Kategori Produk <span class="text-danger">*</span></label>
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
                            <label for="name" class="form-label fw-semibold">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $product->name) }}" required>
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
                                           value="{{ old('purchase_price', $product->purchase_price) }}" required>
                                    @error('purchase_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="selling_price" class="form-label fw-semibold">Harga Jual Standar (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" step="100" min="0" name="selling_price" id="selling_price" 
                                           class="form-control @error('selling_price') is-invalid @enderror" 
                                           value="{{ old('selling_price', $product->selling_price) }}" required>
                                    @error('selling_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="minimum_stock" class="form-label fw-semibold">Batas Minimum Stok (Alert) <span class="text-danger">*</span></label>
                                <input type="number" min="0" name="minimum_stock" id="minimum_stock" 
                                       class="form-control @error('minimum_stock') is-invalid @enderror" 
                                       value="{{ old('minimum_stock', $product->minimum_stock) }}" required>
                                @error('minimum_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">Status Riwayat Transaksi</label>
                                <div>
                                    @if($product->hasTransactions())
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2">
                                            <i class="bi bi-shield-check text-success me-1"></i> Terkunci di Riwayat Transaksi
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border px-3 py-2">
                                            <i class="bi bi-info-circle me-1"></i> Belum Pernah Ada Transaksi
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-3">
                            <a href="{{ route('products.index') }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i> Perbarui Data Produk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
