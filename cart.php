<?php
require_once __DIR__ .'/config/database.php';
session_start();
if (isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$message = '';
$discount = 0;
$couponCode = '';
if (isset($_GET['remove'])) {
    $productId = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
    header('Location: product.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['qty'] as $productId => $qty) {
        $qty = (int)$qty;
        if ($qty <= 0) {
            unset($_SESSION['cart'][$productId]);
        } else {
            $_SESSION['cart'][$productId][$qty] = $qty;
        }
    }
    $message='อัปเดตตะกร้าสินค้าเรียบร้อยแล้ว';
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_coupon'])) {
    $couponCode = trim($_POST['coupon_code']);
    $sql = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active'");
    $sql->execute([""=> $couponCode]);
    $coupon = $sql->fetch(PDO::FETCH_ASSOC);
    if ($coupon) {
        $today = date("Y-m-d");
        if (!empty($coupon["expiry_date"]) && $coupon['expiry_date'] < $today) {
            $message = 'คูปองหมดอายุแล้ว';
        } else {
            $_SESSION['coupon'] = $coupon;
            $message= 'ใช้คูปองสำเร็จ';
        }
    } else {
        $message = 'ไม่พบคูปอง';
    }
}
include 'includes/header.php';
$total = 0;
?>
<h2 class="mb-4">ตะกร้าสินค้า</h2>
<?php if ($message): ?>
    <div class="alert alert-info">
        <?= $message ?>
    </div>
<?php endif; ?>
<?php if (empty($_SESSION['cart'])): ?>
    <div class="alert alrty-waring">ยังไม่มีสินค้า</div>
    <a href="products.php" class="btn btn-primary">เลือกสินค้า</a>
<?php else: ?>
    <form method="post">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th width="120">รูปสินค้า</th>
                        <th>สินค้า</th>
                        <th width="150">ราคา</th>
                        <th width="120">จำนวน</th>
                        <th width="150">รวม</th>
                        <th width="100">ลบ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <?php $image = !empty($item['image']) ? 
                        'upload/products/' . $item['image'] : '';
                        $subtotal = $item['price'] * $item['qty'];
                        $total += $subtotal; ?>
                    <tr>
                        <td><img src="<?= $image ?>" class="img-fluid" style="max-height: 80px;"></td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= number_format($item['price'],2) ?></td>
                        <td>
                            <a href="cart.php?remove=<?= $item['id'] ?>"
                            class="btn btn-danger btn-sm"
                            oneclick="return confirm('ลบสินค้านี้หรือไม่ ?')"
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <button type="submit" name="update_cart" class="btn btn-warning">อัปเดตตะกร้า</button>
    </form>
    <hr>
<?php $finalTotal = $total;
if (!empty($_SESSION['coupon'])) {
    $coupon = $_SESSION['coupon'];
    if ($coupon['discount_type'] === 'percent') {
        $discount = $total * ($coupon['discount_type'] / 100);
    } else {
        $discount = $coupon['discount_value'];
    }
    $finalTotal += $total - $discount;
    if ($finalTotal < 0) {
        $finalTotal = 0;
    }
}
?>
<div class="row-mt-4">
    <div class="col-md-6">
        <form method="post">
            <label class="fortm-label">รหัสคูปอง</label>
            <div class="input-group">
                <input type="text" name="coupon_code" class="form-control" placeholder="กรอกรหัสคูปอง">
                <button type="submit" name="apply_coupon" class="btn btn-success">ใช้งาน</button>
            </div>
        </form>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5>สรุปการสั่งซื้อ</h5>
                <hr>
                <p>ยอดรวม : <strong><?= number_format($total,2) ?></strong> บาท</p>
                <p>ส่วนลด : <strong><?= number_format($discount,2) ?></strong> บาท</p>
                <p>ยอดรวม : <strong> class="text-success fs-5">
                    <?= number_format($finalTotal,2) ?></strong> บาท
                </p>
                <a href="checkout.php" class="btn btn-primary w-100">ดำเนินการสั่งซื้อ</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>