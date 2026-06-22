<?php
session_start();
require_once("../includes/db.php");
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_reset'])) {
    header("Location: customer_login.php");
    exit;
}

$email = trim($_POST['reset_email']);
if (empty($email)) {
    header("Location: customer_login.php?error=Email is required.");
    exit;
}

$sql = "SELECT customer_id, name FROM customers WHERE email = ? AND status = 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: customer_login.php?error=Email not found or account inactive.");
    exit;
}

$customer_id = $user['customer_id'];
$customer_name = $user['name'];

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
mysqli_stmt_bind_param($update_stmt, "si", $hashed_password, $customer_id);
if (!mysqli_stmt_execute($update_stmt)) {
    header("Location: customer_login.php?error=Failed to reset password. Please try again.");
    exit;
}

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'simhengping@gmail.com';  
    $mail->Password   = 'umsk fyxz ngat jrlg';     
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom('no-reply@ysaluminium.com', 'YS Aluminium');
    $mail->addAddress($email, $customer_name);

    $mail->isHTML(true);
    $mail->Subject = 'Your New Password - YS Aluminium';
    $mail->Body = "
    <html>
    <head><meta charset='UTF-8'></head>
    <body style='font-family: Arial, sans-serif; background-color: #f6f6f6; margin: 0; padding: 20px;'>
        <div style='max-width: 480px; margin: 0 auto; background: white; border-radius: 16px; box-shadow: 0 8px 20px rgba(0,0,0,0.05); padding: 35px 30px; text-align: left;'>
            <h2 style='color: #1c1c1e; margin-top: 0; font-weight: 600;'>Hello " . htmlspecialchars($customer_name) . ",</h2>
            <p style='color: #3a3a3c; line-height: 1.5; font-size: 15px;'>You requested a password reset for your customer account.</p>
            <p style='color: #3a3a3c; line-height: 1.5; font-size: 15px; margin-bottom: 25px;'>Your new temporary password is:</p>
            
            <div style='background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 14px; padding: 20px 15px; text-align: center; margin: 10px 0 25px 0;'>
                <span style='font-family: \"Courier New\", monospace; font-size: 36px; font-weight: 700; letter-spacing: 4px; color: #0f172a; word-break: break-all;'>" . $new_password . "</span>
            </div>
            
            <p style='color: #3a3a3c; line-height: 1.5; font-size: 15px;'>Please login with this password and <strong>change it immediately</strong> for security reasons.</p>
            <p style='color: #3a3a3c; line-height: 1.5; font-size: 15px;'>If you did not request this change, please contact the administrator.</p>
            
            <hr style='border: none; border-top: 1px solid #e4e4e7; margin: 30px 0 15px;'>
            <p style='color: #8e8e93; font-size: 13px; text-align: center; margin-bottom: 0;'>YS Aluminium Sdn Bhd</p>
            <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
            <p style='color: #555; font-size: 0.9rem;'>If you have any questions, please contact us:</p>
            <p style='color: #555; font-size: 0.9rem;'><strong>Phone:</strong> +60 18-366 5756<br><strong>Email:</strong> yongshengalu@gmail.com</p>
            <p style='color: #777; font-size: 0.8rem;'>YS Aluminium Team</p>
        </div>
    </body>
    </html>
    ";
    $mail->AltBody = "Hello $customer_name,\n\nYou requested a password reset for your customer account.\n\nYour new temporary password is: $new_password\n\nPlease login and change it immediately.\n\nIf you did not request this change, please contact the administrator.\n\nYS Aluminium Sdn Bhd";

    $mail->send();
    header("Location: customer_login.php?success=New password has been sent to your email. Please check your inbox (including spam folder).");
} catch (Exception $e) {
    error_log("Mail error (forgot password): " . $mail->ErrorInfo);
    header("Location: customer_login.php?success=Password has been reset. Please check your email (if you don't see it, contact support).");
}
exit;
?>