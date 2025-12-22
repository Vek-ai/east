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
                        <select id="product_line" class="form-control calculate add-category select2" name="product_line">
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
                            <select id="product_type" class="form-control add-category calculate select2" name="product_type">
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

                    <?php $selected_profile = (array) json_decode($row['profile'] ?? '[]', true); ?>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">Product Profile</label>
                                <a href="?page=profile_type" target="_blank" class="text-decoration-none">Edit</a>
                            </div>
                            <select id="profile" class="form-control add-category select2" name="profile[]" multiple>
                                <option value="" >Select Profile...</option>
                                <?php
                                $query_profile_type = "SELECT * FROM profile_type WHERE hidden = '0' AND status = '1'";
                                $result_profile_type = mysqli_query($conn, $query_profile_type);            
                                while ($row_profile_type = mysqli_fetch_array($result_profile_type)) {
                                    $selected = in_array($row_profile_type['profile_type_id'], $selected_profile) ? 'selected' : '';
                                                ?>
                                    <option value="<?= $row_profile_type['profile_type_id'] ?>" data-category="<?= $row_profile_type['product_category'] ?>"  <?= $selected ?>><?= $row_profile_type['profile_type'] ?></option>
                                <?php   
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <?php $selected_grade = (array) json_decode($row['grade'] ?? '[]', true); ?>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">Product Grade</label>
                                <a href="?page=product_grade" target="_blank" class="text-decoration-none">Edit</a>
                            </div>
                            <select id="grade" class="form-control calculate add-category select2" name="grade[]" multiple>
                                <option value="" >Select Grade...</option>
                                <?php
                                $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY `product_grade` ASC";
                                $result_grade = mysqli_query($conn, $query_grade);            
                                while ($row_grade = mysqli_fetch_array($result_grade)) {
                                    $selected = in_array($row_grade['product_grade_id'], $selected_grade) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_grade['product_grade_id'] ?>" data-category="<?= $row_grade['product_category'] ?>" data-multiplier="<?= $row_grade['multiplier'] ?>" <?= $selected ?>><?= $row_grade['product_grade'] ?></option>
                                <?php   
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <?php $selected_gauge = (array) json_decode($row['gauge'] ?? '[]', true); ?>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">Product Gauge</label>
                                <a href="?page=product_gauge" target="_blank" class="text-decoration-none">Edit</a>
                            </div>
                            <select id="gauge" class="form-control calculate select2" name="gauge[]" multiple>
                                <option value="" >Select Gauge...</option>
                                <?php
                                $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1' ORDER BY `product_gauge` ASC";
                                $result_gauge = mysqli_query($conn, $query_gauge);

                                $unique_gauges = [];

                                while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                    if (in_array($row_gauge['product_gauge_id'], $unique_gauges)) {
                                        continue;
                                    }

                                    $unique_gauges[] = $row_gauge['product_gauge'];
                                    
                                    $selected = in_array($row_gauge['product_gauge_id'], $selected_gauge) ? 'selected' : '';
                                    ?>
                                    <option value="<?= $row_gauge['product_gauge_id'] ?>" 
                                            data-multiplier="<?= $row_gauge['multiplier'] ?>" 
                                            data-abbrev="<?= $row_gauge['gauge_abbreviations'] ?>" 
                                            <?= $selected ?>>
                                        <?= $row_gauge['product_gauge'] ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <?php 
                    $has_color = $row['has_color'] ?? null;
                    $checked = (!isset($row['has_color']) || $has_color > 0) ? 'checked' : '';
                    ?>
                    <div class="col-4 mb-3 text-center">
                        <label class="form-check-label fw-bold d-block mb-1" for="has_color">
                            Product has color?
                        </label>
                        <div class="form-check d-flex justify-content-center">
                            <input class="form-check-input" type="checkbox" id="has_color" name="has_color" <?= $checked ?>>
                        </div>
                    </div>

                    <?php 
                    $is_special_trim = $row['is_special_trim'] ?? null;
                    $checked = (!isset($row['is_special_trim']) || $is_special_trim > 0) ? 'checked' : '';
                    ?>
                    <div class="col-4 mb-3 text-center">
                        <label class="form-check-label fw-bold d-block mb-1" for="is_special_trim">
                            Is Product Special Trim?
                        </label>
                        <div class="form-check d-flex justify-content-center">
                            <input class="form-check-input" type="checkbox" id="is_special_trim" name="is_special_trim" <?= $checked ?>>
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
                    $selected_color_groups = (array) json_decode($row['color_group'] ?? '[]', true);
                    ?>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">Available Color Groups</label>
                                <a href="?page=color_group" target="_blank" class="text-decoration-none">Edit</a>
                            </div>
                            <select id="color" class="form-control calculate select2" name="color_group[]" multiple>
                                <option value="">Select Color Group...</option>
                                <?php
                                $query_groups = "SELECT * FROM product_color ORDER BY color_name ASC";
                                $result_groups = mysqli_query($conn, $query_groups);

                                while ($row_group = mysqli_fetch_assoc($result_groups)) {
                                    $selected = in_array($row_group['id'], $selected_color_groups) ? 'selected' : '';
                                    ?>
                                    <option value="<?= $row_group['id'] ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($row_group['color_name']) ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
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
                            <label class="form-label">Approx Weight per Ft</label>
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
                <h5 class="mb-0 fw-bold" id="trim_spec_title">Special Trim Specs</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row" id="special_trim_container">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">Customer</label>
                                <a href="?page=customer" target="_blank" class="text-decoration-none">Edit</a>
                            </div>
                            <select id="customer" class="form-control select2" name="customer">
                                <option value="" >Select Customer...</option>
                                <?php
                                $query_customer = "SELECT * FROM customer WHERE hidden = '0' AND status = '1' ORDER BY `customer_first_name` ASC";
                                $result_customer = mysqli_query($conn, $query_customer);            
                                while ($row_customer = mysqli_fetch_array($result_customer)) {
                                    $selected = ($row_customer['customer_id'] == $row['customer']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_customer['customer_id'] ?>" <?= $selected ?>><?= get_customer_name($row_customer['customer_id']) ?></option>
                                <?php   
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8"></div>

                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Special Trim Description</label>
                            <input type="text" id="spec_trim_desc" name="spec_trim_desc" class="form-control" value="<?= $row['spec_trim_desc']?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Special Trim #</label>
                            <input type="text" id="spec_trim_no" name="spec_trim_no" class="form-control" value="<?= $row['spec_trim_no']?>" />
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Flat Sheet Width</label>
                            <input type="text" id="flat_sheet_width" name="flat_sheet_width" class="form-control" value="<?= $row['flat_sheet_width'] ?>"/>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Total Hems</label>
                            <input type="text" id="hems" name="hems" class="form-control" value="<?= $row['hems'] ?>" placeholder="Enter Hems"/>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Total Bends</label>
                            <input type="text" id="bends" name="bends" class="form-control" value="<?= $row['bends']?>" placeholder="Enter Bends"/>
                        </div>
                    </div>
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
                            <label class="form-label">Base Price per Ft</label>
                            <input type="text" id="retail" name="unit_price" class="form-control" value="<?=number_format($unit_price ?? 0,3)?>"/>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <?php $floor_price = floatval($row['floor_price']) ?? 0; ?>
                        <div class="mb-3">
                            <label class="form-label">Floor Price per Ft</label>
                            <input type="text" id="retail" name="floor_price" class="form-control" value="<?=number_format($floor_price ?? 0,3)?>"/>
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
            <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Product IDs</h5>
                <button type="button" id="btn_fetch_prod_id" class="btn btn-sm btn-primary">Fetch Product IDs</button>
            </div>

            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <h4 id="product_ids_abbrev" class="fw-semibold"></h4>
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
