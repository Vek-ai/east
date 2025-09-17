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
    $cw_per_char = $pdf->GetStringWidth('a');
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
        $this->Cell($colWidth, 5, 'Email: Sales@Eastkentuckymetal.com', 0, 0, 'C');

        $this->SetX($marginLeft + 2 * $colWidth);
        $this->Cell($colWidth, 5, 'Website: Eastkentuckymetal.com', 0, 0, 'R');
    }
}

$pdf = new PDF();
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

$col1_x = 10;
$col2_x = 140;

$supplier_order_id = $_REQUEST['id'];
$current_user_id = $_SESSION['userid'];

$query = "SELECT * FROM supplier_orders WHERE supplier_order_id = '$supplier_order_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while($row_orders = mysqli_fetch_assoc($result)){
        $supplier_id = $row_orders['supplier_id'];
        $supplierDetails = getSupplierDetails($supplier_id);
        $delivery_method = 'Deliver';
        $pdf->SetFont('Arial', '', 10);
        $pdf->Image('assets/images/logo-bw.png', 10, 6, 60, 20);
        $pdf->SetXY(10, 26);
        $pdf->Cell(0, 5, '977 E Hal Rogers Parkway, London, KY 40741', 0, 1);
        $pdf->Cell(0, 5, 'Phone: 606-877-1848 / Toll-Free: 877-303-3322', 0, 1);

        $pdf->SetXY($col2_x,  10);
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(95, 5, "Order #: $supplier_order_id", 0, 'L');
        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(95, 5, "Date: " .date("F d, Y", strtotime($row_orders['order_date'])), 0, 1, 'L');
        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(95, 5, 'Salesperson: ' . get_staff_name($current_user_id), 0, 0, 'L');

        $pdf->Ln(20);

        $col1_x = 10;
        $col2_x = 70;
        $col3_x = 140;
        $pdf->SetFont('Arial', 'B', 11);

        $def_y = $pdf->GetY();
        $pdf->SetXY($col1_x, $def_y);
        $pdf->MultiCell(0, 5, 'Supplier: ' .$supplierDetails['supplier_name'], 0, 'L');

        $total_price = 0;
        $total_qty = 0;

        $query_category = "SELECT * FROM product_category WHERE hidden = 0";
        $result_category = mysqli_query($conn, $query_category);
        if (mysqli_num_rows($result_category) > 0) {
            while ($row_category = mysqli_fetch_assoc($result_category)) {
                $product_category_id = $row_category['product_category_id'];
                
                $data = array();
                $query_product="SELECT
                                    p.product_category,
                                    sop.*
                                FROM
                                    supplier_orders_prod AS sop
                                LEFT JOIN product AS p
                                ON
                                    p.product_id = sop.`product_id`
                                WHERE sop.supplier_order_id = '$supplier_order_id' AND p.product_category = '$product_category_id'";
                $result_product = mysqli_query($conn, $query_product);
                if (mysqli_num_rows($result_product) > 0) {
                    $pdf->Ln();
                    $pdf->SetFont('Arial', 'B', 9);
                    $pdf->SetXY($col1_x, $pdf->GetY());
                    $pdf->Cell(10, 5, getProductCategoryName($product_category_id), 0, 1, 'L');
                
                    $pdf->SetFont('Arial', 'B', 7);
                    $widths = [20, 30, 60, 39, 20, 21]; 
                    $headers = ['QTY', "PART/COIL #", 'DESCRIPTION', 'COLOR', 'PRICE', 'TOTAL'];
                
                    foreach ($headers as $i => $header) {
                        $pdf->Cell($widths[$i], 10, $header, 1, 0, 'C');
                    }
                    $pdf->Ln();
                
                    $data = [];
                
                    while ($row_product = mysqli_fetch_assoc($result_product)) {
                        $product_id = $row_product['product_id'];
                        $product_details = getProductDetails($product_id);
                
                        $data[] = [
                            $row_product['quantity'],
                            '',
                            $product_details['product_item'],
                            getColorName($row_product['color']),
                            '$ ' . number_format($product_details['unit_price'], 2),
                            '$ ' . number_format($product_details['unit_price'] * $row_product['quantity'], 2)
                        ];
                
                        $total_price += $product_details['unit_price'] * $row_product['quantity'];
                        $total_qty += $row_product['quantity'];
                    }
                
                    $pdf->SetFont('Arial', '', 8);
                
                    foreach ($data as $row) {
                        $y_initial = $pdf->GetY();
                
                        $height_product = NbLines($pdf, $widths[2], $row[2]) * 5;
                        $height_color = NbLines($pdf, $widths[3], $row[3]) * 5;
                        $height = max($height_product, $height_color);
                
                        $pdf->Cell($widths[0], $height, $row[0], 'LR', 0, 'C');
                        $pdf->Cell($widths[1], $height, $row[1], 'LR', 0, 'C');
                
                        $x = $pdf->GetX();
                        $pdf->MultiCell($widths[2], 5, $row[2], 'LR', 'C');
                        $pdf->SetXY($x + $widths[2], $y_initial);
                
                        $x = $pdf->GetX();
                        $pdf->MultiCell($widths[3], 5, $row[3], 'LR', 'C');
                        $pdf->SetXY($x + $widths[3], $y_initial);
                
                        $pdf->Cell($widths[4], $height, $row[4], 'LR', 0, 'C');
                        $pdf->Cell($widths[5], $height, $row[5], 'LR', 0, 'C');
                
                        $pdf->Ln();
                
                        $pdf->Line(10, $y_initial + $height, 210 - 10, $y_initial + $height);
                    }
                }
                
            }
            
        }else{
            echo "No Supplier Order Found";
        }

        $pdf->Ln(5);

        $col1_x = 10;
        $col2_x = 140;
        $col_y = $pdf->GetY();

        $lineheight = 6;

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetXY($col1_x, $col_y);
        $pdf->MultiCell(120, 4, "Customer is solely responsible for accuracy of order and for verifying accuracy of materials before leaving EKMS or at time of delivery. If an agent orders or takes materials on customer's behalf, EKMS is entitled to rely upon the agent as if s/he has full authority to act on customer's behalf. No returns on metal panels or special trim. All other materials returned undamaged within 60 days of invoice date are subject to a restocking fee equal to 25% of current retail price.", 1);

        $pdf->SetXY($col2_x, $col_y);
        $pdf->Cell(40, $lineheight, 'TOTAL ITEMS:', 0, 0);
        $pdf->Cell(20, $lineheight, $total_qty, 0, 1 , 'R');
        
        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(40, $lineheight, 'SUBTOTAL:', 0, 0);
        $pdf->Cell(20, $lineheight, '$ ' .number_format($total_price,2), 0, 1 , 'R');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(40, $lineheight, 'GRAND TOTAL:', 0, 0);
        $pdf->Cell(20, $lineheight, '$ ' .number_format(($total_price),2), 0, 1, 'R');

        $pdf->Ln(5);

        $pdf->SetTitle('Receipt');
        $pdf->Output('Receipt.pdf', 'I');
            

        
    }
}else{
    echo "ID not Found!";
}

?>
