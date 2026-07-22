<?php
require_once '../config/database.php';
require_once 'includes/admin_auth.php';

$keyword = trim($_GET['keyword'] ?? '');

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $sql = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $sql->execute([$id]);
    header('Location: manage_orders.php');
    exit();
}

$sql = $conn->prepare("SELECT * FROM orders WHERE customer_name LIKE ?
OR id LIKE ? ORDER BY id DESC");
$sql->execute(["%{$keyword}%", "%{$keyword}%"]);
$orders = $sql->fetchAll(PDO::FETCH_ASSOC);

$totalOrders   = count($orders);
$totalPending  = count(array_filter($orders, fn($o) => $o['payment_status'] === 'pending'));
$totalPaid     = count(array_filter($orders, fn($o) => $o['payment_status'] === 'paid'));
$totalRevenue  = array_sum(array_column(array_filter($orders, fn($o) => $o['payment_status'] === 'paid'), 'total_price'));

include 'includes/header.php';
?>

<style>
    .stat-mini {
        border: none;
        border-radius: 12px;
        padding: 1.1rem 1.3rem;
        display: flex;
        align-items: center;
        gap: .8rem;
        color: #fff;
    }
    .stat-mini i { font-size: 1.6rem; opacity: .9; }
    .stat-mini .num { font-size: 1.35rem; font-weight: 700; line-height: 1; }
    .stat-mini .label { font-size: .8rem; opacity: .9; }
    .stat-total    { background: #212529; }
    .stat-pending  { background: #fd7e14; }
    .stat-paid     { background: #198754; }
    .stat-revenue  { background: #0d6efd; }

    .search-wrap { position: relative; }
    .search-wrap i {
        position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        color: #adb5bd;
    }
    .search-wrap input { padding-left: 40px; }

    .order-table thead th {
        font-size: .82rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        vertical-align: middle;
    }
    .order-table tbody td { vertical-align: middle; }
    .order-id { font-weight: 700; color: #495057; }

    .badge-status { font-weight: 500; padding: .45em .7em; border-radius: 20px; }

    .btn-icon-sm {
        width: 34px; height: 34px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 8px;
    }

    .empty-state {
        text-align: center;
        padding: 3.5rem 1rem;
        color: #adb5bd;
    }
    .empty-state i { font-size: 3rem; margin-bottom: .5rem; display: block; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="bi bi-receipt-cutoff me-2"></i>จัดการคำสั่งซื้อ</h2>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-mini stat-total">
            <i class="bi bi-boxes"></i>
            <div>
                <div class="num"><?= number_format($totalOrders) ?></div>
                <div class="label">คำสั่งซื้อทั้งหมด</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-mini stat-pending">
            <i class="bi bi-hourglass-split"></i>
            <div>
                <div class="num"><?= number_format($totalPending) ?></div>
                <div class="label">รอชำระเงิน</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-mini stat-paid">
            <i class="bi bi-check-circle"></i>
            <div>
                <div class="num"><?= number_format($totalPaid) ?></div>
                <div class="label">ชำระเงินแล้ว</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-mini stat-revenue">
            <i class="bi bi-cash-stack"></i>
            <div>
                <div class="num"><?= number_format($totalRevenue, 2) ?></div>
                <div class="label">ยอดขาย (บาท)</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="row g-2">
            <div class="col-md-10">
                <div class="search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" name="keyword" class="form-control"
                        placeholder="ค้นหาเลขที่ออเดอร์ หรือ ชื่อลูกค้า"
                        value="<?= htmlspecialchars($keyword) ?>">
                </div>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>ค้นหา
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (count($orders) > 0) : ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle order-table">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>ลูกค้า</th>
                            <th>ยอดรวม</th>
                            <th>ชำระเงิน</th>
                            <th>จัดส่ง</th>
                            <th>วันที่</th>
                            <th class="text-end">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order) :
                            $paymentColor = match ($order['payment_status']) {
                                'paid' => 'success',
                                'cancelled' => 'danger',
                                default => 'warning'
                            };
                            $shippingColor = match ($order['shipping_status']) {
                                'processing' => 'info',
                                'shipped' => 'primary',
                                'delivered' => 'success',
                                default => 'secondary'
                            };
                        ?>
                            <tr>
                                <td class="order-id">#<?= $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= number_format($order['total_price'], 2) ?> บาท</td>
                                <td>
                                    <span class="badge bg-<?= $paymentColor ?> badge-status">
                                        <?= htmlspecialchars($order['payment_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $shippingColor ?> badge-status">
                                        <?= htmlspecialchars($order['shipping_status']) ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                <td class="text-end">
                                    <a href="order_detail.php?id=<?= $order['id'] ?>"
                                        class="btn btn-outline-primary btn-icon-sm" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="manage_orders.php?delete=<?= $order['id'] ?>"
                                        class="btn btn-outline-danger btn-icon-sm" title="ลบ"
                                        onclick="return confirm('ยืนยันการลบคำสั่งซื้อนี้?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                ไม่พบคำสั่งซื้อ
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>