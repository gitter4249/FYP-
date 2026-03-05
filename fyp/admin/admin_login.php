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
            background: #d7d7d7; /* 更改为较深的背景，以便突出半透明按钮效果 */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            margin: 0;
            position: relative;
        }

        /* --- 修正后的左上角返回按钮 (匹配图片效果) --- */
        .back-home {
            position: absolute;
            top: 30px;
            left: 30px;
            text-decoration: none;
            color: #000000; /* 文字改为白色 */
            font-size: 16px;
            font-weight: 600;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            /* 关键样式：背景与圆角 */
            background: rgb(255, 255, 255); 
            padding: 10px 20px;
            border-radius: 15px;
            border: 1px solid rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(5px); /* 可选：增加磨砂玻璃效果 */
        }

        .back-home:hover {
            background: rgba(255, 255, 255, 0.25);
            color: #fff;
            transform: translateX(-5px);
        }

        .back-home svg {
            stroke-width: 2.5; /* 加粗图标线条 */
        }

        /* --- 登录卡片样式 --- */
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
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
    </style>
</head>
<body>

<a href="../homepage.php" class="back-home">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
        <path d="M19 12H5M12 19l-7-7 7-7"/>
    </svg>
    Back
</a>

<div class="login-card">
    <h2>Admin Login</h2>
    <p>Authorized Access Only - Please sign in to continue.</p>

    <form action="admin_login_process.php" method="POST">
        <div class="mb-3">
            <label class="form-label">Admin Username</label>
            <input type="text" name="username" class="form-control" placeholder="Enter Admin ID" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Secret Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        
        <button type="submit" name="admin_login" class="btn btn-login">Sign In</button>
    </form>
</div>

</body>
</html>
