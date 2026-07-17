<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

$input = $_POST ?: $_GET ?: json_decode(file_get_contents('php://input'), true) ?: [];

// Allow both GET and POST for simple IoT devices
$type = isset($input['type']) ? $input['type'] : (isset($_GET['type']) ? $_GET['type'] : null);

if (!$type) {
    echo json_encode(['ok' => false, 'error' => 'missing type']);
    exit;
}

if ($type === 'ldr') {
    $value = isset($input['value']) ? (int)$input['value'] : (isset($_GET['value']) ? (int)$_GET['value'] : null);
    if ($value === null) {
        echo json_encode(['ok' => false, 'error' => 'missing value']);
        exit;
    }
    $stmt = mysqli_prepare($conn, "INSERT INTO sensor_ldr (value) VALUES (?)");
    mysqli_stmt_bind_param($stmt, 'i', $value);
    mysqli_stmt_execute($stmt);
    echo json_encode(['ok' => true]);
    exit;
}

if ($type === 'switch') {
    $id = isset($input['id']) ? (int)$input['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : null);
    $state = isset($input['state']) ? (int)$input['state'] : (isset($_GET['state']) ? (int)$_GET['state'] : null);
    if ($id === null || $state === null) {
        echo json_encode(['ok' => false, 'error' => 'missing id/state']);
        exit;
    }
    $stmt = mysqli_prepare($conn, "INSERT INTO switch_events (switch_id, state) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $state);
    mysqli_stmt_execute($stmt);
    echo json_encode(['ok' => true]);
    exit;
}

if ($type === 'list_switches') {
    $limit = isset($input['limit']) ? (int)$input['limit'] : (isset($_GET['limit']) ? (int)$_GET['limit'] : 50);
    if ($limit <= 0 || $limit > 500) $limit = 50;
    $res = mysqli_query($conn, "SELECT switch_id, state, event_time FROM switch_events ORDER BY event_time DESC LIMIT " . $limit);
    $data = [];
    while ($r = mysqli_fetch_assoc($res)) {
        $data[] = $r;
    }
    echo json_encode(['ok' => true, 'data' => $data]);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'unknown type']);

?>
