# Timeline & Roadmap Pengembangan POS (Laravel Backend)

Dokumen ini berisi urutan langkah (*roadmap*) pengembangan sistem POS dari hulu ke hilir. Pendekatan ini direkomendasikan agar pengembangan berjalan cepat, minim refaktorisasi, dan data referensi selalu tersedia sebelum dibutuhkan oleh modul lain.
Jika Anda menggunakan **Laravel** sebagai backend, sangat disarankan menggunakan perintah `php artisan make:model NamaModel -mcr` untuk membuat Model, Migration, dan Controller sekaligus.

---

### 🏗️ Fase 1: Fondasi Sistem & Autentikasi
Fase ini memastikan fondasi infrastruktur user dan *role-based access* (hak akses) berjalan terlebih dahulu. Hanya terdapat 3 role: Admin Pusat, Cabang, dan Outlet.

*   **1. Manajemen Role & Cabang**
    *   **Fitur Web**: Pengaturan (Setup awal role & data cabang).
    *   **Migration**: `create_roles_table`, `create_branches_table`
    *   **Model**: `Role`, `Branch`
    *   **Controller**: `RoleController`, `BranchController`
*   **2. Manajemen Pengguna & Login**
    *   **Fitur Web**: Menu **Pengguna** dan halaman **Login** aplikasi (menggunakan Middleware berdasarkan role).
    *   **Migration**: `create_users_table` (Modifikasi migration bawaan Laravel, tambahkan `role_id`, `branch_id`, `customer_id`, dll).
    *   **Model**: `User`
    *   **Controller**: `AuthController` (Untuk Login/Token API), `UserController` (Untuk CRUD user oleh Admin).

---

### 📦 Fase 2: Master Data Bisnis (Produk & Entitas Eksternal)
Fase ini melengkapi master data agar sistem mengenali barang yang dijual dan pihak yang terlibat.

*   **1. Katalog Produk Dasar**
    *   **Fitur Web**: Menu **Daftar Produk** (CRUD kategori dan produk inti).
    *   **Migration**: `create_categories_table`, `create_products_table`
    *   **Model**: `Category`, `Product`
    *   **Controller**: `CategoryController`, `ProductController`
*   **2. Atur Harga Fleksibel**
    *   **Fitur Web**: Menu **Atur Harga Cabang** (Harga khusus dan harga grosir).
    *   **Migration**: `create_product_branch_prices_table`, `create_wholesale_prices_table`
    *   **Model**: `ProductBranchPrice`, `WholesalePrice`
    *   **Controller**: `ProductBranchPriceController`, `WholesalePriceController`
*   **3. Relasi Entitas Bisnis**
    *   **Fitur Web**: Menu **Supplier** & **Outlet** (Profil pelanggan).
    *   **Migration**: `create_suppliers_table`, `create_customers_table`
    *   **Model**: `Supplier`, `Customer`
    *   **Controller**: `SupplierController`, `CustomerController`

---

### 🏢 Fase 3: Fondasi Manajemen Stok (Inventaris)
Mengubah produk dari sekadar "katalog" menjadi "barang fisik" di gudang (Pusat & Cabang).

*   **1. Inisialisasi & Pantau Stok**
    *   **Fitur Web**: Menu **Monitoring Stok**.
    *   **Migration**: `create_product_stocks_table`
    *   **Model**: `ProductStock`
    *   **Controller**: `StockController` (Hanya READ/monitoring).
*   **2. Audit Stok Otomatis**
    *   **Fitur Web**: Menu **Riwayat Stok**.
    *   **Migration**: `create_stock_histories_table`
    *   **Model**: `StockHistory`
    *   **Controller**: `StockHistoryController`
*   **3. Koreksi Stok Fisik (Opname)**
    *   **Fitur Web**: Penyesuaian stok manual (Fitur dari Monitoring Stok).
    *   **Migration**: `create_stock_adjustments_table`, `create_stock_adjustment_items_table`
    *   **Model**: `StockAdjustment`, `StockAdjustmentItem`
    *   **Controller**: `StockAdjustmentController`

---

### 🚚 Fase 4: Modul Pembelian (Purchasing dari Supplier)
Aliran barang MASUK dari eksternal (Supplier) ke Gudang Pusat.

*   **1. Membeli Barang**
    *   **Fitur Web**: Menu **Pembelian** (Form PO ke supplier, otomatis tambah stok pusat saat Diterima).
    *   **Migration**: `create_purchases_table`, `create_purchase_items_table`
    *   **Model**: `Purchase`, `PurchaseItem`
    *   **Controller**: `PurchaseController`
*   **2. Tagihan ke Supplier**
    *   **Fitur Web**: Menu **Hutang & Piutang** (Bagian Hutang).
    *   **Migration**: `create_purchase_payments_table`
    *   **Model**: `PurchasePayment`
    *   **Controller**: `PurchasePaymentController`

---

### 🔄 Fase 5: Distribusi Internal (Pusat <-> Cabang)
Pergerakan/Mutasi stok antar gudang perusahaan.

*   **1. PO Cabang & Transfer Stok**
    *   **Fitur Web**: Menu **PO Ke Pusat** (Sisi cabang) & **Daftar PO / Transfer Stok** (Sisi admin pusat untuk di-ACC).
    *   **Migration**: `create_stock_transfers_table`, `create_stock_transfer_items_table`
    *   **Model**: `StockTransfer`, `StockTransferItem`
    *   **Controller**: `StockTransferController`
*   **2. Pengiriman Internal**
    *   **Fitur Web**: Menu **Pengiriman** (Filter Internal/Transfer Stok).
    *   **Migration**: `create_deliveries_table`
    *   **Model**: `Delivery`
    *   **Controller**: `DeliveryController` (Mengelola `stock_transfer_id`).

---

### 🛒 Fase 6: Penjualan & Transaksi Hilir (Point of Sale)
Aliran barang KELUAR dan uang MASUK dari Pelanggan (Outlet).

*   **1. Keranjang Outlet & Mesin Kasir**
    *   **Fitur Web**: Menu **Order** (Keranjang mandiri outlet) dan Menu **Penjualan** (Kasir pusat/cabang yang menyetujui pesanan / buat baru, memotong stok otomatis).
    *   **Migration**: `create_sales_table`, `create_sale_items_table`
    *   **Model**: `Sale`, `SaleItem`
    *   **Controller**: `SaleController` (Bisa ditambah `OutletOrderController` untuk *self-service*).
*   **2. Piutang Outlet (Pembayaran)**
    *   **Fitur Web**: Menu **Hutang & Piutang** (Bagian pembayaran faktur/piutang pelanggan).
    *   **Migration**: `create_payments_table`
    *   **Model**: `Payment`
    *   **Controller**: `PaymentController`
*   **3. Pengiriman Pesanan ke Outlet**
    *   **Fitur Web**: Menu **Pengiriman** (Mengubah status kiriman pelanggan).
    *   *Menggunakan Migration & Model `Delivery` dari Fase 5, namun dikontrol oleh `sale_id` di `DeliveryController`.*

---

### 📊 Fase 7: Pelaporan & Audit (Reporting)
Menarik semua perhitungan kompleks menjadi visualisasi untuk pengambil keputusan.

*   **1. Dashboard & Laporan Kinerja**
    *   **Fitur Web**: Halaman **Dashboard**, **Laporan Cabang**, dan **Laporan Keuangan**.
    *   **Controller**: `DashboardController`, `ReportController` (Menyajikan data aggregasi dari tabel Sales, Purchases, dll).
*   **2. Keamanan & Log Aksi**
    *   **Fitur Web**: Catatan aktivitas sistem untuk *security audit*.
    *   **Migration**: `create_activity_logs_table`
    *   **Model**: `ActivityLog`
    *   **Controller**: `ActivityLogController`

---
**💡 Tips Penting (Backend & Database)**
*   **Gunakan Database Transaction (`DB::transaction`)**: Sangat wajib dipakai pada `SaleController`, `PurchaseController`, dan `StockTransferController` untuk mencegah data tersimpan sebagian jika terjadi *error* (misal: nota tersimpan tapi stok gagal terpotong).
*   **Testing per Fase**: Selesaikan dan *test* API satu fase secara tuntas (contoh: Master Produk bisa di-CRUD) sebelum pindah membuat tabel dan Controller untuk fase selanjutnya (seperti Stok).
