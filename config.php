<?php

$session_lifetime = 1800; // 30 menit

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', $session_lifetime);
    session_set_cookie_params(0); // cookie habis kalau browser ditutup
    session_start();
}

// Auto logout kalau sudah login & idle > 30 menit
if (isset($_SESSION['admin'])) { // cek kalau user sudah login
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_lifetime)) {
        session_unset();     // hapus semua session
        session_destroy();   // destroy session
        header("Location: login.php?timeout=1");
        exit;
    }
    $_SESSION['LAST_ACTIVITY'] = time();
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
