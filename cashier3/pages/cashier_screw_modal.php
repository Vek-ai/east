<?php
session_start();
require '../../includes/dbconn.php';
require '../../includes/functions.php';

$screw_id = 16;
$panel_id = 3;
$trim_id = 4;

if (isset($_POST['fetch_modal'])) {
    ?>
    <style>
        .d-flex.justify-content-between:has(> *:only-child) {
            justify-content: center !important;
        }
    </style>
    <div class="d-flex justify-content-between align-items-center gap-3">
        <?php
        $cart = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
        $product_ids = [];
        foreach ($cart as $item) {
            if (!empty($item['product_id'])) {
                $product_ids[] = (int)$item['product_id'];
            }
        }
        $product_ids = array_unique($product_ids);

        $panel_product_ids = [];
        $trim_product_ids  = [];
        if (!empty($product_ids)) {
            $ids_in = implode(',', array_map('intval', $product_ids));
            $q = "SELECT product_id, product_category FROM product WHERE product_id IN ($ids_in)";
            $r = mysqli_query($conn, $q);
            while ($row = mysqli_fetch_assoc($r)) {
                if ((int)$row['product_category'] === (int)$panel_id) {
                    $panel_product_ids[(int)$row['product_id']] = true;
                }
                if ((int)$row['product_category'] === (int)$trim_id) {
                    $trim_product_ids[(int)$row['product_id']] = true;
                }
            }
        }

        $color_ids = [];
        foreach ($cart as $item) {
            $pid = isset($item['product_id']) ? (int)$item['product_id'] : 0;
            if ($pid && (isset($panel_product_ids[$pid]) || isset($trim_product_ids[$pid]))) {
                if (isset($item['custom_color']) && is_numeric($item['custom_color'])) {
                    $color_ids[] = (int)$item['custom_color'];
                }
            }
        }
        $color_ids = array_values(array_unique(array_filter($color_ids, fn($v) => $v > 0)));

        if (count($color_ids) > 1) {
            ?>
            <div class="row">
                <h4 class="fw-bold">Select Color</h4>
                <div class="mb-2">
                    <select class="form-control screw_select2 w-auto" 
                            id="select-screw-color" 
                            >
                        <option value="" data-category="">All Colors</option>
                        <optgroup label="Product Colors">
                            <?php
                            $color_in = implode(',', $color_ids);
                            $query_color = "SELECT * 
                                            FROM paint_colors 
                                            WHERE hidden = '0' 
                                            AND color_status = '1' 
                                            AND color_id IN ($color_in) 
                                            ORDER BY color_name ASC";
                            $result_color = mysqli_query($conn, $query_color);
                            while ($row_color = mysqli_fetch_assoc($result_color)) { ?>
                                <option value="<?= (int)$row_color['color_id'] ?>" 
                                        data-category="<?= htmlspecialchars($row_color['product_category']) ?>">
                                    <?= htmlspecialchars($row_color['color_name']) ?>
                                </option>
                            <?php } ?>
                        </optgroup>
                    </select>
                </div>
            </div>
            <?php
        }

        if (!empty($panel_product_ids) && !empty($trim_product_ids)) {
            ?>
            <div class="row">
                <h4 class="fw-bold">Select Where to apply</h4>
                <div class="mb-2">
                    <select id="select-type-to-apply" class="form-control screw_select2 w-auto">
                        <option value="">All</option>
                        <option value="panel">Panel</option>
                        <option value="trim">Trim</option>
                    </select>
                </div>
            </div>
            <?php
        }
        ?>

        <div class="row">
            <h4 class="fw-bold">Select Screw</h4>
            <div class="mb-2">
                <select id="screw_select" class="form-control screw_select2 w-auto text-center">
                    <?php if (empty($color_ids)): ?>
                        <option value="0" disabled selected>Color Not Available</option>
                    <?php
                    else:
                        $color_in = implode(',', $color_ids);

                        $query_screw = "
                            SELECT 
                                p.product_id,
                                p.product_item,
                                p.unit_price,
                                i.color_id
                            FROM product p
                            INNER JOIN inventory i ON i.product_id = p.product_id
                            WHERE p.product_category = $screw_id
                            AND i.color_id IN ($color_in)
                            AND i.quantity > 0
                            GROUP BY p.product_id, p.product_item, p.unit_price, i.color_id
                            ORDER BY p.product_item
                        ";
                        $result_screw = mysqli_query($conn, $query_screw);

                        echo '<option value="0" disabled selected>Select Screw...</option>';
                        while ($screw = mysqli_fetch_assoc($result_screw)) {
                            $label = $screw['product_item'] . " - $" . number_format($screw['unit_price'], 2) . " / pc";

                            echo "<option value='{$screw['product_id']}'
                                        data-unit-price='{$screw['unit_price']}'
                                        data-color='{$screw['color_id']}'>
                                    {$label}
                                </option>";
                        }
                    endif; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="modal-footer d-flex justify-content-end align-items-center px-0">
        <button class="btn btn-success ripple btn-secondary" type="button" id="btn-add-cart-screw">
            Add to Cart
        </button>
    </div>
<?php
}
