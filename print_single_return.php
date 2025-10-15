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
        $this->Cell($colWidth, 5, 'Sales@EastKentuckyMetal.com', 0, 0, 'C');

        $this->SetX($marginLeft + 2 * $colWidth);
        $this->Cell($colWidth, 5, 'EastKentuckyMetal.com', 0, 0, 'R');
    }
}

$pdf = new PDF();
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

$col1_x = 10;
$col2_x = 140;

$return_id = $_REQUEST['id'];
$current_user_id = $_SESSION['userid'];

$query = "SELECT * FROM product_returns WHERE id = '$return_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while($row_order_product = mysqli_fetch_assoc($result)){
        $row_orders = getOrderDetails($row_order_product['orderid']);
        $delivery_price = floatval($row_orders['delivery_amt']);
        $discount = floatval($row_orders['discount_percent']) / 100;
        $orderid = $row_orders['orderid'];
        $customer_id = $row_orders['customerid'];
        $customerDetails = getCustomerDetails($customer_id);
        $tax = floatval(getCustomerTax($customer_id)) / 100;
        $delivery_method = 'Deliver';
        if($delivery_price == 0){
            $delivery_method = 'Pickup';
        }
        $pdf->SetFont('Arial', '', 10);
        $pdf->Image('assets/images/logo-bw.png', 10, 6, 60, 20);
        $pdf->SetXY(10, 26);
        $pdf->Cell(0, 5, '977 E Hal Rogers Parkway, London, KY 40741', 0, 1);
        $pdf->Cell(0, 5, 'Phone: 606-877-1848 / Toll-Free: 877-303-3322', 0, 1);

        $pdf->SetXY($col2_x,  10);
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(95, 5, "Invoice #: $orderid", 0, 'L');
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
        $pdf->MultiCell(60, 5, 'Returned by: ' .get_customer_name($customer_id), 0, 'L');

        $pdf->SetXY($col2_x, $def_y);
        $pdf->Cell(60, 5, 'Job Name: ' .$row_orders['job_name'], 0, 1, 'L');

        $pdf->Ln(5);

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
                                    pr.*
                                FROM
                                    product_returns AS pr
                                LEFT JOIN product AS p
                                ON
                                    p.product_id = pr.productid
                                WHERE orderid = '$orderid' AND p.product_category = '$product_category_id'";
                $result_product = mysqli_query($conn, $query_product);
                if (mysqli_num_rows($result_product) > 0) {
                    $pdf->Ln();
                    $pdf->SetFont('Arial', 'B', 9);
                    $pdf->SetXY($col1_x, $pdf->GetY());
                    $pdf->Cell(10, 5, getProductCategoryName($product_category_id), 0, 1, 'L');

                    $pdf->SetFont('Arial', 'B', 7);
                    $widths = [19, 85.5, 28.5, 28.5, 28.5];
                    $headers = ['QTY', 'DESCRIPTION', 'PRICE', 'STOCK FEE', 'TOTAL'];

                    foreach ($headers as $i => $header) {
                        $pdf->Cell($widths[$i], 10, $header, 1, 0, 'C');
                    }
                    $pdf->Ln();

                    $data = [];

                    while ($row_product = mysqli_fetch_assoc($result_product)) {
                        $productid = $row_product['productid'];
                        $product_details = getProductDetails($productid);
                        $product_name = !empty($row_product['product_item']) ? $row_product['product_item'] : $product_details['product_item'];

                        $quantity = is_numeric($row_product['quantity']) ? floatval($row_product['quantity']) : 0;
                        $discounted_price = floatval($row_product['discounted_price']);
                        $stock_fee = floatval($row_product['stock_fee']) * $discounted_price;
                        $amount_returned = $discounted_price - $stock_fee;

                        $data[] = [
                            $quantity,
                            $product_name,
                            '$ ' . number_format($discounted_price, 2),
                            '$ ' . number_format($stock_fee, 2),
                            '$ ' . number_format($amount_returned, 2)
                        ];

                        $total_price += $amount_returned;
                        $total_qty += $quantity;
                    }

                    $pdf->SetFont('Arial', '', 8);

                    foreach ($data as $row) {
                        $lineHeight = 5;
                        $numLines = NbLines($pdf, $widths[1], $row[1]);
                        $height = $numLines * $lineHeight;
                        $y_initial = $pdf->GetY();

                        $pdf->Cell($widths[0], $height, $row[0], 1, 0, 'C');

                        $x = $pdf->GetX();
                        $pdf->SetXY($x, $y_initial);
                        $pdf->MultiCell($widths[1], $lineHeight, $row[1], 1, 'C');

                        $pdf->SetXY($x + $widths[1], $y_initial);
                        $pdf->Cell($widths[2], $height, $row[2], 1, 0, 'C');
                        $pdf->Cell($widths[3], $height, $row[3], 1, 0, 'C');
                        $pdf->Cell($widths[4], $height, $row[4], 1, 0, 'C');

                        $pdf->Ln();
                    }
                }

            }
            
        }else{
            echo "No key components found";
        }

        $pdf->Ln(5);

        $col1_x = 10;
        $col2_x = 140;
        $col_y = $pdf->GetY();

        $lineheight = 6;

        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(40, $lineheight, 'SUBTOTAL:', 0, 0);
        $pdf->Cell(20, $lineheight, '$ ' .number_format($total_price,2), 0, 1 , 'R');

        $pdf->Ln(5);

        $pdf->SetTitle('Returns');
        $pdf->Output('Returns.pdf', 'I');
            

        
    }
}else{
    echo "ID not Found!";
}

?>
