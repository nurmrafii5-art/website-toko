# TokoKu — Sistem Informasi Penjualan & Kasir Berbasis Web

Project ini dibuat menggunakan PHP native + MySQL + HTML/CSS + JavaScript, dengan UI mengikuti mockup yang dibuat sebelumnya.

## Fitur
- Login admin dan kasir
- Dashboard ringkasan penjualan dan stok menipis
- Kasir dengan pencarian produk, kategori, keranjang, pembayaran, dan kembalian
- CRUD produk
- CRUD kategori
- Riwayat transaksi + filter tanggal
- Laporan penjualan + produk terlaris + grafik sederhana
- Halaman pengaturan
- Layout responsif untuk desktop dan mobile

## Cara menjalankan di XAMPP
1. Salin folder `tokoku-kasir` ke `C:\xampp\htdocs\`.
2. Buka XAMPP, nyalakan **Apache** dan **MySQL**.
3. Buka `http://localhost/phpmyadmin`.
4. Pilih menu **Import**, lalu masukkan file `database.sql` dari project ini.
5. Buka `http://localhost/tokoku-kasir/login.php`.

## Catatan
Konfigurasi database ada di `config/config.php`. Pada instalasi XAMPP standar, user MySQL adalah `root` dengan password kosong. Ubah bagian tersebut jika konfigurasi MySQL kamu berbeda.
