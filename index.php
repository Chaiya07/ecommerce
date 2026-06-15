<?php
require_once __DIR__ .'/config/database.php';
$sql = $conn->prepare("
SELECT * FORM products ORDER BY id DESC LIMIT 8
");
$sql->execute();
$products = $sql->fetchAll(PDO::FETCH_ASSOC);
include '';
?>
<div class="p-5 mb-4 bg-light rounded-3">
    <div class="container-fluid py-3">
        <h1 class="display-5 fw-bold">ยินดีต้อนรับเข้าสู่ร้านค้าออนไลน์</h1>
        <p class="col-md-8 fs-5">
            เลือกซื้อสินค้าคุณภาพดี ราคาคุ้มค่า พร้อมโปรโมชั่นพิเศษ
        </p>
        <a href="products.php" class="btn-primary btn-lg">
            เลือกซื้อสินค้า
        </a>
    </div>
</div>
<div class="row">
    <div class="col-10 mb-4">
        <h2>สินค้าล่าสุด</h2>
    </div>
</div>
<div class="row">
    <?php if (count($products) > 0) : ?>
        <?php foreach ($products as $product) : ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php $image = !empty($products['image'])
                    ? 'uploads/products/' . $product['image']
                    :'https://via.placehoder.com/300x250?text=NO+Image';
                    ?>
                    <img src="<?= $image ?>" class="card-img-top">
                    alt="<?=  htmlspecialchars($product['name']) ?>"
                    style="height: 250px; obgect-fit:cover;">
                    <div class="card-body">
                        <h5 class="card-titlm">
                            <?= htmlspecialchars($product['name']) ?>
                    </h5>
                    <p class="text-danger fw-bold mb-2">
                        <?= number_format($product['price'], 2) ?> บาท
                    </p>
                    <p class="text-muted">
                    </p>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="product_detail.php?id<?= $product['id'] ?>"
                    class="btn btn-primary w-100">ดูรายละเอียด</a>
                </div>
            </div>
        <div
        <?php endforeach; ?>
    <?php else : ?>
        <div class="col-12">
            <div class="alert alert-waring">ไม่พบสินค้าในระบบ</div>
        </div>
    <?php endif; ?>
</div>
<?php include'includes/footer.php'; ?>