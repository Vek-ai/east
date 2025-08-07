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

use PhpOffice\PhpSpreadsheet\IOFactory;

$table = 'test';
//4 = TRIM
$trim_id = 4;
$category_id = 4;
 

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
            'CM' => 'coil_width',
        ];

        $truncateSql = "TRUNCATE TABLE $table";
        if (!mysqli_query($conn, $truncateSql)) {
            echo "Error truncating table: " . mysqli_error($conn) . "<br>";
            exit();
        }

        foreach ($worksheet->getRowIterator() as $rowIndex => $row) {
            if ($rowIndex === 1) continue;
            $rowData = [];

            $colorPrice = 0;
            $coil_width = 0;
            $width = 0;

            foreach ($row->getCellIterator() as $cell) {
                $columnLetter = $cell->getColumn();
                
                if (isset($columnMapping[$columnLetter])) {
                    $dbColumn = $columnMapping[$columnLetter];
                    $cellValue = $cell->getValue() ?? '';
                    

                    if ($dbColumn === 'color') { //color
                        if(strtolower($cellValue) == 'multi'){
                            $query = "SELECT * FROM product_color WHERE product_category = '$category_id' ORDER BY RAND() LIMIT 1";
                            $result = mysqli_query($conn, $query);

                            if ($result && mysqli_num_rows($result) > 0) {
                                $row = mysqli_fetch_assoc($result);
                                $cellValue = $row['id'];
                                $colorPrice = $row['price'];
                            }
                        }else{
                            $query = "SELECT * FROM product_color WHERE color_name LIKE '%$cellValue%'";
                            $result = mysqli_query($conn, $query);
                            if ($result && mysqli_num_rows($result) > 0) {
                                $row = mysqli_fetch_assoc($result);
                                $cellValue = $row['id'];
                            }
                        }
                    }

                    if ($dbColumn === 'product_system') { //product_system
                        $query = "SELECT product_system_id FROM product_system WHERE product_system LIKE '%$cellValue%'";
                        $result = mysqli_query($conn, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);
                            $cellValue = $row['product_system_id'];
                        }
                    }
                    
                    if ($dbColumn === 'product_category') { //category
                        $cellValue = $category_id; //4 = TRIM
                    }

                    if ($dbColumn === 'product_line') { //product_line
                        $query = "SELECT product_line_id FROM product_line WHERE product_line LIKE '%$cellValue%'";
                        $result = mysqli_query($conn, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);
                            $cellValue = $row['product_line_id'];
                        }
                    }

                    if ($dbColumn === 'product_type') { //product_type
                        $query = "SELECT product_type_id FROM product_type WHERE product_type LIKE '%$cellValue%'";
                        $result = mysqli_query($conn, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);
                            $cellValue = $row['product_type_id'];
                        }
                    }

                    if ($dbColumn === 'product_origin') { //manufactured or sourced
                        $cellValue = (strtolower($cell->getValue() ?? '') == 'manufactured' ? '2' : '1'); //2 = manufactured
                    }

                    if ($dbColumn === 'supplier_id') { // supplier_id
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

                    if ($dbColumn === 'bends') { //bends
                        $cellValue = floatval($cell->getValue());
                    }

                    if ($dbColumn === 'hems') { //hems
                        $cellValue = floatval($cell->getValue());
                    }

                    if ($dbColumn === 'hemming_machine') { //hemming_machine
                        $cellValue = (strtolower($cell->getValue() ?? '') == 'yes' ? '1' : '0');
                    }

                    if ($dbColumn === 'trim_rollformer') { //trim_rollformer
                        $cellValue = (strtolower($cell->getValue() ?? '') == 'yes' ? '1' : '0');
                    }

                    if ($dbColumn === 'coil_width') { //coil_width
                        $cellValue = floatval($cell->getValue());
                        $coil_width = $cellValue;
                    }

                    if ($dbColumn === 'width') { //coil_width
                        $cellValue = floatval($cell->getValue());
                        $width = $cellValue;
                    }
            
                    $rowData[$dbColumn] = mysqli_real_escape_string($conn, $cellValue !== null ? (string) $cellValue : '');
                }     

            }

            if (empty($rowData)) {
                continue;
            }

            if($category_id = $trim_id){
                $cost_per_square_inch = 0;

                if ($coil_width > 0) {
                    $cost_per_square_inch = ($width / $coil_width) * $colorPrice;
                }

                $rowData['cost_per_square_inch'] = $cost_per_square_inch;
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

if ($_REQUEST['action'] == "save_table") {
    $table = "product_duplicate";

    $columnsSql = "SHOW COLUMNS FROM test";
    $columnsResult = $conn->query($columnsSql);

    $columns = [];
    while ($row = $columnsResult->fetch_assoc()) {
        if ($row['Field'] !== 'product_id') {
            $columns[] = $row['Field'];
        }
    }

    $columnsList = implode(", ", $columns);

    $sql = "INSERT INTO $table ($columnsList) SELECT $columnsList FROM test";

    if ($conn->query($sql) === TRUE) {
        echo "Data has been successfully saved";

        $truncateSql = "TRUNCATE TABLE test";
        if ($conn->query($truncateSql) !== TRUE) {
            echo " but failed to clear test table: " . $conn->error;
        }
    } else {
        echo "Error: " . $conn->error;
    }
}

if (isset($conn)) {
    mysqli_close($conn);
}
?>
