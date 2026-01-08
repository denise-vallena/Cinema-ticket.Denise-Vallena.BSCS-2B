<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    echo "<p>Missing credentials. <a href=\"login.html\">Back</a></p>";
    exit;
}

$stmt = $mysqli->prepare('SELECT id, password, role FROM users WHERE email = ? LIMIT 1');
if (! $stmt) {
    echo "<p>Database error: " . htmlspecialchars($mysqli->error) . "</p>";
    exit;
}

$stmt->bind_param('s', $email);
if (! $stmt->execute()) {
    echo "<p>Query error: " . htmlspecialchars($stmt->error) . "</p>";
    exit;
}
$stmt->store_result();
if ($stmt->num_rows === 0) {
    echo "<p>Invalid email or password. <a href=\"login.html\">Back</a></p>";
    exit;
}
$stmt->bind_result($id, $hash, $role);
$stmt->fetch();
if (password_verify($password, $hash)) {
    // preserve stored role on login
    login_user_with_role($id, $email, $role);
    // load balance into session
    $bq = $mysqli->prepare('SELECT balance FROM users WHERE id = ? LIMIT 1');
    if ($bq) {
        $bq->bind_param('i', $id);
        $bq->execute();
        $bq->bind_result($bal);
        $bq->fetch();
        $bq->close();
        $_SESSION['balance'] = isset($bal) ? (float)$bal : 1000.00;
    }
    // redirect based on role (admin -> index.php, user -> cinema.php)
    if ($role === 'admin') header('Location: index.php'); else header('Location: cinema.php');
    exit;
}

echo "<p>Invalid email or password. <a href=\"login.html\">Back</a></p>";
