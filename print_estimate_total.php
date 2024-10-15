<?php
session_start();
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 1);

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

$estimateid = $_REQUEST['id'];
$current_user_id = $_SESSION['userid'];

$tax = .15;
$delivery_price = 100;

$query = "SELECT * FROM estimates WHERE estimateid = '$estimateid'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while($row_estimate = mysqli_fetch_assoc($result)){
        $discount = floatval($row_estimate['discount_percent']) / 100;
        $estimateid = $row_estimate['estimateid'];
        $customer_id = $row_estimate['customerid'];
        $customerDetails = getCustomerDetails($customer_id);
        $delivery_method = 'Deliver';
        $pdf->SetFont('Arial', '', 10);
        $pdf->Image('assets/images/logo-bw.png', 10, 6, 60, 20);
        $pdf->SetXY(10, 26);
        $pdf->Cell(0, 5, '977 E Hal Rogers Parkway, London, KY 40741', 0, 1);
        $pdf->Cell(0, 5, 'Phone: 606-877-1848 / Toll-Free: 877-303-3322', 0, 1);

        $pdf->SetXY($col2_x,  10);
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(95, 5, "Estimate #: $estimateid", 0, 'L');
        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(95, 5, "Date: " .date("F d, Y", strtotime($row_estimate['order_date'])), 0, 1, 'L');
        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(95, 5, 'Salesperson: ' . get_staff_name($current_user_id), 0, 0, 'L');

        $pdf->Ln(20);

        $col1_x = 10;
        $col2_x = 70;
        $col3_x = 140;
        $pdf->SetFont('Arial', 'B', 10);

        $pdf->SetXY($col1_x, $pdf->GetY());
        $pdf->Cell(60, 5, 'Sold To: ' .$customerDetails['customer_first_name'] . " " .$customerDetails['customer_last_name'], 0, 0, 'L');

        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(60, 5, 'Ship To: ' .$customerDetails['customer_first_name'] . " " .$customerDetails['customer_last_name'], 0, 1, 'L');

        $pdf->SetXY($col3_x, $pdf->GetY() - 5);
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
        $pdf->Cell(10, 5, 'Customer PO #', 0, 0, 'L');

        $pdf->SetXY($col3_x, $pdf->GetY());
        $pdf->Cell(60, 5, 'Job Name:', 0, 1, 'L');

        $pdf->Ln(5);

        $total_price = 0;
        $total_qty = 0;
        $total_price_undisc = 0;

        $query_key = "SELECT * FROM key_components";
        $result_key = mysqli_query($conn, $query_key);
        if (mysqli_num_rows($result_key) > 0) {
            $pdf->SetFont('Arial', 'B', 7);
            $widths = [35, 75, 28, 28, 25];
            $headers = ['QTY', 'DESCRIPTION', 'PRICE' , 'DISC PRICE', 'TOTAL'];
            
            for ($i = 0; $i < count($headers); $i++) {
                $pdf->Cell($widths[$i], 10, $headers[$i], 1, 0, 'C');
            }
            $pdf->Ln();
            while ($row_key = mysqli_fetch_assoc($result_key)) {
                $component_name = $row_key['component_name'];
                $componentid = $row_key['componentid'];
                $query_usage = "SELECT * FROM component_usage WHERE componentid = '$componentid'";
                $result_usage = mysqli_query($conn, $query_usage);
                $usageArray = array();

                if (mysqli_num_rows($result_usage) > 0) {
                    $qty_per_component = 0;
                    $total_per_component = 0;
                    $undisc_total_per_component = 0;
                    while ($row_usage = mysqli_fetch_assoc($result_usage)) {
                        $usageArray[] = $row_usage['usageid'];
                    }

                    $usageArray = array_unique($usageArray);

                    $usage_ids = implode(',' , $usageArray);
                    $data = array();
                    $query_product = "SELECT * FROM estimate_prod WHERE estimateid = '$estimateid' AND usageid IN ($usage_ids)";
                    $result_product = mysqli_query($conn, $query_product);
                    if (mysqli_num_rows($result_product) > 0) {
                        while($row_product = mysqli_fetch_assoc($result_product)){
                            $product_id = $row_product['product_id'];
                            $product_details = getProductDetails($product_id);
                            $grade_details = getGradeDetails($product_details['grade']);
                            
                            
                            $total_price += ($product_details['unit_price'] * (1 - $discount)) * $row_product['quantity'];
                            $total_price_undisc += $product_details['unit_price'] * $row_product['quantity'];
                            $total_qty += $row_product['quantity'];

                            $total_per_component += ($product_details['unit_price'] * (1 - $discount)) * $row_product['quantity'];
                            $undisc_total_per_component += $product_details['unit_price'] * $row_product['quantity'];
                            $qty_per_component += $row_product['quantity'];
                            
                        }

                        
                        $data[] = [
                            $qty_per_component,
                            $component_name,
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
                
            }

            $qty_per_component = 0;
            $total_per_component = 0;
            $undisc_total_per_component = 0;
            $data = array();
            $query_product = "SELECT * FROM estimate_prod WHERE estimateid = '$estimateid' AND usageid = 0";
            $result_product = mysqli_query($conn, $query_product);
            if (mysqli_num_rows($result_product) > 0) {
                while($row_product = mysqli_fetch_assoc($result_product)){
                    $product_id = $row_product['product_id'];
                    $product_details = getProductDetails($product_id);
                    $grade_details = getGradeDetails($product_details['grade']);
                    
                    $total_price += ($product_details['unit_price'] * (1 - $discount)) * $row_product['quantity'];
                    $total_price_undisc += $product_details['unit_price'] * $row_product['quantity'];
                    $total_qty += $row_product['quantity'];

                    $total_per_component += ($product_details['unit_price'] * (1 - $discount)) * $row_product['quantity'];
                    $undisc_total_per_component += $product_details['unit_price'] * $row_product['quantity'];
                    $qty_per_component += $row_product['quantity'];
                    
                }
                
                $data[] = [
                    $qty_per_component,
                    'Others',
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
        $pdf->SetXY($col2_x, $col_y);
        $pdf->Cell(40, $lineheight, 'MISC:', 0, 0);
        $pdf->Cell(20, $lineheight, $discount < 0 ? '-' : '' .$discount * 100 .'%', 0, 1, 'R');

        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(40, $lineheight, 'SUBTOTAL:', 0, 0);
        $pdf->Cell(20, $lineheight, '$ ' .number_format($total_price,2), 0, 1 , 'R');

        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(40, $lineheight, 'DELIVERY:', 0, 0);
        $pdf->Cell(20, $lineheight, '$ ' .number_format($delivery_price,2) , 0, 1 , 'R');

        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(40, $lineheight, 'SALES TAX:', 0, 0);
        $pdf->Cell(20, $lineheight, '$ ' .number_format($total_price * $tax,2), 0, 1 , 'R');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(40, $lineheight, 'GRAND TOTAL:', 0, 0);
        $pdf->Cell(20, $lineheight, '$ ' .number_format(($total_price - ($total_price * $discount) + $delivery_price),2), 0, 1, 'R');

        $pdf->Ln(5);
        $pdf->Output();
            

        
    }
}else{
    echo "ID not Found!";
}

?>
