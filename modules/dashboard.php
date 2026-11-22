<?php
$pageTitle = 'Tổng quan - Danh Bạ Cá Nhân';
require_once __DIR__ . '/../includes/header.php';

$userId =$_SESSION['user_id'];

// Đếm tổng và từng nhóm
$stmt =$pdo->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN group_name = 'Gia đình' THEN 1 ELSE 0 END) AS family_cnt,
        SUM(CASE WHEN group_name = 'Bạn bè' THEN 1 ELSE 0 END) AS friends_cnt,
        SUM(CASE WHEN group_name = 'Công việc' THEN 1 ELSE 0 END) AS work_cnt,
        SUM(CASE WHEN group_name = 'Khác' THEN 1 ELSE 0 END) AS other_cnt
    FROM contacts
    WHERE user_id = ?
");
$stmt->execute([$userId]);
$stats =$stmt->fetch();

$total = (int)($stats['total'] ?? 0);
$family = (int)($stats['family_cnt'] ?? 0);
$friends = (int)($stats['friends_cnt'] ?? 0);
$work = (int)($stats['work_cnt'] ?? 0);
$other = (int)($stats['other_cnt'] ?? 0);

// Lấy 5 liên hệ cập nhật gần đây nhất
$stmtRecent =$pdo->prepare("
    SELECT id, full_name, phone, group_name, updated_at
    FROM contacts
    WHERE user_id = ?
    ORDER BY updated_at DESC, id DESC
    LIMIT 5
");
$stmtRecent->execute([$userId]);
$recentContacts =$stmtRecent->fetchAll();

// Tính % cho thanh tiến trình phân loại
$pctFamily =$total > 0 ? round($family * 100 / $total) : 0;
$pctFriends =$total > 0 ? round($friends * 100 / $total) : 0;
$pctWork =$total > 0 ? round($work * 100 / $total) : 0;
$pctOther = $total > 0 ? max(0, 100 - $pctFamily - $pctFriends -$pctWork) : 0;
?>

<!-- Header trang -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Tổng quan</h3>
        <p class="text-muted mb-0">Xin chào, <strong><?= htmlspecialchars($user['full_name']) ?></strong>! Chào mừng bạn quay trở lại.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/QuanLyDanhBaCaNhan/modules/contacts/list.php" class="btn btn-outline-secondary">
            <i class="bi bi-list-ul me-1"></i> Danh sách đầy đủ
        </a>
        <a href="/QuanLyDanhBaCaNhan/modules/contacts/list.php?open_add=1" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Thêm liên hệ
        </a>
    </div>
</div>

<!-- 4 thẻ thống kê -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Tổng liên hệ</div>
                    <h3 class="fw-bold mb-1"><?= $total ?></h3>
                    <small class="text-primary">Tất cả danh bạ của bạn</small>
                </div>
                <span class="bg-primary-subtle text-primary rounded-3 p-3 fs-4"><i class="bi bi-people"></i></span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Gia đình</div>
                    <h3 class="fw-bold mb-1"><?= $family ?></h3>
                    <small class="text-success">Nhóm người thân</small>
                </div>
                <span class="bg-success-subtle text-success rounded-3 p-3 fs-4"><i class="bi bi-house-heart"></i></span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Bạn bè</div>
                    <h3 class="fw-bold mb-1"><?= $friends ?></h3>
                    <small class="text-info">Bạn bè & quen biết</small>
                </div>
                <span class="bg-info-subtle text-info rounded-3 p-3 fs-4"><i class="bi bi-emoji-smile"></i></span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Công việc</div>
                    <h3 class="fw-bold mb-1"><?= $work ?></h3>
                    <small class="text-warning">Đồng nghiệp & đối tác</small>
                </div>
                <span class="bg-warning-subtle text-warning rounded-3 p-3 fs-4"><i class="bi bi-briefcase"></i></span>
            </div>
        </div>
    </div>
</div>

<!-- Liên hệ gần đây & Phân loại liên hệ -->
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <div>
                    <h6 class="fw-bold mb-0">Liên hệ gần đây</h6>
                    <small class="text-muted">Các liên hệ được thêm hoặc cập nhật gần đây</small>
                </div>
                <a href="/QuanLyDanhBaCaNhan/modules/contacts/list.php" class="small text-decoration-none">Xem tất cả →</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentContacts)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                        Chưa có liên hệ nào. Hãy bấm <strong>Thêm liên hệ</strong> để bắt đầu!
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentContacts as$c): ?>
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($c['full_name']) ?></div>
                                    <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($c['phone']) ?></small>
                                </div>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($c['group_name']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0">Phân loại liên hệ</h6>
                <small class="text-muted">Tỷ lệ liên hệ theo từng nhóm</small>
            </div>
            <div class="card-body">
                <?php if ($total === 0): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-pie-chart fs-2 d-block mb-2"></i>
                        Chưa có dữ liệu để thống kê nhóm.
                    </div>
                <?php else: ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Gia đình (<?= $family ?>)</span>
                            <strong><?= $pctFamily ?>%</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: <?= $pctFamily ?>%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Bạn bè (<?= $friends ?>)</span>
                            <strong><?= $pctFriends ?>%</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: <?= $pctFriends ?>%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Công việc (<?= $work ?>)</span>
                            <strong><?= $pctWork ?>%</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: <?= $pctWork ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Khác (<?= $other ?>)</span>
                            <strong><?= $pctOther ?>%</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-secondary" style="width: <?= $pctOther ?>%"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>