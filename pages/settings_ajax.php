<?php
require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $settingid = mysqli_real_escape_string($conn, $_POST['settingid']);
        $setting_name = mysqli_real_escape_string($conn, $_POST['setting_name']);
        $value = mysqli_real_escape_string($conn, $_POST['value']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM settings WHERE settingid = '$settingid'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_setting_name = $row['setting_name'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($setting_name != $current_setting_name) {
                $checkSettingName = "SELECT * FROM settings WHERE setting_name = '$setting_name'";
                $resultSettingName = mysqli_query($conn, $checkSettingName);
                if (mysqli_num_rows($resultSettingName) > 0) {
                    $duplicates[] = "Setting Name";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE settings SET setting_name = '$setting_name', value = '$value' WHERE settingid = '$settingid'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "Setting updated successfully.";
                } else {
                    echo "Error updating setting: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkTaxStatusDesc = "SELECT * FROM settings WHERE setting_name = '$setting_name'";
            $resultTaxStatusDesc = mysqli_query($conn, $checkTaxStatusDesc);
            if (mysqli_num_rows($resultTaxStatusDesc) > 0) {
                $duplicates[] = "Setting Name";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO settings (setting_name, value) VALUES ('$setting_name', '$value')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "New setting added successfully.";
                } else {
                    echo "Error adding setting: " . mysqli_error($conn);
                }
            }
        }
    } 

    if ($action == "delete") {
      $settingid = mysqli_real_escape_string($conn, $_POST['settingid']);

      // SQL query to delete the record
      $deleteQuery = "DELETE FROM settings WHERE settingid = '$settingid'";
      if (mysqli_query($conn, $deleteQuery)) {
          echo "Setting deleted successfully.";
      } else {
          echo "Error deleting setting: " . mysqli_error($conn);
      }
    }
    mysqli_close($conn);
}
?>