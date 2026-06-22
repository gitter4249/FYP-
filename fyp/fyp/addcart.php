<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$productId = isset($_GET['product_id']) ? $_GET['product_id'] : null;

if ($productId) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]++;
    } else {
        $_SESSION['cart'][$productId] = 1;
    }
}

header("Location: cart.php");
exit();
?>