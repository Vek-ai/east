<div class="card shadow-sm rounded-3 mb-3">
    <div class="card-header bg-light border-bottom">
        <h5 class="mb-0 fw-bold">Product Color Mapping</h5>
    </div>
    <div class="card-body border rounded p-3">
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label">Color Group</label>
                        <a href="?page=product_color" target="_blank" class="text-decoration-none">Edit</a>
                    </div>
                    <select id="color" class="form-control add-category calculate" name="color">
                        <option value="" >Select Color Group...</option>
                        <?php
                        $query_colors = "SELECT pc.*, cgn.color_group_name AS color_name FROM product_color AS pc LEFT JOIN color_group_name AS cgn ON pc.color = cgn.color_group_name_id ORDER BY cgn.color_group_name ASC";
                        $result_colors = mysqli_query($conn, $query_colors);            
                        while ($row_colors = mysqli_fetch_array($result_colors)) {
                            $selected = (($row['color'] ?? '') == $row_colors['id']) ? 'selected' : '';
                        ?>
                            <option value="<?= $row_colors['id'] ?>" 
                                    data-price="<?=$row_colors['price'] ?>" 
                                    data-color="<?=$row_colors['color'] ?>" 
                                    data-system="<?=$row_colors['product_system'] ?>" 
                                    data-grade="<?=$row_colors['grade'] ?>" 
                                    data-gauge="<?=$row_colors['gauge'] ?>" 
                                    data-category="<?=trim($row_colors['product_category']) ?>" 
                                    data-multiplier="<?= $row_colors['multiplier'] ?>"
                                    <?= $selected ?>
                            >
                                        <?= getColorGroupName($row_colors['color']) ?>
                            </option>
                        <?php   
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label">Color</label>
                        <a href="?page=paint_colors" target="_blank" class="text-decoration-none">Edit</a>
                    </div>
                    <select id="color_paint" class="form-control calculate color-group-filter" name="color_paint">
                        <option value="" >Select Color...</option>
                        <?php
                        $query_color = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' AND color_group REGEXP '^[0-9]+$' ORDER BY `color_name` ASC";
                        $result_color = mysqli_query($conn, $query_color);
                        while ($row_color = mysqli_fetch_array($result_color)) {
                            $selected = ($row['color_paint'] == $row_color['color_id']) ? 'selected' : '';
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

            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Color Multiplier</label>
                    <input type="text" id="color_multiplier" name="color_multiplier" class="form-control readonly" value="<?=$row['color_multiplier'] ?? ''?>"/>
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
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" id="product_item" name="product_item" class="form-control" value="<?= $row['product_item']?>" />
                </div>
            </div>
            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" id="description" name="description" class="form-control" value="<?=$row['description']?>"/>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label">Warranty Type</label>
                        <a href="?page=product_warranty_type" target="_blank" class="text-decoration-none">Edit</a>
                    </div>
                    <select id="warranty_type" class="form-control" name="warranty_type">
                        <option value="" >Select Warranty Type...</option>
                        <?php
                        $query_product_warranty_type = "SELECT * FROM product_warranty_type WHERE hidden = '0' AND status = '1'";
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
                        <option value="" <?= empty($row['unit_of_measure']) ? 'selected' : '' ?>>Select Unit...</option>
                        <option value="ft" <?= $row['unit_of_measure'] == 'ft' ? 'selected' : '' ?>>Ft</option>
                        <option value="each" <?= $row['unit_of_measure'] == 'each' ? 'selected' : '' ?>>Each</option>
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
        <h5 class="mb-0 fw-bold">Product Color Mapping</h5>
    </div>
    <div class="card-body border rounded p-3">
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Cost</label>
                    <input type="text" id="cost" name="cost" class="form-control calculate" value="<?=$row['cost'] ?? ''?>"/>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Retail Price</label>
                    <input type="text" id="retail" name="unit_price" class="form-control" value="<?=$row['unit_price'] ?? ''?>"/>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Per In Price</label>
                    <input type="text" id="per_in_price" name="per_in_price" class="form-control" value="<?=$row['unit_price'] ?? ''?>"/>
                </div>
            </div>
            <div class="col-md-4 text-center mb-3">
                <label for="sold_by_feet" class="form-label d-block">Sold by feet</label>
                <input type="checkbox" class="form-check-input" id="sold_by_feet" name="sold_by_feet" value="1"
                    <?= $row['sold_by_feet'] == 1 ? 'checked' : '' ?>>
            </div>

            <div class="col-md-4 text-center mb-3">
                <label for="is_custom_length" class="form-label d-block">Sold with custom length?</label>
                <input type="checkbox" class="form-check-input" id="is_custom_length" name="is_custom_length" value="1"
                    <?= $row['is_custom_length'] == 1 ? 'checked' : '' ?>>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Per Ft Price</label>
                    <input type="text" id="per_ft_price" name="per_ft_price" class="form-control" value="<?=$row['unit_price'] ?? ''?>"/>
                </div>
            </div>
            <div class="col-md-4 text-center mb-3">
                <label for="standing_seam" class="form-label d-block">Standing Seam Panel</label>
                <input type="radio" class="form-check-input" id="standing_seam" name="panel_type" value="standing_seam"
                    <?= $row['standing_seam'] == 1 ? 'checked' : '' ?>>
            </div>

            <div class="col-md-4 text-center mb-3">
                <label for="board_batten" class="form-label d-block">Board &amp; Batten Panel</label>
                <input type="radio" class="form-check-input" id="board_batten" name="panel_type" value="board_batten"
                    <?= $row['board_batten'] == 1 ? 'checked' : '' ?>>
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
                <div class="mb-3">
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
                    <select id="supplier_id" class="form-control select-2 inventory_supplier" name="supplier_id">
                        <option value="" >Select Supplier...</option>
                        <optgroup label="Supplier">
                            <?php
                            $query_supplier = "SELECT * FROM supplier WHERE status = 1 ORDER BY `supplier_name` ASC";
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

        </div>
    </div>
</div>

<div class="card shadow-sm rounded-3 mb-3">
    <div class="card-header bg-light border-bottom">
        <h5 class="mb-0 fw-bold">Correlated products</h5>
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