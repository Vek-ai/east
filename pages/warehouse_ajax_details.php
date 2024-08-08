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
    mysqli_close($conn);
}
?>
