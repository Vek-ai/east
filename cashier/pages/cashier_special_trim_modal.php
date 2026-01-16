<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';
$drawingData = [
    "points" => [
        ["x" => 885.3585815429688, "y" => 153.90420532226562],
        ["x" => 789.3585815429688, "y" => 153.90420532226562],
        ["x" => 721.4763305490602, "y" => 86.02195432835707],
        ["x" => 289.4763305490602, "y" => 86.02195432835713],
        ["x" => 289.47633054906026, "y" => 590.0219543283571],
        ["x" => 248.178827131785, "y" => 649.0009015171645],
    ],
    "lengths" => [null, 1.2085129176894314, 1.0319983053617332, 5.1250547278197365, 7.859613673694906, 0.7664282548647123],
    "angles" => [],
    "colors" => ["#000000", "#000000", "#000000", "#000000", "#000000", "#000000"],
    "lineTypes" => [null, "open", "normal", "normal", "normal", "open", "normal", "normal"],
    "arrows" => [],
    "images" => [],
];
$jsonDrawing = json_encode($drawingData, JSON_UNESCAPED_UNICODE);
$drawing_str = htmlspecialchars($jsonDrawing, ENT_QUOTES, 'UTF-8');
$trim_id = 4;

if(isset($_POST['fetch_modal'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $product_details = getProductDetails($id);
    $type_details = getProductTypeDetails($product_details['product_type']);
    $custom_multiplier = getCustomMultiplier($product_details['product_category']);
    ?>
    <?php
        if (!empty($product_details)) {
            $category_id = $product_details['product_category'];
        ?>
        <input type="hidden" id="trim_product_id" name="id" value="<?= $id ?>" />
        <input type="hidden" id="trim_unit_price" name="price" value="<?= $product_details['unit_price'] ?>" />
        <input type="hidden" id="custom_multiplier_trim" name="custom_multiplier" value="<?= $custom_multiplier ?>" />
        <input type="hidden" id="is_pre_order" name="is_pre_order" value="0" />
        <input type="hidden" id="is_custom_trim" name="is_custom" value="0" />
        <input type="hidden" id="category_id" name="category_id" value="<?= $category_id ?>" />
        <input type="hidden" id="trim_no" name="trim_no" value="" />

        <h5 class="text-center"><?= $product_details['description'] ?></h5>
        <div class="d-flex justify-content-center flex-column align-items-center mb-3">
            <!-- Canvas Wrapper with Relative Position -->
            <div id="drawingTrimContainer" class="d-flex justify-content-center align-items-center border border-dashed">
                <img id="drawingTrimImage"
                    src=""
                    alt="Drawing"
                    class="img-fluid h-100 w-auto"
                    style="display: none;">

                <input type="hidden" name="img_src" id="img_trim_src" value="">
            </div>
        </div>
        <div class="row">
            <!-- Colors -->
            <div class="col-4">
                <select class="form-control trim_select2" id="trim-color" name="color">
                    <option value="" data-category="">All Colors</option>
                    <optgroup label="Product Colors">
                        <?php
                        $assigned_colors = getAssignedProductColors($id);
                        if (!empty($assigned_colors)) {
                            $color_ids_str = implode(',', array_map('intval', $assigned_colors));
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
                            ?>
                                <option value="<?= htmlspecialchars($row['color_id']) ?>" 
                                        data-category="<?= htmlspecialchars($row['product_category']) ?>">
                                        <?= htmlspecialchars($row['color_name']) ?>
                                    </option>
                            <?php } 
                        }
                        ?>
                    </optgroup>
                </select>
            </div>

            <!-- Grade -->
            <div class="col-4">
                <select class="form-control trim_select2" id="trim-grade" name="grade">
                    <option value="" data-category="">All Grades</option>
                    <optgroup label="Product Grades">
                        <?php
                        $assignedGrades = getAssignedProductGrades($id);
                        $assignedGrades = array_map('intval', $assignedGrades);

                        if (!empty($assignedGrades)) {
                            $ids = implode(',', $assignedGrades);
                            $query_grade = "SELECT * FROM product_grade 
                                            WHERE hidden = '0' 
                                            AND status = '1' 
                                            AND product_grade_id IN ($ids)
                                            ORDER BY product_grade ASC";

                            $result_grade = mysqli_query($conn, $query_grade);
                            if ($result_grade) {
                                while ($row_grade = mysqli_fetch_assoc($result_grade)) {
                                    $gradeId = (int)$row_grade['product_grade_id'];
                                    $category = htmlspecialchars($row_grade['product_category'], ENT_QUOTES);
                                    $gradeName = htmlspecialchars($row_grade['product_grade'], ENT_QUOTES);

                                    echo "<option value=\"$gradeId\" data-category=\"$category\">$gradeName</option>";
                                }
                            }
                        }
                        ?>
                    </optgroup>
                </select>
            </div>

            <!-- Gauge -->
            <div class="col-4">
                <select class="form-control trim_select2" id="trim-gauge" name="gauge">
                    <option value="" data-category="">All Gauges</option>
                    <optgroup label="Product Gauges">
                        <?php
                        $assignedGauges = getAssignedProductGauges($id);
                        $assignedGauges = array_map('intval', $assignedGauges);

                        if (!empty($assignedGauges)) {
                            $ids = implode(',', $assignedGauges);
                            $query_gauge = "SELECT * FROM product_gauge 
                                            WHERE hidden = '0' 
                                            AND status = '1' 
                                            AND product_gauge_id IN ($ids) 
                                            ORDER BY product_gauge ASC";

                            $result_gauge = mysqli_query($conn, $query_gauge);
                            if ($result_gauge) {
                                while ($row_gauge = mysqli_fetch_assoc($result_gauge)) {
                                    $gaugeId = (int)$row_gauge['product_gauge_id'];
                                    $gaugeName = htmlspecialchars($row_gauge['product_gauge'], ENT_QUOTES);

                                    echo "<option value=\"$gaugeId\" data-category=\"gauge\">$gaugeName</option>";
                                }
                            }
                        }
                        ?>
                    </optgroup>
                </select>
            </div>


            <div class="col-12">
                <h5 class="text-center pt-3 fs-5 fw-bold"><span id="coil-stock"></span></h5>
            </div>

            <div class="col-12"><hr class="w-100"></div>

            <div class="row justify-content-center mb-3">
                <div class="col-3 text-center">
                    <label class="fs-4 fw-semibold">Flat Sheet Width</label>
                    <input type="number" step="0.01" name="width" class="form-control mb-1 trim-width" placeholder="Enter Flat Sheet Width">
                </div>
                <div class="col-3 text-center">
                    <label class="fs-4 fw-semibold">Total Hems</label>
                    <input type="number" step="0.01" name="hem" class="form-control mb-1 trim-hem" placeholder="Enter Hems">
                </div>
                <div class="col-3 text-center">
                    <label class="fs-4 fw-semibold">Total Bends</label>
                    <input type="number" step="0.01" name="bend" class="form-control mb-1 trim-bend" placeholder="Enter Bends">
                </div>
            </div>

            <div class="row justify-content-center mb-3">
                <div class="col-9 text-center">
                    <label class="fs-4 fw-semibold">Special Trim Description</label>
                    <input type="text" id="spec_trim_desc" name="description" class="form-control mb-1" placeholder="Enter Description">
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-3 text-center">
                    <label class="fs-4 fw-semibold">Quantity</label>
                </div>
                <div class="col-3 text-center">
                    <label class="fs-4 fw-semibold">Length</label>
                </div>
                <div class="col-3 notes-col text-center d-none">
                    <label class="fs-4 fw-semibold">Notes</label>
                </div>
            </div>

            <div class="quantity-length-row row justify-content-center mb-1">
                <div class="col-3 col-6-md">
                    <input type="number" name="quantity[]" 
                        class="form-control mb-0 trim_qty" 
                        value="" placeholder="Enter Quantity">
                </div>

                <div class="col-3 col-6-md">
                    <select class="form-control mb-0 trim_length_select" name="length[]">
                        <option value="" selected>Select Length</option>
                        <?php
                        $lengths = getProductAvailableLengths($id);
                        foreach ($lengths as $entry) {
                            $product_length = htmlspecialchars($entry['length']);
                            $length_in_feet = htmlspecialchars($entry['feet']);
                            $dimension_id   = htmlspecialchars($entry['dimension_id']);
                            $selected = ($length_in_feet == 1.0) ? 'selected' : '';
                            echo "<option value=\"$length_in_feet\" data-id=\"$dimension_id\" $selected>$product_length</option>";
                        }
                        ?>
                    </select>
                    <input type="hidden" name="length_hidden[]" class="form-control mb-0 trim_length">
                </div>

                <div class="col-3 col-6-md notes-col d-none">
                    <input type="text" name="notes[]" class="form-control mb-1" placeholder="Enter Notes">
                </div>
            </div>


            <div class="col-9 text-end">
                <a href="javascript:void(0)" type="button" id="duplicateTrimFields" class="text-end" title="Add Another">
                    <i class="fas fa-plus"></i>
                </a>
            </div>

            <div class="col-12">
                <div class="product_cost_display">
                    <h5 class="text-center pt-3 fs-5 fw-bold">Product Cost: $<span id="trim_price"><?= number_format(0,2) ?></span></h5>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between align-items-center px-0">
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-secondary" id="toggleNotes">Add Notes</button>
                </div>
                <div class="d-flex justify-content-center">
                    <button
                        class="btn btn-warning ripple btn-secondary"
                        id="customer_special_trim_btn"
                        data-drawing='<?= $drawing_str ?>'
                        type="button"
                        >                        
                        Search Customer SPCL Trim
                    </button>                
                </div>
                <div class="d-flex justify-content-center d-none">
                    <button id="btnCustomChart" class="btn btn-primary ripple" type="button" data-category="<?= $category_id ?>">View Trim Profile</button>
                </div>
                <div class="d-flex justify-content-end">
                    <button id="saveDrawing" class="btn btn-success" type="submit">Save</button>
                </div>
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
            $(document).ready(function () {
                function updatePrice() {
                    const product_id = $('#trim_product_id').val();
                    const quantities = [];
                    const lengthFeetArr = [];

                    $('.quantity-length-row').each(function() {
                        const qty = parseInt($(this).find('.trim_qty').val()) || 0;
                        quantities.push(qty);

                        const lengthFeet = parseFloat($(this).find('.trim_length_select').val()) || 0;
                        lengthFeetArr.push(lengthFeet);
                    });

                    const color = parseInt($('#trim-color').val()) || 0;
                    const grade = parseInt($('#trim-grade').val()) || 0;
                    const gauge = parseInt($('#trim-gauge').val()) || 0;

                    const width = parseFloat($('.trim-width').val()) || 0;
                    const hem = parseFloat($('.trim-hem').val()) || 0;
                    const bend = parseFloat($('.trim-bend').val()) || 0;

                    console.log("Width: " +width);
                    console.log("Hem: " +hem);
                    console.log("Bend: " +bend);

                    $.ajax({
                        url: 'pages/cashier_special_trim_modal.php',
                        method: 'POST',
                        data: {
                            product_id: product_id,
                            quantity: quantities,
                            lengthFeet: lengthFeetArr,
                            color: color,
                            grade: grade,
                            gauge: gauge,
                            width: width,
                            hem: hem,
                            bend: bend,
                            fetch_price: 'fetch_price'
                        },
                        success: function(response) {
                            $('#trim_price').text(response);
                        }
                    });
                }


                $(document).on('change', '.trim_length, .trim_qty, #trim-color, #trim-grade, #trim-gauge, .trim-width, .trim-bend, .trim-hem', function() {
                    updatePrice();
                });

                $(document).on('change', '.trim_length_select', function() {
                    var value = $(this).val();
                    $('.trim_length').val(value);
                    updatePrice();
                });

                function fetchCoilStock() {
                    const color = parseInt($('#trim-color').val()) || 0;
                    const grade = parseInt($('#trim-grade').val()) || 0;
                    const gauge = parseInt($('#trim-gauge').val()) || 0;

                    if (color === 0 || grade === 0 || gauge === 0) {
                        $('#coil-stock').text('');
                        $('#is_pre_order').val('0');
                        return;
                    }

                    $.ajax({
                        url: 'pages/cashier_special_trim_modal.php',
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

                $(document).on('change', '#trim-color, #trim-grade, #trim-gauge', function() {
                    fetchCoilStock();
                });

                $(document).on("keydown", ".trim-length-select", function(e) {
                    if (e.key === "Tab" && !e.shiftKey) {
                        e.preventDefault();

                        let $currentRow = $(this).closest(".quantity-length-row");
                        let $nextRow = $currentRow.next(".quantity-length-row");

                        if ($nextRow.length) {
                            $nextRow.find(".trim-qty").focus().select();
                        } else {
                            $(".quantity-length-row").first().find(".trim-qty").focus().select();
                        }
                    }
                });

                $(document).on("change", ".trim-length-select", function() {
                    let val = $(this).val();
                    $(this).closest(".quantity-length-row").find(".trim-length").val(val);
                });

                function duplicateTrimRow() {
                    let $newRow = $(".quantity-length-row").first().clone();

                    $newRow.find(".trim_qty").val("");
                    $newRow.find(".trim_length_select").prop("selectedIndex", 0);
                    $newRow.find(".trim_length").val("");

                    $(".quantity-length-row").last().after($newRow);
                }

                $('#duplicateTrimFields').on("click", function() {
                    duplicateTrimRow();
                });
            });
        </script>
<?php
}

if (isset($_POST['fetch_price'])) {
    global $conn;

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantities = $_POST['quantity'] ?? [];
    $lengthFeet = $_POST['lengthFeet'] ?? [];

    $color_id = intval($_POST['color'] ?? 0);
    $grade    = intval($_POST['grade'] ?? 0);
    $gauge    = intval($_POST['gauge'] ?? 0);
    $width    = floatval($_POST['width'] ?? 0);
    $hem      = floatval($_POST['hem'] ?? 0);
    $bend     = floatval($_POST['bend'] ?? 0);

    $totalPrice = 0;

    if ($product_id > 0) {
        $product = getProductDetails($product_id);
        $basePrice = floatval($product['unit_price'] ?? 0);
        $soldByFeet = intval($product['sold_by_feet'] ?? 1);

        $bulkData = getBulkData($product_id);
        $bulk_price = floatval($bulkData['bulk_price'] ?? 0);
        $bulk_starts_at = intval($bulkData['bulk_starts_at'] ?? 0);

        foreach ($quantities as $index => $qty) {
            $qty = floatval($qty ?? 0);
            if ($qty <= 0) continue;

            $feet = isset($lengthFeet[$index]) && $lengthFeet[$index] !== '' 
                ? floatval($lengthFeet[$index]) 
                : 0;

            if ($feet <= 0) continue;

            $unitPrice = ($bulk_price > 0 && $qty >= $bulk_starts_at)
                ? $bulk_price
                : $basePrice;

            $width = '';
            $profile_raw = $product['profile'] ?? '[]';
            $profile = json_decode($profile_raw, true);
            $profile = is_array($profile) ? array_map('intval', $profile) : [];

            $customer_id = $_SESSION['customer_id'];
            $customer_details = getCustomerDetails($customer_id);
            $customer_details_pricing = $customer_details['customer_pricing'];

            $customer_pricing_rate = getPricingCategory($trim_id, $customer_details_pricing, $product_id) / 100;

            $price += $qty * (1 - $customer_pricing_rate) * calculateUnitPrice(
                $unitPrice,
                $feet,
                '',
                '',
                $soldByFeet,
                $bend,
                $hem,
                $color_id,
                $grade,
                $gauge,
                $width,
                $category,
                $profile
            );

            $totalPrice += $price;
        }
    }

    echo number_format($totalPrice, 2);
    exit;
}

if (isset($_POST['fetch_stock_coil'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $product_details = getProductDetails($id);

    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);
    $gauge = mysqli_real_escape_string($conn, $_POST['gauge']);

    $query_coil = "
        SELECT 1 FROM coil_product 
        WHERE 
            color_sold_as = '$color' AND
            grade = '$grade' AND
            gauge = '$gauge'
        LIMIT 1
    ";

    $result_coil = mysqli_query($conn, $query_coil);

    if (mysqli_num_rows($result_coil) > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
