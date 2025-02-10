<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$table = 'product_duplicate';

if ($_REQUEST['action'] == "upload_excel") {
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === 0) {
        $filePath = $_FILES['excel_file']['tmp_name'];

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $columnMapping = [
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
        ];

        foreach ($worksheet->getRowIterator() as $rowIndex => $row) {
            if ($rowIndex === 1) continue;
            $rowData = [];

            foreach ($row->getCellIterator() as $cell) {
                $columnLetter = $cell->getColumn();
                
                if (isset($columnMapping[$columnLetter])) {
                    $dbColumn = $columnMapping[$columnLetter];
                    $cellValue = $cell->getValue();

                    if ($columnLetter === 'X') { //product_system
                        $query = "SELECT product_system_id FROM product_system WHERE product_system LIKE '%$cellValue%'";
                        $result = mysqli_query($conn, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);
                            $cellValue = $row['product_system_id'];
                        }
                    }
                    
                    if ($columnLetter === 'Z') { //category
                        $cellValue = '4'; //4 = TRIM
                    }

                    if ($columnLetter === 'AB') { //product_line
                        $query = "SELECT product_line_id FROM product_line WHERE product_line LIKE '%$cellValue%'";
                        $result = mysqli_query($conn, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);
                            $cellValue = $row['product_line_id'];
                        }
                    }

                    if ($columnLetter === 'AD') { //product_type
                        $query = "SELECT product_type_id FROM product_type WHERE product_type LIKE '%$cellValue%'";
                        $result = mysqli_query($conn, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);
                            $cellValue = $row['product_type_id'];
                        }
                    }

                    if ($columnLetter === 'BA') { //manufactured or sourced
                        $cellValue = (strtolower($cell->getValue() ?? '') == 'manufactured' ? '2' : '1'); //2 = manufactured
                    }

                    if ($columnLetter === 'BB') { // Supplier
                        $cellValue = strtolower($cell->getValue() ?? '');
                        $invalidValues = ['manufactured', 'n/a', '#n/a'];
                    
                        $ekm_id = 5; //from database
                        $cellValue = in_array($cellValue, $invalidValues) ? $ekm_id : $cellValue; // If in list, make empty; otherwise, set to '1'

                        if($cellValue != $ekm_id){

                        }
                        $query = "SELECT supplier_id FROM supplier WHERE supplier_name LIKE '%$cellValue%'";
                        $result = mysqli_query($conn, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);
                            $cellValue = $row['supplier_id'];
                        }
                    }

                    if ($columnLetter === 'BP') { //bends
                        $cellValue = floatval($cell->getValue());
                    }

                    if ($columnLetter === 'BV') { //hems
                        $cellValue = floatval($cell->getValue());
                    }

                    if ($columnLetter === 'BP') { //hemming machine
                        $cellValue = (strtolower($cell->getValue() ?? '') == 'yes' ? '1' : '0');
                    }

                    if ($columnLetter === 'BV') { //trim rollformer
                        $cellValue = (strtolower($cell->getValue() ?? '') == 'yes' ? '1' : '0');
                    }

                    if ($columnLetter === 'BV') { //cost_per_square_inch
                        $cellValue = floatval($cell->getValue());
                    }
            
                    $rowData[$dbColumn] = mysqli_real_escape_string($conn, $cellValue !== null ? (string) $cellValue : '');
                }
            }

            if (empty($rowData)) {
                continue;
            }

            $dbColumns = implode(", ", array_keys($rowData));
            $dbValues = "'" . implode("', '", $rowData) . "'";

            $sql = "INSERT INTO $table ($dbColumns) VALUES ($dbValues)";

            if (!mysqli_query($conn, $sql)) {
                echo "Error inserting row $rowIndex: " . mysqli_error($conn) . "<br>";
            }
        }

        echo "success";
    } else {
        echo "Error: No file uploaded or file upload failed.";
    }
}

if (isset($conn)) {
    mysqli_close($conn);
}
?>
