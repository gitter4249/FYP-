<?php
session_start();
// 1. 包含数据库连接文件
require '../includes/db.php'; 

// 2. 检查是否是通过点击注册按钮提交过来的
if (isset($_POST['register_customer'])) {
    
    // 3. 接收并清理数据（防止 SQL 注入）
    $cust_id = mysqli_real_escape_string($conn, $_POST['cust_id']);
    $name    = mysqli_real_escape_string($conn, $_POST['cust_name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $pass    = $_POST['password'];

    // 4. 检查 Customer ID 是否已经存在
    $check_stmt = $conn->prepare("SELECT customer_id FROM customers WHERE customer_id = ?");
    $check_stmt->bind_param("s", $cust_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // 如果 ID 已存在，弹窗并退回
        echo "<script>alert('Error: Customer ID [$cust_id] already exists!'); window.history.back();</script>";
        exit;
    }

    // 5. 加密密码 (使用 PHP 官方推荐的哈希方式)
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

    // 6. 准备插入 SQL 语句
    // 注意：这里的字段名 customer_id, name, contact, password 必须跟你数据库表里的一模一样
    $stmt = $conn->prepare("INSERT INTO customers (customer_id, name, contact, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $cust_id, $name, $contact, $hashed_password);

    // 7. 执行并反馈结果
    if ($stmt->execute()) {
        echo "<script>alert('Success: New customer account created!'); window.location.href='staff_dashboard.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }

    // 8. 关闭连接
    $stmt->close();
    $conn->close();
} else {
    // 如果有人尝试直接访问这个 PHP 文件，直接踢回 Dashboard
    header("Location: staff_dashboard.php");
    exit;
}
?>