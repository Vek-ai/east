<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $id = intval($_POST['id']);
        $color_name = mysqli_real_escape_string($conn, $_POST['color_name']);
        $product_category = mysqli_real_escape_string($conn, $_POST['product_category']);
        $color_multiplier = mysqli_real_escape_string($conn, $_POST['color_multiplier']);
        $availability = mysqli_real_escape_string($conn, $_POST['availability']);
        $coating = mysqli_real_escape_string($conn, $_POST['coating']);
        $surface = mysqli_real_escape_string($conn, $_POST['surface']);
        $gauge = mysqli_real_escape_string($conn, $_POST['gauge']);
        $grade = mysqli_real_escape_string($conn, $_POST['grade']);

        $product_system = mysqli_real_escape_string($conn, $_POST['product_system']);
        $multiplier = mysqli_real_escape_string($conn, $_POST['multiplier']);
        $price_per_sqft = mysqli_real_escape_string($conn, $_POST['price_per_sqft']);
        $calculated_markup = mysqli_real_escape_string($conn, $_POST['calculated_markup']);
    
        $color_details = getColorDetails($color);
        //$color_name = $color_details['color_name'] ?? '';
        $color_code = $color_details['ekm_color_code'] ?? '';
        $color_no = $color_details['ekm_color_no'] ?? '';
        $paint_code = $color_details['ekm_paint_code'] ?? '';
        $system_mapping = ($grade == 2 ? 'e' : '') . $color_name;
    
        $checkQuery = "SELECT * FROM product_color WHERE id = '$id'";
        $result = mysqli_query($conn, $checkQuery);
        $isInsert = false;
    
        if (mysqli_num_rows($result) > 0) {
            $isInsert = false;
            $updateQuery = "UPDATE product_color SET 
                product_category = '$product_category', 
                color_name = '$color_name', 
                system_mapping = '$system_mapping', 
                color_mult_id = '$color_multiplier', 
                availability = '$availability', 
                color_code = '$color_code', 
                color_no = '$color_no', 
                coating = '$coating', 
                surface = '$surface', 
                grade = '$grade', 
                gauge = '$gauge', 
                product_system = '$product_system', 
                multiplier = '$multiplier', 
                price_per_sqft = '$price_per_sqft', 
                calculated_markup = '$calculated_markup', 
                paint_code = '$paint_code'
            WHERE id = '$id'";
    
            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating product: " . mysqli_error($conn);
            }
    
        } else {
            $isInsert = true;
            $insertQuery = "INSERT INTO product_color (
                product_category, 
                color_name,
                system_mapping, 
                color_mult_id, 
                availability, 
                color_code, 
                color_no, 
                coating, 
                surface, 
                grade, 
                gauge,
                product_system,
                multiplier,
                price_per_sqft,
                calculated_markup,
                paint_code) 
            VALUES (
                '$product_category',
                '$color_name',
                '$system_mapping', 
                '$color_multiplier', 
                '$availability', 
                '$color_code', 
                '$color_no', 
                '$paint_code', 
                '$surface', 
                '$grade', 
                '$gauge',
                '$product_system',
                '$multiplier',
                '$price_per_sqft',
                '$calculated_markup', 
                '$paint_code')";
    
            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error inserting product: " . mysqli_error($conn);
            }
        }
    }

    if ($action == "fetch_edit_modal") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $checkQuery = "SELECT * FROM product_color WHERE id = '$id'";
        $result = mysqli_query($conn, $checkQuery);

        $id = '';
        $product_category = '';
        $color = '';
        $color_mult_id = '';
        $availability = '';
        $coating = '';
        $surface = '';
        $grade = '';
        $gauge = '';
        
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $id = $row['id'];
            $product_category = $row['product_category'];
            $color = $row['color'];
            $color_mult_id = $row['color_mult_id'];
            $availability = $row['availability'];
            $coating = $row['coating'];
            $surface = $row['surface'];
            $grade = $row['grade'];
            $gauge = $row['gauge'];
            $price = $row['price'];
            $multiplier = '';
            $price_per_sqft = '';
            $calculated_markup = '';
        }
        ?>
            <div class="card">
                <div class="card-body">
                    <input type="hidden" id="id" name="id" class="form-control"  value="<?= $id ?>"/>

                    <div class="row pt-3">
                        <div class="col-md-12">
                            <label class="form-label">Product Category</label>
                            <div class="mb-3">
                                <select id="product_category" class="form-control" name="product_category">
                                    <option value="">Select Category...</option>
                                    <?php
                                    $query_roles = "SELECT * FROM product_category WHERE hidden = '0'";
                                    $result_roles = mysqli_query($conn, $query_roles);            
                                    while ($row_product_category = mysqli_fetch_array($result_roles)) {
                                        $selected = ($product_category == $row_product_category['product_category_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_product_category['product_category_id'] ?>" <?= $selected ?>><?= $row_product_category['product_category'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="add-fields" class="row <?= empty($row) ? 'd-none' : '' ?> pt-3">
                        <div class="col-md-6 panel-fields">
                            <label class="form-label">Product System</label>
                            <div class="mb-3">
                            <select id="product_system" class="form-control add-category calculate" name="product_system">
                                <option value="" >Select System...</option>
                                <?php
                                $query_system = "SELECT * FROM product_system WHERE hidden = '0' ORDER BY product_system";
                                $result_system = mysqli_query($conn, $query_system);
                                while ($row_system = mysqli_fetch_array($result_system)) {
                                    $selected = (($row['product_system'] ?? '') == $row_system['product_system_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_system['product_system_id'] ?>" data-category="<?= $row_system['product_category'] ?>" <?= $selected ?>><?= $row_system['product_system'] ?></option>
                                <?php
                                }
                                ?>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-6 panel-fields">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="form-label mb-1">Gauge</label>
                                </div>
                                <select id="gauge" class="form-control calculate" name="gauge">
                                    <option value="">Select Gauge...</option>
                                    <?php
                                    $query_gauge = "SELECT DISTINCT product_gauge, multiplier FROM product_gauge WHERE hidden = '0'";
                                    $result_gauge = mysqli_query($conn, $query_gauge);

                                    $existing_gauges = [];

                                    while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                        if (!in_array($row_gauge['product_gauge'], $existing_gauges)) {
                                            $existing_gauges[] = $row_gauge['product_gauge'];
                                            $selected = (($row['gauge'] ?? '') == $row_gauge['product_gauge']) ? 'selected' : '';
                                            ?>
                                            <option value="<?= htmlspecialchars($row_gauge['product_gauge']) ?>" 
                                                    data-multiplier="<?= htmlspecialchars($row_gauge['multiplier']) ?>" 
                                                    <?= $selected ?>>
                                                <?= htmlspecialchars($row_gauge['product_gauge']) ?>
                                            </option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>

                            </div>
                        </div>
                        <div class="col-md-6 panel-fields">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-1">Width</label>
                            </div>
                            <div class="mb-3">
                                <select id="width" class="form-control calculate" name="width">
                                    <option value="" >Select Width...</option>
                                    <option value="13.625" <?= ($row['width'] ?? '') == '13.625' ? 'selected' : '' ?>>13.625</option>
                                    <option value="20.5" <?= ($row['width'] ?? '') == '20.5' ? 'selected' : '' ?>>20.5</option>
                                    <option value="28" <?= ($row['width'] ?? '') == '28' ? 'selected' : '' ?>>28</option>
                                    <option value="41" <?= ($row['width'] ?? '') == '41' ? 'selected' : '' ?>>41</option>
                                    <option value="41.625" <?= ($row['width'] ?? '') == '41.625' ? 'selected' : '' ?>>41.625</option>
                                    <option value="43" <?= ($row['width'] ?? '') == '43' ? 'selected' : '' ?>>43</option>
                                    <option value="20" <?= ($row['width'] ?? '') == '20' ? 'selected' : '' ?>>20</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 panel-fields" data-id="7">
                            <label class="form-label">Color</label>
                            <div class="mb-3">
                                <select id="color" class="form-control calculate" name="color_name">
                                    <option value="">Select Color...</option>
                                    <option value="Bare" <?= (strtolower($row['color_name']) ?? '') == 'Bare' ? 'selected' : '' ?>>Bare</option>
                                    <option value="Acrylic" <?= (strtolower($row['color_name']) ?? '') == 'Acrylic' ? 'selected' : '' ?>>Acrylic</option>
                                    <option value="Standard" <?= (strtolower($row['color_name']) ?? '') == 'Standard' ? 'selected' : '' ?>>Standard</option>
                                    <option value="Premium" <?= (strtolower($row['color_name']) ?? '') == 'Premium' ? 'selected' : '' ?>>Premium</option>
                                    <option value="Textured" <?= (strtolower($row['color_name']) ?? '') == 'Textured' ? 'selected' : '' ?>>Textured</option>
                                    <option value="Metallic" <?= (strtolower($row['color_name']) ?? '') == 'Metallic' ? 'selected' : '' ?>>Metallic</option>
                                    <option value="Woodgrain" <?= (strtolower($row['color_name']) ?? '') == 'Woodgrain' ? 'selected' : '' ?>>Woodgrain</option>
                                    <option value="Embossed" <?= (strtolower($row['color_name']) ?? '') == 'Embossed' ? 'selected' : '' ?>>Embossed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3 panel-fields" data-id="7">
                            <label class="form-label">Multiplier Value</label>
                            <input type="text" class="form-control" name="multiplier" id="multiplier" value="<?=$multiplier?>">
                        </div>
                        <div class="col-md-6 mb-3 panel-fields" data-id="7">
                            <label class="form-label">Price per SQ FT</label>
                            <input type="text" class="form-control" name="price_per_sqft" id="price_per_sqft" value="<?=$price_per_sqft?>">
                        </div>
                        <div class="col-md-6 mb-3 panel-fields" data-id="7">
                            <label class="form-label">Calculated Markup</label>
                            <input type="text" class="form-control" name="calculated_markup" id="calculated_markup" value="<?=$calculated_markup?>">
                        </div>
                    </div>

                    <div class="card d-none">
                        <div class="card-body">
                            <h4 class="card-header">Additional Fields</h4>
                            <div class="row pt-3">
                                <div class="col-md-6">
                                        <label class="form-label">Color Multiplier Category</label>
                                        <div class="mb-3">
                                            <select class="form-control" id="color_multiplier" name="color_multiplier">
                                                <option value="" >Select Color Multiplier...</option>
                                                <?php
                                                $query_color_mult = "SELECT * FROM color_multiplier WHERE hidden = '0'";
                                                $result_color_mult = mysqli_query($conn, $query_color_mult);
                                                while ($row_color_mult = mysqli_fetch_array($result_color_mult)) {
                                                    $selected = ($color_mult_id == $row_color_mult['id']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= $row_color_mult['id'] ?>" <?= $selected ?>><?= $row_color_mult['color'] ?></option>
                                                <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Availability</label>
                                        <div class="mb-3">
                                            <select id="availability" class="form-control" name="availability">
                                                <option value="">Select Availability...</option>
                                                <option value="Stock" <?= $availability == 'Stock' ? 'selected' : '' ?>>Stock</option>
                                                <option value="Special Order" <?= $availability == 'Special Order' ? 'selected' : '' ?>>Special Order</option>
                                                <option value="One-Time" <?= $availability == 'One-Time' ? 'selected' : '' ?>>One-Time</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Coating</label>
                                        <div class="mb-3">
                                            <select id="coating" class="form-control" name="coating">
                                                <option value="">Select Coating...</option>
                                                <option value="Bare" <?= $coating == 'Bare' ? 'selected' : '' ?>>Bare</option>
                                                <option value="Painted" <?= $coating == 'Painted' ? 'selected' : '' ?>>Painted</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Surface</label>
                                        <div class="mb-3">
                                            <select id="surface" class="form-control" name="surface">
                                                <option value="">Select Coating...</option>
                                                <option value="Smooth" <?= $surface == 'Smooth' ? 'selected' : '' ?>>Smooth</option>
                                                <option value="Textured" <?= $surface == 'Textured' ? 'selected' : '' ?>>Textured</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Grade</label>
                                        <div class="mb-3">
                                            <select class="form-control" id="grade" name="grade">
                                                <option value="">Select Grade...</option>
                                                <option value="1" <?= $grade == '1' ? 'selected' : '' ?>>1</option>
                                                <option value="2" <?= $grade == '2' ? 'selected' : '' ?>>2</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3" data-id="7">
                                        <label class="form-label">Price</label>
                                        <input type="text" class="form-control" name="price" id="price" value="<?=$price?>">
                                    </div>
                            </div>
                        </div>
                    </div>

                    
                </div>
            </div>
            <?php
        
    } 
    
    mysqli_close($conn);
}
?>
