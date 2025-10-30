<div class="row justify-content-center mb-2">
    <!-- Colors -->
    <div class="col-3">
        <select class="form-control qty_select2" id="qty-color" name="color">
            <option value="" data-category="">All Colors</option>
            <optgroup label="Assigned Colors">
                <?php
                $assigned_colors = getAssignedProductColors($id);
                if (!empty($assigned_colors)) {
                    $color_ids_str = implode(',', array_map('intval', $assigned_colors));
                    $query_colors = "
                        SELECT color_id, color_name, product_category
                        FROM paint_colors
                        WHERE color_id IN ($color_ids_str)
                        AND hidden = 0
                        AND color_status = 1
                        ORDER BY color_name ASC
                    ";
                    $result_colors = mysqli_query($conn, $query_colors);
                    while ($row = mysqli_fetch_assoc($result_colors)) {
                ?>
                        <option 
                            value="<?= htmlspecialchars($row['color_id']) ?>" 
                            data-category="<?= htmlspecialchars($row['product_category']) ?>">
                            <?= htmlspecialchars($row['color_name']) ?>
                        </option>
                <?php
                    }
                }
                ?>
            </optgroup>
        </select>
    </div>

    <!-- Grade -->
    <div class="col-3">
        <select class="form-control qty_select2" id="qty-grade" name="grade">
            <option value="" data-category="">All Grades</option>
            <optgroup label="Product Grades">
                <?php
                $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY product_grade ASC";
                $result_grade = mysqli_query($conn, $query_grade);
                while ($row_grade = mysqli_fetch_array($result_grade)) {
                ?>
                    <option value="<?= htmlspecialchars($row_grade['product_grade_id']) ?>" 
                            data-category="<?= htmlspecialchars($row_grade['product_category']) ?>">
                        <?= htmlspecialchars($row_grade['product_grade']) ?>
                    </option>
                <?php } ?>
            </optgroup>
        </select>
    </div>

    <!-- Gauge -->
    <div class="col-3">
        <select class="form-control qty_select2" id="qty-gauge" name="gauge">
            <option value="" data-category="">All Gauges</option>
            <optgroup label="Product Gauges">
                <?php
                $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1' AND product_gauge_id IN (2) ORDER BY product_gauge ASC";
                $result_gauge = mysqli_query($conn, $query_gauge);
                while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                ?>
                    <option value="<?= htmlspecialchars($row_gauge['product_gauge_id']) ?>" 
                            data-category="gauge">
                        <?= htmlspecialchars($row_gauge['product_gauge']) ?>
                    </option>
                <?php } ?>
            </optgroup>
        </select>
    </div>
</div>

<div class="col-12">
    <h5 class="text-center pt-3 fs-4 fw-bold"><span id="coil-stock"></span></h5>
</div>

<div class="col-12"><hr class="w-100"></div>

<div class="row align-items-center mb-2">
    <div class="col-12 text-end">
        <button type="button" id="createBundleBtn" class="btn btn-sm btn-primary">
            Create Bundles
        </button>
    </div>
</div>

<div class="row align-items-center mb-2">
    <div class="col-12" id="productFormCol">
        <div class="row justify-content-center">
            <div class="col-1 text-center bundle-checkbox-header d-none">
                <label class="fs-4 fw-semibold"></label>
            </div>
            <div class="col-2 text-center">
                <label class="fs-4 fw-semibold">Quantity</label>
            </div>
            <div class="col-2 text-center">
                <label class="fs-4 fw-semibold">Length</label>
            </div>
            <div class="col-2 text-center notes-col d-none">
                <label class="fs-4 fw-semibold">Notes</label>
            </div>
        </div>

        <div id="bundleGroups"></div>

        <div id="unbundledRows">
            <div class="quantity-length-container row justify-content-center mx-0 align-items-center mb-2">
                <div class="col-1 text-center">
                    <span class="drag-handle me-2" style="cursor: move;">☰</span>
                </div>
                <div class="col-1 text-center bundle-checkbox-wrapper d-none">
                    <input type="checkbox" class="bundle-checkbox">
                    <input type="hidden" name="bundle_name[]" value="">
                </div>
                <div class="col-2">
                    <input type="number" value="" name="quantity_product[]" 
                        class="form-control form-control-sm quantity-product" 
                        placeholder="Qty" list="quantity-product-list" autocomplete="off">
                </div>

                <div class="col-2">
                    <div class="input-group">
                        <input class="form-control form-control-sm length_feet" 
                            type="number" name="length_feet[]" list="length_feet_datalist" 
                            value="<?= $values['estimate_length'] ?>" placeholder="FT">
                        <input step="0.0001" class="form-control form-control-sm length_inch" 
                            type="text" name="length_inch[]" list="length_inch_datalist" 
                            value="<?= $values['estimate_length_inch'] ?>" placeholder="IN">
                    </div>
                </div>
                
                <div class="col-2 notes-col d-none">
                    <input type="text" name="notes[]" class="form-control form-control-sm mb-0" placeholder="Enter Notes">
                </div>
            </div>
        </div>

        <div class="col-7 text-end">
            <a href="javascript:void(0)" type="button" id="duplicateFields" class="text-end">
                <i class="fas fa-plus"></i>
            </a>
        </div>

        <div class="col-auto backer-rod-container d-none">
            <label class="fs-4 fw-semibold text-start me-2">Backer Rod (3/8in)</label><br>
            <input type="number" step="0.001" name="backer_rod" 
                class="form-control form-control-sm backer_rod d-inline-block" style="width:120px;">
        </div>
    </div>
    <div class="col-3 d-none" id="bundleSection">
        <div class="card p-3">
            <h6 class="fw-bold">Add Bundle Info</h6>
            <p class="mb-1">Bundle <span id="bundleCounter">1</span></p>
            <div class="mb-2">
                <label for="bundleName" class="form-label">Name of Bundle:</label>
                <input type="text" id="bundleName" class="form-control form-control-sm" placeholder="Enter bundle name">
            </div>
            <button type="button" id="addToBundleBtn" class="btn btn-success btn-sm w-100">
                Add to Bundles
            </button>
        </div>
    </div>
</div>

<script>
$(function() {
    for (let i = 0; i < 10; i++) {
        duplicateRow();
    }
    maxLength = 20;
});
</script>