<?php
require_once '../config/database.php';
require_once 'includes/admin_auth.php';

$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($orderId <= 0) {
    header('Location: manage_orders.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentStatus  = $_POST['payment_status'];
    $shippingStatus = $_POST['shipping_status'];
    $sql = $conn->prepare("UPDATE orders SET payment_status = ?, shipping_status = ? WHERE id = ?");
    $sql->execute([$paymentStatus, $shippingStatus, $orderId]);
    header('Location: order_detail.php?id=' . $orderId);
    exit();
}

$sql = $conn->prepare("SELECT o.*, c.code AS coupon_code FROM orders o LEFT JOIN
coupons c ON o.coupon_id = c.id WHERE o.id = ?");
$sql->execute([$orderId]);
$order = $sql->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: manage_orders.php');
    exit();
}

$sql = $conn->prepare("SELECT oi.*, p.name, p.image FROM order_items oi LEFT JOIN
products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$sql->execute([$orderId]);
$items = $sql->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>คำสั่งซื้อ #<?= $order['id'] ?></h2>
        <a href="manage_orders.php" class="btn btn-secondary">กลับ</a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">รายการสินค้า</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="100">รูป</th>
                                    <th>สินค้า</th>
                                    <th width="120">ราคา</th>
                                    <th width="100">จำนวน</th>
                                    <th width="150">รวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item) : ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($item['image'])) : ?>
                                                <img src="../uploads/products/<?= htmlspecialchars($item['image']) ?>"
                                                    class="img-thumbnail"
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($item['name']) ?></td>
                                        <td><?= number_format($item['price'], 2) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td><?= number_format($item['subtotal'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">อัปเดตสถานะคำสั่งซื้อ</div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">สถานะการชำระเงิน</label>
                            <select name="payment_status" class="form-select">
                                <option value="pending" <?= $order['payment_status'] === 'pending' ? 'selected' : '' ?>>pending</option>
                                <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>paid</option>
                                <option value="cancelled" <?= $order['payment_status'] === 'cancelled' ? 'selected' : '' ?>>cancelled</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">สถานะการจัดส่ง</label>
                            <select name="shipping_status" class="form-select">
                                <option value="pending" <?= $order['shipping_status'] === 'pending' ? 'selected' : '' ?>>pending</option>
                                <option value="processing" <?= $order['shipping_status'] === 'processing' ? 'selected' : '' ?>>processing</option>
                                <option value="shipped" <?= $order['shipping_status'] === 'shipped' ? 'selected' : '' ?>>shipped</option>
                                <option value="delivered" <?= $order['shipping_status'] === 'delivered' ? 'selected' : '' ?>>delivered</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">บันทึกสถานะ</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">ข้อมูลคำสั่งซื้อ</div>
                <div class="card-body">
                    <p><strong>วันที่สั่งซื้อ :</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                    <p>ชื่อผู้รับ : <strong><?= htmlspecialchars($order['customer_name']) ?></strong></p>
                    <p>โทรศัพท์ : <strong><?= htmlspecialchars($order['customer_phone']) ?></strong></p>
                    <p>อีเมล : <strong><?= htmlspecialchars($order['customer_email']) ?></strong></p>
                    <p>ที่อยู่ : <strong><?= nl2br(htmlspecialchars($order['customer_address'])) ?></strong></p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">สถานะคำสั่งซื้อ</div>
                <div class="card-body">
                    <p><strong>วิธีการชำระ : </strong><?= htmlspecialchars($order['payment_method']) ?></p>
                    <p><strong>คูปอง : </strong><?= $order['coupon_code'] ?: '-' ?></p>
                    <p><strong>ส่วนลด :</strong> <?= number_format($order['discount'], 2) ?> บาท</p>
                    <p><strong>ยอดสุทธิ :</strong> <?= number_format($order['total_price'], 2) ?> บาท</p>

                    <p><strong>สถานะชำระเงิน</strong></p>
                    <?php
                    $paymentColor = match ($order['payment_status']) {
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'warning'
                    };
                    ?>
                    <span class="badge bg-<?= $paymentColor ?>">
                        <?= htmlspecialchars($order['payment_status']) ?>
                    </span>

                    <hr>
                    <p><strong>สถานะจัดส่ง</strong></p>
                    <?php
                    $shippingColor = match ($order['shipping_status']) {
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        default => 'secondary'
                    };  
                    ?>
                    <span class="badge bg-<?= $shippingColor ?>">
                        <?= htmlspecialchars($order['shipping_status']) ?>
                    </span>

                    <?php if (!empty($order['payment_slip'])) : ?>
                        <hr>
                        <p><strong>สลิปการโอนเงิน</strong></p>
                        <a href="../uploads/slips/<?= htmlspecialchars($order['payment_slip']) ?>" target="_blank">
                            <img src="../uploads/slips/<?= htmlspecialchars($order['payment_slip']) ?>"
                                class="img-fluid rounded border">
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>