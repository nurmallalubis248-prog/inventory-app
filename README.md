# Stockify - Inventory Management System

Stockify adalah aplikasi berbasis web untuk manajemen inventaris barang yang dikembangkan menggunakan **PHP (PDO)** dan **MySQL**, dirancang dengan antarmuka bertema **Pink Elegan**. Aplikasi ini mendukung pengelolaan master data, transaksi stok keluar-masuk, laporan valuasi aset, serta manajemen hak akses pengguna (*Role-Based Access Control*).

---

## 🚀 Fitur Utama
1. **Autentikasi & Otorisasi**: Sistem *login* aman dengan manajemen *session* dan pembagian hak akses berdasarkan *role* (**Admin** dan **Staff**).
2. **Manajemen Master Data (CRUD)**:
   - Pengelolaan **Kategori** produk.
   - Pengelolaan data **Supplier**.
   - Pengelolaan data **Produk** (Kode, SKU, Nama, Kategori, Stok, Harga, dan Minimum Stok).
3. **Transaksi Inventaris**:
   - Pencatatan **Barang Masuk** (otomatis menambah stok produk).
   - Pencatatan **Barang Keluar** (otomatis mengurangi stok produk dengan validasi error jika melebihi stok tersedia).
   - **Riwayat Transaksi** untuk memantau seluruh mutasi stok.
4. **Laporan & Dashboard**:
   - Ringkasan statistik jumlah produk, kategori, supplier, dan produk dengan stok menipis.
   - Perhitungan otomatis **Valuasi Aset Inventory** secara *real-time*.
5. **Keamanan Aplikasi**:
   - Menggunakan **Prepared Statements** (PDO) untuk mencegah *SQL Injection*.
   - Menerapkan **Output Encoding** (`htmlspecialchars`) untuk mencegah celah *XSS*.

---

## ✅ Checklist Pengujian & Evaluasi Proyek (Evaluation Checklist)

Berikut adalah daftar pengujian fitur yang telah berhasil dilakukan pada sistem:

- ✔ **Desain Database & Relasi**: Tabel database saling terhubung dengan benar menggunakan relasi *one-to-many* serta penerapan *Foreign Key* dan integritas referensial.
- ✔ **Master Data (CRUD)**: Seluruh modul master data (Kategori, Supplier, Produk, dan Pengguna) dapat melakukan proses *Create*, *Read*, *Update*, dan *Delete* dengan lancar.
- ✔ **Transaksi Inventaris**: 
  - Pencatatan Barang Masuk menambah stok produk secara otomatis.
  - Pencatatan Barang Keluar mengurangi stok produk secara otomatis, lengkap dengan validasi error jika jumlah barang keluar melebihi stok fisik yang tersedia.
- ✔ **Pencarian, Listing, & Agregat**: Fitur *search* berfungsi baik pada tabel-tabel utama, serta *Dashboard* berhasil menampilkan laporan ringkasan statistik (total produk, nilai valuasi aset, stok menipis) menggunakan fungsi agregat SQL (`COUNT`, `SUM`).
- ✔ **Autentikasi & Otorisasi (Role-Based Access Control)**: 
  - Sistem *login* menggunakan manajemen *session* yang aman.
  - Hak akses terbagi dengan jelas di mana Admin dapat mengelola seluruh fitur termasuk manajemen pengguna (`users.php`), sedangkan Staff dibatasi dari menu manajemen pengguna.
- ✔ **Keamanan Aplikasi (Application Security)**: 
  - Seluruh kueri basis data menggunakan *Prepared Statements* (PDO) untuk mencegah *SQL Injection*.
  - Seluruh teks keluaran (*output*) HTML menerapkan fungsi *escaping* (`htmlspecialchars`) guna mencegah celah XSS.

---

## 🛠️ Persyaratan Sistem
* **XAMPP** (PHP versi 8.x dan MySQL/MariaDB)
* Web Browser (Chrome, Edge, atau Firefox)

---

## ⚙️ Cara Menjalankan Proyek (Installation Guide)

Ikuti langkah-langkah berikut untuk menjalankan aplikasi di komputer lokal Anda menggunakan XAMPP:

### 1. Pindahkan Folder Proyek
* Unduh atau salin folder proyek Anda (dinamai `inventory-app`) ke dalam direktori server lokal XAMPP, yaitu di dalam folder **`C:\xampp\htdocs\`**.
* Pastikan strukturnya menjadi seperti ini:
  
```text
  C:\xampp\htdocs\inventory-app\
  ├── config/
  ├── includes/
  ├── public/
  ├── schema.sql
  └── README.md