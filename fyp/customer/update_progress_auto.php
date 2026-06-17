<?php
session_start();
require_once("../includes/db.php");

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$customer_id = intval($input['customer_id'] ?? 0);
$step = $input['progress_step'] ?? '';
$status = $input['status'] ?? 'Completed';

if (!$customer_id || !$step) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$allowed = false;
if (isset($_SESSION['customer_id']) && $_SESSION['customer_id'] == $customer_id) {
    $allowed = true;
} elseif (isset($_SESSION['staff_id'])) {
    $allowed = true;
}
if (!$allowed) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$check = mysqli_prepare($conn, "SELECT id FROM project_progress WHERE customer_id = ? AND progress_step = ?");
mysqli_stmt_bind_param($check, "is", $customer_id, $step);
mysqli_stmt_execute($check);
$res = mysqli_stmt_get_result($check);
$exists = mysqli_fetch_assoc($res);

if ($exists) {
    $sql = "UPDATE project_progress SET status = ?, updated_at = NOW() WHERE customer_id = ? AND progress_step = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sis", $status, $customer_id, $step);
} else {
    $sql = "INSERT INTO project_progress (customer_id, staff_id, progress_step, status, notes) VALUES (?, ?, ?, ?, '')";

    $staff_id = $_SESSION['staff_id'] ?? 0;
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiss", $customer_id, $staff_id, $step, $status);
}

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
}