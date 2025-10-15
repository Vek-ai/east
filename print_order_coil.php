<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

if (!isset($_SESSION['userid'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?redirect=$redirect_url");
    exit();
}

require 'includes/fpdf/fpdf.php';
require 'includes/dbconn.php';
require 'includes/functions.php';

class PDF extends FPDF {
    function Footer() {
        $marginLeft = 10;
        $this->SetY(-15);

        $colWidth = ($this->w - 2 * $marginLeft) / 3;

        $this->SetFont('Arial', '', 9);

        $this->SetX($marginLeft);
        $this->Cell($colWidth, 5, 'Phone: (606) 877-1848 | Fax: (606) 864-4280', 0, 0, 'L');

        $this->SetX($marginLeft + $colWidth + 10);
        $this->Cell($colWidth, 5, 'Sales@EastKentuckyMetal.com', 0, 0, 'C');

        $this->SetX($marginLeft + 2 * $colWidth);
        $this->Cell($colWidth, 5, 'EastKentuckMetal.com', 0, 0, 'R');
    }
}

$pdf = new PDF();
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

$pdf->SetMargins(10, 10, 10);



if(!empty($_SESSION['orders'])){
    $session = $_SESSION['orders'];

    $pdf->Cell(0, 10, 'East Kentucky Metal', 0, 1, 'C');
    $pdf->Ln(10);

    $supplier_details = getSupplierDetails($_SESSION['supplier_id']);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->MultiCell(0, 5, $supplier_details['supplier_name']);
    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 5, 'Contact Name: ' . $supplier_details['contact_name']);
    $pdf->MultiCell(0, 5, 'Contact Number: ' . $supplier_details['contact_number']);
    if (!empty($supplier_details['contact_fax'])) {
        $pdf->MultiCell(0, 5, 'Contact Fax: ' . $supplier_details['contact_fax']);
    }
    $pdf->MultiCell(0, 5, 'Address: ' . $supplier_details['address'] . ', ' . $supplier_details['city'] . ', ' . $supplier_details['state'] . ', ' . $supplier_details['zip']);
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Orders', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);



    $itemWidth = 45;
    $colorWidth = 35; 
    $categoryWidth = 23; 
    $widthWidth = 22;
    $lengthWidth = 25;
    $qtyWidth = 15;
    $priceWidth = 26;

    // Set up column headers
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($itemWidth, 10, 'Item', 0);
    $pdf->Cell($colorWidth, 10, 'Color', 0);
    $pdf->Cell($categoryWidth, 10, 'Category', 0);
    $pdf->Cell($widthWidth, 10, 'Width', 0);
    $pdf->Cell($lengthWidth, 10, 'Length', 0);
    $pdf->Cell($qtyWidth, 10, 'Qty', 0);
    $pdf->Cell($priceWidth, 10, 'Price', 0);
    $pdf->Ln();

    // Orders content
    $pdf->SetFont('Arial', '', 10);
    $ttl_price = 0;
    $ttl_quantity = 0;
    foreach ($session as $item) {
        if (isset($item['product_id'])) {
            $product_details = getProductDetails($item['product_id']);
            $ttl_price += $product_details['unit_price'] * $item['quantity_cart'];
            $ttl_quantity += $item['quantity_cart'];
            $pdf->Cell($itemWidth, 10, $product_details['product_item'], 0);
            $pdf->Cell($colorWidth, 10, getColorName($product_details['color']), 0);
            $pdf->Cell($categoryWidth, 10, getProductCategoryName($product_details['product_category']), 0);
            $pdf->Cell($widthWidth, 10, $product_details['width'], 0);
            $pdf->Cell($lengthWidth, 10, $product_details['length'], 0);
            $pdf->Cell($qtyWidth, 10, $item['quantity_cart'], 0);
            $pdf->Cell($priceWidth, 10, number_format($product_details['unit_price'] * $item['quantity_cart'],2), 0);
        } elseif (isset($item['coil_id'])) {
            $coil_details = getCoilDetails($item['coil_id']);
            $ttl_quantity += $item['quantity_cart'];
            $pdf->Cell($itemWidth, 10, $coil_details['coil'], 0);
            $pdf->Cell($colorWidth, 10, getColorName($coil_details['color']), 0);
            $pdf->Cell($categoryWidth, 10, getProductCategoryName($coil_details['category']), 0);
            $pdf->Cell($widthWidth, 10, $coil_details['width'], 0);
            $pdf->Cell($lengthWidth, 10, $coil_details['length'], 0);
            $pdf->Cell($qtyWidth, 10, $item['quantity_cart'], 0);
            $pdf->Cell($priceWidth, 10, number_format(0,2), 0);
        }
        $pdf->Ln();
    }
    $pdf->Cell($itemWidth+$colorWidth+$categoryWidth+$widthWidth+$lengthWidth, 10, 'Total', 0, 0, 'C');
    $pdf->Cell($qtyWidth, 10, $ttl_quantity, 0);
    $pdf->Cell($priceWidth, 10, $ttl_price, 0);
}else{
    $pdf->Cell(0, 10, 'No Products Added to cart', 0, 1, 'C');
}

// Output PDF
$pdf->Output('I', 'Orders.pdf');

?>
