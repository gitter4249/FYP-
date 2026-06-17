<?php
session_start();
require '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['staff_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$staff_id = intval($_SESSION['staff_id']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$upload_dir = dirname(__DIR__) . "/uploads/staff_avatars/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
if (!is_writable($upload_dir)) {
    echo json_encode(['success' => false, 'message' => 'Upload directory not writable']);
    exit;
}

$ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
$new_filename = "staff_" . $staff_id . "_" . time() . "." . $ext;
$dest = $upload_dir . $new_filename;

if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
    $db_path = "uploads/staff_avatars/" . $new_filename;
    $update_sql = "UPDATE staff SET profile_image = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "si", $db_path, $staff_id);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'new_path' => $db_path]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to move file']);
}
?>