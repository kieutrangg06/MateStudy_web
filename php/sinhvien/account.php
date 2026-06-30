<?php
require_once('../config.php');
require_permission([ROLE_STUDENT], '../login.php');

$user_id = $_SESSION['user_id'];
$thong_bao = "";
$loai_thong_bao = "";

// ===== XỬ LÝ UPLOAD AVATAR =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== 4) {
  $file = $_FILES['avatar'];

  if ($file['error'] === 0) {
    $dung_luong_toi_da = 800 * 1024;
    $cac_dinh_dang_hop_le = ['image/jpeg', 'image/png', 'image/jpg'];

    if ($file['size'] <= $dung_luong_toi_da && in_array($file['type'], $cac_dinh_dang_hop_le)) {
      $phan_mo_rong = pathinfo($file['name'], PATHINFO_EXTENSION);
      $ten_file_moi = "/images/avatars/" . $user_id . "_" . time() . "." . $phan_mo_rong;
      $duong_dan_luu = __DIR__ . "/../.." . $ten_file_moi;

      if (move_uploaded_file($file['tmp_name'], $duong_dan_luu)) {
        $sql_lay_anh_cu = "SELECT anh_dai_dien FROM nguoi_dung WHERE id = ?";
        $stmt_anh_cu = mysqli_prepare($conn, $sql_lay_anh_cu);
        mysqli_stmt_bind_param($stmt_anh_cu, "i", $user_id);
        mysqli_stmt_execute($stmt_anh_cu);
        $result_anh_cu = mysqli_stmt_get_result($stmt_anh_cu);
        $data_anh_cu = mysqli_fetch_assoc($result_anh_cu);
        mysqli_stmt_close($stmt_anh_cu);

        if ($data_anh_cu['anh_dai_dien'] && file_exists(__DIR__ . "/.." . $data_anh_cu['anh_dai_dien'])) {
          unlink(__DIR__ . "/.." . $data_anh_cu['anh_dai_dien']);
        }

        $sql_cap_nhat_anh = "UPDATE nguoi_dung SET anh_dai_dien = ? WHERE id = ?";
        $stmt_cap_nhat = mysqli_prepare($conn, $sql_cap_nhat_anh);
        mysqli_stmt_bind_param($stmt_cap_nhat, "si", $ten_file_moi, $user_id);
        mysqli_stmt_execute($stmt_cap_nhat);
        mysqli_stmt_close($stmt_cap_nhat);

        $thong_bao = "Cập nhật ảnh đại diện thành công!";
        $loai_thong_bao = "success";
      } else {
        $thong_bao = "Không thể tải ảnh lên. Vui lòng thử lại!";
        $loai_thong_bao = "error";
      }
    } else {
      $thong_bao = "Ảnh không hợp lệ hoặc quá dung lượng (tối đa 800KB)";
      $loai_thong_bao = "error";
    }
  } else {
    $thong_bao = "Có lỗi khi tải ảnh lên!";
    $loai_thong_bao = "error";
  }
}

// SỬA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_FILES['avatar'])) {
  $ten_dang_nhap_moi = trim($_POST['ten_dang_nhap']);
  $email_moi = trim($_POST['email']);
  $nien_khoa_moi = !empty($_POST['nien_khoa']) ? (int)$_POST['nien_khoa'] : null;

  $sql_kiem_tra_ten = "SELECT id FROM nguoi_dung WHERE ten_dang_nhap = ? AND id != ?";
  $stmt_ten = mysqli_prepare($conn, $sql_kiem_tra_ten);
  mysqli_stmt_bind_param($stmt_ten, "si", $ten_dang_nhap_moi, $user_id);
  mysqli_stmt_execute($stmt_ten);
  $kq_ten = mysqli_stmt_get_result($stmt_ten);

  if (mysqli_num_rows($kq_ten) > 0) {
    $thong_bao = "Tên đăng nhập đã tồn tại!";
    $loai_thong_bao = "error";
  } else {
    $sql_kiem_tra_email = "SELECT id FROM nguoi_dung WHERE email = ? AND id != ?";
    $stmt_kiem_tra = mysqli_prepare($conn, $sql_kiem_tra_email);
    mysqli_stmt_bind_param($stmt_kiem_tra, "si", $email_moi, $user_id);
    mysqli_stmt_execute($stmt_kiem_tra);
    $ket_qua_kiem_tra = mysqli_stmt_get_result($stmt_kiem_tra);

    if (mysqli_num_rows($ket_qua_kiem_tra) > 0) {
      $thong_bao = "Email này đã được sử dụng bởi tài khoản khác!";
      $loai_thong_bao = "error";
    } else {
      $sql_cap_nhat = "UPDATE nguoi_dung SET ten_dang_nhap = ?, email = ?, nien_khoa = ? WHERE id = ?";
      $stmt = mysqli_prepare($conn, $sql_cap_nhat);
      mysqli_stmt_bind_param($stmt, "ssii", $ten_dang_nhap_moi, $email_moi, $nien_khoa_moi, $user_id);

      if (mysqli_stmt_execute($stmt)) {
        $thong_bao = "Cập nhật thông tin thành công!";
        $loai_thong_bao = "success";
      } else {
        $thong_bao = "Có lỗi khi cập nhật thông tin!";
        $loai_thong_bao = "error";
      }
      mysqli_stmt_close($stmt);
    }
    mysqli_stmt_close($stmt_kiem_tra);
  }
  mysqli_stmt_close($stmt_ten);
}

$sql_lay_thong_tin = "SELECT ten_dang_nhap, email, anh_dai_dien, nien_khoa FROM nguoi_dung WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql_lay_thong_tin);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
  session_destroy();
  header("Location: login.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thông tin tài khoản - MATESTUDY</title>
  <link rel="icon" type="image/png" href="../../images/logo.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.0/css/boxicons.min.css">
  <link rel="stylesheet" href="../../css/header-footer.css">
  <link rel="stylesheet" href="../../css/style.css">
  <link rel="stylesheet" href="../../css/account.css">
</head>

<body>
  <?php include("../partials/header.php"); ?>
  <div class="container">
    <div class="tabs">
      <a href="account.php" class="tab active">
        <i class="bx bx-user"></i> Thông tin tài khoản
      </a>
      <a href="setPass.php" class="tab">
        <i class="bx bx-lock-alt"></i> Đổi mật khẩu
      </a>
    </div>

    <?php if ($thong_bao != ""): ?>
      <div class="alert alert-<?php echo $loai_thong_bao; ?>">
        <?php echo $thong_bao; ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <div class="avatar-section">
          <?php
          if ($user['anh_dai_dien']) {
            $duong_dan_anh = '../../' . htmlspecialchars($user['anh_dai_dien']);
          } else {
            $duong_dan_anh = '../../../images/avatars/avt.jpg';
          }
          ?>
          <img src="<?php echo $duong_dan_anh; ?>" alt="Avatar" class="avatar">
          <div class="upload-controls">
            <form method="post" enctype="multipart/form-data">
              <label for="upload" class="btn-upload">
                Tải ảnh đại diện
                <input type="file" name="avatar" id="upload" accept="image/png, image/jpeg, image/jpg" hidden onchange="this.form.submit()">
              </label>
            </form>
            <p class="note">JPG, PNG. Tối đa 800KB</p>
          </div>
        </div>

        <form method="post">
          <div class="form-grid">
            <div class="form-group">
              <label>Tên đăng nhập</label>
              <input type="text" name="ten_dang_nhap" value="<?php echo htmlspecialchars($user['ten_dang_nhap']); ?>" required>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
              <label>Niên khóa</label>
              <input type="number" name="nien_khoa" placeholder="2021 - 2025" value="<?php echo htmlspecialchars($user['nien_khoa'] ?? ''); ?>">
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-primary">Lưu thay đổi</button>
            <button type="reset" class="btn-secondary">Hủy</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php include("../partials/footer.php"); ?>
</body>

</html>