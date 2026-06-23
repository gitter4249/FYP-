<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

function handleFatalError() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Fatal Error: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']]);
        exit;
    }
}
register_shutdown_function('handleFatalError');
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

session_start();
require '../includes/db.php';
$tcpdf_path = '../includes/TCPDF-main/TCPDF-main/tcpdf.php';
require_once __DIR__ . '/../includes/send_email.php';
if (!file_exists($tcpdf_path)) {
    echo json_encode(['success' => false, 'message' => 'TCPDF file not found at: ' . $tcpdf_path]);
    exit;
}
require_once $tcpdf_path;

if (!isset($_SESSION['staff_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$staff_id = intval($_SESSION['staff_id']);
$staff_name = $_SESSION['staff_name'] ?? 'Staff';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['customer_id']) || empty($_POST['items'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$customer_id = intval($_POST['customer_id']);
$items = $_POST['items'];

$cust_query = "SELECT name, address, phone, email FROM customers WHERE customer_id = ?";
$stmt = mysqli_prepare($conn, $cust_query);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB prepare error: ' . mysqli_error($conn)]);
    exit;
}
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$cust_res = mysqli_stmt_get_result($stmt);
$customer = mysqli_fetch_assoc($cust_res);
if (!$customer) {
    echo json_encode(['success' => false, 'message' => 'Customer not found']);
    exit;
}

$qtn_number = "QT-" . date("Ymd") . "-" . rand(100, 999);
$total_amount = 0;

mysqli_begin_transaction($conn);

try {
    $ins_qtn = "INSERT INTO quotations (customer_id, staff_id, qtn_number, file_path, total_amount, status, created_at) VALUES (?, ?, ?, '', 0, 'Pending', NOW())";
    $stmt = mysqli_prepare($conn, $ins_qtn);
    if (!$stmt) throw new Exception('Prepare quotation failed: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt, "iis", $customer_id, $staff_id, $qtn_number);
    if (!mysqli_stmt_execute($stmt)) throw new Exception('Execute quotation failed: ' . mysqli_stmt_error($stmt));
    $qtn_id = mysqli_insert_id($conn);

    $ins_item = "INSERT INTO quotation_items (qtn_id, product_id, description, quantity, area, width_mm, height_mm, unit_price, discount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_item = mysqli_prepare($conn, $ins_item);
    if (!$stmt_item) throw new Exception('Prepare item failed: ' . mysqli_error($conn));

    $constructed_items = [];

    foreach ($items as $item) {
        $product_id = !empty($item['product_id']) ? intval($item['product_id']) : null;
        $unit_price = floatval($item['unit_price']);
        $discount = floatval($item['discount'] ?? 0);

        $product_name = '';
        if ($product_id) {
            $prod_query = "SELECT door_brand FROM products WHERE product_id = $product_id";
            $prod_res = mysqli_query($conn, $prod_query);
            if ($prod_row = mysqli_fetch_assoc($prod_res)) {
                $product_name = $prod_row['door_brand'];
            }
        }

        if (isset($item['subitems']) && is_array($item['subitems']) && count($item['subitems']) > 0) {
            $subitems = $item['subitems'];
            $sub_desc_lines = [];
            $sub_total_area = 0;
            $prefix = 'a';
            $sub_index = 0;
            foreach ($subitems as $sub) {
                $desc = trim($sub['desc'] ?? '');
                $area = floatval($sub['area'] ?? 0);
                $sub_total_area += $area;
                $sub_desc_lines[] = $prefix . ') ' . $desc . ' (' . number_format($area, 2) . ' sqft)';
                $prefix++;
                $width = intval($sub['width'] ?? 0);
                $height = intval($sub['height'] ?? 0);
                $qty = 1;
                $sub_discount = ($sub_index === 0) ? $discount : 0;
                $sub_index++;
                mysqli_stmt_bind_param($stmt_item, "iisddiddd",
                    $qtn_id, $product_id, $desc, $qty, $area, $width, $height, $unit_price, $sub_discount);
                if (!mysqli_stmt_execute($stmt_item)) {
                    throw new Exception('Insert subitem failed: ' . mysqli_stmt_error($stmt_item));
                }
            }
            $item_amount = round(($sub_total_area * $unit_price) - $discount, 2);
            $total_amount += $item_amount;

            $main_desc = $product_name;
            foreach ($sub_desc_lines as $sub_desc) {
                $main_desc .= "\n" . $sub_desc;
            }
            $constructed_items[] = [
                'description' => $main_desc,
                'quantity' => 1,
                'total_area' => $sub_total_area,
                'unit_price' => $unit_price,
                'discount' => $discount,
                'amount' => $item_amount
            ];
        } else {
            $detail = trim($item['detail'] ?? '');
            $area = floatval($item['area'] ?? 0);
            $qty = floatval($item['quantity'] ?? 1);
            $item_amount = ($area * $unit_price * $qty) - $discount;
            $total_amount += $item_amount;

            $desc = $product_name;
            if (!empty($detail)) {
                $desc .= "\n" . $detail;
            }
            if (empty($desc)) {
                $desc = 'Custom Item';
            }

            $width = 0;
            $height = 0;
            $sub_discount = $discount;
            mysqli_stmt_bind_param($stmt_item, "iisddiddd", $qtn_id, $product_id, $desc, $qty, $area, $width, $height, $unit_price, $sub_discount);
            if (!mysqli_stmt_execute($stmt_item)) {
                throw new Exception('Insert item failed: ' . mysqli_stmt_error($stmt_item));
            }

            $constructed_items[] = [
                'description' => $desc,
                'quantity' => $qty,
                'total_area' => $area * $qty,
                'unit_price' => $unit_price,
                'discount' => $discount,
                'amount' => $item_amount
            ];
        }
    }

    $update_sql = "UPDATE quotations SET total_amount = ? WHERE qtn_id = ?";
    $stmt_update = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt_update, "di", $total_amount, $qtn_id);
    if (!mysqli_stmt_execute($stmt_update)) throw new Exception('Update total failed: ' . mysqli_stmt_error($stmt_update));

    $pdf_path = generateQuotationPDF($qtn_number, $customer, $constructed_items, $total_amount);
    $update_pdf = "UPDATE quotations SET file_path = ? WHERE qtn_id = ?";
    $stmt_pdf = mysqli_prepare($conn, $update_pdf);
    mysqli_stmt_bind_param($stmt_pdf, "si", $pdf_path, $qtn_id);
    if (!mysqli_stmt_execute($stmt_pdf)) throw new Exception('Update PDF path failed: ' . mysqli_stmt_error($stmt_pdf));

    mysqli_commit($conn);

    if ($qtn_id && !empty($customer['email'])) {
        $subject = "New Quotation Created - YS Aluminium";
        $customer_name = $customer['name'];
        $customer_email = $customer['email'];
        
        $body = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Dear $customer_name,</h2>
            <p>A new quotation has been created for you.</p>
            <p><strong>Quotation Number:</strong> $qtn_number</p>
            <p><strong>Total Amount:</strong> RM " . number_format($total_amount, 2) . "</p>
            <p>Please log in to your account to check it.</p>
            <hr>
            <p>YS Aluminium Sdn Bhd</p>
        </body>
        </html>
        ";
        $altBody = "Dear $customer_name,\n\nA new quotation has been created for you.\nQuotation Number: $qtn_number\nTotal Amount: RM " . number_format($total_amount, 2) . "\n\nPlease log in to view it.\n\nYS Aluminium Team";
        
        sendYSAluminiumEmail($customer_email, $customer_name, $subject, $body, $altBody);
    }
    if ($qtn_id) {
        $history_sql = "INSERT INTO quotation_history 
                        (qtn_id, staff_id, action, new_status, total_amount, file_path, notes) 
                        VALUES (?, ?, 'created', 'Pending', ?, ?, 'Quotation created.')";
        $history_stmt = mysqli_prepare($conn, $history_sql);
        mysqli_stmt_bind_param($history_stmt, "iids", $qtn_id, $staff_id, $total_amount, $pdf_path);
        mysqli_stmt_execute($history_stmt);
    }

    echo json_encode(['success' => true, 'qtn_id' => $qtn_id, 'pdf' => $pdf_path]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function generateQuotationPDF($qtn_number, $customer, $items, $total) {
    if (!class_exists('TCPDF')) {
        throw new Exception('TCPDF class not loaded. Check require_once path.');
    }
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('YS Aluminium');
    $pdf->SetAuthor('YS Aluminium');
    $pdf->SetTitle("Quotation $qtn_number");
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->SetPrintHeader(false);
    $pdf->SetPrintFooter(false);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 9);

    $html = '<div style="text-align:center; line-height:1.2;">';
    $html .= '<span style="font-size:16pt;"><b>YONG SHENG ALU ENTERPRISE</b></span><br>';
    $html .= '<span style="font-size:9pt;">No,46 JALAN BELADAU 3,<br>';
    $html .= 'TAMAN PUTERI WANGSA,<br>';
    $html .= '81800 ULU TIRAM<br>';
    $html .= 'JOHOR BAHRU</span>';
    $html .= '</div>';
    $html .= '<div style="border-bottom:1px solid #000; margin-top:5px; margin-bottom:12px;"></div>';
    $html .= '<table width="100%" cellpadding="0" style="font-size:9pt;">';
    $html .= '<tr>';
    $html .= '<td width="35%">';
    $html .= '<table width="100%" cellpadding="0" cellspacing="0">';
    $html .= '<tr>';
    $html .= '<td width="10%" style="border-top:1px solid #000; border-left:1px solid #000; height:6px;"></td>';
    $html .= '<td width="80%"></td>';
    $html .= '<td width="10%" style="border-top:1px solid #000; border-right:1px solid #000;"></td>';
    $html .= '</tr>';
    $html .= '<tr>';
    $html .= '<td style="border-left:1px solid #000;"></td>';
    $html .= '<td style="padding-left:2px; line-height:1.2;">';
    $html .= 'ONLINE SALES<br>';
    $html .= '<span style="text-decoration:underline;">Job Site Address</span><br>';
    $html .= htmlspecialchars($customer['address'] ?? '') . '<br><br>';
    $html .= 'Attn : ' . htmlspecialchars($customer['name']);
    $html .= '</td>';
    $html .= '<td></td>';
    $html .= '</tr>';
    $html .= '<tr>';
    $html .= '<td style="border-bottom:1px solid #000; border-left:1px solid #000; height:6px;"></td>';
    $html .= '<td></td>';
    $html .= '<td style="border-bottom:1px solid #000; border-right:1px solid #000;"></td>';
    $html .= '</tr>';
    $html .= '</table>';
    $html .= '</td>';
    $html .= '<td width="15%"></td>';
    $html .= '<td width="50%">';
    $html .= '<div style="font-size:14pt; margin-bottom:8px;"><b>QUOTATION</b></div>';
    $html .= '<table width="100%" cellpadding="1" style="font-size:9pt; line-height:1.2;">';
    $html .= '<tr><td width="35%">Our Ref. No</td><td width="5%">:</td><td width="60%">' . $qtn_number . '</td></tr>';
    $html .= '<tr><td>Date</td><td>:</td><td>' . date('d/m/Y') . '</td></tr>';
    $html .= '<tr><td>Phone No</td><td>:</td><td>' . htmlspecialchars($customer['phone'] ?? '') . '</td></tr>';
    $html .= '<tr><td>FAX</td><td>:</td><td></td></tr>';
    $html .= '<tr><td>Page</td><td>:</td><td>1 of 1</td></tr>';
    $html .= '</table>';
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '</table>';

    $html .= '<p style="font-size:9pt; margin-top:10px;">Thank you for your inquiry. We are pleased to submit our quote as follows:</p>';

    $html .= '<table width="100%" cellpadding="5" cellspacing="0" style="font-size:9pt;" >';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th width="35%" style="border-top:1px solid #000; border-bottom:1px solid #000;" align="left"><b>Description</b></th>';
    $html .= '<th width="8%" style="border-top:1px solid #000; border-bottom:1px solid #000;" align="center"><b>Qty</b></th>';
    $html .= '<th width="12%" style="border-top:1px solid #000; border-bottom:1px solid #000;" align="center"><b>Area</b><br><span style="font-size:8pt;">(sqft)</span></th>';
    $html .= '<th width="15%" style="border-top:1px solid #000; border-bottom:1px solid #000;" align="center"><b>Unit Price</b><br><span style="font-size:8pt;">(RM)</span></th>';
    $html .= '<th width="8%" style="border-top:1px solid #000; border-bottom:1px solid #000;" align="center"><b>Disc</b><br><span style="font-size:8pt;">(RM)</span></th>';
    $html .= '<th width="22%" style="border-top:1px solid #000; border-bottom:1px solid #000;" align="right"><b>Amount</b><br><span style="font-size:8pt;">(RM)</span></th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    $item_num = 1;
    foreach ($items as $it) {
        $desc_lines = preg_split('/\r\n|\r|\n/', $it['description']);
        $main_desc = $item_num . '. ' . trim($desc_lines[0]);
        $sub_lines = array_slice($desc_lines, 1);
        $html .= '<tr>';
        $html .= '<td width="35%" align="left">';
        $html .= htmlspecialchars($main_desc);
        if (!empty($sub_lines)) {
            foreach ($sub_lines as $sub) {
                $trimmed = trim($sub);
                if (!empty($trimmed)) {
                    $html .= '<br>&nbsp;&nbsp;' . htmlspecialchars($trimmed);
                }
            }
        }
        $html .= '</td>';
        $html .= '<td width="8%" align="center">' . number_format(floatval($it['quantity']), 0) . '</td>';
        $html .= '<td width="12%" align="center">' . number_format(floatval($it['total_area'] ?? 0), 2) . '</td>';
        $html .= '<td width="15%" align="center">' . number_format(floatval($it['unit_price']), 2) . '</td>';
        $html .= '<td width="8%" align="center">' . number_format(floatval($it['discount']), 2) . '</td>';
        $html .= '<td width="22%" align="right">' . number_format(floatval($it['amount']), 2) . '</td>';
        $html .= '</tr>';
        $item_num++;
    }

    $html .= '<tr><td colspan="6" height="180"></td></tr>';
    $html .= '</tbody>';
    $html .= '</table>';
    $html .= '<div style="border-top:1px solid #000;"></div>';

    $html .= '<table width="100%" cellpadding="3" style="font-size:9pt; margin-top:2px;">';
    $html .= '<tr>';
    $html .= '<td width="70%" align="right" style="padding-top:6px;"><b>Total (RM)</b></td>';
    $html .= '<td width="30%">';
    $html .= '<table width="100%" border="1" cellpadding="5"><tr><td align="right"><b>' . number_format($total, 2) . '</b></td></tr></table>';
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '</table>';

    $html .= '<div style="font-size:9pt; line-height:1.4; margin-top:10px;">';
    $html .= '<b>Payment Term:</b><br>';
    $html .= '<b>** 50% Down Payment - After Confirmation</b><br>';
    $html .= '<b>** 30% Payment - On Going Job</b><br>';
    $html .= '<b>** 20% Payment - Complete Job</b><br>';
    $html .= '<b>PUBLIC BANK-3168038306 (YONG SHENG ALU ENTERPRISE)</b><br><br>';
    $html .= '<b>We hope that our quotation is favourable to you and looking forward to receive your valued orders in due course</b>';
    $html .= '</div>';

    $pdf->writeHTML($html, true, false, true, false, '');

    $upload_dir = __DIR__ . '/../uploads/quotations/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            throw new Exception('Failed to create upload directory: ' . $upload_dir);
        }
    }
    $filename = $qtn_number . '_' . time() . '.pdf';
    $filepath = $upload_dir . $filename;
    $pdf->Output($filepath, 'F');

    return 'uploads/quotations/' . $filename;
}
?>