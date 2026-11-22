<?php
require_once __DIR__ . '/auth.php';
require_login();
$user = current_user($pdo);

// Đếm tổng số liên hệ để hiển thị badge trên sidebar
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE user_id = ?");
$stmtCount->execute([$_SESSION['user_id']]);
$totalBadge = (int)$stmtCount->fetchColumn();

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Danh Bạ Cá Nhân') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { width: 250px; min-height: 100vh; background: #fff; border-right: 1px solid #e2e8f0; }
        .sidebar .nav-link { color: #475569; border-radius: 8px; margin-bottom: 4px; font-weight: 500; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #eff6ff; color: #2563eb; }
        @media (max-width: 768px) {
            .sidebar { width: 100%; min-height: auto; border-right: none; border-bottom: 1px solid #e2e8f0; }
        }
    </style>
</head>
<body>
<div class="d-flex flex-column flex-md-row">
    <!-- Sidebar -->
    <aside class="sidebar p-3 d-flex flex-column justify-content-between">
        <div>
            <div class="mb-4 px-2">
                <h5 class="fw-bold text-primary mb-0"><i class="bi bi-person-lines-fill me-1"></i> Danh Bạ Cá Nhân</h5>
                <small class="text-muted">Quản lý liên hệ thông minh</small>
            </div>

            <div class="text-uppercase text-muted fw-bold px-2 mb-2" style="font-size: 11px;">Không gian làm việc</div>
            <ul class="nav flex-column mb-4">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="/QuanLyDanhBaCaNhan/modules/dashboard.php">
                        <i class="bi bi-house-door me-2"></i> Tổng quan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'list.php' ? 'active' : '' ?>" href="/QuanLyDanhBaCaNhan/modules/contacts/list.php">
                        <i class="bi bi-journal-text me-2"></i> Danh bạ
                        <span class="badge bg-primary-subtle text-primary float-end"><?= $totalBadge ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'groups.php' ? 'active' : '' ?>" href="/QuanLyDanhBaCaNhan/modules/groups.php">
                        <i class="bi bi-folder2-open me-2"></i> Nhóm liên hệ
                    </a>
                </li>
            </ul>

            <div class="text-uppercase text-muted fw-bold px-2 mb-2" style="font-size: 11px;">Hệ thống</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'settings.php' ? 'active' : '' ?>" href="/QuanLyDanhBaCaNhan/modules/settings.php">
                        <i class="bi bi-gear me-2"></i> Cài đặt tài khoản
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="/QuanLyDanhBaCaNhan/modules/auth/logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                    </a>
                </li>
            </ul>
        </div>

        <div class="bg-light border rounded-3 p-3 mt-4 small">
            <div class="fw-bold text-dark mb-1"><i class="bi bi-shield-check text-primary me-1"></i> Bảo mật & riêng tư</div>
            <div class="text-muted" style="font-size: 12px;">Thông tin liên hệ luôn được quản lý an toàn, riêng biệt.</div>
        </div>
    </aside>

    <!-- Nội dung chính -->
    <main class="flex-grow-1 p-3 p-md-4">