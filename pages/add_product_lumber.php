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

    if ($action == "fetch_product_modal") {
        $product_id = mysqli_real_escape_string($conn, $_POST['id']);
        $checkQuery = "SELECT * FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
        }

        ?>
        <div class="row pt-3">
            <div class="col-md-4 trim-field panel-fields">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label">Product System</label>
                    <a href="?page=product_system" target="_blank" class="text-decoration-none">Edit</a>
                </div>
                <div class="mb-3">
                <select id="product_system" class="form-control add-category calculate" name="product_system">
                    <option value="" >Select System...</option>
                    <?php
                    $query_system = "SELECT * FROM product_system WHERE hidden = '0' AND status = '1' ORDER BY `product_system` ASC";
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
            <div class="col-md-4 trim-field panel-fields">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label">Product Line</label>
                    <a href="?page=product_line" target="_blank" class="text-decoration-none">Edit</a>
                </div>
                <div class="mb-3">
                <select id="product_line" class="form-control add-category calculate" name="product_line">
                    <option value="" >Select Line...</option>
                    <?php
                    $query_roles = "SELECT * FROM product_line WHERE hidden = '0' AND status = '1' ORDER BY `product_line` ASC";
                    $result_roles = mysqli_query($conn, $query_roles);            
                    while ($row_product_line = mysqli_fetch_array($result_roles)) {
                        $selected = (($row['product_line'] ?? '') == $row_product_line['product_line_id']) ? 'selected' : '';
                    ?>
                        <option value="<?= $row_product_line['product_line_id'] ?>" data-category="<?= $row_product_line['product_category'] ?>" <?= $selected ?>><?= $row_product_line['product_line'] ?></option>
                    <?php   
                    }
                    ?>
                </select>
                </div>
            </div>
            <div class="col-md-4 trim-field screw-fields panel-fields">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label">Product Type</label>
                    <a href="?page=product_type" target="_blank" class="text-decoration-none">Edit</a>
                </div>
                <div class="mb-3">
                <select id="product_type" class="form-control add-category calculate" name="product_type">
                    <option value="" >Select Type...</option>
                    <?php
                    $query_roles = "SELECT * FROM product_type WHERE hidden = '0' AND status = '1' ORDER BY `product_type` ASC";
                    $result_roles = mysqli_query($conn, $query_roles);            
                    while ($row_product_type = mysqli_fetch_array($result_roles)) {
                        $selected = (($row['product_type'] ?? '') == $row_product_type['product_type_id']) ? 'selected' : '';
                    ?>
                        <option value="<?= $row_product_type['product_type_id'] ?>" data-category="<?= $row_product_type['product_category'] ?>" <?= $selected ?>><?= $row_product_type['product_type'] ?></option>
                    <?php   
                    }
                    ?>
                </select>
                </div>
            </div>
            <div class="col-md-4 panel-fields">
                <div class="mb-3">
                    <label class="form-label">Width</label>
                    <input type="text" id="width" name="width" class="form-control" value="<?=$row['width'] ?? ''?>"/>
                </div>
            </div>
            <div class="col-md-4 panel-fields">
                <div class="mb-3">
                    <label class="form-label">Length</label>
                    <input type="text" id="length" name="length" class="form-control" value="<?=$row['length'] ?? ''?>"/>
                </div>
            </div>
            <div class="col-md-4 panel-fields">
                <div class="mb-3">
                    <label class="form-label">Thickness</label>
                    <input type="text" id="thickness" name="thickness" class="form-control" value="<?=$row['thickness'] ?? ''?>"/>
                </div>
            </div>
            <div class="col-md-4 trim-field screw-fields panel-fields">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label">Color Group</label>
                        <a href="?page=product_color" target="_blank" class="text-decoration-none">Edit</a>
                    </div>
                    <select id="color" class="form-control add-category calculate" name="color">
                        <option value="" >Select Color Group...</option>
                        <?php
                        $query_colors = "SELECT
                                            pc.*,
                                            cgn.color_group_name AS color_name
                                        FROM
                                            product_color AS pc
                                        LEFT JOIN color_group_name AS cgn
                                        ON
                                            pc.color = cgn.color_group_name_id
                                        ORDER BY
                                            cgn.color_group_name ASC";
                        $result_colors = mysqli_query($conn, $query_colors);            
                        while ($row_colors = mysqli_fetch_array($result_colors)) {
                            $selected = (($row['color'] ?? '') == $row_colors['id']) ? 'selected' : '';
                        ?>
                            <option value="<?= $row_colors['id'] ?>" 
                                    data-price="<?=$row_colors['price'] ?>" 
                                    data-color="<?=$row_colors['color'] ?>" 
                                    data-system="<?=$row_colors['product_system'] ?>" 
                                    data-grade="<?=$row_colors['grade'] ?>" 
                                    data-gauge="<?=$row_colors['gauge'] ?>" 
                                    data-category="<?=trim($row_colors['product_category']) ?>" 
                                    data-multiplier="<?= $row_colors['multiplier'] ?>"
                                    <?= $selected ?>
                            >
                                        <?= getColorGroupName($row_colors['color']) ?>
                            </option>
                        <?php   
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4 trim-field screw-fields panel-fields">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label">Color</label>
                        <a href="?page=paint_colors" target="_blank" class="text-decoration-none">Edit</a>
                    </div>
                    <select id="color_paint" class="form-control calculate color-group-filter" name="color_paint">
                        <option value="" >Select Color...</option>
                        <?php
                        $query_color = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' AND color_group REGEXP '^[0-9]+$' ORDER BY `color_name` ASC";
                        $result_color = mysqli_query($conn, $query_color);
                        while ($row_color = mysqli_fetch_array($result_color)) {
                            $selected = ($row['color_paint'] == $row_color['color_id']) ? 'selected' : '';
                            $availability_details = getAvailabilityDetails($row_color['stock_availability']);
                            $multiplier = floatval($availability_details['multiplier'] ?? 1);
                        ?>
                            <option value="<?= $row_color['color_id'] ?>" 
                                    data-group="<?= $row_color['color_group'] ?>" 
                                    data-category="<?= $row_color['product_category'] ?>" 
                                    data-stock-multiplier="<?= $multiplier ?>" 
                                    <?= $selected ?>>
                                        <?= $row_color['color_name'] ?>
                            </option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4 trim-field">
                <div class="mb-3">
                    <label class="form-label">Cost</label>
                    <input type="text" id="cost" name="cost" class="form-control readonly" value="<?=$row['cost']?>"/>
                </div>
            </div>

            <div class="col-md-4 trim-field screw-fields">
                <div class="mb-3">
                    <label class="form-label">Retail Price</label>
                    <input type="text" id="retail" name="unit_price" class="form-control" value="<?=$row['unit_price'] ?? ''?>"/>
                </div>
            </div>

            <div class="col-md-12 trim-field screw-fields panel-fields">
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" id="description" name="description" class="form-control" value="<?=$row['description']?>"/>
                </div>
            </div>

            <?php include "add_product_common_fields.php"; ?>
        </div>
        <div class="modal-footer">
            <div class="form-actions">
                <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
        <?php
    }

    mysqli_close($conn);
}
?>
