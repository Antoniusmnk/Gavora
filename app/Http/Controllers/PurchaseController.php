<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    /**
     * Tampilkan daftar transaksi pembelian (Stock-In) dengan filter dan pencarian.
     */
    public function index(Request $request): View
    {
        $query = Purchase::with(['supplier', 'user', 'items'])->latest('purchased_at');

        // Filter nomor pembelian (PO)
        if ($request->filled('search')) {
            $query->where('purchase_number', 'like', "%{$request->search}%");
        }

        // Filter supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter rentang tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('purchased_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('purchased_at', '<=', $request->end_date);
        }

        $purchases = $query->paginate(10)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    /**
     * Tampilkan form pembuatan transaksi pembelian baru.
     */
    public function create(): View
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    /**
     * Simpan transaksi pembelian dan mutasi stok secara atomik dengan DB::transaction.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchased_at' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0.01'],
        ], [
            'supplier_id.required' => 'Supplier pemasok wajib dipilih.',
            'supplier_id.exists' => 'Supplier yang dipilih tidak valid.',
            'items.required' => 'Minimal satu item produk harus ditambahkan dalam pembelian.',
            'items.min' => 'Minimal satu item produk harus ditambahkan dalam pembelian.',
            'items.*.product_id.required' => 'Produk pada rincian item wajib dipilih.',
            'items.*.product_id.distinct' => 'Tidak boleh memilih produk yang sama lebih dari satu kali dalam satu transaksi.',
            'items.*.quantity.required' => 'Jumlah kuantitas item wajib diisi.',
            'items.*.quantity.min' => 'Kuantitas item minimal 1 unit.',
            'items.*.unit_cost.required' => 'Harga beli satuan wajib diisi.',
            'items.*.unit_cost.min' => 'Harga beli satuan minimal 0.01.',
        ]);

        $purchase = DB::transaction(function () use ($request) {
            // 1. Generate nomor purchase unik otomatis
            $dateStr = now()->format('Ymd');
            $countToday = Purchase::whereDate('created_at', today())->count() + 1;
            $purchaseNumber = 'PO-' . $dateStr . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
            while (Purchase::where('purchase_number', $purchaseNumber)->exists()) {
                $countToday++;
                $purchaseNumber = 'PO-' . $dateStr . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
            }

            // 2. Buat header Purchase dengan total 0 sementara
            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id(),
                'purchase_number' => $purchaseNumber,
                'total' => 0,
                'status' => 'completed',
                'purchased_at' => $request->filled('purchased_at') ? $request->purchased_at : now(),
            ]);

            $grandTotal = 0;

            // 3. Loop setiap item transaksi
            foreach ($request->items as $item) {
                // Kunci baris produk dengan lockForUpdate untuk mencegah race conditions
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];
                $subtotal = $qty * $unitCost;

                // Simpan baris detail purchase_items
                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                // Tambahkan kuantitas stok produk (Stock-In) & perbarui harga beli standar
                $product->increment('stock', $qty);
                $product->update(['purchase_price' => $unitCost]);

                $grandTotal += $subtotal;
            }

            // 4. Update total resmi transaksi di header
            $purchase->update(['total' => $grandTotal]);

            return $purchase;
        });

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Transaksi Pembelian ' . $purchase->purchase_number . ' berhasil diproses. Stok barang telah ditambahkan!');
    }

    /**
     * Tampilkan detail transaksi pembelian (PO).
     */
    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'user', 'items.product.category']);

        return view('purchases.show', compact('purchase'));
    }

    /**
     * Batalkan transaksi pembelian (Khusus Admin) dengan pengembalian stok atomik.
     */
    public function cancel(Purchase $purchase): RedirectResponse
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak: Hanya Admin yang berhak membatalkan transaksi.');
        }

        if ($purchase->status !== 'completed') {
            return back()->with('error', 'Transaksi ini tidak dapat dibatalkan karena statusnya sudah ' . $purchase->status . '.');
        }

        DB::transaction(function () use ($purchase) {
            // Loop seluruh item dan cek ketersediaan stok sebelum dikurangi kembali
            foreach ($purchase->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);

                if ($product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'error' => "Pembatalan gagal: Stok produk {$product->name} (sisa {$product->stock} unit) tidak mencukupi untuk dikurangi {$item->quantity} unit karena barang tersebut kemungkinan telah terjual dalam transaksi penjualan.",
                    ]);
                }

                // Kurangi stok barang yang sebelumnya dibeli
                $product->decrement('stock', $item->quantity);
            }

            $purchase->update(['status' => 'cancelled']);
        });

        return back()->with('success', 'Transaksi Pembelian ' . $purchase->purchase_number . ' telah berhasil dibatalkan. Kuantitas stok telah dipulihkan secara otomatis.');
    }
}
