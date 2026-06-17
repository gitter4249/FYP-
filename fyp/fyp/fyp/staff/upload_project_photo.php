<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['staff_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['customer_id']) || empty($_FILES['photo'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$customer_id = intval($_POST['customer_id']);
$caption = isset($_POST['caption']) ? trim($_POST['caption']) : '';
$staff_id = intval($_SESSION['staff_id']);

$upload_dir = '../uploads/project_photos/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$file = $_FILES['photo'];
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'photo_' . $customer_id . '_' . time() . '.' . $ext;
$dest = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $dest)) {
    $db_path = 'uploads/project_photos/' . $filename;
    $sql = "INSERT INTO project_photos (customer_id, staff_id, photo_path, caption) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiss", $customer_id, $staff_id, $db_path, $caption);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Photo uploaded']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'File upload failed']);
}