<?php
include "config.php";   // ini wajib di baris paling atas

if (!isset($_SESSION['admin'])) {
    die("Akses ditolak!");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM items WHERE id = $id";

    if ($conn->query($sql)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error SQL: " . $conn->error;
    }
}
