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
?>

<h1>Kelola PIN</h1>
<form method="post">
    <input type="text" name="pin_code" placeholder="PIN baru" required>
    <input type="text" name="description" placeholder="Keterangan (opsional)">
    <button type="submit">Tambah PIN</button>
</form>

<table border="1">
  <tr><th>ID</th><th>PIN</th><th>Keterangan</th><th>Aksi</th></tr>
  <?php foreach ($pins as $p): ?>
    <tr>
      <td><?php echo $p['id']; ?></td>
      <td><?php echo $p['pin_code']; ?></td>
      <td><?php echo $p['description']; ?></td>
      <td>
        <a href="delete_pin.php?id=<?php echo $p['id']; ?>" onclick="return confirm('Hapus PIN ini?')">Hapus</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
