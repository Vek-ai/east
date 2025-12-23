<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $product_category_id = mysqli_real_escape_string($conn, $_POST['product_category']);
        $customer_pricing_id = mysqli_real_escape_string($conn, $_POST['customer_pricing']);
        $percentage = mysqli_real_escape_string($conn, floatval($_POST['percentage'] ?? 0.00));
        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        $product_items = isset($_POST['product_items']) ? $_POST['product_items'] : [];
        $product_items_str = implode(',', $product_items);
        $product_items_str = mysqli_real_escape_string($conn, $product_items_str);

        $checkQuery = "SELECT * FROM pricing_category WHERE id = '$id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $updateQuery = "UPDATE pricing_category SET product_category_id = '$product_category_id', customer_pricing_id = '$customer_pricing_id', percentage = '$percentage', product_items = '$product_items_str', last_edit = NOW(), edited_by = '$userid'  WHERE id = '$id'";
            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating category: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO pricing_category (product_category_id, customer_pricing_id, percentage, product_items, added_date, added_by) VALUES ('$product_category_id', '$customer_pricing_id', '$percentage', '$product_items_str', NOW(), '$userid')";
            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error adding category: " . mysqli_error($conn);
            }
        }
    } 
    
    if ($action == "change_status") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE pricing_category SET status = '$new_status' WHERE id = '$id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_pricing_category') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "UPDATE pricing_category SET hidden='1' WHERE id='$id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'fetch_modal_content') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM pricing_category WHERE id = '$id'";
        $result = mysqli_query($conn, $query);
        $row = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_array($result) : [];
    
        ?>
        <div class="row pt-3">
            <div class="col-md-6">
                <label class="form-label">Product Category</label>
                <div class="mb-3">
                    <select id="product_category" class="form-control select2" name="product_category">
                        <option value="">Select One...</option>
                        <?php
                        $query_category = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                        $result_category = mysqli_query($conn, $query_category);
                        while ($row_category = mysqli_fetch_array($result_category)) {
                            $selected = ($row['product_category_id'] ?? '') == $row_category['product_category_id'] ? 'selected' : '';
                            ?>
                            <option value="<?= $row_category['product_category_id'] ?>" <?= $selected ?>><?= $row_category['product_category'] ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Customer Pricing</label>
                <div class="mb-3">
                    <select id="customer_pricing" class="form-control select2" name="customer_pricing">
                        <option value="">Select One...</option>
                        <?php
                        $query_pricing = "SELECT * FROM customer_pricing WHERE hidden = '0' ORDER BY `pricing_name` ASC";
                        $result_pricing = mysqli_query($conn, $query_pricing);
                        while ($row_pricing = mysqli_fetch_array($result_pricing)) {
                            $selected = ($row['customer_pricing_id'] ?? '') == $row_pricing['id'] ? 'selected' : '';
                            ?>
                            <option value="<?= $row_pricing['id'] ?>" <?= $selected ?>><?= $row_pricing['pricing_name'] ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
    
        <div class="row pt-3">
            <div class="col-md-12">
                <div class="mb-3">
                    <label class="form-label">Percentage</label>
                    <input type="number" step="0.001" id="percentage" name="percentage" class="form-control" value="<?= $row['percentage'] ?? '' ?>" />
                </div>
            </div>
        </div>
    
        <div class="row pt-3">
            <div class="col-md-12">
                <label class="form-label">Product Item</label>
                <div class="mb-3">
                    <select id="product_items" name="product_items[]" class="select2 form-control" multiple="multiple">
                        <optgroup label="Products">
                            <?php
                            $product_items_array = array_filter(explode(',', $row['product_items'] ?? ''));
                            $query_products = "SELECT * FROM product WHERE status = '1' AND hidden = '0' ORDER BY `product_item` ASC";
                            $result_products = mysqli_query($conn, $query_products);
                            while ($row_products = mysqli_fetch_array($result_products)) {
                                $selected = in_array($row_products['product_id'], $product_items_array) ? 'selected' : '';
                                ?>
                                <option value="<?= $row_products['product_id'] ?>" <?= $selected ?>><?= !empty($row_products['product_item']) ? $row_products['product_item'] : $row_products['description'] ?></option>
                                <?php
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
            </div>
        </div>
    
        <input type="hidden" id="id" name="id" class="form-control" value="<?= $id ?>"/>

        <script>
            $(document).ready(function () {
                $(".select2").each(function () {
                    let parentContainer = $(this).parent();
                    $(this).select2({
                        dropdownParent: parentContainer
                    });
                });
            });
        </script>
        <?php
    }    

    mysqli_close($conn);
}
?>
