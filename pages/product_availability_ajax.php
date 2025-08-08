<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_availability_id = mysqli_real_escape_string($conn, $_POST['product_availability_id']);
        $product_availability = mysqli_real_escape_string($conn, $_POST['product_availability']);
        $multiplier = mysqli_real_escape_string($conn, $_POST['multiplier']);
        $availability_abbreviations = mysqli_real_escape_string($conn, $_POST['availability_abbreviations']);
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_availability WHERE product_availability_id = '$product_availability_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_product_availability = $row['product_availability'];
            $current_avail_abbreviations = $row['availability_abbreviations'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($product_availability != $current_product_availability) {
                $checkAvailability = "SELECT * FROM product_availability WHERE product_availability = '$product_availability'";
                $resultAvailability = mysqli_query($conn, $checkAvailability);
                if (mysqli_num_rows($resultAvailability) > 0) {
                    $duplicates[] = "Availability";
                }
            }

            if ($availability_abbreviations != $current_avail_abbreviations) {
                $checkAbreviations = "SELECT * FROM product_availability WHERE availability_abbreviations = '$availability_abbreviations'";
                $resultAbreviations = mysqli_query($conn, $checkAbreviations);
                if (mysqli_num_rows($resultAbreviations) > 0) {
                    $duplicates[] = "Availability Abbreviations";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE product_availability SET 
                                    product_availability = '$product_availability',
                                    availability_abbreviations = '$availability_abbreviations',
                                    multiplier = '$multiplier',
                                    notes = '$notes', 
                                    last_edit = NOW(), 
                                    edited_by = '$userid'  
                                WHERE 
                                    product_availability_id = '$product_availability_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "update-success";
                } else {
                    echo "Error updating availability type: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkAvailability = "SELECT * FROM product_availability WHERE product_availability = '$product_availability'";
            $resultAvailability = mysqli_query($conn, $checkAvailability);
            if (mysqli_num_rows($resultAvailability) > 0) {
                $duplicates[] = "Availability";
            }

            $checkAbreviations = "SELECT * FROM product_availability WHERE availability_abbreviations = '$availability_abbreviations'";
            $resultAbreviations = mysqli_query($conn, $checkAbreviations);
            if (mysqli_num_rows($resultAbreviations) > 0) {
                $duplicates[] = "Availability Abbreviations";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO product_availability (
                                        product_availability, 
                                        availability_abbreviations, 
                                        multiplier,
                                        notes, 
                                        added_date, 
                                        added_by) 
                                VALUES (
                                        '$product_availability', 
                                        '$availability_abbreviations', 
                                        '$multiplier',
                                        '$notes', 
                                        NOW(), 
                                        '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "add-success";
                } else {
                    echo "Error adding availability type: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $product_availability_id = mysqli_real_escape_string($conn, $_POST['product_availability_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product_availability SET status = '$new_status' WHERE product_availability_id = '$product_availability_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_product_availability') {
        $product_availability_id = mysqli_real_escape_string($conn, $_POST['product_availability_id']);
        $query = "UPDATE product_availability SET hidden='1' WHERE product_availability_id='$product_availability_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'fetch_modal_content') {
        $product_availability_id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM product_availability WHERE product_availability_id = '$product_availability_id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
        }

        ?>
        <div class="row pt-3">
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Availability</label>
                <input type="text" id="product_availability" name="product_availability" class="form-control"  value="<?= $row['product_availability'] ?? '' ?>"/>
            </div>
            </div>
            
        </div>

        <div class="row pt-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Availability Abreviations</label>
                    <input type="text" id="availability_abbreviations" name="availability_abbreviations" class="form-control" value="<?= $row['availability_abbreviations'] ?? '' ?>" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Multiplier</label>
                    <input type="text" id="multiplier" name="multiplier" class="form-control" value="<?= $row['multiplier'] ?? '' ?>" />
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="5"><?= $row['notes'] ?? '' ?></textarea>
        </div>

        <input type="hidden" id="product_availability_id" name="product_availability_id" class="form-control"  value="<?= $product_availability_id ?>"/>
        <?php
    }

    mysqli_close($conn);
}
?>
