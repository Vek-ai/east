<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

$lat = 0;
$lng = 0;

$panel_id = 3;

$deliveryAmt = getDeliveryCost();
$addressSettings = getSettingAddressDetails();
$amtPerMile = getSettingAmtPerMile();
$latSettings = !empty($addressSettings['lat']) ? $addressSettings['lat'] : 0;
$lngSettings = !empty($addressSettings['lng']) ? $addressSettings['lng'] : 0;
?>
<style>
    #special_trim_modal {
        z-index: 11060;
    }

    #special_trim_modal ~ .modal-backdrop.show {
        z-index: 11055;
    }

    #custom_trim_draw_modal {
        z-index: 12060;
    }

    #custom_trim_draw_modal ~ .modal-backdrop.show {
        z-index: 12055;
    }

    #viewOutOfStockmodal {
        z-index: 11060;
    }

    #viewOutOfStockmodal ~ .modal-backdrop.show {
        z-index: 11055;
    }

    #viewInStockmodal {
        z-index: 11060;
    }

    #viewInStockmodal ~ .modal-backdrop.show {
        z-index: 11055;
    }

    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type="number"] {
        -moz-appearance: textfield;
    }

    .select2-dropdown {
        max-height: calc(100vh - 50px);
        overflow-y: auto;
    }

    #productTable th,
    #productTable td {
        text-align: center;
        vertical-align: middle;
    }

    #productTable th:nth-child(1), 
    #productTable td:nth-child(1) {
        width: 25% !important; /* Products */
    }

    #productTable th:nth-child(2), 
    #productTable td:nth-child(2) {
        width: 8% !important; /* Avail. Colors */
    }

    #productTable th:nth-child(3), 
    #productTable td:nth-child(3) {
        width: 7% !important; /* Grade */
    }

    #productTable th:nth-child(4), 
    #productTable td:nth-child(4) {
        width: 10% !important; /* Gauge */
    }

    #productTable th:nth-child(5), 
    #productTable td:nth-child(5) {
        width: 10% !important; /* Type */
    }

    #productTable th:nth-child(6), 
    #productTable td:nth-child(6) {
        width: 10% !important; /* Profile */
    }

    #productTable th:nth-child(7), 
    #productTable td:nth-child(7) {
        width: 10% !important; /* Status */
    }

    #productTable th:nth-child(8), 
    #productTable td:nth-child(8) {
        width: 10% !important; /* Quantity */
    }

    #productTable th:nth-child(9), 
    #productTable td:nth-child(9) {
        width: 10% !important; /* Actions */
    }

    .select2-container .select2-dropdown .select2-results__options {
        max-height: 760px !important;
    }

    #custom_chart_modal {
        z-index: 91100;
    }

    .modal-backdrop.custom-backdrop {
        z-index: 91090;
    }
</style>
<div class="product-list pt-4">
    <div class="card">
        <div class="card-body p-3">
            <div class="row">
                <div class="col-2">
                    <div class="p-2 align-items-center gap-4 text-center">
                        <div style="color: #ffffff !important; opacity: 1;">
                            <input type="checkbox" id="toggleActive" checked> Show only In Stock
                        </div>
                    </div>
                    <div class="mb-9">
                        <div class="position-relative w-100 ps-0">
                            <input type="text" class="form-control search-chat py-2 ps-5 " id="text-srh" placeholder="Search Product">
                            <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                        </div>
                        <div class="align-items-center">
                            <div class="position-relative w-100 py-2 px-1">
                                <select class="form-control search-chat ps-5 filter-selection" id="select-category" data-filter-name="Category">
                                    <option value="">All Categories</option>
                                    <optgroup label="Category">
                                        <?php
                                        $query_category = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                                        $result_category = mysqli_query($conn, $query_category);
                                        while ($row_category = mysqli_fetch_array($result_category)) {
                                        ?>
                                            <option value="<?= $row_category['product_category_id'] ?>"
                                                    data-category="<?= $row_category['product_category'] ?>" >
                                                        <?= $row_category['product_category'] ?>
                                            </option>
                                        <?php
                                        }
                                        ?>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="sub_search_cat">
                                <div class="position-relative w-100 py-2 px-1">
                                    <select class="form-control ps-5 select2_filter filter-selection" id="select-profile" data-filter-name="Profile Type">
                                        <option value="" data-category="">All Profile Types</option>
                                        <optgroup label="Product Line">
                                            <?php
                                            $query_profile = "SELECT * FROM profile_type WHERE hidden = '0' ORDER BY profile_type ASC";
                                            $result_profile = mysqli_query($conn, $query_profile);
                                            while ($row_profile = mysqli_fetch_array($result_profile)) {
                                            ?>
                                                <option value="<?= htmlspecialchars($row_profile['profile_type_id']) ?>" 
                                                        data-category="<?= htmlspecialchars($row_profile['product_category']) ?>">
                                                    <?= htmlspecialchars($row_profile['profile_type']) ?>
                                                </option>
                                            <?php } ?>
                                        </optgroup>
                                    </select>
                                </div>

                                <!-- Product Type -->
                                <div class="position-relative w-100 py-2 px-1">
                                    <select class="form-control search-category ps-5 select2_filter filter-selection" id="select-type" data-filter-name="Product Types">
                                        <option value="" data-category="">All Product Types</option>
                                        <optgroup label="Product Type">
                                            <?php
                                            $query_type = "SELECT * FROM product_type WHERE hidden = '0' AND status = '1' ORDER BY product_type ASC";
                                            $result_type = mysqli_query($conn, $query_type);
                                            while ($row_type = mysqli_fetch_array($result_type)) {
                                            ?>
                                                <option value="<?= htmlspecialchars($row_type['product_type_id']) ?>" 
                                                        data-category="<?= htmlspecialchars($row_type['product_category']) ?>">
                                                    <?= htmlspecialchars($row_type['product_type']) ?>
                                                </option>
                                            <?php } ?>
                                        </optgroup>
                                    </select>
                                </div>

                                <!-- Colors -->
                                <div class="position-relative w-100 py-2 px-1">
                                    <select class="form-control search-category ps-5 select2_filter filter-selection" id="select-color" data-filter-name="Color">
                                        <option value="" data-category="">All Colors</option>
                                        <optgroup label="Product Colors">
                                            <?php
                                            $query_color = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY color_name ASC";
                                            $result_color = mysqli_query($conn, $query_color);
                                            while ($row_color = mysqli_fetch_array($result_color)) {
                                            ?>
                                                <option value="<?= htmlspecialchars($row_color['color_id']) ?>" 
                                                        data-category="<?= htmlspecialchars($row_color['product_category']) ?>">
                                                    <?= htmlspecialchars($row_color['color_name']) ?>
                                                </option>
                                            <?php } ?>
                                        </optgroup>
                                    </select>
                                </div>

                                <!-- Grade -->
                                <div class="position-relative w-100 py-2 px-1">
                                    <select class="form-control search-category ps-5 select2_filter filter-selection" id="select-grade" data-filter-name="Grade">
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
                                <div class="position-relative w-100 py-2 px-1">
                                    <select class="form-control ps-5 select2_filter filter-selection" id="select-gauge" data-filter-name="Gauge">
                                        <option value="" data-category="">All Gauges</option>
                                        <optgroup label="Product Gauges">
                                            <?php
                                            $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1' ORDER BY product_gauge ASC";
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
                            <div class="d-flex justify-content-end py-2">
                                <button type="button" class="btn btn-outline-primary reset_filters">
                                    <i class="fas fa-sync-alt me-1"></i> Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-10">
                    <div class="col-12 mb-3">
                        <h5>Selected Items:</h5>
                        <div id="selected-tags"></div>
                    </div>
                    <div class="table-responsive border rounded">
                        <table id="productTable" class="table align-middle text-wrap mb-0 text-white">
                            <thead>
                                <tr>
                                    <th scope="col" style="color: #ffffff !important; opacity: 1;">Products</th>
                                    <th scope="col" style="color: #ffffff !important; opacity: 1;">Avail. Colors</th>
                                    <th scope="col" style="color: #ffffff !important; opacity: 1;">Grade</th>
                                    <th scope="col" style="color: #ffffff !important; opacity: 1;">Gauge</th>
                                    <th scope="col" style="color: #ffffff !important; opacity: 1;">Type</th>
                                    <th scope="col" style="color: #ffffff !important; opacity: 1;">Profile</th>
                                    <th scope="col" style="color: #ffffff !important; opacity: 1;">Status</th>
                                    <th scope="col" style="color: #ffffff !important; opacity: 1;">Quantity</th>
                                    <th scope="col" style="color: #ffffff !important; opacity: 1;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody"></tbody>
                        </table>
                            
                        <div class="d-flex align-items-center justify-content-end py-1">
                            <p class="mb-0 fs-2">Rows per page:</p>
                            <select id="rowsPerPage" class="form-select w-auto ms-0 ms-sm-2 me-8 me-sm-4 py-1 pe-7 ps-2 border-0" aria-label="Rows per page">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100" selected>100</option>
                            </select>
                            <p id="paginationInfo" class="mb-0 fs-2"></p>
                            <nav aria-label="...">
                                <ul id="paginationControls" class="pagination justify-content-center mb-0 ms-8 ms-sm-9">
                                    <!-- Pagination buttons will be inserted here by JS -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="custom_trim_draw_modal">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h4 class="modal-title"></h4>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="drawing-body">

                </div>
            </div>
            <div class="modal-footer">
                <button id="saveDrawing" class="btn ripple btn-success" type="button">Save</button>
                <button class="btn ripple btn-danger" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="special_trim_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Special Trim Configuration</h4>
                <button aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"></button>
            </div>
            <div class="modal-body">
                <div id="special_trim_body">
                    <!-- Content here -->
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="trim_chart_modal">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Trim Chart</h6>
            </div>
            <div class="modal-body">
                <img id="chartImage" src="../assets/images/trim_chart.jpg" alt="Trim Chart" class="img-fluid">
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-danger" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="custom_chart_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h6 class="modal-title">Custom Chart Guide</h6>
            <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div id="custom_chart_container"></div>
        </div>
        <div class="modal-footer">
            <button class="btn ripple btn-danger" data-bs-dismiss="modal" type="button">Close</button>
        </div>
    </div>
  </div>
</div>
