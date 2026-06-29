<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
$userId = $_SESSION['user']['id'];
$sql = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$sql->execute([$userId]);
$orders = $sql->fetchAll(PDO::FETCH_ASSOC);
include 'includes/header.php';
?>
<div class="row">
    <div class="col-12">
        <h2 class="mb-4">ประวัติการสั่งซื้อ</h2>
    </div>
</div>
<?php if (count($orders) > 0) : ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th width="120">เลขที่ออเดอร์</th>
                    <th width="120">วันที่สั่งซื้อ</th>
                    <th width="120">ยอดรวม</th>
                    <th width="120">ชำระเงิน</th>
                    <th width="120">จัดส่ง</th>
                    <th width="120">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order) : ?>
                    <tr>
                        <td>#<?= $order['id'] ?></td>
                        <td><?= date('d/m/Y H:i',strtotime($order['created_at'])) ?></td>
                        <td><?= number_format($order['total_price'],2) ?> บาท</td>
                        <td><?php $paymentClass = 'bg-waring';
                            if ($order['payment_status'] === 'paid') {
                                $paymentClass = 'bg-success';
                            }
                            if ($order['payment_status'] === 'cancelled') {
                                $paymentClass = 'bg-danger';
                            }
                            ?>
                            <span class="badge <?= $paymentClass ?>">
                                <?= $order['payment_status'] ?>
                            </span>
                        </td>
                        <td><?php $shippingClass = 'bg-secondary';
                            if ($order['shipping_status'] === 'processing') {
                                $shippingClass = 'bg-info';
                            }
                            if ($order['shipping_status'] === 'shipped') {
                                $shippingClass = 'bg-primary';
                            }
                            if ($order['shipping_status'] === 'delivered') {
                                $shippingClass = 'bg-success';
                            }
                            ?>
                            <span class="badge <?= $shippingClass ?>">
                                <?= $order['shipping_status'] ?>
                            </span>
                        </td>
                        <td>
                            <a href="order_success.php?id<?= $order['id'] ?>"
                            class="btn btn-sm btn-primary">ดูรายละเอียด</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else : ?>
    <div class="alert alert-warning">ยังไม่มีรายการสั่งซื้อ</div>
    <a href="products.php" class="btn btn-primary">เลือกซื้อสินค้า</a>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>