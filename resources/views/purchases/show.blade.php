@extends('layouts.app')

@section('title', 'Detail Pembelian: ' . $purchase->purchase_number . ' - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <h3 class="fw-bold mb-1">Faktur Pembelian (Purchase Order)</h3>
            <p class="text-muted mb-0">Rincian faktur barang masuk dan penambahan stok inventaris.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
            </a>
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="bi bi-printer me-1"></i> Cetak Dokumen
            </button>
            @if(auth()->user()->isAdmin() && $purchase->status === 'completed')
                <form action="{{ route('purchases.cancel', $purchase) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('PENTING: Apakah Anda yakin ingin membatalkan transaksi {{ $purchase->purchase_number }}? Stok produk akan dikurangi kembali secara otomatis.');">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i> Batalkan Transaksi Ini
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Faktur Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4 p-md-5">
            <!-- Header Faktur -->
            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-1">
                        <i class="bi bi-box-seam-fill text-primary me-2"></i>GAVORA ERP
                    </h4>
                    <p class="text-muted small mb-0">Sistem Manajemen Inventaris & Penjualan</p>
                </div>
                <div class="text-end">
                    <span class="badge {{ $purchase->status === 'completed' ? 'bg-success' : 'bg-danger' }} fs-6 px-3 py-2 mb-2">
                        {{ strtoupper($purchase->status) }}
                    </span>
                    <h5 class="fw-bold text-primary mb-1"><code>{{ $purchase->purchase_number }}</code></h5>
                    <div class="small text-muted">Tanggal: {{ $purchase->purchased_at->format('d F Y, H:i') }} WIB</div>
                </div>
            </div>

            <!-- Info Pihak Terkait -->
            <div class="row g-4 mb-4">
                <div class="col-sm-6">
                    <h6 class="fw-bold text-uppercase text-secondary small mb-2">Pemasok / Supplier:</h6>
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="fw-bold text-dark fs-6">{{ $purchase->supplier->name }}</div>
                        <div class="text-muted small mt-1"><i class="bi bi-telephone me-1"></i> {{ $purchase->supplier->phone }}</div>
                        @if($purchase->supplier->email)
                            <div class="text-muted small"><i class="bi bi-envelope me-1"></i> {{ $purchase->supplier->email }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-sm-6">
                    <h6 class="fw-bold text-uppercase text-secondary small mb-2">Dicatat Oleh (User Petugas):</h6>
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="fw-bold text-dark fs-6">{{ $purchase->user->name }}</div>
                        <div class="text-muted small mt-1"><i class="bi bi-envelope me-1"></i> {{ $purchase->user->email }}</div>
                        <div class="mt-1">
                            <span class="badge {{ $purchase->user->isAdmin() ? 'badge-role-admin' : 'badge-role-staff' }}" style="font-size: 0.7rem;">
                                Role: {{ ucfirst($purchase->user->role) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Rincian Barang -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Kode SKU</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th class="text-end">Harga Beli Satuan</th>
                            <th class="text-center">Kuantitas</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><code>{{ $item->product->sku }}</code></td>
                            <td>
                                <a href="{{ route('products.show', $item->product) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $item->product->name }}
                                </a>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $item->product->category->name }}</span></td>
                            <td class="text-end">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                            <td class="text-center fw-bold text-success">+{{ $item->quantity }} unit</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="5" class="text-end fs-6">Total Kuantitas Barang:</th>
                            <th class="text-center fw-bold fs-6 text-success">+{{ $purchase->items->sum('quantity') }} unit</th>
                            <th></th>
                        </tr>
                        <tr>
                            <th colspan="6" class="text-end fs-5">Grand Total Pembelian:</th>
                            <th class="text-end fs-4 fw-bold text-primary">Rp {{ number_format($purchase->total, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($purchase->status === 'cancelled')
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-exclamation-octagon-fill me-2"></i>
                    <strong>Pemberitahuan Pembatalan:</strong> Transaksi pembelian ini telah dibatalkan. Seluruh kuantitas stok barang telah dikurangi kembali secara otomatis dari gudang.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
