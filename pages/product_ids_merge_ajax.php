<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if (isset($_POST['merge'])) {
    $product_original = intval($_POST['product_original'] ?? 0);
    $product_merge = intval($_POST['product_merge'] ?? 0);
    $staff_id = intval($_SESSION['userid'] ?? 0);

    if ($product_original > 0 && $product_merge > 0) {
        if ($product_original === $product_merge) {
            echo "error: cannot merge a product into itself";
            exit;
        }

        $updateProduct = mysqli_query(
            $conn,
            "UPDATE product 
             SET status = 0 , hidden = 1
             WHERE product_id = $product_merge"
        );

        $tables = [
            'approval_changes' => 'product_id',
            'coil_defective' => 'product_id',
            'coil_product' => 'product_id',
            'correlated_product' => 'product_id',
            'customer_cart' => 'product_id',
            'estimate_changes' => 'product_id',
            'estimate_prod' => 'product_id',
            'inventory' => 'product_id',
            'order_changes' => 'product_id',
            'product_abr' => 'product_id_from_table',
            'product_db_old' => 'product_id',
            'product_duplicate' => 'product_id',
            'product_excel' => 'product_id',
            'product_preorder' => 'product_id',
            'sales_discounts' => 'product_id',
            'staff_product_access' => 'product_id',
            'staging_bin' => 'product_id',
            'stockable_report' => 'product_id',
            'supplier_orders_prod' => 'product_id',
            'supplier_temp_prod_orders' => 'product_id',
            'test' => 'product_id',
            'approval_product' => 'productid',
            'coil_process' => 'productid',
            'order_product' => 'productid',
            'product_images' => 'productid',
            'product_inventory' => 'productid',
            'product_returns' => 'productid',
            'work_order' => 'productid',
            'work_order_product' => 'productid'
        ];

        $allSuccess = true;

        foreach ($tables as $table => $col) {
            $sql = "UPDATE `$table`
                    SET `$col` = $product_original
                    WHERE `$col` = $product_merge";
            if (!mysqli_query($conn, $sql)) {
                $allSuccess = false;
                error_log("Failed updating $table.$col: " . mysqli_error($conn));
            }
        }

        if ($updateProduct && $allSuccess) {
            $insertHistory = mysqli_query($conn, "
                INSERT INTO product_merge_history (product_original, product_merged, staff_id, date_merged)
                VALUES ($product_original, $product_merge, $staff_id, NOW())
            ");

            if ($insertHistory) {
                echo "success";
            } else {
                error_log('Failed inserting into product_merge_history: ' . mysqli_error($conn));
                echo "error: failed to update history";
            }
        } else {
            echo "error: failed to merge";
        }
    } else {
        echo "error: invalid inputs. please select both products to continue.";
    }
}

if (isset($_POST['fetch_data'])) {
    $product_id = (int)($_POST['product_id'] ?? 0);

    if ($product_id > 0) {
        $query = mysqli_query($conn, "SELECT * FROM product WHERE product_id = $product_id");

        if ($query && mysqli_num_rows($query) > 0) {
            $row = mysqli_fetch_assoc($query);

            function infoRow($label, $value) {
                return "
                    <div class='d-flex justify-content-between border-bottom py-1'>
                        <span><strong>$label</strong></span>
                        <span class='text-end'>{$value}</span>
                    </div>
                ";
            }
            ?>

            <!-- PRODUCT IDENTIFIER -->
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Product Identifier</h5></div>
                <div class="card-body">
                    <?= infoRow('Product Category', getColumnFromTable("product_category", "product_category", $row['product_category'])) ?>
                    <?= infoRow('Product Type', getColumnFromTable("product_type", "product_type", $row['product_type'])) ?>
                    <?= infoRow('Profile', getColumnFromTable("profile_type", "profile_type", $row['profile'])) ?>
                    <?= infoRow('Grade', getColumnFromTable("product_grade", "product_grade", $row['grade'])) ?>
                    <?= infoRow('Gauge', getColumnFromTable("product_gauge", "product_gauge", $row['gauge'])) ?>
                </div>
            </div>

            <!-- PRODUCT COLOR MAPPING -->
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Product Color Mapping</h5></div>
                <div class="card-body">
                    <?= infoRow('Color Group', getColumnFromTable("product_color", "color_name", $row['color_group'])) ?>
                    <?= infoRow('Color Paint', getColumnFromTable("paint_colors", "color_name", $row['color_paint'])) ?>
                </div>
            </div>

            <!-- PRODUCT INFORMATION -->
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Product Information</h5></div>
                <div class="card-body">
                    <?= infoRow('Product Item', $row['product_item'] ?? '') ?>
                    <?= infoRow('Warranty Type', $row['warranty_type'] ?? '') ?>
                    <?= infoRow('Product Origin', $row['product_origin'] ?? '') ?>
                    <?= infoRow('Unit of Measure', $row['unit_of_measure'] ?? '') ?>
                </div>
            </div>

            <!-- PRODUCT PRICING -->
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Product Pricing</h5></div>
                <div class="card-body">
                    <?= infoRow('Cost', $row['cost'] ?? '') ?>
                    <?= infoRow('Unit Price', $row['unit_price'] ?? '') ?>
                    <?= infoRow('Per Inch Price', $row['per_in_price'] ?? '') ?>
                    <?= infoRow('Sold by Feet', $row['sold_by_feet'] ?? '') ?>
                    <?= infoRow('Custom Length', $row['is_custom_length'] ?? '') ?>
                    <?= infoRow('Per Ft Price', $row['per_ft_price'] ?? '') ?>
                    <?= infoRow('Panel Type', $row['panel_type'] ?? '') ?>
                    <?= infoRow('Panel Style', $row['panel_style'] ?? '') ?>
                    <?= infoRow('Standing Seam', $row['standing_seam'] ?? '') ?>
                    <?= infoRow('Board Batten', $row['board_batten'] ?? '') ?>
                    <?= infoRow('Available Lengths', getDimensions($row['available_lengths'])) ?>
                </div>
            </div>

            <!-- INVENTORY TRACKING -->
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Inventory Tracking</h5></div>
                <div class="card-body">
                    <?= infoRow('Inventory ID', $row['inv_id'] ?? '') ?>
                    <?= infoRow('Coil Part No', $row['coil_part_no'] ?? '') ?>
                    <?= infoRow('Product SKU', $row['product_sku'] ?? '') ?>
                    <?= infoRow('UPC', $row['upc'] ?? '') ?>
                    <?= infoRow('Reorder Level', $row['reorder_level'] ?? '') ?>
                    <?= infoRow('Product Usage', $row['product_usage'] ?? '') ?>
                    <?= infoRow('Supplier ID', $row['supplier_id'] ?? '') ?>
                </div>
            </div>

            <!-- CORRELATED PRODUCTS -->
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Correlated Products</h5></div>
                <div class="card-body">
                    <?= infoRow('Correlated Products', $row['correlatedProducts'] ?? '') ?>
                </div>
            </div>

            <!-- PRODUCT NOTES -->
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Product Notes</h5></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between py-1">
                        <span><strong>Comment</strong></span>
                        <span class="text-end"><?= nl2br($row['comment'] ?? '') ?></span>
                    </div>
                </div>
            </div>

            <?php
        } else {
            echo '<p class="text-danger">No product found.</p>';
        }
    } else {
        echo '<p class="text-danger">Invalid product ID.</p>';
    }
}





?>
