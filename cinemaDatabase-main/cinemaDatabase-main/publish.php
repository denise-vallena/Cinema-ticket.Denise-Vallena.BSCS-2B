<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'forbidden', 'message' => 'Admin only']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
$value = isset($_POST['published']) ? ($_POST['published'] ? 1 : 0) : (isset($_GET['published']) ? ($_GET['published'] ? 1 : 0) : null);
if ($id <= 0 || $value === null) {
    echo json_encode(['ok' => false, 'error' => 'invalid_params']);
    exit;
}

$stmt = $mysqli->prepare('UPDATE movies SET published = ? WHERE id = ?');
if (! $stmt) { echo json_encode(['ok'=>false,'error'=>'prepare','message'=>$mysqli->error]); exit; }
$stmt->bind_param('ii', $value, $id);
if (! $stmt->execute()) { echo json_encode(['ok'=>false,'error'=>'execute','message'=>$stmt->error]); exit; }

echo json_encode(['ok' => true, 'id' => $id, 'published' => (int)$value]);
