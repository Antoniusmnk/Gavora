@extends('layouts.app')

@section('title', 'Kasir Penjualan Baru (POS) - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Kasir & Transaksi Penjualan</h4>
            <p class="text-muted mb-0 small">Catat penjualan barang ke pelanggan. Kuantitas stok produk akan langsung disesuaikan secara otomatis.</p>
        </div>
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat Penjualan
        </a>
    </div>

    <form action="{{ route('sales.store') }}" method="POST" id="saleForm" novalidate>
        @csrf

        <div class="row g-4">
            <!-- Kolom Kiri: Form Input & Keranjang Barang -->
            <div class="col-lg-8">
                <!-- Info Kasir & Tanggal -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-badge me-2 text-primary"></i>Informasi Transaksi</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Petugas Kasir Bertugas</label>
                                <input type="text" class="form-control bg-light" value="{{ auth()->user()->name }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="sold_at" class="form-label small fw-semibold text-secondary">Tanggal & Waktu Transaksi</label>
                                <input type="datetime-local" name="sold_at" id="sold_at" 
                                       class="form-control @error('sold_at') is-invalid @enderror" 
                                       value="{{ old('sold_at', now()->format('Y-m-d\TH:i')) }}">
                                @error('sold_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Keranjang Item Produk -->
                <div class="card">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-cart3 me-2 text-success"></i>Daftar Barang Penjualan</h6>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm fw-semibold" id="addRowBtn">
                            <i class="bi bi-plus-lg me-1"></i> + Tambah Baris Barang
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 45%;">Pilih Barang & Sisa Stok <span class="text-danger">*</span></th>
                                        <th style="width: 20%;" class="text-end">Harga Satuan (Rp)</th>
                                        <th style="width: 15%;" class="text-center">Kuantitas <span class="text-danger">*</span></th>
                                        <th style="width: 15%;" class="text-end">Subtotal (Rp)</th>
                                        <th style="width: 5%;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <!-- Diisi oleh Javascript / old('items') -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Ringkasan Transaksi (Order Summary) -->
            <div class="col-lg-4">
                <div class="card sticky-summary">
                    <div class="card-header py-3">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt me-2 text-primary"></i>Ringkasan Transaksi</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2 small text-secondary">
                            <span>Kasir Bertugas:</span>
                            <span class="fw-semibold text-dark">{{ auth()->user()->name }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 small text-secondary">
                            <span>Total Baris Barang:</span>
                            <span class="fw-semibold text-dark" id="summaryTotalRows">0 Item</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3 small text-secondary">
                            <span>Total Unit Barang:</span>
                            <span class="fw-semibold text-dark" id="summaryTotalQty">0 Unit</span>
                        </div>

                        <hr class="my-3">

                        <div class="mb-4">
                            <span class="text-secondary small fw-medium d-block mb-1">Total Tagihan Pembayaran:</span>
                            <h3 class="fw-bold text-success mb-0" id="grandTotalDisplay">Rp 0</h3>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success py-2 fw-semibold shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Proses Penjualan (Selesai)
                            </button>
                            <a href="{{ route('sales.index') }}" class="btn btn-light border py-2">
                                Batal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Template & Skrip Dinamis Vanilla JS -->
<script>
    const availableProducts = @json($products);
    const oldItems = @json(old('items', []));

    let rowIndex = 0;

    function createRow(itemData = null) {
        const tbody = document.getElementById('itemsTableBody');
        const tr = document.createElement('tr');
        tr.id = `row-${rowIndex}`;

        const selectedProductId = itemData ? itemData.product_id : '';
        const initialQty = itemData ? itemData.quantity : 1;

        let productOptions = '<option value="">-- Pilih Barang --</option>';
        availableProducts.forEach(prod => {
            const isSelected = selectedProductId == prod.id ? 'selected' : '';
            const isOutOfStock = prod.stock <= 0;
            productOptions += `<option value="${prod.id}" data-price="${prod.selling_price}" data-stock="${prod.stock}" ${isSelected} ${isOutOfStock ? 'class="text-danger"' : ''}>
                [${prod.sku}] ${prod.name} (Stok: ${prod.stock} unit) - Rp ${Number(prod.selling_price).toLocaleString('id-ID')}
            </option>`;
        });

        tr.innerHTML = `
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-select product-select" required onchange="onProductChange(this, ${rowIndex})">
                    ${productOptions}
                </select>
                <div class="small mt-1 text-muted" id="stock-hint-${rowIndex}">
                    Pilih produk untuk melihat sisa stok gudang.
                </div>
            </td>
            <td class="text-end fw-semibold text-dark" id="price-display-${rowIndex}">
                Rp 0
            </td>
            <td>
                <input type="number" min="1" name="items[${rowIndex}][quantity]" class="form-control text-center quantity-input" value="${initialQty}" required oninput="calculateSubtotal(${rowIndex})">
            </td>
            <td class="text-end fw-semibold text-dark subtotal-display" id="subtotal-${rowIndex}">
                Rp 0
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(${rowIndex})" title="Hapus Baris">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);

        const select = tr.querySelector('.product-select');
        if (select.value) {
            onProductChange(select, rowIndex);
        }

        rowIndex++;
        calculateGrandTotal();
    }

    function onProductChange(selectElement, index) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const price = parseFloat(selectedOption?.getAttribute('data-price')) || 0;
        const stock = parseInt(selectedOption?.getAttribute('data-stock')) || 0;

        const row = document.getElementById(`row-${index}`);
        const priceDisplay = document.getElementById(`price-display-${index}`);
        const stockHint = document.getElementById(`stock-hint-${index}`);
        const qtyInput = row.querySelector('.quantity-input');

        if (selectElement.value) {
            priceDisplay.innerText = 'Rp ' + price.toLocaleString('id-ID');
            qtyInput.setAttribute('max', stock);

            if (stock <= 0) {
                stockHint.innerHTML = `<span class="badge bg-danger-subtle text-danger">Stok Habis</span>`;
            } else if (stock <= 5) {
                stockHint.innerHTML = `<span class="badge bg-warning-subtle text-warning-emphasis">Sisa Stok Terbatas: ${stock} Unit</span>`;
            } else {
                stockHint.innerHTML = `<span class="badge bg-success-subtle text-success">Stok Tersedia: ${stock} Unit</span>`;
            }
        } else {
            priceDisplay.innerText = 'Rp 0';
            stockHint.innerText = 'Pilih produk untuk melihat sisa stok gudang.';
        }

        calculateSubtotal(index);
    }

    function calculateSubtotal(index) {
        const row = document.getElementById(`row-${index}`);
        if (!row) return;

        const select = row.querySelector('.product-select');
        const selectedOption = select.options[select.selectedIndex];
        const price = parseFloat(selectedOption?.getAttribute('data-price')) || 0;
        const qty = parseInt(row.querySelector('.quantity-input').value) || 0;

        const subtotal = price * qty;
        const display = document.getElementById(`subtotal-${index}`);
        if (display) {
            display.innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
        }

        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let grandTotal = 0;
        let totalQty = 0;
        let rowCount = 0;

        document.querySelectorAll('#itemsTableBody tr').forEach(row => {
            const select = row.querySelector('.product-select');
            const selectedOption = select?.options[select.selectedIndex];
            const price = parseFloat(selectedOption?.getAttribute('data-price')) || 0;
            const qty = parseInt(row.querySelector('.quantity-input')?.value) || 0;
            
            grandTotal += (price * qty);
            totalQty += qty;
            rowCount++;
        });

        document.getElementById('grandTotalDisplay').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
        document.getElementById('summaryTotalRows').innerText = rowCount + ' Item';
        document.getElementById('summaryTotalQty').innerText = totalQty + ' Unit';
    }

    function removeRow(index) {
        const tbody = document.getElementById('itemsTableBody');
        const row = document.getElementById(`row-${index}`);
        if (row) {
            if (tbody.children.length > 1) {
                row.remove();
                calculateGrandTotal();
            } else {
                alert('Transaksi penjualan minimal harus memiliki 1 item barang.');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('addRowBtn').addEventListener('click', () => createRow());

        if (Array.isArray(oldItems) && oldItems.length > 0) {
            oldItems.forEach(item => createRow(item));
        } else {
            createRow();
        }
    });
</script>
@endsection
