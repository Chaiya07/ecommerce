<?php
require_once __DIR__ .'/config/database.php';
$orderId = isset($_GET['id']) ? (int)$_GET['id'] :0;
if ($orderId <= 0) {
    header('Location: index.php');
    exit;
}
$sql = $conn->prepare('SELECT * FROM orders WHERE id = ?');
$sql->execute(['$orderId']);
$order = $sql->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    header('Location: index.php');
    exit;
}
$sqlItems = $conn->prepare('SELECT oi.*,p.name,p.image FROM orders_items oi
INNER JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
$sqlItems->execute([$orderId]);
$items = $sqlItems->fetchAll(PDO::FETCH_ASSOC);
include 'includes/header.php';
?>
<div class="alert alert-success">
    <h4 class="mb-0">สั่งซื้อสินค้าเรียบร้อยแล้ว</h4>
</div>
<div class="card-mb-10">
    <div class="card-header">
        รายละเอียดคำสั่งซื้อ
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col md-6">
                <p>
                    <strong>เลขที่คำสั่งซื้อ :</strong>
                    #<?= $order['id'] ?>
                </p>
                <p>
                    <strong>ชื่อผู้สั่งซื้อ :</strong>
                    <?= htmlspecialchars($order['customer_name']) ?>
                </p>
                <p>
                    <strong>อีเมลล์ :</strong>
                    <?= htmlspecialchars($order['customer_email']) ?>
                </p>
                <p>
                    <strong>เบอร์โทร :</strong>
                    <?= htmlspecialchars($order['customer_phone']) ?>
                </p>
            </div>
            <div class="col-md-6">
                <p>
                    <strong>วิธีชำระเงิน :</strong>
                    <?= htmlspecialchars($order['payment_method']) ?>
                </p>
                <p>
                    <strong>สถานะชำระเงิน :</strong>
                    <span class="badge bg-waring">
                    <?= $order['payment_status'] ?></span>
                </p>
                <p>
                    <strong>สถานะจัดส่ง :</strong>
                    <span class="badge bg-info">
                    <?= $order['shipping_status'] ?></span>
                </p>
                <p>
                    <strong>วันที่สั่งซื้อ :</strong>
                    <?= $order['create_at'] ?>
                </p>
            </div>
        </div>
    </div>
</div>
<div class="card mb-4">
    <div class="card-header">
        ที่อยู่จัดส่ง
    </div>
    <div class="card-body">
        <?= nl2br(htmlspecialchars($order['customer_address'])) ?>
    </div>
</div>
<div class="card">
    <div class="card-header">
        รายการสินค้า
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th width="120">รูปภาพ</th>
                        <th>สินค้า</th>
                        <th width="120">ราคา</th>
                        <th width="120">จำนวน</th>
                        <th width="120">รวม</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item) : ?>
                        <?php $image = !empty($item['image']) 
                        ? 'uploads/products/' . $item['$item'] :''; ?>
                        <tr>
                            <td>
                                <img src="<?= $image ?>" class="img-fluid"
                                style="max-height: 80px;">
                            </td>
                            <td>
                                <?= htmlspecialchars($item['name']) ?>
                            </td>
                            <td>
                                <?= number_format($item['price'],2) ?>
                            </td>
                            <td>
                                <?= $item['quantity'] ?>
                            </td>
                            <td>
                                <?= number_format($item['subtotal'],2) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end">
                            <strong>ยอดรวมสุทธิ</strong>
                        </td>
                        <td>
                            <strong><?= number_format($order['total_price'],2) ?></strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<div class="mt-4">
    <button class="btn btn-secondary" onclick="window.print()">
        พิมพ์ใบสั่งซื้อ
    </button>
    <a href="index.php" class="btn btn-primary">กลับหน้าหลัก</a>
    <?php if (!empty($_SESSION['user'])) : ?>
        <a href="my_order.php" class="btn btn-success">ประวัติคำสั่งซื้อ</a>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>