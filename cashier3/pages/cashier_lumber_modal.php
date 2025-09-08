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
        $items = getAvailableInventory($id);
        $treatedItems = [];
        $untreatedItems = [];

        foreach ($items as $item) {
            $type = strtolower(trim($item['lumber_type'] ?? ''));

            if ($type === 'treated') {
                $treatedItems[] = $item;
            } else {
                $untreatedItems[] = $item;
            }
        }
        ?>
        <input type="hidden" id="product_id" name="id" value="<?= $id ?>" />
        <input type="hidden" id="product_price" name="price" value="<?= $unit_price ?>" />
        <input type="hidden" id="is_pre_order" name="is_pre_order" value="0" />
        <input type="hidden" id="category_id" name="category_id" value="<?= $category_id ?>" />

        <h3 class="text-center fw-bold mt-0"><?= $product_details['product_item'] ?></h3>

        <div class="row">
            

            <h5 class="text-center">Treated Lumber</h5>
            <div class="row">
                <div class="col text-center"><label class="fs-4 fw-semibold">Quantity</label></div>
                <div class="col text-center"><label class="fs-4 fw-semibold">Description</label></div>
                <div class="col text-center"><label class="fs-4 fw-semibold">Length</label></div>
                <div class="col notes-col text-center d-none"><label class="fs-4 fw-semibold">Notes</label></div>
            </div>

            <div id="untreated-section">
                <div class="custom-length-row row mt-1">
                    <div class="col">
                        <input type="number" name="quantity[]" class="form-control mb-1 lumber_quantity" value="" placeholder="Enter Quantity">
                    </div>
                    <div class="col">
                        <select name="dimension_id[]" class="form-control dimension_select">
                            <option value="" hidden>Select Description</option>
                            <?php 
                                $seen = [];
                                foreach ($treatedItems as $item){
                                    $key = $item['dimension'] . ' ' . $item['dimension_unit'];
                                    if (in_array($key, $seen)) continue;
                                    $seen[] = $key;
                            ?>
                                <option value="<?= $item['dimension_id'] ?>">
                                    <?= htmlspecialchars($item['dimension']) ?> <?= htmlspecialchars($item['dimension_unit']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col">
                        <select class="form-control mb-1 length_select">
                            <option value="" hidden>Select Length</option>
                            <?php foreach ($treatedItems as $item){
                                $lengthFeet = $item['length_feet'] ?? 0;
                                $feet = floor($lengthFeet);
                                $inch = round(($lengthFeet - $feet) * 12);
                                if ($inch === 12) { $feet += 1; $inch = 0; }
                                $display = '';
                                if ($feet > 0) $display .= "{$feet}ft ";
                                if ($inch > 0) $display .= "{$inch}in";
                                if (($item['quantity_ttl'] ?? 0) < 1) {
                                    $display .= " (Out of Stock)";
                                }
                            ?>
                                <option 
                                    value="<?= htmlspecialchars($item['length']) ?>" 
                                    data-feet="<?= $feet ?>" 
                                    data-inch="<?= $inch ?>" 
                                    data-price="<?= $item['price'] ?>" 
                                    data-dim-id="<?= $item['dimension_id'] ?>" 
                                    data-inventory-id="<?= $item['inventory_id'] ?>"
                                >
                                    <?= $display ?>
                                </option>
                            <?php } ?>
                        </select>
                        <input type="hidden" name="price[]" class="lumber_price">
                        <input type="hidden" name="length_feet[]" class="custom_length_feet">
                        <input type="hidden" name="length_inch[]" class="custom_length_inch">
                    </div>
                    <div class="col notes-col d-none">
                        <input type="text" name="notes[]" class="form-control mb-1" placeholder="Enter Notes">
                    </div>
                </div>
            </div>
            <div class="col text-end"> 
                <a href="javascript:void(0)" type="button" id="duplicateUntreated" title="Add Untreated">
                    <i class="fas fa-plus"></i>
                </a>
            </div>


            <h5 class="mt-4 text-center">Untreated Lumber</h5>
            <div class="row">
                <div class="col text-center"><label class="fs-4 fw-semibold">Quantity</label></div>
                <div class="col text-center"><label class="fs-4 fw-semibold">Description</label></div>
                <div class="col text-center"><label class="fs-4 fw-semibold">Length</label></div>
                <div class="col notes-col text-center d-none"><label class="fs-4 fw-semibold">Notes</label></div>
            </div>

            <div id="treated-section">
                <div class="custom-length-row row mt-1">
                    <div class="col">
                        <input type="number" name="quantity[]" class="form-control mb-1 lumber_quantity" value="" placeholder="Enter Quantity">
                    </div>
                    <div class="col">
                        <select name="dimension_id[]" class="form-control dimension_select">
                            <option value="" hidden>Select Description</option>
                            <?php 
                                $seen = [];
                                foreach ($untreatedItems as $item){
                                    $key = $item['dimension'] . ' ' . $item['dimension_unit'];
                                    if (in_array($key, $seen)) continue;
                                    $seen[] = $key;
                            ?>
                                <option value="<?= $item['dimension_id'] ?>">
                                    <?= htmlspecialchars($item['dimension']) ?> <?= htmlspecialchars($item['dimension_unit']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col">
                        <select class="form-control mb-1 length_select">
                            <option value="" hidden>Select Length</option>
                            <?php foreach ($untreatedItems as $item){
                                $lengthFeet = $item['length_feet'] ?? 0;
                                $feet = floor($lengthFeet);
                                $inch = round(($lengthFeet - $feet) * 12);
                                if ($inch === 12) { $feet += 1; $inch = 0; }
                                $display = '';
                                if ($feet > 0) $display .= "{$feet}ft ";
                                if ($inch > 0) $display .= "{$inch}in";
                                if (($item['quantity_ttl'] ?? 0) < 1) {
                                    $display .= " (Out of Stock)";
                                }
                            ?>
                                <option 
                                    value="<?= htmlspecialchars($item['length']) ?>" 
                                    data-feet="<?= $feet ?>" 
                                    data-inch="<?= $inch ?>" 
                                    data-price="<?= $item['price'] ?>" 
                                    data-dim-id="<?= $item['dimension_id'] ?>" 
                                    data-inventory-id="<?= $item['inventory_id'] ?>"
                                >
                                    <?= $display ?>
                                </option>
                            <?php } ?>
                        </select>
                        <input type="hidden" name="price[]" class="lumber_price">
                        <input type="hidden" name="length_feet[]" class="custom_length_feet">
                        <input type="hidden" name="length_inch[]" class="custom_length_inch">
                    </div>
                    <div class="col notes-col d-none">
                        <input type="text" name="notes[]" class="form-control mb-1" placeholder="Enter Notes">
                    </div>
                </div>
            </div>
            <div class="col text-end"> 
                <a href="javascript:void(0)" type="button" id="duplicateTreated" title="Add Treated">
                    <i class="fas fa-plus"></i>
                </a>
            </div>

            <div class="col-12">
                <div class="product_cost_display">
                    <h5 class="text-center pt-3 fs-5 fw-bold">
                        Product Cost: $<span id="price_display">0.00</span>
                    </h5>
                </div>
            </div>
        </div>

        <div class="modal-footer d-flex justify-content-between align-items-center px-0">
            <button type="button" class="btn btn-outline-secondary" id="toggleNotes">Add Notes</button>
            <button type="submit" class="btn btn-success ripple btn-secondary">Add to Cart</button>
        </div>

        <script>
        $(document).ready(function() {
            function updateAllPrices() {
                
                let grandTotal = 0;

                $('.custom-length-row').each(function () {
                    const $row = $(this);
                    const feet = parseFloat($row.find('.custom_length_feet').val()) || 0;
                    const inches = parseFloat($row.find('.custom_length_inch').val()) || 0;
                    const quantity = parseFloat($row.find('.lumber_quantity').val()) || 1;
                    const basePrice = parseFloat($row.find('.lumber_price').val()) || 0;

                    const totalLength = feet + (inches / 12);
                    const multiplier = totalLength > 0 ? totalLength : 1;
                    const rowPrice = basePrice * multiplier * quantity;

                    grandTotal += rowPrice;
                });

                $('#price_display').text(grandTotal.toFixed(2));
            }

            $(document).on('change', '.custom-length-row #dimension_select', function() {
                const $row = $(this).closest('.custom-length-row');
                const selectedDim = $(this).val();

                const $lumberCol = $row.find('.lumber_type_select').closest('.col-3');
                const $lengthCol = $row.find('.length_select').closest('.col-3');

                if (selectedDim) {
                    $lumberCol.removeClass('d-none');
                    $lumberCol.find('select option').each(function() {
                        const dimIds = ($(this).data('dim-ids') || '').toString().split(',');
                        $(this).toggle(dimIds.includes(selectedDim) || $(this).val() === '');
                    });
                    $lumberCol.find('select').val('');
                } else {
                    $lumberCol.addClass('d-none').find('select').val('');
                    $lengthCol.find('select').val('');
                }
            });

            $(document).on('change', '.custom-length-row .lumber_type_select', function() {
                const $row = $(this).closest('.custom-length-row');
                const selectedDim = $row.find('#dimension_select').val();
                const selectedLumber = $(this).val();
                const $lengthCol = $row.find('.length_select').closest('.col-3');

                if (selectedLumber) {
                    $lengthCol.removeClass('d-none');
                    $lengthCol.find('select option').each(function() {
                        const dimId = $(this).data('dim-id')?.toString();
                        const lumber = $(this).data('lumber-type');
                        $(this).toggle((dimId === selectedDim) && (lumber === selectedLumber) || $(this).index() === 0);
                    });
                    $lengthCol.find('select').val('');
                } else {
                    $lengthCol.addClass('d-none').find('select').val('');
                }
            });

            $(document).on('change', '.length_select', function () {
                let $row = $(this).closest('.custom-length-row');
                let feet = $(this).find(':selected').data('feet') || 0;
                let inch = $(this).find(':selected').data('inch') || 0;
                let type = $(this).find(':selected').data('lumber-type') || '';
                let price = $(this).find(':selected').data('price') || '';

                $row.find('.custom_length_feet').val(feet);
                $row.find('.custom_length_inch').val(inch);
                $row.find('.lumber_price').val(price);

                if(type) $row.find('.lumber_type_select').val(type);

                updateAllPrices();
            });

            $(document).on('input', '.lumber_quantity', updateAllPrices);

            function duplicateUntreatedRow() {
                let $newRow = $("#untreated-section .custom-length-row").first().clone();
                $newRow.find('input, select').val('');
                $("#untreated-section").append($newRow);
            }

            function duplicateTreatedRow() {
                let $newRow = $("#treated-section .custom-length-row").first().clone();
                $newRow.find('input, select').val('');
                $("#treated-section").append($newRow);
            }

            $('#duplicateUntreated').on("click", function() {
                duplicateUntreatedRow();
            });

            $('#duplicateTreated').on("click", function() {
                duplicateTreatedRow();
            });

            for (let i = 0; i < 5; i++) {
                duplicateUntreatedRow();
                duplicateTreatedRow();
            }

            updateAllPrices();
        });
        </script>
        <?php
    } else {
        echo '<h5 class="text-center">Product Not Found!</h5>';
    }
}
?>
