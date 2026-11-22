<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /QuanLyDanhBaCaNhan/modules/contacts/list.php');
    exit;
}

$userId = $_SESSION['user_id'];
$fullName = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$groupName = $_POST['group_name'] ?? 'Khác';

$allowedGroups = ['Gia đình', 'Bạn bè', 'Công việc', 'Khác'];
if (!in_array($groupName, $allowedGroups, true)) {
    $groupName = 'Khác';
}

$errors = [];
if ($fullName == '') {
    $errors[] = 'Họ và tên liên hệ không được để trống.';
}
if (!preg_match('/^[0-9]{10,11}$/', $phone)) {
    $errors[] = 'Số điện thoại phải từ 10 đến 11 chữ số.';
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email không hợp lệ.';
}

if (!empty($errors)) {
    $_SESSION['contact_error'] = implode(' ', $errors);
    header('Location: /QuanLyDanhBaCaNhan/modules/contacts/list.php?open_add=1');
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO contacts (user_id, full_name, phone, email, address, group_name)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->execute([
    $userId,
    $fullName,
    $phone,
    $email !== '' ? $email : null,
    $address !== '' ? $address : null,
    $groupName
]);

$_SESSION['contact_success'] = 'Đã thêm liên hệ "' . $fullName . '" thành công!';
header('Location: /QuanLyDanhBaCaNhan/modules/contacts/list.php');
exit;