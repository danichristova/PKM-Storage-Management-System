<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi database
define('DB_HOST', 'localhost');
define('DB_NAME', 'pkm_storage');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base URL aplikasi
define('BASE_URL', '/PKM-Storage-Management-System/');

// Konfigurasi upload gambar
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_BASE', 'uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
$ALLOWED_EXTS = ['jpg','jpeg','png','gif'];

// Buat koneksi ke database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
