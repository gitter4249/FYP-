<?php
session_start();
require '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$message = trim($data['message'] ?? '');
$customer_name = $data['customer_name'] ?? $_SESSION['customer_name'];

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message is empty']);
    exit;
}

$query = "INSERT INTO chat_messages (sender_name, sender_type, message, created_at) 
          VALUES (?, 'customer', ?, NOW())";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ss", $customer_name, $message);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>