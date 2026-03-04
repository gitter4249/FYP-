<?php
session_start();
require_once 'db.php'; // 引入数据库连接

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 获取表单提交的数据
    // 使用 mysqli_real_escape_string 防止基础的 SQL 注入
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // 1. 在数据库中查找该 Email 对应的用户
    // 假设你的表名是 customers
    $sql = "SELECT id, name, password FROM customers WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // 2. 验证密码
        // 注意：这里使用的是 password_verify，前提是你存入数据库的密码是用 password_hash 加密的
        // 如果你数据库存的是明文（不推荐），则改用: if ($password === $user['password'])
        if (password_verify($password, $user['password'])) {
            
            // 3. 密码正确，将用户信息存入 Session
            $_SESSION['customer_id'] = $user['id'];
            $_SESSION['customer_name'] = $user['name'];

            // 跳转到仪表盘
            header("Location: customer_dashboard.php");
            exit();
        } else {
            // 密码错误
            header("Location: login.php?error=Incorrect password");
            exit();
        }
    } else {
        // 账号不存在
        header("Location: login.php?error=User not found");
        exit();
    }

} else {
    // 非正常提交访问
    header("Location: login.php");
    exit();
}
?>