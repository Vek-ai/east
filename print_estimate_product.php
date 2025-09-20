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

function NbLines($pdf, $w, $txt) {
    $txt = str_replace("\r", '', (string)$txt);
    $nb = strlen($txt);

    if ($nb > 0 && $txt[$nb - 1] == "\n") {
        $nb--;
    }

    $lines = 1;
    $lineWidth = 0;
    $maxWidth = $w; // don’t use $pdf->cMargin
    $spaceWidth = $pdf->GetStringWidth(' ');

    for ($i = 0; $i < $nb; $i++) {
        $c = $txt[$i];
        if ($c == "\n") {
            $lines++;
            $lineWidth = 0;
            continue;
        }

        $charWidth = $pdf->GetStringWidth($c);
        if ($lineWidth + $charWidth > $maxWidth) {
            $lines++;
            $lineWidth = $charWidth;
        } else {
            $lineWidth += $charWidth;
        }
    }
    return $lines;
}

function decimalToFractionInch($decimal, $precision = 16) {
    $inch = round($decimal * $precision);
    $whole = floor($inch / $precision);
    $remainder = $inch % $precision;

    if ($remainder == 0) {
        return $whole . '"';
    }

    $gcd = gcd($remainder, $precision);
    $remainder /= $gcd;
    $denom = $precision / $gcd;

    if ($whole > 0) {
        return $whole . " " . $remainder . "/" . $denom . '"';
    }
    return $remainder . "/" . $denom . '"';
}

function gcd($a, $b) {
    return ($b == 0) ? $a : gcd($b, $a % $b);
}

function renderTableHeader($pdf, $columns) {
    $pdf->SetFillColor(211, 211, 211);
    $pdf->SetFont('Arial', 'B', 7);

    foreach ($columns as $col) {
        $pdf->Cell($col['width'], 6, $col['label'], 1, 0, $col['align'], true);
    }
    $pdf->Ln();

    $pdf->SetFillColor(255, 255, 255);
}

function renderScrewCategory($pdf, $products, $conn) {
    $columns = [
        ['label' => 'DESCRIPTION', 'width' => 65, 'align' => 'C'],
        ['label' => 'COLOR', 'width' => 30, 'align' => 'C'],
        ['label' => 'Profile', 'width' => 20, 'align' => 'C'],
        ['label' => 'QTY', 'width' => 20, 'align' => 'C'],
        ['label' => 'PACK COUNT', 'width' => 25, 'align' => 'C'],
        ['label' => 'CUSTOMER PRICE', 'width' => 28, 'align' => 'R'],
    ];

    renderTableHeader($pdf, $columns);
    $pdf->SetFont('Arial', '', 8);

    $totalQty   = 0;
    $totalPrice = 0;
    $totalActual = 0;

    foreach ($products as $row_product) {
        $product_details = getProductDetails($row_product['product_id']);
        $profile_details = getProfileTypeDetails($product_details['profile']);

        $row = [
            $product_details['product_item'] . 
                (!empty($row_product['note']) ? "\nNote: " . $row_product['note'] : ''),
            getColorName($row_product['custom_color']),
            $profile_details['profile_type'] ?? '',
            $row_product['quantity'],
            $row_product['pack_count'] ?? '',
            '$ ' . number_format($row_product['discounted_price'], 2)
        ];

        renderRow($pdf, $columns, $row);

        $totalQty   += $row_product['quantity'];
        $totalPrice += floatval($row_product['discounted_price']);
        $totalActual += floatval($row_product['actual_price']);
    }

    return [$totalPrice, $totalQty, $totalActual];
}

function renderPanelCategory($pdf, $products, $conn) {
    $columns = [
        ['label' => 'DESCRIPTION', 'width' => 45, 'align' => 'C'],
        ['label' => 'COLOR', 'width' => 30, 'align' => 'C'],
        ['label' => 'Grade', 'width' => 20, 'align' => 'C'],
        ['label' => 'Profile', 'width' => 20, 'align' => 'C'],
        ['label' => 'QTY', 'width' => 20, 'align' => 'C'],
        ['label' => 'LENGTH (FT)', 'width' => 25, 'align' => 'C'],
        ['label' => 'CUSTOMER PRICE', 'width' => 28, 'align' => 'R'],
    ];

    renderTableHeader($pdf, $columns);

    $grouped = [];
    foreach ($products as $row_product) {
        $product_id       = $row_product['product_id'];
        $bundle_name     = $row_product['bundle_id'];
        $product_details = getProductDetails($product_id);
        $grade_details   = getGradeDetails($product_details['grade']);
        $profile_details = getProfileTypeDetails($product_details['profile']);

        $quantity   = floatval($row_product['quantity'] ?? 0);
        $act_price  = floatval($row_product['actual_price']);
        $disc_price = floatval($row_product['discounted_price']);
        $note       = $row_product['note'] ?? '';

        $panel_type  = $row_product['panel_type'];
        $panel_style = $row_product['panel_style'];

        $ft  = floatval($row_product['custom_length'] ?? 0);
        $in  = floatval($row_product['custom_length2'] ?? 0);
        $len = $ft + ($in / 12);

        $key = $product_id;
        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'product_name'    => $product_details['product_item'],
                'color'           => getColorName($row_product['custom_color']),
                'grade'           => $grade_details['grade_abbreviations'] ?? '',
                'profile'         => $profile_details['profile_type'] ?? '',
                'quantity'        => 0,
                'length'          => 0,
                'discounted_total'=> 0,
                'actual_total'    => 0,
                'note'            => $note,
                'bundles'         => []
            ];
        }

        $grouped[$key]['quantity']        += $quantity;
        $grouped[$key]['length']          += $len;
        $grouped[$key]['discounted_total']+= $disc_price;
        $grouped[$key]['actual_total']    += $act_price;

        if (!isset($grouped[$key]['bundles'][$bundle_name])) {
            $grouped[$key]['bundles'][$bundle_name] = [
                'bundle_name' => $bundle_name,
                'rows'        => []
            ];
        }

        $grouped[$key]['bundles'][$bundle_name]['rows'][] = [
            'qty'        => $quantity,
            'ft'         => $ft,
            'inch'       => decimalToFractionInch($in),
            'panel_type' => $panel_type,
            'panel_style'=> $panel_style,
            'disc_price' => $disc_price,
            'act_price'  => $act_price,
            'note'       => $note,
        ];
    }

    $totalQty    = 0;
    $totalPrice  = 0;
    $totalActual = 0;

    foreach ($grouped as $g) {
        $summaryRow = [
            $g['product_name'],
            $g['color'],
            $g['grade'],
            $g['profile'],
            $g['quantity'],
            number_format($g['length'], 2),
            '$ ' . number_format($g['discounted_total'], 2)
        ];
        renderRow($pdf, $columns, $summaryRow, true);

        $totalQty    += $g['quantity'];
        $totalPrice  += $g['discounted_total'];
        $totalActual += $g['actual_total'];

        foreach ($g['bundles'] as $bundle) {
            $subColumns = [
                ['label' => $bundle['bundle_name'], 'width' => 45, 'align' => 'C'],
                ['label' => 'Qty', 'width' => 30, 'align' => 'C'],
                ['label' => 'Ft', 'width' => 20, 'align' => 'C'],
                ['label' => 'In', 'width' => 20, 'align' => 'C'],
                ['label' => 'Panel Type', 'width' => 20, 'align' => 'C'],
                ['label' => 'Panel Style', 'width' => 25, 'align' => 'C'],
                ['label' => 'Line Item Price (Disc)', 'width' => 28, 'align' => 'R'],
            ];

            renderTableHeader($pdf, $subColumns);
            $pdf->SetFont('Arial', '', 7);

            foreach ($bundle['rows'] as $row) {
                $subRow = [
                    (!empty($row['note']) ? "Note: " . $row['note'] : ''),
                    $row['qty'],
                    $row['ft'],
                    $row['inch'],
                    $row['panel_type'],
                    $row['panel_style'],
                    '$ ' . number_format($row['disc_price'], 2)
                ];
                renderRow($pdf, $subColumns, $subRow);
            }
        }
    }

    return [$totalPrice, $totalQty, $totalActual];
}

function renderDefaultCategory($pdf, $products, $conn) {
    $columns = [
        ['label' => 'DESCRIPTION', 'width' => 45, 'align' => 'C'],
        ['label' => 'COLOR', 'width' => 30, 'align' => 'C'],
        ['label' => 'Grade', 'width' => 20, 'align' => 'C'],
        ['label' => 'Profile', 'width' => 20, 'align' => 'C'],
        ['label' => 'QTY', 'width' => 20, 'align' => 'C'],
        ['label' => 'LENGTH (FT)', 'width' => 25, 'align' => 'C'],
        ['label' => 'CUSTOMER PRICE', 'width' => 28, 'align' => 'R'],
    ];

    renderTableHeader($pdf, $columns);
    $pdf->SetFont('Arial', '', 8);

    $totalQty    = 0;
    $totalPrice  = 0;
    $totalActual = 0;

    foreach ($products as $row_product) {
        $product_details = getProductDetails($row_product['product_id']);
        $grade_details   = getGradeDetails($product_details['grade']);
        $profile_details = getProfileTypeDetails($product_details['profile']);

        $ft  = floatval($row_product['custom_length'] ?? 0);
        $in  = floatval($row_product['custom_length2'] ?? 0);
        $len = $ft + ($in / 12);

        $row = [
            $product_details['product_item'] . (!empty($row_product['note']) ? "\nNote: " . $row_product['note'] : ''),
            getColorName($row_product['custom_color']),
            $grade_details['product_grade'] ?? '',
            $profile_details['profile_type'] ?? '',
            $row_product['quantity'],
            number_format($len, 2),
            '$ ' . number_format($row_product['discounted_price'], 2)
        ];

        renderRow($pdf, $columns, $row);

        $totalQty    += $row_product['quantity'];
        $totalPrice  += floatval($row_product['discounted_price']);
        $totalActual += floatval($row_product['actual_price']);
    }

    return [$totalPrice, $totalQty, $totalActual];
}

function renderTrimCategory($pdf, $products, $conn) {
    $columns = [
        ['label' => 'DESCRIPTION', 'width' => 45, 'align' => 'C'],
        ['label' => 'COLOR', 'width' => 30, 'align' => 'C'],
        ['label' => 'Grade', 'width' => 20, 'align' => 'C'],
        ['label' => 'Profile', 'width' => 20, 'align' => 'C'],
        ['label' => 'QTY', 'width' => 20, 'align' => 'C'],
        ['label' => 'LENGTH (FT)', 'width' => 25, 'align' => 'C'],
        ['label' => 'CUSTOMER PRICE', 'width' => 28, 'align' => 'R'],
    ];

    renderTableHeader($pdf, $columns);
    $pdf->SetFont('Arial', '', 8);

    $totalQty    = 0;
    $totalPrice  = 0;
    $totalActual = 0;

    foreach ($products as $row_product) {
        $product_details = getProductDetails($row_product['product_id']);
        $grade_details   = getGradeDetails($product_details['grade']);
        $profile_details = getProfileTypeDetails($product_details['profile']);

        $ft  = floatval($row_product['custom_length'] ?? 0);
        $in  = floatval($row_product['custom_length2'] ?? 0);
        $len = $ft + ($in / 12);

        $row = [
            $product_details['product_item'] . (!empty($row_product['note']) ? "\nNote: " . $row_product['note'] : ''),
            getColorName($row_product['custom_color']),
            $grade_details['product_grade'] ?? '',
            $profile_details['profile_type'] ?? '',
            $row_product['quantity'],
            number_format($len, 2),
            '$ ' . number_format($row_product['discounted_price'], 2)
        ];

        renderRow($pdf, $columns, $row);

        $totalQty    += $row_product['quantity'];
        $totalPrice  += floatval($row_product['discounted_price']);
        $totalActual += floatval($row_product['actual_price']);
    }

    return [$totalPrice, $totalQty, $totalActual];
}

function renderLumberCategory($pdf, $products, $conn) {
    $columns = [
        ['label' => 'DESCRIPTION', 'width' => 115, 'align' => 'C'],
        ['label' => 'QTY', 'width' => 20, 'align' => 'C'],
        ['label' => 'LENGTH (FT)', 'width' => 25, 'align' => 'C'],
        ['label' => 'CUSTOMER PRICE', 'width' => 28, 'align' => 'R'],
    ];

    renderTableHeader($pdf, $columns);
    $pdf->SetFont('Arial', '', 8);

    $totalQty    = 0;
    $totalPrice  = 0;
    $totalActual = 0;

    foreach ($products as $row_product) {
        $product_details = getProductDetails($row_product['product_id']);

        $ft  = floatval($row_product['custom_length'] ?? 0);
        $in  = floatval($row_product['custom_length2'] ?? 0);
        $len = $ft + ($in / 12);

        $row = [
            $product_details['product_item'] . (!empty($row_product['note']) ? "\nNote: " . $row_product['note'] : ''),
            $row_product['quantity'],
            number_format($len, 2),
            '$ ' . number_format($row_product['discounted_price'], 2)
        ];

        renderRow($pdf, $columns, $row);

        $totalQty    += $row_product['quantity'];
        $totalPrice  += floatval($row_product['discounted_price']);
        $totalActual += floatval($row_product['actual_price']);
    }

    return [$totalPrice, $totalQty, $totalActual];
}

function renderRow($pdf, $columns, $row, $bold = false) {
    $pdf->SetFont('Arial', $bold ? 'B' : '', 8);

    $cellHeights = [];
    foreach ($row as $i => $cell) {
        $cellHeights[$i] = NbLines($pdf, $columns[$i]['width'], $cell) * 5;
    }
    $maxHeight = max($cellHeights);

    $x = $pdf->GetX();
    $y = $pdf->GetY();

    foreach ($row as $i => $cell) {
        $w = $columns[$i]['width'];
        $h = $maxHeight;
        $xBefore = $pdf->GetX();
        $yBefore = $pdf->GetY();

        $pdf->Rect($xBefore, $yBefore, $w, $h);
        $pdf->MultiCell($w, 5, $cell, 0, $columns[$i]['align']);
        $pdf->SetXY($xBefore + $w, $yBefore);
    }

    $pdf->Ln($maxHeight);
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
$lumber_id = 1;
$screw_id = 16;
$panel_id = 3;
$trim_id = 4;

$estimateid = $_REQUEST['id'];
$pricing_id = $_REQUEST['pricing_id'] ?? '';
$current_user_id = $_SESSION['userid'];

$query = "SELECT * FROM estimates WHERE estimateid = '$estimateid'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while($row_estimates = mysqli_fetch_assoc($result)){
        $delivery_price = floatval($row_estimates['delivery_amt']);
        $discount = floatval($row_estimates['discount_percent']) / 100;
        $estimateid = $row_estimates['estimateid'];
        $customer_id = $row_estimates['customerid'];
        $customerDetails = getCustomerDetails($customer_id);
        $tax = floatval(getCustomerTax($customer_id)) / 100;
        $delivery_method = 'Deliver';
        $estimated_date = date("F d, Y", strtotime($row_estimates['estimated_date']));
        $scheduled_date = '';
        if (!empty($row_estimates['scheduled_date']) && strtotime($row_estimates['scheduled_date']) !== false) {
            $scheduled_date = date("F d, Y", strtotime($row_estimates['scheduled_date']));
        }
        if($delivery_price == 0){
            $delivery_method = 'Pickup';
        }
        $pdf->SetFont('Arial', '', 10);
        $pdf->Image('assets/images/logo-bw.png', 10, 6, 60, 20);

        $pdf->SetXY(10, 26);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 5, '977 E Hal Rogers Parkway', 0, 1);
        $pdf->Cell(0, 5, 'London, KY 40741', 0, 1);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 5, 'Phone: (606) 877-1848 | Fax: (606) 864-4280', 0, 1);
        $pdf->Cell(0, 5, 'Email: Sales@Eastkentuckymetal.com', 0, 1);
        $pdf->Cell(0, 5, 'Website: Eastkentuckymetal.com', 0, 1);

        $pdf->SetXY($col2_x-10, 6);
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(95, 5, "Estimate #: $estimateid", 0, 'L');
        $pdf->SetXY($col2_x-10, $pdf->GetY());
        $pdf->Cell(95, 5, "Estimate Date: " .$estimated_date, 0, 1, 'L');
        
        $pdf->SetXY($col2_x-10, $pdf->GetY());
        $pdf->Cell(95, 5, 'Pick-up or Delivery: ' . $delivery_method, 0, 1, 'L');
        $pdf->SetXY($col2_x-10, $pdf->GetY());
        $pdf->Cell(95, 5, 'Scheduled Date: ' . $scheduled_date, 0, 1, 'L');
        $pdf->SetXY($col2_x-10, $pdf->GetY());
        $pdf->Cell(95, 5, 'Salesperson: ' . get_staff_name($current_user_id), 0, 1, 'L');

        $pdf->Ln(30);

        $col1_x = 10;
        $col2_x -= 30;
        $col3_x = 140;
        $pageWidth = $pdf->GetPageWidth();
        $marginLeft = 10;
        $marginRight = 10;
        $usableWidth = $pageWidth - $marginLeft - $marginRight;
        $mailToWidth = $usableWidth / 2;

        

        $currentY = $pdf->GetY();
        $pdf->SetFillColor(211, 211, 211);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($mailToWidth+10, 7, 'Bill to:', 0, 1, 'L', true);

        $pdf->SetXY($col2_x, $currentY);
        $pdf->SetFillColor(211, 211, 211);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($mailToWidth-10, 7, 'Ship to:', 0, 1, 'L', true);

        $def_y = $pdf->GetY();

        $pdf->SetXY($col1_x, $def_y);
        $pdf->MultiCell($mailToWidth, 5, get_customer_name($row_estimates['customerid']), 0, 'L');

        $pdf->SetFont('Arial', '', 10);
        $addressParts = [];
        if (!empty($customerDetails['address'])) {
            $addressParts[] = $customerDetails['address'];
        }
        if (!empty($customerDetails['city'])) {
            $addressParts[] = $customerDetails['city'];
        }
        if (!empty($customerDetails['state'])) {
            $addressParts[] = $customerDetails['state'];
        }
        if (!empty($customerDetails['zip'])) {
            $addressParts[] = $customerDetails['zip'];
        }
        $address = implode(', ', $addressParts);
        $pdf->MultiCell($mailToWidth, 5, $address , 0, 'L');


        $pdf->SetXY($col2_x, $def_y);
        $pdf->MultiCell(60, 5, $row_estimates['deliver_fname'] . " " .$row_estimates['deliver_lname'], 0, 'L');
        
        $shipAddressParts = [];
        if (!empty($row_estimates['deliver_address'])) {
            $shipAddressParts[] = $row_estimates['deliver_address'];
        }
        if (!empty($row_estimates['deliver_city'])) {
            $shipAddressParts[] = $row_estimates['deliver_city'];
        }
        if (!empty($row_estimates['deliver_state'])) {
            $shipAddressParts[] = $row_estimates['deliver_state'];
        }
        if (!empty($row_estimates['deliver_zip'])) {
            $shipAddressParts[] = $row_estimates['deliver_zip'];
        }
        $shipAddress = implode(', ', $shipAddressParts);
        $pdf->SetX($col2_x);
        $pdf->MultiCell($mailToWidth, 5, $shipAddress, 0, 'L');

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetXY($col1_x, $pdf->GetY());
        $pdf->Cell(60, 5, $customerDetails['contact_phone'], 0, 0, 'L');
        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(60, 5, '', 0, 1, 'L');

        $pdf->SetXY($col1_x, $pdf->GetY());
        $pdf->Cell(10, 5, 'Tax Exempt #: ', 0, 0, 'L');
        $pdf->SetXY($col2_x-30, $pdf->GetY());
        $pdf->Cell(10, 5, 'Customer PO #: ' .$row_estimates['job_po'], 0, 0, 'L');

        $pdf->SetXY($col3_x, $pdf->GetY());
        $pdf->Cell(60, 5, 'Job Name: ' .$row_estimates['job_name'], 0, 1, 'L');

        $total_price = 0;
        $total_qty = 0;
        $screw_id = 16;

        $query_product = "
            SELECT p.product_category, op.* 
            FROM estimate_prod AS op
            LEFT JOIN product AS p ON p.product_id = op.product_id
            WHERE estimateid = '$estimateid'
            ORDER BY p.product_category
        ";
        $result_product = mysqli_query($conn, $query_product);

        $productsByCategory = [];

        while ($row_product = mysqli_fetch_assoc($result_product)) {
            $cat = $row_product['product_category'];
            if (!isset($productsByCategory[$cat])) {
                $productsByCategory[$cat] = [];
            }
            $productsByCategory[$cat][] = $row_product;
        }

        $total_price  = 0;
        $total_qty    = 0;
        $total_actual = 0;
        $total_saved  = 0;

        foreach ($productsByCategory as $categoryId => $products) {
            if ($categoryId == $screw_id) {
                [$catTotal, $catQty, $catActual] = renderScrewCategory($pdf, $products, $conn);
            } elseif ($categoryId == $panel_id) {
                [$catTotal, $catQty, $catActual] = renderPanelCategory($pdf, $products, $conn);
            } elseif ($categoryId == $trim_id) {
                [$catTotal, $catQty, $catActual] = renderTrimCategory($pdf, $products, $conn);
            } elseif ($categoryId == $lumber_id) {
                [$catTotal, $catQty, $catActual] = renderLumberCategory($pdf, $products, $conn);
            } else {
                [$catTotal, $catQty, $catActual] = renderDefaultCategory($pdf, $products, $conn);
            }

            $catSaved = floatval($catActual) - floatval($catTotal);

            $total_price  += floatval($catTotal);
            $total_qty    += intval($catQty);
            $total_actual += floatval($catActual);
            $total_saved  += $catSaved;

            $pdf->Ln(3);
        }

        $lineheight = 6;

        $pdf->SetX(130);
        $pdf->SetFillColor(211, 211, 211);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(40, $lineheight, 'Customer Savings:', 1, 0, 'L', true);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->Cell(28, $lineheight, '$ ' . number_format($total_saved, 2), 1, 1, 'R', true);

        $pdf->Ln(5);

        $col1_x = 10;
        $col2_x = 140;
        $col_y = $pdf->GetY();

        

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetXY($col1_x, $col_y);
        $disclaimer = "Customer is solely responsible for accuracy of order and for verifying accuracy of materials before leaving EKMS or at time of delivery. If an agent orders or takes materials on customer's behalf, EKMS is entitled to rely upon the agent as if s/he has full authority to act on customer's behalf. No returns on metal panels or special trim. All other materials returned undamaged within 60 days of invoice date are subject to a restocking fee equal to 25% of current retail price.";
        $neededHeight = 4 * ceil($pdf->GetStringWidth($disclaimer) / 120);

        if ($pdf->GetY() + $neededHeight > $pdf->GetPageHeight() - 20) {
            $pdf->AddPage();
            $col_y = $pdf->GetY();
        }

        $pdf->MultiCell(120, 4, $disclaimer, 0);

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
