<?php
require_once('../config.php');
require_permission([ROLE_ADMIN], '../login.php');

$bai_viet_id = (int)($_GET['id'] ?? 0);

if ($bai_viet_id <= 0) {
    echo '<div class="alert alert-danger">ID bài viết không hợp lệ!</div>';
    exit();
}

// ===== LẤY THÔNG TIN BÀI VIẾT =====
$sql = "SELECT bv.*, nd.ten_dang_nhap AS tac_gia, nd.email AS email_tac_gia, nd.anh_dai_dien
        FROM bai_viet_dien_dan bv
        LEFT JOIN nguoi_dung nd ON bv.tac_gia_id = nd.id
        WHERE bv.id = ?";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $bai_viet_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $bai_viet = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    echo '<div class="alert alert-danger">Lỗi truy vấn cơ sở dữ liệu!</div>';
    exit();
}

if (!$bai_viet) {
    echo '<div class="alert alert-danger">Bài viết không tồn tại!</div>';
    exit();
}

// ===== LẤY SỐ LƯỢNG BÌNH LUẬN =====
$sql_binh_luan_count = "SELECT COUNT(*) AS so_luong FROM binh_luan_dien_dan WHERE bai_viet_id = ?";
$stmt_bl_count = mysqli_prepare($conn, $sql_binh_luan_count);
$so_binh_luan = 0;
if ($stmt_bl_count) {
    mysqli_stmt_bind_param($stmt_bl_count, "i", $bai_viet_id);
    mysqli_stmt_execute($stmt_bl_count);
    $result_bl_count = mysqli_stmt_get_result($stmt_bl_count);
    $row_bl_count = mysqli_fetch_assoc($result_bl_count);
    $so_binh_luan = $row_bl_count['so_luong'] ?? 0;
    mysqli_stmt_close($stmt_bl_count);
}

// ===== LẤY SỐ LƯỢNG LIKE =====
$sql_like = "SELECT COUNT(*) AS so_like FROM luot_thich_bai_viet WHERE bai_viet_id = ?";
$stmt_like = mysqli_prepare($conn, $sql_like);
$so_like = 0;
if ($stmt_like) {
    mysqli_stmt_bind_param($stmt_like, "i", $bai_viet_id);
    mysqli_stmt_execute($stmt_like);
    $result_like = mysqli_stmt_get_result($stmt_like);
    $row_like = mysqli_fetch_assoc($result_like);
    $so_like = $row_like['so_like'] ?? 0;
    mysqli_stmt_close($stmt_like);
}

// ===== LẤY DANH SÁCH BÌNH LUẬN =====
$sql_comments = "SELECT bl.*, nd.ten_dang_nhap AS ten_nguoi_bl, nd.anh_dai_dien
                 FROM binh_luan_dien_dan bl
                 LEFT JOIN nguoi_dung nd ON bl.tac_gia_id = nd.id
                 WHERE bl.bai_viet_id = ?
                 ORDER BY bl.ngay_tao ASC";

$stmt_comments = mysqli_prepare($conn, $sql_comments);
$danh_sach_binh_luan = [];
if ($stmt_comments) {
    mysqli_stmt_bind_param($stmt_comments, "i", $bai_viet_id);
    mysqli_stmt_execute($stmt_comments);
    $result_comments = mysqli_stmt_get_result($stmt_comments);
    while ($row = mysqli_fetch_assoc($result_comments)) {
        $danh_sach_binh_luan[] = $row;
    }
    mysqli_stmt_close($stmt_comments);
}

// ===== MAPPING DỮ LIỆU =====
$ten_danh_muc = array(
    'forum' => 'Diễn đàn SV',
    'news' => 'Bản tin',
    'market' => 'Chợ SV',
    'job' => 'Tuyển dụng',
    'lost' => 'Thất lạc',
    'docs' => 'Tài liệu'
);

$ten_trang_thai = array(
    'cho_duyet' => 'Chờ duyệt',
    'da_duyet' => 'Đã duyệt',
    'bi_an' => 'Bị ẩn'
);

$mau_trang_thai = array(
    'cho_duyet' => 'warning',
    'da_duyet' => 'success',
    'bi_an' => 'secondary'
);

$avatar_url = $bai_viet['anh_dai_dien'] ? '../../' . htmlspecialchars($bai_viet['anh_dai_dien']) : '../../images/default-avatar.png';
?>

<div class="row">
    <!-- Cột trái: Nội dung bài viết + Bình luận -->
    <div class="col-md-8">
        <div class="mb-3">
            <span class="badge bg-primary me-2"><?php echo $ten_danh_muc[$bai_viet['category']] ?? $bai_viet['category']; ?></span>
            <span class="badge bg-<?php echo $mau_trang_thai[$bai_viet['trang_thai']]; ?>">
                <?php echo $ten_trang_thai[$bai_viet['trang_thai']]; ?>
            </span>
        </div>

        <h3 class="mb-3"><?php echo htmlspecialchars($bai_viet['tieu_de']); ?></h3>

        <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
            <img src="<?php echo $avatar_url; ?>" alt="Avatar"
                class="rounded-circle me-3" width="50" height="50" style="object-fit: cover;">
            <div>
                <div class="fw-semibold"><?php echo htmlspecialchars($bai_viet['tac_gia'] ?? 'Ẩn danh'); ?></div>
                <small class="text-muted">
                    <?php echo htmlspecialchars($bai_viet['email_tac_gia'] ?? ''); ?>
                </small>
            </div>
        </div>

        <!-- Nội dung bài viết -->
        <div class="post-content mb-4" style="line-height: 1.8;">
            <?php echo nl2br(htmlspecialchars($bai_viet['noi_dung'])); ?>
        </div>

        <!-- File đính kèm -->
        <?php if (!empty($bai_viet['file_dinh_kem'])): ?>
            <?php
            $file_name = $bai_viet['file_dinh_kem'];
            $file_path = "../" . $file_name;
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $image_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            ?>

            <?php if (in_array($ext, $image_exts)): ?>
                <div class="mb-4">
                    <img src="<?php echo htmlspecialchars($file_path); ?>"
                        alt="Hình ảnh bài viết"
                        class="img-fluid rounded"
                        style="max-height: 500px; object-fit: contain; border: 1px solid #ddd;"
                        onerror="this.style.display='none'">
                </div>
            <?php else: ?>
                <div class="mb-4">
                    <div class="alert alert-info">
                        <i class="bi bi-file-earmark me-2"></i>
                        <a href="<?php echo htmlspecialchars($file_path); ?>" target="_blank" class="text-decoration-none">
                            Xem file đính kèm
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Danh sách bình luận -->
        <div class="mt-5">
            <h5 class="mb-4">
                <i class="bi bi-chat-dots me-2"></i>Bình luận
                <span class="badge bg-secondary"><?php echo number_format($so_binh_luan); ?></span>
            </h5>

            <?php if (empty($danh_sach_binh_luan)): ?>
                <div class="alert alert-light text-center py-4">
                    <i class="bi bi-chat-text fs-1 text-muted"></i>
                    <p class="mt-3 mb-0 text-muted">Chưa có bình luận nào.</p>
                </div>
            <?php else: ?>
                <div class="comments-list">
                    <?php foreach ($danh_sach_binh_luan as $bl): ?>
                        <?php
                        $avatar_bl = $bl['anh_dai_dien'] ? '../../' . htmlspecialchars($bl['anh_dai_dien']) : '../../images/default-avatar.png';
                        ?>
                        <div class="d-flex mb-4 pb-4 border-bottom">
                            <img src="<?php echo $avatar_bl; ?>" alt="Avatar"
                                class="rounded-circle flex-shrink-0 me-3" width="40" height="40" style="object-fit: cover;">
                            <div class="flex-grow-1">
                                <div class="fw-semibold"><?php echo htmlspecialchars($bl['ten_nguoi_bl'] ?? 'Ẩn danh'); ?></div>
                                <small class="text-muted">
                                    <?php echo date('d/m/Y H:i', strtotime($bl['ngay_tao'])); ?>
                                </small>
                                <div class="mt-2" style="line-height: 1.6;">
                                    <?php echo nl2br(htmlspecialchars($bl['noi_dung'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cột phải: Thông tin & Hành động -->
    <div class="col-md-4">
        <!-- Card thông tin -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Thông tin bài viết</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">ID bài viết</small>
                    <strong>#<?php echo $bai_viet['id']; ?></strong>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Ngày đăng</small>
                    <strong><?php echo date('d/m/Y H:i', strtotime($bai_viet['ngay_dang'])); ?></strong>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Lượt thích</small>
                    <strong><i class="bi bi-heart-fill text-danger me-1"></i><?php echo number_format($so_like); ?></strong>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Bình luận</small>
                    <strong><i class="bi bi-chat-dots me-1"></i><?php echo number_format($so_binh_luan); ?></strong>
                </div>

                <?php if (!empty($bai_viet['file_dinh_kem'])): ?>
                    <div class="mb-3">
                        <small class="text-muted d-block">File đính kèm</small>
                        <strong><i class="bi bi-paperclip me-1"></i>Có</strong>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card hành động -->
        <div class="card mt-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Hành động</h6>
            </div>
            <div class="card-body">
                <?php if ($bai_viet['trang_thai'] === 'cho_duyet'): ?>
                    <form method="post" action="manage_blog.php" class="mb-2">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="id" value="<?php echo $bai_viet['id']; ?>">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-lg me-2"></i>Duyệt bài viết
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($bai_viet['trang_thai'] === 'da_duyet'): ?>
                    <form method="post" action="manage_blog.php" class="mb-2">
                        <input type="hidden" name="action" value="hide">
                        <input type="hidden" name="id" value="<?php echo $bai_viet['id']; ?>">
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-eye-slash me-2"></i>Ẩn bài viết
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($bai_viet['trang_thai'] === 'bi_an'): ?>
                    <form method="post" action="manage_blog.php" class="mb-2">
                        <input type="hidden" name="action" value="unhide">
                        <input type="hidden" name="id" value="<?php echo $bai_viet['id']; ?>">
                        <button type="submit" class="btn btn-info w-100">
                            <i class="bi bi-eye me-2"></i>Hiện lại bài viết
                        </button>
                    </form>
                <?php endif; ?>

                <form method="post" action="manage_blog.php"
                    onsubmit="return confirm('Xóa vĩnh viễn bài viết này?\nHành động không thể hoàn tác!')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $bai_viet['id']; ?>">
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-trash3 me-2"></i>Xóa vĩnh viễn
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>