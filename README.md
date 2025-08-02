# Proyek E-Commerce PHP Bootstrap

Proyek ini adalah aplikasi web e-commerce sederhana yang dibangun menggunakan PHP, Bootstrap, dan MySQL.

## Fitur

### Halaman Admin

- Manajemen Kasir (POS)
- Manajemen Pesanan Online
- Manajemen Produk (CRUD)
- Laporan Penjualan (POS & Online)

### Halaman User

- Landing Page & Daftar Produk
- Keranjang Belanja
- Proses Checkout & Pemesanan
- Upload Bukti Pembayaran Manual

## Prasyarat

- Web Server (misalnya XAMPP, WAMP, MAMP)
- PHP 7.4 atau lebih tinggi
- MySQL / MariaDB

## Instalasi

1.  **Clone repository atau unduh file ZIP.**

2.  **Buat Database:**

    - Buka phpMyAdmin atau klien database Anda.
    - Buat database baru dengan nama `ecommerce_db`.
    - Impor file `database.sql` yang ada di root proyek ke dalam database yang baru Anda buat.

3.  **Konfigurasi Koneksi:**

    - Buka file `config/config.php`.
    - Sesuaikan pengaturan koneksi database (`DB_SERVER`, `DB_USERNAME`, `DB_PASSWORD`, `DB_NAME`) jika diperlukan.
    - Sesuaikan `BASE_URL` agar sesuai dengan URL proyek Anda di localhost (contoh: `http://localhost/maulida/`).

4.  **Jalankan Aplikasi:**
    - Tempatkan folder proyek di dalam direktori `htdocs` (untuk XAMPP) atau `www` (untuk WAMP) pada web server Anda.
    - Buka browser dan akses `BASE_URL` yang telah Anda konfigurasikan (misalnya `http://localhost/maulida/`).

## Struktur Folder

```
/maulida
|-- /admin          # File-file untuk halaman admin
|-- /auth           # File untuk otentikasi (login, register, logout)
|-- /config         # File konfigurasi (database, dll)
|-- /public         # Aset publik (CSS, JS, gambar)
|-- /templates      # File template (header, footer)
|-- /uploads        # Folder untuk menyimpan file upload (bukti bayar, gambar produk)
|-- .htaccess       # Konfigurasi URL (jika menggunakan URL rewriting)
|-- index.php       # Halaman utama / landing page
|-- database.sql    # Skema database
|-- README.md       # File ini
```
