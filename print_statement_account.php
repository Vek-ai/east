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

class PDF extends FPDF {
    function WriteHTML($html) {
        $html = str_replace("\n", ' ', $html);
        $html = html_entity_decode($html);

        $tokens = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        $is_bold = false;
        $is_italic = false;
        $is_underline = false;

        foreach ($tokens as $i => $token) {
            if ($i % 2 == 0) {
                if ($token !== '') {
                    $this->Write(5, $token);
                }
            } else {
                if ($token[0] == '/') {
                    $tag = strtolower(substr($token, 1));
                    if ($tag == 'b') $is_bold = false;
                    elseif ($tag == 'i') $is_italic = false;
                    elseif ($tag == 'u') $is_underline = false;
                    $this->SetFont('', ($is_bold ? 'B' : '') . ($is_italic ? 'I' : '') . ($is_underline ? 'U' : ''));
                } else {
                    $tag = strtolower(preg_replace('/ .*/', '', $token));
                    if ($tag == 'b') $is_bold = true;
                    elseif ($tag == 'i') $is_italic = true;
                    elseif ($tag == 'u') $is_underline = true;
                    elseif ($tag == 'br') {
                        $this->Ln(5);
                        continue;
                    }
                    $this->SetFont('', ($is_bold ? 'B' : '') . ($is_italic ? 'I' : '') . ($is_underline ? 'U' : ''));
                }
            }
        }
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

if (!empty($_REQUEST['id'])) {
    $customer_id = $_REQUEST['id'];
    $customer_name = get_customer_name($customer_id);

    $marginLeft = 10;
    $marginRight = 10;
    $pageWidth = $pdf->GetPageWidth();
    $usableWidth = $pageWidth - $marginLeft - $marginRight;

    $pdf->Image('assets/images/logo-bw.png', $marginLeft, 6, 60, 20);

    $pdf->SetXY($marginLeft, 26);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, '977 E Hal Rogers Parkway', 0, 1);
    $pdf->Cell(0, 5, 'London, KY 40741', 0, 1);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 5, 'Phone: (606) 877-1848 | Fax: (606) 864-4280', 0, 1);
    $pdf->Cell(0, 5, 'Email: Sales@Eastkentuckymetal.com', 0, 1);
    $pdf->Cell(0, 5, 'Website: Eastkentuckymetal.com', 0, 1);

    $pdf->Ln(5);

    $blueColor = [0, 51, 102];
    $whiteColor = [255, 255, 255];
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFillColor(...$blueColor);

    $rightX = $marginLeft + $usableWidth / 2;
    $rightWidth = $usableWidth / 2;

    $pdf->SetXY($rightX, 26);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell($rightWidth, 10, 'Statement of Account', 0, 1, 'C', true);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetX($rightX);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($rightWidth, 7, 'Statement Period', 0, 1, 'C');
    
    $suf = fn($d) => ($d % 100 >= 11 && $d % 100 <= 13) ? 'th' : (match($d % 10) {
        1 => 'st',
        2 => 'nd',
        3 => 'rd',
        default => 'th',
    });

    $start = new DateTime('first day of this month');
    $end = new DateTime();

    $str = sprintf(
        "%s %d%s, %d - %s %d%s, %d",
        $start->format('F'), $startDay = (int)$start->format('j'), $suf($startDay), $start->format('Y'),
        $end->format('F'), $endDay = (int)$end->format('j'), $suf($endDay), $end->format('Y')
    );

    $pdf->SetX($rightX);
    $pdf->Cell($rightWidth, 7, $str, 0, 1, 'C');

    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(10);

    $mailToWidth = $usableWidth / 2;
    $mailToY =  $pdf->GetY();

    $pdf->SetXY($marginLeft, $mailToY);
    $pdf->SetFillColor(...$blueColor);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell($mailToWidth, 10, 'Mail to:', 0, 0, 'L', true);
    $pdf->ln(8);
    $pdf->SetFillColor(...$whiteColor);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell($mailToWidth, 6, $customer_name, 0, 0, 'L', true);
    $pdf->Ln(5);
    $pdf->Cell($mailToWidth, 6, '00 Mill Creek Rd', 0, 0, 'L', true);
    $pdf->Ln(5);
    $pdf->Cell($mailToWidth, 6, 'London, KY 40701', 0, 0, 'L', true);

    $pdf->SetXY($rightX, $mailToY);
    $pdf->SetFillColor(...$blueColor);
    $pdf->SetTextColor(255, 255, 255);
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
    $pdf->Cell($mailToWidth, 6, '00 Mill Creek Rd', 0, 0, 'L', true);
    $pdf->Ln(5);
    $pdf->SetX($rightX);
    $pdf->Cell($mailToWidth, 6, 'London, KY 40701', 0, 0, 'L', true);

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

    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFillColor(...$blueColor);

    $pdf->Cell($w1, 7, 'Date', 1, 0, 'C', true);
    $pdf->Cell($w2, 7, 'Order ID', 1, 0, 'C', true);
    $pdf->Cell($w3, 7, 'Job Name', 1, 0, 'C', true);
    $pdf->Cell($w4, 7, 'PO #', 1, 0, 'C', true);
    $pdf->Cell($w5, 7, 'Amount', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);

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

    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFillColor(...$blueColor);

    $pdf->Cell($w1, 7, 'Date', 1, 0, 'C', true);
    $pdf->Cell($w2, 7, 'Credit #', 1, 0, 'C', true);
    $pdf->Cell($w3, 7, 'Job Name', 1, 0, 'C', true);
    $pdf->Cell($w4, 7, 'PO #', 1, 0, 'C', true);
    $pdf->Cell($w5, 7, 'Amount', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);

    $total_available = 0;
    $available = 0;

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
    ";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $date = date('M d, Y', strtotime($row['date']));
            $id = $row['id'];
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

    $pdf->SetFillColor(...$blueColor);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell($usableWidth, 10, 'Remaining Balance Due (if all Credits applied)', 0, 0, 'C', true);
    $pdf->ln(10);

    $pdf->SetX(115);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(50, 5, 'Total Invoices Due:', 1, 0, 'R', true);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($w5, 5, '$' . number_format($total_credit, 2), 1, 1, 'R', true);

    $pdf->SetX(115);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(50, 5, 'Total Available Credits:', 1, 0, 'R', true);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($w5, 5, '$' . number_format($total_available, 2), 1, 1, 'R', true);

    $pdf->SetX(115);
    $pdf->SetFillColor(255, 0, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(50, 5, 'Balance Due:', 1, 0, 'R', true);
    $pdf->Cell($w5, 5, '$' . number_format(max(0, $total_credit - $total_available), 2), 1, 1, 'R', true);
    $pdf->Ln(5);

    $pdf->SetTitle('Statement of Account');
    $pdf->Output('Statement_of_Account.pdf', 'I');
}

?>
