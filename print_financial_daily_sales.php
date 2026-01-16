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

$query = "
SELECT o.*, 
       c.customer_first_name,
       c.customer_last_name,
       c.customer_business_name,
       c.customer_farm_name,
       c.tax_status
FROM orders AS o
LEFT JOIN customer AS c ON c.customer_id = o.customerid
WHERE o.status != 6
AND o.order_date BETWEEN '$dateSQLStart' AND '$dateSQLEnd'
ORDER BY o.order_date DESC
";

$result = $conn->query($query);
if (!$result || $result->num_rows === 0) {
    echo 'No receivable or credit balances found for this period.';
    exit;
}

$result = mysqli_query($conn, $query);
$orders_by_tax = [];
$all_tax_statuses = [];

while ($row = mysqli_fetch_assoc($result)) {
    $tax_status = $row['tax_status'] ?? 0;
    $orders_by_tax[$tax_status][] = $row;
    $all_tax_statuses[$tax_status] = $tax_status;
}

if (empty($all_tax_statuses)) $all_tax_statuses[0] = 0;

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

        $noteHeight = 0;
        if (!empty($note)) {
            $totalWidth = array_sum(array_column($columns, 'width'));
            $this->SetFont('Arial', '', 8);
            $noteHeight = $this->GetMultiCellHeight($totalWidth, $lineHeight, $note);
        }

        $rowHeight = max($heights) + $noteHeight;

        if ($yStart + $rowHeight > $this->h - $this->bMargin) {
            $this->AddPage();
            $xStart = $this->GetX();
            $yStart = $this->GetY();
        }

        $x = $xStart;
        $totalWidth = array_sum(array_column($columns, 'width'));

        foreach ($columns as $i => $col) {
            $w = $col['width'];
            $fontSize = $col['fontsize'] ?? 8;
            $this->SetFont('Arial', $cellStyles[$i], $fontSize);
            $saveX = $x;
            $saveY = $yStart;

            $this->SetXY($saveX, $saveY);
            $this->MultiCell($w, $lineHeight, $cellTexts[$i], 0, $col['align']);

            $this->Line($saveX, $saveY, $saveX, $saveY + $rowHeight);
            $this->Line($saveX + $w, $saveY, $saveX + $w, $saveY + $rowHeight);

            $x += $w;
        }

        $this->Line($xStart, $yStart, $xStart + $totalWidth, $yStart);
        $this->Line($xStart, $yStart + $rowHeight, $xStart + $totalWidth, $yStart + $rowHeight);

        if (!empty($note)) {
            $this->SetXY($xStart, $yStart + max($heights));
            $this->SetFont('Arial', '', 9);
            $this->MultiCell($totalWidth, $lineHeight, 'Note: '.$note, 0, 'L');
        }

        $this->SetXY($xStart, $yStart + $rowHeight);
    }

}

$w = [
    ['width'=>16,'align'=>'C'],
    ['width'=>20,'align'=>'C'],
    ['width'=>20,'align'=>'C'],
    ['width'=>20,'align'=>'L'],
    ['width'=>20,'align'=>'L'],
    ['width'=>20,'align'=>'R'],
    ['width'=>20,'align'=>'R'],
    ['width'=>20,'align'=>'R'],
    ['width'=>25,'align'=>'R'],
    ['width'=>20,'align'=>'C']
];

$pdf = new PDF();
$pdf->titleText = 'Daily Sales';
$pdf->rangeText = date('n/j/Y', strtotime($date_from)) . ' - ' . date('n/j/Y', strtotime($date_to));
$pdf->SetLeftMargin(5);
$pdf->SetRightMargin(5);
$pdf->SetAutoPageBreak(true, 5);
$pdf->AddPage();

$grand_totals = ['materials'=>0,'delivery'=>0,'tax'=>0,'total'=>0];

foreach ($all_tax_statuses as $tax_status_id) {
    $orders = $orders_by_tax[$tax_status_id] ?? [];
    $tax_status_name = htmlspecialchars(getCustomerTaxName($tax_status_id));
    $tax_rate_percent = floatval(getCustomerTaxById($tax_status_id));

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(200,200,200);
    $pdf->Cell(array_sum(array_column($w,'width')),8,$tax_status_name,1,1,'L',true);

    $pdf->SetFont('Arial','B',8);
    $pdf->SetFillColor(220,220,220);
    $headers = ['Invoice #','First Name','Last Name','Business','Farm','Materials','Delivery','Sales Tax','Total Price','Tax'];
    foreach($w as $i=>$col) $pdf->Cell($col['width'],7,$headers[$i],1,($i==count($w)-1?1:0),$col['align'],true);

    foreach($orders as $row) {
        $materials_price = floatval($row['discounted_price']??0);
        $delivery_price  = floatval($row['delivery_amt']??0);
        $sales_tax       = $materials_price*$tax_rate_percent/100;
        $total_order     = $materials_price+$delivery_price+$sales_tax;

        $grand_totals['materials']+=$materials_price;
        $grand_totals['delivery']+=$delivery_price;
        $grand_totals['tax']+=$sales_tax;
        $grand_totals['total']+=$total_order;

        $data = [
            getInvoiceNumName($row['orderid']),
            $row['customer_first_name']??'-',
            $row['customer_last_name']??'-',
            $row['customer_business_name']??'-',
            $row['customer_farm_name']??'-',
            '$'.number_format($materials_price,2),
            '$'.number_format($delivery_price,2),
            '$'.number_format($sales_tax,2),
            '$'.number_format($total_order,2),
            $tax_status_name
        ];

        $pdf->renderRow($w, $data);
    }

    $data = [
        $tax_status_name.' Total','','','','',
        '$'.number_format(array_sum(array_column($orders,'discounted_price')),2),
        '$'.number_format(array_sum(array_column($orders,'delivery_amt')),2),
        '$'.number_format(array_sum(array_map(function($r) use($tax_rate_percent){return floatval($r['discounted_price']??0)*$tax_rate_percent/100;},$orders)),2),
        '$'.number_format(array_sum(array_map(function($r) use($tax_rate_percent){return floatval($r['discounted_price']??0)+floatval($r['delivery_amt']??0)+(floatval($r['discounted_price']??0)*$tax_rate_percent/100);},$orders)),2),
        ''
    ];

    $pdf->renderRow($w, $data, true);

    $pdf->ln();
}

$data = [
    'GRAND TOTAL','','','','',
    '$'.number_format($grand_totals['materials'],2),
    '$'.number_format($grand_totals['delivery'],2),
    '$'.number_format($grand_totals['tax'],2),
    '$'.number_format($grand_totals['total'],2),
    ''
];

$pdf->renderRow($w, $data, true);

$pdf->Output('Daily_Sales_'.date('Ymd',strtotime($date_from)).'_to_'.date('Ymd',strtotime($date_to)).'.pdf','I');
