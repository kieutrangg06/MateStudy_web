<?php
require_once('../config.php');
require_permission([ROLE_ADMIN], '../login.php');

// Lấy danh sách học kỳ
$hoc_ky_result = mysqli_query($conn, "SELECT id, ten_hoc_ky FROM hoc_ky ORDER BY id DESC");
$hoc_ky_list = mysqli_fetch_all($hoc_ky_result, MYSQLI_ASSOC);

function convertDayToDbValue($day)
{
  if ($day === 'Thứ 2') return '2';
  elseif ($day === 'Thứ 3') return '3';
  elseif ($day === 'Thứ 4') return '4';
  elseif ($day === 'Thứ 5') return '5';
  elseif ($day === 'Thứ 6') return '6';
  elseif ($day === 'Thứ 7') return '7';
  elseif ($day === 'Chủ nhật') return 'CN';
  else return null;
}

function convertDayToDisplay($dbValue)
{
  if ($dbValue === '2') return 'Thứ 2';
  elseif ($dbValue === '3') return 'Thứ 3';
  elseif ($dbValue === '4') return 'Thứ 4';
  elseif ($dbValue === '5') return 'Thứ 5';
  elseif ($dbValue === '6') return 'Thứ 6';
  elseif ($dbValue === '7') return 'Thứ 7';
  elseif ($dbValue === 'CN') return 'Chủ nhật';
  else return 'N/A';
}

$message = '';
$message_type = '';

// ===== XỬ LÝ HỌC KỲ (Thêm/Sửa/Xóa) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['semester_action'])) {
  $semester_action = $_POST['semester_action'];

  if ($semester_action === 'add_semester' || $semester_action === 'edit_semester') {
    $ten_hoc_ky = trim($_POST['ten_hoc_ky'] ?? '');
    $semester_id = (int)($_POST['semester_id'] ?? 0);

    if (empty($ten_hoc_ky)) {
      $message = "Vui lòng nhập tên học kỳ!";
      $message_type = "danger";
    } else {
      $ten_hoc_ky_esc = mysqli_real_escape_string($conn, $ten_hoc_ky);

      if ($semester_action === 'add_semester') {
        $check = mysqli_query($conn, "SELECT id FROM hoc_ky WHERE ten_hoc_ky = '$ten_hoc_ky_esc'");
        if (mysqli_num_rows($check) > 0) {
          $message = "Học kỳ này đã tồn tại!";
          $message_type = "danger";
        } else {
          if (mysqli_query($conn, "INSERT INTO hoc_ky (ten_hoc_ky) VALUES ('$ten_hoc_ky_esc')")) {
            $message = "Thêm học kỳ thành công!";
            $message_type = "success";
            // Reload danh sách học kỳ
            $hoc_ky_result = mysqli_query($conn, "SELECT id, ten_hoc_ky FROM hoc_ky ORDER BY id DESC");
            $hoc_ky_list = mysqli_fetch_all($hoc_ky_result, MYSQLI_ASSOC);
          } else {
            $message = "Lỗi: " . mysqli_error($conn);
            $message_type = "danger";
          }
        }
      } else {
        if (mysqli_query($conn, "UPDATE hoc_ky SET ten_hoc_ky = '$ten_hoc_ky_esc' WHERE id = $semester_id")) {
          header("Location: manage_data_learing.php?msg=" . urlencode("Cập nhật học kỳ thành công!") . "&type=success&show_semester_modal=1");
          exit();
        } else {
          $message = "Lỗi: " . mysqli_error($conn);
          $message_type = "danger";
        }
      }
    }
  }

  if ($semester_action === 'delete_semester') {
    $semester_id = (int)($_POST['semester_id'] ?? 0);

    // Kiểm tra xem học kỳ có môn học nào đang sử dụng không
    $check = mysqli_query($conn, "SELECT COUNT(*) as count FROM mon_hoc WHERE hoc_ky_id = $semester_id");
    $count_result = mysqli_fetch_assoc($check);

    if ($count_result['count'] > 0) {
      $message = "Không thể xóa học kỳ này vì đang có " . $count_result['count'] . " môn học sử dụng!";
      $message_type = "danger";
    } else {
      if (mysqli_query($conn, "DELETE FROM hoc_ky WHERE id = $semester_id")) {
        $message = "Xóa học kỳ thành công!";
        $message_type = "success";

        $hoc_ky_result = mysqli_query($conn, "SELECT id, ten_hoc_ky FROM hoc_ky ORDER BY id DESC");
        $hoc_ky_list = mysqli_fetch_all($hoc_ky_result, MYSQLI_ASSOC);
      } else {
        $message = "Lỗi: " . mysqli_error($conn);
        $message_type = "danger";
      }
    }
  }
}

// ===== XỬ LÝ MÔN HỌC (Thêm/Sửa/Xóa) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['semester_action'])) {

  $action = $_POST['action'] ?? '';

  $ten_mon = trim($_POST['ten_mon'] ?? '');
  $ten_gv = trim($_POST['ten_gv'] ?? '');
  $thu_display = $_POST['thu'] ?? '';
  $gio = trim($_POST['gio'] ?? '');
  $hoc_ky_id = (int)($_POST['hoc_ky_id'] ?? 0);
  $ngay_bat_dau = $_POST['ngay_bat_dau'] ?? '';
  $ngay_ket_thuc = $_POST['ngay_ket_thuc'] ?? '';
  $dia_diem = trim($_POST['dia_diem'] ?? '');

  $thu_db = convertDayToDbValue($thu_display);

  // Parse giờ học từ format "7h00-9h30" sang "07:00:00" và "09:30:00"
  $gio_bat_dau = '';
  $gio_ket_thuc = '';
  if (preg_match('/(\d{1,2}h\d{2})-(\d{1,2}h\d{2})/', $gio, $m)) {
    $gio_bat_dau = str_replace('h', ':', $m[1]) . ':00';
    $gio_ket_thuc = str_replace('h', ':', $m[2]) . ':00';
    // Thêm số 0 phía trước nếu giờ < 10 (VD: "7:00:00" -> "07:00:00")
    $gio_bat_dau = preg_replace('/^(\d):/', '0$1:', $gio_bat_dau);
    $gio_ket_thuc = preg_replace('/^(\d):/', '0$1:', $gio_ket_thuc);
  }

  if ($action === 'add' || $action === 'edit') {

    $id = (int)($_POST['id'] ?? 0);
    if (empty($ten_mon) || empty($ten_gv) || !$thu_db || empty($gio_bat_dau) || $hoc_ky_id === 0 || empty($ngay_bat_dau) || empty($ngay_ket_thuc)) {
      $message = "Vui lòng điền đầy đủ các trường bắt buộc!";
      $message_type = "danger";
    } elseif (strtotime($ngay_ket_thuc) < strtotime($ngay_bat_dau)) {
      $message = "Ngày kết thúc phải sau ngày bắt đầu!";
      $message_type = "danger";
    } else {
      $ten_mon_esc = mysqli_real_escape_string($conn, $ten_mon);
      $ten_gv_esc = mysqli_real_escape_string($conn, $ten_gv);
      $dia_diem_esc = mysqli_real_escape_string($conn, $dia_diem);

      if ($action === 'add') {
        $sql = "INSERT INTO mon_hoc
                (ten_mon, ten_gv, thu, gio_bat_dau, gio_ket_thuc, hoc_ky_id, ngay_bat_dau, ngay_ket_thuc, dia_diem)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssisss", $ten_mon_esc, $ten_gv_esc, $thu_db, $gio_bat_dau, $gio_ket_thuc, $hoc_ky_id, $ngay_bat_dau, $ngay_ket_thuc, $dia_diem_esc);
        $msg_success = "Thêm môn học thành công!";
      } else {
        $sql = "UPDATE mon_hoc SET
                ten_mon=?, ten_gv=?, thu=?, gio_bat_dau=?, gio_ket_thuc=?, hoc_ky_id=?, ngay_bat_dau=?, ngay_ket_thuc=?, dia_diem=?
                WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssisssi", $ten_mon_esc, $ten_gv_esc, $thu_db, $gio_bat_dau, $gio_ket_thuc, $hoc_ky_id, $ngay_bat_dau, $ngay_ket_thuc, $dia_diem_esc, $id);
        $msg_success = "Cập nhật môn học thành công!";
      }

      if (mysqli_stmt_execute($stmt)) {
        $message = $msg_success;
        $message_type = "success";

        // Giữ lại các tham số GET hiện tại (phân trang, filter)
        $redirect_params = $_GET;
        unset($redirect_params['edit']); // Xóa tham số edit
        $redirect_params['msg'] = $message;
        $redirect_params['type'] = $message_type;

        // Redirect về trang quản lý với thông báo
        $redirect_query = http_build_query($redirect_params);
        header("Location: manage_data_learing.php?" . $redirect_query);
        exit();
      } else {
        $message = "Lỗi cơ sở dữ liệu: " . mysqli_error($conn);
        $message_type = "danger";
      }

      mysqli_stmt_close($stmt);
    }
  }

  if ($action === 'delete' && !empty($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];

    if (mysqli_query($conn, "DELETE FROM mon_hoc WHERE id = $id")) {
      $message = "Xóa môn học thành công!";
      $message_type = "success";
    } else {
      $message = "Không thể xóa môn học này! (Có thể do ràng buộc khóa ngoại)";
      $message_type = "danger";
    }

    $redirect_params = $_GET;
    unset($redirect_params['edit']);
    $redirect_params['msg'] = $message;
    $redirect_params['type'] = $message_type;

    $redirect_query = http_build_query($redirect_params);
    header("Location: manage_data_learing.php?" . $redirect_query);
    exit();
  }
}

// Lấy thông báo từ URL (sau khi redirect)
if (isset($_GET['msg']) && isset($_GET['type'])) {
  $message = htmlspecialchars($_GET['msg']);
  $message_type = htmlspecialchars($_GET['type']);
}

// ===== PHÂN TRANG & BỘ LỌC DANH SÁCH MÔN HỌC =====
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;

$search = trim($_GET['search'] ?? '');
$semester_id = (int)($_GET['semester_id'] ?? 0);

$where = "WHERE 1=1";
if ($search) {
  $search_escaped = mysqli_real_escape_string($conn, $search);
  $where .= " AND (mh.ten_mon LIKE '%{$search_escaped}%' OR mh.ten_gv LIKE '%{$search_escaped}%')";
}

if ($semester_id) {
  $where .= " AND mh.hoc_ky_id = $semester_id";
}

// Đếm tổng số môn học (để tính số trang)
$count_result = mysqli_query($conn, "SELECT COUNT(*) FROM mon_hoc mh $where");
$total = mysqli_fetch_array($count_result)[0];
$pages = ceil($total / $perPage);

$sql_list = "SELECT mh.*, hk.ten_hoc_ky FROM mon_hoc mh
             LEFT JOIN hoc_ky hk ON mh.hoc_ky_id = hk.id
             $where
             ORDER BY mh.ngay_bat_dau DESC
             LIMIT $offset, $perPage";
$result = mysqli_query($conn, $sql_list);
$subjects = mysqli_fetch_all($result, MYSQLI_ASSOC);

$edit_subject = null;
if (isset($_GET['edit'])) {
  $edit_id = (int)$_GET['edit'];
  $edit_result = mysqli_query($conn, "SELECT * FROM mon_hoc WHERE id = $edit_id");
  $edit_subject = mysqli_fetch_assoc($edit_result);
}

$edit_semester = null;
if (isset($_GET['edit_semester'])) {
  $edit_semester_id = (int)$_GET['edit_semester'];
  $edit_semester_result = mysqli_query($conn, "SELECT * FROM hoc_ky WHERE id = $edit_semester_id");
  $edit_semester = mysqli_fetch_assoc($edit_semester_result);
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản Lý Môn Học - Admin</title>
  <link rel="icon" type="image/png" href="../../images/logo.png">
  <link rel="stylesheet" href="../../css/header-footer.css">
  <link rel="stylesheet" href="../../css/style.css">
</head>

<body>
  <?php include("../partials/header.php"); ?>

  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEditModal" title="Thêm môn học">
          <i class="bi bi-plus-lg"></i> Thêm môn học
        </button>

        <button class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#semesterModal" title="Quản lý học kỳ">
          <i class="bi bi-calendar3"></i> Quản lý học kỳ
        </button>
      </div>
    </div>

    <!-- Thông báo -->
    <?php if ($message): ?>
      <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- Form tìm kiếm và lọc môn học -->
    <form method="get" class="card p-3 mb-4">
      <div class="row g-3 align-items-end">
        <div class="col-md-5">
          <input type="text" name="search" class="form-control" placeholder="Tìm kiếm tên môn, giảng viên..." value="<?= htmlspecialchars($search) ?>">
        </div>

        <div class="col-md-4">
          <select name="semester_id" class="form-select">
            <option value="0">Tất cả học kỳ</option>
            <?php foreach ($hoc_ky_list as $hk): ?>
              <option value="<?= $hk['id'] ?>" <?= $semester_id == $hk['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($hk['ten_hoc_ky']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-3">
          <button type="submit" class="btn btn-outline-primary w-100" title="Tìm kiếm">
            <i class="bi bi-search"></i>
          </button>
        </div>
      </div>
    </form>

    <div class="row">
      <?php if (empty($subjects)): ?>
        <div class="col-12 text-center py-5 text-muted">
          <h5>Không có môn học nào phù hợp.</h5>
        </div>

      <?php else: ?>
        <?php foreach ($subjects as $s): ?>
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="card card-subject h-100 shadow-sm border-0">
              <div class="card-body">
                <h6 class="fw-bold"><?= htmlspecialchars($s['ten_mon']) ?></h6>

                <p class="text-muted small mb-1">GV: <?= htmlspecialchars($s['ten_gv']) ?></p>

                <p class="mb-1">
                  <strong><?= convertDayToDisplay($s['thu']) ?></strong>
                  • <?= date('H:i', strtotime($s['gio_bat_dau'])) ?> - <?= date('H:i', strtotime($s['gio_ket_thuc'])) ?>
                </p>

                <p class="mb-2 text-muted">Phòng: <?= htmlspecialchars($s['dia_diem'] ?: 'Chưa xác định') ?></p>

                <p class="mb-2 small">
                  <span class="badge bg-secondary"><?= htmlspecialchars($s['ten_hoc_ky'] ?? 'N/A') ?></span>
                </p>

                <div class="d-flex justify-content-between align-items-center">
                  <span class="badge <?= strtotime($s['ngay_ket_thuc']) >= time() ? 'bg-success' : 'bg-secondary' ?>">
                    <?= strtotime($s['ngay_ket_thuc']) >= time() ? 'Đang diễn ra' : 'Đã kết thúc' ?>
                  </span>

                  <div>
                    <a href="?edit=<?= $s['id'] ?>&page=<?= $page ?>&search=<?= urlencode($search) ?>&semester_id=<?= urlencode($semester_id) ?>"
                      class="btn btn-warning btn-sm" title="Sửa">
                      <i class="bi bi-pencil-square"></i>
                    </a>

                    <form method="post" style="display: inline;" onsubmit="return confirm('Bạn có chắc muốn xóa môn học <?= addslashes(htmlspecialchars($s['ten_mon'])) ?>?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="delete_id" value="<?= $s['id'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm ms-1" title="Xóa">
                        <i class="bi bi-trash3"></i>
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if ($pages > 1): ?>
      <nav class="mt-4">
        <ul class="pagination justify-content-center">
          <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&semester_id=<?= urlencode($semester_id) ?>">Trước</a>
          </li>

          <?php
          $start = max(1, $page - 2);
          $end = min($pages, $page + 2);
          for ($i = $start; $i <= $end; $i++):
          ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&semester_id=<?= urlencode($semester_id) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>

          <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&semester_id=<?= urlencode($semester_id) ?>">Sau</a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
  </div>

  <!-- Modal môn học -->
  <div class="modal fade" id="addEditModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form method="post">
          <div class="modal-header">
            <h5 class="modal-title"><?= $edit_subject ? 'Sửa môn học' : 'Thêm môn học mới' ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <input type="hidden" name="action" value="<?= $edit_subject ? 'edit' : 'add' ?>">
            <input type="hidden" name="id" value="<?= $edit_subject['id'] ?? '' ?>">

            <?php
            if ($edit_subject) {
              $gio_value = date('H:i', strtotime($edit_subject['gio_bat_dau'])) . ' - ' . date('H:i', strtotime($edit_subject['gio_ket_thuc']));
            ?>
              <input type="hidden" name="page" value="<?= $page ?>">
              <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
              <input type="hidden" name="semester_id" value="<?= htmlspecialchars($semester_id) ?>">
            <?php } ?>
            <div class="row g-3">
              <div class="col-md-6">
                <label>Tên môn học <span class="text-danger">*</span></label>
                <input type="text" name="ten_mon" class="form-control" required value="<?= $edit_subject['ten_mon'] ?? '' ?>">
              </div>

              <div class="col-md-6">
                <label>Giảng viên <span class="text-danger">*</span></label>
                <input type="text" name="ten_gv" class="form-control" required value="<?= $edit_subject['ten_gv'] ?? '' ?>">
              </div>

              <div class="col-md-4">
                <label>Thứ <span class="text-danger">*</span></label>
                <select name="thu" class="form-select" required>
                  <option value="">Chọn thứ</option>
                  <?php
                  $days = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
                  foreach ($days as $d) {
                    $current_day_db = convertDayToDbValue($d);
                    $is_selected = (isset($edit_subject['thu']) && $edit_subject['thu'] == $current_day_db) ? 'selected' : '';
                  ?>
                    <option value="<?= $d ?>" <?= $is_selected ?>><?= $d ?></option>
                  <?php } ?>
                </select>
              </div>

              <div class="col-md-4">
                <label>Giờ học <span class="text-danger">*</span></label>
                <select name="gio" class="form-select" required>
                  <option value="">Chọn khung giờ</option>
                  <optgroup label="Buổi sáng">
                    <option value="7h30-9h20" <?= (isset($gio_value) && $gio_value == '07:30 - 09:20') ? 'selected' : '' ?>>7h30 - 9h20</option>
                    <option value="9h30-11h20" <?= (isset($gio_value) && $gio_value == '09:30 - 11:20') ? 'selected' : '' ?>>9h30 - 11h20</option>
                    <option value="7h30-11h20" <?= (isset($gio_value) && $gio_value == '07:30 - 11:20') ? 'selected' : '' ?>>7h30 - 11h20</option>
                  </optgroup>
                  <optgroup label="Buổi chiều">
                    <option value="13h00-14h50" <?= (isset($gio_value) && $gio_value == '13:00 - 14:50') ? 'selected' : '' ?>>13h00 - 14h50</option>
                    <option value="15h00-16h50" <?= (isset($gio_value) && $gio_value == '15:00 - 16:50') ? 'selected' : '' ?>>15h00 - 16h50</option>
                    <option value="13h00-16h50" <?= (isset($gio_value) && $gio_value == '13:00 - 16:50') ? 'selected' : '' ?>>13h00 - 16h50</option>
                  </optgroup>
                </select>
              </div>

              <div class="col-md-4">
                <label>Học kỳ <span class="text-danger">*</span></label>
                <select name="hoc_ky_id" class="form-select" required>
                  <option value="">Chọn học kỳ</option>
                  <?php foreach ($hoc_ky_list as $hk) {
                    $is_selected = (isset($edit_subject['hoc_ky_id']) && $edit_subject['hoc_ky_id'] == $hk['id']) ? 'selected' : '';
                  ?>
                    <option value="<?= $hk['id'] ?>" <?= $is_selected ?>>
                      <?= htmlspecialchars($hk['ten_hoc_ky']) ?>
                    </option>
                  <?php } ?>
                </select>
              </div>

              <div class="col-md-6">
                <label>Ngày bắt đầu <span class="text-danger">*</span></label>
                <input type="date" name="ngay_bat_dau" class="form-control" required value="<?= $edit_subject['ngay_bat_dau'] ?? '' ?>">
              </div>

              <div class="col-md-6">
                <label>Ngày kết thúc <span class="text-danger">*</span></label>
                <input type="date" name="ngay_ket_thuc" class="form-control" required value="<?= $edit_subject['ngay_ket_thuc'] ?? '' ?>">
              </div>

              <div class="col-md-12">
                <label>Phòng học / Địa điểm</label>
                <input type="text" name="dia_diem" class="form-control" value="<?= $edit_subject['dia_diem'] ?? '' ?>">
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg"></i> Lưu
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal học kỳ  -->
  <div class="modal fade" id="semesterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Quản lý học kỳ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="card mb-3">
            <div class="card-body">
              <h6 class="card-title">Thêm học kỳ mới</h6>
              <form method="post" class="row g-2">
                <input type="hidden" name="semester_action" value="add_semester">
                <div class="col-md-8">
                  <input type="text" name="ten_hoc_ky" class="form-control" placeholder="Ví dụ: HK1 2024-2025" required>
                  <small class="text-muted">Gợi ý: HK1 2024-2025, HK2 2024-2025, HK1 2023-2024...</small>
                </div>
                <div class="col-md- comment4">
                  <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-plus-lg"></i> Thêm
                  </button>
                </div>
              </form>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <h6 class="card-title">Danh sách học kỳ</h6>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>STT</th>
                      <th>Tên học kỳ</th>
                      <th>Số môn học</th>
                      <th>Thao tác</th>
                    </tr>
                  </thead>
                  чая <tbody>
                    <?php
                    $stt = 1;
                    foreach ($hoc_ky_list as $hk):
                      $count_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM mon_hoc WHERE hoc_ky_id = " . $hk['id']);
                      $count_data = mysqli_fetch_assoc($count_query);
                      $mon_hoc_count = $count_data['count'];
                    ?>
                      <tr>
                        <td><?= $stt++ ?></td>
                        <td>
                          <?php if (isset($_GET['edit_semester']) && $_GET['edit_semester'] == $hk['id']): ?>
                            <form method="post" class="d-inline">
                              <input type="hidden" name="semester_action" value="edit_semester">
                              <input type="hidden" name="semester_id" value="<?= $hk['id'] ?>">
                              <div class="input-group input-group-sm">
                                <input type="text" name="ten_hoc_ky" class="form-control" value="<?= htmlspecialchars($hk['ten_hoc_ky']) ?>" required>
                                <button type="submit" class="btn btn-success btn-sm">
                                  <i class="bi bi-check-lg"></i>
                                </button>
                                <a href="manage_data_learing.php" class="btn btn-secondary btn-sm">
                                  <i class="bi bi-x-lg"></i>
                                </a>
                              </div>
                            </form>
                          <?php else: ?>
                            <?= htmlspecialchars($hk['ten_hoc_ky']) ?>
                          <?php endif; ?>
                        </td>
                        <td>
                          <span class="badge bg-info"><?= $mon_hoc_count ?> môn</span>
                        </td>
                        <td>
                          <?php if (!isset($_GET['edit_semester']) || $_GET['edit_semester'] != $hk['id']): ?>
                            <a href="?edit_semester=<?= $hk['id'] ?>" class="btn btn-warning btn-sm" title="Sửa">
                              <i class="bi bi-pencil-square"></i>
                            </a>
                            <form method="post" style="display: inline;" onsubmit="return confirm('Bạn có chắc muốn xóa học kỳ <?= addslashes(htmlspecialchars($hk['ten_hoc_ky'])) ?>?<?= $mon_hoc_count > 0 ? '\n\nCảnh báo: Có ' . $mon_hoc_count . ' môn học đang sử dụng học kỳ này!' : '' ?>');">
                              <input type="hidden" name="semester_action" value="delete_semester">
                              <input type="hidden" name="semester_id" value="<?= $hk['id'] ?>">
                              <button type="submit" class="btn btn-danger btn-sm ms-1" title="Xóa" <?= $mon_hoc_count > 0 ? 'disabled' : '' ?>>
                                <i class="bi bi-trash3"></i>
                              </button>
                            </form>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>

                    <?php if (empty($hoc_ky_list)): ?>
                      <tr>
                        <td colspan="4" class="text-center text-muted">Chưa có học kỳ nào</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
        </div>
      </div>
    </div>
  </div>

  <?php if ($edit_subject): ?>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        new bootstrap.Modal(document.getElementById('addEditModal')).show();
      });
    </script>
  <?php endif; ?>

  <?php if ($edit_semester || isset($_GET['show_semester_modal'])): ?>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        new bootstrap.Modal(document.getElementById('semesterModal')).show();
      });
    </script>
  <?php endif; ?>

  <?php include("../partials/footer.php"); ?>

</body>

</html>