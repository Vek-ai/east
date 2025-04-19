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
    #custom_trim_draw_modal {
        z-index: 1060;
    }

    #custom_trim_draw_modal ~ .modal-backdrop.show {
        z-index: 1055;
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
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Draw Custom Trim</h6>
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
                <button id="clearButton" class="btn ripple btn-warning" type="button">Reset</button>
                <button class="btn ripple btn-danger" data-bs-dismiss="modal" type="button">Close</button>
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
            <div class="row mt-4 p-3">
                <div class="col-12 d-flex flex-wrap justify-content-between">
                    <div class="d-flex flex-grow-1 me-2 justify-content-start">
                        <button type="button" class="btn mb-2" id="clear_cart" style="background-color: #dc3545; color: white;">
                            <i class="fa fa-trash fs-4 me-2"></i>
                            Clear Cart
                        </button>
                    </div>
                    <div class="d-flex flex-grow-1 justify-content-end">
                        <button type="button" class="btn mb-2 me-2" id="view_estimate" style="background-color: #ffc107; color: black;">
                            <i class="fa fa-calculator fs-4 me-2"></i>
                            Send as Estimate
                        </button>
                        <button type="button" class="btn mb-2" id="view_order" style="background-color: #28a745; color: white;">
                            <i class="fa fa-shopping-cart fs-4 me-2"></i>
                            Send as Order
                        </button>
                    </div>
                </div>
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
                <button class="btn ripple btn-success d-none" type="button" id="save_estimate">
                    <i class="fe fe-hard-drive"></i> Send
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

<div class="modal" id="viewDetailsModal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Product Details</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewDetailsContent">
            
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="viewInStockmodal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Stock Details</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewInStockContent">
                
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="viewOutOfStockmodal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Stock Details</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewOutOfStockContent">
                
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="viewAvailablemodal"></div>

<div class="modal" id="viewAvailableColormodal"></div>

<div class="modal" id="cashmodal">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Save Order</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="order-tbl"></div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-primary next" type="button" id="next_page_order">
                    <i class="fe fe-hard-drive"></i> Next
                </button>
                <button class="btn ripple btn-primary previous d-none" type="button" id="prev_page_order">
                    <i class="fe fe-hard-drive"></i> Previous
                </button>
                <button class="btn ripple btn-success d-none" type="button" id="save_order">
                    <i class="fe fe-hard-drive"></i> Send
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
                <button class="btn ripple btn-danger" data-bs-dismiss="modal" type="button">Close</button>
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

<div class="modal fade" id="prompt_quantity_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog" role="document">
        <form id="quantity_form" class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Select Quantity</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="qty_prompt_container"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success ripple btn-secondary" data-bs-dismiss="modal" type="submit">Add to Cart</button>
                <button class="btn btn-danger ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
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
            zoom: 13,
        });
        marker1 = updateMarker(map1, marker1, lat1, lng1, "Starting Point");
        google.maps.event.addListener(map1, 'click', function(event) {
            lat1 = event.latLng.lat();
            lng1 = event.latLng.lng();
            marker1 = updateMarker(map1, marker1, lat1, lng1, "Starting Point");
            getPlaceName(lat1, lng1, '#searchBox1');
        });
    }

    function loadGoogleMapsAPI() {
        const script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDFpFbxFFK7-daOKoIk9y_GB4m512Tii8M&callback=initMaps&libraries=geometry,places';
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    }

    window.onload = loadGoogleMapsAPI;

    function calculateDeliveryAmount() {
        var customerLat = parseFloat($('#lat').val());
        var customerLng = parseFloat($('#lng').val());
        var lat2Float = parseFloat(lat2);
        var lng2Float = parseFloat(lng2);

        var deliver_method = $('#order_delivery_method').val();

        if(deliver_method == 'pickup'){
            $('#delivery_amt').val(0).trigger('change');
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

            $('#delivery_amt').val(deliveryAmount).trigger('change');
        }
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
            url: 'pages/browse_ajax.php',
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
            url: 'pages/browse_ajax.php',
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
            url: 'pages/browse_ajax.php',
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
            url: 'pages/browse_ajax.php',
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
            url: 'pages/browse_ajax.php',
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
            url: 'pages/browse_ajax.php',
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
            url: 'pages/browse_ajax.php',
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
            url: 'pages/browse_ajax.php',
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
            url: 'pages/browse_ajax.php',
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

    function loadCart(){      
        $.ajax({
            url: 'pages/customer_cart_modal.php',
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

    function loadOrderContents(){
        $.ajax({
            url: 'pages/customer_order_modal.php',
            type: 'POST',
            data: {
                fetch_order: "fetch_order"
            },
            success: function(response) {
                $('#order-tbl').html('');
                $('#order-tbl').html(response);
                calculateDeliveryAmount();
                loadCartItemsHeader();
                $('#next_page_order').removeClass("d-none");
                $('#prev_page_order').addClass("d-none");
                $('#save_order').addClass("d-none");
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadEstimateContents(){
        $.ajax({
            url: 'pages/customer_estimate_modal.php',
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
            url: "pages/browse_ajax.php",
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
            url: "pages/browse_ajax.php",
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
            url: "pages/browse_ajax.php",
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
            url: "pages/browse_ajax.php",
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
            url: "pages/browse_ajax.php",
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
            },
            error: function() {}
        });
    }

    function duplicate_item(element) {
        var product_id = $(element).data('id');
        var line = $(element).data('line');
        $.ajax({
            url: "pages/browse_ajax.php",
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
    
    $(document).ready(function() {
        var panel_id = '<?= $panel_id ?>';

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
            var grade_id = $('#select-grade').find('option:selected').val();
            var gauge_id = $('#select-gauge').find('option:selected').val();
            var category_id = $('#select-category').find('option:selected').val();
            var profile_id = $('#select-profile').find('option:selected').val();
            var type_id = $('#select-type').find('option:selected').val();
            var onlyInStock = $('#toggleActive').prop('checked');
            $.ajax({
                url: 'pages/browse_ajax.php',
                type: 'POST',
                data: {
                    query: query,
                    color_id: color_id,
                    grade_id: grade_id,
                    gauge_id: gauge_id,
                    category_id: category_id,
                    profile_id: profile_id,
                    type_id: type_id,
                    onlyInStock: onlyInStock
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

        $(document).on("click", "#add-to-cart-btn", function() {
            var id = $(this).data('id');
            $.ajax({
                url: 'pages/customer_quantity_modal.php',
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

        $(document).on('click', '#add-to-cart-non-panel', function() {
            var product_id = $(this).data('id');
            var qty = parseInt($('#qty' + product_id).val(), 10) || 0;

            $.ajax({
                url: "pages/browse_ajax.php",
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

                    setTimeout(function() {
                        $('#' + alertId).alert('close');
                    }, 5000);
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
                url: 'pages/customer_job_name_modal.php',
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
              
        $(document).on('click', '#save_estimate', function(event) {
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
                url: 'pages/browse_ajax.php',
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
                success: function(response) {
                    console.log(response);
                    if (response.success) {
                        alert("Estimate successfully saved.");
                        $('#print_estimate_category').attr('href', '/print_estimate_product.php?id=' + response.estimate_id);
                        $('#print_estimate_category').removeClass('d-none');
                        $('#print_estimate').attr('href', '/print_estimate_total.php?id=' + response.estimate_id);
                        $('#print_estimate').removeClass('d-none');
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
            var cash_amt = $('#order_cash').val();
            var credit_amt = $('#order_credit').val();
            var job_name = $('#order_job_name').val();
            var job_po = $('#order_job_po').val();
            var deliver_address = $('#order_deliver_address').val();
            var deliver_city = $('#order_deliver_city').val();
            var deliver_state = $('#order_deliver_state').val();
            var deliver_zip = $('#order_deliver_zip').val();
            var deliver_fname = $('#order_deliver_fname').val();
            var deliver_lname = $('#order_deliver_lname').val();
            console.log("Delivery Amt: "+delivery_amt);
            $.ajax({
                url: 'pages/browse_ajax.php',
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

        $(document).on('input', '#order_cash', function(event) {
            var cash_amt = parseFloat($('#order_cash').val()) || 0;
            var payable_amt = parseFloat($('#payable_amt').val()) || 0;

            var credit_amt = (payable_amt - cash_amt).toFixed(2);
            if (credit_amt < 0) {
                credit_amt = 0;
            }

            $('#order_credit').val(credit_amt);

            var change = (cash_amt - payable_amt).toFixed(2);
            if (change < 0) {
                change = 0;
            }

            $('#change').text(change);
        });

        $(document).on('input', '#order_credit', function(event) {
            var credit_input = $('#order_credit');
            var credit_amt = parseFloat(credit_input.val()) || 0;
            var payable_amt = parseFloat($('#payable_amt').val()) || 0;

            if (credit_amt > payable_amt) {
                credit_amt = payable_amt;
                credit_input.blur();
                credit_input.val(credit_amt.toFixed(2));
                credit_input.focus();
            }

            var cash_amt = (payable_amt - credit_amt).toFixed(2);
            if (cash_amt < 0) {
                cash_amt = 0;
            }

            $('#order_cash').val(cash_amt);

            var change = (cash_amt - payable_amt).toFixed(2);
            if (change < 0) {
                change = 0;
            }

            $('#change').text(change);
        });

        $(document).on('click', '#clear_cart', function(event) {
            event.preventDefault();
            var isConfirmed = confirm("Are you sure you want to clear your cart contents?");
            if (isConfirmed) {
                $.ajax({
                    url: 'pages/browse_ajax.php',
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
                url: 'pages/customer_prod_details_modal.php',
                type: 'POST',
                data: {
                    id: id,
                    fetch_details_modal: "fetch_details_modal"
                },
                success: function(response) {
                    $('#viewDetailsContent').html(response);
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
            $('#next_page_order').removeClass("d-none");
            $('#prev_page_order').addClass("d-none");
            $('#save_order').addClass("d-none");
            $('#print_order_category').addClass('d-none');
            $('#print_order').addClass('d-none');
            $('#print_deliver').addClass('d-none');
            $('#cashmodal').modal('show');
        });

        $(document).on('click', '#view_in_stock', function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/customer_in_stock_modal.php',
                    type: 'POST',
                    data: {
                        id: id,
                        fetch_in_stock_modal: "fetch_in_stock_modal"
                    },
                    success: function(response) {
                        $('#viewInStockContent').html(response);
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
                url: 'pages/customer_out_of_stock_modal.php',
                type: 'POST',
                data: {
                    id: id,
                    fetch_out_of_stock_modal: "fetch_out_of_stock_modal"
                },
                success: function(response) {
                    $('#viewOutOfStockContent').html(response);
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
                    url: 'pages/customer_available_modal.php',
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
                url: 'pages/customer_available_color_modal.php',
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
                    url: 'pages/browse_ajax.php',
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

        $(document).on('submit', '#job_name_form', function (event) {
            event.preventDefault();

            const formData = new FormData(this);
            formData.append('add_job_name', 'add_job_name');
            $.ajax({
                url: 'pages/browse_ajax.php',
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

            $('.filter-selection').each(function() {
                var selectedOption = $(this).find('option:selected');
                var selectedText = selectedOption.text().trim();
                var filterName = $(this).data('filter-name');

                if ($(this).val()) {
                    displayDiv.append(`
                        <div class="d-inline-block p-1 m-1 border rounded bg-light">
                            <span class="text-dark">${filterName}: ${selectedText}</span>
                            <button type="button" 
                                class="btn-close btn-sm ms-1 remove-tag" 
                                style="width: 0.75rem; height: 0.75rem;" 
                                aria-label="Close" 
                                data-select="#${$(this).attr('id')}">
                            </button>
                        </div>
                    `);
                }
            });

            $('.remove-tag').on('click', function() {
                $($(this).data('select')).val('').trigger('change');
                $(this).parent().remove();
            });
        }
        
        function updateSearchCategory(){
            var product_category = $('#select-category').val() || '';

            console.log(product_category);
            $.ajax({
                url: "pages/browse_ajax.php",
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
            $('.filter-selection').each(function () {
                $(this).val('').trigger('change');;
            });
            performSearch($('#text-srh').val());
        });

        $('.filter-selection').each(function () {
            $(this).select2({
                width: '100%',
                dropdownParent: $(this).parent(),
                dropdownPosition: 'below'
            });
        });

        $(document).on('input change', '#text-srh, #select-color, #select-grade, #select-gauge, #select-category, #select-profile, #select-type, #toggleActive', function() {
            performSearch($('#text-srh').val());
        });

        performSearch('');

        $(document).on('change', '#est_delivery_method', function () {
            calculateDeliveryAmountEst();
        });

        $(document).on('change', '#order_delivery_method', function () {
            calculateDeliveryAmount();
        });
    });
</script>
