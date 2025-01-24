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
        $color = mysqli_real_escape_string($conn, $_POST['color']);
        $product_category = mysqli_real_escape_string($conn, $_POST['product_category']);
        $color_multiplier = mysqli_real_escape_string($conn, $_POST['color_multiplier']);
        $availability = mysqli_real_escape_string($conn, $_POST['availability']);
        $coating = mysqli_real_escape_string($conn, $_POST['coating']);
        $surface = mysqli_real_escape_string($conn, $_POST['surface']);
        $gauge = mysqli_real_escape_string($conn, $_POST['gauge']);
        $grade = mysqli_real_escape_string($conn, $_POST['grade']);
    
        $checkQuery = "SELECT * FROM product_color WHERE id = '$id'";
        $result = mysqli_query($conn, $checkQuery);
        $isInsert = false;
    
        if (mysqli_num_rows($result) > 0) {
            $isInsert = false;
    
        } else {
           
            $isInsert = true;
            
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
        }
        ?>
            <div class="card">
                <div class="card-body">
                    <input type="hidden" id="id" name="id" class="form-control"  value="<?= $id ?>"/>

                    <div class="row pt-3">
                        <div class="col-md-6 opt_field_update" data-id="7">
                            <label class="form-label">Color</label>
                            <div class="mb-3">
                                <select id="color" class="form-control select2-edit" name="color">
                                    <option value="" >Select Color...</option>
                                    <?php
                                    $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0'";
                                    $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                                    while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                        $selected = ($color == $row_paint_colors['color_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_paint_colors['color_id'] ?>" <?= $selected ?>><?= $row_paint_colors['color_name'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row pt-3">
                        <div class="col-md-4">
                            <label class="form-label">Product Category</label>
                            <div class="mb-3">
                                <select id="product_category_update" class="form-control select2-edit" name="product_category">
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
                        <div class="col-md-4">
                            <label class="form-label">Color Multiplier</label>
                            <div class="mb-3">
                                <select class="form-control select2-edit" id="color_multiplier" name="color_multiplier">
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
                        <div class="col-md-4">
                            <label class="form-label">Availability</label>
                            <div class="mb-3">
                                <select id="availability" class="form-control select2-edit" name="availability">
                                    <option value="">Select Availability...</option>
                                    <option value="Stock" <?= $availability == 'Stock' ? 'selected' : '' ?>>Stock</option>
                                    <option value="Special Order" <?= $availability == 'Special Order' ? 'selected' : '' ?>>Special Order</option>
                                    <option value="One-Time" <?= $availability == 'One-Time' ? 'selected' : '' ?>>One-Time</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row pt-3">
                        <div class="col-md-3">
                            <label class="form-label">Coating</label>
                            <div class="mb-3">
                                <select id="coating" class="form-control select2-edit" name="coating">
                                    <option value="">Select Coating...</option>
                                    <option value="Bare" <?= $coating == 'Bare' ? 'selected' : '' ?>>Bare</option>
                                    <option value="Painted" <?= $coating == 'Painted' ? 'selected' : '' ?>>Painted</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Surface</label>
                            <div class="mb-3">
                                <select id="surface" class="form-control select2-edit" name="surface">
                                    <option value="">Select Coating...</option>
                                    <option value="Smooth" <?= $surface == 'Smooth' ? 'selected' : '' ?>>Smooth</option>
                                    <option value="Textured" <?= $surface == 'Textured' ? 'selected' : '' ?>>Textured</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Gauge</label>
                            <div class="mb-3">
                                <select class="form-control select2-edit" id="gauge" name="gauge">
                                    <option value="" >Select Gauge...</option>
                                    <?php
                                    $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0'";
                                    $result_gauge = mysqli_query($conn, $query_gauge);            
                                    while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                        $selected = ($gauge == $row_gauge['product_gauge_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_gauge['product_gauge_id'] ?>" <?= $selected ?>><?= $row_gauge['product_gauge'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Grade</label>
                            <div class="mb-3">
                                <select class="form-control select2-edit" id="grade" name="grade">
                                    <option value="">Select Grade...</option>
                                    <?php
                                    $query_grade = "SELECT * FROM product_grade WHERE hidden = '0'";
                                    $result_grade = mysqli_query($conn, $query_grade);            
                                    while ($row_grade = mysqli_fetch_array($result_grade)) {
                                        $selected = ($grade == $row_grade['product_grade_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_grade['product_grade_id'] ?>" <?= $selected ?>><?= $row_grade['product_grade'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </select>
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
