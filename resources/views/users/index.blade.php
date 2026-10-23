@extends('layouts.app')

@section('title', 'Manajemen Pengguna - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Manajemen Pengguna (User Accounts)</h4>
            <p class="text-muted mb-0 small">Kelola data akun pegawai dengan hak akses Administrator atau Staf operasional.</p>
        </div>
        <div>
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> + Tambah Pengguna Baru
            </a>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <form action="{{ route('users.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-secondary"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Cari nama atau email pengguna..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="role" class="form-select">
                        <option value="">-- Semua Peran (Role) --</option>
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
    <div class="card">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">Daftar Akun Pengguna</h6>
            <span class="badge bg-light text-secondary border">Total: {{ $users->total() }} Pengguna</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Nama Pengguna</th>
                        <th>Email</th>
                        <th>Peran Akses</th>
                        <th class="text-center">Riwayat Transaksi</th>
                        <th>Terdaftar Pada</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
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
                                    <span class="fw-semibold text-dark">{{ $u->name }}</span>
                                    @if($u->id === auth()->id())
                                        <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 0.65rem;">Akun Anda</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $u->email }}</td>
                        <td>
                            @if($u->isAdmin())
                                <span class="badge badge-role-admin px-2 py-1"><i class="bi bi-shield-lock me-1"></i> Admin</span>
                            @else
                                <span class="badge badge-role-staff px-2 py-1"><i class="bi bi-person me-1"></i> Staff</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-secondary border me-1" title="Pembelian">
                                <i class="bi bi-arrow-down-left text-warning-emphasis me-1"></i>{{ $u->purchases_count }} Beli
                            </span>
                            <span class="badge bg-light text-secondary border" title="Penjualan">
                                <i class="bi bi-arrow-up-right text-success me-1"></i>{{ $u->sales_count }} Jual
                            </span>
                        </td>
                        <td class="small text-muted">{{ $u->created_at->format('d M Y, H:i') }}</td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('users.edit', $u) }}" class="btn btn-outline-primary" title="Edit Pengguna">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                @if($u->id === auth()->id())
                                    <button class="btn btn-outline-secondary disabled" title="Tidak dapat menghapus akun sendiri" disabled>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @elseif($u->purchases_count > 0 || $u->sales_count > 0)
                                    <button class="btn btn-outline-secondary" onclick="alert('Pengguna {{ $u->name }} tidak dapat dihapus karena memiliki riwayat pencatatan transaksi ({{ $u->purchases_count }} pembelian, {{ $u->sales_count }} penjualan).')" title="Terkunci karena memiliki riwayat transaksi">
                                        <i class="bi bi-lock-fill text-muted"></i>
                                    </button>
                                @else
                                    <form action="{{ route('users.destroy', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ $u->name }}? Tindakan ini tidak dapat dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus Pengguna">
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
