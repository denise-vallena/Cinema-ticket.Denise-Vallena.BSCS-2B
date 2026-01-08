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
if ($id <= 0) { echo json_encode(['ok'=>false,'error'=>'invalid_id']); exit; }

$stmt = $mysqli->prepare('UPDATE movies SET published = 0, archived_at = NOW() WHERE id = ?');
if (! $stmt) { echo json_encode(['ok'=>false,'error'=>'prepare','message'=>$mysqli->error]); exit; }
$stmt->bind_param('i', $id);
if (! $stmt->execute()) { echo json_encode(['ok'=>false,'error'=>'execute','message'=>$stmt->error]); exit; }

echo json_encode(['ok' => true, 'id' => $id]);
