<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

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
        $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id'] ?? '');
        $product_base = mysqli_real_escape_string($conn, $_POST['product_base'] ?? '');
        

        $correlatedProducts = $_POST['correlatedProducts'];
    
        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $checkQuery);
        $isInsert = false;
    
        if (mysqli_num_rows($result) > 0) {
            // Record exists, proceed with update
            $isInsert = false;
            $updateQuery = "UPDATE product SET 
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
                comment = '$comment', 
                product_base = '$product_base', 
                supplier_id = '$supplier_id'
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
            $insertQuery = "INSERT INTO product (
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
                comment,
                product_base,
                supplier_id
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
                '$comment',
                '$product_base',
                '$supplier_id'
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
                            $sql = "UPDATE product SET main_image='images/product/$newFileName' WHERE product_id='$product_id'";
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
                $sql = "UPDATE product SET main_image='images/product/product.jpg' WHERE product_id='$product_id'";
                if (!$conn->query($sql)) {
                    echo "Error updating record: " . $conn->error;
                }
            }
        }        
    }

    if ($action == "fetch_view_modal") {
        $product_id = mysqli_real_escape_string($conn, $_POST['id']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product WHERE product_id = '$product_id'";
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

    if ($action == "fetch_add_inventory") {
        $product_id = mysqli_real_escape_string($conn, $_POST['id']);
        $prouct_details = getProductDetails($product_id);
        $supplier_id = $prouct_details['supplier_id'];
        ?>
        
        <form id="add_inventory" class="form-horizontal">
            <div class="card">
                <div class="card-body">
                    <h4 class="fw-bold"><?= getProductName($product_id)?></h4>
                    <input type="hidden" id="product_id_filter" class="form-control select2-add" name="Product_id" value="<?= $product_id ?>" />
                    <input type="hidden" id="operation" name="operation" value="add" />
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Color</label>
                            <div class="mb-3">
                                <select id="color<?= $no ?>" class="form-control color-cart select2-inventory" name="color_id">
                                    <option value="" >Select Color...</option>
                                    <?php
                                    $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                    $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                                    while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                    ?>
                                        <option value="<?= $row_paint_colors['color_id'] ?>" <?= $selected ?> data-color="<?= getColorHexFromColorID($row_paint_colors['color_id']) ?>"><?= $row_paint_colors['color_name'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <label class="form-label">Supplier</label>
                            <div class="mb-3">
                                <p><?= !empty($supplier_id) ? getSupplierName($supplier_id) : 'No Supplier Set for Product' ?></p>
                                <input type="hidden" id="supplier_id_update" name="supplier_id" value="<?= $supplier_id ?>" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Warehouse</label>
                            <div class="mb-3">
                            <select id="Warehouse_id" class="form-control select2-inventory" name="Warehouse_id">
                                <option value="" >Select Warehouse...</option>
                                <optgroup label="Warehouse">
                                    <?php
                                    $query_warehouse = "SELECT * FROM warehouses WHERE status = '1'";
                                    $result_warehouse = mysqli_query($conn, $query_warehouse);            
                                    while ($row_warehouse = mysqli_fetch_array($result_warehouse)) {
                                    ?>
                                        <option value="<?= $row_warehouse['WarehouseID'] ?>" ><?= $row_warehouse['WarehouseName'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                                
                            </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Shelf</label>
                            <div class="mb-3">
                            <select id="Shelves_id" class="form-control select2-inventory" name="Shelves_id">
                                <option value="" >Select Shelf...</option>
                                <optgroup label="Shelf">
                                    <?php
                                    $query_shelf = "SELECT * FROM shelves";
                                    $result_shelf = mysqli_query($conn, $query_shelf);            
                                    while ($row_shelf = mysqli_fetch_array($result_shelf)) {
                                    ?>
                                        <option value="<?= $row_shelf['ShelfID'] ?>" ><?= $row_shelf['ShelfCode'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bin</label>
                            <div class="mb-3">
                            <select id="Bin_id" class="form-control select2-inventory" name="Bin_id">
                                <option value="" >Select Bin...</option>
                                <optgroup label="Bin">
                                    <?php
                                    $query_bin = "SELECT * FROM bins";
                                    $result_bin = mysqli_query($conn, $query_bin);            
                                    while ($row_bin = mysqli_fetch_array($result_bin)) {
                                    ?>
                                        <option value="<?= $row_bin['BinID'] ?>" ><?= $row_bin['BinCode'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Row</label>
                            <div class="mb-3">
                            <select id="Row_id" class="form-control select2-inventory" name="Row_id">
                                <option value="" >Select Row...</option>
                                <optgroup label="Row">
                                    <?php
                                    $query_rows = "SELECT * FROM warehouse_rows";
                                    $result_rows = mysqli_query($conn, $query_rows);            
                                    while ($row_rows = mysqli_fetch_array($result_rows)) {
                                    ?>
                                        <option value="<?= $row_rows['WarehouseRowID'] ?>" ><?= $row_rows['WarehouseRowID'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quantity</label>
                            <input type="text" id="quantity_add" name="quantity" class="form-control"  />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pack</label>
                            <div class="mb-3">
                            <select id="pack_add" class="form-control select2-inventory pack_select" name="pack">
                                <option value="" >Select Pack...</option>
                                <optgroup label="Supplier Packs">
                                    <?php
                                    $query_packs = "SELECT * FROM supplier_pack WHERE supplierid = $supplier_id";
                                    $result_packs = mysqli_query($conn, $query_packs);            
                                    while ($row_packs = mysqli_fetch_array($result_packs)) {
                                    ?>
                                        <option value="<?= $row_packs['id'] ?>" data-count="<?= $row_packs['pack_count'] ?>" ><?= $row_packs['pack'] ?> ( <?= $row_packs['pack_count'] ?> )</option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total Quantity</label>
                            <input type="text" id="quantity_ttl_add" name="quantity_ttl" class="form-control"  />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" id="Date" name="Date" class="form-control"  />
                        </div>
                    </div>
                    <div class="form-buttons mt-3 text-right">
                        <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                    </div>
                </div>
                
            </div>
        </form>
        <?php
    }

    if ($action == "fetch_edit_modal") {
        $product_id = mysqli_real_escape_string($conn, $_POST['id']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product WHERE product_id = '$product_id'";
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
                                        <label class="form-label">Select Base Product</label>
                                        <div class="col-md-12">
                                        <select id="base_product_update" name="product_base" class="select2-update form-control">
                                            <option value="" selected>Select Base Product...</option>
                                            <optgroup label="Select Base Product">
                                                <?php
                                                $selected = '';
                                                $query_base = "SELECT * FROM product_base";
                                                $result_base = mysqli_query($conn, $query_base);            
                                                while ($row_base = mysqli_fetch_array($result_base)) {
                                                    $selected = ($row['product_base'] == $row_base['id']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= $row_base['id'] ?>" data-base-price="<?= $row_base['base_price'] ?>" <?= $selected ?>><?= $row_base['product_name'] .'( $'.number_format(floatval($row_base['base_price']),2).' )' ?></option>
                                                <?php   
                                                }
                                                ?>
                                            </optgroup>
                                        </select>
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
                                                    <option value="/">Select One...</option>
                                                    <?php
                                                    $query_roles = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
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
                                                    <option value="/">Select One...</option>
                                                    <?php
                                                    $query_roles = "SELECT * FROM product_line WHERE hidden = '0' AND status = '1' ORDER BY `product_line` ASC";
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
                                                    <option value="/">Select One...</option>
                                                    <?php
                                                    $query_roles = "SELECT * FROM product_type WHERE hidden = '0' AND status = '1' ORDER BY `product_type` ASC";
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
                                        <label class="form-label">Correlated products</label>
                                        <div class="col-md-12">
                                        <select id="correlatedProducts" name="correlatedProducts[]" class="select2-update form-control" multiple="multiple">
                                            <option value="" hidden>Select Base Product...</option>
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
                                        <select id="gauge_update" class="form-control" name="gauge">
                                            <option value="" >Select Gauge...</option>
                                            <?php
                                            $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1' ORDER BY `product_gauge` ASC";
                                            $result_gauge = mysqli_query($conn, $query_gauge);            
                                            while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                                $selected = ($row['gauge'] == $row_gauge['product_gauge_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $row_gauge['product_gauge_id'] ?>" data-multiplier="<?= $row_gauge['multiplier'] ?>" <?= $selected ?>><?= $row_gauge['product_gauge'] ?> ( x<?= $row_gauge['multiplier'] ?> )</option>
                                            <?php   
                                            }
                                            ?>
                                        </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 opt_field_update" data-id="6">
                                        <div class="mb-3">
                                        <label class="form-label">Grade</label>
                                        <select id="grade_update" class="form-control" name="grade">
                                            <option value="" >Select Grade...</option>
                                            <?php
                                            $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY `product_grade` ASC";
                                            $result_grade = mysqli_query($conn, $query_grade);            
                                            while ($row_grade = mysqli_fetch_array($result_grade)) {
                                                $selected = ($row['grade'] == $row_grade['product_grade_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $row_grade['product_grade_id'] ?>" data-multiplier="<?= $row_grade['multiplier'] ?>" <?= $selected ?>><?= $row_grade['product_grade'] ?> ( x<?= $row_grade['multiplier'] ?> )</option>
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
                                        <select id="color_update" class="form-control" name="color">
                                            <option value="" >Select Color...</option>
                                            <?php
                                            $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                            $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                                            while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                                $selected = ($row['color'] == $row_paint_colors['color_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $row_paint_colors['color_id'] ?>" data-multiplier="<?= getProductColorMultValue($row_paint_colors['multiplier_category']) ?>" <?= $selected ?>><?= $row_paint_colors['color_name'] ?> ( x<?= getProductColorMultValue($row_paint_colors['multiplier_category']) ?> )</option>
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
                                            <option value="" >Select Profile...</option>
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

                                    <div class="row pt-3 d-none">
                                    <div class="col-md-6 opt_field_update" data-id="11">
                                        <div class="mb-3">
                                        <label class="form-label">Width</label>
                                        <input type="text" id="width" name="width" class="form-control" value="<?= $row['width']?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-6 opt_field_update" data-id="13">
                                        <div class="mb-3">
                                        <label class="form-label">Weight</label>
                                        <input type="number" id="weight" name="weight" class="form-control" value="<?= $row['weight']?>" />
                                        </div>
                                    </div>
                                    
                                    </div>

                                    <div class="row pt-3">
                                    <div class="col-md-6 opt_field_update" data-id="14">
                                        <div class="mb-3">
                                        <label class="form-label">Unit of Measure</label>
                                        <input type="text" id="unitofMeasure" name="unitofMeasure" class="form-control" value="<?= $row['unit_of_measure']?>" />
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
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                        <label class="form-label">UnitPrice</label>
                                        <input type="text" id="unit_price_update" name="unitPrice" class="form-control" value="<?= $row['unit_price']?>" />
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

                                    <div class="pt-3 mb-3 opt_field_update" data-id="16">
                                        <label class="form-label">Comment</label>
                                        <textarea class="form-control" id="comment" name="comment" rows="5"><?= $row['comment']?></textarea>
                                    </div>

                                    <div class="row pt-3">
                                        <label class="form-label">Select Supplier</label>
                                        <div class="mb-3">
                                            <select id="supplier_id" class="form-control select2-update" name="supplier_id">
                                                <option value="" >Select Supplier...</option>
                                                <optgroup label="Supplier">
                                                    <?php
                                                    $query_supplier = "SELECT * FROM supplier WHERE status = 1 ORDER BY `supplier_name` ASC";
                                                    $result_supplier = mysqli_query($conn, $query_supplier);            
                                                    while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                                                        $selected = ($row['supplier_id'] == $row_supplier['supplier_id']) ? 'selected' : '';
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

        $statusQuery = "UPDATE product SET status = '$new_status' WHERE product_id = '$product_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }

    if ($action == 'hide_category') {
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
        $query = "UPDATE product SET hidden='1' WHERE product_id='$product_id'";
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
                            product AS p
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
    
    mysqli_close($conn);
}
?>
