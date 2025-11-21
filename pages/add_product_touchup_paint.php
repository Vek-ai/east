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
                <h5 class="mb-0 fw-bold">Product Identifier</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Product Category</label>
                        <div class="mb-3">
                        <select id="product_category" class="form-control" name="product_category">
                            <option value="" >Select One...</option>
                            <?php
                            $query_roles = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                            $result_roles = mysqli_query($conn, $query_roles);            
                            while ($row_product_category = mysqli_fetch_array($result_roles)) {
                            ?>
                                <option value="<?= $row_product_category['product_category_id'] ?>" 
                                        data-category="<?= $row_product_category['product_category'] ?>"
                                        data-filename="<?= $row_product_category['product_filename'] ?>"
                                >
                                            <?= $row_product_category['product_category'] ?>
                                </option>
                            <?php   
                            }
                            ?>
                        </select>
                        </div>
                    </div>

                    <?php $selected_line = (array) json_decode($row['product_line'] ?? '[]', true); ?>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label">Product Line</label>
                            <a href="?page=product_line" target="_blank" class="text-decoration-none">Edit</a>
                        </div>
                        <div class="mb-3">
                        <select id="product_line" class="form-control calculate add-category select2" name="product_line" multiple>
                            <option value="" >Select Line...</option>
                            <?php
                            $query_roles = "SELECT * FROM product_line WHERE hidden = '0' AND status = '1' ORDER BY `product_line` ASC";
                            $result_roles = mysqli_query($conn, $query_roles);            
                            while ($row_product_line = mysqli_fetch_array($result_roles)) {
                                $selected = in_array($row_product_line['product_line_id'], $selected_line) ? 'selected' : '';
                            ?>
                                <option value="<?= $row_product_line['product_line_id'] ?>" data-category="<?= $row_product_line['product_category'] ?>" <?= $selected ?>><?= $row_product_line['product_line'] ?></option>
                            <?php   
                            }
                            ?>
                        </select>
                        </div>
                    </div>

                    <?php $selected_product_type = (array) json_decode($row['product_type'] ?? '[]', true); ?>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label">Product Type</label>
                            <a href="?page=product_type" target="_blank" class="text-decoration-none">Edit</a>
                        </div>
                        <div class="mb-3">
                            <select id="product_type" class="form-control add-category calculate select2" name="product_type[]" multiple>
                                <option value="" >Select Type...</option>
                                <?php
                                $query_roles = "SELECT * FROM product_type WHERE hidden = '0' AND status = '1' ORDER BY `product_type` ASC";
                                $result_roles = mysqli_query($conn, $query_roles);            
                                while ($row_product_type = mysqli_fetch_array($result_roles)) {
                                    $selected = in_array($row_product_type['product_type_id'], $selected_product_type) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_product_type['product_type_id'] ?>" data-category="<?= $row_product_type['product_category'] ?>" <?= $selected ?>><?= $row_product_type['product_type'] ?></option>
                                <?php   
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <?php 
                    $has_color = floatval($row['has_color'] ?? 0);
                    ?>
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="has_color" <?= ($has_color > 0) ? 'checked' : '' ?> name="has_color">
                            <label class="form-check-label fw-bold" for="has_color">
                                Product has color?
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product Color Mapping</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <?php
                    $assigned_colors = getAssignedProductColors($product_id);
                    $assigned_colors_list = !empty($assigned_colors) ? implode(',', array_map('intval', $assigned_colors)) : '0';

                    $query_color = "
                        SELECT DISTINCT * FROM paint_colors
                        WHERE (hidden = '0' AND color_status = '1' AND color_group REGEXP '^[0-9]+$')
                        OR color_id IN ($assigned_colors_list)
                        ORDER BY `color_name` ASC
                    ";

                    $result_color = mysqli_query($conn, $query_color);
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
                                while ($row_color = mysqli_fetch_assoc($result_color)) {
                                    $color_id = intval($row_color['color_id']);
                                    $selected = in_array($color_id, $assigned_colors) ? 'selected' : '';
                                    $availability_details = getAvailabilityDetails($row_color['stock_availability']);
                                    $multiplier = floatval($availability_details['multiplier'] ?? 1);

                                    echo '<option value="'.$color_id.'" 
                                            data-group="'.htmlspecialchars($row_color['color_group']).'" 
                                            data-category="'.htmlspecialchars($row_color['product_category']).'" 
                                            data-stock-multiplier="'.$multiplier.'" 
                                            '.$selected.'>'.htmlspecialchars($row_color['color_name']).'</option>';
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
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Abbreviation</label>
                            <input type="text" id="abbreviation" name="abbreviation" class="form-control" value="<?= $row['abbreviation']?>" />
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
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Weight per Item</label>
                            <input type="number" step="0.001" id="weight" name="weight" class="form-control" value="<?= $row['weight']?>" />
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
                    <div class="col-md-4">
                        <?php $unit_price = floatval($row['unit_price']) ?? 0; ?>
                        <div class="mb-3">
                            <label class="form-label">Retail Price</label>
                            <input type="text" id="retail" name="unit_price" class="form-control" value="<?=number_format($unit_price ?? 0,3)?>"/>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <?php $floor_price = floatval($row['floor_price']) ?? 0; ?>
                        <div class="mb-3">
                            <label class="form-label">Floor Price</label>
                            <input type="text" id="floor_price" name="floor_price" class="form-control" value="<?=number_format($floor_price ?? 0,3)?>"/>
                        </div>
                    </div>
                    <div class="col-md-4"></div>
                    <?php 
                    $bulk_price = floatval($row['bulk_price'] ?? 0);
                    $bulk_starts_at = floatval($row['bulk_starts_at'] ?? 0);
                    ?>
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="enable_bulk_pricing" <?= ($bulk_price > 0 || $bulk_starts_at > 0) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="enable_bulk_pricing">
                                Bulk Pricing
                            </label>
                        </div>
                    </div>

                    <div id="bulk_pricing_fields" class="row align-items-end <?= ($bulk_price > 0) ? '' : 'd-none' ?>">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold mb-1">Bulk Price</label>
                            <input type="number" class="form-control" id="bulk_price" name="bulk_price" step="0.0001" placeholder="Enter bulk price" value="<?= $bulk_price ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold mb-1">Bulk Pricing Starts At</label>
                            <input type="number" class="form-control" id="bulk_starts_at" name="bulk_starts_at" placeholder="Enter quantity threshold" value="<?= $bulk_starts_at ?>">
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

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Product SKU</label>
                            <input type="text" id="product_sku" name="product_sku" class="form-control" value="<?= $row['product_sku']?>" />
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

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product IDs</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <h4 id="product_ids_abbrev">
                                <?php
                                $productIDsString = fetchProductIDs($product_id);
                                $items = array_filter(array_map('trim', explode(',', $productIDsString)));
                                ?>

                                <?php if (!empty($items)): ?>
                                    <ul style="
                                        display: grid;
                                        grid-template-columns: repeat(3, 1fr);
                                        gap: 5px;
                                        padding: 0 20px;
                                        list-style-position: inside;
                                    ">
                                        <?php foreach ($items as $id): ?>
                                            <li><?= htmlspecialchars($id) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p>No product IDs found.</p>
                                <?php endif; ?>
                            </h4>
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
