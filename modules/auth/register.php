<?php
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /QuanLyDanhBaCaNhan/index.php?auth=register');
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$agree = isset($_POST['agree_terms']);

$errors = [];

if ($full_name == '') {
    $errors['full_name'] = 'Vui lòng nhập họ và tên.';
}
if (!preg_match('/^[0-9]{10,11}$/', $phone)) {
    $errors['phone'] = 'Số điện thoại phải từ 10 đến 11 chữ số.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Email không đúng định dạng.';
}
if (strlen($password) < 6 || strlen($password) > 32) {
    $errors['password'] = 'Mật khẩu phải từ 6 đến 32 ký tự.';
}
if ($password !== $confirm_password) {
    $errors['confirm_password'] = 'Mật khẩu xác nhận không khớp.';
}
if (!$agree) {
    $errors['agree_terms'] = 'Bạn phải đồng ý với điều khoản sử dụng.';
}

if (empty($errors)) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1");
    $stmt->execute([$email, $phone]);
    if ($stmt->fetch()) {
        $errors['email'] = 'Email hoặc số điện thoại đã tồn tại trong hệ thống.';
    }
}

if (!empty($errors)) {
    $_SESSION['auth_errors'] = $errors;
    $_SESSION['auth_old'] = [
        'full_name' => $full_name,
        'phone' => $phone,
        'email' => $email
    ];
    header('Location: /QuanLyDanhBaCaNhan/index.php?auth=register');
    exit;
}

$username = preg_replace('/[^a-zA-Z0-9_]/', '', explode('@', $email)[0]);
if (strlen($username) < 4) {
    $username .= '_user';
}
$username = substr($username, 0, 20) . '_' . rand(100, 999);

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$full_name, $username, $email, $phone, $hash]);

$_SESSION['auth_success'] = 'Đăng ký thành công! Mời bạn đăng nhập.';
header('Location: /QuanLyDanhBaCaNhan/index.php?auth=login');
exit;