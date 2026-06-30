<?php
require_once 'config.php';

$message = '';
$type = '';

// Giá trị giữ lại khi validate lỗi
$ten_dang_nhap_val = '';
$email_val = '';
$nien_khoa_val = '';

// ===== XỬ LÝ ĐĂNG KÝ =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $ten_dang_nhap = trim($_POST['ten_dang_nhap'] ?? '');
  $email         = trim($_POST['email'] ?? '');
  $nien_khoa     = (int)($_POST['nien_khoa'] ?? 0);
  $mat_khau      = trim($_POST['mat_khau'] ?? '');

  // Giữ lại giá trị để hiển thị lại form khi lỗi
  $ten_dang_nhap_val = htmlspecialchars($ten_dang_nhap);
  $email_val         = htmlspecialchars($email);
  $nien_khoa_val     = htmlspecialchars($nien_khoa);

  // Validate input
  if (empty($ten_dang_nhap) || empty($email) || empty($mat_khau) || $nien_khoa < 2015 || $nien_khoa > 2030) {
    $message = "Vui lòng điền đầy đủ và hợp lệ!";
    $type = 'error';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $message = "Email không hợp lệ!";
    $type = 'error';
  } elseif (strlen($mat_khau) < 6) {
    $message = "Mật khẩu phải từ 6 ký tự trở lên!";
    $type = 'error';
  } else {
    // Kiểm tra username/email đã tồn tại
    $check = $conn->prepare("SELECT id FROM nguoi_dung WHERE ten_dang_nhap = ? OR email = ?");
    $check->bind_param("ss", $ten_dang_nhap, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
      $check->close();
      $message = "Tên đăng nhập hoặc email đã được sử dụng!";
      $type = 'error';
    } else {
      $check->close();

      $avatar_url = "/images/avatars/avt.jpg";
      $mat_khau_hash = password_hash($mat_khau, PASSWORD_DEFAULT);

      // Tạo tài khoản mới
      $sql = "INSERT INTO nguoi_dung 
                    (ten_dang_nhap, email, mat_khau, vai_tro, nien_khoa, anh_dai_dien, trang_thai) 
                    VALUES (?, ?, ?, 'sinh_vien', ?, ?, 'hoat_dong')";

      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssis", $ten_dang_nhap, $email, $mat_khau_hash, $nien_khoa, $avatar_url);

        if ($stmt->execute()) {
          $message = "Đăng ký thành công! Đang chuyển đến trang chủ...";
          $type = 'success';

          // Tự động đăng nhập
          $_SESSION['user_id']      = $conn->insert_id;
          $_SESSION['ten_dang_nhap'] = $ten_dang_nhap;
          $_SESSION['vai_tro']      = 'sinh_vien';

          // Chuyển hướng sau 2 giây
          echo '<script>setTimeout(function(){ window.location.href="home.php"; }, 2000);</script>';
        } else {
          $message = "Lỗi hệ thống, vui lòng thử lại!";
          $type = 'error';
        }
        $stmt->close();
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Đăng ký - MATESTUDY</title>
  <link rel="icon" type="image/png" href="../images/logo.png">
  <link rel="stylesheet" href="../css/login.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" />
</head>

<body>
  <div class="container">
    <div class="card">
      <div class="logo">MATESTUDY</div>
      <p class="tagline">Đăng ký cùng MATESTUDY trên giảng đường</p>

      <?php if ($message): ?>
        <div class="alert alert-<?= $type === 'success' ? 'success' : 'error' ?>">
          <i class='bx <?= $type === 'success' ? 'bx-check-circle' : 'bx-error-circle' ?>'></i>
          <span><?= $message ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label>Tên đăng nhập <span class="text-danger">*</span></label>
          <input type="text" name="ten_dang_nhap" placeholder="Nhập tên đăng nhập" required value="<?= $ten_dang_nhap_val ?>" />
        </div>
        <div class="form-group">
          <label>Email trường <span class="text-danger">*</span></label>
          <input type="email" name="email" placeholder="you@example.edu.vn" required value="<?= $email_val ?>" />
        </div>
        <div class="form-group">
          <label>Niên khóa <span class="text-danger">*</span></label>
          <input type="number" name="nien_khoa" placeholder="Ví dụ: 2023" min="2015" max="2030" required value="<?= $nien_khoa_val ?>" />
        </div>
        <div class="form-group">
          <label>Mật khẩu <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="password" name="mat_khau" placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)" required minlength="6" />
            <i class="bx bx-hide toggle-password" onclick="togglePass(this)"></i>
          </div>
        </div>
        <button type="submit" class="btn">Đăng ký ngay</button>
      </form>

      <div class="links">
        Bạn đã có tài khoản?
        <a href="login.php">Đăng nhập</a>
      </div>
    </div>
  </div>
  <script>
    function togglePass(icon) {
      const input = icon.previousElementSibling;
      if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("bx-hide", "bx-show");
      } else {
        input.type = "password";
        icon.classList.replace("bx-show", "bx-hide");
      }
    }
  </script>
</body>

</html>