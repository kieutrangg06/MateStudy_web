<?php
require_once('../config.php');
require_permission([ROLE_STUDENT], '../login.php');

$id_danh_gia = (int)($_GET['id'] ?? 0);

if ($id_danh_gia === 0) {
    header("Location: review.php");
    exit();
}

// ===== HELPER FUNCTIONS =====
function hien_thi_sao($diem_sao)
{
    $diem_sao = (int)$diem_sao;
    $sao_day = str_repeat('★', $diem_sao);
    $sao_rong = str_repeat('☆', 5 - $diem_sao);
    return $sao_day . $sao_rong;
}

// ===== LẤY DỮ LIỆU ĐÁNH GIÁ =====
$sql_lay_danh_gia = "SELECT dg.*, 
                             m.ten_mon, 
                             m.ten_gv, 
                             sv.ten_dang_nhap AS ten_sinh_vien
                      FROM danh_gia_mon_giang_vien dg
                      LEFT JOIN mon_hoc m ON dg.mon_hoc_id = m.id
                      LEFT JOIN nguoi_dung sv ON dg.sinh_vien_id = sv.id
                      WHERE dg.id = $id_danh_gia 
                      AND dg.trang_thai = 'da_duyet'";

$ket_qua = mysqli_query($conn, $sql_lay_danh_gia);
$danh_gia = mysqli_fetch_assoc($ket_qua);

if (!$danh_gia) {
    header("Location: review.php");
    exit();
}

$diem_sao = (int)$danh_gia['diem_sao'];
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đánh giá - <?php echo htmlspecialchars($danh_gia['ten_mon']); ?></title>
    <link rel="icon" type="image/png" href="../../images/logo.png">
    <link rel="stylesheet" href="../../css/header-footer.css">
    <link rel="stylesheet" href="../../css/review.css">
    <link rel="stylesheet" href="../../css/style.css">
</head>

<body>
    <!-- ===== HEADER ===== -->
    <?php include("../partials/header.php"); ?>

    <!-- ===== NỘI DUNG CHÍNH ===== -->
    <div class="container mt-4">

        <a href="review.php" class="btn btn-outline-secondary btn-sm mb-4">Quay lại Đánh giá</a>

        <!-- Chi tiết đánh giá -->
        <div class="card shadow-sm">

            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    Chi tiết đánh giá môn: <?php echo htmlspecialchars($danh_gia['ten_mon'] ?? 'N/A'); ?>
                </h4>
            </div>

            <div class="card-body">

                <div class="row mb-4 border-bottom pb-3">
                    <div class="col-md-6">
                        <p class="mb-1">
                            <strong>Giảng viên:</strong>
                            <?php echo htmlspecialchars($danh_gia['ten_gv'] ?? 'N/A'); ?>
                        </p>
                        <p class="mb-1">
                            <strong>Người đánh giá:</strong>
                            <?php echo htmlspecialchars($danh_gia['ten_sinh_vien'] ?? 'Ẩn danh'); ?>
                        </p>
                        <p class="mb-0">
                            <strong>Ngày:</strong>
                            <?php echo date('d/m/Y H:i', strtotime($danh_gia['ngay_dang'])); ?>
                        </p>
                    </div>

                    <div class="col-md-6 text-md-end">
                        <div class="d-inline-block p-2 rounded bg-light">
                            <h5 class="mb-1">ĐIỂM ĐÁNH GIÁ</h5>
                            <div class="rating-stars">
                                <?php echo hien_thi_sao($diem_sao); ?>
                                <span class="ms-2 fw-bold" style="font-size: 1.5rem;">
                                    (<?php echo $diem_sao; ?>/5)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <h5>Nội dung chi tiết</h5>
                <div class="p-3 border rounded bg-light">
                    <?php echo nl2br(htmlspecialchars($danh_gia['noi_dung'])); ?>
                </div>

                <div class="mt-4">
                    <a href="review.php" class="btn btn-secondary">Quay về danh sách</a>
                </div>

            </div>
        </div>
    </div>

    <?php include("../partials/footer.php"); ?>

</body>

</html>