<?php
session_start();
require '../includes/db.php';

// 获取 token
$token = isset($_GET['token']) ? $_GET['token'] : '';
$error = '';
$success = '';

if (empty($token)) {
    die("Invalid reset link.");
}

$stmt = $conn->prepare("SELECT id, username, email FROM admins WHERE reset_token = ? AND reset_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $admin_id = $row['id'];
    $admin_username = $row['username'];
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $new_password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters.";
        } elseif ($new_password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE admins SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
            $update->bind_param("si", $hashed, $admin_id);
            if ($update->execute()) {
                $success = "Password has been reset successfully. You can now login.";
                header("refresh:3;url=admin_login.php");
            } else {
                $error = "Database error. Please try again.";
            }
        }
    }
} else {
    $error = "The reset link is invalid or has expired. Please request a new one.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f6f6f6;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .card {
            max-width: 450px;
            width: 100%;
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }
        .card-header {
            background: white;
            border-bottom: none;
            text-align: center;
            padding-top: 30px;
        }
        .btn-dark {
            background: #000;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
        }
        .btn-dark:hover {
            background: #333;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <h2>Set New Password</h2>
        <p class="text-muted"><?php echo isset($admin_username) ? "For: " . htmlspecialchars($admin_username) : ""; ?></p>
    </div>
    <div class="card-body p-4">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?> Redirecting to login...</div>
        <?php endif; ?>
        
        <?php if (!$success && isset($admin_id)): ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required minlength="6">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-dark w-100">Reset Password</button>
        </form>
        <?php elseif (!$success && !isset($admin_id)): ?>
            <a href="admin_forgot_password.php" class="btn btn-dark w-100">Request New Link</a>
        <?php endif; ?>
        <div class="text-center mt-3">
            <a href="admin_login.php" style="color: #666; text-decoration: none;">← Back to Login</a>
        </div>
    </div>
</div>
</body>
</html>