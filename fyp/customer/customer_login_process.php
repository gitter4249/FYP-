<?php
// 1. 启动 Session
session_start();

// 2. 引入数据库连接文件 (请确保路径正确)
require '../includes/db.php';

if (isset($_POST['submit_login'])) {
    // 接收并转义输入，防止基本的 SQL 注入（虽然用了预处理，但这仍是好习惯）
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // 3. 使用预处理语句查询用户
    $sql = "SELECT * FROM customers WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // 4. 验证密码
            // 注意：如果你的数据库存的是明文，请用 ($password == $row['password'])
            // 如果是加密过的，请保持 password_verify
            if (password_verify($password, $row['password']) || $password == $row['password']) {
                
                // --- 状态检查 ---
                if ($row['status'] !== 'active') {
                    header("Location: customer_login.php?error=Your account is inactive. Please contact staff.");
                    exit;
                }

                // 5. 关键修复：统一 Session 键名
                // 我们统一使用 'customer_id' 和 'name'，方便后面 Dashboard 调用
                $_SESSION['customer_id'] = $row['customer_id'];
                $_SESSION['name'] = $row['name']; 
                $_SESSION['email'] = $row['email'];
                
                // 登录成功，跳转到仪表盘
                header("Location: customer_dashboard.php");
                exit;

            } else {
                // 密码错误
                header("Location: customer_login.php?error=Invalid email or password.");
                exit;
            }
        } else {
            // 邮箱不存在
            header("Location: customer_login.php?error=Invalid email or password.");
            exit;
        }
    } else {
        // 数据库语句错误
        header("Location: customer_login.php?error=System error. Please try again later.");
        exit;
    }
} else {
    // 如果不是通过 POST 提交访问的，退回登录页
    header("Location: customer_login.php");
    exit;
}
?>