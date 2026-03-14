<?php
session_start();
require_once("../includes/db.php"); // 确保路径指向你的数据库连接文件

// 获取 fetch 发送过来的 JSON 数据
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($_SESSION['customer_id']) && $data) {
    $customer_id = $_SESSION['customer_id'];
    $new_name = $data['name'];
    $new_phone = $data['phone'];
    $new_address = $data['address'];

    // 执行数据库更新
    $sql = "UPDATE customers SET name = ?, phone = ?, address = ? WHERE customer_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssi", $new_name, $new_phone, $new_address, $customer_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // 更新成功后，同步更新 Session 中的名字
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