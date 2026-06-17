<?php
session_start();
require '../includes/db.php';
require '../customer/send_email.php';

if (isset($_SESSION['admin'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$error = $success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_email'])) {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = "Please enter your email address.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, full_name FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $admin_id = $row['id'];
            $admin_name = $row['full_name'] ?: $row['username'];
            
            function generateRandomPassword($length = 8) {
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $password = '';
                for ($i = 0; $i < $length; $i++) {
                    $password .= $chars[random_int(0, strlen($chars) - 1)];
                }
                return $password;
            }
            
            $new_password = generateRandomPassword(8);
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $update->bind_param("si", $hashed_password, $admin_id);
            
            if ($update->execute()) {
                $subject = "Your New Admin Password - YS Aluminium";
                $body = "
                    <html>
                    <head><meta charset='UTF-8'></head>
                    <body>
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                            <h2>Hello " . htmlspecialchars($admin_name) . ",</h2>
                            <p>You requested a password reset for your admin account.</p>
                            <p>Your new temporary password is:</p>
                            <p style='font-size: 24px; font-weight: bold; background: #f5f5f5; padding: 10px 20px; border-radius: 8px; display: inline-block;'>" . $new_password . "</p>
                            <p>Please login with this password and <strong>change it immediately</strong> for security reasons.</p>
                            <p>If you did not request this change, please contact the system administrator.</p>
                            <hr style='margin:30px 0; border:none; border-top:1px solid #eee;'>
                            <p style='color:#777; font-size:0.8rem;'>YS Aluminium Sdn Bhd</p>
                        </div>
                    </body>
                    </html>
                ";
                $altBody = "Hello $admin_name,\n\nYour new temporary password is: $new_password\n\nPlease login and change it immediately.\n\nYS Aluminium Team";
                
                if (sendYSAluminiumEmail($email, $admin_name, $subject, $body, $altBody)) {
                    $success = "A new password has been sent to your email address. Please check your inbox (or spam folder).";
                } else {
                    $error = "Failed to send email. Please try again later.";
                }
            } else {
                $error = "Database error. Could not reset password.";
            }
            $update->close();
        } else {
            $error = "No admin account found with that email address.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f6f6f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            padding: 16px;
        }
        .card-custom {
            background: white;
            padding: 40px 35px;
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.06);
            width: 100%;
            max-width: 450px;
        }
        .btn-dark {
            background: #0f172a;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
        }
        .btn-dark:hover {
            background: #334155;
        }
        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
        }
        .form-control:focus {
            border-color: #0f172a;
            box-shadow: none;
        }
        .alert {
            border-radius: 12px;
            font-size: 0.9rem;
        }
        h4 {
            font-weight: 700;
            color: #0f172a;
        }
        a {
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="card-custom">
        <h4 class="text-center mb-2">Reset Admin Password</h4>
        <p class="text-center text-muted small mb-4">Enter your registered email address</p>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="admin@ysaluminium.com" required>
            </div>
            <button type="submit" name="submit_email" class="btn btn-dark">Send New Password</button>
            <div class="text-center mt-3">
                <a href="admin_login.php" class="small text-secondary">← Back to Login</a>
            </div>
        </form>
    </div>
</body>
</html>