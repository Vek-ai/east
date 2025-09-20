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

function NbLines($pdf, $width, $text) {
    $cw = $pdf->GetStringWidth($text);
    if ($cw == 0) {
        return 1;
    }
    $nb_lines = ceil($cw / $width);
    return $nb_lines;
}

class PDF extends FPDF {
    function Footer() {
        $marginLeft = 10;
        $this->SetY(-15);

        $colWidth = ($this->w - 2 * $marginLeft) / 3;

        $this->SetFont('Arial', '', 9);

        $this->SetX($marginLeft);
        $this->Cell($colWidth, 5, 'Phone: (606) 877-1848 | Fax: (606) 864-4280', 0, 0, 'L');

        $this->SetX($marginLeft + $colWidth + 10);
        $this->Cell($colWidth, 5, 'Sales@Eastkentuckymetal.com', 0, 0, 'C');

        $this->SetX($marginLeft + 2 * $colWidth);
        $this->Cell($colWidth, 5, 'Eastkentuckymetal.com', 0, 0, 'R');
    }
}

$pdf = new PDF();
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

$col1_x = 10;
$col2_x = 140;

if(!empty($_REQUEST['id'])){
    $orderid = $_REQUEST['id'];

    $orderid = explode(',', $orderid);

    $orderid_list = "('" . implode("', '", array_map(function($id) use ($conn) {
        return mysqli_real_escape_string($conn, $id);
    }, $orderid)) . "')";

    $pdf->SetFont('Arial', '', 10);
    $pdf->Image('assets/images/logo-bw.png', 10, 6, 60, 20);
    $pdf->SetXY(10, 26);
    $pdf->Cell(0, 5, '977 E Hal Rogers Parkway, London, KY 40741', 0, 1);
    $pdf->Cell(0, 5, 'Phone: 606-877-1848 / Toll-Free: 877-303-3322', 0, 1);

    $pdf->Ln(5);

    $col1_x = 10;
    $col2_x = 70;
    $col3_x = 140;
    $pdf->SetFont('Arial', 'B', 10);

    $def_y = $pdf->GetY();

    $pdf->SetXY($col1_x, $def_y);
    $pdf->MultiCell(0, 5, 'Custom Discounted Product List', 0, 'C');

    $pdf->Ln(5);

    $total_price = 0;
    $total_qty = 0;

    $data = array();
    $query_product = "
        SELECT 
            o.*, 
            op.*, 
            CONCAT(c.customer_first_name, ' ', c.customer_last_name) AS customer_name 
        FROM order_product AS op
        LEFT JOIN orders AS o ON op.orderid = o.orderid
        LEFT JOIN customer AS c ON o.originalcustomerid = c.customer_id 
        WHERE op.id IN $orderid_list
    ";
    $result_product = mysqli_query($conn, $query_product);
    if (mysqli_num_rows($result_product) > 0) {

        $pdf->SetFont('Arial', 'B', 7);
        $widths = [40, 45, 30, 10, 20, 20, 26];
        $headers = ['Customer Name', 'Product Item' , 'Order Date', 'Qty', 'Actual Discount', 'Discount Used', 'Total Price'];

        for ($i = 0; $i < count($headers); $i++) {
            $pdf->Cell($widths[$i], 10, $headers[$i], 1, 0, 'C');
        }
        $pdf->Ln();

        while ($row_product = mysqli_fetch_assoc($result_product)) {
            $actual_discount = max($row_product['current_customer_discount'], $row_product['current_loyalty_discount']);
            $price = $row_product['discounted_price'] * $row_product['quantity'];
        
            $data[] = [
                $row_product['customer_name'],
                getProductName($row_product['productid']),
                date('F j, Y', strtotime($row_product['order_date'])),
                $row_product['quantity'],
                intval($actual_discount) .'%',
                intval($row_product['used_discount']) .'%',
                '$' .number_format($price,2)
            ];
        
            $total_price += ($row_product['discounted_price'] * (1 - $row_product['used_discount'])) * $row_product['quantity'];
            $total_qty += $row_product['quantity'];
        }
        
        $pdf->SetFont('Arial', '', 8);
        
        foreach ($data as $row) {
            $heights = [];
    
            for ($i = 0; $i < count($row); $i++) {
                $heights[] = NbLines($pdf, $widths[$i], $row[$i]) * 5;
            }

            $height = max($heights);
            $y_initial = $pdf->GetY();
        
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->MultiCell($widths[0], $height, $row[0], 'LR', 0);
            $pdf->SetXY($x + $widths[0], $y_initial);
            
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->MultiCell($widths[1], 5, $row[1], 'LR', 'L');
            $pdf->SetXY($x + $widths[1], $y_initial);

            $pdf->Cell($widths[2], $height, $row[2], 'LR', 0, 'C');
            $pdf->Cell($widths[3], $height, $row[3], 'LR', 0, 'C');
            $pdf->Cell($widths[4], $height, $row[4], 'LR', 0, 'C');
            $pdf->Cell($widths[5], $height, $row[5], 'LR', 0, 'C');
            $pdf->Cell($widths[6], $height, $row[6], 'LR', 0, 'R');

            $pdf->SetXY($x + $widths[1], $y_initial);
        
            $pdf->Ln();
        
            $y_bottom = $pdf->GetY();
        
            $pdf->Line(10, $y_initial + $height, 210 - 10, $y_initial + $height);
        }
        
    }

    $pdf->Ln(5);

    $col1_x = 10;
    $col2_x = 140;
    $col_y = $pdf->GetY();

    $lineheight = 6;

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetXY($col2_x, $pdf->GetY());
    $pdf->Cell(40, $lineheight, 'GRAND TOTAL:', 0, 0);
    $pdf->Cell(20, $lineheight, '$ ' .number_format(($total_price),2), 0, 1, 'R');

    $pdf->Ln(5);

    $pdf->SetTitle('Receipt');
    $pdf->Output('Receipt.pdf', 'I');
}

            

?>
