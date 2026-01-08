<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: user.php');
    exit;
}
$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0.0;
if ($amount <= 0) {
    header('Location: user.php');
    exit;
}
$userId = (int) $_SESSION['user_id'];
$stmt = $mysqli->prepare('UPDATE users SET balance = balance + ? WHERE id = ?');
$stmt->bind_param('di', $amount, $userId);
if (! $stmt->execute()) {
    echo "<p>Unable to add balance: " . htmlspecialchars($stmt->error) . "</p>";
    exit;
}
// refresh session balance
$sel = $mysqli->prepare('SELECT balance FROM users WHERE id = ? LIMIT 1');
$sel->bind_param('i', $userId);
$sel->execute();
$sel->bind_result($bal);
$sel->fetch();
$sel->close();
$_SESSION['balance'] = isset($bal) ? (float)$bal : $_SESSION['balance'];
header('Location: user.php');
exit;
