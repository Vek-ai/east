<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

$gauge_29_id = 1;
$gauge_26_id = 2;

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $main_image = isset($_POST['picture_path']) ? mysqli_real_escape_string($conn, $_POST['picture_path']) : '';
        $coil_id = isset($_POST['coil_id']) ? mysqli_real_escape_string($conn, $_POST['coil_id']) : '';
        $entry_no = isset($_POST['entry_no']) ? mysqli_real_escape_string($conn, $_POST['entry_no']) : '';
        $warehouse = isset($_POST['warehouse']) ? mysqli_real_escape_string($conn, $_POST['warehouse']) : '';
        $color_close = isset($_POST['color_close']) ? mysqli_real_escape_string($conn, $_POST['color_close']) : '';
        $actual_color = isset($_POST['actual_color']) ? mysqli_real_escape_string($conn, $_POST['actual_color']) : '';
        $color_family = isset($_POST['color_family']) ? mysqli_real_escape_string($conn, $_POST['color_family']) : '';
        $color_abbreviation = isset($_POST['color_abbreviation']) ? mysqli_real_escape_string($conn, $_POST['color_abbreviation']) : '';
        $paint_supplier = isset($_POST['paint_supplier']) ? mysqli_real_escape_string($conn, $_POST['paint_supplier']) : '';
        $paint_code = isset($_POST['paint_code']) ? mysqli_real_escape_string($conn, $_POST['paint_code']) : '';
        $stock_availability = isset($_POST['stock_availability']) ? mysqli_real_escape_string($conn, $_POST['stock_availability']) : '';
        $multiplier_category = isset($_POST['multiplier_category']) ? mysqli_real_escape_string($conn, $_POST['multiplier_category']) : '';
        $coil_no = isset($_POST['coil_no']) ? mysqli_real_escape_string($conn, $_POST['coil_no']) : '';
        $date = isset($_POST['date']) ? mysqli_real_escape_string($conn, $_POST['date']) : '';
        $supplier = isset($_POST['supplier']) ? mysqli_real_escape_string($conn, $_POST['supplier']) : '';
        $supplier_tag = isset($_POST['supplier_tag']) ? mysqli_real_escape_string($conn, $_POST['supplier_tag']) : '';
        $color_sold_as = isset($_POST['color_sold_as']) ? mysqli_real_escape_string($conn, $_POST['color_sold_as']) : '';
        $product_id = isset($_POST['product_id']) ? mysqli_real_escape_string($conn, $_POST['product_id']) : '';
        $og_length = isset($_POST['og_length']) ? mysqli_real_escape_string($conn, $_POST['og_length']) : 0;
        $weight = isset($_POST['weight']) ? mysqli_real_escape_string($conn, $_POST['weight']) : 0;
        $thickness = isset($_POST['thickness']) ? mysqli_real_escape_string($conn, $_POST['thickness']) : 0;
        $width = isset($_POST['width']) ? mysqli_real_escape_string($conn, $_POST['width']) : 0;
        $grade = isset($_POST['grade']) ? mysqli_real_escape_string($conn, $_POST['grade']) : '';
        $gauge = isset($_POST['gauge']) ? mysqli_real_escape_string($conn, $_POST['gauge']) : '';
        $coating = isset($_POST['coating']) ? mysqli_real_escape_string($conn, $_POST['coating']) : '';
        $tag_no = isset($_POST['tag_no']) ? mysqli_real_escape_string($conn, $_POST['tag_no']) : '';
        $invoice_no = isset($_POST['invoice_no']) ? mysqli_real_escape_string($conn, $_POST['invoice_no']) : '';
        $remaining_feet = isset($_POST['remaining_feet']) ? mysqli_real_escape_string($conn, $_POST['remaining_feet']) : 0; 
        $last_inventory_count = isset($_POST['last_inventory_count']) ? mysqli_real_escape_string($conn, $_POST['last_inventory_count']) : 0; 
        $coil_class = isset($_POST['coil_class']) ? mysqli_real_escape_string($conn, $_POST['coil_class']) : '';
        if (!empty($date)) {
            $year = date('Y', strtotime($date));
            $month = date('m', strtotime($date));
        } else {
            $year = '';
            $month = '';
        }
        $extracting_price = '';
        if (!empty($product_id)) {
            $extracting_price = substr($product_id, 2, 3);
        }

        $grade_no = isset($_POST['grade_no']) ? mysqli_real_escape_string($conn, $_POST['grade_no']) : '';
        $price = isset($_POST['price']) ? mysqli_real_escape_string($conn, $_POST['price']) : 0;
        $avg_by_color = floatval($price);
        $total = floatval($remaining_feet) * floatval($price);
        $lb_per_ft = (floatval($og_length) != 0) ? (floatval($weight) / floatval($og_length)) : 0;
        $current_weight = floatval($lb_per_ft) * floatval($remaining_feet);
        $contract_ppf = isset($_POST['contract_ppf']) ? mysqli_real_escape_string($conn, $_POST['contract_ppf']) : 0;
        $contract_ppcwg = isset($_POST['contract_ppcwg']) ? mysqli_real_escape_string($conn, $_POST['contract_ppcwg']) : '';
        $invoice_price = isset($_POST['invoice_price']) ? mysqli_real_escape_string($conn, $_POST['invoice_price']) : 0;
        $round_width = isset($_POST['round_width']) ? mysqli_real_escape_string($conn, $_POST['round_width']) : '';

        $checkQuery = "SELECT * FROM coil_product WHERE coil_id = '$coil_id'";
        $result = mysqli_query($conn, $checkQuery);
        $isInsert = false;

        if (mysqli_num_rows($result) > 0) {
            // Record exists, update the coil_product table
            $isInsert = false;
            $updateQuery = "UPDATE coil_product SET
                entry_no = '$entry_no',
                warehouse = '$warehouse',
                color_close = '$color_close',
                actual_color = '$actual_color',
                color_family = '$color_family',
                color_abbreviation = '$color_abbreviation',
                paint_supplier = '$paint_supplier',
                paint_code = '$paint_code',
                stock_availability = '$stock_availability',
                multiplier_category = '$multiplier_category',
                coil_no = '$coil_no',
                date = '$date',
                supplier = '$supplier',
                supplier_tag = '$supplier_tag',
                color_sold_as = '$color_sold_as',
                product_id = '$product_id',
                og_length = '$og_length',
                weight = '$weight',
                thickness = '$thickness',
                width = '$width',
                grade = '$grade',
                coating = '$coating',
                tag_no = '$tag_no',
                invoice_no = '$invoice_no',
                remaining_feet = '$remaining_feet',
                last_inventory_count = '$last_inventory_count',
                coil_class = '$coil_class',
                gauge = '$gauge',
                grade_no = '$grade_no',
                year = '$year',
                month = '$month',
                extracting_price = '$extracting_price',
                price = '$price',
                avg_by_color = '$avg_by_color',
                total = '$total',
                current_weight = '$current_weight',
                lb_per_ft = '$lb_per_ft',
                contract_ppf = '$contract_ppf',
                contract_ppcwg = '$contract_ppcwg',
                invoice_price = '$invoice_price',
                round_width = '$round_width',
                main_image = '$main_image'
            WHERE coil_id = '$coil_id'";

            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating record: " . mysqli_error($conn);
            }
        } else {
            $isInsert = true;
            $insertQuery = "INSERT INTO coil_product (
                coil_id, entry_no, warehouse, color_family, color_abbreviation, paint_supplier, paint_code, stock_availability, multiplier_category, color_close, actual_color, coil_no, date, supplier, supplier_tag,
                color_sold_as, product_id, og_length, weight, thickness, width, grade, coating, 
                tag_no, invoice_no, remaining_feet, last_inventory_count, coil_class, gauge, grade_no, year, month, extracting_price, 
                price, avg_by_color, total, current_weight, lb_per_ft, contract_ppf, contract_ppcwg, invoice_price, 
                round_width, main_image
            ) VALUES (
                '$coil_id', '$entry_no', '$warehouse', '$color_family', '$color_abbreviation', '$paint_supplier', '$paint_code', '$stock_availability', '$multiplier_category', '$color_close', '$actual_color', '$coil_no', 
                '$date', '$supplier', '$supplier_tag', '$color_sold_as', '$product_id', '$og_length', '$weight', 
                '$thickness', '$width', '$grade', '$coating', '$tag_no', '$invoice_no', '$remaining_feet', '$last_inventory_count',
                '$coil_class', '$gauge', '$grade_no', '$year', '$month', '$extracting_price', '$price', '$avg_by_color', 
                '$total', '$current_weight', '$lb_per_ft', '$contract_ppf', '$contract_ppcwg', '$invoice_price', 
                '$round_width', '$main_image'
            )";

            if (mysqli_query($conn, $insertQuery)) {
                $coil_id = $conn->insert_id;
                echo "success_add";
            } else {
                echo "Error inserting record: " . mysqli_error($conn);
            }
        }
       

        if (!empty($_FILES['picture_path']['name'][0])) {
            if (is_array($_FILES['picture_path']['name']) && count($_FILES['picture_path']['name']) > 0) {
                $uploadFileDir = '../images/coils/';
                
                for ($i = 0; $i < count($_FILES['picture_path']['name']); $i++) {
                    $fileTmpPath = $_FILES['picture_path']['tmp_name'][$i];
                    $fileName = $_FILES['picture_path']['name'][$i];
                    
                    if (empty($fileName)) {
                        continue;
                    }
        
                    $fileNameCmps = explode(".", $fileName);
                    $fileExtension = strtolower(end($fileNameCmps));
                    
                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                    $dest_path = $uploadFileDir . $newFileName;
        
                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $picture_path = mysqli_real_escape_string($conn, $dest_path);
        
                        if ($i == 0) {
                            $sql = "UPDATE coil_product SET main_image='images/coils/$newFileName' WHERE coil_id='$coil_id'";
                            if (!$conn->query($sql)) {
                                echo "Error updating record: " . $conn->error;
                            }
                        }
                    } else {
                        echo 'Error moving the file to the upload directory.';
                    }
                }
            }
        } else {
            if ($isInsert) {
                $sql = "UPDATE coil_product SET main_image='images/coils/product.jpg' WHERE coil_id='$coil_id'";
                if (!$conn->query($sql)) {
                    echo "Error updating record: " . $conn->error;
                }
            }
        }
    }

    if ($action == "fetch_edit_modal") {
        $coil_id = mysqli_real_escape_string($conn, $_POST['id']);
        $checkQuery = "SELECT * FROM coil_product WHERE coil_id = '$coil_id'";
        $result = mysqli_query($conn, $checkQuery);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            ?>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">
                            Update Coil Details
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="edit_coil" class="form-horizontal" autocomplete="off">
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body">
                                    <input type="hidden" id="coil_id_edit" name="coil_id" class="form-control" value="<?= $row['coil_id'] ?>"/>

                                    <div class="row">
                                        <div class="card-body p-0">
                                            <h4 class="card-title text-center">Coil Image</h4>
                                            <p action="#" id="myUpdateDropzone" class="dropzone">
                                                <div class="fallback">
                                                <input type="file" id="picture_path_edit" name="picture_path" class="form-control" style="display: none"/>
                                                </div>
                                            </p>
                                        </div>
                                    </div>

                                    <?php
                                               
                                    if (!empty($row['main_image'])) {
                                    ?>
                                    <div class="card card-body m-2">
                                        <h5>Current Images</h5>
                                        <div class="row pt-3">
                                            <div class="col-md-2 position-relative">
                                                <div class="mb-3">
                                                    <img src="<?= $row_img['image_url'] ?>" class="img-fluid" alt="Product Image" />
                                                    <button class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image-btn" data-image-id="<?= $row['coil_id'] ?>">X</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    }
                                    ?>
    
                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Entry #</label>
                                                <input type="text" id="entry_no_edit" name="entry_no" class="form-control" value="<?= $row['entry_no'] ?>" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Warehouse</label>
                                            <div class="mb-3">
                                                <select id="warehouse_edit" class="form-control select2-edit" name="warehouse">
                                                    <option value="" >Select One...</option>
                                                    <?php
                                                    $query_warehouses = "SELECT * FROM warehouses WHERE status = 1 ORDER BY `WarehouseName` ASC";
                                                    $result_warehouses = mysqli_query($conn, $query_warehouses);            
                                                    while ($row_warehouses = mysqli_fetch_array($result_warehouses)) {
                                                        $selected = ($row['warehouse'] == $row_warehouses['WarehouseID']) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $row_warehouses['WarehouseID'] ?>" <?= $selected ?>><?= $row_warehouses['WarehouseName'] ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card">
                                        <h4 class="card-header">Color Details</h4>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 opt_field">
                                                    <label class="form-label">Close EKM Color</label>
                                                    <div class="mb-3">
                                                        <select id="color_close_edit" class="form-control select2-edit colors-edit" name="color_close">
                                                            <option value="" >Select Color...</option>
                                                            <?php
                                                            $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                                            $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                                                            while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                                                $selected = ($row['color_close'] == $row_paint_colors['color_id']) ? 'selected' : '';
                                                            ?>
                                                                <option value="<?= $row_paint_colors['color_id'] ?>" <?= $selected ?>><?= $row_paint_colors['color_name'] ?></option>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 opt_field">
                                                    <label class="form-label">Actual Color</label>
                                                    <div class="mb-3">
                                                        <select id="actual_color_edit" class="form-control select2-edit colors-edit" name="actual_color">
                                                            <option value="" >Select Color...</option>
                                                            <?php
                                                            $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                                            $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                                                            while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                                                $selected = ($row['actual_color'] == $row_paint_colors['color_id']) ? 'selected' : '';
                                                            ?>
                                                                <option value="<?= $row_paint_colors['color_id'] ?>" <?= $selected ?>><?= $row_paint_colors['color_name'] ?></option>
                                                            <?php   
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 opt_field">
                                                    <div class="mb-3">
                                                        <label class="form-label">Color Family</label>
                                                        <input type="text" id="color_family_edit" name="color_family" class="form-control" value="<?= $row['color_family'] ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Color Abbreviation</label>
                                                        <input type="text" id="color_abbreviation_edit" name="color_abbreviation" class="form-control" value="<?= $row['color_abbreviation'] ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Paint Supplier</label>
                                                        <input type="text" id="paint_supplier_edit" name="paint_supplier" class="form-control" value="<?= $row['paint_supplier'] ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Paint ID Code ID</label>
                                                        <input type="text" id="paint_code_edit" name="paint_code" class="form-control" value="<?= $row['paint_code'] ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Stock Availability</label>
                                                        <input type="text" id="stock_availability_edit" name="stock_availability" class="form-control" value="<?= $row['stock_availability'] ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Multiplier Category</label>
                                                        <input type="text" id="multiplier_category_edit" name="multiplier_category" class="form-control" value="<?= $row['multiplier_category'] ?>"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Coil #</label>
                                                <input type="text" id="coil_no_edit" name="coil_no" class="form-control" value="<?= $row['coil_no'] ?>"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Date</label>
                                                <input type="date" id="date_edit" name="date" class="form-control" value="<?= trim($row['date']) ?>"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Supplier</label>
                                            <div class="mb-3">
                                                <select id="supplier_edit" class="form-control select2-edit" name="supplier">
                                                    <option value="">Select Supplier...</option>
                                                    <?php
                                                    $query_supplier = "SELECT * FROM supplier WHERE status = '1'";
                                                    $result_supplier = mysqli_query($conn, $query_supplier);            
                                                    while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                                                        $selected = ($row['supplier'] == $row_supplier['supplier_id']) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $row_supplier['supplier_id'] ?>" <?= $selected ?>><?= $row_supplier['supplier_name'] ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Supplier Tag #</label>
                                            <input type="text" id="supplier_tag" name="supplier_tag" class="form-control" />
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Sold As</label>
                                            <div class="mb-3">
                                                <select id="color_sold_as_edit" class="form-control select2-edit" name="color_sold_as">
                                                    <option value="" >Select Color...</option>
                                                    <?php
                                                    $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                                    $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                                                    while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                                        $selected = ($row['color_sold_as'] == $row_paint_colors['color_id']) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $row_paint_colors['color_id'] ?>" <?= $selected ?>><?= $row_paint_colors['color_name'] ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Product ID</label>
                                                <input type="text" id="product_id_edit" name="product_id" class="form-control" value="<?= $row['product_id'] ?>"/>
                                            </div>
                                        </div>
                                    </div>    
                                    
                                    <div class="row pt-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Original Length</label>
                                                <input type="text" id="og_length_edit" name="og_length" class="form-control" value="<?= $row['og_length'] ?>"/>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Remaining Length</label>
                                                <input type="text" id="remaining_feet_edit" name="remaining_feet" class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Last Inventory Count</label>
                                                <input type="text" id="last_inventory_count_edit" name="last_inventory_count" class="form-control" />
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row pt-3">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Weight</label>
                                                <input type="text" id="weight_edit" name="weight" class="form-control" value="<?= $row['weight'] ?>"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Thickness</label>
                                                <input type="text" id="thickness_edit" name="thickness" class="form-control" value="<?= $row['thickness'] ?>"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Width</label>
                                            <div class="mb-3">
                                                <select id="width_edit" class="form-control select2-edit width-select" data-type="edit" name="width">
                                                    <option value="" >Select Coil Width...</option>
                                                    <?php
                                                    $query_width = "SELECT * FROM coil_width WHERE hidden = '0'";
                                                    $result_width = mysqli_query($conn, $query_width);            
                                                    while ($row_width = mysqli_fetch_array($result_width)) {
                                                        $selected = ($row['width'] == $row_width['id']) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $row_width['id'] ?>" ><?= $row_width['actual_width'] ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3 opt_field" data-id="5">
                                            <label class="form-label">Coil Class</label>
                                            <div class="mb-3">
                                                <input type="text" id="coil_class_edit" name="coil_class" class="form-control" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Grade</label>
                                            <div class="mb-3">
                                                <select id="grade_edit" class="form-control select2-edit" name="grade">
                                                    <option value="" >Select Grade...</option>
                                                    <?php
                                                    $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY `product_grade` ASC";
                                                    $result_grade = mysqli_query($conn, $query_grade);            
                                                    while ($row_grade = mysqli_fetch_array($result_grade)) {
                                                        $selected = ($row['grade'] == $row_grade['product_grade_id']) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $row_grade['product_grade_id'] ?>" <?= $selected ?>><?= $row_grade['product_grade'] ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Gauge</label>
                                            <div class="mb-3">
                                                <select id="gauge_edit" class="form-control select2-edit" name="gauge">
                                                    <option value="" >Select Gauge...</option>
                                                    <?php
                                                    $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1' GROUP BY product_gauge ORDER BY `product_gauge` ASC";
                                                    $result_gauge = mysqli_query($conn, $query_gauge);            
                                                    while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                                        $selected = ($row['gauge'] == $row_gauge['product_gauge_id']) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $row_gauge['product_gauge_id'] ?>" <?= $selected ?>><?= $row_gauge['product_gauge'] ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Coating</label>
                                                <input type="text" id="coating_edit" name="coating" class="form-control" value="<?= $row['coating'] ?>"/>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Tag #</label>
                                                <input type="text" id="tag_no_edit" name="tag_no" class="form-control" value="<?= $row['tag_no'] ?>"/>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Invoice #</label>
                                                <input type="text" id="invoice_no_edit" name="invoice_no" class="form-control" value="<?= $row['invoice_no'] ?>"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-4 opt_field" data-id="5">
                                            <label class="form-label">Grade No.</label>
                                            <div class="mb-3">
                                                <select id="grade_no_edit" class="form-control select2-edit" name="grade_no">
                                                    <option value="" <?= $row['grade_no'] == '' ? 'selected' : '' ?>>Select Grade No...</option>
                                                    <option value="1" <?= $row['grade_no'] == '1' ? 'selected' : '' ?>>1</option>
                                                    <option value="2" <?= $row['grade_no'] == '2' ? 'selected' : '' ?>>2</option>
                                                    <option value="3" <?= $row['grade_no'] == '3' ? 'selected' : '' ?>>3</option>
                                                    <option value="4" <?= $row['grade_no'] == '4' ? 'selected' : '' ?>>4</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Price ($)</label>
                                                <input type="text" id="price_edit" name="price" class="form-control" value="<?= $row['price'] ?>"/>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Contract PPF</label>
                                                <input type="text" id="contract_ppf_edit" name="contract_ppf" class="form-control" value="<?= $row['contract_ppf'] ?>"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Contract PPCWG</label>
                                                <input type="text" id="contract_ppcwg_edit" name="contract_ppcwg" class="form-control" value="<?= $row['contract_ppcwg'] ?>"/>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Invoice Price ($)</label>
                                                <input type="text" id="invoice_price_edit" name="invoice_price" class="form-control" value="<?= $row['invoice_price'] ?>"/>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Round Width</label>
                                            <input type="text" id="round_width_edit" name="round_width" class="form-control" value="<?= $row['round_width'] ?>"/>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="form-actions">
                                <div class="card-body">
                                    <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                                    <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /.modal-content -->
            </div>
            <script>
                $(document).ready(function() {
                    $(".select2-edit").each(function () {
                        $(this).select2({
                            width: '100%',
                            placeholder: "Select One...",
                            allowClear: true,
                            dropdownParent: $(this).parent()
                        });
                    });

                    $(document).on("change", ".colors-edit", function () {
                        let colorId = $(this).val();
                        if (colorId) {
                            $.ajax({
                                url: "pages/coil_product_ajax.php",
                                type: "POST",
                                data: { 
                                    color_id: colorId,
                                    action: 'fetch_color_details'
                                },
                                dataType: "json",
                                success: function (response) {
                                    if (response.success) {
                                        $("#color_family_edit").val(response.color_group);
                                        $("#color_abbreviation_edit").val(response.color_abbreviation);
                                        $("#paint_supplier_edit").val(response.provider_id);
                                        $("#paint_code_edit").val(response.ekm_paint_code);
                                        $("#stock_availability_edit").val(response.stock_availability);
                                        $("#multiplier_category_edit").val(response.multiplier_category);
                                    }
                                },
                                error: function (xhr, status, error) {
                                    console.error("XHR Response:", xhr.responseText);
                                    console.error("Status:", status);
                                    console.error("Error:", error);
                                    alert("Error fetching color details.");
                                }
                            });
                        }
                    });

                    let uploadedUpdateFiles = [];

                    $('#myUpdateDropzone').dropzone({
                        addRemoveLinks: true,
                        dictRemoveFile: "X",
                        init: function() {
                            this.on("addedfile", function(file) {
                                uploadedUpdateFiles.push(file);
                                updateFileInput2();
                                displayFileNames2()
                            });

                            this.on("removedfile", function(file) {
                                uploadedUpdateFiles = uploadedUpdateFiles.filter(f => f.name !== file.name);
                                updateFileInput2();
                                displayFileNames2()
                            });
                        }
                    });

                    function updateFileInput2() {
                        const fileInput = document.getElementById('picture_path_edit');
                        const dataTransfer = new DataTransfer();

                        uploadedUpdateFiles.forEach(file => {
                            const fileBlob = new Blob([file], { type: file.type });
                            dataTransfer.items.add(new File([fileBlob], file.name, { type: file.type }));
                        });

                        fileInput.files = dataTransfer.files;
                    }

                    function displayFileNames2() {
                        let files = document.getElementById('picture_path_edit').files;
                        let fileNames = '';

                        if (files.length > 0) {
                            for (let i = 0; i < files.length; i++) {
                                let file = files[i];
                                fileNames += `<p>${file.name}</p>`;
                            }
                        } else {
                            fileNames = '<p>No files selected</p>';
                        }

                        console.log(fileNames);
                    }

                    $('#product_category_update').on('change', function() {
                        var product_category_id = $(this).val();
                        $.ajax({
                            url: 'pages/product_ajax.php',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                product_category_id: product_category_id,
                                action: "fetch_product_fields"
                            },
                            success: function(response) {
                                $('.opt_field_update').hide();

                                if (response.length > 0) {

                                    response.forEach(function(field) {
                                        var fieldParts = field.fields.split(',');
                                        fieldParts.forEach(function(part) {
                                            $('.opt_field_update[data-id="' + part + '"]').show();
                                        });
                                    });
                                } else {
                                    $('.opt_field_update').show();
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                alert('Error: ' + textStatus + ' - ' + errorThrown);
                            }
                        });
                    });
                    
                });

            </script>

            <?php
        }
    } 

    if ($action == "fetch_color_details") {
        $color_id = intval($_POST['color_id']);

        $query = "SELECT * FROM paint_colors WHERE color_id = $color_id AND hidden = 0";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            echo json_encode([
                "success" => true,
                "color_group" => $row['color_group'],
                "color_abbreviation" => $row['color_abbreviation'],
                "provider_id" => strtoupper(getPaintProviderName($row['provider_id'])),
                "ekm_paint_code" => $row['ekm_paint_code'],
                "stock_availability" => $row['stock_availability'],
                "multiplier_category" => getColorMultiplierName($row['multiplier_category'])
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "No color details found."]);
        }
    }

    if ($action == "fetch_width_details") {
        $id = intval($_POST['width_id']);

        $query = "SELECT * FROM coil_width WHERE id = $id AND hidden = 0";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            echo json_encode([
                "success" => true,
                "classification" => $row['classification']
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "No width details found."]);
        }
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

    if ($action == "change_status") {
        $coil_id = mysqli_real_escape_string($conn, $_POST['coil_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE coil_product SET status = '$new_status' WHERE coil_id = '$coil_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }

    if ($action == 'hide_category') {
        $coil_id = mysqli_real_escape_string($conn, $_POST['coil_id']);
        $query = "UPDATE coil_product SET hidden='1' WHERE coil_id='$coil_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'fetch_product_fields') {
        $product_category_id = mysqli_real_escape_string($conn, $_POST['product_category_id']);
        $query = "SELECT * FROM product_fields WHERE product_category_id='$product_category_id'";
        $result = mysqli_query($conn, $query);
    
        if ($result) {
            $fields = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $fields[] = $row;
            }
            echo json_encode($fields);
        } else {
            echo 'error';
        }
    }
    
    mysqli_close($conn);
}
?>
