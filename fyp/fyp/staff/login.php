<?php
session_start();
include "../includes/db.php";

// 处理登录（保持原有逻辑）
$error = "";
if (isset($_POST['staff_login'])) {
    $input_email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM staff WHERE email = ? AND status = 1";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $input_email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['staff_logged_in'] = true;
                $_SESSION['staff_id'] = $row['id'];
                $_SESSION['staff_name'] = $row['staff_name'];
                header("Location: staff_dashboard.php");
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "Account inactive or email not found!";
        }
    } else {
        $error = "Database query error!";
    }
}

// 从 URL 获取消息（重置成功/错误）
$url_error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : "";
$url_success = "";
if (isset($_GET['reset_success'])) {
    $url_success = "A new password has been sent to your email address.";
} elseif (isset($_GET['reset_error'])) {
    $url_error = htmlspecialchars($_GET['reset_error']);
} elseif (isset($_GET['msg'])) {
    $url_success = htmlspecialchars($_GET['msg']);
}

$display_error = !empty($error) ? $error : $url_error;
$display_success = $url_success;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Staff Login | YS Aluminium</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-dark: #0f172a;
            --accent-blue: #3b82f6;
            --text-gray: #64748b;
            --bg-glass: rgba(255, 255, 255, 0.92);
            --error-red: #dc2626;
            --success-green: #059669;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { height: 100%; font-family: 'Inter', sans-serif; overflow: hidden; }
        .page-container {
            display: flex;
            height: 100vh;
            width: 100%;
            background: linear-gradient(rgba(15,23,42,0.6), rgba(15,23,42,0.6)), url('../images/login-bg.jpg');
            background-size: cover;
            background-position: center;
            justify-content: center;
            align-items: center;
            position: relative;
            flex-direction: column;
            padding: 20px;
        }
        .msg-box { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; text-align: left; border: 1px solid; }
        .error-box { background: #fee2e2; color: var(--error-red); border-color: #fecaca; }
        .success-box { background: #d1fae5; color: var(--success-green); border-color: #a7f3d0; }
        .login-card {
            background: var(--bg-glass);
            backdrop-filter: blur(8px);
            padding: 40px 30px;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.6);
            width: 95%;
            max-width: 400px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.4);
            animation: fadeIn 0.8s ease;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .logo-group { display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 25px; }
        .logo-group img { width: 60px; height: auto; border-radius: 8px; object-fit: cover; }
        .logo-text { font-size: 1.6rem; font-weight: 800; color: var(--primary-dark); letter-spacing: -1px; }
        .subtitle { color: var(--text-gray); font-size: 0.9rem; margin-bottom: 30px; }
        .form-group { text-align: left; margin-bottom: 20px; }
        label { display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 8px; color: var(--primary-dark); text-transform: uppercase; letter-spacing: 0.5px; }
        input { width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 1rem; background: white; transition: 0.3s; box-sizing: border-box; }
        input:focus { outline: none; border-color: var(--accent-blue); box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
        .btn { width: 100%; padding: 14px; background-color: var(--primary-dark); color: white; border: none; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn:hover { background-color: var(--accent-blue); transform: translateY(-2px); }
        .forgot-link, .back-to-login { display: block; margin-top: 20px; font-size: 0.85rem; color: var(--accent-blue); text-decoration: none; cursor: pointer; font-weight: 600; }
        .back-to-login { display: none; }
        #forgot-fields { display: none; }
        .forgot-mode #login-fields { display: none; }
        .forgot-mode #forgot-fields { display: block; }
        .forgot-mode .back-to-login { display: block; }
        .info-note { margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(0,0,0,0.05); font-size: 0.8rem; color: #64748b; line-height: 1.5; }
        .close-wrapper { text-align: center; margin-top: 25px; }
        .close-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            border-radius: 40px;
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .close-btn:hover { background: rgba(0, 0, 0, 0.7); transform: translateY(-2px); }
    </style>
</head>
<body>
<div class="page-container">
    <div class="login-card" id="card">
        <div class="logo-group">
            <img src="../images/ys.jpg" alt="YS Logo">
            <div class="logo-text">YS Aluminium</div>
        </div>
        <p class="subtitle" id="form-subtitle">Staff Access Portal</p>

        <?php if ($display_error): ?>
            <div class="msg-box error-box"><strong>Error:</strong> <?php echo $display_error; ?></div>
        <?php endif; ?>
        <?php if ($display_success): ?>
            <div class="msg-box success-box"><strong>Success:</strong> <?php echo $display_success; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <!-- 登录表单 -->
            <div id="login-fields">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" id="login_email" placeholder="staff@ysaluminium.com" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" id="login_password" placeholder="••••••••" required>
                </div>
                <button type="submit" name="staff_login" class="btn">Sign In</button>
                <span class="forgot-link" onclick="toggleMode(true)">Forgot Password?</span>
            </div>

            <!-- 重置密码表单 -->
            <div id="forgot-fields">
                <div class="form-group">
                    <label>Registered Email</label>
                    <input type="email" name="reset_email" id="reset_email" placeholder="Enter your email to reset" required>
                </div>
                <button type="submit" name="staff_reset" class="btn">Send New Password</button>
                <span class="back-to-login" onclick="toggleMode(false)">Back to Login</span>
            </div>
        </form>

        <div class="info-note">
            Internal staff use only. Contact IT if you face issues.
        </div>
    </div>

    <div class="close-wrapper">
        <a href="../homepage.php" class="close-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Close
        </a>
    </div>
</div>

<script>
    function toggleMode(isForgot) {
        const card = document.getElementById('card');
        const subtitle = document.getElementById('form-subtitle');
        const loginEmail = document.getElementById('login_email');
        const loginPass = document.getElementById('login_password');
        const resetEmail = document.getElementById('reset_email');
        if (isForgot) {
            card.classList.add('forgot-mode');
            subtitle.innerText = "Reset Your Password";
            loginEmail.required = false;
            loginPass.required = false;
            resetEmail.required = true;
        } else {
            card.classList.remove('forgot-mode');
            subtitle.innerText = "Staff Access Portal";
            loginEmail.required = true;
            loginPass.required = true;
            resetEmail.required = false;
        }
    }
    window.onload = () => toggleMode(false);
</script>
</body>
</html>