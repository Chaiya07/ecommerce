<?php
require_once __DIR__ .'/config/database.php';
$message = "";
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['passeord'],PASSWORD_DEFAULT);
    $avatar = "";
    if (!empty($_FILES['avatar']['name'])) {
        $fileName = time() . "_" . $_FILES['avater']['name'];
        move_uploaded_file($_FILES['avatar']['name'],"upload/avatars/" . $fileName);
        $avatar = $fileName;
    } try {
        $sql = $conn->prepare(
            "INSERT INTO user(username,$email,password,avatar) VALUES(?,?,?,?)"
        );
        $sql->execute([$username,$email,$password,$avatar]);
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
        <input type="meail" name="email" class="form-control mb-3"
        placeholder="Email" required>
        <input type="password"  name="password" class="form-control mb-3"
        placeholder="Password"required>
        <input type="file" name="avatar" class="form-control mb-3"
        <button class="btu btu-success">สมัครสมาชิก</button>
    </form>
    <div class="mt-3m text-success">
        <?= $message  ?>
    </div>
    <?php include 'include/footer.php'; ?>