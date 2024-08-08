<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);
        $color_name = mysqli_real_escape_string($conn, $_POST['color_name']);
        $color_code = mysqli_real_escape_string($conn, $_POST['color_code']);
        $color_group = mysqli_real_escape_string($conn, $_POST['color_group']);
        $provider_id = mysqli_real_escape_string($conn, $_POST['provider']);

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM paint_colors WHERE color_id = '$color_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_color_name = $row['color_name'];
            $current_color_code = $row['color_code'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($color_name != $current_color_name) {
                $checkColorName = "SELECT * FROM paint_colors WHERE color_name = '$color_name'";
                $resultColorName = mysqli_query($conn, $checkColorName);
                if (mysqli_num_rows($resultColorName) > 0) {
                    $duplicates[] = "Color Name";
                }
            }

            if ($color_code != $current_color_code) {
                $checkColorCode = "SELECT * FROM paint_colors WHERE color_code = '$color_code'";
                $resultColorCode = mysqli_query($conn, $checkColorCode);
                if (mysqli_num_rows($resultColorCode) > 0) {
                    $duplicates[] = "Color Code";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE paint_colors SET color_name = '$color_name', color_code = '$color_code', color_group = '$color_group', provider_id = '$provider_id', last_edit = NOW(), edited_by = '$userid'  WHERE color_id = '$color_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "Paint color updated successfully.";
                } else {
                    echo "Error updating paint color: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkColorName = "SELECT * FROM paint_colors WHERE color_name = '$color_name'";
            $resultColorName = mysqli_query($conn, $checkColorName);
            if (mysqli_num_rows($resultColorName) > 0) {
                $duplicates[] = "Color Name";
            }

            $checkColorCode = "SELECT * FROM paint_colors WHERE color_code = '$color_code'";
            $resultColorCode = mysqli_query($conn, $checkColorCode);
            if (mysqli_num_rows($resultColorCode) > 0) {
                $duplicates[] = "Color Code";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO paint_colors (color_name, color_code, color_group, provider_id, added_date, added_by) VALUES ('$color_name', '$color_code', '$color_group', '$provider_id', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "New paint color added successfully.";
                } else {
                    echo "Error adding paint color: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE paint_colors SET color_status = '$new_status' WHERE color_id = '$color_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_paint_color') {
        $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);
        $query = "UPDATE paint_colors SET hidden='1' WHERE color_id='$color_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
