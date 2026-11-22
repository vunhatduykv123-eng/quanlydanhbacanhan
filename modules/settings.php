<?php
$pageTitle = 'Cài đặt tài khoản - Danh Bạ Cá Nhân';
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];
$msg = '';
$err = '';
$activeTab = $_POST['tab'] ?? ($_GET['tab'] ?? 'profile');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Cập nhật thông tin tài khoản & ảnh đại diện
    if ($action === 'update_profile') {
        $activeTab = 'profile';
        $fullName = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $birthday = trim($_POST['birthday'] ?? '');

        if ($fullName == '') {
            $err = 'Họ và tên không được để trống.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{4,30}$/', $username)) {
            $err = 'Tên đăng nhập gồm 4–30 ký tự chữ, số hoặc gạch dưới (_).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err = 'Email không hợp lệ.';
        } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
            $err = 'Số điện thoại phải từ 10 đến 11 chữ số.';
        } else {
            // Kiểm tra trùng email/username/phone với người khác
            $chk = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR username = ? OR phone = ?) AND id != ? LIMIT 1");
            $chk->execute([$email, $username, $phone, $userId]);
            if ($chk->fetch()) {
                $err = 'Email, tên đăng nhập hoặc số điện thoại đã được dùng bởi tài khoản khác.';
            }
        }

        $avatarPath = $user['avatar'];
        if ($err === '' && !empty($_FILES['avatar']['name'])) {
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) {
                $err = 'Chỉ hỗ trợ ảnh định dạng JPG, JPEG, PNG, GIF, WEBP.';
            } elseif ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
                $err = 'Dung lượng ảnh đại diện tối đa 2MB.';
            } else {
                $fileName = 'avatar_' . $userId . '_' . time() . '.' . $ext;
                $dest = __DIR__ . '/../assets/uploads/' . $fileName;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
                    $avatarPath = '/QuanLyDanhBaCaNhan/assets/uploads/' . $fileName;
                } else {
                    $err = 'Không thể lưu ảnh tải lên.';
                }
            }
        }

        if ($err === '') {
            $upd = $pdo->prepare("
                UPDATE users
                SET full_name = ?, username = ?, email = ?, phone = ?, address = ?, birthday = ?, avatar = ?
                WHERE id = ?
            ");
            $upd->execute([
                $fullName,
                $username,
                $email,
                $phone,
                $address !== '' ? $address : null,
                $birthday !== '' ? $birthday : null,
                $avatarPath,
                $userId
            ]);
            $_SESSION['user_name'] = $fullName;
            $user = current_user($pdo);
            $msg = 'Đã cập nhật thông tin tài khoản thành công!';
        }
    }

    // 2. Cập nhật thiết lập bảo mật
    if ($action === 'update_security') {
        $activeTab = 'security';
        $twoFactor = isset($_POST['two_factor_enabled']) ? 1 : 0;
        $loginAlert = isset($_POST['login_alert_enabled']) ? 1 : 0;

        $upd = $pdo->prepare("UPDATE users SET two_factor_enabled = ?, login_alert_enabled = ? WHERE id = ?");
        $upd->execute([$twoFactor, $loginAlert, $userId]);
        $user = current_user($pdo);
        $msg = 'Đã lưu thiết lập bảo mật tài khoản!';
    }

    // 3. Đổi mật khẩu
    if ($action === 'change_password') {
        $activeTab = 'password';
        $currentPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        $stmtPass = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmtPass->execute([$userId]);
        $rowPass = $stmtPass->fetch();

        if (!$rowPass || !password_verify($currentPass, $rowPass['password_hash'])) {
            $err = 'Mật khẩu hiện tại không đúng.';
        } elseif (strlen($newPass) < 6 || strlen($newPass) > 32) {
            $err = 'Mật khẩu mới phải từ 6 đến 32 ký tự.';
        } elseif ($newPass !== $confirmPass) {
            $err = 'Xác nhận mật khẩu mới không trùng khớp.';
        } else {
            $newHash = password_hash($newPass, PASSWORD_DEFAULT);
            $upd = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $upd->execute([$newHash, $userId]);
            $msg = 'Đổi mật khẩu thành công!';
        }
    }
}

$avatarSrc = !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : '';
?>

<div class="mb-4">
    <h3 class="fw-bold mb-1">Cài đặt tài khoản</h3>
    <p class="text-muted mb-0">Quản lý thông tin hồ sơ, tùy chọn bảo mật và thay đổi mật khẩu tài khoản của bạn.</p>
</div>

<?php if ($msg !== ''): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($err !== ''): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($err) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <button class="nav-link <?= $activeTab === 'profile' ? 'active fw-semibold' : '' ?>" data-bs-toggle="tab" data-bs-target="#tabProfile" type="button">
            <i class="bi bi-person me-1"></i> Thông tin tài khoản
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link <?= $activeTab === 'security' ? 'active fw-semibold' : '' ?>" data-bs-toggle="tab" data-bs-target="#tabSecurity" type="button">
            <i class="bi bi-shield-lock me-1"></i> Bảo mật tài khoản
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link <?= $activeTab === 'password' ? 'active fw-semibold' : '' ?>" data-bs-toggle="tab" data-bs-target="#tabPassword" type="button">
            <i class="bi bi-key me-1"></i> Đổi mật khẩu
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- Tab 1: Thông tin tài khoản -->
    <div class="tab-pane fade <?= $activeTab === 'profile' ? 'show active' : '' ?>" id="tabProfile">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_profile">
                    <input type="hidden" name="tab" value="profile">

                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <?php if ($avatarSrc !== ''): ?>
                            <img src="<?= $avatarSrc ?>" alt="Avatar" class="rounded-circle object-fit-cover border" width="72" height="72">
                        <?php else: ?>
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width:72px;height:72px;">
                                <?= strtoupper(mb_substr($user['full_name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <label class="btn btn-sm btn-outline-primary mb-1">
                                <i class="bi bi-upload me-1"></i> Tải ảnh đại diện lên
                                <input type="file" name="avatar" accept="image/*" hidden>
                            </label>
                            <div class="text-muted small">Hỗ trợ JPG, PNG, GIF, WEBP (tối đa 2MB).</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Họ và tên</label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tên đăng nhập</label>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Địa chỉ email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Ngày sinh</label>
                            <input type="date" name="birthday" class="form-control" value="<?= htmlspecialchars($user['birthday'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Địa chỉ</label>
                            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Nhập địa chỉ">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2 me-1"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tab 2: Bảo mật tài khoản -->
    <div class="tab-pane fade <?= $activeTab === 'security' ? 'show active' : '' ?>" id="tabSecurity">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="action" value="update_security">
                    <input type="hidden" name="tab" value="security">

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="twoFactor" name="two_factor_enabled" <?= !empty($user['two_factor_enabled']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="twoFactor">Xác thực hai yếu tố (2FA)</label>
                        <div class="text-muted small">Tăng thêm một lớp bảo vệ khi đăng nhập tài khoản.</div>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="loginAlert" name="login_alert_enabled" <?= !empty($user['login_alert_enabled']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="loginAlert">Cảnh báo đăng nhập bất thường</label>
                        <div class="text-muted small">Thông báo khi có phiên đăng nhập mới vào tài khoản.</div>
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-4 small">
                        <div class="fw-bold mb-1">Phiên đăng nhập hiện tại</div>
                        <div class="text-muted">Trình duyệt cục bộ • Đang hoạt động</div>
                    </div>

                    <button type="submit" class="btn btn-primary">Lưu thiết lập bảo mật</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tab 3: Đổi mật khẩu -->
    <div class="tab-pane fade <?= $activeTab === 'password' ? 'show active' : '' ?>" id="tabPassword">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4" style="max-width: 520px;">
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" name="tab" value="password">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Mật khẩu hiện tại</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Mật khẩu mới (6–32 ký tự)</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Xác nhận mật khẩu mới</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Cập nhật mật khẩu</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>