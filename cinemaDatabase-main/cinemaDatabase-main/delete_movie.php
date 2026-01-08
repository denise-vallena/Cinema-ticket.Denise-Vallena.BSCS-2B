<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'forbidden']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    echo json_encode(['ok' => false, 'error' => 'invalid_id']);
    exit;
}

// delete movie record and optional image file
$stmt = $mysqli->prepare('SELECT image FROM movies WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($img);
$has = $stmt->fetch();
$stmt->close();

$del = $mysqli->prepare('DELETE FROM movies WHERE id = ?');
if (! $del) { echo json_encode(['ok'=>false,'error'=>'prepare','message'=>$mysqli->error]); exit; }
$del->bind_param('i', $id);
if (! $del->execute()) { echo json_encode(['ok'=>false,'error'=>'execute','message'=>$del->error]); exit; }

// remove image file if exists
if ($has && $img) {
    $path = __DIR__ . '/' . $img;
    if (is_file($path)) @unlink($path);
}

echo json_encode(['ok' => true, 'id' => $id]);
