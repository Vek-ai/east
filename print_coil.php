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

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

if (!empty($_REQUEST['id'])) {

    $marginLeft = 10;
    $marginRight = 10;
    $pageWidth = $pdf->GetPageWidth();
    $usableWidth = $pageWidth - $marginLeft - $marginRight;

    $pdf->Image('assets/images/logo-bw.png', $marginLeft, 6, 60, 20);

    $pdf->SetXY($marginLeft, 26);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, '977 E Hal Rogers Parkway', 0, 1);
    $pdf->Cell(0, 5, 'London, KY 40741', 0, 1);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 5, 'Phone: (606) 877-1848 | Fax: (606) 864-4280', 0, 1);
    $pdf->Cell(0, 5, 'Email: Sales@Eastkentuckymetal.com', 0, 1);
    $pdf->Cell(0, 5, 'Website: Eastkentuckymetal.com', 0, 1);

    $pdf->Ln(5);

    $pdf->SetTitle('Coil');
    $pdf->Output('coil.pdf', 'I');
}

?>
