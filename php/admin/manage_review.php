<?php
require_once('../config.php');
require_permission([ROLE_ADMIN], '../login.php');

$message = '';
$message_type = '';

// ===== XỬ LÝ HÀNH ĐỘNG (Duyệt/Ẩn/Hiện lại/Xóa đánh giá) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    $valid_actions = ['approve', 'hide', 'unhide', 'delete'];

    if (!in_array($action, $valid_actions) || $id <= 0) {
        $message = "Thao tác không hợp lệ!";
        $message_type = "danger";
    } else {
        $info_sql = "SELECT dg.sinh_vien_id, COALESCE(m.ten_mon, 'Môn đã xóa') AS ten_mon
                     FROM danh_gia_mon_giang_vien dg
                     LEFT JOIN mon_hoc m ON dg.mon_hoc_id = m.id
                     WHERE dg.id = ?";
        $info_stmt = mysqli_prepare($conn, $info_sql);
        mysqli_stmt_bind_param($info_stmt, "i", $id);
        mysqli_stmt_execute($info_stmt);
        $info_result = mysqli_stmt_get_result($info_stmt);
        $review_info = mysqli_fetch_assoc($info_result);
        mysqli_stmt_close($info_stmt);

        if (!$review_info) {
            $message = "Đánh giá không tồn tại!";
            $message_type = "danger";
        } else {
            $sinh_vien_id = $review_info['sinh_vien_id'];
            $ten_mon = $review_info['ten_mon'];

            // Xử lý hành động
            if ($action === 'delete') {
                $sql = "DELETE FROM danh_gia_mon_giang_vien WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $id);
                $msg_success = "Xóa";
                $tieu_de_tb = "Đánh giá bị xóa";
                $noi_dung_tb = "Đánh giá của bạn về môn \"$ten_mon\" đã bị xóa vĩnh viễn bởi quản trị viên.";
            } else {
                if ($action === 'approve') {
                    $status = 'da_duyet';
                    $msg_success = "Duyệt";
                    $tieu_de_tb = "Đánh giá được duyệt";
                    $noi_dung_tb = "Đánh giá của bạn về môn \"$ten_mon\" đã được duyệt và hiển thị công khai.";
                } elseif ($action === 'hide') {
                    $status = 'bi_an';
                    $msg_success = "Ẩn";
                    $tieu_de_tb = "Đánh giá bị ẩn";
                    $noi_dung_tb = "Đánh giá của bạn về môn \"$ten_mon\" đã bị ẩn bởi quản trị viên.";
                } else {
                    $status = 'da_duyet';
                    $msg_success = "Hiện lại";
                    $tieu_de_tb = "Đánh giá đã được hiện lại";
                    $noi_dung_tb = "Đánh giá của bạn về môn \"$ten_mon\" đã được khôi phục và hiển thị lại.";
                }

                $sql = "UPDATE danh_gia_mon_giang_vien SET trang_thai = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "si", $status, $id);
            }

            if (mysqli_stmt_execute($stmt)) {
                // Gửi thông báo cho sinh viên
                if ($sinh_vien_id) {
                    $notif_sql = "INSERT INTO thong_bao 
                        (sinh_vien_id, tieu_de, noi_dung, loai, da_doc, id_lien_quan, loai_lien_quan, ngay_tao)
                        VALUES (?, ?, ?, 'bai_viet', 0, ?, 'danh_gia', NOW())";
                    $notif_stmt = mysqli_prepare($conn, $notif_sql);
                    mysqli_stmt_bind_param($notif_stmt, "issi", $sinh_vien_id, $tieu_de_tb, $noi_dung_tb, $id);
                    mysqli_stmt_execute($notif_stmt);
                    mysqli_stmt_close($notif_stmt);
                }

                $message = "$msg_success thành công!";
                $message_type = "success";
            } else {
                $message = "Lỗi khi thực hiện thao tác!";
                $message_type = "danger";
            }

            mysqli_stmt_close($stmt);
        }
    }

    $query = $_GET;
    $query['msg'] = $message;
    $query['type'] = $message_type;
    header("Location: manage_review.php?" . http_build_query($query));
    exit();
}

// Xử lý thông báo từ redirect
if (isset($_GET['msg'], $_GET['type'])) {
    $message = htmlspecialchars($_GET['msg']);
    $message_type = htmlspecialchars($_GET['type']);

    // Xóa msg và type khỏi URL để sạch hơn (tùy chọn)
    unset($_GET['msg'], $_GET['type']);
}

// ===== LỌC VÀ TÌM KIẾM =====
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';

$where = "WHERE 1=1";
$params = [];
$types = "";

if ($search !== '') {
    $where .= " AND (m.ten_mon LIKE ? OR m.ten_gv LIKE ? OR nd.ten_dang_nhap LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

$valid_statuses = ['cho_duyet', 'da_duyet', 'bi_an'];
if (in_array($status_filter, $valid_statuses)) {
    $where .= " AND dg.trang_thai = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$sql = "SELECT dg.*, COALESCE(m.ten_mon, 'Môn đã xóa') AS ten_mon, m.ten_gv, nd.ten_dang_nhap AS sinh_vien 
        FROM danh_gia_mon_giang_vien dg
        LEFT JOIN mon_hoc m ON dg.mon_hoc_id = m.id
        LEFT JOIN nguoi_dung nd ON dg.sinh_vien_id = nd.id
        $where
        ORDER BY dg.ngay_dang DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($types && $params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$reviews = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đánh Giá - Admin</title>
    <link rel="icon" type="image/png" href="../../images/logo.png">
    <link rel="stylesheet" href="../../css/header-footer.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .review-table-container {
            max-height: 70vh;
            overflow-y: auto;
            overflow-x: auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .review-table-container table {
            margin-bottom: 0;
            width: 100%;
        }

        .review-table-container thead th {
            position: sticky;
            top: 0;
            background: #f8f9fa;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>

<body>
    <?php include("../partials/header.php"); ?>

    <div class="container mt-4">

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Form tìm kiếm và lọc -->
        <form method="get" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Tìm môn học, giảng viên, sinh viên..." value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="cho_duyet" <?= $status_filter === 'cho_duyet' ? 'selected' : '' ?>>Chờ duyệt</option>
                        <option value="da_duyet" <?= $status_filter === 'da_duyet' ? 'selected' : '' ?>>Đã duyệt</option>
                        <option value="bi_an" <?= $status_filter === 'bi_an' ? 'selected' : '' ?>>Bị ẩn</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="manage_review.php" class="btn btn-secondary w-100"><i class="bi bi-arrow-clockwise"></i></a>
                </div>
            </div>
        </form>

        <!-- Bảng danh sách đánh giá -->
        <div class="review-table-container">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Môn học / Giảng viên</th>
                        <th>Sinh viên</th>
                        <th>Nội dung</th>
                        <th>Điểm</th>
                        <th>Ngày đăng</th>
                        <th>Trạng thái</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reviews)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Không có đánh giá nào.</td>
                        </tr>
                        <?php else: foreach ($reviews as $r): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($r['ten_mon']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($r['ten_gv'] ?? 'N/A') ?></small>
                                </td>
                                <td><?= htmlspecialchars($r['sinh_vien'] ?? 'Ẩn danh') ?></td>
                                <td>
                                    <?php
                                    $noi_dung_cat = mb_substr($r['noi_dung'], 0, 80);
                                    echo nl2br(htmlspecialchars($noi_dung_cat));
                                    if (mb_strlen($r['noi_dung']) > 80) echo '...';
                                    ?>
                                </td>
                                <td>
                                    <?= str_repeat('★', $r['diem_sao']) . str_repeat('☆', 5 - $r['diem_sao']) ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($r['ngay_dang'])) ?></td>
                                <td>
                                    <?php if ($r['trang_thai'] === 'cho_duyet'): ?>
                                        <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                    <?php elseif ($r['trang_thai'] === 'da_duyet'): ?>
                                        <span class="badge bg-success">Đã duyệt</span>
                                    <?php elseif ($r['trang_thai'] === 'bi_an'): ?>
                                        <span class="badge bg-secondary">Bị ẩn</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <!-- Nút hành động với confirm cho xóa -->
                                    <?php if ($r['trang_thai'] === 'cho_duyet'): ?>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Duyệt đánh giá này?');">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm" title="Duyệt">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($r['trang_thai'] === 'da_duyet'): ?>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Ẩn đánh giá này?');">
                                            <input type="hidden" name="action" value="hide">
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                            <button type="submit" class="btn btn-warning btn-sm" title="Ẩn">
                                                <i class="bi bi-eye-slash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($r['trang_thai'] === 'bi_an'): ?>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Hiện lại đánh giá này?');">
                                            <input type="hidden" name="action" value="unhide">
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                            <button type="submit" class="btn btn-info btn-sm" title="Hiện lại">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="post" class="d-inline" onsubmit="return confirm('XÓA VĨNH VIỄN đánh giá này?\nHành động KHÔNG THỂ HOÀN TÁC!');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Xóa vĩnh viễn">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include("../partials/footer.php"); ?>
</body>

</html>