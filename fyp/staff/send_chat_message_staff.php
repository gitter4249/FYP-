<?php
session_start();
require '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['staff_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$customer_name = $data['customer_name'] ?? '';
$message = trim($data['message'] ?? '');
$staff_name = $data['staff_name'] ?? $_SESSION['staff_name'] ?? 'Staff';

if (empty($customer_name) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$query = "INSERT INTO chat_messages (sender_name, sender_type, message, created_at) 
          VALUES (?, 'staff', ?, NOW())";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ss", $staff_name, $message);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>