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
        $pdf->Cell(10, 5, 'Tax Exempt #: ', 0, 0, 'L');
        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(10, 5, 'Customer PO #: ' .$row_orders['job_po'], 0, 0, 'L');

        $pdf->SetXY($col3_x, $pdf->GetY());
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
                                    op.*
                                FROM
                                    `order_product` AS op
                                LEFT JOIN product AS p
                                ON
                                    p.product_id = op.`productid`
                                WHERE orderid = '$orderid' AND p.product_category = '$product_category_id'";
                $result_product = mysqli_query($conn, $query_product);
                if (mysqli_num_rows($result_product) > 0) {
                    $pdf->Ln();
                    $pdf->SetFont('Arial', 'B', 9);
                    $pdf->SetXY($col1_x, $pdf->GetY());
                    $pdf->Cell(10, 5, getProductCategoryName($product_category_id), 0, 1, 'L');

                    $pdf->SetFont('Arial', 'B', 7);
                    $widths = [15, 20, 55, 20, 10, 10, 10, 18, 18, 15];
                    $headers = ['QTY', "IMAGE", 'DESCRIPTION', 'COLOR', 'Grade', 'FT.', 'IN.', 'PRICE' , 'DISC PRICE', 'TOTAL'];

                    for ($i = 0; $i < count($headers); $i++) {
                        $pdf->Cell($widths[$i], 10, $headers[$i], 1, 0, 'C');
                    }
                    $pdf->Ln();

                    $trim_directory = "images/drawing/";

                    while ($row_product = mysqli_fetch_assoc($result_product)) {
                        $productid = $row_product['productid'];
                        $product_details = getProductDetails($productid);
                        $grade_details = getGradeDetails($product_details['grade']);

                        $product_name = !empty($row_product['product_item']) ? $row_product['product_item'] : $product_details['product_item'];
                        $picture_path = !empty($row_product['custom_img_src']) ? $trim_directory . $row_product["custom_img_src"] : '';

                        $quantity = is_numeric($row_product['quantity']) ? floatval($row_product['quantity']) : 0;
                        $unit_price = floatval($row_product['actual_price']);
                        $discounted_price = floatval($row_product['discounted_price']);

                        $total_line_price = $discounted_price;

                        $data[] = [
                            $quantity,
                            '',
                            $product_name,
                            getColorName($product_details['color']),
                            $grade_details['grade_abbreviations'] ?? '',
                            '',
                            '',
                            '$ ' . number_format($unit_price, 2),
                            '$ ' . number_format($discounted_price, 2),
                            '$ ' . number_format($total_line_price, 2),
                            $picture_path
                        ];

                        $total_price += $total_line_price;
                        $total_qty += $quantity;
                    }

                    $pdf->SetFont('Arial', '', 8);

                    foreach ($data as $row) {
                        $height_product = NbLines($pdf, $widths[2], $row[2]) * 5; 
                        $height_color = NbLines($pdf, $widths[3], $row[3]) * 5;

                        $isTrim = false;
                        $height = max($height_product, $height_color);

                        if (!empty($row[10])) {
                            $isTrim = true;
                            $height = 30;
                        }
                        
                        $y_initial = $pdf->GetY();

                        $pdf->Cell($widths[0], $height, $row[0], 'LR', 0, 'C');

                        if ($isTrim) {
                            $pdf->Cell($widths[1], $height, '', 'LR', 0, 'C');
                            $xImg = $pdf->GetX() - $widths[1];
                            $yImg = $y_initial;
                            $imgWidth = $widths[1] - 2;
                            $imgHeight = $height - 2;
                            $pdf->Image($row[10], $xImg + 1, $yImg, $imgWidth, $imgHeight);
                        } else {
                            $pdf->Cell($widths[1], $height, $row[1], 'LR', 0, 'C');
                        }
                        
                        $lineHeight = 5;

                        $numLines = NbLines($pdf, $widths[2], $row[2]);
                        $contentHeight = $numLines * $lineHeight;
                        $verticalOffset = $y_initial + ($height - $contentHeight) / 2;

                        $x = $pdf->GetX();
                        $y = $verticalOffset;

                        $pdf->Line($x, $y_initial, $x, $y_initial + $height);
                        $pdf->Line($x + $widths[2], $y_initial, $x + $widths[2], $y_initial + $height);

                        $pdf->SetXY($x, $y);
                        $pdf->MultiCell($widths[2], $lineHeight, $row[2], 0, 'C');
                        $pdf->SetXY($x + $widths[2], $y_initial);

                        $numLines = NbLines($pdf, $widths[3], $row[3]);
                        $contentHeight = $numLines * $lineHeight;
                        $verticalOffset = $y_initial + ($height - $contentHeight) / 2;

                        $x = $pdf->GetX();
                        $y = $verticalOffset;

                        $pdf->Line($x, $y_initial, $x, $y_initial + $height);
                        $pdf->Line($x + $widths[3], $y_initial, $x + $widths[3], $y_initial + $height);

                        $pdf->SetXY($x, $y);
                        $pdf->MultiCell($widths[3], $lineHeight, $row[3], 0, 'C');
                        $pdf->SetXY($x + $widths[3], $y_initial);

                        $pdf->Cell($widths[4], $height, $row[4], 'LR', 0, 'C');  
                        $pdf->Cell($widths[5], $height, $row[5], 'LR', 0, 'C');  
                        $pdf->Cell($widths[6], $height, $row[6], 'LR', 0, 'C');  
                        $pdf->Cell($widths[7], $height, $row[7], 'LR', 0, 'R');  
                        $pdf->Cell($widths[8], $height, $row[8], 'LR', 0, 'R');  
                        $pdf->Cell($widths[9], $height, $row[9], 'LR', 0, 'R');  
                        

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
