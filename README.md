# Rumah Pitik

MVP Sistem Informasi E-Commerce CV Rumah Pitik sesuai PRD: katalog, autentikasi role, keranjang, checkout, pesanan, dashboard admin/owner, laporan, stok dasar, dan activity log.

## Menjalankan di XAMPP

1. Jalankan Apache dan MySQL dari XAMPP.
2. Buka `http://localhost/phpmyadmin` lalu import file `schema.sql`.
3. Buka `http://localhost/SI-Ecommerce-Aul/`.
4. Kredensial staf seed:
   - Admin: `admin@rumahpitik.test` / `password`
   - Owner: `owner@rumahpitik.test` / `password`

Konfigurasi koneksi ada di `config.php` dan memakai default XAMPP: host `127.0.0.1`, user `root`, password kosong, database `rumah_pitik`.

## Catatan alur

- Pelanggan dapat mendaftar, melihat produk aktif, menambah keranjang, checkout, dan melihat pesanan.
- Admin dapat mengelola produk, menonaktifkan produk, memproses status pesanan, melihat laporan, dan activity log.
- Owner memiliki dashboard monitoring dan akses laporan tanpa aksi operasional.
- Pembayaran hanya dicatat sebagai metode pembayaran, tanpa payment gateway sesuai batasan PRD.
