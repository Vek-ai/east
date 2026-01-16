<?php
session_start();
require 'includes/fpdf/fpdf.php';
require 'includes/dbconn.php';
require 'includes/functions.php';

if (!isset($_SESSION['userid'])) {
    exit;
}

$date_from = $_GET['from'] ?? '';
$date_to   = $_GET['to'] ?? '';

$dateFromSQL = date('Y-m-d 00:00:00', strtotime($date_from));
$dateToSQL   = date('Y-m-d 23:59:59', strtotime($date_to));

if (!$date_from || !$date_to) {
    exit;
}

$query = "
    SELECT 
        cf.orderid,
        cf.amount,
        cf.date,
        cf.cash_flow_type,
        o.pay_type,
        o.customerid
    FROM cash_flow cf
    LEFT JOIN orders o ON o.orderid = cf.orderid
    WHERE DATE(cf.date) BETWEEN '$dateFromSQL' AND '$dateToSQL'
    AND cf.movement_type = 'cash_inflow'
    ORDER BY cf.date ASC
";

$result = $conn->query($query);
if (!$result || $result->num_rows === 0) {
    echo 'No Cash Flows found for this period.';
    exit;
}

$groups = [];
$grandTotal = 0;

while ($row = mysqli_fetch_assoc($result)) {

    $payTypeRaw = strtolower($row['pay_type'] ?? '');

    if ($row['cash_flow_type'] === 'job_deposit') {
        $payType = 'Account Payments';
    } elseif (strpos($payTypeRaw, 'cash') !== false) {
        $payType = 'Cash';
    } elseif (strpos($payTypeRaw, 'card') !== false) {
        $payType = 'Credit/Debit Card';
    } elseif (strpos($payTypeRaw, 'check') !== false || strpos($payTypeRaw, 'cheque') !== false) {
        $payType = 'Check';
    } elseif (strpos($payTypeRaw, 'pickup') !== false) {
        $payType = 'Pay at Pick-Up';
    } elseif (strpos($payTypeRaw, 'delivery') !== false) {
        $payType = 'Pay at Delivery';
    } elseif (strpos($payTypeRaw, 'net30') !== false) {
        $payType = 'Charge Net 30';
    } else {
        $payType = 'Other';
    }

    $groups[$payType]['rows'][] = $row;
    $groups[$payType]['total'] = ($groups[$payType]['total'] ?? 0) + $row['amount'];
    $grandTotal += $row['amount'];
}

if (isset($groups['Other'])) {
    $otherGroup = $groups['Other'];
    unset($groups['Other']);
    $groups['Other'] = $otherGroup;
}

class PDF extends FPDF {

    public $titleText;
    public $rangeText;

    function Header() {
        $this->Image('assets/images/logo-bw.png', 5, 6, 60, 20);

        $this->SetFont('Arial', 'B', 28);
        $this->SetY(10);
        $this->Cell(0, 15, $this->titleText, 0, 1, 'C');

        $this->SetFont('Arial', '', 16);
        $this->Cell(0, 6, '(' . $this->rangeText . ')', 0, 1, 'C');

        $this->Ln(8);
    }
}

$pdf = new PDF();
$pdf->titleText = 'Cash Flow';
$pdf->rangeText = date('n/j/Y', strtotime($date_from)) . ' - ' . date('n/j/Y', strtotime($date_to));

$pdf->SetLeftMargin(5);
$pdf->SetRightMargin(5);
$pdf->SetAutoPageBreak(true, 5);
$pdf->AddPage();

foreach ($groups as $payType => $group) {

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(200, 200, 200);
    $pdf->Cell(200, 8, 'Payment Type: ' .$payType, 1, 1, 'L', true);

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(220, 220, 220);

    $pdf->Cell(18, 7, 'Invoice #', 1, 0, 'C', true);
    $pdf->Cell(24, 7, 'First Name', 1, 0, 'C', true);
    $pdf->Cell(24, 7, 'Last Name', 1, 0, 'C', true);
    $pdf->Cell(30, 7, 'Business', 1, 0, 'C', true);
    $pdf->Cell(30, 7, 'Farm', 1, 0, 'C', true);
    $pdf->Cell(36, 7, 'Job Name', 1, 0, 'C', true);
    $pdf->Cell(18, 7, 'PO #', 1, 0, 'C', true);
    $pdf->Cell(20, 7, 'Amount', 1, 1, 'R', true);

    $pdf->SetFont('Arial', '', 8);

    foreach ($group['rows'] as $row) {

        $order = getOrderDetails($row['orderid']);
        $customer = getCustomerDetails($order['customerid'] ?? '');

        $pdf->Cell(18, 7, $order['orderid'] ?? '-', 1);
        $pdf->Cell(24, 7, $customer['customer_first_name'] ?? '-', 1);
        $pdf->Cell(24, 7, $customer['customer_last_name'] ?? '-', 1);
        $pdf->Cell(30, 7, $customer['customer_business_name'] ?? '-', 1);
        $pdf->Cell(30, 7, $customer['customer_farm_name'] ?? '-', 1);
        $pdf->Cell(36, 7, $order['job_name'] ?? '-', 1);
        $pdf->Cell(18, 7, $order['job_po'] ?? '-', 1);
        $pdf->Cell(20, 7, '$' . number_format($row['amount'], 2), 1, 1, 'R');
    }

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(180, 8, $payType . ' Total', 1, 0, 'R');
    $pdf->Cell(20, 8, '$' . number_format($group['total'], 2), 1, 1, 'R');

    $pdf->Ln(4);
}

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(180, 10, 'TOTAL CASH INFLOW', 1, 0, 'R');
$pdf->Cell(20, 10, '$' . number_format($grandTotal, 2), 1, 1, 'R');

$pdf->Output('Cash_Flow_' . $dateFromSQL . '_to_' . $dateToSQL . '.pdf', 'I');
