<?php
require_once __DIR__ . '/db.php';


$search = trim($_GET['q'] ?? '');
$rack = $_GET['rack'] ?? 'all';

$sql = "SELECT i.*, r.name AS rack_name
        FROM items i
        LEFT JOIN racks r ON i.rack = r.id
        WHERE 1";
$params = [];


$racks = db()->query("SELECT * FROM racks ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$validRackIds = array_column($racks, 'id');

if ($rack !== 'all' && in_array($rack, $validRackIds)) {
  $sql .= " AND i.rack = :rack";
  $params[':rack'] = $rack;
}



if ($search !== '') {
  $sql .= " AND i.name LIKE :q";
  $params[':q'] = '%' . $search . '%';
}
if (in_array($rack, ['1', '2', '3'])) {
  $sql .= " AND rack = :rack";
  $params[':rack'] = $rack;
}
$sql .= " ORDER BY created_at DESC";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

include __DIR__ . '/partials/header.php';
?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
  <h1 class="h4 m-0">Katalog Barang</h1>
  <span class="text-muted">/ lihat & cari barang</span>
</div>

<form class="row g-2 mb-4" method="get" id="filter-form">
  <div class="col-md-6">
    <input type="text" class="form-control" name="q" id="search" placeholder="Cari nama barang...">
  </div>
  <div class="col-md-3">
    <select class="form-select" name="rack" id="rack">
      <option value="all">Semua Rak</option>
      <?php foreach ($racks as $r): ?>
        <option value="<?php echo (int) $r['id']; ?>"><?php echo h($r['name']); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3 d-grid">
    <button type="submit" class="btn btn-primary">Terapkan</button>
  </div>
</form>

<div id="items-container" class="row g-3"></div>
<div id="loading" class="text-center my-3" style="display:none;">Loading...</div>

<script>
  const isAdmin = <?php echo isset($_SESSION['admin']) ? 'true' : 'false'; ?>;
</script>



<script>
  let page = 1;
  let loading = false;
  let finished = false;

  async function loadItems(reset = false) {
    if (loading || finished) return;
    loading = true;
    document.getElementById("loading").style.display = "block";

    const search = document.getElementById("search").value;
    const rack = document.getElementById("rack").value;

    const res = await fetch(`get_items.php?page=${page}&q=${encodeURIComponent(search)}&rack=${rack}`);
    const data = await res.json();

    if (reset) {
      document.getElementById("items-container").innerHTML = "";
      page = 1;
      finished = false;
    }


    if (data.length === 0) {
      finished = true;
      document.getElementById("loading").innerText = "Tidak ada lagi data.";
    } else {
      const container = document.getElementById("items-container");
      data.forEach(it => {
        let adminButtons = "";
        if (isAdmin) {
          adminButtons = `
    <a href="hapus.php?id=${it.id}" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus barang ini?');">Hapus</a>
    <a href="settings_edit_item_index.php?id=${it.id}" class="btn btn-sm btn-warning">Edit</a>
  `;
        }
        container.innerHTML += `
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            ${it.photo_path ? `<img src="${it.photo_path}" class="img-thumb" alt="">` :
            `<svg class="img-thumb bg-light"><rect width="100%" height="100%" fill="#e9ecef"/></svg>`}
            <div class="card-body d-flex flex-column">
              <div class="d-flex justify-content-between align-items-start mb-1">
                <h5 class="card-title m-0">${it.name}</h5>
                <span class="badge text-bg-secondary badge-rack">${it.rack_name ?? '—'}</span>
              </div>
              <p class="text-muted mb-2">${it.type}</p>
              <p class="mb-2">Stok saat ini: <strong>${it.qty}</strong></p>
              <div class="mt-auto d-flex gap-2">
                <a href="borrow.php?item_id=${it.id}" class="btn btn-sm btn-outline-primary">Pinjam</a>
                <a href="return.php?item_id=${it.id}" class="btn btn-sm btn-outline-success">Kembalikan</a>
                <a href="take.php?item_id=${it.id}" class="btn btn-sm btn-outline-danger">Ambil</a>
                ${adminButtons}
              </div>
            </div>
          </div>
        </div>`;
      });
      page++;
    }

    document.getElementById("loading").style.display = "none";
    loading = false;
  }

  // trigger saat pertama kali load
  loadItems();

  // scroll event
  window.addEventListener("scroll", () => {
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 200) {
      loadItems();
    }
  });

  // filter submit
  document.getElementById("filter-form").addEventListener("submit", (e) => {
    e.preventDefault();
    page = 1;
    finished = false;
    loadItems(true);
  });
</script>


<?php include __DIR__ . '/partials/footer.php'; ?>