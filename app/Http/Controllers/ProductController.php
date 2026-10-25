<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Tampilkan daftar produk (Dapat diakses Admin & Staff).
     */
    public function index(Request $request): View
    {
        $query = Product::with('category')->latest();

        // Filter Pencarian (SKU atau Nama Produk)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter Kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter Status Stok (Semua vs Low Stock)
        if ($request->stock_status === 'low_stock') {
            $query->lowStock();
        }

        $products = $query->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Halaman khusus produk dengan stok menipis (Low Stock).
     */
    public function lowStock(): View
    {
        $products = Product::with('category')
            ->lowStock()
            ->orderBy('stock', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('products.low-stock', compact('products'));
    }

    /**
     * Tampilkan detail produk beserta riwayat transaksi singkatnya.
     */
    public function show(Product $product): View
    {
        $product->load([
            'category',
            'purchaseItems.purchase.supplier',
            'saleItems.sale.user',
        ]);

        return view('products.show', compact('product'));
    }

    /**
     * Tampilkan form pembuatan produk (Khusus Admin).
     */
    public function create(): View
    {
        $this->authorizeAdmin();

        $categories = Category::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    /**
     * Simpan produk baru ke database (Khusus Admin).
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
        ], [
            'sku.required' => 'SKU produk wajib diisi.',
            'sku.unique' => 'SKU produk sudah terdaftar. SKU harus unik.',
            'name.required' => 'Nama produk wajib diisi.',
            'category_id.required' => 'Kategori produk wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'purchase_price.required' => 'Harga beli wajib diisi.',
            'purchase_price.min' => 'Harga beli minimal 0.',
            'selling_price.required' => 'Harga jual wajib diisi.',
            'selling_price.min' => 'Harga jual minimal 0.',
            'stock.min' => 'Stok awal tidak boleh bernilai negatif.',
            'minimum_stock.required' => 'Batas minimum stok wajib diisi.',
            'minimum_stock.min' => 'Batas minimum stok minimal 0.',
        ]);

        $validated['stock'] = $validated['stock'] ?? 0;

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Produk "' . $validated['name'] . '" (SKU: ' . $validated['sku'] . ') berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit produk (Khusus Admin).
     */
    public function edit(Product $product): View
    {
        $this->authorizeAdmin();

        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Perbarui data master produk (Khusus Admin).
     * CATATAN: Field 'stock' TIDAK DIPERBOLEHKAN diubah secara manual lewat form edit ini
     * untuk menjaga integritas mutasi stok murni dari transaksi pembelian dan penjualan!
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product->id)],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
        ], [
            'sku.required' => 'SKU produk wajib diisi.',
            'sku.unique' => 'SKU produk sudah digunakan oleh produk lain.',
            'name.required' => 'Nama produk wajib diisi.',
            'category_id.required' => 'Kategori produk wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'purchase_price.required' => 'Harga beli wajib diisi.',
            'selling_price.required' => 'Harga jual wajib diisi.',
            'minimum_stock.required' => 'Batas minimum stok wajib diisi.',
        ]);

        // Perbarui atribut tanpa menyentuh kolom 'stock'
        $product->update([
            'sku' => $validated['sku'],
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'purchase_price' => $validated['purchase_price'],
            'selling_price' => $validated['selling_price'],
            'minimum_stock' => $validated['minimum_stock'],
        ]);

        return redirect()->route('products.index')->with('success', 'Data produk "' . $product->name . '" berhasil diperbarui.');
    }

    /**
     * Hapus master produk dengan proteksi integritas transaksi.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeAdmin();

        // Proteksi: Cek apakah produk pernah digunakan dalam transaksi
        if ($product->hasTransactions()) {
            return back()->with('error', 'Produk tidak dapat dihapus karena sudah tercatat dalam riwayat transaksi pembelian atau penjualan.');
        }

        $name = $product->name;
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk "' . $name . '" berhasil dihapus dari sistem.');
    }

    /**
     * Helper server-side authorization: Pastikan hanya Admin yang dapat mengeksekusi mutasi master produk.
     */
    private function authorizeAdmin(): void
    {
        if (!auth()->user()?->isAdmin()) {
            abort(403, 'Akses ditolak: Hanya Admin yang berhak mengelola master data produk.');
        }
    }
}
