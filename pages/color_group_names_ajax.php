<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $color_group_name_id = mysqli_real_escape_string($conn, $_POST['color_group_name_id']);
        $color_group_name = mysqli_real_escape_string($conn, $_POST['color_group_name']);
        $group_abbreviations = mysqli_real_escape_string($conn, $_POST['group_abbreviations']);
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM color_group_name WHERE color_group_name_id = '$color_group_name_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_color_group_name = $row['color_group_name'];
            $current_group_abbreviations = $row['group_abbreviations'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($color_group_name != $current_color_group_name) {
                $checkColorGroupName = "SELECT * FROM color_group_name WHERE color_group_name = '$color_group_name'";
                $resultColorGroupName = mysqli_query($conn, $checkColorGroupName);
                if (mysqli_num_rows($resultColorGroupName) > 0) {
                    $duplicates[] = "Color Group Name";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE color_group_name SET color_group_name = '$color_group_name', group_abbreviations = '$group_abbreviations', notes = '$notes', last_edit = NOW(), edited_by = '$userid'  WHERE color_group_name_id = '$color_group_name_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "update-success";
                } else {
                    echo "Error updating Color Group Name: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkColorGroupName = "SELECT * FROM color_group_name WHERE color_group_name = '$color_group_name'";
            $resultColorGroupName = mysqli_query($conn, $checkColorGroupName);
            if (mysqli_num_rows($resultColorGroupName) > 0) {
                $duplicates[] = "Color Group Name";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO color_group_name (color_group_name, group_abbreviations, notes, added_date, added_by) VALUES ('$color_group_name', '$group_abbreviations', '$notes', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "add-success";
                } else {
                    echo "Error adding Color Group Name: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $color_group_name_id = mysqli_real_escape_string($conn, $_POST['color_group_name_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE color_group_name SET status = '$new_status' WHERE color_group_name_id = '$color_group_name_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_color_group_name') {
        $color_group_name_id = mysqli_real_escape_string($conn, $_POST['color_group_name_id']);
        $query = "UPDATE color_group_name SET hidden='1' WHERE color_group_name_id='$color_group_name_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
