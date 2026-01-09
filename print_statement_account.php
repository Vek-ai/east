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
include_once 'delivery/qrlib.php';

class PDF extends FPDF {
    public string $qrImage = '';

    function Footer() {
        $marginLeft = 5;
        $marginLeft = 5;
        $colWidthLeft  = 115;
        $colWidthRight = 75;
        $this->SetY(-25);

        $colWidth = ($this->w - 2 * $marginLeft) / 3;

        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 51, 153);
        $this->Cell($this->w - 2 * $marginLeft, 5, 'We appreciate your continued business with East Kentucky Metal!', 0, 1, 'C');

        $this->SetTextColor(0, 0, 0);
        $this->SetY($this->GetY() + 1);
        $gpsIcon = 'assets/images/gps.png';
        $text = '977 E. Hal Rogers Parkway';
        $iconWidth = 5;
        $spacing = 2;

        $totalWidth = $iconWidth + $spacing + $this->GetStringWidth($text);
        $x = ($this->w - $totalWidth) / 2;

        $this->Image($gpsIcon, $x, $this->GetY(), $iconWidth, 5);
        $this->SetXY($x + $iconWidth + $spacing, $this->GetY());
        $this->Cell($this->GetStringWidth($text), 5, $text, 0, 1, 'L');

        $this->Ln();
        $this->SetFont('Arial', '', 9);

        $phoneIcon = 'assets/images/phone.png';
        $this->Image($phoneIcon, $marginLeft, $this->GetY(), 5, 5);
        $this->SetXY($marginLeft + 7, $this->GetY());
        $this->Cell($colWidth, 5, '(606) 877-1848 | Fax: (606) 864-4280', 0, 0, 'L');

        $emailIcon = 'assets/images/email.png';
        $this->Image($emailIcon, $marginLeft + $colWidth + 10, $this->GetY(), 5, 5);
        $this->SetXY($marginLeft + $colWidth + 17, $this->GetY());
        $this->Cell($colWidth, 5, 'Sales@EastKentuckyMetal.com', 0, 0, 'L');

        $webIcon = 'assets/images/web.png';
        $this->Image($webIcon, $marginLeft + 2 * $colWidth + 10, $this->GetY(), 5, 5);
        $this->SetXY($marginLeft + 2 * $colWidth + 17, $this->GetY());
        $this->Cell($colWidth, 5, 'EastKentuckyMetal.com', 0, 0, 'L');

        $yStart = $this->GetY() - 35;
        $this->SetFont('Arial', '', 10);
        $this->SetXY($marginLeft, $yStart);
        $this->MultiCell($colWidthRight, 5,
            "Scan me for a Digtal copy of this receipt", 0, 'C');

        $qrX = 20;
        $qrY = $this->GetY();
        if (!empty($this->qrImage) && file_exists($this->qrImage)) {
            $this->Image($this->qrImage, $qrX, $qrY, 25, 25);
        }
    }
}

$pdf = new PDF();
$pdf->SetLeftMargin(5);
$pdf->SetRightMargin(5);
$pdf->SetAutoPageBreak(true, 45);

if (!empty($_REQUEST['id'])) {
    $customer_id = $_REQUEST['id'];
    $customer_name = get_customer_name($customer_id);
    $customer_details = getCustomerDetails($customer_id);

    $token = bin2hex(random_bytes(8));
    $filename = "statement_{$customer_id}_{$token}.pdf";

    $saveDir = $_SERVER['DOCUMENT_ROOT'] . "/statements/";
    if (!is_dir($saveDir)) {
        mkdir($saveDir, 0775, true);
    }

    $baseUrl = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
    $statementUrl = $baseUrl . "/statements/" . $filename;

    $qrTemp = sys_get_temp_dir() . "/qr_statement_{$token}.png";
    QRcode::png($statementUrl, $qrTemp, QR_ECLEVEL_M, 4);
    $pdf->qrImage = $qrTemp;

    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 10);

    $marginLeft = 5;
    $marginRight = 5;
    $pageWidth = $pdf->GetPageWidth();
    $usableWidth = $pageWidth - $marginLeft - $marginRight;

    $pdf->SetFont('Arial', '', 10);
    $pdf->Image('assets/images/logo-bw.png', 5, 6, 60, 20);

    $pdf->SetXY($marginLeft, 26);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, '977 E Hal Rogers Parkway', 0, 1);
    $pdf->Cell(0, 5, 'London, KY 40741', 0, 1);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 5, 'Phone: (606) 877-1848 | Fax: (606) 864-4280', 0, 1);
    $pdf->Cell(0, 5, 'Email: Sales@EastKentuckyMetal.com', 0, 1);
    $pdf->Cell(0, 5, 'Website: EastKentuckyMetal.com', 0, 1);

    $pdf->Ln(5);

    $blueColor = [0, 51, 102];
    $whiteColor = [255, 255, 255];
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFillColor(...$blueColor);

    $rightX = $marginLeft + $usableWidth / 2;
    $rightWidth = $usableWidth / 2;

    $pdf->SetXY($rightX, 10);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell($rightWidth, 10, 'Statement of Account', 0, 1, 'C', true);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetX($rightX);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($rightWidth, 7, 'Statement Period', 0, 1, 'C');

    $date_to = $_REQUEST['date_to'] ?? '';
    $date_from = $_REQUEST['date_from'] ?? '';

    if (!empty($date_from) && !empty($date_to)) {
        $start = new DateTime($date_from);
        $end = new DateTime($date_to);
    } else {
        $first_credit_date = getFirstCreditDate($customer_id, $conn);

        if ($first_credit_date) {
            $start = new DateTime($first_credit_date);
        } else {
            $start = new DateTime('first day of this month');
        }

        $end = new DateTime();
    }

    $suf = fn($d) => ($d % 100 >= 11 && $d % 100 <= 13) ? 'th' : match($d % 10) {
        1 => 'st',
        2 => 'nd',
        3 => 'rd',
        default => 'th',
    };

    $str = sprintf(
        "%s %d%s, %d - %s %d%s, %d",
        $start->format('F'), $startDay = (int)$start->format('j'), $suf($startDay), $start->format('Y'),
        $end->format('F'), $endDay = (int)$end->format('j'), $suf($endDay), $end->format('Y')
    );

    $pdf->SetX($rightX);
    $pdf->Cell($rightWidth, 7, $str, 0, 1, 'C');
    

    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(20);

    $mailToWidth = $usableWidth / 2;
    $mailToY =  $pdf->GetY();

    $line1 = trim(implode(' ', array_filter([
        $customer_details['address'] ?? null,
        $customer_details['city'] ?? null,
    ])));

    $line2 = trim(implode(' ', array_filter([
        $customer_details['state'] ?? null,
        $customer_details['zip'] ?? null,
    ])));

    $pdf->SetXY($marginLeft, $mailToY);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell($mailToWidth, 10, 'Mail to:', 0, 0, 'L', true);
    $pdf->ln(8);
    $pdf->SetFillColor(...$whiteColor);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($mailToWidth, 6, $customer_name, 0, 0, 'L', true);
    $pdf->Ln(5);
    $pdf->Cell($mailToWidth, 6, $line1, 0, 0, 'L', true);
    $pdf->Ln(5);
    $pdf->Cell($mailToWidth, 6, $line2, 0, 0, 'L', true);

    $pdf->SetXY($rightX, $mailToY);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetX($rightX);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell($mailToWidth, 10, 'Customer:', 0, 0, 'L', true);
    $pdf->ln(8);
    $pdf->SetFillColor(...$whiteColor);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetX($rightX);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($mailToWidth, 6, $customer_name, 0, 0, 'L', true);
    $pdf->Ln(5);
    $pdf->SetX($rightX);
    $pdf->Cell($mailToWidth, 6, $line1, 0, 0, 'L', true);
    $pdf->Ln(5);
    $pdf->SetX($rightX);
    $pdf->Cell($mailToWidth, 6, $line2, 0, 0, 'L', true);

    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->MultiCell(0, 5, "Invoices", 1, 'C', true);

    $pdf->SetFillColor(230, 240, 255);
    $pdf->SetFont('Arial', 'B', 10);

    $scale = $usableWidth / 135;
    $w1 = 25 * $scale;
    $w2 = 25 * $scale;
    $w3 = 30 * $scale;
    $w4 = 30 * $scale;
    $w5 = 25 * $scale;

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFillColor(255, 255, 255);

    $pdf->Cell($w1, 7, 'Date', 1, 0, 'C', true);
    $pdf->Cell($w2, 7, 'Order ID', 1, 0, 'C', true);
    $pdf->Cell($w3, 7, 'Job Name', 1, 0, 'C', true);
    $pdf->Cell($w4, 7, 'PO #', 1, 0, 'C', true);
    $pdf->Cell($w5, 7, 'Amount', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 10);

    $total_credit = 0;
    

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
    ";

    if (!empty($date_from) && !empty($date_to)) {
        $from = mysqli_real_escape_string($conn, $date_from);
        $to = mysqli_real_escape_string($conn, $date_to);
        $query .= " AND DATE(l.created_at) BETWEEN '$from' AND '$to'";
    }

    $query .= " ORDER BY l.created_at ASC";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $balance = 0;
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

            $pdf->Cell($w1, 6, $date, 1, 0, 'C');
            $pdf->Cell($w2, 6, $orderid, 1, 0, 'C');
            $pdf->Cell($w3, 6, $job_name, 1, 0, 'C');
            $pdf->Cell($w4, 6, $po_number, 1, 0, 'C');
            $pdf->Cell($w5, 6, '$' . number_format($balance, 2), 1, 1, 'R');

            $total_credit += $receivable;
        }
    }

    $pdf->Ln(3);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetX(115);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(50, 7, 'Total Invoices Due:', 1, 0, 'R', true);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($w5, 7, '$' . number_format($total_credit, 2), 1, 1, 'R', true);
    
    $pdf->Ln(10);

    

    $query = "
        SELECT 
            c.created_at AS date,
            c.id,
            c.credit_amount AS amount,
            NULL AS job_id,
            'credit_history' AS source
        FROM 
            customer_store_credit_history c
        WHERE 
            c.customer_id = '$customer_id'

        UNION ALL

        SELECT 
            jd.created_at AS date,
            jd.deposit_id AS id,
            jd.deposit_remaining AS amount,
            j.job_id,
            'job_deposit' AS source
        FROM 
            job_deposits jd
        JOIN 
            jobs j ON jd.job_id = j.job_id
        WHERE 
            j.customer_id = '$customer_id'

        ORDER BY date ASC
    ";

    $total_available = 0;
    $result_available = mysqli_query($conn, $query);
    if ($result_available && mysqli_num_rows($result_available) > 0) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 5, "Available Credits", 1, 'C', true);

        $pdf->SetFillColor(230, 240, 255);
        $pdf->SetFont('Arial', 'B', 10);

        $scale = $usableWidth / 135;
        $w1 = 25 * $scale;
        $w2 = 25 * $scale;
        $w3 = 30 * $scale;
        $w4 = 30 * $scale;
        $w5 = 25 * $scale;

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(255, 255, 255);

        $pdf->Cell($w1, 7, 'Date', 1, 0, 'C', true);
        $pdf->Cell($w2, 7, 'Credit #', 1, 0, 'C', true);
        $pdf->Cell($w3, 7, 'Job Name', 1, 0, 'C', true);
        $pdf->Cell($w4, 7, 'PO #', 1, 0, 'C', true);
        $pdf->Cell($w5, 7, 'Amount', 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);

        
        $available = 0;
        while ($row = mysqli_fetch_assoc($result_available)) {
            $date = date('M d, Y', strtotime($row['date']));
            $source = $row['source'];
            $id = ($source == 'job_deposit' ? 'C' : 'RC') . '-' . $row['id'];
            $job_id = $row['job_id'];
            $job_details = getJobDetails($job_id);
            $job_name = $job_details['job_name'] ?? '';

            $available = $row['amount'] ?? '';

            if ($available <= 0) continue;

            $pdf->Cell($w1, 6, $date, 1, 0, 'C');
            $pdf->Cell($w2, 6, $id, 1, 0, 'C');
            $pdf->Cell($w3, 6, $job_name, 1, 0, 'C');
            $pdf->Cell($w4, 6, '', 1, 0, 'C');
            $pdf->Cell($w5, 6, '$' . number_format($available, 2), 1, 1, 'R');

            $total_available += $available;
        }

        $pdf->Ln(3);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetX(115);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(50, 7, 'Total Available Credits:', 1, 0, 'R', true);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell($w5, 7, '$' . number_format($total_available, 2), 1, 1, 'R', true);
        $pdf->Ln(5);
    }

    

    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell($usableWidth, 7, 'Remaining Balance Due (if all Credits applied)', 0, 0, 'C', true);
    $pdf->ln(7);

    /* $pdf->SetX(115);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(50, 5, 'Total Invoices Due:', 1, 0, 'R', true);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($w5, 5, '$' . number_format($total_credit, 2), 1, 1, 'R', true);

    if ($result_available && mysqli_num_rows($result_available) > 0) {
        $pdf->SetX(115);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(50, 5, 'Total Available Credits:', 1, 0, 'R', true);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell($w5, 5, '$' . number_format($total_available, 2), 1, 1, 'R', true);
    } */

    $pdf->SetX(115);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(50, 5, 'Balance Due:', 1, 0, 'R', true);
    $pdf->Cell($w5, 5, '$' . number_format(max(0, $total_credit - $total_available), 2), 1, 1, 'R', true);
    $pdf->Ln(5);

    $colWidthLeft  = 90;
    $colWidthRight = 90;

    $yStart = $pdf->GetY();

    $pdf->SetTitle('Statement of Account');

    $filePath = $saveDir . $filename;
    $pdf->Output($filePath, 'F');
    $pdf->Output('Statement_of_Account.pdf', 'I');
    register_shutdown_function(fn() => @unlink($qrTemp));
}

?>
