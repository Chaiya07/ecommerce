<?php
require_once '../config/database.php';
require_once 'includes/admin_auth.php';

$dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-29 days'));
$dateTo   = $_GET['date_to'] ?? date('Y-m-d');

// ---- สถิติภาพรวม ----
$sql = $conn->prepare("SELECT
    COUNT(*) AS total_orders,
    IFNULL(SUM(CASE WHEN payment_status = 'paid' THEN total_price ELSE 0 END), 0) AS total_revenue,
    COUNT(DISTINCT customer_email) AS total_customers
    FROM orders WHERE DATE(created_at) BETWEEN ? AND ?");
$sql->execute([$dateFrom, $dateTo]);
$stats = $sql->fetch(PDO::FETCH_ASSOC);

$paidOrdersCount = $conn->prepare("SELECT COUNT(*) AS c FROM orders WHERE payment_status = 'paid' AND DATE(created_at) BETWEEN ? AND ?");
$paidOrdersCount->execute([$dateFrom, $dateTo]);
$paidCount = $paidOrdersCount->fetch(PDO::FETCH_ASSOC)['c'];

$avgOrderValue = $paidCount > 0 ? $stats['total_revenue'] / $paidCount : 0;

// ---- ยอดขายรายวัน (สำหรับกราฟเส้น) ----
$sql = $conn->prepare("SELECT DATE(created_at) AS d, SUM(total_price) AS total
    FROM orders WHERE payment_status = 'paid' AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at) ORDER BY d ASC");
$sql->execute([$dateFrom, $dateTo]);
$dailySales = $sql->fetchAll(PDO::FETCH_ASSOC);

$chartLabels = [];
$chartData = [];
$dailyMap = [];
foreach ($dailySales as $row) {
    $dailyMap[$row['d']] = (float) $row['total'];
}
$period = new DatePeriod(
    new DateTime($dateFrom),
    new DateInterval('P1D'),
    (new DateTime($dateTo))->modify('+1 day')
);
foreach ($period as $date) {
    $key = $date->format('Y-m-d');
    $chartLabels[] = $date->format('d/m');
    $chartData[] = $dailyMap[$key] ?? 0;
}

// ---- สินค้าขายดี Top 5 ----
$sql = $conn->prepare("SELECT p.name, SUM(oi.quantity) AS qty, SUM(oi.subtotal) AS revenue
    FROM order_items oi
    INNER JOIN products p ON oi.product_id = p.id
    INNER JOIN orders o ON oi.order_id = o.id
    WHERE o.payment_status = 'paid' AND DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY p.id ORDER BY qty DESC LIMIT 5");
$sql->execute([$dateFrom, $dateTo]);
$topProducts = $sql->fetchAll(PDO::FETCH_ASSOC);

// ---- สัดส่วนสถานะการชำระเงิน ----
$sql = $conn->prepare("SELECT payment_status, COUNT(*) AS c FROM orders
    WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY payment_status");
$sql->execute([$dateFrom, $dateTo]);
$statusRows = $sql->fetchAll(PDO::FETCH_ASSOC);
$statusMap = ['pending' => 0, 'paid' => 0, 'cancelled' => 0];
foreach ($statusRows as $row) {
    $statusMap[$row['payment_status']] = (int) $row['c'];
}

$currenPage = 'reports.php';
include 'includes/header.php';
?>

<style>
    .report-hero {
        background: linear-gradient(135deg, #0d6efd 0%, #212529 100%);
        border-radius: 16px;
        padding: 1.75rem 2rem;
        color: #fff;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    .report-hero::after {
        content: '';
        position: absolute;
        right: -40px; top: -40px;
        width: 180px; height: 180px;
        background: rgba(255,255,255,.08);
        border-radius: 50%;
    }
    .report-hero h2 { font-weight: 700; margin-bottom: .25rem; }
    .report-hero p { opacity: .85; margin-bottom: 0; }

    .metric-card {
        border: none;
        border-radius: 14px;
        padding: 1.4rem;
        color: #fff;
        position: relative;
        overflow: hidden;
        height: 100%;
    }
    .metric-card i.bg-icon {
        position: absolute;
        right: 10px; bottom: -10px;
        font-size: 4.5rem;
        opacity: .15;
    }
    .metric-card .metric-label {
        font-size: .82rem;
        opacity: .9;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .metric-card .metric-value {
        font-size: 1.7rem;
        font-weight: 700;
        margin-top: .25rem;
    }
    .metric-revenue  { background: linear-gradient(135deg, #0d6efd, #0a58ca); }
    .metric-orders   { background: linear-gradient(135deg, #198754, #146c43); }
    .metric-avg      { background: linear-gradient(135deg, #fd7e14, #d96b0d); }
    .metric-customer { background: linear-gradient(135deg, #6f42c1, #59339d); }

    .chart-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(0,0,0,.06);
        height: 100%;
    }
    .chart-card .card-header {
        background: #fff;
        border-bottom: 1px solid #f1f3f5;
        font-weight: 600;
        border-radius: 14px 14px 0 0 !important;
    }

    .top-product-row {
        display: flex;
        align-items: center;
        padding: .7rem 0;
        border-bottom: 1px solid #f1f3f5;
    }
    .top-product-row:last-child { border-bottom: none; }
    .top-product-rank {
        width: 30px; height: 30px;
        border-radius: 50%;
        background: #f1f3f5;
        color: #495057;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700;
        font-size: .85rem;
        margin-right: .8rem;
        flex-shrink: 0;
    }
    .top-product-row:nth-child(1) .top-product-rank { background: #ffd43b; color: #664d03; }
    .top-product-row:nth-child(2) .top-product-rank { background: #dee2e6; color: #495057; }
    .top-product-row:nth-child(3) .top-product-rank { background: #eab08a; color: #6c3f1c; }
</style>

<div class="report-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h2><i class="bi bi-graph-up-arrow me-2"></i>รายงานยอดขาย</h2>
        <p>ภาพรวมยอดขายและสินค้าขายดีของร้านค้า</p>
    </div>
    <form method="get" class="d-flex gap-2 align-items-end flex-wrap">
        <div>
            <label class="form-label small text-white-50 mb-1">จากวันที่</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($dateFrom) ?>">
        </div>
        <div>
            <label class="form-label small text-white-50 mb-1">ถึงวันที่</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($dateTo) ?>">
        </div>
        <button class="btn btn-light btn-sm"><i class="bi bi-filter-circle me-1"></i>กรอง</button>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="metric-card metric-revenue">
            <i class="bi bi-cash-stack bg-icon"></i>
            <div class="metric-label">ยอดขายรวม</div>
            <div class="metric-value"><?= number_format($stats['total_revenue'], 0) ?> ฿</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="metric-card metric-orders">
            <i class="bi bi-bag-check-fill bg-icon"></i>
            <div class="metric-label">คำสั่งซื้อทั้งหมด</div>
            <div class="metric-value"><?= number_format($stats['total_orders']) ?></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="metric-card metric-avg">
            <i class="bi bi-graph-up bg-icon"></i>
            <div class="metric-label">มูลค่าเฉลี่ย/ออเดอร์</div>
            <div class="metric-value"><?= number_format($avgOrderValue, 0) ?> ฿</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="metric-card metric-customer">
            <i class="bi bi-people-fill bg-icon"></i>
            <div class="metric-label">ลูกค้าที่สั่งซื้อ</div>
            <div class="metric-value"><?= number_format($stats['total_customers']) ?></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card chart-card">
            <div class="card-header"><i class="bi bi-bar-chart-line me-1"></i>แนวโน้มยอดขายรายวัน</div>
            <div class="card-body">
                <canvas id="revenueChart" height="110"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card chart-card">
            <div class="card-header"><i class="bi bi-pie-chart me-1"></i>สัดส่วนสถานะการชำระเงิน</div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="statusChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card chart-card">
            <div class="card-header"><i class="bi bi-trophy me-1"></i>สินค้าขายดี Top 5</div>
            <div class="card-body">
                <?php if (count($topProducts) > 0) : ?>
                    <?php foreach ($topProducts as $i => $p) : ?>
                        <div class="top-product-row">
                            <div class="top-product-rank"><?= $i + 1 ?></div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold"><?= htmlspecialchars($p['name']) ?></div>
                                <div class="text-muted small">ขายได้ <?= number_format($p['qty']) ?> ชิ้น</div>
                            </div>
                            <div class="fw-bold text-primary"><?= number_format($p['revenue'], 0) ?> ฿</div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        ยังไม่มีข้อมูลการขายในช่วงนี้
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card chart-card">
            <div class="card-header"><i class="bi bi-bar-chart me-1"></i>เปรียบเทียบยอดขายสินค้าขายดี</div>
            <div class="card-body">
                <canvas id="topProductChart" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
const revenueCtx = document.getElementById('revenueChart');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'ยอดขาย (บาท)',
            data: <?= json_encode($chartData) ?>,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13,110,253,0.12)',
            fill: true,
            tension: 0.35,
            pointRadius: 3,
            pointBackgroundColor: '#0d6efd'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() } }
        }
    }
});

const statusCtx = document.getElementById('statusChart');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['ชำระแล้ว', 'รอตรวจสอบ', 'ยกเลิก'],
        datasets: [{
            data: [<?= $statusMap['paid'] ?>, <?= $statusMap['pending'] ?>, <?= $statusMap['cancelled'] ?>],
            backgroundColor: ['#198754', '#fd7e14', '#dc3545'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        cutout: '68%'
    }
});

const topProductCtx = document.getElementById('topProductChart');
new Chart(topProductCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($topProducts, 'name')) ?>,
        datasets: [{
            label: 'จำนวนที่ขายได้',
            data: <?= json_encode(array_map('intval', array_column($topProducts, 'qty'))) ?>,
            backgroundColor: '#0d6efd',
            borderRadius: 6,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>