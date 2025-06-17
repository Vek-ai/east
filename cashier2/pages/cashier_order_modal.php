<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

$trim_id = 4;
$panel_id = 3;

if(isset($_POST['fetch_order'])){
    $discount = 0;
    $tax = 0;
    $customer_details_pricing = 0;
    if(isset($_SESSION['customer_id'])){
        $customer_id = $_SESSION['customer_id'];
        $customer_details = getCustomerDetails($customer_id);
        $fullAddress = trim(implode(', ', array_filter([
            $customer_details['address'] ?? null,
            $customer_details['city'] ?? null,
            $customer_details['state'] ?? null,
            $customer_details['zip'] ?? null,
        ])));
        $fname = $customer_details['customer_first_name'];
        $lname = $customer_details['customer_last_name'];
        $discount = floatval(getCustomerDiscount($customer_id)) / 100;
        $tax = floatval(getCustomerTax($customer_id)) / 100;
        $customer_details_pricing = $customer_details['customer_pricing'];
    }
    $delivery_price = getDeliveryCost();
    ?>
    <style>
        .high-zindex-select2 + .select2-container--open {
            z-index: 1055 !important;
        }

        .table-fixed {
            table-layout: fixed;
            width: 100%;
        }

        .table-fixed th,
        .table-fixed td {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
            word-wrap: break-word;
        }

        .table-fixed th:nth-child(1),
        .table-fixed td:nth-child(1) { width: 5%; }
        .table-fixed th:nth-child(2),
        .table-fixed td:nth-child(2) { width: 15%; }
        .table-fixed th:nth-child(3),
        .table-fixed td:nth-child(3) { width: 7%; }
        .table-fixed th:nth-child(4),
        .table-fixed td:nth-child(4) { width: 8%; }
        .table-fixed th:nth-child(5),
        .table-fixed td:nth-child(5) { width: 8%; }
        .table-fixed th:nth-child(6),
        .table-fixed td:nth-child(6) { width: 11%; }
        .table-fixed th:nth-child(7),
        .table-fixed td:nth-child(7) { width: 11%; }
        .table-fixed th:nth-child(8),
        .table-fixed td:nth-child(8) { width: 11%; }
        .table-fixed th:nth-child(9),
        .table-fixed td:nth-child(9) { width: 7%; }
        .table-fixed th:nth-child(10),
        .table-fixed td:nth-child(10) { width: 7%; }
        .table-fixed th:nth-child(11),
        .table-fixed td:nth-child(11) { width: 7%; }
        .table-fixed th:nth-child(12),
        .table-fixed td:nth-child(12) { width: 3%; }

        .table-fixed tbody tr:hover input[readonly] {
            background-color: transparent;
        }

        #msform {
            text-align: center;
            position: relative;
            margin-top: 30px;
            
        }

        #msform fieldset {
            border: 0 none;
            border-radius: 0px;
            padding: 20px 30px;
            box-sizing: border-box;

            position: relative;
        }

        #msform fieldset:not(:first-of-type) {
            display: none;
        }

        .select2-container--default .select2-results__option[aria-disabled=true] { 
            display: none;
        }
    </style>
    <div id="customer_cash_section">
        <?php 
        if(!empty($_SESSION["customer_id"])){
            $customer_id = $_SESSION["customer_id"];
            $customer_details = getCustomerDetails($customer_id);
            $credit_limit = number_format(floatval($customer_details['credit_limit'] ?? 0), 2);
            $credit_total = number_format(getCustomerCreditTotal($customer_id),2);
            $lat = !empty($customer_details['lat']) ? $customer_details['lat'] : 0;
            $lng = !empty($customer_details['lng']) ? $customer_details['lng'] : 0;

            $addressDetails = implode(', ', [
                $customer_details['address'] ?? '',
                $customer_details['city'] ?? '',
                $customer_details['state'] ?? '',
                $customer_details['zip'] ?? ''
            ]);

            $store_credit = number_format(floatval($customer_details['store_credit'] ?? 0),2);

            $customer_name = get_customer_name($_SESSION["customer_id"]);
        ?>
        <div class="form-group row align-items-center" style="color: #ffffff !important;">
            <div class="col-6">
                <label>Customer Name: <?= $customer_name ?></label>
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
                                <button class="btn btn-sm ripple btn-primary mt-1" type="button" id="cancel_change_address_order" >
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
                    <span class="text-primary fs-4 fw-bold pl-3">$<?= $credit_limit ?></span>
                </div>
                <div>
                    <span class="fw-bold">Unpaid Credit:</span><br>
                    <span class="text-primary fs-4 fw-bold pl-3">$<?= $credit_total ?></span>
                </div>
                <div>
                    <span class="fw-bold">Store Credit:</span><br>
                    <span class="text-primary fs-4 fw-bold pl-3">$<?= $store_credit ?></span>
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

    <?php 
    $total = 0;
    $total_customer_price = 0;
    $totalquantity = 0;
    $timestamp = time();
    $no = $timestamp . 1;
    if (!empty($_SESSION["cart"])) {
        foreach ($_SESSION["cart"] as $keys => $values) {
            $data_id = $values["product_id"];
            $product = getProductDetails($data_id);
            $totalstockquantity = $values["quantity_ttl"] + $values["quantity_in_stock"];
            $category_id = $product["product_category"];
            if ($totalstockquantity > 0) {
                $stock_text = '
                    <a href="javascript:void(0);" id="view_in_stock" data-id="' . htmlspecialchars($data_id, ENT_QUOTES, 'UTF-8') . '" class="d-flex align-items-center">
                        <span class="text-bg-success p-1 rounded-circle"></span>
                        <span class="ms-2">In Stock</span>
                    </a>';
            } else {
                $stock_text = '
                    <a href="javascript:void(0);" id="view_out_of_stock" data-id="' . htmlspecialchars($data_id, ENT_QUOTES, 'UTF-8') . '" class="d-flex align-items-center">
                        <span class="text-bg-danger p-1 rounded-circle"></span>
                        <span class="ms-2 fs-3">Out of Stock</span>
                    </a>';
            } 

            $default_image = '../images/product/product.jpg';

            $picture_path = !empty($product['main_image'])
            ? "../" .$product['main_image']
            : $default_image;

            $images_directory = "../images/drawing/";

            $customer_pricing = getPricingCategory($category_id, $customer_details_pricing) / 100;

            $estimate_length = isset($values["estimate_length"]) && is_numeric($values["estimate_length"]) ? floatval($values["estimate_length"]) : 0;
            $estimate_length_inch = isset($values["estimate_length_inch"]) && is_numeric($values["estimate_length_inch"]) ? floatval($values["estimate_length_inch"]) : 0;

            $total_length = $estimate_length + ($estimate_length_inch / 12);
            $amount_discount = isset($values["amount_discount"]) && is_numeric($values["amount_discount"]) ? floatval($values["amount_discount"]) : 0;

            $quantity = isset($values["quantity_cart"]) && is_numeric($values["quantity_cart"]) ? floatval($values["quantity_cart"]) : 0;
            $unit_price = isset($values["unit_price"]) && is_numeric($values["unit_price"]) ? floatval($values["unit_price"]) : 0;

            $product_price = ($quantity * $unit_price * $total_length) - $amount_discount;

            if (!empty($values["is_custom"]) && $values["is_custom"] == 1) {
                $custom_multiplier = floatval(getCustomMultiplier($category_id));
                $product_price += $product_price * $custom_multiplier;
            }

            $color_id = $values["custom_color"];
            if (isset($values["used_discount"])){
                $discount = isset($values["used_discount"]) ? floatval($values["used_discount"]) / 100 : 0;
            }

            $sold_by_feet = $product['sold_by_feet'];

            $subtotal = $product_price;
            $customer_price = $product_price * (1 - $discount) * (1 - $customer_pricing);
        
            $totalquantity += $values["quantity_cart"];
            $total += $subtotal;
            $total_customer_price += $customer_price;
            $total_tax = number_format((floatval($total_customer_price)) * $tax, 2);

            $total_weight += $values["weight"] * $values["quantity_cart"];
            $no++;
        }
    }
    $_SESSION["total_quantity"] = $totalquantity;
    $_SESSION["grandtotal"] = $total;
    ?>

    <div class="card-body datatables">
        <form id="msform">
            <fieldset class="order-page-1">
                <div id="checkout" class="row mt-3 align-items-stretch">
                    <div class="col-md-4 d-flex flex-column h-100 fs-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h5 class="mb-0 -5">Job Name</h5>
                                <div id="order_checkout">
                                    <select id="order_job_name" class="form-control" name="order_job_name">
                                        <option value="">Select Job Name...</option>
                                        <?php
                                        $query_job_name = "SELECT * FROM job_names WHERE customer_id = '$customer_id'";
                                        $result_job_name = mysqli_query($conn, $query_job_name);
                                        while ($row_job_name = mysqli_fetch_array($result_job_name)) {
                                        ?>
                                            <option value="<?= $row_job_name['job_name']; ?>"><?= $row_job_name['job_name']; ?></option>
                                        <?php } ?>
                                        <option value="add_new_job_name">Add new Job Name</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <h5 class="mb-0 fs-4">Job PO #</h5>
                                <input type="text" id="order_job_po" name="order_job_po" class="form-control fs-4" placeholder="Enter Job PO #">
                            </div>
                        </div>

                        <div class="card flex-grow-1 d-flex flex-column">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <form>
                                    <div class="mb-3">
                                        <h5 class="fs-4">Total Items: <span id="total_items"><?= $_SESSION["total_quantity"] ?? '0' ?></span></h5>
                                    </div>

                                    <div class="mb-3">
                                        <h5 class="fs-4">Discount (%)</h5>
                                        <input type="text" class="form-control discount_input fs-4" id="order_discount" placeholder="%" value="<?= $discount * 100 ?>">
                                    </div>

                                    <div>
                                        <h5 class="fs-4">Cash Amount</h5>
                                        <input type="number" class="form-control fs-4" id="order_cash" value="<?= round($total_customer_price, 2) ?>">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4"></div>

                    <?php
                        
                    ?>

                    <div class="col-md-4">
                        <div class="card flex-grow-1">
                            <div class="card-body pricing container d-flex flex-column justify-content-center">
                                <div class="table-responsive fs-4">
                                    <h4 class="text-center fw-bold mb-3">Order Summary</h4>
                                    <table class="table table-md">
                                        <tbody>
                                            <tr>
                                                <th class="text-right border-bottom">Subtotal</th>
                                                <td class="text-right border-bottom">
                                                    $<span id="total_amt"><?= number_format($total, 2) ?></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="text-right border-bottom">Sales Tax</th>
                                                <td class="text-right border-bottom">$<span id="sales_tax"><?= number_format($total_tax,2) ?></span></td>
                                            </tr>
                                            <tr>
                                                <th class="text-right border-bottom">Discount(-)</th>
                                                <td class="text-right border-bottom">
                                                    $<span id="total_discount"><?= number_format(floatval($total) * floatval($discount), 2) ?></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="text-right border-bottom">Total Price</th>
                                                <td class="text-right border-bottom">
                                                    $<span id="total_payable"><?= number_format((floatval($total_customer_price)), 2) ?></span>
                                                </td>
                                                <input type="hidden" id="payable_amt" value="<?= number_format((floatval($total_customer_price)), 2) ?>">
                                                <input type="hidden" id="delivery_amt" name="delivery_amt" value="0" class="text-right form-control">
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </fieldset>
            <fieldset class="order-page-2" style="display: none;">
                <div class="row">
                    <!-- Left Side -->
                    <div class="col-lg-8" style="color: #ffffff !important;">
                    <h5 class="mb-3">Checkout</h5>
                    <p>Welcome, <strong><?= $customer_name ?></strong></p>

                    <!-- Contact Information -->
                    <div class="card mb-3" style="color: #ffffff !important;">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <span><i class="fa fa-check-circle text-success me-2"></i>Contact Information</span>
                        <a href="#" class="text-primary">Edit Info</a>
                        </div>
                        <div class="card-body">
                        <p class="mb-1"><?= $customer_details['contact_email'] ?></p>
                        <p class="mb-2"><?= $customer_details['contact_phone'] ?></p>
                        <h6 class="fs-2">By providing the phone number above, you consent to receive automated text messages...</h6>
                        </div>
                    </div>

                    <!-- Pickup Details -->
                    <div class="card mb-3" style="color: #ffffff !important;">
                        <div class="card-header bg-white">
                        <i class="fa fa-check-circle text-success me-2"></i>Pickup Details
                        </div>
                        <div class="card-body">
                        <h6 class="mb-1"><?= $customer_details['address'] ?></h6>
                        <p class="mb-1"><?= getCustomerAddress($_SESSION["customer_id"]) ?> <a href="#" class="ms-2">(606) 330-1440</a></p>
                            <div class="mb-3">
                                <label class="form-label">How would you like to pick up your order?</label>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="order_delivery_method" id="pickup_option" value="pickup" checked>
                                    <label class="form-check-label" for="pickup_option">Pickup</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="order_delivery_method" id="openMap" value="deliver">
                                    <label class="form-check-label" for="delivery_option">Delivery</label>
                                </div>

                                <small>
                                    We'll email you when your order is ready.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div class="card mb-3" style="color: #ffffff !important;">
                        <div class="card-header bg-white">
                        <i class="fa fa-check-circle text-success me-2"></i>Pickup Details Payment
                        </div>
                        <div class="card-body">
                        
                        <div class="mb-3" style="color: #ffffff !important;">
                            <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="payMethod" checked>
                            <label class="form-check-label"><i class="fa-brands fa-cc-visa me-1"></i>Credit/Debit Card</label>
                            </div>
                            <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="payMethod">
                            <label class="form-check-label"><i class="fa-brands fa-apple-pay me-1"></i>Apple Pay</label>
                            </div>
                            <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="payMethod">
                            <label class="form-check-label"><i class="fa-brands fa-paypal me-1"></i>PayPal</label>
                            </div>
                        </div>

                        </div>
                    </div>
                    </div>

                    <!-- Right Side (Order Summary) -->
                    <div class="col-lg-4">
                    <div class="card" style="color: #ffffff !important;">
                        <div class="card-header bg-white">
                        <strong>Order Summary</strong>
                        </div>
                        <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span>Item Subtotal (<?= $_SESSION["total_quantity"] ?? '0' ?>)</span>
                            <span>$<?= $total_customer_price ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Delivery</span>
                            <p>$<span id="order_delivery_amt"><?= number_format(0, 2) ?></span></p>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Estimated Tax</span>
                            <span>$<?= number_format((floatval($total_customer_price)) * $tax, 2) ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between text-success mb-3">
                            <span>Savings</span>
                            <span>$<?= number_format(floatval($total) * floatval($discount)) ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <strong>Estimated Total</strong>
                            <p>$<strong id="order_total"><?= number_format((floatval($total_customer_price)), 2) ?></strong></p>
                        </div>
                        <button class="btn btn-success w-100 mt-3" id="save_order">Place Order</button>
                        <p class="mt-2 text-center small">
                            By placing an order, I agree to EKM's <a href="#">Terms</a> and <a href="#">Privacy Statement</a>.
                        </p>
                        </div>
                        <div class="card-footer bg-white d-flex justify-content-between">
                        <span><i class="fa fa-gift me-1"></i>Estimated Points</span>
                        <span><span class="badge bg-primary">+0</span></span>
                        </div>
                    </div>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>

    <script>
        function init_select_cash(){
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
        }
        $(document).ready(function() {
            init_select_cash();

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
                            loadOrderContents();
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
                });
            });

            $(document).on('click', '#customer_change_cash', function(event) {
                $.ajax({
                    url: 'pages/cashier_ajax.php',
                    type: 'POST',
                    data: {
                        unset_customer: "unset_customer"
                    },
                    success: function(response) {
                        loadOrderContents();
                        $('#next_page_order').removeClass("d-none");
                        $('#prev_page_order').addClass("d-none");
                        $('#save_order').addClass("d-none");
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
                });
            });
            
            $(document).on('click', '#cancel_change_address_order', function(event) {
                loadOrderContents();
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

            $(document).on('change', '#delivery_amt', function() {
                var product_cost = parseFloat($('#total_amt').text()) || 0;
                var delivery_cost = parseFloat($(this).val()) || 0;
                var total_payable = product_cost + delivery_cost;
                $('#total_payable').text(total_payable.toFixed(2));
                $('#order_cash').val(total_payable.toFixed(2));
            });

            let animating = false;

            function changePage(currentPage, nextPage, isNext) {
                if (animating) return false;
                animating = true;

                var current_fs = $(currentPage);
                var next_fs = $(nextPage);

                if (isNext) {
                    console.log(isNext);
                    $('#next_page_order').addClass("d-none");
                    $('#prev_page_order').removeClass("d-none");
                    $('#save_order').removeClass("d-none");
                } else {
                    $('#next_page_order').removeClass("d-none");
                    $('#prev_page_order').addClass("d-none");
                    $('#save_order').addClass("d-none");
                }

                next_fs.show();
                current_fs.hide();

                animating = false;
            }

            $(document).on("click", "#next_page_order", function() {
                changePage('.order-page-1', '.order-page-2', true);
                $('#save_estimate').addClass("d-none");
            });

            $(document).on("click", "#prev_page_order", function() {
                changePage('.order-page-2', '.order-page-1', false);
                $('#save_estimate').removeClass("d-none");
            });

            $('#order_job_name').select2({
                width: '100%',
                placeholder: "Select Job Name...",
                dropdownAutoWidth: true,
                dropdownParent: $('#order_checkout'),
                templateResult: function (data) {
                    if (data.id === 'add_new_job_name') {
                        return $(
                            '<div style="border-top: 1px solid #ddd; margin-top: 0px; padding-top: 10px;">' +
                            '<span style="font-style: italic; color: #ff6b6b;">' + data.text + '</span>' +
                            '</div>'
                        );
                    }
                    return data.text;
                },
                templateSelection: function (data) {
                    return data.text;
                },
                matcher: function (params, data) {
                    if (data.id === 'add_new_job_name') {
                        return data;
                    }
                    return $.fn.select2.defaults.defaults.matcher(params, data);
                }
            });

            $('#order_job_name').on('select2:select', function (e) {
                const selectedValue = e.params.data.id;
                if (selectedValue === 'add_new_job_name') {
                    $('#prompt_job_name_modal').modal('show');
                    $('#order_job_name').val(null).trigger('change');
                }
            });

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

            $(".grade-order").each(function() {
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

            $(document).on('change', '.grade-order', function () {
                const selectedGrade = $(this).val();
                const no = $(this).attr('id').replace('grade', '');
                const colorSelect = $(`#color_order${no}`);

                if (colorSelect.length) {
                    colorSelect.val(null).trigger('change');

                    colorSelect.find('option').each(function () {
                        const grade = String($(this).data('grade'));
                        if (!selectedGrade || grade === String(selectedGrade)) {
                            console.log('show')
                            $(this).removeAttr('disabled').show();
                        } else {
                            console.log('hide')
                            $(this).attr('disabled', 'disabled').hide();
                        }
                    });

                    colorSelect.select2('destroy').select2({
                        width: '300px',
                        placeholder: "Select...",
                        dropdownAutoWidth: true,
                        dropdownParent: $('#orderTable'),
                        templateResult: formatOption,
                        templateSelection: formatSelected
                    });
                }
            });
        });
    </script>

    <?php
}