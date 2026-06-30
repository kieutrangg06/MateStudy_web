<?php http_response_code(404);
require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Không tìm thấy trang • MATESTUDY</title>
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../css/404.css" rel="stylesheet">
</head>

<body>

    <div class="floating-icons" aria-hidden="true">
        <i class="bi bi-book floating-icon" style="left:10%; animation-delay:0s;"></i>
        <i class="bi bi-pencil floating-icon" style="left:25%; animation-delay:4s;"></i>
        <i class="bi bi-lightbulb floating-icon" style="left:40%; animation-delay:8s;"></i>
        <i class="bi bi-mortarboard floating-icon" style="left:60%; animation-delay:2s;"></i>
        <i class="bi bi-journal-text floating-icon" style="left:80%; animation-delay:6s;"></i>
        <i class="bi bi-book floating-icon" style="left:90%; animation-delay:10s;"></i>
    </div>

    <div class="error-container">
        <div class="logo">
            <i class="bi bi-book-half"></i> MATESTUDY
        </div>

        <div class="error-code-wrapper">
            <i class="bi bi-book book-icon"></i>
            <div class="error-code">404</div>
            <i class="bi bi-journal-bookmark book-icon"></i>
        </div>

        <h1 class="error-title">Oops! Trang không tồn tại</h1>
        <p class="error-message">Có vẻ như bạn đã lạc đường trong hành trình học tập rồi!</p>
        <p class="error-submessage">Trang bạn đang tìm có thể đã bị xóa hoặc đường dẫn không đúng.</p>

        <a href="/MateStudy/php/home.php" class="btn-home">
            <i class="bi bi-house-door-fill"></i> Về trang chủ
        </a>
    </div>
</body>

</html>