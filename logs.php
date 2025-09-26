<?php
require_once __DIR__ . '/db.php';

if (empty($_SESSION['admin'])) {
  header('Location: login.php');
  exit;
}

// Ambil semua log lalu dipisah per kategori
$stmt = db()->query("
  SELECT l.*, i.name AS item_name
  FROM logs l
  JOIN items i ON i.id = l.item_id
  ORDER BY l.created_at DESC, l.id DESC
");
$all = $stmt->fetchAll();

$stmt = db()->query("SELECT * FROM admin_logs ORDER BY created_at DESC, id DESC");
$log_admin = $stmt->fetchAll();


$log_input = array_filter($all, fn($r) => $r['action'] === 'input');
$log_borrow_return = array_filter($all, fn($r) => in_array($r['action'], ['borrow', 'return']));
$log_take = array_filter($all, fn($r) => $r['action'] === 'take');

include __DIR__ . '/partials/header.php';
?>
<h1 class="h4 mb-3">Log Aktivitas</h1>

<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="pills-input-tab" data-bs-toggle="pill" data-bs-target="#pills-input"
      type="button" role="tab">Log Penginputan</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="pills-borrow-tab" data-bs-toggle="pill" data-bs-target="#pills-borrow" type="button"
      role="tab">Log Peminjaman & Pengembalian</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="pills-take-tab" data-bs-toggle="pill" data-bs-target="#pills-take" type="button"
      role="tab">Log Pengambilan</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="pills-admin-tab" data-bs-toggle="pill" data-bs-target="#pills-admin" type="button"
      role="tab">Log Admin</button>
  </li>

</ul>

<div class="tab-content" id="pills-tabContent">
  <div class="tab-pane fade show active" id="pills-input" role="tabpanel">
    <?php if (empty($log_input)): ?>
      <div class="alert alert-info">Belum ada log penginputan.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>Waktu</th>
              <th>Barang</th>
              <th>Nama Penginput</th>
              <th>Jumlah</th>
              
            </tr>
          </thead>
          <tbody>
            <?php foreach ($log_input as $r): ?>
              <tr>
                <td><?php echo h($r['created_at']); ?></td>
                <td><?php echo h($r['item_name']); ?></td>
                <td><?php echo h($r['actor']); ?></td>
                <td><?php echo (int) $r['qty']; ?></td>
                
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <div class="tab-pane fade" id="pills-borrow" role="tabpanel">
    <?php if (empty($log_borrow_return)): ?>
      <div class="alert alert-info">Belum ada log peminjaman/pengembalian.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>Waktu</th>
              <th>Aksi</th>
              <th>Barang</th>
              <th>Nama</th>
              <th>Jumlah</th>
              <th>Acc</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($log_borrow_return as $r): ?>
              <tr>
                <td><?php echo h($r['created_at']); ?></td>
                <td>
                  <?php if ($r['action'] === 'borrow'): ?>
                    <span class="badge text-bg-primary">Pinjam</span>
                  <?php else: ?>
                    <span class="badge text-bg-success">Kembali</span>
                  <?php endif; ?>
                </td>
                <td><?php echo h($r['item_name']); ?></td>
                <td><?php echo h($r['actor']); ?></td>
                <td><?php echo (int) $r['qty']; ?></td>
                <td><?php echo h( $r['verified_by']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <div class="tab-pane fade" id="pills-take" role="tabpanel">
    <?php if (empty($log_take)): ?>
      <div class="alert alert-info">Belum ada log pengambilan.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>Waktu</th>
              <th>Barang</th>
              <th>Nama Pengambil</th>
              <th>Jumlah</th>
              <th>Acc</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($log_take as $r): ?>
              <tr>
                <td><?php echo h($r['created_at']); ?></td>
                <td><?php echo h($r['item_name']); ?></td>
                <td><?php echo h($r['actor']); ?></td>
                <td><?php echo (int) $r['qty']; ?></td>
                <td><?php echo h( $r['verified_by']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
  <div class="tab-pane fade" id="pills-admin" role="tabpanel">
    <?php if (empty($log_admin)): ?>
      <div class="alert alert-info">Belum ada log admin.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>Waktu</th>
              <th>Admin</th>
              <th>Aksi</th>
              <th>Detail</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($log_admin as $r): ?>
              <tr>
                <td><?php echo h($r['created_at']); ?></td>
                <td><?php echo h($r['admin_username']); ?></td>
                <td><?php echo h($r['action']); ?></td>
                <td><?php echo h($r['details']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>