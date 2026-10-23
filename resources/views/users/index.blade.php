@extends('layouts.app')

@section('title', 'Manajemen User - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Manajemen Pengguna (User)</h3>
            <p class="text-muted mb-0">Kelola akun administrator dan staf yang memiliki akses ke sistem.</p>
        </div>
        <div>
            <a href="{{ route('users.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-person-plus-fill me-1"></i> Tambah User Baru
            </a>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('users.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Cari nama atau email user..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="role" class="form-select">
                        <option value="">-- Semua Role --</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin (Administrator)</option>
                        <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Staff (Gudang & Kasir)</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                    @if(request()->filled('search') || request()->filled('role'))
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Daftar Akun Pengguna</h6>
            <span class="badge bg-light text-dark border">Total: {{ $users->total() }} User</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Nama Pengguna</th>
                        <th>Email</th>
                        <th>Role Akses</th>
                        <th class="text-center">Riwayat Trx</th>
                        <th>Terdaftar Pada</th>
                        <th class="text-center" style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $u)
                    <tr>
                        <td>{{ $users->firstItem() + $index }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light border text-primary fw-bold d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="fw-semibold">{{ $u->name }}</span>
                                    @if($u->id === auth()->id())
                                        <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 0.65rem;">Akun Anda</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $u->email }}</td>
                        <td>
                            @if($u->isAdmin())
                                <span class="badge badge-role-admin px-2 py-1"><i class="bi bi-shield-lock me-1"></i> ADMIN</span>
                            @else
                                <span class="badge badge-role-staff px-2 py-1"><i class="bi bi-person-badge me-1"></i> STAFF</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border me-1" title="Pembelian Tercatat">
                                <i class="bi bi-arrow-down-left text-warning me-1"></i>{{ $u->purchases_count }} Beli
                            </span>
                            <span class="badge bg-light text-dark border" title="Penjualan Tercatat">
                                <i class="bi bi-arrow-up-right text-success me-1"></i>{{ $u->sales_count }} Jual
                            </span>
                        </td>
                        <td class="small text-muted">{{ $u->created_at->format('d M Y, H:i') }}</td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('users.edit', $u) }}" class="btn btn-outline-primary" title="Edit User">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                @if($u->id === auth()->id())
                                    <button class="btn btn-outline-secondary disabled" title="Tidak dapat menghapus akun sendiri" disabled>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @elseif($u->purchases_count > 0 || $u->sales_count > 0)
                                    <button class="btn btn-outline-secondary" onclick="alert('User {{ $u->name }} tidak dapat dihapus karena memiliki riwayat transaksi ({{ $u->purchases_count }} pembelian, {{ $u->sales_count }} penjualan). Sesuai aturan integritas data, user ini diproteksi.')" title="Diproteksi karena memiliki transaksi">
                                        <i class="bi bi-lock-fill text-muted"></i>
                                    </button>
                                @else
                                    <form action="{{ route('users.destroy', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user {{ $u->name }}? Tindakan ini tidak dapat dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus User">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                            Tidak ada data pengguna yang sesuai dengan pencarian atau filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-end">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
