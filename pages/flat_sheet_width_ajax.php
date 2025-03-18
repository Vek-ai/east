<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $id = mysqli_real_escape_string($conn, $_POST['id'] ?? null);
        $product_category = mysqli_real_escape_string($conn, $_POST['product_category'] ?? 0);
        $product_system = mysqli_real_escape_string($conn, $_POST['product_system'] ?? 0);
        $product_line = mysqli_real_escape_string($conn, $_POST['product_line'] ?? 0);
        $product_type = mysqli_real_escape_string($conn, $_POST['product_type'] ?? 0);
        $width = mysqli_real_escape_string($conn, $_POST['width'] ?? '');
        $userid = mysqli_real_escape_string($conn, $_POST['userid'] ?? '');
    
        $checkQuery = "SELECT * FROM coil_width WHERE id = '$id'";
        $result = mysqli_query($conn, $checkQuery);
    
        if ($result && mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE flat_sheet_width 
                            SET product_category = '$product_category', 
                                product_system = '$product_system', 
                                product_line = '$product_line', 
                                product_type = '$product_type', 
                                width = '$width',
                                last_edit = NOW(), 
                                edited_by = '$userid' 
                            WHERE id = '$id'";
    
            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating coil width: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO flat_sheet_width (product_category, product_system, product_line, product_type, width, added_by, last_edit) 
                            VALUES ('$product_category', '$product_system', '$product_line', '$product_type', '$width', '$userid', NOW())";
    
            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error adding coil width: " . mysqli_error($conn);
            }
        }
    }    
    
    if ($action == "change_status") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE flat_sheet_width SET status = '$new_status' WHERE id = '$id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_fs_width') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "UPDATE flat_sheet_width SET hidden='1' WHERE id='$id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    if ($action == 'fetch_modal_content') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM flat_sheet_width WHERE id = '$id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
        }

        ?>
        <div class="row pt-3">
            <div class="col-md-4">
                <label class="form-label">Product Category</label>
                <div class="mb-3">
                    <select class="form-control select2" id="select-category" name="product_category">
                        <option value="">All Categories</option>
                        <optgroup label="Category">
                            <?php
                            $query_category = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                            $result_category = mysqli_query($conn, $query_category);
                            while ($row_category = mysqli_fetch_array($result_category)) {
                                $selected = (($row['product_category'] ?? '') == $row_category['product_category_id']) ? 'selected' : '';
                            ?>
                                <option value="<?= $row_category['product_category_id'] ?>" data-category="<?= $row_category['product_category_id'] ?>" <?= $selected ?>><?= $row_category['product_category'] ?></option>
                            <?php
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
            </div>
        </div>

        <div class="row pt-3 category_selection d-none">
            <div class="col-md-4">
                <label class="form-label">Product System</label>
                <div class="mb-3">
                    <select class="form-control select2" id="select-system" name="product_system">
                        <option value="">All Product Systems</option>
                        <optgroup label="Product Type">
                            <?php
                            $query_system = "SELECT * FROM product_system WHERE hidden = '0'";
                            $result_system = mysqli_query($conn, $query_system);
                            while ($row_system = mysqli_fetch_array($result_system)) {
                                $selected = (($row['product_system'] ?? '') == $row_system['product_system_id']) ? 'selected' : '';
                            ?>
                                <option value="<?= $row_system['product_system_id'] ?>" data-category="<?= $row_system['product_category'] ?>" <?= $selected ?>><?= $row_system['product_system'] ?></option>
                            <?php
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Product Line</label>
                <div class="mb-3">
                    <select class="form-control select2" id="select-line" name="product_line">
                        <option value="" >All Product Lines</option>
                        <optgroup label="Product Type">
                            <?php
                            $query_line = "SELECT * FROM product_line WHERE hidden = '0' AND status = '1' ORDER BY `product_line` ASC";
                            $result_line = mysqli_query($conn, $query_line);
                            while ($row_line = mysqli_fetch_array($result_line)) {
                                $selected = (($row['product_line'] ?? '') == $row_line['product_line_id']) ? 'selected' : '';
                            ?>
                                <option value="<?= $row_line['product_line_id'] ?>" data-category="<?= $row_line['product_category'] ?>" <?= $selected ?>><?= $row_line['product_line'] ?></option>
                            <?php
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Product Type</label>
                <div class="mb-3">
                    <select class="form-control select2" id="select-type" name="product_type">
                        <option value="" >All Product Types</option>
                        <optgroup label="Product Type">
                            <?php
                            $query_type = "SELECT * FROM product_type WHERE hidden = '0' AND status = '1' ORDER BY `product_type` ASC";
                            $result_type = mysqli_query($conn, $query_type);
                            while ($row_type = mysqli_fetch_array($result_type)) {
                                $selected = (($row['product_type'] ?? '') == $row_type['product_type_id']) ? 'selected' : '';
                            ?>
                                <option value="<?= $row_type['product_type_id'] ?>" data-category="<?= $row_type['product_category'] ?>" <?= $selected ?>><?= $row_type['product_type'] ?></option>
                            <?php
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="col-md-12 pt-3">
                <label class="form-label">Width</label>
                <div class="mb-3">
                    <input type="number" id="width" name="width" class="form-control" value="<?= $row['width'] ?? '' ?>"/>
                </div>
            </div>
        </div>

        <input type="hidden" id="id" name="id" class="form-control"  value="<?= $id ?>"/>

        <script>
            $(document).ready(function () {
                $(".select2").each(function () {
                    if ($(this).hasClass("select2-hidden-accessible")) {
                        $(this).select2("destroy");
                    }

                    let parentContainer = $(this).parent();
                    $(this).select2({
                        dropdownParent: parentContainer
                    });
                });

                updateSearchCategory();
            });

        </script>
        <?php
    }

    
    if ($action == 'fetch_view_content') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $type = mysqli_real_escape_string($conn, $_POST['type']);
        ?>
            <h4 class="card-title d-flex justify-content-center align-items-center">Trim profile details here.</h4>
        <?php
    }
    mysqli_close($conn);
}
?>
