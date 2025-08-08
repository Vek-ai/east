<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $pack_name = mysqli_real_escape_string($conn, $_POST['pack_name']);
        $pieces_count = mysqli_real_escape_string($conn, $_POST['pieces_count']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_pack WHERE id = '$id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_pack_name = $row['pack_name'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($pack_name != $current_pack_name) {
                $checkProductPack = "SELECT * FROM product_pack WHERE pack_name = '$pack_name'";
                $resultProductPack = mysqli_query($conn, $checkProductPack);
                if (mysqli_num_rows($resultProductPack) > 0) {
                    $duplicates[] = "Pack Name";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE product_pack SET pack_name = '$pack_name', pieces_count = '$pieces_count', description = '$description', last_edit = NOW(), edited_by = '$userid'  WHERE id = '$id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "Product pack updated successfully.";
                } else {
                    echo "Error updating product pack: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkProductPack = "SELECT * FROM product_pack WHERE pack_name = '$pack_name'";
            $resultProductPack = mysqli_query($conn, $checkProductPack);
            if (mysqli_num_rows($resultProductPack) > 0) {
                $duplicates[] = "Pack Name";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO product_pack (pack_name, pieces_count, description, added_date, added_by) VALUES ('$pack_name', '$pieces_count', '$description', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "New product pack added successfully.";
                } else {
                    echo "Error adding product pack: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product_pack SET status = '$new_status' WHERE id = '$id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_product_pack') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "UPDATE product_pack SET hidden='1' WHERE id='$id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
