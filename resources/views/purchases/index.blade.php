@extends('layouts.app')

@section('title', 'Transaksi Pembelian (Stock-In) - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Riwayat Pembelian Barang (Stock-In)</h3>
            <p class="text-muted mb-0">Kelola pengadaan barang masuk dari supplier dan penambahan kuantitas inventaris.</p>
        </div>
        <div>
            <a href="{{ route('purchases.create') }}" class="btn btn-warning text-dark fw-semibold shadow-sm">
                <i class="bi bi-cart-plus-fill me-1"></i> Buat Pembelian Baru (Stock-In)
            </a>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('purchases.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Cari No. PO (misal: PO-2026...)" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="supplier_id" class="form-select">
                        <option value="">-- Semua Supplier --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                                {{ $sup->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">-- Status --</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed (Sukses)</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled (Batal)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_date" class="form-control" title="Tanggal Mulai" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <input type="date" name="end_date" class="form-control" title="Tanggal Akhir" value="{{ request('end_date') }}">
                    <button type="submit" class="btn btn-secondary px-3" title="Terapkan Filter">
                        <i class="bi bi-filter"></i>
                    </button>
                    @if(request()->anyFilled(['search', 'supplier_id', 'status', 'start_date', 'end_date']))
                        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Purchases Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Daftar Transaksi Pembelian</h6>
            <span class="badge bg-light text-dark border">Total: {{ $purchases->total() }} Transaksi</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>No. Pembelian (PO)</th>
                        <th>Supplier / Rekanan</th>
                        <th>Dicatat Oleh</th>
                        <th class="text-center">Total Item</th>
                        <th class="text-end">Total Pembelian</th>
                        <th class="text-center">Status</th>
                        <th>Tanggal Transaksi</th>
                        <th class="text-center" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $index => $po)
                    <tr class="{{ $po->status === 'cancelled' ? 'table-secondary opacity-75' : '' }}">
                        <td>{{ $purchases->firstItem() + $index }}</td>
                        <td>
                            <a href="{{ route('purchases.show', $po) }}" class="fw-bold text-decoration-none">
                                <code>{{ $po->purchase_number }}</code>
                            </a>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $po->supplier->name }}</div>
                            <small class="text-muted">{{ $po->supplier->phone }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $po->user->name }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-secondary border px-2 py-1">
                                {{ $po->items->sum('quantity') }} unit ({{ $po->items->count() }} jenis)
                            </span>
                        </td>
                        <td class="text-end fw-bold text-dark">
                            Rp {{ number_format($po->total, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            @if($po->status === 'completed')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i> Completed
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    <i class="bi bi-x-circle me-1"></i> Cancelled
                                </span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $po->purchased_at->format('d M Y, H:i') }}</td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('purchases.show', $po) }}" class="btn btn-outline-info" title="Lihat Rincian">
                                    <i class="bi bi-eye"></i> Detail
                                </a>

                                @if(auth()->user()->isAdmin() && $po->status === 'completed')
                                    <form action="{{ route('purchases.cancel', $po) }}" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi {{ $po->purchase_number }}? Stok produk yang sudah bertambah akan dikurangi kembali secara otomatis.');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger" title="Batalkan Transaksi">
                                            <i class="bi bi-x-circle"></i> Batal
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bi bi-cart-x fs-1 d-block mb-2 text-secondary"></i>
                            Tidak ada transaksi pembelian yang sesuai dengan kriteria filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchases->hasPages())
        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-end">
                {{ $purchases->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
