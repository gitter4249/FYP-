<?php

session_start();
include "../includes/db.php";

$error = ""; 

if (isset($_POST['staff_login'])) {
    $input_user = mysqli_real_escape_string($conn, $_POST['staff_id']);
    $password = $_POST['password'];

    // 根据你的截图，字段名是 staff_id
    $sql = "SELECT * FROM staff WHERE staff_id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $input_user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // 验证加密密码
            if (password_verify($password, $row['password'])) {
                // 登录成功，存入 Session
                $_SESSION['staff_id'] = $row['staff_id'];
                $_SESSION['staff_name'] = $row['staff_name'];
                
                // 确保没有输出后执行跳转
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
    <style>
        body {
            background: #f6f6f6;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 400px;
        }
        .login-card h2 {
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }
        .login-card p {
            color: #777;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #eee;
            margin-bottom: 20px;
        }
        .btn-login {
            background: black;
            color: white;
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
            border: none;
            transition: 0.3s;
        }
        .btn-login:hover {
            background: #333;
        }
        .back-home {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #999;
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Staff Login</h2>
    <p>Please enter your credentials to access the staff portal.</p>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger py-2 small text-center"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-1">
            <label class="form-label small fw-bold">Staff ID</label>
            <input type="text" name="staff_id" class="form-control" placeholder="e.g. staff01" required>
        </div>
        <div class="mb-1">
            <label class="form-label small fw-bold">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>
        <button type="submit" name="staff_login" class="btn btn-login">Sign In</button>
    </form>

    <a href="../homepage.html" class="back-home">← Back to Homepage</a>
</div>

</body>
</html>