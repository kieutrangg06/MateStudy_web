-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th1 08, 2026 lúc 04:00 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `matestudy`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bai_viet_dien_dan`
--

CREATE TABLE `bai_viet_dien_dan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tac_gia_id` bigint(20) UNSIGNED NOT NULL,
  `tieu_de` varchar(200) NOT NULL,
  `noi_dung` text NOT NULL,
  `file_dinh_kem` varchar(255) DEFAULT NULL,
  `trang_thai` enum('cho_duyet','da_duyet','bi_an') DEFAULT 'cho_duyet',
  `ngay_dang` timestamp NULL DEFAULT NULL,
  `category` enum('forum','news','market','job','lost','docs') DEFAULT 'forum'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `bai_viet_dien_dan`
--

INSERT INTO `bai_viet_dien_dan` (`id`, `tac_gia_id`, `tieu_de`, `noi_dung`, `file_dinh_kem`, `trang_thai`, `ngay_dang`, `category`) VALUES
(1, 2, 'Ai có tài liệu ôn thi CSDL không?', 'Mình cần tài liệu ôn tập cuối kỳ môn Cơ sở dữ liệu, cảm ơn mọi người!', NULL, 'da_duyet', '2025-11-23 14:08:32', 'forum'),
(2, 4, 'Bán laptop gaming giá sinh viên', 'Dell G15 2022, i7-12700H, RTX 3060, còn bảo hành 18 tháng, giá 18tr', '/uploads/blogs/1764773553_avt-shin.jpg', 'da_duyet', '2024-11-20 07:30:00', 'market'),
(3, 3, '[Tin tuyển dụng] Part-time ReactJS', 'Công ty mình đang tuyển 3 bạn intern ReactJS, lương 5-8tr...', '/uploads/blogs/1764773553_avt-shin.jpg', 'da_duyet', '2024-11-18 02:15:00', 'job'),
(4, 6, 'Mất ví ở ký túc xá khu B', 'Mất ví da màu đen sáng nay ở sảnh B, bên trong có CMND và thẻ sinh viên...', NULL, 'bi_an', '2025-11-23 14:08:32', 'lost'),
(5, 1, 'Thông báo lịch thi học kỳ 1 2024-2025', 'Chi tiết lịch thi đã được cập nhật trên website trường...', '/uploads/blogs/1764773553_avt-shin.jpg', 'da_duyet', '2024-11-10 01:00:00', 'news'),
(6, 2, 'Chia sẻ tài liệu môn Toán Rời Rạc', 'Tổng hợp kiến thức và bài tập lớn môn Toán rời rạc.', '/uploads/blogs/1764773553_avt-shin.jpg', 'da_duyet', '2025-11-25 10:15:00', 'docs'),
(7, 5, 'Tìm mua sách giáo trình CSDL', 'Mình cần mua lại giáo trình \"Cơ sở dữ liệu\" bản mới nhất.', NULL, 'da_duyet', '2025-11-26 05:40:00', 'market'),
(8, 7, 'Thắc mắc về API ReactJS', 'Làm sao để xử lý lỗi CORS khi gọi API từ server?', NULL, 'da_duyet', '2025-11-26 12:20:00', 'forum'),
(13, 2, 'Bán lap', 'Mình cần pass laptop cũ', '/uploads/blogs/1764773553_avt-shin.jpg', 'da_duyet', '2025-12-02 05:19:53', 'market'),
(14, 2, 'ffd', 'ssadws', NULL, 'da_duyet', '2025-12-02 05:27:24', 'lost'),
(15, 2, 'mất đồ', 'mất vì k12sfhksadjadxx', '/uploads/blogs/1764773553_avt-shin.jpg', 'da_duyet', '2025-12-03 14:26:14', 'lost'),
(16, 2, 'dddddddddd', 'sssssssssssssssssss', '/uploads/blogs/1764773553_avt-shin.jpg', 'da_duyet', '2025-12-03 14:30:26', 'news'),
(21, 2, '1111111111111111111111', '111111111111111', '/uploads/blogs/1764773553_avt-shin.jpg', 'cho_duyet', '2025-12-03 14:39:02', 'job'),
(25, 2, '222222222222222', '2222222222222222', '/uploads/blogs/1764773553_avt-shin.jpg', 'cho_duyet', '2025-12-03 14:40:00', 'forum'),
(27, 2, '4444444444444444', '4444444444444444', '/uploads/blogs/1764773553_avt-shin.jpg', 'cho_duyet', '2025-12-03 14:40:38', 'job'),
(34, 2, 'chaof', 'aaa', '/uploads/blogs/1764774405_anh-nobita-7.jpg', 'da_duyet', '2025-12-03 15:06:45', 'news'),
(36, 2, 'mua', 'bán', '/uploads/blogs/1764774727_avt.jpg', 'da_duyet', '2025-12-03 15:12:07', 'market'),
(37, 2, 'Phân tích thiết kế hệ thống slide', 'Slide', '/uploads/blogs/1765684644_SAD- Ch1_Introduction to object-oriented development.pdf', 'da_duyet', '2025-12-14 03:57:24', 'docs');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `binh_luan_dien_dan`
--

CREATE TABLE `binh_luan_dien_dan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bai_viet_id` bigint(20) UNSIGNED NOT NULL,
  `tac_gia_id` bigint(20) UNSIGNED NOT NULL,
  `noi_dung` text NOT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `binh_luan_dien_dan`
--

INSERT INTO `binh_luan_dien_dan` (`id`, `bai_viet_id`, `tac_gia_id`, `noi_dung`, `ngay_tao`) VALUES
(2, 1, 7, 'Cảm ơn bạn nhiều!', '2025-11-23 14:08:32'),
(4, 3, 9, 'Bạn gửi CV vào email hr@company.vn nhé!', '2025-11-23 14:08:32'),
(5, 6, 3, 'Tài liệu rất chi tiết, cảm ơn bạn!', '2025-11-25 10:30:00'),
(6, 6, 4, 'Link download bị lỗi rồi bạn ơi.', '2025-11-25 10:45:00'),
(7, 7, 2, 'Mình có dư một cuốn, bạn có thể mua với giá 50k.', '2025-11-26 06:10:00'),
(8, 8, 9, 'Bạn thử thêm header \"Access-Control-Allow-Origin: *\" xem sao.', '2025-11-26 13:00:00'),
(9, 1, 6, 'Đúng là slide của thầy rất hữu ích!', '2025-11-27 00:05:00'),
(10, 5, 5, 'Thông báo này quan trọng, mình đã lưu lại.', '2025-11-27 09:00:00'),
(13, 6, 2, 'ok bro', '2025-12-02 05:26:20'),
(14, 6, 2, 'hhh', '2025-12-02 05:26:33'),
(15, 6, 2, 'k', '2025-12-03 08:38:28');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cong_viec_nhom`
--

CREATE TABLE `cong_viec_nhom` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nhom_id` bigint(20) UNSIGNED NOT NULL,
  `tieu_de` varchar(150) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `nguoi_duoc_giao` bigint(20) UNSIGNED DEFAULT NULL,
  `han_hoan_thanh` datetime NOT NULL,
  `noi_dung_nop` text DEFAULT NULL,
  `file_nop` varchar(255) DEFAULT NULL,
  `ngay_nop` timestamp NULL DEFAULT NULL,
  `trang_thai` enum('da_nop','chua_nop') DEFAULT 'chua_nop'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cong_viec_nhom`
--

INSERT INTO `cong_viec_nhom` (`id`, `nhom_id`, `tieu_de`, `mo_ta`, `nguoi_duoc_giao`, `han_hoan_thanh`, `noi_dung_nop`, `file_nop`, `ngay_nop`, `trang_thai`) VALUES
(1, 1, 'Thiết kế giao diện', 'Làm trang chủ + login', 4, '2024-12-15 23:59:00', 'Đã upload file Figma', NULL, '2024-12-10 03:30:00', 'da_nop'),
(3, 2, 'Báo cáo bài tập lớn CSDL', 'Phần ERD + Query', 6, '2024-12-18 23:59:00', 'File PDF báo cáo', NULL, '2024-12-17 15:15:00', 'da_nop'),
(4, 4, 'Phân tích yêu cầu', 'Xây dựng tài liệu SRS cho đồ án PTTKTT', 7, '2025-02-01 23:59:00', 'File SRS_v1.0.docx', NULL, '2025-01-29 10:00:00', 'da_nop'),
(5, 4, 'Thiết kế Database', 'Thiết kế mô hình quan hệ và chuẩn hóa CSDL', 3, '2025-02-10 23:59:00', NULL, NULL, NULL, 'chua_nop'),
(6, 5, 'Wireframe Desktop', 'Thiết kế giao diện cho màn hình Desktop', 9, '2025-01-30 23:59:00', 'Link Figma', NULL, '2025-01-28 14:00:00', 'da_nop'),
(7, 5, 'Prototype Mobile', 'Tạo bản mẫu cho ứng dụng mobile', 2, '2025-02-15 23:59:00', NULL, NULL, NULL, 'chua_nop'),
(8, 6, 'Bài Lab 1: TCP/IP', 'Hoàn thành báo cáo Lab 1 và nộp file', 6, '2025-10-10 23:59:00', 'Bao_cao_lab1.pdf', NULL, '2025-10-08 20:00:00', 'da_nop'),
(9, 1, 'nộp đề cương', 'nộp đề cương đồ án chi tiết', 6, '2025-11-30 23:59:59', NULL, NULL, NULL, 'chua_nop'),
(10, 1, 'nộp slide', '', 2, '2025-12-02 23:59:59', 'sdghsdmQ', '/uploads/tasks/task_13_1764771393.docx', '2025-12-02 19:56:57', 'da_nop'),
(11, 3, 'nộp đề cương', 'sssss', 2, '2025-12-02 23:59:59', NULL, NULL, NULL, 'chua_nop'),
(12, 1, 'nộp đề cương', 'cfffff', 2, '2025-12-03 23:59:59', NULL, NULL, NULL, 'chua_nop'),
(13, 1, 'nộp slide', '', 2, '2025-12-04 23:59:59', 'aaa', '/uploads/tasks/task_13_1764771393.docx', '2025-12-03 08:16:33', 'da_nop'),
(14, 1, 'nộp slide đề cương', 'qqqq', 2, '2025-12-27 23:59:59', 'l', '/uploads/tasks/task_14_1764773062.docx', '2025-12-03 08:44:22', 'da_nop'),
(15, 2, 'nộp đề cương', 'l', 2, '2025-12-04 23:59:59', 'o', '/uploads/tasks/task_15_1764774547.docx', '2025-12-03 09:09:07', 'da_nop'),
(16, 1, 'nộp slide', 'lllll', 2, '2025-12-05 23:59:59', NULL, NULL, NULL, 'chua_nop'),
(17, 2, 'l', 'l', 2, '2025-12-25 23:59:59', NULL, NULL, NULL, 'chua_nop'),
(18, 1, 'nộp đề cương', '111', 2, '2025-12-03 23:59:59', NULL, NULL, NULL, 'chua_nop'),
(19, 3, 'nộp slide', 'slide', 2, '2025-12-05 23:59:59', NULL, NULL, NULL, 'chua_nop'),
(20, 3, 'nộp', 'bt', 2, '2025-12-12 23:59:59', NULL, NULL, NULL, 'chua_nop'),
(21, 3, 'nộp slide đề cương', '', 2, '2025-12-19 23:59:59', NULL, NULL, NULL, 'chua_nop'),
(22, 9, 'nộp đề cương', '', 3, '2025-12-11 23:59:59', NULL, NULL, NULL, 'chua_nop'),
(23, 1, 'hahha', '', 6, '2026-01-30 23:59:59', NULL, NULL, NULL, 'chua_nop');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_gia_mon_giang_vien`
--

CREATE TABLE `danh_gia_mon_giang_vien` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mon_hoc_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sinh_vien_id` bigint(20) UNSIGNED NOT NULL,
  `diem_sao` tinyint(4) DEFAULT NULL CHECK (`diem_sao` between 1 and 5),
  `noi_dung` text NOT NULL,
  `trang_thai` enum('cho_duyet','da_duyet','bi_an') DEFAULT 'cho_duyet',
  `ngay_dang` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_gia_mon_giang_vien`
--

INSERT INTO `danh_gia_mon_giang_vien` (`id`, `mon_hoc_id`, `sinh_vien_id`, `diem_sao`, `noi_dung`, `trang_thai`, `ngay_dang`) VALUES
(1, 1, 2, 5, 'Thầy dạy rất nhiệt tình, code mẫu rất rõ ràng, recommend 10/10', 'bi_an', '2025-11-23 14:08:32'),
(3, 2, 3, 3, 'Cô giảng nhanh quá, mình không theo kịp', 'da_duyet', '2025-11-23 14:08:32'),
(4, 3, 6, 5, 'Môn hay, thầy giải thích rất logic', 'da_duyet', '2025-11-23 14:08:32'),
(5, 2, 5, 5, 'Cô Trần Thị B giảng dạy rất dễ hiểu, ví dụ thực tế.', 'bi_an', '2025-11-24 10:00:00'),
(6, 4, 7, 4, 'Thầy Phạm D rất nghiêm khắc, nhưng kiến thức chắc chắn.', 'da_duyet', '2025-11-24 11:00:00'),
(7, 6, 2, 5, 'Môn Hệ điều hành thầy F dạy rất hay, code minh họa chi tiết.', 'da_duyet', '2025-11-25 15:00:00'),
(8, 7, 3, 4, 'Cô G có nhiều kinh nghiệm thực tế về UI/UX.', 'da_duyet', '2025-11-25 16:00:00'),
(9, 8, 4, 3, 'Thầy H giảng hơi nhanh, cần thêm thời gian để tiêu hóa kiến thức.', 'da_duyet', '2025-11-26 10:00:00'),
(12, 2, 2, 4, 'zszhdiwqdjlAWUYIAOJIUWESD', 'da_duyet', '2025-12-03 01:49:18'),
(13, 6, 2, 4, 'zzzzzzadddddddddd', 'bi_an', '2025-12-03 01:49:40'),
(14, 5, 2, 4, 'haaaaaaaaaa', 'da_duyet', '2025-12-03 08:41:03'),
(15, 9, 2, 3, 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'da_duyet', '2025-12-13 17:30:05');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoc_ky`
--

CREATE TABLE `hoc_ky` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ten_hoc_ky` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `hoc_ky`
--

INSERT INTO `hoc_ky` (`id`, `ten_hoc_ky`) VALUES
(1, 'Học kỳ 1 - 2024-2025'),
(2, 'Học kỳ 2 - 2024-2025'),
(3, 'Học kỳ 1 - 2025-2026'),
(4, 'Học kỳ hè - 2025-2026');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_ca_nhan`
--

CREATE TABLE `lich_ca_nhan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sinh_vien_id` bigint(20) UNSIGNED NOT NULL,
  `loai` enum('lop_chinh_thuc','su_kien_rieng') NOT NULL,
  `mon_hoc_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sk_id` bigint(20) UNSIGNED DEFAULT NULL,
  `mau_sac` varchar(7) DEFAULT '#3788d8'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `lich_ca_nhan`
--

INSERT INTO `lich_ca_nhan` (`id`, `sinh_vien_id`, `loai`, `mon_hoc_id`, `sk_id`, `mau_sac`) VALUES
(1, 2, 'lop_chinh_thuc', 1, NULL, '#3788d8'),
(2, 2, 'lop_chinh_thuc', 2, NULL, '#ff9800'),
(3, 2, 'su_kien_rieng', NULL, 1, '#e91e63'),
(4, 3, 'lop_chinh_thuc', 3, NULL, '#4caf50'),
(5, 3, 'lop_chinh_thuc', 4, NULL, '#9c27b0'),
(6, 3, 'su_kien_rieng', NULL, 2, '#f44336'),
(7, 4, 'lop_chinh_thuc', 1, NULL, '#3788d8'),
(8, 4, 'lop_chinh_thuc', 2, NULL, '#3788d8'),
(9, 4, 'su_kien_rieng', NULL, 3, '#3788d8'),
(10, 5, 'lop_chinh_thuc', 1, NULL, '#3788d8'),
(11, 6, 'lop_chinh_thuc', 5, NULL, '#3788d8'),
(12, 5, 'lop_chinh_thuc', 6, NULL, '#ff5722'),
(13, 6, 'lop_chinh_thuc', 7, NULL, '#8bc34a'),
(14, 7, 'lop_chinh_thuc', 8, NULL, '#00bcd4'),
(16, 9, 'lop_chinh_thuc', 10, NULL, '#607d8b'),
(17, 2, 'lop_chinh_thuc', 7, NULL, '#8bc34a'),
(18, 4, 'lop_chinh_thuc', 9, NULL, '#ffeb3b'),
(19, 5, 'su_kien_rieng', NULL, 4, '#f44336'),
(20, 6, 'su_kien_rieng', NULL, 5, '#e91e63'),
(21, 7, 'su_kien_rieng', NULL, 6, '#3f51b5'),
(23, 9, 'su_kien_rieng', NULL, 8, '#ff9800'),
(24, 2, 'lop_chinh_thuc', 3, NULL, '#3788d8'),
(26, 2, 'lop_chinh_thuc', NULL, NULL, '#a12c2f'),
(27, 2, 'su_kien_rieng', NULL, 9, '#a12c2f'),
(28, 2, 'su_kien_rieng', NULL, 10, '#a12c2f'),
(29, 5, 'lop_chinh_thuc', 9, NULL, '#a12c2f'),
(30, 5, 'su_kien_rieng', NULL, 11, '#5aa02c'),
(32, 2, 'su_kien_rieng', NULL, 12, '#a12c2f'),
(33, 2, 'su_kien_rieng', NULL, 13, '#2ca039'),
(34, 2, 'su_kien_rieng', NULL, 14, '#2c98a0'),
(35, 2, 'lop_chinh_thuc', 10, NULL, '#a12c2f'),
(36, 2, 'su_kien_rieng', NULL, 15, '#a02c96'),
(37, 2, 'su_kien_rieng', NULL, 16, '#a12c2f'),
(38, 2, 'lop_chinh_thuc', 9, NULL, '#a12c2f'),
(39, 2, 'su_kien_rieng', NULL, 17, '#a12c2f'),
(40, 2, 'su_kien_rieng', NULL, 18, '#2c58a0'),
(41, 2, 'su_kien_rieng', NULL, 19, '#a12c2f');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `luot_thich_bai_viet`
--

CREATE TABLE `luot_thich_bai_viet` (
  `bai_viet_id` bigint(20) UNSIGNED NOT NULL,
  `sinh_vien_id` bigint(20) UNSIGNED NOT NULL,
  `ngay_thich` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `luot_thich_bai_viet`
--

INSERT INTO `luot_thich_bai_viet` (`bai_viet_id`, `sinh_vien_id`, `ngay_thich`) VALUES
(1, 2, '2025-12-02 05:27:12'),
(1, 3, '2025-11-23 14:08:32'),
(1, 4, '2025-11-24 08:00:00'),
(1, 5, '2025-11-23 14:08:32'),
(1, 6, '2025-11-24 09:00:00'),
(1, 7, '2025-11-23 14:08:32'),
(2, 2, '2024-11-20 08:00:00'),
(2, 3, '2024-11-20 09:00:00'),
(2, 4, '2025-11-23 14:08:32'),
(2, 6, '2025-11-23 14:08:32'),
(3, 2, '2025-11-23 14:08:32'),
(3, 5, '2025-11-23 14:08:32'),
(3, 9, '2025-11-23 14:08:32'),
(5, 2, '2024-11-10 01:30:00'),
(5, 4, '2024-11-10 01:45:00'),
(6, 2, '2025-11-28 15:25:41'),
(6, 7, '2025-11-25 11:00:00'),
(7, 2, '2025-12-03 08:37:57'),
(7, 9, '2025-11-26 07:00:00'),
(8, 3, '2025-11-26 13:20:00'),
(13, 2, '2025-12-02 05:26:47'),
(37, 2, '2026-01-08 13:52:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `mon_hoc`
--

CREATE TABLE `mon_hoc` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ten_mon` varchar(150) NOT NULL,
  `ten_gv` varchar(100) NOT NULL,
  `dia_diem` varchar(100) DEFAULT NULL,
  `thu` enum('2','3','4','5','6','7','CN') DEFAULT NULL,
  `gio_bat_dau` time DEFAULT NULL,
  `gio_ket_thuc` time DEFAULT NULL,
  `ngay_bat_dau` date NOT NULL,
  `ngay_ket_thuc` date NOT NULL,
  `hoc_ky_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `mon_hoc`
--

INSERT INTO `mon_hoc` (`id`, `ten_mon`, `ten_gv`, `dia_diem`, `thu`, `gio_bat_dau`, `gio_ket_thuc`, `ngay_bat_dau`, `ngay_ket_thuc`, `hoc_ky_id`) VALUES
(1, 'Lập trình Web', 'TS. Nguyễn Văn A', 'A301', '3', '07:30:00', '10:00:00', '2024-09-02', '2024-12-20', 1),
(2, 'Cơ sở dữ liệu', 'ThS. Trần Thị B', 'B205', '4', '09:00:00', '11:30:00', '2024-09-02', '2024-12-20', 1),
(3, 'Nhập môn trí tuệ nhân tạo', 'TS. Lê Văn C', 'C102', '5', '13:30:00', '16:00:00', '2024-09-02', '2024-12-20', 1),
(4, 'Toán rời rạc', 'PGS.TS. Phạm D', 'A105', '2', '09:00:00', '11:30:00', '2024-09-02', '2024-12-20', 1),
(5, 'Kỹ thuật lập trình', 'ThS. Hoàng Thị E', 'Lab1', '6', '07:30:00', '10:00:00', '2024-09-02', '2024-12-20', 1),
(6, 'Hệ điều hành', 'PGS.TS. Nguyễn Văn F', 'B302', '3', '13:30:00', '16:00:00', '2025-01-06', '2026-01-13', 2),
(7, 'Thiết kế giao diện người dùng', 'ThS. Lê Thị G', 'Lab2', '5', '07:30:00', '10:00:00', '2025-01-06', '2026-01-13', 2),
(8, 'Phân tích và Thiết kế thuật toán', 'TS. Hoàng Văn H', 'C101', '4', '14:00:00', '16:30:00', '2025-01-06', '2026-01-13', 2),
(9, 'Mạng máy tính', 'TS. Trần Văn K', 'A401', '2', '07:30:00', '10:00:00', '2025-09-01', '2026-01-13', 3),
(10, 'Công nghệ phần mềm', 'ThS. Phạm Thị L', 'B105', '6', '13:30:00', '16:00:00', '2025-09-01', '2026-01-13', 3),
(11, 'Phân tích thiết kế hệ thống', 'TS. A', 'A401', '6', '07:30:00', '09:20:00', '2025-12-24', '2026-01-13', 2);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ten_dang_nhap` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `nien_khoa` year(4) DEFAULT NULL,
  `vai_tro` enum('sinh_vien','admin') DEFAULT 'sinh_vien',
  `trang_thai` enum('hoat_dong','bi_khoa','da_xoa') DEFAULT 'hoat_dong',
  `anh_dai_dien` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`id`, `ten_dang_nhap`, `email`, `mat_khau`, `nien_khoa`, `vai_tro`, `trang_thai`, `anh_dai_dien`) VALUES
(1, 'admin', 'admin@matestudy.vn', '$2y$10$giEaJWleyRPCYYj.ZFNrEeAxJn/2AXihSwpIB4kgkaLBzZs1pdEpi', NULL, 'admin', 'hoat_dong', '/images/avatars/2_1764770823.jpg'),
(2, 'sv2020001', 'sv2020001@gmail.com', '$2y$10$Hi6YpHynfOdZKMOG2KmSx.u7UIDqPljMk/7WXsQOBkcw7uI7XMcbq', '2024', 'sinh_vien', 'hoat_dong', '/images/avatars/2_1765724557.jpg'),
(3, 'sv2021002', 'sv2021002@gmail.com', '$2y$10$Hi6YpHynfOdZKMOG2KmSx.u7UIDqPljMk/7WXsQOBkcw7uI7XMcbq', '2021', 'sinh_vien', 'hoat_dong', '/images/avatars/2_1764770823.jpg'),
(4, 'sv2021015', 'nguyenvana@gmail.com', '$2y$10$Hi6YpHynfOdZKMOG2KmSx.u7UIDqPljMk/7WXsQOBkcw7uI7XMcbq', '2021', 'sinh_vien', 'hoat_dong', '/images/avatars/2_1764770823.jpg'),
(5, 'sv2021037', 'tranb@gmail.com', '$2y$10$Hi6YpHynfOdZKMOG2KmSx.u7UIDqPljMk/7WXsQOBkcw7uI7XMcbq', '2021', 'sinh_vien', 'hoat_dong', '/images/avatars/2_1764770823.jpg'),
(6, 'sv2022058', 'lethic@gmail.com', '$2y$10$Hi6YpHynfOdZKMOG2KmSx.u7UIDqPljMk/7WXsQOBkcw7uI7XMcbq', '2022', 'sinh_vien', 'hoat_dong', '/images/avatars/2_1764770823.jpg'),
(7, 'sv2022111', 'phamvand@gmail.com', '$2y$10$Hi6YpHynfOdZKMOG2KmSx.u7UIDqPljMk/7WXsQOBkcw7uI7XMcbq', '2022', 'sinh_vien', 'hoat_dong', '/images/avatars/2_1764770823.jpg'),
(9, 'sv2023456', 'dangthuy@gmail.com', '$2y$10$Hi6YpHynfOdZKMOG2KmSx.u7UIDqPljMk/7WXsQOBkcw7uI7XMcbq', '2023', 'sinh_vien', 'hoat_dong', '/images/avatars/2_1764770823.jpg'),
(10, 'vy', 'vy20200011222@gmail.com', '$2y$10$Hi6YpHynfOdZKMOG2KmSx.u7UIDqPljMk/7WXsQOBkcw7uI7XMcbq', '2024', '', 'hoat_dong', '/images/avatars/2_1764770823.jpg'),
(11, 'trang', 'trang20200011222@gmail.com', '$2y$10$Hi6YpHynfOdZKMOG2KmSx.u7UIDqPljMk/7WXsQOBkcw7uI7XMcbq', '2024', 'sinh_vien', 'hoat_dong', '/images/avatars/2_1764770823.jpg'),
(12, 'sv202102', 'sv202102@gmail.com', '$2y$10$Hi6YpHynfOdZKMOG2KmSx.u7UIDqPljMk/7WXsQOBkcw7uI7XMcbq', '2021', 'sinh_vien', 'hoat_dong', '/images/avatars/2_1764770823.jpg'),
(13, 'sv20210155', 'sv20210155@gmail.com', '$2y$10$Hi6YpHynfOdZKMOG2KmSx.u7UIDqPljMk/7WXsQOBkcw7uI7XMcbq', '2021', 'sinh_vien', 'hoat_dong', '/images/avatars/2_1764770823.jpg'),
(14, 'kieutrangg', 'kieutrangg06123@gmail.com', '$2y$10$6pQ7FT9qjHrCd0IdAIkp5.lAA1gTQnbrFurOaONPj2vJl3TCmKu7u', '2024', 'sinh_vien', 'hoat_dong', '/images/avatars/avt.jpg'),
(15, 'nhii', 'nhii123@gmail.com', '$2y$10$.CRLSWonWeGcytU6G.b5uu2/vFc0kuz1.1Q7KMLdOoRYa8SBkxkla', '2021', 'sinh_vien', 'hoat_dong', '/images/avatars/avt.jpg'),
(16, 'Tran', 'trang@gmail.com', '$2y$10$Hi6YpHynfOdZKMOG2KmSx.u7UIDqPljMk/7WXsQOBkcw7uI7XMcbq', '2022', 'sinh_vien', 'hoat_dong', '/images/avatars/avt.jpg'),
(17, 'binh', 'binh@gmail.com', '$2y$10$Hi6YpHynfOdZKMOG2KmSx.u7UIDqPljMk/7WXsQOBkcw7uI7XMcbq', '2023', 'sinh_vien', 'hoat_dong', '/images/avatars/avt.jpg'),
(18, 'tienhoang', 'tienhoang@gmail.com', '$2y$10$5QDmozSK.gg5SUm.uaBYg.nZPYnFa3aeWVePQatsVC7VWfcLhr8HS', '2020', 'sinh_vien', 'hoat_dong', '/images/avatars/avt.jpg');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhom_hoc_tap`
--

CREATE TABLE `nhom_hoc_tap` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ten_nhom` varchar(100) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `mon_hoc_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nhom_hoc_tap`
--

INSERT INTO `nhom_hoc_tap` (`id`, `ten_nhom`, `mo_ta`, `mon_hoc_id`) VALUES
(1, 'Nhóm 1 - Đồ án Web', 'Làm đồ án cuối kỳ Lập trình Web', 1),
(2, 'Nhóm 2 - Cơ sở dữ liệu', 'Nhóm học chung CSDL', 2),
(3, 'Nhóm tự do trí tuệ nhân tạo', 'Thảo luận AIoo', 3),
(4, 'Nhóm PTTKTT', 'Phân tích và Thiết kế Thuật toán - Đồ án cuối kỳ', 8),
(5, 'Nhóm UX/UI Design', 'Thảo luận và làm bài tập môn Thiết kế Giao diện', 7),
(6, 'Nhóm 1 - Mạng máy tính', 'Làm các bài lab môn Mạng máy tính', 9),
(7, 'PTTKHT', 'Làm biểu đồ UC', 8),
(8, 'Đồ án', 'Hạn nộp 10/12', 1),
(9, 'Nhom', 'ssss', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sk_ca_nhan`
--

CREATE TABLE `sk_ca_nhan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sinh_vien_id` bigint(20) UNSIGNED NOT NULL,
  `tieu_de` varchar(150) NOT NULL,
  `dia_diem` varchar(100) DEFAULT NULL,
  `thu` enum('2','3','4','5','6','7','CN') DEFAULT NULL,
  `lap_lai` enum('khong','hang_ngay','hang_tuan') NOT NULL DEFAULT 'khong',
  `gio_bat_dau` time DEFAULT NULL,
  `gio_ket_thuc` time DEFAULT NULL,
  `ngay_bat_dau` date NOT NULL,
  `ngay_ket_thuc` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `sk_ca_nhan`
--

INSERT INTO `sk_ca_nhan` (`id`, `sinh_vien_id`, `tieu_de`, `dia_diem`, `thu`, `lap_lai`, `gio_bat_dau`, `gio_ket_thuc`, `ngay_bat_dau`, `ngay_ket_thuc`) VALUES
(1, 2, 'Sinh nhật bạn thân', 'Quán Cafe XYZ', '6', 'khong', '18:00:00', '22:00:00', '2025-01-18', '2025-01-18'),
(2, 3, 'Thi lại môn Toán cao cấp', 'Phòng A201', '4', 'khong', '14:00:00', '16:00:00', '2025-01-10', '2025-01-10'),
(3, 4, 'Họp nhóm đồ án Web', 'Thư viện tầng 3', 'CN', 'khong', '09:00:00', '12:00:00', '2024-12-01', '2024-12-01'),
(4, 5, 'Hẹn gặp nhóm làm assignment', 'Thư viện KTX', '5', 'khong', '19:00:00', '21:00:00', '2025-01-09', '2025-01-09'),
(5, 6, 'Xem phim Black Panther 2', 'Rạp CGV', '7', 'khong', '19:30:00', '22:00:00', '2025-01-11', '2025-01-11'),
(6, 7, 'Kiểm tra giữa kỳ Mạng máy tính', 'Phòng C303', '3', 'khong', '08:00:00', '09:30:00', '2025-10-20', '2025-10-20'),
(8, 9, 'Đăng ký học phần HK2', 'Tại nhà', '2', 'khong', '07:00:00', '08:00:00', '2025-01-05', '2025-01-05'),
(9, 2, '', '', NULL, 'khong', '00:00:00', '00:00:00', '0000-00-00', '0000-00-00'),
(10, 2, 'ddd', 'a', NULL, 'khong', '07:25:00', '08:25:00', '2025-11-28', '2025-11-28'),
(11, 5, 'Đi chơi', 'công viên 29/3', NULL, 'khong', '16:16:00', '17:16:00', '2025-11-28', '2025-11-28'),
(12, 2, 'Làm nhóm', 'Zone 6', NULL, 'hang_tuan', '06:00:00', '07:00:00', '2025-12-05', '2025-12-05'),
(13, 2, 'Đi chơi', 'công viên 29/3', NULL, 'khong', '18:00:00', '21:00:00', '2025-12-05', '2025-12-05'),
(14, 2, 'Học bù', 'A113', NULL, 'khong', '07:30:00', '09:30:00', '2025-12-06', '2025-12-06'),
(15, 2, 'Ielts', '', NULL, 'khong', '18:00:00', '19:30:00', '2025-12-03', '2025-12-03'),
(16, 2, 'Làm nhóm', 'Icoffee', 'CN', 'khong', '08:00:00', '09:00:00', '2025-12-03', '2026-01-01'),
(17, 2, 'Hẹn hò', 'Lotte', '5', 'hang_tuan', '19:00:00', '21:00:00', '2025-12-04', '2025-12-31'),
(18, 2, 'học', 'trường', '6', 'hang_tuan', '07:00:00', '10:00:00', '2025-12-05', '2026-01-02'),
(19, 2, 'Làm nhóm', 'Zone 6', NULL, 'khong', '17:00:00', '21:00:00', '2026-01-09', '2026-01-09');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thanh_vien_nhom`
--

CREATE TABLE `thanh_vien_nhom` (
  `nhom_id` bigint(20) UNSIGNED NOT NULL,
  `sinh_vien_id` bigint(20) UNSIGNED NOT NULL,
  `vai_tro` enum('thanh_vien','truong_nhom') DEFAULT 'thanh_vien'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thanh_vien_nhom`
--

INSERT INTO `thanh_vien_nhom` (`nhom_id`, `sinh_vien_id`, `vai_tro`) VALUES
(1, 2, 'truong_nhom'),
(1, 4, 'thanh_vien'),
(1, 5, 'thanh_vien'),
(1, 6, 'thanh_vien'),
(1, 7, 'thanh_vien'),
(1, 18, 'thanh_vien'),
(2, 2, 'thanh_vien'),
(2, 3, 'truong_nhom'),
(2, 6, 'thanh_vien'),
(2, 11, 'thanh_vien'),
(3, 4, 'thanh_vien'),
(3, 6, 'thanh_vien'),
(3, 9, 'thanh_vien'),
(4, 3, 'thanh_vien'),
(4, 5, 'truong_nhom'),
(4, 7, 'thanh_vien'),
(5, 2, 'thanh_vien'),
(5, 4, 'thanh_vien'),
(5, 6, 'thanh_vien'),
(5, 9, 'truong_nhom'),
(6, 6, 'thanh_vien'),
(6, 7, 'thanh_vien'),
(6, 9, 'thanh_vien'),
(9, 2, 'truong_nhom'),
(9, 3, 'thanh_vien');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thong_bao`
--

CREATE TABLE `thong_bao` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sinh_vien_id` bigint(20) UNSIGNED NOT NULL,
  `tieu_de` varchar(150) NOT NULL,
  `noi_dung` text NOT NULL,
  `loai` enum('hoc_tap','bai_viet') NOT NULL DEFAULT 'hoc_tap',
  `da_doc` tinyint(1) DEFAULT 0,
  `id_lien_quan` bigint(20) UNSIGNED DEFAULT NULL,
  `loai_lien_quan` varchar(50) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thong_bao`
--

INSERT INTO `thong_bao` (`id`, `sinh_vien_id`, `tieu_de`, `noi_dung`, `loai`, `da_doc`, `id_lien_quan`, `loai_lien_quan`, `ngay_tao`) VALUES
(1, 2, 'Có bình luận mới', 'Bạn Nguyễn Văn A đã bình luận bài viết của bạn', 'bai_viet', 1, 1, 'bai_viet', '2025-11-23 14:08:32'),
(2, 4, 'Công việc nhóm được giao', 'Bạn được giao nhiệm vụ \"Thiết kế giao diện\" - Nhóm 1 Đồ án Web', 'hoc_tap', 0, 1, 'cong_viec_nhom', '2025-11-23 14:08:32'),
(4, 6, 'Có người thích bài viết', 'Lê Thị C và 5 người khác đã thích bài viết của bạn', 'bai_viet', 0, 2, 'bai_viet', '2025-11-23 14:08:32'),
(5, 7, 'Công việc nhóm được giao', 'Bạn được giao nhiệm vụ \"Phân tích yêu cầu\" - Nhóm PTTKTT', 'hoc_tap', 0, 4, 'cong_viec_nhom', '2025-11-27 10:45:00'),
(6, 4, 'Có bình luận mới', 'Bạn Trần B đã bình luận bài viết \"Chia sẻ tài liệu môn Toán Rời Rạc\" của bạn', 'bai_viet', 0, 6, 'bai_viet', '2025-11-27 11:00:00'),
(7, 2, 'Nhiệm vụ nhóm mới được tạo', 'Công việc \"Prototype Mobile\" đã được thêm vào Nhóm UX/UI Design', 'hoc_tap', 1, 7, 'cong_viec_nhom', '2025-11-27 11:15:00'),
(8, 5, 'Bài viết của bạn được thích', 'Nguyễn Văn A và 2 người khác đã thích bài viết \"Tìm mua sách...\" của bạn', 'bai_viet', 1, 7, 'bai_viet', '2025-11-27 11:30:00'),
(10, 4, 'Bài viết bị ẩn', 'Bài viết \"Tuyển CTV Marketing online\" của bạn đã bị ẩn bởi quản trị viên.', 'bai_viet', 0, 11, 'bai_viet', '2025-11-28 05:23:08'),
(11, 4, 'Bài viết đã được hiện lại', 'Bài viết \"Tuyển CTV Marketing online\" đã được hiện lại.', 'bai_viet', 0, 11, 'bai_viet', '2025-11-28 05:26:23'),
(17, 6, 'Bạn được giao công việc mới', 'sv2021037 đã giao cho bạn công việc \"nộp đề cương\" trong nhóm \"Nhóm 1 - Đồ án Web\". Hạn: 30/11/2025', 'hoc_tap', 0, 9, 'cong_viec_nhom', '2025-11-28 17:30:06'),
(18, 2, 'Có công việc mới trong nhóm', 'Công việc \"nộp đề cương\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 1, 9, 'cong_viec_nhom', '2025-11-28 17:30:06'),
(19, 4, 'Có công việc mới trong nhóm', 'Công việc \"nộp đề cương\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 9, 'cong_viec_nhom', '2025-11-28 17:30:06'),
(20, 5, 'Có công việc mới trong nhóm', 'Công việc \"nộp đề cương\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 1, 9, 'cong_viec_nhom', '2025-11-28 17:30:06'),
(21, 7, 'Có công việc mới trong nhóm', 'Công việc \"nộp đề cương\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 9, 'cong_viec_nhom', '2025-11-28 17:30:06'),
(23, 2, 'Bài viết mới của bạn', 'Bạn vừa đăng bài: \"Bán lap\"', '', 1, 13, 'bai_viet', '2025-12-02 05:19:53'),
(24, 2, 'Bài viết được duyệt', 'Bài viết \"Bán lap\" của bạn đã được duyệt và hiển thị công khai.', 'bai_viet', 1, 13, 'bai_viet', '2025-12-02 05:20:40'),
(25, 4, 'Có công việc mới trong nhóm', 'Công việc \"nộp slide\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 10, 'cong_viec_nhom', '2025-12-02 05:25:07'),
(26, 5, 'Có công việc mới trong nhóm', 'Công việc \"nộp slide\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 10, 'cong_viec_nhom', '2025-12-02 05:25:07'),
(27, 6, 'Có công việc mới trong nhóm', 'Công việc \"nộp slide\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 10, 'cong_viec_nhom', '2025-12-02 05:25:07'),
(28, 7, 'Có công việc mới trong nhóm', 'Công việc \"nộp slide\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 10, 'cong_viec_nhom', '2025-12-02 05:25:07'),
(29, 7, 'Có người thích bài viết của bạn', 'sv2020001 đã thích bài viết: \"Thắc mắc về API ReactJS\"', '', 0, 8, 'bai_viet', '2025-12-02 05:26:50'),
(30, 2, 'Bài viết mới của bạn', 'Bạn vừa đăng bài: \"ffd\"', '', 1, 14, 'bai_viet', '2025-12-02 05:27:24'),
(31, 3, 'Bài viết bị xóa', 'Bài viết \"Tìm phòng trọ gần trường\" của bạn đã bị xóa vĩnh viễn bởi quản trị viên.', 'bai_viet', 0, 9, 'bai_viet', '2025-12-02 05:36:29'),
(32, 6, 'Bài viết bị ẩn', 'Bài viết \"Mất ví ở ký túc xá khu B\" của bạn đã bị ẩn bởi quản trị viên.', 'bai_viet', 0, 4, 'bai_viet', '2025-12-02 05:36:37'),
(33, 6, 'Bài viết bị ẩn', 'Bài viết \"Mất ví ở ký túc xá khu B\" của bạn đã bị ẩn bởi quản trị viên.', 'bai_viet', 0, 4, 'bai_viet', '2025-12-02 05:37:01'),
(34, 6, 'Bài viết đã được hiện lại', 'Bài viết \"Mất ví ở ký túc xá khu B\" của bạn đã được khôi phục và hiển thị lại.', 'bai_viet', 0, 4, 'bai_viet', '2025-12-02 05:37:37'),
(35, 2, 'Bài viết bị ẩn', 'Bài viết \"Bán lap\" của bạn đã bị ẩn bởi quản trị viên.', 'bai_viet', 0, 13, 'bai_viet', '2025-12-02 05:38:08'),
(36, 4, 'Đánh giá đã được hiện lại', 'Đánh giá của bạn về môn \"Phân tích và Thiết kế thuật toán\" đã được khôi phục và hiển thị lại.', 'bai_viet', 0, 9, 'danh_gia', '2025-12-02 05:44:40'),
(37, 2, 'Đánh giá đã được hiện lại', 'Đánh giá của bạn về môn \"Lập trình Web\" đã được khôi phục và hiển thị lại.', 'bai_viet', 0, 1, 'danh_gia', '2025-12-02 05:45:40'),
(38, 4, 'Đánh giá bị ẩn', 'Đánh giá của bạn về môn \"Lập trình Web\" đã bị ẩn bởi quản trị viên.', 'bai_viet', 0, 2, 'danh_gia', '2025-12-02 05:45:49'),
(39, 4, 'Đánh giá bị xóa', 'Đánh giá của bạn về môn \"Lập trình Web\" đã bị xóa vĩnh viễn bởi quản trị viên.', 'bai_viet', 0, 2, 'danh_gia', '2025-12-02 05:45:57'),
(40, 2, 'Bài viết được duyệt', 'Bài viết \"ffd\" của bạn đã được duyệt và hiển thị công khai.', 'bai_viet', 0, 14, 'bai_viet', '2025-12-02 05:46:56'),
(41, 2, 'Bài viết bị ẩn', 'Bài viết \"Ai có tài liệu ôn thi CSDL không?\" của bạn đã bị ẩn bởi quản trị viên.', 'bai_viet', 1, 1, 'bai_viet', '2025-12-02 05:46:59'),
(42, 6, 'Bài viết bị ẩn', 'Bài viết \"Mất ví ở ký túc xá khu B\" của bạn đã bị ẩn bởi quản trị viên.', 'bai_viet', 0, 4, 'bai_viet', '2025-12-02 05:47:04'),
(43, 4, 'Có công việc mới trong nhóm', 'Công việc \"nộp đề cương\" đã được thêm vào nhóm \"Nhóm tự do trí tuệ nhân tạo\"', 'hoc_tap', 0, 11, 'cong_viec_nhom', '2025-12-02 05:56:47'),
(44, 2, 'Có công việc mới trong nhóm', 'Công việc \"nộp đề cương\" đã được thêm vào nhóm \"Nhóm tự do trí tuệ nhân tạo\"', 'hoc_tap', 1, 11, 'cong_viec_nhom', '2025-12-02 05:56:47'),
(45, 2, 'Bài viết bị ẩn', 'Bài viết \"ffd\" của bạn đã bị ẩn bởi quản trị viên.', 'bai_viet', 0, 14, 'bai_viet', '2025-12-03 01:36:36'),
(46, 2, 'Bài viết đã được hiện lại', 'Bài viết \"ffd\" của bạn đã được khôi phục và hiển thị lại.', 'bai_viet', 1, 14, 'bai_viet', '2025-12-03 01:36:37'),
(47, 2, 'Đánh giá được duyệt', 'Đánh giá của bạn về môn \"Lập trình Web\" đã được duyệt và hiển thị công khai.', 'bai_viet', 0, 1, 'danh_gia', '2025-12-03 01:38:10'),
(50, 2, 'Đánh giá bị ẩn', 'Đánh giá của bạn về môn \"Lập trình Web\" đã bị ẩn bởi quản trị viên.', 'bai_viet', 0, 1, 'danh_gia', '2025-12-03 08:31:36'),
(51, 2, 'Đánh giá đã được hiện lại', 'Đánh giá của bạn về môn \"Lập trình Web\" đã được khôi phục và hiển thị lại.', 'bai_viet', 0, 1, 'danh_gia', '2025-12-03 08:31:38'),
(52, 2, 'Đánh giá bị ẩn', 'Đánh giá của bạn về môn \"Lập trình Web\" đã bị ẩn bởi quản trị viên.', 'bai_viet', 0, 1, 'danh_gia', '2025-12-03 08:31:41'),
(53, 2, 'Đánh giá đã được hiện lại', 'Đánh giá của bạn về môn \"Lập trình Web\" đã được khôi phục và hiển thị lại.', 'bai_viet', 1, 1, 'danh_gia', '2025-12-03 08:31:42'),
(54, 6, 'Đánh giá bị ẩn', 'Đánh giá của bạn về môn \"Nhập môn trí tuệ nhân tạo\" đã bị ẩn bởi quản trị viên.', 'bai_viet', 0, 4, 'danh_gia', '2025-12-03 08:31:44'),
(55, 6, 'Đánh giá đã được hiện lại', 'Đánh giá của bạn về môn \"Nhập môn trí tuệ nhân tạo\" đã được khôi phục và hiển thị lại.', 'bai_viet', 0, 4, 'danh_gia', '2025-12-03 08:31:45'),
(56, 2, 'Đánh giá được duyệt', 'Đánh giá của bạn về môn \"Hệ điều hành\" đã được duyệt và hiển thị công khai.', 'bai_viet', 0, 13, 'danh_gia', '2025-12-03 08:31:50'),
(57, 2, 'Đánh giá bị ẩn', 'Đánh giá của bạn về môn \"Hệ điều hành\" đã bị ẩn bởi quản trị viên.', 'bai_viet', 1, 13, 'danh_gia', '2025-12-03 08:31:51'),
(58, 2, 'Đánh giá được duyệt', 'Đánh giá của bạn về môn \"Cơ sở dữ liệu\" đã được duyệt và hiển thị công khai.', 'bai_viet', 1, 12, 'danh_gia', '2025-12-03 08:35:58'),
(59, 5, 'Đánh giá bị ẩn', 'Đánh giá của bạn về môn \"Cơ sở dữ liệu\" đã bị ẩn bởi quản trị viên.', 'bai_viet', 0, 5, 'danh_gia', '2025-12-03 08:36:00'),
(60, 6, 'Đánh giá bị ẩn', 'Đánh giá của bạn về môn \"Nhập môn trí tuệ nhân tạo\" đã bị ẩn bởi quản trị viên.', 'bai_viet', 0, 4, 'danh_gia', '2025-12-03 08:36:02'),
(61, 6, 'Đánh giá đã được hiện lại', 'Đánh giá của bạn về môn \"Nhập môn trí tuệ nhân tạo\" đã được khôi phục và hiển thị lại.', 'bai_viet', 0, 4, 'danh_gia', '2025-12-03 08:36:03'),
(62, 5, 'Có người thích bài viết của bạn', 'sv2020001 đã thích bài viết: \"Tìm mua sách giáo trình CSDL\"', '', 0, 7, 'bai_viet', '2025-12-03 08:37:57'),
(63, 4, 'Có công việc mới trong nhóm', 'Công việc \"nộp đề cương\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 12, 'cong_viec_nhom', '2025-12-03 08:45:54'),
(64, 5, 'Có công việc mới trong nhóm', 'Công việc \"nộp đề cương\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 12, 'cong_viec_nhom', '2025-12-03 08:45:54'),
(65, 6, 'Có công việc mới trong nhóm', 'Công việc \"nộp đề cương\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 12, 'cong_viec_nhom', '2025-12-03 08:45:54'),
(66, 7, 'Có công việc mới trong nhóm', 'Công việc \"nộp đề cương\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 12, 'cong_viec_nhom', '2025-12-03 08:45:54'),
(67, 4, 'Có công việc mới trong nhóm', 'Công việc \"nộp slide\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 13, 'cong_viec_nhom', '2025-12-03 08:46:33'),
(68, 5, 'Có công việc mới trong nhóm', 'Công việc \"nộp slide\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 13, 'cong_viec_nhom', '2025-12-03 08:46:33'),
(69, 6, 'Có công việc mới trong nhóm', 'Công việc \"nộp slide\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 13, 'cong_viec_nhom', '2025-12-03 08:46:33'),
(70, 7, 'Có công việc mới trong nhóm', 'Công việc \"nộp slide\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 13, 'cong_viec_nhom', '2025-12-03 08:46:33'),
(71, 4, 'Có công việc mới trong nhóm', 'Công việc \"nộp slide\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 14, 'cong_viec_nhom', '2025-12-03 08:47:49'),
(72, 5, 'Có công việc mới trong nhóm', 'Công việc \"nộp slide\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 14, 'cong_viec_nhom', '2025-12-03 08:47:49'),
(73, 6, 'Có công việc mới trong nhóm', 'Công việc \"nộp slide\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 14, 'cong_viec_nhom', '2025-12-03 08:47:49'),
(74, 7, 'Có công việc mới trong nhóm', 'Công việc \"nộp slide\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 14, 'cong_viec_nhom', '2025-12-03 08:47:49'),
(75, 6, 'Bạn đã được thêm vào nhóm học tập', 'Bạn đã được thêm vào nhóm \"Nhóm UX/UI Design\" bởi sv2020001', 'hoc_tap', 0, 5, 'nhom_hoc_tap', '2025-12-03 08:54:04'),
(76, 6, 'Bạn đã được thêm vào nhóm học tập', 'Bạn đã được thêm vào nhóm \"Nhóm tự do trí tuệ nhân tạo\" bởi sv2020001', 'hoc_tap', 0, 3, 'nhom_hoc_tap', '2025-12-03 08:54:37'),
(77, 2, 'Bài viết bị ẩn', 'Bài viết \"ffd\" của bạn đã bị ẩn bởi quản trị viên.', 'bai_viet', 0, 14, 'bai_viet', '2025-12-03 09:46:32'),
(78, 2, 'Bài viết đã được hiện lại', 'Bài viết \"ffd\" của bạn đã được khôi phục và hiển thị lại.', 'bai_viet', 0, 14, 'bai_viet', '2025-12-03 09:46:33'),
(79, 2, 'Bài viết mới của bạn', 'Bạn vừa đăng bài: \"mất đồ\"', '', 0, 15, 'bai_viet', '2025-12-03 14:26:14'),
(80, 2, 'Bài viết được duyệt', 'Bài viết \"mất đồ\" của bạn đã được duyệt và hiển thị công khai.', 'bai_viet', 0, 15, 'bai_viet', '2025-12-03 14:26:33'),
(81, 2, 'Bài viết mới của bạn', 'Bạn vừa đăng bài: \"dddddddddd\"', '', 0, 16, 'bai_viet', '2025-12-03 14:30:26'),
(85, 2, 'Bài viết mới của bạn', 'Bạn vừa đăng bài: \"1111111111111111111111\"', '', 0, 20, 'bai_viet', '2025-12-03 14:36:45'),
(87, 2, 'Bài viết mới của bạn', 'Bạn vừa đăng bài: \"222222222222222\"', '', 0, 22, 'bai_viet', '2025-12-03 14:39:15'),
(92, 2, 'Bài viết mới của bạn', 'Bạn vừa đăng bài: \"4444444444444444\"', '', 0, 27, 'bai_viet', '2025-12-03 14:40:38'),
(93, 2, 'Bài viết mới của bạn', 'Bạn vừa đăng bài: \"llllllllllllllllllllllll\"', '', 0, 28, 'bai_viet', '2025-12-03 14:52:33'),
(99, 2, 'Bài viết mới của bạn', 'Bạn vừa đăng bài: \"chaof\"', '', 0, 34, 'bai_viet', '2025-12-03 15:06:45'),
(102, 6, 'Có công việc mới trong nhóm', 'Công việc \"nộp đề cương\" đã được thêm vào nhóm \"Nhóm 2 - Cơ sở dữ liệu\"', 'hoc_tap', 0, 15, 'cong_viec_nhom', '2025-12-03 15:08:03'),
(103, 2, 'Bài viết mới của bạn', 'Bạn vừa đăng bài: \"mua\"', '', 0, 36, 'bai_viet', '2025-12-03 15:12:07'),
(104, 2, 'Bài viết được duyệt', 'Bài viết \"mua\" của bạn đã được duyệt và hiển thị công khai.', 'bai_viet', 0, 36, 'bai_viet', '2025-12-03 15:13:01'),
(105, 4, 'Có công việc mới trong nhóm', 'Công việc \"nộp slide\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 16, 'cong_viec_nhom', '2025-12-03 15:17:57'),
(106, 5, 'Có công việc mới trong nhóm', 'Công việc \"nộp slide\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 16, 'cong_viec_nhom', '2025-12-03 15:17:57'),
(107, 6, 'Có công việc mới trong nhóm', 'Công việc \"nộp slide\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 16, 'cong_viec_nhom', '2025-12-03 15:17:57'),
(108, 7, 'Có công việc mới trong nhóm', 'Công việc \"nộp slide\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 16, 'cong_viec_nhom', '2025-12-03 15:17:57'),
(109, 3, 'Có công việc mới trong nhóm', 'Công việc \"l\" đã được thêm vào nhóm \"Nhóm 2 - Cơ sở dữ liệu\"', 'hoc_tap', 0, 17, 'cong_viec_nhom', '2025-12-03 15:20:51'),
(110, 6, 'Có công việc mới trong nhóm', 'Công việc \"l\" đã được thêm vào nhóm \"Nhóm 2 - Cơ sở dữ liệu\"', 'hoc_tap', 0, 17, 'cong_viec_nhom', '2025-12-03 15:20:51'),
(111, 4, 'Có công việc mới trong nhóm', 'Công việc \"nộp đề cương\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 18, 'cong_viec_nhom', '2025-12-03 15:25:48'),
(112, 5, 'Có công việc mới trong nhóm', 'Công việc \"nộp đề cương\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 18, 'cong_viec_nhom', '2025-12-03 15:25:48'),
(113, 6, 'Có công việc mới trong nhóm', 'Công việc \"nộp đề cương\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 18, 'cong_viec_nhom', '2025-12-03 15:25:48'),
(114, 7, 'Có công việc mới trong nhóm', 'Công việc \"nộp đề cương\" đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\"', 'hoc_tap', 0, 18, 'cong_viec_nhom', '2025-12-03 15:25:48'),
(115, 4, 'Có công việc mới trong nhóm', 'Công việc \"nộp slide\" đã được thêm vào nhóm \"Nhóm tự do trí tuệ nhân tạo\"', 'hoc_tap', 0, 19, 'cong_viec_nhom', '2025-12-03 21:59:31'),
(116, 6, 'Có công việc mới trong nhóm', 'Công việc \"nộp slide\" đã được thêm vào nhóm \"Nhóm tự do trí tuệ nhân tạo\"', 'hoc_tap', 0, 19, 'cong_viec_nhom', '2025-12-03 21:59:31'),
(117, 9, 'Có công việc mới trong nhóm', 'Công việc \"nộp slide\" đã được thêm vào nhóm \"Nhóm tự do trí tuệ nhân tạo\"', 'hoc_tap', 0, 19, 'cong_viec_nhom', '2025-12-03 21:59:31'),
(118, 2, 'Bạn được giao công việc mới', 'sv2020001 đã giao bạn công việc \"nộp\" trong nhóm \"Nhóm tự do trí tuệ nhân tạo\".\nHạn hoàn thành: 12/12/2025', 'hoc_tap', 1, 20, 'cong_viec_nhom', '2025-12-03 22:03:52'),
(119, 2, 'Công việc mới: nộp slide đề cương', 'Nhóm \"Nhóm tự do trí tuệ nhân tạo\"\nHạn nộp: 19/12/2025', 'hoc_tap', 0, 21, 'cong_viec_nhom', '2025-12-03 22:05:01'),
(120, 11, 'Bạn đã được thêm vào nhóm học tập', 'Bạn đã được thêm vào nhóm \"Nhóm 2 - Cơ sở dữ liệu\" bởi sv2020001', 'hoc_tap', 0, 2, 'nhom_hoc_tap', '2025-12-03 22:06:35'),
(121, 3, 'Bạn đã được thêm vào nhóm học tập', 'Bạn đã được thêm vào nhóm \"Nhom\" bởi sv2020001', 'hoc_tap', 0, 9, 'nhom_hoc_tap', '2025-12-04 06:33:09'),
(122, 3, 'Công việc mới: nộp đề cương', 'Nhóm \"Nhom\"\nHạn nộp: 11/12/2025', 'hoc_tap', 1, 22, 'cong_viec_nhom', '2025-12-04 06:33:38'),
(123, 2, 'Đánh giá được duyệt', 'Đánh giá của bạn về môn \"Mạng máy tính\" đã được duyệt và hiển thị công khai.', 'bai_viet', 0, 15, 'danh_gia', '2025-12-13 17:30:46'),
(124, 2, 'Bài viết đã được hiện lại', 'Bài viết \"Ai có tài liệu ôn thi CSDL không?\" của bạn đã được khôi phục và hiển thị lại.', 'bai_viet', 1, 1, 'bai_viet', '2025-12-14 01:57:05'),
(125, 2, 'Bài viết bị ẩn', 'Bài viết \"mua\" của bạn đã bị ẩn bởi quản trị viên.', 'bai_viet', 0, 36, 'bai_viet', '2025-12-14 02:00:11'),
(126, 2, 'Bài viết đã được hiện lại', 'Bài viết \"mua\" của bạn đã được khôi phục và hiển thị lại.', 'bai_viet', 0, 36, 'bai_viet', '2025-12-14 02:00:13'),
(127, 2, 'Bài viết bị xóa', 'Bài viết \"llllllllllllllllllllllll\" của bạn đã bị xóa vĩnh viễn bởi quản trị viên.', 'bai_viet', 0, 33, 'bai_viet', '2025-12-14 02:00:22'),
(128, 2, 'Đánh giá bị ẩn', 'Đánh giá của bạn về môn \"Lập trình Web\" đã bị ẩn bởi quản trị viên.', 'bai_viet', 0, 1, 'danh_gia', '2025-12-14 03:36:37'),
(129, 2, 'Đánh giá được duyệt', 'Đánh giá của bạn về môn \"Kỹ thuật lập trình\" đã được duyệt và hiển thị công khai.', 'bai_viet', 0, 14, 'danh_gia', '2025-12-14 03:36:48'),
(130, 2, 'Bài viết mới của bạn', 'Bạn vừa đăng bài: \"Phân tích thiết kế hệ thống slide\"', '', 0, 37, 'bai_viet', '2025-12-14 03:57:24'),
(131, 2, 'Bài viết được duyệt', 'Bài viết \"Phân tích thiết kế hệ thống slide\" của bạn đã được duyệt và hiển thị công khai.', 'bai_viet', 1, 37, 'bai_viet', '2025-12-14 03:57:38'),
(132, 18, 'Bạn đã được thêm vào nhóm học tập', 'Bạn đã được thêm vào nhóm \"Nhóm 1 - Đồ án Web\" bởi sv2020001', 'hoc_tap', 0, 1, 'nhom_hoc_tap', '2026-01-08 14:55:01'),
(133, 18, 'Công việc mới: hahha', 'Nhóm \"Nhóm 1 - Đồ án Web\"\nHạn nộp: 30/01/2026', 'hoc_tap', 0, 23, 'cong_viec_nhom', '2026-01-08 14:57:49');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bai_viet_dien_dan`
--
ALTER TABLE `bai_viet_dien_dan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tac_gia_id` (`tac_gia_id`);

--
-- Chỉ mục cho bảng `binh_luan_dien_dan`
--
ALTER TABLE `binh_luan_dien_dan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bai_viet_id` (`bai_viet_id`),
  ADD KEY `tac_gia_id` (`tac_gia_id`);

--
-- Chỉ mục cho bảng `cong_viec_nhom`
--
ALTER TABLE `cong_viec_nhom`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nhom_id` (`nhom_id`),
  ADD KEY `nguoi_duoc_giao` (`nguoi_duoc_giao`);

--
-- Chỉ mục cho bảng `danh_gia_mon_giang_vien`
--
ALTER TABLE `danh_gia_mon_giang_vien`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mon_hoc_id` (`mon_hoc_id`),
  ADD KEY `sinh_vien_id` (`sinh_vien_id`);

--
-- Chỉ mục cho bảng `hoc_ky`
--
ALTER TABLE `hoc_ky`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `lich_ca_nhan`
--
ALTER TABLE `lich_ca_nhan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sinh_vien_id` (`sinh_vien_id`),
  ADD KEY `mon_hoc_id` (`mon_hoc_id`),
  ADD KEY `sk_id` (`sk_id`);

--
-- Chỉ mục cho bảng `luot_thich_bai_viet`
--
ALTER TABLE `luot_thich_bai_viet`
  ADD PRIMARY KEY (`bai_viet_id`,`sinh_vien_id`),
  ADD KEY `sinh_vien_id` (`sinh_vien_id`);

--
-- Chỉ mục cho bảng `mon_hoc`
--
ALTER TABLE `mon_hoc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hoc_ky_id` (`hoc_ky_id`);

--
-- Chỉ mục cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `nhom_hoc_tap`
--
ALTER TABLE `nhom_hoc_tap`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mon_hoc_id` (`mon_hoc_id`);

--
-- Chỉ mục cho bảng `sk_ca_nhan`
--
ALTER TABLE `sk_ca_nhan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sinh_vien_id` (`sinh_vien_id`);

--
-- Chỉ mục cho bảng `thanh_vien_nhom`
--
ALTER TABLE `thanh_vien_nhom`
  ADD PRIMARY KEY (`nhom_id`,`sinh_vien_id`),
  ADD KEY `sinh_vien_id` (`sinh_vien_id`);

--
-- Chỉ mục cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sinh_vien_id` (`sinh_vien_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bai_viet_dien_dan`
--
ALTER TABLE `bai_viet_dien_dan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT cho bảng `binh_luan_dien_dan`
--
ALTER TABLE `binh_luan_dien_dan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `cong_viec_nhom`
--
ALTER TABLE `cong_viec_nhom`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `danh_gia_mon_giang_vien`
--
ALTER TABLE `danh_gia_mon_giang_vien`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `hoc_ky`
--
ALTER TABLE `hoc_ky`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `lich_ca_nhan`
--
ALTER TABLE `lich_ca_nhan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT cho bảng `mon_hoc`
--
ALTER TABLE `mon_hoc`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `nhom_hoc_tap`
--
ALTER TABLE `nhom_hoc_tap`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `sk_ca_nhan`
--
ALTER TABLE `sk_ca_nhan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bai_viet_dien_dan`
--
ALTER TABLE `bai_viet_dien_dan`
  ADD CONSTRAINT `bai_viet_dien_dan_ibfk_1` FOREIGN KEY (`tac_gia_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `binh_luan_dien_dan`
--
ALTER TABLE `binh_luan_dien_dan`
  ADD CONSTRAINT `binh_luan_dien_dan_ibfk_1` FOREIGN KEY (`bai_viet_id`) REFERENCES `bai_viet_dien_dan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `binh_luan_dien_dan_ibfk_2` FOREIGN KEY (`tac_gia_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `cong_viec_nhom`
--
ALTER TABLE `cong_viec_nhom`
  ADD CONSTRAINT `cong_viec_nhom_ibfk_1` FOREIGN KEY (`nhom_id`) REFERENCES `nhom_hoc_tap` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cong_viec_nhom_ibfk_2` FOREIGN KEY (`nguoi_duoc_giao`) REFERENCES `nguoi_dung` (`id`);

--
-- Các ràng buộc cho bảng `danh_gia_mon_giang_vien`
--
ALTER TABLE `danh_gia_mon_giang_vien`
  ADD CONSTRAINT `danh_gia_mon_giang_vien_ibfk_1` FOREIGN KEY (`mon_hoc_id`) REFERENCES `mon_hoc` (`id`),
  ADD CONSTRAINT `danh_gia_mon_giang_vien_ibfk_2` FOREIGN KEY (`sinh_vien_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `lich_ca_nhan`
--
ALTER TABLE `lich_ca_nhan`
  ADD CONSTRAINT `lich_ca_nhan_ibfk_1` FOREIGN KEY (`sinh_vien_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lich_ca_nhan_ibfk_2` FOREIGN KEY (`mon_hoc_id`) REFERENCES `mon_hoc` (`id`),
  ADD CONSTRAINT `lich_ca_nhan_ibfk_3` FOREIGN KEY (`sk_id`) REFERENCES `sk_ca_nhan` (`id`);

--
-- Các ràng buộc cho bảng `luot_thich_bai_viet`
--
ALTER TABLE `luot_thich_bai_viet`
  ADD CONSTRAINT `luot_thich_bai_viet_ibfk_1` FOREIGN KEY (`bai_viet_id`) REFERENCES `bai_viet_dien_dan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `luot_thich_bai_viet_ibfk_2` FOREIGN KEY (`sinh_vien_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `mon_hoc`
--
ALTER TABLE `mon_hoc`
  ADD CONSTRAINT `mon_hoc_ibfk_1` FOREIGN KEY (`hoc_ky_id`) REFERENCES `hoc_ky` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `nhom_hoc_tap`
--
ALTER TABLE `nhom_hoc_tap`
  ADD CONSTRAINT `nhom_hoc_tap_ibfk_1` FOREIGN KEY (`mon_hoc_id`) REFERENCES `mon_hoc` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `sk_ca_nhan`
--
ALTER TABLE `sk_ca_nhan`
  ADD CONSTRAINT `sk_ca_nhan_ibfk_1` FOREIGN KEY (`sinh_vien_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `thanh_vien_nhom`
--
ALTER TABLE `thanh_vien_nhom`
  ADD CONSTRAINT `thanh_vien_nhom_ibfk_1` FOREIGN KEY (`nhom_id`) REFERENCES `nhom_hoc_tap` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `thanh_vien_nhom_ibfk_2` FOREIGN KEY (`sinh_vien_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  ADD CONSTRAINT `thong_bao_ibfk_1` FOREIGN KEY (`sinh_vien_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
