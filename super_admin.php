<?php
require_once __DIR__ . '/db.php';
$pdo = db();


if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'superadmin') {
  header('Location: login.php');
  exit;
}

// Tambah admin baru
if (isset($_POST['new_admin'])) {
  $newUser = trim($_POST['username']);
  $newPass = trim($_POST['password']);

  if ($newUser && $newPass) {
    // Cek apakah username sudah ada
    $check = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
    $check->execute([$newUser]);
    $exists = $check->fetchColumn();

    if ($exists > 0) {
      flash('error', 'Username sudah digunakan, silakan pilih yang lain.');
      header("Location: super_admin.php");
      exit;
    }

    // Kalau belum ada, baru insert
    $hash = password_hash($newPass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, 'admin')");
    $stmt->execute([$newUser, $hash]);

    flash('success', 'Admin baru berhasil ditambahkan.');
  }

  header("Location: super_admin.php");
  exit;
} // Proses update email
elseif (isset($_POST['update_email'])) {
  $newEmail = trim($_POST['notification_email'] ?? '');
  if (filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
    $stmt = $pdo->prepare("
            INSERT INTO settings (name, value) 
            VALUES ('notification_email', ?)
            ON DUPLICATE KEY UPDATE value = VALUES(value)
        ");
    $stmt->execute([$newEmail]);

    flash('success', 'Email notifikasi berhasil diperbarui!');
  } else {
    flash('error', 'Format email tidak valid!');
  }
  header("Location: super_admin.php");
  exit;
}
// Tambah PIN
elseif (isset($_POST['pin_code'])) {
  $pin = trim($_POST['pin_code']);
  $desc = trim($_POST['description'] ?? '');
  if ($pin !== '') {
    $stmt = $pdo->prepare("INSERT INTO pins (pin_code, description) VALUES (?, ?)");
    $stmt->execute([$pin, $desc]);
  }
  header('Location: super_admin.php');
  exit;
}

// Hapus admin
if (isset($_GET['delete_admin'])) {
  $id = (int) $_GET['delete_admin'];

  // Ambil username
  $stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
  $stmt->execute([$id]);
  $row = $stmt->fetch();

  if ($row && $row['username'] !== 'superadmin') {
    $pdo->prepare("DELETE FROM admins WHERE id = ? AND role = 'admin'")->execute([$id]);
  }

  header("Location: super_admin.php");
  exit;
}


// Promote admin jadi superadmin
if (isset($_GET['promote_admin'])) {
  $id = (int) $_GET['promote_admin'];

  $stmt = $pdo->prepare("SELECT username, role FROM admins WHERE id = ?");
  $stmt->execute([$id]);
  $row = $stmt->fetch();

  if ($row && $row['username'] !== 'superadmin' && $row['role'] === 'admin') {
    $stmt = $pdo->prepare("UPDATE admins SET role = 'superadmin' WHERE id = ?");
    $stmt->execute([$id]);
  }

  header("Location: super_admin.php");
  exit;
}


// Demote superadmin jadi admin
if (isset($_GET['demote_admin'])) {
  $id = (int) $_GET['demote_admin'];

  // Ambil username dan role dulu
  $stmt = $pdo->prepare("SELECT username, role FROM admins WHERE id = ?");
  $stmt->execute([$id]);
  $row = $stmt->fetch();

  if ($row) {
    // Cegah demote kalau username = superadmin
    if ($row['username'] !== 'superadmin' && $row['role'] === 'superadmin') {
      $stmt = $pdo->prepare("UPDATE admins SET role = 'admin' WHERE id = ?");
      $stmt->execute([$id]);
    }
  }

  header("Location: super_admin.php");
  exit;
}




// Ambil email sekarang
$stmt = $pdo->prepare("SELECT value FROM settings WHERE name='notification_email' LIMIT 1");
$stmt->execute();
$currentEmail = $stmt->fetchColumn() ?: '';



$admins = $pdo->query("SELECT * FROM admins ORDER BY created_at DESC")->fetchAll();


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
          <input type="text" name="description" class="form-control" placeholder="Pemilik PIN" required>
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
              <th>Pemilik</th>
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
                    <a href="delete_pin.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger"
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


<div class="container my-4">
  <h1 class="h4 mb-4">Kelola Admin</h1>

  <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show" role="alert">
      <?= $_SESSION['flash']['message'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
  <?php endif; ?>

  <!-- Form Tambah Admin -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="post" class="row g-3">
        <input type="hidden" name="new_admin" value="1">
        <div class="col-md-4">
          <input type="text" name="username" class="form-control" placeholder="Username baru" required>
        </div>
        <div class="col-md-4">
          <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <div class="col-md-4 d-grid">
          <button type="submit" class="btn btn-success">Tambah Admin</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Tabel Daftar Admin -->
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Role</th>
              <th>Dibuat</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($admins)): ?>
              <tr>
                <td colspan="5" class="text-center text-muted">Belum ada admin</td>
              </tr>
            <?php else: ?>
              <?php foreach ($admins as $a): ?>
                <tr>
                  <td><?= $a['id'] ?></td>
                  <td><?= h($a['username']) ?></td>
                  <td><span class="badge bg-primary"><?= $a['role'] ?></span></td>
                  <td><?= $a['created_at'] ?></td>
                  <td>
                    <?php if ($a['username'] !== 'superadmin'): ?>
                      <?php if ($a['role'] === 'admin'): ?>
                        <a href="super_admin.php?promote_admin=<?= $a['id'] ?>" class="btn btn-sm btn-warning"
                          onclick="return confirm('Ubah admin ini menjadi superadmin?')">
                          <i class="bi bi-arrow-up"></i> Promote
                        </a>
                      <?php elseif ($a['role'] === 'superadmin'): ?>
                        <a href="super_admin.php?demote_admin=<?= $a['id'] ?>" class="btn btn-sm btn-secondary"
                          onclick="return confirm('Turunkan superadmin ini menjadi admin biasa?')">
                          <i class="bi bi-arrow-down"></i> Demote
                        </a>
                      <?php endif; ?>

                      <a href="super_admin.php?delete_admin=<?= $a['id'] ?>" class="btn btn-sm btn-danger"
                        onclick="return confirm('Hapus admin ini?')">
                        <i class="bi bi-trash"></i> Hapus
                      </a>
                    <?php else: ?>
                    <?php endif; ?>
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

<div class="container my-4">
  <h1 class="h4 mb-4">Pengaturan Email Notifikasi</h1>

  <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show" role="alert">
      <?= $_SESSION['flash']['message'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
  <?php endif; ?>

  <div class="card mb-4">
    <div class="card-body d-flex justify-content-between align-items-center">
      <p class="mb-0"><?= h($currentEmail) ?></p>
      <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editEmailModal">
        <i class="bi bi-pencil"></i> Edit
      </button>
    </div>
  </div>
</div>

<!-- Modal Edit Email -->
<div class="modal fade" id="editEmailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <input type="hidden" name="update_email" value="1">
        <div class="modal-header">
          <h5 class="modal-title">Edit Email Notifikasi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="email" name="notification_email" class="form-control" value="<?= h($currentEmail) ?>" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>




<?php include __DIR__ . '/partials/footer.php';
?>