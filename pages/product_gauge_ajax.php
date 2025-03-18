<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_gauge_id = mysqli_real_escape_string($conn, $_POST['product_gauge_id']);
        $product_gauge = mysqli_real_escape_string($conn, $_POST['product_gauge']);
        $gauge_abbreviations = mysqli_real_escape_string($conn, $_POST['gauge_abbreviations']);
        $thickness = mysqli_real_escape_string($conn, $_POST['thickness']);
        $no_per_sqft = mysqli_real_escape_string($conn, $_POST['no_per_sqft']);
        $no_per_sqin = mysqli_real_escape_string($conn, $_POST['no_per_sqin']);
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_gauge WHERE product_gauge_id = '$product_gauge_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE product_gauge SET product_gauge = '$product_gauge', gauge_abbreviations = '$gauge_abbreviations', notes = '$notes', thickness = '$thickness', no_per_sqft = '$no_per_sqft', no_per_sqin = '$no_per_sqin', last_edit = NOW(), edited_by = '$userid'  WHERE product_gauge_id = '$product_gauge_id'";
            if (mysqli_query($conn, $updateQuery)) {
                echo "update-success";
            } else {
                echo "Error updating product gauge: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO product_gauge (product_gauge, gauge_abbreviations, notes, thickness, no_per_sqft, no_per_sqin, added_date, added_by) VALUES ('$product_gauge', '$gauge_abbreviations', '$thickness', '$no_per_sqft', '$no_per_sqin', '$notes', NOW(), '$userid')";
            if (mysqli_query($conn, $insertQuery)) {
                echo "add-success";
            } else {
                echo "Error adding product gauge: " . mysqli_error($conn);
            }
        }
    } 
    
    if ($action == "change_status") {
        $product_gauge_id = mysqli_real_escape_string($conn, $_POST['product_gauge_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product_gauge SET status = '$new_status' WHERE product_gauge_id = '$product_gauge_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_product_gauge') {
        $product_gauge_id = mysqli_real_escape_string($conn, $_POST['product_gauge_id']);
        $query = "UPDATE product_gauge SET hidden='1' WHERE product_gauge_id='$product_gauge_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'fetch_modal_content') {
        $product_gauge_id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM product_gauge WHERE product_gauge_id = '$product_gauge_id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
        }

        ?>
            <div class="row pt-3">
                <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Product gauge</label>
                    <input type="text" id="product_gauge" name="product_gauge" class="form-control"  value="<?= $row['product_gauge'] ?? '' ?>"/>
                </div>
                </div>
                <div class="col-md-6">
                
                </div>
            </div>

            <div class="row pt-3">
                <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Gauge Abreviations</label>
                    <input type="text" id="gauge_abbreviations" name="gauge_abbreviations" class="form-control" value="<?= $row['gauge_abbreviations'] ?? '' ?>" />
                </div>
                </div>
                <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Multiplier</label>
                    <input type="text" id="multiplier" name="multiplier" class="form-control" value="<?= $row['multiplier'] ?? '' ?>" />
                </div>
                </div>
            </div>

            <div class="row pt-3">
                <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Thickness</label>
                    <input type="number" id="thickness" name="thickness" class="form-control"  value="<?= $row['thickness'] ?? '' ?>"/>
                </div>
                </div>
                <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">#/SQFT</label>
                    <input type="text" id="no_per_sqft" name="no_per_sqft" class="form-control" value="<?= $row['no_per_sqft'] ?? '' ?>" />
                </div>
                </div>
                <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">#/SQFT</label>
                    <input type="text" id="no_per_sqin" name="no_per_sqin" class="form-control" value="<?= $row['no_per_sqin'] ?? '' ?>" />
                </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="5"><?= $row['notes'] ?? '' ?></textarea>
            </div>

            <input type="hidden" id="product_gauge_id" name="product_gauge_id" class="form-control"  value="<?= $product_gauge_id ?>"/>
        <?php
    }

    mysqli_close($conn);
}
?>
