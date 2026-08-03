<?php
require_once '../config/database.php';
require_once 'includes/admin_auth.php';

$keyword = trim($_GET['keyword'] ?? '');
$currentAdminId = $_SESSION['user']['id'];

// สลับสถานะ active/banned
if (isset($_GET['toggle_status'])) {
    $id = (int) $_GET['toggle_status'];
    if ($id !== $currentAdminId) {
        $sql = $conn->prepare("SELECT status FROM users WHERE id = ?");
        $sql->execute([$id]);
        $user = $sql->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $newStatus = $user['status'] === 'active' ? 'banned' : 'active';
            $sql = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
            $sql->execute([$newStatus, $id]);
        }
    }
    header('Location: manage_user.php');
    exit();
}

// สลับสิทธิ์ user / admin
if (isset($_GET['toggle_role'])) {
    $id = (int) $_GET['toggle_role'];
    if ($id !== $currentAdminId) {
        $sql = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $sql->execute([$id]);
        $user = $sql->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $newRole = $user['role'] === 'admin' ? 'user' : 'admin';
            $sql = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $sql->execute([$newRole, $id]);
        }
    }
    header('Location: manage_user.php');
    exit();
}

$sql = $conn->prepare("SELECT * FROM users WHERE username LIKE ? OR email LIKE ? ORDER BY id DESC");
$sql->execute(["%{$keyword}%", "%{$keyword}%"]);
$users = $sql->fetchAll(PDO::FETCH_ASSOC);

$totalUsers  = count($users);
$totalAdmins = count(array_filter($users, fn($u) => $u['role'] === 'admin'));
$totalBanned = count(array_filter($users, fn($u) => $u['status'] === 'banned'));

$currenPage = 'manage_user.php';
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
    .stat-total { background: #212529; }
    .stat-paid  { background: #198754; }

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

    .user-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
    }
    .user-avatar-placeholder {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #adb5bd;
        font-size: 1.4rem;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="bi bi-people-fill me-2"></i>จัดการสมาชิก</h2>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4 col-6">
        <div class="stat-mini stat-total">
            <i class="bi bi-people"></i>
            <div>
                <div class="num"><?= number_format($totalUsers) ?></div>
                <div class="label">สมาชิกทั้งหมด</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="stat-mini stat-paid">
            <i class="bi bi-shield-check"></i>
            <div>
                <div class="num"><?= number_format($totalAdmins) ?></div>
                <div class="label">แอดมิน</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="stat-mini" style="background:#dc3545;">
            <i class="bi bi-slash-circle"></i>
            <div>
                <div class="num"><?= number_format($totalBanned) ?></div>
                <div class="label">ถูกระงับ</div>
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
                        placeholder="ค้นหาชื่อผู้ใช้ หรือ อีเมล"
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
        <?php if (count($users) > 0) : ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle order-table">
                    <thead class="table-dark">
                        <tr>
                            <th width="60">รูป</th>
                            <th>ชื่อผู้ใช้</th>
                            <th>อีเมล</th>
                            <th>สิทธิ์</th>
                            <th>สถานะ</th>
                            <th>สมัครเมื่อ</th>
                            <th class="text-end">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) : ?>
                            <tr>
                                <td>
                                    <?php if (!empty($user['avatar'])) : ?>
                                        <img src="../uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" class="user-avatar">
                                    <?php else : ?>
                                        <div class="user-avatar-placeholder">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($user['username']) ?></strong>
                                    <?php if ($user['id'] === $currentAdminId) : ?>
                                        <span class="badge bg-primary badge-status ms-1">คุณ</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <?php if ($user['role'] === 'admin') : ?>
                                        <span class="badge bg-success badge-status">แอดมิน</span>
                                    <?php else : ?>
                                        <span class="badge bg-secondary badge-status">สมาชิก</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['status'] === 'active') : ?>
                                        <span class="badge bg-success badge-status">ใช้งานได้</span>
                                    <?php else : ?>
                                        <span class="badge bg-danger badge-status">ถูกระงับ</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">
                                    <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($user['id'] !== $currentAdminId) : ?>
                                        <a href="manage_user.php?toggle_role=<?= $user['id'] ?>"
                                            class="btn btn-outline-primary btn-icon-sm"
                                            title="<?= $user['role'] === 'admin' ? 'ลดสิทธิ์เป็นสมาชิก' : 'เลื่อนเป็นแอดมิน' ?>"
                                            onclick="return confirmAction(event, this.href, 'เปลี่ยนสิทธิ์ผู้ใช้', '<?= htmlspecialchars($user['username']) ?> จะถูกเปลี่ยนสิทธิ์')">
                                            <i class="bi bi-shield<?= $user['role'] === 'admin' ? '-slash' : '-check' ?>"></i>
                                        </a>
                                        <a href="manage_user.php?toggle_status=<?= $user['id'] ?>"
                                            class="btn btn-outline-<?= $user['status'] === 'active' ? 'danger' : 'success' ?> btn-icon-sm"
                                            title="<?= $user['status'] === 'active' ? 'ระงับผู้ใช้' : 'ปลดระงับ' ?>"
                                            onclick="return confirmAction(event, this.href, 'เปลี่ยนสถานะผู้ใช้', '<?= htmlspecialchars($user['username']) ?> จะถูกเปลี่ยนสถานะ')">
                                            <i class="bi bi-<?= $user['status'] === 'active' ? 'slash-circle' : 'check-circle' ?>"></i>
                                        </a>
                                    <?php else : ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                ไม่พบสมาชิก
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>