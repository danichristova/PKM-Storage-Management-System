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
function redirect_back($to = 'index.php')
{
    header("Location: $to");
    exit;
}


// Hapus item
if (isset($_GET['delete_item'])) {
    $id = (int) $_GET['delete_item'];
    if ($id > 0) {
        // ambil nama item dulu
        $stmt = $db->prepare("SELECT name FROM items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        $itemName = $item ? $item['name'] : '(tidak diketahui)';

        // catat log admin
        $stmt = $db->prepare("INSERT INTO admin_logs (admin_username, action, details) VALUES (?, ?, ?)");
        $stmt->execute([
            $_SESSION['admin_user'],
            "Hapus Item",
            "Hapus Item ID=$id, Nama=$itemName"
        ]);

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
    // catat log admin
        $stmt = $db->prepare("INSERT INTO admin_logs (admin_username, action, details) VALUES (?, ?, ?)");
        $stmt->execute([
            $_SESSION['admin_user'],
            "Edit Item",
            "ID=$id, Nama=$name, Lokasi=$rack, Qty=$qty"
        ]);
    redirect_back();
}

// fallback
redirect_back();
