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
    }
}
