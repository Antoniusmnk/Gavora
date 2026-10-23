@extends('layouts.app')

@section('title', 'Faktur Pembelian: ' . $purchase->purchase_number . ' - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <h4 class="fw-bold text-dark mb-1">Faktur Pembelian Barang (Purchase Order)</h4>
            <p class="text-muted mb-0 small">Bukti penerimaan barang inventaris dari supplier dan rincian pembebanan biaya.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
            </a>
            <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-printer me-1"></i> Cetak Faktur
            </button>
            @if(auth()->user()->isAdmin() && $purchase->status === 'completed')
                <form action="{{ route('purchases.cancel', $purchase) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Konfirmasi: Apakah Anda yakin ingin membatalkan transaksi {{ $purchase->purchase_number }}? Kuantitas stok barang yang diterima akan dikurangi kembali.');">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-x-circle me-1"></i> Batalkan Faktur Ini
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Faktur Card -->
    <div class="card">
        <div class="card-body p-4 p-md-5">
            <!-- Header Faktur -->
            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-1">
                        <i class="bi bi-box-seam text-primary me-2"></i>GAVORA ERP
                    </h4>
                    <p class="text-muted small mb-0">Divisi Pengadaan & Logistik Inventaris</p>
                </div>
                <div class="text-end">
                    <span class="badge {{ $purchase->status === 'completed' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} fs-6 px-3 py-1 mb-2">
                        {{ $purchase->status === 'completed' ? 'SELESAI' : 'DIBATALKAN' }}
                    </span>
                    <h5 class="fw-bold text-primary mb-1"><code>{{ $purchase->purchase_number }}</code></h5>
                    <div class="small text-muted">Tanggal: {{ $purchase->purchased_at->format('d F Y, H:i') }} WIB</div>
                </div>
            </div>

            <!-- Info Pihak Terkait -->
            <div class="row g-4 mb-4">
                <div class="col-sm-6">
                    <h6 class="fw-bold text-uppercase text-secondary small mb-2">Pemasok (Supplier):</h6>
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="fw-bold text-dark fs-6">{{ $purchase->supplier->name }}</div>
                        <div class="text-muted small mt-1"><i class="bi bi-telephone me-1 text-secondary"></i> {{ $purchase->supplier->phone }}</div>
                        @if($purchase->supplier->email)
                            <div class="text-muted small"><i class="bi bi-envelope me-1 text-secondary"></i> {{ $purchase->supplier->email }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-sm-6">
                    <h6 class="fw-bold text-uppercase text-secondary small mb-2">Petugas Penerima:</h6>
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="fw-bold text-dark fs-6">{{ $purchase->user->name }}</div>
                        <div class="text-muted small mt-1"><i class="bi bi-envelope me-1 text-secondary"></i> {{ $purchase->user->email }}</div>
                        <div class="mt-1">
                            <span class="badge {{ $purchase->user->isAdmin() ? 'badge-role-admin' : 'badge-role-staff' }}" style="font-size: 0.7rem;">
                                {{ ucfirst($purchase->user->role) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Rincian Barang -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Kode SKU</th>
                            <th>Nama Barang</th>
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
                            <td><span class="badge bg-light text-secondary border">{{ $item->product->category->name }}</span></td>
                            <td class="text-end text-muted">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                            <td class="text-center fw-bold text-success">+{{ $item->quantity }} unit</td>
                            <td class="text-end fw-semibold text-dark">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end small">Total Kuantitas Barang:</th>
                            <th class="text-center fw-bold fs-6 text-success">+{{ $purchase->items->sum('quantity') }} unit</th>
                            <th></th>
                        </tr>
                        <tr>
                            <th colspan="6" class="text-end fs-6">Grand Total Pembelian:</th>
                            <th class="text-end fs-5 fw-bold text-primary">Rp {{ number_format($purchase->total, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($purchase->status === 'cancelled')
                <div class="alert alert-danger mb-0 border-0">
                    <i class="bi bi-exclamation-octagon-fill me-2"></i>
                    <strong>Pemberitahuan:</strong> Faktur pembelian ini telah dibatalkan. Kuantitas stok barang yang sebelumnya diterima telah disesuaikan kembali di gudang.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
