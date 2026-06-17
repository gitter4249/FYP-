<?php
session_start();
require '../includes/db.php';
require_once '../includes/TCPDF-main/TCPDF-main/tcpdf.php';

if (!isset($_SESSION['staff_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$staff_id = intval($_SESSION['staff_id']);
$staff_name = $_SESSION['staff_name'] ?? 'Staff';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['customer_id']) || empty($_POST['items'])) {
    die(json_encode(['success' => false, 'message' => 'Invalid request']));
}

$customer_id = intval($_POST['customer_id']);
$items = $_POST['items'];

$cust_query = "SELECT name, address, phone, email FROM customers WHERE customer_id = ?";
$stmt = mysqli_prepare($conn, $cust_query);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$cust_res = mysqli_stmt_get_result($stmt);
$customer = mysqli_fetch_assoc($cust_res);
if (!$customer) {
    die(json_encode(['success' => false, 'message' => 'Customer not found']));
}

$qtn_number = "QT-" . date("Ymd") . "-" . rand(100, 999);
$total_amount = 0;

mysqli_begin_transaction($conn);

try {
    $ins_qtn = "INSERT INTO quotations (customer_id, staff_id, qtn_number, file_path, total_amount, status, created_at) VALUES (?, ?, ?, '', ?, 'Pending', NOW())";
    $stmt = mysqli_prepare($conn, $ins_qtn);
    mysqli_stmt_bind_param($stmt, "iisd", $customer_id, $staff_id, $qtn_number, $total_amount);
    mysqli_stmt_execute($stmt);
    $qtn_id = mysqli_insert_id($conn);

    $ins_item = "INSERT INTO quotation_items (qtn_id, product_id, description, quantity, unit_price, discount) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_item = mysqli_prepare($conn, $ins_item);

    $constructed_items = [];

    foreach ($items as $item) {
        file_put_contents('debug_items.log', print_r($item, true), FILE_APPEND);
        $product_id = !empty($item['product_id']) ? intval($item['product_id']) : null;
        $detail = trim($item['detail'] ?? '');
        
        $product_name = '';
        if ($product_id) {
            $prod_query = "SELECT door_brand FROM products WHERE product_id = $product_id";
            $prod_res = mysqli_query($conn, $prod_query);
            if ($prod_row = mysqli_fetch_assoc($prod_res)) {
                $product_name = $prod_row['door_brand'];
            }
        }
        $desc = $product_name;
        if (!empty($detail)) {
            $desc .= "\n" . $detail;
        }
        if (empty($desc)) {
            $desc = 'Custom Item';
        }
        
        $qty = floatval($item['quantity']);
        $price = floatval($item['unit_price']);
        $disc = floatval($item['discount'] ?? 0);
        
        if ($qty <= 0 || $price < 0) continue;
        
        mysqli_stmt_bind_param($stmt_item, "iisddd", $qtn_id, $product_id, $desc, $qty, $price, $disc);
        mysqli_stmt_execute($stmt_item);
        
        $constructed_items[] = [
            'description' => $desc,
            'quantity' => $qty,
            'unit_price' => $price,
            'discount' => $disc,
            'amount' => ($qty * $price) - $disc
        ];
        
        $total_amount += ($qty * $price) - $disc;
    }

    mysqli_query($conn, "UPDATE quotations SET total_amount = $total_amount WHERE qtn_id = $qtn_id");

    $pdf_path = generateQuotationPDF($qtn_number, $customer, $constructed_items, $total_amount);
    mysqli_query($conn, "UPDATE quotations SET file_path = '$pdf_path' WHERE qtn_id = $qtn_id");

    mysqli_commit($conn);
    echo json_encode(['success' => true, 'qtn_id' => $qtn_id, 'pdf' => $pdf_path]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/**
 * @param string $qtn_number 
 * @param array $customer 
 * @param array $items 
 * @param float $total 
 * @return string PDF 
 */
function generateQuotationPDF($qtn_number, $customer, $items, $total) {
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
    $html .= 'Hero Market<br><br>';
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
    $html .= '<tr><td>Your Ref. No</td><td>:</td><td></td></tr>';
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
    $html .= '<th width="48%" style="border-top:1px solid #000; border-bottom:1px solid #000;" align="left"><b>Description</b></th>';
    $html .= '<th width="12%" style="border-top:1px solid #000; border-bottom:1px solid #000;" align="center"><b>Qty</b></th>';
    $html .= '<th width="15%" style="border-top:1px solid #000; border-bottom:1px solid #000;" align="center"><b>Unit Price (RM)</b></th>'; 
    $html .= '<th width="10%" style="border-top:1px solid #000; border-bottom:1px solid #000;" align="center"><b>Disc (RM)</b></th>';
    $html .= '<th width="15%" style="border-top:1px solid #000; border-bottom:1px solid #000;" align="right"><b>Amount (RM)</b></th>'; //  
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    $item_num = 1;
    foreach ($items as $it) {
        $desc_lines = preg_split('/\r\n|\r|\n/', $it['description']);
        $main_desc = $item_num . '. ' . trim($desc_lines[0]);
        $sub_lines = array_slice($desc_lines, 1);

        $html .= '<tr>';
        $html .= '<td width="48%" align="left">';
        $html .= htmlspecialchars($main_desc);
        if (!empty($sub_lines)) {
            $prefix = 'a';
            foreach ($sub_lines as $sub) {
                $trimmed = trim($sub);
                if (!preg_match('/^[a-z]\)\s/i', $trimmed)) {
                    $html .= '<br>&nbsp;&nbsp;' . htmlspecialchars($prefix . ') ' . $trimmed);
                    $prefix++;
                } else {
                    $html .= '<br>&nbsp;&nbsp;' . htmlspecialchars($trimmed);
                }
            }
        }
        $html .= '</td>';
        $html .= '<td width="12%" align="center">' . number_format($it['quantity'], 0) . '</td>';
        $html .= '<td width="15%" align="center">' . number_format($it['unit_price'], 2) . '</td>';
        $html .= '<td width="10%" align="center">' . number_format($it['discount'], 2) . '</td>';
        $html .= '<td width="15%" align="right">' . number_format($it['amount'], 2) . '</td>';
        $html .= '</tr>';
        $item_num++;
    }

    $html .= '<tr><td colspan="5" height="180"></td></tr>';
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
        mkdir($upload_dir, 0777, true);
    }
    $filename = $qtn_number . '_' . time() . '.pdf';
    $filepath = $upload_dir . $filename;
    $pdf->Output($filepath, 'F');

    return 'uploads/quotations/' . $filename;
}
?>