<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_line_id = mysqli_real_escape_string($conn, $_POST['product_line_id']);
        $product_line = mysqli_real_escape_string($conn, $_POST['product_line']);
        $line_abreviations = mysqli_real_escape_string($conn, $_POST['line_abreviations']);
        $product_category = mysqli_real_escape_string($conn, $_POST['product_category']);
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);
        $multiplier = mysqli_real_escape_string($conn, floatval($_POST['multiplier'] ?? 0.00));

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_line WHERE product_line_id = '$product_line_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE product_line SET product_line = '$product_line', line_abreviations = '$line_abreviations', product_category = '$product_category', notes = '$notes', multiplier = '$multiplier', last_edit = NOW(), edited_by = '$userid'  WHERE product_line_id = '$product_line_id'";
            if (mysqli_query($conn, $updateQuery)) {
                echo "Product line updated successfully.";
            } else {
                echo "Error updating product line: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO product_line (product_line, line_abreviations, product_category, notes, multiplier, added_date, added_by) VALUES ('$product_line', '$line_abreviations', '$product_category', '$notes', '$multiplier', NOW(), '$userid')";
            if (mysqli_query($conn, $insertQuery)) {
                echo "New product line added successfully.";
            } else {
                echo "Error adding product line: " . mysqli_error($conn);
            }
        }
    } 
    
    if ($action == "change_status") {
        $product_line_id = mysqli_real_escape_string($conn, $_POST['product_line_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product_line SET status = '$new_status' WHERE product_line_id = '$product_line_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_product_line') {
        $product_line_id = mysqli_real_escape_string($conn, $_POST['product_line_id']);
        $query = "UPDATE product_line SET hidden='1' WHERE product_line_id='$product_line_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'fetch_modal_content') {
        $product_line_id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM product_line WHERE product_line_id = '$product_line_id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
        }

        ?>
        <div class="row pt-3">
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Product line</label>
                <input type="text" id="product_line" name="product_line" class="form-control"  value="<?= $row['product_line'] ?? '' ?>"/>
            </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Product Category</label>
                    <select id="product_category" class="form-control" name="product_category">
                        <option value="">Select One...</option>
                        <?php
                        $query_roles = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                        $result_roles = mysqli_query($conn, $query_roles);            

                        while ($row_product_category = mysqli_fetch_assoc($result_roles)) {
                            $selected = (($row['product_category'] ?? '') == $row_product_category['product_category_id']) ? 'selected' : '';
                        ?>
                            <option value="<?= htmlspecialchars($row_product_category['product_category_id']) ?>" <?= $selected ?>>
                                <?= htmlspecialchars($row_product_category['product_category']) ?>
                            </option>
                        <?php   
                        }
                        ?>
                    </select>

                </div>
            </div>
        </div>

        <div class="row pt-3">
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Line Abreviations</label>
                <input type="text" id="line_abreviations" name="line_abreviations" class="form-control" value="<?= $row['line_abreviations'] ?? '' ?>" />
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

            <input type="hidden" id="product_line_id" name="product_line_id" class="form-control"  value="<?= $product_line_id ?>"/>
        <?php
    }

    mysqli_close($conn);
}
?>
