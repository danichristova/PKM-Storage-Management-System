<?php
require_once __DIR__ . '/db.php';

$pdo = db();
$racks = $pdo->query("SELECT * FROM racks ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $created_by = trim($_POST['created_by'] ?? '');
  $name = trim($_POST['name'] ?? '');
  $type = trim($_POST['type'] ?? '');
  $rack = $_POST['rack'] ?? '';
  $qty = (int) ($_POST['qty'] ?? 0);

  $validRackIds = array_column($racks, 'id');
  if ($created_by === '' || $name === '' || $type === '' || !in_array($rack, $validRackIds) || $qty < 0) {

    flash('error', 'Mohon lengkapi semua field dengan benar.');
    header('Location: add_item.php');
    exit;
  }

  // Upload file jika ada
  $photo_path = null;
  if (!empty($_FILES['photo']['name'])) {
    $filename = $_FILES['photo']['name'];
    $tmp = $_FILES['photo']['tmp_name'];
    $size = (int) $_FILES['photo']['size'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if ($size > MAX_UPLOAD_SIZE) {
      flash('error', 'Ukuran file terlalu besar (maks 5MB).');
      header('Location: add_item.php');
      exit;
    }
    if (!in_array($ext, $allowed)) {
      flash('error', 'Ekstensi file tidak diizinkan.');
      header('Location: add_item.php');
      exit;
    }

    $newname = 'item_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = UPLOAD_DIR . $newname;
    if (!move_uploaded_file($tmp, $dest)) {
      flash('error', 'Gagal mengunggah file.');
      header('Location: add_item.php');
      exit;
    }
    $photo_path = UPLOAD_BASE . $newname;
  }

  // Insert item
  $pdo = db();
  $pdo->beginTransaction();
  try {
    $stmt = $pdo->prepare("INSERT INTO items (created_by, name, type, rack, qty, photo_path) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$created_by, $name, $type, $rack, $qty, $photo_path]);

    // Ambil waktu dari PHP
    $now = date('Y-m-d H:i:s');
    // Catat log dengan waktu manual
    $stmt2 = $pdo->prepare("INSERT INTO logs (item_id, action, actor, qty, created_at) VALUES (?,?,?,?,?)");
    $stmt2->execute([$item_id, 'input', $created_by, $qty, $now]);

    $pdo->commit();
    flash('success', 'Barang berhasil ditambahkan.');
    header('Location: index.php');
    exit;
  } catch (Exception $e) {
    $pdo->rollBack();
    flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
    header('Location: add_item.php');
    exit;
  }
}

include __DIR__ . '/partials/header.php';
?>
<h1 class="h4 mb-3">Input Barang</h1>
<form method="post" enctype="multipart/form-data" class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Nama Penginput</label>
    <input type="text" name="created_by" class="form-control" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Nama Barang</label>
    <input type="text" name="name" class="form-control" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Jenis Barang</label>
    <input type="text" name="type" class="form-control" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Jumlah</label>
    <input type="number" name="qty" min="0" class="form-control" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Lokasi (Rak)</label>
    <select name="rack" class="form-select" required>
      <option value="">Pilih rak</option>
      <?php foreach ($racks as $r): ?>
        <option value="<?php echo (int) $r['id']; ?>">
          <?php echo htmlspecialchars($r['name']); ?>
        </option>
      <?php endforeach; ?>
    </select>

  </div>
  <div class="col-12">
    <label class="form-label">Foto Barang (opsional)</label>
    <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png,.gif">
  </div>
  <div class="col-12 d-grid">
    <button class="btn btn-primary">Simpan</button>
  </div>
</form>
<?php include __DIR__ . '/partials/footer.php'; ?>