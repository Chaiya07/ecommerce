<?php
require_once '../config/database.php';
require_once 'includes/admin_auth.php';
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
        if (!empty($product['image']) && file_exists('../uploads/products/'.$product['image'])) {
            unlink('../uploads/products/'.$product['image']);
        }
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: manage_products.php');
    exit();
}
$stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
include 'includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>จัดการสินค้า</h2>
    <a href="add_products.php" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> เพิ่มสินค้า</a>
</div>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th width="60">ID</th>
                        <th width="100">รูป</th>
                        <th>สินค้า</th>
                        <th>ราคา</th>
                        <th>Stock</th>
                        <th width="150">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= $product['id'] ?></td>
                            <td>
                                <?php if($product['image']): ?>
                                    <img src="../uploads/products/<?= $product['image'] ?>"
                                    width="70" height="70" style="object-fit:cover">
                                    <?php endif; ?>
                            </td>
                            <td><?= $product['name'] ?></td>
                            <td><?= number_format($product['price'],2) ?> บาท</td>
                            <td><?= $product['stock'] ?></td>
                            <td>
                                <a href="edit_product.php?id=<?= $product['id'] ?>"
                                class="btn btn-warning btn-sm">แก้ไข</a>
                                <a href="manage_products.php?delete=<?= $product['id'] ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('ยืนยันการลบ')">ลบ</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>