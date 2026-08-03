<?php
require_once __DIR__ . '/config/database.php';
$sql = $conn->prepare("SELECT * FROM products ORDER BY id DESC LIMIT 8");
$sql->execute();
$products = $sql->fetchAll(PDO::FETCH_ASSOC);
include 'includes/header.php';

$s = $GLOBALS['storeSettings'] ?? [];
?>

<?php if (!empty($s['banner'])) : ?>
    <div class="hero-banner mb-4" data-aos="fade-up"
        style="background-image: url('uploads/settings/<?= htmlspecialchars($s['banner']) ?>');">
        <div class="hero-banner-overlay">
            <h1 class="display-5 fw-bold">ยินดีต้อนรับเข้าสู่ <?= htmlspecialchars($s['name'] ?? 'ร้านค้าออนไลน์') ?></h1>
            <p class="fs-5">เลือกซื้อสินค้าคุณภาพดี ราคาคุ้มค่า พร้อมโปรโมชั่นพิเศษ</p>
            <a href="products.php" class="btn btn-primary btn-lg">เลือกซื้อสินค้า</a>
        </div>
    </div>
<?php else : ?>
    <div class="p-5 mb-4 bg-light rounded-3" data-aos="fade-up">
        <div class="container-fluid py-3">
            <h1 class="display-5 fw-bold">ยินดีต้อนรับเข้าสู่ร้านค้าออนไลน์</h1>
            <p class="col-md-8 fs-5">
                เลือกซื้อสินค้าคุณภาพดี ราคาคุ้มค่า พร้อมโปรโมชั่นพิเศษ
            </p>
            <a href="products.php" class="btn btn-primary btn-lg">
                เลือกซื้อสินค้า
            </a>
        </div>
    </div>
<?php endif; ?>

<div class="row" data-aos="fade-up">
    <div class="col-12 mb-4">
        <h2>สินค้าล่าสุด</h2>
    </div>
</div>

<div class="row">
    <?php if (count($products) > 0) : ?>
        <?php foreach ($products as $index => $product) : ?>
            <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="<?= $index * 80 ?>">
                <div class="card h-100 shadow-sm product-card">
                    <?php $image = !empty($product['image'])
                        ? 'uploads/products/' . $product['image']
                        : 'https://via.placeholder.com/300x250?text=No+Image';
                    ?>
                    <img src="<?= $image ?>" class="card-img-top"
                        alt="<?= htmlspecialchars($product['name']) ?>"
                        style="height: 250px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title">
                            <?= htmlspecialchars($product['name']) ?>
                        </h5>
                        <p class="text-danger fw-bold mb-2">
                            <?= number_format($product['price'], 2) ?> บาท
                        </p>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <a href="product_detail.php?id=<?= $product['id'] ?>"
                            class="btn btn-primary w-100">ดูรายละเอียด</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="col-12" data-aos="fade-up">
            <div class="alert alert-warning">ไม่พบสินค้าในระบบ</div>
        </div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>