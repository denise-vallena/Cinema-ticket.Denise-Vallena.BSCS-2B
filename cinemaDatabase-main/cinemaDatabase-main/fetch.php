<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

$scope = $_GET['scope'] ?? 'public';
$sql = "SELECT id, title, director, year, description, image, price, published, archived_at, created_at FROM movies ";
if ($scope === 'public') {
    $sql .= "WHERE published = 1 AND (archived_at IS NULL) ";
} elseif ($scope === 'archived') {
    $sql .= "WHERE archived_at IS NOT NULL ";
} else {
    // all
}
$sql .= " ORDER BY id DESC";
$res = $mysqli->query($sql);
$out = [];
while ($row = $res->fetch_assoc()) {
    $out[] = $row;
}

echo json_encode($out);
