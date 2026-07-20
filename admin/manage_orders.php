<?php
require_once '../config/database.php';
require_once 'includes/admin_auth.php';
$keyword = trim($_GET['keyword'] ?? '');
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $sql = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $sql->execute([$id]);
    header('Location: manage_products.php');
    exit();
}
$sql = $conn->prepare("SELECT * FROM orders WHERE customer_name LIKE ?
OR id LIKE ? ORDER BY ID DESC");
$sql->execute(["%{$keyword}%","%{$keyword}%",]);
$orders = $sql->fetchAll(PDO::FETCH_ASSOC);
include 'includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>จัดการคำสั่งซื้อ</h2>
</div>
<form method="get" class="row mb-4">
    <div class="col-md-10">
        <input type="text" name="keyword" class="form-control"
        placeholder="ค้นหาเลขที่ออเดอร์ หรือ ชื่อลูกค้า"
        value="<?= htmlspecialchars($keyword) ?>">
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary w-100">ค้นหา</button>
    </div>
</form>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>ลูกค้า</th>
                        <th>ยอดรวม</th>
                        <th>ชำระเงิน</th>
                        <th>จัดส่ง</th>
                        <th>วันที่</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td><?=  number_format($order['total_price'],2) ?></td>
                            <td><?= $order['payment_status'] ?></td>
                            <td><?= $order['shipping_status'] ?></td>
                            <td><?= date('d/m/Y H:i',strtotime($order['created_at'])) ?></td>
                            <td>
                                <a href="order_detail.php?id=<?= $order['id'] ?>"
                                class="btn btn-info btn-sm">ดูรายละเอียด</a>
                                <a href="manage_orders.php?delete=<?= $order['id'] ?>"
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