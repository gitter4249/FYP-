<?php
session_start();
// 如果已经登录 admin，直接跳转 dashboard
if(isset($_SESSION['admin'])){
    header("Location: admin_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | YS Aluminium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f6f6f6; /* 与 Staff 界面一致的浅灰色背景 */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            margin: 0;
        }

        /* --- 登录卡片样式 --- */
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 400px;
        }

        .login-card h2 {
            font-weight: 700;
            margin-bottom: 10px;
            text-align: center;
            color: #000;
        }

        .login-card p {
            color: #777;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }

        /* --- 表单控件 --- */
        .form-label {
            font-size: 13px;
            font-weight: 700;
            color: #000;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #eee;
            margin-bottom: 20px;
            background-color: #fff;
            color: #000;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #ddd;
        }

        /* --- 黑色登录按钮 --- */
        .btn-login {
            background: #000;
            color: white;
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #333;
            color: white;
        }

        /* --- 返回按钮 --- */
        .back-home {
            display: block;
            text-align: center;
            margin-top: 25px;
            text-decoration: none;
            color: #999;
            font-size: 13px;
            transition: 0.3s;
        }

        .back-home:hover {
            color: #000;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Admin Login</h2>
    <p>Authorized Access Only - Please sign in to continue.</p>

    <form action="admin_login_process.php" method="POST">
        <div class="mb-1">
            <label class="form-label">Admin Username</label>
            <input type="text" name="username" class="form-control" placeholder="Enter Admin ID" required>
        </div>
        <div class="mb-1">
            <label class="form-label">Secret Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        
        <button type="submit" name="admin_login" class="btn btn-login">Sign In</button>
    </form>

    <a href="../homepage.php" class="back-home">← Back to Homepage</a>
</div>

</body>
</html>