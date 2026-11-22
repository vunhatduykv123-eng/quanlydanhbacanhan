<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

$userId = $_SESSION['user_id'];
$q = trim($_GET['q'] ?? '');
$group = trim($_GET['group'] ?? '');

$sql = "SELECT id, full_name, phone, email, address, group_name FROM contacts WHERE user_id = ?";
$params = [$userId];

if ($group !== '' && $group !== 'Tất cả') {
    $sql .= " AND group_name = ?";
    $params[] = $group;
}

if ($q !== '') {
    $like = '%' . $q . '%';
    $sql .= " AND (full_name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY full_name ASC, id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode([
    'success' => true,
    'contacts' => $stmt->fetchAll()
], JSON_UNESCAPED_UNICODE);