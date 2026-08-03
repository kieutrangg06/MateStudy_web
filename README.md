# MateStudy - Nền tảng Hỗ trợ Sinh viên Học tập Trực tuyến

> **MateStudy** là nền tảng học tập trực tuyến toàn diện được thiết kế nhằm hỗ trợ sinh viên quản lý thời gian, tối ưu hóa việc học nhóm, trao đổi kinh nghiệm học tập và tham khảo đánh giá giảng viên/môn học.

---

## 📌 Mục lục
- [Giới thiệu](#-giới-thiệu)
- [Tính năng chính](#-tính-năng-chính)
- [Kiến trúc & Công nghệ](#-kiến-trúc--công-nghệ)
- [Cấu trúc dự án](#-cấu-trúc-dự-án)
- [Thiết kế Hệ thống & Biểu đồ UML](#-thiết-kế-hệ-thống--biểu-đồ-uml)
- [Thiết kế Cơ sở dữ liệu](#-thiết-kế-cơ-sở-dữ-liệu)
- [Hình ảnh Demo Giao diện](#-hình-ảnh-demo-giao-diện)
- [Hướng dẫn Cài đặt & Khởi chạy](#-hướng-dẫn-cài-đặt--khởi-chạy)
- [Thành viên thực hiện](#-thành-viên-thực-hiện)

---

## 📜 Giới thiệu

Trong môi trường giáo dục đại học số hóa, nhu cầu về một công cụ quản lý học tập cá nhân và kết nối cộng đồng trở nên cấp thiết. **MateStudy** ra đời nhằm kiến tạo giải pháp:
- **Quản lý học tập**: Quản lý thời khóa biểu, sự kiện cá nhân trên giao diện lịch trực quan.
- **Kết nối nhóm**: Tạo lập, điều hành nhóm đồ án, phân chia công việc (task management) và nộp bài trực tuyến.
- **Diễn đàn học thuật**: Nơi chia sẻ tài liệu, trao đổi thắc mắc, tin tức, tài liệu học tập.
- **Đánh giá minh bạch**: Hệ thống đánh giá môn học & giảng viên từ các khóa trước giúp sinh viên có cái nhìn khách quan khi đăng ký học phần.

---

## ✨ Tính năng chính

### 👨‍🎓 Dành cho Sinh viên
- **Quản lý tài khoản**: Chỉnh sửa thông tin cá nhân, cập nhật ảnh đại diện, đổi mật khẩu an toàn.
- **Thời khóa biểu & Lịch cá nhân**: 
  - Xem lịch học theo Học kỳ/Tuần/Ngày (Calendar View).
  - Thêm, sửa, xóa các sự kiện cá nhân (học bù, họp nhóm, nhắc nhở).
- **Quản lý Nhóm học tập**:
  - Tạo nhóm mới, mời thành viên qua Email.
  - Phân chia công việc (Task), đặt deadline và theo dõi tiến độ.
  - Nộp kết quả bài tập nhóm trực tiếp lên hệ thống.
- **Diễn đàn Sinh viên**:
  - Đăng bài viết chia sẻ kinh nghiệm, hỏi đáp, tin tức, tài liệu (đính kèm tệp PDF, DOCX, Hình ảnh ≤10MB).
  - Tương tác: Bình luận, Thích (Like) bài viết.
- **Đánh giá Môn học & Giảng viên**:
  - Gửi đánh giá, chấm điểm sao (1-5★) và nhận xét.
  - Tra cứu đánh giá từ các khóa trước.
- **Thông báo**:
  - Nhận thông báo tự động về công việc nhóm, nhắc lịch học, tương tác bài viết/bình luận.

### 👨‍💼 Dành cho Admin (Quản trị viên)
- **Quản lý Người dùng**: Xem danh sách, tìm kiếm, khóa/mở khóa hoặc xóa tài khoản sinh viên vi phạm.
- **Quản lý Dữ liệu Học tập**: Quản lý danh mục môn học, giảng viên, lớp học chính thức, lịch học học kỳ.
- **Kiểm duyệt Diễn đàn**: Duyệt bài viết mới, tạm ẩn hoặc xóa các nội dung vi phạm quy chuẩn.
- **Kiểm duyệt Đánh giá**: Phê duyệt hoặc từ chối các bài đánh giá môn học/giảng viên trước khi công khai.

---

## 🛠 Kiến trúc & Công nghệ

* **Frontend**: HTML5, CSS3, JavaScript (ES6+), Responsive Web Design.
* **Backend**: PHP / Node.js (RESTful APIs).
* **Database**: MySQL / MariaDB (Relational Database Management System).
* **Modeling & Documentation**: UML 2.0 (Use Case, Class, Sequence, Activity, State, Communication Diagrams).

---

## 📂 Cấu trúc dự án

```text
matestudy/
├── config/                  # Cấu hình kết nối Database và hệ thống
│   └── database.php
├── public/                  # Các tệp tĩnh truy cập công khai
│   ├── css/                 # Stylesheet (custom CSS)
│   ├── js/                  # JavaScript xử lý giao diện & AJAX
│   ├── images/              # Images, Banner, UI Assets
│   └── uploads/             # Thư mục chứa tài liệu/ảnh người dùng tải lên
├── src/
│   ├── controllers/         # Bộ điều khiển xử lý Logic nghiệp vụ
│   │   ├── AuthController.php
│   │   ├── ScheduleController.php
│   │   ├── GroupController.php
│   │   ├── ForumController.php
│   │   ├── ReviewController.php
│   │   └── AdminController.php
│   ├── models/              # Lớp tương tác với Cơ sở dữ liệu
│   │   ├── User.php
│   │   ├── Course.php
│   │   ├── Group.php
│   │   ├── Post.php
│   │   └── Review.php
│   └── views/               # Giao diện hiển thị (HTML/PHP Templates)
│       ├── admin/           # Trang quản trị (Admin Dashboard)
│       ├── student/         # Trang dành cho Sinh viên
│       ├── auth/            # Trang Đăng nhập / Đăng ký / Đổi mật khẩu
│       └── layouts/         # Header, Footer, Sidebar dùng chung
├── database/                # Script khởi tạo CSDL
│   └── matestudy_db.sql
├── docs/                    # Tài liệu báo cáo & sơ đồ thiết kế UML
│   └── images/
├── README.md                # Tài liệu hướng dẫn dự án
└── index.php                # Entry point chính của ứng dụng
