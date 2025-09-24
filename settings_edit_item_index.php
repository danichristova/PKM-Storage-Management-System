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
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php'); exit;
}

// ambil item
$stmt = $db->prepare("SELECT * FROM items WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) {
    header('Location: index.php'); exit;
}

// ambil rak utk dropdown
$racks = $db->query("SELECT * FROM racks ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

if (!function_exists('h')) {
    function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
}

include __DIR__ . '/partials/header.php';
?>

<div class="container mt-4">
  <h4>Edit Barang</h4>
  <div class="card mt-3">
    <div class="card-body">
      <form method="post" action="settings_action_index.php" enctype="multipart/form-data">
        <input type="hidden" name="update_item" value="1">
        <input type="hidden" name="item_id" value="<?php echo (int)$item['id']; ?>">

        <div class="mb-3">
          <label class="form-label">Nama</label>
          <input type="text" name="name" class="form-control" required value="<?php echo h($item['name']); ?>">
        </div>

        <div class="mb-3 row">
          <div class="col-md-4">
            <label class="form-label">Jumlah (qty)</label>
            <input type="number" name="qty" class="form-control" min="0" value="<?php echo (int)$item['qty']; ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Rak</label>
            <select name="rack" class="form-select">
              <option value="">-- Tidak ada rak --</option>
              <?php foreach ($racks as $r): ?>
                <option value="<?php echo (int)$r['id']; ?>" <?php echo ((string)$r['id'] === (string)$item['rack']) ? 'selected' : ''; ?>>
                  <?php echo h($r['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Foto (ubah)</label>
            <input type="file" name="photo" class="form-control">
            <?php if (!empty($item['photo_path'])): ?>
              <div class="mt-2">
                <img src="<?php echo h($item['photo_path']); ?>" style="max-width:150px;">
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="d-flex gap-2">
          <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
          <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
