<?php
require_once __DIR__ . '/../config/db.php';

function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: /QuanLyDanhBaCaNhan/index.php?auth=login');
        exit;
    }
}

function current_user(PDO $pdo): ?array {
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = $pdo->prepare("
        SELECT id, full_name, username, email, phone, avatar, address, birthday, two_factor_enabled, login_alert_enabled
        FROM users
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user ?: null;
}