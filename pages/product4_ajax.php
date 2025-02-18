<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = 'test';
//4 = TRIM
$trim_id = 4;
$category_id = 4;

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
        
        $fields = [];
        foreach ($_POST as $key => $value) {
            if ($key != 'product_id') {
                $fields[$key] = mysqli_real_escape_string($conn, $value);
            }
        }
        
        $checkQuery = "SELECT * FROM product_duplicate2 WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $checkQuery);
        
        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE product_duplicate2 SET ";
            
            foreach ($fields as $column => $value) {
                $columnExists = mysqli_query($conn, "SHOW COLUMNS FROM product_duplicate2 LIKE '$column'");
                if (mysqli_num_rows($columnExists) > 0) {
                    $updateQuery .= "$column = '$value', ";
                }
            }
            
            $updateQuery = rtrim($updateQuery, ", ");
            $updateQuery .= " WHERE product_id = '$product_id'";
            
            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating product: " . mysqli_error($conn);
            }
        } else {
            $columns = [];
            $values = [];
            
            foreach ($fields as $column => $value) {
                $columnExists = mysqli_query($conn, "SHOW COLUMNS FROM product_duplicate2 LIKE '$column'");
                if (mysqli_num_rows($columnExists) > 0) {
                    $columns[] = $column;
                    $values[] = "'$value'";
                }
            }
            
            $columnsStr = implode(", ", $columns);
            $valuesStr = implode(", ", $values);
            
            $insertQuery = "INSERT INTO product_duplicate2 (product_id, $columnsStr) VALUES ('$product_id', $valuesStr)";
            
            if (mysqli_query($conn, $insertQuery)) {
                $product_id = $conn->insert_id;
                echo "success_add";
            } else {
                echo "Error adding product: " . mysqli_error($conn);
            }
        }    
    }
    

    if ($action == "fetch_product_modal") {
        $product_id = mysqli_real_escape_string($conn, $_POST['id']);
        $checkQuery = "SELECT * FROM product_duplicate2 WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
        }

        ?>
        <div class="modal-body">
            <div class="card">
                <div class="card-body">
                    <input type="hidden" id="product_id" name="product_id" class="form-control" value="<?= $row['product_category'] ?? ''?>" />

                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label">Product Category</label>
                            <div class="mb-3">
                            <select id="product_category" class="form-control" name="product_category">
                                <option value="" >Select One...</option>
                                <?php
                                $query_roles = "SELECT * FROM product_category WHERE hidden = '0'";
                                $result_roles = mysqli_query($conn, $query_roles);            
                                while ($row_product_category = mysqli_fetch_array($result_roles)) {
                                    $selected = (($row['product_category'] ?? '') == $row_product_category['product_category_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_product_category['product_category_id'] ?>" data-category="<?= $row_product_category['product_category'] ?>" <?= $selected ?>><?= $row_product_category['product_category'] ?></option>
                                <?php   
                                }
                                ?>
                            </select>
                            </div>
                        </div>
                    </div>

                    
                    <div id="add-fields" class="<?= empty($row) ? 'd-none' : ''?>">
                        <div class="row pt-3">
                            <div class="col-md-4 trim-field">
                                <label class="form-label">Product System</label>
                                <div class="mb-3">
                                <select id="product_system" class="form-control add-category calculate" name="product_system">
                                    <option value="" >Select System...</option>
                                    <?php
                                    $query_system = "SELECT * FROM product_system WHERE hidden = '0'";
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
                            <div class="col-md-4 trim-field">
                                <label class="form-label">Product Line</label>
                                <div class="mb-3">
                                <select id="product_line" class="form-control add-category calculate" name="product_line">
                                    <option value="" >Select Line...</option>
                                    <?php
                                    $query_roles = "SELECT * FROM product_line WHERE hidden = '0'";
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
                            <div class="col-md-4 trim-field screw-fields">
                                <label class="form-label">Product Type</label>
                                <div class="mb-3">
                                <select id="product_type" class="form-control add-category calculate" name="product_type">
                                    <option value="" >Select Type...</option>
                                    <?php
                                    $query_roles = "SELECT * FROM product_type WHERE hidden = '0'";
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
                            <div class="col-md-4 trim-field">
                                <label class="form-label">Flat Sheet Width</label>
                                <div class="mb-3">
                                <select id="flat_sheet_width" class="form-control readonly" name="flat_sheet_width">
                                    <option value="">0</option>
                                    <?php
                                    $query_flat_sheet_width = "SELECT * FROM flat_sheet_width WHERE hidden = '0'";
                                    $result_flat_sheet_width = mysqli_query($conn, $query_flat_sheet_width);            
                                    while ($row_flat_sheet_width = mysqli_fetch_array($result_flat_sheet_width)) {
                                        $selected = (($row['flat_sheet_width'] ?? '') == $row_flat_sheet_width['width']) ? 'selected' : '';
                                    ?>
                                        <option 
                                            value="<?= $row_flat_sheet_width['width'] ?>"
                                            data-category="<?= $row_flat_sheet_width['product_category'] ?>"
                                            data-system="<?= $row_flat_sheet_width['product_system'] ?>"
                                            data-line="<?= $row_flat_sheet_width['product_line'] ?>"
                                            data-type="<?= $row_flat_sheet_width['product_type'] ?>"
                                            <?= $selected ?>
                                        >
                                                <?= $row_flat_sheet_width['width'] ?>
                                        </option>
                                    <?php   
                                    }
                                    ?>
                                </select>
                                </div>
                            </div>
                            <div class="col-md-4 trim-field">
                                <div class="mb-3">
                                <label class="form-label">Current Retail Price</label>
                                <input type="text" id="current_retail_price" name="current_retail_price" class="form-control calculate" value="<?=$row['current_retail_price']?>" />
                                </div>
                            </div>
                            <div class="col-md-4 trim-field">
                                <div class="mb-3">
                                    <label class="form-label">$ per inch</label>
                                    <input type="text" id="cost_per_sq_in" name="cost_per_sq_in" class="form-control readonly" value="<?=$row['cost_per_sq_in']?>"/>
                                </div>
                            </div>
                            <div class="col-md-4 trim-field">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-label mb-1">Grade</label>
                                    </div>
                                    <select id="grade" class="form-control calculate" name="grade">
                                        <option value="" >Select Grade...</option>
                                        <?php
                                        $query_grade = "SELECT * FROM product_grade WHERE hidden = '0'";
                                        $result_grade = mysqli_query($conn, $query_grade);            
                                        while ($row_grade = mysqli_fetch_array($result_grade)) {
                                            $selected = (($row['grade'] ?? '') == $row_grade['product_grade']) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $row_grade['product_grade'] ?>" data-multiplier="<?= $row_grade['multiplier'] ?>" <?= $selected ?>><?= $row_grade['product_grade'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 trim-field">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-label mb-1">Gauge</label>
                                    </div>
                                    <select id="gauge" class="form-control calculate" name="gauge">
                                        <option value="" >Select Gauge...</option>
                                        <?php
                                        $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0'";
                                        $result_gauge = mysqli_query($conn, $query_gauge);            
                                        while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                            $selected = (($row['gauge'] ?? '') == $row_gauge['product_gauge']) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $row_gauge['product_gauge'] ?>" data-multiplier="<?= $row_gauge['multiplier'] ?>" <?= $selected ?>><?= $row_gauge['product_gauge'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 trim-field screw-fields">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-label">Color</label>
                                    </div>
                                    <select id="color" class="form-control add-category calculate" name="color">
                                        <option value="" >Select Color...</option>
                                        <?php
                                        $query_colors = "SELECT * FROM product_color";
                                        $result_colors = mysqli_query($conn, $query_colors);            
                                        while ($row_colors = mysqli_fetch_array($result_colors)) {
                                            $selected = (($row['color'] ?? '') == $row_colors['id']) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $row_colors['id'] ?>" 
                                                    data-price="<?=$row_colors['price'] ?>" 
                                                    data-grade="<?=$row_colors['grade'] ?>" 
                                                    data-gauge="<?=$row_colors['gauge'] ?>" 
                                                    data-category="<?=trim($row_colors['product_category']) ?>" 
                                                    data-multiplier="<?= getProductColorMultValue($row_colors['color_mult_id']) ?>"
                                                    <?= $selected ?>
                                            >
                                                        <?= $row_colors['color_name'] ?>
                                            </option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 trim-field">
                                <div class="mb-3">
                                    <label class="form-label">Trim Multiplier</label>
                                    <input type="text" id="trim_multiplier" name="trim_multiplier" class="form-control readonly" value="<?=$row['trim_multiplier']?>"/>
                                </div>
                            </div>
                            <div class="col-md-4 trim-field">
                                <div class="mb-3">
                                    <label class="form-label">Length (Feet)</label>
                                    <input type="text" id="length" name="length" class="form-control calculate" value="<?=$row['length']?>"/>
                                </div>
                            </div>
                            <div class="col-md-4 trim-field">
                                <div class="mb-3">
                                    <label class="form-label">Retail Cost</label>
                                    <input type="text" id="retail_cost" name="retail_cost" class="form-control readonly" value="<?=$row['retail_cost']?>"/>
                                </div>
                            </div>

                            <div class="col-md-4 screw-fields">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-label">Size</label>
                                    </div>
                                    <select id="size" class="form-control calculate" name="size">
                                        <option value="0" hidden>0</option>
                                        <option value="1" <?= ($row['size'] ?? '') == 1 ? 'selected' : '' ?>>1</option>
                                        <option value="1.5" <?= ($row['size'] ?? '') == 1.5 ? 'selected' : '' ?>>1.5</option>
                                        <option value="2" <?= ($row['size'] ?? '') == 2 ? 'selected' : '' ?>>2</option>
                                        <option value="2.5" <?= ($row['size'] ?? '') == 2.5 ? 'selected' : '' ?>>2.5</option>
                                        <option value="3" <?= ($row['size'] ?? '') == 3 ? 'selected' : '' ?>>3</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4 screw-fields">
                                <label class="form-label">Supplier</label>
                                <div class="mb-3">
                                    <select id="supplier_id" class="form-control select-2 inventory_supplier" name="supplier_id">
                                        <option value="" >Select Supplier...</option>
                                        <optgroup label="Supplier">
                                            <?php
                                            $query_supplier = "SELECT * FROM supplier";
                                            $result_supplier = mysqli_query($conn, $query_supplier);            
                                            while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                                                $selected = (($row['supplier_id'] ?? '') == $row_supplier['supplier_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $row_supplier['supplier_id'] ?>" <?= $selected ?>><?= $row_supplier['supplier_name'] ?></option>
                                            <?php   
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4 screw-fields">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="form-label">Pack</label>
                                </div>
                                <div class="mb-3">
                                <select id="pack" class="form-control select-2 pack_select calculate" name="pack">
                                    <option value="0" >Select Pack...</option>
                                </select>
                                </div>
                            </div>

                            <div class="col-md-4 screw-fields">
                                <div class="mb-3">
                                    <label class="form-label">Cost</label>
                                    <input type="text" id="cost" name="cost" class="form-control calculate" value="<?=$row['cost'] ?? ''?>"/>
                                </div>
                            </div>

                            <div class="col-md-4 screw-fields">
                                <div class="mb-3">
                                    <label class="form-label">Price</label>
                                    <input type="text" id="price" name="price" class="form-control readonly" value="<?=$row['price'] ?? ''?>"/>
                                </div>
                            </div>

                            <div class="col-md-4 trim-field screw-fields">
                                <div class="mb-3">
                                    <label class="form-label">Retail Price</label>
                                    <input type="text" id="retail" name="retail" class="form-control" value="<?=$row['retail'] ?? ''?>"/>
                                </div>
                            </div>

                            <div class="col-md-12 trim-field screw-fields">
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <input type="text" id="description" name="description" class="form-control" value="<?=$row['description']?>"/>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="form-actions">
                                <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                                <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                    

                </div>
            </div>
        </div>
        <script>
            $('.readonly').on('mousedown', function(e) {
                e.preventDefault();
            });
        </script>
        <?php
    }

    if ($action == "fetch_view_modal") {
        $product_id = mysqli_real_escape_string($conn, $_POST['id']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_duplicate2 WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            
            ?>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">
                            Add Product
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div id="update_product" class="form-horizontal">
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body">

                                    <div class="card card-body">
                                        <h4 class="card-title text-center">Product Image</h4>
                                        <div class="row pt-3">
                                            <?php
                                            $query_img = "SELECT * FROM product_images WHERE productid = '$product_id'";
                                            $result_img = mysqli_query($conn, $query_img); 
                                            if(mysqli_num_rows($result_img) > 0){
                                                while ($row_img = mysqli_fetch_array($result_img)) {
                                                ?>
                                                <div class="col-md">
                                                    <img src="<?= $row_img['image_url'] ?>" class="img-fluid" alt="Product Image" />
                                                </div>
                                                <?php
                                                }
                                            }else{
                                            ?>
                                            <p class="mb-0 fs-3 text-center">No image found.</p>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Product Name:</label>
                                                <p><?= $row['product_item'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Product SKU:</label>
                                                <p><?= $row['product_sku'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Product Category:</label>
                                                <p><?= getProductCategoryName($row['product_category']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Product Line:</label>
                                                <p><?= getProductLineName($row['product_line']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Product Type:</label>
                                                <p><?= getProductTypeName($row['product_type']) ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Description:</label>
                                        <p><?= $row['description'] ?></p>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Correlated Products:</label>
                                            <ul>
                                                <?php
                                                $correlated_product_ids = [];
                                                $product_id = mysqli_real_escape_string($conn, $row['product_id']);
                                                $query_correlated = "SELECT correlated_id FROM correlated_product WHERE main_correlated_product_id = '$product_id'";
                                                $result_correlated = mysqli_query($conn, $query_correlated);
                                                
                                                while ($row_correlated = mysqli_fetch_assoc($result_correlated)) {
                                                    $correlated_product_ids[] = $row_correlated['correlated_id'];
                                                }
                                                foreach ($correlated_product_ids as $correlated_id) {
                                                    // Assuming you fetch the correlated product name
                                                    echo "<li>" .getProductName($correlated_id) ."</li>";
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Stock Type:</label>
                                                <p><?= getStockTypeName($row['stock_type']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Material:</label>
                                                <p><?= $row['material'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Dimensions:</label>
                                                <p><?= $row['dimensions'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Thickness:</label>
                                                <p><?= $row['thickness'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Gauge:</label>
                                                <p><?= getGaugeName($row['gauge']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Grade:</label>
                                                <p><?= getGradeName($row['grade']) ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Color:</label>
                                                <p><?= getColorName($row['color']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Paint Provider:</label>
                                                <p><?= getPaintProviderName($row['paint_provider']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Coating:</label>
                                                <p><?= $row['coating'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Warranty Type:</label>
                                                <p><?= getWarrantyTypeName($row['warranty_type']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Profile:</label>
                                                <p><?= getProfileTypeName($row['profile']) ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Width:</label>
                                                <p><?= $row['width'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Length:</label>
                                                <p><?= $row['length'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Weight:</label>
                                                <p><?= $row['weight'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Unit of Measure:</label>
                                                <p><?= $row['unit_of_measure'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Unit Price:</label>
                                                <p><?= $row['unit_price'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Unit Cost:</label>
                                                <p><?= $row['unit_cost'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Unit Gross Margin:</label>
                                                <p><?= $row['unit_gross_margin'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Usage:</label>
                                                <p><?= $row['product_usage'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">UPC:</label>
                                                <p><?= $row['upc'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-1">
                                                <label class="form-label">Sold By Feet:</label>
                                                <p><?= $row['sold_by_feet'] == 1 ? 'Yes' : 'No' ?></p>
                                            </div>
                                            <div class="mb-1">
                                                <label class="form-label">Standing Seam Panel:</label>
                                                <p><?= $row['standing_seam'] == 1 ? 'Yes' : 'No' ?></p>
                                            </div>
                                            <div class="mb-1">
                                                <label class="form-label">Board & Batten Panel:</label>
                                                <p><?= $row['board_batten'] == 1 ? 'Yes' : 'No' ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Comment:</label>
                                        <p><?= $row['comment'] ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.modal-content -->
            </div>
            <script>
                $(document).ready(function() {
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
                        const fileInput = document.getElementById('picture_path_update');
                        const dataTransfer = new DataTransfer();

                        uploadedUpdateFiles.forEach(file => {
                            const fileBlob = new Blob([file], { type: file.type });
                            dataTransfer.items.add(new File([fileBlob], file.name, { type: file.type }));
                        });

                        fileInput.files = dataTransfer.files;
                    }

                    function displayFileNames2() {
                        let files = document.getElementById('picture_path_update').files;
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
                });

            </script>

            <?php
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
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product_duplicate2 SET status = '$new_status' WHERE product_id = '$product_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }

    if ($action == 'hide_category') {
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
        $query = "UPDATE product_duplicate2 SET hidden='1' WHERE product_id='$product_id'";
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

    if ($action == "fetch_uploaded_modal") {
        $table = "test";
        
        $sql = "SELECT * FROM $table";
        $result = $conn->query($sql);
    
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $columns = array_keys($row);
            $result->data_seek(0);
    
            $columnsWithData = [];
            while ($row = $result->fetch_assoc()) {
                foreach ($columns as $column) {
                    if (!empty($row[$column])) {
                        $columnsWithData[$column] = true;
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
                                    foreach ($columns as $column) {
                                        if (isset($columnsWithData[$column])) {
                                            $formattedColumn = ucwords(str_replace('_', ' ', $column));
                                            echo "<th class='fs-4'>" . $formattedColumn . "</th>";
                                        }
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                while ($row = $result->fetch_assoc()) {
                                    $product_id = $row['product_id'];
                                    echo '<tr>';
                                    foreach ($columns as $column) {
                                        if (isset($columnsWithData[$column])) {
                                            $value = $row[$column] ?? '';
                                            if ($column == 'product_category') {
                                                ?>
                                                <td contenteditable="false" data-header-name="<?= $column ?>" data-id="<?=$product_id?>">
                                                    <?= getProductCategoryName($value) ?>
                                                </td>
                                                <?php
                                            } else if ($column == 'product_system') {
                                                ?>
                                                <td contenteditable="false" data-header-name="<?= $column ?>" data-id="<?=$product_id?>">
                                                    <?= getProductSystemName($value) ?>
                                                </td>
                                                <?php
                                            } else if ($column == 'product_type') {
                                                ?>
                                                <td contenteditable="false" data-header-name="<?= $column ?>" data-id="<?=$product_id?>">
                                                    <?= getProductTypeName($value) ?>
                                                </td>
                                                <?php
                                            } else if ($column == 'product_line') {
                                                ?>
                                                <td contenteditable="false" data-header-name="<?= $column ?>" data-id="<?=$product_id?>">
                                                    <?= getProductLineName($value) ?>
                                                </td>
                                                <?php
                                            } else if ($column == 'color') {
                                                ?>
                                                <td contenteditable="false" data-header-name="<?= $column ?>" data-id="<?=$product_id?>">
                                                    <?= getColorName($value) ?>
                                                </td>
                                                <?php
                                            } else {
                                                echo "<td contenteditable='true' class='table_data' data-header-name='".$column."' data-id='".$product_id."'>$value</td>";
                                            }
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
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
    
            $table_test = 'test';
    
            if ($fileExtension != "xlsx" && $fileExtension != "xls") {
                echo "Please upload a valid Excel file.";
                exit;
            }
    
            $spreadsheet = IOFactory::load($fileTmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
    
            $columns = $rows[0];
            $dbColumns = [];
            $columnMapping = [];
    
            foreach ($columns as $col) {
                $dbColumn = strtolower(str_replace(' ', '_', $col));
                $dbColumns[] = $dbColumn;
                $columnMapping[$dbColumn] = $col;
            }
    
            $truncateSql = "TRUNCATE TABLE $table_test";
            $truncateResult = $conn->query($truncateSql);
    
            if (!$truncateResult) {
                echo "Error truncating table: " . $conn->error;
                exit;
            }
    
            foreach ($rows as $index => $row) {
                if ($index == 0) {
                    continue;
                }
    
                $data = array_combine($dbColumns, $row);
    
                $columnNames = implode(", ", array_keys($data));
                $columnValues = implode("', '", array_map(function($value) { return $value ?? ''; }, array_values($data)));
    
                $sql = "INSERT INTO $table_test ($columnNames) VALUES ('$columnValues')";
                $result = $conn->query($sql);
    
                if (!$result) {
                    echo "Error inserting data: " . $conn->error;
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
        $table = "product_duplicate2";
    
        $columnsSql = "SHOW COLUMNS FROM test";
        $columnsResult = $conn->query($columnsSql);
    
        $columns = [];
        while ($row = $columnsResult->fetch_assoc()) {
            if ($row['Field'] !== 'product_id') {
                $columns[] = $row['Field'];
            }
        }
    
        $columnsList = implode(", ", $columns);
    
        $sql = "INSERT INTO $table ($columnsList) SELECT $columnsList FROM test";
    
        if ($conn->query($sql) === TRUE) {
            echo "Data has been successfully saved";
    
            $truncateSql = "TRUNCATE TABLE test";
            if ($conn->query($truncateSql) !== TRUE) {
                echo " but failed to clear test table: " . $conn->error;
            }
        } else {
            echo "Error: " . $conn->error;
        }
    }

    if ($action == "download_excel") {
        $product_category = mysqli_real_escape_string($conn, $_REQUEST['category'] ?? '');
        $category_name = strtoupper(getProductCategoryName($product_category));
    
        $includedColumns = array();
        $column_txt = '*';
    
        if($product_category == 4){ // TRIM
            $includedColumns = [ 
                'product_id',
                'product_category',
                'product_system',
                'product_line',
                'product_type',
                'flat_sheet_width',
                'current_retail_price',
                'cost_per_sq_in',
                'grade',
                'gauge',
                'color',
                'trim_multiplier',
                'length',
                'retail_cost',
                'description',
                'retail'
            ];
            $column_txt = implode(', ', $includedColumns);
        } else if($product_category == 16){ // SCREW
            $includedColumns = [ 
                'product_id',
                'product_category',
                'product_type',
                'color',
                'size',
                'supplier_id',
                'pack',
                'cost',
                'price',
                'retail',
                'description'
            ];
            $column_txt = implode(', ', $includedColumns);
        }
    
        $sql = "SELECT " . $column_txt . " FROM product_duplicate2";
        if (!empty($product_category)) {
            $sql .= " WHERE product_category = '$product_category'";
        }
        $result = $conn->query($sql);
    
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        $headers = [];
        $row = 1;
        
        foreach ($includedColumns as $index => $column) {
            $header = ucwords(str_replace('_', ' ', $column));
            $columnLetter = chr(65 + $index);
            $headers[$columnLetter] = $header;
            $sheet->setCellValue($columnLetter . $row, $header);
        }
    
        $row = 2;
        while ($data = $result->fetch_assoc()) {
            foreach ($includedColumns as $index => $column) {
                $columnLetter = chr(65 + $index);
                $sheet->setCellValue($columnLetter . $row, $data[$column] ?? '');
            }
            $row++;
        }
    
        $filename = "$category_name.xlsx";
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
        $product_id = $_POST['id'];
        
        if (empty($column_name) || empty($product_id)) {
            exit;
        }
        
        $column_name = mysqli_real_escape_string($conn, $column_name);
        $new_value = mysqli_real_escape_string($conn, $new_value);
        $product_id = mysqli_real_escape_string($conn, $product_id);
        
        $sql = "UPDATE test SET `$column_name` = '$new_value' WHERE product_id = '$product_id'";

        if ($conn->query($sql) === TRUE) {
            echo 'Success';
        } else {
            echo 'Error updating record: ' . $conn->error;
        }
    }
    
    mysqli_close($conn);
}
?>
