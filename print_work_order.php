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
    $maxWidth = $w;
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
    /* $columns = [
        ['label' => 'DESCRIPTION', 'width' => 65, 'align' => 'C'],
        ['label' => 'COLOR', 'width' => 30, 'align' => 'C'],
        ['label' => 'Profile', 'width' => 20, 'align' => 'C'],
        ['label' => 'QTY', 'width' => 20, 'align' => 'C'],
        ['label' => 'PACK COUNT', 'width' => 25, 'align' => 'C'],
        ['label' => 'CUSTOMER PRICE', 'width' => 28, 'align' => 'R'],
    ]; */

    $columns = [
        ['label' => 'DESCRIPTION',      'width' => 73, 'align' => 'C'],
        ['label' => 'COLOR',            'width' => 30, 'align' => 'C'],
        ['label' => 'GRADE',            'width' => 20, 'align' => 'C'],
        ['label' => 'PROFILE',          'width' => 20, 'align' => 'C'],
        ['label' => 'QTY',              'width' => 20, 'align' => 'C'],
        ['label' => 'LENGTH/PACK',      'width' => 25, 'align' => 'C'],
        ['label' => 'CUSTOMER PRICE', 'width' => 28, 'align' => 'R'],
    ];
    
    $pdf->SetFont('Arial', '', 8);

    $totalQty   = 0;
    $totalPrice = 0;
    $totalActual = 0;

    foreach ($products as $row_product) {
        $product_details = getProductDetails($row_product['productid']);
        $profile_details = getProfileTypeDetails($row_product['custom_profile']);
        $grade_details = getGradeDetails($row_product['custom_grade']);

        $order_product_id = $row_product['id'];
        $query = "SELECT * FROM work_order WHERE work_order_product_id = '$order_product_id'";
        $result = mysqli_query($conn, $query);

        $assigned_coils = '';
        if (mysqli_num_rows($result) > 0) {
            while($row_work_orders = mysqli_fetch_assoc($result)){
                $assigned_coils = $row_work_orders['assigned_coils'];
            }
        }

        $row = [
            $row_product['product_item'] . 
                (!empty($row_product['note']) ? "\nNote: " . $row_product['note'] : ''),
            getColorName($row_product['custom_color']),
            $grade_details['product_grade'] ?? '',
            getGaugeName($row_product['custom_gauge'] ?? ''),
            $assigned_coils,
            $row_product['pack_count'] ?? '',
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
        ['label' => 'DESCRIPTION', 'width' => 73, 'align' => 'C'],
        ['label' => 'COLOR', 'width' => 30, 'align' => 'C'],
        ['label' => 'Grade', 'width' => 20, 'align' => 'C'],
        ['label' => 'Profile', 'width' => 20, 'align' => 'C'],
        ['label' => 'QTY', 'width' => 20, 'align' => 'C'],
        ['label' => 'LENGTH (FT)', 'width' => 25, 'align' => 'C'],
        ['label' => 'CUSTOMER PRICE', 'width' => 28, 'align' => 'R'],
    ];

    $grouped = [];
    foreach ($products as $row_product) {
        $productid       = $row_product['productid'];
        $bundle_name     = $row_product['bundle_id'];

        $product_details = getProductDetails($row_product['productid']);
        $profile_details = getProfileTypeDetails($row_product['custom_profile']);
        $grade_details = getGradeDetails($row_product['custom_grade']);

        $quantity   = floatval($row_product['quantity'] ?? 0);
        $act_price  = floatval($row_product['actual_price']);
        $disc_price = floatval($row_product['discounted_price']);
        $note       = $row_product['note'] ?? '';

        $panel_type  = $row_product['panel_type'];
        $panel_style = $row_product['panel_style'];

        $ft  = floatval($row_product['custom_length'] ?? 0);
        $in  = floatval($row_product['custom_length2'] ?? 0);
        $len = $ft + ($in / 12);

        $order_product_id = $row_product['id'];
        $query = "SELECT * FROM work_order WHERE work_order_product_id = '$order_product_id'";
        $result = mysqli_query($conn, $query);

        $assigned_coils = '';
        if (mysqli_num_rows($result) > 0) {
            while($row_work_orders = mysqli_fetch_assoc($result)){
                $assigned_coils = $row_work_orders['assigned_coils'];
            }
        }

        $key = $productid;
        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'product_name'    => $row_product['product_item'],
                'color'           => getColorName($row_product['custom_color']),
                'grade'           => $grade_details['grade_abbreviations'] ?? '',
                'gauge'           => getGaugeName($row_product['custom_gauge'] ?? ''),
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
            $g['gauge'],
            $assigned_coils,
            number_format($g['length'], 2)
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
                ];
                renderRow($pdf, $subColumns, $subRow);
            }
        }
    }

    return [$totalPrice, $totalQty, $totalActual];
}

function renderDefaultCategory($pdf, $products, $conn) {
    $columns = [
        ['label' => 'DESCRIPTION', 'width' => 73, 'align' => 'C'],
        ['label' => 'COLOR', 'width' => 30, 'align' => 'C'],
        ['label' => 'Grade', 'width' => 20, 'align' => 'C'],
        ['label' => 'Profile', 'width' => 20, 'align' => 'C'],
        ['label' => 'QTY', 'width' => 20, 'align' => 'C'],
        ['label' => 'LENGTH (FT)', 'width' => 25, 'align' => 'C'],
        ['label' => 'CUSTOMER PRICE', 'width' => 28, 'align' => 'R'],
    ];

    $pdf->SetFont('Arial', '', 8);

    $totalQty    = 0;
    $totalPrice  = 0;
    $totalActual = 0;

    foreach ($products as $row_product) {
        $product_details = getProductDetails($row_product['productid']);
        $profile_details = getProfileTypeDetails($row_product['custom_profile']);
        $grade_details = getGradeDetails($row_product['custom_grade']);

        $ft  = floatval($row_product['custom_length'] ?? 0);
        $in  = floatval($row_product['custom_length2'] ?? 0);
        $len = $ft + ($in / 12);

        $order_product_id = $row_product['id'];
        $query = "SELECT * FROM work_order WHERE work_order_product_id = '$order_product_id'";
        $result = mysqli_query($conn, $query);

        $assigned_coils = '';
        if (mysqli_num_rows($result) > 0) {
            while($row_work_orders = mysqli_fetch_assoc($result)){
                $assigned_coils = $row_work_orders['assigned_coils'];
            }
        }

        $row = [
            $row_product['product_item'] . (!empty($row_product['note']) ? "\nNote: " . $row_product['note'] : ''),
            getColorName($row_product['custom_color']),
            $grade_details['product_grade'] ?? '',
            $profile_details['profile_type'] ?? '',
            $row_product['quantity'],
            number_format($len, 2),
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
        ['label' => 'DESCRIPTION', 'width' => 98, 'align' => 'C'],
        ['label' => 'COLOR', 'width' => 30, 'align' => 'C'],
        ['label' => 'Grade', 'width' => 20, 'align' => 'C'],
        ['label' => 'Gauge', 'width' => 20, 'align' => 'C'],
        ['label' => 'Coil #', 'width' => 20, 'align' => 'C'],
    ];

    $totalQty = 0;
    $totalPrice = 0;
    $totalActual = 0;

    foreach ($products as $row_product) {
        $productName = $row_product['product_item'];
        $color       = getColorName($row_product['custom_color']);
        $grade       = getGradeDetails($row_product['custom_grade'])['grade_abbreviations'] ?? '';
        $gauge       = getGaugeName($row_product['custom_gauge'] ?? '');

        $ft  = floatval($row_product['custom_length'] ?? 0);
        $in  = floatval($row_product['custom_length2'] ?? 0);
        $len = $ft + ($in / 12);

        $order_product_id = $row_product['id'];
        $assigned_coils_arr = [];
        $result = mysqli_query($conn, "SELECT assigned_coils FROM work_order WHERE work_order_product_id = '$order_product_id'");
        if (mysqli_num_rows($result) > 0) {
            while ($row_work_orders = mysqli_fetch_assoc($result)) {
                $assigned_coils_arr[] = $row_work_orders['assigned_coils'];
            }
        }

        $assigned_coils_list = !empty($assigned_coils_arr) ? implode(', ', $assigned_coils_arr) : '';

        $summaryRow = [
            $productName,
            $color,
            $grade,
            $gauge,
            $assigned_coils_list
        ];
        renderRow($pdf, $columns, $summaryRow, true);

        $subColumns = [
            ['label' => '', 'width' => 73, 'align' => 'L'],
            ['label' => 'Qty', 'width' => 20, 'align' => 'C'],
            ['label' => 'Ft',  'width' => 20, 'align' => 'C'],
            ['label' => 'In',  'width' => 20, 'align' => 'C'],
            
        ];
        renderTableHeader($pdf, $subColumns);
        $pdf->SetFont('Arial', '', 7);

        $subRow = [
            !empty($row_product['note']) ? "Note: " . $row_product['note'] : '',
            floatval($row_product['quantity'] ?? 0),
            $ft,
            decimalToFractionInch($in),
            
        ];
        renderRow($pdf, $subColumns, $subRow);

        $totalQty    += floatval($row_product['quantity'] ?? 0);
        $totalPrice  += floatval($row_product['discounted_price'] ?? 0);
        $totalActual += floatval($row_product['actual_price'] ?? 0);

        $pdf->Ln(2);
    }

    return [$totalPrice, $totalQty, $totalActual];
}

function renderLumberCategory($pdf, $products, $conn) {
    /* $columns = [
        ['label' => 'DESCRIPTION', 'width' => 115, 'align' => 'C'],
        ['label' => 'QTY', 'width' => 20, 'align' => 'C'],
        ['label' => 'LENGTH (FT)', 'width' => 25, 'align' => 'C'],
        ['label' => 'CUSTOMER PRICE', 'width' => 28, 'align' => 'R'],
    ]; */

    $columns = [
        ['label' => 'DESCRIPTION',      'width' => 73, 'align' => 'C'],
        ['label' => 'COLOR',            'width' => 30, 'align' => 'C'],
        ['label' => 'GRADE',            'width' => 20, 'align' => 'C'],
        ['label' => 'PROFILE',          'width' => 20, 'align' => 'C'],
        ['label' => 'QTY',              'width' => 20, 'align' => 'C'],
        ['label' => 'LENGTH/PACK',      'width' => 25, 'align' => 'C'],
    ];

    renderTableHeader($pdf, $columns);
    $pdf->SetFont('Arial', '', 8);

    $totalQty    = 0;
    $totalPrice  = 0;
    $totalActual = 0;

    foreach ($products as $row_product) {
        $product_details = getProductDetails($row_product['productid']);
        $profile_details = getProfileTypeDetails($row_product['custom_profile']);
        $grade_details = getGradeDetails($row_product['custom_grade']);

        $ft  = floatval($row_product['custom_length'] ?? 0);
        $in  = floatval($row_product['custom_length2'] ?? 0);
        $len = $ft + ($in / 12);

        $order_product_id = $row_product['id'];
        $query = "SELECT * FROM work_order WHERE work_order_product_id = '$order_product_id'";
        $result = mysqli_query($conn, $query);

        $assigned_coils = '';
        if (mysqli_num_rows($result) > 0) {
            while($row_work_orders = mysqli_fetch_assoc($result)){
                $assigned_coils = $row_work_orders['assigned_coils'];
            }
        }

        $row = [
            $row_product['product_item'] . (!empty($row_product['note']) ? "\nNote: " . $row_product['note'] : ''),
            getColorName($row_product['custom_color']),
            $grade_details['product_grade'] ?? '',
            $assigned_coils,
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
    public $orderid;
    public $order_date;
    public $delivery_method;
    public $scheduled_date;
    public $salesperson;

    function Header() {
        $this->SetFont('Arial', '', 10);
        $this->Image('assets/images/logo-bw.png', 10, 6, 60, 20);

        $col2_x = 140;

        $this->SetXY($col2_x - 10, 6);
        $this->MultiCell(95, 5, "Invoice #: " . $this->orderid, 0, 'L');

        $this->SetXY($col2_x - 10, $this->GetY());
        $this->Cell(95, 5, "Order Date: " . $this->order_date, 0, 1, 'L');

        $this->SetXY($col2_x - 10, $this->GetY());
        $this->Cell(95, 5, "Pick-up or Delivery: " . $this->delivery_method, 0, 1, 'L');

        $this->SetXY($col2_x - 10, $this->GetY());
        $this->Cell(95, 5, "Scheduled Date: " . $this->scheduled_date, 0, 1, 'L');

        $this->SetXY($col2_x - 10, $this->GetY());
        $this->Cell(95, 5, "Salesperson: " . $this->salesperson, 0, 0, 'L');

        $this->Ln(5);
    }

    function Footer() {
        $marginLeft = 10;
        $marginLeft = 10;
        $colWidthLeft  = 110;
        $colWidthRight = 70;
        $this->SetY(-35);

        $colWidth = ($this->w - 2 * $marginLeft) / 3;

        $yStart = $this->GetY();
        $this->SetFont('Arial', '', 10);
        $this->SetXY($marginLeft, $yStart);
        $this->MultiCell($colWidthRight, 5,
            "Scan me for a Digtal copy of this receipt", 0, 'C');

        $qrX = 30;
        $qrY = $this->GetY();
        $this->Image('assets/images/qr_rickroll.png', $qrX, $qrY, 25, 25);
    }

    public function GetMultiCellHeight($w, $h, $txt)
    {
        // Calculate number of lines
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', (string)$txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n")
            $nb--;
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
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c] ?? 0;
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }
        return $nl * $h;
    }
}

$pdf = new PDF();
$pdf->SetAutoPageBreak(true, 35);


$col1_x = 10;
$col2_x = 140;
$lumber_id = 1;
$screw_id = 16;
$panel_id = 3;
$trim_id = 4;

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
        $order_date = date("n/j/Y", strtotime($row_orders['order_date']));
        $scheduled_date = '';
        if (!empty($row_orders['scheduled_date']) && strtotime($row_orders['scheduled_date']) !== false) {
            $scheduled_date = date("n/j/Y", strtotime($row_orders['scheduled_date']));
        }
        if($delivery_price == 0){
            $delivery_method = 'Pickup';
        }
        
        $pdf->orderid = $orderid;
        $pdf->order_date = $order_date;
        $pdf->delivery_method = $delivery_method;
        $pdf->scheduled_date = $scheduled_date;
        $pdf->salesperson = get_staff_name($current_user_id);

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
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY($col1_x, $currentY);
        $pdf->Cell($mailToWidth, 7, 'Customer', 0, 0, 'L', true);


        $pdf->Ln(7);
        $def_y = $pdf->GetY();

        $leftX = $col1_x;
        $leftY = $def_y;
        $pdf->SetXY($leftX, $leftY);

        $leftText = get_customer_name($row_orders['customerid'])."\n";
        $addressParts = [];
        if (!empty($customerDetails['address'])) $addressParts[] = $customerDetails['address'];
        if (!empty($customerDetails['city'])) $addressParts[] = $customerDetails['city'];
        if (!empty($customerDetails['state'])) $addressParts[] = $customerDetails['state'];
        if (!empty($customerDetails['zip'])) $addressParts[] = $customerDetails['zip'];
        if (!empty($addressParts)) $leftText .= implode(', ', $addressParts)."\n";
        if (!empty($customerDetails['tax_exempt_number'])) $leftText .= 'Tax Exempt #: '.$customerDetails['tax_exempt_number']."\n";

        $pdf->SetFont('Arial', '', 10);
        $leftStartY = $pdf->GetY();
        $pdf->MultiCell($mailToWidth, 5, $leftText, 0, 'L');
        $leftHeight = $pdf->GetY() - $leftStartY;

        $rightX = $col2_x;
        $rightY = $def_y;
        $pdf->SetXY($rightX, $rightY);

        $rightText = 'Customer PO #: '. $row_orders['job_po'] ."\n\n";
        $rightText .= 'Job Name: '.$row_orders['job_name'];

        $rightStartY = $pdf->GetY();
        $pdf->MultiCell($mailToWidth, 5, $rightText, 0, 'L');
        $rightHeight = $pdf->GetY() - $rightStartY;

        $blockHeight = max($leftHeight, $rightHeight);
        $pdf->SetY($def_y + $blockHeight + 2);


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

        $firstCategory = true;

        foreach ($productsByCategory as $categoryId => $products) {
            if (!$firstCategory) {
                $pdf->AddPage();
            } else {
                $firstCategory = false;
            }

            if ($categoryId == $screw_id) {
                $categoryName = getProductCategoryName($categoryId);

                $pdf->SetFont('Arial', 'B', 16);
                $pdf->SetFillColor(240, 240, 240);
                $pdf->SetDrawColor(0, 0, 0);
                $pdf->Cell(0, 10, $categoryName, 1, 1, 'C', true);
                $pdf->Ln(5);

                $columns = [
                    ['label' => 'DESCRIPTION', 'width' => 73, 'align' => 'C'],
                    ['label' => 'COLOR',       'width' => 30, 'align' => 'C'],
                    ['label' => 'GRADE',       'width' => 20, 'align' => 'C'],
                    ['label' => 'GAUGE',       'width' => 20, 'align' => 'C'],
                    ['label' => 'COIL #',      'width' => 20, 'align' => 'C'],
                    ['label' => 'Total Footage (FT)', 'width' => 25, 'align' => 'C']
                ];

                renderTableHeader($pdf, $columns);

                [$catTotal, $catQty, $catActual] = renderScrewCategory($pdf, $products, $conn);
            } elseif ($categoryId == $panel_id) {
                $categoryName = getProductCategoryName($categoryId) . " WORK ORDER";

                $pdf->SetFont('Arial', 'B', 16);
                $pdf->SetFillColor(240, 240, 240);
                $pdf->SetDrawColor(0, 0, 0);
                $pdf->Cell(0, 10, $categoryName, 1, 1, 'C', true);
                $pdf->Ln(5);

                $columns = [
                    ['label' => 'DESCRIPTION', 'width' => 73, 'align' => 'C'],
                    ['label' => 'COLOR',       'width' => 30, 'align' => 'C'],
                    ['label' => 'GRADE',       'width' => 20, 'align' => 'C'],
                    ['label' => 'GAUGE',       'width' => 20, 'align' => 'C'],
                    ['label' => 'COIL #',      'width' => 20, 'align' => 'C'],
                    ['label' => 'Total Footage (FT)', 'width' => 25, 'align' => 'C']
                ];

                renderTableHeader($pdf, $columns);

                [$catTotal, $catQty, $catActual] = renderPanelCategory($pdf, $products, $conn);
            } elseif ($categoryId == $trim_id) {
                $categoryName = getProductCategoryName($categoryId) . " WORK ORDER";

                $pdf->SetFont('Arial', 'B', 16);
                $pdf->SetFillColor(240, 240, 240);
                $pdf->SetDrawColor(0, 0, 0);
                $pdf->Cell(0, 10, $categoryName, 1, 1, 'C', true);
                $pdf->Ln(5);

                $columns = [
                    ['label' => 'DESCRIPTION', 'width' => 98, 'align' => 'C'],
                    ['label' => 'COLOR',       'width' => 30, 'align' => 'C'],
                    ['label' => 'GRADE',       'width' => 20, 'align' => 'C'],
                    ['label' => 'GAUGE',       'width' => 20, 'align' => 'C'],
                    ['label' => 'COIL #',      'width' => 20, 'align' => 'C']
                ];

                renderTableHeader($pdf, $columns);

                [$catTotal, $catQty, $catActual] = renderTrimCategory($pdf, $products, $conn);
            } elseif ($categoryId == $lumber_id) {
                $categoryName = getProductCategoryName($categoryId);

                $pdf->SetFont('Arial', 'B', 16);
                $pdf->SetFillColor(240, 240, 240);
                $pdf->SetDrawColor(0, 0, 0);
                $pdf->Cell(0, 10, $categoryName, 1, 1, 'C', true);
                $pdf->Ln(5);

                $columns = [
                    ['label' => 'DESCRIPTION', 'width' => 73, 'align' => 'C'],
                    ['label' => 'COLOR',       'width' => 30, 'align' => 'C'],
                    ['label' => 'GRADE',       'width' => 20, 'align' => 'C'],
                    ['label' => 'GAUGE',       'width' => 20, 'align' => 'C'],
                    ['label' => 'COIL #',      'width' => 20, 'align' => 'C'],
                    ['label' => 'Total Footage (FT)', 'width' => 25, 'align' => 'C']
                ];

                renderTableHeader($pdf, $columns);

                [$catTotal, $catQty, $catActual] = renderLumberCategory($pdf, $products, $conn);
            } else {
                $categoryName = getProductCategoryName($categoryId);

                $pdf->SetFont('Arial', 'B', 16);
                $pdf->SetFillColor(240, 240, 240);
                $pdf->SetDrawColor(0, 0, 0);
                $pdf->Cell(0, 10, $categoryName, 1, 1, 'C', true);
                $pdf->Ln(5);

                $columns = [
                    ['label' => 'DESCRIPTION', 'width' => 73, 'align' => 'C'],
                    ['label' => 'COLOR',       'width' => 30, 'align' => 'C'],
                    ['label' => 'GRADE',       'width' => 20, 'align' => 'C'],
                    ['label' => 'GAUGE',       'width' => 20, 'align' => 'C'],
                    ['label' => 'COIL #',      'width' => 20, 'align' => 'C'],
                    ['label' => 'Total Footage (FT)', 'width' => 25, 'align' => 'C']
                ];

                renderTableHeader($pdf, $columns);
                [$catTotal, $catQty, $catActual] = renderDefaultCategory($pdf, $products, $conn);
            }

            $catSaved = floatval($catActual) - floatval($catTotal);

            $total_price  += floatval($catTotal);
            $total_qty    += intval($catQty);
            $total_actual += floatval($catActual);
            $total_saved  += $catSaved;

            $pdf->Ln(3);
        }

        $pdf->SetTitle('Receipt');
        $pdf->Output('Receipt.pdf', 'I');
            

        
    }
}else{
    echo "ID not Found!";
}

?>
