<?php
require_once('../config.php');
require_permission([ROLE_STUDENT], '../login.php');

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Lấy thông tin user hiện tại
$sql_lay_user = "SELECT ten_dang_nhap, anh_dai_dien, vai_tro FROM nguoi_dung WHERE id = '$user_id'";
$user_query = mysqli_query($conn, $sql_lay_user);
$current_user = mysqli_fetch_assoc($user_query);

$username = $current_user['ten_dang_nhap'] ?? 'Bạn';
$avatar = $current_user['anh_dai_dien'] ? htmlspecialchars($current_user['anh_dai_dien']) : 'https://randomuser.me/api/portraits/men/1.jpg';
$is_admin = ($current_user['vai_tro'] === 'admin');

// Hàm định dạng thời gian hiển thị
function dinh_dang_thoi_gian($time)
{
  $date = new DateTime($time);
  return $date->format('d/m/Y H:i');
}

// Hàm tạo badge màu cho danh mục bài viết
function tao_badge_danh_muc($cat)
{
  $ten_danh_muc = array(
    'forum' => 'Diễn đàn SV',
    'news' => 'Bản tin',
    'market' => 'Chợ SV',
    'job' => 'Việc làm',
    'lost' => 'Mất đồ',
    'docs' => 'Tài liệu'
  );

  $mau_danh_muc = array(
    'forum' => 'primary',
    'news' => 'success',
    'market' => 'warning',
    'job' => 'info',
    'lost' => 'danger',
    'docs' => 'secondary'
  );

  $ten = $ten_danh_muc[$cat] ?? 'Khác';
  $mau = $mau_danh_muc[$cat] ?? 'secondary';
  return "<span class=\"badge bg-$mau\">$ten</span>";
}

// ===== XỬ LÝ ĐĂNG BÀI MỚI =====
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_post'])) {

  // Lấy dữ liệu từ form
  $tieu_de = mysqli_real_escape_string($conn, trim($_POST['title']));
  $noi_dung = mysqli_real_escape_string($conn, trim($_POST['content']));
  $danh_muc = $_POST['category'];

  // Kiểm tra dữ liệu đầu vào
  if (empty($tieu_de) || empty($noi_dung) || empty($danh_muc)) {
    $error = "Vui lòng điền đầy đủ các trường bắt buộc!";
  } else {
    // Xử lý upload file đính kèm
    $ten_file = null;

    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
      $file = $_FILES['file'];
      $dung_luong_toi_da = 10 * 1024 * 1024;

      if ($file['size'] > $dung_luong_toi_da) {
        $error = "File quá lớn! Tối đa 10MB.";
      } else {
        $dinh_dang_hop_le = array('pdf', 'docx', 'png', 'jpg', 'jpeg');
        $phan_mo_rong = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($phan_mo_rong, $dinh_dang_hop_le)) {
          $error = "Chỉ cho phép file: PDF, DOCX, PNG, JPG, JPEG";
        } else {
          $ten_file = time() . "_" . basename($file['name']);

          $ten_file_goc = $ten_file;
          $thu_muc_luu = "../uploads/blogs/";
          $duong_dan_csdl = "/uploads/blogs/" . $ten_file_goc;

          if (!is_dir($thu_muc_luu)) {
            mkdir($thu_muc_luu, 0777, true);
          }
          if (move_uploaded_file($file['tmp_name'], $thu_muc_luu . $ten_file_goc)) {
            $ten_file = $duong_dan_csdl;
          } else {
            $error = "Lỗi khi upload file.";
            $ten_file = null;
          }
        }
      }
    }

    // Lưu bài viết vào database
    if (!$error) {
      $file_sql = $ten_file ? "'$ten_file'" : "NULL";
      $trang_thai = $is_admin ? 'da_duyet' : 'cho_duyet';

      $sql_them_bai = "INSERT INTO bai_viet_dien_dan 
                       (tac_gia_id, tieu_de, noi_dung, file_dinh_kem, category, trang_thai, ngay_dang)
                       VALUES ('$user_id', '$tieu_de', '$noi_dung', $file_sql, '$danh_muc', '$trang_thai', NOW())";

      if (mysqli_query($conn, $sql_them_bai)) {
        $post_id = mysqli_insert_id($conn);
        $thong_bao_thanh_cong = $is_admin ? "Đăng bài thành công!" : "Đăng bài thành công! Bài viết đang chờ duyệt.";
        $success = $thong_bao_thanh_cong;

        // Tạo thông báo cho người dùng
        $tieu_de_thong_bao = "Bài viết mới của bạn";
        $noi_dung_thong_bao = "Bạn vừa đăng bài: \"$tieu_de\"";
        $sql_them_thong_bao = "INSERT INTO thong_bao (sinh_vien_id, tieu_de, noi_dung, loai, id_lien_quan, loai_lien_quan)
                               VALUES ('$user_id', '$tieu_de_thong_bao', '$noi_dung_thong_bao', 'dien_dan', '$post_id', 'bai_viet')";
        mysqli_query($conn, $sql_them_thong_bao);
      } else {
        $error = "Lỗi hệ thống. Vui lòng thử lại.";
      }
    }
  }
}

// ===== XỬ LÝ THÍCH BÀI VIẾT =====
if (isset($_GET['like'])) {
  $post_id = (int)$_GET['like'];

  // Kiểm tra đã thích chưa
  $sql_kiem_tra_thich = "SELECT * FROM luot_thich_bai_viet WHERE bai_viet_id = '$post_id' AND sinh_vien_id = '$user_id'";
  $kiem_tra = mysqli_query($conn, $sql_kiem_tra_thich);

  // Nếu đã thích thì bỏ thích, chưa thích thì thêm
  if ($kiem_tra && mysqli_num_rows($kiem_tra) > 0) {
    mysqli_query($conn, "DELETE FROM luot_thich_bai_viet WHERE bai_viet_id = '$post_id' AND sinh_vien_id = '$user_id'");
  } else {
    mysqli_query($conn, "INSERT INTO luot_thich_bai_viet (bai_viet_id, sinh_vien_id) VALUES ('$post_id', '$user_id')");

    // Gửi thông báo cho tác giả bài viết
    $sql_lay_bai_viet = "SELECT tac_gia_id, tieu_de FROM bai_viet_dien_dan WHERE id = '$post_id'";
    $thong_tin_bai_viet = mysqli_fetch_assoc(mysqli_query($conn, $sql_lay_bai_viet));

    if ($thong_tin_bai_viet && $thong_tin_bai_viet['tac_gia_id'] != $user_id) {
      $tac_gia_id = $thong_tin_bai_viet['tac_gia_id'];
      $tieu_de_bai = $thong_tin_bai_viet['tieu_de'];

      $tieu_de_thong_bao = "Có người thích bài viết của bạn";
      $noi_dung_thong_bao = "$username đã thích bài viết: \"$tieu_de_bai\"";

      $sql_them_thong_bao = "INSERT INTO thong_bao (sinh_vien_id, tieu_de, noi_dung, loai, da_doc, id_lien_quan, loai_lien_quan)
                             VALUES ('$tac_gia_id', '$tieu_de_thong_bao', '$noi_dung_thong_bao', 'dien_dan', 0, '$post_id', 'bai_viet')";
      mysqli_query($conn, $sql_them_thong_bao);
    }
  }

  $url_quay_lai = "blog.php";
  if (isset($_GET['cat'])) {
    $url_quay_lai .= "?cat=" . $_GET['cat'];
  }
  if (isset($_GET['s'])) {
    $url_quay_lai .= (strpos($url_quay_lai, '?') ? '&' : '?') . "s=" . urlencode($_GET['s']);
  }
  if (isset($_GET['page'])) {
    $url_quay_lai .= "&page=" . (int)$_GET['page'];
  }

  // Quay lại trang trước đó
  header("Location: $url_quay_lai");
  exit();
}

// ===== XỬ LÝ BÌNH LUẬN =====
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
  $post_id = (int)$_POST['post_id'];
  $binh_luan = mysqli_real_escape_string($conn, trim($_POST['comment']));

  // Thêm bình luận vào database
  if ($binh_luan !== '') {
    $sql_them_binh_luan = "INSERT INTO binh_luan_dien_dan (bai_viet_id, tac_gia_id, noi_dung) 
                           VALUES ('$post_id', '$user_id', '$binh_luan')";
    $them_thanh_cong = mysqli_query($conn, $sql_them_binh_luan);

    // Gửi thông báo cho tác giả bài viết
    if ($them_thanh_cong) {
      $sql_lay_bai_viet = "SELECT tac_gia_id, tieu_de FROM bai_viet_dien_dan WHERE id = '$post_id'";
      $thong_tin_bai_viet = mysqli_fetch_assoc(mysqli_query($conn, $sql_lay_bai_viet));

      if ($thong_tin_bai_viet && $thong_tin_bai_viet['tac_gia_id'] != $user_id) {
        $tac_gia_id = $thong_tin_bai_viet['tac_gia_id'];
        $tieu_de_bai = htmlspecialchars($thong_tin_bai_viet['tieu_de']);

        $tieu_de_thong_bao = "Có bình luận mới";
        $noi_dung_thong_bao = "$username đã bình luận bài viết của bạn: \"$tieu_de_bai\"";

        $sql_them_thong_bao = "INSERT INTO thong_bao (sinh_vien_id, tieu_de, noi_dung, loai, da_doc, id_lien_quan, loai_lien_quan)
                               VALUES ('$tac_gia_id', '$tieu_de_thong_bao', '$noi_dung_thong_bao', 'dien_dan', 0, '$post_id', 'bai_viet')";
        mysqli_query($conn, $sql_them_thong_bao);
      }
    }
  }

  $url_quay_lai = "blog.php";
  if (isset($_GET['cat'])) {
    $url_quay_lai .= "?cat=" . $_GET['cat'];
  }
  if (isset($_GET['s'])) {
    $url_quay_lai .= (strpos($url_quay_lai, '?') ? '&' : '?') . "s=" . urlencode($_GET['s']);
  }

  // Quay lại trang trước đó
  header("Location: $url_quay_lai");
  exit();
}

// ===== XÂY DỰNG ĐIỀU KIỆN LỌC BÀI VIẾT =====
$dieu_kien_where = array();
$tieu_de_trang = "Tất cả các bài viết";

// Sinh viên chỉ xem bài đã duyệt
if (!$is_admin) {
  $dieu_kien_where[] = "b.trang_thai = 'da_duyet'";
}

// Lọc theo danh mục
if (isset($_GET['cat'])) {
  $danh_muc = $_GET['cat'];

  if ($danh_muc == 'hot') {
    $tieu_de_trang = "Nổi bật";
  } elseif (in_array($danh_muc, array('forum', 'news', 'market', 'job', 'lost', 'docs'))) {
    $dieu_kien_where[] = "b.category = '$danh_muc'";

    $ten_danh_muc_map = array(
      'forum' => 'Diễn đàn SV',
      'news' => 'Bản tin trường',
      'market' => 'Chợ SV',
      'job' => 'Việc làm',
      'lost' => 'Đồ thất lạc',
      'docs' => 'Tài liệu'
    );
    $tieu_de_trang = $ten_danh_muc_map[$danh_muc];
  }
}

// Lọc theo từ khóa tìm kiếm
$tu_khoa_tim_kiem = isset($_GET['s']) ? mysqli_real_escape_string($conn, trim($_GET['s'])) : '';
if ($tu_khoa_tim_kiem) {
  $dieu_kien_where[] = "(b.tieu_de LIKE '%$tu_khoa_tim_kiem%' OR b.noi_dung LIKE '%$tu_khoa_tim_kiem%')";
}

// Tạo câu WHERE từ các điều kiện
$where_sql = !empty($dieu_kien_where) ? "WHERE " . implode(" AND ", $dieu_kien_where) : "";

// ===== PHÂN TRANG =====
$trang_hien_tai = max(1, (int)($_GET['page'] ?? 1));
$so_bai_moi_trang = 10;
$vi_tri_bat_dau = ($trang_hien_tai - 1) * $so_bai_moi_trang;

// Sắp xếp: bài nổi bật theo lượt thích + bình luận, còn lại theo ngày đăng
$sap_xep = "b.ngay_dang DESC";
if (isset($_GET['cat']) && $_GET['cat'] == 'hot') {
  $sap_xep = "(COALESCE(likes.total_likes, 0) + COALESCE(comments.total_comments, 0)) DESC, b.ngay_dang DESC";
}

// ===== TRUY VẤN DANH SÁCH BÀI VIẾT =====
$sql_lay_bai_viet = "
    SELECT
        b.*,
        n.ten_dang_nhap as user_name,
        n.anh_dai_dien,
        COALESCE(likes.total_likes, 0) as likes,
        COALESCE(comments.total_comments, 0) as comment_count,
        (SELECT 1 FROM luot_thich_bai_viet l WHERE l.bai_viet_id = b.id AND l.sinh_vien_id = '$user_id') as liked
    FROM bai_viet_dien_dan b
    JOIN nguoi_dung n ON b.tac_gia_id = n.id
    LEFT JOIN (SELECT bai_viet_id, COUNT(*) as total_likes FROM luot_thich_bai_viet GROUP BY bai_viet_id) likes
        ON likes.bai_viet_id = b.id
    LEFT JOIN (SELECT bai_viet_id, COUNT(*) as total_comments FROM binh_luan_dien_dan GROUP BY bai_viet_id) comments
        ON comments.bai_viet_id = b.id
    $where_sql
    ORDER BY $sap_xep
    LIMIT $vi_tri_bat_dau, $so_bai_moi_trang
";

$ket_qua_bai_viet = mysqli_query($conn, $sql_lay_bai_viet);

// Đếm tổng số bài viết để tính số trang
$sql_dem_bai_viet = "SELECT COUNT(*) as total FROM bai_viet_dien_dan b $where_sql";
$ket_qua_dem = mysqli_query($conn, $sql_dem_bai_viet);
$tong_so_bai_viet = $ket_qua_dem ? mysqli_fetch_assoc($ket_qua_dem)['total'] : 0;
$tong_so_trang = ceil($tong_so_bai_viet / $so_bai_moi_trang);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Diễn Đàn Sinh Viên - MateStudy</title>
  <link rel="icon" type="image/png" href="../../images/logo.png">
  <link rel="stylesheet" href="../../css/header-footer.css">
  <link rel="stylesheet" href="../../css/style.css">
  <link rel="stylesheet" href="../../css/blog.css" />
</head>

<body>
  <!-- ===== HEADER ===== -->
  <?php include("../partials/header.php"); ?>

  <!-- ===== NỘI DUNG CHÍNH ===== -->
  <div class="container mt-4">

    <!-- Thông báo -->
    <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show">
        <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="mb-0"><?php echo $tieu_de_trang; ?></h3>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#postModal">
        Đăng bài mới
      </button>
    </div>

    <div class="search-bar mb-4">
      <form class="input-group">
        <input type="text" class="form-control" name="s" placeholder="Tìm kiếm bài viết..."
          value="<?php echo htmlspecialchars($tu_khoa_tim_kiem ?? ''); ?>">
        <button class="btn btn-outline-secondary">Tìm kiếm</button>
      </form>
    </div>

    <ul class="nav nav-tabs mb-4">
      <li class="nav-item">
        <a class="nav-link <?php echo !isset($_GET['cat']) ? 'active' : ''; ?>" href="blog.php">Tất cả</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo ($_GET['cat'] ?? '') == 'hot' ? 'active' : ''; ?>" href="blog.php?cat=hot">Nổi bật</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo ($_GET['cat'] ?? '') == 'forum' ? 'active' : ''; ?>" href="blog.php?cat=forum">Diễn đàn SV</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo ($_GET['cat'] ?? '') == 'news' ? 'active' : ''; ?>" href="blog.php?cat=news">Bản tin</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo ($_GET['cat'] ?? '') == 'market' ? 'active' : ''; ?>" href="blog.php?cat=market">Chợ SV</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo ($_GET['cat'] ?? '') == 'job' ? 'active' : ''; ?>" href="blog.php?cat=job">Việc làm</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo ($_GET['cat'] ?? '') == 'lost' ? 'active' : ''; ?>" href="blog.php?cat=lost">Mất đồ</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo ($_GET['cat'] ?? '') == 'docs' ? 'active' : ''; ?>" href="blog.php?cat=docs">Tài liệu</a>
      </li>
    </ul>

    <!-- ===== DANH SÁCH BÀI VIẾT ===== -->
    <div class="posts-scroll-container">
      <div id="postsContainer">
        <?php if (!$ket_qua_bai_viet || mysqli_num_rows($ket_qua_bai_viet) == 0): ?>
          <p class="text-center text-muted py-5">Không có bài viết nào phù hợp.</p>
        <?php else: ?>
          <?php while ($bai_viet = mysqli_fetch_assoc($ket_qua_bai_viet)): ?>
            <div class="post-card">

              <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-center">
                  <img src="../../<?php echo $bai_viet['anh_dai_dien'] ? htmlspecialchars($bai_viet['anh_dai_dien']) : $avatar; ?>"
                    class="post-avatar me-3" alt="avatar">
                  <div>
                    <div class="fw-bold"><?php echo htmlspecialchars($bai_viet['user_name']); ?></div>
                    <small class="text-muted"><?php echo dinh_dang_thoi_gian($bai_viet['ngay_dang']); ?></small>
                  </div>
                </div>
                <?php echo tao_badge_danh_muc($bai_viet['category']); ?>
              </div>

              <div class="mt-3">
                <h5 class="fw-bold"><?php echo htmlspecialchars($bai_viet['tieu_de']); ?></h5>
                <p class="text-muted">
                  <?php
                  $noi_dung_rut_gon = substr($bai_viet['noi_dung'], 0, 300);
                  echo nl2br(htmlspecialchars($noi_dung_rut_gon));
                  echo strlen($bai_viet['noi_dung']) > 300 ? '...' : '';
                  ?>
                </p>

                <?php if ($bai_viet['file_dinh_kem']): ?>
                  <?php
                  $file_name = $bai_viet['file_dinh_kem'];
                  $file_path = "../" . $file_name;
                  $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                  $image_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                  ?>

                  <div class="post-attachment mt-3">
                    <?php if (in_array($ext, $image_exts)): ?>
                      <div class="attached-image">
                        <a href="<?php echo $file_path; ?>" target="_blank">
                          <img src="<?php echo $file_path; ?>"
                            alt="Ảnh đính kèm"
                            class="img-fluid rounded"
                            style="max-height: 300px; object-fit: cover; border: 1px solid #ddd;">
                        </a>
                        <div class="mt-2">
                          <small class="text-muted">
                            <i class="bi bi-download"></i>
                            <a href="<?php echo $file_path; ?>" download class="text-decoration-none">
                              Tải xuống ảnh
                            </a>
                          </small>
                        </div>
                      </div>
                    <?php else: ?>
                      <div class="post-file p-3 bg-light rounded d-flex align-items-center gap-3">
                        <i class="bi bi-file-earmark-text fs-3 text-primary"></i>
                        <div>
                          <strong>File đính kèm:</strong><br>
                          <a href="<?php echo $file_path; ?>" target="_blank" class="text-decoration-none">
                            <?php echo htmlspecialchars($file_name); ?>
                          </a>
                          <small class="text-muted d-block">
                            (<?php echo strtoupper($ext); ?>)
                          </small>
                        </div>
                        <a href="<?php echo $file_path; ?>" class="btn btn-sm btn-outline-primary" download>
                          <i class="bi bi-download"></i>
                        </a>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>

              <div class="d-flex gap-3 mt-3">
                <?php
                $url_thich = "blog.php?like=" . $bai_viet['id'];
                $url_thich .= "&cat=" . ($_GET['cat'] ?? '');
                $url_thich .= "&s=" . urlencode($tu_khoa_tim_kiem ?? '');
                $url_thich .= "&page=" . $trang_hien_tai;

                $da_thich = $bai_viet['liked'] ? true : false;
                ?>

                <a href="<?php echo $url_thich; ?>"
                  class="btn btn-sm <?php echo $da_thich ? 'btn-danger' : 'btn-outline-danger'; ?> d-flex align-items-center gap-2">
                  <i class="bi <?php echo $da_thich ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                  <span><?php echo $bai_viet['likes']; ?> Thích</span>
                </a>

                <button type="button"
                  class="btn btn-sm btn-outline-primary d-flex align-items-center gap-2"
                  onclick="document.getElementById('comments-<?php echo $bai_viet['id']; ?>').classList.toggle('show')">
                  <i class="bi bi-chat-dots"></i>
                  <span><?php echo $bai_viet['comment_count']; ?> Bình luận</span>
                </button>
              </div>

              <div class="comment-section" id="comments-<?php echo $bai_viet['id']; ?>">

                <div class="d-flex mt-3">
                  <img src="../../<?php echo $avatar; ?>" class="post-avatar me-2" style="width:36px;height:36px;">
                  <form method="post" class="d-flex flex-grow-1">
                    <input type="hidden" name="post_id" value="<?php echo $bai_viet['id']; ?>">
                    <input type="text" name="comment" class="form-control form-control-sm me-2"
                      placeholder="Viết bình luận..." required>
                    <button type="submit" class="btn btn-primary btn-sm">Gửi</button>
                  </form>
                </div>

                <?php
                $sql_lay_binh_luan = "SELECT bl.*, n.ten_dang_nhap, n.anh_dai_dien
                                      FROM binh_luan_dien_dan bl
                                      JOIN nguoi_dung n ON bl.tac_gia_id = n.id
                                      WHERE bl.bai_viet_id = '{$bai_viet['id']}'
                                      ORDER BY bl.ngay_tao DESC LIMIT 5";
                $ket_qua_binh_luan = mysqli_query($conn, $sql_lay_binh_luan);

                while ($binh_luan = mysqli_fetch_assoc($ket_qua_binh_luan)):
                ?>
                  <div class="d-flex mt-3">
                    <img src="../../<?php echo $binh_luan['anh_dai_dien'] ? htmlspecialchars($binh_luan['anh_dai_dien']) : $avatar; ?>"
                      class="post-avatar me-2" style="width:32px;height:32px;">
                    <div>
                      <strong><?php echo htmlspecialchars($binh_luan['ten_dang_nhap']); ?></strong>
                      <p class="mb-0"><?php echo nl2br(htmlspecialchars($binh_luan['noi_dung'])); ?></p>
                      <small class="text-muted"><?php echo dinh_dang_thoi_gian($binh_luan['ngay_tao']); ?></small>
                    </div>
                  </div>
                <?php endwhile; ?>

              </div>

            </div>
          <?php endwhile; ?>
        <?php endif; ?>

        <?php if ($tong_so_trang > 1): ?>
          <nav class="mt-5">
            <ul class="pagination justify-content-center">
              <?php for ($i = 1; $i <= $tong_so_trang; $i++): ?>
                <li class="page-item <?php echo $i == $trang_hien_tai ? 'active' : ''; ?>">
                  <a class="page-link" href="?page=<?php echo $i; ?>&cat=<?php echo $_GET['cat'] ?? ''; ?>&s=<?php echo urlencode($tu_khoa_tim_kiem ?? ''); ?>">
                    <?php echo $i; ?>
                  </a>
                </li>
              <?php endfor; ?>
            </ul>
          </nav>
        <?php endif; ?>

      </div>
    </div>
  </div>

  <!-- ===== MODAL ĐĂNG BÀI MỚI ===== -->
  <div class="modal fade" id="postModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Đăng bài mới</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="post" enctype="multipart/form-data">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Danh mục <span class="text-danger">*</span></label>
              <select class="form-select" name="category" required>
                <option value="">Chọn danh mục</option>
                <option value="forum">Diễn đàn SV</option>
                <option value="news">Bản tin trường</option>
                <option value="market">Chợ SV</option>
                <option value="job">Việc làm</option>
                <option value="lost">Đồ thất lạc</option>
                <option value="docs">Tài liệu</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="title" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Nội dung <span class="text-danger">*</span></label>
              <textarea class="form-control" rows="6" name="content" required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Đính kèm file (tối đa 10MB)</label>
              <input type="file" class="form-control" name="file" accept=".pdf,.docx,.png,.jpg,.jpeg">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" name="submit_post" class="btn btn-primary">Đăng bài</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php include("../partials/footer.php"); ?>
</body>

</html>