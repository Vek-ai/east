<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

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
        'product_id', 'product_sku', 'coil_part_no', 'price_1', 'price_2', 'price_3', 'price_4', 'price_5', 'price_6', 'price_7',
        'product_category', 'product_line', 'product_type', 'product_system', 'product_item', 'stock_type', 'description',
        'material', 'dimensions', 'thickness', 'gauge', 'grade', 'color', 'color_code', 'paint_provider', 'color_group',
        'warranty_type', 'coating', 'profile', 'width', 'bends', 'hems', 'hemming_machine', 'trim_rollformer',
        'cost_per_hem', 'cost_per_bend', 'cost_per_square_inch', 'coil_width', 'length', 'weight', 'quantity_in_stock',
        'quantity_quoted', 'quantity_committed', 'quantity_available', 'quantity_in_transit', 'unit_price', 'date_added',
        'date_modified', 'last_ordered_date', 'last_sold_date', 'supplier_id', 'supplier_sku', 'upc', 'unit_of_measure',
        'coil_id', 'coil_qty', 'unit_gross_margin', 'unit_cost', 'comment', 'product_usage', 'sold_by_feet',
        'standing_seam', 'board_batten', 'correlated_product_id', 'smartbuild_id', 'status', 'hidden', 'main_image',
        'product_origin', 'product_base', 'product_code'
    ];

    $col = 1;
    foreach ($headers as $header) {
        $columnLetter = Coordinate::stringFromColumnIndex($col);
        $sheet->setCellValue($columnLetter . '1', $header);
        $col++;
    }

    $row = 2;
    while ($data = $result->fetch_assoc()) {
        $col = 1;
        foreach ($headers as $header) {
            $columnLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue($columnLetter . $row, $data[$header] ?? '');
            $col++;
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
