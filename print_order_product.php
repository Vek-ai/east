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

$columns = [
    ['label' => 'PRODUCT ID', 'width' => 23, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'DESCRIPTION',  'width' => 23, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'COLOR',        'width' => 20, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'GRADE',        'width' => 17, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'GAUGE',        'width' => 12, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'QTY',          'width' => 12, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'LENGTH',       'width' => 12, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'TYPE',         'width' => 15, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'STYLE',        'width' => 15, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'PRICE',        'width' => 20, 'align' => 'R', 'fontsize' => 9],
    ['label' => 'TOTAL',        'width' => 21, 'align' => 'R', 'fontsize' => 9],
];
$subcolumns = [
    ['label' => '', 'width' => 23, 'align' => 'C', 'fontsize' => 9],
    ['label' => '',  'width' => 23, 'align' => 'C', 'fontsize' => 9],
    ['label' => '',        'width' => 20, 'align' => 'C', 'fontsize' => 9],
    ['label' => '',        'width' => 17, 'align' => 'C', 'fontsize' => 9],
    ['label' => '',        'width' => 12, 'align' => 'C', 'fontsize' => 9],
    ['label' => '',          'width' => 12, 'align' => 'C', 'fontsize' => 9],
    ['label' => 'Ft     In',       'width' => 12, 'align' => 'C', 'fontsize' => 9],
    ['label' => '',         'width' => 12, 'align' => 'C', 'fontsize' => 9],
    ['label' => '',        'width' => 14, 'align' => 'C', 'fontsize' => 9],
    ['label' => '',        'width' => 23, 'align' => 'R', 'fontsize' => 9],
    ['label' => '',        'width' => 24, 'align' => 'R', 'fontsize' => 9],
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
    $totalWidth = array_sum(array_column($columns, 'width'));
    $pdf->Rect($xStart, $yStart, $totalWidth, $maxMainHeight, 'FD');
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

    $pdf->SetY($yStart + $maxMainHeight);
    $pdf->SetFillColor(255, 255, 255);
}

function renderPanelCategory($pdf, $product, $conn) {
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

    $length_display = str_pad($ft_only . 'ft', 6, ' ', STR_PAD_RIGHT)
                . str_pad($inch_only . 'in', 6, ' ', STR_PAD_LEFT);


    $product_abbrev = $product['product_id_abbrev'] ?? '';
    $color = getColorName($product['custom_color']);

    //$unit_price = $quantity > 0 ? $disc_price / $quantity : 0;
    $unit_price = getProductPrice($productid);

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
        '$ ' . number_format($unit_price, 2),
        '$ ' . number_format($disc_price, 2),
    ];

    $pdf->renderRow($columns, $summaryRow, false, $note);

    $totalQty    = $quantity;
    $totalPrice  = $disc_price;
    $totalActual = $act_price;

    return [$totalPrice, $totalQty, $totalActual];
}

function renderTrimCategory($pdf, $product, $conn) {
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

    $length_display = str_pad($ft_only . 'ft', 6, ' ', STR_PAD_RIGHT)
                . str_pad($inch_only . 'in', 6, ' ', STR_PAD_LEFT);


    $product_abbrev = $product['product_id_abbrev'] ?? '';
    $color = getColorName($product['custom_color']);

    //$unit_price = $quantity > 0 ? $disc_price / $quantity : 0;
    $unit_price = getProductPrice($productid);

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
        '$ ' . number_format($unit_price, 2),
        '$ ' . number_format($disc_price, 2),
    ];

    $pdf->renderRow($columns, $summaryRow, false, $note);

    

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
    $screw_type_details   = getProductScrewTypeDetails($product_details['screw_type']);
    $screw_coating_details   = getProductScrewCoatingDetails($product_details['screw_coating']);

    $quantity   = floatval($product['quantity'] ?? 0);
    $act_price  = floatval($product['actual_price'] ?? 0);
    $disc_price = floatval($product['discounted_price'] ?? 0);
    $note       = trim($product['note'] ?? '');

    $panel_type  = !empty($product['panel_type']) ? ucwords(str_replace('_', ' ', $product['panel_type'])) : '';
    $panel_style = !empty($product['panel_style']) ? ucwords(str_replace('_', ' ', $product['panel_style'])) : '';

    $screw_length = $product['screw_length'] ?? '';
    $screw_type = $screw_type_details['product_screw_type'] ?? '';
    $screw_coating = $screw_coating_details['product_screw_coating'] ?? '';

    $length_display = str_pad($screw_length, 12, ' ', STR_PAD_LEFT);

    $product_abbrev = $product['product_id_abbrev'] ?? '';
    $color = getColorName($product['custom_color']);

    $dimension_name = $product['screw_length'] ?? '';

    //$unit_price = $quantity > 0 ? $disc_price / $quantity : 0;
    $dimension_id = getScrewDimensionID($dimension_name);
    $unit_price = getScrewPrice($productid, $dimension_id);

    $productItem = $product['product_item'];
    $pack = '';

    if (preg_match_all('/\(([^()]*)\)/', $productItem, $matches)) {
        $allMatches = $matches[1];
        $pack = end($allMatches);
    }

    $quantity .= "\n $pack";

    $summaryRow = [
        $product_abbrev,
        $product['product_item'],
        $color,
        $screw_coating,
        $screw_type,
        $quantity,
        $length_display,
        '',
        '',
        '$ ' . number_format($unit_price, 3),
        '$ ' . number_format($disc_price, 3),
    ];

    $pdf->renderRow($columns, $summaryRow, false, $note);

    

    $totalQty    = $quantity;
    $totalPrice  = $disc_price;
    $totalActual = $act_price;

    return [$totalPrice, $totalQty, $totalActual];
}

function renderLumberCategory($pdf, $product, $conn) {
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

    $unit_price = getProductPrice($productid);

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
        '$ ' . number_format($unit_price, 2),
        '$ ' . number_format($disc_price, 2),
    ];

    $pdf->renderRow($columns, $summaryRow, false, $note);

    

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

    $unit_price = getProductPrice($productid);

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
        '$ ' . number_format($unit_price, 2),
        '$ ' . number_format($disc_price, 2),
    ];

    $pdf->renderRow($columns, $summaryRow, false, $note);

    $totalQty    = $quantity;
    $totalPrice  = $disc_price;
    $totalActual = $act_price;

    return [$totalPrice, $totalQty, $totalActual];
}

function renderServiceChargeCategory($pdf, $product, $conn) {
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

    $unit_price = round($act_price/$quantity,2);

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
        '$ ' . number_format($unit_price, 2),
        '$ ' . number_format($disc_price, 2),
    ];

    $pdf->renderRow($columns, $summaryRow, false, $note);

    $totalQty    = $quantity;
    $totalPrice  = $disc_price;
    $totalActual = $act_price;

    return [$totalPrice, $totalQty, $totalActual];
}



class PDF extends FPDF {
    public $orderid;
    public $order_date;
    public $delivery_method;
    public $scheduled_date;
    public $salesperson;
    public $token;

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

    public function renderRow($columns, $row, $bold = false, $note = '') {
        $lineHeight = 5;
        $xStart = $this->GetX();
        $yStart = $this->GetY();

        $row = array_slice($row, 0, count($columns));

        $heights = [];
        $cellTexts = [];
        $cellStyles = [];

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
            $cellStyles[$i] = $style;

            if ($i === 6 && strpos($row[$i], 'ft') !== false && strpos($row[$i], 'in') !== false) {
                preg_match('/(\d+)ft\s*(\d+)in/', $row[$i], $m);
                $cellTexts[$i] = $m ? $m[1] . "ft\n" . $m[2] . "in" : $row[$i];
            } else {
                $cellTexts[$i] = $row[$i];
            }

            $this->SetFont('Arial', $style, $fontSize);
            $heights[$i] = $this->GetMultiCellHeight($w, $lineHeight, $cellTexts[$i]);
        }

        $noteHeight = 0;
        if (!empty($note)) {
            $totalWidth = array_sum(array_column($columns, 'width'));
            $this->SetFont('Arial', '', 9);
            $noteHeight = $this->GetMultiCellHeight($totalWidth, $lineHeight, $note);
        }

        $rowHeight = max($heights) + $noteHeight;

        if ($yStart + $rowHeight > $this->h - $this->bMargin) {
            $this->AddPage();
            $xStart = $this->GetX();
            $yStart = $this->GetY();
        }

        $x = $xStart;
        $totalWidth = array_sum(array_column($columns, 'width'));

        $this->Rect($xStart, $yStart, $totalWidth, $rowHeight);

        foreach ($columns as $i => $col) {
            $w = $col['width'];
            $fontSize = $col['fontsize'] ?? 9;

            $this->SetFont('Arial', $cellStyles[$i], $fontSize);

            $saveX = $x;
            $saveY = $yStart;
            $this->SetXY($saveX, $saveY);

            if ($i === 6) {
                $this->Cell($w, $lineHeight, str_replace(["\r","\n"], ' ', $cellTexts[$i]), 0, 0, $col['align']);
            } else {
                $this->MultiCell($w, $lineHeight, $cellTexts[$i], 0, $col['align']);
            }

            $x += $w;
            $this->SetXY($x, $saveY);
        }

        if (!empty($note)) {
            $this->SetXY($xStart, $yStart + max($heights));
            $this->SetFont('Arial', '', 9);
            $this->MultiCell($totalWidth, $lineHeight, 'Note: ' .$note, 0, 'L');
        }

        $this->SetXY($xStart, $yStart + $rowHeight);
    }

    function Header() {
        $this->SetFont('Arial', '', 9);
        $this->Image('assets/images/logo-bw.png', 10, 6, 60, 20);
        $yStart = $this->GetY();

        $col2_x = 100;
        $w = 80;
        $lineH = 5;

        $blockText  = "Invoice #: " . getInvoiceNumName($this->orderid) . "\n";
        $blockText .= "Order Date: " . $this->order_date . "\n";
        $blockText .= "Pick-up or Delivery: " . $this->delivery_method . "\n";
        $blockText .= "Scheduled Date: " . $this->scheduled_date . "\n";
        $blockText .= "Salesperson: " . $this->salesperson;

        $maxHeight = $this->NbLines($w, $blockText) * $lineH;

        $this->SetXY($col2_x, 6);
        $this->MultiCell($w, $lineH, $blockText, 0, 'L');

        $this->SetXY($col2_x + $w, 6);
        $this->MultiCell(30, $lineH, "Digital receipt", 0, 'L');

        $token = $this->token;
        $qrX = $col2_x + $w;
        $qrY = max(0, $maxHeight - 15);
        $imageUrl = 'https://delivery.eastkentuckymetal.com/receiptqr/receiptqr' . $token . '.png';

        $headers = @get_headers($imageUrl, 1);

        if (
            $headers &&
            strpos($headers[0], '200') !== false &&
            isset($headers['Content-Type']) &&
            (is_array($headers['Content-Type'])
                ? in_array('image/png', $headers['Content-Type'])
                : strpos($headers['Content-Type'], 'image/png') !== false)
        ) {
            @$this->Image($imageUrl, $qrX, $qrY, 20, 20);
        }

        $this->SetY(6 + $maxHeight + 5);
    }

    function Footer() {
        $marginLeft = 10;
        $colWidthLeft  = 110;
        $colWidthRight = 70;
        $this->SetY(-40);

        $colWidth = ($this->w - 2 * $marginLeft) / 3;

        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0);
        $this->Cell($this->w - 2 * $marginLeft, 5, 'Thank you for choosing East Kentucky Metal. We appreciate your business!', 0, 1, 'C');

        $this->SetY($this->GetY() + 1);
        $gpsIcon = 'assets/images/gps.png';
        $text = '977 E. Hal Rogers Parkway';
        $iconWidth = 5;
        $spacing = 2;

        $totalWidth = $iconWidth + $spacing + $this->GetStringWidth($text);
        $x = ($this->w - $totalWidth) / 2;

        $this->Image($gpsIcon, $x, $this->GetY(), $iconWidth, 5);
        $this->SetXY($x + $iconWidth + $spacing, $this->GetY());
        $this->Cell($this->GetStringWidth($text), 5, $text, 0, 1, 'L');

        $this->SetY(-20);
        $this->SetFont('Arial', '', 9);

        $phoneIcon = 'assets/images/phone.png';
        $this->Image($phoneIcon, $marginLeft, $this->GetY(), 5, 5);
        $this->SetXY($marginLeft + 7, $this->GetY());
        $this->Cell($colWidth, 5, '(606) 877-1848 | Fax: (606) 864-4280', 0, 0, 'L');

        $emailIcon = 'assets/images/email.png';
        $this->Image($emailIcon, $marginLeft + $colWidth + 10, $this->GetY(), 5, 5);
        $this->SetXY($marginLeft + $colWidth + 17, $this->GetY());
        $this->Cell($colWidth, 5, 'Sales@EastKentuckyMetal.com', 0, 0, 'L');

        $webIcon = 'assets/images/web.png';
        $this->Image($webIcon, $marginLeft + 2 * $colWidth + 10, $this->GetY(), 5, 5);
        $this->SetXY($marginLeft + 2 * $colWidth + 17, $this->GetY());
        $this->Cell($colWidth, 5, 'EastKentuckyMetal.com', 0, 0, 'L');
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


$col1_x = 10;
$col2_x = 140;
$lumber_id = 1;
$screw_id = 16;
$service_chrg_id = 27;
$panel_id = 3;
$trim_id = 4;

$orderid = $_REQUEST['id'];
$pricing_id = $_REQUEST['pricing_id'] ?? '';

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

        $salesperson = $row_orders["cashier"];

        $token = $row_orders['token'] ?? '';
        
        $pdf->orderid = $orderid;
        $pdf->order_date = $order_date;
        $pdf->delivery_method = $delivery_method;
        $pdf->scheduled_date = $scheduled_date;
        $pdf->salesperson = get_staff_name($salesperson);
        $pdf->token = $token;

        $pdf->AliasNbPages();
        $pdf->AddPage();

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
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($col1_x, $currentY);
        $pdf->Cell($mailToWidth + 10, 7, 'BILL TO:', 1, 0, 'L', true);

        $pdf->SetXY($col2_x, $currentY);
        $pdf->Cell($mailToWidth - 5, 7, 'SHIP TO:', 1, 0, 'L', true);

        $pdf->Ln(7);
        $def_y = $pdf->GetY();

        $pdf->SetFont('Arial', '', 9);
        $startY = $pdf->GetY();

        $leftText = get_customer_name($row_orders['customerid']) . "\n";
        $addressParts = [];
        $address = $customerDetails['address'];
        $city = $customerDetails['city'];
        $state = $customerDetails['state'];
        $zip = $customerDetails['zip'];
        if(!empty($customerDetails['different_ship_address'])){
            $address = $customerDetails['ship_address'];
            $city = $customerDetails['ship_city'];
            $state = $customerDetails['ship_state'];
            $zip = $customerDetails['ship_zip'];
        }
        
        if (!empty($address)) $addressParts[] = $address;
        if (!empty($city)) $addressParts[] = $city;
        if (!empty($state)) $addressParts[] = $state;
        if (!empty($zip)) $addressParts[] = $zip;
        if (!empty($addressParts)) $leftText .= implode(', ', $addressParts) . "\n";
        if (!empty($customerDetails['tax_exempt_number'])) $leftText .= 'Tax Exempt #: ' . $customerDetails['tax_exempt_number'] . "\n";
        if (!empty($customerDetails['contact_phone'])) $leftText .= $customerDetails['contact_phone'] . "\n";

        $leftX = $col1_x;
        $leftWidth = $mailToWidth + 5;

        $pdf->SetXY($leftX, $startY);
        $pdf->MultiCell($leftWidth, 5, $leftText, 0, 'L');
        $leftBottom = $pdf->GetY();

        $rightText = trim($row_orders['deliver_fname'] . ' ' . $row_orders['deliver_lname']) . "\n";
        $shipAddressParts = [];
        if (!empty($row_orders['deliver_address'])) $shipAddressParts[] = $row_orders['deliver_address'];
        if (!empty($row_orders['deliver_city'])) $shipAddressParts[] = $row_orders['deliver_city'];
        if (!empty($row_orders['deliver_state'])) $shipAddressParts[] = $row_orders['deliver_state'];
        if (!empty($row_orders['deliver_zip'])) $shipAddressParts[] = $row_orders['deliver_zip'];
        if (!empty($shipAddressParts)) $rightText .= implode(', ', $shipAddressParts) . "\n";

        $rightX = $col2_x;
        $rightWidth = $mailToWidth - 5;

        $pdf->SetXY($rightX, $startY);
        $pdf->MultiCell($rightWidth, 5, $rightText, 0, 'L');
        $rightBottom = $pdf->GetY();

        $pdf->SetFont('Arial', 'B', 9);

        $customerPO = 'Customer PO #: ' . $row_orders['job_po'];
        $jobName = 'Job Name: ' . $row_orders['job_name'];

        $poJobY = max($leftBottom, $rightBottom);

        $pdf->SetXY($leftX, $poJobY);
        $pdf->MultiCell($leftWidth, 5, $customerPO, 0, 'L');
        $leftBottom = $pdf->GetY();

        $pdf->SetXY($rightX, $poJobY);
        $pdf->MultiCell($rightWidth, 5, $jobName, 0, 'L');
        $rightBottom = $pdf->GetY();

        $blockHeight = max($leftBottom, $rightBottom) - $startY;
        $pdf->Rect($leftX, $startY, $leftWidth, $blockHeight);
        $pdf->Rect($rightX, $startY, $rightWidth, $blockHeight);

        $pdf->SetY($startY + $blockHeight + 2);

        $total_price = 0;
        $total_qty = 0;
        $screw_id = 16;

        $query_product = "
            SELECT p.product_category, op.* 
            FROM order_product AS op
            LEFT JOIN product AS p ON p.product_id = op.productid
            WHERE orderid = '$orderid'
            ORDER BY p.product_category
        ";
        $result_product = mysqli_query($conn, $query_product);

        $total_price  = 0;
        $total_qty    = 0;
        $total_actual = 0;
        $total_saved  = 0;

        renderTableHeader($pdf, $columns, $subcolumns);

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

        foreach ($bundledProducts as $bundleId => $bundleGroup) {
            $bundleName = $bundleGroup[0]['bundle_name'] ?? 'Bundle ' . $bundleId;
            $pdf->SetFont('Arial', 'B', 9);
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
                } elseif ($categoryId == $lumber_id) {
                    [$catTotal, $catQty, $catActual] = renderLumberCategory($pdf, $row_product, $conn);
                } elseif ($categoryId == $service_chrg_id) {
                    [$catTotal, $catQty, $catActual] = renderServiceChargeCategory($pdf, $row_product, $conn);
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
            } elseif ($categoryId == $lumber_id) {
                [$catTotal, $catQty, $catActual] = renderLumberCategory($pdf, $row_product, $conn);
            } elseif ($categoryId == $service_chrg_id) {
                [$catTotal, $catQty, $catActual] = renderServiceChargeCategory($pdf, $row_product, $conn);
            } else {
                [$catTotal, $catQty, $catActual] = renderDefaultCategory($pdf, $row_product, $conn);
            }

            $catSaved = floatval($catActual) - floatval($catTotal);
            $total_price  += floatval($catTotal);
            $total_qty    += intval($catQty);
            $total_actual += floatval($catActual);
            $total_saved  += $catSaved;
        }

        $lineheight = 6;

        $pdf->SetX(140);

        $pdf->ln();
        
        $col1_x = 10;
        $col2_x = 140;
        $col_y = $pdf->GetY();

        $pdf->SetFont('Arial', '', 9);

        $disclaimer = "Customer is solely responsible for accuracy of order and for verifying accuracy of materials before leaving EKMS or at time of delivery. If an agent orders or takes materials on customer's behalf, EKMS is entitled to rely upon the agent as if s/he has full authority to act on customer's behalf. No returns on metal panels or special trim. All other materials returned undamaged within 60 days of invoice date are subject to a restocking fee equal to 25% of current retail price.";

        $disclaimerHeight = $pdf->GetMultiCellHeight(120, 4, $disclaimer); 

        $lineheight = 6;
        $summaryHeight = (5 * $lineheight) + 2;

        $blockHeight = $disclaimerHeight + $summaryHeight;
        if ($pdf->GetY() + $blockHeight > $pdf->GetPageHeight() - 20) {
            $pdf->AddPage();
            $col_y = $pdf->GetY();
        } else {
            $col_y = $pdf->GetY();
        }

        $pdf->SetXY($col1_x, $col_y);
        $pdf->MultiCell(120, 4, $disclaimer, 1, 'L');

        $pdf->Cell(0, 5, 'Page ' . $pdf->PageNo() . ' of {nb}', 0, 0, 'L');

        $pdf->SetFont('Arial', '', 9);

        $materials_total = $row_orders['discounted_price'] ?? 0;
        $discount_value = 0;
        if (!empty($row_orders['discount_percent']) && $row_orders['discount_percent'] > 0) {
            $discount_value = $materials_total * ($row_orders['discount_percent'] / 100);
        } elseif (!empty($row_orders['discount_amount']) && $row_orders['discount_amount'] > 0) {
            $discount_value = min($row_orders['discount_amount'], $materials_total);
        }

        $subtotal = max(0, $materials_total - $discount_value);
        $taxable_total = $subtotal + $delivery_price;
        $sales_tax = $taxable_total * $tax;
        $grand_total = $taxable_total + $sales_tax;

        $total_saved = $discount_value;

        $pdf->SetXY($col2_x, $col_y);
        $pdf->Cell(40, $lineheight, 'SAVINGS:', 0, 0);
        $pdf->Cell(20, $lineheight, '$ ' . number_format(max(0, $total_saved), 2), 0, 1, 'R');

        $pdf->Ln(5);

        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(40, $lineheight, 'MATERIALS PRICE:', 0, 0);
        $pdf->Cell(20, $lineheight, '$ ' . number_format($subtotal, 2), 0, 1 , 'R');

        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(40, $lineheight, 'DELIVERY CHARGE:', 0, 0);
        $pdf->Cell(20, $lineheight, '$ ' . number_format($delivery_price, 2), 0, 1 , 'R');

        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(40, $lineheight, 'SALES TAX:', 0, 0);
        $pdf->Cell(20, $lineheight, '$ ' . number_format($sales_tax, 2), 0, 1, 'R');

        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY($col2_x, $pdf->GetY());
        $pdf->Cell(40, $lineheight, 'TOTAL PRICE:', 0, 0);
        $pdf->Cell(20, $lineheight, '$ ' . number_format($grand_total, 2), 0, 1, 'R');

        $pdf->Ln(5);

        $payments = [
            'Pay at Pickup'    => floatval($row_orders['pay_pickup']),
            'Pay at Delivery'  => floatval($row_orders['pay_delivery']),
            'Cash'             => floatval($row_orders['pay_cash']),
            'Credit/Debit Card'=> floatval($row_orders['pay_card']),
            'Check'            => floatval($row_orders['pay_check']),
            'Charge Net 30'      => floatval($row_orders['pay_net30'])
        ];

        $lineheight = 5;
        $rectWidth  = 60;

        foreach ($payments as $type => $amt) {
            if ($amt <= 0) continue;

            $yStart = $pdf->GetY();
            $rectHeight = $lineheight * 2;

            $pdf->Rect($col2_x, $yStart, $rectWidth, $rectHeight);

            $pdf->SetXY($col2_x, $yStart);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(30, $lineheight, 'Payment Type:', 0, 0, 'R');
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(30, $lineheight, $type, 0, 1, 'L');

            
            $pdf->SetXY($col2_x, $pdf->GetY());
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(30, $lineheight, 'Amount:', 0, 0, 'R');
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(30, $lineheight, '$' . number_format($amt, 2), 0, 1, 'L');

            $pdf->SetY($pdf->GetY() + 1);
        }

        $pdf->SetTitle('Receipt');
        $pdf->Output('Receipt.pdf', 'I');
            
    }
}else{
    echo "ID not Found!";
}

?>
