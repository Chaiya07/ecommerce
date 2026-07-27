<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$userId  = $_SESSION['user']['id'];

if ($orderId <= 0) {
    header('Location: my_orders.php');
    exit;
}

$sql = $conn->prepare("SELECT o.* FROM orders o WHERE o.id = ? AND o.user_id = ?");
$sql->execute([$orderId, $userId]);
$order = $sql->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: my_orders.php');
    exit;
}

$sql = $conn->prepare("SELECT oi.*, p.name, p.image FROM order_items oi INNER JOIN products p
ON oi.product_id = p.id WHERE oi.order_id = ?");
$sql->execute([$orderId]);
$items = $sql->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="container py-4" data-aos="fade-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-receipt me-2"></i>รายละเอียดคำสั่งซื้อ #<?= $order['id'] ?></h2>
        <a href="my_orders.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> กลับ
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card order-card mb-4" data-aos="fade-up">
                <div class="card-header">
                    <i class="bi bi-bag-check me-1"></i>รายการสินค้า
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered order-table align-middle">
                            <thead class="table-dark">
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
                                                <img src="uploads/products/<?= htmlspecialchars($item['image']) ?>"
                                                    class="img-thumbnail"
                                                    style="width: 70px; height: 70px; object-fit: cover;">
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($item['name']) ?></td>
                                        <td><?= number_format($item['price'], 2) ?> บาท</td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td class="fw-bold"><?= number_format($item['subtotal'], 2) ?> บาท</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card order-card mb-4" data-aos="fade-up" data-aos-delay="80">
                <div class="card-header">
                    <i class="bi bi-info-circle me-1"></i>ข้อมูลการสั่งซื้อ
                </div>
                <div class="card-body">
                    <p><strong>วันที่สั่งซื้อ :</strong><br><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                    <p><strong>โทรศัพท์ :</strong><br><?= htmlspecialchars($order['customer_phone']) ?></p>
                    <p class="mb-0"><strong>ที่อยู่ :</strong><br><?= nl2br(htmlspecialchars($order['customer_address'])) ?></p>
                </div>
            </div>

            <div class="card order-card mb-4" data-aos="fade-up" data-aos-delay="140">
                <div class="card-header">
                    <i class="bi bi-truck me-1"></i>สถานะการสั่งซื้อ
                </div>
                <div class="card-body">
                    <p><strong>ยอดรวม :</strong> <span class="fw-bold text-primary"><?= number_format($order['total_price'], 2) ?> บาท</span></p>

                    <p class="mb-1"><strong>การชำระเงิน :</strong></p>
                    <?php
                    $paymentText = ['pending' => 'รอตรวจสอบ', 'paid' => 'ชำระแล้ว', 'cancelled' => 'ยกเลิก'];
                    $paymentColor = ['pending' => 'warning', 'paid' => 'success', 'cancelled' => 'danger'];
                    ?>
                    <span class="badge bg-<?= $paymentColor[$order['payment_status']] ?? 'secondary' ?> badge-status">
                        <?= $paymentText[$order['payment_status']] ?? $order['payment_status'] ?>
                    </span>

                    <hr>

                    <p class="mb-1"><strong>การจัดส่ง :</strong></p>
                    <?php
                    $shippingText  = ['pending' => 'รอดำเนินการ', 'processing' => 'กำลังเตรียมสินค้า', 'shipped' => 'จัดส่งแล้ว', 'delivered' => 'จัดส่งสำเร็จ'];
                    $shippingColor = ['pending' => 'secondary', 'processing' => 'info', 'shipped' => 'primary', 'delivered' => 'success'];
                    ?>
                    <span class="badge bg-<?= $shippingColor[$order['shipping_status']] ?? 'secondary' ?> badge-status">
                        <?= $shippingText[$order['shipping_status']] ?? $order['shipping_status'] ?>
                    </span>
                </div>
            </div>

            <?php if (!empty($order['payment_slip'])) : ?>
                <div class="card order-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-header">
                        <i class="bi bi-image me-1"></i>สลิปการชำระเงิน
                    </div>
                    <div class="card-body text-center">
                        <img src="uploads/slips/<?= htmlspecialchars($order['payment_slip']) ?>"
                            class="img-fluid rounded border">
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>