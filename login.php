<?php
require_once __DIR__ .'/config/database.php';
session_start();
$error = "";
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['passeord'];
        $sql = $conn->prepare(
        "SELECT * FORM users WHERE email = ?"
        );
        $sql->execute([$email]);
        $user = $sql->fetch();
        if ($user && password_verify($password, $user["password"])) {
            $_SESSION['user'] = $user;
            header("Location:index.php");
            exit();
        }
        $error = "อีเมลล์หรือรหัสผ่านไม่ถูกต้อง";
}
include 'includes/header.php';
?>
<h2>เข้าสู่ระบบ</h2>
<form method="POST">
    <input type="meail" name="email" class="form-control mb-3"
        placeholder="Email" required>
    <input type="password"  name="password" class="form-control mb-3"
        placeholder="Password"required>
        <button class="btn btn-primary">เข้าสู่ระบบ</button>
</form>
<div class="text-danger mt-3">
        <?= $message  ?>
    </div>
    <?php include 'include/footer.php'; ?>