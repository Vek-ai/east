<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

$table = 'test';
//4 = TRIM
$trim_id = 4;
$category_id = 4;

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
        
        
        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product Color Mapping</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <?php
                    $color_group_selected = (array) json_decode($row['color_group'] ?? '[]', true);
                    ?>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">Available Color Groups</label>
                                <a href="?page=product_color" target="_blank" class="text-decoration-none">Edit</a>
                            </div>
                            <select id="color" class="form-control calculate select2" name="color_group[]" multiple>
                                <option value="">Select Color Group...</option>
                                <?php
                                $query_groups = "SELECT DISTINCT color_group_name_id, color_group_name 
                                                FROM color_group_name 
                                                ORDER BY color_group_name ASC";
                                $result_groups = mysqli_query($conn, $query_groups);
                                while ($row_group = mysqli_fetch_array($result_groups)) {
                                    $selected = in_array($row_group['color_group_name_id'], $color_group_selected) ? 'selected' : '';
                                    ?>
                                    <option value="<?= $row_group['color_group_name_id'] ?>" <?= $selected ?>>
                                        <?= $row_group['color_group_name'] ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <?php
                    $color_paint_selected = (array) json_decode($row['color_paint'] ?? '[]', true);
                    ?>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">Available Colors</label>
                                <a href="?page=paint_colors" target="_blank" class="text-decoration-none">Edit</a>
                            </div>
                            <select id="color_paint" class="form-control add-category calculate color-group-filter select2" name="color_paint[]" multiple>
                                <option value="">Select Color...</option>
                                <?php
                                $query_color = "SELECT * FROM paint_colors 
                                                WHERE hidden = '0' 
                                                AND color_status = '1'
                                                ORDER BY `color_name` ASC";
                                $result_color = mysqli_query($conn, $query_color);
                                while ($row_color = mysqli_fetch_array($result_color)) {
                                    $selected = in_array($row_color['color_id'], $color_paint_selected) ? 'selected' : '';
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
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product Information</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Product Description</label>
                            <input type="text" id="product_item" name="product_item" class="form-control" value="<?= $row['product_item']?>" />
                        </div>
                    </div>
                    <div class="col-md-4"></div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">Warranty Type</label>
                                <a href="?page=product_warranty_type" target="_blank" class="text-decoration-none">Edit</a>
                            </div>
                            <?php
                            $warranty_selected = (array) json_decode($row['warranty_type'] ?? '[]', true);
                            ?>
                            <select id="warranty_type" class="form-control select2" name="warranty_type[]" multiple>
                                <option value="">Select Warranty Type...</option>
                                <?php
                                $query_product_warranty_type = "SELECT * FROM product_warranty_type WHERE hidden = '0' AND status = '1'";
                                $result_product_warranty_type = mysqli_query($conn, $query_product_warranty_type);            
                                while ($row_product_warranty_type = mysqli_fetch_array($result_product_warranty_type)) {
                                    $selected = in_array($row_product_warranty_type['product_warranty_type_id'], $warranty_selected) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_product_warranty_type['product_warranty_type_id'] ?>" <?= $selected ?>>
                                        <?= $row_product_warranty_type['product_warranty_type'] ?>
                                    </option>
                                <?php   
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Manufactured or Purchased</label>
                        <select id="product_origin" class="form-control" name="product_origin">
                            <option value="" <?= empty($row['product_origin']) ? 'selected' : '' ?>>Select One...</option>
                            <option value="1" <?= $row['product_origin'] == '1' ? 'selected' : '' ?>>Purchased</option>
                            <option value="2" <?= $row['product_origin'] == '2' ? 'selected' : '' ?>>Manufactured</option>
                        </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Unit of Measure</label>
                            <select id="unit_of_measure" class="form-control" name="unit_of_measure">
                                <option value="ft" <?= $row['unit_of_measure'] == 'ft' ? 'selected' : '' ?>>Ft</option>
                                <option value="each" <?= empty($row['unit_of_measure']) || $row['unit_of_measure'] == 'each' ? 'selected' : '' ?>>Each</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card-body p-0">
                            <h4 class="card-title text-center">Product Image</h4>
                            <p action="#" id="myUpdateDropzone" class="dropzone">
                                <div class="fallback">
                                <input type="file" id="picture_path_update" name="picture_path[]" class="form-control" style="display: none" multiple/>
                                </div>
                            </p>
                        </div>
                    </div>

                    <?php
                    $query_img = "SELECT * FROM product_images WHERE productid = '$product_id'";
                    $result_img = mysqli_query($conn, $query_img);
                    if (mysqli_num_rows($result_img) > 0) { ?>
                        <div class="col-md-12">
                            <h5>Current Images</h5>
                            <div class="row pt-3">
                                <?php while ($row_img = mysqli_fetch_array($result_img)) { 
                                    $image_id = $row_img['prodimgid'];
                                    ?>
                                    <div class="col-md-2 position-relative">
                                        <div class="mb-3">
                                            <img src="<?= $row_img['image_url'] ?>" class="img-fluid" alt="Product Image" />
                                            <button class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image-btn" data-image-id="<?= $image_id ?>">X</button>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>

                    <script>
                        window.uploadedUpdateFiles = window.uploadedUpdateFiles || [];
                        $('#myUpdateDropzone').dropzone({
                            addRemoveLinks: true,
                            dictRemoveFile: "X",
                            init: function() {
                                this.on("addedfile", function(file) {
                                    uploadedUpdateFiles.push(file);
                                    updateFileInput2();
                                });

                                this.on("removedfile", function(file) {
                                    uploadedUpdateFiles = uploadedUpdateFiles.filter(f => f.name !== file.name);
                                    updateFileInput2();
                                });
                            }
                        });
                        function updateFileInput2() {
                            const fileInput = document.getElementById('picture_path_update');
                            const dataTransfer = new DataTransfer();
                            uploadedUpdateFiles.forEach(file => {
                                const fileBlob = new Blob([file], { type: file.type });
                                dataTransfer.items.add(new File([fileBlob], file.name, { type: file.type }));
                            });
                            fileInput.files = dataTransfer.files;
                        }
                    </script>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product Pricing</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        <label for="is_custom_length" class="form-label d-block">Sold with custom length?</label>
                        <input type="checkbox" class="form-check-input" id="is_custom_length" name="is_custom_length" value="1"
                            <?= (empty($row['is_custom_length']) || $row['is_custom_length'] == 1) ? 'checked' : '' ?>>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label">Available Lengths</label>
                            <a href="?page=dimensions" target="_blank" class="text-decoration-none">Edit</a>
                        </div>
                        <div class="mb-3">
                            <?php
                            $selected_lengths = (array) json_decode($row['available_lengths'] ?? '[]', true);
                            ?>
                            <select id="available_lengths" name="available_lengths[]" class="select2 form-control" multiple="multiple">
                                <optgroup label="Select Available Lengths">
                                    <?php
                                    $trim_id = 4;
                                    $sql = "SELECT dimension_id, dimension, dimension_unit 
                                            FROM dimensions 
                                            WHERE dimension_category = $trim_id 
                                            ORDER BY dimension ASC";
                                    $result = $conn->query($sql);

                                    if ($result && $result->num_rows > 0) {
                                        while ($row_dim = $result->fetch_assoc()) {
                                            $dimension_id = $row_dim['dimension_id'];
                                            $dimension    = $row_dim['dimension'];
                                            $unit         = $row_dim['dimension_unit'];

                                            $selected = in_array($dimension_id, $selected_lengths) ? 'selected' : '';

                                            echo '<option value="' . $dimension_id . '" ' . $selected . '>'
                                                . $dimension . ' ' . $unit . '</option>';
                                        }
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <?php $unit_price = floatval($row['unit_price']) ?? 0; ?>
                        <div class="mb-3">
                            <label class="form-label">Retail Price</label>
                            <input type="text" id="retail" name="unit_price" class="form-control" value="<?=number_format($unit_price ?? 0,3)?>"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Inventory Tracking</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">InvID</label>
                            <input type="text" id="inv_id" name="inv_id" class="form-control" value="<?= $row['inv_id']?>" />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Coil/Part No.</label>
                            <input type="text" id="coil_part_no" name="coil_part_no" class="form-control" value="<?= $row['coil_part_no']?>" />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Product SKU</label>
                            <input type="text" id="product_sku" name="product_sku" class="form-control" value="<?= $row['product_sku']?>" />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">UPC</label>
                        <input type="text" id="upc" name="upc" class="form-control" value="<?= !empty($row['upc']) ? $row['upc'] : generateRandomUPC(); ?>" />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Reorder Level</label>
                            <input type="number" id="reorder_level" name="reorder_level" class="form-control" step="0.01" value="<?= $row['reorder_level']?>" />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3 d-none">
                            <label class="form-label">Usage</label>
                            <select id="product_usage" class="form-control" name="product_usage">
                                <option value="" >Select Product Usage...</option>
                                <?php
                                $query_usage = "SELECT * FROM component_usage";
                                $result_usage = mysqli_query($conn, $query_usage);            
                                while ($row_usage = mysqli_fetch_array($result_usage)) {
                                    $selected = ($row['product_usage'] == $row_usage['usageid']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_usage['usageid'] ?>" <?= $selected ?>><?= $row_usage['usage_name'] ?></option>
                                <?php   
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4 screw-fields">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label">Supplier</label>
                            <a href="?page=product_supplier" target="_blank" class="text-decoration-none">Edit</a>
                        </div>
                        <div class="mb-3">
                            <?php
                            $supplier_selected = (array) json_decode($row['supplier_id'] ?? '[]', true);
                            ?>
                            <select id="supplier_id" class="form-control select2 inventory_supplier" name="supplier_id[]" multiple>
                                <option value="">Select Supplier...</option>
                                <optgroup label="Supplier">
                                    <?php
                                    $query_supplier = "SELECT * FROM supplier WHERE status = 1 ORDER BY `supplier_name` ASC";
                                    $result_supplier = mysqli_query($conn, $query_supplier);            
                                    while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                                        $selected = in_array($row_supplier['supplier_id'], $supplier_selected) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_supplier['supplier_id'] ?>" <?= $selected ?>><?= $row_supplier['supplier_name'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Correlated Products</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <select id="correlatedProducts" name="correlatedProducts[]" class="select2 form-control" multiple="multiple">
                                <optgroup label="Select Correlated Products">
                                    <?php
                                    $correlated_product_ids = [];
                                    $product_id = mysqli_real_escape_string($conn, $row['product_id']);
                                    $query_correlated = "SELECT correlated_id FROM correlated_product WHERE main_correlated_product_id = '$product_id'";
                                    $result_correlated = mysqli_query($conn, $query_correlated);
                                    
                                    while ($row_correlated = mysqli_fetch_assoc($result_correlated)) {
                                        $correlated_product_ids[] = $row_correlated['correlated_id'];
                                    }
                                    
                                    $query_products = "SELECT * FROM product";
                                    $result_products = mysqli_query($conn, $query_products);            
                                    while ($row_products = mysqli_fetch_array($result_products)) {
                                        $selected = in_array($row_products['product_id'], $correlated_product_ids) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_products['product_id'] ?>" <?= $selected ?> ><?= $row_products['description'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product Notes</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <textarea class="form-control" id="comment" name="comment" rows="5"><?= $row['comment']?></textarea>
                        </div>
                    </div>
                </div>
            </div>
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
