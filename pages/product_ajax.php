<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';

function generateRandomUPC() {
    global $conn;

    do {
        $upc_code = '';
        for ($i = 0; $i < 11; $i++) {
            $upc_code .= mt_rand(0, 9);
        }

        $odd_sum = 0;
        $even_sum = 0;
        for ($i = 0; $i < 11; $i++) {
            if ($i % 2 === 0) {
                $odd_sum += $upc_code[$i];
            } else {
                $even_sum += $upc_code[$i];
            }
        }

        $total_sum = (3 * $odd_sum) + $even_sum;
        $check_digit = (10 - ($total_sum % 10)) % 10;

        $upc_code .= $check_digit;

        $query = "SELECT COUNT(*) as count FROM product WHERE upc = '$upc_code'";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        $count = $row['count'];

    } while ($count > 0);

    return $upc_code;
}

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
        $upc = generateRandomUPC();
        $unit_of_measure = mysqli_real_escape_string($conn, $_POST['unitofMeasure']);
        $unit_cost = mysqli_real_escape_string($conn, $_POST['unitCost']);
        $unit_gross_margin = mysqli_real_escape_string($conn, $_POST['unitGrossMargin']);
        $product_usage = mysqli_real_escape_string($conn, $_POST['product_usage']);
        $comment = mysqli_real_escape_string($conn, $_POST['comment']);

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
    
    }

    if ($action == "fetch_modal") {
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
                    <form id="add_product" class="form-horizontal">
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body">
                                <input type="hidden" id="product_id" name="product_id" class="form-control"  value="<?= $row['product_id']?>"/>

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
                                            <select id="product_category" class="form-control" name="product_category">
                                                <option value="/">Select One...</option>
                                                <?php
                                                $query_roles = "SELECT * FROM product_category WHERE hidden = '0'";
                                                $result_roles = mysqli_query($conn, $query_roles);            
                                                while ($row_product_category = mysqli_fetch_array($result_roles)) {
                                                    $selected = ($row['product_category'] == $row_product_category['product_id']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= $row_product_category['product_id'] ?>" <?= $selected ?>><?= $row_product_category['product_category'] ?></option>
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
                                                <option value="/">Select One...</option>
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
                                <div class="col-md-6">
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
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Material</label>
                                    <input type="text" id="material" name="material" class="form-control" value="<?= $row['material']?>" />
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Dimensions</label>
                                    <input type="text" id="dimensions" name="dimensions" class="form-control" value="<?= $row['dimensions']?>" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Thickness</label>
                                    <input type="text" id="thickness" name="thickness" class="form-control" value="<?= $row['thickness']?>" />
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-6">
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
                                <div class="col-md-6">
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
                                <div class="col-md-4">
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
                                <div class="col-md-4">
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
                                <div class="col-md-4">
                                    <div class="mb-3">
                                    <label class="form-label">Coating</label>
                                    <input type="text" id="coating" name="coating" class="form-control" value="<?= $row['coating']?>" />
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-6">
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
                                <div class="col-md-6">
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
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Width</label>
                                    <input type="text" id="width" name="width" class="form-control" value="<?= $row['width']?>" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Length</label>
                                    <input type="text" id="length" name="length" class="form-control" value="<?= $row['length']?>" />
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Weight</label>
                                    <input type="text" id="weight" name="weight" class="form-control" value="<?= $row['weight']?>" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Unit of Measure</label>
                                    <input type="text" id="unitofMeasure" name="unitofMeasure" class="form-control" value="<?= $row['unit_of_measure']?>" />
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">UnitPrice</label>
                                    <input type="text" id="unitPrice" name="unitPrice" class="form-control" value="<?= $row['unit_price']?>" />
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
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Unit Cost</label>
                                    <input type="text" id="unitCost" name="unitCost" class="form-control" value="<?= $row['unit_cost']?>" />
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Unit Gross Margin</label>
                                    <input type="text" id="unitGrossMargin" name="unitGrossMargin" class="form-control" value="<?= $row['unit_gross_margin']?>" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Usage</label>
                                    <input type="text" id="product_usage" name="product_usage" class="form-control" value="<?= $row['product_usage']?>" />
                                    </div>
                                </div>
                                </div>

                                <div class="mb-3">
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
            <?php
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
    
    
    mysqli_close($conn);
}
?>
