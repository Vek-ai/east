<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = 'customer';
$test_table = 'customer_excel';
$main_primary_key = getPrimaryKey($table);

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
        $customer_first_name = mysqli_real_escape_string($conn, $_POST['customer_first_name']);
        $customer_last_name = mysqli_real_escape_string($conn, $_POST['customer_last_name']);
        $customer_business_name = mysqli_real_escape_string($conn, $_POST['customer_business_name']);
        $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email']);
        $contact_phone = mysqli_real_escape_string($conn, $_POST['contact_phone']);
        $primary_contact = mysqli_real_escape_string($conn, $_POST['primary_contact']);
        $contact_fax = mysqli_real_escape_string($conn, $_POST['contact_fax']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $city = mysqli_real_escape_string($conn, $_POST['city']);
        $state = mysqli_real_escape_string($conn, $_POST['state']);
        $zip = mysqli_real_escape_string($conn, $_POST['zip']);
        $lat = mysqli_real_escape_string($conn, isset($_POST['lat']) ? $_POST['lat'] : '');
        $lng = mysqli_real_escape_string($conn, isset($_POST['lng']) ? $_POST['lng'] : '');
        $secondary_contact_name = mysqli_real_escape_string($conn, $_POST['secondary_contact_name']);
        $secondary_contact_phone = mysqli_real_escape_string($conn, $_POST['secondary_contact_phone']);
        $ap_contact_name = mysqli_real_escape_string($conn, $_POST['ap_contact_name']);
        $ap_contact_email = mysqli_real_escape_string($conn, $_POST['ap_contact_email']);
        $ap_contact_phone = mysqli_real_escape_string($conn, $_POST['ap_contact_phone']);
        $tax_status = mysqli_real_escape_string($conn, $_POST['tax_status']);
        $tax_exempt_number = mysqli_real_escape_string($conn, $_POST['tax_exempt_number']);
        $customer_notes = mysqli_real_escape_string($conn, $_POST['customer_notes']);
        $new_customer_type_id = mysqli_real_escape_string($conn, $_POST['customer_type']);
        $call_status = isset($_POST['call_status']) ? mysqli_real_escape_string($conn, $_POST['call_status']) : 0;
        $charge_net_30 = isset($_POST['charge_net_30']) ? 1 : 0;
        $credit_limit = isset($_POST['credit_limit']) ? mysqli_real_escape_string($conn, $_POST['credit_limit']) : 0;
        $loyalty = isset($_POST['loyalty']) ? mysqli_real_escape_string($conn, $_POST['loyalty']) : '';
        $customer_pricing = isset($_POST['customer_pricing']) ? mysqli_real_escape_string($conn, $_POST['customer_pricing']) : 0;
        
        $customer_name = $customer_first_name . "" . $customer_last_name;

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        $checkQuery = "SELECT * FROM customer WHERE customer_id = '$customer_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "
                UPDATE customer
                SET  
                    customer_first_name = '$customer_first_name', 
                    customer_last_name = '$customer_last_name', 
                    customer_business_name = '$customer_business_name', 
                    contact_email = '$contact_email', 
                    contact_phone = '$contact_phone', 
                    primary_contact = '$primary_contact', 
                    contact_fax = '$contact_fax', 
                    address = '$address', 
                    city = '$city', 
                    state = '$state',
                    zip = '$zip',
                    lat = '$lat',
                    lng = '$lng',
                    secondary_contact_name = '$secondary_contact_name',
                    secondary_contact_phone = '$secondary_contact_phone',
                    ap_contact_name = '$ap_contact_name',
                    ap_contact_email = '$ap_contact_email',
                    ap_contact_phone = '$ap_contact_phone',
                    tax_status = '$tax_status',
                    tax_exempt_number = '$tax_exempt_number',
                    customer_notes = '$customer_notes',
                    call_status = '$call_status',
                    charge_net_30 = '$charge_net_30',
                    credit_limit = '$credit_limit',
                    customer_type_id = '$new_customer_type_id',
                    loyalty = '$loyalty',
                    customer_pricing = '$customer_pricing'
                WHERE 
                    customer_id = '$customer_id'";
            if (mysqli_query($conn, $updateQuery)) {
                // Get the currently added customer
                    $sql = "SELECT c.customer_id, c.customer_type_id, ct.customer_type_name
                            FROM customer c
                            JOIN customer_types ct ON c.customer_type_id = ct.customer_type_id
                            WHERE c.customer_first_name = '$customer_first_name' 
                            AND c.customer_last_name = '$customer_last_name'";

                // Get the current customer type ID
                    $resultSql = mysqli_query($conn, $sql);
                    if($new_customer_type_id != 0 && mysqli_num_rows($resultSql) > 0) {
                        $row = mysqli_fetch_assoc($resultSql);
                        $customer_id = $row['customer_id'];
                        $customer_type_name = $row['customer_type_name'];

                        if($current_customer_type_id != $new_customer_type_id) {
                            $insertQuery = "INSERT INTO customer_customer_type (
                                customer_id,
                                customer_type,
                                date_added
                            ) VALUE (
                                '$customer_id',
                                '$customer_type_name',
                                NOW()
                            )";
                            
                            if (mysqli_query($conn, $insertQuery)) {
                                echo "Customer updated successfully.";
                            } else {
                                echo "Error updating customer: " . mysqli_error($conn);
                            }
                        } else {
                            echo "Customer updated successfully.";
                        }
                    } else {
                        echo "Customer updated successfully.";
                    }
            } else {
                echo "Error updating customer: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "
                INSERT INTO customer (
                    customer_first_name, 
                    customer_last_name, 
                    customer_business_name, 
                    contact_email,
                    contact_phone,
                    primary_contact,
                    contact_fax,
                    address,
                    city,
                    state,
                    zip,
                    lat,
                    lng,
                    secondary_contact_name,
                    secondary_contact_phone,
                    ap_contact_name,
                    ap_contact_email,
                    ap_contact_phone,
                    tax_status,
                    tax_exempt_number,
                    customer_notes,
                    customer_type_id,
                    call_status,
                    charge_net_30,
                    credit_limit,
                    loyalty,
                    customer_pricing) 
                    VALUES (
                    '$customer_first_name', 
                    '$customer_last_name', 
                    '$customer_business_name',
                    '$contact_email',
                    '$contact_phone',
                    '$primary_contact',
                    '$contact_fax',
                    '$address',
                    '$city',
                    '$state',
                    '$zip',
                    '$lat',
                    '$lng',
                    '$secondary_contact_name',
                    '$secondary_contact_phone',
                    '$ap_contact_name',
                    '$ap_contact_email',
                    '$ap_contact_phone',
                    '$tax_status',
                    '$tax_exempt_number',
                    '$customer_notes',
                    '$new_customer_type_id',
                    '$call_status',
                    '$charge_net_30',
                    '$credit_limit',
                    '$loyalty',
                    '$customer_pricing')";

            if (mysqli_query($conn, $insertQuery)) {
                    // Get the currently added customer
                    $sql = "SELECT c.customer_id, ct.customer_type_name
                                        FROM customer c
                                        JOIN customer_types ct ON c.customer_type_id = ct.customer_type_id
                                        WHERE c.customer_first_name = '$customer_first_name' 
                                        AND c.customer_last_name = '$customer_last_name'";
                                    
                    $resultSql = mysqli_query($conn, $sql);
                    if($new_customer_type_id != 0 && mysqli_num_rows($resultSql) > 0) {
                        $row = mysqli_fetch_assoc($resultSql);
                        $customer_id = $row['customer_id'];
                        $customer_type_name = $row['customer_type_name'];

                        $insertQuery = "INSERT INTO customer_customer_type (
                            customer_id,
                            customer_type,
                            date_added
                        ) VALUE (
                            '$customer_id',
                            '$customer_type_name',
                            NOW()
                        )";

                        if (mysqli_query($conn, $insertQuery)) {
                            echo "New customer added successfully.";
                        } else {
                            echo "Error adding customer type: " . mysqli_error($conn);
                        }
                    } else {
                        echo "New customer added successfully.";
                    }
            } else {
                echo "Error adding customer: " . mysqli_error($conn);
            }
        }
    } 
    if ($action == "change_status") {
        $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE customer SET status = '$new_status' WHERE customer_id = '$customer_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_customer') {
        $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
        $query = "UPDATE customer SET hidden='1' WHERE customer_id='$customer_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    if ($action == 'change_act_cust_id') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $_SESSION['active_customer_id'] = $id;

        echo "ID: " .$_SESSION['active_customer_id'];
    }

    if ($action == "download_excel") {
        $includedColumns = array();
        $column_txt = '*';

        $includedColumns = [ 
            'customer_id',
            'customer_notes',
            'customer_first_name',
            'customer_last_name',
            'customer_business_name',
            'customer_type_id',
            'contact_email',
            'contact_phone',
            'primary_contact',
            'contact_fax',
            'address',
            'city',
            'state',
            'zip',
            'lat',
            'lng',
            'secondary_contact_name',
            'secondary_contact_phone',
            'ap_contact_name',
            'ap_contact_email',
            'ap_contact_phone',
            'tax_status',
            'tax_exempt_number',
            'call_status',
            'charge_net_30',
            'credit_limit',
            'customer_pricing'
        ];

        $column_txt = implode(', ', $includedColumns);

        $sql = "SELECT " . $column_txt . " FROM $table WHERE hidden = '0' AND status = '1'";
        $result = $conn->query($sql);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [];
        $row = 1;
        
        foreach ($includedColumns as $index => $column) {
            $header = ucwords(str_replace('_', ' ', $column));
        
            if ($index >= 26) {
                $columnLetter = indexToColumnLetter($index);
            } else {
                $columnLetter = chr(65 + $index);
            }
        
            $sheet->setCellValue($columnLetter . $row, $header);
        }        

        $row = 2;
        while ($data = $result->fetch_assoc()) {
            foreach ($includedColumns as $index => $column) {
                if ($index >= 26) {
                    $columnLetter = indexToColumnLetter($index);
                } else {
                    $columnLetter = chr(65 + $index);
                }
                $sheet->setCellValue($columnLetter . $row, $data[$column] ?? '');
            }
            $row++;
        }

        $name = strtoupper(str_replace('_', ' ', $table));

        $filename = "$name.xlsx";
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

    if ($action == "upload_excel") {
        if (isset($_FILES['excel_file'])) {
            $fileTmpPath = $_FILES['excel_file']['tmp_name'];
            $fileName = $_FILES['excel_file']['name'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            if ($fileExtension != "xlsx" && $fileExtension != "xls") {
                echo "Please upload a valid Excel file.";
                exit;
            }

            $spreadsheet = IOFactory::load($fileTmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $columns = $rows[0];
            $dbColumns = [];
            $columnMapping = [];

            foreach ($columns as $col) {
                $dbColumn = strtolower(str_replace(' ', '_', $col));

                $dbColumns[] = $dbColumn;
                $columnMapping[$dbColumn] = $col;
            }

            $truncateSql = "TRUNCATE TABLE $test_table";
            $truncateResult = $conn->query($truncateSql);

            if (!$truncateResult) {
                echo "Error truncating table: " . $conn->error;
                exit;
            }

            foreach ($rows as $index => $row) {
                if ($index == 0) {
                    continue;
                }

                $data = array_combine($dbColumns, $row);

                $columnNames = implode(", ", array_keys($data));
                $columnValues = implode("', '", array_map(function($value) { return $value ?? ''; }, array_values($data)));

                $sql = "INSERT INTO $test_table ($columnNames) VALUES ('$columnValues')";
                $result = $conn->query($sql);

                if (!$result) {
                    echo "Error inserting data: " . $conn->error;
                    exit;
                }
            }

            echo "success";
        } else {
            echo "No file uploaded.";
            exit;
        }
    }   
    
    if ($action == "update_test_data") {
        $column_name = $_POST['header_name'];
        $new_value = $_POST['new_value'];
        $id = $_POST['id'];
        
        if (empty($column_name) || empty($id)) {
            exit;
        }

        $test_primary = getPrimaryKey($test_table);
        
        $column_name = mysqli_real_escape_string($conn, $column_name);
        $new_value = mysqli_real_escape_string($conn, $new_value);
        $id = mysqli_real_escape_string($conn, $id);
        
        $sql = "UPDATE $test_table SET `$column_name` = '$new_value' WHERE $test_primary = '$id'";

        if ($conn->query($sql) === TRUE) {
            echo 'success';
        } else {
            echo 'Error updating record: ' . $conn->error;
        }
    }

    if ($action == "save_table") {
        $main_primary = getPrimaryKey($table);
        $test_primary = getPrimaryKey($test_table);
        
        $selectSql = "SELECT * FROM $test_table";
        $result = $conn->query($selectSql);
    
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $main_primary_id = trim($row[$main_primary] ?? ''); 
    
                unset($row[$test_primary]);
    
                if (!empty($main_primary_id)) {
                    $checkSql = "SELECT COUNT(*) as count FROM $table WHERE $main_primary = '$main_primary_id'";
                    $checkResult = $conn->query($checkSql);
                    $exists = $checkResult->fetch_assoc()['count'] > 0;
    
                    if ($exists) {
                        $updateFields = [];
                        foreach ($row as $column => $value) {
                            if ($column !== $main_primary && $value !== null && $value !== '') {
                                $updateFields[] = "$column = '$value'";
                            }
                        }
                        if (!empty($updateFields)) {
                            $updateSql = "UPDATE $table SET " . implode(", ", $updateFields) . " WHERE $main_primary = '$main_primary_id'";
                            $conn->query($updateSql);
                        }
                        continue;
                    }
                }
    
                $columns = [];
                $values = [];
                foreach ($row as $column => $value) {
                    if ($value !== null && $value !== '') {
                        $columns[] = $column;
                        $values[] = "'$value'";
                    }
                }
                if (!empty($columns)) {
                    $insertSql = "INSERT INTO $table (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ")";
                    $conn->query($insertSql);
                }
            }
    
            echo "Data has been successfully saved";
    
            $truncateSql = "TRUNCATE TABLE $test_table";
            if ($conn->query($truncateSql) !== TRUE) {
                echo " but failed to clear test color table: " . $conn->error;
            }
        } else {
            echo "No data found in test color table.";
        }
    }     

    if ($action == "fetch_uploaded_modal") {
        $test_primary = getPrimaryKey($test_table);
        
        $sql = "SELECT * FROM $test_table";
        $result = $conn->query($sql);
    
        if ($result->num_rows > 0) {
            $columns = [];
            while ($field = $result->fetch_field()) {
                $columns[] = $field->name;
            }
    
            $includedColumns = [ 
                'customer_id',
                'customer_notes',
                'customer_first_name',
                'customer_last_name',
                'customer_business_name',
                'customer_type_id',
                'contact_email',
                'contact_phone',
                'primary_contact',
                'contact_fax',
                'address',
                'city',
                'state',
                'zip',
                'lat',
                'lng',
                'secondary_contact_name',
                'secondary_contact_phone',
                'ap_contact_name',
                'ap_contact_email',
                'ap_contact_phone',
                'tax_status',
                'tax_exempt_number',
                'call_status',
                'charge_net_30',
                'credit_limit',
                'customer_pricing'
            ];
    
            $columns = array_filter($columns, function ($col) use ($includedColumns) {
                return in_array($col, $includedColumns, true);
            });
    
            $columnsWithData = [];
            while ($row = $result->fetch_assoc()) {
                foreach ($columns as $column) {
                    if (!empty(trim($row[$column] ?? ''))) {
                        $columnsWithData[$column] = true;
                    }
                }
            }
    
            $result->data_seek(0);
            ?>
    
            <div class="card card-body shadow" data-table="<?=$table?>">
                <form id="tableForm">
                    <div style="overflow-x: auto; overflow-y: auto; max-height: 80vh; max-width: 100%;">
                        <table class="table table-bordered table-striped text-center">
                            <thead>
                                <tr>
                                    <?php
                                    foreach ($columns as $column) {
                                        if (isset($columnsWithData[$column])) {
                                            $formattedColumn = ucwords(str_replace('_', ' ', $column));
                                            echo "<th class='fs-4'>$formattedColumn</th>";
                                        }
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                while ($row = $result->fetch_assoc()) {
                                    $primaryValue = $row[$test_primary] ?? '';
                                    echo '<tr>';
                                    foreach ($columns as $column) {
                                        if (isset($columnsWithData[$column])) {
                                            $value = htmlspecialchars($row[$column] ?? '', ENT_QUOTES, 'UTF-8');
                                            echo "<td contenteditable='true' class='table_data' data-header-name='$column' data-id='$primaryValue'>$value</td>";
                                        }
                                    }
                                    echo '</tr>';
                                }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <button type="button" id="saveTable" class="btn btn-primary mt-3">Save</button>
                    </div>
                </form>
            </div>
            <?php
        } else {
            echo "<p>No data found in the table.</p>";
        }
    }

    if ($action == "download_classifications") {
        $classification = mysqli_real_escape_string($conn, $_REQUEST['class'] ?? '');

        $classifications = [
            'tax_status' => [
                'columns' => ['taxid', 'tax_status_desc'],
                'table' => 'customer_tax',
                'where' => "1"
            ],
            'customer_pricing' => [
                'columns' => ['id', 'pricing_name'],
                'table' => 'customer_pricing',
                'where' => "status = '1'"
            ]
        ];

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $selectedClassifications = empty($classification) ? array_keys($classifications) : [$classification];

        foreach ($selectedClassifications as $class) {
            if (!isset($classifications[$class])) {
                continue;
            }

            $includedColumns = $classifications[$class]['columns'];
            $table = $classifications[$class]['table'];
            $where = $classifications[$class]['where'];
            $column_txt = implode(', ', $includedColumns);
            $sql = "SELECT $column_txt FROM $table WHERE $where";
            $result = $conn->query($sql);

            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle(ucwords($class));

            $row = 1;
            foreach ($includedColumns as $index => $column) {
                $header = ucwords(str_replace('_', ' ', $column));
                $columnLetter = chr(65 + $index);
                $sheet->setCellValue($columnLetter . $row, $header);
            }

            $row = 2;
            while ($data = $result->fetch_assoc()) {
                foreach ($includedColumns as $index => $column) {
                    $columnLetter = chr(65 + $index);

                    $value = $data[$column] ?? '';
                        
                    $sheet->setCellValue($columnLetter . $row, $value);
                }
                $row++;
            }
        }

        if(empty($classification)){
            $classification = 'All';
        }else{
            $classification = ucwords($classification);
        }

        $filename = "$classification Classifications.xlsx";
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
    mysqli_close($conn);
}
?>
