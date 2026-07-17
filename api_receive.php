<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

// Check API key in header
$provided = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (!$provided) {
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'missing api key']);
    exit;
}

$p = mysqli_real_escape_string($conn, $provided);
$r = mysqli_query($conn, "SELECT 1 FROM api_keys WHERE api_key = '$p' LIMIT 1");
if (!($r && mysqli_num_rows($r))) {
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'invalid api key']);
    exit;
}

// Read JSON body
$body = file_get_contents('php://input');
$data = json_decode($body, true);
if (!is_array($data)) {
    echo json_encode(['ok'=>false,'error'=>'invalid json']);
    exit;
}

$ok = false;
// Accept sw1, sw2 as switch events (use switch_id 1 and 2)
if (isset($data['sw1'])) {
    $state = (int)$data['sw1'];
    $stmt = mysqli_prepare($conn, "INSERT INTO switch_events (switch_id, state) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, 'ii', $id_param, $state_param);
    $id_param = 1; $state_param = $state;
    mysqli_stmt_execute($stmt);
    $ok = true;
}
if (isset($data['sw2'])) {
    $state = (int)$data['sw2'];
    $stmt = mysqli_prepare($conn, "INSERT INTO switch_events (switch_id, state) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, 'ii', $id_param, $state_param);
    $id_param = 2; $state_param = $state;
    mysqli_stmt_execute($stmt);
    $ok = true;
}

// Accept ldr value
if (isset($data['ldr'])) {
    $val = (int)$data['ldr'];
    $stmt = mysqli_prepare($conn, "INSERT INTO sensor_ldr (value) VALUES (?)");
    mysqli_stmt_bind_param($stmt, 'i', $val);
    mysqli_stmt_execute($stmt);
    $ok = true;
}

if ($ok) {
    echo json_encode(['ok'=>true]);
} else {
    echo json_encode(['ok'=>false,'error'=>'no recognized fields']);
}

?>
