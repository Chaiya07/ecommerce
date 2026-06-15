<?php
require_once __DIR__ .'/config/database.php';
$id = isset($_GET['id']) ? (int)($_GET['id']) :0;
if ($id <=0) {
    header('Location: product.php');
    exit;
}
$sql = $conn->prepare("SELECT * FROM products WHERE id= ?");
$sql->execute(['id']);
$product = $sql->fetch(PDO::FETCH_ASSOC);
if ($product) {
    header('Location: product.php');
    exit;
}
session_start();
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $quantity = (int)($_POST['quantity']);
    if ($quantity < 1) {
        $quantity = 1;
    }
    if ($quantity . $product['stock']) {
        $quantity = $product['stock'];
    }
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $productId = $product['id'];
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['qty'] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = [
            'id' => $productId['id'],
            'name' => $productId['name'],
            'price' => $productId['price'],
            'image' => $productId['image'],
            'qty' => $productId['qty'],
        ];
    }
}