<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $id = mysqli_real_escape_string($conn, $_POST['id'] ?? null);
        $supplierid = mysqli_real_escape_string($conn, $_POST['supplierid'] ?? '');
        $color = mysqli_real_escape_string($conn, $_POST['color'] ?? '');
        $color_code = mysqli_real_escape_string($conn, $_POST['color_code'] ?? '');
        $userid = mysqli_real_escape_string($conn, $_POST['userid'] ?? '');

        $checkQuery = "SELECT * FROM supplier_color WHERE id = '$id'";
        $result = mysqli_query($conn, $checkQuery);

        if ($result && mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE supplier_color 
                            SET supplierid = '$supplierid', 
                                color = '$color', 
                                color_code = '$color_code', 
                                last_edit = NOW(), 
                                edited_by = '$userid' 
                            WHERE id = '$id'";

            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating color: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO supplier_color (supplierid, color, color_code, added_date, added_by) 
                            VALUES ('$supplierid', '$color', '$color_code', NOW(), '$userid')";

            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error adding color: " . mysqli_error($conn);
            }
        }
    } 
    
    if ($action == "change_status") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE supplier_color SET status = '$new_status' WHERE id = '$id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_supplier_color') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "UPDATE supplier_color SET hidden='1' WHERE id='$id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
