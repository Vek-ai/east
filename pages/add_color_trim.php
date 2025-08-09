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
            <div class="col-md-6 panel-fields" data-id="7">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label">Color Group</label>
                    <a href="?page=product_color" target="_blank" class="text-decoration-none">Edit</a>
                </div>
                <div class="mb-3">
                    <select id="color" class="form-control calculate" name="color">
                        <option value="">Select Color Group...</option>
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
                <label class="form-label">Color Multiplier Value</label>
                <input type="text" class="form-control" name="multiplier" id="multiplier" value="<?=$row['multiplier'] ?? ''?>">
            </div>
            <div class="col-md-6 panel-fields" data-id="7">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label">Coating</label>
                    <a href="?page=product_coating" target="_blank" class="text-decoration-none">Edit</a>
                </div>
                <div class="mb-3">
                    <select id="coating" class="form-control calculate" name="coating">
                        <option value="">Select Coating...</option>
                        <?php
                        $query_product_coating = "SELECT * FROM product_coating WHERE hidden = '0' AND status = '1' ORDER BY product_coating";
                        $result_product_coating = mysqli_query($conn, $query_product_coating);
                        while ($row_product_coating = mysqli_fetch_array($result_product_coating)) {
                            $selected = (($row['coating'] ?? '') == $row_product_coating['product_coating_id']) ? 'selected' : '';
                        ?>
                            <option value="<?= $row_product_coating['product_coating_id'] ?>" <?= $selected ?>><?= $row_product_coating['product_coating'] ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6 mb-3 panel-fields" data-id="7">
                <label class="form-label">Coating Multiplier Value</label>
                <input type="text" class="form-control" name="coating_multiplier" id="coating_multiplier" value="<?=$row['coating_multiplier'] ?? ''?>">
            </div>
        <?php
        
    } 
    
    mysqli_close($conn);
}
?>
