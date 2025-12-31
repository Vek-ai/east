<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_POST['fetch_modal'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $product_details = getProductDetails($id);

    if (!empty($product_details)) {
        $unit_price = $product_details['unit_price'];
        $inventoryItems = getAvailableInventory($id);

        $screw_type = $product_details['screw_type'];
        $screw_type_det = getProductScrewType($screw_type);
        ?>
        <input type="hidden" id="product_id" name="id" value="<?= $id ?>" />
        <input type="hidden" id="product_price" name="price" value="<?= $unit_price ?>" />

        <h3 class="text-center fw-bold mt-0"><?= $product_details['product_item'] ?></h3>

        <div class="row">
            <div class="col-3 mb-3">
                <select class="form-control screw_select2" id="screw-color" name="color_id">
                    <option value="" data-category="">All Colors</option>
                    <optgroup label="Assigned Colors">
                        <?php
                        $assigned_colors = getAssignedProductColors($id);
                        $all_colors = $assigned_colors ?: [];

                        if ($color && !in_array($color, $all_colors)) {
                            $all_colors[] = $color;
                        }

                        if (!empty($all_colors)) {
                            $color_ids_str = implode(',', array_map('intval', $all_colors));
                            $query_colors = "
                                SELECT color_id, color_name, product_category
                                FROM paint_colors
                                WHERE color_id IN ($color_ids_str)
                                AND hidden = 0
                                AND color_status = 1
                                ORDER BY color_name ASC
                            ";
                            $result_colors = mysqli_query($conn, $query_colors);
                            while ($row = mysqli_fetch_assoc($result_colors)) {
                                $selected = ($row['color_id'] == $color) ? 'selected' : '';
                        ?>
                                <option 
                                    value="<?= htmlspecialchars($row['color_id']) ?>" 
                                    data-category="<?= htmlspecialchars($row['product_category']) ?>"
                                    <?= $selected ?>>
                                    <?= htmlspecialchars($row['color_name']) ?>
                                </option>
                        <?php
                            }
                        }
                        ?>
                    </optgroup>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="row">
                <div class="col"><label class="fs-4 fw-semibold">Quantity</label></div>
                <div class="col"><label class="fs-4 fw-semibold">Length</label></div>
                <div class="col"><label class="fs-4 fw-semibold">Pack</label></div>
                <div class="col notes-col text-center d-none"><label class="fs-4 fw-semibold">Notes</label></div>
            </div>

            <div class="screw-row row mt-1">
                <div class="col col-6-md">
                    <input type="number" name="quantity[]" class="form-control mb-1 screw_quantity" value="" placeholder="Enter Quantity">
                </div>
                <div class="col">
                    <select id="dimension_select" name="dimension_id[]" class="form-control">
                        <option value="" hidden>Select Length</option>
                        <?php 
                        $dimension_arr = json_decode($screw_type_det['dimensions'] ?? '[]', true);
                        if (!is_array($dimension_arr)) $dimension_arr = [];

                        $lengths = [];
                        $lengthQuery = "SELECT * FROM dimensions WHERE dimension_category = 16 ORDER BY dimension ASC";
                        $lengthRes = mysqli_query($conn, $lengthQuery);
                        while ($l = mysqli_fetch_assoc($lengthRes)) {
                            $lengths[$l['dimension_id']] = $l;
                        }

                        foreach ($dimension_arr as $dim_id):
                            $dim = $lengths[$dim_id] ?? null;
                            if (!$dim) continue;

                            $unit_price  = $product_lengths[$dim_id]['unit_price'] ?? '';
                            $floor_price = $product_lengths[$dim_id]['floor_price'] ?? '';
                            $bulk_price  = $product_lengths[$dim_id]['bulk_price'] ?? '';
                            $bulk_starts_at = $product_lengths[$dim_id]['bulk_starts_at'] ?? '';

                            $dimensionDisplay = trim(($dim['dimension'] ?? 0));
                        ?>
                        <option value="<?= $dim_id ?>">
                            <?= $dimensionDisplay ?>
                        </option>
                        <?php endforeach; ?>
                        ?>
                    </select>
                </div>
                <div class="col">
                    <select class="form-control mb-1 screw_select pack_select select-2" name="pack[]">
                        <option value="">Select Pack</option>
                        <?php
                        $packArray = [];
                        if (!empty($product_details['pack'])) {
                            $packArray = json_decode($product_details['pack'], true);
                            if (!is_array($packArray)) {
                                $packArray = [];
                            }
                        }

                        $dim_id = $item['dimension_id'] ?? '';
                        $colorId = $item['color_id'] ?? '';
                        $price   = $item['price'] ?? '';
                        $inventoryId = $item['inventory_id'] ?? '';

                        foreach ($packArray as $pack) {
                            $packPieces = getPackPieces($pack);
                            $packName = getPackName($pack);

                            $display = $packName;
                            if ($packPieces !== '') {
                                $display .= ' (' . $packPieces .' PCS)';
                            }

                            echo "<option 
                                    value='{$pack}'>
                                    {$display}
                                </option>";
                        }
                        ?>
                    </select>
                    <input type="hidden" name="price[]" class="screw_price" value="<?= $unit_price ?>">
                    <input type="hidden" name="length_feet[]" class="custom_pack">
                    <input type="hidden" name="length_inch[]" class="custom_length_inch">
                </div>
                <div class="col notes-col d-none">
                    <input type="text" name="notes[]" class="form-control mb-1" placeholder="Enter Notes">
                </div>
            </div>

            <div class="col-12 text-end">
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

        <div class="modal-footer d-flex justify-content-between align-items-center px-0">
            <button type="button" class="btn btn-outline-secondary" id="toggleNotes">Add Notes</button>
            <button class="btn btn-success ripple btn-secondary" type="submit">Add to Cart</button>
        </div>

        <script>
        $(document).ready(function() {
            function updateAllPrices() {
                const formData = new FormData();
                formData.append('fetch_price', 1);
                formData.append('product_id', $('#product_id').val());
                formData.append('color', $('select[name="color_id[]"]').first().val() || '');

                $('.screw-row').each(function () {
                    const $row = $(this);
                    formData.append('quantity[]', $row.find('.screw_quantity').val() || 0);
                    formData.append('dimension_id[]', $row.find('#dimension_select').val() || 0);
                    formData.append('pack[]', $row.find('.pack_select').val() || 0);
                });

                $.ajax({
                    url: 'pages/cashier_screw_modal.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        $('#price_display').text(response);
                        console.log(response);
                    },
                    error: function (xhr, status, error) {
                        console.error('Price fetch error:', error);
                    }
                });
            }

            $(document).on('change input', '.screw_quantity, .screw_select, .color_select, #dimension_select', updateAllPrices);

            updateAllPrices();

            $(document).on('change', '.screw_select', updateAllPrices);
            $(document).on('input', '.screw_quantity', updateAllPrices);

            $(document).on('change', '.screw-row #dimension_select', function() {
                const $row = $(this).closest('.screw-row');
                const selectedDim = $(this).val();
                const $colorSelect = $row.find('.color_select');

                if (selectedDim) {
                    $colorSelect.closest('.col').removeClass('d-none');
                    $colorSelect.find('option').each(function() {
                        const dimId = $(this).data('dim-id')?.toString();
                        $(this).toggle(dimId === selectedDim || $(this).val() === '');
                    });
                    $colorSelect.val('');
                }
            });

            $(document).on('change', '.screw_select', function () {
                let $row = $(this).closest('.screw-row');
                let pack = $(this).find(':selected').data('pack') || 0;
                let price = $(this).find(':selected').data('price') || 0;

                $row.find('.custom_pack').val(pack);
                $row.find('.screw_price').val(price);

                updateAllPrices();
            });

            function duplicateScrewRow() {
                let $newRow = $(".screw-row").first().clone();
                $newRow.find('.screw_quantity').val("");
                $newRow.find('.screw_select').prop("selectedIndex", 0);
                $newRow.find('.color_select').prop("selectedIndex", 0);
                $(".screw-row").last().after($newRow);
            }

            $('#duplicateScrewFields').on("click", function() {
                duplicateScrewRow();
            });

            for (let i = 0; i < 5; i++) {
                duplicateScrewRow();
            }

            updateAllPrices();
        });
        </script>
        <?php
    } else {
        echo '<h5 class="text-center">Product Not Found!</h5>';
    }
}

if (isset($_POST['fetch_price'])) {
    $product_id = intval($_POST['product_id'] ?? 0);
    $color_id   = intval($_POST['color'] ?? 0);
    $quantities = $_POST['quantity'] ?? [];
    $dimension_ids = $_POST['dimension_id'] ?? [];
    $pack = $_POST['pack'] ?? [];

    $totalPrice = 0;

    if ($product_id > 0 && !empty($quantities)) {
        $product = getProductDetails($product_id);
        $soldByFeet = intval($product['sold_by_feet'] ?? 0);

        $bulkData = getBulkData($product_id);
        $bulk_starts = $product['bulk_starts_at'] ?? 1;

        $length_count = count($quantities);

        for ($i = 0; $i < $length_count; $i++) {
            $qty = floatval($quantities[$i] ?? 0);
            if ($qty <= 0) continue;

            $dim_id = intval($dimension_ids[$i] ?? 0);
            $packPieces = getPackPieces($pack[$i]);
            $pack_count = ($packPieces < 1) ? 1 : $packPieces;

            $res = mysqli_query($conn, "SELECT * FROM product_screw_lengths WHERE product_id = '$product_id' AND dimension_id = '$dim_id' LIMIT 1");
            $row = mysqli_fetch_assoc($res);

            $unit_price  = floatval($row['unit_price'] ?? $product['unit_price'] ?? 0);
            $bulk_price  = floatval($row['bulk_price'] ?? 0);

            if ($bulk_price > 0 && $qty >= $bulk_starts) {
                $unit_price = $bulk_price;
            }

            $panel_type = '';
            $bends = 0;
            $hems = 0;
            $grade = '';
            $gauge = '';

            $totalPrice += $pack_count * $qty * calculateUnitPrice(
                $unit_price,
                1,
                0,
                $panel_type,
                $soldByFeet,
                $bends,
                $hems,
                $color_id,
                $grade,
                $gauge
            );
        }
    }

    echo number_format($totalPrice, 2);
    exit;
}

?>
