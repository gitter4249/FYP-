<?php
session_start();
// 1. 引入数据库连接 (确保路径正确)
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
        $check_email->close();
        exit;
    }
    $check_email->close();

    // 4. 加密密码 (安全第一)
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

    // 5. 插入数据库
    // 注意：既然你已经把 id 改名为 customer_id，系统会自动处理自增。
    // 我们只需要插入 name, email, password 即可。
    $stmt = $conn->prepare("INSERT INTO customers (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashed_password);

    if ($stmt->execute()) {
        // 注册成功，跳转回 Dashboard
        echo "<script>alert('Success: New customer account created!'); window.location.href='staff_dashboard.php';</script>";
    } else {
        // 报错处理
        echo "<script>alert('Error: Could not register customer. " . $conn->error . "'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    // 非正常提交，直接退回
    header("Location: staff_dashboard.php");
    exit;
}
?>