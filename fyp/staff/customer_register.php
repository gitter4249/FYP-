<?php
session_start();
// 1. 引入数据库连接
require '../includes/db.php'; 

if (isset($_POST['register_customer'])) {
    // 2. 接收数据并防止 SQL 注入
    $name  = mysqli_real_escape_string($conn, $_POST['cust_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass  = $_POST['password'];

    // 3. 检查 Email 是否已经存在 (防止重复注册)
    $check_email = $conn->prepare("SELECT email FROM customers WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result = $check_email->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Error: This email is already registered!'); window.history.back();</script>";
        exit;
    }

    // 4. 加密密码 (安全第一)
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

    // 5. 插入数据库 (对应列名: name, email, password)
    // 根据你的 phpMyAdmin 截图，表里没有 customer_id，系统会自动生成自增 id
    $stmt = $conn->prepare("INSERT INTO customers (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashed_password);

    if ($stmt->execute()) {
        echo "<script>alert('Success: New customer account created!'); window.location.href='staff_dashboard.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
} else {
    // 非正常提交，直接退回
    header("Location: staff_dashboard.php");
    exit;
}
?>