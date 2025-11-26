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
        <input type="hidden" id="product_price" name="price" value="<?= $unit_price ?>" />
        <input type="hidden" id="is_pre_order" name="is_pre_order" value="0" />
        <input type="hidden" id="category_id" name="category_id" value="<?= $category_id ?>" />

        <h3 class="text-center fw-bold mt-0"><?= $product_details['product_item'] ?></h3>

        <div class="row">
            <div class="row justify-content-center">
                <div class="col-3 text-center"><label class="fs-4 fw-semibold">Quantity</label></div>
                <div class="col-3 text-center"><label class="fs-4 fw-semibold">Length</label></div>
                <div class="col-3 notes-col text-center d-none"><label class="fs-4 fw-semibold">Notes</label></div>
            </div>

            <div id="untreated-section">
                <div class="custom-length-row row justify-content-center mt-1">
                    <div class="col-3">
                        <input type="number" name="quantity[]" class="form-control mb-1 lumber_quantity" value="" placeholder="Enter Quantity">
                    </div>
                    <div class="col-3">
                        <select class="form-control mb-0 length_select" name="dimension_id[]">
                            <option value="" selected>Select Length</option>
                            <?php
                            $lengths = getProductAvailableLengths($id);
                            foreach ($lengths as $entry) {
                                $product_length = htmlspecialchars($entry['length']);
                                $dimension_id   = htmlspecialchars($entry['dimension_id']);
                                echo "<option value=\"$dimension_id\">$product_length</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-3 notes-col d-none">
                        <input type="text" name="notes[]" class="form-control mb-1" placeholder="Enter Notes">
                    </div>
                </div>
            </div>
            <div class="col text-end"> 
                <a href="javascript:void(0)" type="button" id="duplicateUntreated" title="Add Untreated">
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
                const formData = new FormData();
                formData.append('fetch_price', 1);
                formData.append('product_id', $('#product_id').val());
                formData.append('color', $('select[name="color_id[]"]').first().val() || '');

                $('.custom-length-row').each(function () {
                    const $row = $(this);
                    const qty = $row.find('.lumber_quantity').val() || '';
                    const dimension_id = $row.find('.length_select').val() || '';

                    formData.append('quantity[]', qty);
                    formData.append('dimension_id[]', dimension_id);
                });

                $.ajax({
                    url: 'pages/cashier_lumber_modal.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        $('#price_display').text(response);
                    },
                    error: function (xhr, status, error) {
                        console.error('Price fetch error:', error);
                    }
                });
            }

            $(document).on('change', '.length_select', function () {
                updateAllPrices();
            });

            $(document).on('input', '.lumber_quantity', updateAllPrices);

            function duplicateRow() {
                let $newRow = $("#untreated-section .custom-length-row").first().clone();
                $newRow.find('input, select').val('');
                $("#untreated-section").append($newRow);
            }

            $('#duplicateUntreated').on("click", function() {
                duplicateRow();
            });

            for (let i = 0; i < 5; i++) {
                duplicateRow();
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
    global $conn;

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantities = $_POST['quantity'] ?? [];
    $dimension_ids = $_POST['dimension_id'] ?? [];
    $color_id   = isset($_POST['color']) ? intval($_POST['color']) : 0;

    $totalPrice = 0;

    if ($product_id > 0) {
        $product     = getProductDetails($product_id);
        $basePrice   = floatval($product['unit_price']);
        $soldByFeet  = intval($product['sold_by_feet'] ?? 1);

        $bulk = getBulkData($product_id);
        $bulk_price     = floatval($bulk['bulk_price']);
        $bulk_starts_at = floatval($bulk['bulk_starts_at']);

        $totalQty = 0;
        foreach ($quantities as $qty) {
            $totalQty += floatval($qty);
        }

        if ($bulk_price > 0 && $bulk_starts_at > 0 && $totalQty >= $bulk_starts_at) {
            $basePrice = $bulk_price;
        }

        foreach ($quantities as $index => $qty) {
            $qty = floatval($qty);
            if ($qty <= 0) continue;

            $dim_id = intval($dimension_ids[$index] ?? 0);

            $res = mysqli_query($conn, "SELECT * FROM dimensions WHERE dimension_id = '$dim_id' LIMIT 1");
            $row = mysqli_fetch_assoc($res);

            $feet  = floatval($row['dimension_feet'] ?? 0);
            $inch  = floatval($row['dimension_inches'] ?? 0);

            $unitPrice = calculateUnitPrice(
                $basePrice,
                $feet,
                $inch,
                '',
                '',
                '', 
                '',
                $color_id,
                '',
                ''
            );

            $totalPrice += $unitPrice * $qty;
        }
    }

    echo number_format($totalPrice, 2);
}
?>
