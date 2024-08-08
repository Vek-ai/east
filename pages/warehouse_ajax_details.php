<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    
    if ($action == "fetch_info") {
        $warehouse_id = mysqli_real_escape_string($conn, $_REQUEST['warehouse_id']);

        $checkQuery = "SELECT * FROM warehouses WHERE WarehouseID = '$warehouse_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
        }

        
    }

    if ($action == "add_update_bin") {
        $BinID = mysqli_real_escape_string($conn, $_POST['BinID']);
        $WarehouseID = mysqli_real_escape_string($conn, $_POST['WarehouseID']);
        $BinCode = mysqli_real_escape_string($conn, $_POST['BinCode']);
        $Description = mysqli_real_escape_string($conn, $_POST['Description']);
    
        $checkQuery = "SELECT * FROM bins WHERE BinID = '$BinID'";
        $result = mysqli_query($conn, $checkQuery);
    
        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "
                UPDATE bins 
                SET 
                    BinCode = '$BinCode', 
                    Description = '$Description'
                WHERE BinID = '$BinID'
            ";
    
            if (mysqli_query($conn, $updateQuery)) {
                echo "$updateQuery";
            } else {
                echo "Error updating warehouse: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "
                INSERT INTO bins (
                    BinCode,
                    WarehouseID,
                    Description
                ) VALUES (
                    '$BinCode', 
                    '$WarehouseID', 
                    '$Description'
                )
            ";
    
            if (mysqli_query($conn, $insertQuery)) {
                echo "success";
            } else {
                echo "Error adding warehouse: " . mysqli_error($conn);
            }
        }
    }

    if ($action == "add_update_row") {
        $WarehouseRowID = mysqli_real_escape_string($conn, $_POST['WarehouseRowID']);
        $WarehouseID = mysqli_real_escape_string($conn, $_POST['WarehouseID']);
        $RowCode = mysqli_real_escape_string($conn, $_POST['RowCode']);
        $Description = mysqli_real_escape_string($conn, $_POST['Description']);
    
        $checkQuery = "SELECT * FROM warehouse_rows WHERE RowCode = '$WarehouseRowID'";
        $result = mysqli_query($conn, $checkQuery);
    
        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "
                UPDATE warehouse_rows 
                SET 
                    RowCode = '$RowCode', 
                    Description = '$Description'
                WHERE WarehouseRowID = '$WarehouseRowID'
            ";
    
            if (mysqli_query($conn, $updateQuery)) {
                echo "success";
            } else {
                echo "Error updating warehouse: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "
                INSERT INTO warehouse_rows (
                    RowCode,
                    WarehouseID,
                    Description
                ) VALUES (
                    '$RowCode', 
                    '$WarehouseID', 
                    '$Description'
                )
            ";
    
            if (mysqli_query($conn, $insertQuery)) {
                echo "success";
            } else {
                echo "Error adding warehouse: " . mysqli_error($conn);
            }
        }
    }

    if ($action == "add_update_shelf") {
        $ShelfID = mysqli_real_escape_string($conn, $_POST['ShelfID']);
        $WarehouseRowID = mysqli_real_escape_string($conn, $_POST['WarehouseRowID']);
        $ShelfCode = mysqli_real_escape_string($conn, $_POST['ShelfCode']);
        $Description = mysqli_real_escape_string($conn, $_POST['Description']);
    
        $checkQuery = "SELECT * FROM shelves WHERE ShelfID = '$ShelfID'";
        $result = mysqli_query($conn, $checkQuery);
    
        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "
                UPDATE shelves 
                SET 
                    WarehouseRowID = '$WarehouseRowID', 
                    ShelfCode = '$ShelfCode', 
                    Description = '$Description'
                WHERE WarehouseRowID = '$WarehouseRowID'
            ";
    
            if (mysqli_query($conn, $updateQuery)) {
                echo "success";
            } else {
                echo "Error updating warehouse: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "
                INSERT INTO shelves (
                    WarehouseRowID,
                    ShelfCode,
                    Description
                ) VALUES (
                    '$WarehouseRowID', 
                    '$ShelfCode', 
                    '$Description'
                )
            ";
    
            if (mysqli_query($conn, $insertQuery)) {
                echo "success";
            } else {
                echo "Error adding warehouse: " . mysqli_error($conn);
            }
        }
    }

    mysqli_close($conn);
}
?>
