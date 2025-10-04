<?php
include "config.php"; 

if (!isset($_SESSION['admin'])) {
    die("Akses ditolak!");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Ambil path foto dulu
    $stmt = $conn->prepare("SELECT photo_path FROM items WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $photo = $row['photo_path'] ?? '';
        if ($photo && file_exists($photo)) {
            unlink($photo); // hapus file fisik
        }
    }
    $stmt->close();

    // Hapus row dari DB
    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error SQL: " . $conn->error;
    }
    $stmt->close();
}
