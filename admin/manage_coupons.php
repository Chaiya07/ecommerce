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
    $code = strtoupper(trim($_POST['code']));
    $discountType = $_POST['discount_type'];
    $discountValue = (float) $_POST['discount_value'];
    $usageLimit = !empty($_POST['usage_limit']) ? (int) $_POST['usage_limit'] : null;
    $expiryDate = !empty($_POST['expiry_date']) ? (int) $_POST['expiry_date'] : null;
    $status = $_POST['status'];
    $sql = $conn->prepare("INSERT INTO coupons(code,discount_type,discount_value,usage_limit,
    expiry_date,status) VALUES (?,?,?,?,?,?)");
    $sql->execute([$code,$discountType,$discountValue,$usageLimit,$expiryDate,$status]);
    header('Location: manage_coupons.php');
    exit();
}
$sql = $conn->prepare("SELECT * FROM coupons WHERE code LIKE ? ORDER BY id DESC ");
$sql->execute([ "%{$keyword}%" ]);
$coupons = $sql->fetchAll(PDO::FETCH_ASSOC);
include 'includes/header.php';
?>