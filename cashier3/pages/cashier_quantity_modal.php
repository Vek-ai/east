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
            $basePrice = $product_details["unit_price"];
            $product_system = $product_details["product_system"];
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

            <div class="row align-items-center mb-2">
                <div class="col-12 text-end">
                    <button type="button" id="createBundleBtn" class="btn btn-sm btn-primary">
                        Create Bundles
                    </button>
                </div>
            </div>
            
            <div class="row align-items-center mb-2">
                <div class="col-12" id="productFormCol">
                    <div class="row">
                        <div class="col-3 text-center">
                            <label class="fs-4 fw-semibold text-center">Quantity</label>
                        </div>
                        <div class="col-3">
                            <label class="fs-4 fw-semibold text-center">Length</label>
                        </div>
                        <div class="col-3">
                            <label class="fs-4 fw-semibold text-center">Panel Type</label>
                        </div>
                        <div class="col-3">
                            <label class="fs-4 fw-semibold text-center">Panel Style</label>
                        </div>
                    </div>

                    <div id="bundleGroups"></div>

                    <div id="unbundledRows">
                        <div class="quantity-length-container row mx-0 align-items-center mb-2">
                            <div class="col-1 text-center bundle-checkbox-wrapper d-none">
                                <input type="checkbox" class="bundle-checkbox">
                                <input type="hidden" name="bundle_name[]" value="">
                            </div>
                            <div class="col-2">
                                <input type="number" value="1" name="quantity_product[]" 
                                    class="form-control form-control-sm quantity-product" 
                                    placeholder="Qty" list="quantity-product-list" autocomplete="off">
                            </div>

                            <div class="col-3">
                                <div class="input-group">
                                    <input step="0.0001" class="form-control form-control-sm length_feet" 
                                        type="number" name="length_feet[]" list="length_feet_datalist" 
                                        value="<?= $values['estimate_length'] ?>" placeholder="FT">
                                    <input step="0.0001" class="form-control form-control-sm length_inch" 
                                        type="text" name="length_inch[]" list="length_inch_datalist" 
                                        value="<?= $values['estimate_length_inch'] ?>" placeholder="IN">
                                </div>
                            </div>

                            <div class="col-3">
                                <select id="panel_option" name="panel_option[]" class="form-control form-control-sm">
                                    <option value="solid" selected>Solid</option>
                                    <option value="vented">Vented</option>
                                    <option value="drip_stop">Drip Stop</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <select id="panel_style" name="panel_style[]" class="form-control form-control-sm panel_style">
                                    <?php if (!empty($standing_seam)): ?>
                                        <option value="striated" selected>Striated</option>
                                        <option value="flat">Flat</option>
                                        <option value="minor_rib">Minor Rib</option>
                                    <?php elseif (!empty($board_batten)): ?>
                                        <option value="flat" selected>Flat</option>
                                        <option value="minor_rib">Minor Rib</option>
                                    <?php else: ?>
                                        <option value="regular" selected>Regular</option>
                                        <option value="reversed">Reversed</option>
                                    <?php endif; ?>
                                </select>
                            </div>
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

                    <div class="col-auto backer-rod-container d-none">
                        <label class="fs-4 fw-semibold text-start me-2">Backer Rod (3/8in)</label><br>
                        <input type="number" step="0.001" name="backer_rod" 
                            class="form-control form-control-sm backer_rod d-inline-block" style="width:120px;">
                    </div>

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
                </div>
                <div class="col-3 d-none" id="bundleSection">
                    <div class="card p-3">
                        <h6 class="fw-bold">Add Bundle Info</h6>
                        <p class="mb-1">Bundle <span id="bundleCounter">1</span></p>
                        <div class="mb-2">
                            <label for="bundleName" class="form-label">Name of Bundle:</label>
                            <input type="text" id="bundleName" class="form-control form-control-sm" placeholder="Enter bundle name">
                        </div>
                        <button type="button" id="addToBundleBtn" class="btn btn-success btn-sm w-100">
                            Add to Bundles
                        </button>
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
            let bundleCount = 1;
            let bundleVisible = false;
            let product_system = <?= !empty($product_system) ? $product_system : 'null' ?>;
            var maxLength = 99999;

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

            $(document).off('click', '.removeBundleBtn').on('click', '.removeBundleBtn', function() {
                const $bundle = $(this).closest('.bundle-wrapper');
                $bundle.find('.quantity-length-container').appendTo('#unbundledRows');
                $bundle.remove();
            });

            function setupLayout() {
                $("label:contains('Panel Type')").closest(".col-3").removeClass("d-none");
                $("label:contains('Panel Style')").closest(".col-3").removeClass("d-none");
                $("select[name='panel_option[]']").closest(".col-3").removeClass("d-none");
                $("select[name='panel_style[]']").closest(".col-3").removeClass("d-none");

                $("label:contains('Quantity')").closest("div").removeClass("col-6").addClass("col-3");
                $("label:contains('Length')").closest("div").removeClass("col-6").addClass("col-3");

                $("input[name='quantity_product[]']").closest("div").removeClass("col-6 col-md-6").addClass("col-2 col-md-2");
                $(".length_feet").closest(".col-6, .col-12, .col-2, .col-3").removeClass("col-6 col-md-6 col-12 col-2 col-3").addClass("col-3 col-md-3");

                if ([11, 12].includes(product_system)) {
                    for (let i = 0; i < 10; i++) duplicateRow();
                    maxLength = 60;

                } else if ([13, 7].includes(product_system)) {
                    for (let i = 0; i < 10; i++) duplicateRow();
                    maxLength = 20;

                    $("label:contains('Panel Type')").closest(".col-3").addClass("d-none");
                    $("label:contains('Panel Style')").closest(".col-3").addClass("d-none");
                    $("select[name='panel_option[]']").closest(".col-3").addClass("d-none");
                    $("select[name='panel_style[]']").closest(".col-3").addClass("d-none");

                    $("label:contains('Quantity')").closest("div").removeClass("col-3").addClass("col-4");
                    $("label:contains('Length')").closest("div").removeClass("col-3").addClass("col-7 text-center");

                    $("input[name='quantity_product[]']").closest("div").removeClass("col-2 col-md-2 col-3 col-md-3").addClass("col-4 col-md-4");
                    $(".length_feet").closest(".col-3, .col-md-3").removeClass("col-3 col-md-3").addClass("col-7 col-md-7");

                } else if ([14, 15, 16, 5].includes(product_system)) {
                    let loopCount = (product_system == 5) ? 10 : 10;
                    for (let i = 0; i < loopCount; i++) duplicateRow();
                    maxLength = [14, 15, 16].includes(product_system) ? 60 : 20;

                    $(document).off('change', 'select[name="panel_style"]').on('change', 'select[name="panel_style"]', calculateBackerRod);
                    $(document).off('input', '.quantity-product, .length_feet, .length_inch').on('input', '.quantity-product, .length_feet, .length_inch', calculateBackerRod);

                } else {
                    for (let i = 0; i < 9; i++) duplicateRow();
                    maxLength = 60;
                }
            }

            setupLayout();

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