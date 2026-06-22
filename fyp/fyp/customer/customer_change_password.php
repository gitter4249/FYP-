<?php
session_start();
require '../includes/db.php';
require '../includes/send_email.php';
header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

$customer_id = $_SESSION['customer_id'];
$input = json_decode(file_get_contents('php://input'), true);
$current = $input['current_password'] ?? '';
$new = $input['new_password'] ?? '';

if (empty($current) || empty($new)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}
function is_strong_password($pwd) {
    return strlen($pwd) >= 15 || (strlen($pwd) >= 8 && preg_match('/[0-9]/', $pwd) && preg_match('/[a-z]/', $pwd));
}
if (!is_strong_password($new)) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters, including uppercase, lowercase, number, and special symbol.']);
    exit;
}

$sql = "SELECT name, email, password FROM customers WHERE customer_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

if (!password_verify($current, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
    exit;
}

$new_hashed = password_hash($new, PASSWORD_DEFAULT);
$update_sql = "UPDATE customers SET password = ? WHERE customer_id = ?";
$update_stmt = mysqli_prepare($conn, $update_sql);
mysqli_stmt_bind_param($update_stmt, "si", $new_hashed, $customer_id);
if (mysqli_stmt_execute($update_stmt)) {
    $subject = "Your Password Has Been Changed - YS Aluminium";
    $body = "
    <html>
    <head><meta charset='UTF-8'></head>
    <body style='font-family: Arial, sans-serif; background-color: #f6f6f6; margin: 0; padding: 20px;'>
        <div style='max-width: 480px; margin: 0 auto; background: white; border-radius: 16px; box-shadow: 0 8px 20px rgba(0,0,0,0.05); padding: 35px 30px; text-align: left;'>
            <h2 style='color: #1c1c1e; margin-top: 0; font-weight: 600;'>Hello " . htmlspecialchars($user['name']) . ",</h2>
            <p style='color: #3a3a3c; line-height: 1.5; font-size: 15px;'>Your YS Aluminium account password was successfully changed.</p>
            <p style='color: #3a3a3c; line-height: 1.5; font-size: 15px;'>If you did not perform this action, please contact our support team immediately.</p>
            <hr style='border: none; border-top: 1px solid #e4e4e7; margin: 30px 0 15px;'>
            <p style='color: #555; font-size: 0.9rem;'>If you have any questions, please contact us:</p>
            <p style='color: #555; font-size: 0.9rem;'><strong>Phone:</strong> +60 18-366 5756<br><strong>Email:</strong> yongshengalu@gmail.com</p>
            <p style='color: #8e8e93; font-size: 13px; text-align: center; margin-bottom: 0;'>YS Aluminium Sdn Bhd</p>
        </div>
    </body>
    </html>
    ";
    $altBody = "Hello {$user['name']},\n\nYour YS Aluminium account password was successfully changed.\n\nIf you did not perform this action, please contact our support team immediately.\n\nYS Aluminium Sdn Bhd";
    sendYSAluminiumEmail($user['email'], $user['name'], $subject, $body, $altBody);
    
    echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
}
?>