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
        $product_sku = mysqli_real_escape_string($conn, $_POST['product_sku']);
        $product_item = mysqli_real_escape_string($conn, $_POST['product_item']);
        $product_category = mysqli_real_escape_string($conn, $_POST['product_category']);
        $product_line = mysqli_real_escape_string($conn, $_POST['product_line']);
        $product_type = mysqli_real_escape_string($conn, $_POST['product_type']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $stock_type = mysqli_real_escape_string($conn, $_POST['stock_type']);
        $material = mysqli_real_escape_string($conn, $_POST['material']);
        $dimensions = mysqli_real_escape_string($conn, $_POST['dimensions']);
        $thickness = mysqli_real_escape_string($conn, $_POST['thickness']);
        $gauge = mysqli_real_escape_string($conn, $_POST['gauge']);
        $grade = mysqli_real_escape_string($conn, $_POST['grade']);
        $color = mysqli_real_escape_string($conn, $_POST['color']);
        $paint_provider = mysqli_real_escape_string($conn, $_POST['paintProvider']);
        $warranty_type = mysqli_real_escape_string($conn, $_POST['warrantyType']);
        $coating = mysqli_real_escape_string($conn, $_POST['coating']);
        $profile = mysqli_real_escape_string($conn, $_POST['profile']);
        $width = mysqli_real_escape_string($conn, $_POST['width']);
        $length = mysqli_real_escape_string($conn, $_POST['length']);
        $weight = mysqli_real_escape_string($conn, $_POST['weight']);
        $unit_price = mysqli_real_escape_string($conn, $_POST['unitPrice']);
        $upc = mysqli_real_escape_string($conn, $_POST['upc']);
        $unit_of_measure = mysqli_real_escape_string($conn, $_POST['unitofMeasure']);
        $unit_cost = mysqli_real_escape_string($conn, $_POST['unitCost']);
        $unit_gross_margin = mysqli_real_escape_string($conn, $_POST['unitGrossMargin']);
        $product_usage = mysqli_real_escape_string($conn, $_POST['product_usage']);
        $sold_by_feet = isset($_POST['sold_by_feet']) ? 1 : 0;
        $standing_seam = isset($_POST['standing_seam']) ? 1 : 0;
        $board_batten = isset($_POST['board_batten']) ? 1 : 0;
        $comment = mysqli_real_escape_string($conn, $_POST['comment']);
        $product_origin = mysqli_real_escape_string($conn, $_POST['product_origin']);

        $correlatedProducts = $_POST['correlatedProducts'];
    
        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_duplicate2 WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $checkQuery);
        $isInsert = false;
    
        if (mysqli_num_rows($result) > 0) {
            // Record exists, proceed with update
            $isInsert = false;
            $updateQuery = "UPDATE product_duplicate2 SET 
                product_item = '$product_item', 
                product_sku = '$product_sku', 
                product_category = '$product_category', 
                product_line = '$product_line', 
                product_type = '$product_type', 
                description = '$description', 
                stock_type = '$stock_type', 
                material = '$material', 
                dimensions = '$dimensions', 
                thickness = '$thickness', 
                gauge = '$gauge', 
                grade = '$grade', 
                color = '$color', 
                paint_provider = '$paint_provider', 
                warranty_type = '$warranty_type', 
                coating = '$coating', 
                profile = '$profile', 
                width = '$width', 
                length = '$length', 
                weight = '$weight', 
                unit_price = '$unit_price', 
                upc = '$upc', 
                unit_of_measure = '$unit_of_measure', 
                unit_cost = '$unit_cost', 
                unit_gross_margin = '$unit_gross_margin', 
                product_usage = '$product_usage',
                sold_by_feet = '$sold_by_feet',
                standing_seam = '$standing_seam',
                board_batten = '$board_batten',
                product_origin = '$product_origin',
                comment = '$comment' 
            WHERE product_id = '$product_id'";
    
            if (mysqli_query($conn, $updateQuery)) {
                
                $query_delete = "DELETE FROM correlated_product WHERE main_correlated_product_id = '$product_id'";
                if (!mysqli_query($conn, $query_delete)) {
                    echo "Error: " . mysqli_error($conn);
                }else{
                    foreach ($correlatedProducts as $correlated_product_id) {
                        $query_correlated = "INSERT INTO correlated_product (`correlated_id`, `main_correlated_product_id`) VALUES ('$correlated_product_id','$product_id')";
                        if (mysqli_query($conn, $query_correlated)) {
                        } else {
                            echo "Error: " . mysqli_error($conn);
                        }    
                    }
                }

                echo "success";
            } else {
                echo "Error updating product: " . mysqli_error($conn);
            }
    
        } else {
            $upc = generateRandomUPC();
            // Record does not exist, proceed with insert
            $isInsert = true;
            $insertQuery = "INSERT INTO product_duplicate2 (
                product_item, 
                product_sku, 
                product_category, 
                product_line, 
                product_type, 
                description, 
                stock_type, 
                material, 
                dimensions, 
                thickness, 
                gauge, 
                grade, 
                color, 
                paint_provider, 
                warranty_type, 
                coating, 
                profile, 
                width, 
                length, 
                weight, 
                quantity_in_stock, 
                quantity_quoted, 
                quantity_committed, 
                quantity_available, 
                quantity_in_transit, 
                unit_price, 
                date_added, 
                date_modified, 
                last_ordered_date, 
                last_sold_date, 
                upc, 
                unit_of_measure, 
                unit_cost, 
                unit_gross_margin, 
                product_usage,
                sold_by_feet,
                standing_seam,
                board_batten,
                product_origin,
                comment
            ) VALUES (
                '$product_item', 
                '$product_sku', 
                '$product_category', 
                '$product_line', 
                '$product_type', 
                '$description', 
                '$stock_type', 
                '$material', 
                '$dimensions', 
                '$thickness', 
                '$gauge', 
                '$grade', 
                '$color', 
                '$paint_provider', 
                '$warranty_type', 
                '$coating', 
                '$profile', 
                '$width', 
                '$length', 
                '$weight', 
                '$quantity_in_stock', 
                '$quantity_quoted', 
                '$quantity_committed', 
                '$quantity_available', 
                '$quantity_in_transit', 
                '$unit_price', 
                '$date_added', 
                '$date_modified', 
                '$last_ordered_date', 
                '$last_sold_date', 
                '$upc', 
                '$unit_of_measure', 
                '$unit_cost', 
                '$unit_gross_margin', 
                '$product_usage', 
                '$sold_by_feet',
                '$standing_seam',
                '$board_batten',
                '$product_origin',
                '$comment'
            )";
    
            if (mysqli_query($conn, $insertQuery)) {
                $product_id = $conn->insert_id;
                $query_delete = "DELETE FROM correlated_product WHERE main_correlated_product_id = '$product_id'";
                if (!mysqli_query($conn, $query_delete)) {
                    echo "Error: " . mysqli_error($conn);
                }else{
                    foreach ($correlatedProducts as $correlated_product_id) {
                        $query_correlated = "INSERT INTO correlated_product (`correlated_id`, `main_correlated_product_id`) VALUES ('$correlated_product_id','$product_id')";
                        if (mysqli_query($conn, $query_correlated)) {
                        } else {
                            echo "Error: " . mysqli_error($conn);
                        }    
                    }
                }

                echo "success";
            } else {
                echo "Error adding product: " . mysqli_error($conn);
            }
        }

        if (!empty($_FILES['picture_path']['name'][0])) {
            if (is_array($_FILES['picture_path']['name']) && count($_FILES['picture_path']['name']) > 0) {
                $uploadFileDir = '../images/product/';
                
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
        
                        // Update main image only for the first file (or as per your logic)
                        if ($i == 0) {
                            $sql = "UPDATE product_duplicate2 SET main_image='images/product/$newFileName' WHERE product_id='$product_id'";
                            if (!$conn->query($sql)) {
                                echo "Error updating record: " . $conn->error;
                            }
                        }
        
                        // Insert all images into the product_images table
                        $sql = "INSERT INTO product_images (productid, image_url) VALUES ('$product_id', 'images/product/$newFileName')";
                        if (!$conn->query($sql)) {
                            echo "Error updating record: " . $conn->error;
                        }
                    } else {
                        echo 'Error moving the file to the upload directory.';
                    }
                }
            } else {
                // Handle case where no files are uploaded (if needed)
                echo "No files were uploaded.";
            }
        } else {
            if ($isInsert) {
                $sql = "UPDATE product_duplicate2 SET main_image='images/product/product.jpg' WHERE product_id='$product_id'";
                if (!$conn->query($sql)) {
                    echo "Error updating record: " . $conn->error;
                }
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
                    <input type="hidden" id="product_id" name="product_id" class="form-control"  />

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
                                ?>
                                    <option value="<?= $row_product_category['product_category_id'] ?>" data-category="<?= $row_product_category['product_category'] ?>" <?= $selected ?>><?= $row_product_category['product_category'] ?></option>
                                <?php   
                                }
                                ?>
                            </select>
                            </div>
                        </div>
                    </div>
                    
                    <div id="add-fields" class="d-none">
                        <div class="row pt-3">
                            <div class="col-md-4">
                                <label class="form-label">Product System</label>
                                <div class="mb-3">
                                <select id="product_system" class="form-control add-category calculate" name="product_system">
                                    <option value="" >Select System...</option>
                                    <?php
                                    $query_system = "SELECT * FROM product_system WHERE hidden = '0'";
                                    $result_system = mysqli_query($conn, $query_system);
                                    while ($row_system = mysqli_fetch_array($result_system)) {
                                        $selected = ($product_system == $row_system['product_system_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_system['product_system_id'] ?>" data-category="<?= $row_system['product_category'] ?>" <?= $selected ?>><?= $row_system['product_system'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Product Line</label>
                                <div class="mb-3">
                                <select id="product_line" class="form-control add-category calculate" name="product_line">
                                    <option value="" >Select Line...</option>
                                    <?php
                                    $query_roles = "SELECT * FROM product_line WHERE hidden = '0'";
                                    $result_roles = mysqli_query($conn, $query_roles);            
                                    while ($row_product_line = mysqli_fetch_array($result_roles)) {
                                    ?>
                                        <option value="<?= $row_product_line['product_line_id'] ?>" data-category="<?= $row_product_line['product_category'] ?>"><?= $row_product_line['product_line'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Product Type</label>
                                <div class="mb-3">
                                <select id="product_type" class="form-control add-category calculate" name="product_type">
                                    <option value="" >Select Type...</option>
                                    <?php
                                    $query_roles = "SELECT * FROM product_type WHERE product_category = '4'";
                                    $result_roles = mysqli_query($conn, $query_roles);            
                                    while ($row_product_type = mysqli_fetch_array($result_roles)) {
                                    ?>
                                        <option value="<?= $row_product_type['product_type_id'] ?>" data-category="<?= $row_product_type['product_category'] ?>"><?= $row_product_type['product_type'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </select>
                                </div>
                            </div>
                            <div class="col-md-4 opt_field" data-id="10">
                                <label class="form-label">Flat Sheet Width</label>
                                <div class="mb-3">
                                <select id="flat_sheet_width" class="form-control readonly" name="flat_sheet_width">
                                    <option value="">System, Line and Type Required</option>
                                    <?php
                                    $query_flat_sheet_width = "SELECT * FROM flat_sheet_width WHERE hidden = '0'";
                                    $result_flat_sheet_width = mysqli_query($conn, $query_flat_sheet_width);            
                                    while ($row_flat_sheet_width = mysqli_fetch_array($result_flat_sheet_width)) {
                                    ?>
                                        <option 
                                            value="<?= $row_flat_sheet_width['width'] ?>"
                                            data-category="<?= $row_flat_sheet_width['product_category'] ?>"
                                            data-system="<?= $row_flat_sheet_width['product_system'] ?>"
                                            data-line="<?= $row_flat_sheet_width['product_line'] ?>"
                                            data-type="<?= $row_flat_sheet_width['product_type'] ?>"
                                        >
                                                <?= $row_flat_sheet_width['width'] ?>
                                        </option>
                                    <?php   
                                    }
                                    ?>
                                </select>
                                </div>
                            </div>
                            <div class="col-md-4 opt_field" data-id="2">
                                <div class="mb-3">
                                <label class="form-label">Current Retail Price</label>
                                <input type="text" id="current_retail_price" name="current_retail_price" class="form-control calculate"  />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">$ per inch</label>
                                    <input type="text" id="cost_per_sq_in" name="cost_per_sq_in" class="form-control readonly" />
                                </div>
                            </div>
                            <div class="col-md-4 opt_field">
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
                                        ?>
                                            <option value="<?= $row_grade['product_grade'] ?>" data-multiplier="<?= $row_grade['multiplier'] ?>"><?= $row_grade['product_grade'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 opt_field">
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
                                        ?>
                                            <option value="<?= $row_gauge['product_gauge'] ?>" data-multiplier="<?= $row_gauge['multiplier'] ?>"><?= $row_gauge['product_gauge'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 opt_field">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-label mb-1">Color</label>
                                    </div>
                                    <select id="color" class="form-control d-none calculate" name="color">
                                        <option value="" >Select Color...</option>
                                        <?php
                                        $query_colors = "SELECT * FROM product_color";
                                        $result_colors = mysqli_query($conn, $query_colors);            
                                        while ($row_colors = mysqli_fetch_array($result_colors)) {
                                        ?>
                                            <option value="<?= $row_colors['id'] ?>" 
                                                    data-price="<?=$row_colors['price'] ?>" 
                                                    data-grade="<?=$row_colors['grade'] ?>" 
                                                    data-gauge="<?=$row_colors['gauge'] ?>" 
                                                    data-category="<?=trim($row_colors['product_category']) ?>" 
                                                    data-multiplier="<?= getProductColorMultValue($row_colors['color_mult_id']) ?>">
                                                        <?= $row_colors['color_name'] ?>
                                            </option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Trim Multiplier</label>
                                    <input type="text" id="trim_multiplier" name="trim_multiplier" class="form-control readonly" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Length (Feet)</label>
                                    <input type="text" id="length" name="length" class="form-control calculate" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Retail Cost</label>
                                    <input type="text" id="retail_cost" name="retail_cost" class="form-control readonly" />
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <input type="text" id="description" name="description" class="form-control" />
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

    if ($action == "fetch_uploaded_modal") {
        $table = "test";
    
        $headers = [
            'InvID' => ['label' => 'product_id'],
            'Coil/Part Number' => ['label' => 'coil_part_no'],
            'Description' => ['label' => ''],
            'Color' => ['label' => 'color'],
            'Qty' => ['label' => 'quantity_in_stock'],
            'Units' => ['label' => 'unit_of_measure'],
            'Notes' => ['label' => ''],
            'Price' => ['label' => 'price_1'],
            'Price2' => ['label' => 'price_2'],
            'Price3' => ['label' => 'price_3'],
            'Price4' => ['label' => 'price_4'],
            'Price5' => ['label' => 'price_5'],
            'Price6' => ['label' => 'price_6'],
            'Price7' => ['label' => 'price_7'],
            'Order' => ['label' => ''],
            'ItemType' => ['label' => ''],
            'VendorID' => ['label' => ''],
            'Cost' => ['label' => ''],
            'Blank 1' => ['label' => ''],
            'Blank 2' => ['label' => ''],
            'Color Options' => ['label' => ''],
            'Length Options' => ['label' => ''],
            'Product System Abbreviations' => ['label' => ''],
            'Product Systems' => ['label' => 'product_system'],
            'Category Abbreviations' => ['label' => ''],
            'Product Category' => ['label' => 'product_category'],
            'Line Abbreviations' => ['label' => ''],
            'Product Line' => ['label' => 'product_line'],
            'Type Abbreviations' => ['label' => ''],
            'Product Type' => ['label' => 'product_type'],
            'Product Item' => ['label' => 'product_item'],
            'Product Code Abbreviation' => ['label' => ''],
            'Item Abbreviation' => ['label' => ''],
            'COLOR CODE Table' => ['label' => ''],
            'Color Consolidation' => ['label' => ''],
            'LENGTH/Size Table CODE' => ['label' => ''],
            'Size Consolidation' => ['label' => ''],
            'Gauge' => ['label' => 'gauge'],
            'Product Code' => ['label' => 'product_code'],
            'Concatenated' => ['label' => ''],
            'Product Description' => ['label' => 'description'],
            'Size Scrub' => ['label' => ''],
            'Comments' => ['label' => 'comment'],
            'Blank 4' => ['label' => ''],
            'Intent? Usage?' => ['label' => 'product_usage'],
            'Width' => ['label' => ''],
            '# of Hems' => ['label' => ''],
            '# of Bends' => ['label' => ''],
            'Length' => ['label' => ''],
            'Thickness' => ['label' => ''],
            'What Size?' => ['label' => ''],
            'Stock or Special Order' => ['label' => 'stock_type'],
            'Mfg or Purchased' => ['label' => 'product_origin'],
            'SupplierName' => ['label' => 'supplier_id'],
            'Cost Example' => ['label' => ''],
            'Old System Description' => ['label' => ''],
            'Category Use-Area (Cousin) (Marketing-Sales?)' => ['label' => ''],
            'Category Type-Category (2nd-Cousin) (Marketing-Sales?)' => ['label' => ''],
            'Category Use-Application (3rd-Cousin) (Marketing-Sales?)' => ['label' => ''],
            'SPM1' => ['label' => ''],
            'SPM2' => ['label' => ''],
            'SPM3' => ['label' => ''],
            'SPM4' => ['label' => ''],
            'SPM5' => ['label' => ''],
            'SPM6' => ['label' => ''],
            'SPM7' => ['label' => ''],
            'Flat Sheet Width' => ['label' => ''],
            'Number of Bends' => ['label' => 'bends'],
            'Number of Hems' => ['label' => 'hems'],
            'Hemming Machine' => ['label' => ''],
            'Trim Rollformer' => ['label' => ''],
            '$ per Hem' => ['label' => 'cost_per_hem'],
            '$ Per Bend' => ['label' => 'cost_per_bend'],
            '$ per square inch' => ['label' => 'cost_per_square_inch'],
            '' => ['label' => ''],
            'Actual Moving Cost' => ['label' => ''],
            'Manual Assigned Cost' => ['label' => ''],
            'Retail Pricing' => ['label' => ''],
            'Retail Pricing $/ft' => ['label' => ''],
            'Contractor Pricing (Level 2)' => ['label' => ''],
            'Contractor Pricing (Level 3)' => ['label' => ''],
            'Contractor Pricing (Level 4)' => ['label' => ''],
            'Wholesale Pricing (Level 5)' => ['label' => ''],
            'Wholesale Pricing (Level 6)' => ['label' => ''],
            'Penetration Pricing (Level 7)' => ['label' => ''],
            'Retail' => ['label' => ''],
            'Contractor 1' => ['label' => ''],
            'Contractor 2' => ['label' => ''],
            'Low Rib Coil Width' => ['label' => 'coil_width'],
            'Number of Trim per sheet width' => ['label' => ''],
            'Drop' => ['label' => ''],
            'Ft or Each' => ['label' => ''],
            'Flat Sheet Length converted to Inches' => ['label' => ''],
            'Square Inches per piece' => ['label' => ''],
            'Full Flat Stock Width' => ['label' => ''],
            "$'s per square Inch" => ['label' => ''],
            '$ Per Piece' => ['label' => ''],
            '$ per piece' => ['label' => ''],
            'Flat Sheet Length (Ft)' => ['label' => ''],
            'Square Inches per piece' => ['label' => ''],
            '10' => ['label' => ''],
            '12' => ['label' => ''],
            '14' => ['label' => ''],
            '16' => ['label' => ''],
            '18' => ['label' => ''],
            '20' => ['label' => ''],
            '24' => ['label' => ''],
            'Full Flat Stock Width' => ['label' => ''],
            "#'s per square Inch" => ['label' => ''],
            '# of Pieces per sheet' => ['label' => ''],
            'Weight per piece' => ['label' => '']
        ];
    
        $sql = "SELECT * FROM $table";
        $result = $conn->query($sql);
    
        $output = [];
        ?>
        
        <div class="card card-body shadow">
            <form id="tableForm">
                <div style="overflow-x: auto; overflow-y: auto; max-height: 90vh; max-width: 100%;">
                    <table class="table table-bordered table-striped text-center">
                        <thead>
                            <tr>
                                <?php
                                foreach ($headers as $column => $header) {
                                    echo "<th class='fs-4'>" . ($column) . "</th>";
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            while ($row = $result->fetch_assoc()) {
                                $product_id = $row['product_id'];
                                echo '<tr>';
                                foreach ($headers as $column => $header) {
                                    $value = $row[$header['label']] ?? '';
                                    if($header['label'] == 'product_category'){
                                        ?>
                                        <td class="" contenteditable="false" data-header-name="<?= $header['label'] ?>" data-id="<?=$product_id?>">
                                            <?= getProductCategoryName($value) ?>
                                        </td>
                                        <?php
                                    } else if($header['label'] == 'product_system'){
                                        ?>
                                        <td class="" contenteditable="false" data-header-name="<?= $header['label'] ?>" data-id="<?=$product_id?>">
                                            <?= getProductSystemName($value) ?>
                                        </td>
                                        <?php
                                    } else if($header['label'] == 'product_type'){
                                        ?>
                                        <td class="" contenteditable="false" data-header-name="<?= $header['label'] ?>" data-id="<?=$product_id?>">
                                            <?= getProductTypeName($value) ?>
                                        </td>
                                        <?php
                                    } else if($header['label'] == 'product_line'){
                                        ?>
                                        <td class="" contenteditable="false" data-header-name="<?= $header['label'] ?>" data-id="<?=$product_id?>">
                                            <?= getProductLineName($value) ?>
                                        </td>
                                        <?php
                                    } else if($header['label'] == 'color'){
                                        ?>
                                        <td class="" contenteditable="false" data-header-name="<?= $header['label'] ?>" data-id="<?=$product_id?>">
                                            <?= getColorName($value) ?>
                                        </td>
                                        <?php
                                    } else {
                                        echo "<td contenteditable='true' class='table_data' data-header-name='".$header['label']."' data-id='".$product_id."'>$value</td>";
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
    }    
    

    if ($action == "fetch_edit_modal") {
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
                    <form id="update_product" method="POST" class="form-horizontal" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body">
                                    <input type="hidden" id="product_id" name="product_id" class="form-control"  value="<?= $row['product_id']?>"/>

                                    <div class="row">
                                        <div class="card-body p-0">
                                            <h4 class="card-title text-center">Product Image</h4>
                                                <p action="#" id="myUpdateDropzone" class="dropzone">
                                                    <div class="fallback">
                                                    <input type="file" id="picture_path_update" name="picture_path[]" class="form-control" style="display: none" multiple/>
                                                    </div>
                                                </p>
                                        </div>
                                    </div>
                                    
                                    <div class="card card-body m-2">
                                        <h5>Current Images</h5>
                                        <div class="row pt-3">
                                            <?php
                                            $query_img = "SELECT * FROM product_images WHERE productid = '$product_id'";
                                            $result_img = mysqli_query($conn, $query_img);            
                                            while ($row_img = mysqli_fetch_array($result_img)) {
                                                $image_id = $row_img['prodimgid'];
                                            ?>
                                            <div class="col-md-2 position-relative">
                                                <div class="mb-3">
                                                    <img src="<?= $row_img['image_url'] ?>" class="img-fluid" alt="Product Image" />
                                                    <button class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image-btn" data-image-id="<?= $image_id ?>">X</button>
                                                </div>
                                            </div>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Product Name</label>
                                                <input type="text" id="product_item" name="product_item" class="form-control" value="<?= $row['product_item']?>" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Product SKU</label>
                                                <input type="text" id="product_sku" name="product_sku" class="form-control" value="<?= $row['product_sku']?>" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Product Category</label>
                                                <select id="product_category_update" class="form-control" name="product_category">
                                                    <option value="">Select One...</option>
                                                    <?php
                                                    $query_roles = "SELECT * FROM product_category WHERE hidden = '0'";
                                                    $result_roles = mysqli_query($conn, $query_roles);            
                                                    while ($row_product_category = mysqli_fetch_array($result_roles)) {
                                                        $selected = ($row['product_category'] == $row_product_category['product_id']) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $row_product_category['product_category_id'] ?>" <?= $selected ?>><?= $row_product_category['product_category'] ?></option>
                                                    <?php   
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Product Line</label>
                                                <select id="product_line" class="form-control" name="product_line">
                                                    <option value="">Select One...</option>
                                                    <?php
                                                    $query_roles = "SELECT * FROM product_line WHERE hidden = '0'";
                                                    $result_roles = mysqli_query($conn, $query_roles);            
                                                    while ($row_product_line = mysqli_fetch_array($result_roles)) {
                                                        $selected = ($row['product_line'] == $row_product_line['product_line_id']) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $row_product_line['product_line_id'] ?>" <?= $selected ?>><?= $row_product_line['product_line'] ?></option>
                                                    <?php   
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Product Type</label>
                                                <select id="product_type" class="form-control" name="product_type">
                                                    <option value="">Select One...</option>
                                                    <?php
                                                    $query_roles = "SELECT * FROM product_type WHERE hidden = '0'";
                                                    $result_roles = mysqli_query($conn, $query_roles);            
                                                    while ($row_product_type = mysqli_fetch_array($result_roles)) {
                                                        $selected = ($row['product_type'] == $row_product_type['product_type_id']) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $row_product_type['product_type_id'] ?>" <?= $selected ?>><?= $row_product_type['product_type'] ?></option>
                                                    <?php   
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>



                                    <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="5"><?= $row['product_item']?></textarea>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-12">
                                        <label class="form-label">Correlated products</label>
                                        <select id="correlatedProducts" name="correlatedProducts[]" class="select2-update form-control" multiple="multiple">
                                            <optgroup label="Select Correlated Products">
                                                <?php
                                                $correlated_product_ids = [];
                                                $product_id = mysqli_real_escape_string($conn, $row['product_id']);
                                                $query_correlated = "SELECT correlated_id FROM correlated_product WHERE main_correlated_product_id = '$product_id'";
                                                $result_correlated = mysqli_query($conn, $query_correlated);
                                                
                                                while ($row_correlated = mysqli_fetch_assoc($result_correlated)) {
                                                    $correlated_product_ids[] = $row_correlated['correlated_id'];
                                                }
                                                
                                                $query_products = "SELECT * FROM product_duplicate2";
                                                $result_products = mysqli_query($conn, $query_products);            
                                                while ($row_products = mysqli_fetch_array($result_products)) {
                                                    $selected = in_array($row_products['product_id'], $correlated_product_ids) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= $row_products['product_id'] ?>" <?= $selected ?> ><?= $row_products['product_item'] ?></option>
                                                <?php   
                                                }
                                                ?>
                                            </optgroup>
                                        </select>
                                        </div>
                                    </div>  

                                    <div class="row pt-3">
                                    <div class="col-md-6 opt_field_update" data-id="1">
                                        <div class="mb-3">
                                        <label class="form-label">Stock Type</label>
                                        <select id="stock_type" class="form-control" name="stock_type">
                                            <option value="/" >Select Stock Type...</option>
                                            <?php
                                            $query_stock_type = "SELECT * FROM stock_type WHERE hidden = '0'";
                                            $result_stock_type = mysqli_query($conn, $query_stock_type);            
                                            while ($row_stock_type = mysqli_fetch_array($result_stock_type)) {
                                                $selected = ($row['stock_type'] == $row_stock_type['stock_type_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $row_stock_type['stock_type_id'] ?>" <?= $selected ?>><?= $row_stock_type['stock_type'] ?></option>
                                            <?php   
                                            }
                                            ?>
                                        </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 opt_field_update" data-id="2">
                                        <div class="mb-3">
                                        <label class="form-label">Material</label>
                                        <input type="text" id="material" name="material" class="form-control" value="<?= $row['material']?>" />
                                        </div>
                                    </div>
                                    </div>

                                    <div class="row pt-3">
                                    <div class="col-md-6 opt_field_update" data-id="3">
                                        <div class="mb-3">
                                        <label class="form-label">Dimensions</label>
                                        <input type="text" id="dimensions" name="dimensions" class="form-control" value="<?= $row['dimensions']?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-6 opt_field_update" data-id="4">
                                        <div class="mb-3">
                                        <label class="form-label">Thickness</label>
                                        <input type="text" id="thickness" name="thickness" class="form-control" value="<?= $row['thickness']?>" />
                                        </div>
                                    </div>
                                    </div>

                                    <div class="row pt-3">
                                    <div class="col-md-6 opt_field_update" data-id="5">
                                        <div class="mb-3">
                                        <label class="form-label">Gauge</label>
                                        <select id="gauge" class="form-control" name="gauge">
                                            <option value="/" >Select Gauge...</option>
                                            <?php
                                            $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0'";
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
                                    <div class="col-md-6 opt_field_update" data-id="6">
                                        <div class="mb-3">
                                        <label class="form-label">Grade</label>
                                        <select id="grade" class="form-control" name="grade">
                                            <option value="/" >Select Grade...</option>
                                            <?php
                                            $query_grade = "SELECT * FROM product_grade WHERE hidden = '0'";
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
                                    </div>

                                    <div class="row pt-3">
                                    <div class="col-md-4 opt_field_update" data-id="7">
                                        <div class="mb-3">
                                        <label class="form-label">Color</label>
                                        <select id="color" class="form-control" name="color">
                                            <option value="/" >Select Color...</option>
                                            <?php
                                            $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0'";
                                            $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                                            while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                                $selected = ($row['color'] == $row_paint_colors['color_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $row_paint_colors['color_id'] ?>" <?= $selected ?>><?= $row_paint_colors['color_name'] ?></option>
                                            <?php   
                                            }
                                            ?>
                                        </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 opt_field_update" data-id="8">
                                        <div class="mb-3">
                                        <label class="form-label">Paint Provider</label>
                                        <select id="paintProvider" class="form-control" name="paintProvider">
                                            <option value="/" >Select Color...</option>
                                            <?php
                                            $query_paint_providers = "SELECT * FROM paint_providers WHERE hidden = '0'";
                                            $result_paint_providers = mysqli_query($conn, $query_paint_providers);            
                                            while ($row_paint_providers = mysqli_fetch_array($result_paint_providers)) {
                                                $selected = ($row['paint_provider'] == $row_paint_providers['provider_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $row_paint_providers['provider_id'] ?>" <?= $selected ?>><?= $row_paint_providers['provider_name'] ?></option>
                                            <?php   
                                            }
                                            ?>
                                        </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 opt_field_update" data-id="17">
                                        <div class="mb-3">
                                        <label class="form-label">Coating</label>
                                        <input type="text" id="coating" name="coating" class="form-control" value="<?= $row['coating']?>" />
                                        </div>
                                    </div>
                                    </div>

                                    <div class="row pt-3">
                                    <div class="col-md-6 opt_field_update" data-id="9">
                                        <div class="mb-3">
                                        <label class="form-label">Warranty Type</label>
                                        <select id="warrantyType" class="form-control" name="warrantyType">
                                            <option value="/" >Select Warranty Type...</option>
                                            <?php
                                            $query_product_warranty_type = "SELECT * FROM product_warranty_type WHERE hidden = '0'";
                                            $result_product_warranty_type = mysqli_query($conn, $query_product_warranty_type);            
                                            while ($row_product_warranty_type = mysqli_fetch_array($result_product_warranty_type)) {
                                                $selected = ($row['warranty_type'] == $row_product_warranty_type['product_warranty_type_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $row_product_warranty_type['product_warranty_type_id'] ?>" <?= $selected ?>><?= $row_product_warranty_type['product_warranty_type'] ?></option>
                                            <?php   
                                            }
                                            ?>
                                        </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 opt_field_update" data-id="10">
                                        <div class="mb-3">
                                        <label class="form-label">Profile</label>
                                        <select id="profile" class="form-control" name="profile">
                                            <option value="/" >Select Profile...</option>
                                            <?php
                                            $query_profile_type = "SELECT * FROM profile_type WHERE hidden = '0'";
                                            $result_profile_type = mysqli_query($conn, $query_profile_type);            
                                            while ($row_profile_type = mysqli_fetch_array($result_profile_type)) {
                                                $selected = ($row['profile'] == $row_profile_type['profile_type_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $row_profile_type['profile_type_id'] ?>" <?= $selected ?>><?= $row_profile_type['profile_type'] ?></option>
                                            <?php   
                                            }
                                            ?>
                                        </select>
                                        </div>
                                    </div>
                                    </div>

                                    <div class="row pt-3">
                                    <div class="col-md-6 opt_field_update" data-id="11">
                                        <div class="mb-3">
                                        <label class="form-label">Width</label>
                                        <input type="text" id="width" name="width" class="form-control" value="<?= $row['width']?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-6 opt_field_update" data-id="12">
                                        <div class="mb-3">
                                        <label class="form-label">Length</label>
                                        <input type="text" id="length" name="length" class="form-control" value="<?= $row['length']?>" />
                                        </div>
                                    </div>
                                    </div>

                                    <div class="row pt-3">
                                    <div class="col-md-6 opt_field_update" data-id="13">
                                        <div class="mb-3">
                                        <label class="form-label">Weight</label>
                                        <input type="number" id="weight" name="weight" class="form-control" value="<?= $row['weight']?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-6 opt_field_update" data-id="14">
                                        <div class="mb-3">
                                        <label class="form-label">Unit of Measure</label>
                                        <input type="text" id="unitofMeasure" name="unitofMeasure" class="form-control" value="<?= $row['unit_of_measure']?>" />
                                        </div>
                                    </div>
                                    </div>

                                    <div class="row pt-3">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                        <label class="form-label">UnitPrice</label>
                                        <input type="text" id="unitPrice" name="unitPrice" class="form-control" value="<?= $row['unit_price']?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                        <label class="form-label">Unit Cost</label>
                                        <input type="text" id="unitCost" name="unitCost" class="form-control" value="<?= $row['unit_cost']?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                        <label class="form-label">Unit Gross Margin</label>
                                        <input type="text" id="unitGrossMargin" name="unitGrossMargin" class="form-control" value="<?= $row['unit_gross_margin']?>" />
                                        </div>
                                    </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6 opt_field_update" data-id="15">
                                            <div class="mb-3">
                                            <label class="form-label">Usage</label>
                                            <input type="text" id="product_usage" name="product_usage" class="form-control" value="<?= $row['product_usage']?>" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                            <label class="form-label">UPC</label>
                                            <input type="text" id="upc" name="upc" class="form-control" value="<?= $row['upc']?>" />
                                            </div>
                                        </div>
                                        
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6 opt_field" data-id="15">
                                            <div class="mb-3">
                                            <label class="form-label">Product Origin</label>
                                            <select id="product_origin" class="form-control" name="product_origin">
                                                <option value="" <?= empty($row['product_origin']) ? 'selected' : '' ?>>Select Origin...</option>
                                                <option value="1" <?= $row['product_origin'] == '1' ? 'selected' : '' ?>>Source</option>
                                                <option value="2" <?= $row['product_origin'] == '2' ? 'selected' : '' ?>>Manufactured</option>
                                            </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-1">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="sold_by_feet" name="sold_by_feet" value="1" <?= $row['sold_by_feet'] == 1 ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="sold_by_feet">Sold by feet</label>
                                                </div>
                                            </div>
                                            <div class="mb-1">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="standing_seam" name="standing_seam" value="1" <?= $row['standing_seam'] == 1 ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="standing_seam">Standing Seam Panel</label>
                                                </div>
                                            </div>
                                            <div class="mb-1">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="board_batten" name="board_batten" value="1" <?= $row['board_batten'] == 1 ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="board_batten">Board & Batten Panel</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3 opt_field_update" data-id="16">
                                    <label class="form-label">Comment</label>
                                    <textarea class="form-control" id="comment" name="comment" rows="5"><?= $row['comment']?></textarea>
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

    if ($action == 'fetch_table_data') {
        $color_id = isset($_REQUEST['color_id']) ? mysqli_real_escape_string($conn, $_REQUEST['color_id']) : '';
        $grade_id = isset($_REQUEST['grade_id']) ? mysqli_real_escape_string($conn, $_REQUEST['grade_id']) : '';
        $gauge_id = isset($_REQUEST['gauge_id']) ? mysqli_real_escape_string($conn, $_REQUEST['gauge_id']) : '';
        $category_id = isset($_REQUEST['category_id']) ? mysqli_real_escape_string($conn, $_REQUEST['category_id']) : '';
        $profile_id = isset($_REQUEST['profile_id']) ? mysqli_real_escape_string($conn, $_REQUEST['profile_id']) : '';
        $type_id = isset($_REQUEST['type_id']) ? mysqli_real_escape_string($conn, $_REQUEST['type_id']) : '';
        $onlyInStock = isset($_REQUEST['onlyInStock']) ? filter_var($_REQUEST['onlyInStock'], FILTER_VALIDATE_BOOLEAN) : false;
    
        $query_product="SELECT 
                            p.*,
                            COALESCE(SUM(i.quantity_ttl), 0) AS total_quantity
                        FROM 
                            product_duplicate2 AS p
                        LEFT JOIN 
                            inventory AS i ON p.product_id = i.product_id
                        WHERE 
                            p.hidden = '0'";
        if (!empty($color_id)) {
            $query_product .= " AND p.color = '$color_id'";
        }
        if (!empty($grade_id)) {
            $query_product .= " AND p.grade = '$grade_id'";
        }
        if (!empty($gauge_id)) {
            $query_product .= " AND p.gauge = '$gauge_id'";
        }
        if (!empty($type_id)) {
            $query_product .= " AND p.product_type = '$type_id'";
        }
        if (!empty($profile_id)) {
            $query_product .= " AND p.profile = '$profile_id'";
        }
        if (!empty($category_id)) {
            $query_product .= " AND p.product_category = '$category_id'";
        }
        $query_product .= " GROUP BY p.product_id";
        if ($onlyInStock) {
            $query_product .= " HAVING total_quantity > 1";
        }
        $result_product = mysqli_query($conn, $query_product);
        $no = 1;
        while ($row_product = mysqli_fetch_array($result_product)) {
            $product_id = $row_product['product_id'];
            $db_status = $row_product['status'];
            $status_icon = $db_status == '0' ? "text-danger ti ti-trash" : "text-warning ti ti-reload";
            $status = $db_status == '0'
                ? "<a href='#'><div class='alert alert-danger bg-danger text-white' role='alert'>Inactive</div></a>"
                : "<a href='#'><div class='alert alert-success bg-success text-white' role='alert'>Active</div></a>";
            
            $picture_path = !empty($row_product['main_image']) ? $row_product['main_image'] : "images/product/product.jpg";
            ?>
            <tr class="search-items">
                <td>
                    <a href="/?page=product_details&product_id=<?= $row_product['product_id'] ?>">
                        <div class="d-flex align-items-center">
                            <img src="<?= $picture_path ?>" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                            <div class="ms-3">
                                <h6 class="fw-semibold mb-0 fs-4"><?= $row_product['product_item'] ?></h6>
                            </div>
                        </div>
                    </a>
                </td>
                <td><?= $row_product['product_sku'] ?></td>
                <td><?= getProductCategoryName($row_product['product_category']) ?></td>
                <td><?= getProductLineName($row_product['product_line']) ?></td>
                <td><?= getProductTypeName($row_product['product_type']) ?></td>
                <td><?= $status ?></td>
                <td>
                    <div class="action-btn text-center">
                        <a href="#" id="view_product_btn" class="text-primary edit" data-id="<?= $row_product['product_id'] ?>">
                            <i class="text-primary ti ti-eye fs-7"></i>
                        </a>
                        <a href="#" id="edit_product_btn" class="text-warning edit" data-id="<?= $row_product['product_id'] ?>">
                            <i class="text-warning ti ti-pencil fs-7"></i>
                        </a>
                        <a href="#" id="delete_product_btn" class="text-danger edit changeStatus" data-id="<?= $product_id ?>" data-status='<?= $db_status ?>'>
                            <i class="text-danger ti ti-trash fs-7"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php
            $no++;
        }
    }

    if ($action == "upload_excel") {
        if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === 0) {
            $filePath = $_FILES['excel_file']['tmp_name'];
    
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
    
            $columnMapping = [
                'B' => 'coil_part_no',
                'D' => 'color',
                'E' => 'quantity_in_stock',
                'F' => 'unit_of_measure',
                'H' => 'price_1',
                'I' => 'price_2',
                'J' => 'price_3',
                'K' => 'price_4',
                'L' => 'price_5',
                'M' => 'price_6',
                'N' => 'price_7',
                'X' => 'product_system',
                'Z' => 'product_category',
                'AB' => 'product_line',
                'AD' => 'product_type',
                'AE' => 'product_item',
                'AL' => 'gauge',
                'AM' => 'product_code',
                'AO' => 'description',
                'AQ' => 'comment',
                'AS' => 'product_usage',
                'BO' => 'width',
                'AW' => 'length',
                'AX' => 'thickness',
                'AZ' => 'stock_type',
                'BA' => 'product_origin',
                'BB' => 'supplier_id',
                'BP' => 'bends',
                'BQ' => 'hems',
                'BR' => 'hemming_machine',
                'BS' => 'trim_rollformer',
                'BT' => 'cost_per_hem',
                'BU' => 'cost_per_bend',
                'CM' => 'coil_width',
            ];
    
            $truncateSql = "TRUNCATE TABLE $table";
            if (!mysqli_query($conn, $truncateSql)) {
                echo "Error truncating table: " . mysqli_error($conn) . "<br>";
                exit();
            }
    
            foreach ($worksheet->getRowIterator() as $rowIndex => $row) {
                if ($rowIndex === 1) continue;
                $rowData = [];
    
                $colorPrice = 0;
                $coil_width = 0;
                $width = 0;
    
                foreach ($row->getCellIterator() as $cell) {
                    $columnLetter = $cell->getColumn();
                    
                    if (isset($columnMapping[$columnLetter])) {
                        $dbColumn = $columnMapping[$columnLetter];
                        $cellValue = $cell->getValue() ?? '';
                        
    
                        if ($dbColumn === 'color') { //color
                            if(strtolower($cellValue) == 'multi'){
                                $query = "SELECT * FROM product_color WHERE product_category = '$category_id' ORDER BY RAND() LIMIT 1";
                                $result = mysqli_query($conn, $query);
    
                                if ($result && mysqli_num_rows($result) > 0) {
                                    $row = mysqli_fetch_assoc($result);
                                    $cellValue = $row['id'];
                                    $colorPrice = $row['price'];
                                }
                            }else{
                                $query = "SELECT * FROM product_color WHERE color_name LIKE '%$cellValue%'";
                                $result = mysqli_query($conn, $query);
                                if ($result && mysqli_num_rows($result) > 0) {
                                    $row = mysqli_fetch_assoc($result);
                                    $cellValue = $row['id'];
                                }
                            }
                        }
    
                        if ($dbColumn === 'product_system') { //product_system
                            $query = "SELECT product_system_id FROM product_system WHERE product_system LIKE '%$cellValue%'";
                            $result = mysqli_query($conn, $query);
    
                            if ($result && mysqli_num_rows($result) > 0) {
                                $row = mysqli_fetch_assoc($result);
                                $cellValue = $row['product_system_id'];
                            }
                        }
                        
                        if ($dbColumn === 'product_category') { //category
                            $cellValue = $category_id; //4 = TRIM
                        }
    
                        if ($dbColumn === 'product_line') { //product_line
                            $query = "SELECT product_line_id FROM product_line WHERE product_line LIKE '%$cellValue%'";
                            $result = mysqli_query($conn, $query);
    
                            if ($result && mysqli_num_rows($result) > 0) {
                                $row = mysqli_fetch_assoc($result);
                                $cellValue = $row['product_line_id'];
                            }
                        }
    
                        if ($dbColumn === 'product_type') { //product_type
                            $query = "SELECT product_type_id FROM product_type WHERE product_type LIKE '%$cellValue%'";
                            $result = mysqli_query($conn, $query);
    
                            if ($result && mysqli_num_rows($result) > 0) {
                                $row = mysqli_fetch_assoc($result);
                                $cellValue = $row['product_type_id'];
                            }
                        }
    
                        if ($dbColumn === 'product_origin') { //manufactured or sourced
                            $cellValue = (strtolower($cell->getValue() ?? '') == 'manufactured' ? '2' : '1'); //2 = manufactured
                        }
    
                        if ($dbColumn === 'supplier_id') { // supplier_id
                            $cellValue = strtolower($cell->getValue() ?? '');
                            $invalidValues = ['manufactured', 'n/a', '#n/a'];
                        
                            $ekm_id = 5; //from database
                            $cellValue = in_array($cellValue, $invalidValues) ? $ekm_id : $cellValue; // If in list, make empty; otherwise, set to '1'
    
                            if($cellValue != $ekm_id){
    
                            }
                            $query = "SELECT supplier_id FROM supplier WHERE supplier_name LIKE '%$cellValue%'";
                            $result = mysqli_query($conn, $query);
    
                            if ($result && mysqli_num_rows($result) > 0) {
                                $row = mysqli_fetch_assoc($result);
                                $cellValue = $row['supplier_id'];
                            }
                        }
    
                        if ($dbColumn === 'bends') { //bends
                            $cellValue = floatval($cell->getValue());
                        }
    
                        if ($dbColumn === 'hems') { //hems
                            $cellValue = floatval($cell->getValue());
                        }
    
                        if ($dbColumn === 'hemming_machine') { //hemming_machine
                            $cellValue = (strtolower($cell->getValue() ?? '') == 'yes' ? '1' : '0');
                        }
    
                        if ($dbColumn === 'trim_rollformer') { //trim_rollformer
                            $cellValue = (strtolower($cell->getValue() ?? '') == 'yes' ? '1' : '0');
                        }
    
                        if ($dbColumn === 'coil_width') { //coil_width
                            $cellValue = floatval($cell->getValue());
                            $coil_width = $cellValue;
                        }
    
                        if ($dbColumn === 'width') { //coil_width
                            $cellValue = floatval($cell->getValue());
                            $width = $cellValue;
                        }
                
                        $rowData[$dbColumn] = mysqli_real_escape_string($conn, $cellValue !== null ? (string) $cellValue : '');
                    }     
    
                }
    
                if (empty($rowData)) {
                    continue;
                }
    
                if($category_id = $trim_id){
                    $cost_per_square_inch = 0;
    
                    if ($coil_width > 0) {
                        $cost_per_square_inch = ($width / $coil_width) * $colorPrice;
                    }
    
                    $rowData['cost_per_square_inch'] = $cost_per_square_inch;
                }
    
                $dbColumns = implode(", ", array_keys($rowData));
                $dbValues = "'" . implode("', '", $rowData) . "'";
    
                $sql = "INSERT INTO $table ($dbColumns) VALUES ($dbValues)";
                if (!mysqli_query($conn, $sql)) {
                    echo "Error inserting row $rowIndex: " . mysqli_error($conn) . "<br>";
                }
            }
    
            echo "success";
        } else {
            echo "Error: No file uploaded or file upload failed.";
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

        $headers = [
            'A' => 'InvID', 'B' => 'Coil/Part Number', 'C' => 'Description', 'D' => 'Color', 'E' => 'Qty', 'F' => 'Units', 'G' => 'Notes', 'H' => 'Price',
            'I' => 'Price2', 'J' => 'Price3', 'K' => 'Price4', 'L' => 'Price5', 'M' => 'Price6', 'N' => 'Price7', 'O' => 'Order', 'P' => 'ItemType', 'Q' => 'VendorID',
            'R' => 'Cost', 'S' => 'Blank 1', 'T' => 'Blank 2', 'U' => 'Color Options', 'V' => 'Length Options', 'W' => 'Product System Abreviations',
            'X' => 'Product Systems', 'Y' => 'Category Abreviations', 'Z' => 'Product Category', 'AA' => 'Line Abreviations', 'AB' => 'Product Line',
            'AC' => 'Type Abreviations', 'AD' => 'Product Type', 'AE' => 'Product Item', 'AF' => 'Product Code Abreviation', 'AG' => 'Item Abreviation',
            'AH' => 'COLOR CODE Table', 'AI' => 'Color Consolidation', 'AJ' => 'LENGTH/Size Table CODE', 'AK' => 'Size consolidation', 'AL' => 'Gauge',
            'AM' => 'Product Code', 'AN' => 'Concatenated', 'AO' => 'Product Description', 'AP' => 'Size Scrub', 'AQ' => 'Comments', 'AR' => 'Blank4',
            'AS' => 'Intent? Usage?', 'AT' => 'Width', 'AU' => '# of Hems', 'AV' => '# of Bends', 'AW' => 'Length', 'AX' => 'Thickness', 'AY' => 'What Size?',
            'AZ' => 'Stock or Special Order', 'BA' => 'Mfg or Purchased', 'BB' => 'SupplierName', 'BC' => 'Cost Example', 'BD' => 'Old System Description',
            'BE' => 'Category Use-Area (Cousin) (Marketing-Sales?)', 'BF' => 'Category Type-Category (2nd-Cousin) (Marketing-Sales?)',
            'BG' => 'Category Use-Application (3nd-Cousin) (Marketing-Sales?)', 'BH' => 'SPM1', 'BI' => 'SPM2', 'BJ' => 'SPM3', 'BK' => 'SPM4', 'BL' => 'SPM5',
            'BM' => 'SPM6', 'BN' => 'SPM7', 'BO' => 'Flat Sheet Width', 'BP' => 'Number of Bends', 'BQ' => 'Number of Hems', 'BR' => 'Hemming Machine',
            'BS' => 'Trim Rollformer', 'BT' => '$ per Hem', 'BU' => '$ Per Bend', 'BV' => '$ Per square inch', 'BW' => '', 'BX' => '', 'BY' => 'Actual Moving Cost',
            'BZ' => 'Manual Assigned Cost', 'CA' => 'Retail Pricing', 'CB' => '', 'CC' => 'Retail Pricing $/ft', 'CD' => 'Contractor Pricing (Level 2)',
            'CE' => 'Contractor Pricing (Level 3)', 'CF' => 'Contractor Pricing (Level 4)', 'CG' => 'Wholesale Pricing (Level 5)',
            'CH' => 'Wholesale Pricing (Level 6)', 'CI' => 'Penetration Pricing (Level 7)', 'CJ' => 'Retail', 'CK' => 'Contractor 1',
            'CL' => 'Contractor 2', 'CM' => 'Low Rib Coil Width', 'CN' => 'Number of Trim per sheet width', 'CO' => 'Drop', 'CP' => 'Ft or Each',
            'CQ' => 'Flat Sheet Length converted to Inches', 'CR' => '', 'CS' => '', 'CT' => 'Square Inches per piece', 'CU' => 'Full Flat Stock Width',
            'CV' => "$'s per square Inch", 'CW' => '$ Per Piece', 'CX' => '$ per piece', 'CY' => 'Flat Sheet Length (Ft)', 'CZ' => '', 'DA' => '',
            'DB' => 'Square Inches per piece', 'DC' => '10', 'DD' => '12', 'DE' => '14', 'DF' => '16', 'DG' => '18', 'DH' => '20', 'DI' => '24',
            'DJ' => 'Full Flat Stock Width', 'DK' => "#'s per square Inch", 'DL' => '# of Pieces per sheet', 'DM' => 'Weight per piece'
        ];

        $columnMapping = [
            'A' => 'product_id',
            'B' => 'coil_part_no',
            'D' => 'color',
            'E' => 'quantity_in_stock',
            'F' => 'unit_of_measure',
            'H' => 'price_1',
            'I' => 'price_2',
            'J' => 'price_3',
            'K' => 'price_4',
            'L' => 'price_5',
            'M' => 'price_6',
            'N' => 'price_7',
            'X' => 'product_system',
            'Z' => 'product_category',
            'AB' => 'product_line',
            'AD' => 'product_type',
            'AE' => 'product_item',
            'AL' => 'gauge',
            'AM' => 'product_code',
            'AO' => 'description',
            'AQ' => 'comment',
            'AS' => 'product_usage',
            'BO' => 'width',
            'AW' => 'length',
            'AX' => 'thickness',
            'AZ' => 'stock_type',
            'BA' => 'product_origin',
            'BB' => 'supplier_id',
            'BP' => 'bends',
            'BQ' => 'hems',
            'BR' => 'hemming_machine',
            'BS' => 'trim_rollformer',
            'BT' => 'cost_per_hem',
            'BU' => 'cost_per_bend',
            'BV' => 'cost_per_square_inch',
            'CM' => 'coil_width',
        ];
    
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
    
        $sql = "SELECT * FROM product_duplicate2";
        if (!empty($product_category)) {
            $sql .= " WHERE product_category = '$product_category'";
        }
        $result = $conn->query($sql);
    
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        $headers = [
            'A' => 'InvID', 'B' => 'Coil/Part Number', 'C' => 'Description', 'D' => 'Color', 'E' => 'Qty', 'F' => 'Units', 'G' => 'Notes', 'H' => 'Price',
            'I' => 'Price2', 'J' => 'Price3', 'K' => 'Price4', 'L' => 'Price5', 'M' => 'Price6', 'N' => 'Price7', 'O' => 'Order', 'P' => 'ItemType', 'Q' => 'VendorID',
            'R' => 'Cost', 'S' => 'Blank 1', 'T' => 'Blank 2', 'U' => 'Color Options', 'V' => 'Length Options', 'W' => 'Product System Abreviations',
            'X' => 'Product Systems', 'Y' => 'Category Abreviations', 'Z' => 'Product Category', 'AA' => 'Line Abreviations', 'AB' => 'Product Line',
            'AC' => 'Type Abreviations', 'AD' => 'Product Type', 'AE' => 'Product Item', 'AF' => 'Product Code Abreviation', 'AG' => 'Item Abreviation',
            'AH' => 'COLOR CODE Table', 'AI' => 'Color Consolidation', 'AJ' => 'LENGTH/Size Table CODE', 'AK' => 'Size consolidation', 'AL' => 'Gauge',
            'AM' => 'Product Code', 'AN' => 'Concatenated', 'AO' => 'Product Description', 'AP' => 'Size Scrub', 'AQ' => 'Comments', 'AR' => 'Blank4',
            'AS' => 'Intent? Usage?', 'AT' => 'Width', 'AU' => '# of Hems', 'AV' => '# of Bends', 'AW' => 'Length', 'AX' => 'Thickness', 'AY' => 'What Size?',
            'AZ' => 'Stock or Special Order', 'BA' => 'Mfg or Purchased', 'BB' => 'SupplierName', 'BC' => 'Cost Example', 'BD' => 'Old System Description',
            'BE' => 'Category Use-Area (Cousin) (Marketing-Sales?)', 'BF' => 'Category Type-Category (2nd-Cousin) (Marketing-Sales?)',
            'BG' => 'Category Use-Application (3nd-Cousin) (Marketing-Sales?)', 'BH' => 'SPM1', 'BI' => 'SPM2', 'BJ' => 'SPM3', 'BK' => 'SPM4', 'BL' => 'SPM5',
            'BM' => 'SPM6', 'BN' => 'SPM7', 'BO' => 'Flat Sheet Width', 'BP' => 'Number of Bends', 'BQ' => 'Number of Hems', 'BR' => 'Hemming Machine',
            'BS' => 'Trim Rollformer', 'BT' => '$ per Hem', 'BU' => '$ Per Bend', 'BV' => '$ Per square inch', 'BW' => '', 'BX' => '', 'BY' => 'Actual Moving Cost',
            'BZ' => 'Manual Assigned Cost', 'CA' => 'Retail Pricing', 'CB' => '', 'CC' => 'Retail Pricing $/ft', 'CD' => 'Contractor Pricing (Level 2)',
            'CE' => 'Contractor Pricing (Level 3)', 'CF' => 'Contractor Pricing (Level 4)', 'CG' => 'Wholesale Pricing (Level 5)',
            'CH' => 'Wholesale Pricing (Level 6)', 'CI' => 'Penetration Pricing (Level 7)', 'CJ' => 'Retail', 'CK' => 'Contractor 1',
            'CL' => 'Contractor 2', 'CM' => 'Low Rib Coil Width', 'CN' => 'Number of Trim per sheet width', 'CO' => 'Drop', 'CP' => 'Ft or Each',
            'CQ' => 'Flat Sheet Length converted to Inches', 'CR' => '', 'CS' => '', 'CT' => 'Square Inches per piece', 'CU' => 'Full Flat Stock Width',
            'CV' => "$'s per square Inch", 'CW' => '$ Per Piece', 'CX' => '$ per piece', 'CY' => 'Flat Sheet Length (Ft)', 'CZ' => '', 'DA' => '',
            'DB' => 'Square Inches per piece', 'DC' => '10', 'DD' => '12', 'DE' => '14', 'DF' => '16', 'DG' => '18', 'DH' => '20', 'DI' => '24',
            'DJ' => 'Full Flat Stock Width', 'DK' => "#'s per square Inch", 'DL' => '# of Pieces per sheet', 'DM' => 'Weight per piece'
        ];
    
        foreach ($headers as $columnLetter => $headerName) {
            $sheet->setCellValue($columnLetter . '1', $headerName);
        }
    
        // Column Mapping
        $columnMapping = [
            'A' => 'product_id',
            'B' => 'coil_part_no',
            'D' => 'color',
            'E' => 'quantity_in_stock',
            'F' => 'unit_of_measure',
            'H' => 'price_1',
            'I' => 'price_2',
            'J' => 'price_3',
            'K' => 'price_4',
            'L' => 'price_5',
            'M' => 'price_6',
            'N' => 'price_7',
            'X' => 'product_system',
            'Z' => 'product_category',
            'AB' => 'product_line',
            'AD' => 'product_type',
            'AE' => 'product_item',
            'AL' => 'gauge',
            'AM' => 'product_code',
            'AO' => 'description',
            'AQ' => 'comment',
            'AS' => 'product_usage',
            'BO' => 'width',
            'AW' => 'length',
            'AX' => 'thickness',
            'AZ' => 'stock_type',
            'BA' => 'product_origin',
            'BB' => 'supplier_id',
            'BP' => 'bends',
            'BQ' => 'hems',
            'BR' => 'hemming_machine',
            'BS' => 'trim_rollformer',
            'BT' => 'cost_per_hem',
            'BU' => 'cost_per_bend',
            'BV' => 'cost_per_square_inch',
            'CM' => 'coil_width',
        ];
    
        $row = 2;
        while ($data = $result->fetch_assoc()) {
            foreach ($columnMapping as $columnLetter => $dbColumn) {
                $sheet->setCellValue($columnLetter . $row, $data[$dbColumn] ?? '');
            }
            $row++;
        }
    
        $filename = "products.xlsx";
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
        
        $column_name = mysqli_real_escape_string($conn, $column_name);
        $new_value = mysqli_real_escape_string($conn, $new_value);
        $product_id = mysqli_real_escape_string($conn, $product_id);
        
        $sql = "UPDATE test SET `$column_name` = '$new_value' WHERE product_id = '$product_id'";

        if ($conn->query($sql) === TRUE) {
            echo 'success';
        } else {
            echo 'Error updating record: ' . $conn->error;
        }

        echo $sql;
    }
    
    mysqli_close($conn);
}
?>
