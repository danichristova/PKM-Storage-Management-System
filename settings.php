<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// pastikan pengguna admin
if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$db = db();

// fallback helper h() jika belum ada
if (!function_exists('h')) {
    function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
}

// ambil data rak (jika tabel racks belum ada, jalankan SQL CREATE di bawah)
$racks = $db->query("SELECT * FROM racks ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// ambil data barang
$items = $db->query("SELECT * FROM items ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/partials/header.php';
?>

<div class="container mt-4">
  <div class="d-flex gap-2 align-items-center mb-3">
    <h1 class="h4 m-0">Settings Admin</h1>
    <span class="text-muted">/ kelola rak & barang</span>
  </div>

  <!-- Form tambah rak -->
  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Tambah Rak</h5>
      <form method="post" action="settings_action.php" class="row g-2">
        <div class="col-md-12">
          <input type="text" name="rack_name" class="form-control" placeholder="Nama Rak (mis: Rak A)" required>
        </div>
        <div class="col-md-12 d-grid">
          <button class="btn btn-primary" name="add_rack" type="submit">Tambah Rak</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Daftar rak -->
  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Daftar Rak</h5>
      <table class="table">
        <thead><tr><th>ID</th><th>Nama</th><th>Edit</th></tr></thead>
        <tbody>
        <?php foreach ($racks as $r): ?>
          <tr>
            <td><?php echo (int)$r['id']; ?></td>
            <td><?php echo h($r['name']); ?></td>
            <td>
              <!-- form inline edit -->
              <form class="d-inline" method="post" action="settings_action.php">
                <input type="hidden" name="rack_id" value="<?php echo (int)$r['id']; ?>">
                <input type="hidden" name="update_rack" value="1">
                <input type="text" name="rack_name" value="<?php echo h($r['name']); ?>" class="form-control form-control-sm d-inline-block" style="width:220px;" required>
                <button class="btn btn-sm btn-success" type="submit">Simpan</button>
              </form>

              <a href="settings_action.php?delete_rack=<?php echo (int)$r['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus rak ini? Barang di rak ini akan dipindahkan ke null.')">Hapus</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Daftar barang -->
  <div class="card mb-5">
    <div class="card-body">
      <h5 class="card-title">Daftar Barang</h5>

      <?php if (empty($items)): ?>
        <div class="alert alert-info">Belum ada barang.</div>
      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($items as $it): ?>
            <div class="col-md-4">
              <div class="card h-100 shadow-sm">
                <?php if (!empty($it['photo_path'])): ?>
                  <img src="<?php echo h($it['photo_path']); ?>" class="img-thumb" alt="">
                <?php else: ?>
                  <svg class="img-thumb bg-light" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder" preserveAspectRatio="xMidYMid slice" focusable="false"><rect width="100%" height="100%" fill="#e9ecef"/></svg>
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                  <div class="d-flex justify-content-between align-items-start mb-1">
                    <h5 class="card-title m-0"><?php echo h($it['name']); ?></h5>
                    <span class="badge text-bg-secondary badge-rack">
                      <?php
                        // tampilkan nama rak jika ada mapping
                        $rak_label = $it['rack'];
                        foreach ($racks as $r) {
                          if ((string)$r['id'] === (string)$it['rack']) {
                              $rak_label = $r['name'];
                              break;
                          }
                        }
                        echo h($rak_label ?: '—');
                      ?>
                    </span>
                  </div>
                  <p class="text-muted mb-2"><?php echo h($it['type'] ?? ''); ?></p>
                  <p class="mb-2">Stok saat ini: <strong><?php echo (int)$it['qty']; ?></strong></p>

                  <div class="mt-auto d-flex gap-2">
                    <a href="settings_edit_item.php?id=<?php echo (int)$it['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="settings_action.php?delete_item=<?php echo (int)$it['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus barang ini?');">Hapus</a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
