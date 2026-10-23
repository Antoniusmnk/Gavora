@extends('layouts.app')

@section('title', 'Transaksi Penjualan (POS) - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Riwayat Transaksi Penjualan (Kasir)</h4>
            <p class="text-muted mb-0 small">Daftar transaksi penjualan ke pelanggan dan histori pengeluaran barang dari inventaris.</p>
        </div>
        <div>
            <a href="{{ route('sales.create') }}" class="btn btn-success btn-sm shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> + Penjualan Baru (Kasir)
            </a>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <form action="{{ route('sales.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-secondary"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Cari No. Faktur (misal: INV-2026...)" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai (Completed)</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan (Cancelled)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_date" class="form-control" title="Tanggal Mulai" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <input type="date" name="end_date" class="form-control" title="Tanggal Akhir" value="{{ request('end_date') }}">
                    <button type="submit" class="btn btn-secondary px-3" title="Terapkan Filter">
                        <i class="bi bi-filter"></i>
                    </button>
                    @if(request()->anyFilled(['search', 'status', 'start_date', 'end_date']))
                        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Sales Table Card -->
    <div class="card">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">Daftar Transaksi Penjualan</h6>
            <span class="badge bg-light text-secondary border">Total: {{ $sales->total() }} Transaksi</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>No. Faktur</th>
                        <th>Kasir Bertugas</th>
                        <th class="text-center">Total Kuantitas</th>
                        <th class="text-end">Total Pembayaran</th>
                        <th class="text-center">Status</th>
                        <th>Waktu Transaksi</th>
                        <th class="text-center" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $index => $sale)
                    <tr class="{{ $sale->status === 'cancelled' ? 'table-secondary opacity-75' : '' }}">
                        <td>{{ $sales->firstItem() + $index }}</td>
                        <td>
                            <a href="{{ route('sales.show', $sale) }}" class="fw-bold text-decoration-none">
                                <code>{{ $sale->sale_number }}</code>
                            </a>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $sale->user->name }}</div>
                            <small class="text-muted"><i class="bi bi-person me-1"></i>{{ ucfirst($sale->user->role) }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-secondary border px-2 py-1">
                                {{ $sale->items->sum('quantity') }} Unit
                            </span>
                        </td>
                        <td class="text-end fw-bold text-dark fs-6">
                            Rp {{ number_format($sale->total, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            @if($sale->status === 'completed')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    Selesai
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    Dibatalkan
                                </span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $sale->sold_at->format('d M Y, H:i') }}</td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline-secondary" title="Lihat Struk/Invoice">
                                    <i class="bi bi-eye"></i> Detail
                                </a>

                                @if(auth()->user()->isAdmin() && $sale->status === 'completed')
                                    <form action="{{ route('sales.cancel', $sale) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Konfirmasi: Apakah Anda yakin ingin membatalkan transaksi {{ $sale->sale_number }}? Kuantitas stok barang yang terjual akan dikembalikan ke gudang.');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger" title="Batalkan Penjualan">
                                            <i class="bi bi-x-circle"></i> Batal
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-bag-x fs-1 d-block mb-2 text-secondary"></i>
                            Tidak ada transaksi penjualan yang sesuai dengan kriteria filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-end">
                {{ $sales->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
