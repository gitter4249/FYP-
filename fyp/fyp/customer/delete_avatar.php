<?php
session_start();
require_once("../includes/db.php");
if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$customer_id = $_SESSION['customer_id'];
$sql = "UPDATE customers SET profile_image = NULL WHERE customer_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
}
?>