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
    ['label' => 'PRODUCT ID #', 'width' => 30, 'align' => 'C'],
    ['label' => 'DESCRIPTION', 'width' => 30, 'align' => 'C'],
    ['label' => 'COLOR', 'width' => 20, 'align' => 'C'],
    ['label' => 'GRADE', 'width' => 15, 'align' => 'C'],
    ['label' => 'GAUGE', 'width' => 15, 'align' => 'C'],
    ['label' => 'PROFILE', 'width' => 15, 'align' => 'C'],
    ['label' => 'QTY', 'width' => 15, 'align' => 'C'],
    ['label' => 'LENGTH', 'width' => 20, 'align' => 'C'],
    ['label' => 'CUSTOMER PRICE', 'width' => 28, 'align' => 'R'],
];

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

function renderPanelCategory($pdf, $products, $conn) {
    global $columns;

    $grouped = [];

    foreach ($products as $row_product) {
        $productid   = $row_product['productid'];
        $bundle_name = $row_product['bundle_id'];

        $product_details = getProductDetails($productid);
        $profile_details = getProfileTypeDetails($row_product['custom_profile']);
        $grade_details   = getGradeDetails($row_product['custom_grade']);
        $gauge_details   = getGaugeDetails($row_product['custom_gauge']);

        $quantity   = floatval($row_product['quantity'] ?? 0);
        $act_price  = floatval($row_product['actual_price'] ?? 0);
        $disc_price = floatval($row_product['discounted_price'] ?? 0);
        $note       = trim($row_product['note'] ?? '');

        $panel_type  = $row_product['panel_type'];
        $panel_style = $row_product['panel_style'];

        $ft  = floatval($row_product['custom_length'] ?? 0);
        $in  = floatval($row_product['custom_length2'] ?? 0);
        $len = $ft + ($in / 12);

        $product_abbrev = $row_product['product_id_abbrev'] ?? '';

        $key = $productid;

        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'product_name'     => $row_product['product_item'],
                'product_abbrev'   => $product_abbrev,
                'color'            => getColorName($row_product['custom_color']),
                'grade'            => $grade_details['grade_abbreviations'] ?? '',
                'gauge'            => $gauge_details['gauge_abbreviations'] ?? '',
                'profile'          => $profile_details['profile_type'] ?? '',
                'quantity'         => 0,
                'length'           => 0,
                'discounted_total' => 0,
                'actual_total'     => 0,
                'bundles'          => []
            ];
        }

        $grouped[$key]['quantity']         += $quantity;
        $grouped[$key]['length']           += $len;
        $grouped[$key]['discounted_total'] += $disc_price;
        $grouped[$key]['actual_total']     += $act_price;

        if (!isset($grouped[$key]['bundles'][$bundle_name])) {
            $grouped[$key]['bundles'][$bundle_name] = [
                'bundle_name' => $bundle_name,
                'rows'        => []
            ];
        }

        $grouped[$key]['bundles'][$bundle_name]['rows'][] = [
            'qty'         => $quantity,
            'ft'          => $ft,
            'in'          => $in,
            'inch_text'   => decimalToFractionInch($in),
            'panel_type'  => $panel_type,
            'panel_style' => $panel_style,
            'disc_price'  => $disc_price,
            'act_price'   => $act_price,
            'note'        => $note,
        ];
    }

    $totalQty    = 0;
    $totalPrice  = 0;
    $totalActual = 0;

    foreach ($grouped as $g) {
        $summaryRow = [
            $g['product_abbrev'],
            $g['product_name'],
            $g['color'],
            $g['grade'],
            $g['gauge'],
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
            $bundle_header = $bundle['bundle_name'] ?: '';

            $subColumns = [
                ['label' => '',              'width' => 30, 'align' => 'C'],
                ['label' => $bundle_header,  'width' => 30, 'align' => 'C'],
                ['label' => 'Qty',           'width' => 20, 'align' => 'C'],
                ['label' => 'Ft',            'width' => 15, 'align' => 'C'],
                ['label' => 'In',            'width' => 15, 'align' => 'C'],
                ['label' => 'Panel Type',    'width' => 15, 'align' => 'C'],
                ['label' => 'Panel Style',   'width' => 15, 'align' => 'C'],
                
            ];

            renderTableHeader($pdf, $subColumns);
            $pdf->SetFont('Arial', '', 7);

            foreach ($bundle['rows'] as $row) {
                $row_prod_id = $g['product_abbrev'] ?? '';
                $row_text    = !empty($row['note']) ? 'Note: ' . $row['note'] : '';

                $product_unit_price = floatval($product_details['unit_price'] ?? 0);
                $color_id = $row_product['custom_color'] ?? '';
                $grade    = $row_product['custom_grade'] ?? '';
                $gauge    = $row_product['custom_gauge'] ?? '';

                $unit_price = calculateUnitPrice(
                    $product_unit_price,
                    $row['ft'],
                    $row['in'],
                    $row['panel_type'],
                    0,
                    0,
                    0,
                    $color_id,
                    $grade,
                    $gauge
                );

                $subRow = [
                    $row_prod_id,
                    $row_text,
                    $row['qty'],
                    $row['ft'] . '\'',
                    $row['inch_text'],
                    $row['panel_type'],
                    $row['panel_style'],
                    
                ];
                renderSubRow($pdf, $subColumns, $subRow);
            }
        }
    }

    return [$totalPrice, $totalQty, $totalActual];
}

function renderTrimCategory($pdf, $products, $conn) {
    global $columns;

    $grouped = [];

    foreach ($products as $row_product) {
        $productid   = $row_product['productid'];
        $bundle_name = $row_product['bundle_id'];

        $product_details = getProductDetails($productid);
        $profile_details = getProfileTypeDetails($row_product['custom_profile']);
        $grade_details   = getGradeDetails($row_product['custom_grade']);
        $gauge_details   = getGaugeDetails($row_product['custom_gauge']);

        $quantity   = floatval($row_product['quantity'] ?? 0);
        $act_price  = floatval($row_product['actual_price'] ?? 0);
        $disc_price = floatval($row_product['discounted_price'] ?? 0);
        $note       = trim($row_product['note'] ?? '');

        $ft  = floatval($row_product['custom_length'] ?? 0);
        $in  = floatval($row_product['custom_length2'] ?? 0);
        $len = $ft + ($in / 12);

        $product_abbrev = $row_product['product_id_abbrev'] ?? '';

        $key = $productid;

        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'product_name'     => $row_product['product_item'],
                'product_abbrev'   => $product_abbrev,
                'color'            => getColorName($row_product['custom_color']),
                'grade'            => $grade_details['grade_abbreviations'] ?? '',
                'gauge'            => $gauge_details['gauge_abbreviations'] ?? '',
                'profile'          => $profile_details['profile_type'] ?? '',
                'quantity'         => 0,
                'length'           => 0,
                'discounted_total' => 0,
                'actual_total'     => 0,
                'bundles'          => []
            ];
        }

        $grouped[$key]['quantity']         += $quantity;
        $grouped[$key]['length']           += $len;
        $grouped[$key]['discounted_total'] += $disc_price;
        $grouped[$key]['actual_total']     += $act_price;

        if (!isset($grouped[$key]['bundles'][$bundle_name])) {
            $grouped[$key]['bundles'][$bundle_name] = [
                'bundle_name' => $bundle_name,
                'rows'        => []
            ];
        }

        $grouped[$key]['bundles'][$bundle_name]['rows'][] = [
            'qty'         => $quantity,
            'ft'          => $ft,
            'in'          => $in,
            'inch_text'   => decimalToFractionInch($in),
            'panel_type'  => '',
            'panel_style' => '',
            'disc_price'  => $disc_price,
            'act_price'   => $act_price,
            'note'        => $note,
        ];
    }

    $totalQty    = 0;
    $totalPrice  = 0;
    $totalActual = 0;

    foreach ($grouped as $g) {
        $summaryRow = [
            $g['product_abbrev'],
            $g['product_name'],
            $g['color'],
            $g['grade'],
            $g['gauge'],
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
            $bundle_header = $bundle['bundle_name'] ?: '';

            $subColumns = [
                ['label' => '',              'width' => 30, 'align' => 'C'],
                ['label' => $bundle_header,  'width' => 30, 'align' => 'C'],
                ['label' => 'Qty',           'width' => 20, 'align' => 'C'],
                ['label' => 'Ft',            'width' => 15, 'align' => 'C'],
                ['label' => 'In',            'width' => 15, 'align' => 'C'],
                ['label' => '',    'width' => 15, 'align' => 'C'],
                ['label' => '',   'width' => 15, 'align' => 'C'],
                
            ];

            renderTableHeader($pdf, $subColumns);
            $pdf->SetFont('Arial', '', 7);

            foreach ($bundle['rows'] as $row) {
                $row_prod_id = $g['product_abbrev'] ?? '';
                $row_text    = !empty($row['note']) ? 'Note: ' . $row['note'] : '';

                $product_unit_price = floatval($product_details['unit_price'] ?? 0);
                $color_id = $row_product['custom_color'] ?? '';
                $grade    = $row_product['custom_grade'] ?? '';
                $gauge    = $row_product['custom_gauge'] ?? '';

                $unit_price = calculateUnitPrice(
                    $product_unit_price,
                    $row['ft'],
                    $row['in'],
                    $row['panel_type'],
                    0,
                    0,
                    0,
                    $color_id,
                    $grade,
                    $gauge
                );

                $subRow = [
                    $row_prod_id,
                    $row_text,
                    $row['qty'],
                    $row['ft'] . '\'',
                    $row['inch_text'],
                    '',
                    ''
                ];
                renderSubRow($pdf, $subColumns, $subRow);
            }
        }
    }

    return [$totalPrice, $totalQty, $totalActual];
}

function renderScrewCategory($pdf, $products, $conn) {
    global $columns;

    $grouped = [];

    foreach ($products as $row_product) {
        $productid   = $row_product['productid'];
        $bundle_name = $row_product['bundle_id'];

        $product_details = getProductDetails($productid);
        $profile_details = getProfileTypeDetails($row_product['custom_profile']);
        $grade_details   = getGradeDetails($row_product['custom_grade']);
        $gauge_details   = getGaugeDetails($row_product['custom_gauge']);

        $quantity   = floatval($row_product['quantity'] ?? 0);
        $act_price  = floatval($row_product['actual_price'] ?? 0);
        $disc_price = floatval($row_product['discounted_price'] ?? 0);
        $note       = trim($row_product['note'] ?? '');

        $ft  = floatval($row_product['custom_length'] ?? 0);
        $in  = floatval($row_product['custom_length2'] ?? 0);
        $len = $ft + ($in / 12);

        $product_abbrev = $row_product['product_id_abbrev'] ?? '';

        $key = $productid;

        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'product_name'     => $row_product['product_item'],
                'product_abbrev'   => $product_abbrev,
                'color'            => getColorName($row_product['custom_color']),
                'grade'            => $grade_details['grade_abbreviations'] ?? '',
                'gauge'            => $gauge_details['gauge_abbreviations'] ?? '',
                'profile'          => $profile_details['profile_type'] ?? '',
                'quantity'         => 0,
                'length'           => 0,
                'discounted_total' => 0,
                'actual_total'     => 0,
                'bundles'          => []
            ];
        }

        $grouped[$key]['quantity']         += $quantity;
        $grouped[$key]['length']           += $len;
        $grouped[$key]['discounted_total'] += $disc_price;
        $grouped[$key]['actual_total']     += $act_price;

        if (!isset($grouped[$key]['bundles'][$bundle_name])) {
            $grouped[$key]['bundles'][$bundle_name] = [
                'bundle_name' => $bundle_name,
                'rows'        => []
            ];
        }

        $grouped[$key]['bundles'][$bundle_name]['rows'][] = [
            'qty'         => $quantity,
            'ft'          => $ft,
            'in'          => $in,
            'inch_text'   => decimalToFractionInch($in),
            'panel_type'  => '',
            'panel_style' => '',
            'disc_price'  => $disc_price,
            'act_price'   => $act_price,
            'note'        => $note,
        ];
    }

    $totalQty    = 0;
    $totalPrice  = 0;
    $totalActual = 0;

    foreach ($grouped as $g) {
        $summaryRow = [
            $g['product_abbrev'],
            $g['product_name'],
            $g['color'],
            $g['grade'],
            $g['gauge'],
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
            $bundle_header = $bundle['bundle_name'] ?: '';

            $subColumns = [
                ['label' => '',              'width' => 30, 'align' => 'C'],
                ['label' => $bundle_header,  'width' => 30, 'align' => 'C'],
                ['label' => 'Qty',           'width' => 20, 'align' => 'C'],
                ['label' => 'Length',            'width' => 15, 'align' => 'C'],
                ['label' => 'Type',            'width' => 15, 'align' => 'C'],
                ['label' => 'Pack Size',    'width' => 15, 'align' => 'C'],
                ['label' => '',   'width' => 15, 'align' => 'C'],
                
            ];

            renderTableHeader($pdf, $subColumns);
            $pdf->SetFont('Arial', '', 7);

            foreach ($bundle['rows'] as $row) {
                $row_prod_id = $g['product_abbrev'] ?? '';
                $row_text    = !empty($row['note']) ? 'Note: ' . $row['note'] : '';

                $product_unit_price = floatval($product_details['unit_price'] ?? 0);
                $color_id = $row_product['custom_color'] ?? '';
                $grade    = $row_product['custom_grade'] ?? '';
                $gauge    = $row_product['custom_gauge'] ?? '';

                $unit_price = calculateUnitPrice(
                    $product_unit_price,
                    $row['ft'],
                    $row['in'],
                    $row['panel_type'],
                    0,
                    0,
                    0,
                    $color_id,
                    $grade,
                    $gauge
                );

                $subRow = [
                    $row_prod_id,
                    $row_text,
                    $row['qty'],
                    '',
                    '',
                    $row['ft'],
                    '',
                    
                ];
                renderSubRow($pdf, $subColumns, $subRow);
            }
        }
    }

    return [$totalPrice, $totalQty, $totalActual];
}

function renderLumberCategory($pdf, $products, $conn) {
    global $columns;

    $grouped = [];

    foreach ($products as $row_product) {
        $productid   = $row_product['productid'];
        $bundle_name = $row_product['bundle_id'];

        $product_details = getProductDetails($productid);
        $profile_details = getProfileTypeDetails($row_product['custom_profile']);
        $grade_details   = getGradeDetails($row_product['custom_grade']);
        $gauge_details   = getGaugeDetails($row_product['custom_gauge']);

        $quantity   = floatval($row_product['quantity'] ?? 0);
        $act_price  = floatval($row_product['actual_price'] ?? 0);
        $disc_price = floatval($row_product['discounted_price'] ?? 0);
        $note       = trim($row_product['note'] ?? '');

        $ft  = floatval($row_product['custom_length'] ?? 0);
        $in  = floatval($row_product['custom_length2'] ?? 0);
        $len = $ft + ($in / 12);

        $product_abbrev = $row_product['product_id_abbrev'] ?? '';

        $key = $productid;

        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'product_name'     => $row_product['product_item'],
                'product_abbrev'   => $product_abbrev,
                'color'            => getColorName($row_product['custom_color']),
                'grade'            => $grade_details['grade_abbreviations'] ?? '',
                'gauge'            => $gauge_details['gauge_abbreviations'] ?? '',
                'profile'          => $profile_details['profile_type'] ?? '',
                'quantity'         => 0,
                'length'           => 0,
                'discounted_total' => 0,
                'actual_total'     => 0,
                'bundles'          => []
            ];
        }

        $grouped[$key]['quantity']         += $quantity;
        $grouped[$key]['length']           += $len;
        $grouped[$key]['discounted_total'] += $disc_price;
        $grouped[$key]['actual_total']     += $act_price;

        if (!isset($grouped[$key]['bundles'][$bundle_name])) {
            $grouped[$key]['bundles'][$bundle_name] = [
                'bundle_name' => $bundle_name,
                'rows'        => []
            ];
        }

        $grouped[$key]['bundles'][$bundle_name]['rows'][] = [
            'qty'         => $quantity,
            'ft'          => $ft,
            'in'          => $in,
            'inch_text'   => decimalToFractionInch($in),
            'panel_type'  => '',
            'panel_style' => '',
            'disc_price'  => $disc_price,
            'act_price'   => $act_price,
            'note'        => $note,
        ];
    }

    $totalQty    = 0;
    $totalPrice  = 0;
    $totalActual = 0;

    foreach ($grouped as $g) {
        $summaryRow = [
            $g['product_abbrev'],
            $g['product_name'],
            $g['color'],
            $g['grade'],
            $g['gauge'],
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
            $bundle_header = $bundle['bundle_name'] ?: '';

            $subColumns = [
                ['label' => '',              'width' => 30, 'align' => 'C'],
                ['label' => $bundle_header,  'width' => 30, 'align' => 'C'],
                ['label' => 'Qty',           'width' => 20, 'align' => 'C'],
                ['label' => 'Qty in Pack',            'width' => 15, 'align' => 'C'],
                ['label' => 'Pack Size',            'width' => 15, 'align' => 'C'],
                ['label' => '',    'width' => 15, 'align' => 'C'],
                ['label' => '',   'width' => 15, 'align' => 'C'],
                
            ];

            renderTableHeader($pdf, $subColumns);
            $pdf->SetFont('Arial', '', 7);

            foreach ($bundle['rows'] as $row) {
                $row_prod_id = $g['product_abbrev'] ?? '';
                $row_text    = !empty($row['note']) ? 'Note: ' . $row['note'] : '';

                $product_unit_price = floatval($product_details['unit_price'] ?? 0);
                $color_id = $row_product['custom_color'] ?? '';
                $grade    = $row_product['custom_grade'] ?? '';
                $gauge    = $row_product['custom_gauge'] ?? '';

                $unit_price = calculateUnitPrice(
                    $product_unit_price,
                    $row['ft'],
                    $row['in'],
                    $row['panel_type'],
                    0,
                    0,
                    0,
                    $color_id,
                    $grade,
                    $gauge
                );

                $subRow = [
                    $row_prod_id,
                    $row_text,
                    $row['qty'],
                    $row['ft'],
                    
                    
                ];
                renderSubRow($pdf, $subColumns, $subRow);
            }
        }
    }

    return [$totalPrice, $totalQty, $totalActual];
}

function renderDefaultCategory($pdf, $products, $conn) {
    global $columns;

    $grouped = [];

    foreach ($products as $row_product) {
        $productid   = $row_product['productid'];
        $bundle_name = $row_product['bundle_id'];

        $product_details = getProductDetails($productid);
        $profile_details = getProfileTypeDetails($row_product['custom_profile']);
        $grade_details   = getGradeDetails($row_product['custom_grade']);
        $gauge_details   = getGaugeDetails($row_product['custom_gauge']);

        $quantity   = floatval($row_product['quantity'] ?? 0);
        $act_price  = floatval($row_product['actual_price'] ?? 0);
        $disc_price = floatval($row_product['discounted_price'] ?? 0);
        $note       = trim($row_product['note'] ?? '');

        $ft  = floatval($row_product['custom_length'] ?? 0);
        $in  = floatval($row_product['custom_length2'] ?? 0);
        $len = $ft + ($in / 12);

        $product_abbrev = $row_product['product_id_abbrev'] ?? '';

        $key = $productid;

        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'product_name'     => $row_product['product_item'],
                'product_abbrev'   => $product_abbrev,
                'color'            => getColorName($row_product['custom_color']),
                'grade'            => $grade_details['grade_abbreviations'] ?? '',
                'gauge'            => $gauge_details['gauge_abbreviations'] ?? '',
                'profile'          => $profile_details['profile_type'] ?? '',
                'quantity'         => 0,
                'length'           => 0,
                'discounted_total' => 0,
                'actual_total'     => 0,
                'bundles'          => []
            ];
        }

        $grouped[$key]['quantity']         += $quantity;
        $grouped[$key]['length']           += $len;
        $grouped[$key]['discounted_total'] += $disc_price;
        $grouped[$key]['actual_total']     += $act_price;

        if (!isset($grouped[$key]['bundles'][$bundle_name])) {
            $grouped[$key]['bundles'][$bundle_name] = [
                'bundle_name' => $bundle_name,
                'rows'        => []
            ];
        }

        $grouped[$key]['bundles'][$bundle_name]['rows'][] = [
            'qty'         => $quantity,
            'ft'          => $ft,
            'in'          => $in,
            'inch_text'   => decimalToFractionInch($in),
            'panel_type'  => '',
            'panel_style' => '',
            'disc_price'  => $disc_price,
            'act_price'   => $act_price,
            'note'        => $note,
        ];
    }

    $totalQty    = 0;
    $totalPrice  = 0;
    $totalActual = 0;

    foreach ($grouped as $g) {
        $summaryRow = [
            $g['product_abbrev'],
            $g['product_name'],
            $g['color'],
            $g['grade'],
            $g['gauge'],
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
            $bundle_header = $bundle['bundle_name'] ?: '';

            $subColumns = [
                ['label' => '',              'width' => 30, 'align' => 'C'],
                ['label' => $bundle_header,  'width' => 30, 'align' => 'C'],
                ['label' => 'Qty',           'width' => 20, 'align' => 'C'],
                ['label' => 'Qty in Pack',            'width' => 15, 'align' => 'C'],
                ['label' => 'Pack Size',            'width' => 15, 'align' => 'C'],
                ['label' => '',    'width' => 15, 'align' => 'C'],
                ['label' => '',   'width' => 15, 'align' => 'C'],
                
            ];

            renderTableHeader($pdf, $subColumns);
            $pdf->SetFont('Arial', '', 7);

            foreach ($bundle['rows'] as $row) {
                $row_prod_id = $g['product_abbrev'] ?? '';
                $row_text    = !empty($row['note']) ? 'Note: ' . $row['note'] : '';

                $product_unit_price = floatval($product_details['unit_price'] ?? 0);
                $color_id = $row_product['custom_color'] ?? '';
                $grade    = $row_product['custom_grade'] ?? '';
                $gauge    = $row_product['custom_gauge'] ?? '';

                $unit_price = calculateUnitPrice(
                    $product_unit_price,
                    $row['ft'],
                    $row['in'],
                    $row['panel_type'],
                    0,
                    0,
                    0,
                    $color_id,
                    $grade,
                    $gauge
                );

                $subRow = [
                    $row_prod_id,
                    $row_text,
                    $row['qty'],
                    $row['ft'],
                    '',
                    
                    
                ];
                renderSubRow($pdf, $subColumns, $subRow);
            }
        }
    }

    return [$totalPrice, $totalQty, $totalActual];
}

function renderRow($pdf, $columns, $row, $bold = false) {
    $pdf->SetFont('Arial', $bold ? 'B' : '', 8);
    $lineHeight = 4.2;

    $cellHeights = [];
    foreach ($row as $i => $cell) {
        $cellHeights[$i] = NbLines($pdf, $columns[$i]['width'], $cell) * $lineHeight;
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
        $pdf->MultiCell($w, $lineHeight, $cell, 0, $columns[$i]['align']);
        $pdf->SetXY($xBefore + $w, $yBefore);
    }

    $pdf->Ln($maxHeight);
}

function renderSubRow($pdf, $columns, $row, $bold = false) {
    $pdf->SetFont('Arial', $bold ? 'B' : '', 8);
    $lineHeight = 4.2;

    $cellHeights = [];
    foreach ($row as $i => $cell) {
        $cellHeights[$i] = NbLines($pdf, $columns[$i]['width'], $cell) * $lineHeight;
    }
    $maxHeight = max($cellHeights);

    $x = $pdf->GetX();
    $y = $pdf->GetY();

    foreach ($row as $i => $cell) {
        $w = $columns[$i]['width'];
        $h = $maxHeight;
        $xBefore = $pdf->GetX();
        $yBefore = $pdf->GetY();

        $xStart = $pdf->GetX();
        $pdf->MultiCell($w, $lineHeight, $cell, 0, $columns[$i]['align']);
        $pdf->SetXY($xStart, $yBefore + $h);
        $pdf->Cell($w, 0, '', 'B');
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
        $this->SetY(-40);

        $colWidth = ($this->w - 2 * $marginLeft) / 3;

        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 51, 153);
        $this->Cell($this->w - 2 * $marginLeft, 5, 'We appreciate your continued business with East Kentucky Metal!', 0, 1, 'C');

        $this->SetTextColor(0, 0, 0);
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

        $yStart = $this->GetY() - 35;
        $this->SetFont('Arial', '', 10);
        $this->SetXY($marginLeft, $yStart);
        $this->MultiCell($colWidthRight, 5,
            "Scan me for a Digtal copy of this receipt", 0, 'C');

        $qrX = 20;
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
$pdf->SetAutoPageBreak(true, 40);


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
        $pdf->Cell($mailToWidth+10, 7, 'Bill to:', 0, 0, 'L', true);

        $pdf->SetXY($col2_x, $currentY);
        $pdf->Cell($mailToWidth-10, 7, 'Ship to:', 0, 0, 'L', true);

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
        if (!empty($customerDetails['contact_phone'])) $leftText .= $customerDetails['contact_phone']."\n";
        $leftText .= 'Customer PO #: '.$row_orders['job_po'];

        $pdf->SetFont('Arial', '', 10);
        $leftStartY = $pdf->GetY();
        $pdf->MultiCell($mailToWidth, 5, $leftText, 0, 'L');
        $leftHeight = $pdf->GetY() - $leftStartY;

        $rightX = $col2_x;
        $rightY = $def_y;
        $pdf->SetXY($rightX, $rightY);

        $rightText = trim($row_orders['deliver_fname'].' '.$row_orders['deliver_lname'])."\n";
        $shipAddressParts = [];
        if (!empty($row_orders['deliver_address'])) $shipAddressParts[] = $row_orders['deliver_address'];
        if (!empty($row_orders['deliver_city'])) $shipAddressParts[] = $row_orders['deliver_city'];
        if (!empty($row_orders['deliver_state'])) $shipAddressParts[] = $row_orders['deliver_state'];
        if (!empty($row_orders['deliver_zip'])) $shipAddressParts[] = $row_orders['deliver_zip'];
        if (!empty($shipAddressParts)) $rightText .= implode(', ', $shipAddressParts)."\n";
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
    
        renderTableHeader($pdf, $columns);

        foreach ($productsByCategory as $categoryId => $products) {
            if (!empty($pricing_id)) {
                if ($pricing_id == 1) {
                    foreach ($products as &$prod) {
                        $tmp = $prod['discounted_price'];
                        $prod['discounted_price'] = $prod['actual_price'];
                        $prod['actual_price'] = $tmp;
                    }
                    unset($prod);
                } /* else {
                    $customer_details_pricing = $customerDetails['customer_pricing'];
                    $customer_pricing = getPricingCategory($categoryId, $pricing_id) / 100;

                    foreach ($products as &$prod) {
                        $prod['discounted_price'] = $prod['discounted_price'] * (1 - $customer_pricing);
                    }
                    unset($prod);
                } */
            }
            

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
        $pdf->Cell(28, $lineheight, '$ ' . number_format(max(0, $total_saved), 2), 1, 1, 'R', true);

        $pdf->Ln(5);

        $col1_x = 10;
        $col2_x = 140;
        $col_y = $pdf->GetY();

        

        $pdf->SetFont('Arial', '', 10);

        $disclaimer = "Customer is solely responsible for accuracy of order and for verifying accuracy of materials before leaving EKMS or at time of delivery. If an agent orders or takes materials on customer's behalf, EKMS is entitled to rely upon the agent as if s/he has full authority to act on customer's behalf. No returns on metal panels or special trim. All other materials returned undamaged within 60 days of invoice date are subject to a restocking fee equal to 25% of current retail price.";

        $savings_note = "*Customer Savings represent your savings on this Order by being an EKM Member.*";

        $disclaimerHeight = $pdf->GetMultiCellHeight(120, 4, $disclaimer); 

        $savingsHeight = 0;
        if ($total_saved > 0) {
            $savingsHeight = $pdf->GetMultiCellHeight(120, 4, $savings_note) + 3;
        }

        $lineheight = 6;
        $summaryHeight = (5 * $lineheight) + 2;

        $blockHeight = $disclaimerHeight + $savingsHeight + $summaryHeight;
        if ($pdf->GetY() + $blockHeight > $pdf->GetPageHeight() - 20) {
            $pdf->AddPage();
            $col_y = $pdf->GetY();
        } else {
            $col_y = $pdf->GetY();
        }

        $pdf->SetXY($col1_x, $col_y);
        $pdf->MultiCell(120, 4, $disclaimer, 0, 'L');

        if ($total_saved > 0) {
            $pdf->Ln(3);
            $pdf->MultiCell(120, 4, $savings_note, 0, 'L');
        }

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
