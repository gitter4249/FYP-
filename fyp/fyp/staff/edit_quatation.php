<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['staff_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$staff_id = intval($_SESSION['staff_id']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$qtn_id = isset($_POST['qtn_id']) ? intval($_POST['qtn_id']) : 0;
$new_amount = isset($_POST['total_amount']) ? floatval($_POST['total_amount']) : 0;

if ($qtn_id <= 0 || $new_amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$sql = "SELECT file_path, qtn_number FROM quotations WHERE qtn_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $qtn_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$quotation = mysqli_fetch_assoc($result);
if (!$quotation) {
    echo json_encode(['success' => false, 'message' => 'Quotation not found']);
    exit;
}

$old_file = $quotation['file_path'];
$qtn_number = $quotation['qtn_number'];

$new_file_path = $old_file; 
if (isset($_FILES['quotation_file']) && $_FILES['quotation_file']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = "../uploads/quotations/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_extension = pathinfo($_FILES['quotation_file']['name'], PATHINFO_EXTENSION);
    $new_filename = $qtn_number . "_" . time() . "." . $file_extension;
    $dest_path = $upload_dir . $new_filename;

    if (move_uploaded_file($_FILES['quotation_file']['tmp_name'], $dest_path)) {
        $new_file_path = "uploads/quotations/" . $new_filename;

        if ($old_file && file_exists("../" . $old_file)) {
            unlink("../" . $old_file);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
        exit;
    }
}

$update_sql = "UPDATE quotations SET total_amount = ?, file_path = ? WHERE qtn_id = ?";
$update_stmt = mysqli_prepare($conn, $update_sql);
mysqli_stmt_bind_param($update_stmt, "dsi", $new_amount, $new_file_path, $qtn_id);
if (mysqli_stmt_execute($update_stmt)) {
    echo json_encode(['success' => true, 'message' => 'Quotation updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed: ' . mysqli_error($conn)]);
}
?>