<?php
session_start();
require_once("../includes/db.php");

header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$customer_id = $_SESSION['customer_id'];
$sql = "SELECT q.qtn_id, q.qtn_number, q.total_amount, 
               (SELECT CASE WHEN qi.product_id IS NOT NULL THEN p.door_brand ELSE SUBSTRING_INDEX(qi.description, '\n', 1) END
                FROM quotation_items qi LEFT JOIN products p ON qi.product_id = p.product_id
                WHERE qi.qtn_id = q.qtn_id LIMIT 1) as product_name
        FROM quotations q
        WHERE q.customer_id = ? AND q.status = 'Accepted'
        ORDER BY q.created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$quotations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $qtn_id = $row['qtn_id'];
    $row['product_name'] = $row['product_name'] ?: 'Product';
    $row['invoices'] = [];
    $inv_sql = "SELECT inv_id, invoice_number, final_amount, issue_date, due_date, file_path, stage 
                FROM invoices WHERE qtn_id = ?";
    $inv_stmt = mysqli_prepare($conn, $inv_sql);
    mysqli_stmt_bind_param($inv_stmt, "i", $qtn_id);
    mysqli_stmt_execute($inv_stmt);
    $inv_result = mysqli_stmt_get_result($inv_stmt);
    while ($inv = mysqli_fetch_assoc($inv_result)) {
        $row['invoices'][] = $inv;
    }
    $quotations[] = $row;
}

echo json_encode(['quotations' => $quotations]);
?>