<?php
session_start();
require_once("../includes/db.php");

$conn = mysqli_connect("localhost", "root", "", "fyp");
if (!$conn) {
    die("Database connection failed.");
}

if (!isset($_SESSION['customer_id'])) {
    die("Please log in first.");
}

$customer_id = $_SESSION['customer_id'];


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid quotation ID.");
}
$qtn_id = intval($_GET['id']);

$sql = "SELECT file_path FROM quotations WHERE qtn_id = ? AND customer_id = ?";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("Query failed: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, "ii", $qtn_id, $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$quotation = mysqli_fetch_assoc($result);

if (!$quotation) {
    die("Quotation not found or you are not the owner.");
}

$file_path = $quotation['file_path'];

$base_path = dirname(__DIR__); 
$full_path = $base_path . '/' . $file_path;

if (!file_exists($full_path)) {
    die("File not found.");
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($full_path) . '"');
readfile($full_path);
exit;