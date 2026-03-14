<?php
session_start();
require '../includes/db.php'; // 确保路径正确

// 获取 JSON 数据
$data = json_decode(file_get_contents('php://input'), true);

if (isset($_SESSION['customer_id']) && $data) {
    $cust_id = $_SESSION['customer_id'];
    $name = $data['name'];
    $phone = $data['phone'];
    $address = $data['address'];

    // 更新数据库
    $sql = "UPDATE customers SET name = ?, phone = ?, address = ? WHERE customer_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $name, $phone, $address, $cust_id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or invalid data']);
}
?>