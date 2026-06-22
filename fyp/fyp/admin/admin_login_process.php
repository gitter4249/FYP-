<?php
session_start();
require '../includes/db.php';
require '../includes/send_email.php'; 

if (isset($_POST['admin_login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM admins WHERE LOWER(email) = LOWER(?)";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if ($row) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['admin'] = $row['full_name'];
                $_SESSION['admin_id'] = $row['id'];
                header("Location: admin_dashboard.php");
                exit;
            } else {
                header("Location: admin_login.php?error=Invalid password.");
                exit;
            }
        } else {
            header("Location: admin_login.php?error=Email not found.");
            exit;
        }
    } else {
        header("Location: admin_login.php?error=Database error.");
        exit;
    }
}

if (isset($_POST['admin_reset'])) {
    $reset_email = trim($_POST['reset_email']);

    $check_sql = "SELECT id, full_name FROM admins WHERE LOWER(email) = LOWER(?)";
    $stmt = mysqli_prepare($conn, $check_sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $reset_email);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($res);

        if ($user) {
            $admin_id = $user['id'];
            $admin_name = $user['full_name'];

            $new_password = substr(md5(uniqid(rand(), true)), 0, 8);
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $update = mysqli_query($conn, "UPDATE admins SET password = '$hashed_password' WHERE id = $admin_id");

            if ($update) {
                $subject = "Your New Admin Portal Password - YS Aluminium";
                $body = "
                    <html>
                    <head><meta charset='UTF-8'></head>
                    <body>
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                            <h2>Hello " . htmlspecialchars($admin_name) . ",</h2>
                            <p>You requested a password reset for your admin account.</p>
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
                $altBody = "Hello $admin_name,\n\nYour new temporary password is: $new_password\n\nPlease login and change it immediately.\n\nYS Aluminium Team";

                if (sendYSAluminiumEmail($reset_email, $admin_name, $subject, $body, $altBody)) {
                    header("Location: admin_login.php?reset_success=1");
                    exit;
                } else {
                    header("Location: admin_login.php?reset_error=Failed to send email. Please try again later.");
                    exit;
                }
            } else {
                header("Location: admin_login.php?reset_error=Database error. Could not update password.");
                exit;
            }
        } else {
            header("Location: admin_login.php?reset_error=No admin account found with that email address.");
            exit;
        }
    } else {
        header("Location: admin_login.php?reset_error=Database query error.");
        exit;
    }
}

header("Location: admin_login.php");
exit;
?>