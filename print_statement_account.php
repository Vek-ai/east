<?php
session_start();
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 1);

if (!isset($_SESSION['userid'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?redirect=$redirect_url");
    exit();
}

require 'includes/fpdf/fpdf.php';
require 'includes/dbconn.php';
require 'includes/functions.php';

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

if (!empty($_REQUEST['id'])) {
    $customer_id = $_REQUEST['id'];
    $customer_name = get_customer_name($customer_id);

    $pdf->Image('assets/images/logo-bw.png', 10, 6, 60, 20);
    $pdf->SetXY(10, 26);
    $pdf->Cell(0, 5, '977 E Hal Rogers Parkway, London, KY 40741', 0, 1);
    $pdf->Cell(0, 5, 'Phone: 606-877-1848 / Toll-Free: 877-303-3322', 0, 1);
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->MultiCell(0, 5, "Accounts Receivable for $customer_name", 0, 'L');
    $pdf->Ln(5);

    $pdf->SetFillColor(230, 240, 255);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 7, 'Order ID', 1, 0, 'C', true);
    $pdf->Cell(25, 7, 'Date', 1, 0, 'C', true);
    $pdf->Cell(30, 7, 'Job', 1, 0, 'C', true);
    $pdf->Cell(30, 7, 'PO Number', 1, 0, 'C', true);
    $pdf->Cell(30, 7, 'Payment Method', 1, 0, 'C', true);
    $pdf->Cell(25, 7, 'Receivable', 1, 0, 'C', true);
    $pdf->Cell(25, 7, 'Balance', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetFillColor(255, 255, 255);

    $total_credit = 0;
    $balance = 0;

    $pay_labels = [
        'pickup'   => ['label' => 'Pay at Pick-up'],
        'delivery' => ['label' => 'Pay at Delivery'],
        'cash'     => ['label' => 'Cash'],
        'check'    => ['label' => 'Check'],
        'card'     => ['label' => 'Credit/Debit Card'],
        'net30'    => ['label' => 'Charge Net 30'],
        'job_deposit'    => ['label' => 'Job Deposit'],
    ];

    $query = "
        SELECT 
            l.ledger_id,
            l.job_id,
            l.created_at AS date,
            l.description,
            j.job_name,
            l.po_number,
            l.entry_type,
            l.reference_no AS orderid,
            l.payment_method,
            CASE WHEN l.entry_type = 'credit' THEN l.amount ELSE NULL END AS credit
        FROM job_ledger l
        LEFT JOIN jobs j ON l.job_id = j.job_id
        WHERE l.customer_id = '$customer_id' AND l.entry_type = 'credit'
        ORDER BY l.created_at ASC;
    ";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $orderid = $row['orderid'];
            $date = date('M d, Y', strtotime($row['date']));
            $job_name = $row['job_name'];
            $po_number = $row['po_number'];
            $credit = $row['credit'] ? floatval($row['credit']) : 0;
            $total_payments = getTotalJobPayments($row['ledger_id']);
            $receivable = max($credit - $total_payments, 0);

            if ($receivable <= 0) continue;

            $balance += $receivable;

            $pay_key = strtolower(trim($row['payment_method']));
            $pay_label = $pay_labels[$pay_key]['label'] ?? ucfirst($pay_key);

            $pdf->Cell(25, 6, $orderid, 1, 0, 'C');
            $pdf->Cell(25, 6, $date, 1, 0, 'C');
            $pdf->Cell(30, 6, $job_name, 1, 0, 'C');
            $pdf->Cell(30, 6, $po_number, 1, 0, 'C');
            $pdf->Cell(30, 6, $pay_label, 1, 0, 'C');
            $pdf->Cell(25, 6, $receivable > 0 ? '$' . number_format($receivable, 2) : '', 1, 0, 'R');
            $pdf->Cell(25, 6, '$' . number_format($balance, 2), 1, 1, 'R');

            $total_credit += $receivable;
        }
    }

    $pdf->Ln(3);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(165, 7, 'TOTAL RECEIVABLE:', 0, 0, 'R');
    $pdf->Cell(25, 7, '$' . number_format($total_credit, 2), 0, 1, 'R');
    $pdf->Ln(5);

    $pdf->SetTitle('Statement of Account');
    $pdf->Output('Statement_of_Account.pdf', 'I');
}

?>
