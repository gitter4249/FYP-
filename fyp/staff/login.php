<?php
session_start();
include "../includes/db.php";

$error = ""; 

if (isset($_POST['staff_login'])) {
    $input_user = mysqli_real_escape_string($conn, $_POST['staff_id']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM staff WHERE staff_id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $input_user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                // --- 修正部分开始 ---
                $_SESSION['staff_logged_in'] = true; // 【关键点 1】必须设置这个，Dashboard 才能放行
                $_SESSION['staff_id'] = $row['staff_id'];
                $_SESSION['staff_name'] = $row['staff_name'];
                // --- 修正部分结束 ---
                
                header("Location: staff_dashboard.php");
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "Staff ID not found!";
        }
    } else {
        $error = "Database query error!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login | YS Aluminium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f6f6f6;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            margin: 0;
        }

        /* --- 左上角返回按钮样式 --- */
        .back-home-btn {
            position: fixed; 
            top: 30px;
            left: 30px; /* 关键点：设置在左侧 */
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.8); /* 稍微白一点，在浅色背景上更清爽 */
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            color: #475569;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }

        .back-home-btn:hover {
            background: #fff;
            transform: translateX(-5px); /* 悬停时往左动一点，引导视觉 */
            color: #0f172a;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        /* --- 登录卡片样式 --- */
        .login-card {
            background: white;
            padding: 45px 40px;
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.06);
            width: 95%;
            max-width: 420px;
            animation: fadeIn 0.7s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card h2 {
            font-weight: 800;
            margin-bottom: 8px;
            text-align: center;
            color: #0f172a;
            letter-spacing: -1px;
        }
        .login-card p.subtitle {
            color: #64748b;
            text-align: center;
            margin-bottom: 35px;
            font-size: 14px;
        }
        .form-label {
            color: #0f172a;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 8px;
        }
        .form-control {
            border-radius: 12px;
            padding: 13px;
            border: 1px solid #e2e8f0;
            margin-bottom: 22px;
            transition: 0.3s;
        }
        .form-control:focus {
            border-color: #0f172a;
            box-shadow: none;
            background-color: #fff;
        }
        .btn-login {
            background: #0f172a;
            color: white;
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            border: none;
            transition: 0.3s;
            margin-top: 5px;
        }
        .btn-login:hover {
            background: #334155;
            transform: translateY(-2px);
            color: white;
        }
    </style>
</head>
<body>

<a href="../homepage.php" class="back-home-btn">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
    </svg>
    Back
</a>

<div class="login-card">
    <h2>Staff Login</h2>
    <p class="subtitle">Please enter your credentials to access the staff portal.</p>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger py-2 small text-center" style="border-radius: 10px; border: none; background-color: #fee2e2; color: #dc2626;">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label class="form-label">Staff ID</label>
            <input type="text" name="staff_id" class="form-control" placeholder="e.g. staff01" required>
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>
        <button type="submit" name="staff_login" class="btn btn-login">Sign In</button>
    </form>
</div>

</body>
</html>