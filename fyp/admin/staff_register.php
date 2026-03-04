<?php
// 1. 开启 Session
session_start();

// 2. 引入数据库连接 (根据你的文件结构，db.php 在 includes 文件夹中)
require '../includes/db.php';

// 3. 检查是否是通过 POST 提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 获取表单数据
    // 注意：这里的 'name', 'username', 'password' 必须对应你 HTML 表单里的 name 属性
    $staff_name     = mysqli_real_escape_string($conn, $_POST['name']);
    $staff_username = mysqli_real_escape_string($conn, $_POST['username']);
    $staff_password = $_POST['password'];

    // 4. 检查用户名是否已存在（防止重复注册）
    $check_user = "SELECT id FROM staff WHERE username = ?";
    $stmt_check = $conn->prepare($check_user);
    $stmt_check->bind_param("s", $staff_username);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        // 用户名已存在，跳回并显示错误
        header("Location: register_staff.php?error=Username already exists");
        exit();
    }

    // 5. 密码哈希加密 (和你创建 Admin 时用的逻辑一致)
    $hashed_password = password_hash($staff_password, PASSWORD_DEFAULT);

    // 6. 插入数据库
    // 假设你的表字段是 name, username, password
    $sql = "INSERT INTO staff (name, username, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $staff_name, $staff_username, $hashed_password);

    if ($stmt->execute()) {
        // 注册成功，跳回注册页并显示成功消息
        header("Location: register_staff.php?success=Staff registered successfully");
        exit();
    } else {
        // 数据库报错
        header("Location: register_staff.php?error=Registration failed: " . $conn->error);
        exit();
    }

} else {
    // 如果不是 POST 访问，直接踢回注册页面
    header("Location: register_staff.php");
    exit();
}
?>