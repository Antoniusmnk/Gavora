@extends('layouts.app')

@section('title', 'Daftar Supplier - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Master Supplier (Pemasok)</h3>
            <p class="text-muted mb-0">Kelola informasi kontak dan daftar rekanan pemasok barang inventaris.</p>
        </div>
        <div>
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-truck me-1"></i> Tambah Supplier Baru
            </a>
        </div>
    </div>

    <!-- Search Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('suppliers.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Cari nama, nomor telepon, atau email pemasok..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-filter me-1"></i> Cari
                    </button>
                    @if(request()->filled('search'))
                        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary" title="Reset Pencarian">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Suppliers Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Daftar Pemasok</h6>
            <span class="badge bg-light text-dark border">Total: {{ $suppliers->total() }} Supplier</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Nama Perusahaan / Rekanan</th>
                        <th>Nomor Telepon</th>
                        <th>Alamat Email</th>
                        <th class="text-center">Riwayat Pembelian</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $index => $sup)
                    <tr>
                        <td>{{ $suppliers->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-semibold text-dark">
                                <i class="bi bi-building text-secondary me-2"></i>{{ $sup->name }}
                            </div>
                        </td>
                        <td>
                            <a href="tel:{{ $sup->phone }}" class="text-decoration-none text-dark">
                                <i class="bi bi-telephone text-primary me-1"></i>{{ $sup->phone }}
                            </a>
                        </td>
                        <td>
                            @if($sup->email)
                                <a href="mailto:{{ $sup->email }}" class="text-decoration-none">
                                    <i class="bi bi-envelope text-muted me-1"></i>{{ $sup->email }}
                                </a>
                            @else
                                <span class="text-muted small"><em>Tidak ada email</em></span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($sup->purchases_count > 0)
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                    <i class="bi bi-cart-check me-1"></i>{{ $sup->purchases_count }} Transaksi Beli
                                </span>
                            @else
                                <span class="badge bg-light text-muted border px-2 py-1">Belum Ada Transaksi</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('suppliers.edit', $sup) }}" class="btn btn-outline-primary" title="Edit Supplier">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                @if($sup->purchases_count > 0)
                                    <button class="btn btn-outline-secondary" onclick="alert('Pemasok {{ $sup->name }} tidak dapat dihapus karena sudah memiliki {{ $sup->purchases_count }} transaksi pembelian tercatat. Sesuai aturan integritas data akuntansi, supplier ini diproteksi.')" title="Supplier terkunci karena memiliki riwayat pembelian">
                                        <i class="bi bi-lock-fill text-muted"></i>
                                    </button>
                                @else
                                    <form action="{{ route('suppliers.destroy', $sup) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus supplier {{ $sup->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus Supplier">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-truck fs-1 d-block mb-2 text-secondary"></i>
                            Tidak ada supplier yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-end">
                {{ $suppliers->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
