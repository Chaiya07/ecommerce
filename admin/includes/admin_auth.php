<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo '
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>403 Forbidden</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container" mt-5>
            <div class="alert alert-danger">
                <h3>403 Forbidden</h3>
                <hr>
                <p>คุณไม่มีสิทธิเข้าใช้งานหน้านี้ หึหึหึ</p>
                <a href="../index.php" class="btn btn=primary">กลับหน้าหลัก</a>
            </div>
        </div>
    </body>
    </html>
    ';
    exit();
}