<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_category_id = mysqli_real_escape_string($conn, $_POST['product_category']);
        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        $product_items = isset($_POST['product_items']) ? $_POST['product_items'] : [];
        $product_items_str = mysqli_real_escape_string($conn, implode(',', $product_items));

        $customer_pricing_percentages = $_POST['customer_pricing_percentages'] ?? [];

        foreach ($customer_pricing_percentages as $customer_pricing_id => $percentage) {
            $customer_pricing_id = mysqli_real_escape_string($conn, $customer_pricing_id);
            $percentage = mysqli_real_escape_string($conn, floatval($percentage));

            $checkQuery = "
                SELECT id 
                FROM pricing_category
                WHERE product_category_id = '$product_category_id'
                AND product_items = '$product_items_str'
                AND customer_pricing_id = '$customer_pricing_id'
                AND hidden = 0
            ";
            $result = mysqli_query($conn, $checkQuery);

            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $id = $row['id'];

                $updateQuery = "
                    UPDATE pricing_category
                    SET percentage = '$percentage',
                        last_edit = NOW(),
                        edited_by = '$userid'
                    WHERE id = '$id'
                ";
                mysqli_query($conn, $updateQuery);

            } else {
                $insertQuery = "
                    INSERT INTO pricing_category
                        (product_category_id, customer_pricing_id, percentage, product_items, added_date, added_by)
                    VALUES
                        ('$product_category_id', '$customer_pricing_id', '$percentage', '$product_items_str', NOW(), '$userid')
                ";
                mysqli_query($conn, $insertQuery);
            }
        }

        echo "success";
    }
    
    if ($action == "change_status") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $rowQuery = "SELECT product_category_id, product_items FROM pricing_category WHERE id = '$id' AND hidden = 0";
        $rowResult = mysqli_query($conn, $rowQuery);

        if ($rowResult && mysqli_num_rows($rowResult) > 0) {
            $rowData = mysqli_fetch_assoc($rowResult);
            $product_category_id = $rowData['product_category_id'];
            $product_items = $rowData['product_items'];

            $updateQuery = "
                UPDATE pricing_category
                SET status = '$new_status'
                WHERE product_category_id = '$product_category_id'
                AND product_items = '$product_items'
                AND hidden = 0
            ";

            if (mysqli_query($conn, $updateQuery)) {
                echo "success";
            } else {
                echo "Error updating status: " . mysqli_error($conn);
            }
        } else {
            echo "Row not found.";
        }
    }

    if ($action == 'hide_pricing_category') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);

        $rowQuery = "SELECT product_category_id, product_items FROM pricing_category WHERE id = '$id'";
        $rowResult = mysqli_query($conn, $rowQuery);

        if ($rowResult && mysqli_num_rows($rowResult) > 0) {
            $rowData = mysqli_fetch_assoc($rowResult);
            $product_category_id = $rowData['product_category_id'];
            $product_items = $rowData['product_items'];

            $updateQuery = "
                UPDATE pricing_category
                SET hidden = 1
                WHERE product_category_id = '$product_category_id'
                AND product_items = '$product_items'
            ";

            if (mysqli_query($conn, $updateQuery)) {
                echo 'success';
            } else {
                echo 'Error hiding rows: ' . mysqli_error($conn);
            }
        } else {
            echo "Row not found.";
        }
    }

    if ($action == 'fetch_modal_content') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);

        $query = "SELECT * FROM pricing_category WHERE id = '$id' AND hidden = 0";
        $result = mysqli_query($conn, $query);

        $pricing_percentages = [];
        $selected_items = [];
        $product_category_id = '';

        if ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $product_category_id = $row['product_category_id'];
            $product_items_str = $row['product_items'];

            $query_group = "SELECT * FROM pricing_category 
                            WHERE product_category_id = '$product_category_id' 
                            AND product_items = '".mysqli_real_escape_string($conn, $product_items_str)."' 
                            AND hidden = 0";
            $result_group = mysqli_query($conn, $query_group);

            while ($row_group = mysqli_fetch_array($result_group, MYSQLI_ASSOC)) {
                $customer_pricing_id = $row_group['customer_pricing_id'] ?? null;
                $percentage = $row_group['percentage'] ?? null;

                if ($customer_pricing_id !== null) {
                    $pricing_percentages[$customer_pricing_id] = $percentage;
                }

                $items = array_filter(explode(',', $row_group['product_items'] ?? ''));
                $selected_items = array_merge($selected_items, $items);
            }

            $selected_items = array_unique($selected_items);
        }
        ?>
        <input type="hidden" id="id" name="id" value="<?= $id ?>"/>

        <div class="row pt-3">
            <label class="form-label">Product Category</label>
            <div class="col-md-12 mb-3">
                <select id="product_category" class="form-control select2" name="product_category">
                    <option value="">Select One...</option>
                    <?php
                    $query_category = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                    $result_category = mysqli_query($conn, $query_category);
                    while ($row_category = mysqli_fetch_array($result_category, MYSQLI_ASSOC)) {
                        $selected = ($product_category_id == $row_category['product_category_id']) ? 'selected' : '';
                        ?>
                        <option value="<?= $row_category['product_category_id'] ?>" <?= $selected ?>>
                            <?= htmlspecialchars($row_category['product_category']) ?>
                        </option>
                        <?php
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label">Product Items</label>
                <select id="product_items" name="product_items[]" class="select2 form-control" multiple="multiple">
                    <optgroup label="Products">
                        <?php
                        $query_products = "SELECT * FROM product WHERE status = '1' AND hidden = '0' ORDER BY `product_item` ASC";
                        $result_products = mysqli_query($conn, $query_products);
                        while ($row_products = mysqli_fetch_array($result_products, MYSQLI_ASSOC)) {
                            $selected = in_array($row_products['product_id'], $selected_items) ? 'selected' : '';
                            ?>
                            <option value="<?= $row_products['product_id'] ?>" <?= $selected ?>>
                                <?= !empty($row_products['product_item']) ? htmlspecialchars($row_products['product_item']) : htmlspecialchars($row_products['description']) ?>
                            </option>
                            <?php
                        }
                        ?>
                    </optgroup>
                </select>
            </div>

            <div class="col-md-12">
                <div class="row mb-3 fw-bold">
                    <div class="col-6 text-center">Customer Pricing</div>
                    <div class="col-6 text-center">Percentage (%)</div>
                </div>
                <?php
                $query_pricing = "SELECT * FROM customer_pricing WHERE status = '1' ORDER BY `pricing_name` ASC";
                $result_pricing = mysqli_query($conn, $query_pricing);

                while ($row_pricing = mysqli_fetch_array($result_pricing, MYSQLI_ASSOC)) {
                    $pricing_id = $row_pricing['id'];
                    $percentage_val = $pricing_percentages[$pricing_id] ?? '';
                    ?>
                    <div class="row mb-2 align-items-center">
                        <div class="col-6 text-center">
                            <label for="customer_pricing_<?= $pricing_id ?>" class="form-label mb-0">
                                <?= htmlspecialchars($row_pricing['pricing_name']) ?>
                            </label>
                        </div>
                        <div class="col-6 text-center">
                            <input type="number"
                                step="0.001"
                                id="customer_pricing_<?= $pricing_id ?>"
                                name="customer_pricing_percentages[<?= $pricing_id ?>]"
                                class="form-control"
                                value="<?= htmlspecialchars($percentage_val) ?>"
                                placeholder="%"
                            />
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

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
