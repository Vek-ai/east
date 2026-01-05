<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_POST['fetch_modal'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $product_details = getProductDetails($id);

    if (!empty($product_details)) {
        $category_id = $product_details['product_category'];
        $unit_price  = $product_details['unit_price'];
        ?>
        <input type="hidden" id="product_id" name="id" value="<?= $id ?>" />
        <input type="hidden" id="product_price" name="retail_price" value="<?= $unit_price ?>" />
        <input type="hidden" id="is_pre_order" name="is_pre_order" value="0" />
        <input type="hidden" id="category_id" name="category_id" value="<?= $category_id ?>" />

        <h3 class="text-center fw-bold mt-0"><?= $product_details['product_item'] ?></h3>

        <div class="row">
            <div class="row justify-content-center">
                <div class="col-3 text-center"><label class="fs-4 fw-semibold">Quantity</label></div>
                <div class="col-3 text-center"><label class="fs-4 fw-semibold">Description</label></div>
                <div class="col-3 text-center"><label class="fs-4 fw-semibold">Price</label></div>
            </div>

            <div id="untreated-section">
                <div class="custom-length-row row justify-content-center mt-1">
                    <div class="col-3">
                        <input type="number" name="quantity[]" class="form-control mb-1" value="" placeholder="Enter Quantity">
                    </div>
                    <div class="col-3">
                        <input type="text" name="description[]" class="form-control mb-1" value="" placeholder="Enter Description">
                    </div>
                    <div class="col-3">
                        <input type="number" step="0.001" name="price[]" class="form-control mb-1" value="" placeholder="Enter Price">
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer d-flex justify-content-end align-items-center px-0">
            <button type="submit" class="btn btn-success ripple btn-secondary">Add to Cart</button>
        </div>
        <?php
    } else {
        echo '<h5 class="text-center">Product Not Found!</h5>';
    }
}
?>
