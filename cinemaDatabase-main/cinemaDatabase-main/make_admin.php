<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
header('Content-Type: text/html; charset=utf-8');

require_login();
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo '<p>Forbidden</p>';
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$role = isset($_POST['role']) ? $_POST['role'] : '';
if ($id <= 0 || !in_array($role, ['admin','user'])) {
    echo '<p>Invalid parameters. <a href="admin_settings.php">Back</a></p>';
    exit;
}

$stmt = $mysqli->prepare('UPDATE users SET role = ? WHERE id = ?');
if (! $stmt) { echo '<p>DB error: ' . htmlspecialchars($mysqli->error) . '</p>'; exit; }
$stmt->bind_param('si', $role, $id);
if (! $stmt->execute()) { echo '<p>DB error: ' . htmlspecialchars($stmt->error) . '</p>'; exit; }

// if demoting/promoting the current user, update session
if ((int)$_SESSION['user_id'] === $id) {
    $_SESSION['role'] = $role;
}

// redirect based on where user came from
if ($role === 'user') {
    // demoted user should go to account page
    header('Location: user.php');
} else {
    // promoted user should go to admin settings
    header('Location: admin_settings.php');
}
exit;
