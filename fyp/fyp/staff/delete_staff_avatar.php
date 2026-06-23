<?php
session_start();
require '../includes/db.php';
if (!isset($_SESSION['staff_id'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
$staff_id = $_SESSION['staff_id'];
$default_path = '../uploads/staff_avatars/default_avatar.png';
$sql = "UPDATE staff SET profile_image = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "si", $default_path, $staff_id);
if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
}
?>