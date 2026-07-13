<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/database.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location:../login.php');
    exit();
}
$storeName = "Chaiya E-commerce";
try {
$sql = $conn->query("SELECT * FROM settings LIMIT 1");
$setting = $sql->fetch(PDO::FETCH_ASSOC);
if ($setting && !empty($setting['store_name'])) {
    $storeName = $setting['store_name'];
    }
} catch (Exception $e) {
    'ครูบอกเว้นเอาไว้';
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>
            <?= htmlspecialchars($storeName) ?> | Admin Panel
        </title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
        <style>
            body {background: #f5f6fa; font-size: 15px;}
            .navbar {box-shadow: 0 2px 8px rgba(0, 0, 0, .08);}
            .navbar-brand{font-weight: bold;}
            .sidebar{min-height: 100vh; background: #212529;}
            .sidebar a {display: block;padding: 14px 20px; transition: .25s;
            color: #fff; text-decoration: none;
            display: block; padding: 12px 18px;}
            .sidebar a:hover {
            background: #0d6efd;
            color: #fff;
            padding-left: 28px;
            }
            .sidebar a.active {background-color: #0d6efd; font-weight: bold;}
            .content {padding: 25px;}
            .card {border: none; border-radius: 12px;}
            .table {background: #fff;}
        </style>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold" href="dashboard.php">
                    <i class="bi bi-shop"></i>
                    <?= htmlspecialchars($storeName) ?>Admin
                </a>
                <button class="navbar-toggler" type="button" 
                data-bs-toggle="collapse" data-bs-target="#navbarAdmin">
                <span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarAdmin">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <span class="nav-link">
                                <i class="bi bi-person-circle"></i>
                                <?= htmlspecialchars($_SESSION['user']['username']) ?>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../login.php">
                                <i class="bi bi-box-arrow-right"></i>ออกจากระบบ
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-2 col-md-3 p-0">
                    <?php include __DIR__ . '/sidebar.php'; ?>
                </div>
                <div class="col-lg-10 col-md-9">
                    <div class="content">