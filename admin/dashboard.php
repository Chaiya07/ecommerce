<?php
require_once '../config/database.php';
require_once '../includes/admin_auth.php';

$sql = $conn->query("SELECT COUNT(*) AS total FROM users");
$totalUsers = $sql->fetch(PDO::FETCH_ASSOC)['total'];

$sql = $conn->query("SELECT COUNT(*) AS total FROM products");
$totalProducts = $sql->fetch(PDO::FETCH_ASSOC)['total'];

$sql = $conn->query("SELECT COUNT(*) AS total FROM orders");
$totalOrders = $sql->fetch(PDO::FETCH_ASSOC)['total'];

$sql = $conn->query("SELECT IFNULL(SUM(total_price), 0) AS total FROM orders WHERE payment_status = 'paid'");
$totalSales = $sql->fetch(PDO::FETCH_ASSOC)['total'];

$sql = $conn->query("SELECT id, customer_name, total_price, payment_status,
    shipping_status, create_at FROM orders ORDER BY id DESC LIMIT 10");
$orders = $sql->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>
<div class="container-fluid">
    <h2 class="mb-4">Dashboard</h2>
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h3><?= number_format($totalUsers) ?></h3>สมาชิก
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3><?= number_format($totalProducts) ?></h3>สินค้า
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3><?= number_format($totalOrders) ?></h3>คำสั่งซื้อ
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3><?= number_format($totalSales, 2) ?></h3>ยอดขาย (บาท)
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Order</th>
                            <th>ลูกค้า</th>
                            <th>ยอดรวม</th>
                            <th>ชำระเงิน</th>
                            <th>จัดส่ง</th>
                            <th>วันที่</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order) : ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= number_format($order['total_price'], 2) ?></td>
                                <td><?= htmlspecialchars($order['payment_status']) ?></td>
                                <td><?= htmlspecialchars($order['shipping_status']) ?></td>
                                <td><?= date("d/m/Y H:i", strtotime($order['create_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
