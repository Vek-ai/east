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

$columns = [
    ['label' => 'PRODUCT ID',  'width' => 26, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'DESCRIPTION', 'width' => 27, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'COLOR',       'width' => 21, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'GRADE',       'width' => 18, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'GAUGE',       'width' => 18, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'QTY',         'width' => 19, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'LENGTH',      'width' => 16, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'TYPE',        'width' => 18, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'STYLE',       'width' => 18, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'COIL #',      'width' => 19, 'align' => 'C', 'fontsize' => 9],
];

$trim_columns = [
    ['label' => 'PRODUCT ID',  'width' => 26, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'DESCRIPTION', 'width' => 27, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'COLOR',       'width' => 21, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'GRADE',       'width' => 18, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'GAUGE',       'width' => 18, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'QTY',         'width' => 19, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'LENGTH',      'width' => 16, 'align' => 'C', 'fontsize' => 9],
    ['label' => '',            'width' => 18, 'align' => 'C', 'fontsize' => 9],
    ['label' => '',            'width' => 18, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'COIL #',      'width' => 19, 'align' => 'C', 'fontsize' => 9],
];

function buildTotalsByCustom(array $products) {
    global $conn;
    $totals_by_custom = [];

    foreach ($products as $row) {
        $custom_profile = (int)$row['custom_profile'];
        $custom_grade   = (int)$row['custom_grade'];
        $custom_gauge   = (int)$row['custom_gauge'];
        $custom_color   = (int)$row['custom_color'];

        $length   = (float)$row['custom_length'];
        $quantity = (float)$row['quantity'];

        if ($length <= 0 || $quantity <= 0) {
            continue;
        }

        $group_key = $custom_profile . '|' . $custom_grade . '|' . $custom_gauge . '|' . $custom_color;

        if (!isset($totals_by_custom[$group_key])) {

            $nameParts = [];

            $profileAbbr = $custom_profile ? getAbbr('profile_type', 'profile_type', $custom_profile) : null;
            $gradeAbbr   = $custom_grade   ? getAbbr('product_grade', 'product_grade', $custom_grade) : null;
            $gaugeAbbr   = $custom_gauge   ? getAbbr('product_gauge', 'product_gauge', $custom_gauge) : null;
            $colorAbbr   = $custom_color   ? getAbbr('paint_colors', 'color_name', $custom_color) : null;

            if ($profileAbbr) $nameParts[] = $profileAbbr;
            if ($gradeAbbr)   $nameParts[] = $gradeAbbr;
            if ($gaugeAbbr)   $nameParts[] = $gaugeAbbr;
            if ($colorAbbr)   $nameParts[] = $colorAbbr;

            $totals_by_custom[$group_key] = [
                'profile'      => $custom_profile,
                'grade'        => $custom_grade,
                'gauge'        => $custom_gauge,
                'color'        => $custom_color,
                'name'         => implode(' - ', $nameParts),
                'total_length' => 0
            ];
        }

        $totals_by_custom[$group_key]['total_length'] += ($length * $quantity);
    }

    return $totals_by_custom;
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
    $inch_raw   = ($total_length - $ft_only) * 12;
    $inch_round = round($inch_raw, 2);
    $inch_disp  = rtrim(rtrim(number_format($inch_round, 2, '.', ''), '0'), '.');

    if ($inch_round >= 12) {
        $ft_only++;
        $inch_disp = '0';
    }

    $length_display =
        str_pad($ft_only . 'ft', 6, ' ', STR_PAD_RIGHT) .
        str_pad($inch_disp . 'in', 6, ' ', STR_PAD_LEFT);


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
        $coils_assigned,
    ];

    renderRow($pdf, $columns, $summaryRow, false);

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
    $inch_raw   = ($total_length - $ft_only) * 12;
    $inch_round = round($inch_raw, 2);
    $inch_disp  = rtrim(rtrim(number_format($inch_round, 2, '.', ''), '0'), '.');

    if ($inch_round >= 12) {
        $ft_only++;
        $inch_disp = '0';
    }

    $length_display =
        str_pad($ft_only . 'ft', 6, ' ', STR_PAD_RIGHT) .
        str_pad($inch_disp . 'in', 6, ' ', STR_PAD_LEFT);


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
        $coils_assigned,
    ];

    renderRow($pdf, $columns, $summaryRow, false);

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

function renderRow($pdf, $columns, $row, $bold = false) {
    $lineHeight = 5;
    $xStart = $pdf->GetX();
    $yStart = $pdf->GetY();
    $x = $xStart;

    $row = array_slice($row, 0, count($columns));
    $columnHeights = [];

    foreach ($columns as $i => $col) {
        $w = $col['width'];
        $fontSize = $col['fontsize'] ?? 9;

        $cellBold = $bold;
        $cellItalic = false;
        $cellUnderline = false;

        if ($i === 4) {
            if ($row[$i] === '26ga') $cellBold = true;
            elseif ($row[$i] === '24ga') { $cellBold = true; $cellItalic = true; $cellUnderline = true; }
        } elseif ($i === 7 && stripos($row[$i], 'Vented') !== false) {
            $cellBold = true;
        } elseif ($i === 8) {
            if (stripos($row[$i], 'Reversed') !== false) { $cellBold = true; $cellUnderline = true; }
            elseif (stripos($row[$i], 'Minor Rib') !== false) $cellBold = true;
            elseif (stripos($row[$i], 'Pencil Ribs') !== false) $cellUnderline = true;
        }

        $style = '';
        if ($cellBold) $style .= 'B';
        if ($cellItalic) $style .= 'I';
        if ($cellUnderline) $style .= 'U';

        $pdf->SetFont('Arial', $style, $fontSize);
        $pdf->SetXY($x, $yStart);

        if ($i === 1) {
            $startY = $pdf->GetY();
            $pdf->MultiCell($w, $lineHeight, $row[$i], 0, $col['align']);
            $endY = $pdf->GetY();
            $columnHeights[$i] = $endY - $startY;
        } elseif ($i === 6 && strpos($row[$i], 'ft') !== false && strpos($row[$i], 'in') !== false) {
            preg_match('/(\d+)ft\s*(\d+)in/', $row[$i], $matches);
            if ($matches) {
                $ft = $matches[1] . 'ft';
                $in = $matches[2] . 'in';
                $pdf->Cell($w, $lineHeight, $ft, 0, 0, 'L');
                $pdf->SetXY($x, $yStart);
                $pdf->Cell($w, $lineHeight, $in, 0, 0, 'R');
            } else {
                $pdf->Cell($w, $lineHeight, $row[$i], 0, 0, $col['align']);
            }
            $columnHeights[$i] = $lineHeight;
        } else {
            $fittedSize = $pdf->fitTextToWidth($row[$i], $w, $fontSize, 'Arial', $style);
            $pdf->SetFont('Arial', $style, $fittedSize);
            $pdf->Cell($w, $lineHeight, $row[$i], 0, 0, $col['align']);
            $columnHeights[$i] = $lineHeight;
        }

        $x += $w;
    }

    $maxHeight = max($columnHeights);
    $totalWidth = array_sum(array_column($columns, 'width'));
    $pdf->Rect($xStart, $yStart, $totalWidth, $maxHeight);
    $pdf->SetXY($xStart, $yStart + $maxHeight);
}

function renderInvoiceHeader($pdf, $row_orders, $type, array $totals_by_custom = []) {
    $salesperson = $row_orders["cashier"];

    $delivery_price = floatval($row_orders['delivery_amt']);
    $discount = floatval($row_orders['discount_percent']) / 100;
    $orderid = $row_orders['orderid'];
    $customer_id = $row_orders['customerid'];
    $salesperson = get_staff_name($salesperson);
    $customerDetails = getCustomerDetails($customer_id);
    $tax = floatval(getCustomerTax($customer_id)) / 100;
    $delivery_method = 'Deliver';
    $order_date = '';
    if (!empty($row_orders['order_date']) && $row_orders['order_date'] !== '0000-00-00 00:00:00') {
        $order_date = date("(l) - m/d/Y || g:i A", strtotime($row_orders['order_date']));
    }

    $scheduled_date = '';
    if (!empty($row_orders["scheduled_date"]) && $row_orders["scheduled_date"] !== '0000-00-00 00:00:00') {
        $scheduled_date = date("(l) - m/d/Y || g:i A", strtotime($row_orders["scheduled_date"]));
    }

    if($delivery_price == 0){
        $delivery_method = 'Pickup';
    }
    $col1_x = 5;
    $col3_x = 110;

    $pdf->Image('assets/images/logo-bw.png', 5, 6, 60, 20);

    $title = '';
    if($type == 'panel'){
        $title = 'METAL COPY';
    }else if($type == 'trim'){
        $title = 'TRIM SHOP';
    }

    $pdf->SetFont('Arial', 'B', 32);
    $pdf->SetXY($col3_x, 10);
    $pdf->Cell(0, 15, $title, 0, 1, 'C');
    $pdf->Ln(5);

    $pageWidth   = $pdf->GetPageWidth();
    $marginLeft  = 5;
    $marginRight = 5;
    $usableWidth = $pageWidth - $marginLeft - $marginRight;
    $mailToWidth = $usableWidth / 2;

    $currentY = $pdf->GetY();

    $pdf->SetFillColor(211, 211, 211);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 9);

    $pdf->SetXY($col1_x, $currentY);
    $pdf->Cell($mailToWidth + 10, 7, 'BILL TO:', 1, 0, 'L', true);

    $pdf->Ln(7);
    $pdf->SetFont('Arial', '', 9);
    $startY = $pdf->GetY();

    $leftText = get_customer_name($row_orders['customerid']) . "\n";

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

    if (!empty($addressParts)) {
        $leftText .= implode(', ', $addressParts) . "\n";
    }

    $leftText .= 'Customer PO #: ' . $row_orders['job_po'] . "\n";
    $leftText .= 'Job Name: ' . $row_orders['job_name'] . "\n";

    $pdf->SetXY($col1_x, $startY);
    $pdf->MultiCell($mailToWidth + 5, 5, $leftText, 0, 'L');
    $leftBottom = $pdf->GetY();

    $pdf->SetXY($col3_x, $currentY);
    $pdf->Cell($mailToWidth - 5, 7, 'Invoice #: ' . getInvoiceNumName($orderid), 1, 0, 'L', true);

    $rightText =
        "Order Date: " . $order_date . "\n" .
        "Pick-up or Delivery: " . $delivery_method . "\n" .
        "Scheduled Date: " . $scheduled_date . "\n" .
        "Salesperson: " . $salesperson;

    $pdf->SetXY($col3_x, $startY);
    $pdf->MultiCell($mailToWidth - 5, 5, $rightText, 0, 'L');
    $rightBottom = $pdf->GetY();

    $pdf->SetFont('Arial', 'B', 9);
    $blockHeight = max($leftBottom, $rightBottom) - $startY;

    $pdf->Rect($col1_x, $startY, $mailToWidth + 5, $blockHeight);
    $pdf->Rect($col3_x, $startY, $mailToWidth - 5, $blockHeight);

    $pdf->SetY($startY + $blockHeight + 3);

    if (!empty($totals_by_custom)) {
        $lineH = 6;
        $summaryY = $startY + $blockHeight;

        $pdf->SetXY($col3_x, $summaryY);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(211, 211, 211);
        $pdf->Cell($mailToWidth - 5, 7, 'Linear Footage', 1, 2, 'L', true);

        $pdf->SetFont('Arial', '', 9);

        foreach ($totals_by_custom as $info) {
            if (empty($info['name'])) continue;

            $text = $info['name'] . ': ' .
                    number_format((float)$info['total_length'], 2) . ' ft';

            $pdf->SetX($col3_x);
            $pdf->Cell($mailToWidth - 5, $lineH, $text, 1, 2, 'L');
        }

        $pdf->SetY($summaryY + 7 + (count($totals_by_custom) * $lineH));
    }

    $pdf->ln(5);
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

$orderid = $_REQUEST['id'];
$type = $_REQUEST['type'] ?? '';

$pdf = new PDF();
$pdf->SetLeftMargin(5);
$pdf->SetRightMargin(5);
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
        AND op.product_category IN ('$panel_id','$trim_id')
        ORDER BY p.product_category
    ";
    $result_product = mysqli_query($conn, $query_product);

    $panelProducts = [];
    $trimProducts  = [];

    while ($row_product = mysqli_fetch_assoc($result_product)) {
        if ($row_product['product_category'] == $panel_id) {
            $panelProducts[] = $row_product;
        } elseif ($row_product['product_category'] == $trim_id) {
            $trimProducts[] = $row_product;
        }
    }

    $panelTotals = buildTotalsByCustom($panelProducts, $conn);
    $trimTotals  = buildTotalsByCustom($trimProducts, $conn);

    if ($type === 'panel' || $type === '') {
        if (!empty($panelProducts)) {
            $pdf->AddPage();
            renderInvoiceHeader($pdf, $row_orders, 'panel', $panelTotals);
            renderTableHeader($pdf, $columns);

            $panelBundled    = [];
            $panelNonBundled = [];

            foreach ($panelProducts as $row_product) {
                $bundleId = $row_product['bundle_id'] ?? null;
                if (!empty($bundleId)) {
                    $panelBundled[$bundleId][] = $row_product;
                } else {
                    $panelNonBundled[] = $row_product;
                }
            }

            foreach ($panelBundled as $bundleId => $bundleGroup) {
                $bundleName = $bundleGroup[0]['bundle_id'] ?? 'Bundle ' . $bundleId;
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(0, 6, $bundleName, 1, 1, 'L');
                $pdf->SetFont('Arial', '', 7);

                foreach ($bundleGroup as $row_product) {
                    renderPanelCategory($pdf, $row_product, $conn);
                }
                $pdf->Ln(5);
            }

            foreach ($panelNonBundled as $row_product) {
                renderPanelCategory($pdf, $row_product, $conn);
            }
        }
    }

    if ($type === 'trim' || $type === '') {
        if (!empty($trimProducts)) {
            $pdf->AddPage();
            renderInvoiceHeader($pdf, $row_orders, 'trim');
            renderTableHeader($pdf, $trim_columns);

            $trimBundled    = [];
            $trimNonBundled = [];

            foreach ($trimProducts as $row_product) {
                $bundleId = $row_product['bundle_id'] ?? null;
                if (!empty($bundleId)) {
                    $trimBundled[$bundleId][] = $row_product;
                } else {
                    $trimNonBundled[] = $row_product;
                }
            }

            foreach ($trimBundled as $bundleId => $bundleGroup) {
                $bundleName = $bundleGroup[0]['bundle_id'] ?? 'Bundle ' . $bundleId;
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(0, 6, $bundleName, 1, 1, 'L');
                $pdf->SetFont('Arial', '', 7);

                foreach ($bundleGroup as $row_product) {
                    renderTrimCategory($pdf, $row_product, $conn);
                }
                $pdf->Ln(5);
            }

            foreach ($trimNonBundled as $row_product) {
                renderTrimCategory($pdf, $row_product, $conn);
            }
        }
    }

    $pdf->SetTitle('Work Order');
    $pdf->Output('Work Order.pdf', 'I');

} else {
    echo "ID not Found!";
}


?>
