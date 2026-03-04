<?php
// 1. 启动 Session（PHP 处理登录必须的第一步）
session_start();

// 2. 模拟逻辑：如果用户已经登录过了，直接送他去 Dashboard，不让他看登录页
if (isset($_SESSION['user_id'])) {
    header("Location: customer_dashboard.php");
    exit;
}

// 3. 接收从处理文件传回来的错误消息（如果有的话）
$error_message = isset($_GET['error']) ? $_GET['error'] : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - YS Aluminum</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-dark: #0f172a;
            --accent-blue: #3b82f6;
            --text-gray: #64748b;
            --bg-glass: rgba(255, 255, 255, 0.92);
        }

        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Inter', sans-serif;
            overflow: hidden;
        }

        .page-container {
            display: flex;
            height: 100vh;
            width: 100%;
            background: linear-gradient(rgba(15, 23, 42, 0.5), rgba(15, 23, 42, 0.5)), 
                        url('YOUR_IMAGE_PATH_HERE.jpg'); 
            background-size: cover;
            background-position: center;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .back-home-btn {
            position: absolute;
            top: 30px;
            left: 30px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: white;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        /* 错误提示样式 */
        .error-box {
            background: #fee2e2;
            color: #dc2626;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            border: 1px solid #fecaca;
        }

        .login-card {
            background: var(--bg-glass);
            backdrop-filter: blur(8px);
            padding: 40px 30px;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.6);
            width: 95%;
            max-width: 420px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.4);
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 30px;
        }

        .logo-group img {
            width: 45px;
            height: auto;
            border-radius: 8px;
            object-fit: contain;
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary-dark);
            letter-spacing: -1px;
            margin: 0;
        }

        .subtitle {
            color: var(--text-gray);
            font-size: 0.95rem;
            margin-bottom: 35px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 22px;
        }

        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--primary-dark);
        }

        input {
            width: 100%;
            padding: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.8);
            transition: 0.3s;
            box-sizing: border-box;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background-color: var(--primary-dark);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn:hover {
            background-color: var(--accent-blue);
            transform: translateY(-2px);
        }

        .forgot-link, .back-to-login {
            display: block;
            margin-top: 20px;
            font-size: 0.85rem;
            color: var(--accent-blue);
            text-decoration: none;
            cursor: pointer;
            font-weight: 500;
        }

        .back-to-login { display: none; }
        .register-note {
            margin-top: 35px;
            padding-top: 25px;
            border-top: 1px solid rgba(0,0,0,0.05);
            font-size: 0.8rem;
            color: #64748b;
        }

        .forgot-mode #login-fields, .forgot-mode #login-btn { display: none; }
        .forgot-mode #forgot-fields, .forgot-mode #reset-btn, .forgot-mode .back-to-login { display: block; }
        #forgot-fields, #reset-btn { display: none; }
    </style>
</head>
<body>

    <div class="page-container">
        
        <a href="../homepage.php" class="back-home-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back 
        </a>

        <div class="login-card" id="card">
            
            <div class="logo-group">
                <img src="../images/ys aluminium.jpg" alt="YS Aluminum Logo">
                <div class="logo-text">YS Aluminium</div>
            </div>

            <p class="subtitle" id="form-subtitle">Customer Management Portal</p>

            <?php if ($error_message): ?>
                <div class="error-box">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form action="customer_login_process.php" method="POST">
                <div id="login-fields">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" name="submit_login" class="btn" id="login-btn">Login</button>
                </div>

                <div id="forgot-fields">
                    <div class="form-group">
                        <label>Registered Email Address</label>
                        <input type="email" name="reset_email" placeholder="Enter your email to reset">
                    </div>
                    <button type="submit" name="submit_reset" class="btn" id="reset-btn">Send Reset Link</button>
                </div>
            </form>

            <span class="forgot-link" id="toggle-forgot" onclick="toggleMode(true)">Forgot Password?</span>
            <span class="back-to-login" onclick="toggleMode(false)">Back to Login</span>

            <div class="register-note">
                New customer? Please contact our <strong>Staff</strong><br> to create your project account.
            </div>
        </div>
    </div>

    <script>
        function toggleMode(isForgot) {
            const card = document.getElementById('card');
            const subtitle = document.getElementById('form-subtitle');
            const toggleForgot = document.getElementById('toggle-forgot');
            if (isForgot) {
                card.classList.add('forgot-mode');
                subtitle.innerText = "Reset Your Password";
                toggleForgot.style.display = "none";
            } else {
                card.classList.remove('forgot-mode');
                subtitle.innerText = "Customer Management Portal";
                toggleForgot.style.display = "block";
            }
        }
    </script>
</body>
</html>