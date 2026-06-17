<?php
session_start();
require '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$customer_id = $_SESSION['customer_id'];

$query = "SELECT * FROM chat_messages 
          WHERE sender_type = 'staff' OR (sender_type = 'customer' AND sender_name = ?)
          ORDER BY created_at ASC 
          LIMIT 100";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $_SESSION['customer_name']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}

echo json_encode(['success' => true, 'messages' => $messages]);
?>