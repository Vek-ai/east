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
$screw_id = 16;

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
        $screw_id = 16;

        $query_category = "SELECT * FROM product_category WHERE hidden = 0";
        $result_category = mysqli_query($conn, $query_category);

        if (mysqli_num_rows($result_category) > 0) {
            while ($row_category = mysqli_fetch_assoc($result_category)) {
                $product_category_id = $row_category['product_category_id'];

                // Fetch products in this category
                $query_product = "
                    SELECT p.product_category, op.* 
                    FROM order_product AS op
                    LEFT JOIN product AS p ON p.product_id = op.productid
                    WHERE orderid = '$orderid' AND p.product_category = '$product_category_id'
                ";
                $result_product = mysqli_query($conn, $query_product);

                if (mysqli_num_rows($result_product) > 0) {

                    $pdf->Ln();
                    $pdf->SetFont('Arial', 'B', 9);
                    $pdf->SetXY($col1_x, $pdf->GetY());
                    $pdf->Cell(10, 5, getProductCategoryName($product_category_id), 0, 1, 'L');

                    if ($product_category_id == $screw_id) {
                        $columns = [
                            ['label' => 'DESCRIPTION', 'width' => 45, 'align' => 'C'],
                            ['label' => 'COLOR', 'width' => 30, 'align' => 'C'],
                            ['label' => 'Grade', 'width' => 20, 'align' => 'C'],
                            ['label' => 'Profile', 'width' => 20, 'align' => 'C'],
                            ['label' => 'QTY', 'width' => 20, 'align' => 'C'],
                            ['label' => 'PACK COUNT', 'width' => 25, 'align' => 'C'],
                            ['label' => 'CUSTOMER PRICE', 'width' => 28, 'align' => 'R'],
                        ];
                    } else {
                        $columns = [
                            ['label' => 'DESCRIPTION', 'width' => 45, 'align' => 'C'],
                            ['label' => 'COLOR', 'width' => 30, 'align' => 'C'],
                            ['label' => 'Grade', 'width' => 20, 'align' => 'C'],
                            ['label' => 'Profile', 'width' => 20, 'align' => 'C'],
                            ['label' => 'QTY', 'width' => 20, 'align' => 'C'],
                            ['label' => 'LENGTH (FT)', 'width' => 25, 'align' => 'C'],
                            ['label' => 'CUSTOMER PRICE', 'width' => 28, 'align' => 'R'],
                        ];
                    }

                    $pdf->SetFont('Arial', 'B', 7);
                    foreach ($columns as $col) {
                        $pdf->Cell($col['width'], 10, $col['label'], 1, 0, $col['align']);
                    }
                    $pdf->Ln();

                    $data = [];
                    $grouped = [];
                    $trim_directory = "images/drawing/";

                    while ($row_product = mysqli_fetch_assoc($result_product)) {
                        $productid = $row_product['productid'];
                        $product_details = getProductDetails($productid);
                        $grade_details   = getGradeDetails($product_details['grade']);
                        $profile_details = getProfileTypeDetails($product_details['profile']);

                        $product_name = !empty($row_product['product_item']) ? $row_product['product_item'] : $product_details['product_item'];

                        $quantity = floatval($row_product['quantity'] ?? 0);
                        $unit_price = floatval($row_product['actual_price']);       // <-- actual price
                        $discounted_price = floatval($row_product['discounted_price']); // <-- discounted price

                        $ft = floatval($row_product['custom_length'] ?? 0);
                        $in = floatval($row_product['custom_length2'] ?? 0);
                        $length_ft_decimal = $ft + ($in / 12);

                        $key = $productid;

                        $bundle_name = $row_product['bundle_id'];

                        if (!isset($grouped[$key])) {
                            $grouped[$key] = [
                                'product_name' => $product_name,
                                'color' => getColorName($product_details['color']),
                                'grade' => $grade_details['grade_abbreviations'] ?? '',
                                'profile' => $profile_details['profile_type'] ?? '',
                                'quantity' => 0,
                                'length' => 0,
                                'discounted_total' => 0,
                                'actual_total' => 0,
                                'bundles' => []
                            ];
                        }

                        $grouped[$key]['quantity'] += $quantity;
                        $grouped[$key]['length']   += $length_ft_decimal;
                        $grouped[$key]['discounted_total'] += $discounted_price;
                        $grouped[$key]['actual_total']     += $unit_price;

                        if (!isset($grouped[$key]['bundles'][$bundle_name])) {
                            $grouped[$key]['bundles'][$bundle_name] = [
                                'bundle_name' => $bundle_name,
                                'rows' => []
                            ];
                        }

                        $grouped[$key]['bundles'][$bundle_name]['rows'][] = [
                            'product_name' => $product_name,
                            'color' => getColorName($product_details['color']),
                            'grade' => $grade_details['grade_abbreviations'] ?? '',
                            'profile' => $profile_details['profile_type'] ?? '',
                            'qty' => $quantity,
                            'length' => $length_ft_decimal,
                            'unit_price' => $unit_price,
                            'disc_price' => $discounted_price
                        ];
                    }

                    $pdf->SetFont('Arial', '', 8);

                    foreach ($grouped as $g) {
                        // --- GROUPED SUMMARY ROW ---
                        $pdf->SetFont('Arial', 'B', 8);
                        $rowHeight = 5;
                        $summaryRow = [
                            $g['product_name'],
                            $g['color'],
                            $g['grade'],
                            $g['profile'],
                            $g['quantity'],
                            number_format($g['length'], 2),
                            '$ ' . number_format($g['discounted_total'], 2)
                        ];

                        $total_price += $g['discounted_total'];

                        $xStart = $pdf->GetX();
                        $yStart = $pdf->GetY();

                        $firstColWidth = $columns[0]['width'];
                        $xStart = $pdf->GetX();
                        $yStart = $pdf->GetY();

                        $pdf->MultiCell($firstColWidth, $rowHeight, $summaryRow[0], 1, $columns[0]['align']);

                        $yEnd = $pdf->GetY();
                        $cellHeight = $yEnd - $yStart;
                        $xAfter = $xStart + $firstColWidth;
                        $pdf->SetXY($xAfter, $yStart);

                        for ($i = 1; $i < count($summaryRow); $i++) {
                            $pdf->Cell($columns[$i]['width'], $cellHeight, $summaryRow[$i], 1, 0, $columns[$i]['align']);
                        }
                        $pdf->Ln($cellHeight);

                        $subColumns = [
                            ['label' => 'Bundle Name', 'width' => 45, 'align' => 'C'],
                            ['label' => 'Qty', 'width' => 30, 'align' => 'C'],
                            ['label' => 'Length', 'width' => 20, 'align' => 'C'],
                            ['label' => 'Panel Type', 'width' => 20, 'align' => 'C'],
                            ['label' => 'Panel Style', 'width' => 20, 'align' => 'C'],
                            ['label' => 'Bundle Name', 'width' => 25, 'align' => 'R'],
                            ['label' => 'Line Item Price (Disc)', 'width' => 28, 'align' => 'R'],
                        ];
                        $pdf->SetFont('Arial', 'B', 6.5);
                        foreach ($subColumns as $col) {
                            $pdf->Cell($col['width'], 6, $col['label'], 1, 0, $col['align']);
                        }
                        $pdf->Ln();

                        $pdf->SetFont('Arial', '', 7);
                        $bundle_qty_total = $bundle_length_total = $bundle_price_total = $bundle_disc_total = 0;

                        foreach ($g['bundles'] as $bundle) {
                            foreach ($bundle['rows'] as $row) {
                                $subRow = [
                                    $bundle['bundle_name'],
                                    $row['qty'],
                                    number_format($row['length'], 2),
                                    $row['panel_type'] ?? '', 
                                    $row['panel_style'] ?? '',
                                    '',
                                    '$ ' . number_format($row['disc_price'], 2)
                                ];
                                foreach ($subRow as $i => $cell) {
                                    $pdf->Cell($subColumns[$i]['width'], 6, $cell, 1, 0, $subColumns[$i]['align']);
                                }
                                $pdf->Ln();

                                $bundle_qty_total    += $row['qty'];
                                $bundle_length_total += $row['length'];
                                $bundle_price_total  += $row['unit_price'];
                                $bundle_disc_total   += $row['disc_price'];
                            }
                        }

                        $pdf->SetFont('Arial', 'B', 7);
                        $totalsRow = [
                            'TOTAL',
                            $bundle_qty_total,
                            number_format($bundle_length_total, 2),
                            '',
                            '',
                            '',
                            '$ ' . number_format($bundle_disc_total, 2)
                        ];
                        foreach ($totalsRow as $i => $cell) {
                            $pdf->Cell($subColumns[$i]['width'], 6, $cell, 1, 0, $subColumns[$i]['align']);
                        }
                        $pdf->Ln(10);
                    }



                }
            }
        } else {
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

        $subtotal   = $total_price;
        $sales_tax  = $subtotal * $tax;
        $grand_total = $subtotal + $delivery_price + $sales_tax;

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
