<?php
require_once('../config.php');
require_permission([ROLE_STUDENT], '../login.php');
$user_id = $_SESSION['user_id'];

// Lấy thông tin user hiện tại
$nguoi_dung_hien_tai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM nguoi_dung WHERE id = '$user_id'"));

// ===== Tìm kiếm sinh viên theo email =====
if (isset($_GET['search_email'])) {
    $tu_khoa = mysqli_real_escape_string($conn, $_GET['search_email']);
    $nhom_id = (int)($_GET['group'] ?? 0);
    $sql = "SELECT id, ten_dang_nhap, email FROM nguoi_dung 
            WHERE email LIKE '%$tu_khoa%' AND vai_tro = 'sinh_vien' LIMIT 10";
    $res = mysqli_query($conn, $sql);
    $found = false;
    while ($sv = mysqli_fetch_assoc($res)) {
        $in_group = mysqli_num_rows(mysqli_query($conn, "SELECT 1 FROM thanh_vien_nhom WHERE nhom_id = $nhom_id AND sinh_vien_id = {$sv['id']}"));
        if (!$in_group) {
            $found = true;
            echo "<div class='p-3 border-bottom hover-bg-light cursor-pointer' 
                      onclick=\"addMember({$sv['id']}, '" . addslashes($sv['ten_dang_nhap']) . "', '" . addslashes($sv['email']) . "')\">";
            echo "<strong>" . htmlspecialchars($sv['ten_dang_nhap']) . "</strong><br>";
            echo "<small class='text-muted'>" . htmlspecialchars($sv['email']) . "</small></div>";
        }
    }
    if (!$found) echo "<div class='p-3 text-muted fst-italic'>Không tìm thấy sinh viên nào</div>";
    die();
}

// Helper: Gửi thông báo
function guiThongBao($conn, $sv_id, $tieu_de, $noi_dung, $loai = 'hoc_tap', $id_lien_quan = null, $loai_lien_quan = null)
{
    $sv_id = (int)$sv_id;
    $tieu_de = mysqli_real_escape_string($conn, $tieu_de);
    $noi_dung = mysqli_real_escape_string($conn, $noi_dung);
    $loai = mysqli_real_escape_string($conn, $loai);
    $id_lien_quan = $id_lien_quan ? (int)$id_lien_quan : 'NULL';
    $loai_lien_quan = $loai_lien_quan ? "'" . mysqli_real_escape_string($conn, $loai_lien_quan) . "'" : 'NULL';
    mysqli_query($conn, "INSERT INTO thong_bao (sinh_vien_id, tieu_de, noi_dung, loai, id_lien_quan, loai_lien_quan)
                         VALUES ($sv_id, '$tieu_de', '$noi_dung', '$loai', $id_lien_quan, $loai_lien_quan)");
}

// ===== XỬ LÝ POST =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_group') {
        $ten = mysqli_real_escape_string($conn, $_POST['ten_nhom']);
        $mon_id = !empty($_POST['mon_hoc_id']) ? (int)$_POST['mon_hoc_id'] : 'NULL';
        $mo_ta = mysqli_real_escape_string($conn, $_POST['mo_ta']);
        mysqli_query($conn, "INSERT INTO nhom_hoc_tap (ten_nhom, mo_ta, mon_hoc_id) VALUES ('$ten', '$mo_ta', $mon_id)");
        $nhom_id = mysqli_insert_id($conn);
        mysqli_query($conn, "INSERT INTO thanh_vien_nhom (nhom_id, sinh_vien_id, vai_tro) VALUES ($nhom_id, $user_id, 'truong_nhom')");
    } elseif ($action === 'edit_group') {
        $nhom_id = (int)$_POST['nhom_id'];
        $ten = mysqli_real_escape_string($conn, $_POST['ten_nhom']);
        $mo_ta = mysqli_real_escape_string($conn, $_POST['mo_ta']);
        $mon_id = !empty($_POST['mon_hoc_id']) ? (int)$_POST['mon_hoc_id'] : 'NULL';
        mysqli_query($conn, "UPDATE nhom_hoc_tap SET ten_nhom='$ten', mo_ta='$mo_ta', mon_hoc_id=$mon_id WHERE id=$nhom_id");
    } elseif ($action === 'leave_group') {
        $nhom_id = (int)$_POST['nhom_id'];
        mysqli_query($conn, "DELETE FROM thanh_vien_nhom WHERE nhom_id=$nhom_id AND sinh_vien_id=$user_id");
    } elseif ($action === 'add_member') {
        $nhom_id = (int)$_POST['nhom_id'];
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $sv = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id, ten_dang_nhap FROM nguoi_dung WHERE email='$email' AND vai_tro='sinh_vien'"));
        if ($sv && !mysqli_num_rows(mysqli_query($conn, "SELECT 1 FROM thanh_vien_nhom WHERE nhom_id=$nhom_id AND sinh_vien_id={$sv['id']}"))) {
            mysqli_query($conn, "INSERT INTO thanh_vien_nhom (nhom_id, sinh_vien_id) VALUES ($nhom_id, {$sv['id']})");
            $ten_nhom = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ten_nhom FROM nhom_hoc_tap WHERE id=$nhom_id"))['ten_nhom'];
            guiThongBao(
                $conn,
                $sv['id'],
                "Bạn đã được thêm vào nhóm học tập",
                "Bạn đã được thêm vào nhóm \"$ten_nhom\" bởi {$nguoi_dung_hien_tai['ten_dang_nhap']}",
                'hoc_tap',
                $nhom_id,
                'nhom_hoc_tap'
            );
        }
    } elseif ($action === 'add_task') {
        $nhom_id = (int)$_POST['nhom_id'];
        $tieu_de = mysqli_real_escape_string($conn, $_POST['tieu_de']);
        $nguoi_giao = (int)$_POST['nguoi_giao'];
        $han = $_POST['han'] . ' 23:59:59';
        mysqli_query($conn, "INSERT INTO cong_viec_nhom (nhom_id, tieu_de, nguoi_duoc_giao, han_hoan_thanh, trang_thai)
                             VALUES ($nhom_id, '$tieu_de', $nguoi_giao, '$han', 'chua_nop')");
        $cv_id = mysqli_insert_id($conn);
        $ten_nhom = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ten_nhom FROM nhom_hoc_tap WHERE id=$nhom_id"))['ten_nhom'] ?? 'Nhóm';
        guiThongBao($conn, $nguoi_giao, "Công việc mới: $tieu_de", "Nhóm \"$ten_nhom\"\nHạn nộp: " . date('d/m/Y', strtotime($_POST['han'])), 'hoc_tap', $cv_id, 'cong_viec_nhom');
    } elseif ($action === 'submit_task') {
        $cv_id = (int)$_POST['task_id'];
        $noi_dung = mysqli_real_escape_string($conn, $_POST['noi_dung_nop']);
        $file_path = null;
        if (isset($_FILES['file_nop']) && $_FILES['file_nop']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['file_nop']['name'], PATHINFO_EXTENSION));
            $allowed = ['pdf', 'doc', 'docx', 'zip', 'rar', 'jpg', 'jpeg', 'png', 'txt', 'pptx'];
            if (in_array($ext, $allowed) && $_FILES['file_nop']['size'] <= 10 * 1024 * 1024) {
                $new_name = "task_{$cv_id}_" . time() . ".$ext";
                $dir = "../uploads/tasks/";
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                move_uploaded_file($_FILES['file_nop']['tmp_name'], $dir . $new_name);
                $file_path = "/uploads/tasks/$new_name";
            }
        }
        $file_sql = $file_path ? "'$file_path'" : 'NULL';
        mysqli_query($conn, "UPDATE cong_viec_nhom SET noi_dung_nop='$noi_dung', file_nop=$file_sql, ngay_nop=NOW(), trang_thai='da_nop'
                             WHERE id=$cv_id AND nguoi_duoc_giao=$user_id");
    } elseif ($action === 'cancel_submit') {
        $cv_id = (int)$_POST['task_id'];
        $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT file_nop FROM cong_viec_nhom WHERE id=$cv_id AND nguoi_duoc_giao=$user_id"));
        if ($row['file_nop'] && file_exists("../" . $row['file_nop'])) unlink("../" . $row['file_nop']);
        mysqli_query($conn, "UPDATE cong_viec_nhom SET noi_dung_nop=NULL, file_nop=NULL, ngay_nop=NULL, trang_thai='chua_nop'
                             WHERE id=$cv_id AND nguoi_duoc_giao=$user_id");
    } elseif ($action === 'delete_task' || $action === 'edit_task') {
        $cv_id = (int)$_POST['task_id'];
        $nhom_id = (int)$_POST['nhom_id'];
        if (mysqli_fetch_assoc(mysqli_query($conn, "SELECT 1 FROM thanh_vien_nhom tv JOIN cong_viec_nhom cv ON tv.nhom_id=cv.nhom_id WHERE tv.sinh_vien_id=$user_id AND cv.id=$cv_id"))) {
            if ($action === 'delete_task') {
                $file = mysqli_fetch_assoc(mysqli_query($conn, "SELECT file_nop FROM cong_viec_nhom WHERE id=$cv_id"))['file_nop'];
                if ($file && file_exists("../$file")) unlink("../$file");
                mysqli_query($conn, "DELETE FROM cong_viec_nhom WHERE id=$cv_id");
            } else {
                $tieu_de = mysqli_real_escape_string($conn, $_POST['tieu_de']);
                $mo_ta = mysqli_real_escape_string($conn, $_POST['mo_ta'] ?? '');
                $nguoi = (int)$_POST['nguoi_giao'];
                $han = $_POST['han'] . ' 23:59:59';
                mysqli_query($conn, "UPDATE cong_viec_nhom SET tieu_de='$tieu_de', mo_ta='$mo_ta', nguoi_duoc_giao=$nguoi, han_hoan_thanh='$han' WHERE id=$cv_id");
            }
        }
    }

    $redirect = $_SERVER['PHP_SELF'];
    if (!empty($_POST['current_group'])) $redirect .= "?group=" . (int)$_POST['current_group'];
    header("Location: $redirect");
    exit();
}

// Lấy danh sách nhóm của user
$nhom_res = mysqli_query($conn, "
    SELECT n.*, m.ten_mon, COUNT(tv.sinh_vien_id) as so_tv
    FROM nhom_hoc_tap n
    LEFT JOIN mon_hoc m ON n.mon_hoc_id = m.id
    JOIN thanh_vien_nhom tv ON tv.nhom_id = n.id
    WHERE tv.sinh_vien_id = $user_id
    GROUP BY n.id
");

// Chi tiết nhóm hiện tại
$nhom_hien_tai = null;
$thanh_vien_res = $cong_viec_res = null;
if (isset($_GET['group'])) {
    $nhom_id = (int)$_GET['group'];
    $nhom_hien_tai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT n.*, m.ten_mon FROM nhom_hoc_tap n LEFT JOIN mon_hoc m ON n.mon_hoc_id=m.id WHERE n.id=$nhom_id"));
    if ($nhom_hien_tai) {
        $thanh_vien_res = mysqli_query($conn, "SELECT nd.id, nd.ten_dang_nhap, nd.email, nd.anh_dai_dien FROM nguoi_dung nd JOIN thanh_vien_nhom tv ON tv.sinh_vien_id=nd.id WHERE tv.nhom_id=$nhom_id");
        $cong_viec_res = mysqli_query($conn, "SELECT cv.*, nd.ten_dang_nhap as assignee_name FROM cong_viec_nhom cv LEFT JOIN nguoi_dung nd ON cv.nguoi_duoc_giao=nd.id WHERE cv.nhom_id=$nhom_id ORDER BY cv.han_hoan_thanh");
    }
}

// Danh sách môn học
$mon_hoc_res = mysqli_query($conn, "SELECT id, ten_mon FROM mon_hoc ORDER BY ten_mon");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý nhóm học tập</title>
    <link rel="icon" type="image/png" href="../../images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../../css/header-footer.css" rel="stylesheet">
    <link href="../../css/team.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
</head>

<body>
    <?php include("../partials/header.php"); ?>

    <div class="container mt-4">
        <?php if (!$nhom_hien_tai): ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <input type="text" id="searchGroup" class="form-control w-50" placeholder="Tìm kiếm nhóm..." onkeyup="filterGroups()">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createGroupModal"><i class='bx bx-plus'></i> Tạo nhóm mới</button>
            </div>
            <div class="card shadow-sm">
                <div class="group-list-scroll">
                    <table class="table table-hover align-middle mb-0" id="groupTable">
                        <thead class="table-light">
                            <tr>
                                <th>Tên nhóm</th>
                                <th>Môn học</th>
                                <th>Mô tả</th>
                                <th>Thành viên</th>
                                <th class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($n = mysqli_fetch_assoc($nhom_res)): ?>
                                <tr>
                                    <td onclick="location.href='?group=<?= $n['id'] ?>'"><strong><?= htmlspecialchars($n['ten_nhom']) ?></strong></td>
                                    <td onclick="location.href='?group=<?= $n['id'] ?>'"><?= htmlspecialchars($n['ten_mon'] ?? 'Tự do') ?></td>
                                    <td onclick="location.href='?group=<?= $n['id'] ?>'" class="text-truncate-2" style="max-width:300px;">
                                        <?= $n['mo_ta'] ? htmlspecialchars($n['mo_ta']) : '<em class="text-muted">Chưa có mô tả</em>' ?>
                                    </td>
                                    <td onclick="location.href='?group=<?= $n['id'] ?>'">
                                        <div class="avatar-group d-inline-flex">
                                            <?php
                                            $avt_res = mysqli_query($conn, "SELECT anh_dai_dien FROM nguoi_dung nd JOIN thanh_vien_nhom tv ON tv.sinh_vien_id=nd.id WHERE tv.nhom_id={$n['id']} LIMIT 3");
                                            while ($a = mysqli_fetch_assoc($avt_res)): ?>
                                                <img src="../../<?= $a['anh_dai_dien'] ?: '../images/avatars/1.png' ?>" class="rounded-circle avatar-xs">
                                            <?php endwhile; ?>
                                            <?php if ($n['so_tv'] > 3): ?>
                                                <span class="avatar-xs rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center more-count">+<?= $n['so_tv'] - 3 ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn p-0" data-bs-toggle="dropdown"><i class='bx bx-dots-vertical-rounded'></i></button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="editGroup(<?= $n['id'] ?>, '<?= addslashes($n['ten_nhom']) ?>', '<?= addslashes($n['mo_ta'] ?? '') ?>', <?= $n['mon_hoc_id'] ?? 'null' ?>)">Sửa</a></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="leaveGroup(<?= $n['id'] ?>)">Rời nhóm</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold"><?= htmlspecialchars($nhom_hien_tai['ten_nhom']) ?></h5>
                <a href="?" class="btn btn-secondary btn-sm"><i class='bx bx-arrow-back'></i> Quay lại</a>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between">
                            <h6>Thành viên</h6>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal"><i class='bx bx-user-plus'></i></button>
                        </div>
                        <div class="card-body">
                            <?php while ($tv = mysqli_fetch_assoc($thanh_vien_res)): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <img src="../../<?= $tv['anh_dai_dien'] ?: '../images/avatars/1.png' ?>" class="rounded-circle me-2" width="32">
                                    <div>
                                        <div class="fw-medium"><?= htmlspecialchars($tv['ten_dang_nhap']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($tv['email']) ?></small>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between">
                            <h6>Công việc nhóm</h6>
                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#addTaskModal"><i class='bx bx-task'></i> Thêm công việc</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tên công việc</th>
                                        <th>Mô tả</th>
                                        <th>Người thực hiện</th>
                                        <th>Hạn</th>
                                        <th>Nội dung nộp</th>
                                        <th>Ngày nộp</th>
                                        <th>File</th>
                                        <th>Trạng thái</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($cv = mysqli_fetch_assoc($cong_viec_res)):
                                        $da_nop = $cv['trang_thai'] === 'da_nop';
                                        $co_nop = ($cv['nguoi_duoc_giao'] == $user_id && !$da_nop);
                                        $co_huy = ($cv['nguoi_duoc_giao'] == $user_id && $da_nop);
                                        $in_group = mysqli_fetch_assoc(mysqli_query($conn, "SELECT 1 FROM thanh_vien_nhom WHERE nhom_id={$_GET['group']} AND sinh_vien_id=$user_id"));
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($cv['tieu_de']) ?></td>
                                            <td class="text-truncate-2" style="max-width:180px;" title="<?= htmlspecialchars($cv['mo_ta'] ?? '') ?>">
                                                <?= $cv['mo_ta'] ? htmlspecialchars($cv['mo_ta']) : '<em class="text-muted">Không có</em>' ?>
                                            </td>
                                            <td><?= htmlspecialchars($cv['assignee_name'] ?? 'Chưa giao') ?></td>
                                            <td><?= date('d/m/Y', strtotime($cv['han_hoan_thanh'])) ?></td>
                                            <td class="text-truncate-2" style="max-width:200px;">
                                                <?= $cv['noi_dung_nop'] ? htmlspecialchars($cv['noi_dung_nop']) : '<em class="text-muted">Chưa nộp</em>' ?>
                                            </td>
                                            <td><?= $cv['ngay_nop'] ? date('d/m/Y H:i', strtotime($cv['ngay_nop'])) : '-' ?></td>
                                            <td><?= $cv['file_nop'] ? '<a href="../' . $cv['file_nop'] . '" target="_blank" class="btn btn-sm btn-info"><i class="bx bx-download"></i></a>' : '' ?></td>
                                            <td><span class="badge bg-<?= $da_nop ? 'success' : 'warning' ?>"><?= $da_nop ? 'Đã nộp' : 'Chưa nộp' ?></span></td>
                                            <td>
                                                <?php if ($co_nop): ?><button class="btn btn-sm btn-outline-primary mb-1" onclick="submitTask(<?= $cv['id'] ?>)">Nộp</button><?php endif; ?>
                                                <?php if ($co_huy): ?><button class="btn btn-sm btn-outline-danger mb-1" onclick="cancelSubmit(<?= $cv['id'] ?>)">Hủy nộp</button><?php endif; ?>
                                                <?php if ($in_group): ?>
                                                    <button class="btn btn-sm btn-outline-secondary mb-1" onclick="editTask(<?= $cv['id'] ?>, '<?= addslashes($cv['tieu_de']) ?>', '<?= addslashes($cv['mo_ta'] ?? '') ?>', <?= $cv['nguoi_duoc_giao'] ?>, '<?= date('Y-m-d', strtotime($cv['han_hoan_thanh'])) ?>')"><i class='bx bx-edit'></i></button>
                                                    <button class="btn btn-sm btn-outline-danger mb-1" onclick="deleteTask(<?= $cv['id'] ?>)"><i class='bx bx-trash'></i></button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Các Modal (giữ nguyên, chỉ rút gọn một chút) -->
    <!-- Create Group -->
    <div class="modal fade" id="createGroupModal">
        <div class="modal-dialog">
            <form method="post">
                <input type="hidden" name="action" value="create_group">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tạo nhóm mới</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label>Tên nhóm</label><input type="text" name="ten_nhom" class="form-control" required></div>
                        <div class="mb-3"><label>Môn học</label><select name="mon_hoc_id" class="form-select">
                                <option value="">-- Không chọn --</option>
                                <?php mysqli_data_seek($mon_hoc_res, 0);
                                while ($m = mysqli_fetch_assoc($mon_hoc_res)): ?>
                                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['ten_mon']) ?></option>
                                <?php endwhile; ?>
                            </select></div>
                        <div class="mb-3"><label>Mô tả</label><textarea name="mo_ta" class="form-control" rows="3"></textarea></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary">Tạo nhóm</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Group -->
    <div class="modal fade" id="editGroupModal">
        <div class="modal-dialog">
            <form method="post">
                <input type="hidden" name="action" value="edit_group"><input type="hidden" name="nhom_id" id="edit_nhom_id">
                <input type="hidden" name="current_group" value="<?= $_GET['group'] ?? '' ?>">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Sửa nhóm</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label>Tên nhóm</label><input type="text" name="ten_nhom" id="edit_ten_nhom" class="form-control" required></div>
                        <div class="mb-3"><label>Môn học</label><select name="mon_hoc_id" id="edit_mon_hoc" class="form-select">
                                <option value="">-- Không chọn --</option>
                                <?php mysqli_data_seek($mon_hoc_res, 0);
                                while ($m = mysqli_fetch_assoc($mon_hoc_res)): ?>
                                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['ten_mon']) ?></option>
                                <?php endwhile; ?>
                            </select></div>
                        <div class="mb-3"><label>Mô tả</label><textarea name="mo_ta" id="edit_mo_ta" class="form-control" rows="3"></textarea></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary">Cập nhật</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Member -->
    <div class="modal fade" id="addMemberModal">
        <div class="modal-dialog">
            <form onsubmit="return false;">
                <input type="hidden" name="action" value="add_member"><input type="hidden" name="nhom_id" value="<?= $_GET['group'] ?? '' ?>">
                <input type="hidden" name="current_group" value="<?= $_GET['group'] ?? '' ?>">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Thêm thành viên</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label>Email sinh viên</label>
                        <input type="email" id="memberEmail" class="form-control" placeholder="Nhập email..." autocomplete="off">
                        <div id="searchResults" class="mt-2"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button></div>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL THÊM CÔNG VIỆC ===== -->
    <div class="modal fade" id="addTaskModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="post">
                <input type="hidden" name="action" value="add_task">
                <input type="hidden" name="nhom_id" value="<?= $_GET['group'] ?? '' ?>">
                <input type="hidden" name="current_group" value="<?= $_GET['group'] ?? '' ?>">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Thêm công việc</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tên công việc</label>
                            <input type="text" name="tieu_de" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea name="mo_ta" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Người thực hiện</label>
                            <select name="nguoi_giao" class="form-select" required>
                                <?php
                                if ($nhom_hien_tai) {
                                    mysqli_data_seek($thanh_vien_res, 0);
                                    while ($tv = mysqli_fetch_assoc($thanh_vien_res)):
                                ?>
                                        <option value="<?= $tv['id'] ?>"><?= htmlspecialchars($tv['ten_dang_nhap']) ?></option>
                                <?php endwhile;
                                } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hạn hoàn thành</label>
                            <input type="date" name="han" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success">Lưu</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL NỘP CÔNG VIỆC ===== -->
    <div class="modal fade" id="submitTaskModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="submit_task">
                <input type="hidden" name="task_id" id="submit_task_id">
                <input type="hidden" name="current_group" value="<?= $_GET['group'] ?? '' ?>">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nộp kết quả công việc</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nội dung nộp</label>
                            <textarea name="noi_dung_nop" class="form-control" rows="5" placeholder="Mô tả kết quả, link Google Drive, v.v." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File đính kèm (tối đa 10MB)</label>
                            <input type="file" name="file_nop" class="form-control" accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png,.txt,.pptx">
                            <small class="text-muted">Định dạng cho phép: pdf, doc, docx, zip, rar, jpg, jpeg, png, txt, pptx</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Gửi nộp</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL SỬA CÔNG VIỆC ===== -->
    <div class="modal fade" id="editTaskModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="post">
                <input type="hidden" name="action" value="edit_task">
                <input type="hidden" name="task_id" id="edit_task_id">
                <input type="hidden" name="nhom_id" value="<?= $_GET['group'] ?? '' ?>">
                <input type="hidden" name="current_group" value="<?= $_GET['group'] ?? '' ?>">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Sửa công việc</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tên công việc</label>
                            <input type="text" name="tieu_de" id="edit_task_tieu_de" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea name="mo_ta" id="edit_task_mo_ta" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Người thực hiện</label>
                            <select name="nguoi_giao" id="edit_task_nguoi_giao" class="form-select" required>
                                <?php
                                if ($nhom_hien_tai) {
                                    mysqli_data_seek($thanh_vien_res, 0);
                                    while ($tv = mysqli_fetch_assoc($thanh_vien_res)):
                                ?>
                                        <option value="<?= $tv['id'] ?>"><?= htmlspecialchars($tv['ten_dang_nhap']) ?></option>
                                <?php endwhile;
                                } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hạn hoàn thành</label>
                            <input type="date" name="han" id="edit_task_han" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL XÓA CÔNG VIỆC ===== -->
    <div class="modal fade" id="deleteTaskModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="post">
                <input type="hidden" name="action" value="delete_task">
                <input type="hidden" name="task_id" id="delete_task_id">
                <input type="hidden" name="nhom_id" value="<?= $_GET['group'] ?? '' ?>">
                <input type="hidden" name="current_group" value="<?= $_GET['group'] ?? '' ?>">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">Xóa công việc</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn <strong>xóa công việc này</strong>?</p>
                        <p class="text-danger">Hành động này không thể hoàn tác. Toàn bộ nội dung nộp và file sẽ bị xóa vĩnh viễn.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger">Xóa vĩnh viễn</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function filterGroups() {
            let input = document.getElementById('searchGroup').value.toLowerCase();
            document.querySelectorAll('#groupTable tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(input) ? '' : 'none';
            });
        }

        function leaveGroup(id) {
            if (confirm('Rời nhóm?')) {
                let f = document.createElement('form');
                f.method = 'post';
                f.innerHTML = `<input type="hidden" name="action" value="leave_group"><input type="hidden" name="nhom_id" value="${id}">`;
                document.body.appendChild(f);
                f.submit();
            }
        }

        function editGroup(id, ten, mo_ta, mon_id) {
            document.getElementById('edit_nhom_id').value = id;
            document.getElementById('edit_ten_nhom').value = ten;
            document.getElementById('edit_mo_ta').value = mo_ta;
            document.getElementById('edit_mon_hoc').value = mon_id || '';
            new bootstrap.Modal(document.getElementById('editGroupModal')).show();
        }

        function submitTask(id) {
            document.getElementById('submit_task_id').value = id;
            new bootstrap.Modal(document.getElementById('submitTaskModal')).show();
        }

        function cancelSubmit(id) {
            if (confirm('Hủy nộp bài?')) {
                let f = document.createElement('form');
                f.method = 'post';
                f.innerHTML = `<input type="hidden" name="action" value="cancel_submit"><input type="hidden" name="task_id" value="${id}"><input type="hidden" name="current_group" value="<?= $_GET['group'] ?? '' ?>">`;
                document.body.appendChild(f);
                f.submit();
            }
        }

        function editTask(id, tieu_de, mo_ta, nguoi, han) {
            document.getElementById('edit_task_id').value = id;
            document.getElementById('edit_task_tieu_de').value = tieu_de;
            document.getElementById('edit_task_mo_ta').value = mo_ta || '';
            document.getElementById('edit_task_nguoi_giao').value = nguoi;
            document.getElementById('edit_task_han').value = han;
            new bootstrap.Modal(document.getElementById('editTaskModal')).show();
        }

        function deleteTask(id) {
            document.getElementById('delete_task_id').value = id;
            new bootstrap.Modal(document.getElementById('deleteTaskModal')).show();
        }

        // Tìm kiếm thành viên
        document.getElementById('memberEmail')?.addEventListener('input', function() {
            let email = this.value.trim();
            let results = document.getElementById('searchResults');
            if (email.length < 3) return results.innerHTML = '';
            fetch(`?search_email=${encodeURIComponent(email)}&group=<?= $_GET['group'] ?? '' ?>`)
                .then(r => r.text())
                .then(html => results.innerHTML = html);
        });

        window.addMember = function(id, ten, email) {
            let f = document.createElement('form');
            f.method = 'post';
            f.innerHTML = `<input type="hidden" name="action" value="add_member">
                           <input type="hidden" name="email" value="${email}">
                           <input type="hidden" name="nhom_id" value="<?= $_GET['group'] ?? '' ?>">
                           <input type="hidden" name="current_group" value="<?= $_GET['group'] ?? '' ?>">`;
            document.body.appendChild(f);
            f.submit();
        }
    </script>

    <?php include("../partials/footer.php");
    mysqli_close($conn); ?>
</body>

</html>