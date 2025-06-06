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
        ?>
        <input type="hidden" id="product_id" name="product_id" value="<?= $id ?>" />
        <input type="hidden" id="category_id" name="category_id" value="<?= $category_id ?>" />
        <div class="quantity_input">
            <div class="mb-2">
                <label class="fs-5 fw-bold" for="quantity-product">Quantity</label>
                <input id="quantity-product" name="quantity_product" class="form-control" placeholder="Enter Quantity" list="quantity-product-list" autocomplete="off">
            </div>
        </div>
        <div class="length_input">
            <div class="mb-2 <?= empty($sold_by_feet) ? 'd-none' : '';?>">
                <fieldset class="p-0 position-relative">
                    <legend class="fs-5 fw-bold">Length</legend>
                    <div class="input-group d-flex align-items-center">
                        <input class="form-control mr-1" type="number" id="length_feet" name="length_feet"  list="length_feet_datalist" value="<?= $values["estimate_length"] ?>" placeholder="FT" size="5" style="color:#ffffff;">
                        <input class="form-control" type="number" id="length_inch" name="length_inch" list="length_inch_datalist" value="<?= $values["estimate_length_inch"]; ?>" placeholder="IN" size="5" style="color:#ffffff;">
                    </div>
                </fieldset>
            </div>
            <div class="mb-2 <?= (($category_id == $fastener_id) || $id == 21) ? '' : 'd-none';?>">
                <label class="fs-5 fw-bold" for="case_type">Select Case</label>
                <div class="input-group d-flex align-items-center">
                    <select class="form-control mr-1" id="case_type" name="case_type" style="color:#ffffff;">
                        <option>100</option>
                        <option>250</option>
                        <option>500</option>
                        <option>1000</option>
                    </select>
                </div>
            </div>
            <div class="input-group d-flex align-items-center justify-content-between flex-wrap w-100 mt-3">
                <div class="mb-2 <?= empty($standing_seam) ? 'd-none' : '';?>">
                    <div class="me-2 flex-grow-1">
                        <label class="fs-5 fw-bold" for="stiff_stand_seam">Standing Seam Style</label>
                        <select class="form-control" id="stiff_stand_seam" name="stiff_stand_seam" style="color:#ffffff; width: 100%;">
                            <option value="1" selected>Striated</option>
                            <option value="2">Flat</option>
                            <option value="3">Minor Rib</option>
                        </select>
                    </div>
                </div>
                <div class="mb-2 <?= empty($board_batten) ? 'd-none' : '';?>">
                    <div class="me-2 flex-grow-1">
                        <label class="fs-5 fw-bold" for="stiff_board_batten">Board and Batten Style</label>
                        <select class="form-control" id="stiff_board_batten" name="stiff_board_batten" style="color:#ffffff; width: 100%;">
                            <option value="1" selected>Flat</option>
                            <option value="2">Minor Rib</option>
                        </select>
                    </div>
                </div>
            </div> 
        </div>
        <div class="panel_options">
            <div class="mb-2 <?= ($category_id == $panel_id) ? '' : 'd-none'; ?>">
                <label class="fs-5 fw-bold" for="quantity-product">Select Panel Type</label>
                <div class="input-group d-flex align-items-center position-relative">
                    <div class="form-control mr-1">
                        <input type="checkbox" id="solid_panel" name="panel_type" value="solid"> Solid
                    </div>
                    <div class="form-control mr-1 position-relative">
                        <input type="checkbox" id="vented_panel" name="panel_type" value="vented"> Vented
                        <div id="tooltip" class="tooltip-custom" style="display: none;">Double-click to select Vented</div>
                    </div>
                    <div class="form-control">
                        <input type="checkbox" id="drip_stop_panel" name="panel_drip_stop" value="drip_stop"> Drip Stop
                    </div>
                </div>
            </div>
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
        <script>
        $(document).ready(function () {
            function calculateProductCost() {
                const quantity = parseInt($('#quantity-product').val()) || 0;
                const lengthFeet = parseInt($('#length_feet').val()) || 0;
                const lengthInch = parseInt($('#length_inch').val()) || 0;
                const totalLength = lengthFeet + lengthInch / 12;
                const panelType = $('input[name="panel_type"]:checked').val();
                const panel_drip_stop = $('input[name="panel_drip_stop"]:checked').val();
                const bends = parseInt($('#bend_product').val()) || 0;
                const hems = parseInt($('#hem_product').val()) || 0;
                const soldByFeet = <?= $sold_by_feet; ?>;
                const basePrice = <?= $product_details["unit_price"]; ?>;

                $.ajax({
                    url: 'pages/cashier_quantity_modal.php',
                    method: 'POST',
                    data: {
                        quantity: quantity,
                        lengthFeet: lengthFeet,
                        lengthInch: lengthInch,
                        panelType: panelType,
                        panel_drip_stop: panel_drip_stop,
                        soldByFeet: soldByFeet,
                        bends: bends,
                        hems: hems,
                        basePrice: basePrice,
                        fetch_price: 'fetch_price'
                    },
                    success: function(response) {
                        $('#product-cost').text(response);
                    }
                });
            }

            // Recalculate product cost on input or changes
            $('#quantity-product, #length_feet, #length_inch, input[name="panel_type"], #bend_product, #hem_product').change(function() {
                calculateProductCost();
            });

            $('#quantity-product, #length_feet, #length_inch').on('input', calculateProductCost);
            $('input[name="panel_type"]').on('change', calculateProductCost);

            $('#solid_panel').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#vented_panel').prop('checked', false);
                }
                calculateProductCost();
            });

            $('#vented_panel').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#solid_panel').prop('checked', false);
                }
                calculateProductCost();
            });

            $('#vented_panel').on('click', function (e) {
                if (!$(this).data('clicked')) {
                    e.preventDefault();
                    $('#tooltip').fadeIn(200);

                    $(this).data('clicked', true);

                    setTimeout(() => {
                        $('#tooltip').fadeOut(200);
                        $(this).data('clicked', false);
                    }, 2000);
                }
            });

            $('#vented_panel').on('dblclick', function () {
                $(this).prop('checked', true);
                $('#tooltip').fadeOut(200);
                calculateProductCost();
            });

            $('#solid_panel').on('click', function () {
                $('#tooltip').fadeOut(200);
                $('#vented_panel').data('clicked', false);
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

if(isset($_POST['fetch_price'])){
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $lengthFeet = isset($_POST['lengthFeet']) ? intval($_POST['lengthFeet']) : 0;
    $lengthInch = isset($_POST['lengthInch']) ? intval($_POST['lengthInch']) : 0;
    $panelType = isset($_POST['panelType']) ? $_POST['panelType'] : '';
    $soldByFeet = isset($_POST['soldByFeet']) ? intval($_POST['soldByFeet']) : 0;
    $bends = isset($_POST['bends']) ? intval($_POST['bends']) : 0;
    $hems = isset($_POST['hems']) ? intval($_POST['hems']) : 0;
    $basePrice = isset($_POST['basePrice']) ? floatval($_POST['basePrice']) : 0;

    $totalPrice = calculateUnitPrice($basePrice, $lengthFeet, $lengthInch, $panelType, $soldByFeet, $bends, $hems);

    echo number_format($totalPrice, 2);
}