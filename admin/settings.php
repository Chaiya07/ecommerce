<?php
require_once '../config/database.php';
require_once 'includes/admin_auth.php';

$message = '';

$sql = $conn->query("SELECT * FROM settings LIMIT 1");
$settings = $sql->fetch(PDO::FETCH_ASSOC);

if (!$settings) {
    $conn->exec("INSERT INTO settings (store_name) VALUES ('')");
    $settings = [
        'id' => $conn->lastInsertId(),
        'store_name' => '',
        'logo' => '',
        'favicon' => '',
        'banner' => '',
        'store_address' => '',
        'store_phone' => '',
        'store_email' => '',
        'facebook' => '',
        'line_id' => '',
    ];
}

function uploadImage($file, $oldfile = '')
{
    if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return $oldfile;
    }
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowTypes = ['jpg', 'jpeg', 'png', 'webp', 'ico'];
    if (!in_array($extension, $allowTypes)) {
        return $oldfile;
    }
    $fileName = time() . '_' . uniqid() . '.' . $extension;
    $uploadPath = '../uploads/settings/' . $fileName;
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        if (!empty($oldfile) && file_exists('../uploads/settings/' . $oldfile)) {
            unlink('../uploads/settings/' . $oldfile);
        }
        return $fileName;
    }
    return $oldfile;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $storeName    = trim($_POST['store_name']);
    $storeAddress = trim($_POST['store_address']);
    $storePhone   = trim($_POST['store_phone']);
    $storeEmail   = trim($_POST['store_email']);
    $facebook     = trim($_POST['facebook']);
    $lineId       = trim($_POST['line_id']);

    $logo    = uploadImage($_FILES['logo'] ?? [], $settings['logo']);
    $favicon = uploadImage($_FILES['favicon'] ?? [], $settings['favicon']);
    $banner  = uploadImage($_FILES['banner'] ?? [], $settings['banner']);

    $sql = $conn->prepare("UPDATE settings SET
        store_name = ?, logo = ?, favicon = ?, banner = ?,
        store_address = ?, store_phone = ?, store_email = ?,
        facebook = ?, line_id = ? WHERE id = ?");
    $sql->execute([
        $storeName, $logo, $favicon, $banner,
        $storeAddress, $storePhone, $storeEmail,
        $facebook, $lineId, $settings['id']
    ]);

    $message = 'บันทึกการตั้งค่าเรียบร้อยแล้ว';

    $sql = $conn->query("SELECT * FROM settings LIMIT 1");
    $settings = $sql->fetch(PDO::FETCH_ASSOC);
}

$currenPage = 'settings.php';
include 'includes/header.php';
?>

<style>
    .settings-preview {
        width: 100%;
        max-height: 160px;
        object-fit: contain;
        border: 1px dashed #ced4da;
        border-radius: 8px;
        padding: 8px;
        background: #f8f9fa;
        margin-bottom: .75rem;
    }
    .settings-preview-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100px;
        border: 1px dashed #ced4da;
        border-radius: 8px;
        background: #f8f9fa;
        color: #adb5bd;
        margin-bottom: .75rem;
        font-size: .85rem;
    }
    .settings-section-title {
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: .5rem;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="bi bi-gear-fill me-2"></i>ตั้งค่าร้านค้า</h2>
</div>

<?php if ($message) : ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i><?= $message ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <div class="row g-4">

        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="settings-section-title"><i class="bi bi-shop"></i>ข้อมูลร้านค้า</div>

                    <div class="mb-3">
                        <label class="form-label">ชื่อร้านค้า</label>
                        <input type="text" name="store_name" class="form-control"
                            value="<?= htmlspecialchars($settings['store_name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ที่อยู่ร้านค้า</label>
                        <textarea name="store_address" rows="3" class="form-control"><?= htmlspecialchars($settings['store_address']) ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">เบอร์โทรศัพท์</label>
                            <input type="text" name="store_phone" class="form-control"
                                value="<?= htmlspecialchars($settings['store_phone']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">อีเมล</label>
                            <input type="email" name="store_email" class="form-control"
                                value="<?= htmlspecialchars($settings['store_email']) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="settings-section-title"><i class="bi bi-share"></i>ช่องทางติดต่อ / โซเชียล</div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-facebook me-1"></i>Facebook</label>
                            <input type="text" name="facebook" class="form-control"
                                placeholder="https://facebook.com/..."
                                value="<?= htmlspecialchars($settings['facebook']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-line me-1"></i>Line ID</label>
                            <input type="text" name="line_id" class="form-control"
                                placeholder="@yourshop"
                                value="<?= htmlspecialchars($settings['line_id']) ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="settings-section-title"><i class="bi bi-image"></i>โลโก้ร้านค้า</div>

                    <?php if (!empty($settings['logo'])) : ?>
                        <img src="../uploads/settings/<?= htmlspecialchars($settings['logo']) ?>" class="settings-preview">
                    <?php else : ?>
                        <div class="settings-preview-empty">ยังไม่มีโลโก้</div>
                    <?php endif; ?>

                    <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="settings-section-title"><i class="bi bi-app-indicator"></i>Favicon</div>

                    <?php if (!empty($settings['favicon'])) : ?>
                        <img src="../uploads/settings/<?= htmlspecialchars($settings['favicon']) ?>" class="settings-preview" style="max-height: 80px;">
                    <?php else : ?>
                        <div class="settings-preview-empty">ยังไม่มี Favicon</div>
                    <?php endif; ?>

                    <input type="file" name="favicon" class="form-control" accept=".jpg,.jpeg,.png,.webp,.ico">
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="settings-section-title"><i class="bi bi-image-fill"></i>แบนเนอร์หน้าเว็บ</div>

                    <?php if (!empty($settings['banner'])) : ?>
                        <img src="../uploads/settings/<?= htmlspecialchars($settings['banner']) ?>" class="settings-preview">
                    <?php else : ?>
                        <div class="settings-preview-empty">ยังไม่มีแบนเนอร์</div>
                    <?php endif; ?>

                    <input type="file" name="banner" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-check-circle me-1"></i>บันทึกการตั้งค่า
            </button>
        </div>

    </div>
</form>

<?php include 'includes/footer.php'; ?>