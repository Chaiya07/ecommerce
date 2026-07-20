<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$userId = $_SESSION['user']['id'];
$message = '';

$sql = $conn->prepare("SELECT * FROM users WHERE id = ?");
$sql->execute([$userId]);   
$user = $sql->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: logout.php');
    exit();
}

$presetColors = [
    '#0d6efd', // ฟ้า (เดิม)
    '#6610f2', // ม่วง
    '#d63384', // ชมพู
    '#dc3545', // แดง
    '#fd7e14', // ส้ม
    '#198754', // เขียว
    '#20c997', // เขียวมิ้นท์
    '#212529', // ดำ
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username    = trim($_POST['username']);
    $email       = trim($_POST['email']);
    $avatar      = $user['avatar'];
    $bannerColor = $_POST['banner_color'] ?? $user['banner_color'] ?? '#0d6efd';

    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $bannerColor)) {
        $bannerColor = '#0d6efd';
    }

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allow = ['jpg', 'png', 'jpeg', 'webp'];

        if (in_array($extension, $allow)) {
            $newAvatar = time() . '_' . uniqid() . '.' . $extension;
            move_uploaded_file($_FILES['avatar']['tmp_name'], 'uploads/avatars/' . $newAvatar);

            if (!empty($user['avatar']) && file_exists('uploads/avatars/' . $user['avatar'])) {
                unlink('uploads/avatars/' . $user['avatar']);
            }

            $avatar = $newAvatar;
        }
    }

    $passwordSql = '';
    $params = [$username, $email, $avatar, $bannerColor];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $passwordSql = ", password = ?";
        $params[] = $password;
    }

    $params[] = $userId;

    $sql = $conn->prepare("UPDATE users SET username = ?, email = ?, avatar = ?, banner_color = ? $passwordSql WHERE id = ?");
    $sql->execute($params);

    $_SESSION['user']['username'] = $username;
    $_SESSION['user']['email']    = $email;
    $_SESSION['user']['avatar']   = $avatar;

    $message = 'บันทึกข้อมูลเรียบร้อยแล้ว';

    $sql = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $sql->execute([$userId]);
    $user = $sql->fetch(PDO::FETCH_ASSOC);
}

$currentColor = !empty($user['banner_color']) ? $user['banner_color'] : '#0d6efd';

include 'includes/header.php';
?>

<style>
    .profile-cover {
        height: 180px;
        border-radius: 12px 12px 0 0;
        background: <?= htmlspecialchars($currentColor) ?>;
        position: relative;
        transition: background .2s;
    }
    .profile-card {
        border: none;
        border-radius: 12px;
        overflow: visible;
        box-shadow: 0 4px 20px rgba(0,0,0,.08);
        margin-bottom: 2rem;
    }
    .profile-avatar-wrap {
        position: relative;
        width: 130px;
        margin: -65px auto 0;
    }
    .profile-avatar-wrap img,
    .profile-avatar-wrap .avatar-placeholder {
        width: 130px;
        height: 130px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,.15);
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #adb5bd;
        font-size: 2.5rem;
    }
    .avatar-upload-badge {
        position: absolute;
        bottom: 6px;
        right: 6px;
        width: 36px;
        height: 36px;
        background: #0d6efd;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        border: 3px solid #fff;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0,0,0,.2);
        transition: .2s;
    }
    .avatar-upload-badge:hover {
        background: #0b5ed7;
        transform: scale(1.08);
    }
    .avatar-upload-badge input[type="file"] {
        display: none;
    }
    .profile-name {
        font-weight: 700;
        margin-top: 1rem;
    }
    .profile-file-hint {
        font-size: .85rem;
        color: #6c757d;
    }
    .color-swatch {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-block;
        cursor: pointer;
        border: 3px solid transparent;
        transition: .15s;
    }
    .color-swatch:hover {
        transform: scale(1.12);
    }
    .color-swatch.selected {
        border-color: #212529;
        box-shadow: 0 0 0 2px #fff inset;
    }
    .color-swatch-custom {
        width: 32px;
        height: 32px;
        padding: 0;
        border: none;
        border-radius: 50%;
        overflow: hidden;
        cursor: pointer;
    }
    .color-swatch-custom::-webkit-color-swatch-wrapper { padding: 0; }
    .color-swatch-custom::-webkit-color-swatch { border: 3px solid #fff; border-radius: 50%; }
</style>

<div class="row justify-content-center">
    <div class="col-lg-7">

        <?php if ($message) : ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" id="profileForm">

            <div class="card profile-card">
                <div class="profile-cover" id="profileCover"></div>

                <div class="profile-avatar-wrap">
                    <?php if (!empty($user['avatar'])) : ?>
                        <img src="uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="avatar">
                    <?php else : ?>
                        <div class="avatar-placeholder">
                            <i class="bi bi-person-fill"></i>
                        </div>
                    <?php endif; ?>

                    <label class="avatar-upload-badge" title="เปลี่ยนรูปโปรไฟล์">
                        <i class="bi bi-camera-fill"></i>
                        <input type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp"
                            onchange="document.getElementById('avatarFileHint').textContent = this.files[0]?.name || ''">
                    </label>
                </div>

                <div class="text-center">
                    <h4 class="profile-name"><?= htmlspecialchars($user['username']) ?></h4>
                    <p class="profile-file-hint" id="avatarFileHint"></p>
                </div>

                <div class="card-body px-4 pb-4">

                    <div class="mb-4">
                        <label class="form-label d-block mb-2">สีพื้นหลังโปรไฟล์</label>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <?php foreach ($presetColors as $color) : ?>
                                <span class="color-swatch <?= strcasecmp($color, $currentColor) === 0 ? 'selected' : '' ?>"
                                    style="background: <?= htmlspecialchars($color) ?>;"
                                    data-color="<?= htmlspecialchars($color) ?>"
                                    onclick="selectColor('<?= htmlspecialchars($color) ?>', this)"></span>
                            <?php endforeach; ?>

                            <input type="color" class="color-swatch-custom" id="customColor"
                                value="<?= htmlspecialchars($currentColor) ?>"
                                onchange="selectColor(this.value, null)" title="เลือกสีเอง">
                        </div>
                        <input type="hidden" name="banner_color" id="bannerColorInput" value="<?= htmlspecialchars($currentColor) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ชื่อผู้ใช้</label>
                        <input type="text" name="username" class="form-control"
                            value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">อีเมล</label>
                        <input type="email" name="email" class="form-control"
                            value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">รหัสผ่านใหม่ (เว้นว่างถ้าไม่ต้องการเปลี่ยน)</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-2">
                        <i class="bi bi-check-circle me-1"></i>บันทึกข้อมูล
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>

<script>
function selectColor(color, el) {
    document.getElementById('profileCover').style.background = color;
    document.getElementById('bannerColorInput').value = color;
    document.getElementById('customColor').value = color;

    document.querySelectorAll('.color-swatch').forEach(function (s) {
        s.classList.remove('selected');
    });
    if (el) {
        el.classList.add('selected');
    }
}
</script>

<?php include 'includes/footer.php'; ?>