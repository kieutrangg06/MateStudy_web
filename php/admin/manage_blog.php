<?php
require_once('../config.php');
require_permission([ROLE_ADMIN], '../login.php');

$thong_bao = '';
$loai_thong_bao = '';

// ===== XỬ LÝ HÀNH ĐỘNG (Duyệt/Ẩn/Hiện lại/Xóa bài viết) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hanh_dong = $_POST['action'] ?? '';
    $bai_viet_id = (int)($_POST['id'] ?? 0);

    $cac_hanh_dong_hop_le = ['approve', 'hide', 'unhide', 'delete'];

    if (!in_array($hanh_dong, $cac_hanh_dong_hop_le) || $bai_viet_id <= 0) {
        $thong_bao = "Thao tác không hợp lệ!";
        $loai_thong_bao = "danger";
    } else {
        $sql_lay_thong_tin = "SELECT tac_gia_id, tieu_de FROM bai_viet_dien_dan WHERE id = ?";
        $stmt_thong_tin = mysqli_prepare($conn, $sql_lay_thong_tin);
        mysqli_stmt_bind_param($stmt_thong_tin, "i", $bai_viet_id);
        mysqli_stmt_execute($stmt_thong_tin);
        $ket_qua_thong_tin = mysqli_stmt_get_result($stmt_thong_tin);
        $thong_tin_bai_viet = mysqli_fetch_assoc($ket_qua_thong_tin);
        mysqli_stmt_close($stmt_thong_tin);

        if (!$thong_tin_bai_viet) {
            $thong_bao = "Bài viết không tồn tại!";
            $loai_thong_bao = "danger";
        } else {
            $tac_gia_id = $thong_tin_bai_viet['tac_gia_id'];
            $tieu_de_bai_viet = $thong_tin_bai_viet['tieu_de'];

            // Xử lý hành động
            if ($hanh_dong === 'delete') {
                $sql = "DELETE FROM bai_viet_dien_dan WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $bai_viet_id);
                $thong_bao_thanh_cong = "Xóa";
            } else {
                if ($hanh_dong === 'approve') {
                    $trang_thai_moi = 'da_duyet';
                    $thong_bao_thanh_cong = "Duyệt";
                } elseif ($hanh_dong === 'hide') {
                    $trang_thai_moi = 'bi_an';
                    $thong_bao_thanh_cong = "Ẩn";
                } else {
                    $trang_thai_moi = 'da_duyet';
                    $thong_bao_thanh_cong = "Hiện lại";
                }
                $sql = "UPDATE bai_viet_dien_dan SET trang_thai = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "si", $trang_thai_moi, $bai_viet_id);
            }

            if (mysqli_stmt_execute($stmt)) {
                // Gửi thông báo cho tác giả
                if ($hanh_dong === 'approve') {
                    $tieu_de_tb = "Bài viết được duyệt";
                    $noi_dung_tb = "Bài viết \"$tieu_de_bai_viet\" của bạn đã được duyệt và hiển thị công khai.";
                } elseif ($hanh_dong === 'hide') {
                    $tieu_de_tb = "Bài viết bị ẩn";
                    $noi_dung_tb = "Bài viết \"$tieu_de_bai_viet\" của bạn đã bị ẩn bởi quản trị viên.";
                } elseif ($hanh_dong === 'unhide') {
                    $tieu_de_tb = "Bài viết đã được hiện lại";
                    $noi_dung_tb = "Bài viết \"$tieu_de_bai_viet\" của bạn đã được khôi phục và hiển thị lại.";
                } else {
                    $tieu_de_tb = "Bài viết bị xóa";
                    $noi_dung_tb = "Bài viết \"$tieu_de_bai_viet\" của bạn đã bị xóa vĩnh viễn bởi quản trị viên.";
                }

                $sql_tb = "INSERT INTO thong_bao (sinh_vien_id, tieu_de, noi_dung, loai, da_doc, id_lien_quan, loai_lien_quan, ngay_tao)
                           VALUES (?, ?, ?, 'bai_viet', 0, ?, 'bai_viet', NOW())";
                $stmt_tb = mysqli_prepare($conn, $sql_tb);
                mysqli_stmt_bind_param($stmt_tb, "issi", $tac_gia_id, $tieu_de_tb, $noi_dung_tb, $bai_viet_id);
                mysqli_stmt_execute($stmt_tb);
                mysqli_stmt_close($stmt_tb);

                $thong_bao = "$thong_bao_thanh_cong thành công!";
                $loai_thong_bao = "success";
            } else {
                $thong_bao = "Lỗi hệ thống!";
                $loai_thong_bao = "danger";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Redirect để tránh submit lại form và hiển thị thông báo
    $params = array_merge($_GET, ['msg' => $thong_bao, 'type' => $loai_thong_bao]);
    header("Location: manage_blog.php?" . http_build_query($params));
    exit();
}

// Lấy thông báo từ URL (sau redirect)
if (isset($_GET['msg'], $_GET['type'])) {
    $thong_bao = htmlspecialchars($_GET['msg']);
    $loai_thong_bao = htmlspecialchars($_GET['type']);
}

// ===== XỬ LÝ TÌM KIẾM & BỘ LỌC =====
$tu_khoa_tim_kiem = trim($_GET['search'] ?? '');
$danh_muc = $_GET['category'] ?? '';
$trang_thai = $_GET['status'] ?? '';

$dieu_kien_where = "WHERE 1=1";
$tham_so = [];
$kieu_du_lieu = "";

if ($tu_khoa_tim_kiem !== '') {
    $dieu_kien_where .= " AND (bv.tieu_de LIKE ? OR nd.ten_dang_nhap LIKE ?)";
    $tu_khoa_like = "%$tu_khoa_tim_kiem%";
    $tham_so[] = $tu_khoa_like;
    $tham_so[] = $tu_khoa_like;
    $kieu_du_lieu .= "ss";
}
if ($danh_muc !== '') {
    $dieu_kien_where .= " AND bv.category = ?";
    $tham_so[] = $danh_muc;
    $kieu_du_lieu .= "s";
}
if ($trang_thai !== '') {
    $dieu_kien_where .= " AND bv.trang_thai = ?";
    $tham_so[] = $trang_thai;
    $kieu_du_lieu .= "s";
}

$sql = "SELECT bv.*, nd.ten_dang_nhap AS tac_gia
        FROM bai_viet_dien_dan bv
        LEFT JOIN nguoi_dung nd ON bv.tac_gia_id = nd.id
        $dieu_kien_where
        ORDER BY bv.ngay_dang DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($kieu_du_lieu && !empty($tham_so)) {
    mysqli_stmt_bind_param($stmt, $kieu_du_lieu, ...$tham_so);
}
mysqli_stmt_execute($stmt);
$ket_qua = mysqli_stmt_get_result($stmt);
$danh_sach_bai_viet = mysqli_fetch_all($ket_qua, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Diễn Đàn - Admin</title>
    <link rel="icon" type="image/png" href="../../images/logo.png">
    <link rel="stylesheet" href="../../css/header-footer.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .table-scroll-wrapper {
            max-height: 70vh;
            overflow-y: auto;
            overflow-x: hidden;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            position: relative;
        }

        .table-scroll-wrapper thead th {
            position: sticky;
            top: 0;
            background: #f8f9fa;
            z-index: 10;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        }

        .table-scroll-wrapper::-webkit-scrollbar {
            width: 10px;
        }

        .table-scroll-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .table-scroll-wrapper::-webkit-scrollbar-thumb {
            background: #c0c0c0;
            border-radius: 10px;
        }

        .table-scroll-wrapper::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>

<body>
    <?php include("../partials/header.php"); ?>

    <div class="container mt-4">
        <!-- Thông báo -->
        <?php if ($thong_bao): ?>
            <div class="alert alert-<?php echo $loai_thong_bao; ?> alert-dismissible fade show">
                <?php echo $thong_bao; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Form tìm kiếm & lọc -->
        <form method="get" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control"
                            placeholder="Tìm tiêu đề, tác giả..."
                            value="<?php echo htmlspecialchars($tu_khoa_tim_kiem); ?>">
                        <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">Tất cả danh mục</option>
                        <option value="forum" <?php echo $danh_muc === 'forum' ? 'selected' : ''; ?>>Diễn đàn</option>
                        <option value="news" <?php echo $danh_muc === 'news' ? 'selected' : ''; ?>>Tin tức</option>
                        <option value="market" <?php echo $danh_muc === 'market' ? 'selected' : ''; ?>>Mua bán</option>
                        <option value="job" <?php echo $danh_muc === 'job' ? 'selected' : ''; ?>>Tuyển dụng</option>
                        <option value="lost" <?php echo $danh_muc === 'lost' ? 'selected' : ''; ?>>Thất lạc</option>
                        <option value="docs" <?php echo $danh_muc === 'docs' ? 'selected' : ''; ?>>Tài liệu</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="cho_duyet" <?php echo $trang_thai === 'cho_duyet' ? 'selected' : ''; ?>>Chờ duyệt</option>
                        <option value="da_duyet" <?php echo $trang_thai === 'da_duyet' ? 'selected' : ''; ?>>Đã duyệt</option>
                        <option value="bi_an" <?php echo $trang_thai === 'bi_an' ? 'selected' : ''; ?>>Bị ẩn</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-secondary w-100" onclick="location.href='manage_blog.php'">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
        </form>

        <!-- Bảng danh sách bài viết -->
        <div class="table-scroll-wrapper">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Tác giả</th>
                        <th>Danh mục</th>
                        <th>Trạng thái</th>
                        <th>Ngày đăng</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $ten_danh_muc = [
                        'forum' => 'Diễn đàn',
                        'news' => 'Tin tức',
                        'market' => 'Mua bán',
                        'job' => 'Tuyển dụng',
                        'lost' => 'Thất lạc',
                        'docs' => 'Tài liệu'
                    ];
                    ?>
                    <?php foreach ($danh_sach_bai_viet as $bai_viet): ?>
                        <tr>
                            <td>
                                <a href="#" class="text-decoration-none fw-semibold"
                                    data-bs-toggle="modal" data-bs-target="#postModal"
                                    onclick="loadPost(<?php echo $bai_viet['id']; ?>)">
                                    <?php echo htmlspecialchars($bai_viet['tieu_de']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($bai_viet['tac_gia'] ?? 'Ẩn danh'); ?></td>
                            <td>
                                <span class="badge bg-primary">
                                    <?php echo $ten_danh_muc[$bai_viet['category']] ?? $bai_viet['category']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($bai_viet['trang_thai'] === 'cho_duyet'): ?>
                                    <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                <?php elseif ($bai_viet['trang_thai'] === 'da_duyet'): ?>
                                    <span class="badge bg-success">Đã duyệt</span>
                                <?php elseif ($bai_viet['trang_thai'] === 'bi_an'): ?>
                                    <span class="badge bg-secondary">Bị ẩn</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($bai_viet['ngay_dang'])); ?></td>
                            <td class="text-center">
                                <?php if ($bai_viet['trang_thai'] === 'cho_duyet'): ?>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="id" value="<?php echo $bai_viet['id']; ?>">
                                        <button type="submit" class="btn btn-success btn-sm" title="Duyệt">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($bai_viet['trang_thai'] === 'da_duyet'): ?>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="action" value="hide">
                                        <input type="hidden" name="id" value="<?php echo $bai_viet['id']; ?>">
                                        <button type="submit" class="btn btn-warning btn-sm" title="Ẩn">
                                            <i class="bi bi-eye-slash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($bai_viet['trang_thai'] === 'bi_an'): ?>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="action" value="unhide">
                                        <input type="hidden" name="id" value="<?php echo $bai_viet['id']; ?>">
                                        <button type="submit" class="btn btn-info btn-sm" title="Hiện lại">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <form method="post" class="d-inline"
                                    onsubmit="return confirm('Xóa vĩnh viễn bài viết này?\nHành động không thể hoàn tác!')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $bai_viet['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Xóa vĩnh viễn">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($danh_sach_bai_viet)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Không có bài viết nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal chi tiết bài viết -->
    <div class="modal fade" id="postModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chi tiết bài viết</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="postContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include("../partials/footer.php"); ?>

    <?php mysqli_close($conn); ?>

    <!-- Load nội dung -->
    <script>
        function loadPost(bai_viet_id) {
            fetch(`post_detail.php?id=${bai_viet_id}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('postContent').innerHTML = html;
                })
                .catch(() => {
                    document.getElementById('postContent').innerHTML =
                        '<div class="alert alert-danger">Lỗi tải nội dung.</div>';
                });
        }
    </script>
</body>

</html>