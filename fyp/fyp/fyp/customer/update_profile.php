<?php
session_start();
require_once("../includes/db.php"); 

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($_SESSION['customer_id']) && $data) {
    $customer_id = $_SESSION['customer_id'];
    $new_name = $data['name'];
    $new_phone = $data['phone'];
    $new_address = $data['address'];

    $sql = "UPDATE customers SET name = ?, phone = ?, address = ? WHERE customer_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssi", $new_name, $new_phone, $new_address, $customer_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['name'] = $new_name;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'SQL prepare failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>