<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
$db = db();

// helper small
function redirect_back($to = 'settings.php')
{
    header("Location: $to");
    exit;
}

// Tambah rak
if (isset($_POST['add_rack'])) {
    $name = trim($_POST['rack_name'] ?? '');
    if ($name !== '') {
        $stmt = $db->prepare("INSERT INTO racks (name) VALUES (?)");
        $stmt->execute([$name]);
    }
    redirect_back();
}

// Update rak
if (isset($_POST['update_rack'])) {
    $oldId = (int) $_POST['old_id'];
    $newId = (int) $_POST['id'];
    $name = trim($_POST['name']);

    $db = db();

    try {
        $db->beginTransaction();

        // Update tabel racks
        $stmt = $db->prepare("UPDATE racks SET id = ?, name = ? WHERE id = ?");
        $stmt->execute([$newId, $name, $oldId]);

        // Update juga semua items yang masih pakai oldId
        $stmt = $db->prepare("UPDATE items SET rack = ? WHERE rack = ?");
        $stmt->execute([$newId, $oldId]);

        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        die("Gagal update rak: " . $e->getMessage());
    }

    header("Location: settings.php");
    exit;
}

// Hapus rak -> set items.rack = NULL dulu
if (isset($_GET['delete_rack'])) {
    $id = (int)$_GET['delete_rack'];
    $db = db();
    $stmt = $db->prepare("DELETE FROM racks WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: settings.php");
    exit;
}

// Hapus item
if (isset($_GET['delete_item'])) {
    $id = (int) $_GET['delete_item'];
    if ($id > 0) {
        $db->prepare("DELETE FROM items WHERE id = ?")->execute([$id]);
    }
    redirect_back();
}

// Update item (dipanggil dari settings_edit_item.php)
if (isset($_POST['update_item'])) {
    $id = (int) ($_POST['item_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $qty = (int) ($_POST['qty'] ?? 0);
    $rack = $_POST['rack'] !== '' ? (int) $_POST['rack'] : null;

    // photo upload
    $photo_path = null;
    if (!empty($_FILES['photo']['name']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $fn = 'item_' . $id . '_' . time() . '.' . $ext;
        $dest = __DIR__ . '/uploads/' . $fn;
        if (!is_dir(__DIR__ . '/uploads'))
            mkdir(__DIR__ . '/uploads', 0755, true);
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            $photo_path = 'uploads/' . $fn;
            // (Opsional) delete previous photo if stored in DB
            $old = $db->prepare("SELECT photo_path FROM items WHERE id = ?")->execute([$id]);
        }
    }

    if ($photo_path) {
        $db->prepare("UPDATE items SET name = ?, qty = ?, rack = ?, photo_path = ? WHERE id = ?")
            ->execute([$name, $qty, $rack, $photo_path, $id]);
    } else {
        $db->prepare("UPDATE items SET name = ?, qty = ?, rack = ? WHERE id = ?")
            ->execute([$name, $qty, $rack, $id]);
    }
    redirect_back();
}

// fallback
redirect_back();
