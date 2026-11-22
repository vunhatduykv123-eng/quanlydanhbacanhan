<?php
$pageTitle = 'Danh bạ - Danh Bạ Cá Nhân';
require_once __DIR__ . '/../../includes/header.php';

$userId = $_SESSION['user_id'];
$q = trim($_GET['q'] ?? '');
$group = trim($_GET['group'] ?? 'Tất cả');
$openAdd = isset($_GET['open_add']);

$allowedGroups = ['Tất cả', 'Gia đình', 'Bạn bè', 'Công việc', 'Khác'];
if (!in_array($group, $allowedGroups, true)) {
    $group = 'Tất cả';
}

$sql = "SELECT * FROM contacts WHERE user_id = ?";
$params = [$userId];

if ($group !== 'Tất cả') {
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
$contacts = $stmt->fetchAll();

$successMsg = $_SESSION['contact_success'] ?? '';
$errorMsg = $_SESSION['contact_error'] ?? '';
unset($_SESSION['contact_success'], $_SESSION['contact_error']);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Danh bạ</h3>
        <p class="text-muted mb-0">Quản lý và tìm kiếm thông tin liên hệ cá nhân (<?= count($contacts) ?> kết quả).</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contactModal" onclick="openAddModal()">
        <i class="bi bi-plus-lg me-1"></i> Thêm liên hệ
    </button>
</div>

<?php if ($successMsg !== ''): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($successMsg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($errorMsg !== ''): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($errorMsg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Bộ lọc & Tìm kiếm -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" id="searchInput" class="form-control"
                           placeholder="Tìm theo tên, số điện thoại hoặc email..."
                           value="<?= htmlspecialchars($q) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <select name="group" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($allowedGroups as $g): ?>
                        <option value="<?= htmlspecialchars($g) ?>" <?= $group === $g ? 'selected' : '' ?>>
                            <?= $g === 'Tất cả' ? 'Tất cả nhóm' : htmlspecialchars($g) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary w-100">Lọc</button>
                <?php if ($q !== '' || $group !== 'Tất cả'): ?>
                    <a href="/QuanLyDanhBaCaNhan/modules/contacts/list.php" class="btn btn-light border" title="Xóa lọc">✕</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Bảng danh sách liên hệ -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="contactsTable">
            <thead class="table-light">
                <tr>
                    <th>Họ và tên</th>
                    <th>Số điện thoại</th>
                    <th>Email</th>
                    <th>Địa chỉ</th>
                    <th>Nhóm</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contacts)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Không tìm thấy liên hệ nào phù hợp.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($contacts as $c): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($c['full_name']) ?></td>
                            <td><?= htmlspecialchars($c['phone']) ?></td>
                            <td><?= htmlspecialchars($c['email'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($c['address'] ?? '—') ?></td>
                            <td><span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($c['group_name']) ?></span></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        data-bs-toggle="modal" data-bs-target="#contactModal"
                                        onclick='openEditModal(<?= json_encode($c, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                    <i class="bi bi-pencil"></i> Sửa
                                </button>
                                <a href="/QuanLyDanhBaCaNhan/modules/contacts/delete.php?id=<?= (int)$c['id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa liên hệ <?= htmlspecialchars($c['full_name'], ENT_QUOTES) ?> không?');">
                                    <i class="bi bi-trash"></i> Xóa
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Thêm / Sửa liên hệ -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <form id="contactForm" action="/QuanLyDanhBaCaNhan/modules/contacts/add.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="contactModalTitle">Thêm liên hệ mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="contactId">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" id="contactName" class="form-control" placeholder="VD: Nguyễn Văn A" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="text" name="phone" id="contactPhone" class="form-control" placeholder="VD: 0901234567" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" name="email" id="contactEmail" class="form-control" placeholder="VD: example@email.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Địa chỉ</label>
                        <input type="text" name="address" id="contactAddress" class="form-control" placeholder="VD: TP. Hồ Chí Minh">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Nhóm phân loại</label>
                        <select name="group_name" id="contactGroup" class="form-select">
                            <option value="Gia đình">Gia đình</option>
                            <option value="Bạn bè">Bạn bè</option>
                            <option value="Công việc">Công việc</option>
                            <option value="Khác" selected>Khác</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="contactSubmitBtn">Lưu liên hệ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('contactModalTitle').innerText = 'Thêm liên hệ mới';
    document.getElementById('contactForm').action = '/QuanLyDanhBaCaNhan/modules/contacts/add.php';
    document.getElementById('contactId').value = '';
    document.getElementById('contactName').value = '';
    document.getElementById('contactPhone').value = '';
    document.getElementById('contactEmail').value = '';
    document.getElementById('contactAddress').value = '';
    document.getElementById('contactGroup').value = 'Khác';
    document.getElementById('contactSubmitBtn').innerText = 'Lưu liên hệ';
}

function openEditModal(item) {
    document.getElementById('contactModalTitle').innerText = 'Chỉnh sửa liên hệ';
    document.getElementById('contactForm').action = '/QuanLyDanhBaCaNhan/modules/contacts/edit.php';
    document.getElementById('contactId').value = item.id;
    document.getElementById('contactName').value = item.full_name || '';
    document.getElementById('contactPhone').value = item.phone || '';
    document.getElementById('contactEmail').value = item.email || '';
    document.getElementById('contactAddress').value = item.address || '';
    document.getElementById('contactGroup').value = item.group_name || 'Khác';
    document.getElementById('contactSubmitBtn').innerText = 'Cập nhật';
}

<?php if ($openAdd): ?>
window.addEventListener('DOMContentLoaded', function() {
    openAddModal();
    var m = new bootstrap.Modal(document.getElementById('contactModal'));
    m.show();
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>