<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 200;
if ($limit <= 0 || $limit > 1000) $limit = 200;

$res = mysqli_query($conn, "SELECT recorded_at, value FROM sensor_ldr ORDER BY recorded_at DESC LIMIT " . $limit);
$data = [];
while ($r = mysqli_fetch_assoc($res)) {
    $data[] = ['t' => $r['recorded_at'], 'v' => (int)$r['value']];
}

// return in chronological order
$data = array_reverse($data);

echo json_encode(['ok' => true, 'data' => $data]);

?>
