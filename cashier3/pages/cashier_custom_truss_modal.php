<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_POST['fetch_modal'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $product_details = getProductDetails($id);
    $category_id = $product_details['product_category'];
?>
    <input type="hidden" id="product_id" name="product_id" value="<?= $id ?>" />

    <div class="row">
        <div class="col-6">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label">Material</label>
                <a href="/?page=truss_material" target="_blank" class="text-decoration-none">Edit</a>
            </div>
            <div class="mb-3">
                <select id="truss_material" class="form-select truss_select2" name="truss_material">
                    <option value="">Select Truss Material...</option>
                    <?php
                    $query = "SELECT * FROM truss_material WHERE status = 1 ORDER BY `truss_material` ASC";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_array($result)) {
                        ?>
                        <option value="<?= $row['truss_material_id']; ?>"><?= $row['truss_material']; ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-6">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label">Type</label>
                <a href="/?page=truss_type" target="_blank" class="text-decoration-none">Edit</a>
            </div>
            <div class="mb-3">
                <select id="truss_type" class="form-select truss_select2" name="truss_type">
                    <option value="">Select Truss Type...</option>
                    <?php
                    $query = "SELECT * FROM truss_type WHERE status = 1 ORDER BY `truss_type` ASC";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_array($result)) {
                        ?>
                        <option value="<?= $row['truss_type_id']; ?>"><?= $row['truss_type']; ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-6">
            <div class="mb-3">
                <label for="size" class="form-label">Size</label>
                <input type="text" id="size" name="size" class="form-control" placeholder="Enter Size" />
            </div>
        </div>

        <div class="col-6">
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label mb-0">Overhangs</label>
                <a href="/?page=truss_overhang" target="_blank" class="text-decoration-none small">Edit</a>
                </div>
                <div class="d-flex gap-2">
                <select id="truss_left_overhang" class="form-select truss_select2" name="truss_left_overhang">
                    <option value="" selected>No Left Overhang...</option>
                    <?php
                    $query = "SELECT * FROM truss_overhang WHERE status = 1 ORDER BY `truss_overhang` ASC";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_array($result)) {
                    echo '<option value="' . $row['truss_overhang_id'] . '">' . $row['truss_overhang'] . '</option>';
                    }
                    ?>
                </select>

                <select id="truss_right_overhang" class="form-select truss_select2" name="truss_right_overhang">
                    <option value="" selected>No Right Overhang...</option>
                    <?php
                    mysqli_data_seek($result, 0);
                    while ($row = mysqli_fetch_array($result)) {
                    echo '<option value="' . $row['truss_overhang_id'] . '">' . $row['truss_overhang'] . '</option>';
                    }
                    ?>
                </select>
                </div>
            </div>
        </div>

        <div class="col-4">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label">Ceiling Load</label>
                <a href="/?page=truss_ceiling_load" target="_blank" class="text-decoration-none">Edit</a>
            </div>
            <div class="mb-3">
                <select id="truss_ceiling_load" class="form-select truss_select2" name="truss_ceiling_load">
                    <option value="">Select Truss Ceiling Load...</option>
                    <?php
                    $query = "SELECT * FROM truss_ceiling_load WHERE status = 1 ORDER BY `truss_ceiling_load` ASC";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_array($result)) {
                        ?>
                        <option value="<?= $row['truss_ceiling_load_id']; ?>"><?= $row['truss_ceiling_load']; ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-4">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label">Pitch</label>
                <a href="/?page=truss_pitch" target="_blank" class="text-decoration-none">Edit</a>
            </div>
            <div class="mb-3">
                <select id="truss_pitch" class="form-control truss_select2" name="truss_pitch">
                    <option value="">Select Truss Pitch...</option>
                    <?php
                    $query = "SELECT * FROM truss_pitch WHERE status = 1 ORDER BY `truss_pitch` ASC";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_array($result)) {
                        ?>
                        <option value="<?= $row['truss_pitch_id']; ?>">(<?= $row['numerator'] ?>/<?= $row['denominator'] ?>)<?= $row['truss_pitch']; ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-4">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label">Spacing</label>
                <a href="/?page=truss_spacing" target="_blank" class="text-decoration-none">Edit</a>
            </div>
            <div class="mb-3">
                <select id="truss_spacing" class="form-control truss_select2" name="truss_spacing">
                    <option value="">Select Truss Ceiling Load...</option>
                    <?php
                    $query = "SELECT * FROM truss_spacing WHERE status = 1 ORDER BY `truss_spacing` ASC";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_array($result)) {
                        ?>
                        <option value="<?= $row['truss_spacing_id']; ?>"><?= $row['truss_spacing']; ?> <?= $row['unit_of_measure']; ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="col-6">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label ">Top Pitch</label>
                <a href="/?page=truss_pitch" target="_blank" class="text-decoration-none">Edit</a>
            </div>
            <div class="mb-3">
                <select id="truss_top_pitch" class="form-select truss_select2" name="truss_top_pitch">
                    <option value="">Select Top Pitch...</option>
                    <?php
                    $query = "SELECT * FROM truss_pitch WHERE status = 1 ORDER BY `truss_pitch` ASC";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_array($result)) {
                        ?>
                        <option value="<?= $row['truss_pitch_id']; ?>">(<?= $row['numerator'] ?>/<?= $row['denominator'] ?>)<?= $row['truss_pitch']; ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-6">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label ">Bottom Pitch</label>
                <a href="/?page=truss_pitch" target="_blank" class="text-decoration-none">Edit</a>
            </div>
            <div class="mb-3">
                <select id="truss_bottom_pitch" class="form-select truss_select2" name="truss_bottom_pitch">
                    <option value="">Select Bottom Pitch...</option>
                    <?php
                    $query = "SELECT * FROM truss_pitch WHERE status = 1 ORDER BY `truss_pitch` ASC";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_array($result)) {
                        ?>
                        <option value="<?= $row['truss_pitch_id']; ?>">(<?= $row['numerator'] ?>/<?= $row['denominator'] ?>)<?= $row['truss_pitch']; ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        
        <div class="col-4">
            <div class="mb-3">
                <label class="form-label" for="quantity-product">Quantity</label>
                <input type="number" value="1" id="truss_quantity" name="quantity_product[]" class="form-control mb-1" placeholder="Enter Quantity">
            </div>
        </div>
        <div class="col-4">
            <div class="mb-3">
                <label class="form-label" for="cost">Cost</label>
                <input type="number" id="truss_cost" name="cost" class="form-control mb-1" placeholder="Enter Cost">
            </div>
        </div>
        <div class="col-4">
            <div class="mb-3">
                <label class="form-label" for="price">Price</label>
                <input type="number" id="truss_price" name="price" class="form-control mb-1" placeholder="Enter Price">
            </div>
        </div>
    </div>

    <div class="modal-footer d-flex justify-content-end align-items-center px-0">
        <div class="d-flex justify-content-end">
            <button class="btn btn-success ripple btn-secondary" type="submit">Add to Cart</button>
        </div>
    </div>
<?php
}