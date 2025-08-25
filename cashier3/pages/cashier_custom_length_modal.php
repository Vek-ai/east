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

        <div class="row">
            <div class="row">
                <div class="col-3">
                    <label class="fs-4 fw-semibold">Quantity</label>
                </div>
                <div class="col-9">
                    <label class="fs-4 fw-semibold">
                        <?= ($category_id == 16) ? 'Pack' : 'Length'; ?>
                    </label>
                </div>
            </div>

            <div class="custom-length-row row mt-1">
                <div class="col-2 col-6-md">
                    <input type="number" name="quantity[]" class="form-control mb-1 custom_length_quantity" value="1" placeholder="Enter Quantity">
                </div>
                <div class="col-3 col-6-md">
                    <?php if ($category_id == 16): ?>
                        <select class="form-control mb-1 custom_length_select select-2">
                            <option value="0">Select Pack...</option>
                            <?php
                            $query_pack = "
                                SELECT DISTINCT sp.id, sp.pack, sp.pack_abbreviation, sp.pack_count
                                FROM inventory i
                                INNER JOIN supplier_pack sp ON i.pack = sp.id
                                WHERE i.product_id = '$id'
                                AND i.pack IS NOT NULL
                                ORDER BY sp.pack ASC
                            ";
                            $result_pack = mysqli_query($conn, $query_pack);
                            while ($pack = mysqli_fetch_assoc($result_pack)) {
                                $pack_label = $pack['pack'] . " - " . $pack['pack_count'];
                                echo "<option value='{$pack['pack_count']}' data-feet='{$pack['pack_count']}'>
                                        {$pack_label}
                                    </option>";
                            }
                            ?>
                        </select>
                    <?php else: ?>
                        <select class="form-control mb-1 custom_length_select">
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

                                echo "<option value=\"$length_in_feet\" data-feet=\"$feet\" data-inch=\"$inch\" $selected>
                                        $display
                                    </option>";
                            }
                            ?>
                        </select>
                    <?php endif; ?>

                    <input type="hidden" name="length_feet[]" class="custom_length_feet">
                    <input type="hidden" name="length_inch[]" class="custom_length_inch">
                </div>
            </div>

            <div class="col-5 text-end"> 
                <a href="javascript:void(0)" type="button" id="duplicateCustomLengthFields" class="" title="Add Another">
                    <i class="fas fa-plus"></i>
                </a>
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
                function updatePrice($row) {
                    const basePrice = parseFloat($('#product_price').val()) || 0;
                    const feet = parseFloat($row.find('.custom_length_feet').val()) || 0;
                    const inches = parseFloat($row.find('.custom_length_inch').val()) || 0;
                    const quantity = parseFloat($row.find('.custom_length_quantity').val()) || 1;

                    const totalLength = feet + (inches / 12);
                    const multiplier = totalLength > 0 ? totalLength : 1; // prevent zero
                    const finalPrice = (basePrice * multiplier * quantity).toFixed(2);

                    $('#price_display').text(finalPrice);
                }

                $(document).on('change', '.custom_length_select', function () {
                    let $row = $(this).closest('.custom-length-row');
                    let feet = $(this).find(':selected').data('feet') || 0;
                    let inch = $(this).find(':selected').data('inch') || 0;

                    $row.find('.custom_length_feet').val(feet);
                    $row.find('.custom_length_inch').val(inch);

                    updatePrice($row);
                });

                $(document).on('input', '.custom_length_quantity', function () {
                    let $row = $(this).closest('.custom-length-row');
                    updatePrice($row);
                });

                $('#duplicateCustomLengthFields').on("click", function() {
                    let $newRow = $(".custom-length-row").first().clone();

                    $newRow.find('.custom_length_quantity').val("1");
                    $newRow.find('.custom_length_select').prop("selectedIndex", 0);
                    $newRow.find('.custom_length_feet').val("");
                    $newRow.find('.custom_length_inch').val("");

                    $(".custom-length-row").last().after($newRow);
                });

                updatePrice($(".custom-length-row").first());
            });

        </script>

<?php
}
