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
    $type_details = getProductTypeDetails($product_details['product_type']);
    ?>
    <?php
        if (!empty($product_details)) {
            $category_id = $product_details['product_category'];
        ?>
        <input type="hidden" id="product_id" name="id" value="<?= $id ?>" />
        <input type="hidden" id="product_price" value="<?= $product_details['unit_price'] ?>" />
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

            <div class="col-12"><hr class="w-100"></div>
            
            <div class="col-6">
                <div class="mb-3">
                    <label class="form-label" for="trim_quantity">Quantity</label>
                    <input type="number" id="trim_qty" name="quantity" class="form-control mb-1" value="<?= $quantity ?>" placeholder="Enter Quantity">
                </div>
            </div>
            <div class="col-6">
                <label class="form-label" for="trim_length">Length</label>
                <div class="mb-3">
                    <select id="trim_length" name="length" class="form-select mb-1 trim_select2">
                        <option value="10" <?= $length == 10 ? 'selected' : '' ?>>10 ft</option>
                        <option value="12" <?= $length == 12 ? 'selected' : '' ?>>12 ft</option>
                        <option value="14" <?= $length == 14 ? 'selected' : '' ?>>14 ft</option>
                        <option value="16" <?= $length == 16 ? 'selected' : '' ?>>16 ft</option>
                        <option value="18" <?= $length == 18 ? 'selected' : '' ?>>18 ft</option>
                        <option value="20" <?= $length == 20 ? 'selected' : '' ?>>20 ft</option>
                    </select>
                </div>
            </div>
            <div class="col-12">
                <div class="mb-3">
                    <label class="form-label" for="truss_price">Price</label>
                    <input type="text" id="trim_price" name="price" class="form-control mb-1" value="<?= $price ?>" placeholder="Enter Price">
                </div>
            </div>
        </div>
        <div class="modal-footer d-flex justify-content-between align-items-center px-0">
            <button class="btn btn-warning ripple btn-secondary" id="trim_draw" type="button">Modify Trim</button>
            <button class="btn btn-success ripple btn-secondary" type="submit">Add to Cart</button>
        </div>
        <?php
        }else{
        ?>
        <h5 class="text-center">Product Not Found!</h5>
        <?php
        }
        ?>
<?php
}