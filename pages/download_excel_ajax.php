<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "download_excel") {
    $product_category = mysqli_real_escape_string($conn, $_REQUEST['category'] ?? '');

    $sql = "SELECT * FROM product_duplicate";
    if (!empty($product_category)) {
        $sql .= " WHERE product_category = '$product_category'";
    }
    $result = $conn->query($sql);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $headers = [
        'A' => 'InvID', 'B' => 'Coil/Part Number', 'C' => 'Description', 'D' => 'Color', 'E' => 'Qty', 'F' => 'Units', 'G' => 'Notes', 'H' => 'Price',
        'I' => 'Price2', 'J' => 'Price3', 'K' => 'Price4', 'L' => 'Price5', 'M' => 'Price6', 'N' => 'Price7', 'O' => 'Order', 'P' => 'ItemType', 'Q' => 'VendorID',
        'R' => 'Cost', 'S' => 'Blank 1', 'T' => 'Blank 2', 'U' => 'Color Options', 'V' => 'Length Options', 'W' => 'Product System Abreviations',
        'X' => 'Product Systems', 'Y' => 'Category Abreviations', 'Z' => 'Product Category', 'AA' => 'Line Abreviations', 'AB' => 'Product Line',
        'AC' => 'Type Abreviations', 'AD' => 'Product Type', 'AE' => 'Product Item', 'AF' => 'Product Code Abreviation', 'AG' => 'Item Abreviation',
        'AH' => 'COLOR CODE Table', 'AI' => 'Color Consolidation', 'AJ' => 'LENGTH/Size Table CODE', 'AK' => 'Size consolidation', 'AL' => 'Gauge',
        'AM' => 'Product Code', 'AN' => 'Concatenated', 'AO' => 'Product Description', 'AP' => 'Size Scrub', 'AQ' => 'Comments', 'AR' => 'Blank4',
        'AS' => 'Intent? Usage?', 'AT' => 'Width', 'AU' => '# of Hems', 'AV' => '# of Bends', 'AW' => 'Length', 'AX' => 'Thickness', 'AY' => 'What Size?',
        'AZ' => 'Stock or Special Order', 'BA' => 'Mfg or Purchased', 'BB' => 'SupplierName', 'BC' => 'Cost Example', 'BD' => 'Old System Description',
        'BE' => 'Category Use-Area (Cousin) (Marketing-Sales?)', 'BF' => 'Category Type-Category (2nd-Cousin) (Marketing-Sales?)',
        'BG' => 'Category Use-Application (3nd-Cousin) (Marketing-Sales?)', 'BH' => 'SPM1', 'BI' => 'SPM2', 'BJ' => 'SPM3', 'BK' => 'SPM4', 'BL' => 'SPM5',
        'BM' => 'SPM6', 'BN' => 'SPM7', 'BO' => 'Flat Sheet Width', 'BP' => 'Number of Bends', 'BQ' => 'Number of Hems', 'BR' => 'Hemming Machine',
        'BS' => 'Trim Rollformer', 'BT' => '$ per Hem', 'BU' => '$ Per Bend', 'BV' => '$ Per square inch', 'BW' => '', 'BX' => '', 'BY' => 'Actual Moving Cost',
        'BZ' => 'Manual Assigned Cost', 'CA' => 'Retail Pricing', 'CB' => '', 'CC' => 'Retail Pricing $/ft', 'CD' => 'Contractor Pricing (Level 2)',
        'CE' => 'Contractor Pricing (Level 3)', 'CF' => 'Contractor Pricing (Level 4)', 'CG' => 'Wholesale Pricing (Level 5)',
        'CH' => 'Wholesale Pricing (Level 6)', 'CI' => 'Penetration Pricing (Level 7)', 'CJ' => 'Retail', 'CK' => 'Contractor 1',
        'CL' => 'Contractor 2', 'CM' => 'Low Rib Coil Width', 'CN' => 'Number of Trim per sheet width', 'CO' => 'Drop', 'CP' => 'Ft or Each',
        'CQ' => 'Flat Sheet Length converted to Inches', 'CR' => '', 'CS' => '', 'CT' => 'Square Inches per piece', 'CU' => 'Full Flat Stock Width',
        'CV' => "$'s per square Inch", 'CW' => '$ Per Piece', 'CX' => '$ per piece', 'CY' => 'Flat Sheet Length (Ft)', 'CZ' => '', 'DA' => '',
        'DB' => 'Square Inches per piece', 'DC' => '10', 'DD' => '12', 'DE' => '14', 'DF' => '16', 'DG' => '18', 'DH' => '20', 'DI' => '24',
        'DJ' => 'Full Flat Stock Width', 'DK' => "#'s per square Inch", 'DL' => '# of Pieces per sheet', 'DM' => 'Weight per piece'
    ];

    foreach ($headers as $columnLetter => $headerName) {
        $sheet->setCellValue($columnLetter . '1', $headerName);
    }

    // Column Mapping
    $columnMapping = [
        'A' => 'product_id',
        'B' => 'coil_part_no',
        'D' => 'color',
        'E' => 'quantity_in_stock',
        'F' => 'unit_of_measure',
        'H' => 'price_1',
        'I' => 'price_2',
        'J' => 'price_3',
        'K' => 'price_4',
        'L' => 'price_5',
        'M' => 'price_6',
        'N' => 'price_7',
        'X' => 'product_system',
        'Z' => 'product_category',
        'AB' => 'product_line',
        'AD' => 'product_type',
        'AE' => 'product_item',
        'AL' => 'gauge',
        'AM' => 'product_code',
        'AO' => 'description',
        'AQ' => 'comment',
        'AS' => 'product_usage',
        'BO' => 'width',
        'AW' => 'length',
        'AX' => 'thickness',
        'AZ' => 'stock_type',
        'BA' => 'product_origin',
        'BB' => 'supplier_id',
        'BP' => 'bends',
        'BQ' => 'hems',
        'BR' => 'hemming_machine',
        'BS' => 'trim_rollformer',
        'BT' => 'cost_per_hem',
        'BU' => 'cost_per_bend',
        'BV' => 'cost_per_square_inch',
        'CM' => 'coil_width',
    ];

    $row = 2;
    while ($data = $result->fetch_assoc()) {
        foreach ($columnMapping as $columnLetter => $dbColumn) {
            $sheet->setCellValue($columnLetter . $row, $data[$dbColumn] ?? '');
        }
        $row++;
    }

    $filename = "products.xlsx";
    $filePath = $filename;

    $writer = new Xlsx($spreadsheet);
    $writer->save($filePath);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: max-age=0');

    readfile($filePath);

    unlink($filePath);
    exit;
}

if (isset($conn)) {
    mysqli_close($conn);
}
?>
