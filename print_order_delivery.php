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

$table_id = $_REQUEST['id'];
$current_user_id = $_SESSION['userid'];
$type = $_REQUEST['type'] ?? 'order';

if($type == 'approval'){
    $query = "SELECT * FROM approval WHERE approval_id='$table_id'";
}else if($type == 'estimate'){
    $query = "SELECT * FROM estimates WHERE estimateid='$table_id'";
}else if($type == 'order'){
    $query = "SELECT * FROM orders WHERE orderid = '$table_id'";
}

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while($row_orders = mysqli_fetch_assoc($result)){
        if($type == 'approval'){
            $date = $row_orders['submitted_date'];
        }else if($type == 'estimate'){
            $date = $row_orders['estimated_date'];
        }else if($type == 'order'){
            $date = $row_orders['order_date'];
        }

        $delivery_price = floatval($row_orders['delivery_amt']);
        $discount = floatval($row_orders['discount_percent']) / 100;
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
        $pdf->MultiCell(95, 5, ucwords($type) ." #: $table_id", 0, 'L');
        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(95, 5, "Date: " .date("F d, Y", strtotime($date)), 0, 1, 'L');
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

        $pdf->SetXY($col1_x, $pdf->GetY());
        $pdf->Cell(10, 5, 'Truck: ' .getTruckName($row_orders['truck']), 0, 0, 'L');

        $pdf->Ln(5);

        $total_price = 0;
        $total_qty = 0;

        $data = array();
        if($type == 'approval'){
            $query_product = "SELECT * FROM approval_product WHERE approval_id = '$table_id'";
        }else if($type == 'estimate'){
            $query_product = "SELECT * FROM estimate_prod WHERE estimateid = '$table_id'";
        }else if($type == 'order'){
            $query_product = "SELECT * FROM order_product WHERE orderid = '$table_id'";
        }

       
        $result_product = mysqli_query($conn, $query_product);
        if (mysqli_num_rows($result_product) > 0) {

            $pdf->SetFont('Arial', 'B', 7);
            $widths = [50, 141];
            $headers = ['QTY', 'PRODUCT ITEM'];

            for ($i = 0; $i < count($headers); $i++) {
                $pdf->Cell($widths[$i], 10, $headers[$i], 1, 0, 'C');
            }
            $pdf->Ln();

            while($row_product = mysqli_fetch_assoc($result_product)){
                if($type == 'approval'){
                    $productid = $row_product['productid'];
                }else if($type == 'estimate'){
                    $productid = $row_product['product_id'];
                }else if($type == 'order'){
                    $productid = $row_product['productid'];
                }
                $product_details = getProductDetails($productid);
                $grade_details = getGradeDetails($product_details['grade']);
                $data[] = [
                    $row_product['quantity'],
                    $product_details['product_item']
                ];

                $total_price += ($product_details['unit_price'] * (1 - $discount)) * $row_product['quantity'];
                $total_qty += $row_product['quantity'];
            }

            $pdf->SetFont('Arial', '', 8);

            foreach ($data as $row) {
                $height = NbLines($pdf, $widths[1], $row[1]) * 5; 
                
                $y_initial = $pdf->GetY();

                $pdf->Cell($widths[0], $height, $row[0], 'LR', 0, 'C');
                
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->MultiCell($widths[1], 5, $row[1], 'LR', 'C');
                $pdf->SetXY($x + $widths[1], $y_initial);

                $pdf->Ln();

                $y_bottom = $pdf->GetY();

                $pdf->Line(10, $y_initial + $height, 210 - 10, $y_initial + $height);

                
            }
            
        }else {
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->SetTextColor(255, 0, 0);
            $pdf->Cell(0, 10, 'No approved products found for dispatch.', 0, 1, 'C');
            $pdf->SetTextColor(0, 0, 0);
        }

        $pdf->Ln(5);

        $col1_x = 10;
        $col2_x = 140;
        $col_y = $pdf->GetY();

        $lineheight = 6;

        $query_qr = "SELECT * FROM order_estimate WHERE order_estimate_id = '$table_id' AND type = '2'";
        $result_qr = mysqli_query($conn, $query_qr);
        if (mysqli_num_rows($result_qr) > 0) {
            $row_qr = mysqli_fetch_assoc($result_qr);
            $image_url = 'https://delivery.ilearnsda.com/deliveryqr/qrcode' . $row_qr['id'] . '.png';

            $headers = @get_headers($image_url, 1);
            if (isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'image/png') !== false) {
                $image_data = @file_get_contents($image_url);
                if ($image_data !== false) {
                    $local_image_path = 'temp_qr_' . $row_qr['id'] . '.png';
                    file_put_contents($local_image_path, $image_data);

                    $info = @getimagesize($local_image_path);
                    if ($info && $info['mime'] === 'image/png') {
                        $pdf->SetXY($col1_x, $col_y);
                        $pdf->Image($local_image_path, $col1_x, $col_y, 60, 60);
                    }

                    unlink($local_image_path);
                }
            }
        }

        
        $pdf->SetFont('Arial', '', 9);

        $subtotal   = $total_price;
        $sales_tax  = $subtotal * $tax;
        $grand_total = $subtotal + $delivery_price + $sales_tax;

        $pdf->SetXY($col2_x, $col_y);
        $pdf->Cell(40, $lineheight, 'MISC:', 0, 0);
        $pdf->Cell(20, $lineheight, ($discount < 0 ? '-' : '') . $discount * 100 .'%', 0, 1, 'R');

        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(40, $lineheight, 'DELIVERY:', 0, 0);
        $pdf->Cell(20, $lineheight, '$ ' . number_format($delivery_price, 2), 0, 1 , 'R');

        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(40, $lineheight, 'SUBTOTAL:', 0, 0);
        $pdf->Cell(20, $lineheight, '$ ' . number_format($subtotal, 2), 0, 1 , 'R');

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
