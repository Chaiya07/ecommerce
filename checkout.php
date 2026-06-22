<?php
require_once __DIR__ .'/config/database.php';
session_start();
if (empty($_SESSION['cart'])) {
    header('Location: products.php');
    exit;
}
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += ($item['price'] * $item['qty']);
}
$discount = 0;
if (!empty($_SESSION['coupon'])) {
    $coupon = $_SESSION['coupon'];
    if ($coupon['discount_type'] == 'percent') {
        $discount = $total * ($coupon['discount_value'] / 100);
    } else {
        $discount = $coupon['discount_value'];
    }
}
$finalTotal = $total - $discount;
if ($finalTotal < 0) {
    $finalTotal = 0;
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = trim($_POST['customer_name']);
    $customerEmail = trim($_POST['customer_email']);
    $customerPhone = trim($_POST['customer_phone']);
    $customerAddress = trim($_POST['customer_address']);
    $paymentMethod = ($_POST['payment_method']);
    $paymentSlip = null;

if ($paymentMethod === 'transfer' && !empty($_FILES['payment_slip']['name'])) {
    $fileName = time() .'_'. basename($_FILES['payment_slip']['name']);
    move_uploaded_file($_FILES['payment_slip']['tmp_name']
    ,'uploads/slips/' . $fileName);
    $paymentSlip = $fileName;
} try {
    $conn->beginTransaction();
    $userId = null;
    if (!empty($_SESSION['user'])) {
        $userId = $_SESSION['user']['id'];
    }
    $sql = $conn->prepare("INSERT INTO orders(
        user_id,
        customer_name,
        customer_email,
        customer_phone,
        customer_address,
        total_price,
        payment_method,
        payment_slip)
    VALUES (?,?,?,?,?,?,?,?)");
    $sql->execute([
        $userId,
        $customerName,
        $customerEmail,
        $customerPhone,
        $customerAddress,
        $finalTotal,
        $paymentMethod,
        $paymentSlip]);
    $orderId = $conn->lastInsertId();
    foreach ($_SESSION['cart'] as $item) {
        $subtotal = $item['price'] * $item['qty'];
        $sqlItem = $conn->prepare("INSERT INTO  orders_items(
        order_id,
        product_id,
        quantity,
        price,
        subtotal)
        VALUES (?,?,?,?,?)");
        $sqlItem->execute([
            $orderId,
            $item['id'],
            $item['qty'],
            $item['price'],
            $subtotal
        ]);
        $sqlStock = $conn->prepare('UPDATE products
        SET stock = stock - ? WHERE id = ?');
        $sqlStock->execute([$item['qty'],$item['id']]); 
    }
    if (!empty($_SESSION['coupon'])) {
        $sqlCoupon = $conn->prepare('UPDATE coupons
        SET used_count = used_count + 1 WHERE id = ?');
        $sqlCoupon->execute([$_SESSION['coupon']['id']]);
    }
    $conn->commit();
    unset($_SESSION['cart']);
    unset($_SESSION['coupon']);
    header('Location: order_success.php?id=' . $orderId);
    exit;
} catch (Exception $e) {
    $conn->rollback();
    $message = 'เกิดข้อผิดพลาด : ' . $e->getMessage();
    }
}
include 'includes/header.php';
?>
<h2 class="mb-4">ยืนยันคำสั่งซื้อ</h2>
<?php if ($message) : ?>
    <div class="alert alert-danger">
        <?= $message ?>
    </div>
<?php endif; ?>
<div class="row">
    <div class="col-md-7">
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">ชื่อผู้รับ</label>
                <input type="text" name="customer_name" 
                class="form-control" require>
            </div>
            <div class="mb-3">
                <label class="form-label">อีเมลล์</label>
                <input type="email" name="customer_email" 
                class="form-control" require>
            </div>
            <div class="mb-3">
                <label class="form-label">เบอร์โทรศัพท์</label>
                <input type="text" name="customer_phone" 
                class="form-control" require>
            </div>
                <div class="mb-3">
                <label class="form-label">ที่อยู่จัดส่ง</label>
                <textarea name="customer_address" 
                class="form-control" rows="4" require></textarea>
            </div>
        </form>
        <div class="mb-3">
                <label class="form-label">วิธีชำระเงิน</label>
                <select name="payment_method" 
                class="form-select" require>
                <option value="transfer">โอนเงิน</option>
                <option value="cod">เก็บเงินปลายทาง</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">แนบสลิปโอนเงิน</label>
                <input type="file" name="payment_slip" 
                class="form-control">
            </div>
            <button type="submit" class="btn btn-success">ยืนยันคำสั่งซื้อ</button>
        </form>
    </div>
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">สรุปคำสั่งซื้อ</div>
            <div class="card-body">
                <p>ยอดรวม :
                    <strong><?= number_format($total, 2) ?></strong> บาท
                </p>
                <p>ส่วนลด :<strong class="text-danger">
                    <?= number_format($discount, 2) ?></strong> บาท
                </p>
                <hr>
                <p>ยอดสุทธิ :<strong class="text-success fs-4">
                    <?= number_format($finalTotal, 2) ?></strong> บาท
                </p>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>