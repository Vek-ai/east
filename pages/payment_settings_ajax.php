<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $payment_setting_name = mysqli_real_escape_string($conn, $_POST['payment_setting_name']);
        $value = mysqli_real_escape_string($conn, $_POST['value']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM payment_settings WHERE payment_setting_id = '$id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_payment_setting_name = $row['payment_setting_name'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($payment_setting_name != $current_payment_setting_name) {
                $checkSettingName = "SELECT * FROM payment_settings WHERE payment_setting_name = '$payment_setting_name'";
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
                $updateQuery = "UPDATE payment_settings SET payment_setting_name = '$payment_setting_name', value = '$value' WHERE payment_setting_id = '$id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "success_update";
                } else {
                    echo "Error updating setting: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkTaxStatusDesc = "SELECT * FROM payment_settings WHERE payment_setting_name = '$payment_setting_name'";
            $resultTaxStatusDesc = mysqli_query($conn, $checkTaxStatusDesc);
            if (mysqli_num_rows($resultTaxStatusDesc) > 0) {
                $duplicates[] = "Setting Name";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO payment_settings (payment_setting_name, value) VALUES ('$payment_setting_name', '$value')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "success_add";
                } else {
                    echo "Error adding setting: " . mysqli_error($conn);
                }
            }
        }
    } 

    if ($action == "delete") {
      $id = mysqli_real_escape_string($conn, $_POST['id']);

      // SQL query to delete the record
      $deleteQuery = "DELETE FROM payment_settings WHERE payment_setting_id = '$id'";
      if (mysqli_query($conn, $deleteQuery)) {
          echo "success_delete";
      } else {
          echo "Error deleting setting: " . mysqli_error($conn);
      }
    }
    mysqli_close($conn);
}
?>
