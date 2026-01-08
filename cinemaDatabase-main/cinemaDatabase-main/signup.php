<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
header('Content-Type: text/html; charset=utf-8');

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['password_confirm'] ?? '';

if ($email === '' || $password === '' || $confirm === '') {
    echo "<p>All fields are required. <a href=\"signup.html\">Back</a></p>";
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<p>Invalid email address. <a href=\"signup.html\">Back</a></p>";
    exit;
}
if ($password !== $confirm) {
    echo "<p>Passwords do not match. <a href=\"signup.html\">Back</a></p>";
    exit;
}

// check email availability
$stmt = $mysqli->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo "<p>Email already registered. <a href=\"signup.html\">Back</a></p>";
    exit;
}

// determine role: first user becomes admin
$res = $mysqli->query('SELECT COUNT(*) as cnt FROM users');
$row = $res->fetch_assoc();
$role = ($row && intval($row['cnt']) === 0) ? 'admin' : 'user';

$hash = password_hash($password, PASSWORD_DEFAULT);
$ins = $mysqli->prepare('INSERT INTO users (email, password, role) VALUES (?, ?, ?)');
$ins->bind_param('sss', $email, $hash, $role);
if ($ins->execute()) {
    // auto-login and redirect to main site (admin -> index.php, user -> cinema.html)
    $userId = $ins->insert_id;
    login_user_with_role($userId, $email, $role);
    // ensure session has balance loaded
    $bq = $mysqli->prepare('SELECT balance FROM users WHERE id = ? LIMIT 1');
    if ($bq) {
        $bq->bind_param('i', $userId);
        $bq->execute();
        $bq->bind_result($bal);
        $bq->fetch();
        $bq->close();
        $_SESSION['balance'] = isset($bal) ? (float)$bal : 1000.00;
    }
    // redirect based on role (admin -> index.php, user -> cinema.php)
    if ($role === 'admin') header('Location: index.php'); else header('Location: cinema.php');
    exit;
} else {
    echo "<p>Unable to create account: " . htmlspecialchars($ins->error) . "</p>";
}
