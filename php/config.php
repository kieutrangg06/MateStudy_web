<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    $conn = new mysqli('localhost', 'root', '', 'matestudy');

    if ($conn->connect_error) {
        throw new Exception("Lỗi kết nối database: " . $conn->connect_error);
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

define('ROLE_ADMIN', 'admin');
define('ROLE_STUDENT', 'sinh_vien');
define('ROLE_GUEST', 'guest');

// Kiểm tra quyền truy cập
function check_permission(array $allowed_roles): bool
{
    $current_role = $_SESSION['vai_tro'] ?? ROLE_GUEST;

    if (in_array($current_role, $allowed_roles)) {
        return true;
    }
    return false;
}

// Yêu cầu quyền truy cập (redirect nếu không đủ quyền)
function require_permission(array $allowed_roles, string $redirect_page = 'login.php')
{
    if (!check_permission($allowed_roles)) {
        header("Location: " . $redirect_page);
        exit();
    }
}
