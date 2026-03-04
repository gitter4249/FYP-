<?php
require '../includes/db.php'; // 注意路径，如果在 staff 文件夹下需要 ../

// 设置你要创建的测试客户信息
$name = 'John Doe';
$email = 'test@example.com';
$pass = '123456';

// 使用 password_hash 进行加密，这与 login_process.php 里的 password_verify 对应
$hashed_password = password_hash($pass, PASSWORD_DEFAULT);

// 准备插入语句
$stmt = $conn->prepare("INSERT INTO customers (name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $hashed_password);

if ($stmt->execute()) {
    echo "<h3>Customer account created successfully!</h3>";
    echo "Name: " . $name . "<br>";
    echo "Email: " . $email . "<br>";
    echo "Password: " . $pass . "<br>";
    echo "<a href='login.php'>Go to Customer Login</a>";
} else {
    echo "Error: " . $conn->error;
}
?>