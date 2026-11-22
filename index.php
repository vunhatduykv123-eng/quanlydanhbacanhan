<?php
require_once __DIR__ . '/config/db.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: /QuanLyDanhBaCaNhan/modules/dashboard.php');
    exit;
}

$errors = $_SESSION['auth_errors'] ?? [];
$old = $_SESSION['auth_old'] ?? [];
$success = $_SESSION['auth_success'] ?? '';
unset($_SESSION['auth_errors'], $_SESSION['auth_old'], $_SESSION['auth_success']);

$auth = $_GET['auth'] ?? '';
$showModal = ($auth === 'login' || $auth === 'register' || !empty($errors) || $success !== '');
$tab = ($auth === 'register' || isset($old['full_name'])) ? 'register' : 'login';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Bạ Cá Nhân - Quản lý liên hệ thông minh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        html { scroll-behavior: smooth; }
        body { background-color: #f8fafc; color: #1e293b; }
        .hero-box { background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); border-radius: 16px; }
        .feature-card { border: 1px solid #e2e8f0; border-radius: 12px; transition: 0.2s; }
        .feature-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); }
    </style>
</head>
<body>

<!-- Thanh menu đầu trang -->
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="#top">
            <i class="bi bi-person-lines-fill me-1"></i> Danh Bạ Cá Nhân
            <small class="d-block text-muted fw-normal" style="font-size: 12px;">Quản lý liên hệ thông minh</small>
        </a>
        <div class="collapse navbar-collapse justify-content-center">
            <ul class="navbar-nav gap-3">
                <li class="nav-item"><a class="nav-link" href="#top">Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link" href="#features">Tính năng</a></li>
                <li class="nav-item"><a class="nav-link" href="#about">Giới thiệu</a></li>
            </ul>
        </div>
        <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#authModal" onclick="showTab('login')">
            Đăng nhập <i class="bi bi-arrow-right"></i>
        </button>
    </div>
</nav>

<!-- Hero Banner -->
<section id="top" class="container my-5">
    <div class="hero-box p-4 p-md-5 border">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <span class="badge bg-primary-subtle text-primary mb-2 px-3 py-2">QUẢN LÝ DANH BẠ CÁ NHÂN</span>
                <h1 class="display-5 fw-bold mb-3">Quản lý danh bạ cá nhân <span class="text-primary">Đơn giản và hiệu quả</span></h1>
                <p class="text-muted mb-4">
                    Lưu trữ và quản lý thông tin liên hệ một cách khoa học. Tìm kiếm nhanh chóng và kết nối với mọi người dễ dàng hơn.
                </p>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#authModal" onclick="showTab('register')">
                        Bắt đầu sử dụng <i class="bi bi-arrow-right"></i>
                    </button>
                    <a href="#features" class="btn btn-outline-secondary btn-lg">Khám phá tính năng</a>
                </div>
                <div class="d-flex gap-4 text-muted small">
                    <span><i class="bi bi-check-circle-fill text-success"></i> Dễ sử dụng</span>
                    <span><i class="bi bi-shield-check text-primary"></i> Bảo mật an toàn</span>
                    <span><i class="bi bi-globe text-info"></i> Truy cập mọi nơi</span>
                </div>
            </div>

            <!-- Khối minh họa thông tin ẩn -->
            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Danh bạ của tôi</h6>
                                <div class="d-flex align-items-center gap-2 border-bottom pb-2 mb-2">
                                    <span class="badge rounded-circle bg-primary p-2">LH</span>
                                    <span class="text-muted">Liên hệ #01</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 border-bottom pb-2 mb-2">
                                    <span class="badge rounded-circle bg-success p-2">LH</span>
                                    <span class="text-muted">Liên hệ #02</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge rounded-circle bg-warning text-dark p-2">LH</span>
                                    <span class="text-muted">Liên hệ #03</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="text-center mb-2">
                                    <i class="bi bi-person-circle fs-1 text-primary"></i>
                                    <h6 class="fw-bold mt-1 mb-0">Thông tin liên hệ</h6>
                                    <small class="text-muted">Liên hệ #01 • Nhóm riêng tư</small>
                                </div>
                                <ul class="list-unstyled small text-muted mt-3 mb-0">
                                    <li class="mb-1"><i class="bi bi-telephone me-1"></i> 09xx xxx xxx</li>
                                    <li class="mb-1"><i class="bi bi-envelope me-1"></i> ••••••@example.com</li>
                                    <li><i class="bi bi-geo-alt me-1"></i> Thông tin được ẩn</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Tính năng nổi bật -->
<section id="features" class="container my-5 py-3">
    <div class="text-center mb-5">
        <span class="text-primary fw-bold small">TÍNH NĂNG NỔI BẬT</span>
        <h2 class="fw-bold mt-1">Mọi thứ bạn cần để quản lý danh bạ</h2>
        <p class="text-muted">Tập hợp những công cụ hữu ích giúp kết nối dễ dàng hơn.</p>
    </div>
    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <div class="card feature-card h-100 p-3">
                <div class="card-body">
                    <i class="bi bi-journal-bookmark fs-2 text-primary"></i>
                    <h5 class="fw-bold mt-3">Quản lý liên hệ</h5>
                    <p class="text-muted small mb-0">Lưu trữ và quản lý thông tin liên hệ.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card feature-card h-100 p-3">
                <div class="card-body">
                    <i class="bi bi-search fs-2 text-primary"></i>
                    <h5 class="fw-bold mt-3">Tìm kiếm nhanh</h5>
                    <p class="text-muted small mb-0">Nhanh chóng tìm kiếm liên hệ dễ dàng.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card feature-card h-100 p-3">
                <div class="card-body">
                    <i class="bi bi-diagram-3 fs-2 text-primary"></i>
                    <h5 class="fw-bold mt-3">Phân loại linh hoạt</h5>
                    <p class="text-muted small mb-0">Sắp xếp liên hệ theo Gia đình, Bạn bè, Công việc và nhiều hơn.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card feature-card h-100 p-3">
                <div class="card-body">
                    <i class="bi bi-shield-lock fs-2 text-primary"></i>
                    <h5 class="fw-bold mt-3">Quản lý riêng tư</h5>
                    <p class="text-muted small mb-0">Bảo vệ thông tin cá nhân của bạn với hệ thống bảo mật.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer CTA -->
<section id="about" class="container my-5">
    <div class="bg-primary text-white text-center rounded-4 p-5">
        <h3 class="fw-bold">Quản lý các mối liên hệ dễ dàng hơn</h3>
        <p class="mb-4">Danh Bạ Cá Nhân giúp bạn lưu trữ, tìm kiếm và kết nối mọi người chỉ trong vài thao tác.</p>
        <button class="btn btn-light text-primary fw-bold px-4" data-bs-toggle="modal" data-bs-target="#authModal" onclick="showTab('register')">
            Bắt đầu ngay <i class="bi bi-arrow-right"></i>
        </button>
    </div>
</section>

<!-- Modal Đăng nhập / Đăng ký -->
<div class="modal fade" id="authModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold text-primary">Danh Bạ Cá Nhân</h5>
                    <small class="text-muted">Quản lý liên hệ thông minh</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <ul class="nav nav-pills nav-fill bg-light p-1 rounded-3 mb-3">
                    <li class="nav-item">
                        <button type="button" class="nav-link <?= $tab === 'login' ? 'active' : '' ?>" id="btnTabLogin" onclick="showTab('login')">Đăng nhập</button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link <?= $tab === 'register' ? 'active' : '' ?>" id="btnTabRegister" onclick="showTab('register')">Đăng ký</button>
                    </li>
                </ul>

                <?php if ($success !== ''): ?>
                    <div class="alert alert-success py-2 small"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <!-- Form Đăng nhập -->
                <form id="formLogin" action="/QuanLyDanhBaCaNhan/modules/auth/login.php" method="POST" style="display: <?= $tab === 'login' ? 'block' : 'none' ?>;">
                    <div class="text-center mb-3">
                        <h5 class="fw-bold mb-1">Chào mừng trở lại!</h5>
                        <small class="text-muted">Đăng nhập để tiếp tục sử dụng ứng dụng.</small>
                    </div>

                    <?php if (!empty($errors['login'])): ?>
                        <div class="alert alert-danger py-2 small"><?= htmlspecialchars($errors['login']) ?></div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email hoặc số điện thoại</label>
                        <input type="text" name="login_id" class="form-control" placeholder="Nhập email hoặc số điện thoại" value="<?= htmlspecialchars($old['login_id'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Mật khẩu</label>
                        <div class="input-group">
                            <input type="password" id="loginPass" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePass('loginPass')"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3 small">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="rememberCheck">
                            <label class="form-check-label" for="rememberCheck">Ghi nhớ đăng nhập</label>
                        </div>
                        <a href="#" onclick="alert('Vui lòng liên hệ quản trị viên để cấp lại mật khẩu.'); return false;">Quên mật khẩu?</a>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold">Đăng nhập →</button>
                    <p class="text-center small mt-3 mb-0">
                        Chưa có tài khoản? <a href="#" onclick="showTab('register'); return false;">Đăng ký ngay</a>
                    </p>
                </form>

                <!-- Form Đăng ký -->
                <form id="formRegister" action="/QuanLyDanhBaCaNhan/modules/auth/register.php" method="POST" style="display: <?= $tab === 'register' ? 'block' : 'none' ?>;">
                    <div class="text-center mb-3">
                        <h5 class="fw-bold mb-1">Tạo tài khoản mới</h5>
                        <small class="text-muted">Bắt đầu quản lý danh bạ của riêng bạn.</small>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Họ và tên</label>
                        <input type="text" name="full_name" class="form-control" placeholder="Nhập họ và tên đầy đủ" value="<?= htmlspecialchars($old['full_name'] ?? '') ?>" required>
                        <?php if (!empty($errors['full_name'])): ?><small class="text-danger"><?= $errors['full_name'] ?></small><?php endif; ?>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" placeholder="VD: 0901234567" value="<?= htmlspecialchars($old['phone'] ?? '') ?>" required>
                        <?php if (!empty($errors['phone'])): ?><small class="text-danger"><?= $errors['phone'] ?></small><?php endif; ?>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Địa chỉ email</label>
                        <input type="email" name="email" class="form-control" placeholder="Nhập địa chỉ email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                        <?php if (!empty($errors['email'])): ?><small class="text-danger"><?= $errors['email'] ?></small><?php endif; ?>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Mật khẩu</label>
                        <div class="input-group">
                            <input type="password" id="regPass" name="password" class="form-control" placeholder="Từ 6–32 ký tự" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePass('regPass')"><i class="bi bi-eye"></i></button>
                        </div>
                        <?php if (!empty($errors['password'])): ?><small class="text-danger"><?= $errors['password'] ?></small><?php endif; ?>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Xác nhận mật khẩu</label>
                        <div class="input-group">
                            <input type="password" id="regConfirmPass" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePass('regConfirmPass')"><i class="bi bi-eye"></i></button>
                        </div>
                        <?php if (!empty($errors['confirm_password'])): ?><small class="text-danger"><?= $errors['confirm_password'] ?></small><?php endif; ?>
                    </div>

                    <div class="form-check mb-3 small">
                        <input class="form-check-input" type="checkbox" name="agree_terms" id="agreeTerms" required>
                        <label class="form-check-label" for="agreeTerms">Tôi đồng ý với điều khoản sử dụng và chính sách bảo mật.</label>
                        <?php if (!empty($errors['agree_terms'])): ?><div class="text-danger"><?= $errors['agree_terms'] ?></div><?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold">Đăng ký tài khoản →</button>
                    <p class="text-center small mt-3 mb-0">
                        Đã có tài khoản? <a href="#" onclick="showTab('login'); return false;">Đăng nhập</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showTab(tab) {
    document.getElementById('formLogin').style.display = (tab === 'login') ? 'block' : 'none';
    document.getElementById('formRegister').style.display = (tab === 'register') ? 'block' : 'none';
    document.getElementById('btnTabLogin').classList.toggle('active', tab === 'login');
    document.getElementById('btnTabRegister').classList.toggle('active', tab === 'register');
}

function togglePass(id) {
    var input = document.getElementById(id);
    input.type = (input.type === 'password') ? 'text' : 'password';
}

<?php if ($showModal): ?>
window.addEventListener('DOMContentLoaded', function() {
    var myModal = new bootstrap.Modal(document.getElementById('authModal'));
    myModal.show();
});
<?php endif; ?>
</script>
</body>
</html>