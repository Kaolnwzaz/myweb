<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

$res = mysqli_query($conn, "SELECT relay_id, state FROM relay_states");
$states = [1=>0,2=>0,3=>0,4=>0,5=>0];
while ($r = mysqli_fetch_assoc($res)) {
    $id = (int)$r['relay_id'];
    if ($id >=1 && $id <=5) $states[$id] = (int)$r['state'];
}

echo json_encode([
    'ry1' => $states[1],
    'ry2' => $states[2],
    'ry3' => $states[3],
    'ry4' => $states[4],
    'ry5' => $states[5]
]);

?>
