@extends('layouts.app')

@section('title', 'Transaksi Pembelian Baru (Stock-In) - Gavora ERP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Pencatatan Pembelian Barang (Stock-In)</h3>
            <p class="text-muted mb-0">Input faktur pembelian barang dari supplier untuk menambah stok inventaris secara langsung.</p>
        </div>
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
        </a>
    </div>

    <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm" novalidate>
        @csrf

        <!-- Header Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-receipt me-1"></i> Informasi Faktur Pembelian</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="supplier_id" class="form-label fw-semibold">Supplier Rekanan <span class="text-danger">*</span></label>
                        <select name="supplier_id" id="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Supplier Pemasok --</option>
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
                        <label for="purchased_at" class="form-label fw-semibold">Tanggal & Waktu Pembelian</label>
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

        <!-- Items Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-boxes me-1"></i> Rincian Barang yang Dibeli</h6>
                    <small class="text-muted">Kuantitas stok akan bertambah secara atomik di dalam transaksi database.</small>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="addRowBtn">
                    <i class="bi bi-plus-lg me-1"></i> + Tambah Baris Produk
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40%;">Produk <span class="text-danger">*</span></th>
                                <th style="width: 25%;">Harga Beli Satuan (Rp) <span class="text-danger">*</span></th>
                                <th style="width: 15%;">Kuantitas <span class="text-danger">*</span></th>
                                <th style="width: 15%;" class="text-end">Subtotal (Rp)</th>
                                <th style="width: 5%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            <!-- Baris produk di-render di sini oleh JavaScript atau Blade old() -->
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end fw-bold fs-6">Grand Total Pembelian:</th>
                                <th class="text-end fw-bold fs-5 text-primary" id="grandTotalDisplay">Rp 0</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white py-3 d-flex justify-content-end gap-2">
                <a href="{{ route('purchases.index') }}" class="btn btn-light border">Batal</a>
                <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                    <i class="bi bi-save me-1"></i> Simpan Transaksi Pembelian (Stock-In)
                </button>
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

        let productOptions = '<option value="">-- Pilih Produk --</option>';
        availableProducts.forEach(prod => {
            const isSelected = selectedProductId == prod.id ? 'selected' : '';
            productOptions += `<option value="${prod.id}" data-price="${prod.purchase_price}" data-stock="${prod.stock}" ${isSelected}>[${prod.sku}] ${prod.name} (Stok Saat Ini: ${prod.stock})</option>`;
        });

        tr.innerHTML = `
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-select product-select" required onchange="onProductChange(this, ${rowIndex})">
                    ${productOptions}
                </select>
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-text bg-light">Rp</span>
                    <input type="number" step="100" min="0.01" name="items[${rowIndex}][unit_cost]" class="form-control unit-cost-input" value="${initialPrice}" required oninput="calculateSubtotal(${rowIndex})">
                </div>
            </td>
            <td>
                <input type="number" min="1" name="items[${rowIndex}][quantity]" class="form-control text-center quantity-input" value="${initialQty}" required oninput="calculateSubtotal(${rowIndex})">
            </td>
            <td class="text-end fw-semibold subtotal-display" id="subtotal-${rowIndex}">
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
        document.querySelectorAll('#itemsTableBody tr').forEach(row => {
            const cost = parseFloat(row.querySelector('.unit-cost-input').value) || 0;
            const qty = parseInt(row.querySelector('.quantity-input').value) || 0;
            grandTotal += (cost * qty);
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
                alert('Transaksi pembelian minimal harus memiliki 1 item barang.');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('addRowBtn').addEventListener('click', () => createRow());

        // Inisialisasi dari old items jika validasi gagal, atau buat 1 baris kosong
        if (Array.isArray(oldItems) && oldItems.length > 0) {
            oldItems.forEach(item => createRow(item));
        } else {
            createRow();
        }
    });
</script>
@endsection
