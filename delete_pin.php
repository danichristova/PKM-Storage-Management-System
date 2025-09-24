<?php
require_once __DIR__ . '/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $pdo = db();
    $stmt = $pdo->prepare("DELETE FROM pins WHERE id = ?");
    $stmt->execute([$id]);
}

// balik ke halaman pengaturan PIN
header('Location: pin.php');
exit;
