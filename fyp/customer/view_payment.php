<?php
session_start();
require_once("../includes/db.php");
if (!isset($_SESSION['customer_id'])) die("Please log in first.");
$customer_id = $_SESSION['customer_id'];
$id = intval($_GET['id'] ?? 0);
$sql = "SELECT file_path FROM payment_records WHERE id = ? AND customer_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $id, $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$rec = mysqli_fetch_assoc($result);
if (!$rec || empty($rec['file_path'])) die("Payment record not found.");
$full_path = dirname(__DIR__) . '/' . $rec['file_path'];
if (!file_exists($full_path)) die("File not found.");
$ext = pathinfo($full_path, PATHINFO_EXTENSION);
if ($ext === 'pdf') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($full_path) . '"');
} else {
    header('Content-Type: image/jpeg');
    header('Content-Disposition: inline; filename="' . basename($full_path) . '"');
}
readfile($full_path);
exit;