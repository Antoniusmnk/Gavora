@extends('layouts.app')

@section('title', 'Tambah Kategori - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-1">Tambah Kategori Baru</h4>
                    <p class="text-muted mb-0 small">Tambahkan kelompok kategori baru untuk klasifikasi produk.</p>
                </div>
                <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card">
                <div class="card-header py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-tag me-2 text-primary"></i>Formulir Kategori</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('categories.store') }}" method="POST" novalidate>
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="form-label small fw-semibold text-secondary">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" 
                                   placeholder="Misal: Perangkat Jaringan" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small">Nama kategori harus unik di seluruh sistem inventaris.</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-3">
                            <a href="{{ route('categories.index') }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                                <i class="bi bi-save me-1"></i> Simpan Kategori
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
