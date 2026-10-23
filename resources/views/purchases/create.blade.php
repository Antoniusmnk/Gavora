@extends('layouts.app')

@section('title', 'Pencatatan Pembelian Baru (Stock-In) - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Pencatatan Faktur Pembelian Barang</h4>
            <p class="text-muted mb-0 small">Input faktur penerimaan barang dari supplier untuk menambah kuantitas stok di gudang.</p>
        </div>
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat Pembelian
        </a>
    </div>

    <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm" novalidate>
        @csrf

        <div class="row g-4">
            <!-- Kolom Kiri: Form Input & Daftar Barang Masuk -->
            <div class="col-lg-8">
                <!-- Info Supplier & Tanggal -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-truck me-2 text-primary"></i>Informasi Pemasok & Faktur</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="supplier_id" class="form-label small fw-semibold text-secondary">Pemasok (Supplier) <span class="text-danger">*</span></label>
                                <select name="supplier_id" id="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Rekanan Pemasok --</option>
                                    @foreach($suppliers as $sup)
                                        <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                                            {{ $sup->name }} (Telp: {{ $sup->phone }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="purchased_at" class="form-label small fw-semibold text-secondary">Tanggal Penerimaan Barang</label>
                                <input type="datetime-local" name="purchased_at" id="purchased_at" 
                                       class="form-control @error('purchased_at') is-invalid @enderror" 
                                       value="{{ old('purchased_at', now()->format('Y-m-d\TH:i')) }}">
                                @error('purchased_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Item Barang Masuk -->
                <div class="card">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-box-arrow-in-down me-2 text-primary"></i>Rincian Barang yang Diterima</h6>
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
                                        <th style="width: 42%;">Produk <span class="text-danger">*</span></th>
                                        <th style="width: 25%;">Harga Beli Satuan (Rp) <span class="text-danger">*</span></th>
                                        <th style="width: 13%;">Kuantitas <span class="text-danger">*</span></th>
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

            <!-- Kolom Kanan: Ringkasan Pembelian (Summary) -->
            <div class="col-lg-4">
                <div class="card sticky-summary">
                    <div class="card-header py-3">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt me-2 text-primary"></i>Ringkasan Faktur Pembelian</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2 small text-secondary">
                            <span>Petugas Penerima:</span>
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
                            <span class="text-secondary small fw-medium d-block mb-1">Total Nilai Faktur:</span>
                            <h3 class="fw-bold text-primary mb-0" id="grandTotalDisplay">Rp 0</h3>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2 fw-semibold shadow-sm">
                                <i class="bi bi-save me-1"></i> Simpan Faktur Pembelian
                            </button>
                            <a href="{{ route('purchases.index') }}" class="btn btn-light border py-2">
                                Batal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Template Produk untuk JavaScript -->
<script>
    const availableProducts = @json($products);
    const oldItems = @json(old('items', []));

    let rowIndex = 0;

    function createRow(itemData = null) {
        const tbody = document.getElementById('itemsTableBody');
        const tr = document.createElement('tr');
        tr.id = `row-${rowIndex}`;

        const selectedProductId = itemData ? itemData.product_id : '';
        const initialPrice = itemData ? itemData.unit_cost : 0;
        const initialQty = itemData ? itemData.quantity : 1;

        let productOptions = '<option value="">-- Pilih Barang --</option>';
        availableProducts.forEach(prod => {
            const isSelected = selectedProductId == prod.id ? 'selected' : '';
            productOptions += `<option value="${prod.id}" data-price="${prod.purchase_price}" ${isSelected}>[${prod.sku}] ${prod.name} (Stok Saat Ini: ${prod.stock} unit)</option>`;
        });

        tr.innerHTML = `
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-select product-select" required onchange="onProductChange(this, ${rowIndex})">
                    ${productOptions}
                </select>
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-text bg-light text-secondary">Rp</span>
                    <input type="number" step="100" min="0.01" name="items[${rowIndex}][unit_cost]" class="form-control unit-cost-input" value="${initialPrice}" required oninput="calculateSubtotal(${rowIndex})">
                </div>
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
        calculateSubtotal(rowIndex);
        rowIndex++;
        calculateGrandTotal();
    }

    function onProductChange(selectElement, index) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const defaultPrice = selectedOption.getAttribute('data-price') || 0;
        const row = document.getElementById(`row-${index}`);
        const costInput = row.querySelector('.unit-cost-input');

        if (!costInput.value || costInput.value == 0) {
            costInput.value = defaultPrice;
        }
        calculateSubtotal(index);
    }

    function calculateSubtotal(index) {
        const row = document.getElementById(`row-${index}`);
        if (!row) return;

        const cost = parseFloat(row.querySelector('.unit-cost-input').value) || 0;
        const qty = parseInt(row.querySelector('.quantity-input').value) || 0;
        const subtotal = cost * qty;

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
            const cost = parseFloat(row.querySelector('.unit-cost-input').value) || 0;
            const qty = parseInt(row.querySelector('.quantity-input').value) || 0;
            grandTotal += (cost * qty);
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
                alert('Transaksi pembelian minimal harus memiliki 1 item barang.');
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
