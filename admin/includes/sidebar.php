<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="text-center text-white py-4 border-bottom">
        <i class="bi bi-speedometer2 fs-1"></i>
        <h5 class="mt-2 mb-0">Admin Panel</h5>
    </div>

    <div class="mt-3">
        <a href="dashboard.php"
           class="<?= ($currentPage === 'dashboard.php') ? 'active' : '' ?>">
            <i class="bi bi-house-door-fill me-2"></i>Dashboard
        </a>

        <a href="manage_user.php"
           class="<?= ($currentPage === 'manage_user.php') ? 'active' : '' ?>">
            <i class="bi bi-people-fill me-2"></i>จัดการสมาชิก
        </a>

        <a href="manage_product.php"
           class="<?= ($currentPage === 'manage_product.php') ? 'active' : '' ?>">
            <i class="bi bi-box-seam me-2"></i>จัดการสินค้า
        </a>

        <a href="manage_orders.php"
           class="<?= ($currentPage === 'manage_orders.php') ? 'active' : '' ?>">
            <i class="bi bi-receipt-cutoff me-2"></i>จัดการคำสั่งซื้อ
        </a>

        <a href="manage_coupons.php"
           class="<?= ($currentPage === 'manage_coupons.php') ? 'active' : '' ?>">
            <i class="bi bi-ticket-perforated me-2"></i>จัดการคูปอง
        </a>

        <a href="reports.php"
           class="<?= ($currentPage === 'reports.php') ? 'active' : '' ?>">
            <i class="bi bi-bar-chart-fill me-2"></i>รายงานยอดขาย
        </a>

        <a href="settings.php"
           class="<?= ($currentPage === 'settings.php') ? 'active' : '' ?>">
            <i class="bi bi-gear-fill me-2"></i>ตั้งค่าร้านค้า
        </a>

        <hr class="border-light">

        <a href="../index.php">
            <i class="bi bi-shop me-2"></i>หน้าร้าน
        </a>

        <a href="../logout.php">
            <i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ
        </a>
    </div>
</div>