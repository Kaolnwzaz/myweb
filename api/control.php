<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$data = $method === 'POST' ? $_POST : $_GET;

$relay = isset($data['relay']) ? (int)$data['relay'] : null;
$state = isset($data['state']) ? (int)$data['state'] : null;

if ($relay === null || $state === null) {
    // return current states
    $res = mysqli_query($conn, "SELECT relay_id, state FROM relay_states ORDER BY relay_id");
    $out = [];
    while ($r = mysqli_fetch_assoc($res)) {
        $out[(int)$r['relay_id']] = (int)$r['state'];
    }
    echo json_encode(['ok' => true, 'states' => $out]);
    exit;
}

if ($relay < 1 || $relay > 5) {
    echo json_encode(['ok' => false, 'error' => 'relay out of range']);
    exit;
}

$state = $state ? 1 : 0;

// insert or update
$stmt = mysqli_prepare($conn, "INSERT INTO relay_states (relay_id, state) VALUES (?, ?) ON DUPLICATE KEY UPDATE state=VALUES(state), updated_at=CURRENT_TIMESTAMP");
mysqli_stmt_bind_param($stmt, 'ii', $relay, $state);
mysqli_stmt_execute($stmt);

echo json_encode(['ok' => true, 'relay' => $relay, 'state' => $state]);

?>
