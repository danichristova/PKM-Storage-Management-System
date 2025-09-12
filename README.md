# 📦 PKM Storage Management System

Online Server bisa di akses di https://pkm.page.gd

**PKM Storage Management System**, dirancang untuk mengelola input barang, katalog, peminjaman, pengembalian, serta pengambilan barang dengan pencatatan log yang lengkap.  

---

## ✨ Fitur Utama

- 📝 **Input Barang**  
  Nama penginput, nama barang, jenis, jumlah, lokasi (rak), dan upload foto barang.
- 📑 **Katalog Barang**  
  Cari barang berdasarkan nama + filter berdasarkan nama rak.
- 🔄 **Peminjaman & Pengembalian**  
  Sistem pencatatan pinjam & return stok barang.
- 📦 **Pengambilan Barang (Ambil)**  
  Untuk pengurangan stok permanen.
- 🛡️ **Admin Session**  
  Hanya admin yang bisa melihat log aktivitas dan melakukan manajemen.
- 🗂️ **Log Aktivitas Lengkap**  
  - Input barang  
  - Peminjaman & pengembalian  
  - Pengambilan barang  
- ⚙️ **Manajemen Barang & Rak**  
  Hapus barang, edit barang, dan custom nama rak.

---

## 🗄️ Struktur Database

📌 File: [`init.sql`](./init.sql)

- **`items`** — data barang.  
- **`logs`** — pencatatan semua aktivitas (input, pinjam, kembalikan, ambil).  

---

## ⚡ Cara Pasang (XAMPP)

1. Ekstrak folder ini ke: C:\xampp\htdocs\PKM-Storage-Management
2. Jalankan **Apache** & **MySQL** dari XAMPP Control Panel.
3. Buat database baru di **phpMyAdmin** (`http://localhost/phpmyadmin`) dengan nama sesuai `config.php` (default: `pkm_storage`).
4. Import file **`init.sql`** ke database tersebut.
5. Ubah kredensial database di `config.php` bila perlu (`host`, `user`, `password`).
6. Akses lewat browser:  


---

## 👥 Kontak

Dibuat oleh **Rombel 3A2** TMK ATMI 56  
📅 08 September 2025  

---

🔖 *Sistem ini dikembangkan untuk mendukung manajemen inventaris secara digital, efisien, efektif dan stabil.*

