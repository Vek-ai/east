<?php
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
                    <select id="color" class="form-control calculate" name="color">
                        <option value="">Select Color...</option>
                        <?php
                        $query_color_group = "SELECT * FROM color_group_name WHERE hidden = '0' ORDER BY color_group_name";
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
