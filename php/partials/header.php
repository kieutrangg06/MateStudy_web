<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect nếu chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    //getcwd() → lấy thư mục hiện tại
    //basename() → lấy tên thư mục cuối
    //Nếu đang ở thư mục php → login ở cùng cấp
    //Nếu không → quay về ../login.php
    $redirect_login = (basename(getcwd()) === 'php') ? 'login.php' : '../login.php';
    header("Location: " . $redirect_login);
    exit();
}

$user_id = $_SESSION['user_id'];
$vai_tro = $_SESSION['vai_tro'] ?? 'sinh_vien';

// ===== LẤY THÔNG TIN USER =====
$query = "SELECT ten_dang_nhap, anh_dai_dien, email, nien_khoa FROM nguoi_dung WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$ten_hien_thi = htmlspecialchars($user['ten_dang_nhap']);
$is_admin = ($vai_tro === 'admin');

// Xác định đường dẫn root
$is_top_level_php = (basename(getcwd()) === 'php');
//File ở php/ → quay về 1 cấp, File ở php/sinhvien/ → quay về 2 cấp
$root_path = $is_top_level_php ? "../" : "../../";

// Xử lý avatar
if ($user['anh_dai_dien']) {
    $avatar_url = $root_path . htmlspecialchars($user['anh_dai_dien']);
} else {
    $avatar_url = $root_path . "images/avatars/avt.jpg";
}

// ===== ĐẾM THÔNG BÁO CHƯA ĐỌC (Chỉ cho sinh viên) =====
$unread_count = 0;
$badge_html = "";

if (!$is_admin) {
    $notif_query = "SELECT COUNT(*) AS unread FROM thong_bao WHERE sinh_vien_id = ? AND da_doc = FALSE";
    $stmt2 = mysqli_prepare($conn, $notif_query);
    mysqli_stmt_bind_param($stmt2, "i", $user_id);
    mysqli_stmt_execute($stmt2);
    $notif_result = mysqli_stmt_get_result($stmt2);
    $unread_count = mysqli_fetch_assoc($notif_result)['unread'];
    mysqli_stmt_close($stmt2);

    if ($unread_count > 0) {
        $badge_html = "<span class='badge'>$unread_count</span>";
    }
}
?>

<header class="header">
    <div class="top-bar">
        MATESTUDY – Mọi khoảnh khắc cùng sinh viên!
    </div>

    <div class="main-header">
        <a href="<?php echo $root_path; ?>php/home.php" class="logo">MATE<span>STUDY</span></a>

        <nav class="nav-menu">
            <?php if ($is_admin): ?>
                <a href="<?php echo $root_path; ?>php/admin/manage_blog.php"><i class='bx bx-conversation me-2'></i>Quản lý diễn đàn</a>
                <a href="<?php echo $root_path; ?>php/admin/manage_data_learing.php"><i class='bx bx-data me-2'></i>Quản lý dữ liệu học tập</a>
                <a href="<?php echo $root_path; ?>php/admin/manage_review.php"><i class='bx bx-star me-2'></i>Quản lý đánh giá</a>
                <a href="<?php echo $root_path; ?>php/admin/manage_user.php"><i class='bx bx-user me-2'></i>Quản lý người dùng</a>
            <?php else: ?>
                <a href="<?php echo $root_path; ?>php/sinhvien/blog.php"><i class='bx bx-conversation me-2'></i>Diễn đàn</a>
                <a href="<?php echo $root_path; ?>php/sinhvien/calender.php"><i class='bx bx-calendar me-2'></i>Thời gian biểu</a>
                <a href="<?php echo $root_path; ?>php/sinhvien/review.php"><i class='bx bx-star me-2'></i>Đánh giá</a>
                <a href="<?php echo $root_path; ?>php/sinhvien/team.php"><i class='bx bx-group me-2'></i>Nhóm học tập</a>
            <?php endif; ?>
        </nav>

        <!-- User actions -->
        <div class="<?= $is_admin ? 'admin-info dropdown' : 'user-actions' ?>">
            <?php if (!$is_admin): ?>
                <a href="<?php echo $root_path; ?>php/sinhvien/notification.php" class="notification" title="Thông báo">
                    <i class='bx bx-bell'></i>
                    <?= $badge_html ?>
                </a>
            <?php endif; ?>

            <div class="user-dropdown">
                <a href="#" class="dropdown-toggle d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown">
                    <img src="<?= $avatar_url ?>" alt="Avatar" class="user-avatar rounded-circle" width="38" height="38">
                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow-lg">

                    <li class="dropdown-header">
                        <strong><?= $ten_hien_thi ?></strong><br>
                        <small class="text-muted">
                            <?php
                            if ($is_admin) {
                                echo 'Quản trị viên';
                            } else {
                                echo 'Sinh viên';
                            }
                            ?>
                        </small>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <?php if (!$is_admin): ?>
                        <li>
                            <a class="dropdown-item" href="<?php echo $root_path; ?>php/sinhvien/account.php">
                                <i class='bx bx-user me-2'></i> Thông tin tài khoản
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo $root_path; ?>php/sinhvien/setPass.php">
                                <i class='bx bx-lock-alt me-2'></i> Đổi mật khẩu
                            </a>
                        </li>
                    <?php endif; ?>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item text-danger" href="<?php echo $root_path; ?>php/logout.php">
                            <i class='bx bx-log-out me-2'></i> Đăng xuất
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>

<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?php echo $root_path; ?>css/style.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>