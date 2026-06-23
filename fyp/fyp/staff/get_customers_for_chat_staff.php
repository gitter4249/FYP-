<?php
session_start();
require '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['staff_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$query = "SELECT DISTINCT customer_id, name, email FROM customers WHERE status = 1 ORDER BY name";
$result = mysqli_query($conn, $query);

$customers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $customers[] = $row;
}

echo json_encode(['success' => true, 'customers' => $customers]);
?>