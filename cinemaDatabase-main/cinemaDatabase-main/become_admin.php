<?php
// promote current logged-in user to admin (best-effort endpoint)
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Support both form POST (redirect) and AJAX (JSON) clients.
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
         || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

if (empty($_SESSION['user_id'])) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'not_logged_in', 'message' => 'You must be logged in to become admin']);
        exit;
    } else {
        header('Location: login.html');
        exit;
    }
}

$userId = (int) $_SESSION['user_id'];
try {
    $stmt = $mysqli->prepare('UPDATE users SET role = ? WHERE id = ?');
    if (! $stmt) throw new Exception('Prepare failed: ' . $mysqli->error);
    $newRole = 'admin';
    $stmt->bind_param('si', $newRole, $userId);
    if (! $stmt->execute()) throw new Exception('Execute failed: ' . $stmt->error);
    // update session role
    $_SESSION['role'] = 'admin';
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'redirect' => 'index.php']);
    } else {
        header('Location: index.php');
    }
    exit;
} catch (Exception $e) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'server_error', 'message' => $e->getMessage()]);
    } else {
        echo "<p>Unable to promote: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    exit;
}
