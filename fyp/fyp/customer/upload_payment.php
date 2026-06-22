<?php
session_start();
require_once("../includes/db.php");

if (!isset($_SESSION['customer_id'])) {
    echo "not_logged_in";
    exit;
}
$customer_id = $_SESSION['customer_id'];
$qtn_id = intval($_POST['qtn_id'] ?? 0);
$stage = $_POST['stage'] ?? 'deposit';

if (!in_array($stage, ['deposit', 'progress', 'final'])) {
    echo "invalid_stage";
    exit;
}

if ($qtn_id <= 0) {
    echo "invalid_quotation";
    exit;
}

$check_qtn = mysqli_query($conn, "SELECT qtn_id FROM quotations WHERE qtn_id = $qtn_id AND customer_id = $customer_id AND status = 'Accepted'");
if (mysqli_num_rows($check_qtn) == 0) {
    echo "quotation_not_accepted";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['payment_receipt'])) {
    $target_dir = "../uploads/payments/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $file_name = time() . '_' . basename($_FILES['payment_receipt']['name']);
    $target_file = $target_dir . $file_name;
    if (move_uploaded_file($_FILES['payment_receipt']['tmp_name'], $target_file)) {
        $db_file_path = 'uploads/payments/' . $file_name;
        $check_dup = mysqli_query($conn, "SELECT id FROM payment_records WHERE customer_id = $customer_id AND qtn_id = $qtn_id AND stage = '$stage'");
        if (mysqli_num_rows($check_dup) > 0) {
            $stmt = mysqli_prepare($conn, "UPDATE payment_records SET file_path = ?, status = 'Pending', uploaded_at = NOW() WHERE customer_id = ? AND qtn_id = ? AND stage = ?");
            mysqli_stmt_bind_param($stmt, "siis", $db_file_path, $customer_id, $qtn_id, $stage);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO payment_records (customer_id, qtn_id, stage, file_path, status) VALUES (?, ?, ?, ?, 'Pending')");
            mysqli_stmt_bind_param($stmt, "iiss", $customer_id, $qtn_id, $stage, $db_file_path);
        }
        if (mysqli_stmt_execute($stmt)) {
            echo "success";
        } else {
            echo "db_error";
        }
    } else {
        echo "move_failed";
    }
} else {
    echo "no_file";
}
?>