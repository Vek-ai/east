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
    ['label' => 'PRODUCT ID', 'width' => 25, 'align' => 'C', 'fontsize' => 8],
    ['label' => 'DESCRIPTION',  'width' => 26, 'align' => 'C', 'fontsize' => 8],
    ['label' => 'COLOR',        'width' => 20, 'align' => 'C', 'fontsize' => 8],
    ['label' => 'GRADE',        'width' => 17, 'align' => 'C', 'fontsize' => 8],
    ['label' => 'GAUGE',        'width' => 17, 'align' => 'C', 'fontsize' => 8],
    ['label' => 'QTY',          'width' => 18, 'align' => 'C', 'fontsize' => 8],
    ['label' => 'LENGTH',       'width' => 15, 'align' => 'C', 'fontsize' => 8],
    ['label' => 'TYPE',         'width' => 17, 'align' => 'C', 'fontsize' => 8],
    ['label' => 'STYLE',        'width' => 17, 'align' => 'C', 'fontsize' => 8],
    ['label' => 'COIL #',        'width' => 18, 'align' => 'C', 'fontsize' => 8],
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
            $fontSize = $pdf->fitTextToWidth($line, $col['width'], $col['fontsize'] ?? 8, 'Arial', 'B');
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
        $fontSize = $col['fontsize'] ?? 8;
        $pdf->SetFont('Arial', $bold ? 'B' : '', $fontSize);
        $pdf->SetXY($x, $yStart);

        if ($i === 1) {
            $startY = $pdf->GetY();
            $pdf->MultiCell($w, $lineHeight, $row[$i], 0, $col['align']);
            $endY = $pdf->GetY();
            $columnHeights[$i] = $endY - $startY;
        }
        elseif ($i === 6 && strpos($row[$i], 'ft') !== false && strpos($row[$i], 'in') !== false) {
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
        }
        else {
            $fittedSize = $pdf->fitTextToWidth($row[$i], $w, $fontSize, 'Arial', $bold ? 'B' : '');
            $pdf->SetFont('Arial', $bold ? 'B' : '', $fittedSize);
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

function renderInvoiceHeader($pdf, $row_orders) {
    $current_user_id = $_SESSION['userid'];

    $delivery_price = floatval($row_orders['delivery_amt']);
    $discount = floatval($row_orders['discount_percent']) / 100;
    $orderid = $row_orders['orderid'];
    $customer_id = $row_orders['customerid'];
    $salesperson = get_staff_name($current_user_id);
    $customerDetails = getCustomerDetails($customer_id);
    $tax = floatval(getCustomerTax($customer_id)) / 100;
    $delivery_method = 'Deliver';
    $order_date = '';
    if (!empty($row_orders['order_date']) && $row_orders['order_date'] !== '0000-00-00 00:00:00') {
        $order_date = date("m/d/Y || g:i A", strtotime($row_orders['order_date']));
    }

    $scheduled_date = '';
    if (!empty($row_orders["scheduled_date"]) && $row_orders["delivered_date"] !== '0000-00-00 00:00:00') {
        $scheduled_date = date("m/d/Y || g:i A", strtotime($row_orders["scheduled_date"]));
    }
    if($delivery_price == 0){
        $delivery_method = 'Pickup';
    }
    $col1_x = 10;
    $col3_x = 110;

    $pdf->Image('assets/images/logo-bw.png', 10, 6, 60, 20);

    $pdf->SetFont('Arial', 'B', 32);
    $pdf->SetXY($col3_x, 10);
    $pdf->Cell(0, 15, 'METAL COPY', 0, 1, 'L');
    $pdf->Ln(5);

    $pageWidth   = $pdf->GetPageWidth();
    $marginLeft  = 10;
    $marginRight = 10;
    $usableWidth = $pageWidth - $marginLeft - $marginRight;
    $mailToWidth = $usableWidth / 2;

    $currentY = $pdf->GetY();

    $pdf->SetFillColor(211, 211, 211);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 8);

    $pdf->SetXY($col1_x, $currentY);
    $pdf->Cell($mailToWidth + 10, 7, 'BILL TO:', 1, 0, 'L', true);

    $pdf->Ln(7);
    $pdf->SetFont('Arial', '', 9);
    $startY = $pdf->GetY();

    $leftText = get_customer_name($row_orders['customerid']) . "\n";

    $addressParts = [];
    if (!empty($customerDetails['address'])) $addressParts[] = $customerDetails['address'];
    if (!empty($customerDetails['city']))    $addressParts[] = $customerDetails['city'];
    if (!empty($customerDetails['state']))   $addressParts[] = $customerDetails['state'];
    if (!empty($customerDetails['zip']))     $addressParts[] = $customerDetails['zip'];

    if (!empty($addressParts)) {
        $leftText .= implode(', ', $addressParts) . "\n";
    }

    $leftText .= 'Customer PO #: ' . $row_orders['job_po'] . "\n";
    $leftText .= 'Job Name: ' . $row_orders['job_name'] . "\n";

    $pdf->SetXY($col1_x, $startY);
    $pdf->MultiCell($mailToWidth + 5, 5, $leftText, 0, 'L');
    $leftBottom = $pdf->GetY();

    $pdf->SetXY($col3_x, $currentY);
    $pdf->Cell($mailToWidth - 5, 7, 'Invoice #: ' . $orderid, 1, 0, 'L', true);

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
}

class PDF extends FPDF {
    public function fitTextToWidth($text, $maxWidth, $initialFontSize = 8, $font = 'Arial', $style = '') {
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

$pdf = new PDF();
$pdf->SetAutoPageBreak(true, 40);

$query = "SELECT * FROM orders WHERE orderid = '$orderid'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while($row_orders = mysqli_fetch_assoc($result)){
        $pdf->AddPage();

        renderInvoiceHeader($pdf, $row_orders);

        $total_price = 0;
        $total_qty = 0;

        $query_product = "
            SELECT p.product_category, op.* 
            FROM order_product AS op
            LEFT JOIN product AS p ON p.product_id = op.productid
            WHERE orderid = '$orderid'
            AND op.product_category IN ('$panel_id','$trim_id')
            ORDER BY p.product_category
        ";
        $result_product = mysqli_query($conn, $query_product);

        $total_price  = 0;
        $total_qty    = 0;
        $total_actual = 0;
        $total_saved  = 0;

        $bundledProducts = [];
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

        renderTableHeader($pdf, $columns);

        foreach ($bundledProducts as $bundleId => $bundleGroup) {
            $bundleName = $bundleGroup[0]['bundle_name'] ?? 'Bundle ' . $bundleId;
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(0, 6, $bundleName, 1, 1, 'L');
            $pdf->SetFont('Arial', '', 7);

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

        $pdf->SetTitle('Receipt');
        $pdf->Output('Receipt.pdf', 'I');
            

        
    }
}else{
    echo "ID not Found!";
}

?>
