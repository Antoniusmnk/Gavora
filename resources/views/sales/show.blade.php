@extends('layouts.app')

@section('title', 'Struk Penjualan: ' . $sale->sale_number . ' - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <h3 class="fw-bold mb-1">Struk / Invoice Penjualan</h3>
            <p class="text-muted mb-0">Rincian bukti pembayaran pelanggan dan pengurangan inventaris stok keluar.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
            </a>
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="bi bi-printer me-1"></i> Cetak Struk
            </button>
            @if(auth()->user()->isAdmin() && $sale->status === 'completed')
                <form action="{{ route('sales.cancel', $sale) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('PENTING: Apakah Anda yakin ingin membatalkan transaksi penjualan {{ $sale->sale_number }}? Kuantitas stok produk yang terjual akan dikembalikan ke gudang secara otomatis.');">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i> Batalkan Penjualan Ini
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Struk Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4 p-md-5">
            <!-- Header Faktur -->
            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-1">
                        <i class="bi bi-box-seam-fill text-success me-2"></i>GAVORA ERP
                    </h4>
                    <p class="text-muted small mb-0">Sistem Kasir & Inventaris Barang Terintegrasi</p>
                </div>
                <div class="text-end">
                    <span class="badge {{ $sale->status === 'completed' ? 'bg-success' : 'bg-danger' }} fs-6 px-3 py-2 mb-2">
                        {{ strtoupper($sale->status) }}
                    </span>
                    <h5 class="fw-bold text-success mb-1"><code>{{ $sale->sale_number }}</code></h5>
                    <div class="small text-muted">Waktu Transaksi: {{ $sale->sold_at->format('d F Y, H:i') }} WIB</div>
                </div>
            </div>

            <!-- Info Kasir & Dokumen -->
            <div class="row g-4 mb-4">
                <div class="col-sm-6">
                    <h6 class="fw-bold text-uppercase text-secondary small mb-2">Petugas Kasir / Pelayan:</h6>
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="fw-bold text-dark fs-6">{{ $sale->user->name }}</div>
                        <div class="text-muted small mt-1"><i class="bi bi-envelope me-1"></i> {{ $sale->user->email }}</div>
                        <div class="mt-1">
                            <span class="badge {{ $sale->user->isAdmin() ? 'badge-role-admin' : 'badge-role-staff' }}" style="font-size: 0.7rem;">
                                Role: {{ ucfirst($sale->user->role) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <h6 class="fw-bold text-uppercase text-secondary small mb-2">Status Pembayaran:</h6>
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="fw-bold text-success fs-6"><i class="bi bi-cash-stack me-1"></i> LUNAS / TUNAI</div>
                        <div class="text-muted small mt-1">Dicatat di sistem pada: {{ $sale->created_at->format('d M Y, H:i:s') }} WIB</div>
                        <div class="text-muted small">Status: <strong>{{ ucfirst($sale->status) }}</strong></div>
                    </div>
                </div>
            </div>

            <!-- Tabel Rincian Item Penjualan -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Kode SKU</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th class="text-end">Harga Satuan Resmi</th>
                            <th class="text-center">Kuantitas</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><code>{{ $item->product->sku }}</code></td>
                            <td>
                                <a href="{{ route('products.show', $item->product) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $item->product->name }}
                                </a>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $item->product->category->name }}</span></td>
                            <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="text-center fw-bold text-danger">-{{ $item->quantity }} unit</td>
                            <td class="text-end fw-semibold text-dark">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="5" class="text-end fs-6">Total Kuantitas Terjual:</th>
                            <th class="text-center fw-bold fs-6 text-danger">-{{ $sale->items->sum('quantity') }} unit</th>
                            <th></th>
                        </tr>
                        <tr>
                            <th colspan="6" class="text-end fs-5">Grand Total Penjualan:</th>
                            <th class="text-end fs-4 fw-bold text-success">Rp {{ number_format($sale->total, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($sale->status === 'cancelled')
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-exclamation-octagon-fill me-2"></i>
                    <strong>Pemberitahuan Pembatalan:</strong> Transaksi penjualan ini telah dibatalkan oleh Administrator. Kuantitas seluruh produk telah dikembalikan (*restored*) ke stok inventaris gudang secara otomatis.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
