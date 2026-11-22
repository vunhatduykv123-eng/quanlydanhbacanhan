<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

$userId = $_SESSION['user_id'];
$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    $_SESSION['contact_success'] = 'Đã xóa liên hệ khỏi danh bạ.';
}

header('Location: /QuanLyDanhBaCaNhan/modules/contacts/list.php');
exit;