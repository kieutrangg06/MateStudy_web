<?php
$is_top_level = (basename(getcwd()) === 'php');
$root_path = $is_top_level ? "" : "../";

// Xác định vai trò người dùng
$is_admin = isset($_SESSION['vai_tro']) && $_SESSION['vai_tro'] === 'admin';
$user_folder = $is_admin ? "admin/" : "sinhvien/";
?>

<footer class="footer">
  <div class="footer-content">
    <div class="footer-section about">
      <h3 class="footer-logo">MATE<span>STUDY</span></h3>
      <p>Nền tảng học tập trực tuyến hàng đầu, kết nối học viên với kiến thức chất lượng cao.</p>
      <div class="social-links">
        <a href="#" aria-label="Facebook"><i class='bx bxl-facebook-circle'></i></a>
        <a href="#" aria-label="YouTube"><i class='bx bxl-youtube'></i></a>
        <a href="#" aria-label="Instagram"><i class='bx bxl-instagram'></i></a>
        <a href="#" aria-label="LinkedIn"><i class='bx bxl-linkedin-square'></i></a>
      </div>
    </div>

    <div class="footer-section links">
      <h4>Liên Kết Nhanh</h4>
      <ul>
        <li><a href="<?php echo $root_path; ?>home.php"><i class='bx bx-chevron-right'></i> Trang Chủ</a></li>
        <?php if (!$is_admin): ?>
          <li><a href="<?php echo $root_path . $user_folder; ?>blog.php"><i class='bx bx-chevron-right'></i> Diễn Đàn</a></li>
          <li><a href="<?php echo $root_path . $user_folder; ?>calender.php"><i class='bx bx-chevron-right'></i> Thời Khóa Biểu</a></li>
          <li><a href="<?php echo $root_path . $user_folder; ?>review.php"><i class='bx bx-chevron-right'></i> Đánh Giá</a></li>
          <li><a href="<?php echo $root_path . $user_folder; ?>team.php"><i class='bx bx-chevron-right'></i> Học Tập</a></li>
        <?php endif; ?>
      </ul>
    </div>

    <div class="footer-section support">
      <h4>Hỗ Trợ</h4>
      <ul>
        <li><a href="#"><i class='bx bx-chevron-right'></i> Trung Tâm Trợ Giúp</a></li>
        <li><a href="#"><i class='bx bx-chevron-right'></i> Điều Khoản Sử Dụng</a></li>
        <li><a href="#"><i class='bx bx-chevron-right'></i> Chính Sách Bảo Mật</a></li>
        <li><a href="#"><i class='bx bx-chevron-right'></i> Câu Hỏi Thường Gặp</a></li>
      </ul>
    </div>

    <div class="footer-section contact">
      <h4>Liên Hệ</h4>
      <div class="contact-item">
        <i class='bx bx-map'></i>
        <span>470 Trần Đại Nghĩa, Hòa Quý, Ngũ Hành Sơn, Đà Nẵng</span>
      </div>
      <div class="contact-item">
        <i class='bx bx-phone'></i>
        <span>+84 123 456 789</span>
      </div>
      <div class="contact-item">
        <i class='bx bx-envelope'></i>
        <span>support@matestudy.vn</span>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <p>© 2025 MATESTUDY - Cộng đồng sinh viên</p>
  </div>
</footer>