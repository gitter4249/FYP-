<?php
session_start();
require '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['staff_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$customer_name = isset($_GET['customer_name']) ? $_GET['customer_name'] : '';

if (empty($customer_name)) {
    echo json_encode(['success' => false, 'message' => 'Customer name required']);
    exit;
}

$query = "SELECT * FROM chat_messages 
          WHERE sender_name = ? OR (sender_type = 'staff' AND message IN (SELECT message FROM chat_messages WHERE sender_name = ?))
          ORDER BY created_at ASC 
          LIMIT 200";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ss", $customer_name, $customer_name);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}

echo json_encode(['success' => true, 'messages' => $messages]);
?>