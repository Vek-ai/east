<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "fetch_edit_modal") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $checkQuery = "SELECT * FROM product_color WHERE id = '$id'";
        $result = mysqli_query($conn, $checkQuery);
        
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
        }
        ?>
            

            <div class="col-md-6 panel-fields">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label">Product System</label>
                    <a href="?page=product_system" target="_blank" class="text-decoration-none">Edit</a>
                </div>
                <div class="mb-3">
                <select id="product_system" class="form-control add-category calculate" name="product_system">
                    <option value="" >Select System...</option>
                    <?php
                    $query_system = "SELECT * FROM product_system WHERE hidden = '0' AND status = '1' ORDER BY product_system";
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
                        <label class="form-label">Gauge</label>
                        <a href="?page=product_gauge" target="_blank" class="text-decoration-none">Edit</a>
                    </div>
                    <select id="gauge" class="form-control calculate" name="gauge">
                        <option value="">Select Gauge...</option>
                        <?php
                        $query_gauge = "SELECT DISTINCT product_gauge, multiplier FROM product_gauge WHERE hidden = '0' AND status = '1'";
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
                    <label class="form-label">Product Grade</label>
                    <a href="?page=product_grade" target="_blank" class="text-decoration-none">Edit</a>
                </div>
                <div class="mb-3">
                <select id="product_grade" class="form-control add-category calculate" name="grade">
                    <option value="" >Select Grade...</option>
                    <?php
                    $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY product_grade";
                    $result_grade = mysqli_query($conn, $query_grade);
                    while ($row_grade = mysqli_fetch_array($result_grade)) {
                        $selected = (($row['product_grade'] ?? '') == $row_grade['product_grade_id']) ? 'selected' : '';
                    ?>
                        <option value="<?= $row_grade['product_grade_id'] ?>" data-category="<?= $row_grade['product_category'] ?>" <?= $selected ?>><?= $row_grade['product_grade'] ?></option>
                    <?php
                    }
                    ?>
                </select>
                </div>
            </div>
            <div class="col-md-6 panel-fields">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label">Width</label>
                    <a href="?page=product_color_width" target="_blank" class="text-decoration-none">Edit</a>
                </div>
                <div class="mb-3">
                    <select id="width" class="form-control calculate" name="width">
                        <option value="" >Select Width...</option>
                        <?php
                        $query_color_group = "SELECT * FROM product_color_width WHERE hidden = '0' AND status = '1' ORDER BY product_color_width";
                        $result_color_group = mysqli_query($conn, $query_color_group);
                        while ($row_color_group = mysqli_fetch_array($result_color_group)) {
                            $selected = (($row['width'] ?? '') == $row_color_group['product_color_width_id']) ? 'selected' : '';
                        ?>
                            <option value="<?= $row_color_group['product_color_width_id'] ?>" <?= $selected ?>><?= $row_color_group['product_color_width'] ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="col-md-6 panel-fields" data-id="7">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label">Color Group</label>
                    <a href="?page=product_color" target="_blank" class="text-decoration-none">Edit</a>
                </div>
                <div class="mb-3">
                    <select id="color" class="form-control calculate" name="color">
                        <option value="">Select Color...</option>
                        <?php
                        $query_color_group = "SELECT * FROM color_group_name WHERE hidden = '0' AND status = '1' ORDER BY color_group_name";
                        $result_color_group = mysqli_query($conn, $query_color_group);
                        while ($row_color_group = mysqli_fetch_array($result_color_group)) {
                            $selected = (($row['color'] ?? '') == $row_color_group['color_group_name_id']) ? 'selected' : '';
                        ?>
                            <option value="<?= $row_color_group['color_group_name_id'] ?>" <?= $selected ?>><?= $row_color_group['color_group_name'] ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6 mb-3 panel-fields" data-id="7">
                <label class="form-label">Multiplier Value</label>
                <input type="text" class="form-control" name="multiplier" id="multiplier" value="<?=$row['multiplier'] ?? ''?>">
            </div>
            <div class="col-md-6 mb-3 panel-fields" data-id="7">
                <label class="form-label">Price per SQ FT</label>
                <input type="text" class="form-control" name="price_per_sqft" id="price_per_sqft" value="<?=$row['price_per_sqft'] ?? ''?>">
            </div>
            <div class="col-md-6 mb-3 panel-fields" data-id="7">
                <label class="form-label">Calculated Markup</label>
                <input type="text" class="form-control" name="calculated_markup" id="calculated_markup" value="<?=$row['calculated_markup'] ?? ''?>">
            </div>
            <?php
        
    } 
    
    mysqli_close($conn);
}
?>
