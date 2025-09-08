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
        <input type="hidden" id="product_id" name="id" value="<?= $id ?>" />
        <input type="hidden" id="trim_unit_price" name="price" value="<?= $product_details['unit_price'] ?>" />
        <input type="hidden" id="custom_multiplier_trim" name="custom_multiplier" value="<?= $custom_multiplier ?>" />
        <input type="hidden" id="is_pre_order" name="is_pre_order" value="0" />
        <input type="hidden" id="is_custom_trim" name="is_custom" value="0" />
        <input type="hidden" id="category_id" name="category_id" value="<?= $category_id ?>" />

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
                        $query_color = "SELECT MIN(color_id) AS color_id, color_name, product_category FROM paint_colors 
                                        WHERE hidden = '0' AND color_status = '1' $category_condition
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
            <div class="col-4">
                <select class="form-control trim_select2" id="trim-grade" name="grade">
                    <option value="" data-category="">All Grades</option>
                    <optgroup label="Product Grades">
                        <?php
                        $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' $category_condition ORDER BY product_grade ASC";
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
            <div class="col-4">
                <select class="form-control trim_select2" id="trim-gauge" name="gauge">
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
                <h5 class="text-center pt-3 fs-5 fw-bold"><span id="coil-stock"></span></h5>
            </div>

            <div class="col-12"><hr class="w-100"></div>

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
                    <select class="form-control mb-0 trim_length_select">
                        <option value="0" selected>Select Length</option>
                        <?php
                        $lengths = getInventoryLengths($id);
                        foreach ($lengths as $entry) {
                            $product_length = htmlspecialchars($entry['length']);
                            $length_in_feet = htmlspecialchars($entry['feet']);
                            $selected = ($length_in_feet == 1.0) ? 'selected' : '';
                            echo "<option value=\"$length_in_feet\" $selected>$product_length</option>";
                        }
                        ?>
                    </select>
                    <input type="hidden" name="length[]" class="form-control mb-0 trim_length">
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
                        id="trim_draw"
                        data-drawing='<?= $drawing_str ?>'
                        type="button"
                    >                        
                        Modify Trim
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
                        url: 'pages/cashier_trim_modal.php',
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

                for (let i = 0; i < 4; i++) {
                    duplicateTrimRow();
                }
            });
        </script>
<?php
}

if (isset($_POST['fetch_stock_coil'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $product_details = getProductDetails($id);

    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);
    $gauge = mysqli_real_escape_string($conn, $_POST['gauge']);

    $query_product = "
        SELECT 
            p.product_id,
            COALESCE(SUM(i.quantity_ttl), 0) AS total_quantity
        FROM 
            product AS p
        LEFT JOIN 
            inventory AS i ON p.product_id = i.product_id
        WHERE 
            p.product_category = '3' AND 
            i.color_id = '$color' AND
            p.grade = '$grade' AND
            p.gauge = '$gauge'
        GROUP BY p.product_id
        HAVING total_quantity > 0
        LIMIT 1
    ";

    $result_product = mysqli_query($conn, $query_product);

    if (mysqli_num_rows($result_product) > 0) {
        echo json_encode(['success' => true]);
        exit;
    }

    $query_coil = "
        SELECT 1 FROM coil_product 
        WHERE 
            hidden = '0' AND
            status = '1' AND
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
