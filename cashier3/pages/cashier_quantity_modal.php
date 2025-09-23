<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';
$trim_id = 4;
$panel_id = 3;
$lumber_id = 1;
$truss_id = 2;
$acc_id = 6;
$fastener_id = 5;
$stiffening_rib_id = 7;
if(isset($_POST['fetch_prompt_quantity'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $grade = mysqli_real_escape_string($conn, $_POST['grade'] ?? '');
    $gauge = mysqli_real_escape_string($conn, $_POST['gauge'] ?? '');
    $product_details = getProductDetails($id);
    $type_details = getProductTypeDetails($product_details['product_type']);
    $is_special = $type_details['special'];
    ?>
    <style>
        .tooltip-custom {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 10;
            margin-top: 5px;
        }
    </style>
    <?php
        if (!empty($product_details)) {
            $category_id = $product_details['product_category'];
            $sold_by_feet = $product_details["sold_by_feet"];
            $standing_seam = $product_details["standing_seam"];
            $board_batten = $product_details["board_batten"];
            $category_id = $product_details["product_category"];
            $basePrice = $product_details["unit_price"];
            $product_system = $product_details["product_system"];
            $profile = $product_details["profile"];
        ?>
        <input type="hidden" id="product_id" name="product_id" value="<?= $id ?>" />
        <input type="hidden" id="category_id" name="category_id" value="<?= $category_id ?>" />
        <input type="hidden" id="is_pre_order" name="is_pre_order" value="0" />
        <div class="row">
            
            <?php
            if (!empty($profile)) {
                if (is_string($profile)) {
                    $profileArray = json_decode($profile, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($profileArray)) {
                        $profile = $profileArray;
                    } else {
                        $profile = [$profile];
                    }
                } elseif (!is_array($profile)) {
                    $profile = [$profile];
                }

                $highestProfile = max($profile);

                switch ($highestProfile) {
                    case 14: // low-rib
                        include "panel_layouts/low_rib.php";
                        break;
                    case 15: // hi-rib
                        include "panel_layouts/hi_rib.php";
                        break;
                    case 16: // corrugated
                        include "panel_layouts/corrugated.php";
                        break;
                    case 17: // 5v
                        include "panel_layouts/5v.php";
                        break;
                    case 18: // standing_seam
                        include "panel_layouts/standing_seam.php";
                        break;
                    case 19: // snap_lock
                        include "panel_layouts/snap_lock.php";
                        break;
                    case 20: // mechanical_seam
                        include "panel_layouts/mechanical_seam.php";
                        break;
                    case 21: // board_batten
                        include "panel_layouts/board_batten.php";
                        break;
                    case 41: // flush_wall
                        include "panel_layouts/flush_wall.php";
                        break;
                    case 42: // plank panel
                        include "panel_layouts/plank_panel.php";
                        break;
                    default:
                        echo 'Profile '. $highestProfile .'<h5 class="text-center text-danger pt-3 fs-5 fw-bold">Product Profile is not set.</h5>';
                }
            } else {
                echo 'Profile '. $profile .' <h5 class="text-center text-danger pt-3 fs-5 fw-bold">Product Profile is not set.</h5>';
            }
            ?>
        </div>

        <div class="input-group d-flex align-items-center justify-content-between flex-wrap w-100 mt-3 mb-2 <?= empty($is_special) ? 'd-none' : '';?>">
            <div class="me-2 flex-grow-1">
                <label class="fs-5 fw-bold" for="bend_product">Bends</label>
                <input id="bend_product" name="bend_product" class="form-control" placeholder="Enter Bends">
            </div>
            <div class="me-2 flex-grow-1">
                <label class="fs-5 fw-bold" for="hem_product">Hems</label>
                <input id="hem_product" name="hem_product" class="form-control" placeholder="Enter Hems">
            </div>
        </div> 
        <div class="product_cost_display">
            <h5 class="text-center pt-3 fs-5 fw-bold">Product Cost: $<span id="product-cost">0.00</span></h5>
        </div>

        <div class="modal-footer d-flex justify-content-between align-items-center px-0">
            <button type="button" class="btn btn-outline-secondary" id="toggleNotes">Add Notes</button>
            <button class="btn btn-success ripple btn-secondary" type="submit">Add to Cart</button>
        </div>
        <script>
        var maxLength = 99999;

        function duplicateRow() {
            var $newRow = $('.quantity-length-container').first().clone(true, true);
            var uniqueId = Date.now() + Math.floor(Math.random() * 1000);

            $newRow.find('input, div').each(function() {
                var oldId = $(this).attr('id');
                if (oldId) {
                    $(this).attr('id', oldId + '_' + uniqueId);
                }
            });

            $newRow.find('label').each(function() {
                var oldFor = $(this).attr('for');
                if (oldFor) {
                    $(this).attr('for', oldFor + '_' + uniqueId);
                }
            });

            $newRow.find('.solid_panel').prop('checked', true);
            $newRow.find('.vented_panel').prop('checked', false);

            $newRow.find('.bundle-checkbox').prop('checked', false);
            $('#unbundledRows').append($newRow);

            calculateProductCost();
        }

        function parseFraction(val) {
            if (!val) return 0;
            if (val.includes('/')) {
                let parts = val.split('/');
                if (parts.length === 2) {
                    let num = parseFloat(parts[0]) || 0;
                    let den = parseFloat(parts[1]) || 1;
                    return den !== 0 ? num / den : 0;
                }
            }
            return parseFloat(val) || 0;
        }

        function calculateProductCost() {
            const product_id = $('#product_id').val();
            const quantities = [], lengthFeetArr = [], lengthInchArr = [], panelOptionArr = [];

            $('.quantity-length-container').each(function() {
                quantities.push(parseInt($(this).find('.quantity-product').val()) || 0);
                lengthFeetArr.push(parseFloat($(this).find('.length_feet').val()) || 0);
                lengthInchArr.push($(this).find('.length_inch').val() || 0);

                panelOptionArr.push($(this).find('select[name="panel_option"]').val() || 'solid');
            });

            const bends = parseInt($('#bend_product').val()) || 0;
            const hems = parseInt($('#hem_product').val()) || 0;
            const color = parseInt($('#qty-color').val()) || 0;
            const soldByFeet = <?= $sold_by_feet; ?>;
            const basePrice = <?= $basePrice; ?>;

            $.ajax({
                url: 'pages/cashier_quantity_modal.php',
                method: 'POST',
                data: {
                    product_id: product_id,
                    quantity: quantities,
                    lengthFeet: lengthFeetArr,
                    lengthInch: lengthInchArr,
                    panel_option: panelOptionArr,
                    soldByFeet: soldByFeet,
                    bends: bends,
                    hems: hems,
                    basePrice: basePrice,
                    color: color,
                    fetch_price: 'fetch_price'
                },
                success: function(response) {
                    $('#product-cost').text(response);
                }
            });
        }

        function fetchCoilStock() {
            const color = parseInt($('#qty-color').val()) || 0;
            const grade = parseInt($('#qty-grade').val()) || 0;
            const gauge = parseInt($('#qty-gauge').val()) || 0;

            if (color === 0 || grade === 0 || gauge === 0) {
                $('#coil-stock').text('');
                $('#is_pre_order').val('0');
                return;
            }

            $.ajax({
                url: 'pages/cashier_quantity_modal.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    color: color,
                    grade: grade,
                    gauge: gauge,
                    fetch_stock_coil: 'fetch_stock_coil'
                },
                success: function(response) {
                    if (response.success === true) {
                        $('#coil-stock').text('AVAILABLE');
                        $('#is_pre_order').val('0');
                    } else {
                        $('#coil-stock').text('PREORDER');
                        $('#is_pre_order').val('1');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", {
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                }
            });
        }
        
        function calculateBackerRod() {
            let totalBackerRod = 0;

            $('.quantity-length-container').each(function () {
                let $row = $(this);

                let style  = ($row.find('.panel_style').val() || '').toLowerCase();
                let qty    = parseFloat($row.find('.quantity-product').val()) || 0;
                let ft     = parseFloat($row.find('.length_feet').val()) || 0;
                let inch   = parseFloat($row.find('.length_inch').val()) || 0;

                let length = ft + (inch / 12);

                if (style === 'flat' && length >= 3) {
                    totalBackerRod += qty * length;
                }
            });

            if (totalBackerRod > 0) {
                $('.backer-rod-container').removeClass('d-none');
                $('.backer_rod').val(totalBackerRod.toFixed(3));
            } else {
                $('.backer-rod-container').addClass('d-none');
                $('.backer_rod').val('');
            }
        }

        $(document).ready(function () {
            let bundleCount = 1;
            let bundleVisible = false;
            let product_system = <?= !empty($product_system) ? $product_system : 'null' ?>;
            

            $(document).on('change', '#qty-color, #qty-grade, #qty-gauge', function() {
                fetchCoilStock();
                calculateProductCost();
            });

            $(document).on("change", ".fraction_input", function() {
                const allowed = [0.25, 0.50, 0.75];
                const val = parseFloat($(this).val());

                if (val && !allowed.includes(val)) {
                    alert("Invalid fraction. Allowed values: .25, .50, .75 only.");
                    $(this).val("");
                    $(this).focus();
                }
            });

            $(".quantity-length-container .panel-options *").attr("tabindex", "-1");

            $(document).on("keydown", ".length_inch", function(e) {
                if (e.key === "Tab" && !e.shiftKey) {
                    e.preventDefault();

                    let $currentRow = $(this).closest(".quantity-length-container");
                    let $allRows = $(".quantity-length-container").filter(function() {
                        return $(this).find(".quantity-product").length > 0;
                    });

                    let currentIndex = $allRows.index($currentRow);
                    let $nextRow = $allRows.eq(currentIndex + 1);

                    if ($nextRow.length) {
                        $nextRow.find(".quantity-product").focus().select();
                    } else {
                        $("#duplicateFields").focus();
                    }
                }
            });

            $('#duplicateFields').click(function() {
                duplicateRow();
            });

            $(document).on('change input', '.quantity-product, .length_feet, .length_inch, .fraction_input, #panel_option, #bend_product, #hem_product', calculateProductCost);

            $('input[name="panel_type"]').on('change', calculateProductCost);

            $(document).off('click', '#createBundleBtn').on('click', '#createBundleBtn', function () {
                bundleVisible = !bundleVisible;

                if (bundleVisible) {
                    $('#productFormCol').removeClass('col-12').addClass('col-9');
                    $('#bundleSection').removeClass('d-none');
                    $('.bundle-checkbox-wrapper, .bundle-checkbox-header').removeClass('d-none');
                } else {
                    $('#productFormCol').removeClass('col-9').addClass('col-12');
                    $('#bundleSection').addClass('d-none');
                    $('.bundle-checkbox-wrapper, .bundle-checkbox-header').addClass('d-none');
                    $('.bundle-checkbox').prop('checked', false);
                }
            });

            $(document).off('click', '#addToBundleBtn').on('click', '#addToBundleBtn', function () {
                const bundleName = $('#bundleName').val().trim();
                if (!bundleName) {
                    alert("Please enter a bundle name");
                    return;
                }

                $('.bundle-checkbox:checked').each(function () {
                    $(this)
                        .closest('.quantity-length-container')
                        .find('input[name="bundle_name[]"]')
                        .val(bundleName);
                });

                let $selectedRows = $('.bundle-checkbox:checked').closest('.quantity-length-container');
                if ($selectedRows.length === 0) {
                    alert("Please select at least one row to add to the bundle");
                    return;
                }

                let $bundleWrapper = $(`
                    <div class="card p-2 mb-3 bundle-wrapper">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0">Bundle ${bundleCount}: ${bundleName}</h6>
                            <button class="btn btn-sm btn-outline-danger removeBundleBtn">Remove Bundle</button>
                        </div>
                        <div class="bundle-rows"></div>
                    </div>
                `);

                $selectedRows.appendTo($bundleWrapper.find('.bundle-rows'));

                $('#bundleGroups').append($bundleWrapper);

                $('#bundleName').val('');
                $('#bundleCounter').text(++bundleCount);

                $('#bundleSection').addClass('d-none');
                $('.bundle-checkbox-wrapper, .bundle-checkbox-header').addClass('d-none');
                $('.bundle-checkbox').prop('checked', false);
                bundleVisible = false;
            });

            $(document).off('click', '.removeBundleBtn').on('click', '.removeBundleBtn', function() {
                const $bundle = $(this).closest('.bundle-wrapper');
                $bundle.find('.quantity-length-container').appendTo('#unbundledRows');
                $bundle.remove();
            });

            $(document).off("input", ".length_feet, .length_inch").on("input", ".length_feet, .length_inch", function () {
                let $group = $(this).closest('.input-group');
                let feet = parseFloat($group.find(".length_feet").val()) || 0;
                let inch = parseFloat($group.find(".length_inch").val()) || 0;

                if (inch >= 12) {
                    feet += Math.floor(inch / 12);
                    inch = inch % 12;
                }

                let totalFeet = feet + (inch / 12);

                if (typeof maxLength === "undefined" || maxLength <= 0) maxLength = 60;

                if (totalFeet > maxLength) {
                    $group.find(".length_feet").val(Math.floor(maxLength));
                    $group.find(".length_inch").val(Math.round((maxLength - Math.floor(maxLength)) * 12));
                    alert("Maximum length allowed is " + maxLength + " ft.");
                }
            });

        });
        </script>
        <?php
        }else{
        ?>
        <h5 class="text-center">Product Not Found! ID: <?= $id; ?>  </h5>
        <?php
        }
        ?>
<?php
}

if (isset($_POST['fetch_price'])) {
    $product_id      = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantities      = $_POST['quantity'] ?? [];
    $lengthFeet      = $_POST['lengthFeet'] ?? [];
    $lengthInch      = $_POST['lengthInch'] ?? [];
    $panelTypes      = $_POST['panel_option'] ?? [];
    $panelDripStops  = $_POST['panel_drip_stop'] ?? [];
    $bends           = isset($_POST['bends']) ? intval($_POST['bends']) : 0;
    $hems            = isset($_POST['hems']) ? intval($_POST['hems']) : 0;
    $color_id           = isset($_POST['color']) ? intval($_POST['color']) : 0;

    $product = getProductDetails($product_id);
    $color_details = getColorDetails($color_id);
    $category_id   = intval($product["product_category"]);
    $productSystem = intval($product["product_system"]);
    $grade         = intval($product["grade"]);
    $gauge         = intval($product["gauge"]);
    $colorGroup    = intval($color_details['color_group']);

    $color_mult = fetchColorMultiplier($colorGroup, $productSystem, $grade, $gauge, $category_id);

    $totalPrice = 0;
    if ($product_id > 0) {
        $product      = getProductDetails($product_id);
        $basePrice    = floatval($product['unit_price']);
        $soldByFeet   = intval($product['sold_by_feet']);

        foreach ($quantities as $index => $qty) {
            if ($qty <= 0) continue;

            $feet = isset($lengthFeet[$index]) ? parseNumber($lengthFeet[$index]) : 0;
            $inch = isset($lengthInch[$index]) ? parseNumber($lengthInch[$index]) : 0;

            $panelType = $panelTypes[$index] ?? 'solid';
            $panelDripStop = $panelDripStops[$index] ?? '';

            $totalPrice += $qty * calculateUnitPrice(
                $basePrice,
                $feet,
                $inch,
                $panelType,
                $soldByFeet,
                $bends,
                $hems
            );
        }
    }

    $totalPrice *= $color_mult;

    echo number_format($totalPrice, 2);
}

if (isset($_POST['fetch_stock_coil'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $product_details = getProductDetails($id);

    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);
    $gauge = mysqli_real_escape_string($conn, $_POST['gauge']);

    $query_coil = "SELECT 1 FROM coil_product 
                   WHERE 
                       hidden = '0' AND
                       status = '1' AND
                       color_sold_as = '$color' AND
                       grade = '$grade' AND
                       gauge = '$gauge'
                   LIMIT 1";

    $result = mysqli_query($conn, $query_coil);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}