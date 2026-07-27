<?php
require_once '../config/database.php';
require_once 'includes/admin_auth.php';

$keyword = trim($_GET['keyword'] ?? '');

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $sql = $conn->prepare("DELETE FROM coupons WHERE id = ?");
    $sql->execute([$id]);
    header('Location: manage_coupons.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code          = strtoupper(trim($_POST['code']));
    $discountType  = $_POST['discount_type'];
    $discountValue = (float) $_POST['discount_value'];
    $usageLimit    = !empty($_POST['usage_limit']) ? (int) $_POST['usage_limit'] : null;
    $expiryDate    = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $status        = $_POST['status'];

    $sql = $conn->prepare("INSERT INTO coupons(code,discount_type,discount_value,usage_limit,
    expiry_date,status) VALUES (?,?,?,?,?,?)");
    $sql->execute([$code, $discountType, $discountValue, $usageLimit, $expiryDate, $status]);
    header('Location: manage_coupons.php');
    exit();
}

$sql = $conn->prepare("SELECT * FROM coupons WHERE code LIKE ? ORDER BY id DESC ");
$sql->execute(["%{$keyword}%"]);
$coupons = $sql->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>จัดการคูปอง</h2>
</div>

<div class="card mb-4">
    <div class="card-header">
        เพิ่มคูปองใหม่
    </div>
    <div class="card-body">
        <form method="post" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">รหัสคูปอง</label>
                <input type="text" name="code" class="form-control" placeholder="เช่น SALE50" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">ประเภทส่วนลด</label>
                <select name="discount_type" class="form-select" required>
                    <option value="percent">เปอร์เซ็นต์ (%)</option>
                    <option value="amount">จำนวนเงิน (บาท)</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">มูลค่าส่วนลด</label>
                <input type="number" step="0.01" min="0" name="discount_value" class="form-control" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">จำนวนสิทธิ์ใช้งาน</label>
                <input type="number" min="1" name="usage_limit" class="form-control" placeholder="ไม่จำกัดถ้าเว้นว่าง">
            </div>

            <div class="col-md-3">
                <label class="form-label">วันหมดอายุ</label>
                <input type="date" name="expiry_date" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">สถานะ</label>
                <select name="status" class="form-select" required>
                    <option value="active">เปิดใช้งาน</option>
                    <option value="inactive">ปิดใช้งาน</option>
                </select>
            </div>

            <div class="col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-plus-circle"></i> เพิ่มคูปอง
                </button>
            </div>
        </form>
    </div>
</div>

<form method="GET" class="row mb-4">
    <div class="col-md-10">
        <input type="text" name="keyword" class="form-control"
            placeholder="ค้นหารหัสคูปอง"
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
                        <th>รหัสคูปอง</th>
                        <th>ประเภท</th>
                        <th>มูลค่า</th>
                        <th>ใช้ไปแล้ว</th>
                        <th>สิทธิ์ทั้งหมด</th>
                        <th>วันหมดอายุ</th>
                        <th>สถานะ</th>
                        <th width="100">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($coupons) > 0) : ?>
                        <?php foreach ($coupons as $coupon) : ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($coupon['code']) ?></strong></td>
                                <td><?= $coupon['discount_type'] === 'percent' ? 'เปอร์เซ็นต์' : 'จำนวนเงิน' ?></td>
                                <td>
                                    <?= $coupon['discount_type'] === 'percent'
                                        ? number_format($coupon['discount_value'], 0) . ' %'
                                        : number_format($coupon['discount_value'], 2) . ' บาท' ?>
                                </td>
                                <td><?= $coupon['used_count'] ?></td>
                                <td><?= $coupon['usage_limit'] ?? 'ไม่จำกัด' ?></td>
                                <td><?= $coupon['expiry_date'] ?? '-' ?></td>
                                <td>
                                    <?php if ($coupon['status'] === 'active') : ?>
                                        <span class="badge bg-success">เปิดใช้งาน</span>
                                    <?php else : ?>
                                        <span class="badge bg-secondary">ปิดใช้งาน</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="manage_coupons.php?delete=<?= $coupon['id'] ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('ยืนยันการลบคูปองนี้ ?')">ลบ</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">ไม่พบคูปอง</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>