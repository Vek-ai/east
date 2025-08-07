<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_warranty_type_id = mysqli_real_escape_string($conn, $_POST['product_warranty_type_id']);
        $product_warranty_type = mysqli_real_escape_string($conn, $_POST['product_warranty_type']);
        $warranty_type_abbreviations = mysqli_real_escape_string($conn, $_POST['warranty_type_abbreviations']);
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_warranty_type WHERE product_warranty_type_id = '$product_warranty_type_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_product_warranty_type = $row['product_warranty_type'];
            $current_warranty_type_abbreviations = $row['warranty_type_abbreviations'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($product_warranty_type != $current_product_warranty_type) {
                $checkProductwarranty_type = "SELECT * FROM product_warranty_type WHERE product_warranty_type = '$product_warranty_type'";
                $resultProductwarranty_type = mysqli_query($conn, $checkProductwarranty_type);
                if (mysqli_num_rows($resultProductwarranty_type) > 0) {
                    $duplicates[] = "Product warranty type";
                }
            }

            if ($warranty_type_abbreviations != $current_warranty_type_abbreviations) {
                $checkAbreviations = "SELECT * FROM product_warranty_type WHERE warranty_type_abbreviations = '$warranty_type_abbreviations'";
                $resultAbreviations = mysqli_query($conn, $checkAbreviations);
                if (mysqli_num_rows($resultAbreviations) > 0) {
                    $duplicates[] = "Warranty type Abbreviations";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE product_warranty_type SET product_warranty_type = '$product_warranty_type', warranty_type_abbreviations = '$warranty_type_abbreviations', notes = '$notes', last_edit = NOW(), edited_by = '$userid'  WHERE product_warranty_type_id = '$product_warranty_type_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "update-success";
                } else {
                    echo "Error updating product warranty type: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkProductwarranty_type = "SELECT * FROM product_warranty_type WHERE product_warranty_type = '$product_warranty_type'";
            $resultProductwarranty_type = mysqli_query($conn, $checkProductwarranty_type);
            if (mysqli_num_rows($resultProductwarranty_type) > 0) {
                $duplicates[] = "Product warranty type";
            }

            $checkAbreviations = "SELECT * FROM product_warranty_type WHERE warranty_type_abbreviations = '$warranty_type_abbreviations'";
            $resultAbreviations = mysqli_query($conn, $checkAbreviations);
            if (mysqli_num_rows($resultAbreviations) > 0) {
                $duplicates[] = "warranty type Abbreviations";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO product_warranty_type (product_warranty_type, warranty_type_abbreviations, notes, added_date, added_by) VALUES ('$product_warranty_type', '$warranty_type_abbreviations', '$notes', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "add-success";
                } else {
                    echo "Error adding product warranty type: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $product_warranty_type_id = mysqli_real_escape_string($conn, $_POST['product_warranty_type_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product_warranty_type SET status = '$new_status' WHERE product_warranty_type_id = '$product_warranty_type_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_warranty_type') {
        $product_warranty_type_id = mysqli_real_escape_string($conn, $_POST['product_warranty_type_id']);
        $query = "UPDATE product_warranty_type SET hidden='1' WHERE product_warranty_type_id='$product_warranty_type_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'fetch_modal_content') {
        $product_warranty_type_id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM product_warranty_type WHERE product_warranty_type_id = '$product_warranty_type_id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
        } else {
            $row = [];
        }

        ?>
        <div class="row pt-3" data-dal="<?=$query?>">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Product Warranty Type</label>
                    <input type="text" id="product_warranty_type" name="product_warranty_type" class="form-control" value="<?= htmlspecialchars($row['product_warranty_type'] ?? '') ?>"/>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Profile Type Abbreviations</label>
                    <input type="text" id="warranty_type_abbreviations" name="warranty_type_abbreviations" class="form-control" value="<?= htmlspecialchars($row['warranty_type_abbreviations'] ?? '') ?>"/>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="5"><?= htmlspecialchars($row['notes'] ?? '') ?></textarea>
        </div>

        <input type="hidden" id="product_warranty_type_id" name="product_warranty_type_id" value="<?= intval($product_warranty_type_id) ?>"/>
        <?php
    }

    if ($action === 'fetch_table') {
        $query = "SELECT * FROM product_warranty_type WHERE hidden = 0";
        $result = mysqli_query($conn, $query);

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $no = $row['product_warranty_type_id'];
            $product_warranty_type = $row['product_warranty_type'];
            $warranty_type_abbreviations = $row['warranty_type_abbreviations'];
            $notes = $row['notes'];

            $last_edit = !empty($row['last_edit']) ? (new DateTime($row['last_edit']))->format('m-d-Y') : '';
            $added_by = $row['added_by'];
            $edited_by = $row['edited_by'];

            if ($edited_by != "0") {
                $last_user_name = get_name($edited_by);
            } elseif ($added_by != "0") {
                $last_user_name = get_name($added_by);
            } else {
                $last_user_name = "";
            }

            $last_edit_text = "Last Edited $last_edit by $last_user_name";

            $status_html = $row['status'] == '0'
                ? "<a href='javascript:void(0)' class='changeStatus' data-no='$no' data-id='$no' data-status='0'>
                        <div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;'>Inactive</div>
                </a>"
                : "<a href='javascript:void(0)' class='changeStatus' data-no='$no' data-id='$no' data-status='1'>
                        <div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;'>Active</div>
                </a>";

            $action_html = $row['status'] == '0'
                ? "<a href='javascript:void(0)' class='py-1 text-dark hideWarrantyType' title='Archive' data-id='$no' data-row='$no' style='border-radius: 10%;'>
                        <i class='text-danger ti ti-trash fs-7'></i>
                </a>"
                : "<a href='javascript:void(0)' id='addModalBtn' title='Edit' class='d-flex align-items-center justify-content-center text-decoration-none' data-id='$no' data-type='edit'>
                        <i class='ti ti-pencil fs-7'></i>
                </a>";

            $data[] = [
                'product_warranty_type' => $product_warranty_type,
                'warranty_type_abbreviations' => $warranty_type_abbreviations,
                'notes' => $notes,
                'last_edit' => $last_edit_text,
                'status_html' => $status_html,
                'action_html' => $action_html
            ];
        }

        echo json_encode(['data' => $data]);
        exit;
    }
    mysqli_close($conn);
}
?>
