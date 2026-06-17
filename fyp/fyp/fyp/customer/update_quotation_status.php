<?php
session_start();
header('Content-Type: application/json');
require_once("../includes/db.php");
require_once("../customer/send_email.php");

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$customer_id = $_SESSION['customer_id'];
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['qtn_id']) || !isset($input['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$qtn_id = intval($input['qtn_id']);
$new_status = $input['status'];
$reason = isset($input['reason']) ? $input['reason'] : '';

$check_sql = "SELECT q.*, c.name, c.email FROM quotations q 
              JOIN customers c ON q.customer_id = c.customer_id 
              WHERE q.qtn_id = ?";
$stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($stmt, "i", $qtn_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$qtn = mysqli_fetch_assoc($res);

if (!$qtn || $qtn['customer_id'] != $customer_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid quotation']);
    exit;
}

if ($new_status === 'Rejected') {
    $update_sql = "UPDATE quotations SET status = ?, rejection_reason = ? WHERE qtn_id = ? AND status IN ('Pending', 'Updated')";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "ssi", $new_status, $reason, $qtn_id);
} else {
    $update_sql = "UPDATE quotations SET status = ? WHERE qtn_id = ? AND status IN ('Pending', 'Updated')";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "si", $new_status, $qtn_id);
}

if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
    if ($new_status === 'Accepted') {
        $customer_name = $qtn['name'];
        $customer_email = $qtn['email'];
        $qtn_number = $qtn['qtn_number'];
        $total_amount = $qtn['total_amount'];
        $deposit_amount = $total_amount / 2;

        $subject = "Payment Reminder - 50% Deposit Required for Quotation $qtn_number";
        $body = "
            <html>
            <head><meta charset='UTF-8'></head>
            <body style='font-family: Arial, sans-serif; background-color: #f6f6f6; margin: 0; padding: 20px;'>
                <div style='max-width: 550px; margin: 0 auto; background: white; border-radius: 16px; box-shadow: 0 8px 20px rgba(0,0,0,0.05); padding: 35px 30px; text-align: left;'>
                    <h2 style='color: #1c1c1e; margin-top: 0;'>Dear " . htmlspecialchars($customer_name) . ",</h2>
                    <p style='color: #3a3a3c; line-height: 1.5; font-size: 15px;'>Thank you for accepting quotation <strong>$qtn_number</strong>.</p>
                    <p style='color: #3a3a3c; line-height: 1.5; font-size: 15px;'>Please make a <strong>50% deposit payment</strong> within <strong>7 days</strong> to proceed with your order.</p>
                    
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 12px; margin: 25px 0;'>
                        <p style='margin: 0 0 10px 0;'><strong>Total Amount:</strong> RM " . number_format($total_amount, 2) . "</p>
                        <p style='margin: 0;'><strong>Deposit Required (50%):</strong> RM " . number_format($deposit_amount, 2) . "</p>
                    </div>
                    
                    <p style='color: #3a3a3c; line-height: 1.5; font-size: 15px;'><strong>Bank Transfer Details:</strong></p>
                    <p style='color: #3a3a3c; line-height: 1.5; font-size: 15px;'>
                        Bank: Public Bank<br>
                        Account Name: YONG SHENG ALU ENTERPRISE<br>
                        Account Number: 3168038306
                    </p>
                    
                    <p style='color: #3a3a3c; line-height: 1.5; font-size: 15px; margin-top: 20px;'>After payment, please upload the receipt in the Payment section of your portal.</p>
                    
                    <hr style='border: none; border-top: 1px solid #e4e4e7; margin: 30px 0 15px;'>
                    <p style='color: #555; font-size: 0.9rem;'>If you have any questions, please contact us:</p>
                    <p style='color: #555; font-size: 0.9rem;'><strong>Phone:</strong> +60 18-366 5756<br><strong>Email:</strong> yongshengalu@gmail.com</p>
                    <p style='color: #8e8e93; font-size: 13px; text-align: center; margin: 20px 0 0;'>YS Aluminium Sdn Bhd</p>
                </div>
            </body>
            </html>
        ";
        $altBody = "Dear $customer_name,\n\nThank you for accepting quotation $qtn_number.\n\nPlease make a 50% deposit payment of RM " . number_format($deposit_amount, 2) . " within 7 days to proceed.\n\nBank: Public Bank\nAccount Name: YONG SHENG ALU ENTERPRISE\nAccount Number: 3168038306\n\nAfter payment, upload the receipt in the Payment section.\n\nPhone: +60 18-366 5756\nEmail: yongshengalu@gmail.com\n\nYS Aluminium Sdn Bhd";

        sendYSAluminiumEmail($customer_email, $customer_name, $subject, $body, $altBody);
    }

    echo json_encode(['success' => true, 'message' => 'Status updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed or quotation already processed']);
}