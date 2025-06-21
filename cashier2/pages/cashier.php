<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);
require '../includes/dbconn.php';
require '../includes/functions.php';



$panel_id = 3;

$deliveryAmt = getDeliveryCost();
$addressSettings = getSettingAddressDetails();
$amtPerMile = getSettingAmtPerMile();

$lat = !empty($addressSettings['lat']) ? $addressSettings['lat'] : 0;
$lng = !empty($addressSettings['lng']) ? $addressSettings['lng'] : 0;

$latSettings = !empty($addressSettings['lat']) ? $addressSettings['lat'] : 0;
$lngSettings = !empty($addressSettings['lng']) ? $addressSettings['lng'] : 0;

$editEstimateId = isset($_GET['editestimate']) ? intval($_GET['editestimate']) : null;

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
    
    .line:last-child {
        cursor: pointer;
    }

    .context-menu {
        background-color: #ffffff;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        z-index: 9999;
        padding: 5px;
    }

    .context-menu ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .context-menu li {
        padding: 8px 12px;
        cursor: pointer;
    }

    .context-menu li:hover {
        background-color: #f1f1f1;
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
                        <button class="btn btn-primary m-2" data-bs-toggle="modal" data-bs-target="#trim_chart_modal" type="button">
                            View Trim Chart
                        </button>
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
                                                $query_profile = "
                                                    SELECT DISTINCT profile_type
                                                    FROM profile_type 
                                                    WHERE hidden = '0' $category_condition
                                                    ORDER BY profile_type ASC";
                                                $result_profile = mysqli_query($conn, $query_profile);
                                                while ($row_profile = mysqli_fetch_array($result_profile)) {
                                                ?>
                                                    <option value="<?= $row_profile['profile_type'] ?>">
                                                        <?= $row_profile['profile_type'] ?>
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
                                                <option value="<?= htmlspecialchars($row_grade['product_grade']) ?>" 
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
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 px-3 py-3">
                                <div class="ps-2">
                                    <div class="form-check mb-2 text-start">
                                        <input class="form-check-input" type="checkbox" id="toggleActive" checked>
                                        <label class="form-check-label" for="toggleActive" style="color:#fff;">
                                            Show only In Stock
                                        </label>
                                    </div>
                                    <div class="form-check mb-2 text-start">
                                        <input class="form-check-input" type="checkbox" id="onlyPromotions" value="true" data-filter="promotion" data-filter-name="On Promotions">
                                        <label class="form-check-label" for="onlyPromotions" style="color:#fff;">
                                            Show Promotions
                                        </label>
                                    </div>
                                    <div class="form-check mb-2 text-start">
                                        <input class="form-check-input" type="checkbox" id="onlyOnSale" value="true" data-filter="on-sale" data-filter-name="On Sale">
                                        <label class="form-check-label" for="onlyOnSale" style="color:#fff;">
                                            Show On Sale
                                        </label>
                                    </div>
                                </div>

                                <div class="py-2">
                                    <button type="button" class="btn btn-outline-primary reset_filters">
                                        <i class="fas fa-sync-alt me-1"></i> Reset Filters
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-10">
                    <div class="row mb-3">
                        <div class="col-sm-12 col-md-10">
                            <h5>Selected Items:</h5>
                            <div id="selected-tags"></div>
                        </div>
                        <div class="col-sm-12 col-md-2 d-flex justify-content-sm-start justify-content-end">
                            <button type="button" class="btn mb-2 me-2 flex-fill" id="view_order_list" style="background-color: rgb(1, 85, 187); color: white;">
                                <i class="fa fa-undo fs-4 me-2"></i>
                                Start Return
                            </button>
                        </div>
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
        <img id="chartImage" src="../assets/images/low_rib.jpg" alt="Trim Chart" class="img-fluid mb-4">

        <h6>Exposed Fasteners</h6>
        <div class="d-flex flex-wrap gap-2 mb-3">
          <button class="chart-btn btn btn-sm btn-outline-primary" data-img="../assets/images/low_rib.jpg">Low-Rib</button>
          <button class="chart-btn btn btn-sm btn-outline-primary" data-img="../assets/images/hi_rib.jpg">Hi-Rib</button>
          <button class="chart-btn btn btn-sm btn-outline-primary" data-img="../assets/images/corrugated.jpg">Corrugated</button>
          <button class="chart-btn btn btn-sm btn-outline-primary" data-img="../assets/images/5v.jpg">5-V</button>
        </div>

        <h6>Concealed Fasteners</h6>
        <div class="d-flex flex-wrap gap-2">
          <button class="chart-btn btn btn-sm btn-outline-primary" data-img="../assets/images/board_batten.jpg">Board & Batten</button>
          <button class="chart-btn btn btn-sm btn-outline-primary" data-img="../assets/images/standing_seam.jpg">Standing Seam 1.5in</button>
          <button class="chart-btn btn btn-sm btn-outline-primary" data-img="../assets/images/snap_lock.jpg">Snap Lock 1.75in</button>
          <button class="chart-btn btn btn-sm btn-outline-primary" data-img="../assets/images/mechanical_seam.jpg">Mechanical Seam 2in</button>
          <button class="chart-btn btn btn-sm btn-outline-primary" data-img="../assets/images/plank.jpg">Plank</button>
          <button class="chart-btn btn btn-sm btn-outline-primary" data-img="../assets/images/flush_mount.jpg">Flush Mount Soffit</button>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn ripple btn-danger" data-bs-dismiss="modal" type="button">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="view_cart_modal">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Cart Contents</h6>
                <button aria-label="Close" class="close text-light" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="cart-tbl"></div>
            </div>
            <div class="">
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="row gx-2 justify-content-between">
                            <div class="col-auto mb-2">
                                <button type="button" class="btn btn-danger px-3" id="clear_cart">
                                    <i class="fa fa-trash fs-5 me-2"></i> Clear Cart
                                </button>
                            </div>
                            <div class="col-auto mb-2">
                                <button type="button" class="btn btn-primary px-3" id="btnPriceGroupModal">
                                    <i class="fa fa-tag fs-5 me-2"></i> Change Price Group
                                </button>
                            </div>
                            <div class="col-auto mb-2">
                                <button type="button" class="btn btn-secondary px-3" id="btnGradeModal">
                                    <i class="fa fa-chart-line fs-5 me-2"></i> Change Grade
                                </button>
                            </div>
                            <div class="col-auto mb-2">
                                <button type="button" class="btn btn-info px-3 text-white" id="btnColorModal">
                                    <i class="fa fa-palette fs-5 me-2"></i> Change Color
                                </button>
                            </div>
                            <div class="col-auto mb-2">
                                <button type="button" class="btn px-3" id="btnApprovalModal" style="background-color: #800080; color: white;">
                                    <i class="fa fa-check-circle fs-5 me-2"></i> Submit Approval
                                </button>
                            </div>
                            <div class="col-auto mb-2">
                                <button type="button" class="btn px-3" id="view_order" style="background-color: rgb(0, 218, 47); color: white; text-shadow: 1px 1px 2px #000;">
                                    <i class="fa fa-shopping-cart fs-5 me-2"></i> Next
                                </button>
                            </div>
                            <div class="col-auto mb-2">
                                <button type="button" class="btn btn-danger px-3" data-bs-dismiss="modal">
                                    <i class="fa fa-times fs-5 me-2"></i> Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="view_est_list_modal">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Estimates List</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="estimates-tbl">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="view_est_details_modal">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Estimate Details</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="estimates-details">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="view_order_list_modal">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Orders List</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row align-items-end g-2 mb-3">
                    <div class="col-md-5">
                        <label for="return_order_id" class="form-label">Invoice #</label>
                        <input type="text" class="form-control" id="return_order_id" name="return_order_id" placeholder="Enter Order ID">
                    </div>
                    <div class="col-md-5">
                        <label for="return_customer_name" class="form-label">Customer Name</label>
                        <input type="text" class="form-control" id="return_customer_name" name="return_customer_name" placeholder="Enter Customer Name">
                        <input type="hidden" id="return_customer_id" name="return_customer_id">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="button" class="btn btn-primary" id="return_search_button">
                            <i class="fa fa-search mx-2"></i> Search
                        </button>
                    </div>
                </div>
                <div id="orders-tbl">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="view_order_details_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Order Details</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="order-details">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="return_stocking_fee_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Stocking Fee</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" class="form-control" id="return_id" name="return_id">
                <input type="hidden" class="form-control" id="return_quantity" name="return_quantity">
                <input type="hidden" class="form-control" id="price_to_return" name="price_to_return">
                <input type="hidden" class="form-control" id="is_store_credited" name="is_store_credited">
                <div class="col position-relative text-center">
                    <label for="return_stock_fee" class="form-label">Stocking Fee (%)</label>
                    <input type="number" class="form-control" id="return_stock_fee" name="return_stock_fee"
                        placeholder="Enter Stocking Fee(%)" min="0" max="25">
                    <div id="fee-warning" style="display:none; font-size:1.1em; color:red; font-weight:600; margin-top:5px;">
                        25% is the maximum allowed.
                    </div>
                </div>
                <div class="col mt-3 d-flex justify-content-between align-items-center">
                    <p id="return_stock_fee_display"></p>
                    <p id="return_price_display"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="return_finalize_btn">
                    <i class="fa fa-undo mx-2"></i> Return
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="view_estimate_modal">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Save Estimate</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="estimate-tbl"></div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-primary next" type="button" id="next_page_est">
                    <i class="fe fe-hard-drive"></i> Next
                </button>
                <button class="btn ripple btn-primary previous d-none" type="button" id="prev_page_est">
                    <i class="fe fe-hard-drive"></i> Previous
                </button>
                <button class="btn ripple btn-success d-none" type="button" id="save_estimate_modal">
                    <i class="fe fe-hard-drive"></i> Save
                </button>
                <a href="#" class="btn ripple btn-light text-dark d-none" type="button" id="print_estimate_category" target="_blank">
                    <i class="fe fe-print"></i> Print Details
                </a>
                <a href="#" class="btn ripple btn-warning text-dark d-none" type="button" id="print_estimate" target="_blank">
                    <i class="fe fe-print"></i> Print Total
                </a>
                <button class="btn ripple btn-danger" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="viewDetailsModal"></div>

<div class="modal" id="viewInStockmodal"></div>

<div class="modal" id="viewOutOfStockmodal"></div>

<div class="modal" id="viewAvailablemodal"></div>

<div class="modal" id="viewAvailableColormodal"></div>

<div class="modal" id="cashmodal">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Checkout</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="order-tbl"></div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-warning next" type="button" id="save_estimate">
                    <i class="fe fe-hard-drive"></i> Save Estimate
                </button>
                <button class="btn ripple btn-primary previous d-none" type="button" id="prev_page_order">
                    <i class="fe fe-hard-drive"></i> Previous
                </button>
                <button class="btn ripple btn-success d-none" type="button" id="save_order">
                    <i class="fe fe-hard-drive"></i> Save
                </button>
                <a href="#" class="btn ripple btn-light text-dark d-none" type="button" id="print_order_category" target="_blank">
                    <i class="fe fe-print"></i> Print Details
                </a>
                <a href="#" class="btn ripple btn-warning text-dark d-none" type="button" id="print_order" target="_blank">
                    <i class="fe fe-print"></i> Print Total
                </a>
                <a href="#" class="btn ripple btn-info d-none" type="button" id="print_deliver" target="_blank">
                    <i class="fe fe-print"></i> Print Delivery
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="map1Modal" tabindex="-1" role="dialog" aria-labelledby="mapsModalLabel" aria-hidden="true" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapsModalLabel">Search Address</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="mapForm" class="form-horizontal">
              <div class="modal-body">
                  <div class="mb-2">
                      <input id="searchBox1" class="form-control" placeholder="<?= $addressDetails ?>" list="address1-list" autocomplete="off">
                      <datalist id="address1-list"></datalist>
                  </div>
                  <div id="map1" class="map-container" style="height: 60vh; width: 100%;"></div>
              </div>
              <div class="modal-footer">
                  <div class="form-actions">
                      <div class="card-body">
                          <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Cancel</button>
                      </div>
                  </div>
              </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="confirm_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div id="confirmHeaderContainer" class="modal-header align-items-center modal-colored-header">
                <h4 id="confirmHeader" class="text-center m-0 p-2 pb-0">Are you Sure?</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 class="text-muted px-2">Add the product to cart with <span class="text-warning">VENTED</span> Panel Type?</h5>
            </div>
            <div class="text-center mb-2">
                <button type="button" id="confirm_yes_btn" class="btn bg-success-subtle text-success waves-effect text-start">
                    Yes
                </button>
                <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">
                    No
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="approval_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header align-items-center modal-colored-header pb-0">
                <h4 id="responseHeader" class="m-0"></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 class="text-center pt-0"> Are you sure you want to submit these items for approval?</h5>
            </div>
            <div class="text-center p-3">
                <button type="button" id="submitApprovalBtn" class="btn bg-success-subtle waves-effect text-start">
                    Yes
                </button>
                <button type="button" class="btn bg-danger-subtle waves-effect text-start" data-bs-dismiss="modal">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="response_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div id="responseHeaderContainer" class="modal-header align-items-center modal-colored-header">
            <h4 id="responseHeader" class="m-0"></h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-center" id="responseMsg"></p>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">
                Close
            </button>
        </div>
    </div>
    </div>
</div>

<div class="modal fade" id="chng_color_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Color</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="change_color_container"></div>
            </div>
            <div class="modal-footer">
                <div class="form-actions">
                    <div class="card-body">
                        <button type="button" id="save_color_change" class="btn bg-success-subtle text-light waves-effect text-start">Save</button>
                        <button type="button" class="btn bg-danger-subtle text-light waves-effect text-start" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="chng_price_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Price Group</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="change_price_container"></div>
            </div>
            <div class="modal-footer">
                <div class="form-actions">
                    <div class="card-body">
                        <button type="button" id="save_price_change" class="btn bg-success-subtle text-light waves-effect text-start">Save</button>
                        <button type="button" class="btn bg-danger-subtle text-light waves-effect text-start" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="chng_grade_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Grade</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="change_grade_container"></div>
            </div>
                <div class="modal-footer">
                    <div class="form-actions">
                        <div class="card-body">
                            <button type="button" id="save_grade_change" class="btn bg-success-subtle text-light waves-effect text-start">Save</button>
                            <button type="button" class="btn bg-danger-subtle text-light waves-effect text-start" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            
        </div>
    </div>
</div>

<div class="modal fade" id="prompt_quantity_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form id="quantity_form" class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Metal Panel Configuration</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="qty_prompt_container"></div>
            </div>
        </form>
        </div>
    </div>
</div>

<div class="modal fade" id="trim_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form id="trim_form" class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Trim Configuration</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="trim_container"></div>
            </div>
        </form>
        </div>
    </div>
</div>

<div class="modal fade" id="custom_length_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form id="custom_length_form" class="modal-content modal-content-demo">
            <div class="modal-header">
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="custom_length_container"></div>
            </div>
        </form>
        </div>
    </div>
</div>

<div class="modal fade" id="custom_truss_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <form id="custom_truss_form" class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Custom Truss</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="custom_truss_container"></div>
            </div>
        </form>
        </div>
    </div>
</div>

<div class="modal fade" id="prompt_job_name_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog" role="document">
        <form id="job_name_form" class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">New Job Name</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="job_name_prompt_container">
                    <div class="job_name_input">
                        <div class="mb-2">
                            <label class="fs-5 fw-bold" for="job_name">Job Name</label>
                            <input id="job_name" name="job_name" class="form-control" placeholder="Enter Job Name" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success ripple btn-secondary" data-bs-dismiss="modal" type="submit">Save</button>
                <button class="btn btn-danger ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </form>
        </div>
    </div>
</div>

<div class="modal fade" id="order_product_modal" tabindex="-1" data-bs-backdrop="static" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Products to Order</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body mt-0" id="order_product_container">
            </div>
            <div class="modal-footer">
                <button class="btn ripple fw-bold text-white me-auto" type="button" id="view_order_product_list" 
                    style="background-color: rgb(108, 111, 114); border-color:rgb(108, 111, 114);">
                    <i class="fas fa-list" style="color: #E3F2FD;"></i> View Saved Orders
                </button>
                <button class="btn ripple fw-bold text-white" type="button" id="save_order_supplier" 
                    style="background-color: #17A2B8; border-color: #138496;">
                    <i class="fas fa-save" style="color: #E3F2FD;"></i> Save Order
                </button>
                <button class="btn btn-danger ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="view_order_product_list_modal" tabindex="-1" data-bs-backdrop="static" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Saved Orders List</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="orders-saved-tbl">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="view_order_product_details_modal" tabindex="-1" data-bs-backdrop="static" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Saved Order Details</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="order-saved-details">
                </div>
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


<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>

<script>
    let map1;
    let marker1;
    let lat1 = <?= $lat ?>, lng1 = <?= $lng ?>;
    let lat2 = <?= $latSettings ?>, lng2 = <?= $lngSettings ?>;
    var amtPerMile = <?= $amtPerMile ?>;
    var amtDeliveryDefault = <?= $deliveryAmt ?? 0 ?>;

    $('#searchBox1').on('input', function() {
        updateSuggestions('#searchBox1', '#address1-list');
    });

    $('#address').on('input', function() {
        updateSuggestions('#address', '#address-data-list');
    });

    function updateSuggestions(inputId, listId) {
        var query = $(inputId).val();
        if (query.length >= 2) {
            $.ajax({
                url: `https://nominatim.openstreetmap.org/search`,
                data: {
                    q: query,
                    format: 'json',
                    addressdetails: 1,
                    limit: 5
                },
                dataType: 'json',
                success: function(data) {
                    var datalist = $(listId);
                    datalist.empty();
                    data.forEach(function(item) {
                        var option = $('<option>')
                            .attr('value', item.display_name)
                            .data('lat', item.lat)
                            .data('lon', item.lon);
                        datalist.append(option);
                    });
                }
            });
        }
    }

    function getPlaceName(lat, lng, inputId) {
        const url = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json&addressdetails=1`;

        console.log(lat)

        $.ajax({
            url: url,
            dataType: 'json',
            success: function(data) {
                if (data && data.display_name) {
                    $(inputId).val(data.display_name);

                    let address = data.address;
                    $('#order_deliver_address').val(
                        address.road || 
                        address.neighbourhood || 
                        address.suburb || 
                        ''
                    );
                    $('#order_deliver_city').val(
                        address.city || 
                        address.town || 
                        address.village || 
                        ''
                    );
                    $('#order_deliver_state').val(
                        address.state || 
                        address.province || 
                        address.region || 
                        address.county || 
                        ''
                    );
                    $('#order_deliver_zip').val(address.postcode || '');

                    $('#lat').val(lat);
                    $('#lng').val(lng);

                    $('#est_deliver_address').val(
                        address.road || 
                        address.neighbourhood || 
                        address.suburb || 
                        ''
                    );
                    $('#est_deliver_city').val(
                        address.city || 
                        address.town || 
                        address.village || 
                        ''
                    );
                    $('#est_deliver_state').val(
                        address.state || 
                        address.province || 
                        address.region || 
                        address.county || 
                        ''
                    );
                    $('#est_deliver_zip').val(address.postcode || '');

                    $('#est_lat').val(lat);
                    $('#est_lng').val(lng);

                    calculateDeliveryAmount();
                    calculateDeliveryAmountEst();

                } else {
                    console.error("Address not found for these coordinates.");
                    $(inputId).val("Address not found");
                }
            },
            error: function() {
                console.error("Error retrieving address from Nominatim.");
                $(inputId).val("Error retrieving address");
            }
        });
    }

    $('#searchBox1').on('change', function() {
        let selectedOption = $('#address1-list option[value="' + $(this).val() + '"]');
        lat1 = parseFloat(selectedOption.data('lat'));
        lng1 = parseFloat(selectedOption.data('lon'));
        
        updateMarker(map1, marker1, lat1, lng1, "Starting Point");
        getPlaceName(lat1, lng1, '#searchBox1');
    });

    $('#address').on('change', function() {
        let selectedOption = $('#address-data-list option[value="' + $(this).val() + '"]');
        lat1 = parseFloat(selectedOption.data('lat'));
        lng1 = parseFloat(selectedOption.data('lon'));
        
        updateMarker(map1, marker1, lat1, lng1, "Starting Point");
        getPlaceName(lat1, lng1, '#address');
    });

    function updateMarker(map, marker, lat, lng, title) {
        if (!map) return;
        const position = new google.maps.LatLng(lat, lng);
        if (marker) {
            marker.setMap(null);
        }
        marker = new google.maps.Marker({
            position: position,
            map: map,
            title: title
        });
        map.setCenter(position);
        return marker;
    }

    function initMaps() {
        map1 = new google.maps.Map(document.getElementById("map1"), {
            center: { lat: <?= $lat ?>, lng: <?= $lng ?> },
            zoom: 16,
        });
        marker1 = updateMarker(map1, marker1, lat1, lng1, "Starting Point");
        getPlaceName(lat1, lng1, '#searchBox1');
        google.maps.event.addListener(map1, 'click', function(event) {
            lat1 = event.latLng.lat();
            lng1 = event.latLng.lng();
            marker1 = updateMarker(map1, marker1, lat1, lng1, "Starting Point");
            getPlaceName(lat1, lng1, '#searchBox1');
        });
    }

    function loadGoogleMapsAPI() {
        const script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDRPyR0tSWQUm4sR0BwqDxSjVsdHXQvw7U&callback=initMaps&libraries=geometry,places';
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    }

    window.onload = loadGoogleMapsAPI;

    function calculateDeliveryAmount() {
        var customerLat = parseFloat($('#lat').val());
        var customerLng = parseFloat($('#lng').val());
        var payable_amt = parseFloat($('#payable_amt').val());
        var store_credit = parseFloat($('#store_credit').val());
        var isapplystorecredit = $('#applyStoreCredit').is(':checked');
        var deliver_method = $('input[name="order_delivery_method"]:checked').val();

        var lat2Float = typeof lat2 !== 'undefined' ? parseFloat(lat2) : 0;
        var lng2Float = typeof lng2 !== 'undefined' ? parseFloat(lng2) : 0;

        let deliveryAmount = 0;

        if (deliver_method === 'pickup') {
            deliveryAmount = 0;
        } else {
            if (
                !isNaN(customerLat) && !isNaN(customerLng) &&
                !isNaN(lat2Float) && !isNaN(lng2Float) &&
                customerLat !== 0 && customerLng !== 0 &&
                lat2Float !== 0 && lng2Float !== 0
            ) {
                const point1 = new google.maps.LatLng(customerLat, customerLng);
                const point2 = new google.maps.LatLng(lat2Float, lng2Float);
                const distanceInMeters = google.maps.geometry.spherical.computeDistanceBetween(point1, point2);
                const distanceInMiles = distanceInMeters / 1609.34;
                deliveryAmount = parseFloat((amtDeliveryDefault + (amtPerMile * distanceInMiles)).toFixed(2));
            } else {
                deliveryAmount = parseFloat(amtDeliveryDefault.toFixed(2));
            }
        }

        var store_credit_calc = 0;
        const totalBeforeCredit = payable_amt + deliveryAmount;

        if (isapplystorecredit) {
            store_credit_calc = Math.min(store_credit, totalBeforeCredit);
            $('#storeCreditValue').text(`-$${store_credit_calc.toFixed(2)}`);
            $('#storeCreditDisplay').removeClass('d-none');
        } else {
            $('#storeCreditDisplay').addClass('d-none');
            $('#storeCreditValue').text('');
            store_credit_calc = 0;
        }

        const raw_total = totalBeforeCredit - store_credit_calc;
        const total_amt = Math.max(0, raw_total).toFixed(2);

        $('#delivery_amt').val(deliveryAmount).trigger('change');
        $('#order_delivery_amt').text(deliveryAmount.toFixed(2));
        $('#order_total').text(total_amt);
    }


    function calculateDeliveryAmountEst() {
        var customerLat = parseFloat($('#est_lat').val());
        var customerLng = parseFloat($('#est_lng').val());
        var lat2Float = parseFloat(lat2);
        var lng2Float = parseFloat(lng2);

        var deliver_method = $('#est_delivery_method').val();

        if(deliver_method == 'pickup'){
            $('#est_delivery_amt').val(0).trigger('change');
        }else{
            if (customerLat !== 0 && customerLng !== 0 && lat2Float !== 0 && lng2Float !== 0) {
                const point1 = new google.maps.LatLng(customerLat, customerLng);
                const point2 = new google.maps.LatLng(lat2Float, lng2Float);
                const distanceInMeters = google.maps.geometry.spherical.computeDistanceBetween(point1, point2);
                const distanceInMiles = distanceInMeters / 1609.34;
                var deliveryAmount = amtPerMile * distanceInMiles;
                deliveryAmount = deliveryAmount.toFixed(2);
            } else {
                deliveryAmount = amtDeliveryDefault.toFixed(2);
            }

            $('#est_delivery_amt').val(deliveryAmount).trigger('change');
        }
    }

    function updateColor(element){
        var color = $(element).val();
        var id = $(element).data('id');
        var line = $(element).data('line');

        $.ajax({
            url: 'pages/cashier_ajax.php',
            type: 'POST',
            data: {
                color_id: color,
                id: id,
                line: line,
                set_color: "set_color"
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updateGrade(element){
        var grade = $(element).val();
        var id = $(element).data('id');
        var line = $(element).data('line');

        $.ajax({
            url: 'pages/cashier_ajax.php',
            type: 'POST',
            data: {
                grade: grade,
                id: id,
                line: line,
                set_grade: "set_grade"
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }
    
    function updateEstimateBend(element){
        var bend = $(element).val();
        var id = $(element).data('id');
        var line = $(element).data('line');

        $.ajax({
            url: 'pages/cashier_ajax.php',
            type: 'POST',
            data: {
                bend: bend,
                id: id,
                line: line,
                set_estimate_bend: "set_estimate_bend"
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updateEstimateHem(element){
        var hem = $(element).val();
        var id = $(element).data('id');
        var line = $(element).data('line');

        $.ajax({
            url: 'pages/cashier_ajax.php',
            type: 'POST',
            data: {
                hem: hem,
                id: id,
                line: line,
                set_estimate_hem: "set_estimate_hem"
            },
            success: function(response) {
                loadCart();
                loadOrderContents();
                loadEstimateContents();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updateEstimateLength(element){
        var length = $(element).val();
        var id = $(element).data('id');
        var line = $(element).data('line');

        $.ajax({
            url: 'pages/cashier_ajax.php',
            type: 'POST',
            data: {
                length: length,
                id: id,
                line: line,
                set_estimate_length: "set_estimate_length"
            },
            success: function(response) {
                loadCart();
                loadOrderContents();
                loadEstimateContents();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updateEstimateLengthInch(element){
        var length_inch = $(element).val();
        var id = $(element).data('id');
        var line = $(element).data('line');

        $.ajax({
            url: 'pages/cashier_ajax.php',
            type: 'POST',
            data: {
                length_inch: length_inch,
                id: id,
                line: line,
                set_estimate_length_inch: "set_estimate_length_inch"
            },
            success: function(response) {
                loadCart();
                loadOrderContents();
                loadEstimateContents();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updateEstimateHeight(element){
        var height = $(element).val();
        var id = $(element).data('id');
        var line = $(element).data('line');

        $.ajax({
            url: 'pages/cashier_ajax.php',
            type: 'POST',
            data: {
                height: height,
                id: id,
                line: line,
                set_estimate_height: "set_estimate_height"
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updateEstimateWidth(element){
        var width = $(element).val();
        var id = $(element).data('id');
        var line = $(element).data('line');
        $.ajax({
            url: 'pages/cashier_ajax.php',
            type: 'POST',
            data: {
                width: width,
                id: id,
                line: line,
                set_estimate_width: "set_estimate_width"
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updateUsage(element){
        var usage = $(element).val();
        var id = $(element).data('id');
        var line = $(element).data('line');
        $.ajax({
            url: 'pages/cashier_ajax.php',
            type: 'POST',
            data: {
                usage: usage,
                id: id,
                line: line,
                set_usage: "set_usage"
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadDrawingModal(element){
        var id = $(element).data('id');
        var line = $(element).data('line');
        $.ajax({
            url: 'pages/cashier_drawing_modal.php',
            type: 'POST',
            data: {
                id: id,
                line: line,
                fetch_drawing: "fetch_drawing"
            },
            success: function(response) {
                $('#drawing-body').html(response);
                initializeDrawingApp();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadEstimatesList(){
        $.ajax({
            url: 'pages/cashier_est_list_modal.php',
            type: 'POST',
            data: {
                fetch_est_list: "fetch_est_list"
            },
            success: function(response) {
                $('#estimates-tbl').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadEstimatesDetails(estimate_id){
        $.ajax({
            url: 'pages/cashier_est_details_modal.php',
            type: 'POST',
            data: {
                estimateid: estimate_id,
                fetch_est_details: "fetch_est_details"
            },
            success: function(response) {
                $('#estimates-details').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadOrderList(){
        var orderid = $('#return_order_id').val();
        var customer_id = $('#return_customer_id').val();
        $.ajax({
            url: 'pages/cashier_order_list_modal.php',
            type: 'POST',
            data: {
                orderid: orderid,
                customer_id: customer_id,
                fetch_order_list: "fetch_order_list"
            },
            success: function(response) {
                $('#orders-tbl').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    $("#return_customer_name").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "pages/cashier_ajax.php",
                type: 'post',
                dataType: "json",
                data: {
                    search_customer: request.term
                },
                success: function(data) {
                    response(data);
                },
                error: function(xhr, status, error) {
                    console.log("Error: " + xhr.responseText);
                }
            });
        },
        select: function(event, ui) {
            $('#return_customer_name').val(ui.item.label);
            $('#return_customer_id').val(ui.item.value);
            return false;
        },
        appendTo: "#view_order_list_modal", 
        open: function() {
            $(".ui-autocomplete").css("z-index", 1050);
        }
    });

    function loadOrderSupplierList(){
        $.ajax({
            url: 'pages/cashier_order_product_modal.php',
            type: 'POST',
            data: {
                fetch_order_saved: "fetch_order_saved"
            },
            success: function(response) {
                $('#orders-saved-tbl').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadOrderSupplierDetails(orderid){
        $.ajax({
            url: 'pages/cashier_order_product_modal.php',
            type: 'POST',
            data: {
                orderid: orderid,
                fetch_order_product_details: "fetch_order_product_details"
            },
            success: function(response) {
                $('#order-saved-details').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadOrderDetails(orderid){
        $.ajax({
            url: 'pages/cashier_order_details_modal.php',
            type: 'POST',
            data: {
                orderid: orderid,
                fetch_order_details: "fetch_order_details"
            },
            success: function(response) {
                $('#order-details').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadCart(){      
        $.ajax({
            url: 'pages/cashier_cart_modal.php',
            type: 'POST',
            data: {
                fetch_cart: "fetch_cart"
            },
            success: function(response) {
                $('#cart-tbl').html(''); 
                $('#cart-tbl').html(response); 
                loadCartItemsHeader();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadOrderProductCart(){      
        $.ajax({
            url: 'pages/cashier_order_product_modal.php',
            type: 'POST',
            data: {
                fetch_order_supplier: "fetch_order_supplier"
            },
            success: function(response) {
                $('#order_product_container').html(''); 
                $('#order_product_container').html(response); 
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadOrderContents(){
        $.ajax({
            url: 'pages/cashier_order_modal.php',
            type: 'POST',
            data: {
                fetch_order: "fetch_order"
            },
            success: function(response) {
                $('#order-tbl').html('');
                $('#order-tbl').html(response);
                calculateDeliveryAmount();
                loadCartItemsHeader();
                $('#prev_page_order').removeClass("d-none");
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadEstimateContents(){
        $.ajax({
            url: 'pages/cashier_estimate_modal.php',
            type: 'POST',
            data: {
                fetch_estimate: "fetch_estimate"
            },
            success: function(response) {
                $('#estimate-tbl').html('');
                $('#estimate-tbl').html(response);
                calculateDeliveryAmountEst();
                loadCartItemsHeader();
                $('#next_page_est').removeClass("d-none");
                $('#prev_page_est').addClass("d-none");
                $('#save_estimate').addClass("d-none");
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }
    
    function addtocart(element) {
        var product_id = $(element).data('id');
        var line = $(element).data('line');

        $.ajax({
            url: "pages/cashier_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                line: line,
                modifyquantity: 'modifyquantity',
                addquantity: 'addquantity'
            },
            success: function(data) {
                console.log(data);
                loadCart();
                loadOrderContents();
                loadEstimateContents();
                loadOrderProductCart();
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }

    function updatequantity(element) {
        var product_id = $(element).data('id');
        var line = $(element).data('line');
        var qty = $(element).val();
        $.ajax({
            url: "pages/cashier_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                line: line,
                qty: qty,
                modifyquantity: 'modifyquantity',
                setquantity: 'setquantity'
            },
            success: function(data) {
                loadCart();
                loadOrderContents();
                loadEstimateContents();
                loadOrderProductCart();
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }

    function addquantity(element) {
        var product_id = $(element).data('id');
        var line = $(element).data('line');
        var input_quantity = $('input[data-id="' + product_id + '"]');
        var quantity = Number(input_quantity.val());
        $.ajax({
            url: "pages/cashier_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                line: line,
                quantity: quantity,
                modifyquantity: 'modifyquantity',
                addquantity: 'addquantity'
            },
            success: function(data) {
                loadCart();
                loadOrderContents();
                loadEstimateContents();
                loadOrderProductCart();
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }

    function deductquantity(element) {
        var product_id = $(element).data('id');
        var line = $(element).data('line');
        var input_quantity = $('input[data-id="' + product_id + '"]');
        var quantity = Number(input_quantity.val());
        $.ajax({
            url: "pages/cashier_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                line: line,
                quantity: quantity,
                modifyquantity: 'modifyquantity',
                deductquantity: 'deductquantity'
            },
            success: function(data) {
                loadCart();
                loadOrderContents();
                loadEstimateContents();
                loadOrderProductCart();
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }

    function delete_item(element) {
        var id = $(element).data('id');
        var line = $(element).data('line');
        $.ajax({
            url: "pages/cashier_ajax.php",
            data: {
                product_id_del: id,
                line: line,
                deleteitem: 'deleteitem'
            },
            type: "POST",
            success: function(data) {
                loadCart();
                loadOrderContents();
                loadEstimateContents();
                loadOrderProductCart();
            },
            error: function() {}
        });
    }

    function duplicate_item(element) {
        var product_id = $(element).data('id');
        var line = $(element).data('line');
        $.ajax({
            url: "pages/cashier_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                line: line,
                modifyquantity: 'modifyquantity',
                duplicate_product: 'duplicate_product'
            },
            success: function(data) {
                loadCart();
                loadOrderContents();
                loadEstimateContents();
                loadOrderProductCart();
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }

    function decodeHtmlEntities(text) {
        var doc = new DOMParser().parseFromString(text, 'text/html');
        return doc.documentElement.textContent;
    }

    function initializeDrawingApp() {
        const canvas = document.getElementById('drawingCanvas');
        const ctx = canvas.getContext('2d');
        const clearButton = document.getElementById('resetBtn');
        const saveDrawing = document.getElementById('saveDrawing');
        const lineColorPicker = document.getElementById('lineColorPicker');
        const undoButton = document.getElementById('undoBtn');
        const redoButton = document.getElementById('redoBtn');
        const dataInput = document.getElementById('initial_drawing_data');

        let points = [];
        let lengths = [];
        let angles = [];
        let colors = [];
        let images = [];
        let currentStartPoint = null;
        let undoStack = [];
        let redoStack = [];
        let lineTypes = [];
        let pixelsPerInch = 96;
        let currentColor = "#000000";
        let hemHeight = 15;

        let isDragging = false;
        let dragIndex = -1;
        let wasDragging = false;
        let dragStartSnapshot = null;
        let isLoading = true;
        let showAngles = false;

        let isResizing = false;
        let isRotating = false;
        let dragHandle = null;

        let isTemporaryLineActive = true;
        let hemImages = {
            flat: new Image(),
            open: new Image()
        };

        let arrows = [];

        let isDrawingArrow = false;
        let isDraggingArrow = false;
        let arrowStartPoint = null;
        let arrowEndPoint = null;

        let dragArrowIndex = -1;
        let draggingArrowPoint = null;

        let contextMenu = null;

        hemImages.flat.src = '../images/hems1.png';
        hemImages.open.src = '../images/hems2.png';

        const drawPlaceholderText = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.font = "30px Arial";
            ctx.textAlign = "center";

            if (isLoading) {
                ctx.fillStyle = "lightgray";
                ctx.fillText("Loading...", canvas.width / 2, canvas.height / 2);
            } else {
                ctx.fillStyle = "lightgray";
                ctx.fillText("Draw here", canvas.width / 2, canvas.height / 2);
            }
        };
        
        if (dataInput && dataInput.value) {
            isLoading = true;
            drawPlaceholderText();

            try {
                let decodedData = decodeHtmlEntities(dataInput.value);
                decodedData = decodedData.replace(/\\"/g, '"').replace(/\\\\/g, '\\');
                let loadedData = JSON.parse(decodedData);

                points = loadedData.points || [];
                lengths = loadedData.lengths || [];
                angles = loadedData.angles || [];
                colors = loadedData.colors || [];
                lineTypes = loadedData.lineTypes || [];
                arrows = loadedData.arrows || [];

                images = [];
                if (Array.isArray(loadedData.images)) {
                    for (let item of loadedData.images) {
                        if (item && item.src) {
                            let img = new Image();
                            img.src = item.src;
                            img.onload = () => redrawCanvas();

                            images.push({
                                img: img,
                                x: item.x,
                                y: item.y,
                                width: item.width,
                                height: item.height,
                                rotation: item.rotation || 0
                            });
                        }
                    }
                }

                currentStartPoint = points.length > 0 ? points[points.length - 1] : null;
            } catch (e) {
                console.error("Invalid drawing data:", e);
            }

            isLoading = false;
        } else {
            isLoading = false;
            drawPlaceholderText();
        }

        function decodeHtmlEntities(text) {
            var doc = new DOMParser().parseFromString(text, 'text/html');
            return doc.documentElement.textContent;
        }

        function adjustLineAngle(p1, p2, newAngle) {
            newAngle = (newAngle % 360 + 360) % 360;

            const length = Math.hypot(p2.x - p1.x, p2.y - p1.y);
            const angleRad = (newAngle * Math.PI) / 180;

            const newX = p1.x + Math.cos(angleRad) * length;
            const newY = p1.y + Math.sin(angleRad) * length;

            points[points.indexOf(p2)] = { x: newX, y: newY };

            undoStack.push({
                points: [...points.map(p => ({ ...p }))],
                lengths: [...lengths],
                angles: [...angles],
                colors: [...colors]
            });

            redoStack = [];
            redrawCanvas();
        }

        function calculateLineAngle(p1, p2) {
            if (!p1 || !p2) return null;

            const angle = Math.atan2(p2.y - p1.y, p2.x - p1.x);

            let degrees = (angle * 180 / Math.PI) % 360;

            if (degrees < 0) degrees += 360;

            return degrees;
        }

        function updateImageProperty(index, prop, value) {
            const image = images[index];
            if (!image) return;

            undoStack.push({
                points: [...points],
                lengths: [...lengths],
                angles: [...angles],
                colors: [...colors],
                images: images.map(img => ({ ...img }))
            });

            if (prop === 'width') {
                const newWidthPx = value * pixelsPerInch;
                const aspectRatio = image.height / image.width;
                image.width = newWidthPx;
                image.height = newWidthPx * aspectRatio;
            } else if (prop === 'rotation') {
                image.rotation = value;
            }

            redrawCanvas();
        }

        function isPointInRotatedRect(px, py, img) {
            const cx = img.x;
            const cy = img.y;
            const width = img.width;
            const height = img.height;
            const angle = (img.rotation || 0) * Math.PI / 180;

            const dx = px - cx;
            const dy = py - cy;

            const rx = dx * Math.cos(-angle) - dy * Math.sin(-angle);
            const ry = dx * Math.sin(-angle) + dy * Math.cos(-angle);

            return Math.abs(rx) <= width / 2 && Math.abs(ry) <= height / 2;
        }

        function drawHemImage(p1, p2, img, flip = false) {
            if (!img.complete) return;

            const dx = p2.x - p1.x;
            const dy = p2.y - p1.y;
            const length = Math.sqrt(dx * dx + dy * dy);

            const imgHeight = img.height;
            const desiredHeight = hemHeight;

            const scaleX = length / img.width;
            const scaleY = desiredHeight / imgHeight;

            const angle = Math.atan2(dy, dx);

            ctx.save();
            ctx.translate(p1.x, p1.y);
            ctx.rotate(angle);

            if (flip) {
                ctx.scale(-1, 1);
                ctx.drawImage(img, -img.width * scaleX, -desiredHeight / 2, img.width * scaleX, imgHeight * scaleY);
            } else {
                ctx.drawImage(img, 0, -desiredHeight / 2, img.width * scaleX, imgHeight * scaleY);
            }

            ctx.restore();
        }

        function drawArrow(from, to, color = '#90EE90') {
            const headLength = 30;
            const angle = Math.atan2(to.y - from.y, to.x - from.x);

            ctx.strokeStyle = '#90EE90';
            ctx.lineWidth = 5;

            ctx.beginPath();
            ctx.moveTo(from.x, from.y);
            ctx.lineTo(to.x, to.y);
            ctx.stroke();

            ctx.beginPath();
            ctx.moveTo(to.x, to.y);
            ctx.lineTo(
                to.x - headLength * Math.cos(angle - Math.PI / 6),
                to.y - headLength * Math.sin(angle - Math.PI / 6)
            );
            ctx.moveTo(to.x, to.y);
            ctx.lineTo(
                to.x - headLength * Math.cos(angle + Math.PI / 6),
                to.y - headLength * Math.sin(angle + Math.PI / 6)
            );
            ctx.stroke();
        }

        const updateLineEditor = () => {
            const editorList = document.getElementById('lineEditorList');
            editorList.innerHTML = '';

            let visibleLineIndex = 1;

            for (let i = 1; i < points.length; i++) {
                const p1 = points[i - 1];
                const p2 = points[i];

                if (!p1 || !p2) continue;

                const distance = calculateDistance(p1, p2);
                
                if (isNaN(distance)) {
                    console.error(`Invalid distance for line ${i}`);
                    continue;
                }

                const angle = calculateLineAngle(p1, p2);
                const isFirstOrLastLine = 
                        i === 1 || 
                        i === points.length - 1 || 
                        points[i - 1] === null || 
                        points[i + 1] === null;

                const lineDiv = document.createElement('div');
                lineDiv.className = 'mb-2 py-1 px-2 border rounded bg-light';

                lineDiv.innerHTML = `
                    <div class="row g-2 align-items-center">
                        <div class="col-1">
                            <span class="fw-bold">L${visibleLineIndex++}:</span>
                        </div>

                        <div class="col-4 d-flex align-items-center gap-2">
                            <label class="fw-bold mb-0">Length</label>
                            <input type="number" step="0.01" value="${distance.toFixed(2)}" data-index="${i}"
                                class="form-control form-control-sm line-length-input" style="width: 100%;">
                        </div>

                        <div class="col-3 d-flex align-items-center gap-2">
                            <label class="fw-bold mb-0">Angle</label>
                            <input type="number" step="0.1" value="${angle.toFixed(1)}" data-index="${i}"
                                class="form-control form-control-sm line-angle-input" style="width: 100%;">
                        </div>

                        ${isFirstOrLastLine ? `
                        <div class="col-3 d-flex align-items-center gap-2">
                            <label class="fw-bold mb-0">Hem</label>
                            <select class="form-select form-select-sm line-hem-select" data-index="${i}">
                                <option value="normal" ${lineTypes[i] === 'normal' ? 'selected' : ''}>None</option>
                                <option value="flat" ${lineTypes[i] === 'flat' ? 'selected' : ''}>Flat</option>
                                <option value="open" ${lineTypes[i] === 'open' ? 'selected' : ''}>Open</option>
                            </select>
                        </div>
                        ` : ''}

                        <div class="col-1 d-flex align-items-center justify-content-center">
                            <a href="javascript:void(0)" class="delete-line-btn" data-index="${i}">&times;</a>
                        </div>
                    </div>
                `;

                editorList.appendChild(lineDiv);
            }

            images.forEach((imgObj, idx) => {
                const imageRow = document.createElement('div');
                imageRow.className = 'mb-2 py-1 px-2 border rounded bg-light';

                imageRow.innerHTML = `
                    <div class="row g-2 align-items-center">
                        <div class="col-2">
                            <span class="fw-bold">Image ${idx + 1}:</span>
                        </div>

                        <div class="col-4 d-flex align-items-center gap-2">
                            <label class="fw-bold mb-0">Width (in)</label>
                            <input type="number" step="0.01" value="${(imgObj.width / pixelsPerInch).toFixed(2)}" data-index="${idx}" data-prop="width"
                                class="form-control form-control-sm image-prop-input" style="width: 100%;">
                        </div>

                        <div class="col-4 d-flex align-items-center gap-2">
                            <label class="fw-bold mb-0">Angle</label>
                            <input type="number" step="0.1" value="${imgObj.rotation.toFixed(1)}" data-index="${idx}" data-prop="rotation"
                                class="form-control form-control-sm image-prop-input" style="width: 100%;">
                        </div>

                        <div class="col-1 d-flex align-items-center justify-content-center">
                            <a href="javascript:void(0)" class="delete-image-btn text-danger" data-index="${idx}">&times;</a>
                        </div>
                    </div>
                `;

                editorList.appendChild(imageRow);
            });

            document.querySelectorAll('.delete-image-btn').forEach(button => {
                button.addEventListener('click', (e) => {
                    const index = parseInt(e.target.dataset.index);
                    images.splice(index, 1);
                    redrawCanvas();
                    updateLineEditor();
                });
            });

            document.querySelectorAll('.delete-line-btn').forEach(button => {
                button.addEventListener('click', (e) => {
                    const index = parseInt(e.target.dataset.index);
                    deleteLine(index);
                });
            });

            document.querySelectorAll('.image-prop-input').forEach(input => {
                input.addEventListener('change', (e) => {
                    const index = parseInt(e.target.dataset.index);
                    const prop = e.target.dataset.prop;
                    let value = parseFloat(e.target.value);

                    if (!isNaN(value)) {
                        updateImageProperty(index, prop, value);
                    }
                });
            });

            document.querySelectorAll('.line-angle-input').forEach(input => {
                input.addEventListener('change', (e) => {
                    const index = parseInt(e.target.dataset.index);
                    const newAngle = parseFloat(e.target.value);

                    if (!isNaN(newAngle) && points[index - 1] && points[index]) {
                        adjustLineAngle(points[index - 1], points[index], newAngle);
                    }
                });
            });

            document.querySelectorAll('.line-hem-select').forEach(select => {
                select.addEventListener('change', (e) => {
                    const index = parseInt(e.target.dataset.index);
                    lineTypes[index] = e.target.value;
                    redrawCanvas();
                });
            });
        };

        const finalizeDraw = () => {
            currentStartPoint = null;
            redrawCanvas();
        };

        const drawLine = (p1, p2, color) => {
            ctx.beginPath();
            ctx.moveTo(p1.x, p1.y);
            ctx.lineTo(p2.x, p2.y);
            ctx.strokeStyle = color;
            ctx.lineWidth = 4;
            ctx.stroke();

            ctx.beginPath();
            ctx.arc(p2.x, p2.y, 4, 0, 2 * Math.PI);
            ctx.fillStyle = 'lightblue';
            ctx.fill();
        };

        const drawTemporaryLine = (p1, p2) => {
            ctx.beginPath();
            ctx.moveTo(p1.x, p1.y);
            ctx.lineTo(p2.x, p2.y);
            ctx.strokeStyle = 'gray';
            ctx.lineWidth = 1;
            ctx.stroke();

            ctx.beginPath();
            ctx.arc(p2.x, p2.y, 4, 0, 2 * Math.PI);
            ctx.fillStyle = 'lightblue';
            ctx.fill();
        };

        const calculateDistance = (p1, p2) => {
            const dist = Math.sqrt((p2.x - p1.x) ** 2 + (p2.y - p1.y) ** 2);
            return dist / pixelsPerInch;
        };

        const calculateInteriorAngle = (p1, p2, p3) => {
            if (!p1 || !p2 || !p3) return null;
            let angle = Math.atan2(p3.y - p2.y, p3.x - p2.x) - Math.atan2(p1.y - p2.y, p1.x - p2.x);
            let degrees = (angle * 180 / Math.PI) % 360;
            if (degrees < 0) degrees += 360;
            if (degrees > 180) degrees = 360 - degrees;
            return degrees;
        };

        function drawAngleArc(p1, p2, p3, adjustment = 0) {
            const radius = 30;

            const adjustedP3 = rotatePoint(p3, p2, adjustment);

            const angle1 = Math.atan2(p1.y - p2.y, p1.x - p2.x);
            const angle2 = Math.atan2(adjustedP3.y - p2.y, adjustedP3.x - p2.x);

            let start = angle1;
            let end = angle2;
            let anticlockwise = false;

            if (end < start) {
                const temp = start;
                start = end;
                end = temp;
                anticlockwise = true;
            }

            ctx.beginPath();
            ctx.arc(p2.x, p2.y, radius, start, end, anticlockwise);
            ctx.strokeStyle = 'red';
            ctx.lineWidth = 2;
            ctx.stroke();
        }

        function clearCanvas(){
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        const rotatePoint = (point, origin, angle) => {
            const rad = angle * Math.PI / 180;
            const dx = point.x - origin.x;
            const dy = point.y - origin.y;
            
            const rotatedX = origin.x + (dx * Math.cos(rad) - dy * Math.sin(rad));
            const rotatedY = origin.y + (dx * Math.sin(rad) + dy * Math.cos(rad));
            
            return { x: rotatedX, y: rotatedY };
        };

        const redrawCanvas = () => {
            clearCanvas();

            for (let i = 1; i < points.length; i++) {
                let p1 = points[i - 1];
                let p2 = points[i];

                if (!p1 || !p2) continue;

                const type = lineTypes[i] || 'normal';
                const midX = (p1.x + p2.x) / 2;
                const midY = (p1.y + p2.y) / 2;
                const distance = calculateDistance(p1, p2);

                if (type === 'flat' || type === 'open') {
                    const img = hemImages[type];
                    const flip = (i === 1);
                    drawHemImage(p1, p2, img, flip);
                } else {
                    drawLine(p1, p2, colors[i - 1]);
                }

                ctx.font = "14px Arial";
                ctx.fillStyle = "white";
                ctx.fillText(`${distance.toFixed(2)} in`, midX + 5, midY - 5);
            }

            if (showAngles) {
                for (let i = 2; i < points.length; i++) {
                    const p0 = points[i - 2];
                    const p1 = points[i - 1];
                    const p2 = points[i];

                    if (!p0 || !p1 || !p2) continue;

                    const type = lineTypes[i] || 'normal';

                    let adjustment = 0;
                    if (type === 'flat') adjustment = 5;
                    else if (type === 'open') adjustment = 20;

                    drawAngleArc(p0, p1, p2, adjustment);

                    const angle = calculateInteriorAngle(p0, p1, p2) + adjustment;

                    const radius = 30;
                    const dx1 = p0.x - p1.x;
                    const dy1 = p0.y - p1.y;
                    const dx2 = p2.x - p1.x;
                    const dy2 = p2.y - p1.y;

                    const avgDx = (dx1 + dx2) / 2;
                    const avgDy = (dy1 + dy2) / 2;
                    const norm = Math.hypot(avgDx, avgDy);
                    const labelX = p1.x + (radius + 5) * (avgDx / norm);
                    const labelY = p1.y + (radius + 5) * (avgDy / norm);

                    ctx.font = "14px Arial";
                    ctx.fillStyle = "red";
                    ctx.fillText(`${angle.toFixed(1)}°`, labelX, labelY);
                }
            }

            for (const arrow of arrows) {
                drawArrow(arrow.p1, arrow.p2, arrow.color);

                const midX = (arrow.p1.x + arrow.p2.x) / 2;
                const midY = (arrow.p1.y + arrow.p2.y) / 2;
                const distance = calculateDistance(arrow.p1, arrow.p2);
            }

            for (let i = 0; i < images.length; i++) {
                const img = images[i].img;
                const x = images[i].x;
                const y = images[i].y;
                const width = images[i].width;
                const height = images[i].height;
                const rotation = images[i].rotation;

                ctx.save();
                ctx.translate(x, y);
                ctx.rotate(rotation * Math.PI / 180);
                ctx.drawImage(img, -width / 2, -height / 2, width, height);
                ctx.restore();
            }

            if (points.length === 0) drawPlaceholderText();

            updateLineEditor();
        };

        const deleteLine = (index) => {
            if (index < 1 || index >= points.length) return;

            points.splice(index, 1);
            lengths.splice(index - 1, 1);
            angles.splice(index - 1, 1);
            colors.splice(index - 1, 1);

            if (index === points.length) {
                currentStartPoint = points[points.length - 1] || null;
            }

            if (index < points.length) {
                const p1 = points[index - 1];
                const p2 = points[index];
                drawLine(p1, p2, colors[index - 1]);
                lengths[index - 1] = calculateDistance(p1, p2);
            }

            redrawCanvas();
            updateLineEditor();
        };

        canvas.addEventListener('mousedown', (e) => {
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            if (isDrawingArrow) {
                arrowStartPoint = { x, y };
                arrowEndPoint = null;
                isDraggingArrow = true;
                return;
            }

            for (let i = 0; i < arrows.length; i++) {
                const { p1, p2 } = arrows[i];
                if (Math.hypot(p1.x - x, p1.y - y) < 6) {
                    isDragging = true;
                    wasDragging = false;
                    dragArrowIndex = i;
                    draggingArrowPoint = 'p1';
                    dragStartSnapshot = {
                        arrows: arrows.map(a => ({ p1: { ...a.p1 }, p2: { ...a.p2 }, color: a.color })),
                    };
                    return;
                } else if (Math.hypot(p2.x - x, p2.y - y) < 6) {
                    isDragging = true;
                    wasDragging = false;
                    dragArrowIndex = i;
                    draggingArrowPoint = 'p2';
                    dragStartSnapshot = {
                        arrows: arrows.map(a => ({ p1: { ...a.p1 }, p2: { ...a.p2 }, color: a.color })),
                    };
                    return;
                }
            }

            for (let i = 0; i < points.length; i++) {
                if (points[i] && Math.hypot(points[i].x - x, points[i].y - y) < 6) {
                    isDragging = true;
                    dragIndex = i;
                    wasDragging = false;

                    dragStartSnapshot = {
                        points: [...points.map(p => p ? { ...p } : null)],
                        lengths: [...lengths],
                        angles: [...angles],
                        colors: [...colors]
                    };
                    return;
                }
            }

            for (let i = 0; i < images.length; i++) {
                if (isPointInRotatedRect(x, y, images[i])) {
                    undoStack.push({
                        points: [...points],
                        lengths: [...lengths],
                        angles: [...angles],
                        colors: [...colors],
                        images: images.map(img => ({ ...img }))
                    });

                    isDragging = true;
                    dragIndex = i;
                    wasDragging = false;

                    dragStartSnapshot = {
                        images: [...images.map(i => ({ ...i }))],
                        points: [...points.map(p => p ? { ...p } : null)],
                        lengths: [...lengths],
                        angles: [...angles],
                        colors: [...colors]
                    };
                    return;
                }
            }
        });

        canvas.addEventListener('mousemove', (e) => {
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            if (currentStartPoint && isTemporaryLineActive) {
                const currentPoint = { x, y };

                redrawCanvas();
                drawTemporaryLine(currentStartPoint, currentPoint);
                const midX = (currentStartPoint.x + currentPoint.x) / 2;
                const midY = (currentStartPoint.y + currentPoint.y) / 2;
                const distance = calculateDistance(currentStartPoint, currentPoint);
                ctx.font = "14px Arial";
                ctx.fillStyle = "gray";
                ctx.fillText(`${distance.toFixed(2)} in`, midX + 5, midY - 5);
            }

            if(!currentStartPoint && isTemporaryLineActive){
                canvas.style.cursor = 'crosshair';
            }else{
                const lastLineIndex = points.length - 1;
                if (lastLineIndex >= 1) {
                    const p1 = points[lastLineIndex - 1];
                    const p2 = points[lastLineIndex];

                    if (!p1 || !p2) return;

                    const distanceToLine = Math.hypot(p2.x - p1.x, p2.y - p1.y);
                    const distanceToPoint = Math.hypot(x - p1.x, y - p1.y) + Math.hypot(x - p2.x, y - p2.y);

                    const isNearLastLine = Math.abs(distanceToPoint - distanceToLine) < 6;
                    const hoveringOnPoint = points.some(p => p && Math.hypot(p.x - x, p.y - y) < 6);
                    const hoveringOnImage = images.some(img => isPointInRotatedRect(x, y, img));
                    const hoveringOnArrowEndpoint = arrows.some(a =>
                        Math.hypot(a.p1.x - x, a.p1.y - y) < 6 ||
                        Math.hypot(a.p2.x - x, a.p2.y - y) < 6
                    );

                    if (isDrawingArrow) {
                        canvas.style.cursor = 'crosshair';
                    } else {
                        const shouldShowMoveCursor = hoveringOnPoint || hoveringOnImage || hoveringOnArrowEndpoint;
                        canvas.style.cursor = shouldShowMoveCursor ? 'move' : 'default';
                    }
                }
            }

            if (isDrawingArrow && isDraggingArrow && arrowStartPoint) {
                canvas.style.cursor = 'crosshair';
                arrowEndPoint = { x, y };
                redrawCanvas();
                drawArrow(arrowStartPoint, arrowEndPoint, '#90EE90');
                return;
            }

            if (isDragging && dragArrowIndex !== -1 && draggingArrowPoint) {
                arrows[dragArrowIndex][draggingArrowPoint] = { x, y };
                wasDragging = true;
                redrawCanvas();
                return;
            }

            if (isDragging && dragIndex !== -1) {
                if (dragStartSnapshot.images) {
                    images[dragIndex].x = x;
                    images[dragIndex].y = y;
                } else {
                    points[dragIndex] = { x, y };
                    if (dragIndex === points.length - 1) {
                        currentStartPoint = points[dragIndex];
                    }
                }

                wasDragging = true;
                redrawCanvas();
            }
        });

        canvas.addEventListener('mouseup', () => {
            if (isDrawingArrow && isDraggingArrow && arrowStartPoint && arrowEndPoint) {
                arrows.push({
                    p1: arrowStartPoint,
                    p2: arrowEndPoint,
                    color: currentColor
                });

                undoStack.push({
                    points: [...points],
                    lengths: [...lengths],
                    angles: [...angles],
                    colors: [...colors],
                    lineTypes: [...lineTypes]
                });

                redoStack = [];
                return;
            }

            if (isDragging && dragArrowIndex !== -1 && draggingArrowPoint) {
                undoStack.push(dragStartSnapshot);
                redoStack = [];
            }

            if (isDragging && dragStartSnapshot) {
                const moved =
                    (dragStartSnapshot.images &&
                        images.some((img, i) =>
                            dragStartSnapshot.images[i] &&
                            (img.x !== dragStartSnapshot.images[i].x || img.y !== dragStartSnapshot.images[i].y)
                        )) ||
                    (dragStartSnapshot.points &&
                        points.some((p, i) =>
                            dragStartSnapshot.points[i] &&
                            (p.x !== dragStartSnapshot.points[i].x || p.y !== dragStartSnapshot.points[i].y)
                        )) ||
                    (dragStartSnapshot.arrows &&
                        arrows.some((a, i) =>
                            dragStartSnapshot.arrows[i] &&
                            (a.p1.x !== dragStartSnapshot.arrows[i].p1.x || a.p1.y !== dragStartSnapshot.arrows[i].p1.y ||
                                a.p2.x !== dragStartSnapshot.arrows[i].p2.x || a.p2.y !== dragStartSnapshot.arrows[i].p2.y)
                        ));

                if (moved) {
                    undoStack.push(dragStartSnapshot);
                    redoStack = [];
                }
            }

            isDragging = false;
            draggingArrowPoint = null;
            dragArrowIndex = -1;
            dragIndex = -1;
            dragStartSnapshot = null;
            setTimeout(() => { wasDragging = false; }, 100);
        });

        canvas.addEventListener('click', (e) => {
            if (wasDragging) return;

            if (isDrawingArrow || isDraggingArrow || arrowStartPoint || arrowEndPoint) {
                isDrawingArrow = false;
                isDraggingArrow = false;
                arrowStartPoint = null;
                arrowEndPoint = null;
                canvas.style.cursor = 'default';
                isTemporaryLineActive = true;
                redrawCanvas();
                return;
            }

            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const clickedPoint = { x, y };

            if (isTemporaryLineActive) {
                let selected = { x, y };

                for (let point of points) {
                    if (!point) continue;
                    if (Math.hypot(point.x - x, point.y - y) < 6) {
                        selected = point;
                        break;
                    }
                }

                if (currentStartPoint && selected === currentStartPoint) return;

                if (currentStartPoint) {
                    points.push(selected);
                    colors.push(currentColor);
                    lineTypes.push('normal');
                    lengths.push(calculateDistance(currentStartPoint, selected));

                    currentStartPoint = selected;

                } else {
                    points.push(selected);
                    colors.push(currentColor);
                    lineTypes.push(null);
                    lengths.push(null);

                    currentStartPoint = selected;
                }

                undoStack.push({
                    points: [...points],
                    lengths: [...lengths],
                    angles: [...angles],
                    colors: [...colors]
                });
                redoStack = [];

                redrawCanvas();
            }
        });

        undoButton.addEventListener('click', () => {
            if (undoStack.length > 0) {
                redoStack.push({
                    points: [...points],
                    lengths: [...lengths],
                    angles: [...angles],
                    colors: [...colors],
                    images: images.map(img => ({ ...img }))
                });

                let last = undoStack.pop();
                points = [...last.points];
                lengths = [...last.lengths];
                angles = [...last.angles];
                colors = [...last.colors];
                images = last.images ? last.images.map(img => ({ ...img })) : [];

                currentStartPoint = points.length > 0 ? points[points.length - 1] : null;
                redrawCanvas();
            }
        });

        redoButton.addEventListener('click', () => {
            if (redoStack.length > 0) {
                undoStack.push({
                    points: [...points],
                    lengths: [...lengths],
                    angles: [...angles],
                    colors: [...colors],
                    images: images.map(img => ({ ...img }))
                });

                let next = redoStack.pop();
                points = [...next.points];
                lengths = [...next.lengths];
                angles = [...next.angles];
                colors = [...next.colors];
                images = next.images ? next.images.map(img => ({ ...img })) : [];

                currentStartPoint = points.length > 0 ? points[points.length - 1] : null;
                redrawCanvas();
            }
        });

        clearButton.addEventListener('click', () => {
            clearCanvas();
            points = [];
            lengths = [];
            angles = [];
            colors = [];
            currentStartPoint = null;
            undoStack = [];
            redoStack = [];
            drawPlaceholderText();
        });

        $(document).on('contextmenu', '#drawingCanvas', function (e) {
            e.preventDefault();
        });

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && isDrawingArrow) {
                isDrawingArrow = false;
                isDraggingArrow = false;
                arrowStartPoint = null;
                arrowEndPoint = null;
                canvas.style.cursor = 'default';
                redrawCanvas();
            }
        });

        $(document).on('click', '.insert-arrow-line', function () {
            isDrawingArrow = true;
            arrowStartPoint = null;
            arrowEndPoint = null;
            canvas.style.cursor = 'crosshair';

            isTemporaryLineActive = false;
            redrawCanvas();
        });

        $(document).on('change', '.line-hem-select', function () {
            const index = $(this).data('index');
            const selectedType = $(this).val();
            let currentType = lineTypes[index];

            if (!['normal', 'flat', 'open'].includes(currentType)) {
                currentType = 'normal';
                lineTypes[index] = currentType;
            }

            if (!['normal', 'flat', 'open'].includes(selectedType)) {
                console.error('Invalid selectedType:', selectedType);
                return;
            }

            lineTypes[index] = selectedType;

            const adjustments = {
                normal: 0,
                flat: 5,
                open: 20
            };

            const adjustmentDifference = adjustments[selectedType] - adjustments[currentType];

            if (isNaN(adjustmentDifference)) {
                console.error('Invalid adjustment difference:', adjustmentDifference);
                return;
            }

            var p1 = points[index - 1];
            var p2 = points[index];

            p2 = rotatePoint(p2, p1, adjustmentDifference);
            points[index] = p2;
            redrawCanvas();
        });

        $(document).on('click', '#btn-pencil', function () {
            isTemporaryLineActive = true;
            currentStartPoint = null;
            points.push(null); 
            lineTypes.push(null);    
            colors.push(null);    
            lengths.push(null);

            $('#btn-pencil').hide();
            $('#btn-stop').show();
        });

        $(document).on('click', '#btn-stop', function () {
            isTemporaryLineActive = false;
            redrawCanvas();
            $('#btn-stop').hide();
            $('#btn-pencil').show();
        });

        $(document).on('click', '#btn-show-angles', function () {
            showAngles = true;
            $('#btn-show-angles').hide();
            $('#btn-hide-angles').css('display', 'inline-block');
            redrawCanvas();
        });

        $(document).on('click', '#btn-hide-angles', function () {
            showAngles = false;
            $('#btn-hide-angles').hide();
            $('#btn-show-angles').css('display', 'inline-block');
            redrawCanvas();
        });

        $(document).on('input', '.line-color-input', function () {
            const index = parseInt($(this).data('index'));
            const color = $(this).val();
            colors[index - 1] = color;
            redrawCanvas();
        });

        $(document).on('change', '.line-length-input', function () {
            const index = parseInt($(this).data('index'));
            const newLengthInch = parseFloat($(this).val());
            if (isNaN(newLengthInch) || index < 1 || index >= points.length) return;

            const p1 = points[index - 1];
            const p2 = points[index];

            const dx = p2.x - p1.x;
            const dy = p2.y - p1.y;
            const currentLength = Math.sqrt(dx * dx + dy * dy);

            if (currentLength === 0) return;

            const scale = (newLengthInch * pixelsPerInch) / currentLength;
            const newX = p1.x + dx * scale;
            const newY = p1.y + dy * scale;

            points[index] = { x: newX, y: newY };
            redrawCanvas();
        });

        $('#triggerUpload').on('click', function () {
            $('#uploadImage').click();
        });

        $('#uploadImage').on('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (event) {
                const imgElement = new Image();
                imgElement.src = event.target.result;

                imgElement.onload = function () {
                    let imageWidth = imgElement.width;
                    let imageHeight = imgElement.height;

                    if (imageWidth > 80) {
                        const scaleFactor = 80 / imageWidth;
                        imageWidth = 80;
                        imageHeight = imageHeight * scaleFactor;
                    }

                    const imgX = currentStartPoint ? currentStartPoint.x + imageWidth / 2 : canvas.width / 2;
                    const imgY = currentStartPoint ? currentStartPoint.y : canvas.height / 2;

                    images.push({
                        img: imgElement,
                        x: imgX,
                        y: imgY,
                        width: imageWidth,
                        height: imageHeight,
                        rotation: 0
                    });

                    redrawCanvas();
                };
            };

            reader.readAsDataURL(file);
        });

        $(document).off('click', '#saveDrawing').on('click', '#saveDrawing', function () {
            if (confirm("Are you sure you want to finalize your custom trim?")) {
                finalizeDraw();
                const image_data = canvas.toDataURL('image/png');

                const drawingData = {
                    points: points,
                    lengths: lengths,
                    angles: angles,
                    colors: colors,
                    lineTypes: lineTypes,
                    arrows: arrows,
                    images: images
                };

                const totalLength = points.reduce((sum, point, i) => {
                    if (i === 0 || points[i] === null || points[i - 1] === null) return sum;

                    const p1 = points[i - 1];
                    const p2 = point;

                    const dx = p2.x - p1.x;
                    const dy = p2.y - p1.y;
                    const segmentLength = Math.sqrt(dx * dx + dy * dy);

                    return sum + segmentLength / pixelsPerInch;
                }, 0);

                const drawingDataJson = JSON.stringify(drawingData);

                $('.drawingContainer').each(function () {
                    $(this).data("drawing", drawingDataJson);
                    $(this).attr("data-drawing", drawingDataJson);
                    console.log(drawingDataJson);
                });

                $('#drawing_data').val(drawingDataJson);

                $.ajax({
                    url: 'pages/cashier_ajax.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        image_data: image_data,
                        save_drawing: 'save_drawing'
                    },
                    success: function(response) {
                        if (response.filename) {
                            $('#custom_trim_draw_modal').modal('hide');
                            currentStartPoint = null;

                            if (typeof totalLength !== 'undefined' && !isNaN(totalLength)) {
                                var lengthInFeet = totalLength / 12;
                                var formattedFeet = Math.floor(lengthInFeet);
                                var formattedInches = ((lengthInFeet - formattedFeet) * 12).toFixed(2);

                                var combinedText = formattedFeet + ' ft ' + formattedInches + ' in (custom)';
                                var lengthFeetValue = lengthInFeet.toFixed(2);

                                let $option = $('#trim_length_select option[value="' + lengthFeetValue + '"]');

                                if ($option.length) {
                                    $option.prop('selected', true);
                                } else {
                                    $('#trim_length_select').append(
                                        $('<option>', {
                                            value: lengthFeetValue,
                                            text: combinedText,
                                            selected: true,
                                            hidden: true
                                        })
                                    );
                                }
                            } else {
                                console.log('totalLength is undefined or not a number:', totalLength);
                            }

                            $('#trim_length').val(totalLength / 12);
                            $('#is_custom_trim').val("1");
                            updatePrice();

                            $('#drawingImage').attr('src', '../images/drawing/' + response.filename).show();
                            $('#drawingTrimImage').attr('src', '../images/drawing/' + response.filename).show();
                            $('#placeholderText').hide();
                            $('#img_src').val(response.filename);
                            $('#img_trim_src').val(response.filename);
                        } else {
                            console.log("Error: Response:" + response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("Error: XHR:" + xhr.responseText);
                    }
                });
            }
        });

        drawPlaceholderText();
        document.getElementById('btn-stop').style.display = 'inline-block';
        document.getElementById('btn-show-angles').style.display = 'inline-block';
    }

    function formatOption(state) {
        if (!state.id) {
            return state.text;
        }
        var color = $(state.element).data('color');
        var $state = $(
            '<span class="d-flex align-items-center small">' +
                '<span class="rounded-circle d-block p-1 me-2" style="background-color:' + color + '; width: 16px; height: 16px;"></span>' +
                state.text + 
            '</span>'
        );
        return $state;
    }

    function formatSelected(state) {
        if (!state.id) {
            return state.text;
        }
        var color = $(state.element).data('color');
        var $state = $( 
            '<span class="d-flex align-items-center justify-content-center">' + 
                '<span class="rounded-circle d-block p-1" style="background-color:' + color + '; width: 25px; height: 25px;"></span>' +
                '&nbsp;' +
            '</span>'
        );
        return $state;
    }

    function updatePrice() {
        const basePrice = parseFloat($('#product_price').val()) || 0;
        const feet = parseFloat($('#trim_length').val()) || 0;
        const quantity = parseFloat($('#trim_qty').val()) || 1;
        const is_custom = parseInt($('#is_custom_trim').val()) || 0;
        const custom_multiplier_trim = parseFloat($('#custom_multiplier_trim').val()) || 1;

        let totalPrice = basePrice * feet * quantity;

        if (is_custom === 1) {
            totalPrice += totalPrice * custom_multiplier_trim;
        }

        $('#trim_price').text(totalPrice.toFixed(2));
    }

    $(document).on('change', '#trim_length, #trim_qty', function() {
        updatePrice();
    });

    $(document).on('change', '#trim_length_select', function() {
        var value = $(this).val();
        $('#trim_length').val(value);
        updatePrice();
    });

    updatePrice();
    
    $(document).ready(function() {
        var panel_id = '<?= $panel_id ?>';

        const urlParams = new URLSearchParams(window.location.search);
        const editestimate = urlParams.get('editestimate');
        if (editestimate) {
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    editestimate: editestimate,
                    set_estimate_data: 'set_estimate_data'
                },
                success: function (response) {
                    console.log(response);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + jqXHR.responseText);
                }
            });
        }

        $(document).on('contextmenu', '#drawingCanvas', function (e) {
            e.preventDefault();
            e.stopPropagation();
        });

        if (typeof $.ui === 'undefined') {
            $.getScript("https://code.jquery.com/ui/1.12.1/jquery-ui.min.js", function() {
                console.log("jQuery UI has been successfully loaded.");
            }).fail(function() {
                console.error("Failed to load jQuery UI.");
            });
        }

        $(document).on('click', '#openMap', function () {
            $('#map1Modal').modal('show');
        });

        $(document).on('click', '#btnColorModal', function () {
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    fetch_change_color_modal: 'fetch_change_color_modal'
                },
                success: function(response) {
                    $('#change_color_container').html(response);
                    $('#chng_color_modal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#btnApprovalModal', function () {
            $('#approval_modal').modal('show');
        });

        $(document).on('click', '#btnPriceGroupModal', function () {
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    fetch_change_price_modal: 'fetch_change_price_modal'
                },
                success: function(response) {
                    $('#change_price_container').html(response);
                    $('#chng_price_modal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#btnGradeModal', function () {
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    fetch_change_grade_modal: 'fetch_change_grade_modal'
                },
                success: function(response) {
                    $('#change_grade_container').html(response);
                    $('#chng_grade_modal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#btnCustomChart', function () {
            var category = $(this).data("category");

            $.ajax({
                url: 'pages/cashier_custom_chart.php',
                type: 'POST',
                data: {
                    category: category,
                    fetch_modal: 'fetch_modal'
                },
                success: function (response) {
                    $('#custom_chart_container').html(response);

                    $('#custom_chart_modal').modal({
                        backdrop: true,
                        keyboard: true
                    });

                    $('#custom_chart_modal').modal("show");
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '.chart-btn', function () {
            const newSrc = $(this).data('img');
            $('#chartImage').attr('src', newSrc);
        });

        $(document).on('click', '#save_color_change', function () {
            var orig_color = $('#orig-colors').val();
            var in_stock_color = $('#in-stock-colors').val();
            var category_id = $('#category_id_color').val();
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    orig_color: orig_color,
                    in_stock_color: in_stock_color,
                    category_id: category_id,
                    change_color: 'change_color'
                },
                success: function(response) {
                    $('.modal').modal("hide");
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Product Color Changed successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response_modal').modal("show");
                        $('#response_modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    }else{
                        $('#responseHeader').text("Failed");
                        $('#responseMsg').text(response);
                        $('#responseHeaderContainer').removeClass("bg-success");
                        $('#responseHeaderContainer').addClass("bg-danger");
                        $('#response_modal').modal("show");
                        $('#response_modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('change', '.discount_input', function () {
            var discount = $(this).val();
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    discount: discount,
                    change_discount: 'change_discount'
                },
                success: function(response) {
                    loadCart();
                    loadEstimateContents();
                    loadOrderContents();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#save_price_change', function () {
            var price_group_select = $('#price_group_select').val();
            var product_select = $('#product_select').val();
            var price = $('#price_input').val();
            var disc = $('#disc_input').val();
            var notes = $('#notes_input').val();

            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    price: price,
                    disc: disc,
                    notes: notes,
                    price_group_select: price_group_select,
                    product_select: product_select,
                    change_price: 'change_price'
                },
                success: function(response) {
                    $('.modal').modal("hide");
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Product discount changed successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response_modal').modal("show");
                        $('#response_modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    }else{
                        $('#responseHeader').text("Failed");
                        $('#responseMsg').text(response);
                        $('#responseHeaderContainer').removeClass("bg-success");
                        $('#responseHeaderContainer').addClass("bg-danger");
                        $('#response_modal').modal("show");
                        $('#response_modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#save_grade_change', function () {
            var orig_grade = $('#orig-grade').val();
            var in_stock_grade = $('#in-stock-grade').val();
            var category_id = $('#category_id').val();
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    orig_grade: orig_grade,
                    in_stock_grade: in_stock_grade,
                    category_id: category_id,
                    change_grade: 'change_grade'
                },
                success: function(response) {
                    $('.modal').modal("hide");
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Product Grades Changed successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response_modal').modal("show");
                        $('#response_modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    }else{
                        $('#responseHeader').text("Failed");
                        $('#responseMsg').text(response);
                        $('#responseHeaderContainer').removeClass("bg-success");
                        $('#responseHeaderContainer').addClass("bg-danger");
                        $('#response_modal').modal("show");
                        $('#response_modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        var currentPage = 1,
            rowsPerPage = parseInt($('#rowsPerPage').val()),
            totalRows = 0,
            totalPages = 0,
            maxPageButtons = 5,
            stepSize = 5;

        function updateTable() {
            var $rows = $('#productTableBody tr');
            totalRows = $rows.length;
            totalPages = Math.ceil(totalRows / rowsPerPage);

            var start = (currentPage - 1) * rowsPerPage,
                end = Math.min(currentPage * rowsPerPage, totalRows);

            $rows.hide().slice(start, end).show();

            $('#paginationControls').html(generatePagination());
            $('#paginationInfo').text(`${start + 1}–${end} of ${totalRows}`);

            $('#paginationControls').find('a').click(function(e) {
                e.preventDefault();
                if ($(this).hasClass('page-link-next')) {
                    currentPage = Math.min(currentPage + stepSize, totalPages);
                } else if ($(this).hasClass('page-link-prev')) {
                    currentPage = Math.max(currentPage - stepSize, 1);
                } else {
                    currentPage = parseInt($(this).text());
                }
                updateTable();
            });
        }

        function generatePagination() {
            var pagination = '';
            var startPage = Math.max(1, currentPage - Math.floor(maxPageButtons / 2));
            var endPage = Math.min(totalPages, startPage + maxPageButtons - 1);

            if (currentPage > 1) {
                pagination += `<li class="page-item p-1"><a class="page-link border-0 rounded-circle text-dark fs-6 round-32 d-flex align-items-center justify-content-center page-link-prev" href="#">‹</a></li>`;
            }

            for (var i = startPage; i <= endPage; i++) {
                pagination += `<li class="page-item p-1 ${i === currentPage ? 'active' : ''}"><a class="page-link border-0 rounded-circle text-dark fs-6 round-32 d-flex align-items-center justify-content-center" href="#">${i}</a></li>`;
            }

            if (currentPage < totalPages) {
                pagination += `<li class="page-item p-1"><a class="page-link border-0 rounded-circle text-dark fs-6 round-32 d-flex align-items-center justify-content-center page-link-next" href="#">›</a></li>`;
            }

            return pagination;
        }

        function performSearch(query) {
            var color_id = $('#select-color').find('option:selected').val();
            var grade = $('#select-grade').find('option:selected').val();
            var gauge_id = $('#select-gauge').find('option:selected').val();
            var category_id = $('#select-category').find('option:selected').val();
            var profile_id = $('#select-profile').find('option:selected').val();
            var type_id = $('#select-type').find('option:selected').val();
            var onlyInStock = $('#toggleActive').prop('checked');
            var onlyPromotions = $('#onlyPromotions').prop('checked');
            var onlyOnSale = $('#onlyOnSale').prop('checked');
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    query: query,
                    color_id: color_id,
                    grade: grade,
                    gauge_id: gauge_id,
                    category_id: category_id,
                    profile_id: profile_id,
                    type_id: type_id,
                    onlyInStock: onlyInStock,
                    onlyPromotions: onlyPromotions,
                    onlyOnSale: onlyOnSale
                },
                success: function(response) {
                    $('#productTableBody').html(response);
                    currentPage = 1;
                    updateTable();
                    updateSelectedTags();

                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        }

        $(document).on("click", "#add-to-cart-custom-truss-btn", function() {
            var id = $(this).data('id');
            $.ajax({
                url: 'pages/cashier_custom_truss_modal.php',
                type: 'POST',
                data: {
                    id: id,
                    fetch_modal: 'fetch_modal'
                },
                success: function(response) {
                    $('#custom_truss_container').html(response);
                    $('#custom_truss_modal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on("click", "#add-to-cart-special-trim-btn", function() {
            var id = $(this).data('id');
            var line = '0';

            $.ajax({
                url: 'pages/cashier_special_trim_modal.php',
                type: 'POST',
                data: {
                    id: id,
                    line: line,
                    fetch_modal: 'fetch_modal'
                },
                success: function(response) {
                    $('#special_trim_body').html(response);
                    $('#special_trim_modal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#custom_trim_draw', function(event) {
            var id = $(this).data('id');
            var line = $(this).data('line');

            $.ajax({
                url: 'pages/cashier_special_trim_modal.php',
                type: 'POST',
                data: {
                    id: id,
                    line: line,
                    fetch_modal: 'fetch_modal'
                },
                success: function(response) {
                    $('#special_trim_body').html(response);
                    $('#special_trim_modal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('submit', '#specialTrimForm', function (event) {
            event.preventDefault();

            var formData = new FormData(this);
            formData.append('save_trim', 'save_trim');

            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    console.log(response);
                    loadCart();
                    loadEstimateContents();
                    loadOrderContents();
                    $('#special_trim_modal').modal("hide");
                },
                error: function (xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        });

        $(document).on("click", "#drawingContainer, #trim_draw", function() {
            var id = $(this).data('id');
            var line = $(this).data('line');
            var drawing_data = $(this).data('drawing');

            $.ajax({
                url: 'pages/cashier_drawing_modal.php',
                type: 'POST',
                data: {
                    id: id,
                    line: line,
                    drawing_data: drawing_data,
                    fetch_drawing: 'fetch_drawing'
                },
                success: function(response) {
                    $('#drawing-body').html(response);
                    $('#custom_trim_draw_modal').modal('show');
                    initializeDrawingApp();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on("click", "#add-to-cart-panel-btn", function() {
            var id = $(this).data('id');

            var color_id = $('#select-color').find('option:selected').val();
            var grade_id = $('#select-grade').find('option:selected').val();
            var gauge_id = $('#select-gauge').find('option:selected').val();
            $.ajax({
                url: 'pages/cashier_quantity_modal.php',
                type: 'POST', 
                data: {
                    id: id,
                    fetch_prompt_quantity: 'fetch_prompt_quantity'
                },
                success: function(response) {
                    $('#qty_prompt_container').html(response);

                    $('.qty_select2').each(function () {
                        $(this).select2({
                            width: '300px',
                            dropdownParent: $(this).parent(),
                            dropdownPosition: 'below'
                        });
                    });

                    $('#qty-color').val(color_id).trigger('change');
                    $('#qty-grade').val(grade_id).trigger('change');
                    $('#qty-gauge').val(gauge_id).trigger('change');

                    $('#prompt_quantity_modal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on("click", "#add-to-cart-trim-btn", function() {
            var id = $(this).data('id');

            var color_id = $('#select-color').find('option:selected').val();
            var grade_id = $('#select-grade').find('option:selected').val();
            var gauge_id = $('#select-gauge').find('option:selected').val();
            $.ajax({
                url: 'pages/cashier_trim_modal.php',
                type: 'POST', 
                data: {
                    id: id,
                    fetch_modal: 'fetch_modal'
                },
                success: function(response) {
                    $('#trim_container').html(response);

                    $('.trim_select2').each(function () {
                        $(this).select2({
                            width: '300px',
                            dropdownParent: $(this).parent(),
                            dropdownPosition: 'below'
                        });
                    });

                    $('#trim-color').val(color_id).trigger('change');
                    $('#trim-grade').val(grade_id).trigger('change');
                    $('#trim-gauge').val(gauge_id).trigger('change');

                    $('#trim_modal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on("click", "#add-to-cart-custom-length-btn", function() {
            var id = $(this).data('id');

            $.ajax({
                url: 'pages/cashier_custom_length_modal.php',
                type: 'POST', 
                data: {
                    id: id,
                    fetch_modal: 'fetch_modal'
                },
                success: function(response) {
                    $('#custom_length_container').html(response);

                    $('.custom_length_select2').each(function () {
                        $(this).select2({
                            width: '300px',
                            dropdownParent: $(this).parent(),
                            dropdownPosition: 'below'
                        });
                    });

                    $('#custom_length_modal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#add-to-cart-btn', function() {
            var product_id = $(this).data('id');
            var qty = parseInt($('#qty' + product_id).val(), 10) || 0;

            $.ajax({
                url: "pages/cashier_ajax.php",
                type: "POST",
                data: {
                    product_id: product_id,
                    line: 1,
                    qty: qty,
                    modifyquantity: 'modifyquantity',
                    addquantity: 'addquantity'
                },
                success: function(data) {
                    $('#qty' + product_id).val(1);
                    loadCartItemsHeader();
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", {
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                }
            });
        });

        $(document).on('click', '.btn-plus', function () {
            var product_id = $(this).data('id');
            var input = $('#qty' + product_id);
            var currentValue = parseInt(input.val(), 10) || 0;
            input.val(currentValue + 1).trigger('change');
        });

        $(document).on('click', '.btn-minus', function () {
            var product_id = $(this).data('id');
            var input = $('#qty' + product_id);
            var currentValue = parseInt(input.val(), 10) || 0;
            var minValue = parseInt(input.attr('min')) || 1;
            if (currentValue > minValue) {
                input.val(currentValue - 1).trigger('change');
            }
        });


        $(document).on("click", "#new-job-name-btn", function() {
            var id = $(this).data('id');
            $.ajax({
                url: 'pages/cashier_job_name_modal.php',
                type: 'POST',
                data: {
                    id: id,
                    fetch_prompt_quantity: 'fetch_prompt_quantity'
                },
                success: function(response) {
                    $('#qty_prompt_container').html(response);
                    $('#prompt_quantity_modal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });
              
        $(document).on('click', '#save_estimate', function (event) {
            event.preventDefault();

            const editEstimateId = new URLSearchParams(window.location.search).get('editestimate');

            if (editEstimateId) {
                
                $.ajax({
                    url: 'pages/cashier_ajax.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        edit_estimate: editEstimateId
                    },
                    success: function (response) {
                        if (response.success) {
                            alert('Estimate updated successfully.');

                            const url = new URL(window.location.href);
                            url.searchParams.delete('editestimate');
                            window.history.replaceState({}, document.title, url.toString());

                            location.reload();
                        } else if (response.error) {
                            alert('Process Failed.');
                            console.log(response);
                        } else {
                            alert('Unexpected response from server.');
                            console.log(response);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + jqXHR.responseText);
                    }
                });

            } else {
                var discount = $('#est_discount').val();
                var delivery_amt = $('#est_delivery_amt').val();
                var cash_amt = $('#est_cash').val();
                var credit_amt = $('#est_credit').val();
                var job_name = $('#est_job_name').val();
                var job_po = $('#est_job_po').val();
                var deliver_address = $('#est_deliver_address').val();
                var deliver_city = $('#est_deliver_city').val();
                var deliver_state = $('#est_deliver_state').val();
                var deliver_zip = $('#est_deliver_zip').val();
                var deliver_fname = $('#est_deliver_fname').val();
                var deliver_lname = $('#est_deliver_lname').val();

                $.ajax({
                    url: 'pages/cashier_ajax.php',
                    type: 'POST',
                    data: {
                        cash_amt: cash_amt,
                        credit_amt: credit_amt,
                        discount: discount,
                        delivery_amt: delivery_amt,
                        job_name: job_name,
                        job_po: job_po,
                        deliver_address: deliver_address,
                        deliver_city: deliver_city,
                        deliver_state: deliver_state,
                        deliver_zip: deliver_zip,
                        deliver_fname: deliver_fname,
                        deliver_lname: deliver_lname,
                        save_estimate: 'save_estimate'
                    },
                    success: function (response) {
                        if (response.success) {
                            alert("Estimate successfully saved.");
                            $('#print_estimate_category').attr('href', '/print_estimate_product.php?id=' + response.estimate_id).removeClass('d-none');
                            $('#print_estimate').attr('href', '/print_estimate_total.php?id=' + response.estimate_id).removeClass('d-none');
                        } else if (response.error) {
                            alert("Error: " + response.error);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
                });
            }
        });


        $(document).on('click', '#load_estimate', function(event) {
            var id = $(this).data('id');
            console.log(id);
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    id: id,
                    load_estimate: 'load_estimate'
                },
                success: function(response) {
                    if (response.success) {
                        loadOrderContents();
                        $('#cashmodal').modal('show');
                    } else if (response.error) {
                        alert("Error: " + response.error);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#save_order', function(event) {
            var discount = $('#order_discount').val();
            var delivery_amt = $('#delivery_amt').val();
            var cash_amt = $('#payable_amt').val();
            var credit_amt = 0;
            var job_name = $('#order_job_name').val();
            var job_po = $('#order_job_po').val();
            var deliver_address = $('#order_deliver_address').val();
            var deliver_city = $('#order_deliver_city').val();
            var deliver_state = $('#order_deliver_state').val();
            var deliver_zip = $('#order_deliver_zip').val();
            var deliver_fname = $('#order_deliver_fname').val();
            var deliver_lname = $('#order_deliver_lname').val();
            var applyStoreCredit = $('#applyStoreCredit').is(':checked') ? $('#applyStoreCredit').val() : 0;
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    cash_amt: cash_amt,
                    credit_amt: credit_amt,
                    discount: discount,
                    delivery_amt: delivery_amt,
                    job_name: job_name,
                    job_po: job_po,
                    deliver_address: deliver_address,
                    deliver_city: deliver_city,
                    deliver_state: deliver_state,
                    deliver_zip: deliver_zip,
                    deliver_fname: deliver_fname,
                    deliver_lname: deliver_lname,
                    applyStoreCredit: applyStoreCredit,
                    save_order: 'save_order'
                },
                success: function(response) {
                    console.log(response);
                    if (response.success) {
                        alert("Order successfully saved.");
                        $('#print_order_category').attr('href', '/print_order_product.php?id=' + response.order_id);
                        $('#print_order').attr('href', '/print_order_total.php?id=' + response.order_id);
                        $('#print_deliver').attr('href', '/print_order_delivery.php?id=' + response.order_id);
                        $('#print_order_category').removeClass('d-none');
                        $('#print_order').removeClass('d-none');
                        $('#print_deliver').removeClass('d-none');
                        print_deliver
                    } else if (response.error) {
                        alert("Error: " + response.error);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('Response Text: ' + jqXHR.responseText);
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#submitApprovalBtn', function(event) {
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    save_approval: 'save_approval'
                },
                success: function(response) {
                    console.log(response);
                    if (response.success) {
                        alert("Application for approval submitted!");
                        location.reload();
                    } else if (response.error) {
                        alert("Error: " + response.error);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('Response Text: ' + jqXHR.responseText);
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#clear_cart', function(event) {
            event.preventDefault();
            var isConfirmed = confirm("Are you sure you want to clear your cart contents?");
            if (isConfirmed) {
                $.ajax({
                    url: 'pages/cashier_ajax.php',
                    type: 'POST',
                    data: {
                        clear_cart: "clear_cart"
                    },
                    success: function(response) {
                        loadCart();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
                });
            }
        });

        $(document).on('click', '#view_product_details', function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                url: 'pages/cashier_prod_details_modal.php',
                type: 'POST',
                data: {
                    id: id,
                    fetch_details_modal: "fetch_details_modal"
                },
                success: function(response) {
                    $('#viewDetailsModal').html(response);
                    $('#viewDetailsModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#view_cart', function(event) {
            loadCart();
            $('#view_cart_modal').modal('show');
        });

        $(document).on('click', '#order_product', function(event) {
            loadOrderProductCart();
            $('#order_product_modal').modal('show');
        });

        $(document).on('click', '#view_order_product_list', function(event) {
            loadOrderSupplierList();
            $('#view_order_product_list_modal').modal('show');
        });

        $(document).on('click', '#view_order_product_details', function(event) {
            let orderId = $(this).data('id');
            loadOrderSupplierDetails(orderId);
            $('#view_order_product_details_modal').modal('show');
        });

        $(document).on('click', '#save_order_supplier', function(event) {
            if (!confirm("Save this Order for future use?")) {
                return;
            }
            $.ajax({
                url: 'pages/cashier_order_product_modal.php',
                type: 'POST',
                data: {
                    save_order_supplier: 'save_order_supplier'
                },
                success: function(response) {
                    if (response.success) {
                        alert("Order successfully saved.");
                    } else if (response.error) {
                        alert("Error: " + response.error);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('Response Text: ' + jqXHR.responseText);
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#edit_saved_order', function(event) {
            if (!confirm("Load and edit this saved order?")) {
                return;
            }

            var orderid = $(this).data('id');
            $.ajax({
                url: 'pages/cashier_order_product_modal.php',
                type: 'POST',
                data: {
                    orderid: orderid,
                    load_saved_order: 'load_saved_order'
                },
                success: function(response) {
                    if (response.success) {
                        alert("Order successfully loaded.");

                        loadOrderProductCart();
                        $('#view_order_product_details_modal').modal('hide');
                        $('#view_order_product_list_modal').modal('hide');

                    } else if (response.error) {
                        alert("Error: " + response.error);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('Response Text: ' + jqXHR.responseText);
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#view_est_list', function(event) {
            loadEstimatesList();
            $('#view_est_list_modal').modal('show');
        });

        $(document).on('click', '#view_est_details', function(event) {
            var estimate_id = $(this).data('id');
            loadEstimatesDetails(estimate_id);
            $('#view_est_details_modal').modal('show');
        });

        $(document).on('click', '#view_order_list', function(event) {
            $('#view_order_list_modal').modal('show');
        });

        $(document).on('click', '#return_search_button', function(event) {
            loadOrderList();
        });

        $(document).on('click', '#view_order_details', function(event) {
            var orderid = $(this).data('id');
            loadOrderDetails(orderid);
            $('#view_order_details_modal').modal('toggle');
        });

        $(document).on('click', '#return_product', function(event) {
            var id = $(this).data('id');
            var quantity = $('#return_quantity' + id).val();
            var price = $('#return_price' + id).text();
            var store_credited = $(this).data('store-credited');

            var price = parseFloat(price.replace(/[^0-9.-]+/g, ''));

            $('#return_id').val(id);
            $('#return_quantity').val(quantity);
            $('#price_to_return').val(price);
            $('#is_store_credited').val(store_credited);

            updateReturnPrice();

            $('#return_stocking_fee_modal').modal('toggle');
        });

        function updateReturnPrice() {
            let percentage = parseFloat($('#return_stock_fee').val());
            let price = parseFloat($('#price_to_return').val());
            let isStoreCredited = $('#is_store_credited').val() == "1";

            if (!isNaN(percentage) && !isNaN(price)) {
                let fee = (price * percentage) / 100;
                let returnPrice = price - fee;

                let label = isStoreCredited ? "Store Credit" : "Return Price";

                $('#return_stock_fee_display').text(`Stocking Fee: $${fee.toFixed(2)}`);
                $('#return_price_display').text(`${label}: $${returnPrice.toFixed(2)}`);
            } else {
                $('#return_stock_fee_display').text('');
                $('#return_price_display').text('');
            }
        }

        $('#return_stock_fee').on('input', function () {
            if (parseFloat($(this).val()) > 25) {
                $(this).val(25);
                $('#fee-warning').fadeIn();

                setTimeout(function () {
                    $('#fee-warning').fadeOut();
                }, 2000);
            } else {
                $('#fee-warning').fadeOut();
            }
        });

        $(document).on('input', '#return_stock_fee', updateReturnPrice);

        $(document).on('click', '#return_finalize_btn', function(event) {
            event.preventDefault();

            var id = $('#return_id').val();
            var quantity = $('#return_quantity').val();
            var stock_fee = $('#return_stock_fee').val();

            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    id: id,
                    quantity: quantity,
                    stock_fee: stock_fee,
                    return_product: "return_product"
                },
                success: function(response) {
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Product Returned successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response_modal').modal("show");
                        $('#response_modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    }else{
                        console.log(response);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#view_estimate', function(event) {
            $('.modal').modal('hide');
            loadEstimateContents();
            $('#next_page_est').removeClass("d-none");
            $('#prev_page_est').addClass("d-none");
            $('#save_estimate').addClass("d-none");
            $('#print_estimate_category').addClass('d-none');
            $('#print_estimate').addClass('d-none');
            $('#view_estimate_modal').modal('show');
        });

        $(document).on('click', '#view_order', function(event) {
            $('.modal').modal('hide');
            loadOrderContents();
            $('#prev_page_order').addClass("d-none");
            $('#save_order').addClass("d-none");
            $('#print_order_category').addClass('d-none');
            $('#print_order').addClass('d-none');
            $('#print_deliver').addClass('d-none');
            $('#cashmodal').modal('show');
        });

        $(document).on('click', '#prev_page_order', function(event) {
            $('.modal').modal('hide');
            loadCart();
            $('#view_cart_modal').modal('show');
        });

        $(document).on('click', '#view_in_stock', function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/cashier_in_stock_modal.php',
                    type: 'POST',
                    data: {
                        id: id,
                        fetch_in_stock_modal: "fetch_in_stock_modal"
                    },
                    success: function(response) {
                        $('#viewInStockmodal').html(response);
                        $('#viewInStockmodal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $(document).on('click', '#view_out_of_stock', function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                url: 'pages/cashier_out_of_stock_modal.php',
                type: 'POST',
                data: {
                    id: id,
                    fetch_out_of_stock_modal: "fetch_out_of_stock_modal"
                },
                success: function(response) {
                    $('#viewOutOfStockmodal').html(response);
                    $('#viewOutOfStockmodal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#view_available', function(event) {
            event.preventDefault();
            var color = $(this).data('color');
            var width = $(this).data('width');
            console.log("Color: " +color +" Width: " +width);
            $.ajax({
                    url: 'pages/cashier_available_modal.php',
                    type: 'POST',
                    data: {
                        color: color,
                        width: width,
                        fetch_available: "fetch_available"
                    },
                    success: function(response) {
                        $('#viewAvailablemodal').html(response);
                        $('#viewAvailablemodal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $(document).on('click', '#view_available_color', function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                url: 'pages/cashier_available_color_modal.php',
                type: 'POST',
                data: {
                    id: id,
                    fetch_available: "fetch_available"
                },
                success: function(response) {
                    $('#viewAvailableColormodal').html(response);
                    $('#viewAvailableColormodal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('submit', '#quantity_form', function (event) {
            event.preventDefault();

            const formData = new FormData(this);
            const category_id = formData.get('category_id');
            const panel_type = formData.get('panel_type');
            const panel_drip_stop = formData.get('panel_drip_stop');

            const performAjax = (formData) => {
                formData.append('add_to_cart', 'add_to_cart');
                $.ajax({
                    url: 'pages/cashier_ajax.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $('.modal').modal("hide");
                        loadCart();
                    },
                    error: function (xhr) {
                        console.error('Error:', xhr.responseText);
                    }
                });
            };

            if (category_id === panel_id && panel_type === '2') {
                $('#confirm_modal').modal('show');
                $('#confirm_yes_btn').off('click').on('click', function () {
                    $('#confirm_modal').modal('hide');
                    performAjax(formData);
                });
            } else {
                performAjax(formData);
            }
        });

        $(document).on('submit', '#trim_form', function (event) {
            event.preventDefault();

            const formData = new FormData(this);
            formData.append('save_trim', 'save_trim');
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('.modal').modal("hide");
                    loadCart();
                },
                error: function (xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        });

        $(document).on('submit', '#custom_length_form', function (event) {
            event.preventDefault();

            const formData = new FormData(this);
            formData.append('save_custom_length', 'save_custom_length');
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    console.log(response);
                    $('.modal').modal("hide");
                    loadCart();
                },
                error: function (xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        });

        $(document).on('submit', '#custom_truss_form', function (event) {
            event.preventDefault();
            const formData = new FormData(this);

            formData.append('add_custom_truss_to_cart', 'add_custom_truss_to_cart');
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('.modal').modal("hide");
                    loadCart();
                },
                error: function (xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        });

        $(document).on('submit', '#job_name_form', function (event) {
            event.preventDefault();

            const formData = new FormData(this);
            formData.append('add_job_name', 'add_job_name');
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('.modal').modal("hide");
                    const isSuccess = response.trim() === "success";
                    $('#responseHeader').text(isSuccess ? "Success" : "Failed");
                    $('#responseMsg').text(isSuccess ? "Successfully added Job Name." : response);
                    $('#responseHeaderContainer')
                        .toggleClass("bg-success", isSuccess)
                        .toggleClass("bg-danger", !isSuccess);
                    $('#response_modal').modal("show");
                    if (isSuccess) loadCart();
                },
                error: function (xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        });


        $('#rowsPerPage').change(function() {
            rowsPerPage = parseInt($(this).val());
            currentPage = 1;
            updateTable();
        });

        function updateSelectedTags() {
            var displayDiv = $('#selected-tags');
            displayDiv.empty();

            $('.filter-selection, #onlyOnSale, #onlyPromotions').each(function() {
                var $filter = $(this);
                var filterName = $filter.data('filter-name');
                var filterId = $filter.attr('id');
                var value = $filter.val();

                if ($filter.attr('type') === 'checkbox') {
                    if ($filter.is(':checked')) {
                        displayDiv.append(`
                            <div class="d-inline-block p-1 m-1 border rounded bg-light">
                                <span class="text-dark">${filterName}</span>
                                <button type="button"
                                    class="btn-close btn-sm ms-1 remove-tag"
                                    style="width: 0.75rem; height: 0.75rem;"
                                    aria-label="Close"
                                    data-select="#${filterId}">
                                </button>
                            </div>
                        `);
                    }
                } else {
                    if (value) {
                        var selectedOption = $filter.find('option:selected');
                        var selectedText = selectedOption.text().trim();

                        displayDiv.append(`
                            <div class="d-inline-block p-1 m-1 border rounded bg-light">
                                <span class="text-dark">${filterName}: ${selectedText}</span>
                                <button type="button"
                                    class="btn-close btn-sm ms-1 remove-tag"
                                    style="width: 0.75rem; height: 0.75rem;"
                                    aria-label="Close"
                                    data-select="#${filterId}">
                                </button>
                            </div>
                        `);
                    }
                }
            });

            $('.remove-tag').on('click', function() {
                var $target = $($(this).data('select'));
                if ($target.attr('type') === 'checkbox') {
                    $target.prop('checked', false).trigger('change');
                } else {
                    $target.val('').trigger('change');
                }
                $(this).parent().remove();
            });
        }

        function updateSearchCategory(){
            var product_category = $('#select-category').val() || '';

            console.log(product_category);
            $.ajax({
                url: "pages/cashier_ajax.php",
                type: "POST",
                data: {
                    product_category: product_category,
                    filter_category: 'filter_category'
                },
                success: function(result) {
                    $('.sub_search_cat').html('');

                    $('.select2_filter').select2('destroy');

                    $('.sub_search_cat').html(result);

                    $('.select2_filter').each(function () {
                        $(this).select2({
                            width: '100%',
                            dropdownParent: $(this).parent(),
                            dropdownPosition: 'below'
                        });
                    });
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", {
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                }
            }); 
        }

        $('#select-category').on('change', updateSearchCategory);

        $(document).on('click', '.reset_filters', function () {
            $('.filter-selection').val(null).trigger('change');
            $('#text-srh').val('').trigger('input');
            $('#onlyOnSale, #onlyPromotions').prop('checked', false).trigger('change');
        });

        $('.filter-selection').each(function () {
            $(this).select2({
                width: '100%',
                dropdownParent: $(this).parent(),
                dropdownPosition: 'below'
            });
        });

        $(document).on('input change', '#text-srh, #select-color, #select-grade, #select-gauge, #select-category, #select-profile, #select-type, #toggleActive, #onlyOnSale, #onlyPromotions', function() {
            performSearch($('#text-srh').val());
        });

        performSearch('');

        $(document).on('change', '#est_delivery_method', function () {
            calculateDeliveryAmountEst();
        });

        $(document).on('change', 'input[name="order_delivery_method"]', function () {
            calculateDeliveryAmount();
        });

        $(document).on('change', '#applyStoreCredit', function () {
            calculateDeliveryAmount();
        });
        
    });
</script>
