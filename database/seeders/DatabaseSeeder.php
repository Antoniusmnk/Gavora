<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Users (1 Admin, 2 Staff)
        $admin = User::create([
            'name' => 'Admin Gudang & Toko',
            'email' => 'admin@inventory.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $staff1 = User::create([
            'name' => 'Budi Staff Inventory',
            'email' => 'staff1@inventory.test',
            'password' => Hash::make('password123'),
            'role' => 'staff',
        ]);

        $staff2 = User::create([
            'name' => 'Siti Staff Kasir',
            'email' => 'staff2@inventory.test',
            'password' => Hash::make('password123'),
            'role' => 'staff',
        ]);

        // 2. Seed Categories (Minimal 4 Kategori)
        $catHardware = Category::create(['name' => 'Laptop & Komputer']);
        $catAcc = Category::create(['name' => 'Aksesoris Komputer']);
        $catOffice = Category::create(['name' => 'Perlengkapan Kantor & Kertas']);
        $catNetwork = Category::create(['name' => 'Perangkat Jaringan']);

        // 3. Seed Suppliers (Minimal 4 Supplier)
        $sup1 = Supplier::create([
            'name' => 'PT Sinar Komputindo Jaya',
            'phone' => '021-3849102',
            'email' => 'sales@sinarkomputindo.co.id',
        ]);

        $sup2 = Supplier::create([
            'name' => 'CV Mega Office Prima',
            'phone' => '022-7201948',
            'email' => 'order@megaoffice.co.id',
        ]);

        $sup3 = Supplier::create([
            'name' => 'PT Indo Distribusi Periferal',
            'phone' => '031-8492011',
            'email' => 'info@indoperiferal.com',
        ]);

        $sup4 = Supplier::create([
            'name' => 'CV NetSolusi Teknologi',
            'phone' => '024-8419500',
            'email' => 'contact@netsolusi.co.id',
        ]);

        // 4. Seed Products (Minimal 10 Produk, 3 Low-Stock di bawah/sama dengan minimum_stock)
        $p1 = Product::create([
            'category_id' => $catHardware->id,
            'sku' => 'PRD-LPT-001',
            'name' => 'Laptop ASUS Vivobook 14 A1404',
            'purchase_price' => 6500000,
            'selling_price' => 7800000,
            'stock' => 15,
            'minimum_stock' => 5,
        ]);

        // Low-stock item 1: stock 3 <= min_stock 5
        $p2 = Product::create([
            'category_id' => $catHardware->id,
            'sku' => 'PRD-LPT-002',
            'name' => 'Laptop Lenovo ThinkPad E14 Gen 5',
            'purchase_price' => 11200000,
            'selling_price' => 13500000,
            'stock' => 3,
            'minimum_stock' => 5,
        ]);

        $p3 = Product::create([
            'category_id' => $catAcc->id,
            'sku' => 'PRD-ACC-001',
            'name' => 'Mouse Wireless Logitech M220 Silent',
            'purchase_price' => 145000,
            'selling_price' => 195000,
            'stock' => 40,
            'minimum_stock' => 10,
        ]);

        // Low-stock item 2: stock 4 <= min_stock 8
        $p4 = Product::create([
            'category_id' => $catAcc->id,
            'sku' => 'PRD-ACC-002',
            'name' => 'Keyboard Mechanical Keychron K2 V2',
            'purchase_price' => 950000,
            'selling_price' => 1250000,
            'stock' => 4,
            'minimum_stock' => 8,
        ]);

        $p5 = Product::create([
            'category_id' => $catAcc->id,
            'sku' => 'PRD-ACC-003',
            'name' => 'Headset Jabra Evolve 20 Stereo',
            'purchase_price' => 420000,
            'selling_price' => 550000,
            'stock' => 20,
            'minimum_stock' => 5,
        ]);

        $p6 = Product::create([
            'category_id' => $catOffice->id,
            'sku' => 'PRD-ATK-001',
            'name' => 'Kertas A4 PaperOne 80gr (1 Rim)',
            'purchase_price' => 48000,
            'selling_price' => 60000,
            'stock' => 120,
            'minimum_stock' => 25,
        ]);

        // Low-stock item 3: stock 2 <= min_stock 5
        $p7 = Product::create([
            'category_id' => $catOffice->id,
            'sku' => 'PRD-ATK-002',
            'name' => 'Toner Cartridge Canon 325 Hitam',
            'purchase_price' => 310000,
            'selling_price' => 410000,
            'stock' => 2,
            'minimum_stock' => 5,
        ]);

        $p8 = Product::create([
            'category_id' => $catNetwork->id,
            'sku' => 'PRD-NET-001',
            'name' => 'Router Mikrotik RB750Gr3 (hEX)',
            'purchase_price' => 680000,
            'selling_price' => 850000,
            'stock' => 12,
            'minimum_stock' => 5,
        ]);

        $p9 = Product::create([
            'category_id' => $catNetwork->id,
            'sku' => 'PRD-NET-002',
            'name' => 'Access Point Ubiquiti UniFi U6+',
            'purchase_price' => 1650000,
            'selling_price' => 2050000,
            'stock' => 8,
            'minimum_stock' => 3,
        ]);

        $p10 = Product::create([
            'category_id' => $catNetwork->id,
            'sku' => 'PRD-NET-003',
            'name' => 'Kabel UTP Belden Cat6 305 Meter',
            'purchase_price' => 1850000,
            'selling_price' => 2300000,
            'stock' => 6,
            'minimum_stock' => 3,
        ]);

        // 5. Seed Purchases (3 Transaksi Purchase Realistis)
        // Purchase 1: Admin beli Laptop ASUS & Lenovo dari PT Sinar Komputindo Jaya
        $purchase1 = Purchase::create([
            'supplier_id' => $sup1->id,
            'user_id' => $admin->id,
            'purchase_number' => 'PO-202610-0001',
            'total' => 54900000,
            'status' => 'completed',
            'purchased_at' => now()->subDays(12)->setTime(9, 30),
        ]);
        PurchaseItem::create([
            'purchase_id' => $purchase1->id,
            'product_id' => $p1->id,
            'quantity' => 5,
            'unit_cost' => 6500000,
            'subtotal' => 32500000,
        ]);
        PurchaseItem::create([
            'purchase_id' => $purchase1->id,
            'product_id' => $p2->id,
            'quantity' => 2,
            'unit_cost' => 11200000,
            'subtotal' => 22400000,
        ]);

        // Purchase 2: Staff 1 beli Paper & Toner dari CV Mega Office Prima
        $purchase2 = Purchase::create([
            'supplier_id' => $sup2->id,
            'user_id' => $staff1->id,
            'purchase_number' => 'PO-202610-0002',
            'total' => 3950000,
            'status' => 'completed',
            'purchased_at' => now()->subDays(10)->setTime(14, 15),
        ]);
        PurchaseItem::create([
            'purchase_id' => $purchase2->id,
            'product_id' => $p6->id,
            'quantity' => 50,
            'unit_cost' => 48000,
            'subtotal' => 2400000,
        ]);
        PurchaseItem::create([
            'purchase_id' => $purchase2->id,
            'product_id' => $p7->id,
            'quantity' => 5,
            'unit_cost' => 310000,
            'subtotal' => 1550000,
        ]);

        // Purchase 3: Admin beli Router & Access Point dari CV NetSolusi Teknologi
        $purchase3 = Purchase::create([
            'supplier_id' => $sup4->id,
            'user_id' => $admin->id,
            'purchase_number' => 'PO-202610-0003',
            'total' => 13400000,
            'status' => 'completed',
            'purchased_at' => now()->subDays(7)->setTime(11, 00),
        ]);
        PurchaseItem::create([
            'purchase_id' => $purchase3->id,
            'product_id' => $p8->id,
            'quantity' => 10,
            'unit_cost' => 680000,
            'subtotal' => 6800000,
        ]);
        PurchaseItem::create([
            'purchase_id' => $purchase3->id,
            'product_id' => $p9->id,
            'quantity' => 4,
            'unit_cost' => 1650000,
            'subtotal' => 6600000,
        ]);

        // 6. Seed Sales (4 Transaksi Sale Realistis)
        // Sale 1: Staff 2 jual Laptop Asus & Mouse
        $sale1 = Sale::create([
            'user_id' => $staff2->id,
            'sale_number' => 'INV-202610-0001',
            'total' => 7995000,
            'status' => 'completed',
            'sold_at' => now()->subDays(6)->setTime(10, 15),
        ]);
        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $p1->id,
            'quantity' => 1,
            'unit_price' => 7800000,
            'subtotal' => 7800000,
        ]);
        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $p3->id,
            'quantity' => 1,
            'unit_price' => 195000,
            'subtotal' => 195000,
        ]);

        // Sale 2: Staff 1 jual Paper & Toner
        $sale2 = Sale::create([
            'user_id' => $staff1->id,
            'sale_number' => 'INV-202610-0002',
            'total' => 1420000,
            'status' => 'completed',
            'sold_at' => now()->subDays(5)->setTime(13, 45),
        ]);
        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $p6->id,
            'quantity' => 10,
            'unit_price' => 60000,
            'subtotal' => 600000,
        ]);
        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $p7->id,
            'quantity' => 2,
            'unit_price' => 410000,
            'subtotal' => 820000,
        ]);

        // Sale 3: Admin jual Paket Jaringan (Router, AP, Kabel)
        $sale3 = Sale::create([
            'user_id' => $admin->id,
            'sale_number' => 'INV-202610-0003',
            'total' => 6050000,
            'status' => 'completed',
            'sold_at' => now()->subDays(3)->setTime(15, 30),
        ]);
        SaleItem::create([
            'sale_id' => $sale3->id,
            'product_id' => $p8->id,
            'quantity' => 2,
            'unit_price' => 850000,
            'subtotal' => 1700000,
        ]);
        SaleItem::create([
            'sale_id' => $sale3->id,
            'product_id' => $p9->id,
            'quantity' => 1,
            'unit_price' => 2050000,
            'subtotal' => 2050000,
        ]);
        SaleItem::create([
            'sale_id' => $sale3->id,
            'product_id' => $p10->id,
            'quantity' => 1,
            'unit_price' => 2300000,
            'subtotal' => 2300000,
        ]);

        // Sale 4: Staff 2 jual Keyboard & Headset
        $sale4 = Sale::create([
            'user_id' => $staff2->id,
            'sale_number' => 'INV-202610-0004',
            'total' => 2350000,
            'status' => 'completed',
            'sold_at' => now()->subDays(1)->setTime(16, 20),
        ]);
        SaleItem::create([
            'sale_id' => $sale4->id,
            'product_id' => $p4->id,
            'quantity' => 1,
            'unit_price' => 1250000,
            'subtotal' => 1250000,
        ]);
        SaleItem::create([
            'sale_id' => $sale4->id,
            'product_id' => $p5->id,
            'quantity' => 2,
            'unit_price' => 550000,
            'subtotal' => 1100000,
        ]);
    }
}
