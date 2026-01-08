<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'not_logged_in']);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$movieId = $_POST['id'] ?? null;
$buyerName = $_POST['buyer'] ?? ($_SESSION['email'] ?? '');

// if movie id is not numeric, treat as placeholder: no server-side charge
if (! is_numeric($movieId)) {
    // create a server-side ticket record with null movie_id and zero price? We'll return a client-only ticket
    $ticketId = 'PH-' . time() . '-' . strtoupper(substr(bin2hex(random_bytes(4)),0,8));
    $issued = date('c');
    $payload = ['ticketId'=>$ticketId,'movie'=>'placeholder','buyer'=>$buyerName,'issued'=>$issued,'price'=>0.00];
    echo json_encode(['ok'=>true,'ticketId'=>$ticketId,'payload'=>$payload,'price'=>0.00]);
    exit;
}

$movieId = (int)$movieId;
// fetch movie price and title
$stmt = $mysqli->prepare('SELECT id, title, price FROM movies WHERE id = ? LIMIT 1');
if (! $stmt) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'db_prepare','message'=>$mysqli->error]); exit; }
$stmt->bind_param('i',$movieId);
$stmt->execute();
$res = $stmt->get_result();
$movie = $res->fetch_assoc();
if (! $movie) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'not_found']); exit; }
$price = (float)($movie['price'] ?? 0.0);

// enforce minimum price
if ($price < 300.00) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'price_too_low','message'=>'Ticket price must be at least 300 pesos']);
    exit;
}

// check user balance
$stmt = $mysqli->prepare('SELECT balance FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i',$userId);
$stmt->execute();
$stmt->bind_result($balance);
$stmt->fetch();
$stmt->close();
$balance = (float)$balance;
if ($balance < $price) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'insufficient_funds','balance'=>$balance,'price'=>$price]);
    exit;
}

// Deduct balance and insert ticket in transaction
$mysqli->begin_transaction();
try {
    // update balance
    $upd = $mysqli->prepare('UPDATE users SET balance = balance - ? WHERE id = ?');
    $upd->bind_param('di', $price, $userId);
    if (! $upd->execute()) throw new Exception('Failed to deduct balance: ' . $upd->error);

    // build ticket id
    $ticketId = 'TKT-' . time() . '-' . strtoupper(substr(bin2hex(random_bytes(4)),0,8));
    $payload = ['ticketId'=>$ticketId,'movie'=> $movie['title'],'movie_id'=>$movieId,'buyer'=>$buyerName,'issued'=>date('c')];
    $payloadJson = json_encode($payload);
    $ins = $mysqli->prepare('INSERT INTO tickets (ticket_id,user_id,movie_id,price,payload) VALUES (?,?,?,?,?)');
    $ins->bind_param('siids', $ticketId, $userId, $movieId, $price, $payloadJson);
    if (! $ins->execute()) throw new Exception('Failed to insert ticket: ' . $ins->error);

    $mysqli->commit();
    // update session balance for immediate UI
    $_SESSION['balance'] = max(0, $balance - $price);
    echo json_encode(['ok'=>true,'ticketId'=>$ticketId,'payload'=>$payload,'price'=>$price,'balance'=>$_SESSION['balance']]);
    exit;
} catch (Exception $e) {
    $mysqli->rollback();
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'server_error','message'=>$e->getMessage()]);
    exit;
}
