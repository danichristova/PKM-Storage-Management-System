<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mailer.php';

$item_id = (int) ($_GET['item_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $item_id = (int) ($_POST['item_id'] ?? 0);
  $actor = trim($_POST['actor'] ?? '');
  $qty = max(0, (int) ($_POST['qty'] ?? 0));
  $pin = trim($_POST['pin'] ?? '');

  if ($item_id <= 0 || $actor === '' || $qty <= 0) {
    flash('error', 'Mohon lengkapi data dengan benar.');
    header('Location: take.php?item_id=' . $item_id);
    exit;
  }

  $pdo = db();

  // CEK PIN DI DB
  $stmt = $pdo->prepare("SELECT id, description FROM pins WHERE pin_code = ?");
  $stmt->execute([$pin]);
  $valid_pin = $stmt->fetch();
  $verified_by = $valid_pin['description'] ?? 'Unknown';


  if (!$valid_pin) {
    flash('error', 'PIN tidak valid!');
    header('Location: take.php?item_id=' . $item_id);
    exit;
  }


  $pdo = db();

  $pdo->beginTransaction();
  try {
    $stmt = $pdo->prepare("SELECT qty, name FROM items WHERE id = ? FOR UPDATE");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();
    if (!$item) {
      throw new Exception('Barang tidak ditemukan.');
    }
    if ($item['qty'] < $qty) {
      throw new Exception('Stok tidak mencukupi.');
    }

    // Kurangi stok permanen
    $stmt2 = $pdo->prepare("UPDATE items SET qty = qty - ? WHERE id = ?");
    $stmt2->execute([$qty, $item_id]);

    // Ambil waktu dari PHP
    $now = date('Y-m-d H:i:s');

    // Catat log dengan waktu manual
    $stmt3 = $pdo->prepare("INSERT INTO logs (item_id, action, actor, qty, verified_by, created_at) 
                        VALUES (?,?,?,?,?,?)");
    $stmt3->execute([$item_id, 'take', $actor, $qty, $verified_by, $now]);


    // Kirim email notifikasi
    $subject = "Notifikasi Pengambilan";
    $body = "
    <h3>Ada pengambilan baru!</h3>
    <p><strong>Nama Pengambil:</strong> {$actor}</p>
    <p><strong>Barang:</strong> {$item['name']}</p>
    <p><strong>Jumlah:</strong> {$qty}</p>
    <p><strong>Waktu:</strong> " . date('d-m-Y H:i:s') . "</p>
    <p><strong>Acc:</strong> {$verified_by}</p>
    ";

    // Ambil email dari tabel settings
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'notification_email' LIMIT 1");
    $stmt->execute();
    $notifEmail = $stmt->fetchColumn() ?: 'danichristova02@gmail.com';

    // Kirim email ke alamat yang diatur
    sendEmail($notifEmail, $subject, $body);

    $pdo->commit();
    flash('success', 'Pengambilan dicatat dan stok dikurangi.');
    header('Location: index.php');
    exit;
  } catch (Exception $e) {
    $pdo->rollBack();
    flash('error', 'Gagal mengambil: ' . $e->getMessage());
    header('Location: take.php?item_id=' . $item_id);
    exit;
  }
}

$items = db()->query("SELECT id, name, qty FROM items ORDER BY name ASC")->fetchAll();

include __DIR__ . '/partials/header.php';
?>
<h1 class="h4 mb-3">Ambil Barang</h1>
<form method="post" class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Pilih Barang</label>
    <select name="item_id" class="form-select" required>
      <option value="">-- pilih barang --</option>
      <?php foreach ($items as $it): ?>
        <option value="<?php echo (int) $it['id']; ?>" <?php echo $item_id === $it['id'] ? 'selected' : ''; ?>>
          <?php echo h($it['name']); ?> (stok: <?php echo (int) $it['qty']; ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Jumlah</label>
    <input type="number" name="qty" min="1" class="form-control" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Nama Pengambil</label>
    <input type="text" name="actor" class="form-control" required>
  </div>
  <div class="col-12 d-grid">
    <button type="button" class="btn btn-primary" onclick="showPinModal()">Catat Pengambilan</button>

    <div class="modal fade" id="pinModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="post">
            <div class="modal-header">
              <h5 class="modal-title">Konfirmasi Pengambilan</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <!-- Hidden untuk submit -->
              <input type="hidden" name="item_id" id="itemField">
              <input type="hidden" name="actor" id="actorField">
              <input type="hidden" name="qty" id="qtyField">

              <p><b>Barang:</b> <span id="itemPreview"></span></p>
              <p><b>Jumlah:</b> <span id="qtyPreview"></span></p>
              <p><b>Peminjam:</b> <span id="actorPreview"></span></p>

              <label>PIN Verifikasi</label>
              <input type="password" name="pin" class="form-control" required>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-success">Konfirmasi</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
      function showPinModal() {
        let itemSelect = document.querySelector('select[name="item_id"]');
        let itemName = itemSelect.options[itemSelect.selectedIndex].text;
        let itemId = itemSelect.value;
        let qty = document.querySelector('input[name="qty"]').value;
        let actor = document.querySelector('input[name="actor"]').value;

        if (!itemId || !qty || !actor) {
          alert("Mohon isi semua data dulu!");
          return;
        }

        // Isi hidden field untuk submit
        document.getElementById('itemField').value = itemId;
        document.getElementById('qtyField').value = qty;
        document.getElementById('actorField').value = actor;

        // Isi preview
        document.getElementById('itemPreview').textContent = itemName;
        document.getElementById('qtyPreview').textContent = qty;
        document.getElementById('actorPreview').textContent = actor;

        // Show modal
        var modal = new bootstrap.Modal(document.getElementById('pinModal'));
        modal.show();
      }
    </script>


  </div>
</form>
<?php include __DIR__ . '/partials/footer.php'; ?>