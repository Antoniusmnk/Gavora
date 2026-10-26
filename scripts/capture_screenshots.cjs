const { spawn, execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const OUT_DIR = path.resolve(__dirname, '..', 'docs', 'screenshots');

if (!fs.existsSync(OUT_DIR)) {
  fs.mkdirSync(OUT_DIR, { recursive: true });
}

const targets = [
  { file: '01_login_page.png', url: 'http://127.0.0.1:8000/login', label: 'Halaman Login & Akses Cepat' },
  { file: '02_dashboard_admin.png', url: 'http://127.0.0.1:8000/dev-preview/admin/dashboard', label: 'Dashboard Utama Admin & Metrik Transaksi' },
  { file: '03_product_catalog.png', url: 'http://127.0.0.1:8000/dev-preview/admin/products', label: 'Katalog & Master Data Produk' },
  { file: '04_low_stock_monitoring.png', url: 'http://127.0.0.1:8000/dev-preview/admin/products__low-stock', label: 'Monitoring Stok Menipis (Audit Gudang)' },
  { file: '05_purchases_index.png', url: 'http://127.0.0.1:8000/dev-preview/admin/purchases', label: 'Riwayat Transaksi Pembelian (Stock-In)' },
  { file: '06_purchases_create.png', url: 'http://127.0.0.1:8000/dev-preview/admin/purchases__create', label: 'Form Input Transaksi Pembelian Multi-Item' },
  { file: '07_purchases_detail.png', url: 'http://127.0.0.1:8000/dev-preview/admin/purchases__1', label: 'Faktur & Rincian Transaksi Pembelian' },
  { file: '08_sales_index.png', url: 'http://127.0.0.1:8000/dev-preview/admin/sales', label: 'Riwayat Transaksi Penjualan (Stock-Out)' },
  { file: '09_sales_create.png', url: 'http://127.0.0.1:8000/dev-preview/admin/sales__create', label: 'Form POS Kasir (Multi-Item Penjualan)' },
  { file: '10_sales_detail.png', url: 'http://127.0.0.1:8000/dev-preview/admin/sales__1', label: 'Struk / Faktur Penjualan Kasir' },
  { file: '11_master_categories.png', url: 'http://127.0.0.1:8000/dev-preview/admin/categories', label: 'Master Data Kategori Produk' },
  { file: '12_master_suppliers.png', url: 'http://127.0.0.1:8000/dev-preview/admin/suppliers', label: 'Master Data Mitra Pemasok (Supplier)' },
  { file: '13_users_management.png', url: 'http://127.0.0.1:8000/dev-preview/admin/users', label: 'Manajemen Hak Akses Pengguna & Karyawan' }
];

async function capture() {
  console.log('=== MEMULAI PENGAMBILAN SCREENSHOT PROYEK GAVORA ===');
  console.log(`Menyimpan ke: ${OUT_DIR}`);

  // Mulai PHP Server jika belum aktif
  let phpProcess = null;
  const dbSqlite = path.resolve(__dirname, '..', 'database', 'database.sqlite');
  
  const env = Object.assign({}, process.env, {
    DB_CONNECTION: 'sqlite',
    DB_DATABASE: dbSqlite
  });

  console.log('Memulai server internal PHP...');
  phpProcess = spawn('php', ['-S', '127.0.0.1:8000', '-t', 'public'], {
    cwd: path.resolve(__dirname, '..'),
    env: env,
    stdio: 'ignore'
  });

  // Tunggu 2 detik agar server siap
  await new Promise(r => setTimeout(r, 2000));

  try {
    for (const [idx, item] of targets.entries()) {
      const outPath = path.join(OUT_DIR, item.file);
      console.log(`[${idx + 1}/${targets.length}] Mengambil screenshot: ${item.label}...`);
      
      try {
        execSync(`"${CHROME_PATH}" --headless=new --disable-gpu --no-sandbox --window-size=1400,900 "--screenshot=${outPath}" "${item.url}"`, {
          timeout: 15000,
          stdio: 'ignore'
        });

        if (fs.existsSync(outPath)) {
          const stats = fs.statSync(outPath);
          console.log(`  -> Berhasil! (${item.file}, ${stats.size} bytes)`);
        } else {
          console.warn(`  -> Peringatan: File ${item.file} tidak terbuat.`);
        }
      } catch (err) {
        console.error(`  -> Gagal mengambil ${item.file}: ${err.message}`);
      }
    }

    console.log('\n=== PENGAMBILAN SCREENSHOT SELESAI ===');
  } finally {
    if (phpProcess) {
      phpProcess.kill();
      console.log('Server internal PHP telah dihentikan.');
    }
  }
}

capture().catch(console.error);
