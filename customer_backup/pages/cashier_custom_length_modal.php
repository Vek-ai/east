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
    ?>
    <?php
        if (!empty($product_details)) {
            $category_id = $product_details['product_category'];
        ?>
        <input type="hidden" id="product_id" name="id" value="<?= $id ?>" />
        <input type="hidden" id="product_price" name="price" value="<?= $product_details['unit_price'] ?>" />
        <input type="hidden" id="is_pre_order" name="is_pre_order" value="0" />
        <input type="hidden" id="category_id" name="category_id" value="<?= $category_id ?>" />

        <h3 class="text-center fw-bold mt-0"><?= $product_details['product_item'] ?></h3>
        
        <div class="row mt-4">
            
            <div class="col-6">
                <div class="mb-3">
                    <label class="form-label" for="custom_length_quantity">Quantity</label>
                    <input type="number" id="custom_length_quantity" name="quantity" class="form-control mb-1" placeholder="Enter Quantity">
                </div>
            </div>
            <div class="col-6">
                <label class="form-label">Length</label>
                <div class="mb-3">
                    <select id="custom_length" class="form-control">
                        <option value="0">Select length</option>
                        <?php
                        $lengths = getInventoryLengths($id);
                        foreach ($lengths as $entry) {
                            $length = htmlspecialchars($entry['length']);
                            $feet = htmlspecialchars($entry['feet']);
                            echo "<option value=\"$feet\">$length</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-12">
                <div class="product_cost_display">
                    <h5 class="text-center pt-3 fs-5 fw-bold">Product Cost: $<span id="price_display">0.00</span></h5>
                </div>
            </div>
        </div>
        <div class="modal-footer d-flex justify-content-end align-items-center px-0">
            <div class="d-flex justify-content-end">
                <button class="btn btn-success ripple btn-secondary" type="submit">Add to Cart</button>
            </div>
        </div>
        <?php
        }else{
        ?>
        <h5 class="text-center">Product Not Found!</h5>
        <?php
        }
        ?>

        <script>
            $(document).ready(function() {
                function updatePrice() {
                    const basePrice = parseFloat($('#product_price').val()) || 0;
                    const totalLength = parseFloat($('#custom_length').val()) || 0;
                    const quantity = parseFloat($('#custom_length_quantity').val()) || 1;

                    const finalPrice = (basePrice * totalLength * quantity).toFixed(2);

                    $('#total_price').val(finalPrice);
                    $('#price_display').text(finalPrice);
                }

                $(document).on('change input', '#custom_length, #custom_length_quantity, #product_price', updatePrice);

                updatePrice();
            });
        </script>

<?php
}
