<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $provider_id = mysqli_real_escape_string($conn, $_POST['provider_id']);
        $provider_name = mysqli_real_escape_string($conn, $_POST['provider_name']);
        $contact_person = mysqli_real_escape_string($conn, $_POST['contact_person']);
        $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email']);
        $contact_phone = mysqli_real_escape_string($conn, $_POST['contact_phone']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $website = mysqli_real_escape_string($conn, $_POST['website']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM paint_providers WHERE provider_id = '$provider_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_provider_name = $row['provider_name'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($provider_name != $current_provider_name) {
                $checkProviderName = "SELECT * FROM paint_providers WHERE provider_name = '$provider_name'";
                $resultProviderName = mysqli_query($conn, $checkProviderName);
                if (mysqli_num_rows($resultProviderName) > 0) {
                    $duplicates[] = "Paint Provider";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE paint_providers SET 
                                        provider_name = '$provider_name', 
                                        contact_person = '$contact_person', 
                                        contact_email = '$contact_email', 
                                        contact_phone = '$contact_phone', 
                                        address = '$address', 
                                        website = '$website'
                                WHERE provider_id = '$provider_id'
                                ";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "update-success";
                } else {
                    echo "Error updating paint provider: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkProviderName = "SELECT * FROM paint_providers WHERE provider_name = '$provider_name'";
            $resultProviderName = mysqli_query($conn, $checkProviderName);
            if (mysqli_num_rows($resultProviderName) > 0) {
                $duplicates[] = "Paint Provider";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);

                echo "$checkQuery";
            } else {
                $insertQuery = "INSERT INTO paint_providers (
                                        provider_name, 
                                        contact_person, 
                                        contact_email, 
                                        contact_phone, 
                                        address, 
                                        website) 
                                VALUES (
                                        '$provider_name',
                                        '$contact_person',
                                        '$contact_email',
                                        '$contact_phone',
                                        '$address',
                                        '$website'
                                )";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "add-success";
                } else {
                    echo "Error adding paint provider: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $provider_id = mysqli_real_escape_string($conn, $_POST['provider_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE paint_providers SET provider_status = '$new_status' WHERE provider_id  = '$provider_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_paint_provider') {
        $provider_id = mysqli_real_escape_string($conn, $_POST['provider_id']);
        $query = "UPDATE paint_providers SET hidden='1' WHERE provider_id='$provider_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
