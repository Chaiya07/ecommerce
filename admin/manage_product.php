<?php
require_once '../config/database.php';
require_once '../includes/admin_auth.php';
$message = '';
if  ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float) $_POST['price'];
    $stock = (int) $_POST['stock'];
    $imageName = null;
    if (
        isset($_FILES['image']) && $_FILES['image']['error'] === 0
    ) {
        $extension = strtolower(pathinfo(
            $_FILES['image']['name'], PATHINFO_EXTENSION
        ));
        $allowTypes = ['jpg','jpeg','png','webp'];
        if (in_array($extension, $allowTypes)) {
            $imageName = time() . '_' . uniqid() . '.' . $extension;
            move_uploaded_file(
                $_FILES['image']['tmp_name'], '../uploads/products/' . $imageName
            );
        }
    }
    $sql = $conn->prepare("INSERT INTO products (name,description,price,stock,image) VALUES (?,?,?,?,?)");
    $sql->execute([$name,$description,$price,$stock,$imageName]);
    header('Location: manage_products.php');
    exit();
}
include 'includes/header.php';
?>
<div class="d-flex justify_content_between align_item_center mb-4">
    <h2>เพิ่มสินค้า</h2>
    <a href="manage_products.php" class="btn btn-secondary">กลับ</a>
</div>
<div class="card">
    <div class="card_body">
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">ชื่อสินค้า</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">รายละเอียดสินค้า</label>
                <textarea name="description" rows="5" class="form-control"></textarea>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">ราคา</label>
                        <input type="number" step="0.01" min="1" name="price" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">จำนวนสินค้า</label>
                        <input type="number" min="1" name="stock" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">รูปสินค้า</label>
                <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
            </div>
            <button type="submit" class="btn btn-success">บันทึกสินค้า</button>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>