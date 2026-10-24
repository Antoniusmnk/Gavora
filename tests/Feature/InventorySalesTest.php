<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InventorySalesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected Category $category;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan akun Admin & Staff
        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $this->staff = User::create([
            'name' => 'Staff Test',
            'email' => 'staff@test.com',
            'password' => Hash::make('password123'),
            'role' => 'staff',
        ]);

        // Siapkan Master Kategori & Supplier
        $this->category = Category::create(['name' => 'Elektronik Kantor']);
        $this->supplier = Supplier::create([
            'name' => 'PT Mitra Supplier',
            'phone' => '021-5551234',
            'email' => 'supplier@mitra.com',
        ]);
    }

    /**
     * Skenario A: Autentikasi Pengguna
     * Guest tidak dapat membuka /dashboard (redirect ke login), dan login dengan password salah ditolak.
     */
    public function test_guest_cannot_access_dashboard_and_invalid_login_is_rejected(): void
    {
        // 1. Guest akses dashboard harus diredirect ke login
        $guestResponse = $this->get('/dashboard');
        $guestResponse->assertRedirect(route('login'));

        // 2. Percobaan login dengan password keliru
        $loginResponse = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'passwordsalah123',
        ]);

        $loginResponse->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Skenario B: Validasi Produk
     * SKU duplikat ditolak dan harga negatif ditolak.
     */
    public function test_product_validation_rejects_duplicate_sku_and_negative_prices(): void
    {
        $this->actingAs($this->admin);

        // Buat produk pertama
        Product::create([
            'category_id' => $this->category->id,
            'sku' => 'SKU-TEST-001',
            'name' => 'Printer Canon IP2770',
            'purchase_price' => 500000,
            'selling_price' => 650000,
            'stock' => 10,
            'minimum_stock' => 2,
        ]);

        // 1. Coba buat produk kedua dengan SKU duplikat
        $duplicateSkuResponse = $this->post('/products', [
            'category_id' => $this->category->id,
            'sku' => 'SKU-TEST-001',
            'name' => 'Printer Epson Duplikat',
            'purchase_price' => 700000,
            'selling_price' => 850000,
            'minimum_stock' => 2,
        ]);
        $duplicateSkuResponse->assertSessionHasErrors(['sku']);

        // 2. Coba buat produk dengan harga negatif
        $negativePriceResponse = $this->post('/products', [
            'category_id' => $this->category->id,
            'sku' => 'SKU-TEST-002',
            'name' => 'Tinta Botol',
            'purchase_price' => -10000,
            'selling_price' => -20000,
            'minimum_stock' => 2,
        ]);
        $negativePriceResponse->assertSessionHasErrors(['purchase_price', 'selling_price']);
    }

    /**
     * Skenario C: Proteksi Hapus Master Data Produk
     * Produk yang sudah tercatat dalam riwayat transaksi pembelian atau penjualan ditolak saat dihapus.
     */
    public function test_product_with_transaction_history_cannot_be_deleted(): void
    {
        $this->actingAs($this->admin);

        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'SKU-LOCKED-01',
            'name' => 'Mouse Gaming Logitech',
            'purchase_price' => 200000,
            'selling_price' => 300000,
            'stock' => 10,
            'minimum_stock' => 2,
        ]);

        // Buat riwayat penjualan untuk produk ini
        $sale = Sale::create([
            'user_id' => $this->staff->id,
            'sale_number' => 'INV-TEST-0001',
            'total' => 300000,
            'status' => 'completed',
            'sold_at' => now(),
        ]);
        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 300000,
            'subtotal' => 300000,
        ]);

        // Coba hapus produk yang telah memiliki riwayat
        $deleteResponse = $this->delete("/products/{$product->id}");
        $deleteResponse->assertSessionHas('error');

        // Pastikan produk tetap ada di database
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    /**
     * Skenario D: Transaksi Pembelian Multi-Item (Stock-In)
     * Pembelian multi-item berhasil menambah stok produk dan menghitung total di sisi server.
     */
    public function test_multi_item_purchase_increments_stock_and_calculates_total_server_side(): void
    {
        $this->actingAs($this->staff);

        $p1 = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'SKU-P1',
            'name' => 'Kertas F4 Sinar Dunia',
            'purchase_price' => 40000,
            'selling_price' => 50000,
            'stock' => 10,
            'minimum_stock' => 5,
        ]);

        $p2 = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'SKU-P2',
            'name' => 'Spidol Snowman Hitam',
            'purchase_price' => 8000,
            'selling_price' => 10000,
            'stock' => 20,
            'minimum_stock' => 5,
        ]);

        $response = $this->post('/purchases', [
            'supplier_id' => $this->supplier->id,
            'purchased_at' => now()->toDateTimeString(),
            'items' => [
                ['product_id' => $p1->id, 'quantity' => 5, 'unit_cost' => 40000],   // 5 * 40.000 = 200.000
                ['product_id' => $p2->id, 'quantity' => 10, 'unit_cost' => 8000],   // 10 * 8.000 = 80.000
            ],
        ]);

        $response->assertSessionHas('success');

        // Verifikasi stok bertambah
        $this->assertEquals(15, $p1->fresh()->stock); // 10 + 5
        $this->assertEquals(30, $p2->fresh()->stock); // 20 + 10

        // Verifikasi total transaksi dihitung otomatis 280.000
        $this->assertDatabaseHas('purchases', [
            'supplier_id' => $this->supplier->id,
            'total' => 280000,
            'status' => 'completed',
        ]);
    }

    /**
     * Skenario E: Transaksi Penjualan Multi-Item (Stock-Out)
     * Penjualan berhasil mengurangi stok dan menggunakan harga jual resmi dari database.
     */
    public function test_sale_decrements_stock_and_uses_official_server_database_price(): void
    {
        $this->actingAs($this->staff);

        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'SKU-SALE-01',
            'name' => 'Keyboard Mechanical',
            'purchase_price' => 500000,
            'selling_price' => 800000, // Harga resmi di database
            'stock' => 10,
            'minimum_stock' => 2,
        ]);

        // Kirim request penjualan di mana penyerang mencoba memalsukan harga jual menjadi Rp 100
        $response = $this->post('/sales', [
            'sold_at' => now()->toDateTimeString(),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'unit_price' => 100, // Upaya manipulasi harga input browser
                ],
            ],
        ]);

        $response->assertSessionHas('success');

        // Kuantitas stok berkurang 3 (10 - 3 = 7)
        $this->assertEquals(7, $product->fresh()->stock);

        // Server WAJIB menghitung total berdasarkan harga database (800.000 * 3 = 2.400.000)
        $this->assertDatabaseHas('sales', [
            'total' => 2400000,
            'status' => 'completed',
        ]);
    }

    /**
     * Skenario F: Validasi Ketersediaan Stok (Insufficient Stock)
     * Penjualan dengan kuantitas melebihi stok ditolak dan stok produk tidak berubah.
     */
    public function test_sale_with_insufficient_stock_is_rejected_and_stock_remains_unchanged(): void
    {
        $this->actingAs($this->staff);

        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'SKU-LOW-01',
            'name' => 'Webcam HD 1080p',
            'purchase_price' => 300000,
            'selling_price' => 450000,
            'stock' => 2, // Sisa stok hanya 2
            'minimum_stock' => 1,
        ]);

        // Coba jual 5 unit
        $response = $this->post('/sales', [
            'sold_at' => now()->toDateTimeString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ]);

        $response->assertSessionHasErrors(['items']);

        // Stok produk tidak boleh berubah
        $this->assertEquals(2, $product->fresh()->stock);

        // Tidak ada sale baru yang tercatat
        $this->assertEquals(0, Sale::count());
    }

    /**
     * Skenario G: Atomisitas Transaksi & Rollback (DB::transaction)
     * Jika item pertama cukup namun item kedua kurang, seluruh transaksi di-rollback secara utuh.
     */
    public function test_multi_item_sale_atomicity_rolls_back_when_one_item_lacks_stock(): void
    {
        $this->actingAs($this->staff);

        $itemCukup = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'SKU-OK-01',
            'name' => 'Kabel HDMI 2 Meter',
            'purchase_price' => 30000,
            'selling_price' => 50000,
            'stock' => 10,
            'minimum_stock' => 2,
        ]);

        $itemKurang = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'SKU-FAIL-01',
            'name' => 'SSD NVMe 1TB',
            'purchase_price' => 800000,
            'selling_price' => 1100000,
            'stock' => 1, // Hanya ada 1
            'minimum_stock' => 2,
        ]);

        // Beli itemCukup 2 unit dan itemKurang 5 unit dalam 1 transaksi
        $response = $this->post('/sales', [
            'sold_at' => now()->toDateTimeString(),
            'items' => [
                ['product_id' => $itemCukup->id, 'quantity' => 2],
                ['product_id' => $itemKurang->id, 'quantity' => 5], // Melebihi stok
            ],
        ]);

        $response->assertSessionHasErrors(['items']);

        // UJI KRITIS ATOMISITAS: Stok item pertama TIDAK BOLEH berkurang (tetap 10)
        $this->assertEquals(10, $itemCukup->fresh()->stock);
        $this->assertEquals(1, $itemKurang->fresh()->stock);

        // Tidak ada data header Sale maupun SaleItem yang tersimpan
        $this->assertEquals(0, Sale::count());
        $this->assertEquals(0, SaleItem::count());
    }

    /**
     * Skenario H: Otorisasi Server-Side Berbasis Peran (Role-Based Authorization)
     * Akun Staff mendapat respons HTTP 403 Forbidden saat mengakses route khusus Admin.
     */
    public function test_staff_gets_403_forbidden_on_admin_exclusive_routes(): void
    {
        $this->actingAs($this->staff);

        // 1. Staff dilarang membuat kategori baru
        $categoryResponse = $this->post('/categories', ['name' => 'Kategori Liar']);
        $categoryResponse->assertStatus(403);

        // 2. Staff dilarang menghapus produk
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'SKU-DEL-01',
            'name' => 'Flashdisk 32GB',
            'purchase_price' => 40000,
            'selling_price' => 60000,
            'stock' => 5,
            'minimum_stock' => 2,
        ]);
        $deleteProductResponse = $this->delete("/products/{$product->id}");
        $deleteProductResponse->assertStatus(403);

        // 3. Staff dilarang mengakses halaman manajemen pengguna (users)
        $usersResponse = $this->get('/users');
        $usersResponse->assertStatus(403);

        // 4. Staff dilarang membatalkan transaksi pembelian dan penjualan
        $purchase = Purchase::create([
            'supplier_id' => $this->supplier->id,
            'user_id' => $this->admin->id,
            'purchase_number' => 'PO-TEST-FORBID',
            'total' => 100000,
            'status' => 'completed',
            'purchased_at' => now(),
        ]);
        $cancelPurchaseResponse = $this->post("/purchases/{$purchase->id}/cancel");
        $cancelPurchaseResponse->assertStatus(403);

        $sale = Sale::create([
            'user_id' => $this->admin->id,
            'sale_number' => 'INV-TEST-FORBID',
            'total' => 100000,
            'status' => 'completed',
            'sold_at' => now(),
        ]);
        $cancelSaleResponse = $this->post("/sales/{$sale->id}/cancel");
        $cancelSaleResponse->assertStatus(403);

        // 5. Staff dilarang membuka form create/edit produk maupun store/update produk
        $this->get('/products/create')->assertStatus(403);
        $this->post('/products', [
            'sku' => 'SKU-FORBID',
            'name' => 'Produk Ilegal',
            'category_id' => $this->category->id,
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'minimum_stock' => 1,
        ])->assertStatus(403);
        $this->get("/products/{$product->id}/edit")->assertStatus(403);
        $this->put("/products/{$product->id}", [
            'sku' => 'SKU-DEL-01',
            'name' => 'Ubah Ilegal',
            'category_id' => $this->category->id,
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'minimum_stock' => 1,
        ])->assertStatus(403);

        // 6. Staff dilarang mengakses CRUD Kategori dan Supplier
        $this->get('/categories')->assertStatus(403);
        $this->get('/categories/create')->assertStatus(403);
        $this->get('/suppliers')->assertStatus(403);
        $this->get('/suppliers/create')->assertStatus(403);
    }

    /**
     * Skenario Tambahan: Proteksi Akun Pengguna (Self-Deletion & Transaksi Terkait)
     */
    public function test_user_self_deletion_and_transaction_protection(): void
    {
        $this->actingAs($this->admin);

        // 1. Admin tidak bisa menghapus akunnya sendiri
        $selfDeleteResponse = $this->delete("/users/{$this->admin->id}");
        $selfDeleteResponse->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);

        // 2. Admin tidak bisa mengubah rolenya sendiri menjadi staff
        $roleChangeResponse = $this->put("/users/{$this->admin->id}", [
            'name' => 'Admin Ganti Role',
            'email' => $this->admin->email,
            'role' => 'staff',
        ]);
        $roleChangeResponse->assertSessionHas('error');
        $this->assertEquals('admin', $this->admin->fresh()->role);

        // 3. User yang punya transaksi tidak bisa dihapus
        Sale::create([
            'user_id' => $this->staff->id,
            'sale_number' => 'INV-USER-PROTECT',
            'total' => 50000,
            'status' => 'completed',
            'sold_at' => now(),
        ]);
        $deleteStaffResponse = $this->delete("/users/{$this->staff->id}");
        $deleteStaffResponse->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->staff->id]);
    }

    /**
     * Skenario I: Proteksi Hapus Master Data Kategori dan Supplier
     */
    public function test_category_and_supplier_integrity_protection(): void
    {
        $this->actingAs($this->admin);

        // Kategori yang memiliki produk tidak boleh terhapus
        Product::create([
            'category_id' => $this->category->id,
            'sku' => 'SKU-CAT-LOCK',
            'name' => 'Barang Terkait Kategori',
            'purchase_price' => 10000,
            'selling_price' => 15000,
            'stock' => 5,
            'minimum_stock' => 1,
        ]);

        $deleteCatResponse = $this->delete("/categories/{$this->category->id}");
        $deleteCatResponse->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $this->category->id]);

        // Supplier yang memiliki transaksi pembelian tidak boleh terhapus
        Purchase::create([
            'supplier_id' => $this->supplier->id,
            'user_id' => $this->admin->id,
            'purchase_number' => 'PO-SUPP-LOCK',
            'total' => 50000,
            'status' => 'completed',
            'purchased_at' => now(),
        ]);

        $deleteSupResponse = $this->delete("/suppliers/{$this->supplier->id}");
        $deleteSupResponse->assertSessionHas('error');
        $this->assertDatabaseHas('suppliers', ['id' => $this->supplier->id]);
    }

    /**
     * Skenario J: Pembatalan Transaksi Mengembalikan Stok Secara Konsisten
     */
    public function test_cancellation_reverses_stock_accurately(): void
    {
        $this->actingAs($this->admin);

        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'SKU-CANCEL-TEST',
            'name' => 'Produk Tes Pembatalan',
            'purchase_price' => 50000,
            'selling_price' => 80000,
            'stock' => 10,
            'minimum_stock' => 2,
        ]);

        // 1. Pembelian 5 unit -> stok menjadi 15
        $this->post('/purchases', [
            'supplier_id' => $this->supplier->id,
            'purchased_at' => now()->toDateTimeString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 50000],
            ],
        ]);
        $this->assertEquals(15, $product->fresh()->stock);

        $purchase = Purchase::latest()->first();
        // Batalkan pembelian -> stok berkurang kembali menjadi 10
        $this->post("/purchases/{$purchase->id}/cancel");
        $this->assertEquals(10, $product->fresh()->stock);
        $this->assertEquals('cancelled', $purchase->fresh()->status);

        // 2. Penjualan 4 unit -> stok menjadi 6
        $this->post('/sales', [
            'sold_at' => now()->toDateTimeString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
        ]);
        $this->assertEquals(6, $product->fresh()->stock);

        $sale = Sale::latest()->first();
        // Batalkan penjualan -> stok bertambah kembali menjadi 10
        $this->post("/sales/{$sale->id}/cancel");
        $this->assertEquals(10, $product->fresh()->stock);
        $this->assertEquals('cancelled', $sale->fresh()->status);
    }

    /**
     * Skenario K: Logout Menghapus Sesi dan Mengarahkan ke Login
     */
    public function test_logout_invalidates_session_and_redirects(): void
    {
        $this->actingAs($this->admin);
        $this->assertAuthenticated();

        $response = $this->post('/logout');
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * Skenario L: Metrik Dashboard Menghitung Data Aktual dari Database
     */
    public function test_dashboard_displays_correct_metrics(): void
    {
        $this->actingAs($this->admin);

        // Buat produk dengan stok menipis
        Product::create([
            'category_id' => $this->category->id,
            'sku' => 'SKU-LOW-METRIC',
            'name' => 'Produk Kritis',
            'purchase_price' => 10000,
            'selling_price' => 20000,
            'stock' => 1,
            'minimum_stock' => 5,
        ]);

        $response = $this->get('/dashboard');
        $response->assertOk();
        $response->assertViewHasAll([
            'totalProducts',
            'totalCategories',
            'todaySalesTotal',
            'todaySalesCount',
            'thisMonthSalesTotal',
            'thisMonthPurchasesTotal',
            'lowStockCount',
            'lowStockProducts',
            'recentSales',
        ]);
    }

    /**
     * Skenario M: Pencarian dan Filter pada Halaman Index
     */
    public function test_search_and_filter_functionality(): void
    {
        $this->actingAs($this->admin);

        $pA = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'SKU-ALPHA',
            'name' => 'Meja Kerja Ergonomis',
            'purchase_price' => 500000,
            'selling_price' => 750000,
            'stock' => 20,
            'minimum_stock' => 5,
        ]);

        $pB = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'SKU-BETA',
            'name' => 'Kursi Kantor Putar',
            'purchase_price' => 300000,
            'selling_price' => 450000,
            'stock' => 2,
            'minimum_stock' => 5, // low stock
        ]);

        // Filter search SKU
        $searchResponse = $this->get('/products?search=ALPHA');
        $searchResponse->assertSee('Meja Kerja Ergonomis');
        $searchResponse->assertDontSee('Kursi Kantor Putar');

        // Filter low stock
        $lowStockResponse = $this->get('/products?stock_status=low_stock');
        $lowStockResponse->assertSee('Kursi Kantor Putar');
        $lowStockResponse->assertDontSee('Meja Kerja Ergonomis');
    }
}
