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

$includedColumns = [
    'customer_id'                => 'Customer ID #',
    'customer_notes'             => 'Customer Notes',
    'customer_first_name'        => 'First Name',
    'customer_last_name'         => 'Last Name',
    'customer_business_name'     => 'Name',
    'customer_business_website'  => 'Website',
    'customer_type_id'           => 'Type of Tax Exempt Customer',
    'contact_email'              => 'Primary Email Address',
    'contact_phone'              => 'Primary Phone #',
    'primary_contact'            => 'Primary Contact Name',
    'contact_fax'                => 'Primary Fax #',
    'address'                    => 'Billing Address',
    'city'                       => 'City',
    'state'                      => 'State',
    'zip'                        => 'Zip',
    'different_ship_address'     => 'Shipping Address different than Billing Address?',
    'ship_address'               => 'Shipping Address',
    'ship_city'                  => 'Shipping City',
    'ship_state'                 => 'Shipping State',
    'ship_zip'                   => 'Shipping Zip',
    'secondary_contact_name'     => 'Secondary Contact Name',
    'secondary_contact_phone'    => 'Secondary Phone #',
    'secondary_contact_email'    => 'Secondary Email Address',
    'tax_status'                 => 'Tax Status',
    'tax_exempt_number'          => 'Tax Exemption #',
    'is_corporate_parent'        => 'Corporate Parent',
    'corpo_parent_name'          => 'Corporate Parent Name',
    'corpo_phone_no'             => 'Corporate Phone',
    'corpo_address'              => 'Corporate Address',
    'corpo_city'                 => 'Corporate City',
    'corpo_state'                => 'Corporate State',
    'corpo_zip'                  => 'Corporate Zip',
    'is_bill_corpo_address'      => 'Bill to Corporate Address',
    'is_charge_net'              => 'Charge Net 30',
    'charge_net_30'              => 'Charge Net 30',
    'credit_limit'               => 'Charge Net 30 Limit',
    'loyalty'                    => 'Loyalty',
    'customer_pricing'           => 'Customer Pricing',
    'is_approved'                => 'Portal Access',
    'payment_pickup'             => 'Pay at Pick-Up',
    'payment_delivery'           => 'Pay at Delivery',
    'payment_cash'               => 'Cash',
    'payment_check'              => 'Check',
    'payment_card'               => 'Credit-Debit',
    'is_contractor'              => 'Is Customer a Contractor',
    'username'                   => 'Portal Username',
    'password'                   => 'Portal Password'
];

$table = 'customer';
$test_table = 'customer_excel';
$main_primary_key = getPrimaryKey($table);

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
        $customer_first_name = mysqli_real_escape_string($conn, $_POST['customer_first_name'] ?? '');
        $customer_last_name = mysqli_real_escape_string($conn, $_POST['customer_last_name'] ?? '');
        $customer_business_name = mysqli_real_escape_string($conn, $_POST['customer_business_name'] ?? '');
        $customer_business_website = mysqli_real_escape_string($conn, $_POST['customer_business_website'] ?? '');
        $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email'] ?? '');
        $contact_phone = mysqli_real_escape_string($conn, $_POST['contact_phone'] ?? '');
        $primary_contact = mysqli_real_escape_string($conn, $_POST['primary_contact'] ?? '');
        $contact_fax = mysqli_real_escape_string($conn, $_POST['contact_fax'] ?? '');
        $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
        $city = mysqli_real_escape_string($conn, $_POST['city'] ?? '');
        $state = mysqli_real_escape_string($conn, $_POST['state'] ?? '');
        $zip = mysqli_real_escape_string($conn, $_POST['zip'] ?? '');
        $lat = mysqli_real_escape_string($conn, $_POST['lat'] ?? '');
        $lng = mysqli_real_escape_string($conn, $_POST['lng'] ?? '');
        $secondary_contact_name = mysqli_real_escape_string($conn, $_POST['secondary_contact_name'] ?? '');
        $secondary_contact_phone = mysqli_real_escape_string($conn, $_POST['secondary_contact_phone'] ?? '');
        $secondary_contact_email = mysqli_real_escape_string($conn, $_POST['secondary_contact_email'] ?? '');
        $ap_contact_name = mysqli_real_escape_string($conn, $_POST['ap_contact_name'] ?? '');
        $ap_contact_email = mysqli_real_escape_string($conn, $_POST['ap_contact_email'] ?? '');
        $ap_contact_phone = mysqli_real_escape_string($conn, $_POST['ap_contact_phone'] ?? '');
        $tax_status = mysqli_real_escape_string($conn, $_POST['tax_status'] ?? '');
        $tax_exempt_number = mysqli_real_escape_string($conn, $_POST['tax_exempt_number'] ?? '');
        $customer_notes = mysqli_real_escape_string($conn, $_POST['customer_notes']);
        $customer_type_id = mysqli_real_escape_string($conn, $_POST['customer_type_id'] ?? '');
        $call_status = mysqli_real_escape_string($conn, $_POST['call_status'] ?? 0);
        $is_charge_net = mysqli_real_escape_string($conn, $_POST['is_charge_net'] ?? 0);
        $is_contractor = mysqli_real_escape_string($conn, $_POST['is_contractor'] ?? 0);
        $is_corporate_parent = mysqli_real_escape_string($conn, $_POST['is_corporate_parent'] ?? 0);
        $is_bill_corpo_address = mysqli_real_escape_string($conn, $_POST['is_bill_corpo_address'] ?? 0);
        $corpo_parent_name = mysqli_real_escape_string($conn, $_POST['corpo_parent_name'] ?? '');
        $corpo_phone_no = mysqli_real_escape_string($conn, $_POST['corpo_phone_no'] ?? '');
        $corpo_address = mysqli_real_escape_string($conn, $_POST['corpo_address'] ?? '');
        $corpo_city = mysqli_real_escape_string($conn, $_POST['corpo_city'] ?? '');
        $corpo_state = mysqli_real_escape_string($conn, $_POST['corpo_state'] ?? '');
        $corpo_zip = mysqli_real_escape_string($conn, $_POST['corpo_zip'] ?? '');
        $corpo_lat = mysqli_real_escape_string($conn, $_POST['corpo_lat'] ?? '');
        $corpo_lng = mysqli_real_escape_string($conn, $_POST['corpo_lng'] ?? '');
        $charge_net_30 = mysqli_real_escape_string($conn, $_POST['charge_net_30']);
        $credit_limit = mysqli_real_escape_string($conn, $_POST['credit_limit'] ?? 0);
        $loyalty = mysqli_real_escape_string($conn, $_POST['loyalty'] ?? 0);
        $customer_pricing = mysqli_real_escape_string($conn, $_POST['customer_pricing'] ?? 0);
        $is_approved = mysqli_real_escape_string($conn, $_POST['portal_access'] ?? 0);
        $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');

        $desired_invoice_preference = mysqli_real_escape_string($conn, $_POST['desired_invoice_preference'] ?? 0);

        $payment_pickup    = isset($_POST['payment_pickup']) ? 1 : 0;
        $payment_delivery  = isset($_POST['payment_delivery']) ? 1 : 0;
        $payment_cash      = isset($_POST['payment_cash']) ? 1 : 0;
        $payment_check     = isset($_POST['payment_check']) ? 1 : 0;
        $payment_card      = isset($_POST['payment_card']) ? 1 : 0;

        $different_ship_address = isset($_POST['different_ship_address']) ? 1 : 0;
        if ($different_ship_address == 0) {
            $ship_address = $address;
            $ship_city    = $city;
            $ship_state   = $state;
            $ship_zip     = $zip;
            $ship_lat     = $lat;
            $ship_lng     = $lng;
        } else {
            $ship_address = mysqli_real_escape_string($conn, $_POST['ship_address']);
            $ship_city    = mysqli_real_escape_string($conn, $_POST['ship_city']);
            $ship_state   = mysqli_real_escape_string($conn, $_POST['ship_state']);
            $ship_zip     = mysqli_real_escape_string($conn, $_POST['ship_zip']);
            $ship_lat     = mysqli_real_escape_string($conn, $_POST['ship_lat']);
            $ship_lng     = mysqli_real_escape_string($conn, $_POST['ship_lng']);
        }

        /* 
        if (!empty($_POST['username'])) {
            $username = mysqli_real_escape_string($conn, $_POST['username']);

            if (!empty($customer_id)) {
                $checkQuery = "
                    SELECT customer_id 
                    FROM customer 
                    WHERE username = '$username' 
                    AND customer_id != '$customer_id' 
                    AND status = 1 
                    AND hidden = 0
                ";
            } else {
                $checkQuery = "
                    SELECT customer_id 
                    FROM customer 
                    WHERE username = '$username' 
                    AND status = 1 
                    AND hidden = 0
                ";
            }

            $result = mysqli_query($conn, $checkQuery) or die("Error checking username: " . mysqli_error($conn));

            if (mysqli_num_rows($result) > 0) {
                echo "The username \"$username\" is already taken. Please use another username.";
                exit;
            }
        } 
        */

        $checkQuery = "SELECT * FROM customer WHERE customer_id = '$customer_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "
                UPDATE customer SET  
                    customer_first_name = '$customer_first_name', 
                    customer_last_name = '$customer_last_name', 
                    customer_business_name = '$customer_business_name', 
                    customer_business_website = '$customer_business_website',
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
                    different_ship_address = '$different_ship_address',
                    ship_address = '$ship_address',
                    ship_city = '$ship_city',
                    ship_state = '$ship_state',
                    ship_zip = '$ship_zip',
                    ship_lat = '$ship_lat',
                    ship_lng = '$ship_lng',
                    secondary_contact_name = '$secondary_contact_name',
                    secondary_contact_phone = '$secondary_contact_phone',
                    secondary_contact_email = '$secondary_contact_email',
                    ap_contact_name = '$ap_contact_name',
                    ap_contact_email = '$ap_contact_email',
                    ap_contact_phone = '$ap_contact_phone',
                    tax_status = '$tax_status',
                    tax_exempt_number = '$tax_exempt_number',
                    customer_notes = '$customer_notes',
                    call_status = '$call_status',
                    is_charge_net = '$is_charge_net',
                    is_contractor = '$is_contractor',
                    is_corporate_parent = '$is_corporate_parent',
                    is_bill_corpo_address = '$is_bill_corpo_address',
                    corpo_parent_name = '$corpo_parent_name',
                    corpo_phone_no = '$corpo_phone_no',
                    corpo_address = '$corpo_address',
                    corpo_city = '$corpo_city',
                    corpo_state = '$corpo_state',
                    corpo_zip = '$corpo_zip',
                    corpo_lat = '$corpo_lat',
                    corpo_lng = '$corpo_lng',
                    charge_net_30 = '$charge_net_30',
                    credit_limit = '$credit_limit',
                    customer_type_id = '$customer_type_id',
                    loyalty = '$loyalty',
                    desired_invoice_preference = '$desired_invoice_preference',
                    customer_pricing = '$customer_pricing',
                    is_approved = '$is_approved',
                    payment_pickup = '$payment_pickup',
                    payment_delivery = '$payment_delivery',
                    payment_cash = '$payment_cash',
                    payment_check = '$payment_check',
                    payment_card = '$payment_card',
                    username = '$username',
                    updated_at = NOW()
                WHERE customer_id = '$customer_id'";
            mysqli_query($conn, $updateQuery) or die("Error updating customer: " . mysqli_error($conn));
            echo "success_update";
            $isUpdate = true;
        } else {
            $insertQuery = "
            INSERT INTO customer (
                customer_first_name, customer_last_name, customer_business_name, customer_business_website,
                contact_email, contact_phone, primary_contact, contact_fax,
                address, city, state, zip, lat, lng,
                different_ship_address, ship_address, ship_city, ship_state, ship_zip, ship_lat, ship_lng,
                secondary_contact_name, secondary_contact_phone, secondary_contact_email,
                ap_contact_name, ap_contact_email, ap_contact_phone,
                tax_status, tax_exempt_number, customer_notes,
                customer_type_id, call_status, is_charge_net, is_contractor, is_corporate_parent, is_bill_corpo_address,
                corpo_parent_name, corpo_phone_no, corpo_address, corpo_city, corpo_state, corpo_zip, corpo_lat, corpo_lng,
                charge_net_30, credit_limit, loyalty, desired_invoice_preference, customer_pricing, is_approved,
                payment_pickup, payment_delivery, payment_cash, payment_check, payment_card, username,
                created_at, updated_at
            ) VALUES (
                '$customer_first_name', '$customer_last_name', '$customer_business_name', '$customer_business_website',
                '$contact_email', '$contact_phone', '$primary_contact', '$contact_fax',
                '$address', '$city', '$state', '$zip', '$lat', '$lng',
                '$different_ship_address', '$ship_address', '$ship_city', '$ship_state', '$ship_zip', '$ship_lat', '$ship_lng',
                '$secondary_contact_name', '$secondary_contact_phone', '$secondary_contact_email',
                '$ap_contact_name', '$ap_contact_email', '$ap_contact_phone',
                '$tax_status', '$tax_exempt_number', '$customer_notes',
                '$customer_type_id', '$call_status', '$is_charge_net', '$is_contractor', '$is_corporate_parent', '$is_bill_corpo_address',
                '$corpo_parent_name', '$corpo_phone_no', '$corpo_address', '$corpo_city', '$corpo_state', '$corpo_zip', '$corpo_lat', '$corpo_lng',
                '$charge_net_30', '$credit_limit', '$loyalty', '$desired_invoice_preference', '$customer_pricing', '$is_approved',
                '$payment_pickup', '$payment_delivery', '$payment_cash', '$payment_check', '$payment_card', '$username',
                NOW(), NOW()
            )";
            mysqli_query($conn, $insertQuery) or die("Error adding customer: " . mysqli_error($conn));
            echo "success_add";
            $isUpdate = false;

            $customer_id = mysqli_insert_id($conn);
        }

        if (!empty($_POST['password'])) {
            $encryptedPassword = encrypt_password_for_storage($_POST['password']);
            $passwordQuery = "
                UPDATE customer 
                SET password = '" . mysqli_real_escape_string($conn, $encryptedPassword) . "', 
                    updated_at = NOW()
                WHERE customer_id = '" . mysqli_real_escape_string($conn, $customer_id) . "'
            ";
            mysqli_query($conn, $passwordQuery) or die("Error updating password: " . mysqli_error($conn));
        }

        if (!empty($_FILES['picture_path']['name'][0])) {
            $uploadDir = __DIR__ . "/../images/customer_tax_documents/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['picture_path']['tmp_name'] as $key => $tmpName) {
                if (!empty($tmpName)) {
                    $filename = time() . "_" . preg_replace("/[^A-Za-z0-9\._-]/", "_", $_FILES['picture_path']['name'][$key]);
                    $targetFile = $uploadDir . $filename;

                    if (move_uploaded_file($tmpName, $targetFile)) {
                        $filePath = "images/customer_tax_documents/" . $filename;
                        $insertImg = "INSERT INTO customer_tax_images (customer_id, image_url) 
                                    VALUES ('$customer_id', '$filePath')";
                        mysqli_query($conn, $insertImg);
                    }
                }
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

    if ($action == "remove_image") {
        $image_id = $_POST['image_id'];
    
        $delete_query = "DELETE FROM customer_tax_images WHERE taximgid = '$image_id'";
        if (mysqli_query($conn, $delete_query)) {
            /* if (file_exists($image_url)) {
                unlink($image_url);
            } */
            echo 'success';
        } else {
            echo "Error removing image: " . mysqli_error($conn);
        }
    }

    if ($action == 'get_place_name') {
        $lat = $_POST['lat'];
        $lng = $_POST['lng'];
        $url = "https://nominatim.openstreetmap.org/reverse?lat=$lat&lon=$lng&format=json&addressdetails=1";

        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: Metal/1.0\r\n"
            ]
        ];
        $context = stream_context_create($opts);
        $response = @file_get_contents($url, false, $context);

        if ($response) {
            header('Content-Type: application/json');
            echo $response;
        } else {
            echo json_encode(['error' => 'Unable to fetch address']);
        }
        exit;
    }

    if ($action == 'search_address') {
        $query = urlencode($_POST['query']);
        $url = "https://nominatim.openstreetmap.org/search?q=$query&format=json&addressdetails=1&limit=5";

        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: Metal/1.0\r\n"
            ]
        ];
        $context = stream_context_create($opts);
        $response = @file_get_contents($url, false, $context);

        if ($response) {
            header('Content-Type: application/json');
            echo $response;
        } else {
            echo json_encode([]);
        }
        exit;
    }

    if ($action === "download_excel") {
        $column_txt = implode(', ', array_keys($includedColumns));

        $sql = "
            SELECT $column_txt
            FROM $table
            WHERE hidden = '0'
            AND status = '1'
            AND customer_type_id IS NOT NULL
            AND customer_type_id != '0'
        ";

        $result = $conn->query($sql);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $customerTypeMap = [
            1 => 'Customer Type (Personal)',
            2 => 'Customer Type (Business)',
            3 => 'Customer Type (Farm)',
            4 => 'Customer Type (Exempt)'
        ];

        $sheets = [];
        $currentRow = [];
        $columnHasData = [];

        while ($data = $result->fetch_assoc()) {

            $customerTypeId = (int) $data['customer_type_id'];
            if (!isset($customerTypeMap[$customerTypeId])) continue;

            $sheetName = sanitizeSheetTitle($customerTypeMap[$customerTypeId]);

            if (!isset($sheets[$sheetName])) {
                $sheet = $spreadsheet->createSheet();
                $sheet->setTitle($sheetName);
                $sheets[$sheetName] = $sheet;
                $columnHasData[$sheetName] = [];

                $headerRow = 1;
                $colIndex = 0;
                foreach ($includedColumns as $dbColumn => $displayName) {
                    $colLetter = indexToColumnLetter($colIndex);
                    $sheet->setCellValue($colLetter . $headerRow, $displayName);
                    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
                    $colIndex++;
                }

                $headerRange = 'A1:' . $sheet->getHighestColumn() . '1';
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9D9D9']
                    ],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
                ]);

                $currentRow[$sheetName] = 2;
            }

            $sheet = $sheets[$sheetName];

            $colIndex = 0;
            foreach ($includedColumns as $dbColumn => $displayName) {
                $colLetter = indexToColumnLetter($colIndex);
                $value = $data[$dbColumn] ?? '';

                if ($dbColumn === 'password' && !empty($value)) {
                    try {
                        $value = decrypt_password_from_storage($value);
                    } catch (Exception $e) {
                        $value = '';
                    }
                }

                if ($value !== '' && $value !== null) {
                    $columnHasData[$sheetName][$colLetter] = true;
                }

                $sheet->setCellValueExplicit(
                    $colLetter . $currentRow[$sheetName],
                    $value,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );

                $colIndex++;
            }

            $currentRow[$sheetName]++;
        }

        foreach ($sheets as $sheetName => $sheet) {
            $highestColumn = $sheet->getHighestColumn();
            foreach (range('A', $highestColumn) as $colLetter) {
                if (empty($columnHasData[$sheetName][$colLetter])) {
                    $sheet->getColumnDimension($colLetter)->setVisible(false);
                }
            }
        }

        $spreadsheet->setActiveSheetIndex(0);
        $name = strtoupper(str_replace('_', ' ', $table));
        $filename = "{$name}.xlsx";

        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filename));
        header('Cache-Control: max-age=0');

        readfile($filename);
        unlink($filename);
        exit;
    }

    if ($action === "upload_excel") {
        if (!isset($_FILES['excel_file'])) {
            echo "No file uploaded.";
            exit;
        }

        $fileTmpPath = $_FILES['excel_file']['tmp_name'];
        $fileName = $_FILES['excel_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, ['xlsx', 'xls'])) {
            echo "Please upload a valid Excel file.";
            exit;
        }

        $spreadsheet = IOFactory::load($fileTmpPath);

        if (!$conn->query("TRUNCATE TABLE `$test_table`")) {
            echo "Error truncating table: " . $conn->error;
            exit;
        }

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $rows = $sheet->toArray(null, true, true, false);

            if (count($rows) < 2) continue;

            $headers = array_map(
                fn($h) => $h === null ? '' : trim((string)$h),
                $rows[0]
            );

            $columnMap = [];
            foreach ($includedColumns as $dbCol => $displayTitle) {
                $index = array_search($displayTitle, $headers, true);
                if ($index !== false) {
                    $columnMap[$index] = $dbCol;
                }
            }

            if (empty($columnMap)) continue;

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                if (empty(array_filter($row, fn($v) => $v !== null && trim((string)$v) !== ''))) {
                    continue;
                }

                $data = [];
                foreach ($columnMap as $excelIndex => $dbCol) {

                    $value = $row[$excelIndex] ?? '';
                    $value = $value === null ? '' : trim((string)$value);
                    
                    if (strcasecmp($value, 'Yes') === 0) {
                        $value = 1;
                    } elseif (strcasecmp($value, 'No') === 0) {
                        $value = 0;
                    }

                    switch ($dbCol) {
                        case 'tax_status':
                            $value = getIdsFromColumnValues("customer_tax", "tax_status_desc", $value);
                            $value = trim($value, '[]');
                            break;
                        case 'customer_pricing':
                            $value = getIdsFromColumnValues("customer_pricing", "pricing_name", $value);
                            $value = trim($value, '[]');
                            break;
                    }

                    $data[$dbCol] = mysqli_real_escape_string($conn, $value);
                }

                if (empty($data)) continue;

                $columnNames  = implode(', ', array_map(fn($c) => "`$c`", array_keys($data)));
                $columnValues = implode("', '", array_values($data));

                $sql = "INSERT INTO `$test_table` ($columnNames) VALUES ('$columnValues')";

                if (!$conn->query($sql)) {
                    echo "Error inserting row " . ($i + 1) . " in sheet '{$sheet->getTitle()}': " . $conn->error;
                    exit;
                }
            }
        }

        echo "success";
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
                                if ($column === 'password') {
                                    try {
                                        $value = encrypt_password_for_storage($value);
                                    } catch (Exception $e) {
                                        $value = '';
                                    }
                                }
                                $updateFields[] = "$column = '" . $conn->real_escape_string($value) . "'";
                            }
                        }
                        if (!empty($updateFields)) {
                            $updateSql = "UPDATE $table SET " . implode(", ", $updateFields) . " WHERE $main_primary = '$main_primary_id'";
                            if (!$conn->query($updateSql)) {
                                error_log("Update failed for $main_primary_id: " . $conn->error);
                            }
                        }
                        continue;
                    }
                }

                $columns = [];
                $values = [];
                foreach ($row as $column => $value) {
                    if ($value !== null && $value !== '') {
                        if ($column === 'password') {
                            try {
                                $value = encrypt_password_for_storage($value);
                            } catch (Exception $e) {
                                $value = '';
                            }
                        }
                        $columns[] = $column;
                        $values[] = "'" . $conn->real_escape_string($value) . "'";
                    }
                }
                if (!empty($columns)) {
                    $insertSql = "INSERT INTO $table (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ")";
                    if (!$conn->query($insertSql)) {
                        error_log("Insert failed: " . $conn->error);
                    }
                }
            }

            echo "Data has been successfully saved";

            $truncateSql = "TRUNCATE TABLE $test_table";
            if ($conn->query($truncateSql) !== TRUE) {
                echo " but failed to clear test table: " . $conn->error;
            }
        } else {
            echo "No data found in test table.";
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
                'customer_business_website',
                'customer_type_id',
                'contact_email',
                'contact_phone',
                'primary_contact',
                'contact_fax',
                'address',
                'city',
                'state',
                'zip',
                'different_ship_address',
                'ship_address',
                'ship_city',
                'ship_state',
                'ship_zip',
                'secondary_contact_name',
                'secondary_contact_phone',
                'secondary_contact_email',
                'tax_status',
                'tax_exempt_number',
                'is_corporate_parent',
                'corpo_parent_name',
                'corpo_phone_no',
                'corpo_address',
                'corpo_city',
                'corpo_state',
                'corpo_zip',
                'is_bill_corpo_address',
                'is_charge_net',
                'charge_net_30',
                'credit_limit',
                'loyalty',
                'customer_pricing',
                'is_approved',
                'payment_pickup',
                'payment_delivery',
                'payment_cash',
                'payment_check',
                'payment_card',
                'is_contractor',
                'username',
                'password'
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

    if ($action === "add_job_name") {
        $customer_id = $_POST['customer_id'] ?? 0;
        $job_name = isset($_POST['job_name']) ? trim($_POST['job_name']) : '';

        if (empty($customer_id)) {
            echo json_encode([
                'success' => false,
                'message' => 'Customer is not set. Please select customer first!'
            ]);
            exit;
        }

        if (empty($job_name)) {
            echo json_encode([
                'success' => false,
                'message' => 'Job name cannot be empty!'
            ]);
            exit;
        }

        $job_name = mysqli_real_escape_string($conn, $job_name);

        $insert_query = "
            INSERT INTO jobs (customer_id, job_name)
            VALUES ('$customer_id', '$job_name')
        ";

        if (mysqli_query($conn, $insert_query)) {
            echo json_encode([
                'success' => true,
                'job_id'  => mysqli_insert_id($conn),
                'job_name'=> $job_name
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . mysqli_error($conn)
            ]);
        }
    }

    if ($action === "deposit_job") {
        $job_id         = trim($_POST['job_id'] ?? '');
        $deposit_amount = floatval($_POST['deposit_amount'] ?? 0);
        $deposited_by   = $_POST['deposited_by'];
        $reference_no   = trim($_POST['reference_no'] ?? '');
        $payment_method = $_POST['type'] ?? 'cash';
        $check_no       = trim($_POST['check_no'] ?? '');
        $auth_no        = trim($_POST['auth_no'] ?? '');
        $job_po        = trim($_POST['job_po'] ?? '');
        $description    = mysqli_real_escape_string($conn, $_POST['description'] ?? 'Job deposit');

        $job_id   = mysqli_real_escape_string($conn, $job_id);
        $check_no = $payment_method === 'check'
            ? mysqli_real_escape_string($conn, $check_no)
            : '';

        $auth_no = $payment_method === 'card'
            ? mysqli_real_escape_string($conn, $auth_no)
            : '';

        $deposit_status = 1;
        if ($payment_method === 'cash' && $deposit_amount > 10000) {
            $deposit_status = 0;
        }

        $insert_ledger = "
            INSERT INTO job_ledger (
                job_id,
                customer_id,
                entry_type,
                amount,
                payment_method,
                check_number,
                reference_no,
                description,
                created_by
            ) VALUES (
                '$job_id',
                '" . mysqli_real_escape_string($conn, $deposited_by) . "',
                'deposit',
                '$deposit_amount',
                'job_deposit',
                '$check_no',
                '" . mysqli_real_escape_string($conn, $reference_no) . "',
                '$description',
                '" . mysqli_real_escape_string($conn, $deposited_by) . "'
            )
        ";

        if (mysqli_query($conn, $insert_ledger)) {
            $insert_deposit = "
                INSERT INTO job_deposits (
                    job_id,
                    deposit_amount,
                    deposit_remaining,
                    deposit_status,
                    deposited_by,
                    reference_no,
                    type,
                    check_no,
                    auth_no,
                    job_po
                ) VALUES (
                    '$job_id',
                    '$deposit_amount',
                    '$deposit_amount',
                    '$deposit_status',
                    '" . mysqli_real_escape_string($conn, $deposited_by) . "',
                    '" . mysqli_real_escape_string($conn, $reference_no) . "',
                    '$payment_method',
                    '$check_no',
                    '$auth_no',
                    '$job_po'
                )
            ";
            mysqli_query($conn, $insert_deposit);

            recordCashInflow($payment_method, 'job_deposit', $deposit_amount);

            if ($payment_method === 'cash' && $deposit_amount > 10000) {

                $deposit_id = mysqli_insert_id($conn);

                $actorId   = $_SESSION['userid'];
                $actorName = get_staff_name($actorId);

                createNotification(
                    $actorId,
                    'deposit_approval',
                    $deposit_id,
                    'Cash Deposit Approval',
                    "$actorName has requested cash deposit approval",
                    'admin',
                    '?page=job_deposit_approval'
                );
            }

            if ($job_id !== '') {
                mysqli_query($conn, "
                    UPDATE jobs
                    SET deposit_amount = deposit_amount + $deposit_amount
                    WHERE job_id = '$job_id'
                ");
            }

            $cashierid = $_SESSION['userid'] ?? 0;
            $job = getJobDetails($job_id);
            $job_name = $job['job_name'] ?? '';

            $pay_cash = 0;
            $pay_card = 0;
            $pay_check = 0;
            $pay_pickup = 0;
            $pay_delivery = 0;
            $pay_net30 = 0;

            if($payment_method == 'cash'){
                $pay_cash = $deposit_amount;
            }else if($payment_method == 'card'){
                $pay_card = $deposit_amount;
            }else if($payment_method == 'check'){
                $pay_check = $deposit_amount;
            }

            $customer_details   = getCustomerDetails($deposited_by);
            $tax_status = $customer_details['tax_status'];
            $tax_exempt_number  = $customer_details['tax_exempt_number'] ?? '';

            $token = bin2hex(random_bytes(8));

            $_GET['prod'] = $token;  
            ob_start();
            include __DIR__ . '/../delivery/receipt.php';
            ob_end_clean();

            $sql_insert = "
                INSERT INTO orders (
                    estimateid, cashier, station, total_price, discounted_price, discount_percent, discount_amount,
                    order_date, scheduled_date, customerid, originalcustomerid,
                    cash_amt, credit_amt, job_name, job_po,
                    deliver_address, deliver_city, deliver_state, deliver_zip,
                    delivery_amt, deliver_method, deliver_fname, deliver_lname,
                    pay_type, pay_cash, pay_card, pay_check, pay_pickup, pay_delivery, pay_net30,
                    tax_status, tax_exempt_number, truck, contractor_id, token
                ) VALUES (
                    '', '$cashierid', '', '$deposit_amount', '$deposit_amount', '', '',
                    NOW(), NOW(), '$deposited_by', '$deposited_by',
                    '$deposit_amount', '', '$job_name', '$job_po',
                    '', '', '', '',
                    '', '', '', '',
                    '$payment_method',
                    '$pay_cash', '$pay_card', '$pay_check', '$pay_pickup', '$pay_delivery', '$pay_net30',
                    '$tax_status', '$tax_exempt_number', '', '', '$token'
                )
            ";

            if ($conn->query($sql_insert) === TRUE) {
                $orderid = $conn->insert_id;

                $query = "INSERT INTO order_product (
                    orderid, productid, product_item, quantity, custom_width, custom_bend, custom_hem,
                    custom_length, custom_length2, actual_price, discounted_price, product_category,
                    custom_color, custom_grade, custom_gauge, custom_profile, current_customer_discount, current_loyalty_discount,
                    used_discount, stiff_stand_seam, stiff_board_batten, panel_type, panel_style, custom_img_src, bundle_id, note,
                    product_id_abbrev, screw_length
                ) VALUES (
                    '$orderid', '', 'Customer Deposit', '1', '',
                    '', '', '', '',
                    '$deposit_amount', '$deposit_amount', '',
                    '', '', '', '', '', '',
                    '', '', '', '', '', '', '', '',
                    '', ''
                )";

                if ($conn->query($query) !== TRUE) {
                    die("Error: " . $conn->error);
                }
            }

            echo 'success';

        } else {
            echo 'error_insert';
        }
    }

    mysqli_close($conn);
}
?>
