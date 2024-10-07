<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require 'includes/fpdf/fpdf.php';
require 'includes/dbconn.php';
require 'includes/functions.php';

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

$pdf->SetMargins(10, 10, 10);

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

$session = $_SESSION['orders'];

$itemWidth = 60;
$colorWidth = 40; 
$categoryWidth = 30; 
$widthWidth = 33;
$lengthWidth = 33;

// Set up column headers
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($itemWidth, 10, 'Item', 0);
$pdf->Cell($colorWidth, 10, 'Color', 0);
$pdf->Cell($categoryWidth, 10, 'Category', 0);
$pdf->Cell($widthWidth, 10, 'Width', 0);
$pdf->Cell($lengthWidth, 10, 'Length', 0);
$pdf->Ln();

// Orders content
$pdf->SetFont('Arial', '', 10);
foreach ($session as $item) {
    if (isset($item['product_id'])) {
        $product_details = getProductDetails($item['product_id']);
        $pdf->Cell($itemWidth, 10, $product_details['product_item'], 0);
        $pdf->Cell($colorWidth, 10, getColorName($product_details['color']), 0);
        $pdf->Cell($categoryWidth, 10, getProductCategoryName($product_details['product_category']), 0);
        $pdf->Cell($widthWidth, 10, $product_details['width'], 0);
        $pdf->Cell($lengthWidth, 10, $product_details['length'], 0);
    } elseif (isset($item['coil_id'])) {
        $coil_details = getCoilDetails($item['coil_id']);
        $pdf->Cell($itemWidth, 10, $coil_details['coil'], 0);
        $pdf->Cell($colorWidth, 10, getColorName($coil_details['color']), 0);
        $pdf->Cell($categoryWidth, 10, getProductCategoryName($coil_details['category']), 0);
        $pdf->Cell($widthWidth, 10, $coil_details['width'], 0);
        $pdf->Cell($lengthWidth, 10, $coil_details['length'], 0);
    }
    $pdf->Ln();
}

// Output PDF
$pdf->Output('I', 'Orders.pdf');
?>
