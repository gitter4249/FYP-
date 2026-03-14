<?php
session_start();
// 1. 引入数据库连接
require '../includes/db.php'; 

if (isset($_POST['register_customer'])) {
    // 2. 接收数据并防止 SQL 注入
    $name    = mysqli_real_escape_string($conn, $_POST['cust_name']);
    $email   = mysqli_real_escape_string($conn, $_POST['email']);
    $phone   = mysqli_real_escape_string($conn, $_POST['phone']);   // 新增接收
    $gender  = mysqli_real_escape_string($conn, $_POST['gender']);  // 新增接收
    $race    = mysqli_real_escape_string($conn, $_POST['race']);    // 新增接收
    $address = mysqli_real_escape_string($conn, $_POST['address']); // 新增接收
    $pass    = $_POST['password'];

    // 3. 检查 Email 是否已经存在
    $check_email = $conn->prepare("SELECT email FROM customers WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result = $check_email->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Error: This email is already registered!'); window.history.back();</script>";
        $check_email->close();
        exit;
    }
    $check_email->close();

    // 4. 加密密码
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

    // 5. 插入数据库 (核心修复：把缺失的字段全部加上)
    // 这里的 SQL 语句必须和你数据库的列名完全对应
    $stmt = $conn->prepare("INSERT INTO customers (name, email, phone, gender, race, address, password, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
    
    // 对应上面的 7 个问号 (s 代表 string)
    $stmt->bind_param("sssssss", $name, $email, $phone, $gender, $race, $address, $hashed_password);

    if ($stmt->execute()) {
        echo "<script>alert('Success: New customer account created!'); window.location.href='staff_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error: Could not register customer. " . $conn->error . "'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: staff_dashboard.php");
    exit;
}
?>