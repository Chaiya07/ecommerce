<?php
if (session_status()=== PHP_SESSION_NONE){
    session_start();
}
require_once __DIR__ .'/../config/database.php';
$cartCount = 0;
if(!empty($_SESSION['cart'])){
    foreach ($_SESSION['cart'] as $item){
        $cartCount += $item['qty'];
    }
}
?>
<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>chaiya E-commerce</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <nav  class="navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                  <a class="navbar-brand" href="index.php">ซีเค้ก</a>
                  <button class="navber-toggler" type="button"
                  data-bs-toggler="collapse" data-bs-traget="#navbarMenu">
                  <span class="navber-toggler-icon"></span>
                  </button>
                  <div class="collapse navbar-collapse" id="navbarMenu">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">หน้าหลัก</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php">สินค้า</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">ตระกร้า (<?= $cartCount ?>)</a>
                        </li>
                        <?php if (!empty($_SESSION['user'])) : ?>
                            <li class="nav-item">
                                <a class="nav-link" href="profile.php">โปรไฟล์</a>
                        </li>
                        <?php if ($_SESSION['user']['role'] === 'admin') : ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/dashboard.php">ADMIN</a>
                            </li>
                        <?php endif; ?>
                        li class="nav-item">
                            <a class="nav-link" href="logout.php">ออกจากระบบ</a>
                        </li>
                        <?php else : ?>
                            li class="nav-item">
                            <a class="nav-link" href="login.php">เข้าสู่ระบบ</a>
                         </li>
                        li class="nav-item">
                            <a class="nav-link" href="register.php">สมัครสมาชิก</a>
                        </li>
                    <?php endif; ?>
                    </ul>
                  </div>
            </div>
        </nav>  
        <div class="container mt-4">