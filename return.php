<?php
require_once __DIR__ . '/db.php';

$item_id = (int)($_GET['item_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = (int)($_POST['item_id'] ?? 0);
    $actor = trim($_POST['actor'] ?? '');
    $qty = max(0, (int)($_POST['qty'] ?? 0));

    if ($item_id <= 0 || $actor === '' || $qty <= 0) {
        flash('error', 'Mohon lengkapi data dengan benar.');
        header('Location: return.php?item_id=' . $item_id);
        exit;
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        // Tambah stok
        $stmt2 = $pdo->prepare("UPDATE items SET qty = qty + ? WHERE id = ?");
        $stmt2->execute([$qty, $item_id]);

        // Catat log
        $stmt3 = $pdo->prepare("INSERT INTO logs (item_id, action, actor, qty) VALUES (?,?,?,?)");
        $stmt3->execute([$item_id, 'return', $actor, $qty]);

        $pdo->commit();
        flash('success', 'Pengembalian dicatat dan stok ditambah.');
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        flash('error', 'Gagal mengembalikan: ' . $e->getMessage());
        header('Location: return.php?item_id=' . $item_id);
        exit;
    }
}

$items = db()->query("SELECT id, name, qty FROM items ORDER BY name ASC")->fetchAll();

include __DIR__ . '/partials/header.php';
?>
  <h1 class="h4 mb-3">Kembalikan Barang</h1>
  <form method="post" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Pilih Barang</label>
      <select name="item_id" class="form-select" required>
        <option value="">-- pilih barang --</option>
        <?php foreach ($items as $it): ?>
          <option value="<?php echo (int)$it['id']; ?>" <?php echo $item_id===$it['id']?'selected':''; ?>>
            <?php echo h($it['name']); ?> (stok saat ini: <?php echo (int)$it['qty']; ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Jumlah</label>
      <input type="number" name="qty" min="1" class="form-control" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Nama Pengembali</label>
      <input type="text" name="actor" class="form-control" required>
    </div>
    <div class="col-12 d-grid">
      <button class="btn btn-success">Catat Pengembalian</button>
    </div>
  </form>
<?php include __DIR__ . '/partials/footer.php'; ?>
