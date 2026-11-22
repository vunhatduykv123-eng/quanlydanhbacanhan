<?php
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /QuanLyDanhBaCaNhan/index.php?auth=login');
    exit;
}

$login_id = trim($_POST['login_id'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

if ($login_id == '' || $password == '') {
    $_SESSION['auth_errors'] = ['login' => 'Vui lòng nhập đầy đủ email/số điện thoại và mật khẩu.'];
    $_SESSION['auth_old'] = ['login_id' => $login_id];
    header('Location: /QuanLyDanhBaCaNhan/index.php?auth=login');
    exit;
}

$sql = "SELECT * FROM users WHERE email = ? OR phone = ? OR username = ? LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$login_id, $login_id, $login_id]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    $_SESSION['auth_errors'] = ['login' => 'Email/số điện thoại hoặc mật khẩu không chính xác.'];
    $_SESSION['auth_old'] = ['login_id' => $login_id];
    header('Location: /QuanLyDanhBaCaNhan/index.php?auth=login');
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['full_name'];

if ($remember) {
    setcookie(session_name(), session_id(), time() + (86400 * 7), '/');
}

header('Location: /QuanLyDanhBaCaNhan/modules/dashboard.php');
exit;