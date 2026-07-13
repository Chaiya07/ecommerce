<?php
require_once '../config/database.php';
require_once '../includes/admin_auth.php';
$id = isset($_GET['id']) ? (int)$_GET['id']: 0;
if ($id <=0) {
    header('Location: manage_products.php');
    exit();
}
$sql = $conn->prepare("SELECT * FROM products WHERE id = ?");
$sql->execute(['id']);
$product = $sql->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    header('Location: manage_products.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float) $_POST['price'];
    $stock = (int) $_POST['stock'];
    $imageName = $product['image'];
    if (
        isset($_FILES['image']) && $_FILES['image']['error'] === 0
    ) {
        $extension = strtolower(pathinfo(
            $_FILES['image']['name'], PATHINFO_EXTENSION
        ));
        $allowTypes = ['jpg','jpeg','png','webp'];
        if (in_array($extension, $allowTypes)) {
            $newImageName = time() . '_' . uniqid() . '.' . $extension;
            move_uploaded_file(
                $_FILES['image']['tmp_name'], '../uploads/products/' . $newImageName
            );
            if (!empty($product['image']) && file_exists('../uploads/products/' .
            $product['image'])) {
                unlink('../uploads/products/' . $product['image']);
            }
            $imageName = $newImageName;
        }
    }
    $sql = $conn->prepare("UPDATE products SET name=?,description=?,price=?,stock=?,image=? WHERE id=?");
    $sql->execute([$name,$description,$price,$stock,$imageName,$id]);
    header('Location: manage_products.php');
    exit();
}
include 'includes/header.php';
?>
<div class="d-flex justify_content_between align_item_center mb-4">
    <h2>แก้ไขสินค้า</h2>
    <a href="manage_products.php" class="btn btn-secondary">กลับ</a>
</div>
<div class="card">
    <div class="card_body">
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">ชื่อสินค้า</label>
                <input type="text" name="name" class="form-control"
                value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">รายละเอียดสินค้า</label>
                <textarea name="description" rows="5" class="form-control">
                    <?= htmlspecialchars($product['description']) ?>
                </textarea>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">ราคา</label>
                        <input type="number" step="0.01" min="1" name="price" class="form-control"
                        value="<?= $product['price'] ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">จำนวนสินค้า</label>
                        <input type="number" min="1" name="stock" class="form-control"
                        value="<?= $product['stock'] ?>" required>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">รูปสินค้าปัจจุบัน</label>
                <br>
                <?php if (!empty($product['image'])): ?>
                    <img src="../uploads/products/<?= $product['image'] ?>"
                    class="img-thumbnail" style="max-width: 200px;">
                    <?php else: ?>
                        <div class="alert alert-warning">
                            ไม่มีรูปสินค้า
                        </div>
                    <?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form_label">
                    อัปโหลดรูปใหม่
                </label>
                <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
            </div>
            <button type="submit" class="btn btn-success">อัพเดตสินค้า</button>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>