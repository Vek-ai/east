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

$pdf = new FPDF();
$pdf->AddPage();

$col1_x = 10;
$col2_x = 140;

$orderid = $_REQUEST['id'];
$pricing_id = $_REQUEST['pricing_id'] ?? '';
$current_user_id = $_SESSION['userid'];

$tax = .15;

$query = "SELECT * FROM orders WHERE orderid = '$orderid'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while($row_orders = mysqli_fetch_assoc($result)){
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
        $pdf->MultiCell(95, 5, "Estimate #: $orderid", 0, 'L');
        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(95, 5, "Date: " .date("F d, Y", strtotime($row_orders['order_date'])), 0, 1, 'L');
        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(95, 5, 'Salesperson: ' . get_staff_name($current_user_id), 0, 0, 'L');

        $pdf->Ln(20);

        $col1_x = 10;
        $col2_x = 70;
        $col3_x = 140;
        $pdf->SetFont('Arial', 'B', 10);

        $def_y = $pdf->GetY();

        $pdf->SetXY($col1_x, $def_y);
        $pdf->MultiCell(60, 5, 'Sold To: ' .$row_orders['deliver_fname'] . " " .$row_orders['deliver_lname'], 0, 'L');

        $addressParts = [];
        if (!empty($row_orders['deliver_address'])) {
            $addressParts[] = $row_orders['deliver_address'];
        }
        if (!empty($row_orders['deliver_city'])) {
            $addressParts[] = $row_orders['deliver_city'];
        }
        if (!empty($row_orders['deliver_state'])) {
            $addressParts[] = $row_orders['deliver_state'];
        }
        if (!empty($row_orders['deliver_zip'])) {
            $addressParts[] = $row_orders['deliver_zip'];
        }
        $address = implode(', ', $addressParts);
        $pdf->SetXY($col2_x, $def_y);
        $pdf->MultiCell(60, 5, 'Ship To: ' .$address, 0, 'L');

        $pdf->SetXY($col3_x, $def_y);
        $pdf->Cell(60, 5, 'Delivery Method: ' .$delivery_method, 0, 1, 'L');

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetXY($col1_x, $pdf->GetY());
        $pdf->Cell(60, 5, $customerDetails['contact_phone'], 0, 0, 'L');
        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(60, 5, '', 0, 1, 'L');

        $pdf->Ln(5);
        $pdf->SetXY($col1_x, $pdf->GetY());
        $pdf->Cell(10, 5, 'Tax Exempt #', 0, 0, 'L');
        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(10, 5, 'Customer PO #: ' .$row_orders['job_po'], 0, 0, 'L');

        $pdf->SetXY($col3_x, $pdf->GetY());
        $pdf->Cell(60, 5, 'Job Name: ' .$row_orders['job_name'], 0, 1, 'L');

        $pdf->Ln(5);

        $total_price = 0;
        $total_qty = 0;
        $total_price_undisc = 0;

        $query_category = "SELECT * FROM product_category WHERE hidden = 0";
        $result_category = mysqli_query($conn, $query_category);
        if (mysqli_num_rows($result_category) > 0) {
            $pdf->SetFont('Arial', 'B', 7);
            $widths = [35, 75, 28, 28, 25];
            $headers = ['QTY', 'DESCRIPTION', 'PRICE' , 'DISC PRICE', 'TOTAL'];
            
            for ($i = 0; $i < count($headers); $i++) {
                $pdf->Cell($widths[$i], 10, $headers[$i], 1, 0, 'C');
            }
            $pdf->Ln();
            while ($row_category = mysqli_fetch_assoc($result_category)) {
                $product_category_id = $row_category['product_category_id'];

                $qty_per_component = 0;
                $total_per_component = 0;
                $undisc_total_per_component = 0;

                $data = array();
                $query_product="SELECT
                                    p.product_category,
                                    op.*
                                FROM
                                    `order_product` AS op
                                LEFT JOIN product AS p
                                ON
                                    p.product_id = op.`productid`
                                WHERE orderid = '$orderid' AND p.product_category = '$product_category_id'";
                $result_product = mysqli_query($conn, $query_product);
                if (mysqli_num_rows($result_product) > 0) {
                    while ($row_product = mysqli_fetch_assoc($result_product)) {
                        $productid = $row_product['productid'];
                        $product_details = getProductDetails($productid);
                        $grade_details = getGradeDetails($product_details['grade']);

                        $quantity = is_numeric($row_product['quantity']) ? floatval($row_product['quantity']) : 0;

                        $price = floatval($row_product['discounted_price']);
                        $price_undisc = floatval($row_product['actual_price']);

                        $total_price += $price;
                        $total_price_undisc += $price_undisc;
                        $total_qty += $quantity;

                        $total_per_component += $price;
                        $undisc_total_per_component += $price_undisc;
                        $qty_per_component += $quantity;
                    }

                    
                    $data[] = [
                        $qty_per_component,
                        getProductCategoryName($product_category_id),
                        '$ ' .number_format($undisc_total_per_component,2),
                        '$ ' .number_format($total_per_component,2),
                        '$ ' .number_format($total_per_component,2) ,
                    ];
        
                    $pdf->SetFont('Arial', '', 8);
        
                    foreach ($data as $row) {
        
                        $height = NbLines($pdf, $widths[2], $row[2]) * 5; 
                        
                        $y_initial = $pdf->GetY();
        
                        $pdf->Cell($widths[0], $height, $row[0], 'LR', 0, 'C');
                        
                        $x = $pdf->GetX();
                        $y = $pdf->GetY();
                        $pdf->MultiCell($widths[1], 5, $row[1], 'LR', 'C');
                        $pdf->SetXY($x + $widths[1], $y_initial);

                        $pdf->Cell($widths[2], $height, $row[2], 'LR', 0, 'R');  
                        $pdf->Cell($widths[3], $height, $row[3], 'LR', 0, 'R');  
                        $pdf->Cell($widths[4], $height, $row[4], 'LR', 0, 'R');  
                        
                        $pdf->Ln();
                        $y_bottom = $pdf->GetY();
                        $pdf->Line(10, $y_initial + $height, 210 - 10, $y_initial + $height);
                        
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

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetXY($col1_x, $col_y);
        $pdf->MultiCell(120, 4, "Customer is solely responsible for accuracy of order and for verifying accuracy of materials before leaving EKMS or at time of delivery. If an agent orders or takes materials on customer's behalf, EKMS is entitled to rely upon the agent as if s/he has full authority to act on customer's behalf. No returns on metal panels or special trim. All other materials returned undamaged within 60 days of invoice date are subject to a restocking fee equal to 25% of current retail price.", 1);

        $pdf->SetFont('Arial', '', 9);

        $grand_total = $total_price + $delivery_price;
        $sales_tax = $grand_total * $tax / (1 + $tax);
        $subtotal = $grand_total - $sales_tax;

        $pdf->SetXY($col2_x, $col_y);
        $pdf->Cell(40, $lineheight, 'MISC:', 0, 0);
        $pdf->Cell(20, $lineheight, ($discount < 0 ? '-' : '') . $discount * 100 .'%', 0, 1, 'R');

        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(40, $lineheight, 'SUBTOTAL:', 0, 0);
        $pdf->Cell(20, $lineheight, '$ ' . number_format($subtotal, 2), 0, 1 , 'R');

        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(40, $lineheight, 'DELIVERY:', 0, 0);
        $pdf->Cell(20, $lineheight, '$ ' . number_format($delivery_price, 2), 0, 1 , 'R');

        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(40, $lineheight, 'SALES TAX:', 0, 0);
        $pdf->Cell(20, $lineheight, '$ ' . number_format($sales_tax, 2), 0, 1, 'R');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(40, $lineheight, 'GRAND TOTAL:', 0, 0);
        $pdf->Cell(20, $lineheight, '$ ' . number_format($grand_total, 2), 0, 1, 'R');


        $pdf->Ln(5);

        $pdf->SetTitle('Receipt');
        $pdf->Output('Receipt.pdf', 'I');
    }
}else{
    echo "ID not Found!";
}

?>
