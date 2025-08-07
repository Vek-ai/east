<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $trim_color_id = mysqli_real_escape_string($conn, $_POST['trim_color_id']);
        $trim_color = mysqli_real_escape_string($conn, $_POST['trim_color']);
        $multiplier = mysqli_real_escape_string($conn, floatval($_POST['multiplier'] ?? 0.00));
        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        $checkQuery = "SELECT * FROM trim_color WHERE trim_color_id = '$trim_color_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $current_trim_color = $row['trim_color'];

            $duplicates = array();
            if ($trim_color != $current_trim_color) {
                $checkTrimMultiplier = "SELECT * FROM trim_color WHERE trim_color = '$trim_color'";
                $resultTrimMultiplier = mysqli_query($conn, $checkTrimMultiplier);
                if (mysqli_num_rows($resultTrimMultiplier) > 0) {
                    $duplicates[] = "Trim Multiplier";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $updateQuery = "UPDATE trim_color SET trim_color = '$trim_color', multiplier = '$multiplier', last_edit = NOW(), edited_by = '$userid'  WHERE trim_color_id = '$trim_color_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "success_update";
                } else {
                    echo "Error updating category: " . mysqli_error($conn);
                }
            }
        } else {
            $duplicates = array();
            $checkTrimMultiplier = "SELECT * FROM trim_color WHERE trim_color = '$trim_color'";
            $resultTrimMultiplier = mysqli_query($conn, $checkTrimMultiplier);
            if (mysqli_num_rows($resultTrimMultiplier) > 0) {
                $duplicates[] = "Trim Multiplier";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO trim_color (trim_color, multiplier, added_date, added_by) VALUES ('$trim_color', '$multiplier', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "success_add";
                } else {
                    echo "Error adding category: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $trim_color_id = mysqli_real_escape_string($conn, $_POST['trim_color_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';
        $statusQuery = "UPDATE trim_color SET status = '$new_status' WHERE trim_color_id = '$trim_color_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_trim_color') {
        $trim_color_id = mysqli_real_escape_string($conn, $_POST['trim_color_id']);
        $query = "UPDATE trim_color SET hidden='1' WHERE trim_color_id='$trim_color_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
