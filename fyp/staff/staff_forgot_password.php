<?php
session_start();
require '../includes/db.php';
require '../customer/send_email.php';

$error = $success = "";

if (isset($_POST['submit_email'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $check = mysqli_query($conn, "SELECT id, staff_name FROM staff WHERE email = '$email' AND status = 1");
    if (mysqli_num_rows($check) == 1) {
        $row = mysqli_fetch_assoc($check);
        $staff_id = $row['id'];
        $staff_name = $row['staff_name'];

        $new_password = substr(md5(uniqid(rand(), true)), 0, 8);
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $update = mysqli_query($conn, "UPDATE staff SET password = '$hashed_password' WHERE id = $staff_id");

        if ($update) {
            $subject = "Your New Staff Portal Password - YS Aluminium";
            $body = "
                <html>
                <head><meta charset='UTF-8'></head>
                <body>
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                        <h2>Hello " . htmlspecialchars($staff_name) . ",</h2>
                        <p>You requested a password reset for your staff account.</p>
                        <p>Your new temporary password is:</p>
                        <p style='font-size: 24px; font-weight: bold; background: #f5f5f5; padding: 10px 20px; border-radius: 8px; display: inline-block;'>" . $new_password . "</p>
                        <p>Please login with this password and change it immediately for security reasons.</p>
                        <p>If you did not request this change, please contact the administrator.</p>
                        <hr style='margin:30px 0; border:none; border-top:1px solid #eee;'>
                        <p style='color:#777; font-size:0.8rem;'>YS Aluminium Sdn Bhd</p>
                        <hr style='border: none; border-top: 1px solid #e4e4e7; margin: 30px 0 15px;'>
                        <p style='color: #555; font-size: 0.9rem;'>If you have any questions, please contact us:</p>
                        <p style='color: #555; font-size: 0.9rem;'><strong>Phone:</strong> +60 18-366 5756<br><strong>Email:</strong> yongshengalu@gmail.com</p>
                        <p style='color: #8e8e93; font-size: 13px; text-align: center; margin-bottom: 0;'>YS Aluminium Sdn Bhd</p>
                    </div>
                </body>
                </html>
            ";
            $altBody = "Hello $staff_name,\n\nYour new temporary password is: $new_password\n\nPlease login and change it immediately.\n\nYS Aluminium Team";

            if (sendYSAluminiumEmail($email, $staff_name, $subject, $body, $altBody)) {
                $success = "A new password has been sent to your email address.";
            } else {
                $error = "Failed to send email. Please try again later.";
            }
        } else {
            $error = "Database error. Could not update password.";
        }
    } else {
        $error = "No active staff account found with that email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
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
            transition: all 0.2s ease;
        }
        .btn-dark {
            background: #0f172a;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: background 0.2s;
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
        <h4 class="text-center mb-2">Reset Staff Password</h4>
        <p class="text-center text-muted small mb-4">Enter your registered email address</p>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="staff@example.com" required>
            </div>
            <button type="submit" name="submit_email" class="btn btn-dark">Send New Password</button>
            <div class="text-center mt-3">
                <a href="login.php" class="small text-secondary">← Back to Login</a>
            </div>
        </form>
    </div>
</body>
</html>