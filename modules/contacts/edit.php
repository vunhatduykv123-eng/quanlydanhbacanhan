<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /QuanLyDanhBaCaNhan/modules/contacts/list.php');
    exit;
}

$userId = $_SESSION['user_id'];
$id = (int)($_POST['id'] ?? 0);
$fullName = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$groupName = $_POST['group_name'] ?? 'Khác';

$allowedGroups = ['Gia đình', 'Bạn bè', 'Công việc', 'Khác'];
if (!in_array($groupName, $allowedGroups, true)) {
    $groupName = 'Khác';
}

if ($id <= 0 || $fullName == '' || !preg_match('/^[0-9]{10,11}$/', $phone)) {
    $_SESSION['contact_error'] = 'Vui lòng nhập đầy đủ họ tên và số điện thoại hợp lệ (10–11 số).';
    header('Location: /QuanLyDanhBaCaNhan/modules/contacts/list.php');
    exit;
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['contact_error'] = 'Địa chỉ email không hợp lệ.';
    header('Location: /QuanLyDanhBaCaNhan/modules/contacts/list.php');
    exit;
}

// Chỉ cập nhật liên hệ thuộc tài khoản đang đăng nhập
$stmt = $pdo->prepare("
    UPDATE contacts
    SET full_name = ?, phone = ?, email = ?, address = ?, group_name = ?
    WHERE id = ? AND user_id = ?
");
$stmt->execute([
    $fullName,
    $phone,
    $email !== '' ? $email : null,
    $address !== '' ? $address : null,
    $groupName,
    $id,
    $userId
]);

$_SESSION['contact_success'] = 'Đã cập nhật thông tin liên hệ thành công!';
header('Location: /QuanLyDanhBaCaNhan/modules/contacts/list.php');
exit;