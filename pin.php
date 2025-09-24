<?php
require_once __DIR__ . '/db.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = trim($_POST['pin_code'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if ($pin !== '') {
        $stmt = $pdo->prepare("INSERT INTO pins (pin_code, description) VALUES (?, ?)");
        $stmt->execute([$pin, $desc]);
    }
    header('Location: pin.php');
    exit;
}

$pins = $pdo->query("SELECT * FROM pins ORDER BY created_at DESC")->fetchAll();

include __DIR__ . '/partials/header.php';
?>

<div class="container my-4">
  <h1 class="h4 mb-4">Kelola PIN</h1>

  <!-- Form Tambah PIN -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="post" class="row g-3">
        <div class="col-md-4">
          <input type="text" name="pin_code" class="form-control" placeholder="PIN baru" required>
        </div>
        <div class="col-md-5">
          <input type="text" name="description" class="form-control" placeholder="Keterangan (opsional)">
        </div>
        <div class="col-md-3 d-grid">
          <button type="submit" class="btn btn-primary">Tambah PIN</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Tabel Daftar PIN -->
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>PIN</th>
              <th>Keterangan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($pins)): ?>
              <tr>
                <td colspan="4" class="text-center text-muted">Belum ada PIN</td>
              </tr>
            <?php else: ?>
              <?php foreach ($pins as $p): ?>
                <tr>
                  <td><?php echo $p['id']; ?></td>
                  <td><span class="badge bg-secondary"><?php echo h($p['pin_code']); ?></span></td>
                  <td><?php echo h($p['description']); ?></td>
                  <td>
                    <a href="delete_pin.php?id=<?php echo $p['id']; ?>" 
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Hapus PIN ini?')">
                       <i class="bi bi-trash"></i> Hapus
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
