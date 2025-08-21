<?php
// screw_modal.php
session_start();
require '../../includes/dbconn.php';
require '../../includes/functions.php';

$screw_id = 16;
$panel_id = 3;

if (isset($_POST['fetch_modal'])) {

    $cart = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
    $product_ids = [];
    foreach ($cart as $item) {
        if (!empty($item['product_id'])) {
            $product_ids[] = (int)$item['product_id'];
        }
    }
    $product_ids = array_unique($product_ids);

    $panel_product_ids = [];
    if (!empty($product_ids)) {
        $ids_in = implode(',', array_map('intval', $product_ids));
        $q = "SELECT product_id, product_category FROM product WHERE product_id IN ($ids_in)";
        $r = mysqli_query($conn, $q);
        while ($row = mysqli_fetch_assoc($r)) {
            if ((int)$row['product_category'] === (int)$panel_id) {
                $panel_product_ids[(int)$row['product_id']] = true;
            }
        }
    }

    $color_ids = [];
    foreach ($cart as $item) {
        $pid = isset($item['product_id']) ? (int)$item['product_id'] : 0;
        if ($pid && isset($panel_product_ids[$pid])) {
            if (isset($item['custom_color']) && is_numeric($item['custom_color'])) {
                $color_ids[] = (int)$item['custom_color'];
            }
        }
    }
    $color_ids = array_values(array_unique(array_filter($color_ids, fn($v) => $v > 0)));
    ?>
    <h4 class="text-center mt-0">Select Screw</h4>

    <div class="row mt-4">
        <div class="col-12 d-flex justify-content-center">
            <select id="screw_select" class="form-control screw_select2 w-auto text-center">
                <?php if (empty($color_ids)): ?>
                    <option value="0" disabled selected>Color Not Available</option>
                <?php
                else:
                    $color_in = implode(',', array_map('intval', $color_ids));
                    $query_screw = "
                        SELECT 
                            p.product_id,
                            p.product_item,
                            p.unit_price
                        FROM product p
                        WHERE p.product_category = $screw_id
                    ";
                    $result_screw = mysqli_query($conn, $query_screw);

                    echo '<option value="0" disabled selected>Select Screw...</option>';
                    while ($screw = mysqli_fetch_assoc($result_screw)) {
                        $label = $screw['product_item'] . " - $" . number_format($screw['unit_price'], 2) . " / pc";

                        echo "<option value='{$screw['product_id']}'
                                    data-unit-price='{$screw['unit_price']}'>
                                {$label}
                            </option>";
                    }
                endif; ?>
            </select>
        </div>
    </div>


    <div class="modal-footer d-flex justify-content-end align-items-center px-0">
        <button class="btn btn-success ripple btn-secondary" type="button" id="btn-add-cart-screw">
            Add to Cart
        </button>
    </div>
<?php
}
