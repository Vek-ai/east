<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $profile_type_id = mysqli_real_escape_string($conn, $_POST['profile_type_id']);
        $profile_type = mysqli_real_escape_string($conn, $_POST['profile_type']);
        $profile_abbreviations = mysqli_real_escape_string($conn, $_POST['profile_abbreviations']);
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM profile_type WHERE profile_type_id = '$profile_type_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_profile_type = $row['profile_type'];
            $current_profile_abbreviations = $row['profile_abbreviations'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($profile_type != $current_profile_type) {
                $checkProfile = "SELECT * FROM profile_type WHERE profile_type = '$profile_type'";
                $resultProfile = mysqli_query($conn, $checkprofile);
                if (mysqli_num_rows($resultProfile) > 0) {
                    $duplicates[] = "Profile type";
                }
            }

            if ($profile_abbreviations != $current_profile_abbreviations) {
                $checkAbreviations = "SELECT * FROM profile_type WHERE profile_abbreviations = '$profile_abbreviations'";
                $resultAbreviations = mysqli_query($conn, $checkAbreviations);
                if (mysqli_num_rows($resultAbreviations) > 0) {
                    $duplicates[] = "Profile type Abbreviations";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE profile_type SET profile_type = '$profile_type', profile_abbreviations = '$profile_abbreviations', notes = '$notes', last_edit = NOW(), edited_by = '$userid'  WHERE profile_type_id = '$profile_type_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "update-success";
                } else {
                    echo "Error updating profile type: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkProfile = "SELECT * FROM profile_type WHERE profile_type = '$profile_type'";
            $resultProfile = mysqli_query($conn, $checkProfile);
            if (mysqli_num_rows($resultProfile) > 0) {
                $duplicates[] = "Profile type";
            }

            $checkAbreviations = "SELECT * FROM profile_type WHERE profile_abbreviations = '$profile_abbreviations'";
            $resultAbreviations = mysqli_query($conn, $checkAbreviations);
            if (mysqli_num_rows($resultAbreviations) > 0) {
                $duplicates[] = "Profile type Abbreviations";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO profile_type (profile_type, profile_abbreviations, notes, added_date, added_by) VALUES ('$profile_type', '$profile_abbreviations', '$notes', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "add-success";
                } else {
                    echo "Error adding profile type: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $profile_type_id = mysqli_real_escape_string($conn, $_POST['profile_type_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE profile_type SET status = '$new_status' WHERE profile_type_id = '$profile_type_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_profile_type') {
        $profile_type_id = mysqli_real_escape_string($conn, $_POST['profile_type_id']);
        $query = "UPDATE profile_type SET hidden='1' WHERE profile_type_id='$profile_type_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
