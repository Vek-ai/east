<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

$trim_id = 4;
$panel_id = 3;

$points_details = getSetting('points');
$data = json_decode($points_details, true);
$points_order_total = isset($data['order_total']) ? $data['order_total'] : 0;
$points_gained = isset($data['points_gained']) ? $data['points_gained'] : 0;

$points_ratio = getPointsRatio();

$is_points_enabled = getSetting('is_points_enabled');

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
            $charge_net_30 = floatval($customer_details['charge_net_30']);
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
            </div>
            <div class="col-6">
                <div>
                    <span class="fw-bold">Charge Net 30:</span><br>
                    <span class="text-primary fs-4 fw-bold pl-3">$<?= number_format($charge_net_30,2) ?></span>
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

        <?php } ?>
    </div>
    <input type='hidden' id='customer_id_cash' name="customer_id"/>

    <?php 
    $total = 0;
    $total_customer_price = 0;
    $totalquantity = 0;
    $timestamp = time();
    $no = $timestamp . 1;
    $total_weight = 0;
    $cart = getCartDataByCustomerId($customer_id);
    if (!empty($cart)) {
        foreach ($cart as $keys => $values) {
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

            $estimate_length = isset($values["estimate_length"]) && is_numeric($values["estimate_length"]) ? floatval($values["estimate_length"]) : 1;
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

            $total_weight += $product["weight"] * $values["quantity_cart"];
            $no++;
        }
    }
    $_SESSION["total_quantity"] = $totalquantity;
    $_SESSION["grandtotal"] = $total;
    $total_customer_price = floatval(str_replace(',', '', $total_customer_price));
    ?>

    <div class="card-body datatables">
        <form id="msform">
            <input type="hidden" id="order_payable_amt" value="<?= $total_customer_price ?>">
            <input type="hidden" id="delivery_amt" name="delivery_amt" value="0">
            <input type="hidden" id="store_credit" name="store_credit" value="<?= $store_credit ?>">
            <input type="hidden" id="points_ratio" name="points_ratio" value="<?= $points_ratio ?>">
            <input type="hidden" id="charge_net_30" value="<?= $charge_net_30 ?>">
            <input type="hidden" id="customer_tax_hidden" value="<?= $tax ?>">

            <div class="row text-start">
                <div class="col-12 mb-2" style="color: #ffffff !important;">
                    <h5 class="mb-1">Checkout</h5>
                    <p>Welcome, <strong><?= $customer_name ?></strong></p>
                </div>
                <!-- Left Side -->
                <div class="col-lg-8" style="color: #ffffff !important;">
                

                <!-- Contact Information -->
                <div class="card mb-3" style="color: #ffffff !important;">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <span><i class="fa fa-check-circle text-success me-2"></i>Contact Information</span>
                        <a href="#" id="toggle_edit_info" class="text-primary">Edit Info</a>
                    </div>

                    <div class="card-body">
                        <div id="display_contact_info">
                            <p class="mb-1" id="disp_email"><?= $customer_details['contact_email'] ?></p>
                            <p class="mb-2" id="disp_phone"><?= $customer_details['contact_phone'] ?></p>
                            <h6 class="fs-2">By providing the phone number above, you consent to receive automated text messages...</h6>
                        </div>

                        <div id="edit_contact_info" class="d-none">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="order_deliver_fname" class="form-label">First Name</label>
                                    <input type="text" id="order_deliver_fname" class="form-control" value="<?= $customer_details['customer_first_name'] ?>" placeholder="First Name">
                                </div>
                                <div class="col-md-3">
                                    <label for="order_deliver_lname" class="form-label">Last Name</label>
                                    <input type="text" id="order_deliver_lname" class="form-control" value="<?= $customer_details['customer_last_name'] ?>" placeholder="Last Name">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="order_deliver_phone" class="form-label">Contact Phone</label>
                                    <input type="text" id="order_deliver_phone" class="form-control" value="<?= $customer_details['contact_phone'] ?>" placeholder="Contact Phone">
                                </div>
                                <div class="col-md-3">
                                    <label for="order_deliver_email" class="form-label">Contact Email</label>
                                    <input type="text" id="order_deliver_email" class="form-control" value="<?= $customer_details['contact_email'] ?>" placeholder="Contact Email">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Job Details -->
                <div class="card mb-3" style="color: #ffffff !important;">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <span><i class="fa fa-check-circle text-success me-2"></i>Job Details</span>
                    </div>
                    <div class="card-body">
                        <div class="col-md-4 mb-3">
                            <h6 class="mb-0">Job Name</h6>
                            <div id="order_checkout">
                                <select id="order_job_name" class="form-control" name="order_job_name">
                                    <option value="">Select Job Name...</option>
                                    <?php
                                    $query_job_name = "SELECT * FROM jobs WHERE customer_id = '$customer_id'";
                                    $result_job_name = mysqli_query($conn, $query_job_name);
                                    while ($row_job_name = mysqli_fetch_array($result_job_name)) {
                                        $job_id = $row_job_name['job_id'];
                                    ?>
                                        <option value="<?= $row_job_name['job_name']; ?>" 
                                                data-constructor="<?= htmlspecialchars($row_job_name['constructor_name']); ?>" 
                                                data-constructor-contact="<?= htmlspecialchars($row_job_name['constructor_contact']); ?>"
                                                data-credit="<?= htmlspecialchars(getJobBalance($job_id)); ?>"
                                                data-job-id="<?= $job_id ?>"
                                                >
                                            <?= htmlspecialchars($row_job_name['job_name']); ?>
                                        </option>
                                    <?php } ?>
                                    <option value="add_new_job_name">Add new Job Name</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <h6 class="mb-0">Job PO #</h6>
                            <input type="text" id="order_job_po" name="order_job_po" class="form-control" placeholder="Enter Job PO #">
                        </div>

                        <div class="col-md-4">
                            <div class="mb-2 position-relative">
                                <strong>Contractor Name:</strong>
                                <input type="text" class="form-control" id="constructor_name" autocomplete="off">
                                <div class="border bg-white shadow-sm position-absolute w-100 d-none" id="contractor_dropdown" style="z-index: 10; max-height: 200px; overflow-y: auto;">
                                    <?php
                                    $query_job_name = "SELECT DISTINCT constructor_name, constructor_contact FROM jobs WHERE customer_id = '$customer_id'";
                                    $result_job_name = mysqli_query($conn, $query_job_name);
                                    $contractors = [];
                                    while ($row_job_name = mysqli_fetch_array($result_job_name)) {
                                        $name = htmlspecialchars($row_job_name['constructor_name']);
                                        $contact = htmlspecialchars($row_job_name['constructor_contact']);
                                        echo "<div class='dropdown-item contractor-item' data-name='{$name}' data-contact='{$contact}' style='cursor: pointer; line-height: 38px; padding-top: 0px; padding-bottom: 0px;'>{$name}</div>";
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <strong>Contractor Cell Phone:</strong>
                                <input type="text" class="form-control" id="constructor_contact">
                            </div>
                        </div>

                        
                        <div class="col-md-8 mb-3 d-none align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="pay_via_job_deposit" name="pay_via_job_deposit" value="1">
                                <label class="form-check-label" for="pay_via_job_deposit">
                                    Pay via Job Deposit 
                                    <span id="job_credit_balance" class="text-success fw-semibold ms-1"></span>
                                </label>
                            </div>
                        </div>

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
                                <input class="form-check-input" type="radio" name="order_delivery_method" id="deliver_option" value="deliver">
                                <label class="form-check-label" for="delivery_option">Delivery</label>
                            </div>

                            <div class="mb-3">
                                <small>
                                    We'll email you when your order is ready.
                                </small>
                            </div>

                            <div id="truck_div" class="col-md-3 mb-3 d-none">
                                <label for="truck" class="form-label mb-0">Truck</label>
                                <div class="mb-2">
                                    <?php
                                        $query = "
                                            SELECT id, truck_name, max_limit
                                            FROM trucks
                                            WHERE max_limit >= $total_weight
                                            ORDER BY max_limit ASC
                                            LIMIT 1
                                        ";
                                        $result = mysqli_query($conn, $query);

                                        if ($row = mysqli_fetch_assoc($result)) {
                                            $id = $row['id'];
                                            $name = htmlspecialchars($row['truck_name']);
                                            echo "
                                                <select class='form-control-plaintext p-0 ms-2 bg-transparent' id='truck' name='truck_id' disabled>
                                                    <option value='$id' selected>$name</option>
                                                </select>
                                            ";
                                        } else {
                                            echo "
                                                <div class='text-danger small'>
                                                    No suitable truck found.
                                                    <a href='/?page=truck' class='text-decoration-underline'>Add one here</a>.
                                                </div>
                                            ";
                                        }
                                        ?>
                                </div>
                            </div>

                            <div class="mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="ship_separate_address">
                                    <label class="form-check-label" for="ship_separate_address">
                                        Ship to a separate address?
                                    </label>
                                </div>
                            </div>

                            <!-- Hidden by default -->
                            <div id="separate_address_section" class="d-none">
                                <div class="col-12 mb-3">
                                    <button class="btn btn-sm ripple btn-primary mt-1" type="button" id="openMap">
                                        <i class="fa fa-map"></i> Get Directions
                                    </button>
                                </div>

                                <div class="col-12">
                                    <label>Address:</label>
                                    <div class="row mb-3">
                                        <div class="col-sm-2">
                                            <input type="text" id="order_deliver_address" name="order_deliver_address" value="<?= $customer_details['address'] ?>" class="form-control" placeholder="Address">
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" id="order_deliver_city" name="order_deliver_city" value="<?= $customer_details['city'] ?>" class="form-control" placeholder="City">
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" id="order_deliver_state" name="order_deliver_state" value="<?= $customer_details['state'] ?>" class="form-control" placeholder="State">
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" id="order_deliver_zip" name="order_deliver_zip" value="<?= $customer_details['zip'] ?>" class="form-control" placeholder="Zip">
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" id="lat" name="lat" class="form-control" value="<?= $lat ?>" />
                                <input type="hidden" id="lng" name="lng" class="form-control" value="<?= $lng ?>" />

                                <div class="col-8">
                                    <label for="delivery_driver_instructions" class="form-label">Delivery Driver Instructions</label>
                                    <input type="text" id="delivery_driver_instructions" name="delivery_driver_instructions" class="form-control" placeholder="Instructions for the driver...">
                                </div>
                            </div>



                        </div>
                    </div>
                </div>

                <!-- Payment -->
                <div class="card mb-3" style="color: #ffffff !important;">
                    <div class="card-header bg-white">
                    <i class="fa fa-check-circle text-success me-2"></i>Pickup Details Payment
                    </div>
                    <div class="card-body">
                    
                    <div class="mb-3 text-white">
                        <div id="paymentOptions">
                            <label class="form-label fw-bold">Select Payment Method</label><br>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="payMethod" id="payPickup" value="pickup">
                                <label class="form-check-label" for="payPickup">
                                <i class="fa-solid fa-store me-1"></i>Pay at Pick-Up
                                </label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="payMethod" id="payDelivery" value="delivery">
                                <label class="form-check-label" for="payDelivery">
                                <i class="fa-solid fa-truck me-1"></i>Pay at Delivery
                                </label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="payMethod" id="payCard" value="card">
                                <label class="form-check-label" for="payCard">
                                <i class="fa-brands fa-cc-visa me-1"></i>Credit/Debit Card
                                </label>
                            </div>

                            <?php if (!empty($customer_details['charge_net_30'])): ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="payMethod" id="payNet30" value="net30">
                                    <label class="form-check-label" for="payNet30">
                                        <i class="fa-solid fa-calendar-check me-1"></i>Charge Net 30
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (floatval($customer_details['store_credit']) > 0): ?>
                        <div class="mb-3 text-white">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="applyStoreCredit" name="applyStoreCredit" value="1" >
                                <label class="form-check-label text-white" for="applyStoreCredit">
                                Apply Store Credit (Available: $<?= number_format(floatval($customer_details['store_credit']), 2) ?>)
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
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
                    <div class="d-flex justify-content-between align-items-center flex-column flex-sm-row mb-2">
                        <span>Item Subtotal (<?= $_SESSION["total_quantity"] ?? '0' ?>)</span>
                        <div class="d-flex flex-column align-items-end">
                            <span>$<?= number_format($total_customer_price,2) ?></span>
                            <div style="width: 100px; height: 2px; background-color: white; margin-top: 2px;"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pb-2">
                        <span>Estimated Tax</span>
                        <span>$<?= number_format((floatval($total_customer_price)) * $tax, 2) ?></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pb-2">
                        <span>Delivery</span>
                        <span>$<span id="order_delivery_amt"><?= number_format(0, 2) ?></span></span>
                    </div>

                    
                    <hr>
                    <div class="d-flex justify-content-between text-success mb-2">
                        <span>Savings</span>
                        <span>$<?= number_format(floatval($total) * floatval($discount)) ?></span>
                    </div>
                    <div id="storeCreditDisplay" class="d-flex justify-content-between mb-2 d-none text-success">
                        <span>Store Credit:</span>
                        <span class="fw-bold" id="storeCreditValue"></span>
                    </div>
                    <div id="jobDepositDisplay" class="d-flex justify-content-between mb-2 d-none text-success">
                        <span>Job Deposit:</span>
                        <span class="fw-bold" id="jobDepositValue"></span>
                    </div>
                    <div id="net30Display" class="d-flex justify-content-between mb-2 d-none text-success">
                        <span>Charge Net 30:</span>
                        <span class="fw-bold" id="net30Value"></span>
                    </div>

                    <div class="d-flex justify-content-end mb-3">
                        <div style="width: 100px; height: 2px; background-color: white;"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <strong>Estimated Total</strong>
                        <p><strong id="order_total"></strong></p>
                    </div>
                    <button class="btn btn-success w-100 mt-3" id="save_order">Place Order</button>
                    <p class="mt-2 text-center small">
                        By placing an order, I agree to EKM's <a href="#">Terms</a> and <a href="#">Privacy Statement</a>.
                    </p>
                    </div>
                    <?php if($is_points_enabled == '1'){
                    ?>
                        <div class="card-footer bg-white d-flex justify-content-between">
                            <span><i class="fa fa-gift me-1"></i>Estimated Points</span>
                            <span><span id="estimated_points" class="badge bg-primary">0</span></span>
                        </div>
                    <?php
                    }
                    ?>
                </div>
                </div>
            </div>
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

            $(document).on('change', '#ship_separate_address', function () {
                if ($(this).is(':checked')) {
                    $('#separate_address_section').removeClass('d-none');
                } else {
                    $('#separate_address_section').addClass('d-none');
                    $('#separate_address_section input').val('');
                }
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

            var originalData = {
                fname: $('#order_deliver_fname').val(),
                lname: $('#order_deliver_lname').val(),
                phone: $('#order_deliver_phone').val(),
                email: $('#order_deliver_email').val()
            };

            $('#toggle_edit_info').on('click', function (e) {
                e.preventDefault();

                if ($(this).text() === 'Edit Info') {
                    $('#edit_contact_info').removeClass('d-none');
                    $('#display_contact_info').addClass('d-none');
                    $(this).text('Cancel');
                } else {
                    $('#order_deliver_fname').val(originalData.fname);
                    $('#order_deliver_lname').val(originalData.lname);
                    $('#order_deliver_phone').val(originalData.phone);
                    $('#order_deliver_email').val(originalData.email);

                    $('#edit_contact_info').addClass('d-none');
                    $('#display_contact_info').removeClass('d-none');
                    $(this).text('Edit Info');
                }
            });

            $('#order_job_name').on('select2:select', function (e) {
                const selectedValue = e.params.data.id;

                if (selectedValue === 'add_new_job_name') {
                    $('#prompt_job_name_modal').modal('show');
                    $('#order_job_name').val(null).trigger('change');
                    $('#constructor_name').val('');
                    $('#constructor_contact').val('');
                    $('#pay_via_job_deposit').prop('checked', false);
                    $('#pay_via_job_deposit').closest('.col-md-8').addClass('d-none');
                    $('#job_credit_balance').text('');
                } else {
                    const selectedOption = $(this).find('option[value="' + selectedValue + '"]');
                    const constructorName = selectedOption.data('constructor') || '';
                    const constructorContact = selectedOption.data('constructor-contact') || '';
                    const jobBalance = parseFloat(selectedOption.data('credit')) || 0;

                    $('#constructor_name').val(constructorName);
                    $('#constructor_contact').val(constructorContact);

                    if (jobBalance > 0) {
                        $('#pay_via_job_deposit').closest('.col-md-8').removeClass('d-none');
                        $('#job_credit_balance').text(`(Credit Available: $${jobBalance.toFixed(2)})`);
                    } else {
                        $('#pay_via_job_deposit').prop('checked', false);
                        $('#pay_via_job_deposit').closest('.col-md-8').addClass('d-none');
                        $('#job_credit_balance').text('');
                    }

                    $('#pay_via_job_deposit').data('deposit', jobBalance);
                }
            });

            $('#constructor_name').on('focus input', function () {
                var filter = $(this).val().toLowerCase();
                var hasMatch = false;

                $('#contractor_dropdown .contractor-item').each(function () {
                    var name = $(this).data('name').toLowerCase();
                    var isMatch = name.includes(filter);
                    $(this).toggle(isMatch);
                    if (isMatch) hasMatch = true;
                });

                if (hasMatch) {
                    $('#contractor_dropdown').removeClass('d-none');
                } else {
                    $('#contractor_dropdown').addClass('d-none');
                }
            });

            $(document).on('click', '.contractor-item', function () {
                var name = $(this).data('name');
                var contact = $(this).data('contact');

                $('#constructor_name').val(name);
                $('#constructor_contact').val(contact);
                $('#contractor_dropdown').addClass('d-none');
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('#constructor_name, #contractor_dropdown').length) {
                    $('#contractor_dropdown').addClass('d-none');
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
                            $(this).removeAttr('disabled').show();
                        } else {
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