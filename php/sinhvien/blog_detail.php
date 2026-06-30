<?php
require_once('../config.php');
require_permission([ROLE_STUDENT], '../login.php');

$user_id = $_SESSION['user_id'];
$post_id = (int)($_GET['id'] ?? 0);

if ($post_id === 0) {
    header("Location: blog.php");
    exit();
}

$url_hien_tai = "blog_detail.php?id=$post_id";

// Hàm hiển thị thời gian tương đối (vừa xong, 5 phút trước, ...)
function hien_thi_thoi_gian_tuong_doi($datetime)
{
    $thoi_gian_hien_tai = time();
    $thoi_gian_binh_luan = strtotime($datetime);
    $chenh_lech = $thoi_gian_hien_tai - $thoi_gian_binh_luan;

    if ($chenh_lech < 60) return "Vừa xong";
    if ($chenh_lech < 3600) return floor($chenh_lech / 60) . " phút trước";
    if ($chenh_lech < 86400) return floor($chenh_lech / 3600) . " giờ trước";
    if ($chenh_lech < 172800) return "Hôm qua";
    if ($chenh_lech < 604800) return floor($chenh_lech / 86400) . " ngày trước";

    return date("d/m/Y H:i", $thoi_gian_binh_luan);
}

// ===== XỬ LÝ THÍCH BÀI VIẾT =====
if (isset($_POST['like_post'])) {
    // Kiểm tra đã thích chưa
    $sql_kiem_tra_thich = "SELECT * FROM luot_thich_bai_viet WHERE bai_viet_id = $post_id AND sinh_vien_id = $user_id";
    $kiem_tra = mysqli_query($conn, $sql_kiem_tra_thich);

    // Lấy ID tác giả bài viết
    $sql_lay_tac_gia = "SELECT tac_gia_id FROM bai_viet_dien_dan WHERE id = $post_id";
    $ket_qua_tac_gia = mysqli_query($conn, $sql_lay_tac_gia);
    $tac_gia_id = mysqli_fetch_assoc($ket_qua_tac_gia)['tac_gia_id'];

    // Nếu đã thích thì bỏ thích, chưa thích thì thêm
    if (mysqli_num_rows($kiem_tra) > 0) {
        mysqli_query($conn, "DELETE FROM luot_thich_bai_viet WHERE bai_viet_id = $post_id AND sinh_vien_id = $user_id");
    } else {
        mysqli_query($conn, "INSERT INTO luot_thich_bai_viet (bai_viet_id, sinh_vien_id) VALUES ($post_id, $user_id)");

        // Gửi thông báo cho tác giả (nếu không phải tự thích bài của mình)
        if ($user_id != $tac_gia_id) {
            $sql_lay_ten = "SELECT ten_dang_nhap FROM nguoi_dung WHERE id = $user_id";
            $username = mysqli_fetch_assoc(mysqli_query($conn, $sql_lay_ten))['ten_dang_nhap'];

            $tieu_de_thong_bao = "Bài viết của bạn được thích!";
            $noi_dung_thong_bao = "$username đã thích bài viết của bạn.";

            $sql_them_thong_bao = "INSERT INTO thong_bao (sinh_vien_id, tieu_de, noi_dung, loai, id_lien_quan, loai_lien_quan, ngay_tao)
                                   VALUES ($tac_gia_id, '$tieu_de_thong_bao', '$noi_dung_thong_bao', 'dien_dan', $post_id, 'bai_viet', NOW())";
            mysqli_query($conn, $sql_them_thong_bao);
        }
    }

    header("Location: " . $url_hien_tai);
    exit();
}

// ===== XỬ LÝ THÊM BÌNH LUẬN =====
if (isset($_POST['add_comment'])) {
    $noi_dung_binh_luan = mysqli_real_escape_string($conn, trim($_POST['text'] ?? ''));

    // Thêm bình luận vào database
    if ($noi_dung_binh_luan !== '') {
        $sql_them_binh_luan = "INSERT INTO binh_luan_dien_dan (bai_viet_id, tac_gia_id, noi_dung, ngay_tao) 
                               VALUES ($post_id, $user_id, '$noi_dung_binh_luan', NOW())";
        mysqli_query($conn, $sql_them_binh_luan);

        // Lấy ID tác giả bài viết
        $sql_lay_tac_gia = "SELECT tac_gia_id FROM bai_viet_dien_dan WHERE id = $post_id";
        $ket_qua_tac_gia = mysqli_query($conn, $sql_lay_tac_gia);
        $tac_gia_id = mysqli_fetch_assoc($ket_qua_tac_gia)['tac_gia_id'];

        // Gửi thông báo cho tác giả (nếu không phải tự bình luận bài của mình)
        if ($user_id != $tac_gia_id) {
            $sql_lay_ten = "SELECT ten_dang_nhap FROM nguoi_dung WHERE id = $user_id";
            $username = mysqli_fetch_assoc(mysqli_query($conn, $sql_lay_ten))['ten_dang_nhap'];

            $tieu_de_thong_bao = "Bài viết của bạn có bình luận mới!";
            $noi_dung_thong_bao = "$username: $noi_dung_binh_luan";

            $sql_them_thong_bao = "INSERT INTO thong_bao (sinh_vien_id, tieu_de, noi_dung, loai, id_lien_quan, loai_lien_quan, ngay_tao)
                                   VALUES ($tac_gia_id, '$tieu_de_thong_bao', '$noi_dung_thong_bao', 'dien_dan', $post_id, 'bai_viet', NOW())";
            mysqli_query($conn, $sql_them_thong_bao);
        }
    }

    header("Location: " . $url_hien_tai);
    exit();
}

// ===== LẤY DỮ LIỆU BÀI VIẾT =====
$sql_lay_bai_viet = "SELECT bv.*, nd.ten_dang_nhap, nd.anh_dai_dien,
                     (SELECT COUNT(*) FROM luot_thich_bai_viet lt WHERE lt.bai_viet_id = bv.id) AS likes,
                     (SELECT COUNT(*) FROM luot_thich_bai_viet lt WHERE lt.bai_viet_id = bv.id AND lt.sinh_vien_id = $user_id) AS liked
                     FROM bai_viet_dien_dan bv
                     JOIN nguoi_dung nd ON bv.tac_gia_id = nd.id
                     WHERE bv.id = $post_id AND bv.trang_thai = 'da_duyet'";

$ket_qua_bai_viet = mysqli_query($conn, $sql_lay_bai_viet);
$bai_viet = mysqli_fetch_assoc($ket_qua_bai_viet);

if (!$bai_viet) {
    header("Location: blog.php");
    exit();
}

// ===== LẤY DANH SÁCH BÌNH LUẬN =====
$sql_lay_binh_luan = "SELECT bl.*, nd.ten_dang_nhap, nd.anh_dai_dien
                      FROM binh_luan_dien_dan bl
                      JOIN nguoi_dung nd ON bl.tac_gia_id = nd.id
                      WHERE bl.bai_viet_id = $post_id
                      ORDER BY bl.ngay_tao ASC";
$ket_qua_binh_luan = mysqli_query($conn, $sql_lay_binh_luan);

// ===== XỬ LÝ ĐƯỜNG DẪN AVATAR =====
$sql_lay_avatar_user = "SELECT anh_dai_dien FROM nguoi_dung WHERE id = $user_id";
$avatar_user = mysqli_fetch_assoc(mysqli_query($conn, $sql_lay_avatar_user))['anh_dai_dien'];

$avatar = ($avatar_user && !str_contains($avatar_user, 'http')) ? "../../" . $avatar_user : ($avatar_user ?: "../images/default-avatar.png");

$duong_dan_avatar_tac_gia = $bai_viet['anh_dai_dien'];
$avatar_tac_gia = ($duong_dan_avatar_tac_gia && !str_contains($duong_dan_avatar_tac_gia, 'http'))
    ? "../../" . $duong_dan_avatar_tac_gia
    : ($duong_dan_avatar_tac_gia ?: "../images/default-avatar.png");

$da_thich = $bai_viet['liked'] > 0;
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($bai_viet['tieu_de']); ?> - Diễn đàn</title>
    <link rel="icon" type="image/png" href="../../images/logo.png">
    <link rel="stylesheet" href="../../css/header-footer.css">
    <link rel="stylesheet" href="../../css/blog.css">
    <link rel="stylesheet" href="../../css/style.css">
</head>

<body>
    <!-- ===== HEADER ===== -->
    <?php include("../partials/header.php"); ?>

    <!-- ===== NỘI DUNG CHÍNH ===== -->
    <div class="container mt-4">
        <a href="blog.php" class="btn btn-outline-secondary btn-sm mb-3">Quay lại Diễn đàn</a>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="post-author-info d-flex gap-3 align-items-center mb-4">
                    <img src="<?php echo htmlspecialchars($avatar_tac_gia); ?>" class="comment-avatar" width="50" height="50" alt="Avatar">
                    <div>
                        <h5 class="mb-0 fw-bold"><?php echo htmlspecialchars($bai_viet['ten_dang_nhap']); ?></h5>
                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($bai_viet['ngay_dang'])); ?></small>
                    </div>
                </div>

                <h3 class="card-title mb-3"><?php echo htmlspecialchars($bai_viet['tieu_de']); ?></h3>

                <div class="post-content mb-4">
                    <?php echo nl2br(htmlspecialchars($bai_viet['noi_dung'])); ?>
                </div>

                <?php if ($bai_viet['file_dinh_kem']): ?>
                    <div class="mb-4 p-3 bg-light rounded border">
                        File đính kèm:
                        <a href="../<?php echo htmlspecialchars($bai_viet['file_dinh_kem']); ?>" target="_blank">
                            <?php echo htmlspecialchars(basename($bai_viet['file_dinh_kem'])); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="d-flex gap-3 border-top pt-3 align-items-center">
                    <form method="POST" class="d-inline">
                        <button type="submit" name="like_post"
                            class="btn btn-sm <?php echo $da_thich ? 'btn-danger' : 'btn-outline-danger'; ?> d-flex align-items-center gap-2">
                            <i class="bi <?php echo $da_thich ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                            <span><?php echo $bai_viet['likes']; ?> Thích</span>
                        </button>
                    </form>

                    <div class="d-flex align-items-center gap-2 text-muted">
                        <i class="bi bi-chat-dots"></i>
                        <span><?php echo mysqli_num_rows($ket_qua_binh_luan); ?> Bình luận</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== PHẦN BÌNH LUẬN ===== -->
        <h4 class="mt-5 mb-3">Bình luận</h4>

        <div class="card card-body mb-4">
            <form method="POST" class="d-flex gap-3">
                <img src="<?php echo htmlspecialchars($avatar); ?>" class="comment-avatar" alt="Avatar">
                <input type="text" name="text" class="form-control" placeholder="Viết bình luận..." required>
                <button type="submit" name="add_comment" class="btn btn-primary">Gửi</button>
            </form>
        </div>

        <div class="comment-list">
            <?php if (mysqli_num_rows($ket_qua_binh_luan) == 0): ?>
                <p class="text-center text-muted py-3">Chưa có bình luận nào.</p>
            <?php else: ?>
                <?php while ($binh_luan = mysqli_fetch_assoc($ket_qua_binh_luan)):
                    $duong_dan_avatar_bl = $binh_luan['anh_dai_dien'];
                    $avatar_binh_luan = ($duong_dan_avatar_bl && !str_contains($duong_dan_avatar_bl, 'http'))
                        ? "../" . $duong_dan_avatar_bl
                        : ($duong_dan_avatar_bl ?: "../images/default-avatar.png");
                ?>
                    <div class="d-flex gap-3 mb-3 comment-item">
                        <img src="<?php echo htmlspecialchars($avatar_binh_luan); ?>" class="comment-avatar" alt="Avatar">
                        <div class="bg-light p-3 rounded flex-grow-1">
                            <div class="fw-bold mb-1"><?php echo htmlspecialchars($binh_luan['ten_dang_nhap']); ?></div>
                            <div><?php echo nl2br(htmlspecialchars($binh_luan['noi_dung'])); ?></div>
                            <small class="text-muted mt-1 d-block">
                                <?php echo hien_thi_thoi_gian_tuong_doi($binh_luan['ngay_tao']); ?>
                            </small>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include("../partials/footer.php"); ?>
</body>

</html>