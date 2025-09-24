<?php
require_once __DIR__ . '/config.php';

// Ganti username & password sesuai keinginan
$username = 'superadmin';
$password = 'sunade'; // ganti kalau mau

// hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

$mysqli = $conn; // pakai koneksi mysqli dari config.php
$stmt = $mysqli->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, 'superadmin')");
$stmt->bind_param("ss", $username, $hash);

if ($stmt->execute()) {
    echo "Superadmin berhasil dibuat. Username: {$username}<br>";
    echo "Hapus file seed_superadmin.php setelah ini untuk keamanan.";
} else {
    echo "Gagal: " . $mysqli->error;
}
$stmt->close();
?>
