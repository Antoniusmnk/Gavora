@extends('layouts.app')

@section('title', 'Transaksi Penjualan Baru (Stock-Out) - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Kasir & Transaksi Penjualan (Stock-Out)</h3>
            <p class="text-muted mb-0">Input penjualan barang ke pelanggan. Kuantitas stok otomatis dikurangi dan divalidasi ketat di server.</p>
        </div>
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
        </a>
    </div>

    <!-- Alert Integritas Server-Side -->
    <div class="alert alert-primary d-flex align-items-center mb-4 border-0 shadow-sm rounded-3">
        <i class="bi bi-shield-check fs-3 me-3 text-primary"></i>
        <div>
            <div class="fw-bold">Proteksi Integritas Stok & Harga Sisi Server (Server-Side Enforcement):</div>
            <div class="small">
                Harga satuan dan ketersediaan stok produk akan dihitung ulang langsung dari database server menggunakan <code>lockForUpdate()</code>. Total penjualan yang disimpan tidak mempercayai input dari browser.
            </div>
        </div>
    </div>

    <form action="{{ route('sales.store') }}" method="POST" id="saleForm" novalidate>
        @csrf

        <!-- Header Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-person-badge me-1"></i> Informasi Kasir & Waktu Transaksi</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Petugas Kasir</label>
                        <input type="text" class="form-control bg-light" value="{{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})" readonly>
                    </div>

                    <div class="col-md-6">
                        <label for="sold_at" class="form-label fw-semibold">Tanggal & Waktu Transaksi</label>
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

        <!-- Items Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-cart3 me-1"></i> Keranjang Item Penjualan</h6>
                    <small class="text-muted">Pilih produk dan tentukan jumlah yang dibeli pelanggan.</small>
                </div>
                <button type="button" class="btn btn-outline-success btn-sm fw-semibold" id="addRowBtn">
                    <i class="bi bi-plus-lg me-1"></i> + Tambah Baris Produk
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 45%;">Pilih Produk & Info Stok <span class="text-danger">*</span></th>
                                <th style="width: 20%;" class="text-end">Harga Satuan Resmi (Rp)</th>
                                <th style="width: 15%;" class="text-center">Kuantitas Jual <span class="text-danger">*</span></th>
                                <th style="width: 15%;" class="text-end">Subtotal Estimasi</th>
                                <th style="width: 5%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            <!-- Diisi oleh Javascript / old('items') -->
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end fw-bold fs-6">Grand Total Pembayaran:</th>
                                <th class="text-end fw-bold fs-5 text-success" id="grandTotalDisplay">Rp 0</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white py-3 d-flex justify-content-end gap-2">
                <a href="{{ route('sales.index') }}" class="btn btn-light border">Batal</a>
                <button type="submit" class="btn btn-success px-4 fw-semibold shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> Selesaikan Transaksi Penjualan (Stock-Out)
                </button>
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

        let productOptions = '<option value="">-- Pilih Produk yang Dijual --</option>';
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
            <td class="text-end fw-semibold text-success subtotal-display" id="subtotal-${rowIndex}">
                Rp 0
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(${rowIndex})" title="Hapus Baris">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);

        // Jika ada produk yang terpilih saat inisialisasi
        const select = tr.querySelector('.product-select');
        if (select.value) {
            onProductChange(select, rowIndex);
        }

        rowIndex++;
    }

    function onProductChange(selectElement, index) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;

        const row = document.getElementById(`row-${index}`);
        const priceDisplay = document.getElementById(`price-display-${index}`);
        const stockHint = document.getElementById(`stock-hint-${index}`);
        const qtyInput = row.querySelector('.quantity-input');

        if (selectElement.value) {
            priceDisplay.innerText = 'Rp ' + price.toLocaleString('id-ID');
            qtyInput.setAttribute('max', stock);

            if (stock <= 0) {
                stockHint.innerHTML = `<span class="badge bg-danger">STOK HABIS (0 unit)</span> Produk tidak dapat dijual.`;
            } else if (stock <= 5) {
                stockHint.innerHTML = `<span class="badge bg-warning text-dark">Stok Kritis: Sisa ${stock} unit</span>`;
            } else {
                stockHint.innerHTML = `<span class="badge bg-success">Stok Tersedia: ${stock} unit</span>`;
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
        document.querySelectorAll('#itemsTableBody tr').forEach(row => {
            const select = row.querySelector('.product-select');
            const selectedOption = select?.options[select.selectedIndex];
            const price = parseFloat(selectedOption?.getAttribute('data-price')) || 0;
            const qty = parseInt(row.querySelector('.quantity-input')?.value) || 0;
            grandTotal += (price * qty);
        });

        document.getElementById('grandTotalDisplay').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
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

        // Inisialisasi baris dari old items (jika redirect kembali karena validasi error)
        if (Array.isArray(oldItems) && oldItems.length > 0) {
            oldItems.forEach(item => createRow(item));
        } else {
            createRow();
        }
    });
</script>
@endsection
