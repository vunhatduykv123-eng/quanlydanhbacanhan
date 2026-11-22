<?php
$pageTitle = 'Nhóm liên hệ - Danh Bạ Cá Nhân';
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT group_name, COUNT(*) AS cnt
    FROM contacts
    WHERE user_id = ?
    GROUP BY group_name
");
$stmt->execute([$userId]);
$counts = [];
while ($row = $stmt->fetch()) {
    $counts[$row['group_name']] = (int)$row['cnt'];
}

$groups = [
    [
        'name' => 'Gia đình',
        'desc' => 'Người thân trong gia đình và họ hàng.',
        'icon' => 'bi-house-heart',
        'color' => 'success'
    ],
    [
        'name' => 'Bạn bè',
        'desc' => 'Bạn bè, bạn học và những người quen thân thiết.',
        'icon' => 'bi-emoji-smile',
        'color' => 'info'
    ],
    [
        'name' => 'Công việc',
        'desc' => 'Đồng nghiệp, khách hàng và đối tác làm việc.',
        'icon' => 'bi-briefcase',
        'color' => 'warning'
    ],
    [
        'name' => 'Khác',
        'desc' => 'Các liên hệ bổ sung hoặc chưa phân vào nhóm khác.',
        'icon' => 'bi-tag',
        'color' => 'secondary'
    ]
];
?>

<div class="mb-4">
    <h3 class="fw-bold mb-1">Nhóm liên hệ</h3>
    <p class="text-muted mb-0">Xem và quản lý danh bạ theo từng nhóm phân loại.</p>
</div>

<div class="row g-4">
    <?php foreach ($groups as $g):
        $cnt = $counts[$g['name']] ?? 0;
        $c = $g['color'];
    ?>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="bg-<?= $c ?>-subtle text-<?= $c ?> rounded-3 p-3 fs-4">
                                <i class="bi <?= $g['icon'] ?>"></i>
                            </span>
                            <span class="badge bg-<?= $c ?>-subtle text-<?= $c ?> fs-6 px-3 py-2">
                                <?= $cnt ?> liên hệ
                            </span>
                        </div>
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($g['name']) ?></h5>
                        <p class="text-muted small mb-4"><?= htmlspecialchars($g['desc']) ?></p>
                    </div>

                    <div class="border-top pt-3 d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Nhóm mặc định</span>
                        <a href="/QuanLyDanhBaCaNhan/modules/contacts/list.php?group=<?= urlencode($g['name']) ?>" class="btn btn-sm btn-outline-primary">
                            Xem liên hệ <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>