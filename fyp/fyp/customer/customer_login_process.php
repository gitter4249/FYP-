<?php
session_start();
require '../includes/db.php'; 
require '../includes/send_email.php';

if (isset($_POST['submit_login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM customers WHERE LOWER(email) = LOWER(?)";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if ($row) {
            if (password_verify($password, $row['password'])) {
                if ((int)$row['status'] !== 1) {
                    header("Location: customer_login.php?error=Account is deactivated.");
                    exit;
                }

                $_SESSION['customer_id'] = $row['customer_id'];
                $_SESSION['customer_name'] = $row['name'];

                header("Location: customer_dashboard.php");
                exit;
            } else {
                header("Location: customer_login.php?error=Incorrect password.");
                exit;
            }
        } else {
            header("Location: customer_login.php?error=No account found.");
            exit;
        }
    } else {
        die("Database error: " . mysqli_error($conn));
    }

} elseif (isset($_POST['submit_reset'])) {
    $reset_email = trim($_POST['reset_email']);

    $check_sql = "SELECT customer_id, name FROM customers WHERE LOWER(email) = LOWER(?) AND status = 1";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "s", $reset_email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($res);

    if ($user) {
        $cust_id = $user['customer_id'];
        $cust_name = $user['name'];

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

        $update_sql = "UPDATE customers SET password = ? WHERE customer_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "si", $hashed_password, $cust_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            if (mysqli_stmt_affected_rows($update_stmt) > 0) {
                $subject = "Your Password Has Been Reset - YS Aluminium";
                $body = "
                    <html>
                    <head><meta charset='UTF-8'></head>
                    <body>
                        <h2>Hello $cust_name,</h2>
                        <p>We received a request to reset your password.</p>
                        <p>Your new password is: <strong>$new_password</strong></p>
                        <p>Please login and change it immediately.</p>
                        <p>If you did not request this, please contact support.</p>
                        <p>Thank you,<br>YS Aluminium Team</p>
                        <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                        <p style='color: #555; font-size: 0.9rem;'>If you have any questions, please contact us:</p>
                        <p style='color: #555; font-size: 0.9rem;'><strong>Phone:</strong> +60 18-366 5756<br><strong>Email:</strong> yongshengalu@gmail.com</p>
                        <p style='color: #777; font-size: 0.8rem;'>YS Aluminium Team</p>
                        </div>
                    </body>
                    </html>
                ";
                $altBody = "Hello $cust_name,\n\nYour new password is: $new_password\n\nPlease login and change it immediately.\n\nYS Aluminium Team";
                sendYSAluminiumEmail($reset_email, $cust_name, $subject, $body, $altBody);

                header("Location: customer_login.php?success=Your password has been reset. Please check your email (including spam folder).");
                exit;
            } else {
                header("Location: customer_login.php?error=Password reset failed (no changes).");
                exit;
            }
        } else {
            header("Location: customer_login.php?error=Database error: " . urlencode(mysqli_error($conn)));
            exit;
        }
    } else {
        header("Location: customer_login.php?error=Email not found or account inactive.");
        exit;
    }
}

mysqli_close($conn);
?>