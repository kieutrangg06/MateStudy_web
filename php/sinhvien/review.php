<?php
require_once('../config.php');
require_permission([ROLE_STUDENT], '../login.php');

$user_id = $_SESSION['user_id'];

// ===== XỬ LÝ GỬI ĐÁNH GIÁ MỚI =====
$submit_msg = '';
$submit_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
  $mon_hoc_id = (int)$_POST['subject'];
  $diem_sao   = (int)$_POST['rating'];
  $noi_dung   = trim($_POST['comment']);

  if ($mon_hoc_id > 0 && $diem_sao >= 1 && $diem_sao <= 5 && strlen($noi_dung) >= 10) {
    $noi_dung_escaped = mysqli_real_escape_string($conn, $noi_dung);
    $sql_them_danh_gia = "INSERT INTO danh_gia_mon_giang_vien 
                              (mon_hoc_id, sinh_vien_id, diem_sao, noi_dung, trang_thai, ngay_dang)
                              VALUES ($mon_hoc_id, $user_id, $diem_sao, '$noi_dung_escaped', 'cho_duyet', NOW())";
    if (mysqli_query($conn, $sql_them_danh_gia)) {
      $submit_msg  = 'Đánh giá của bạn đã được gửi và đang chờ duyệt.';
      $submit_type = 'success';
    } else {
      $submit_msg  = 'Có lỗi xảy ra, vui lòng thử lại.';
      $submit_type = 'danger';
    }
  } else {
    $submit_msg  = 'Vui lòng chọn môn học, đánh giá sao và viết nhận xét (tối thiểu 10 ký tự)!';
    $submit_type = 'danger';
  }
}

// ===== LẤY DANH SÁCH MÔN HỌC & RATING =====
$sql_lay_mon_hoc = "SELECT 
                      m.id,
                      m.ten_mon AS name,
                      m.ten_gv AS lecturer,
                      ROUND(IFNULL(AVG(d.diem_sao),0),1) AS avgRating,
                      COUNT(CASE WHEN d.trang_thai = 'da_duyet' THEN 1 END) AS reviewCount
                    FROM mon_hoc m
                    LEFT JOIN danh_gia_mon_giang_vien d ON m.id = d.mon_hoc_id AND d.trang_thai = 'da_duyet'
                    GROUP BY m.id
                    ORDER BY m.ten_mon";

$ket_qua_mon_hoc = mysqli_query($conn, $sql_lay_mon_hoc);
$danh_sach_mon_hoc = array();
while ($mon_hoc = mysqli_fetch_assoc($ket_qua_mon_hoc)) {
  $danh_sach_mon_hoc[] = $mon_hoc;
}

// ===== LẤY DANH SÁCH ĐÁNH GIÁ ĐÃ DUYỆT =====
$sql_lay_danh_gia = "SELECT d.*, 
                            m.ten_mon, 
                            m.ten_gv, 
                            n.ten_dang_nhap AS user
                     FROM danh_gia_mon_giang_vien d
                     JOIN mon_hoc m ON d.mon_hoc_id = m.id
                     JOIN nguoi_dung n ON d.sinh_vien_id = n.id
                     WHERE d.trang_thai = 'da_duyet'
                     ORDER BY d.ngay_dang DESC";

$ket_qua_danh_gia = mysqli_query($conn, $sql_lay_danh_gia);
$danh_sach_danh_gia = array();
while ($danh_gia = mysqli_fetch_assoc($ket_qua_danh_gia)) {
  $danh_gia['time'] = $danh_gia['ngay_dang'];
  $danh_sach_danh_gia[] = $danh_gia;
}

// ===== XỬ LÝ TÌM KIẾM VÀ TAB =====
$search = trim($_GET['search'] ?? '');
$current_tab = $_GET['tab'] ?? 'subjects';

// Lọc dữ liệu theo từ khóa tìm kiếm
$filtered_mon_hoc = array();
foreach ($danh_sach_mon_hoc as $mon) {
  if (
    $search === '' ||
    stripos($mon['name'], $search) !== false ||
    stripos($mon['lecturer'], $search) !== false
  ) {
    $filtered_mon_hoc[] = $mon;
  }
}

$filtered_danh_gia = array();
foreach ($danh_sach_danh_gia as $dg) {
  if (
    $search === '' ||
    stripos($dg['ten_mon'], $search) !== false ||
    stripos($dg['ten_gv'], $search) !== false
  ) {
    $filtered_danh_gia[] = $dg;
  }
}

// Hàm định dạng thời gian
function format_time_vi($iso)
{
  $date = new DateTime($iso);
  $now  = new DateTime();
  $diff = $now->diff($date);

  if ($diff->days == 0) {
    return 'Hôm nay, ' . $date->format('H:i');
  }
  return $date->format('d/m/Y');
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Đánh Giá Môn Học & Giảng Viên</title>
  <link rel="icon" type="image/png" href="../../images/logo.png">
  <link rel="stylesheet" href="../../css/header-footer.css">
  <link rel="stylesheet" href="../../css/review.css" />
  <link rel="stylesheet" href="../../css/style.css">
</head>

<body>
  <!-- ===== HEADER ===== -->
  <?php include("../partials/header.php"); ?>

  <!-- ===== NỘI DUNG CHÍNH ===== -->
  <div class="container">

    <!-- Nút thêm đánh giá -->
    <div class="d-flex justify-content-end mb-3">
      <button class="btn btn-primary add-review-btn" data-bs-toggle="modal" data-bs-target="#reviewModal">
        <i class="bi bi-plus-lg"></i> Thêm đánh giá mới
      </button>
    </div>

    <!-- Thanh tìm kiếm -->
    <div class="search-bar mb-3">
      <form method="get" class="d-flex gap-2 align-items-center">
        <input type="text" class="form-control search-input" name="search" placeholder="Tìm kiếm môn học, giảng viên..." value="<?php echo htmlspecialchars($search); ?>">
        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($current_tab); ?>">
        <button class="btn search-btn" type="submit">Tìm kiếm</button>
      </form>
    </div>

    <!-- Thông báo sau khi gửi đánh giá -->
    <?php if ($submit_msg): ?>
      <div class="alert alert-<?php echo $submit_type; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($submit_msg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- Tab điều hướng -->
    <div class="review-tabs">
      <a href="?tab=subjects&search=<?php echo urlencode($search); ?>"
        class="review-tab <?php echo $current_tab === 'subjects' ? 'active' : ''; ?>">
        Danh sách môn học <span class="badge"><?php echo count($filtered_mon_hoc); ?></span>
      </a>
      <a href="?tab=reviews&search=<?php echo urlencode($search); ?>"
        class="review-tab <?php echo $current_tab === 'reviews' ? 'active' : ''; ?>">
        Đánh giá từ sinh viên <span class="badge"><?php echo count($filtered_danh_gia); ?></span>
      </a>
    </div>

    <!-- ===== TAB DANH SÁCH MÔN HỌC ===== -->
    <?php if ($current_tab === 'subjects'): ?>
      <div class="tab-content">
        <div class="subjects-scroll-area">
          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light sticky-top bg-white">
                <tr>
                  <th>MÔN HỌC</th>
                  <th>GIẢNG VIÊN</th>
                  <th>ĐÁNH GIÁ TRUNG BÌNH</th>
                  <th>SỐ LƯỢT ĐÁNH GIÁ</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($filtered_mon_hoc as $mon_hoc): ?>
                  <tr>
                    <td>
                      <div class="subject-name"><?php echo htmlspecialchars($mon_hoc['name']); ?></div>
                    </td>
                    <td>
                      <div class="lecturer-name"><?php echo htmlspecialchars($mon_hoc['lecturer']); ?></div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-1">
                        <span class="rating-stars">
                          <?php
                          $so_sao_day = round($mon_hoc['avgRating']);
                          echo str_repeat('★', $so_sao_day) . str_repeat('☆', 5 - $so_sao_day);
                          ?>
                        </span>
                        <span class="ms-1"><?php echo number_format($mon_hoc['avgRating'], 1); ?></span>
                      </div>
                    </td>
                    <td><?php echo $mon_hoc['reviewCount']; ?> đánh giá</td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- ===== TAB ĐÁNH GIÁ TỪ SINH VIÊN ===== -->
    <?php if ($current_tab === 'reviews'): ?>
      <div class="tab-content">
        <div class="reviews-scroll-area">
          <div id="reviewsContainer">
            <?php if (empty($filtered_danh_gia)): ?>
              <p class="text-muted text-center">Chưa có đánh giá nào được duyệt.</p>
            <?php else: ?>
              <?php foreach ($filtered_danh_gia as $danh_gia): ?>
                <div class="review-card">
                  <div class="review-header">
                    <div>
                      <div class="review-subject"><?php echo htmlspecialchars($danh_gia['ten_mon']); ?></div>
                      <div class="review-lecturer"><?php echo htmlspecialchars($danh_gia['ten_gv']); ?></div>
                    </div>
                    <div class="review-rating">
                      <?php echo str_repeat('★', $danh_gia['diem_sao']) . str_repeat('☆', 5 - $danh_gia['diem_sao']); ?>
                    </div>
                  </div>
                  <div class="review-content"><?php echo htmlspecialchars($danh_gia['noi_dung']); ?></div>
                  <div class="review-meta">
                    <span><i class="bi bi-person"></i> <?php echo htmlspecialchars($danh_gia['user']); ?></span>
                    <span><i class="bi bi-clock"></i> <?php echo format_time_vi($danh_gia['time']); ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- ===== MODAL THÊM ĐÁNH GIÁ ===== -->
  <div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Thêm đánh giá mới</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form method="post">
            <input type="hidden" name="action" value="submit_review">

            <div class="mb-3">
              <label class="form-label">Môn học <span class="text-danger">*</span></label>
              <select class="form-select" name="subject" required>
                <option value="">Chọn môn học</option>
                <?php foreach ($danh_sach_mon_hoc as $mon_hoc): ?>
                  <option value="<?php echo $mon_hoc['id']; ?>">
                    <?php echo htmlspecialchars($mon_hoc['name'] . ' - ' . $mon_hoc['lecturer']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Đánh giá (sao) <span class="text-danger">*</span></label>
              <div class="star-rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <label>
                    <input type="radio" name="rating" value="<?php echo $i; ?>" required style="display:none;">
                    <span class="star">★</span>
                  </label>
                <?php endfor; ?>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Nhận xét <span class="text-danger">*</span></label>
              <textarea class="form-control" name="comment" rows="4" placeholder="Chia sẻ trải nghiệm của bạn..." required></textarea>
              <small class="text-muted">Tối thiểu 10 ký tự</small>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
              <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php include("../partials/footer.php"); ?>

  <!-- Chỉ giữ JS tối thiểu cho star rating trong modal -->
  <script>
    document.querySelectorAll('.star-rating input[type="radio"]').forEach(radio => {
      radio.addEventListener('change', function() {
        const stars = this.parentElement.parentElement.querySelectorAll('.star');
        stars.forEach((star, idx) => {
          star.style.color = (idx < this.value) ? '#ffc107' : '#e9ecef';
        });
      });
    });
  </script>
</body>

</html>