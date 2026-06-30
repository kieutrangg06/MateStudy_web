<?php
require_once('../config.php');
require_permission([ROLE_STUDENT], '../login.php');
$user_id = $_SESSION['user_id'];

// ===== XỬ LÝ AJAX =====
if (!empty($_POST['action'])) {
  header('Content-Type: application/json');

  $action = $_POST['action'];

  // LẤY DANH SÁCH LỚP HỌC
  if ($action === 'get_classes') {
    $semester = $_POST['semester'] ?? 'hk1-2025';
    $search = $_POST['search'] ?? '';

    $stmt = $conn->prepare("SELECT mh.id, mh.ten_mon AS title, mh.ten_gv AS teacher, mh.dia_diem AS room,
                                       mh.thu AS days, mh.gio_bat_dau AS startTime, mh.gio_ket_thuc AS endTime,
                                       mh.ngay_bat_dau AS startDate, mh.ngay_ket_thuc AS endDate
                                FROM mon_hoc mh
                                JOIN hoc_ky hk ON mh.hoc_ky_id = hk.id
                                WHERE hk.ten_hoc_ky LIKE ? AND (mh.ten_mon LIKE ? OR mh.ten_gv LIKE ?)");
    $like_sem = "%$semester%";
    $like_search = "%$search%";
    $stmt->bind_param('sss', $like_sem, $like_search, $like_search);
    $stmt->execute();
    $res = $stmt->get_result();

    $classes = [];
    while ($row = $res->fetch_assoc()) $classes[] = $row;
    echo json_encode($classes);
    exit;
  }

  // THÊM LỚP HỌC VÀO LỊCH
  if ($action === 'add_class') {
    $mon_id = (int)$_POST['mon_hoc_id'];
    $color = $_POST['color'] ?? '#a12c2f';

    $check = $conn->query("SELECT 1 FROM lich_ca_nhan WHERE sinh_vien_id=$user_id AND mon_hoc_id=$mon_id AND loai='lop_chinh_thuc'");
    if ($check->num_rows === 0) {
      $conn->query("INSERT INTO lich_ca_nhan (sinh_vien_id, loai, mon_hoc_id, mau_sac) VALUES ($user_id, 'lop_chinh_thuc', $mon_id, '$color')");
    }
    echo json_encode(['status' => 'ok']);
    exit;
  }

  // Lấy toàn bộ lớp học + sự kiện cá nhân của sinh viên → chuyển thành mảng $events → trả JSON cho FullCalendar hiển thị
  //FullCalendar KHÔNG biết lặp, nên:
  //PHP phải tự sinh từng buổi học / từng lần lặp
  //Mỗi buổi = 1 event riêng
  if ($action === 'get_events') {
    $events = [];

    // Lớp học chính thức
    $res = $conn->query("SELECT mh.ten_mon AS title, mh.ten_gv AS teacher, mh.dia_diem AS room,
                                    mh.thu AS days, mh.gio_bat_dau AS startTime, mh.gio_ket_thuc AS endTime,
                                    mh.ngay_bat_dau AS startDate, mh.ngay_ket_thuc AS endDate,
                                    lc.mau_sac AS color, lc.id AS lc_id
                             FROM lich_ca_nhan lc
                             JOIN mon_hoc mh ON lc.mon_hoc_id = mh.id
                             WHERE lc.sinh_vien_id = $user_id AND lc.loai = 'lop_chinh_thuc'");

    while ($class = $res->fetch_assoc()) {//Mỗi vòng = 1 môn học
      foreach (explode(',', $class['days']) as $day) {//Tách các ngày trong tuần, Mỗi $day là 1 thứ học
        $dow = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][(int)trim($day)];
        $startDate = new DateTime($class['startDate']);
        $endDate = new DateTime($class['endDate']);
        $current = clone $startDate;

        //Tìm ngày học đầu tiên đúng thứ
        $targetDow = array_search($dow, ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);//số thứ → tên thứ
        $diff = ($targetDow - $current->format('w') + 7) % 7;
        $current->modify("+$diff days");

        while ($current <= $endDate) {
          $events[] = [
            'id' => 'class_' . $class['lc_id'] . '_' . $current->format('Ymd'),
            'title' => $class['title'] . "\n" . $class['room'],
            'start' => $current->format('Y-m-d') . ' ' . $class['startTime'],
            'end' => $current->format('Y-m-d') . ' ' . $class['endTime'],
            'color' => $class['color'],
            'extendedProps' => ['type' => 'class', 'teacher' => $class['teacher'], 'room' => $class['room'], 'lc_id' => $class['lc_id']]
          ];
          $current->modify('+7 days');//sang tuần sau -> hết Hki
        }
      }
    }

    // Sự kiện cá nhân
    $res = $conn->query("SELECT sk.*, lc.mau_sac AS color, lc.id AS lc_id
                             FROM sk_ca_nhan sk
                             JOIN lich_ca_nhan lc ON sk.id = lc.sk_id
                             WHERE lc.sinh_vien_id = $user_id AND lc.loai='su_kien_rieng'");

    while ($ev = $res->fetch_assoc()) {
      $location = $ev['dia_diem'] ?: 'Không có địa điểm';
      $start = new DateTime($ev['ngay_bat_dau']);
      $endRepeat = new DateTime($ev['ngay_ket_thuc']);
      $endRepeat->setTime(23, 59, 59);//được tính trọn vẹn nếu đó là ngày hạn, 24g59p 0g0p

      if ($ev['lap_lai'] === 'khong') {
        $events[] = [
          'id' => 'event_' . $ev['lc_id'],
          'title' => $ev['tieu_de'] . "\n" . $location,
          'start' => $ev['ngay_bat_dau'] . ' ' . $ev['gio_bat_dau'],
          'end' => $ev['ngay_bat_dau'] . ' ' . $ev['gio_ket_thuc'],
          'color' => $ev['color'],
          'extendedProps' => ['type' => 'event', 'lc_id' => $ev['lc_id'], 'sk_id' => $ev['id']]
        ];
      } else {
        $interval = $ev['lap_lai'] === 'hang_ngay' ? '+1 day' : '+7 days';
        $targetDow = $ev['thu'] ? ['CN' => 0, '2' => 1, '3' => 2, '4' => 3, '5' => 4, '6' => 5, '7' => 6][$ev['thu']] : null;//Chỉ hiển thị đúng thứ đã chọn

        $current = clone $start;
        while ($current <= $endRepeat) {
          if ($ev['lap_lai'] === 'hang_tuan' && $targetDow !== null && $current->format('w') != $targetDow) {
            $current->modify($interval);
            continue;
          }
          $events[] = [
            'id' => 'event_' . $ev['lc_id'] . '_' . $current->format('Ymd'),
            'title' => $ev['tieu_de'] . "\n" . $location,
            'start' => $current->format('Y-m-d') . ' ' . $ev['gio_bat_dau'],
            'end' => $current->format('Y-m-d') . ' ' . $ev['gio_ket_thuc'],
            'color' => $ev['color'],
            'extendedProps' => ['type' => 'event', 'lc_id' => $ev['lc_id'], 'sk_id' => $ev['id']]
          ];
          $current->modify($interval);
          if ($current->format('Y') > date('Y') + 5) break;//Không cho tạo quá 5 năm
        }
      }
    }

    echo json_encode($events);//TRẢ DỮ LIỆU CHO FULLCALENDAR
    exit;
  }

  // LƯU/SỬA SỰ KIỆN
  if ($action === 'save_event') {
    $title = $conn->real_escape_string($_POST['title']);
    $location = $conn->real_escape_string($_POST['location'] ?? '');
    $start = $_POST['start'];
    $end = $_POST['end'];
    $color = $_POST['color'] ?? '#a12c2f';
    $lc_id = (int)($_POST['lc_id'] ?? 0);
    $repeat = in_array($_POST['repeat'] ?? '', ['khong', 'hang_ngay', 'hang_tuan']) ? $_POST['repeat'] : 'khong';

    $thu = null;
    if ($repeat === 'hang_tuan') {
      $dow = (new DateTime(substr($start, 0, 10)))->format('w');
      $thu = $dow == 0 ? 'CN' : $dow;
    }

    $ngay_bd = substr($start, 0, 10);
    $gio_bd = substr($start, 11, 5) . ':00';
    $ngay_kt = substr($end, 0, 10);
    $gio_kt = substr($end, 11, 5) . ':00';

    if ($lc_id == 0) {
      // Thêm mới
      $stmt = $conn->prepare("INSERT INTO sk_ca_nhan (sinh_vien_id, tieu_de, dia_diem, thu, gio_bat_dau, gio_ket_thuc, ngay_bat_dau, ngay_ket_thuc, lap_lai)
                                    VALUES ($user_id, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param('ssssssss', $title, $location, $thu, $gio_bd, $gio_kt, $ngay_bd, $ngay_kt, $repeat);
      $stmt->execute();
      $sk_id = $conn->insert_id;

      $stmt2 = $conn->prepare("INSERT INTO lich_ca_nhan (sinh_vien_id, loai, sk_id, mau_sac) VALUES ($user_id, 'su_kien_rieng', ?, ?)");
      $stmt2->bind_param('is', $sk_id, $color);
      $stmt2->execute();
      echo json_encode(['status' => 'ok', 'lc_id' => $conn->insert_id]);
    } else {
      // Cập nhật
      $res = $conn->query("SELECT sk_id FROM lich_ca_nhan WHERE id=$lc_id AND sinh_vien_id=$user_id");
      if ($row = $res->fetch_assoc()) {
        $sk_id = $row['sk_id'];
        $stmt = $conn->prepare("UPDATE sk_ca_nhan SET tieu_de=?, dia_diem=?, thu=?, gio_bat_dau=?, gio_ket_thuc=?, ngay_bat_dau=?, ngay_ket_thuc=?, lap_lai=? WHERE id=?");
        $stmt->bind_param('ssssssssi', $title, $location, $thu, $gio_bd, $gio_kt, $ngay_bd, $ngay_kt, $repeat, $sk_id);
        $stmt->execute();

        $conn->query("UPDATE lich_ca_nhan SET mau_sac='$color' WHERE id=$lc_id");
        echo json_encode(['status' => 'ok']);
      }
    }
    exit;
  }

  // XÓA SỰ KIỆN/LỚP
  if ($action === 'delete') {
    $lc_id = (int)$_POST['lc_id'];
    $type = $_POST['type'];

    if ($type === 'class') {
      $conn->query("DELETE FROM lich_ca_nhan WHERE id=$lc_id AND sinh_vien_id=$user_id AND loai='lop_chinh_thuc'");
    } else {
      $res = $conn->query("SELECT sk_id FROM lich_ca_nhan WHERE id=$lc_id AND sinh_vien_id=$user_id");
      if ($row = $res->fetch_assoc()) {
        $conn->query("DELETE FROM sk_ca_nhan WHERE id={$row['sk_id']}");
      }
      $conn->query("DELETE FROM lich_ca_nhan WHERE id=$lc_id");
    }
    echo json_encode(['status' => 'ok']);
    exit;
  }

  // LẤY CHI TIẾT SỰ KIỆN ĐỂ SỬA
  if ($action === 'get_event_detail') {
    $lc_id = (int)$_POST['lc_id'];
    $stmt = $conn->prepare("SELECT sk.*, lc.mau_sac FROM sk_ca_nhan sk JOIN lich_ca_nhan lc ON sk.id=lc.sk_id WHERE lc.id=? AND lc.sinh_vien_id=?");
    $stmt->bind_param('ii', $lc_id, $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row) {
      echo json_encode([
        'tieu_de' => $row['tieu_de'],
        'dia_diem' => $row['dia_diem'],
        'ngay_bat_dau' => $row['ngay_bat_dau'],
        'gio_bat_dau' => substr($row['gio_bat_dau'], 0, 5),
        'ngay_ket_thuc' => $row['ngay_ket_thuc'],
        'gio_ket_thuc' => substr($row['gio_ket_thuc'], 0, 5),
        'mau_sac' => $row['mau_sac'],
        'lap_lai' => $row['lap_lai'] ?? 'khong'
      ]);
    } else {
      echo json_encode(['error' => 'Không tìm thấy']);
    }
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lịch Học – MateStudy</title>
  <link rel="icon" type="image/png" href="../../images/logo.png">
  <link rel="stylesheet" href="../../css/header-footer.css">
  <link rel="stylesheet" href="../../css/calender.css">
  <link rel="stylesheet" href="../../css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
</head>

<body>
  <?php include("../partials/header.php"); ?>

  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 page-header">
      <div class="position-relative">
        <button class="btn btn-upload d-flex align-items-center gap-2" id="addEventBtn">
          <i class="bi bi-plus-circle-fill"></i> Thêm
        </button>
        <div id="addMenu" class="d-none position-absolute end-0 mt-2 py-2 px-3 rounded-3 shadow-lg bg-white border" style="z-index:1050;min-width:200px;">
          <div class="d-flex align-items-center mb-2 option-item px-3 py-2 rounded" id="addClassOption" style="cursor:pointer;">
            <i class="bi bi-book me-2"></i> Thêm lớp học
          </div>
          <div class="d-flex align-items-center option-item px-3 py-2 rounded" id="addEventOption" style="cursor:pointer;">
            <i class="bi bi-calendar-plus me-2"></i> Thêm sự kiện
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body p-3">
        <div id="calendar"></div>
      </div>
    </div>
  </div>

  <!-- Modal Thêm/Sửa Sự Kiện -->
  <div class="modal fade" id="eventModal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content rounded-4">
        <div class="modal-header border-0 pb-2">
          <h5 class="modal-title fw-semibold" id="eventModalLabel">Thêm sự kiện</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body pt-2">
          <form id="eventForm">
            <input type="hidden" id="edit_lc_id">
            <div class="row g-3">
              <div class="col-12"><label>Tiêu đề <span class="text-danger">*</span></label><input type="text" id="eventTitle" class="form-control" required></div>
              <div class="col-12"><label>Địa điểm</label><input type="text" id="eventLocation" class="form-control"></div>
              <div class="col-12"><label>Lặp lại</label>
                <select id="eventRepeat" class="form-select">
                  <option value="khong">Không lặp</option>
                  <option value="hang_ngay">Hằng ngày</option>
                  <option value="hang_tuan">Hằng tuần (cùng thứ)</option>
                </select>
              </div>
              <div class="col-md-6"><label>Ngày bắt đầu <span class="text-danger">*</span></label><input type="datetime-local" id="eventStart" class="form-control" required></div>
              <div class="col-md-6"><label>Ngày kết thúc <span class="text-danger">*</span></label><input type="datetime-local" id="eventEnd" class="form-control" required></div>
              <div class="col-6"><label>Màu</label><input type="color" id="eventColor" class="form-control form-control-color" value="#a12c2f"></div>
            </div>
            <div class="mt-4 text-end">
              <button type="submit" class="btn btn-primary">Lưu</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Chọn Lớp Học -->
  <div class="modal fade" id="classModal">
    <div class="modal-dialog modal-xl">
      <div class="modal-content rounded-4">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-semibold">Chọn lớp học</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3 mb-3">
            <div class="col-md-4"><label>Học kỳ</label>
              <select id="semesterSelect" class="form-control">
                <?php
                $hk_res = $conn->query("SELECT ten_hoc_ky FROM hoc_ky ORDER BY id DESC");
                while ($hk = $hk_res->fetch_assoc()) {
                  $selected = $hk['ten_hoc_ky'] === 'Học kỳ 1 - 2025-2026' ? 'selected' : '';
                  echo "<option value=\"{$hk['ten_hoc_ky']}\" $selected>{$hk['ten_hoc_ky']}</option>";
                }
                ?>
              </select>
            </div>
            <div class="col-md-5"><label>Tìm kiếm</label><input type="text" id="classSearch" class="form-control" placeholder="Môn học, giảng viên..."></div>
            <div class="col-md-3"><label>Màu</label><input type="color" id="classColorPicker" class="form-control form-control-color" value="#a12c2f"></div>
          </div>
          <div class="table-responsive" style="max-height:450px;">
            <table class="table table-hover">
              <thead class="table-light sticky-top">
                <tr>
                  <th>Môn học</th>
                  <th>Giảng viên</th>
                  <th>Thời gian</th>
                  <th>Phòng</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="classList"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include("../partials/footer.php"); ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'timeGridWeek',
        locale: 'vi',
        slotMinTime: "07:00:00",
        slotMaxTime: "22:00:00",
        height: 'auto',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: (info, success, fail) => fetch('', {
            method: 'POST',
            body: new URLSearchParams({
              action: 'get_events'
            })
          })
          .then(r => r.json()).then(success).catch(fail),

        eventClick: info => {
          const e = info.event,
            p = e.extendedProps,
            isClass = p.type === 'class';
          const format = d => d.toLocaleString('vi-VN', {
            weekday: 'long',
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
          });
          const startStr = format(e.start);
          const endStr = e.end ? ' – ' + new Date(e.end).toLocaleTimeString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit'
          }) : '';
          const [mainTitle, subTitle] = e.title.split('\n');

          const detailHTML = `
           <div class="modal fade" id="detailModal" data-bs-backdrop="static">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4" style="background:#1f272b;color:#fff;">
                  <div class="modal-header border-0 d-flex justify-content-between align-items-center">
                    <h5 class="m-0">Chi tiết</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body pt-2">
                    <h5 class="text-white fw-bold mb-4" style="font-size: 1.5rem; color: #ffffff !important;">${mainTitle}</h5>           
                    ${subTitle ? `<p class="mb-2 text-info"><i class="bi bi-geo-alt-fill me-2"></i>${subTitle}</p>` : ''}
                    <p class="mb-2"><i class="bi bi-calendar3 me-2"></i>${startStr}${endStr}</p>
                    ${p.teacher ? `<p class="mb-2"><i class="bi bi-person-fill me-2"></i>${p.teacher}</p>` : ''}
                    <hr class="text-secondary my-4">
                    <div class="d-flex gap-2">
                      ${!isClass ? `
                        <button class="btn btn-outline-light flex-fill" id="editBtn">
                          <i class="bi bi-pencil-square"></i> Sửa
                        </button>
                      ` : ''}
                      <button class="btn btn-outline-danger flex-fill" id="deleteBtn">
                        <i class="bi bi-trash3-fill"></i> Xóa
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>`;

          document.getElementById('detailModal')?.remove();
          document.body.insertAdjacentHTML('beforeend', detailHTML);
          const modal = new bootstrap.Modal('#detailModal');
          modal.show();

          document.getElementById('editBtn')?.addEventListener('click', () => {
            modal.hide();
            fetch('', {
                method: 'POST',
                body: new URLSearchParams({
                  action: 'get_event_detail',
                  lc_id: p.lc_id
                })
              })
              .then(r => r.json())
              .then(d => {
                document.getElementById('eventModalLabel').textContent = 'Sửa sự kiện';
                document.getElementById('eventTitle').value = d.tieu_de;
                document.getElementById('eventLocation').value = d.dia_diem || '';
                document.getElementById('eventColor').value = d.mau_sac;
                document.getElementById('edit_lc_id').value = p.lc_id;
                document.getElementById('eventRepeat').value = d.lap_lai || 'khong';
                document.getElementById('eventStart').value = `${d.ngay_bat_dau}T${d.gio_bat_dau}`;
                document.getElementById('eventEnd').value = `${d.ngay_ket_thuc}T${d.gio_ket_thuc}`;
                new bootstrap.Modal('#eventModal').show();
              });
          });

          document.getElementById('deleteBtn').addEventListener('click', () => {
            if (confirm(isClass ? 'Xóa lớp học này khỏi lịch?' : 'Xóa sự kiện và các lần lặp?')) {
              fetch('', {
                  method: 'POST',
                  body: new URLSearchParams({
                    action: 'delete',
                    lc_id: p.lc_id,
                    type: p.type
                  })
                })
                .then(() => {
                  e.remove();
                  modal.hide();
                });
            }
          });

          $('#detailModal').on('hidden.bs.modal', function() {
            this.remove();
          });
        }
      });
      calendar.render();

      // Menu Thêm
      const addBtn = document.getElementById('addEventBtn');
      const menu = document.getElementById('addMenu');
      addBtn.onclick = e => {
        e.stopPropagation();
        menu.classList.toggle('d-none');
      };
      document.addEventListener('click', () => menu.classList.add('d-none'));

      document.getElementById('addClassOption').onclick = () => {
        menu.classList.add('d-none');
        loadClasses();
        new bootstrap.Modal('#classModal').show();
      };
      document.getElementById('addEventOption').onclick = () => {
        menu.classList.add('d-none');
        document.getElementById('eventModalLabel').textContent = 'Thêm sự kiện';
        document.getElementById('eventForm').reset();
        document.getElementById('edit_lc_id').value = '';
        document.getElementById('eventRepeat').value = 'khong';
        const now = new Date();
        const end = new Date(now.getTime() + 3600000);
        document.getElementById('eventStart').value = now.toISOString().slice(0, 16);
        document.getElementById('eventEnd').value = end.toISOString().slice(0, 16);
        new bootstrap.Modal('#eventModal').show();
      };

      function loadClasses() {
        const semester = document.getElementById('semesterSelect').value;
        const search = document.getElementById('classSearch').value;
        fetch('', {
            method: 'POST',
            body: new URLSearchParams({
              action: 'get_classes',
              semester,
              search
            })
          })
          .then(r => r.json())
          .then(data => {
            const tbody = document.getElementById('classList');
            tbody.innerHTML = '';
            data.forEach(c => {
              tbody.innerHTML += `<tr>
                <td><strong>${c.title}</strong><br><small class="text-muted">(${c.startDate} – ${c.endDate})</small></td>
                <td>${c.teacher}</td>
                <td>${c.startTime.slice(0,5)}–${c.endTime.slice(0,5)}<br><small>${c.days}</small></td>
                <td>${c.room}</td>
                <td><button class="btn btn-sm btn-success add-class-btn" data-id="${c.id}">Thêm</button></td>
              </tr>`;
            });
            document.querySelectorAll('.add-class-btn').forEach(btn => {
              btn.onclick = () => {
                const color = document.getElementById('classColorPicker').value;
                fetch('', {
                    method: 'POST',
                    body: new URLSearchParams({
                      action: 'add_class',
                      mon_hoc_id: btn.dataset.id,
                      color
                    })
                  })
                  .then(() => {
                    bootstrap.Modal.getInstance('#classModal').hide();
                    calendar.refetchEvents();
                  });
              };
            });
          });
      }

      document.getElementById('classSearch').oninput = loadClasses;
      document.getElementById('semesterSelect').onchange = loadClasses;

      document.getElementById('eventForm').onsubmit = e => {
        e.preventDefault();
        const fd = new URLSearchParams();
        fd.append('action', 'save_event');
        fd.append('title', document.getElementById('eventTitle').value);
        fd.append('location', document.getElementById('eventLocation').value);
        fd.append('start', document.getElementById('eventStart').value);
        fd.append('end', document.getElementById('eventEnd').value);
        fd.append('color', document.getElementById('eventColor').value);
        fd.append('lc_id', document.getElementById('edit_lc_id').value);
        fd.append('repeat', document.getElementById('eventRepeat').value);

        fetch('', {
            method: 'POST',
            body: fd
          })
          .then(() => {
            bootstrap.Modal.getInstance('#eventModal').hide();
            calendar.refetchEvents();
          });
      };

      document.getElementById('eventStart').onchange = function() {
        if (!document.getElementById('eventEnd').value) {
          let d = new Date(this.value);
          d.setHours(d.getHours() + 1);
          document.getElementById('eventEnd').value = d.toISOString().slice(0, 16);
        }
      };
    });
  </script>
</body>

</html>