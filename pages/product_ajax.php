<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';

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
        $color_code = mysqli_real_escape_string($conn, $_POST['colorCode']);
        $paint_provider = mysqli_real_escape_string($conn, $_POST['paintProvider']);
        $color_group = mysqli_real_escape_string($conn, $_POST['colorGroup']);
        $warranty_type = mysqli_real_escape_string($conn, $_POST['warrantyType']);
        $coating = mysqli_real_escape_string($conn, $_POST['coating']);
        $profile = mysqli_real_escape_string($conn, $_POST['profile']);
        $width = mysqli_real_escape_string($conn, $_POST['width']);
        $length = mysqli_real_escape_string($conn, $_POST['length']);
        $weight = mysqli_real_escape_string($conn, $_POST['weight']);
        $quantity_in_stock = mysqli_real_escape_string($conn, $_POST['quantityInStock']);
        $quantity_quoted = mysqli_real_escape_string($conn, $_POST['quantityQuoted']);
        $quantity_committed = mysqli_real_escape_string($conn, $_POST['quantityCommitted']);
        $quantity_available = mysqli_real_escape_string($conn, $_POST['quantityAvailable']);
        $quantity_in_transit = mysqli_real_escape_string($conn, $_POST['quantityInTransit']);
        $unit_price = mysqli_real_escape_string($conn, $_POST['unitPrice']);
        $date_added = mysqli_real_escape_string($conn, $_POST['dateAdded']);
        $date_modified = mysqli_real_escape_string($conn, $_POST['dateModified']);
        $last_ordered_date = mysqli_real_escape_string($conn, $_POST['lastOrderedDate']);
        $last_sold_date = mysqli_real_escape_string($conn, $_POST['lastSoldDate']);
        $upc = mysqli_real_escape_string($conn, $_POST['upc']);
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
                color_code = '$color_code', 
                paint_provider = '$paint_provider', 
                color_group = '$color_group', 
                warranty_type = '$warranty_type', 
                coating = '$coating', 
                profile = '$profile', 
                width = '$width', 
                length = '$length', 
                weight = '$weight', 
                quantity_in_stock = '$quantity_in_stock', 
                quantity_quoted = '$quantity_quoted', 
                quantity_committed = '$quantity_committed', 
                quantity_available = '$quantity_available', 
                quantity_in_transit = '$quantity_in_transit', 
                unit_price = '$unit_price', 
                date_added = '$date_added', 
                date_modified = '$date_modified', 
                last_ordered_date = '$last_ordered_date', 
                last_sold_date = '$last_sold_date', 
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
                color_code, 
                paint_provider, 
                color_group, 
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
                '$color_code', 
                '$paint_provider', 
                '$color_group', 
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
                                                    $selected = ($row['product_category'] == $row_product_category['product_category_id']) ? 'selected' : '';
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
                                    <select class="form-select" id="stock_type" name="stock_type">
                                        <option selected>Choose...</option>
                                        <option value="1" <?php ($row['stock_type'] == 1) ? 'selected' : ''; ?> >One</option>
                                        <option value="2" <?php ($row['stock_type'] == 2) ? 'selected' : ''; ?> >Two</option>
                                        <option value="3" <?php ($row['stock_type'] == 3) ? 'selected' : ''; ?> >Three</option>
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
                                    <input type="text" id="gauge" name="gauge" class="form-control" value="<?= $row['gauge']?>" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Grade</label>
                                    <input type="text" id="grade" name="grade" class="form-control" value="<?= $row['grade']?>" />
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Color</label>
                                    <input type="text" id="color" name="color" class="form-control" value="<?= $row['color']?>"  />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Color Code</label>
                                    <input type="text" id="colorCode" name="colorCode" class="form-control" value="<?= $row['color_code']?>" />
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                    <label class="form-label">Paint Provider</label>
                                    <input type="text" id="paintProvider" name="paintProvider" class="form-control" value="<?= $row['paint_provider']?>" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                    <label class="form-label">Color Group</label>
                                    <input type="text" id="colorGroup" name="colorGroup" class="form-control" value="<?= $row['color_group']?>" />
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
                                    <input type="text" id="warrantyType" name="warrantyType" class="form-control" value="<?= $row['warranty_type']?>" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Profile</label>
                                    <input type="text" id="profile" name="profile" class="form-control" value="<?= $row['profile']?>" />
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
                                    <label class="form-label">Quantity In Stock</label>
                                    <input type="text" id="quantityInStock" name="quantityInStock" class="form-control" value="<?= $row['quantity_in_stock']?>" />
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Quantity Quoted</label>
                                    <input type="text" id="quantityQuoted" name="quantityQuoted" class="form-control" value="<?= $row['quantity_quoted']?>" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Quantity Committed</label>
                                    <input type="text" id="quantityCommitted" name="quantityCommitted" class="form-control" value="<?= $row['quantity_committed']?>" />
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Quantity Available</label>
                                    <input type="text" id="quantityAvailable" name="quantityAvailable" class="form-control" value="<?= $row['quantity_available']?>" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">QuantityInTransit</label>
                                    <input type="text" id="quantityInTransit" name="quantityInTransit" class="form-control" value="<?= $row['quantity_in_transit']?>" />
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
                                    <label class="form-label">Date Added</label>
                                    <input type="date" id="dateAdded" name="dateAdded" class="form-control" value="<?= $row['date_added']?>" />
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Date Modified</label>
                                    <input type="date" id="dateModified" name="dateModified" class="form-control" value="<?= $row['date_modified']?>" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Last Ordered Date</label>
                                    <input type="date" id="lastOrderedDate" name="lastOrderedDate" class="form-control" value="<?= $row['last_ordered_date']?>" />
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Last Sold Date</label>
                                    <input type="date" id="lastSoldDate" name="lastSoldDate" class="form-control" value="<?= $row['last_sold_date']?>" />
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
                                    <label class="form-label">Unit of Measure</label>
                                    <input type="text" id="unitofMeasure" name="unitofMeasure" class="form-control" value="<?= $row['unit_of_measure']?>" />
                                    </div>
                                </div>
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

            <script>
            $(document).ready(function() {
                $(".select2").select2({});

                $('#addProductModal').on('shown.bs.modal', function () {
                    $('.select2').select2({
                        width: '100%',
                        placeholder: "Select Correlated Products",
                        allowClear: true
                    });
                });
            });
            </script>
            <?php
        }
    } 
    
    
    mysqli_close($conn);
}
?>
