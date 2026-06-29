<?php
require_once __DIR__ . '/config/database.php';
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: products.php');
    exit;
}

$sql = $conn->prepare("SELECT * FROM products WHERE id = ?");
$sql->execute([$id]);
$product = $sql->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: products.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $quantity = (int)$_POST['quantity'];
    if ($quantity < 1) {
        $quantity = 1;
    }
    if ($quantity > $product['stock']) {
        $quantity = $product['stock'];
    }
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $productId = $product['id'];
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['qty'] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = [
            'id'    => $product['id'],
            'name'  => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'],
            'qty'   => $quantity,
        ];
    }
    $message = 'เพิ่มสินค้าลงตะกร้าเรียบร้อยแล้ว';
}

include 'includes/header.php';
$image = !empty($product['image']) ? 'uploads/products/' . $product['image'] : '';
?>
<div class="row">
    <div class="col-md-5">
        <img src="<?= $image ?>" class="img-fluid rounded shadow"
            alt="<?= htmlspecialchars($product['name']) ?>">
    </div>
    <div class="col-md-7">
        <h2><?= htmlspecialchars($product['name']) ?></h2>
        <hr>
        <h3 class="text-danger"><?= number_format($product['price'], 2) ?> บาท</h3>
        <p><strong>จำนวนคงเหลือ :</strong> <?= $product['stock'] ?> ชิ้น</p>
        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

        <?php if ($message) : ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>

        <?php if ($product['stock'] > 0) : ?>
            <form method="post">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">จำนวน</label>
                        <input type="number" name="quantity" class="form-control"
                            value="1" min="1" max="<?= $product['stock'] ?>">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-success">เพิ่มลงตะกร้า</button>
                    <a href="cart.php" class="btn btn-primary">ดูตะกร้าสินค้า</a>
                </div>
            </form>
        <?php else : ?>
            <div class="alert alert-danger mt-3">สินค้าหมด</div>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
