<?php
require_once('../config.php');
require_permission([ROLE_STUDENT], '../login.php');

// ===== KHỞI TẠO =====
$user_id = $_SESSION['user_id'];
$message = '';
$type = 'danger';

// ===== XỬ LÝ ĐỔI MẬT KHẨU =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $mat_khau_hien_tai = $_POST['currentPassword'] ?? '';
  $mat_khau_moi      = $_POST['newPassword'] ?? '';
  $xac_nhan_mk       = $_POST['confirmPassword'] ?? '';

  $stmt = $conn->prepare("SELECT mat_khau FROM nguoi_dung WHERE id = ? LIMIT 1");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();
  $stmt->close();

  if (!$user || !password_verify($mat_khau_hien_tai, $user['mat_khau'])) {
    $message = "Mật khẩu hiện tại không đúng!";
  } elseif ($mat_khau_moi !== $xac_nhan_mk) {
    $message = "Mật khẩu xác nhận không khớp!";
  } elseif (strlen($mat_khau_moi) < 8) {
    $message = "Mật khẩu mới phải có ít nhất 8 ký tự!";
  } else {

    $co_chu_hoa = preg_match('@[A-Z]@', $mat_khau_moi);
    $co_so      = preg_match('@[0-9]@', $mat_khau_moi);
    $co_dac_biet = preg_match('@[^\w]@', $mat_khau_moi);

    if (!$co_chu_hoa || !$co_so || !$co_dac_biet) {
      $message = "Mật khẩu chưa đủ mạnh! Cần có chữ hoa, số và ký tự đặc biệt.";
    } else {

      $mat_khau_moi_hash = password_hash($mat_khau_moi, PASSWORD_DEFAULT);

      $update_stmt = $conn->prepare("UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?");
      $update_stmt->bind_param("si", $mat_khau_moi_hash, $user_id);

      if ($update_stmt->execute()) {
        $message = "Đổi mật khẩu thành công! Hãy đăng nhập lại nếu cần.";
        $type = "success";
      } else {
        $message = "Lỗi hệ thống, vui lòng thử lại!";
      }
      $update_stmt->close();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đổi mật khẩu - MATESTUDY</title>
  <link rel="icon" type="image/png" href="../../images/logo.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" />
  <link rel="stylesheet" href="../../css/header-footer.css">
  <link rel="stylesheet" href="../../css/style.css">
  <link rel="stylesheet" href="../../css/account.css">
  <script>
    function togglePassword(inputId, eyeIcon) {
      const input = document.getElementById(inputId);
      if (input.type === "password") {
        input.type = "text";
        eyeIcon.classList.replace('bx-hide', 'bx-show');
      } else {
        input.type = "password";
        eyeIcon.classList.replace('bx-show', 'bx-hide');
      }
    }
  </script>
</head>

<body>
  <!-- ===== HEADER ===== -->
  <?php include("../partials/header.php"); ?>

  <!-- ===== NỘI DUNG CHÍNH ===== -->
  <div class="container">
    <!-- Tabs điều hướng -->
    <div class="tabs">
      <a href="account.php" class="tab"><i class="bx bx-user"></i> Thông tin tài khoản</a>
      <a href="setPass.php" class="tab active"><i class="bx bx-lock-alt"></i> Đổi mật khẩu</a>
    </div>

    <!-- Form đổi mật khẩu -->
    <div class="card">
      <div class="card-body">

        <?php if ($message): ?>
          <div class="alert alert-<?= $type ?>">
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <form method="post">
          <div class="form-grid">

            <div class="form-group">
              <label>Mật khẩu hiện tại</label>
              <div class="password-wrapper">
                <input type="password" id="currentPassword" name="currentPassword" placeholder="Nhập mật khẩu hiện tại" required>
                <i class='bx bx-hide password-toggle' onclick="togglePassword('currentPassword', this)"></i>
              </div>
            </div>

            <div class="form-group">
              <label>Mật khẩu mới</label>
              <div class="password-wrapper">
                <input type="password" id="newPassword" name="newPassword" placeholder="Tối thiểu 8 ký tự" required>
                <i class='bx bx-hide password-toggle' onclick="togglePassword('newPassword', this)"></i>
              </div>
            </div>

            <div class="form-group">
              <label>Xác nhận mật khẩu mới</label>
              <div class="password-wrapper">
                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Nhập lại mật khẩu mới" required>
                <i class='bx bx-hide password-toggle' onclick="togglePassword('confirmPassword', this)"></i>
              </div>
            </div>
          </div>

          <div class="password-requirements">
            <p><strong>Yêu cầu mật khẩu:</strong></p>
            <ul>
              <li>Ít nhất 8 ký tự</li>
              <li>Có chữ hoa (A-Z)</li>
              <li>Có số (0-9)</li>
              <li>Có ký tự đặc biệt (!@#$%^&*...)</li>
            </ul>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-primary">Cập nhật mật khẩu</button>
            <button type="reset" class="btn-secondary">Hủy</button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <?php include("../partials/footer.php"); ?>
</body>

</html>