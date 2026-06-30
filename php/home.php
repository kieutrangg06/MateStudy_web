<?php
require_once 'config.php';

$user_name = "guest";
$is_logged_in = false;

// Kiểm tra đăng nhập
if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];
  $query = "SELECT ten_dang_nhap FROM nguoi_dung WHERE id = $user_id";
  $result = mysqli_query($conn, $query);
  if ($row = mysqli_fetch_assoc($result)) {
    $user_name = $row['ten_dang_nhap'];
    $is_logged_in = true;
  }
}

// ===== XỬ LÝ FORM LIÊN HỆ =====
$name_val = htmlspecialchars($_POST['name'] ?? '');
$email_val = htmlspecialchars($_POST['email'] ?? '');
$message_val = htmlspecialchars($_POST['message'] ?? '');
$contact_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
  $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
  $message = mysqli_real_escape_string($conn, $_POST['message'] ?? '');

  $name_val = htmlspecialchars($_POST['name'] ?? '');
  $email_val = htmlspecialchars($_POST['email'] ?? '');
  $message_val = htmlspecialchars($_POST['message'] ?? '');

  if (empty($name) || empty($email) || empty($message)) {
    $contact_msg = "<div class='alert alert-danger'>Vui lòng điền đầy đủ thông tin!</div>";
  } elseif (!filter_var($email_val, FILTER_VALIDATE_EMAIL)) {
    $contact_msg = "<div class='alert alert-danger'>Email không hợp lệ!</div>";
  } else {
    // Tạo bảng nếu chưa tồn tại
    $create_table = "CREATE TABLE IF NOT EXISTS lien_he (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ho_ten VARCHAR(100),
        email VARCHAR(100),
        noi_dung TEXT,
        ngay_gui DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $create_table);

    $insert_query = "INSERT INTO lien_he (ho_ten, email, noi_dung) VALUES ('$name', '$email', '$message')";
    $save = mysqli_query($conn, $insert_query);

    if ($save) {
      $contact_msg = "<div class='alert alert-success'>Cảm ơn bạn! Tin nhắn đã được gửi thành công.</div>";
      $name_val = '';
      $email_val = '';
      $message_val = '';
    } else {
      $contact_msg = "<div class='alert alert-danger'>Có lỗi xảy ra. Vui lòng thử lại.</div>";
    }
  }
}

// ===== LẤY THỐNG KÊ =====
$query_users = "SELECT COUNT(*) FROM nguoi_dung WHERE vai_tro = 'sinh_vien'";
$result_users = mysqli_query($conn, $query_users);
$total_users = mysqli_fetch_array($result_users)[0];

$query_monhoc = "SELECT COUNT(*) FROM mon_hoc";
$result_monhoc = mysqli_query($conn, $query_monhoc);
$total_monhoc = mysqli_fetch_array($result_monhoc)[0];

$query_reviews = "SELECT COUNT(*) FROM danh_gia_mon_giang_vien WHERE trang_thai = 'da_duyet'";
$result_reviews = mysqli_query($conn, $query_reviews);
$total_reviews = mysqli_fetch_array($result_reviews)[0];

$query_giangvien = "SELECT COUNT(DISTINCT ten_gv) FROM mon_hoc";
$result_giangvien = mysqli_query($conn, $query_giangvien);
$total_giangvien = mysqli_fetch_array($result_giangvien)[0];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>MATESTUDY - Cộng đồng sinh viên</title>
  <link rel="icon" type="image/png" href="../images/logo.png">
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/home.css">
  <link rel="stylesheet" href="../css/header-footer.css">
  <link rel="stylesheet" href="../css/style.css">
</head>

<body>
  <?php include("partials/header_home.php"); ?>

  <!-- ===== BANNER CHÍNH VỚI VIDEO NỀN ===== -->
  <section class="section main-banner" id="top">
    <!-- Video nền tự động phát -->
    <video autoplay muted loop playsinline id="bg-video">
      <source src="../images/course-video.mp4" type="video/mp4" />
      Trình duyệt của bạn không hỗ trợ video HTML5.
    </video>

    <!-- Lớp phủ tối để text dễ đọc hơn -->
    <div class="video-overlay header-text">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="caption">
              <h6>
                Xin chào
                <?php
                if ($is_logged_in) {
                  echo ", <strong>$user_name</strong>";
                }
                ?>!
              </h6>
              <h2 style="color: #a12c2f;">Welcome to MATESTUDY</h2>
              <p style="color: #f5a425;">Hãy tạo nên những năm tháng đại học vui vẻ và ý nghĩa bên những người bạn cùng trường.</p>

              <div class="main-button-red">
                <?php if ($is_logged_in): ?>
                  <a href="sinhvien/blog.php">Vào ngay!</a>
                <?php else: ?>
                  <a href="login.php">Tham gia ngay!</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="upcoming-meetings" id="descript">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="section-heading">
            <h2>MATESTUDY</h2>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="categories">
            <h4>Mọi khoảnh khắc cùng Sinh viên</h4>
            <ul>
              <li><a href="sinhvien/review.php">Đánh giá môn học & giảng viên</a></li>
              <li><a href="sinhvien/calender.php">Thời khóa biểu</a></li>
              <li><a href="sinhvien/blog.php">Diễn đàn</a></li>
              <li><a href="sinhvien/team.php">Nhóm học tập</a></li>
            </ul>
            <div class="main-button-red">
              <a href="#contact">Liên hệ ngay</a>
            </div>
          </div>
        </div>
        <div class="col-lg-8">
          <div class="row">
            <div class="col-lg-6">
              <div class="meeting-item">
                <div class="thumb">
                  <a href="sinhvien/review.php"><img src="../images/meeting-01.jpg" alt="Đánh giá"></a>
                </div>
                <div class="down-content">
                  <a href="sinhvien/review.php">
                    <h4>Đánh giá môn học & giảng viên</h4>
                  </a>
                  <p>Do chính các bạn sinh viên chia sẻ.</p>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="meeting-item">
                <div class="thumb">
                  <a href="sinhvien/calender.php"><img src="../images/meeting-02.jpg" alt="Lịch học"></a>
                </div>
                <div class="down-content">
                  <a href="sinhvien/calender.php">
                    <h4>Thời khóa biểu</h4>
                  </a>
                  <p>Tạo lịch học nhanh chóng và dễ dàng.</p>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="meeting-item">
                <div class="thumb">
                  <a href="sinhvien/blog.php"><img src="../images/meeting-03.jpg" alt="Diễn đàn"></a>
                </div>
                <div class="down-content">
                  <a href="sinhvien/blog.php">
                    <h4>Diễn đàn</h4>
                  </a>
                  <p>Nơi trò chuyện riêng của chúng ta.</p>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="meeting-item">
                <div class="thumb">
                  <a href="sinhvien/team.php"><img src="../images/meeting-04.jpg" alt="Nhóm học"></a>
                </div>
                <div class="down-content">
                  <a href="sinhvien/team.php">
                    <h4>Nhóm học tập</h4>
                  </a>
                  <p>Dễ dàng phân chia và hoàn thành bài tập nhóm.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="our-facts">
    <div class="container">
      <div class="row">
        <div class="col-lg-6">
          <div class="row">
            <div class="col-lg-12">
              <h2>MATESTUDY CÓ GÌ?</h2>
            </div>

            <div class="col-lg-6">
              <div class="row">
                <div class="col-12">
                  <div class="count-area-content percentage">
                    <div class="count-digit">94</div>
                    <div class="count-title" style="color: #f5a425;">Đánh giá tốt</div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="count-area-content">
                    <div class="count-digit"><?= $total_giangvien ?>+</div>
                    <div class="count-title" style="color: #f5a425;">Giảng viên</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="row">
                <div class="col-12">
                  <div class="count-area-content new-students">
                    <div class="count-digit"><?= number_format($total_users) ?>+</div>
                    <div class="count-title" style="color: #f5a425;">Người dùng</div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="count-area-content">
                    <div class="count-digit"><?= $total_monhoc ?>+</div>
                    <div class="count-title" style="color: #f5a425;">Môn học</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-6 align-self-center">
          <div class="video">
            <a href="https://www.youtube.com/watch?v=HndV87XpkWg" target="_blank">
              <img src="../images/play-icon.png" alt="Xem video giới thiệu">
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="contact-us" id="contact">
    <div class="container">
      <div class="row">

        <div class="col-lg-8 align-self-center">
          <div class="row">
            <div class="col-lg-12">
              <div id="contact">

                <?= $contact_msg ?>

                <form method="POST" action="#contact">
                  <div class="row">
                    <div class="col-lg-12">
                      <h2>Liên hệ với chúng tôi</h2>
                    </div>

                    <div class="col-lg-6">
                      <fieldset>
                        <input name="name" type="text" placeholder="Họ và tên*" value="<?= $name_val ?>" required>
                      </fieldset>
                    </div>

                    <div class="col-lg-6">
                      <fieldset>
                        <input name="email" type="email" placeholder="Email của bạn*" value="<?= $email_val ?>" required>
                      </fieldset>
                    </div>

                    <div class="col-lg-12">
                      <fieldset>
                        <textarea name="message" rows="6" placeholder="Nội dung tin nhắn của bạn*" required><?= $message_val ?></textarea>
                      </fieldset>
                    </div>

                    <div class="col-lg-12">
                      <fieldset>
                        <button type="submit">
                          <i class='bx bx-send'></i> Gửi tin nhắn
                        </button>
                      </fieldset>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="right-info">
            <ul>
              <li>
                <h6>Số điện thoại</h6>
                <span>0123.456.789</span>
              </li>
              <li>
                <h6>Email</h6>
                <span>support@matestudy.vn</span>
              </li>
              <li>
                <h6>Địa chỉ</h6>
                <span>470 Trần Đại Nghĩa, Đà Nẵng</span>
              </li>
              <li>
                <h6>Website</h6>
                <span>www.matestudy.vn</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php include("partials/footer.php"); ?>
</body>

</html>