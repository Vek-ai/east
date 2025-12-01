<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$product_excel = 'special_trim_excel';
//4 = TRIM
$trim_id = 4;
$category_id = 4;

$includedColumns = [
    'special_trim_id'   => 'ID',
    'product_category'  => 'Product Category',
    'product_type'      => 'Product Type',
    'profile'           => 'Product Profile',
    'grade'             => 'Grade',
    'gauge'             => 'Gauge',
    'color_group'       => 'Color Group',
    'color_paint'       => 'Color',
    'customer_id'       => 'Customer',
    'trim_no'           => 'Special Trim #',
    'description'       => 'Description',
    'warranty_type'     => 'Warranty Type',
    'product_origin'    => 'Manufactured or Purchased',
    'unit_of_measure'   => 'Unit of Measure',
    'weight'            => 'Weight',
    'flat_sheet_width'  => 'Flat Sheed Width',
    'bends'             => 'Total Bends',
    'hems'              => 'Total Hems',
    'available_lengths' => 'Available Lengths',
    'unit_price'        => 'Retail Price',
    'comment'           => 'Notes',
    'last_order'        => 'Last Ordered'
];

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $special_trim_id = mysqli_real_escape_string($conn, $_POST['special_trim_id']);

        $product_type      = isset($_POST['product_type']) ? array_filter(array_map('intval', (array)$_POST['product_type'])) : [];
        $profile           = isset($_POST['profile']) ? array_filter(array_map('intval', (array)$_POST['profile'])) : [];
        $grade             = isset($_POST['grade']) ? array_filter(array_map('intval', (array)$_POST['grade'])) : [];
        $gauge             = isset($_POST['gauge']) ? array_filter(array_map('intval', (array)$_POST['gauge'])) : [];
        $color_paint       = isset($_POST['color_paint']) ? array_filter(array_map('intval', (array)$_POST['color_paint'])) : [];
        $available_lengths = isset($_POST['available_lengths']) ? array_filter(array_map('intval', (array)$_POST['available_lengths'])) : [];


        $fields = [];
        foreach ($_POST as $key => $value) {
            if (is_array($value)) {
                $allNumeric = array_reduce($value, fn($carry, $item) => $carry && is_numeric($item), true);
                if ($allNumeric) $value = array_map('intval', $value);
                $value = json_encode($value);
            }
            $escapedValue = mysqli_real_escape_string($conn, $value);
            if ($key != 'special_trim_id') $fields[$key] = $escapedValue;
            if ($key == 'color_paint') $fields['color'] = $escapedValue;
        }

        $checkQuery = "SELECT * FROM special_trim WHERE special_trim_id = '$special_trim_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE special_trim SET ";
            foreach ($fields as $column => $value) {
                $columnExists = mysqli_query($conn, "SHOW COLUMNS FROM special_trim LIKE '$column'");
                if (mysqli_num_rows($columnExists) > 0) {
                    $updateQuery .= "$column = '$value', ";
                }
            }
            $updateQuery = rtrim($updateQuery, ", ") . " WHERE special_trim_id = '$special_trim_id'";
            if (!mysqli_query($conn, $updateQuery)) {
                echo "Error updating special_trim: " . mysqli_error($conn);
                exit;
            }
            echo "success_update";
        } else {
            $columns = $values = [];
            foreach ($fields as $column => $value) {
                $columnExists = mysqli_query($conn, "SHOW COLUMNS FROM special_trim LIKE '$column'");
                if (mysqli_num_rows($columnExists) > 0) {
                    $columns[] = $column;
                    $values[] = "'$value'";
                }
            }
            $columnsStr = implode(", ", $columns);
            $valuesStr = implode(", ", $values);
            $insertQuery = "INSERT INTO special_trim (special_trim_id, $columnsStr) VALUES ('$special_trim_id', $valuesStr)";
            if (!mysqli_query($conn, $insertQuery)) {
                echo "Error adding special_trim: " . mysqli_error($conn);
                exit;
            }
            $special_trim_id = $conn->insert_id;
            $conn->query("UPDATE special_trim SET main_image='images/product/product.jpg' WHERE special_trim_id='$special_trim_id'");
            echo "success_add";
        }

        if (!empty($_FILES['picture_path']['name'][0])) {
            $uploadFileDir = '../images/product/';
            for ($i = 0; $i < count($_FILES['picture_path']['name']); $i++) {
                $fileTmpPath = $_FILES['picture_path']['tmp_name'][$i];
                $fileName = $_FILES['picture_path']['name'][$i];
                if (empty($fileName)) continue;
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    if ($i == 0) $conn->query("UPDATE special_trim SET main_image='images/product/$newFileName' WHERE special_trim_id='$special_trim_id'");
                    $conn->query("INSERT INTO product_images (productid, image_url) VALUES ('$special_trim_id', 'images/product/$newFileName')");
                }
            }
        }

        if (!empty($product_type) || !empty($profile) || !empty($grade) || !empty($gauge) || !empty($color_paint) || !empty($available_lengths)) {
            generateProductAbr([$product_category], $profile, $grade, $gauge, $product_type, $color_paint, $available_lengths, $special_trim_id);
        }
    }

    if ($action == "get_product_abr") {
        $category_ids = isset($_POST['category_ids']) ? array_map('intval', $_POST['category_ids']) : [];
        $type_ids     = isset($_POST['type_ids']) ? array_map('intval', $_POST['type_ids']) : [];
        $profile_ids  = isset($_POST['profile_ids']) ? array_map('intval', $_POST['profile_ids']) : [];
        $grade_ids    = isset($_POST['grade_ids']) ? array_map('intval', $_POST['grade_ids']) : [];
        $gauge_ids    = isset($_POST['gauge_ids']) ? array_map('intval', $_POST['gauge_ids']) : [];
        $color_ids    = isset($_POST['color_ids']) ? array_map('intval', $_POST['color_ids']) : [];
        $length       = isset($_POST['length']) ? floatval($_POST['length']) : 0;
        $special_trim_id   = isset($_POST['special_trim_id']) ? $_POST['special_trim_id'] : '';

        $panel_id = 3;
        $trim_id  = 4;

        $product_ids_string = generateProductAbrString(
                                $category_ids,
                                $profile_ids,
                                $grade_ids,
                                $gauge_ids,
                                $type_ids,
                                $color_ids,
                                '',
                                $special_trim_id = null
                            );

        echo $product_ids_string;
    } 

    if ($action == "fetch_modal") {
        $special_trim_id = mysqli_real_escape_string($conn, $_POST['id']);

        $checkQuery = "SELECT * FROM special_trim WHERE special_trim_id = '$special_trim_id'";
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
                    <?php $selected_category = (array) json_decode($row['product_category'] ?? '[]', true); ?>
                    <div class="col-md-4">
                        <label class="form-label">Product Category</label>
                        <div class="mb-3">
                        <select id="product_category" class="form-control select2" name="product_category">
                            <option value="" >Select One...</option>
                            <?php
                            $query_roles = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                            $result_roles = mysqli_query($conn, $query_roles);            
                            while ($row_product_category = mysqli_fetch_array($result_roles)) {
                                $selected = in_array($row_product_category['product_category_id'], $selected_category) ? 'selected' : '';
                            ?>
                                <option value="<?= $row_product_category['product_category_id'] ?>" 
                                        data-category="<?= $row_product_category['product_category'] ?>"
                                        data-filename="<?= $row_product_category['product_filename'] ?>"
                                        <?= $selected ?>
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
                    $assigned_colors = getAssignedProductColors($special_trim_id);
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
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">Customer</label>
                                <a href="?page=customer" target="_blank" class="text-decoration-none">Edit</a>
                            </div>
                            <select id="customer_id" class="form-control select2" name="customer_id" multiple>
                                <option value="" >Select Customer...</option>
                                <?php
                                $query_customer = "SELECT * FROM customer WHERE hidden = '0' AND status = '1' ORDER BY `customer_first_name` ASC";
                                $result_customer = mysqli_query($conn, $query_customer);            
                                while ($row_customer = mysqli_fetch_array($result_customer)) {
                                    $selected = ($row_customer['customer_id'] == $row['customer_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_customer['customer_id'] ?>" <?= $selected ?>><?= get_customer_name($row_customer['customer_id']) ?></option>
                                <?php   
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4"></div>

                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Sepcial Trim Description</label>
                            <input type="text" id="description" name="description" class="form-control" value="<?= $row['description']?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Special Trim #</label>
                            <input type="text" id="trim_no" name="trim_no" class="form-control" value="<?= $row['trim_no']?>" />
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
                    $query_img = "SELECT * FROM product_images WHERE productid = '$special_trim_id'";
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
                <h5 class="mb-0 fw-bold">Trim Specs</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">Flat Sheet Width</label>
                                <a href="?page=flat_sheet_width" target="_blank" class="text-decoration-none">Edit</a>
                            </div>
                            <select id="flat_sheet_width" class="form-control select2" name="flat_sheet_width[]" multiple>
                                <option value="">Select Widths...</option>
                                <?php $selected_fs_width = (array) json_decode($row['flat_sheet_width'] ?? '[]', true);
                                $query_fs_width = "SELECT * FROM flat_sheet_width WHERE hidden = '0' AND status = '1' ORDER BY `width` ASC";
                                $result_fs_width = mysqli_query($conn, $query_fs_width);            
                                while ($row_fs_width = mysqli_fetch_array($result_fs_width)) {
                                    $selected = in_array($row_fs_width['id'], $selected_fs_width) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_fs_width['id'] ?>" data-type="<?= $row_fs_width['product_type'] ?>" data-line="<?= $row_fs_width['product_line'] ?>" <?= $selected ?>><?= $row_fs_width['width'] ?></option>
                                <?php   
                                }
                                ?>
                            </select>
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
        
        <div class="modal-footer">
            <div class="form-actions">
                <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
        <?php
    }

    if ($action == "remove_image") {
        $image_id = $_POST['image_id'];
    
        $delete_query = "DELETE FROM product_images WHERE prodimgid = '$image_id'";
        if (mysqli_query($conn, $delete_query)) {
            /* if (file_exists($image_url)) {
                unlink($image_url);
            } */
            echo 'success';
        } else {
            echo "Error removing image: " . mysqli_error($conn);
        }
    }

    if ($action == "fetch_uploaded_modal") {
        $sql = "SELECT * FROM `$product_excel`";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $columnsWithData = [];
            while ($row = $result->fetch_assoc()) {
                foreach (array_keys($includedColumns) as $col) {
                    if (!empty(trim($row[$col] ?? ''))) {
                        $columnsWithData[$col] = true;
                    }
                }
            }

            $result->data_seek(0);
            ?>
            
            <div class="card card-body shadow">
                <form id="tableForm">
                    <div style="overflow-x: auto; overflow-y: auto; max-height: 90vh; max-width: 100%;">
                        <table class="table table-bordered table-striped text-center">
                            <thead>
                                <tr>
                                    <?php
                                    foreach ($includedColumns as $dbCol => $label) {
                                        if (isset($columnsWithData[$dbCol])) {
                                            echo "<th class='fs-4'>" . htmlspecialchars($label) . "</th>";
                                        }
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            while ($row = $result->fetch_assoc()) {
                                $special_trim_id = htmlspecialchars($row['special_trim_id'] ?? '');
                                echo '<tr>';
                                foreach ($includedColumns as $dbCol => $label) {
                                    if (isset($columnsWithData[$dbCol])) {
                                        $value = htmlspecialchars($row[$dbCol] ?? '');
                                        echo "<td contenteditable='true' class='table_data' data-header-name='" . htmlspecialchars($dbCol) . "' data-id='" . $special_trim_id . "'>$value</td>";
                                    }
                                }
                                echo '</tr>';
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <button type="button" id="saveTable" class="btn btn-primary mt-3">Save</button>
                    </div>
                </form>
            </div>
            <?php
        } else {
            echo "<p>No data found in the table.</p>";
        }
    }

    if ($action == "upload_excel") {
        if (isset($_FILES['excel_file'])) {
            $fileTmpPath = $_FILES['excel_file']['tmp_name'];
            $fileName = $_FILES['excel_file']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExtension, ["xlsx", "xls"])) {
                echo "Please upload a valid Excel file.";
                exit;
            }

            $spreadsheet = IOFactory::load($fileTmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $dbColumns = array_keys($includedColumns);
            $numColumns = count($dbColumns);

            if (!$conn->query("TRUNCATE TABLE `$product_excel`")) {
                echo "Error truncating table: " . $conn->error;
                exit;
            }

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                $row = array_pad($row, $numColumns, '');
                $row = array_slice($row, 0, $numColumns);

                $data = array_combine($dbColumns, $row);

                $escapedValues = array_map(fn($v) => mysqli_real_escape_string($conn, $v ?? ''), array_values($data));
                $columnNames = implode(", ", array_map(fn($col) => "`$col`", array_keys($data)));
                $columnValues = implode("', '", $escapedValues);

                $sql = "INSERT INTO `$product_excel` ($columnNames) VALUES ('$columnValues')";
                if (!$conn->query($sql)) {
                    echo "Error inserting row {$i}: " . $conn->error;
                    exit;
                }
            }

            echo "success";
        } else {
            echo "No file uploaded.";
            exit;
        }
    }
    
    if ($action == "save_table") {
        $table = "special_trim";
    
        $selectSql = "SELECT * FROM $product_excel";
        $result = $conn->query($selectSql);
    
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                unset($row['id']);
    
                $special_trim_id = trim($row['special_trim_id'] ?? '');
    
                $conditions = [];
                foreach ($row as $column => $value) {
                    $conditions[] = "$column = '" . $conn->real_escape_string($value) . "'";
                }
    
                $checkSql = "SELECT COUNT(*) as count FROM $table WHERE " . implode(" AND ", $conditions);
                $checkResult = $conn->query($checkSql);
                $exists = $checkResult->fetch_assoc()['count'] > 0;
    
                if ($exists) {
                    continue;
                }
    
                if (!empty($special_trim_id)) {
                    $idCheckSql = "SELECT COUNT(*) as count FROM $table WHERE special_trim_id = '$special_trim_id'";
                    $idCheckResult = $conn->query($idCheckSql);
                    $idExists = $idCheckResult->fetch_assoc()['count'] > 0;
    
                    if ($idExists) {
                        $updateFields = [];
                        foreach ($row as $column => $value) {
                            if ($column !== 'special_trim_id') {
                                $updateFields[] = "$column = '" . $conn->real_escape_string($value) . "'";
                            }
                        }
                        $updateSql = "UPDATE $table SET " . implode(", ", $updateFields) . " WHERE special_trim_id = '$special_trim_id'";
                        $conn->query($updateSql);
                        continue;
                    }
                }
    
                $columns = implode(", ", array_keys($row));
                $values = "'" . implode("', '", array_map([$conn, 'real_escape_string'], array_values($row))) . "'";
                $insertSql = "INSERT INTO $table ($columns) VALUES ($values)";
                $conn->query($insertSql);
            }
    
            echo "Data has been successfully saved";
    
            $truncateSql = "TRUNCATE TABLE $product_excel";
            if ($conn->query($truncateSql) !== TRUE) {
                echo " but failed to clear $product_excel table: " . $conn->error;
            }
        } else {
            echo "No data found in $product_excel table.";
        }
    }    

    if ($action == "download_excel") {
        $columnNames = array_keys($includedColumns);
        $column_txt = implode(', ', $columnNames);

        $sql = "SELECT $column_txt FROM special_trim WHERE hidden = '0' AND status = '1'";
        $result = $conn->query($sql);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $row = 1;

        foreach ($includedColumns as $column => $header) {
            $index = array_search($column, $columnNames);
            $columnLetter = indexToColumnLetter($index);
            $sheet->setCellValue($columnLetter . $row, $header);
        }

        $row = 2;
        while ($data = $result->fetch_assoc()) {
            foreach ($includedColumns as $column => $header) {
                $index = array_search($column, $columnNames);
                $columnLetter = indexToColumnLetter($index);
                $sheet->setCellValue($columnLetter . $row, $data[$column] ?? '');
            }
            $row++;
        }

        $filename = "Special Trim.xlsx";
        $filePath = $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: max-age=0');

        readfile($filePath);
        unlink($filePath);
        exit;
    }

    if ($action == "download_classifications") {
        $classification = mysqli_real_escape_string($conn, $_REQUEST['class'] ?? '');
    
        $classifications = [
            'category' => [
                'columns' => ['product_category_id', 'product_category'],
                'table' => 'product_category',
                'where' => "status = '1'"
            ],
            'type' => [
                'columns' => ['product_type_id', 'product_type'],
                'table' => 'product_type',
                'where' => "status = '1'"
            ],
            'grade' => [
                'columns' => ['product_grade_id', 'product_grade'],
                'table' => 'product_grade',
                'where' => "status = '1'"
            ],
            'color' => [
                'columns' => ['color_id', 'product_category', 'color_name'],
                'table' => 'paint_colors',
                'where' => "color_status = '1'"
            ],
            'profile' => [
                'columns' => ['profile_type_id', 'profile_type'],
                'table' => 'profile_type',
                'where' => "status = '1'"
            ],
            'flat_sheet_width' => [
                'columns' => ['id', 'product_system', 'product_category', 'product_line', 'product_type', 'width'],
                'table' => 'flat_sheet_width',
                'where' => "status = '1'"
            ]
        ];
    
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $selectedClassifications = empty($classification) ? array_keys($classifications) : [$classification];
    
        foreach ($selectedClassifications as $class) {
            if (!isset($classifications[$class])) {
                continue;
            }
    
            $includedColumns = $classifications[$class]['columns'];
            $table = $classifications[$class]['table'];
            $where = $classifications[$class]['where'];
            $column_txt = implode(', ', $includedColumns);
            $sql = "SELECT $column_txt FROM $table WHERE $where";
            $result = $conn->query($sql);
    
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle(ucwords($class));
    
            $row = 1;
            foreach ($includedColumns as $index => $column) {
                $header = ucwords(str_replace('_', ' ', $column));
                $columnLetter = chr(65 + $index);
                $sheet->setCellValue($columnLetter . $row, $header);
            }
    
            $row = 2;
            while ($data = $result->fetch_assoc()) {
                foreach ($includedColumns as $index => $column) {
                    $columnLetter = chr(65 + $index);

                    $value = $data[$column] ?? '';
    
                    if ($column == 'product_category' && $class == 'color') {
                        $value = getProductCategoryName($data[$column] ?? '');
                    }

                    if ($column == 'product_system' && $class == 'flat_sheet_width') {
                        $value = getProductSystemName($data[$column] ?? '');
                    }

                    if ($column == 'product_category' && $class == 'flat_sheet_width') {
                        $value = getProductCategoryName($data[$column] ?? '');
                    }

                    if ($column == 'product_line' && $class == 'flat_sheet_width') {
                        $value = getProductLineName($data[$column] ?? '');
                    }

                    if ($column == 'product_type' && $class == 'flat_sheet_width') {
                        $value = getProductTypeName($data[$column] ?? '');
                    }
                        
                    $sheet->setCellValue($columnLetter . $row, $value);
                }
                $row++;
            }
        }

        if(empty($classification)){
            $classification = 'Classifications';
        }else{
            $classification = ucwords($classification);
        }
    
        $filename = "$classification.xlsx";
        $filePath = $filename;
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: max-age=0');
    
        readfile($filePath);
        unlink($filePath);
        exit;
    }    

    if ($action == "update_product_data") {
        $column_name = $_POST['header_name'];
        $new_value = $_POST['new_value'];
        $special_trim_id = $_POST['id'];
        
        if (empty($column_name) || empty($special_trim_id)) {
            exit;
        }
        
        $column_name = mysqli_real_escape_string($conn, $column_name);
        $new_value = mysqli_real_escape_string($conn, $new_value);
        $special_trim_id = mysqli_real_escape_string($conn, $special_trim_id);
        
        $sql = "UPDATE $product_excel SET `$column_name` = '$new_value' WHERE special_trim_id = '$special_trim_id'";

        if ($conn->query($sql) === TRUE) {
            echo 'Success';
        } else {
            echo 'Error updating record: ' . $conn->error;
        }
    }
    
    if ($action == 'fetch_products') {
        $permission = $_SESSION['permission'];
        $data = [];
        $query = "
            SELECT 
                * 
            FROM special_trim 
            WHERE 
                hidden = 0 AND 
                status = 1
        ";
        $result = mysqli_query($conn, $query);
        $no = 1;
    
        while ($row = mysqli_fetch_assoc($result)) {
            $special_trim_id = $row['special_trim_id'];
            $customer_id = $row['customer_id'];
            $customer = get_customer_name($customer_id);
            $description = $row['description'];
            $trim_no = $row['trim_no'];
            $last_order = $row['last_order'];
            
            $action_html = '';
            if ($permission === 'edit') {
                $action_html = "
                    <div class='action-btn text-center'>
                        <a href='javascript:void(0)' 
                        id='view_product_btn' 
                        title='View' 
                        class='text-primary edit' 
                        data-id='{$special_trim_id}' 
                        data-category='{$row['product_category']}'>
                        <i class='ti ti-eye fs-7'></i>
                        </a>
                        <a href='javascript:void(0)' 
                        id='addProductModalBtn' 
                        title='Edit' 
                        class='text-warning edit'
                        data-id='{$special_trim_id}' 
                        >
                        <i class='ti ti-pencil fs-7'></i>
                        </a>
                    </div>";
            }
    
            $data[] = [
                'customer'          => $customer,
                'color'             => getColorName($row['color']),
                'description'       => $description,
                'trim_no'           => $trim_no,
                'last_order'        => $last_order,
                'customer_id'       => $customer_id,
                'grade'             => $row['grade'],
                'gauge'             => $row['gauge'],
                'action_html'       => $action_html
            ];
    
            $no++;
        }
    
        echo json_encode(['data' => $data]);
    }
    
    mysqli_close($conn);
}
?>
