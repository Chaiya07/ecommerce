<?php
require_once __DIR__ . '/config/database.php';
session_start();

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $avatar   = "";

    if (!empty($_FILES['avatar']['name'])) {
        $fileName = time() . "_" . $_FILES['avatar']['name'];
        move_uploaded_file($_FILES['avatar']['tmp_name'], "uploads/avatars/" . $fileName);
        $avatar = $fileName;
    }

    try {
        $sql = $conn->prepare("INSERT INTO users(username, email, password, avatar) VALUES(?, ?, ?, ?)");
        $sql->execute([$username, $email, $password, $avatar]);
        $message = "สมัครสมาชิกสำเร็จ";
    } catch (Exception $e) {
        $message = "Email ถูกใช้งานแล้ว";
    }
}

include 'includes/header.php';
?>
<h2>สมัครสมาชิก</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="username" class="form-control mb-3"
        placeholder="Username" required>
    <input type="email" name="email" class="form-control mb-3"
        placeholder="Email" required>
    <input type="password" name="password" class="form-control mb-3"
        placeholder="Password" required>
    <input type="file" name="avatar" class="form-control mb-3">
    <button class="btn btn-success">สมัครสมาชิก</button>
</form>
<div class="mt-3 text-success">
    <?= $message ?>
</div>
<?php include 'includes/footer.php'; ?>
