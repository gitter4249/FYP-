<?php

require_once '../includes/TCPDF-main/TCPDF-main/tcpdf.php';

/**
 *
 * @param int $inv_id 
 * @param string $invoice_number 
 * @param array $customer 
 * @param array $items
 * @param float $total
 * @param string $issue_date
 * @param string $notes_text 
 * @param float $stage_percent 
 * @param string $stage_name 
 * @return string PDF 
 */
function generateInvoicePDF($inv_id, $invoice_number, $customer, $items, $total, $issue_date, $notes_text, $stage_percent = 1.0, $stage_name = '') {
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('YS Aluminium');
    $pdf->SetAuthor('YS Aluminium');
    $pdf->SetTitle("Invoice $invoice_number");
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->SetPrintHeader(false);
    $pdf->SetPrintFooter(false);
    $pdf->AddPage();

    $pdf->SetFont('helvetica', '', 9);

    $html = '<div style="text-align:center; line-height:1.2; font-family:helvetica;">';
    $html .= '<span style="font-size:16pt;"><b>YONG SHENG ALU ENTERPRISE</b></span><br>';
    $html .= '<span style="font-size:9pt;">No,46 JALAN BELADAU 3,<br>';
    $html .= 'TAMAN PUTERI WANGSA,<br>';
    $html .= '81800 ULU TIRAM<br>';
    $html .= 'JOHOR BAHRU</span>';
    $html .= '</div>';

    $html .= '<div style="font-size:18pt; font-weight:bold; margin-top:10px;">Invoice</div>';
    if (!empty($stage_name)) {
        $html .= '<div style="font-size:11pt; color:#0d6efd; font-weight:600; margin-bottom:5px;">' . htmlspecialchars($stage_name) . ' Invoice</div>';
    }
    $html .= '<div style="border-bottom:1px solid #000; margin-bottom:8px;"></div>';

    $html .= '<table width="100%" cellpadding="1" style="font-size:8pt; font-family:helvetica;">';
    $html .= '<tr>';
    $html .= '<td width="50%">';
    $html .= 'Billing Address<br>';
    $html .= '<span style="font-size:10pt;"><b>ONLINE SALES</b></span><br>';
    $html .= htmlspecialchars($customer['address'] ?? 'NO.20 JALAN EKO TROPIKA 3/13, KOTA MASAI<br>81700 PASIR GUDANG JOHOR DARUL TAKZIM') . '<br><br>';
    $html .= 'Attn: ' . htmlspecialchars($customer['name']) . '<br>';
    $html .= 'Tel: ' . htmlspecialchars($customer['phone'] ?? '') . '<br>';
    $html .= 'Fax: ';
    $html .= '</td>';
    $html .= '<td width="50%">';
    $html .= 'Job Site Address<br>';
    $html .= '<br><br><br><br>';
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '</table>';
    $html .= '<br><br>';

    $formatted_date = date('d/m/Y', strtotime($issue_date));
    $html .= '<table width="100%" cellpadding="3" style="font-size:7pt; border-top:1px solid #000; border-bottom:1px solid #000; font-family:helvetica;">';
    $html .= '<tr>';
    $html .= '<td width="12%">Customer Account</td>';
    $html .= '<td width="12%">Sales Executive</td>';
    $html .= '<td width="8%">Currency</td>';
    $html .= '<td width="12%">From Doc Date</td>';
    $html .= '<td width="15%">From Doc No</td>';
    $html .= '<td width="11%">Name</td>';
    $html .= '<td width="10%">Page No</td>';
    $html .= '<td width="10%">Invoice No.</td>';
    $html .= '<td width="10%" align="right">Date</td>';
    $html .= '</tr>';
    $html .= '<tr style="font-weight:bold;">';
    $html .= '<td>3000/0002</td>';
    $html .= '<td>----</td>';
    $html .= '<td>MYR</td>';
    $html .= '<td>' . $formatted_date . '</td>';
    $html .= '<td>QT-00793-26</td>';
    $html .= '<td>ADMIN</td>';
    $html .= '<td>1 of 1</td>';
    $html .= '<td>' . $invoice_number . '</td>';
    $html .= '<td align="right">' . $formatted_date . '</td>';
    $html .= '</tr>';
    $html .= '</table>';
    $html .= '<br>';

    $html .= '<table width="100%" cellpadding="3" style="font-size:8pt; font-family:helvetica;">';
    $html .= '<tr>';
    $html .= '<th width="45%" align="left" style="font-size:7pt; color:#666;">Description</th>';
    $html .= '<th width="10%" align="center" style="font-size:7pt; color:#666;">Qty</th>';
    $html .= '<th width="15%" align="right" style="font-size:7pt; color:#666;">Price/Unit</th>';
    $html .= '<th width="15%" align="right" style="font-size:7pt; color:#666;">Discount</th>';
    $percent_display = ($stage_percent < 1.0) ? ' (' . ($stage_percent * 100) . '%)' : '';
    $html .= '<th width="15%" align="right" style="font-size:7pt; color:#666;">Total' . $percent_display . '</th>';
    $html .= '</tr>';

    $scaled_total = 0;
    foreach ($items as $it) {
        $qty = floatval($it['quantity']);
        $price = floatval($it['unit_price']);
        $disc = floatval($it['discount'] ?? 0);
        $full_amount = ($qty * $price) - $disc;
        $scaled_amount = $full_amount * $stage_percent;
        $scaled_total += $scaled_amount;

        $desc_lines = preg_split('/\r\n|\r|\n/', $it['description']);
        $html .= '<tr>';
        $html .= '<td width="45%" align="left">';
        foreach ($desc_lines as $line) {
            $html .= htmlspecialchars(trim($line)) . '<br>';
        }
        $html .= '</td>';
        $html .= '<td width="10%" align="center">' . number_format($qty, 2) . '</td>';
        $html .= '<td width="15%" align="right">' . number_format($price, 2) . '</td>';
        $html .= '<td width="15%" align="right">' . ($disc > 0 ? number_format($disc, 2) : '') . '</td>';
        $html .= '<td width="15%" align="right">' . number_format($scaled_amount, 2) . '</td>';
        $html .= '</tr>';
    }
    if ($stage_percent < 1.0) {
        $html .= '<tr><td colspan="4" align="right" style="font-style:italic;">Subtotal (' . ($stage_percent * 100) . '% of quoted amount)</td>';
        $html .= '<td align="right"><strong>' . number_format($scaled_total, 2) . '</strong></td></tr>';
    }
    $html .= '<tr><td colspan="5" height="50"> </td></tr>';
    $html .= '</table>';

    $html .= '<div style="font-size:8pt; text-transform:uppercase; margin-bottom:5px;">';
    $html .= 'RINGGIT MALAYSIA : ' . numberToWords($scaled_total) . ' ONLY';
    $html .= '</div>';

    $html .= '<table width="100%" cellpadding="3" style="font-size:8pt; border-top:1px solid #000; border-bottom:1px solid #000; font-family:helvetica;">';
    $html .= '<tr>';
    $html .= '<td width="50%">';
    $html .= '<span style="font-size:7pt; color:#666;">Payment Terms :</span><br>';
    $html .= '<b>0 Days</b>';
    $html .= '</td>';
    $html .= '<td width="50%" align="right">';
    $html .= '<span style="font-size:7pt; color:#666;">Total Payable</span><br>';
    $html .= '<b style="font-size:10pt;">' . number_format($scaled_total, 2) . '</b>';
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '</table>';
    $html .= '<br>';

    $html .= '<table width="100%" cellpadding="2" style="font-size:8pt; font-family:helvetica;">';
    $html .= '<tr>';
    $html .= '<td width="60%" style="line-height:1.3;">';
    $html .= '<i>Notes :</i><br>';
    $html .= '1. All cheques should be crossed and made payable to YONG SHENG ALU ENTERPRISE.<br>';
    $html .= '2. Our bank account is public bank (PBB Account No-5123-4567-8901)<br>';
    $html .= '3. Goods sold are neither returnable nor refundable. Otherwise a cancellation fee 5% on<br>&nbsp;&nbsp;&nbsp;&nbsp;purchase price will be imposed.';
    if(!empty($notes_text)) {
        $html .= '<br>' . nl2br(htmlspecialchars($notes_text));
    }
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '</table>';

    $pdf->writeHTML($html, true, false, true, false, '');

    $upload_dir = __DIR__ . '/../uploads/invoices/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $filename = $invoice_number . '_' . time() . '.pdf';
    $filepath = $upload_dir . $filename;
    $pdf->Output($filepath, 'F');

    return 'uploads/invoices/' . $filename;
}

function numberToWords($number) {
    $hyphen      = '-';
    $conjunction = ' AND ';
    $separator   = ' ';
    $negative    = 'NEGATIVE ';
    $decimal     = ' POINT ';
    $dictionary  = array(
        0 => 'ZERO', 1 => 'ONE', 2 => 'TWO', 3 => 'THREE', 4 => 'FOUR', 5 => 'FIVE',
        6 => 'SIX', 7 => 'SEVEN', 8 => 'EIGHT', 9 => 'NINE', 10 => 'TEN',
        11 => 'ELEVEN', 12 => 'TWELVE', 13 => 'THIRTEEN', 14 => 'FOURTEEN',
        15 => 'FIFTEEN', 16 => 'SIXTEEN', 17 => 'SEVENTEEN', 18 => 'EIGHTEEN',
        19 => 'NINETEEN', 20 => 'TWENTY', 30 => 'THIRTY', 40 => 'FORTY',
        50 => 'FIFTY', 60 => 'SIXTY', 70 => 'SEVENTY', 80 => 'EIGHTY', 90 => 'NINETY',
        100 => 'HUNDRED', 1000 => 'THOUSAND', 1000000 => 'MILLION', 1000000000 => 'BILLION',
    );

    if (!is_numeric($number)) return false;
    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) return false;
    if ($number < 0) return $negative . numberToWords(abs($number));

    $string = $fraction = null;
    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[(int)$number];
            break;
        case $number < 100:
            $tens = ((int) ($number / 10)) * 10;
            $units = $number % 10;
            $string = $dictionary[$tens];
            if ($units) $string .= $hyphen . $dictionary[(int)$units];
            break;
        case $number < 1000:
            $hundreds = (int)($number / 100);
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) $string .= $conjunction . numberToWords($remainder);
            break;
        default:
            $baseUnit = (int) pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = numberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= numberToWords($remainder);
            }
            break;
    }

    if (null !== $fraction && is_numeric($fraction) && intval($fraction) > 0) {
        $string .= $conjunction . numberToWords(intval($fraction)) . ' CENTS';
    }
    return $string;
}
?>