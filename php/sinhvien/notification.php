<?php
require_once('../config.php');
require_permission([ROLE_STUDENT], '../login.php');

$user_id = $_SESSION['user_id'];
$bo_loc_hien_tai = $_GET['filter'] ?? 'all';

// Hàm định dạng thời gian hiển thị (dd/mm/yyyy HH:MM)
function dinh_dang_thoi_gian($datetime)
{
  return date("d/m/Y H:i", strtotime($datetime));
}

// Hàm xử lý khi click vào một thông báo:
// - Đánh dấu thông báo đó là đã đọc
// - Chuyển hướng đến trang liên quan (nếu có)
function xu_ly_chuyen_huong($conn, $id_thong_bao, $user_id, $id_lien_quan, $loai_lien_quan)
{
  // Đánh dấu đã đọc
  $sql_danh_dau_da_doc = "UPDATE thong_bao SET da_doc = 1 WHERE id = ? AND sinh_vien_id = ?";
  $stmt = mysqli_prepare($conn, $sql_danh_dau_da_doc);
  mysqli_stmt_bind_param($stmt, "ii", $id_thong_bao, $user_id);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  // Chuyển hướng theo loại liên quan
  if ($id_lien_quan && $loai_lien_quan) {
    if ($loai_lien_quan === 'bai_viet') {
      header("Location: blog_detail.php?id=" . $id_lien_quan);
      exit();
    } elseif ($loai_lien_quan === 'danh_gia') {
      header("Location: review_detail.php?id=" . $id_lien_quan);
      exit();
    } elseif ($loai_lien_quan === 'nhom_hoc_tap') {
      header("Location: team.php?group=" . $id_lien_quan);
      exit();
    } elseif ($loai_lien_quan === 'cong_viec_nhom') {
      // Tìm nhóm học tập chứa công việc nhóm
      $sql_tim_nhom = "SELECT nhom_id FROM cong_viec_nhom WHERE id = " . (int)$id_lien_quan;
      $ket_qua_tim_nhom = mysqli_query($conn, $sql_tim_nhom);
      $cong_viec = mysqli_fetch_assoc($ket_qua_tim_nhom);
      if ($cong_viec) {
        header("Location: team.php?group=" . $cong_viec['nhom_id']);
        exit();
      }
    }
  }
  header("Location: notification.php");
  exit();
}

// ===== XỬ LÝ HÀNH ĐỘNG TỪ URL =====
if (isset($_GET['action'])) {
  $hanh_dong = $_GET['action'];

  // Đánh dấu tất cả thông báo của user hiện tại là đã đọc
  if ($hanh_dong === 'mark_all') {
    $sql_danh_dau_tat_ca = "UPDATE thong_bao SET da_doc = 1 WHERE sinh_vien_id = $user_id";
    mysqli_query($conn, $sql_danh_dau_tat_ca);
    header("Location: notification.php");
    exit();
  }

  // Đánh dấu một thông báo cụ thể và chuyển hướng (nếu có link)
  if ($hanh_dong === 'mark_one' && isset($_GET['notif_id'])) {
    $id_thong_bao = (int)$_GET['notif_id'];
    $id_lien_quan = $_GET['lien_quan_id'] ?? null;
    $loai_lien_quan = $_GET['lien_quan_loai'] ?? null;
    xu_ly_chuyen_huong($conn, $id_thong_bao, $user_id, $id_lien_quan, $loai_lien_quan);
  }
}

// ===== LẤY DANH SÁCH THÔNG BÁO THEO BỘ LỌC =====
$dieu_kien_where = "WHERE sinh_vien_id = $user_id";
$cac_bo_loc_hop_le = ['hoc_tap', 'bai_viet'];

// Nếu filter hợp lệ thì thêm điều kiện loại thông báo
if ($bo_loc_hien_tai !== 'all' && in_array($bo_loc_hien_tai, $cac_bo_loc_hop_le)) {
  $bo_loc_an_toan = mysqli_real_escape_string($conn, $bo_loc_hien_tai);
  $dieu_kien_where .= " AND loai = '$bo_loc_an_toan'";
} elseif ($bo_loc_hien_tai !== 'all') {
  $bo_loc_hien_tai = 'all';
}

$sql_lay_thong_bao = "SELECT * FROM thong_bao $dieu_kien_where ORDER BY ngay_tao DESC";
$ket_qua_thong_bao = mysqli_query($conn, $sql_lay_thong_bao);
$danh_sach_thong_bao = mysqli_fetch_all($ket_qua_thong_bao, MYSQLI_ASSOC);

// ===== ĐẾM SỐ LƯỢNG THÔNG BÁO CHƯA ĐỌC CHO TỪNG TAB =====
$sql_dem_thong_bao = "SELECT loai, da_doc FROM thong_bao WHERE sinh_vien_id = $user_id";
$ket_qua_dem = mysqli_query($conn, $sql_dem_thong_bao);

$so_luong_chua_doc = [
  'all' => 0,
  'hoc_tap' => 0,
  'bai_viet' => 0
];

while ($thong_bao = mysqli_fetch_assoc($ket_qua_dem)) {
  if (!$thong_bao['da_doc']) {
    $so_luong_chua_doc['all']++;
    $loai = $thong_bao['loai'] ?? null;
    if ($loai && isset($so_luong_chua_doc[$loai])) {
      $so_luong_chua_doc[$loai]++;
    }
  }
}

// ===== CẤU HÌNH ICON VÀ MÀU SẮC CHO TỪNG LOẠI THÔNG BÁO =====
$mang_icon = [
  'hoc_tap' => 'bx-calendar-check',
  'bai_viet' => 'bx-message-square-dots'
];

$mang_mau_sac = [
  'hoc_tap' => 'bg-primary-subtle text-primary',
  'bai_viet' => 'bg-success-subtle text-success'
];
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thông báo - MATESTUDY</title>
  <link rel="icon" type="image/png" href="../../images/logo.png">
  <link rel="stylesheet" href="../../css/header-footer.css">
  <link rel="stylesheet" href="../../css/notification.css">
  <link rel="stylesheet" href="../../css/style.css">
</head>

<body>
  <!-- Header chung -->
  <?php include("../partials/header.php"); ?>

  <div class="container mt-4">
    <!-- Tiêu đề trang + nút đánh dấu tất cả đã đọc -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">Thông báo</h4>
      <?php if ($so_luong_chua_doc['all'] > 0): ?>
        <a href="notification.php?action=mark_all" class="btn btn-outline-primary btn-sm">
          Đánh dấu tất cả đã đọc
        </a>
      <?php endif; ?>
    </div>

    <!-- Tabs bộ lọc -->
    <div class="tabs mb-4">
      <a href="notification.php?filter=all" class="tab <?= $bo_loc_hien_tai === 'all' ? 'active' : ''; ?>">
        Tất cả <span class="count"><?= $so_luong_chua_doc['all']; ?></span>
      </a>
      <a href="notification.php?filter=hoc_tap" class="tab <?= $bo_loc_hien_tai === 'hoc_tap' ? 'active' : ''; ?>">
        Học tập <span class="count"><?= $so_luong_chua_doc['hoc_tap']; ?></span>
      </a>
      <a href="notification.php?filter=bai_viet" class="tab <?= $bo_loc_hien_tai === 'bai_viet' ? 'active' : ''; ?>">
        Bài viết & Đánh giá <span class="count"><?= $so_luong_chua_doc['bai_viet']; ?></span>
      </a>
    </div>

    <!-- Danh sách thông báo -->
    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <div class="notification-list">
          <?php if (empty($danh_sach_thong_bao)): ?>
            <!-- Trường hợp không có thông báo -->
            <div class="text-center py-5 text-muted">
              <i class='bx bx-bell-off' style="font-size: 4rem; opacity: 0.3;"></i>
              <p class="mt-3">
                Chưa có thông báo nào <?= $bo_loc_hien_tai !== 'all' ? 'trong mục này' : ''; ?>
              </p>
            </div>
          <?php else: ?>
            <?php foreach ($danh_sach_thong_bao as $tb):
              $chua_doc = !$tb['da_doc'];                                      // Kiểm tra chưa đọc
              $icon = $mang_icon[$tb['loai']] ?? 'bx-bell';                    // Icon theo loại
              $mau_sac = $mang_mau_sac[$tb['loai']] ?? 'bg-secondary-subtle text-secondary';

              // Xây dựng link: nếu chưa đọc thì đi qua action mark_one để đánh dấu trước, nếu đã đọc thì đi thẳng đến trang liên quan
              $link_thong_bao = "notification.php?action=mark_one&notif_id={$tb['id']}";
              if ($tb['id_lien_quan'] && $tb['loai_lien_quan']) {
                $link_thong_bao .= "&lien_quan_id={$tb['id_lien_quan']}&lien_quan_loai={$tb['loai_lien_quan']}";

                // Nếu đã đọc thì cho phép đi thẳng đến trang liên quan
                if (!$chua_doc) {
                  if ($tb['loai_lien_quan'] === 'bai_viet') {
                    $link_thong_bao = "blog_detail.php?id={$tb['id_lien_quan']}";
                  } elseif ($tb['loai_lien_quan'] === 'danh_gia') {
                    $link_thong_bao = "review_detail.php?id={$tb['id_lien_quan']}";
                  } elseif ($tb['loai_lien_quan'] === 'nhom_hoc_tap') {
                    $link_thong_bao = "team.php?group={$tb['id_lien_quan']}";
                  } elseif ($tb['loai_lien_quan'] === 'cong_viec_nhom') {
                    $sql_tim_nhom_cv = "SELECT nhom_id FROM cong_viec_nhom WHERE id = {$tb['id_lien_quan']}";
                    $ket_qua_nhom_cv = mysqli_query($conn, $sql_tim_nhom_cv);
                    $cong_viec_data = mysqli_fetch_assoc($ket_qua_nhom_cv);
                    if ($cong_viec_data) {
                      $link_thong_bao = "team.php?group={$cong_viec_data['nhom_id']}";
                    }
                  }
                }
              }
            ?>
              <a href="<?= $link_thong_bao; ?>" class="notification-item <?= $chua_doc ? 'unread' : ''; ?>">
                <div class="notif-icon <?= $mau_sac; ?> me-3">
                  <i class='bx <?= $icon; ?>'></i>
                </div>
                <div class="notif-content flex-grow-1">
                  <div class="notif-title fw-semibold">
                    <?= htmlspecialchars($tb['tieu_de']); ?>
                  </div>
                  <div class="notif-message text-muted small">
                    <?= nl2br(htmlspecialchars($tb['noi_dung'])); ?>
                  </div>
                  <div class="notif-time text-muted small mt-1">
                    <?= dinh_dang_thoi_gian($tb['ngay_tao']); ?>
                  </div>
                </div>
                <div class="notif-actions">
                  <?php if ($chua_doc): ?>
                    <i class="bx bx-circle unread-dot"></i>
                  <?php endif; ?>
                </div>
              </a>
              <hr class="m-0">
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer chung -->
  <?php include("../partials/footer.php"); ?>
</body>

</html>