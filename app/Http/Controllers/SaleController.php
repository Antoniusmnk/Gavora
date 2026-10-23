<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleController extends Controller
{
    /**
     * Tampilkan daftar transaksi penjualan (Stock-Out) dengan filter dan pencarian.
     */
    public function index(Request $request): View
    {
        $query = Sale::with(['user', 'items'])->latest('sold_at');

        // Filter nomor invoice penjualan
        if ($request->filled('search')) {
            $query->where('sale_number', 'like', "%{$request->search}%");
        }

        // Filter status transaksi
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter rentang tanggal transaksi
        if ($request->filled('start_date')) {
            $query->whereDate('sold_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('sold_at', '<=', $request->end_date);
        }

        $sales = $query->paginate(10)->withQueryString();

        return view('sales.index', compact('sales'));
    }

    /**
     * Tampilkan form kasir/penjualan baru.
     */
    public function create(): View
    {
        $products = Product::with('category')->orderBy('name')->get();

        return view('sales.create', compact('products'));
    }

    /**
     * Simpan transaksi penjualan dengan validasi stok ketat dan DB::transaction atomik.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'sold_at' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Minimal satu item produk harus dipilih untuk transaksi penjualan.',
            'items.min' => 'Minimal satu item produk harus dipilih untuk transaksi penjualan.',
            'items.*.product_id.required' => 'Produk pada baris penjualan wajib dipilih.',
            'items.*.product_id.distinct' => 'Produk yang sama tidak boleh dimasukkan berulang pada baris berbeda.',
            'items.*.quantity.required' => 'Kuantitas penjualan wajib diisi.',
            'items.*.quantity.min' => 'Kuantitas penjualan minimal 1 unit.',
        ]);

        $sale = DB::transaction(function () use ($request) {
            // 1. Generate nomor invoice unik otomatis
            $dateStr = now()->format('Ymd');
            $countToday = Sale::whereDate('created_at', today())->count() + 1;
            $saleNumber = 'INV-' . $dateStr . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
            while (Sale::where('sale_number', $saleNumber)->exists()) {
                $countToday++;
                $saleNumber = 'INV-' . $dateStr . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
            }

            // 2. Buat header transaksi penjualan dengan total 0 sementara
            $sale = Sale::create([
                'user_id' => auth()->id(),
                'sale_number' => $saleNumber,
                'total' => 0,
                'status' => 'completed',
                'sold_at' => $request->filled('sold_at') ? $request->sold_at : now(),
            ]);

            $grandTotal = 0;

            // 3. Loop setiap item yang dijual
            foreach ($request->items as $item) {
                // Kunci baris produk dengan lockForUpdate untuk mencegah race condition / overselling
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];

                // VALIDASI ATURAN BISNIS KRITIS: Cek ketersediaan stok
                if ($qty < 1 || $product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok produk {$product->name} (SKU: {$product->sku}) tidak mencukupi. Sisa stok tersedia: {$product->stock} unit, sedangkan permintaan: {$qty} unit.",
                    ]);
                }

                // Ambil harga jual RESMI dari database server, BUKAN dari input browser pengguna!
                $unitPrice = (float) $product->selling_price;
                $subtotal = $unitPrice * $qty;

                // Simpan rincian item ke sale_items
                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                // Kurangi stok produk secara aman (Stock-Out)
                $product->decrement('stock', $qty);

                $grandTotal += $subtotal;
            }

            // 4. Update total resmi transaksi penjualan
            $sale->update(['total' => $grandTotal]);

            return $sale;
        });

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Transaksi Penjualan ' . $sale->sale_number . ' berhasil diselesaikan. Stok inventaris telah diperbarui.');
    }

    /**
     * Tampilkan detail struk/invoice transaksi penjualan.
     */
    public function show(Sale $sale): View
    {
        $sale->load(['user', 'items.product.category']);

        return view('sales.show', compact('sale'));
    }

    /**
     * Batalkan transaksi penjualan (Khusus Admin) dengan pemulihan stok produk otomatis.
     */
    public function cancel(Sale $sale): RedirectResponse
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak: Hanya Admin yang berhak membatalkan transaksi.');
        }

        if ($sale->status !== 'completed') {
            return back()->with('error', 'Transaksi penjualan ini tidak dapat dibatalkan karena berstatus ' . $sale->status . '.');
        }

        DB::transaction(function () use ($sale) {
            // Loop seluruh item dan pulihkan stok barang yang sebelumnya terjual
            foreach ($sale->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);
                $product->increment('stock', $item->quantity);
            }

            $sale->update(['status' => 'cancelled']);
        });

        return back()->with('success', 'Transaksi Penjualan ' . $sale->sale_number . ' telah berhasil dibatalkan. Kuantitas stok produk telah dikembalikan ke inventaris.');
    }
}
