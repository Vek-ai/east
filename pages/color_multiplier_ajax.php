<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $id = mysqli_real_escape_string($conn, $_POST['id'] ?? null);
        $color = mysqli_real_escape_string($conn, $_POST['color'] ?? '');
        $multiplier = floatval(mysqli_real_escape_string($conn, $_POST['multiplier'] ?? 0));
        $userid = mysqli_real_escape_string($conn, $_POST['userid'] ?? '');

        $checkQuery = "SELECT * FROM color_multiplier WHERE id = '$id'";
        $result = mysqli_query($conn, $checkQuery);

        if ($result && mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE color_multiplier 
                            SET color = '$color', 
                                multiplier = '$multiplier', 
                                last_edit = NOW(), 
                                edited_by = '$userid' 
                            WHERE id = '$id'";

            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating supplier pack: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO color_multiplier (color, multiplier, added_by) 
                            VALUES ('$color', '$multiplier', '$userid')";

            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error adding supplier pack: " . mysqli_error($conn);
            }
        }
    } 
    
    if ($action == "change_status") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE color_multiplier SET status = '$new_status' WHERE id = '$id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_color_multiplier') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "UPDATE color_multiplier SET hidden='1' WHERE id='$id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
