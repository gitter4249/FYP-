<?php
session_start();
require_once("../includes/db.php");
if (!isset($_SESSION['customer_id'])) die("Please log in first.");
$customer_id = $_SESSION['customer_id'];
$inv_id = intval($_GET['id'] ?? 0);
$sql = "SELECT file_path FROM invoices WHERE inv_id = ? AND customer_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $inv_id, $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$inv = mysqli_fetch_assoc($result);
if (!$inv || empty($inv['file_path'])) die("Invoice not found.");
$file_path = $inv['file_path'];
if (strpos($file_path, 'uploads/') !== 0 && strpos($file_path, '../') !== 0) {
    $file_path = 'uploads/invoices/' . $file_path;
}
$full_path = dirname(__DIR__) . '/' . $file_path;
if (!file_exists($full_path)) die("File not found.");
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($full_path) . '"');
readfile($full_path);
exit;