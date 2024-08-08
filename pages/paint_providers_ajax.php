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
            $provider_name = $row['provider_name'];
            $contact_person = $row['contact_person'];
            $contact_email = $row['contact_email'];
            $contact_phone = $row['contact_phone'];
            $address = $row['address'];
            $website = $row['website'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($paint_providers != $current_provider_name) {
                $checkProviderName = "SELECT * FROM paint_providers WHERE provider_name = '$provider_name'";
                $resultProviderName = mysqli_query($conn, $checkProviderName);
                if (mysqli_num_rows($resultProviderName) > 0) {
                    $duplicates[] = "Paint Provider";
                }
            }

            if ($contact_person != $current_contact_person) {
                $checkContactPerson = "SELECT * FROM paint_providers WHERE contact_person = '$contact_person'";
                $resultContactPerson = mysqli_query($conn, $checkContactPerson);
                if (mysqli_num_rows($resultContactPerson) > 0) {
                    $duplicates[] = "Contact Person";
                }
            }

            if ($contact_email != $current_contact_email) {
                $checkContactEmail = "SELECT * FROM paint_providers WHERE contact_email = '$contact_email'";
                $resultContactEmail = mysqli_query($conn, $checkContactEmail);
                if (mysqli_num_rows($resultContactEmail) > 0) {
                    $duplicates[] = "Email";
                }
            }

            if ($contact_phone != $current_contact_phone) {
                $checkContactPhone = "SELECT * FROM paint_providers WHERE contact_phone = '$contact_phone'";
                $resultContactPhone = mysqli_query($conn, $checkContactPhone);
                if (mysqli_num_rows($resultContactPhone) > 0) {
                    $duplicates[] = "Phone";
                }
            }

            if ($address != $current_line_abreviations) {
                $checkAddress = "SELECT * FROM paint_providers WHERE address = '$address'";
                $resultAddress = mysqli_query($conn, $checkAddress);
                if (mysqli_num_rows($resultAddress) > 0) {
                    $duplicates[] = "Address";
                }
            }

            if ($website != $current_line_abreviations) {
                $checkWebsite = "SELECT * FROM paint_providers WHERE website = '$website'";
                $resultWebsite = mysqli_query($conn, $checkWebsite);
                if (mysqli_num_rows($resultWebsite) > 0) {
                    $duplicates[] = "Website";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE paint_providers SET provider_nam = '$provider_name', contact_person = '$contact_person', contact_email = '$contact_email', contact_phone = '$contact_phone', address = '$address', website = '$website'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "Paint provider updated successfully.";
                } else {
                    echo "Error updating product line: " . mysqli_error($conn);
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

            $checkContactPerson = "SELECT * FROM paint_providers WHERE contact_person = '$contact_person'";
            $resultContactPerson = mysqli_query($conn, $checkContactPerson);
            if (mysqli_num_rows($resultContactPerson) > 0) {
                $duplicates[] = "Contact Person";
            }

            $checkContactEmail = "SELECT * FROM paint_providers WHERE contact_email = '$contact_email'";
            $resultContactEmail = mysqli_query($conn, $checkContactEmail);
            if (mysqli_num_rows($resultContactEmail) > 0) {
                $duplicates[] = "Email";
            }
            
            $checkContactPhone = "SELECT * FROM paint_providers WHERE contact_phone = '$contact_phone'";
            $resultContactPhone = mysqli_query($conn, $checkContactPhone);
            if (mysqli_num_rows($resultContactPhone) > 0) {
                $duplicates[] = "Phone";
            }

            $checkAddress = "SELECT * FROM paint_providers WHERE address = '$address'";
            $resultAddress = mysqli_query($conn, $checkAddress);
            if (mysqli_num_rows($resultAddress) > 0) {
                $duplicates[] = "Address";
            }

            $checkWebsite = "SELECT * FROM paint_providers WHERE website = '$website'";
            $resultWebsite = mysqli_query($conn, $checkWebsite);
            if (mysqli_num_rows($resultWebsite) > 0) {
                $duplicates[] = "Website";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO paint_providers (provider_name, contact_person, contact_email, contact_phone, address, website) VALUES ('$provider_name', '$provider_name', '$contact_person', '$contact_email', '$contact_phone', '$address', '$website')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "New Paint provider added successfully.";
                } else {
                    echo "Error adding product line: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $product_line_id = mysqli_real_escape_string($conn, $_POST['product_line_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE paint_providers SET status = '$new_status' WHERE product_line_id = '$product_line_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_product_line') {
        $product_line_id = mysqli_real_escape_string($conn, $_POST['product_line_id']);
        $query = "UPDATE paint_providers SET hidden='1' WHERE product_line_id='$product_line_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
