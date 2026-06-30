<?php
require_once 'config.php';

// Redirect nếu đã đăng nhập
if (check_permission([ROLE_ADMIN, ROLE_STUDENT])) {
  header("Location: home.php");
  exit();
}

$error = "";
$input_value = htmlspecialchars($_POST['login_input'] ?? '');

// ===== XỬ LÝ ĐĂNG NHẬP =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $input = trim($_POST['login_input']);
  $password = trim($_POST['password'] ?? '');

  if (empty($input) || empty($password)) {
    $error = "Vui lòng nhập đầy đủ thông tin!";
  } else {
    // Tìm user theo email hoặc username
    $sql = "SELECT id, ten_dang_nhap, mat_khau, vai_tro, trang_thai 
            FROM nguoi_dung 
            WHERE email = ? OR ten_dang_nhap = ? 
            LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $input, $input);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
      // Kiểm tra trạng thái tài khoản
      if ($user['trang_thai'] !== 'hoat_dong') {
        $error = "Tài khoản của bạn đã bị khóa hoặc chưa kích hoạt!";
      } elseif (password_verify($password, $user['mat_khau'])) {
        // Đăng nhập thành công
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['ten_dang_nhap'] = $user['ten_dang_nhap'];
        $_SESSION['vai_tro'] = $user['vai_tro'];

        header("Location: home.php");
        exit();
      } else {
        $error = "Mật khẩu không chính xác!";
      }
    } else {
      $error = "Tài khoản không tồn tại!";
    }

    mysqli_stmt_close($stmt);
  }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Đăng nhập - MATESTUDY</title>
  <link rel="icon" type="image/png" href="../images/logo.png">
  <link rel="stylesheet" href="../css/login.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" />
</head>

<body>
  <div class="container">
    <div class="card">
      <div class="logo">MATESTUDY</div>
      <p class="tagline">Đăng nhập cùng MATESTUDY trên giảng đường</p>

      <?php if ($error): ?>
        <div class="alert alert-danger">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label>Email hoặc Tên đăng nhập</label>
          <input type="text" name="login_input" placeholder="Nhập email hoặc tên đăng nhập"
            value="<?= $input_value ?>" required autofocus />
        </div>
        <div class="form-group">
          <div class="input-group">
            <input type="password" name="password" placeholder="Nhập mật khẩu" required />
            <i class='bx bx-hide toggle-password' onclick="togglePass(this)"></i>
          </div>
        </div>
        <button type="submit" class="btn">Đăng nhập ngay</button>
      </form>

      <div class="links">
        Bạn chưa có tài khoản? <a href="register.php">Đăng ký miễn phí</a>
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