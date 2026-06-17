<?php
session_start();
require_once("../includes/db.php");

if (!isset($_SESSION['customer_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$customer_id = intval($_SESSION['customer_id']);
$sql = "SELECT photo_id, photo_path, caption, uploaded_at FROM project_photos WHERE customer_id = ? ORDER BY uploaded_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$photos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $photos[] = $row;
}
echo json_encode(['success' => true, 'photos' => $photos]);