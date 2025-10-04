<?php
require_once __DIR__ . '/db.php';

$search = trim($_GET['q'] ?? '');
$rack = $_GET['rack'] ?? 'all';
$page = (int)($_GET['page'] ?? 1);
$limit = 12; // jumlah item per load
$offset = ($page - 1) * $limit;

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

$sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

$stmt = db()->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($items);
