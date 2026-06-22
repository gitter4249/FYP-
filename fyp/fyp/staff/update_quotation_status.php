<?php
session_start();
require_once("../includes/db.php");

header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$customer_id = $_SESSION['customer_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['qtn_id']) || empty($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$qtn_id = intval($data['qtn_id']);
$new_status = $data['status'];
$reason = isset($data['reason']) ? trim($data['reason']) : '';

if (!in_array($new_status, ['Accepted', 'Rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

$query = "SELECT qtn_number, total_amount, file_path, status FROM quotations WHERE qtn_id = ? AND customer_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $qtn_id, $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$quotation = mysqli_fetch_assoc($result);

if (!$quotation) {
    echo json_encode(['success' => false, 'message' => 'Quotation not found']);
    exit;
}

$old_status = $quotation['status'];
$total_amount = $quotation['total_amount'];
$file_path = $quotation['file_path'];

if ($old_status === $new_status) {
    echo json_encode(['success' => true, 'message' => 'Status already ' . $new_status]);
    exit;
}

if ($new_status === 'Rejected' && empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'Rejection reason required']);
    exit;
}

$update_sql = "UPDATE quotations SET status = ?, rejection_reason = ? WHERE qtn_id = ?";
$stmt_upd = mysqli_prepare($conn, $update_sql);
mysqli_stmt_bind_param($stmt_upd, "ssi", $new_status, $reason, $qtn_id);
$update_ok = mysqli_stmt_execute($stmt_upd);

if (!$update_ok) {
    echo json_encode(['success' => false, 'message' => 'Database update failed']);
    exit;
}

$history_sql = "INSERT INTO quotation_history 
    (qtn_id, staff_id, action, old_status, new_status, rejection_reason, file_path, total_amount, notes, created_at) 
    VALUES (?, NULL, 'status_change', ?, ?, ?, ?, ?, ?, NOW())";
$hist_stmt = mysqli_prepare($conn, $history_sql);
$notes = ($new_status === 'Accepted') ? 'Customer accepted quotation' : 'Customer rejected quotation';
mysqli_stmt_bind_param($hist_stmt, "isssssds", 
    $qtn_id, 
    $old_status, 
    $new_status, 
    $reason, 
    $file_path, 
    $total_amount, 
    $notes
);
mysqli_stmt_execute($hist_stmt);

echo json_encode(['success' => true]);
?>