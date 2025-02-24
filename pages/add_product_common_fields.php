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
<div class="col-md-12 mb-3">
    <label class="form-label">Correlated products</label>
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
<div class="col-md-6">
    <div class="mb-3">
    <label class="form-label">Material</label>
    <input type="text" id="material" name="material" class="form-control" value="<?= $row['material']?>" />
    </div>
</div> 
<div class="col-md-6">
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <label class="form-label">Warranty Type</label>
            <a href="?page=warranty_type" target="_blank" class="text-decoration-none">Edit</a>
        </div>
        <select id="warranty_type" class="form-control" name="warranty_type">
            <option value="" >Select Warranty Type...</option>
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
        <div class="d-flex justify-content-between align-items-center">
            <label class="form-label">Product Profile</label>
            <a href="?page=profile_type" target="_blank" class="text-decoration-none">Edit</a>
        </div>
        <select id="profile" class="form-control add-category" name="profile">
            <option value="" >Select Profile...</option>
            <?php
            $query_profile_type = "SELECT * FROM profile_type WHERE hidden = '0'";
            $result_profile_type = mysqli_query($conn, $query_profile_type);            
            while ($row_profile_type = mysqli_fetch_array($result_profile_type)) {
                $selected = ($row['profile'] == $row_profile_type['profile_type_id']) ? 'selected' : '';
            ?>
                <option value="<?= $row_profile_type['profile_type_id'] ?>" data-category="<?= $row_profile_type['product_category'] ?>"  <?= $selected ?>><?= $row_profile_type['profile_type'] ?></option>
            <?php   
            }
            ?>
        </select>
    </div>
</div>
<div class="col-md-6">
    <div class="mb-3">
    <label class="form-label">Weight</label>
    <input type="number" id="weight" name="weight" class="form-control" step="0.01" value="<?= $row['weight']?>" />
    </div>
</div>
<div class="col-md-6">
    <div class="mb-3">
    <label class="form-label">Unit of Measure</label>
    <input type="text" id="unit_of_measure" name="unit_of_measure" class="form-control" value="<?= $row['unit_of_measure']?>" />
    </div>
</div>
<div class="col-md-6">
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
<div class="col-md-6">
    <div class="mb-3">
    <label class="form-label">UPC</label>
    <input type="text" id="upc" name="upc" class="form-control" value="<?= !empty($row['upc']) ? $row['upc'] : generateRandomUPC(); ?>" />
    </div>
</div>
<div class="col-md-6" data-id="15">
    <div class="mb-3">
    <label class="form-label">Product Origin</label>
    <select id="product_origin" class="form-control" name="product_origin">
        <option value="" <?= empty($row['product_origin']) ? 'selected' : '' ?>>Select Origin...</option>
        <option value="1" <?= $row['product_origin'] == '1' ? 'selected' : '' ?>>Source</option>
        <option value="2" <?= $row['product_origin'] == '2' ? 'selected' : '' ?>>Manufactured</option>
    </select>
    </div>
</div>

<div class="col-md-12">
    <div class="mb-3 opt_field_update" data-id="16">
        <label class="form-label">Comment</label>
        <textarea class="form-control" id="comment" name="comment" rows="5"><?= $row['comment']?></textarea>
    </div>
</div>