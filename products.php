<?php
require_once __DIR__ .'/config/database.php';
$keyword = $_GET['keyword']?? '';
$sql = $conn->prepare("
SELECT * FORM products WHERE name LIKE ? ORDER BY id DESC
");
$sql->execute(["%{$keyword}%"]);
$products = $sql->fetchAll(PDO::FETCH_ASSOC);
include 'includes/header.php';
?>
<div class="row mb-4">
    <div class="col-md-12">
        <h2>สินค้าทั้งหมด</h2>
    </diV>
</div>
<form method="GET" class="row mb-4">
    <div class="col-md-10">
        <input type="text" name="keyword" class="form-control"
        placeholder="ค้นหาสินค้า..."value="<?=  htmlspecialchars($keyword) ?>">
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary w-100">ค้นหา</button>
    </div>
</form>
<div class="row">
    <?php if ($products) : ?>
        <?php foreach ($products as $product) : ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <?php $image = !empty($product['image'])
                    ?'uploads/products/'.$product['image']
                    :'https://via.placholdae.com/300x250';?>
                    <img src="<?= $image ?>" class="card-img-top"
                    style="height: 250px;object-fit:cover" alt="" >
                    <div class="card-body">
                        <h5>
                            <?=  htmlspecialchars($product['name']) ?>
                        </h5>
                        <p class="text-danger fw-bold">
                            <?=  number_format($product['price'], 2) ?> บาท
                        </p>
                        <p>คงเหลือ <?= $product['stock'] ?></p>
                    </div>     
                    <div class="card-footer">
                        <a href="product_detail.php?id=<?= $product['id'] ?>"
                        class="btn btn-success w-100">ดูรายละเอียด</a>
                    </div>     
                </div>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="col-12">
            <div class="alert alert-danger">ไม่พบสินค้า</div>
        </div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>