<?php
session_start();
require '../includes/dbconn.php';
$permission = $_SESSION['permission'];

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

    if ($action == "update_address") {
        $address = mysqli_real_escape_string($conn, isset($_POST['address']) ? $_POST['address'] : '');
        $city = mysqli_real_escape_string($conn, isset($_POST['city']) ? $_POST['city'] : '');
        $state = mysqli_real_escape_string($conn, isset($_POST['state']) ? $_POST['state'] : '');
        $zip = mysqli_real_escape_string($conn, isset($_POST['zip']) ? $_POST['zip'] : '');
        $lat = mysqli_real_escape_string($conn, isset($_POST['lat']) ? $_POST['lat'] : '');
        $lng = mysqli_real_escape_string($conn, isset($_POST['lng']) ? $_POST['lng'] : '');
    
        $data = [
            'address' => $address ?: '',
            'city' => $city ?: '',
            'state' => $state ?: '',
            'zip' => $zip ?: '',
            'lat' => $lat ?: '',
            'lng' => $lng ?: ''
        ];
        $jsonData = json_encode($data);
        $checkQuery = "SELECT * FROM settings WHERE setting_name = 'address'";
        $result = mysqli_query($conn, $checkQuery);
    
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $settingid = mysqli_real_escape_string($conn, $row['settingid']);
            $updateQuery = "UPDATE settings SET value = '$jsonData' WHERE settingid = '$settingid'";
    
            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update_address";
            } else {
                echo "Error updating setting: " . mysqli_error($conn);
            }
        } else {
            echo "No address setting found.";
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

    if ($action  == 'update_order_points') {
        $order_total = floatval($_POST['order_total']);
        $points_gained = intval($_POST['points_gained']);

        $json_data = json_encode([
            'order_total' => $order_total,
            'points_gained' => $points_gained
        ]);

        $json_data_escaped = mysqli_real_escape_string($conn, $json_data);

        $update_sql = "UPDATE settings SET value = '$json_data_escaped' WHERE setting_name = 'points'";
        $update_result = mysqli_query($conn, $update_sql);

        if ($update_result) {
            echo json_encode([
                'success' => true,
                'message' => 'Settings updated successfully.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database update failed.',
                'error' => mysqli_error($conn)
            ]);
        }

        exit;
    }

    if ($action  == 'update_contractor_order_points') {
        $order_total = floatval($_POST['order_total']);
        $points_gained = intval($_POST['points_gained']);

        $json_data = json_encode([
            'order_total' => $order_total,
            'points_gained' => $points_gained
        ]);

        $json_data_escaped = mysqli_real_escape_string($conn, $json_data);

        $update_sql = "UPDATE settings SET value = '$json_data_escaped' WHERE setting_name = 'contractor_points'";
        $update_result = mysqli_query($conn, $update_sql);

        if ($update_result) {
            echo json_encode([
                'success' => true,
                'message' => 'Settings updated successfully.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database update failed.',
                'error' => mysqli_error($conn)
            ]);
        }

        exit;
    }

    if ($action == "toggle_points") {
        $status = $_POST['status'] ?? '';
        $newValue = ($status === 'enable') ? 1 : 0;

        $sql = "UPDATE settings SET value = '$newValue' WHERE setting_name = 'is_points_enabled'";

        if (mysqli_query($conn, $sql)) {
            echo json_encode([
                "status" => "success",
                "new_value" => $newValue
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => mysqli_error($conn)
            ]);
        }
    }

    mysqli_close($conn);
}
?>
