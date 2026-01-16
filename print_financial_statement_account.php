<?php
session_start();
require 'includes/fpdf/fpdf.php';
require 'includes/dbconn.php';
require 'includes/functions.php';

if (!isset($_SESSION['userid'])) exit;

$date_from = $_GET['from'] ?? '';
$date_to   = $_GET['to'] ?? '';
if (!$date_from || !$date_to) exit;

$dateSQLStart = date('Y-m-d 00:00:00', strtotime($date_from));
$dateSQLEnd   = date('Y-m-d 23:59:59', strtotime($date_to));

$sql = "
SELECT
    c.customer_id,
    c.customer_first_name,
    c.customer_last_name,
    c.customer_business_name,
    c.customer_farm_name,

    COALESCE(sc.total_store_credit, 0) + COALESCE(dp.total_deposits, 0) AS available_balance,
    COALESCE(cr.total_credit, 0) - COALESCE(py.total_paid, 0) AS outstanding_credit,
    cr.first_credit_date,
    py.last_payment_date
FROM customer c

LEFT JOIN (
    SELECT customer_id, SUM(credit_amount) AS total_store_credit
    FROM customer_store_credit_history
    WHERE credit_type = 'add' AND credit_amount > 0
    GROUP BY customer_id
) sc ON sc.customer_id = c.customer_id

LEFT JOIN (
    SELECT deposited_by AS customer_id, SUM(deposit_remaining) AS total_deposits
    FROM job_deposits
    WHERE deposit_status = 1 AND deposit_remaining > 0
    GROUP BY deposited_by
) dp ON dp.customer_id = c.customer_id

LEFT JOIN (
    SELECT
        customer_id,
        SUM(amount) AS total_credit,
        MIN(created_at) AS first_credit_date
    FROM job_ledger
    WHERE entry_type = 'credit' AND created_at <= '$dateSQLEnd'
    GROUP BY customer_id
) cr ON cr.customer_id = c.customer_id

LEFT JOIN (
    SELECT
        jl.customer_id,
        SUM(jp.amount) AS total_paid,
        MAX(jp.created_at) AS last_payment_date
    FROM job_payment jp
    INNER JOIN job_ledger jl ON jl.ledger_id = jp.ledger_id
    WHERE jp.status = 1 AND jp.created_at <= '$dateSQLEnd'
    GROUP BY jl.customer_id
) py ON py.customer_id = c.customer_id

WHERE c.status = 1
HAVING available_balance > 0 OR outstanding_credit > 0
ORDER BY c.customer_last_name, c.customer_first_name
";

$result = $conn->query($sql);
if (!$result || $result->num_rows === 0) {
    echo 'No receivable or credit balances found for this period.';
    exit;
}

class PDF extends FPDF {
    public $titleText;
    public $rangeText;

    function Header() {
        $this->Image('assets/images/logo-bw.png', 5, 6, 60, 20);
        $this->SetFont('Arial', 'B', 28);
        $this->SetXY(60,10);
        $this->Cell(140, 15, $this->titleText, 0, 1, 'C');
        $this->SetX(60);
        $this->SetFont('Arial', '', 16);
        $this->Cell(140, 6, '(' . $this->rangeText . ')', 0, 1, 'C');
        $this->Ln(8);
    }

    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb-1] == "\n") $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) $i++;
                } else $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else $i++;
        }
        return $nl;
    }

    function GetMultiCellHeight($w, $lineHeight, $txt) {
        return $this->NbLines($w, $txt) * $lineHeight;
    }

    public function renderRow($columns, $row, $bold = false, $note = '') {
        $lineHeight = 5;
        $xStart = $this->GetX();
        $yStart = $this->GetY();

        $row = array_slice($row, 0, count($columns));
        $heights = [];
        $cellTexts = [];
        $cellStyles = [];

        foreach ($columns as $i => $col) {
            $w = $col['width'];
            $fontSize = $col['fontsize'] ?? 9;
            $style = $bold ? 'B' : '';
            $cellStyles[$i] = $style;
            $cellTexts[$i] = $row[$i];
            $this->SetFont('Arial', $style, $fontSize);
            $heights[$i] = $this->GetMultiCellHeight($w, $lineHeight, $cellTexts[$i]);
        }

        $rowHeight = max($heights);

        if ($yStart + $rowHeight > $this->h - $this->bMargin) $this->AddPage();

        $x = $xStart;
        foreach ($columns as $i => $col) {
            $w = $col['width'];
            $fontSize = $col['fontsize'] ?? 8;
            $this->SetFont('Arial', $cellStyles[$i], $fontSize);
            $saveX = $x;
            $saveY = $yStart;
            $this->SetXY($saveX, $saveY);
            $this->MultiCell($w, $lineHeight, $cellTexts[$i], 0, $col['align']);
            $this->Line($saveX, $saveY, $saveX, $saveY + $rowHeight); // left
            $this->Line($saveX + $w, $saveY, $saveX + $w, $saveY + $rowHeight); // right
            $x += $w;
        }

        $totalWidth = array_sum(array_column($columns, 'width'));
        $this->Line($xStart, $yStart, $xStart + $totalWidth, $yStart); // top
        $this->Line($xStart, $yStart + $rowHeight, $xStart + $totalWidth, $yStart + $rowHeight); // bottom

        $this->SetXY($xStart, $yStart + $rowHeight);
    }

}

$pdf = new PDF();
$pdf->titleText = 'Accounts Receivable';
$pdf->rangeText = date('n/j/Y', strtotime($date_from)) . ' - ' . date('n/j/Y', strtotime($date_to));
$pdf->SetLeftMargin(5);
$pdf->SetRightMargin(5);
$pdf->SetAutoPageBreak(true, 5);
$pdf->AddPage();

$w = [
    ['width'=>20,'align'=>'C'],
    ['width'=>20,'align'=>'C'],
    ['width'=>25,'align'=>'L'],
    ['width'=>25,'align'=>'L'],
    ['width'=>25,'align'=>'C'],
    ['width'=>25,'align'=>'C'],
    ['width'=>30,'align'=>'R'],
    ['width'=>30,'align'=>'R']
];

$pdf->SetFont('Arial','B',8);
$pdf->SetFillColor(220,220,220);
$headers = ['First Name','Last Name','Business Name','Farm Name','Date Outstanding','Last Payment','Credits Available','Balance Due'];
foreach($w as $i=>$col) {
    $pdf->Cell($col['width'],7,$headers[$i],1,($i==count($w)-1?1:0),$col['align'],true);
}

$grand_total_available = 0;
$grand_total_due = 0;

while ($row = $result->fetch_assoc()) {
    $first_name  = $row['customer_first_name'] ?? '-';
    $last_name   = $row['customer_last_name'] ?? '-';
    $business    = $row['customer_business_name'] ?? '-';
    $farm_name   = $row['customer_farm_name'] ?? '-';
    $date_outstanding = $row['first_credit_date'] ? date('n/j/Y', strtotime($row['first_credit_date'])) : '-';
    $last_payment     = $row['last_payment_date'] ? date('n/j/Y', strtotime($row['last_payment_date'])) : '-';
    $available_balance = floatval($row['available_balance']);
    $outstanding_credit = floatval($row['outstanding_credit']);
    $grand_total_available += $available_balance;
    $grand_total_due += $outstanding_credit;

    $data = [
        $first_name,$last_name,$business,$farm_name,$date_outstanding,$last_payment,
        '$'.number_format($available_balance,2),
        '$'.number_format($outstanding_credit,2)
    ];
    $pdf->renderRow($w,$data);
}

$data = [
    'GRAND TOTAL','','','','','',
    '$'.number_format($grand_total_available,2),
    '$'.number_format($grand_total_due,2)
];

$pdf->renderRow($w,$data,true);

$pdf->Output('Statement_of_Account_'.date('Ymd',strtotime($date_from)).'_to_'.date('Ymd',strtotime($date_to)).'.pdf','I');
