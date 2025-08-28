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
        $unit_price = $product_details['unit_price'];
        $inventoryItems = getAvailableInventory($id);

        $availableColors = [];
        foreach ($inventoryItems as $item) {
            if (!empty($item['color_id'])) $availableColors[$item['color_id']] = true;
        }

        $colorOptions = [];
        if (!empty($availableColors)) {
            $colorIds = implode(',', array_keys($availableColors));
            $query_color = "SELECT color_id, color_name FROM paint_colors WHERE color_id IN ($colorIds) AND hidden='0' AND color_status='1'";
            $result_color = mysqli_query($conn, $query_color);
            while ($row_color = mysqli_fetch_assoc($result_color)) {
                $colorOptions[$row_color['color_id']] = htmlspecialchars($row_color['color_name']);
            }
        }
        ?>
        <input type="hidden" id="product_id" name="id" value="<?= $id ?>" />
        <input type="hidden" id="product_price" name="price" value="<?= $unit_price ?>" />

        <h3 class="text-center fw-bold mt-0"><?= $product_details['product_item'] ?></h3>

        <div class="row">
            <div class="row">
                <div class="col-3"><label class="fs-4 fw-semibold">Quantity</label></div>
                <div class="col-3"><label class="fs-4 fw-semibold">Pack</label></div>
                <div class="col-3"><label class="fs-4 fw-semibold">Color</label></div>
            </div>

            <div class="screw-row row mt-1">
                <div class="col-3 col-6-md">
                    <input type="number" name="quantity[]" class="form-control mb-1 screw_quantity" value="1" placeholder="Enter Quantity">
                </div>
                <div class="col-3 col-6-md">
                    <select class="form-control mb-1 screw_select select-2">
                        <option value="0">Select Pack...</option>
                        <?php
                        foreach ($inventoryItems as $item) {
                            $inventoryId = $item['inventory_id'];
                            $pack = getPackPieces($item['pack']);
                            $colorId = $item['color_id'] ?? 0;
                            $price = $item['price'] ?? 0;

                            $dimensionParts = [];
                            if (!empty($item['dimension'])) {
                                $dimensionParts = array_map('trim', explode('-', $item['dimension']));
                            }
                            $dimensionDisplay = !empty($dimensionParts) ? implode(' - ', $dimensionParts) : '';
                            $display = $pack . ' pcs';
                            if ($dimensionDisplay !== '') {
                                $display .= ' - ' . $dimensionDisplay;
                            }

                            echo "<option value='{$inventoryId}' data-pack='{$pack}' data-color='{$colorId}' data-price='{$price}' data-dimension='{$dimensionDisplay}'>
                                    {$display}
                                </option>";
                        }
                        ?>
                    </select>
                    <input type="hidden" name="price[]" class="screw_price">
                    <input type="hidden" name="length_feet[]" class="custom_pack">
                    <input type="hidden" name="length_inch[]" class="custom_length_inch">
                </div>
                <div class="col-3 col-6-md">
                    <select class="form-control mb-1 color_select select-2" name="color_id[]">
                        <option value="0">Select Color...</option>
                        <?php
                        foreach ($inventoryItems as $item) {
                            if (!empty($item['color_id'])) {
                                $dimensionParts = [];
                                if (!empty($item['dimension'])) {
                                    $dimensionParts = array_map('trim', explode('-', $item['dimension']));
                                }
                                $dimensionDisplay = !empty($dimensionParts) ? implode(' - ', $dimensionParts) : '';

                                $display = '';
                                if ($dimensionDisplay !== '') $display .= $dimensionDisplay . ' - ';
                                $display .= $colorOptions[$item['color_id']] ?? '';

                                echo "<option value='{$item['color_id']}'>{$display}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

            </div>

            <div class="col-5 text-end">
                <a href="javascript:void(0)" id="duplicateScrewFields" title="Add Another">
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
            <button class="btn btn-success ripple btn-secondary" type="submit">Add to Cart</button>
        </div>

        <script>
        $(document).ready(function() {
            function updateAllPrices() {
                let grandTotal = 0;

                $('.screw-row').each(function () {
                    const $row = $(this);
                    const quantity = parseFloat($row.find('.screw_quantity').val()) || 1;
                    const pack = parseFloat($row.find('.custom_pack').val()) || 1;
                    const basePrice = parseFloat($row.find('.screw_price').val()) || 0;
                    const rowPrice = basePrice * quantity * pack;
                    grandTotal += rowPrice;
                });

                $('#price_display').text(grandTotal.toFixed(2));
            }

            $(document).on('change', '.screw_select', updateAllPrices);
            $(document).on('input', '.screw_quantity', updateAllPrices);

            $(document).on('change', '.screw_select', function () {
                let $row = $(this).closest('.screw-row');
                let pack = $(this).find(':selected').data('pack') || 0;
                let price = $(this).find(':selected').data('price') || 0;

                $row.find('.custom_pack').val(pack);
                $row.find('.screw_price').val(price);

                updateAllPrices();
            });

            $('#duplicateScrewFields').on("click", function() {
                let $newRow = $(".screw-row").first().clone();
                $newRow.find('.screw_quantity').val("1");
                $newRow.find('.screw_select').prop("selectedIndex", 0);
                $newRow.find('.color_select').prop("selectedIndex", 0);
                $(".screw-row").last().after($newRow);
            });

            updateAllPrices();
        });
        </script>
        <?php
    } else {
        echo '<h5 class="text-center">Product Not Found!</h5>';
    }
}
?>
