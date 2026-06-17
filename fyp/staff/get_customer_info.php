<?php
session_start();
require '../includes/db.php';
if (!isset($_SESSION['staff_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$customer_id = intval($_GET['id'] ?? 0);
if ($customer_id <= 0) {
    die(json_encode(['success' => false, 'message' => 'Invalid ID']));
}

$query = "SELECT name, email, phone, gender, race, address FROM customers WHERE customer_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$customer = mysqli_fetch_assoc($result);

if ($customer) {
    echo json_encode(['success' => true] + $customer);
} else {
    echo json_encode(['success' => false, 'message' => 'Customer not found']);
}
?>