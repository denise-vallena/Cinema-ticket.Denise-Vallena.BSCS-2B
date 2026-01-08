<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

// support form-data (with file upload) or JSON
$title = trim($_POST['title'] ?? '');
$director = trim($_POST['director'] ?? '');
$year = isset($_POST['year']) ? $_POST['year'] : null;
$description = trim($_POST['description'] ?? '');
$price = isset($_POST['price']) ? $_POST['price'] : null;
$published = isset($_POST['published']) ? $_POST['published'] : null;

// if JSON body
if ($title === '' && strtoupper($_SERVER['CONTENT_TYPE'] ?? '') === 'APPLICATION/JSON') {
    $data = json_decode(file_get_contents('php://input'), true);
    $title = trim($data['title'] ?? '');
    $director = trim($data['director'] ?? '');
    $year = $data['year'] ?? null;
    $description = trim($data['description'] ?? '');
    $price = $data['price'] ?? null;
    $published = $data['published'] ?? $published;
}

// handle uploaded image
$imagePath = null;
if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['image']['tmp_name'];
    $name = basename($_FILES['image']['name']);
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $safe = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($name, PATHINFO_FILENAME));
    $targetName = $safe . '_' . time() . '.' . $ext;
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $dest = $uploadDir . $targetName;
    if (move_uploaded_file($tmp, $dest)) {
        $imagePath = 'uploads/' . $targetName;
    }
}

if ($title === '') {
    echo json_encode(['ok' => false, 'error' => 'Title is required']);
    exit;
}
// enforce minimum ticket price of 300 (pesos)
if ($price !== null && $price !== '') {
    $priceVal = (float)$price;
    if ($priceVal < 300) {
        echo json_encode(['ok' => false, 'error' => 'price_too_low', 'message' => 'Price must be at least 300 pesos']);
        exit;
    }
}

// Insert with optional year and description
// build insert statement dynamically to include optional fields
$cols = ['title','director'];
$placeholders = ['?','?'];
$types = 'ss';
$values = [$title, $director];
if ($year !== null && $year !== '') { $cols[] = 'year'; $placeholders[] = '?'; $types .= 'i'; $values[] = (int)$year; }
if ($description !== '') { $cols[] = 'description'; $placeholders[] = '?'; $types .= 's'; $values[] = $description; }
if ($price !== null && $price !== '') { $cols[] = 'price'; $placeholders[] = '?'; $types .= 'd'; $values[] = (float)$price; }
if ($imagePath !== null) { $cols[] = 'image'; $placeholders[] = '?'; $types .= 's'; $values[] = $imagePath; }
if ($published !== null && $published !== '') { $cols[] = 'published'; $placeholders[] = '?'; $types .= 'i'; $values[] = (int)$published; }

$sql = 'INSERT INTO movies (' . implode(',', $cols) . ') VALUES (' . implode(',', $placeholders) . ')';
$stmt = $mysqli->prepare($sql);
if (! $stmt) {
    echo json_encode(['ok' => false, 'error' => 'prepare_failed', 'message' => $mysqli->error]);
    exit;
}

// bind params dynamically in a way compatible with older PHP versions
if ($types !== '') {
    $params = array_merge([$types], $values);
    // make references
    $refs = [];
    foreach ($params as $key => $val) $refs[$key] = &$params[$key];
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

if (! $stmt->execute()) {
    echo json_encode(['ok' => false, 'error' => 'execute_failed', 'message' => $stmt->error]);
    exit;
}

echo json_encode(['ok' => true, 'id' => $stmt->insert_id]);
