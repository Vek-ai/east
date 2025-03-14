<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);
require '../includes/dbconn.php';
require '../includes/functions.php';

$lat = 0;
$lng = 0;

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
</style>
<div class="product-list pt-4">
    <!-- <div class="row row-xs pr-3">
        <div class="col-md-9"></div>
        <?php 
        $discount = 0;
        $tax = 0;
        $totalQuantity = 0;
        if(isset($_SESSION['customer_id'])){
            $customer_id = $_SESSION['customer_id'];
            $customer_details = getCustomerDetails($customer_id);
            $discount = floatval(getCustomerDiscount($customer_id)) / 100;
            $tax = floatval(getCustomerTax($customer_id)) / 100;
        }
        $delivery_price = getDeliveryCost();
        
        if (!empty($_SESSION["cart"])) {
            foreach ($_SESSION["cart"] as $item) {
                $totalQuantity += $item["quantity_cart"];
            }
        }
        ?>
        <div class="col-md-3 bg-primary rounded" style="padding: 1rem;">
            <div id="thegrandtotal" style="background: transparent; color: #fff; font-size: 16px;">
                <table style="width: 100%; margin: 0; border-spacing: 0;">
                    <tbody>
                        <tr style="height: 30px;">
                            <td style="text-align: left; width: 70%;">Total:</td>
                            <td style="text-align: right; width: 30%;"><?= "$" . number_format($_SESSION["grandtotal"] ?? 0, 2); ?></td>
                        </tr>
                        <tr style="height: 30px;">
                            <td style="text-align: left; width: 70%;">Total items:</td>
                            <td style="text-align: right; width: 30%;"><?= $totalQuantity ?></td>
                        </tr>
                        <tr style="height: 30px;">
                            <td style="text-align: left; width: 70%;">Discount:</td>
                            <td style="text-align: right; width: 30%;"><?= $discount * 100 ?>%</td>
                        </tr>
                        <tr style="height: 30px;">
                            <td style="text-align: left; width: 70%;">Delivery Amount:</td>
                            <td style="text-align: right; width: 30%;"><?= "$" . number_format($delivery_price, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div> -->
    <div class="card">
        <div class="card-body p-3">
            
            <div class="p-2 text-right">
                <input type="checkbox" id="toggleActive" checked> Show only In Stock
            </div>
            <div class="d-flex justify-content-between align-items-center mb-9">
                <div class="position-relative w-100 col-2 ps-0">
                    <input type="text" class="form-control search-chat py-2 ps-5 " id="text-srh" placeholder="Search Product">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
                <!-- <div class="position-relative w-100 px-1 col-2">
                    <select class="form-control search-chat py-0 ps-5" id="select-color" data-category="">
                        <option value="" data-category="">All Colors</option>
                        <optgroup label="Product Colors">
                            <?php
                            $query_color = "SELECT * FROM paint_colors WHERE hidden = '0'";
                            $result_color = mysqli_query($conn, $query_color);
                            while ($row_color = mysqli_fetch_array($result_color)) {
                            ?>
                                <option value="<?= $row_color['color_id'] ?>" data-category="category"><?= $row_color['color_name'] ?></option>
                            <?php
                            }
                            ?>
                        </optgroup>
                    </select>
                </div> -->
                <div class="position-relative w-100 px-1 col-2">
                    <select class="form-control search-chat py-0 ps-5" id="select-grade" data-category="">
                        <option value="" data-category="">All Grades</option>
                        <optgroup label="Product Grades">
                            <?php
                            $query_grade = "SELECT * FROM product_grade WHERE hidden = '0'";
                            $result_grade = mysqli_query($conn, $query_grade);
                            while ($row_grade = mysqli_fetch_array($result_grade)) {
                            ?>
                                <option value="<?= $row_grade['product_grade_id'] ?>" data-category="grade"><?= $row_grade['product_grade'] ?></option>
                            <?php
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
                <div class="position-relative w-100 px-1 col-2">
                    <select class="form-control search-chat py-0 ps-5" id="select-gauge" data-category="">
                        <option value="" data-category="">All Gauges</option>
                        <optgroup label="Product Gauges">
                            <?php
                            $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0'";
                            $result_gauge = mysqli_query($conn, $query_gauge);
                            while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                            ?>
                                <option value="<?= $row_gauge['product_gauge_id'] ?>" data-category="gauge"><?= $row_gauge['product_gauge'] ?></option>
                            <?php
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
                <div class="position-relative w-100 px-1 col-2">
                    <select class="form-control search-chat py-0 ps-5" id="select-category" data-category="">
                        <option value="" data-category="">All Categories</option>
                        <optgroup label="Category">
                            <?php
                            $query_category = "SELECT * FROM product_category WHERE hidden = '0'";
                            $result_category = mysqli_query($conn, $query_category);
                            while ($row_category = mysqli_fetch_array($result_category)) {
                            ?>
                                <option value="<?= $row_category['product_category_id'] ?>" data-category="category"><?= $row_category['product_category'] ?></option>
                            <?php
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
                <div class="position-relative w-100 px-1 col-2">
                    <select class="form-control search-chat py-0 ps-5" id="select-profile" data-category="">
                        <option value="" data-category="">All Profile Types</option>
                        <optgroup label="Product Line">
                            <?php
                            $query_profile = "SELECT * FROM profile_type WHERE hidden = '0'";
                            $result_profile = mysqli_query($conn, $query_profile);
                            while ($row_profile = mysqli_fetch_array($result_profile)) {
                            ?>
                                <option value="<?= $row_profile['profile_type_id'] ?>" data-category="profile"><?= $row_profile['profile_type'] ?></option>
                            <?php
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
                <div class="position-relative w-100 px-1 col-2">
                    <select class="form-control search-chat py-0 ps-5" id="select-type" data-category="">
                        <option value="" data-category="">All Product Types</option>
                        <optgroup label="Product Type">
                            <?php
                            $query_type = "SELECT * FROM product_type WHERE hidden = '0'";
                            $result_type = mysqli_query($conn, $query_type);
                            while ($row_type = mysqli_fetch_array($result_type)) {
                            ?>
                                <option value="<?= $row_type['product_type_id'] ?>" data-category="type"><?= $row_type['product_type'] ?></option>
                            <?php
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="table-responsive border rounded">
                <table id="productTable" class="table align-middle text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Products</th>
                            <th scope="col">Avail. Colors</th>
                            <th scope="col">Grade</th>
                            <th scope="col">Gauge</th>
                            <th scope="col">Type</th>
                            <th scope="col">Profile</th>
                            <th scope="col">Category</th>
                            <th scope="col">Status</th>
                           
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody"></tbody>
                </table>
                    
                <div class="d-flex align-items-center justify-content-end py-1">
                    <p class="mb-0 fs-2">Rows per page:</p>
                    <select id="rowsPerPage" class="form-select w-auto ms-0 ms-sm-2 me-8 me-sm-4 py-1 pe-7 ps-2 border-0" aria-label="Rows per page">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                    </select>
                    <p id="paginationInfo" class="mb-0 fs-2"></p>
                    <nav aria-label="...">
                        <ul id="paginationControls" class="pagination justify-content-center mb-0 ms-8 ms-sm-9">
                            <!-- Pagination buttons will be inserted here by JS -->
                        </ul>
                    </nav>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-6">
                    <div class="d-flex justify-content-start">
                        <!-- 
                        <button class="btn btn-primary mb-2 me-2" type="button" id="view_est_list">
                            <i class="fa fa-save fs-4 me-2"></i>
                            View Estimates
                        </button>
                        <button class="btn btn-primary mb-2 me-2" type="button" id="view_order_list">
                            <i class="fa fa-rotate-left fs-4 me-2"></i>
                            Return
                        </button> 
                        -->
                        <a href="/">
                            <button class="btn btn-primary mb-2 me-2" type="button">
                                <i class="fa fa-home fs-4 me-2"></i>
                                Main Dashboard
                            </button> 
                        </a>
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary mb-2 me-2" type="button" id="view_cart">
                            <i class="fa fa-shopping-cart fs-4 me-2"></i>
                            Cart
                        </button>
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
                <div id="customer_cart_section">
                    <?php 
                        if(!empty($_SESSION["customer_id"])){
                            $customer_id = $_SESSION["customer_id"];
                            $customer_details = getCustomerDetails($customer_id);
                            $credit_limit = number_format($customer_details['credit_limit'] ?? 0,2);
                            $credit_total = number_format(getCustomerCreditTotal($customer_id),2);
                        ?>

                        <div class="form-group row align-items-center">
                            <div class="col-6">
                                <label class="mb-0 me-3">Customer Name: <?= get_customer_name($_SESSION["customer_id"]);?></label>
                                <button class="btn btn-primary btn-sm me-3" type="button" id="customer_change_cart">
                                    <i class="fe fe-reload"></i> Change
                                </button>
                                <div class="mt-1"> 
                                    <span class="fw-bold">Address: <?= getCustomerAddress($_SESSION["customer_id"]) ?></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div>
                                    <span class="fw-bold">Credit Limit:</span><br>
                                    <span class="text-primary fs-5 fw-bold pl-3">$<?= $credit_limit ?></span>
                                </div>
                                <div>
                                    <span class="fw-bold">Unpaid Credit:</span><br>
                                    <span class="text-primary fs-5 fw-bold pl-3">$<?= $credit_total ?></span>
                                </div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="form-group row align-items-center">
                            <div class="col-6">
                                <label>Customer Name</label>
                                <div class="input-group">
                                    <input class="form-control" placeholder="Search Customer" type="text" id="customer_select_cart">
                                    <a class="input-group-text rounded-right m-0 p-0" href="/cashier/?page=customer" target="_blank">
                                        <span class="input-group-text"> + </span>
                                    </a>
                                </div>
                            </div>
                            <div class="col-6">
                                <span class="fw-bold">Credit Limit:</span><br>
                                <span class="text-primary fw-bold ms-3">Credit Limit: $0.00</span>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <input type='hidden' id='customer_id_cart' name="customer_id"/>
                <div id="cart-tbl">
                </div>
            </div>
            <div class="">
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex flex-wrap justify-content-center">
                            <button type="button" class="btn mb-2 me-2 flex-fill" id="clear_cart" style="background-color: #dc3545; color: white;">
                                <i class="fa fa-trash fs-4 me-2"></i>
                                Clear Cart
                            </button>
                            <button type="button" class="btn mb-2 me-2 flex-fill" id="change_price_group" style="background-color: #007bff; color: white;">
                                <i class="fa fa-tag fs-4 me-2"></i>
                                Change Price Group
                            </button>
                            <button type="button" class="btn mb-2 me-2 flex-fill" id="change_grade" style="background-color: #6c757d; color: white;">
                                <i class="fa fa-chart-line fs-4 me-2"></i>
                                Change Grade
                            </button>
                            <button type="button" class="btn mb-2 me-2 flex-fill" id="btnColorModal" style="background-color: #17a2b8; color: white;">
                                <i class="fa fa-palette fs-4 me-2"></i>
                                Change Color
                            </button>
                            <button type="button" class="btn mb-2 me-2 flex-fill" id="view_estimate" style="background-color: #ffc107; color: black;">
                                <i class="fa fa-calculator fs-4 me-2"></i>
                                Estimate
                            </button>
                            <button type="button" class="btn mb-2 me-2 flex-fill" id="view_order" style="background-color: #28a745; color: white;">
                                <i class="fa fa-shopping-cart fs-4 me-2"></i>
                                Order
                            </button>
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
                <div class="form-group col-12">
                    <div id="customer_est_section">
                        <?php 
                            if(!empty($_SESSION["customer_id"])){
                                $customer_id = $_SESSION["customer_id"];
                                $customer_details = getCustomerDetails($customer_id);
                                $credit_limit = number_format($customer_details['credit_limit'] ?? 0,2);
                                $credit_total = number_format(getCustomerCreditTotal($customer_id),2);
                            ?>

                            <div class="form-group row align-items-center">
                                <div class="col-6">
                                    <label class="mb-0 me-3">Customer Name: <?= get_customer_name($_SESSION["customer_id"]);?></label>
                                    <button class="btn btn-primary btn-sm me-3" type="button" id="customer_change_estimate">
                                        <i class="fe fe-reload"></i> Change
                                    </button>
                                    <div class="mt-1"> 
                                        <span class="fw-bold">Address: <?= getCustomerAddress($_SESSION["customer_id"]) ?></span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div>
                                        <span class="fw-bold">Credit Limit:</span><br>
                                        <span class="text-primary fs-5 fw-bold pl-3">$<?= $credit_limit ?></span>
                                    </div>
                                    <div>
                                        <span class="fw-bold">Unpaid Credit:</span><br>
                                        <span class="text-primary fs-5 fw-bold pl-3">$<?= $credit_total ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="form-group row align-items-center">
                                <div class="col-6">
                                    <label>Customer Name</label>
                                    <div class="input-group">
                                        <input class="form-control" placeholder="Search Customer" type="text" id="customer_select_estimate">
                                        <a class="input-group-text rounded-right m-0 p-0" href="/cashier/?page=customer" target="_blank">
                                            <span class="input-group-text"> + </span>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <span class="fw-bold">Credit Limit:</span><br>
                                    <span class="text-primary fw-bold ms-3">Credit Limit: $0.00</span>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <input type='hidden' id='customer_id_estimate' name="customer_id"/>
                </div>
                <div id="estimate-tbl"></div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary d-flex align-items-center mb-2 me-2" id="save_estimate">
                    <i class="fa fa-save fs-4 me-2"></i>
                    Save
                </button>
                <a href="#" class="btn ripple btn-success d-none" type="button" id="print_estimate_category" target="_blank">
                    <i class="fe fe-print"></i> Print Details
                </a>
                <a href="#" class="btn ripple btn-warning d-none" type="button" id="print_estimate" target="_blank">
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
                <h6 class="modal-title">Save Order</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <div id="customer_cash_section">
                        <?php 
                        if(!empty($_SESSION["customer_id"])){
                            $customer_id = $_SESSION["customer_id"];
                            $customer_details = getCustomerDetails($customer_id);
                            $credit_limit = number_format($customer_details['credit_limit'] ?? 0,2);
                            $credit_total = number_format(getCustomerCreditTotal($customer_id),2);
                            $lat = !empty($customer_details['lat']) ? $customer_details['lat'] : 0;
                            $lng = !empty($customer_details['lng']) ? $customer_details['lng'] : 0;

                            $addressDetails = implode(', ', [
                                $customer_details['address'] ?? '',
                                $customer_details['city'] ?? '',
                                $customer_details['state'] ?? '',
                                $customer_details['zip'] ?? ''
                            ]);
                        ?>
                        <div class="form-group row align-items-center">
                            <div class="col-6">
                                <label>Customer Name: <?= get_customer_name($_SESSION["customer_id"]); ?></label>
                                <button class="btn btn-sm ripple btn-primary mt-1" type="button" id="customer_change_cash">
                                    <i class="fe fe-reload"></i> Change
                                </button>
                                <div class="mt-1"> 
                                    <div id="defaultDeliverDetails">
                                        <span class="fw-bold">Address: <?= getCustomerAddress($_SESSION["customer_id"]) ?></span>
                                        <button class="btn btn-sm ripple btn-primary mt-1" type="button" id="address_change_cash">
                                            <i class="fe fe-reload"></i> Change
                                        </button>
                                    </div>
                                    <div class="mt-1">
                                        <div id="deliverDetails" class="row d-none">
                                            <div class="col-12">
                                                <label>Recipient:</label>
                                                <div class="row mb-3">
                                                    <div class="col-sm-6">
                                                        <input type="text" id="order_deliver_fname" name="order_deliver_fname" value="<?= $customer_details['customer_first_name'] ?>" class="form-control diffNameInput" placeholder="First Name">
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <input type="text" id="order_deliver_lname" name="order_deliver_lname" value="<?= $customer_details['customer_last_name'] ?>" class="form-control diffNameInput" placeholder="Last Name">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <label>Address:</label>
                                                <div class="row mb-3">
                                                    <div class="col-sm-3">
                                                        <input type="text" id="order_deliver_address" name="order_deliver_address" value="<?= $customer_details['address'] ?>" class="form-control" placeholder="Address">
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <input type="text" id="order_deliver_city" name="order_deliver_city" value="<?= $customer_details['city'] ?>" class="form-control" placeholder="City">
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <input type="text" id="order_deliver_state" name="order_deliver_state" value="<?= $customer_details['state'] ?>" class="form-control" placeholder="State">
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <input type="text" id="order_deliver_zip" name="order_deliver_zip" value="<?= $customer_details['zip'] ?>" class="form-control" placeholder="Zip">
                                                    </div>
                                                </div>
                                            </div>

                                            <input type="hidden" id="lat" name="lat" class="form-control" value="<?= $lat ?>" />
                                            <input type="hidden" id="lng" name="lng" class="form-control" value="<?= $lng ?>" />

                                            <div class="col-12 text-end">
                                                <button class="btn btn-sm ripple btn-primary mt-1" type="button" id="openMap">
                                                    <i class="fa fa-map"></i> Open Map
                                                </button>
                                                <button class="btn btn-sm ripple btn-primary mt-1" type="button" id="cancel_change_address" >
                                                    <i class="fa fa-rotate-left"></i> Cancel
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div>
                                    <span class="fw-bold">Credit Limit:</span><br>
                                    <span class="text-primary fs-5 fw-bold pl-3">$<?= $credit_limit ?></span>
                                </div>
                                <div>
                                    <span class="fw-bold">Unpaid Credit:</span><br>
                                    <span class="text-primary fs-5 fw-bold pl-3">$<?= $credit_total ?></span>
                                </div>
                            </div>
                        </div>

                        <?php } else {?>
                        
                        <div class="form-group row align-items-center">
                            <div class="col-3">
                                <label>Customer Name</label>
                                <div class="input-group">
                                    <input class="form-control" placeholder="Search Customer" type="text" id="customer_select_cash">
                                    <a class="input-group-text rounded-right m-0 p-0" href="/cashier/?page=customer" target="_blank">
                                        <span class="input-group-text"> + </span>
                                    </a>
                                </div>
                            </div>
                            <div class="col-3">
                                <span class="fw-bold">Credit Limit:</span><br>
                                <span class="text-primary fw-bold ms-3">Credit Limit: $0.00</span>
                            </div>
                        </div>
                        
                    <?php } ?>
                    </div>
                    <input type='hidden' id='customer_id_cash' name="customer_id"/>
                </div>
                <div id="order-tbl">
                    
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-primary next" type="button" id="next_page_order">
                    <i class="fe fe-hard-drive"></i> Next
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

<div class="modal fade" id="response-modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
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

<div class="modal fade" id="chng-color-modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);"></div>

<div class="modal fade" id="prompt-quantity-modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);"></div>

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

                } else {
                    console.error("Address not found for these coordinates.");
                    $(inputId).val("Address not found");
                }

                calculateDeliveryAmount();
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
        $.ajax({
            url: 'pages/cashier_order_list_modal.php',
            type: 'POST',
            data: {
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
                
                setTimeout(function() {
                    $(".color-cart").each(function() {
                        if ($(this).data('select2')) {
                            $(this).select2('destroy');
                        }
                        $(this).select2({
                            width: '300px',
                            placeholder: "Select...",
                            dropdownAutoWidth: true,
                            dropdownParent: $('#cartTable'),
                            templateResult: formatOption,
                            templateSelection: formatSelected
                        });
                    });

                    $(".usage-cart").each(function() {
                        if ($(this).data('select2')) {
                            $(this).select2('destroy');
                        }
                        $(this).select2({
                            width: '300px',
                            placeholder: "Select...",
                            dropdownAutoWidth: true,
                            dropdownParent: $('#cartTable')
                        });
                    });
                }, 100);

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
            url: 'pages/cashier_order_modal.php',
            type: 'POST',
            data: {
                fetch_order: "fetch_order"
            },
            success: function(response) {
                $('#order-tbl').html('');
                setTimeout(function() {    
                    $(".color-order").each(function() {
                        if ($(this).data('select2')) {
                            $(this).select2('destroy');
                        }
                        $(this).select2({
                            width: '300px',
                            placeholder: "Select...",
                            dropdownAutoWidth: true,
                            dropdownParent: $('#orderTable'),
                            templateResult: formatOption,
                            templateSelection: formatSelected
                        });
                    });

                    $(".usage-order").each(function() {
                        if ($(this).data('select2')) {
                            $(this).select2('destroy');
                        }
                        $(this).select2({
                            width: '300px',
                            placeholder: "Select...",
                            dropdownAutoWidth: true,
                            dropdownParent: $('#orderTable')
                        });
                    });

                }, 100);
                $('#order-tbl').html(response);
                calculateDeliveryAmount();
                loadCartItemsHeader();
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
                setTimeout(function() {
                    $(".color-est").each(function() {
                        if ($(this).data('select2')) {
                            $(this).select2('destroy');
                        }
                        $(this).select2({
                            width: '300px',
                            placeholder: "Select...",
                            dropdownAutoWidth: true,
                            dropdownParent: $('#estimateTable'),
                            templateResult: formatOption,
                            templateSelection: formatSelected
                        });
                    });

                    $(".usage-est").each(function() {
                        if ($(this).data('select2')) {
                            $(this).select2('destroy');
                        }
                        $(this).select2({
                            width: '300px',
                            placeholder: "Select...",
                            dropdownAutoWidth: true,
                            dropdownParent: $('#estimateTable')
                        });
                    });
                }, 100);
                $('#estimate-tbl').html(response);
                loadCartItemsHeader();
                
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

    function initializeDrawingApp() {
        const canvas = document.getElementById('drawingCanvas');
        const ctx = canvas.getContext('2d');
        const totalLengthDiv = document.getElementById('totalLength');
        const totalCostDiv = document.getElementById('totalCost');
        const lengthAnglePairs = document.getElementById('lengthAnglePairs');
        const clearButton = document.getElementById('clearButton');
        const saveDrawing = document.getElementById('saveDrawing');

        let points = [];
        let lengths = [];
        let angles = [];
        let colors = [];
        let currentStartPoint = null;
        const pixelsPerInch = 96;

        const colorPrices = {
            black: 1.9,
            red: 2.0,
            green: 2.1,
            blue: 2.3,
            yellow: 2.5
        };

        function drawPlaceholderText() {
            ctx.font = "30px Arial";
            ctx.fillStyle = "lightgray";
            ctx.textAlign = "center";
            ctx.fillText("Draw here", canvas.width / 2, canvas.height / 2);
        }

        function drawLine(point1, point2, color) {
            ctx.beginPath();
            ctx.moveTo(point1.x, point1.y);
            ctx.lineTo(point2.x, point2.y);
            ctx.strokeStyle = color;
            ctx.stroke();
        }

        function drawTemporaryLine(point1, point2) {
            ctx.beginPath();
            ctx.moveTo(point1.x, point1.y);
            ctx.lineTo(point2.x, point2.y);
            ctx.strokeStyle = 'gray';
            ctx.stroke();
        }

        function calculateDistance(point1, point2) {
            const distanceInPixels = Math.sqrt(Math.pow(point2.x - point1.x, 2) + Math.pow(point2.y - point1.y, 2));
            return (distanceInPixels / pixelsPerInch).toFixed(2);
        }

        function calculateInteriorAngle(p1, p2, p3) {
            const angle = Math.atan2(p3.y - p2.y, p3.x - p2.x) - Math.atan2(p1.y - p2.y, p1.x - p2.x);
            let degrees = (angle * 180 / Math.PI) % 360;
            if (degrees < 0) {
                degrees += 360;
            }
            if (degrees > 180) {
                degrees = 360 - degrees;
            }
            return degrees;
        }

        function drawAngleArc(p1, p2, p3, angle) {
            const radius = 30;
            const startAngle = Math.atan2(p1.y - p2.y, p1.x - p2.x);
            const endAngle = Math.atan2(p3.y - p2.y, p3.x - p2.x);

            ctx.beginPath();
            ctx.arc(p2.x, p2.y, radius, startAngle, endAngle, endAngle < startAngle);
            ctx.strokeStyle = 'red';
            ctx.stroke();
        }

        function updateLengthAnglePairs() {
            lengthAnglePairs.innerHTML = '';
            let totalLength = 0;
            let totalCost = 0;

            lengths.forEach((length, index) => {
                const pair = document.createElement('div');
                pair.classList.add('length-angle-pair');

                const lengthDiv = document.createElement('div');
                lengthDiv.textContent = `Line ${index + 1}: ${length} inches`;
                totalLength += parseFloat(length);

                pair.appendChild(lengthDiv);

                if (index < angles.length) {
                    const angleInput = document.createElement('input');
                    angleInput.type = 'number';
                    angleInput.value = angles[index].toFixed(2);
                    angleInput.addEventListener('change', (e) => {
                        const newAngle = parseFloat(e.target.value);
                        angles[index] = newAngle;
                        const newPoint = calculateNewPoint(points[index - 1], points[index], lengths[index], newAngle);
                        points[index + 1] = newPoint;
                        redrawCanvas();
                    });

                    const angleLabel = document.createElement('label');
                    angleLabel.textContent = 'Angle (°): ';
                    pair.appendChild(angleLabel);
                    pair.appendChild(angleInput);
                }

                const colorSelect = document.createElement('select');
                const colorsOptions = Object.keys(colorPrices);
                colorsOptions.forEach((color) => {
                    const option = document.createElement('option');
                    option.value = color;
                    option.textContent = color.charAt(0).toUpperCase() + color.slice(1);
                    colorSelect.appendChild(option);
                });
                colorSelect.value = colors[index];
                colorSelect.addEventListener('change', (e) => {
                    colors[index] = e.target.value;
                    updateLengthAnglePairs();
                    redrawCanvas();
                });

                pair.appendChild(colorSelect);

                const price = colorPrices[colors[index]];
                const lineTotal = parseFloat(length) * price;
                totalCost += lineTotal;

                const priceDiv = document.createElement('div');
                priceDiv.textContent = `Price: $${price.toFixed(2)}`;
                pair.appendChild(priceDiv);

                const totalDiv = document.createElement('div');
                totalDiv.textContent = `Total: $${lineTotal.toFixed(2)}`;
                pair.appendChild(totalDiv);

                lengthAnglePairs.appendChild(pair);
            });

            totalLengthDiv.textContent = `Total Length: ${totalLength.toFixed(2)} inches`;
            totalCostDiv.textContent = `Total Cost: $${totalCost.toFixed(2)}`;
        }

        function calculateNewPoint(p1, p2, length, angle) {
            const radians = (angle * Math.PI) / 180;
            const dx = length * Math.cos(radians);
            const dy = length * Math.sin(radians);
            return { x: p2.x + dx, y: p2.y + dy };
        }

        function clearCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        function redrawCanvas() {
            clearCanvas();
            for (let i = 1; i < points.length; i++) {
                drawLine(points[i - 1], points[i], colors[i - 1]);
            }
            for (let i = 2; i < points.length; i++) {
                drawAngleArc(points[i - 2], points[i - 1], points[i], angles[i - 2]);
            }
            if (points.length === 0) {
                drawPlaceholderText();
            }
        }

        canvas.addEventListener('click', (e) => {
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            let selectedPoint = { x, y };

            for (let point of points) {
                if (Math.hypot(point.x - x, point.y - y) < 5) {
                    selectedPoint = point;
                    break;
                }
            }

            if (currentStartPoint) {
                points.push(selectedPoint);
                colors.push('black');
                drawLine(currentStartPoint, selectedPoint, 'black');
                const length = calculateDistance(currentStartPoint, selectedPoint);
                lengths.push(length);
                if (points.length > 2) {
                    const angle = calculateInteriorAngle(points[points.length - 3], points[points.length - 2], points[points.length - 1]);
                    angles.push(angle);
                    drawAngleArc(points[points.length - 3], points[points.length - 2], points[points.length - 1], angle);
                }
                updateLengthAnglePairs();
                currentStartPoint = null;
            } else {
                currentStartPoint = selectedPoint;
                if (!points.includes(selectedPoint)) {
                    points.push(selectedPoint);
                }
            }
        });

        canvas.addEventListener('mousemove', (e) => {
            if (currentStartPoint) {
                const rect = canvas.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                redrawCanvas();
                drawTemporaryLine(currentStartPoint, { x, y });
            }
        });

        clearButton.addEventListener('click', () => {
            clearCanvas();
            points = [];
            lengths = [];
            angles = [];
            colors = [];
            currentStartPoint = null;
            updateLengthAnglePairs();
            drawPlaceholderText();
        });

        saveDrawing.addEventListener('click', () => {
            var isSave = confirm("Are you sure you want to finalize your custom trim?");
            
            if (isSave) {
                const canvasDrawn = $('#drawingCanvas')[0];
                const image_data = canvasDrawn.toDataURL('image/png');

                const id = $('#custom_trim_id').val();
                const line = $('#custom_trim_line').val();

                $.ajax({
                    url: 'pages/cashier_ajax.php',
                    type: 'POST',
                    contentType: 'application/json',
                    dataType: 'json',
                    data: JSON.stringify({
                        image_data: image_data,
                        save_drawing: 'save_drawing',
                        id: id,
                        line: line
                    }),
                    success: function(response) {
                        if (response.filename) {
                            loadCart();
                            loadOrderContents();
                            loadEstimateContents();
                            $('#custom_trim_draw_modal').modal('hide');
                        } else {
                            console.log("Error: " + response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("Error: " + xhr.responseText);
                    }
                });
            }
        });

        drawPlaceholderText();
    }

    $("#customer_select_cart").autocomplete({
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
            $('#customer_select_cart').val(ui.item.label);
            $('#customer_id_cart').val(ui.item.value);
            return false;
        },
        focus: function(event, ui) {
            $('#customer_select_cart').val(ui.item.label);
            return false;
        },
        appendTo: "#view_cart_modal", 
        open: function() {
            $(".ui-autocomplete").css("z-index", 1050);
        }
    });

    $("#customer_select_estimate").autocomplete({
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
            $('#customer_select_estimate').val(ui.item.label);
            $('#customer_id_estimate').val(ui.item.value);
            return false;
        },
        focus: function(event, ui) {
            $('#customer_select_estimate').val(ui.item.label);
            return false;
        },
        appendTo: "#view_estimate_modal", 
        open: function() {
            $(".ui-autocomplete").css("z-index", 1050);
        }
    });

    $("#customer_select_cash").autocomplete({
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
            $('#customer_select_cash').val(ui.item.label);
            $('#customer_id_cash').val(ui.item.value);
            return false;
        },
        focus: function(event, ui) {
            $('#customer_select_cash').val(ui.item.label);
            return false;
        },
        appendTo: "#cashmodal", 
        open: function() {
            $(".ui-autocomplete").css("z-index", 1050);
        }
    });

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
                    $('#chng-color-modal').html(response);
                    $('#chng-color-modal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#save_color_change', function () {
            var orig_color = $('#orig-colors').val();
            var in_stock_color = $('#in-stock-colors').val();
            var category_id = $('#category_id').val();

            console.log(orig_color);
            console.log(in_stock_color);
            console.log(category_id);
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
                        $('#responseMsg').text("Product Returned successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");
                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    }else{
                        $('#responseHeader').text("Failed");
                        $('#responseMsg').text(response);
                        $('#responseHeaderContainer').removeClass("bg-success");
                        $('#responseHeaderContainer').addClass("bg-danger");
                        $('#response-modal').modal("show");
                        $('#response-modal').on('hide.bs.modal', function () {
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

        let animating = false;

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
            //var color_id = $('#select-color').find('option:selected').val();
            var grade_id = $('#select-grade').find('option:selected').val();
            var gauge_id = $('#select-gauge').find('option:selected').val();
            var category_id = $('#select-category').find('option:selected').val();
            var profile_id = $('#select-profile').find('option:selected').val();
            var type_id = $('#select-type').find('option:selected').val();
            var onlyInStock = $('#toggleActive').prop('checked');
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    query: query,
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
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        }

        $(document).on('change', '#delivery_amt', function() {
            var product_cost = parseFloat($('#total_amt').text()) || 0;
            var delivery_cost = parseFloat($(this).val()) || 0;
            var total_payable = product_cost + delivery_cost;
            $('#total_payable').text(total_payable.toFixed(2));
            $('#order_cash').val(total_payable.toFixed(2));
        });

        $(document).on("click", "#next_page_order", function() {
            if (animating) return false;
            animating = true;
            var current_fs = $('.order-page-1');
            var next_fs = $('.order-page-2');
            $('#next_page_order').addClass("d-none");
            $('#prev_page_order').removeClass("d-none");
            $('#save_order').removeClass("d-none");
            next_fs.show();
            current_fs.animate({ opacity: 0 }, {
                step: function(now, mx) {
                    var scale = 1 - (1 - now) * 0.2;
                    var left = (now * 50) + "%";
                    var opacity = 1 - now;
                    current_fs.css({
                        'transform': 'scale(' + scale + ')',
                        'position': 'absolute'
                    });
                    next_fs.css({ 'left': left, 'opacity': opacity });
                },
                duration: 800,
                complete: function() {
                    current_fs.hide();
                    animating = false;
                },
                easing: 'easeInOutBack'
            });
        });

        $(document).on("click", "#prev_page_order", function() {
            
            if (animating) return false;
            animating = true;
            var current_fs = $('.order-page-2');
            var previous_fs = $('.order-page-1');
            $('#next_page_order').removeClass("d-none");
            $('#prev_page_order').addClass("d-none");
            $('#save_order').addClass("d-none");
            previous_fs.show();
            current_fs.animate({ opacity: 0 }, {
                step: function(now, mx) {
                    var scale = 0.8 + (1 - now) * 0.2;
                    var left = ((1 - now) * 50) + "%";
                    var opacity = 1 - now;
                    current_fs.css({ 'left': left });
                    previous_fs.css({ 'transform': 'scale(' + scale + ')', 'opacity': opacity });
                },
                duration: 800,
                complete: function() {
                    current_fs.hide();
                    animating = false;
                },
                easing: 'easeInOutBack'
            });
            
        });
        
        $(document).on("click", "#add-to-cart-btn", function() {
            var id = $(this).data('id');
            $.ajax({
                url: 'pages/cashier_quantity_modal.php',
                type: 'POST',
                data: {
                    id: id,
                    fetch_prompt_quantity: 'fetch_prompt_quantity'
                },
                success: function(response) {
                    $('#prompt-quantity-modal').html(response);
                    $('#prompt-quantity-modal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });
        
        
        $(document).on('click', '#save_estimate', function(event) {
            var discount = $('#est_discount').val();
            var job_name = $('#est_job_name').val();
            var job_po = $('#est_job_po').val();
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    discount: discount,
                    job_name: job_name,
                    job_po: job_po,
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

        $(document).on('change', '#customer_select_cart', function(event) {
            var customer_id = $('#customer_id_cart').val();
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    customer_id: customer_id,
                    change_customer: "change_customer"
                },
                success: function(response) {
                    if (response.trim() == 'success') {
                        $('#customer_cart_section').load(location.href + " #customer_cart_section");
                        loadOrderContents();
                        loadEstimateContents();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#customer_change_cart', function(event) {
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    unset_customer: "unset_customer"
                },
                success: function(response) { 
                    $('#customer_cart_section').load(location.href + " #customer_cart_section", function() {
                        $.getScript("https://code.jquery.com/ui/1.12.1/jquery-ui.min.js", function() {
                            $("#customer_select_cart").autocomplete({
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
                                    $('#customer_select_cart').val(ui.item.label);
                                    $('#customer_id_cart').val(ui.item.value);
                                    return false;
                                },
                                focus: function(event, ui) {
                                    $('#customer_select_cart').val(ui.item.label);
                                    return false;
                                },
                                appendTo: "#view_cart_modal", 
                                open: function() {
                                    $(".ui-autocomplete").css("z-index", 1050);
                                }
                            });
                        });
                        loadOrderContents();
                        loadEstimateContents();
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('change', '#customer_select_estimate', function(event) {
            var customer_id = $('#customer_id_estimate').val();
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    customer_id: customer_id,
                    change_customer: "change_customer"
                },
                success: function(response) {
                    if (response.trim() == 'success') {
                        $('#customer_est_section').load(location.href + " #customer_est_section");
                        loadOrderContents();
                        loadEstimateContents();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('change', '#customer_select_cash', function(event) {
            var customer_id = $('#customer_id_cash').val();
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    customer_id: customer_id,
                    change_customer: "change_customer"
                },
                success: function(response) {
                    if (response.trim() == 'success') {
                        $('#customer_cash_section').load(location.href + " #customer_cash_section");
                        loadOrderContents();
                        $('#next_page_order').removeClass("d-none");
                        $('#prev_page_order').addClass("d-none");
                        $('#save_order').addClass("d-none");
                        loadEstimateContents();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#cancel_change_address', function(event) {
            $('#customer_cash_section').load(location.href + " #customer_cash_section");
        });

        $(document).on('click', '#customer_change_cash', function(event) {
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    unset_customer: "unset_customer"
                },
                success: function(response) {
                    $('#customer_cash_section').load(location.href + " #customer_cash_section", function() {
                        $.getScript("https://code.jquery.com/ui/1.12.1/jquery-ui.min.js", function() {
                            $("#customer_select_cash").autocomplete({
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
                                    $('#customer_select_cash').val(ui.item.label);
                                    $('#customer_id_cash').val(ui.item.value);
                                    return false;
                                },
                                focus: function(event, ui) {
                                    $('#customer_select_cash').val(ui.item.label);
                                    return false;
                                },
                                appendTo: "#cashmodal",
                                open: function() {
                                    $(".ui-autocomplete").css("z-index", 1050);
                                }
                            });
                        });
                    });
                    loadOrderContents();
                    $('#next_page_order').removeClass("d-none");
                    $('#prev_page_order').addClass("d-none");
                    $('#save_order').addClass("d-none");
                    loadEstimateContents();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#address_change_cash', function(event) {
            $('#deliverDetails').removeClass('d-none');
            $('#defaultDeliverDetails').addClass('d-none');
            $('#order_deliver_fname').val('');
            $('#order_deliver_lname').val('');
            $('#order_deliver_address').val('');
            $('#order_deliver_city').val('');
            $('#order_deliver_state').val('');
            $('#order_deliver_zip').val('');
        });

        $(document).on('click', '#customer_change_estimate', function(event) {
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    unset_customer: "unset_customer"
                },
                success: function(response) { 
                    $('#customer_est_section').load(location.href + " #customer_est_section", function() {
                        $.getScript("https://code.jquery.com/ui/1.12.1/jquery-ui.min.js", function() {
                            $("#customer_select_estimate").autocomplete({
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
                                    $('#customer_select_estimate').val(ui.item.label);
                                    $('#customer_id_estimate').val(ui.item.value);
                                    return false;
                                },
                                focus: function(event, ui) {
                                    $('#customer_select_estimate').val(ui.item.label);
                                    return false;
                                },
                                appendTo: "#view_estimate_modal", 
                                open: function() {
                                    $(".ui-autocomplete").css("z-index", 1050);
                                }
                            });
                        });
                        loadOrderContents();
                        loadEstimateContents();
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#custom_trim_draw', function(event) {
            loadDrawingModal(this);
            $('#custom_trim_draw_modal').modal('show');
        });

        $(document).on('click', '#view_cart', function(event) {
            loadCart();
            $('#view_cart_modal').modal('show');
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
            loadOrderList();
            $('#view_order_list_modal').modal('show');
        });

        $(document).on('click', '#view_order_details', function(event) {
            var orderid = $(this).data('id');
            loadOrderDetails(orderid);
            $('#view_order_details_modal').modal('toggle');
        });

        $(document).on('click', '#return_product', function(event) {
            event.preventDefault();

            var id = $(this).data('id');
            var quantity = $('#return_quantity' + id).val();

            if (confirm("Are you sure you want to return this product?")) {
                $.ajax({
                    url: 'pages/cashier_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        quantity: quantity,
                        return_product: "return_product"
                    },
                    success: function(response) {
                        if (response.trim() === "success") {
                            $('#responseHeader').text("Success");
                            $('#responseMsg').text("Product Returned successfully.");
                            $('#responseHeaderContainer').removeClass("bg-danger");
                            $('#responseHeaderContainer').addClass("bg-success");
                            $('#response-modal').modal("show");
                            $('#response-modal').on('hide.bs.modal', function () {
                                location.reload();
                            });
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
                });
            }
        });

        $(document).on('click', '#view_estimate', function(event) {
            loadEstimateContents();
            $('#view_estimate_modal').modal('show');
        });

        $(document).on('click', '#view_order', function(event) {
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

        $(document).on('submit', '#quantity_form', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            formData.append('add_to_cart', 'add_to_cart');
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    console.log(response);
                    $('.modal').modal("hide");
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Added to Cart.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");
                    }else{
                        $('#responseHeader').text("Failed");
                        $('#responseMsg').text(response);
                        $('#responseHeaderContainer').removeClass("bg-success");
                        $('#responseHeaderContainer').addClass("bg-danger");
                        $('#response-modal').modal("show");
                    }
                    loadCart();
                },
                error: function (xhr, status, error) {
                    console.error('Error:', xhr.responseText);
                }
            });
        });

        $('#rowsPerPage').change(function() {
            rowsPerPage = parseInt($(this).val());
            currentPage = 1;
            updateTable();
        });

        //$('#select-color').select2();
        $('#select-grade').select2();
        $('#select-gauge').select2();
        $('#select-category').select2();
        $('#select-profile').select2();
        $('#select-type').select2();

        $(document).on('input change', '#text-srh, #select-grade, #select-gauge, #select-category, #select-profile, #select-type, #toggleActive', function() {
            performSearch($('#text-srh').val());
        });

        performSearch('');
    });
</script>
