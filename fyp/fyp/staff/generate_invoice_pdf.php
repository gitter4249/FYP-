<?php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';
require_once 'invoice_functions.php';

if (!isset($_SESSION['staff_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$staff_id = intval($_SESSION['staff_id']);
$customer_id = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 0;
$items = isset($_POST['items']) ? $_POST['items'] : [];
$issue_date = isset($_POST['issue_date']) ? $_POST['issue_date'] : date('Y-m-d');
$due_date = isset($_POST['due_date']) ? $_POST['due_date'] : date('Y-m-d', strtotime('+30 days'));
$notes_text = isset($_POST['notes']) ? trim($_POST['notes']) : '';
$qtn_id = isset($_POST['qtn_id']) ? intval($_POST['qtn_id']) : 0;
$stage = isset($_POST['stage']) ? $_POST['stage'] : '';
$stage_percent = isset($_POST['stage_percent']) ? intval($_POST['stage_percent']) : 100;
$qtn_query = "SELECT qtn_number FROM quotations WHERE qtn_id = ?";
$stmt_qtn = mysqli_prepare($conn, $qtn_query);
mysqli_stmt_bind_param($stmt_qtn, "i", $qtn_id);
mysqli_stmt_execute($stmt_qtn);
$qtn_res = mysqli_stmt_get_result($stmt_qtn);
$qtn_row = mysqli_fetch_assoc($qtn_res);
$qtn_number = $qtn_row['qtn_number'] ?? 'QT-00000-00';

if ($customer_id <= 0 || empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Missing customer or items']);
    exit;
}

$cust_query = "SELECT name, address, phone, email FROM customers WHERE customer_id = ?";
$stmt = mysqli_prepare($conn, $cust_query);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$cust_res = mysqli_stmt_get_result($stmt);
$customer = mysqli_fetch_assoc($cust_res);
if (!$customer) {
    echo json_encode(['success' => false, 'message' => 'Customer not found']);
    exit;
}

$invoice_items = [];
foreach ($items as $item) {
    $qty = floatval(str_replace(',', '', $item['quantity'] ?? 1));
    $price = floatval(str_replace(',', '', $item['unit_price'] ?? 0));
    $disc = floatval(str_replace(',', '', $item['discount'] ?? 0));
    $desc = trim($item['description'] ?? '');
    $area = floatval(str_replace(',', '', $item['area'] ?? 0)); 
    
    $invoice_items[] = [
        'description' => $desc,
        'quantity' => $qty,
        'unit_price' => $price,
        'discount' => $disc,
        'area' => $area,
    ];
}

$total = 0;
foreach ($invoice_items as $it) {
    $total += ($it['area'] * $it['unit_price']) - $it['discount'];
}
$final_amount = $total * ($stage_percent / 100);
$inv_number = 'INV-' . date('Ymd') . '-' . rand(100, 999);
$initial_pdf_path = ''; 
if ($qtn_id > 0) {
    $sql = "INSERT INTO invoices (qtn_id, customer_id, staff_id, invoice_number, total_amount, final_amount, status, issue_date, due_date, stage, file_path) 
            VALUES (?, ?, ?, ?, ?, ?, 'Draft', ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiissdssss", $qtn_id, $customer_id, $staff_id, $inv_number, $total, $final_amount, $issue_date, $due_date, $stage, $initial_pdf_path);
    mysqli_stmt_execute($stmt);
    $inv_id = mysqli_insert_id($conn);
} else {
    $sql = "INSERT INTO invoices (customer_id, staff_id, invoice_number, total_amount, final_amount, status, issue_date, due_date, stage, file_path) 
            VALUES (?, ?, ?, ?, ?, 'Draft', ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iissdssss", $customer_id, $staff_id, $inv_number, $total, $final_amount, $issue_date, $due_date, $stage, $initial_pdf_path);
    mysqli_stmt_execute($stmt);
    $inv_id = mysqli_insert_id($conn);
}

if ($inv_id) {
    $pdf_path = generateInvoicePDF(
        $inv_id,
        $inv_number,
        $customer,
        $invoice_items,
        $final_amount,
        $issue_date,
        $notes_text,
        $stage_percent / 100,
        $stage ? ucfirst($stage) . ' (' . $stage_percent . '%)' : ''
    );

    $update_sql = "UPDATE invoices SET file_path = ? WHERE inv_id = ?";
    $up_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($up_stmt, "si", $pdf_path, $inv_id);
    mysqli_stmt_execute($up_stmt);

    echo json_encode(['success' => true, 'inv_id' => $inv_id, 'pdf' => $pdf_path]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save invoice to database']);
}
?>