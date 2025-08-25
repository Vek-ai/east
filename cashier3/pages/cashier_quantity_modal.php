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
        <input type="hidden" id="is_pre_order" name="is_pre_order" value="0" />
        <div class="row">

            <!-- Colors -->
            <div class="col-3">
                <select class="form-control qty_select2" id="qty-color" name="color" >
                    <option value="" data-category="">All Colors</option>
                    <optgroup label="Product Colors">
                        <?php
                        $query_color = "SELECT MIN(color_id) AS color_id, color_name, product_category FROM paint_colors 
                                        WHERE hidden = '0' AND color_status = '1'
                                        GROUP BY color_name 
                                        ORDER BY color_name ASC";

                        $result_color = mysqli_query($conn, $query_color);
                        while ($row_color = mysqli_fetch_array($result_color)) {
                        ?>
                            <option value="<?= htmlspecialchars($row_color['color_id']) ?>" 
                                    data-category="<?= htmlspecialchars($row_color['product_category']) ?>">
                                <?= htmlspecialchars($row_color['color_name']) ?>
                            </option>
                        <?php } ?>
                    </optgroup>
                </select>
            </div>

            <!-- Grade -->
            <div class="col-3">
                <select class="form-control qty_select2" id="qty-grade" name="grade">
                    <option value="" data-category="">All Grades</option>
                    <optgroup label="Product Grades">
                        <?php
                        $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY product_grade ASC";
                        $result_grade = mysqli_query($conn, $query_grade);
                        while ($row_grade = mysqli_fetch_array($result_grade)) {
                        ?>
                            <option value="<?= htmlspecialchars($row_grade['product_grade_id']) ?>" 
                                    data-category="<?= htmlspecialchars($row_grade['product_category']) ?>">
                                <?= htmlspecialchars($row_grade['product_grade']) ?>
                            </option>
                        <?php } ?>
                    </optgroup>
                </select>
            </div>

            <!-- Gauge -->
            <div class="col-3">
                <select class="form-control qty_select2" id="qty-gauge" name="gauge">
                    <option value="" data-category="">All Gauges</option>
                    <optgroup label="Product Gauges">
                        <?php
                        $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1' ORDER BY product_gauge ASC";
                        $result_gauge = mysqli_query($conn, $query_gauge);
                        while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                        ?>
                            <option value="<?= htmlspecialchars($row_gauge['product_gauge_id']) ?>" 
                                    data-category="gauge">
                                <?= htmlspecialchars($row_gauge['product_gauge']) ?>
                            </option>
                        <?php } ?>
                    </optgroup>
                </select>
            </div>

            <div class="col-12">
                <h5 class="text-center pt-3 fs-4 fw-bold"><span id="coil-stock"></span></h5>
            </div>

            <div class="col-12"><hr class="w-100"></div>
            
            <div class="row">
                <div class="col-3">
                    <label class="fs-4 fw-semibold">Quantity</label>
                </div>
                <div class="col-9">
                    <label class="fs-4 fw-semibold">Length</label>
                </div>
            </div>
            
            <div class="quantity-length-container row mx-0">
                <div class="quantity-field <?= empty($sold_by_feet) ? 'col-12 col-md-6' : 'col-6 col-md-3'; ?> mb-1">
                    <input type="number" value="1" id="quantity-product" name="quantity_product[]" 
                        class="form-control form-control-sm mb-1 quantity-product" 
                        placeholder="Qty" list="quantity-product-list" autocomplete="off">
                </div>

                <div class="col-8 col-md-4 mb-1 <?= empty($sold_by_feet) ? 'd-none' : '';?> length-field">
                    <fieldset class="p-0 position-relative">
                        <div id="length-field" class="input-group d-flex align-items-center mb-1">
                            <input step="0.0001" class="form-control form-control-sm mr-1 length_feet" 
                                type="number" id="length_feet" name="length_feet[]" 
                                list="length_feet_datalist" 
                                value="<?= $values["estimate_length"] ?>" 
                                placeholder="FT" style="color:#ffffff; max-width:70px;">

                            <input step="0.0001" class="form-control form-control-sm mr-1 length_inch" 
                                type="number" id="length_inch" name="length_inch[]" 
                                list="length_inch_datalist" 
                                value="<?= $values["estimate_length_inch"]; ?>" 
                                placeholder="IN" style="color:#ffffff; max-width:70px;">

                            <input class="form-control form-control-sm fraction_input" 
                                type="number" step="0.01" id="length_fraction" name="length_fraction[]" 
                                placeholder="Fraction" style="color:#ffffff; max-width:80px;">
                        </div>
                    </fieldset>
                </div>
            </div>


            <?php
            if($category_id == $panel_id){
            ?>
            <div class="col-7 text-end">
                <a href="javascript:void(0)" type="button" id="duplicateFields" class="text-end">
                    <i class="fas fa-plus"></i>
                </a>
            </div>
            <?php 
            } 
            ?>
            <div class="mb-2 <?= (($category_id == $fastener_id) || $id == 21) ? '' : 'd-none';?>">
                <label class="fs-4 fw-bold" for="case_type">Select Case</label>
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
                        <label class="fs-4 fw-bold" for="stiff_stand_seam">Standing Seam Style</label>
                        <select class="form-control" id="stiff_stand_seam" name="stiff_stand_seam" style="color:#ffffff; width: 100%;">
                            <option value="1" selected>Striated</option>
                            <option value="2">Flat</option>
                            <option value="3">Minor Rib</option>
                        </select>
                    </div>
                </div>
                <div class="mb-2 <?= empty($board_batten) ? 'd-none' : '';?>">
                    <div class="me-2 flex-grow-1">
                        <label class="fs-4 fw-bold" for="stiff_board_batten">Board and Batten Style</label>
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
                <label class="fs-4 fw-semibold" for="quantity-product">Select Panel Type</label>
                <div class="row g-2 align-items-center">
                    <div class="col-2 ">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="solid_panel" name="panel_type" value="solid" checked>
                            <label class="form-check-label" for="solid_panel">Solid</label>
                        </div>
                    </div>
                    <div class="col-2 position-relative">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="vented_panel" name="panel_type" value="vented">
                            <label class="form-check-label" for="vented_panel">Vented</label>
                        </div>
                        <div id="tooltip" class="tooltip-custom small" style="display: none;">
                            Double-click to select Vented
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="drip_stop_panel" name="panel_drip_stop" value="drip_stop">
                            <label class="form-check-label" for="drip_stop_panel">Drip Stop</label>
                        </div>
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

        <div class="modal-footer d-flex justify-content-end align-items-center px-0">
            <div class="d-flex justify-content-end">
                <button class="btn btn-success ripple btn-secondary" type="submit">Add to Cart</button>
            </div>
        </div>
        <script>
        $(document).ready(function () {
            function calculateProductCost() {
                const quantities = [];
                const lengthFeetArr = [];
                const lengthInchArr = [];
                
                $('.quantity-field input').each(function() {
                    quantities.push(parseInt($(this).val()) || 0);
                });
                
                $('.length-field #length_feet').each(function() {
                    lengthFeetArr.push(parseInt($(this).val()) || 0);
                });
                
                $('.length-field #length_inch').each(function() {
                    lengthInchArr.push(parseInt($(this).val()) || 0);
                });

                const panelType = $('input[name="panel_type"]:checked').val();
                const panelDripStop = $('input[name="panel_drip_stop"]:checked').val();
                const bends = parseInt($('#bend_product').val()) || 0;
                const hems = parseInt($('#hem_product').val()) || 0;
                const soldByFeet = <?= $sold_by_feet; ?>;

                <?php 
                $basePrice = floatval($product_details['unit_price'] ?? 0);
                if($product_details['sold_by_feet'] == '1'){
                    $basePrice = $basePrice / floatval($product_details['length'] ?? 1);
                }
                ?>

                const basePrice = <?= $basePrice; ?>;

                $.ajax({
                    url: 'pages/cashier_quantity_modal.php',
                    method: 'POST',
                    data: {
                        quantity: quantities,
                        lengthFeet: lengthFeetArr,
                        lengthInch: lengthInchArr,
                        panelType: panelType,
                        panel_drip_stop: panelDripStop,
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

            $(document).on('change', '#qty-color, #qty-grade, #qty-gauge', function() {
                fetchCoilStock();
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

            $(document).on("keydown", ".fraction_input", function(e) {
                if (e.key === "Tab" && !e.shiftKey) {
                    e.preventDefault();

                    let $currentRow = $(this).closest(".quantity-length-container");
                    let $nextRow = $currentRow.next(".quantity-length-container");

                    if ($nextRow.length) {
                        $nextRow.find(".quantity-product").focus().select();
                    } else {
                        $(".quantity-length-container").first().find(".quantity-product").focus().select();
                    }
                }
            });

            $('#duplicateFields').click(function() {
                var $newRow = $('.quantity-length-container').first().clone();
                $newRow.find("input").val("");
                $('.quantity-length-container').last().after($newRow);
            });

            $(document).on('change input', '.quantity-product, .length_feet, .length_inch, input[name="panel_type"], #bend_product, #hem_product', calculateProductCost);

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

if (isset($_POST['fetch_price'])) {
    $quantities = isset($_POST['quantity']) ? $_POST['quantity'] : [];
    $lengthFeet = isset($_POST['lengthFeet']) ? $_POST['lengthFeet'] : [];
    $lengthInch = isset($_POST['lengthInch']) ? $_POST['lengthInch'] : [];
    $panelType = isset($_POST['panelType']) ? $_POST['panelType'] : '';
    $soldByFeet = isset($_POST['soldByFeet']) ? intval($_POST['soldByFeet']) : 0;
    $bends = isset($_POST['bends']) ? intval($_POST['bends']) : 0;
    $hems = isset($_POST['hems']) ? intval($_POST['hems']) : 0;
    $basePrice = isset($_POST['basePrice']) ? floatval($_POST['basePrice']) : 0;

    $totalPrice = 0;

    foreach ($quantities as $index => $quantity) {
        $length_feet = isset($lengthFeet[$index]) ? intval($lengthFeet[$index]) : 0;
        $length_inch = isset($lengthInch[$index]) ? intval($lengthInch[$index]) : 0;

        $totalPrice += $quantity * calculateUnitPrice($basePrice, $length_feet, $length_inch, $panelType, $soldByFeet, $bends, $hems);
    }

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