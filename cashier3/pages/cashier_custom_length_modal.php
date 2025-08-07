<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
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
                <div class="mb-3 row g-2">
                    <select id="custom_length_select" class="form-control mb-1">
                        <option value="0">Select Length</option>
                        <?php
                        $lengths = getInventoryLengths($id);
                        foreach ($lengths as $entry) {
                            $length_in_feet = floatval($entry['feet']);
                            $feet = floor($length_in_feet);
                            $inch = round(($length_in_feet - $feet) * 12);

                            if ($inch === 12) {
                                $feet += 1;
                                $inch = 0;
                            }

                            $selected = ($feet == 1 && $inch == 0) ? 'selected' : '';

                            $display = '';
                            if ($feet > 0) $display .= "{$feet}ft ";
                            if ($inch > 0) $display .= "{$inch}in";
                            $display = trim($display);

                            echo "<option value=\"$length_in_feet\" data-feet=\"$feet\" data-inch=\"$inch\" $selected>$display</option>";
                        }
                        ?>
                    </select>

                    <input type="hidden" id="custom_length_feet" name="custom_length_feet" class="form-control mb-1">
                    <input type="hidden" id="custom_length_inch" name="custom_length_inch" class="form-control mb-1">
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
                    const feet = parseFloat($('#custom_length_feet').val()) || 0;
                    const inches = parseFloat($('#custom_length_inch').val()) || 0;
                    const quantity = parseFloat($('#custom_length_quantity').val()) || 1;

                    const totalLength = feet + (inches / 12);
                    const multiplier = totalLength;

                    const finalPrice = (basePrice * multiplier * quantity).toFixed(2);

                    $('#total_price').val(finalPrice);
                    $('#price_display').text(finalPrice);
                }

                $(document).on('change', '#custom_length_select', function () {
                    var feet = $(this).find(':selected').data('feet');
                    var inch = $(this).find(':selected').data('inch');

                    $('#custom_length_feet').val(feet || '');
                    $('#custom_length_inch').val(inch || '');

                    updatePrice();
                });

                $(document).on('input', '#custom_length_quantity', updatePrice);

                updatePrice();
            });
        </script>

<?php
}
