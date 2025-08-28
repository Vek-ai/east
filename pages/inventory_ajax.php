<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);


require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $operation    = mysqli_real_escape_string($conn, $_POST['operation']);
        $Product_id   = mysqli_real_escape_string($conn, $_POST['Product_id']);
        $color_id     = mysqli_real_escape_string($conn, $_POST['color_id']);
        $supplier_id  = mysqli_real_escape_string($conn, $_POST['supplier_id']);
        $Warehouse_id = mysqli_real_escape_string($conn, $_POST['Warehouse_id']);
        $Shelves_id   = mysqli_real_escape_string($conn, $_POST['Shelves_id']);
        $Bin_id       = mysqli_real_escape_string($conn, $_POST['Bin_id']);
        $Row_id       = mysqli_real_escape_string($conn, $_POST['Row_id']);
        $Date         = mysqli_real_escape_string($conn, $_POST['Date']);
        $quantity     = (int)($_POST['quantity'] ?? 0);
        $quantity_ttl = (int)($_POST['quantity_ttl'] ?? 0);
        $pack         = mysqli_real_escape_string($conn, $_POST['pack']);
        $cost         = mysqli_real_escape_string($conn, $_POST['cost'] ?? '0');
        $price        = mysqli_real_escape_string($conn, $_POST['price'] ?? '0');
        $lumber_type  = mysqli_real_escape_string($conn, $_POST['lumber_type'] ?? '');
        $addedby      = $_SESSION['userid'];

        $product = getProductDetails($Product_id);
        $product_category = $product['product_category'] ?? 0;

        $dimension_id = null;

        if ($product_category == 1) {
            $length_value = mysqli_real_escape_string($conn, $_POST['length_value'] ?? '');
            $length_unit  = mysqli_real_escape_string($conn, $_POST['length_unit'] ?? '');

            if (!empty($length_value) && !empty($length_unit)) {
                $checkDim = "
                    SELECT dimension_id 
                    FROM dimensions 
                    WHERE dimension_category = 1 
                    AND dimension = '$length_value' 
                    AND dimension_unit = '$length_unit' 
                    LIMIT 1";
                $resDim = mysqli_query($conn, $checkDim);

                if ($resDim && mysqli_num_rows($resDim) > 0) {
                    $rowDim = mysqli_fetch_assoc($resDim);
                    $dimension_id = $rowDim['dimension_id'];
                } else {
                    $insertDim = "
                        INSERT INTO dimensions (dimension_category, dimension, dimension_unit) 
                        VALUES (1, '$length_value', '$length_unit')";
                    if (!mysqli_query($conn, $insertDim)) {
                        die("Error inserting lumber dimension: " . mysqli_error($conn));
                    }
                    $dimension_id = mysqli_insert_id($conn);
                }
            }
        }

        if ($product_category == 16) {
            $size = mysqli_real_escape_string($conn, $_POST['size'] ?? '');

            if (!empty($size)) {
                $checkDim = "
                    SELECT dimension_id 
                    FROM dimensions 
                    WHERE dimension_category = 16 
                    AND dimension = '$size' 
                    AND dimension_unit = 'inches' 
                    LIMIT 1";
                $resDim = mysqli_query($conn, $checkDim);

                if ($resDim && mysqli_num_rows($resDim) > 0) {
                    $rowDim = mysqli_fetch_assoc($resDim);
                    $dimension_id = $rowDim['dimension_id'];
                } else {
                    $insertDim = "
                        INSERT INTO dimensions (dimension_category, dimension, dimension_unit) 
                        VALUES (16, '$size', 'size')";
                    if (!mysqli_query($conn, $insertDim)) {
                        die("Error inserting screw dimension: " . mysqli_error($conn));
                    }
                    $dimension_id = mysqli_insert_id($conn);
                }
            }
        }

        $checkQuery = "SELECT inventory_id FROM inventory WHERE Product_id = '$Product_id' AND color_id = '$color_id' LIMIT 1";
        $result = mysqli_query($conn, $checkQuery);

        if (!$result) {
            die("Error executing query: " . mysqli_error($conn));
        }

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $inventory_id = $row['inventory_id'];

            if ($operation == 'add') {
                $updateQuery = "
                    UPDATE inventory SET
                        supplier_id  = '$supplier_id',
                        Warehouse_id = '$Warehouse_id',
                        Shelves_id   = '$Shelves_id',
                        Bin_id       = '$Bin_id',
                        Row_id       = '$Row_id',
                        Date         = '$Date',
                        quantity     = quantity + $quantity,
                        quantity_ttl = quantity_ttl + $quantity_ttl,
                        pack         = '$pack',
                        cost         = '$cost',
                        price        = '$price',
                        lumber_type  = '$lumber_type',
                        dimension_id = " . ($dimension_id ?: "NULL") . ",
                        addedby      = '$addedby'
                    WHERE inventory_id = '$inventory_id'";
            } else {
                $updateQuery = "
                    UPDATE inventory SET
                        supplier_id  = '$supplier_id',
                        Warehouse_id = '$Warehouse_id',
                        Shelves_id   = '$Shelves_id',
                        Bin_id       = '$Bin_id',
                        Row_id       = '$Row_id',
                        Date         = '$Date',
                        quantity     = '$quantity',
                        quantity_ttl = '$quantity_ttl',
                        pack         = '$pack',
                        cost         = '$cost',
                        price        = '$price',
                        lumber_type  = '$lumber_type',
                        dimension_id = " . ($dimension_id ?: "NULL") . ",
                        addedby      = '$addedby'
                    WHERE inventory_id = '$inventory_id'";
            }

            if (!mysqli_query($conn, $updateQuery)) {
                die("Error updating inventory: " . mysqli_error($conn));
            }

        } else {
            $insertQuery = "
                INSERT INTO inventory (
                    Product_id, color_id, supplier_id, Warehouse_id, Shelves_id,
                    Bin_id, Row_id, Date, quantity, pack, quantity_ttl, 
                    cost, price, lumber_type, addedby, dimension_id
                ) VALUES (
                    '$Product_id', '$color_id', '$supplier_id', '$Warehouse_id',
                    '$Shelves_id', '$Bin_id', '$Row_id', '$Date',
                    '$quantity', '$pack', '$quantity_ttl',
                    '$cost', '$price', '$lumber_type', '$addedby',
                    " . ($dimension_id ?: "NULL") . "
                )";

            if (!mysqli_query($conn, $insertQuery)) {
                die("Error inserting inventory: " . mysqli_error($conn));
            }

            $inventory_id = mysqli_insert_id($conn);
        }

        echo "success";
    }

    if ($action == "fetch_modal") {
        $Inventory_id = (int)($_POST['id'] ?? 0);

        $response = ["status" => "not_found"];

        if ($Inventory_id > 0) {
            $checkQuery = "
                SELECT i.*, p.product_category 
                FROM inventory i
                LEFT JOIN product p ON i.Product_id = p.product_id
                WHERE i.Inventory_id = '$Inventory_id' 
                LIMIT 1";
                
            $result = mysqli_query($conn, $checkQuery);
            
            if ($row = mysqli_fetch_assoc($result)) {
                $response = [
                    "status" => "found",
                    "data"   => $row
                ];
            }
        }

        echo json_encode($response);
        exit;
    }

    if ($action == "change_status") {
        $Inventory_id = mysqli_real_escape_string($conn, $_POST['inventory_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE inventory SET status = '$new_status' WHERE Inventory_id = '$Inventory_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }

    if ($action == "fetch_supplier_packs") {
        $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
    
        $query_pack = "SELECT * FROM supplier_pack WHERE supplierid = '$supplier_id' AND hidden = '0'";
        $result_pack = mysqli_query($conn, $query_pack);

        $packs = [];
        while ($row_pack = mysqli_fetch_assoc($result_pack)) {
            $packs[] = [
                'id' => $row_pack['id'],
                'pack' => $row_pack['pack'],
                'pack_count' => $row_pack['pack_count']
            ];
        }
        
        echo json_encode($packs);
    }

    if ($action == "fetch_supplier_cases") {
        $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
    
        $query_case = "SELECT * FROM supplier_case WHERE supplierid = '$supplier_id' AND hidden = '0'";
        $result_case = mysqli_query($conn, $query_case);

        $cases = [];
        while ($row_case = mysqli_fetch_assoc($result_case)) {
            $cases[] = [
                'id' => $row_case['id'],
                'case' => $row_case['case'],
                'case_count' => $row_case['case_count']
            ];
        }
        
        echo json_encode($cases);
    }
    
    mysqli_close($conn);
}
?>
