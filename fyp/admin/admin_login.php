<?php
session_start();
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
    <title>Login-staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('css/background.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            margin: 0;
        }

        /* --- 左上角返回按钮 --- */
        .back-home-btn {
            position: fixed;
            top: 30px;
            left: 30px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.08); 
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .back-home-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            transform: translateX(-5px);
        }

        /* --- 登录卡片 --- */
        .admin-card {
            border: none;
            border-radius: 25px;
            background: rgba(25, 28, 31, 0.92); /* 稍微调深了一点灰色 */
            backdrop-filter: blur(15px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.6);
            padding: 20px;
        }

        .admin-header {
            text-align: center;
            padding: 30px 10px 20px 10px;
        }

        .admin-header h4 {
            color: #ffffff;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: 1.2rem;
        }

        /* 标签文字颜色 */
        .form-label {
            color: #94a3b8; /* 柔和的灰蓝色 */
            font-weight: 500;
        }

        /* --- 输入框样式 --- */
        .form-control {
            border-radius: 12px;
            padding: 14px;
            background: rgba(255, 255, 255, 0.05); /* 降低背景亮度 */
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #e2e8f0; /* 输入后的字颜色 */
            transition: all 0.3s;
        }

        /* 关键修改：调灰色提示文字 */
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.35); /* 调成灰白色/半透明 */
            font-size: 0.9rem;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #dc3545;
            color: #fff;
            box-shadow: none;
        }

        /* 登录按钮 */
        .btn-admin {
            background-color: #dc3545;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            color: white;
            margin-top: 10px;
            transition: all 0.3s;
        }

        .btn-admin:hover {
            background-color: #bb2d3b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
        }
    </style>
</head>
<body>

<a href="../homepage.html" class="back-home-btn">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
    </svg>
    <span>Back</span>
</a>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5 col-xl-4">
            <div class="card admin-card">
                <div class="admin-header">
                    <h4>admin sign in</h4>
                    <p style="color: rgba(255,255,255,0.4); font-size: 0.8rem;">Authorized Access Only</p>
                </div>
                
                <div class="card-body p-4 pt-0">
                    <form action="admin_login_process.php" method="post">
                        <div class="mb-3">
                            <label class="form-label small">Admin Username</label>
                            <input type="text" class="form-control" name="username" placeholder="Enter Admin ID" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small">Secret Password</label>
                            <input type="password" class="form-control" name="password" placeholder="••••••••" required>
                        </div>

                        <button type="submit" name="admin_login" class="btn btn-admin w-100">Sign In</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>