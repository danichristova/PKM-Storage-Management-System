PKM Storage Management System
=============================

Tech stack: PHP 8+, MySQL/MariaDB (XAMPP), Bootstrap 5, minimal CSS.

Features
--------
- Input barang (nama penginput, nama barang, jenis barang, jumlah, lokasi (rak 1/2/3), foto).
- Katalog barang (grid + tabel) dengan pencarian nama dan filter rak.
- Peminjaman barang (pinjam) + pengembalian barang (return).
- Pengambilan barang (ambil) untuk pengurangan stok permanen.
- Log lengkap:
  - Log penginputan
  - Log peminjaman & pengembalian
  - Log pengambilan

Struktur Database (lihat init.sql)
----------------------------------
- `items` — data barang (stok saat ini disimpan pada field `qty`).
- `logs` — pencatatan semua aktivitas (input/borrow/return/take).

Cara Pasang (XAMPP)
-------------------
1. Ekstrak folder ini ke: `C:\xampp\htdocs\PKM-Storage-Management` (Windows) atau ke htdocs di OS Anda.
2. Jalankan Apache dan MySQL dari XAMPP Control Panel.
3. Buat database baru lewat phpMyAdmin (http://localhost/phpmyadmin) dengan nama sesuai `config.php` (default: `pkm_storage`).
4. Import file `init.sql` ke database tersebut.
5. Ubah kredensial database di `config.php` bila perlu (host, user, pass).
6. Buka di browser: `http://localhost/PKM-Storage-Management/index.php`.

Catatan
-------
- Folder `uploads` digunakan untuk menyimpan foto barang. Pastikan Apache/PHP dapat menulis ke folder ini.
- Untuk keamanan produksi, pertimbangkan validasi tambahan, pembatasan ukuran file, autentikasi user, CSRF token, dsb.
- Project ini disiapkan untuk demo/latihan sehingga kodenya dibuat sederhana & mudah dibaca.

Kontak
------
Dibuat oleh ChatGPT pada 2025-09-08.
