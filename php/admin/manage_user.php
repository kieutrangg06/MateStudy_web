<?php
require_once('../config.php');
require_permission([ROLE_ADMIN], '../login.php');

$message = '';
if (isset($_GET['msg'])) {
  $message = htmlspecialchars($_GET['msg']);
}

// ===== XỬ LÝ HÀNH ĐỘNG (Khóa/Mở khóa/Xóa) =====
if ($_POST) {
  $action = $_POST['action'] ?? '';
  $id = (int)($_POST['user_id'] ?? 0);

  if ($action === 'lock' && $id) {
    $sql = "UPDATE nguoi_dung SET trang_thai='bi_khoa' WHERE id=$id AND vai_tro='sinh_vien'";
    mysqli_query($conn, $sql);
    $message = "Đã khóa sinh viên!";
  } elseif ($action === 'unlock' && $id) {
    $sql = "UPDATE nguoi_dung SET trang_thai='hoat_dong' WHERE id=$id AND vai_tro='sinh_vien'";
    mysqli_query($conn, $sql);
    $message = "Đã mở khóa sinh viên!";
  } elseif ($action === 'delete' && $id) {
    $sql = "DELETE FROM nguoi_dung WHERE id=$id AND vai_tro='sinh_vien'";
    mysqli_query($conn, $sql);
    $message = "Đã xóa sinh viên!";
  }

  header("Location: manage_user.php?msg=" . urlencode($message)); //Tránh lỗi URL khi có dấu cách, dấu tiếng Việt, ký tự đặc biệt.
  exit();
}

// ===== PHÂN TRANG & BỘ LỌC =====
// Xử lý phân trang
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 5;
$offset = ($page - 1) * $limit;

// Lấy các tham số lọc từ query string
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? 'all';
$nien_khoa_filter = $_GET['nien_khoa'] ?? 'all';

$nien_khoa_query = "SELECT DISTINCT nien_khoa FROM nguoi_dung WHERE vai_tro = 'sinh_vien' AND nien_khoa IS NOT NULL AND nien_khoa != '' ORDER BY nien_khoa DESC";
$nien_khoa_result = mysqli_query($conn, $nien_khoa_query);
$nien_khoa_list = mysqli_fetch_all($nien_khoa_result, MYSQLI_ASSOC);

$where = "WHERE vai_tro = 'sinh_vien' AND trang_thai != 'da_xoa'";

// Thêm điều kiện tìm kiếm
if ($search) {
  $search_escaped = mysqli_real_escape_string($conn, $search); //escape (thoát) các ký tự đặc biệt
  $where .= " AND (ten_dang_nhap LIKE '%$search_escaped%' OR email LIKE '%$search_escaped%')";
}

if ($status !== 'all') {
  $status_escaped = mysqli_real_escape_string($conn, $status);
  $where .= " AND trang_thai = '$status_escaped'";
}

if ($nien_khoa_filter !== 'all') {
  $nien_khoa_escaped = mysqli_real_escape_string($conn, $nien_khoa_filter);
  $where .= " AND nien_khoa = '$nien_khoa_escaped'";
}

// ===== LẤY DANH SÁCH SINH VIÊN =====
$count_result = mysqli_query($conn, "SELECT COUNT(*) FROM nguoi_dung $where");
$total = mysqli_fetch_array($count_result)[0];
$pages = ceil($total / $limit);

$sql = "SELECT * FROM nguoi_dung $where ORDER BY id DESC LIMIT $offset, $limit";
$result = mysqli_query($conn, $sql);
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản Lý Sinh Viên - Admin</title>
  <link rel="icon" type="image/png" href="../../images/logo.png">
  <link rel="stylesheet" href="../../css/header-footer.css">
  <link rel="stylesheet" href="../../css/style.css">

  <style>
    :root {
      --primary: #4361ee;
    }

    .user-avatar {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      object-fit: cover;
    }

    .status-badge {
      padding: .35rem .75rem;
      border-radius: 50px;
      font-size: .85rem;
      font-weight: 600;
    }

    .status-active {
      background: #d4edda;
      color: #155724;
    }

    .status-locked {
      background: #fff3cd;
      color: #856404;
    }
  </style>
</head>

<body class="bg-light">
  <?php include("../partials/header.php"); ?>

  <div class="container mt-4 pb-5">

    <?php if ($message): ?>
      <div class="alert alert-success alert-dismissible fade show">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="card mb-4">
      <div class="card-body">
        <form method="get" class="row g-3 align-items-end justify-content-center">

          <div class="col-md-5 col-lg-4">
            <input type="text" name="search" class="form-control" placeholder="Tìm tên đăng nhập, email..." value="<?= htmlspecialchars($search) ?>">
          </div>

          <div class="col-md-3 col-lg-2">
            <select name="status" class="form-select">
              <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Tất cả trạng thái</option>
              <option value="hoat_dong" <?= $status === 'hoat_dong' ? 'selected' : '' ?>>Hoạt động</option>
              <option value="bi_khoa" <?= $status === 'bi_khoa' ? 'selected' : '' ?>>Bị khóa</option>
            </select>
          </div>

          <div class="col-md-3 col-lg-2">
            <select name="nien_khoa" class="form-select">
              <option value="all" <?= $nien_khoa_filter === 'all' ? 'selected' : '' ?>>Tất cả niên khóa</option>
              <?php foreach ($nien_khoa_list as $nk): ?>
                <option value="<?= htmlspecialchars($nk['nien_khoa']) ?>" <?= $nien_khoa_filter === $nk['nien_khoa'] ? 'selected' : '' ?>>
                  K<?= substr($nk['nien_khoa'], 2) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-auto">
            <button type="submit" class="btn btn-primary" title="Lọc">
              <i class="bi bi-funnel"></i>
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-primary">
            <tr>
              <th>Sinh viên</th>
              <th>Niên khóa</th>
              <th>Trạng thái</th>
              <th width="180">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u):

              if ($u['anh_dai_dien']) {
                $avatar = "../../{$u['anh_dai_dien']}";
              } else {
                $avatar = "https://ui-avatars.com/api/?name=" . urlencode($u['ten_dang_nhap']) . "&background=4361ee&color=fff&bold=1";
              }

              if ($u['trang_thai'] === 'hoat_dong') {
                $status_class = 'status-active';
                $status_text = 'Hoạt động';
              } else {
                $status_class = 'status-locked';
                $status_text = 'Bị khóa';
              }
            ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-3">
                    <img src="<?= $avatar ?>" class="user-avatar" alt="">
                    <div>
                      <div class="fw-bold"><?= htmlspecialchars($u['ten_dang_nhap']) ?></div>
                      <small class="text-muted"><?= htmlspecialchars($u['email']) ?></small>
                    </div>
                  </div>
                </td>

                <td>
                  <?php
                  if ($u['nien_khoa']) {
                    echo 'K' . substr($u['nien_khoa'], 2);
                  } else {
                    echo '—';
                  }
                  ?>
                </td>

                <td>
                  <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                </td>

                <td class="text-center">

                  <?php if ($u['trang_thai'] === 'hoat_dong'): ?>
                    <form method="post" style="display: inline;" onsubmit="return confirm('Khóa sinh viên này?');">
                      <input type="hidden" name="action" value="lock">
                      <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-warning" title="Khóa">
                        <i class="bi bi-lock"></i>
                      </button>
                    </form>
                  <?php else: ?>
                    <form method="post" style="display: inline;" onsubmit="return confirm('Mở khóa sinh viên này?');">
                      <input type="hidden" name="action" value="unlock">
                      <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-success" title="Mở khóa">
                        <i class="bi bi-unlock"></i>
                      </button>
                    </form>
                  <?php endif; ?>

                  <form method="post" style="display: inline;" onsubmit="return confirm('Xóa sinh viên này?\nHành động không thể hoàn tác!');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger ms-1" title="Xóa">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php if ($pages > 1): ?>
      <nav class="mt-4">
        <ul class="pagination justify-content-center">

          <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>&nien_khoa=<?= urlencode($nien_khoa_filter) ?>">Trước</a>
          </li>

          <?php
          $start = max(1, $page - 2);
          $end = min($pages, $page + 2);
          for ($i = $start; $i <= $end; $i++):
          ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>&nien_khoa=<?= urlencode($nien_khoa_filter) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>

          <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>&nien_khoa=<?= urlencode($nien_khoa_filter) ?>">Sau</a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>

  </div>

  <?php include("../partials/footer.php"); ?>

</body>

</html>