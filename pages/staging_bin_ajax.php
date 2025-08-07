<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/send_email.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_inventory") {
        $operation = mysqli_real_escape_string($conn, $_POST['operation']);
        $staging_bin_id = mysqli_real_escape_string($conn, $_POST['id']);
        $Warehouse_id = mysqli_real_escape_string($conn, $_POST['Warehouse_id']);
        $Shelves_id = mysqli_real_escape_string($conn, $_POST['Shelves_id']);
        $Bin_id = mysqli_real_escape_string($conn, $_POST['Bin_id']);
        $Row_id = mysqli_real_escape_string($conn, $_POST['Row_id']);
        $Date = mysqli_real_escape_string($conn, $_POST['Date']);
        $addedby = $_SESSION['userid'];
    
        $checkQuery  = "SELECT * 
                        FROM staging_bin sb 
                        LEFT JOIN supplier_orders_prod sop ON sb.supplier_orders_prod_id = sop.id 
                        LEFT JOIN supplier_orders so ON sop.supplier_order_id = so.supplier_order_id 
                        WHERE sb.id = '$staging_bin_id'";

        $result = mysqli_query($conn, $checkQuery);

        if (!$result) {
            die("Error executing query: " . mysqli_error($conn));
        }

        if ($row = mysqli_fetch_assoc($result)) {
            $Product_id = $row['product_id'];
            $color_id = $row['color_id'];
            $quantity = $row['quantity'];
            $supplier_id = $row['supplier_id'];
        } else {
            die("No staging bin record found.");
        }
    
        $checkQuery = "SELECT * FROM inventory 
                        WHERE 
                            Product_id = '$Product_id' 
                            AND color_id = '$color_id' 
                            AND Warehouse_id = '$Warehouse_id' 
                            AND Shelves_id = '$Shelves_id' 
                            AND Bin_id = '$Bin_id' 
                            AND Row_id = '$Row_id'";
        $result = mysqli_query($conn, $checkQuery);
    
        if (!$result) {
            die("Error executing query: " . mysqli_error($conn));
        }

        $is_success = false;
    
        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE inventory SET 
                                quantity = quantity + '$quantity',
                                quantity_ttl = quantity_ttl + '$quantity'
                            WHERE 
                                Product_id = '$Product_id' 
                                AND color_id = '$color_id'
                                AND Warehouse_id = '$Warehouse_id' 
                                AND Shelves_id = '$Shelves_id' 
                                AND Bin_id = '$Bin_id' 
                                AND Row_id = '$Row_id'";
    
            if (mysqli_query($conn, $updateQuery)) {
                $is_success = true;
            } else {
                die("Error updating record: " . mysqli_error($conn));
            }
        } else {
            $insertQuery = "INSERT INTO inventory (
                                Product_id, 
                                color_id, 
                                supplier_id, 
                                Warehouse_id, 
                                Shelves_id, 
                                Bin_id, 
                                Row_id, 
                                Date, 
                                quantity, 
                                pack, 
                                quantity_ttl, 
                                addedby
                            ) VALUES (
                                '$Product_id', 
                                '$color_id', 
                                '$supplier_id', 
                                '$Warehouse_id',
                                '$Shelves_id', 
                                '$Bin_id', 
                                '$Row_id', 
                                '$Date', 
                                '$quantity', 
                                '', 
                                '$quantity', 
                                '$addedby'
                            )";
    
            if (mysqli_query($conn, $insertQuery)) {
                $is_success = true;
            } else {
                die("Error inserting record: " . mysqli_error($conn));
            }
        }

        if ($is_success) {
            $updateQuery = "UPDATE 
                                staging_bin 
                            SET 
                                status = '1' 
                            WHERE 
                                id = '$staging_bin_id'";
        
            if (mysqli_query($conn, $updateQuery)) {
                if (mysqli_affected_rows($conn) > 0) {
                    echo "success";
                } else {
                    echo "No rows updated";
                }
            }
        }
    }
    
    mysqli_close($conn);
}
?>
