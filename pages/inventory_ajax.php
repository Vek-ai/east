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
        $price        = mysqli_real_escape_string($conn, $_POST['price'] ?? '0');
        $dimension_id = mysqli_real_escape_string($conn, $_POST['dimension_id'] ?? '0');
        $addedby      = $_SESSION['userid'];

        $lumber_type         = mysqli_real_escape_string($conn, $_POST['lumber_type']);

        $length_value = mysqli_real_escape_string($conn, $_POST['length_value']);
        $length_unit  = mysqli_real_escape_string($conn, $_POST['length_unit']);
        $formatted_length = "$length_value $length_unit";

        $product = getProductDetails($Product_id);
        $product_category = $product['product_category'] ?? 0;

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
                        dimension_id = '$dimension_id',
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
                        dimension_id = '$dimension_id',
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
                    '$dimension_id'
                )";

            if (!mysqli_query($conn, $insertQuery)) {
                die("Error inserting inventory: " . mysqli_error($conn));
            }

            $inventory_id = mysqli_insert_id($conn);
        }

        $check_length = mysqli_query($conn, "SELECT variant_id FROM product_variant_length WHERE inventory_id = '$inventory_id'");
        if (!$check_length) {
            echo "Error checking length: " . mysqli_error($conn);
            exit;
        }

        if (mysqli_num_rows($check_length) > 0) {
            if (!mysqli_query($conn, "UPDATE product_variant_length SET length = '$formatted_length' WHERE inventory_id = '$inventory_id'")) {
                echo "Error updating length: " . mysqli_error($conn);
                exit;
            }
        } else {
            if (!mysqli_query($conn, "INSERT INTO product_variant_length (inventory_id, length) VALUES ('$inventory_id', '$formatted_length')")) {
                echo "Error inserting length: " . mysqli_error($conn);
                exit;
            }
        }

        echo "success";
    }

    if ($action == "fetch_modal") {
        $Inventory_id = (int)($_POST['id'] ?? 0);

        $response = ["status" => "not_found"];

        if ($Inventory_id > 0) {
            $checkQuery = "
                SELECT i.*, p.product_category, pvl.length AS variant_length
                FROM inventory i
                LEFT JOIN product p ON i.Product_id = p.product_id
                LEFT JOIN product_variant_length pvl ON i.Inventory_id = pvl.inventory_id
                WHERE i.Inventory_id = '$Inventory_id'
                LIMIT 1
            ";
                
            $result = mysqli_query($conn, $checkQuery);
            
            if ($row = mysqli_fetch_assoc($result)) {
                $length_value = 0;
                $length_unit  = '';

                if (!empty($row['variant_length'])) {
                    $parts = explode(' ', $row['variant_length'], 2);
                    $length_value = floatval($parts[0]);
                    $length_unit  = $parts[1] ?? '';
                }

                $response = [
                    "status"       => "found",
                    "data"         => $row,
                    "length_value" => $length_value,
                    "length_unit"  => $length_unit
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
