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

$col1_x = 10;
$lumber_id = 1;
$screw_id = 16;
$panel_id = 3;
$trim_id = 4;

$orderid = $_REQUEST['id'];
$pricing_id = $_REQUEST['pricing_id'] ?? '';

$columns = [
    ['label' => 'PRODUCT ID', 'width' => 29, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'DESCRIPTION',  'width' => 30, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'COLOR',        'width' => 20, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'GRADE',        'width' => 20, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'GAUGE',        'width' => 20, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'QTY',          'width' => 18, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'LENGTH',       'width' => 15, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'TYPE',         'width' => 19, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'STYLE',        'width' => 19, 'align' => 'C', 'fontsize' => 9],
];

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
    $pdf->SetTextColor(0, 0, 0);

    $lineHeight = 4;
    $xStart = $pdf->GetX();
    $yStart = $pdf->GetY();

    $maxMainHeight = 0;
    foreach ($columns as $col) {
        $lines = preg_split("/\r\n|\n|\r/", $col['label']);
        $maxMainHeight = max($maxMainHeight, count($lines) * $lineHeight);
    }

    $totalHeight = $maxMainHeight;
    $totalWidth = array_sum(array_column($columns, 'width'));

    $pdf->Rect($xStart, $yStart, $totalWidth, $totalHeight, 'FD');

    $x = $xStart;
    foreach ($columns as $col) {
        $lines = preg_split("/\r\n|\n|\r/", $col['label']);
        $textHeight = count($lines) * $lineHeight;
        $yOffset = ($maxMainHeight - $textHeight) / 2;

        $y = $yStart + $yOffset;
        foreach ($lines as $line) {
            $fontSize = $pdf->fitTextToWidth($line, $col['width'], $col['fontsize'] ?? 9, 'Arial', 'B');
            $pdf->SetFont('Arial', 'B', $fontSize);
            $pdf->SetXY($x, $y);
            $pdf->Cell($col['width'], $lineHeight, $line, 0, 0, $col['align']);
            $y += $lineHeight;
        }
        $x += $col['width'];
    }

    $pdf->SetY($yStart + $totalHeight);
    $pdf->SetFillColor(255, 255, 255);
}

function renderPanelCategory($pdf, $product, $conn) {
    global $columns;

    $id = $product['id'];
    $work_order = getWorkOrderProductDetails($id);
    $coils_raw = $work_order['assigned_coils'];
    $coils = json_decode($coils_raw, true);

    $ids = array_filter(array_map('intval', explode(',', trim($work_order['assigned_coils'], '[]'))));
    $coils_assigned = implode(', ', array_filter(array_map(function($id){
        return getCoilEntry($id);
    }, $ids)));

    $productid = $product['productid'];
    $product_details = getProductDetails($productid);
    $grade_details   = getGradeDetails($product['custom_grade']);
    $gauge_details   = getGaugeDetails($product['custom_gauge']);

    $quantity   = floatval($product['quantity'] ?? 0);
    $act_price  = floatval($product['actual_price'] ?? 0);
    $disc_price = floatval($product['discounted_price'] ?? 0);
    $note       = trim($product['note'] ?? '');

    $panel_type  = !empty($product['panel_type']) ? ucwords(str_replace('_', ' ', $product['panel_type'])) : '';
    $panel_style = !empty($product['panel_style']) ? ucwords(str_replace('_', ' ', $product['panel_style'])) : '';

    $ft = floor(floatval($product['custom_length'] ?? 0));
    $in_decimal = floatval($product['custom_length2'] ?? 0);
    $total_length = $ft + ($in_decimal / 12);

    $ft_only = floor($total_length);
    $inch_only = round(($total_length - $ft_only) * 12);

    $length_display = str_pad($ft_only . 'ft', 6, ' ', STR_PAD_RIGHT)
                . str_pad($inch_only . 'in', 6, ' ', STR_PAD_LEFT);


    $product_abbrev = $product['product_id_abbrev'] ?? '';
    $color = getColorName($product['custom_color']);

    $unit_price = $quantity > 0 ? $disc_price / $quantity : 0;

    $summaryRow = [
        $product_abbrev,
        $product['product_item'],
        $color,
        $grade_details['product_grade'] ?? '',
        $gauge_details['gauge_abbreviations'] ?? '',
        $quantity,
        $length_display,
        $panel_type,
        $panel_style,
        '',
    ];

    $pdf->renderRow($columns, $summaryRow);

    if (!empty($note)) {
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->Cell(0, 4, 'Note: ' . $note, 0, 1, 'L');
        $pdf->SetFont('Arial', '', 7);
    }

    $totalQty    = $quantity;
    $totalPrice  = $disc_price;
    $totalActual = $act_price;

    return [$totalPrice, $totalQty, $totalActual];
}

function renderTrimCategory($pdf, $product, $conn) {
    global $columns;

    $id = $product['id'];
    $work_order = getWorkOrderProductDetails($id);
    $coils_raw = $work_order['assigned_coils'];
    $coils = json_decode($coils_raw, true);

    $ids = array_filter(array_map('intval', explode(',', trim($work_order['assigned_coils'], '[]'))));
    $coils_assigned = implode(', ', array_filter(array_map(function($id){
        return getCoilEntry($id);
    }, $ids)));

    $productid = $product['productid'];
    $product_details = getProductDetails($productid);
    $grade_details   = getGradeDetails($product['custom_grade']);
    $gauge_details   = getGaugeDetails($product['custom_gauge']);

    $quantity   = floatval($product['quantity'] ?? 0);
    $act_price  = floatval($product['actual_price'] ?? 0);
    $disc_price = floatval($product['discounted_price'] ?? 0);
    $note       = trim($product['note'] ?? '');

    $panel_type  = !empty($product['panel_type']) ? ucwords(str_replace('_', ' ', $product['panel_type'])) : '';
    $panel_style = !empty($product['panel_style']) ? ucwords(str_replace('_', ' ', $product['panel_style'])) : '';

    $ft = floor(floatval($product['custom_length'] ?? 0));
    $in_decimal = floatval($product['custom_length2'] ?? 0);
    $total_length = $ft + ($in_decimal / 12);

    $ft_only = floor($total_length);
    $inch_only = round(($total_length - $ft_only) * 12);

    $length_display = str_pad($ft_only . 'ft', 6, ' ', STR_PAD_RIGHT)
                . str_pad($inch_only . 'in', 6, ' ', STR_PAD_LEFT);


    $product_abbrev = $product['product_id_abbrev'] ?? '';
    $color = getColorName($product['custom_color']);

    $unit_price = $quantity > 0 ? $disc_price / $quantity : 0;

    $summaryRow = [
        $product_abbrev,
        $product['product_item'],
        $color,
        $grade_details['product_grade'] ?? '',
        $gauge_details['gauge_abbreviations'] ?? '',
        $quantity,
        $length_display,
        '',
        '',
    ];

    $pdf->renderRow($columns, $summaryRow);

    if (!empty($note)) {
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->Cell(0, 4, 'Note: ' . $note, 0, 1, 'L');
        $pdf->SetFont('Arial', '', 7);
    }

    $totalQty    = $quantity;
    $totalPrice  = $disc_price;
    $totalActual = $act_price;

    return [$totalPrice, $totalQty, $totalActual];
}

function renderScrewCategory($pdf, $product, $conn) {
    global $columns;

    $productid = $product['productid'];
    $product_details = getProductDetails($productid);
    $grade_details   = getGradeDetails($product['custom_grade']);
    $gauge_details   = getGaugeDetails($product['custom_gauge']);

    $quantity   = floatval($product['quantity'] ?? 0);
    $act_price  = floatval($product['actual_price'] ?? 0);
    $disc_price = floatval($product['discounted_price'] ?? 0);
    $note       = trim($product['note'] ?? '');

    $panel_type  = !empty($product['panel_type']) ? ucwords(str_replace('_', ' ', $product['panel_type'])) : '';
    $panel_style = !empty($product['panel_style']) ? ucwords(str_replace('_', ' ', $product['panel_style'])) : '';

    $ft = floor(floatval($product['custom_length'] ?? 0));
    $in_decimal = floatval($product['custom_length2'] ?? 0);
    $total_length = $ft + ($in_decimal / 12);

    $ft_only = floor($total_length);
    $inch_only = round(($total_length - $ft_only) * 12);


    $product_abbrev = $product['product_id_abbrev'] ?? '';
    $color = getColorName($product['custom_color']);

    $unit_price = $quantity > 0 ? $disc_price / $quantity : 0;

    $summaryRow = [
        $product_abbrev,
        $product['product_item'],
        $color,
        '',
        '',
        $quantity,
        '',
        '',
        '',
        '',
        '',
    ];

    $pdf->renderRow($columns, $summaryRow);

    if (!empty($note)) {
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->Cell(0, 4, 'Note: ' . $note, 0, 1, 'L');
        $pdf->SetFont('Arial', '', 7);
    }

    $totalQty    = $quantity;
    $totalPrice  = $disc_price;
    $totalActual = $act_price;

    return [$totalPrice, $totalQty, $totalActual];
}

function renderDefaultCategory($pdf, $product, $conn) {
    global $columns;

    $productid = $product['productid'];
    $product_details = getProductDetails($productid);
    $grade_details   = getGradeDetails($product['custom_grade']);
    $gauge_details   = getGaugeDetails($product['custom_gauge']);

    $quantity   = floatval($product['quantity'] ?? 0);
    $act_price  = floatval($product['actual_price'] ?? 0);
    $disc_price = floatval($product['discounted_price'] ?? 0);
    $note       = trim($product['note'] ?? '');

    $panel_type  = !empty($product['panel_type']) ? ucwords(str_replace('_', ' ', $product['panel_type'])) : '';
    $panel_style = !empty($product['panel_style']) ? ucwords(str_replace('_', ' ', $product['panel_style'])) : '';

    $ft = floor(floatval($product['custom_length'] ?? 0));
    $in_decimal = floatval($product['custom_length2'] ?? 0);
    $total_length = $ft + ($in_decimal / 12);

    $ft_only = floor($total_length);
    $inch_only = round(($total_length - $ft_only) * 12);

    $product_abbrev = $product['product_id_abbrev'] ?? '';
    $color = getColorName($product['custom_color']);

    $unit_price = $quantity > 0 ? $disc_price / $quantity : 0;

    $summaryRow = [
        $product_abbrev,
        $product['product_item'],
        $color,
        '',
        '',
        $quantity,
        '',
        '',
        '',
        '',
        '',
    ];

    $pdf->renderRow($columns, $summaryRow);

    if (!empty($note)) {
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->Cell(0, 4, 'Note: ' . $note, 0, 1, 'L');
        $pdf->SetFont('Arial', '', 7);
    }

    $totalQty    = $quantity;
    $totalPrice  = $disc_price;
    $totalActual = $act_price;

    return [$totalPrice, $totalQty, $totalActual];
}


function renderInvoiceHeader($pdf, $row_orders) {
    $current_user_id = $_SESSION['userid'];
    $delivery_price = floatval($row_orders['delivery_amt']);
    $discount = floatval($row_orders['discount_percent']) / 100;
    $orderid = $row_orders['orderid'];
    $customer_id = $row_orders['customerid'];
    $salesperson = get_staff_name($current_user_id);
    $customerDetails = getCustomerDetails($customer_id);
    $tax = floatval(getCustomerTax($customer_id)) / 100;
    $delivery_method = $delivery_price == 0 ? 'Pickup' : 'Deliver';

    $order_date = '';
    if (!empty($row_orders['order_date']) && $row_orders['order_date'] !== '0000-00-00 00:00:00') {
        $order_date = date("m/d/Y || g:i A", strtotime($row_orders['order_date']));
    }

    $scheduled_date = '';
    if (!empty($row_orders["scheduled_date"]) && $row_orders["scheduled_date"] !== '0000-00-00 00:00:00') {
        $scheduled_date = date("m/d/Y || g:i A", strtotime($row_orders["scheduled_date"]));
    }

    $col1_x = 10;
    $col3_x = 110;

    $pdf->Image('assets/images/logo-bw.png', 10, 6, 60, 20);

    $pdf->SetFont('Arial', 'B', 30);
    $pdf->SetXY($col3_x, 10);
    $pdf->Cell(0, 15, 'DELIVERY TICKET', 0, 1, 'C');
    $pdf->Ln(5);

    $pageWidth   = $pdf->GetPageWidth();
    $marginLeft  = 10;
    $marginRight = 10;
    $usableWidth = $pageWidth - $marginLeft - $marginRight;
    $mailToWidth = $usableWidth / 2;

    $currentY = $pdf->GetY();

    $pdf->SetFillColor(211, 211, 211);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 9);

    $wHalf = ($mailToWidth / 2) + 5;
    $col2_x = $col1_x + $wHalf;

    $pdf->SetXY($col1_x, $currentY);
    $pdf->Cell($wHalf, 7, 'BILL TO:', 1, 0, 'L', true);

    $pdf->SetXY($col2_x, $currentY);
    $pdf->Cell($wHalf, 7, 'SHIP TO:', 1, 1, 'L', true);

    $pdf->SetFont('Arial', '', 9);
    $startY = $pdf->GetY();

    $pdf->SetXY($col1_x, $startY);
    $billStartY = $pdf->GetY();

    $name = get_customer_name($row_orders['customerid']);
    $pdf->MultiCell($wHalf, 5, $name, 1, 'L');

    if (!empty($customerDetails['different_ship_address'])) {
        $address = $customerDetails['ship_address'] ?? '';
        $city    = $customerDetails['ship_city'] ?? '';
        $state   = $customerDetails['ship_state'] ?? '';
        $zip     = $customerDetails['ship_zip'] ?? '';
    } else {
        $address = $customerDetails['address'] ?? '';
        $city    = $customerDetails['city'] ?? '';
        $state   = $customerDetails['state'] ?? '';
        $zip     = $customerDetails['zip'] ?? '';
    }

    $addressParts = [];
    if (!empty($address)) $addressParts[] = $address;
    if (!empty($city))    $addressParts[] = $city;
    if (!empty($state))   $addressParts[] = $state;
    if (!empty($zip))     $addressParts[] = $zip;

    $fullAddress = implode(', ', $addressParts);

    $pdf->SetX($col1_x);
    $pdf->MultiCell($wHalf, 5, $fullAddress, 1, 'L');

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetX($col1_x);
    $pdf->MultiCell($mailToWidth + 5, 5, "Job Name: " . $row_orders['job_name'], 1, 'L');

    $pdf->SetX($col1_x);
    $pdf->MultiCell($mailToWidth + 5, 5, "PO #: " . $row_orders['job_po'], 1, 'L');

    $pdf->SetFont('Arial', '', 9);
    

    $billEnd = $pdf->GetY();

    $pdf->SetXY($col2_x, $startY);
    $shipStartY = $pdf->GetY();

    $shipName = trim($row_orders['deliver_fname'] . ' ' . $row_orders['deliver_lname']);
    $pdf->MultiCell($wHalf-5, 5, $shipName, 1, 'L');

    $shipAddressParts = [];
    if (!empty($row_orders['deliver_address'])) $shipAddressParts[] = $row_orders['deliver_address'];
    if (!empty($row_orders['deliver_city'])) $shipAddressParts[] = $row_orders['deliver_city'];
    if (!empty($row_orders['deliver_state'])) $shipAddressParts[] = $row_orders['deliver_state'];
    if (!empty($row_orders['deliver_zip'])) $shipAddressParts[] = $row_orders['deliver_zip'];
    $shipAddress = implode(', ', $shipAddressParts);

    $pdf->SetX($col2_x);
    $pdf->MultiCell($wHalf-5, 5, $shipAddress, 1, 'L');

    $shipEnd = $pdf->GetY();

    $maxLeftRightHeight = max($billEnd, $shipEnd) - $startY;

    $invoiceX = $col3_x;
    $invoiceW = $mailToWidth - 5;

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY($invoiceX, $currentY);
    $pdf->Cell($invoiceW, 7, 'Invoice #: ' . $orderid, 1, 1, 'L', true);

    $pdf->SetFont('Arial', '', 9);
    $pdf->SetXY($invoiceX, $startY);
    $rightText =
        "Order Date: " . $order_date . "\n" .
        "Pick-up or Delivery: " . $delivery_method . "\n" .
        "Scheduled Date: " . $scheduled_date . "\n" .
        "Salesperson: " . $salesperson;
    $pdf->MultiCell($invoiceW, 5, $rightText, 0, 'L');
    $rightEnd = $pdf->GetY();

    $finalHeight = max($billEnd, $shipEnd, $rightEnd) - $startY;

    $pdf->Rect($col1_x, $startY, $mailToWidth + 5, $finalHeight);
    $pdf->Rect($col3_x, $startY, $mailToWidth - 5, $finalHeight);

    $pdf->SetY($startY + $finalHeight + 3);
}


class PDF extends FPDF {
    public function fitTextToWidth($text, $maxWidth, $initialFontSize = 9, $font = 'Arial', $style = '') {
        $this->SetFont($font, $style, $initialFontSize);
        $width = $this->GetStringWidth($text);
        $fontSize = $initialFontSize;

        while ($width > $maxWidth && $fontSize > 4) {
            $fontSize -= 0.5;
            $this->SetFont($font, $style, $fontSize);
            $width = $this->GetStringWidth($text);
        }

        return $fontSize;
    }

    public function renderRow($columns, $row, $bold = false) {
        $lineHeight = 5;

        $xStart = $this->GetX();
        $yStart = $this->GetY();

        $row = array_slice($row, 0, count($columns));

        $heights = [];
        $cellTexts = [];

        foreach ($columns as $i => $col) {
            $w = $col['width'];
            $fontSize = $col['fontsize'] ?? 9;

            $this->SetFont('Arial', $bold ? 'B' : '', $fontSize);

            if ($i === 6 && strpos($row[$i], 'ft') !== false && strpos($row[$i], 'in') !== false) {
                preg_match('/(\d+)ft\s*(\d+)in/', $row[$i], $m);
                $cellTexts[$i] = $m ? $m[1] . "ft\n" . $m[2] . "in" : $row[$i];
            } elseif ($i === 0) {
                $fit = $this->fitTextToWidth($row[$i], $w, $fontSize, 'Arial', $bold ? 'B' : '');
                $this->SetFont('Arial', $bold ? 'B' : '', $fit);
                $cellTexts[$i] = $row[$i];
            } else {
                $cellTexts[$i] = $row[$i];
            }

            $heights[$i] = $this->GetMultiCellHeight($w, $lineHeight, $cellTexts[$i]);
        }

        $rowHeight = max($heights);

        // Page break check
        $bottom = $this->h - $this->bMargin;
        if ($yStart + $rowHeight > $bottom) {
            $this->AddPage();
            $xStart = $this->GetX();
            $yStart = $this->GetY();
        }

        $x = $xStart;

        // Draw the outer border for the row
        $totalWidth = array_sum(array_column($columns, 'width'));
        $this->Rect($xStart, $yStart, $totalWidth, $rowHeight);

        // Print each cell's content
        foreach ($columns as $i => $col) {
            $w = $col['width'];
            $fontSize = $col['fontsize'] ?? 9;

            $this->SetFont('Arial', $bold ? 'B' : '', $fontSize);

            $saveX = $x;
            $saveY = $yStart;
            $this->SetXY($saveX, $saveY);

            if ($i === 6 && strpos($cellTexts[$i], "\n") !== false) {
                list($ft, $inch) = explode("\n", $cellTexts[$i]);
                $this->Cell($w, $lineHeight, $ft, 0, 0, 'L');
                $this->SetXY($saveX, $saveY);
                $this->Cell($w, $lineHeight, $inch, 0, 0, 'R');
            } else {
                $this->MultiCell($w, $lineHeight, $cellTexts[$i], 0, $col['align']);
            }

            // Move to the right for next column
            $x += $w;
            $this->SetXY($x, $saveY);
        }

        // Move cursor to next row
        $this->SetXY($xStart, $yStart + $rowHeight);
    }

    public function getBottomLimit() {
        return $this->h - $this->bMargin;
    }

    public function getStringHeight($w, $txt, $lineHeight){
        $cw = &$this->CurrentFont['cw'];
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);

        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;

        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $nl++;
                $sep = -1;
                $i++;
                $l = 0;
                $j = $i;
                continue;
            }
            if ($c == ' ')
                $sep = $i;

            $l += $cw[$c];

            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else {
                    $i = $sep + 1;
                }
                $nl++;
                $sep = -1;
                $l = 0;
                $j = $i;
            } else {
                $i++;
            }
        }
        return $nl * $lineHeight;
    }

    public function GetMultiCellHeight($w, $h, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', (string)$txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n") $nb--;
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
            $l += $cw[$c] ?? 0;
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
        return $nl * $h;
    }

    public function NbLines($w, $txt) {
        $txt = str_replace("\r", '', (string)$txt);
        $nb = strlen($txt);
        if ($nb > 0 && $txt[$nb - 1] == "\n") $nb--;
        $lines = 1;
        $lineWidth = 0;
        $maxWidth = $w;
        $spaceWidth = $this->GetStringWidth(' ');

        for ($i = 0; $i < $nb; $i++) {
            $c = $txt[$i];
            if ($c == "\n") {
                $lines++;
                $lineWidth = 0;
                continue;
            }

            $charWidth = $this->GetStringWidth($c);
            if ($lineWidth + $charWidth > $maxWidth) {
                $lines++;
                $lineWidth = $charWidth;
            } else {
                $lineWidth += $charWidth;
            }
        }
        return $lines;
    }
}

$pdf = new PDF();
$pdf->SetAutoPageBreak(true, 40);

$query = "SELECT * FROM orders WHERE orderid = '$orderid'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $row_orders = mysqli_fetch_assoc($result);

    $query_product = "
        SELECT p.product_category, op.* 
        FROM order_product AS op
        LEFT JOIN product AS p ON p.product_id = op.productid
        WHERE orderid = '$orderid'
        ORDER BY op.bundle_id, p.product_category
    ";
    $result_product = mysqli_query($conn, $query_product);

    $total_price  = 0;
    $total_qty    = 0;
    $total_actual = 0;
    $total_saved  = 0;

    $pdf->AddPage();

    $bundledProducts    = [];
    $nonBundledProducts = [];

    while ($row_product = mysqli_fetch_assoc($result_product)) {
        if (!empty($pricing_id) && $pricing_id == 1) {
            $tmp = $row_product['discounted_price'];
            $row_product['discounted_price'] = $row_product['actual_price'];
            $row_product['actual_price'] = $tmp;
        }

        $bundleId = $row_product['bundle_id'] ?? null;
        if (!empty($bundleId)) {
            $bundledProducts[$bundleId][] = $row_product;
        } else {
            $nonBundledProducts[] = $row_product;
        }
    }

    renderInvoiceHeader($pdf, $row_orders);
    renderTableHeader($pdf, $columns);

    foreach ($bundledProducts as $bundleId => $bundleGroup) {
        $bundleName = $bundleGroup[0]['bundle_name'] ?? $bundleId;
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 6, $bundleName, 1, 1, 'L');
        $pdf->SetFont('Arial', '', 9);

        foreach ($bundleGroup as $row_product) {
            $categoryId = $row_product['product_category'];

            if ($categoryId == $panel_id) {
                [$catTotal, $catQty, $catActual] = renderPanelCategory($pdf, $row_product, $conn);
            } elseif ($categoryId == $trim_id) {
                [$catTotal, $catQty, $catActual] = renderTrimCategory($pdf, $row_product, $conn);
            } elseif ($categoryId == $screw_id) {
                [$catTotal, $catQty, $catActual] = renderScrewCategory($pdf, $row_product, $conn);
            } else {
                [$catTotal, $catQty, $catActual] = renderDefaultCategory($pdf, $row_product, $conn);
            }

            $catSaved = floatval($catActual) - floatval($catTotal);
            $total_price  += floatval($catTotal);
            $total_qty    += intval($catQty);
            $total_actual += floatval($catActual);
            $total_saved  += $catSaved;
        }

        $pdf->Ln(5);
    }

    foreach ($nonBundledProducts as $row_product) {
        $categoryId = $row_product['product_category'];

        if ($categoryId == $panel_id) {
            [$catTotal, $catQty, $catActual] = renderPanelCategory($pdf, $row_product, $conn);
        } elseif ($categoryId == $trim_id) {
            [$catTotal, $catQty, $catActual] = renderTrimCategory($pdf, $row_product, $conn);
        } elseif ($categoryId == $screw_id) {
            [$catTotal, $catQty, $catActual] = renderScrewCategory($pdf, $row_product, $conn);
        } else {
            [$catTotal, $catQty, $catActual] = renderDefaultCategory($pdf, $row_product, $conn);
        }

        $catSaved = floatval($catActual) - floatval($catTotal);
        $total_price  += floatval($catTotal);
        $total_qty    += intval($catQty);
        $total_actual += floatval($catActual);
        $total_saved  += $catSaved;
    }

    $pdf->Ln(5);

    $box_width = 70;
    $box_height = 30;

    $box1_x = $col1_x;
    $box2_x = $col1_x + $box_width;
    $col_y = $pdf->GetY();
    $box_y = $col_y;

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetXY($box1_x, $box_y);
    $pdf->Cell($box_width, 6, 'Delivered By:', 1, 1, 'C');

    $pdf->Rect($box1_x, $box_y + 6, $box_width, $box_height);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetXY($box2_x, $box_y);
    $pdf->Cell($box_width, 6, 'Digital Delivery Ticket', 1, 1, 'C');

    $pdf->Rect($box2_x, $box_y + 6, $box_width, $box_height);

    $query_qr = "SELECT * FROM order_estimate WHERE order_estimate_id = '$orderid'";
    $result_qr = mysqli_query($conn, $query_qr);

    if (mysqli_num_rows($result_qr) > 0) {
        $row_qr = mysqli_fetch_assoc($result_qr);

        $imageUrl = 'https://delivery.eastkentuckymetal.com/deliveryqr/qrcode' . $row_qr['id'] . '.png';

        $headers = @get_headers($imageUrl);

        if ($headers && strpos($headers[0], '200') !== false) {

            $qr_max_height = $box_height - 1;
            $qr_width = $qr_max_height;

            $qr_x = $box2_x + ($box_width - $qr_width) / 2;
            $qr_y = $box_y + 6 + (($box_height - $qr_max_height) / 2);

            $pdf->Image($imageUrl, $qr_x, $qr_y, $qr_width, $qr_max_height);

        } else {
            $pdf->Cell($box_width, 6, 'QR image not found', 1, 1, 'C');
        }
    }


    $pdf->SetTitle('Receipt');
    $pdf->Output('Receipt.pdf', 'I');

} else {
    echo "ID not Found!";
}


?>
