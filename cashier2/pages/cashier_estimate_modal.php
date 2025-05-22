<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

$trim_id = 4;
$panel_id = 3;

if(isset($_POST['fetch_estimate'])){
    $discount = 0;
    $tax = 0;
    $customer_details_pricing = 0;
    if(isset($_SESSION['customer_id'])){
        $customer_id = $_SESSION['customer_id'];
        $customer_details = getCustomerDetails($customer_id);
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
        <div id="customer_est_section">
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
            ?>
            <div class="form-group row align-items-center">
                <div class="col-6">
                    <label>Customer Name: <?= get_customer_name($_SESSION["customer_id"]); ?></label>
                    <button class="btn btn-sm ripple btn-primary mt-1" type="button" id="customer_change_est">
                        <i class="fe fe-reload"></i> Change
                    </button>
                    <div class="mt-1"> 
                        <div id="defaultDeliverDetailsEst">
                            <span class="fw-bold">Address: <?= getCustomerAddress($_SESSION["customer_id"]) ?></span>
                            <button class="btn btn-sm ripple btn-primary mt-1" type="button" id="address_change_est">
                                <i class="fe fe-reload"></i> Change
                            </button>
                        </div>
                        <div class="mt-1">
                            <div id="deliverDetailsEst" class="row d-none">
                                <div class="col-12">
                                    <label>Recipient:</label>
                                    <div class="row mb-3">
                                        <div class="col-sm-6">
                                            <input type="text" id="est_deliver_fname" name="est_deliver_fname" value="<?= $customer_details['customer_first_name'] ?>" class="form-control diffNameInput" placeholder="First Name">
                                        </div>
                                        <div class="col-sm-6">
                                            <input type="text" id="est_deliver_lname" name="est_deliver_lname" value="<?= $customer_details['customer_last_name'] ?>" class="form-control diffNameInput" placeholder="Last Name">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label>Address:</label>
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <input type="text" id="est_deliver_address" name="est_deliver_address" value="<?= $customer_details['address'] ?>" class="form-control" placeholder="Address">
                                        </div>
                                        <div class="col-sm-3">
                                            <input type="text" id="est_deliver_city" name="est_deliver_city" value="<?= $customer_details['city'] ?>" class="form-control" placeholder="City">
                                        </div>
                                        <div class="col-sm-3">
                                            <input type="text" id="est_deliver_state" name="est_deliver_state" value="<?= $customer_details['state'] ?>" class="form-control" placeholder="State">
                                        </div>
                                        <div class="col-sm-3">
                                            <input type="text" id="est_deliver_zip" name="est_deliver_zip" value="<?= $customer_details['zip'] ?>" class="form-control" placeholder="Zip">
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" id="est_lat" name="lat" class="form-control" value="<?= $lat ?>" />
                                <input type="hidden" id="est_lng" name="lng" class="form-control" value="<?= $lng ?>" />

                                <div class="col-12 text-end">
                                    <button class="btn btn-sm ripple btn-primary mt-1" type="button" id="openMap">
                                        <i class="fa fa-map"></i> Open Map
                                    </button>
                                    <button class="btn btn-sm ripple btn-primary mt-1" type="button" id="cancel_change_address_est" >
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
                        <input class="form-control" placeholder="Search Customer" type="text" id="customer_select_estimate">
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
        <input type='hidden' id='customer_id_estimate' name="customer_id"/>
        <div class="card-body datatables">
            <form id="msform">
                <fieldset class="est-page-1">
                    <div id="product_details" class="product-details table-responsive text-nowrap">
                        <table id="estTable" class="table table-hover table-fixed mb-0 text-md-nowrap">
                            <thead>
                                <tr>
                                    <th class="text-center small" width="5%">Image</th>
                                    <th width="10%">Description</th>
                                    <th width="5%" class="text-center">Color</th>
                                    <th width="5%" class="text-center">Grade</th>
                                    <th width="5%" class="text-center">Profile</th>
                                    <th width="25%" class="text-center pl-3">Quantity</th>
                                    <th width="15%" class="text-center pl-3">Usage</th>
                                    <th width="30%" class="text-center">Dimensions<br>(Width x Length)</th>
                                    <th width="5%" class="text-center">Stock</th>
                                    <th width="7%" class="text-center">Price</th>
                                    <th width="7%" class="text-center small">Customer<br>Price</th>
                                    <th width="1%" class="text-center"> </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total = 0;
                                $total_customer_price = 0;
                                $totalquantity = 0;
                                $timestamp = time();
                                $no = $timestamp . 1;
                                $total_weight = 0;
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

                                        $color_id = $values["custom_color"];
                                        if (isset($values["used_discount"])){
                                            $discount = isset($values["used_discount"]) ? floatval($values["used_discount"]) / 100 : 0;
                                        }

                                        $sold_by_feet = $product['sold_by_feet'];
                                    ?>
                                        <tr class="border-bottom border-3 border-white">
                                            <td>
                                                <?php
                                                if($data_id == '66'){
                                                    if(!empty($values["custom_trim_src"])){
                                                    ?>
                                                    <a href="javascript:void(0);" id="custom_trim_draw" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>">
                                                        <div class="align-items-center text-center w-100" style="background: #ffffff">
                                                            <img src="<?= $images_directory.$values["custom_trim_src"] ?>" class="rounded-circle " alt="materialpro-img" width="56" height="56">
                                                        </div>
                                                    </a>
                                                    <?php
                                                    }else{
                                                    ?>
                                                    <a href="javascript:void(0);" id="custom_trim_draw" class="btn btn-primary py-1 px-2 d-flex justify-content-center align-items-center" data-line="<?php echo $values["line"]; ?>" data-id="<?= $data_id ?>">
                                                        Draw Here
                                                    </a>
                                                    <?php
                                                    }
                                                    ?>
                                                    
                                                <?php }else{
                                                ?>
                                                <div class="align-items-center text-center w-100">
                                                    <img src="<?= $picture_path ?>" class="rounded-circle " alt="materialpro-img" width="56" height="56">
                                                </div>
                                                <?php
                                                } ?>
                                            </td>
                                            <td>
                                                <h6 class="fw-semibold mb-0 fs-4">
                                                    <?= $values["product_item"] ?>
                                                    <?php if ($values["is_pre_order"] == 1): ?>
                                                        <br>( PREORDER )
                                                    <?php endif; ?>
                                                </h6>
                                            </td>
                                            <td>
                                                <select id="color_est<?= $no ?>" class="form-control color-est text-start" name="color" onchange="updateColor(this)" data-line="<?= $values["line"]; ?>" data-id="<?= $data_id; ?>">
                                                    <option value="">Select Color...</option>
                                                    <?php
                                                    if (!empty($color_id)) {
                                                        echo '<option value="' . $color_id . '" selected data-color="' . getColorHexFromColorID($color_id) . '">' . getColorName($color_id) . '</option>';
                                                    }

                                                    $query_colors = "SELECT Product_id, color_id FROM inventory WHERE Product_id = '$data_id'";
                                                    $result_colors = mysqli_query($conn, $query_colors);

                                                    if (mysqli_num_rows($result_colors) > 0) {
                                                        while ($row_colors = mysqli_fetch_array($result_colors)) {
                                                            if ($color_id == $row_colors['color_id']) {
                                                                continue;
                                                            }
                                                            $product_details = getProductDetails($row_colors['Product_id']);
                                                            $disabled = $values['custom_grade'] != $product_details['grade'] ? 'disabled' : '';
                                                            echo '<option value="' . $row_colors['color_id'] . '" data-color="' . getColorHexFromColorID($row_colors['color_id']) . '" data-grade="' . $product_details['grade'] . '"' .$disabled .'>' . getColorName($row_colors['color_id']) . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group text-start">
                                                    <select id="grade<?= $no ?>" class="form-control grade-est" name="grade" onchange="updateGrade(this)" data-line="<?= $values['line']; ?>" data-id="<?= $data_id; ?>">
                                                        <option value="">Select Grade...</option>
                                                        <?php
                                                        $query_grade = "SELECT * FROM product_grade WHERE status = 1";
                                                        $result_grade = mysqli_query($conn, $query_grade);

                                                        while ($row_grade = mysqli_fetch_array($result_grade)) {
                                                            $selected = ($values['custom_grade'] == $row_grade['product_grade_id']) ? 'selected' : '';
                                                            ?>
                                                            <option value="<?= $row_grade['product_grade_id']; ?>" <?= $selected; ?>><?= $row_grade['product_grade']; ?></option>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <?php echo getProfileFromID($data_id); ?>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-primary btn-icon p-1 mr-1" type="button" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onClick="deductquantity(this)">
                                                            <i class="fa fa-minus"></i>
                                                        </button>
                                                    </span> 
                                                    <input class="form-control" type="text" size="5" value="<?php echo $values["quantity_cart"]; ?>" style="color:#ffffff;" onchange="updatequantity(this)" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" id="item_quantity<?php echo $data_id;?>">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-primary btn-icon p-1 ml-1" type="button" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onClick="addquantity(this)">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group text-start">
                                                    <select id="usage<?= $no ?>" class="form-control usage-est" name="usage" onchange="updateUsage(this)" data-line="<?= $values['line']; ?>" data-id="<?= $data_id; ?>">
                                                        <option value="">Select Usage...</option>
                                                        <?php
                                                        $query_key = "SELECT * FROM key_components ORDER BY component_name ASC";
                                                        $result_key = mysqli_query($conn, $query_key);

                                                        while ($row_key = mysqli_fetch_array($result_key)) {
                                                            $componentid = $row_key['componentid'];
                                                            ?>
                                                            <optgroup label="<?= strtoupper($row_key['component_name']); ?>">
                                                                <?php 
                                                                $query_usage = "SELECT * FROM component_usage WHERE componentid = '$componentid' ORDER BY `usage_name` ASC";
                                                                $result_usage = mysqli_query($conn, $query_usage);

                                                                while ($row_usage = mysqli_fetch_array($result_usage)) {
                                                                    $selected = ($values['usage'] == $row_usage['usageid']) ? 'selected' : '';
                                                                    ?>
                                                                    <option value="<?= $row_usage['usageid']; ?>" <?= $selected; ?>><?= $row_usage['usage_name']; ?></option>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </optgroup>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>

                                                </div>
                                            </td>
                                            <?php 
                                            if($category_id == $panel_id){ // Panels ID
                                            ?>
                                            <td>
                                                <div class="d-flex flex-column align-items-center">
                                                    <input class="form-control" type="text" value="<?= $product["width"]; ?>" placeholder="W" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" <?= !empty($product["width"]) ? 'readonly' : '' ?>>
                                                    <span class="mr-3 ml-1"> X</span>
                                                    <?php
                                                    if($sold_by_feet == 1){
                                                        ?>
                                                        <fieldset class="border p-1 position-relative">
                                                            <div class="input-group d-flex align-items-center">
                                                                <input class="form-control pr-0 pl-1 mr-1" type="number" value="<?= $values["estimate_length"] ?>" placeholder="FT" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateLength(this)">
                                                                <input class="form-control pr-0 pl-1" type="number" value="<?= $values["estimate_length_inch"]; ?>" placeholder="IN" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateLengthInch(this)">
                                                            </div>
                                                        </fieldset>
                                                    <?php
                                                    }else{
                                                    ?>
                                                        <input class="form-control" type="text" value="<?= $values["estimate_length"]; ?>" placeholder="H" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateLength(this)">
                                                    <?php
                                                    }
                                                    ?>
                                                    
                                                </div>
                                            </td>
                                            <?php
                                            }else if($category_id == $trim_id){
                                            ?>
                                            <td>
                                                <div class="d-flex flex-column align-items-center">
                                                    <input class="form-control text-center mb-1" type="text" value="<?= isset($values["estimate_width"]) ? $values["estimate_width"] : $product["width"]; ?>" placeholder="Width" size="5" style="color:#ffffff; " data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateWidth(this)">
                                                    <span class="mx-1 text-center mb-1">X</span>
                                                    <input class="form-control text-center mb-1" type="text" value="<?= $values["estimate_bend"]; ?>" placeholder="Bend" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateBend(this)">
                                                    <span class="mx-1 text-center mb-1">X</span>
                                                    <input class="form-control text-center mb-1" type="text" value="<?= $values["estimate_hem"]; ?>" placeholder="Hem" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateHem(this)">
                                                    <span class="mx-1 text-center mb-1">X</span>
                                                    <?php
                                                    if($sold_by_feet == 1){
                                                    ?>
                                                        <fieldset class="border p-1 position-relative">
                                                            <div class="input-group d-flex align-items-center">
                                                                <input class="form-control pr-0 pl-1 mr-1" type="number" value="<?= $values["estimate_length"] ?>" placeholder="FT" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateLength(this)">
                                                                <input class="form-control pr-0 pl-1" type="number" value="<?= $values["estimate_length_inch"]; ?>" placeholder="IN" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateLengthInch(this)">
                                                            </div>
                                                        </fieldset>
                                                    <?php
                                                    }else{
                                                    ?>
                                                        <input class="form-control text-center" type="text" value="<?= isset($values["estimate_length"]) ? $values["estimate_length"] : $product["length"]; ?>" placeholder="Length" size="5" style="color:#ffffff; " data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateLength(this)">
                                                    <?php
                                                    }
                                                    ?>
                                                </div>
                                            </td>
                                            <?php
                                            }else{
                                            ?>
                                            <td class="text-center">
                                                <fieldset class="border p-1 position-relative">
                                                    <div class="input-group d-flex align-items-center">
                                                        <input class="form-control pr-0 pl-1 mr-1" type="number" value="<?= $values["estimate_length"] ?>" placeholder="FT" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateLength(this)">
                                                        <input class="form-control pr-0 pl-1" type="number" value="<?= $values["estimate_length_inch"]; ?>" placeholder="IN" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateLengthInch(this)">
                                                    </div>
                                                </fieldset>
                                            </td>
                                            <?php
                                            }
                                            ?>
                                            <td><?= $stock_text ?></td>
                                            <td class="text-end pl-3">$
                                                <?php
                                                $subtotal = $product_price;
                                                echo number_format($subtotal, 2);
                                                ?>
                                            </td>
                                            <td class="text-end pl-3">$
                                                <?php
                                                $customer_price = $product_price * (1 - $discount) * (1 - $customer_pricing);
                                                echo number_format($customer_price, 2);
                                                ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-danger-gradient btn-sm" type="button" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onClick="delete_item(this)"><i class="fa fa-trash"></i></button>
                                                <button class="btn btn-danger-gradient btn-sm" type="button" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onClick="duplicate_item(this)"><i class="fa fa-plus"></i></button>
                                                <input type="hidden" class="form-control" data-id="<?php echo $data_id; ?>" id="item_id<?php echo $data_id; ?>" value="<?php echo $values["product_id"]; ?>">
                                                <input class="form-control" type="hidden" size="5" value="<?php echo $values["quantity_ttl"];?>" id="warehouse_stock<?php echo $data_id;?>">
                                                <input class="form-control" type="hidden" size="5" value="<?php echo $values["line"];?>" id="line<?php echo $data_id;?>">
                                                <input class="form-control" type="hidden" size="5" value="<?php echo $values["quantity_in_stock"];?>" id="store_stock<?php echo $data_id;?>">
                                            </td>
                                        </tr>
                                <?php
                                        $totalquantity += $values["quantity_cart"];
                                        $total += $subtotal;
                                        $total_customer_price += $customer_price;
                                        $no++;
                                        $total_weight += $values["weight"] * $values["quantity_cart"];
                                    }
                                }
                                $_SESSION["total_quantity"] = $totalquantity;
                                $_SESSION["grandtotal"] = $total;
                                ?>
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="2" class="text-end">Total Weight</td>
                                    <td><?= number_format(floatval($total_weight), 2) ?> LBS</td>
                                    <td colspan="3" class="text-end">Total Quantity:</td>
                                    <td colspan="1" class=""><span id="qty_ttl"><?= $totalquantity ?></span></td>
                                    <td colspan="3" class="text-end">Amount Due:</td>
                                    <td colspan="1" class="text-end"><span id="ammount_due"><?= number_format($total_customer_price,2) ?> $</span></td>
                                    <td colspan="1"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </fieldset>
                <fieldset class="est-page-2">
                    <div id="checkout" class="row mt-3">
                        <div class="col-md-6"></div>
                        <div class="col-md-3 mb-3">
                            <label for="job_name" class="mb-0">Job Name</label>
                            <div id="est_checkout">
                                <select id="est_job_name" class="form-control" name="est_job_name">
                                    <option value="">Select Job Name...</option>
                                    <?php
                                    $query_job_name = "SELECT * FROM job_names WHERE customer_id = '$customer_id'";
                                    $result_job_name = mysqli_query($conn, $query_job_name);
                                    while ($row_job_name = mysqli_fetch_array($result_job_name)) {
                                    ?>
                                        <option value="<?= $row_job_name['job_name']; ?>"><?= $row_job_name['job_name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                    <option value="add_new_job_name">Add new Job Name</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="job_po" class="mb-0">Job PO #</label>
                            <input type="text" id="est_job_po" name="est_job_po" class="form-control" placeholder="Enter Job PO #">
                        </div>
                        <div class="col-md-6">
                            <div class="card box-shadow-0">
                                <div class="card-body">
                                    <form>
                                        <div>
                                            <label>Total Items:</label>
                                            <span id="total_items"><?= $_SESSION["total_quantity"] ?? '0' ?></span>
                                        </div>
                                        <div class="form-group">
                                            <label>Discount (%)</label>
                                            <input type="text" class="form-control discount_input" id="est_discount" placeholder="%" value="<?= $discount * 100 ?>">
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label>Credit Amount</label>
                                                    <input type="number" class="form-control" id="est_credit" value="0.00">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label>Cash Amount</label>
                                                    <input type="number" class="form-control" id="est_cash" value="<?= round($total_customer_price + $delivery_price, 2) ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body pricing container">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <th class="text-right border-bottom">Total</th>
                                                    <td class="text-right border-bottom">$<span id="est_total_amt"><?= round(floatval($total_customer_price), 2) ?></span></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-right border-bottom">Discount(-)</th>
                                                    <td class="text-right border-bottom">$<span id="est_total_discount"><?= number_format(floatval($total) * floatval($discount), 2) ?></span></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-right border-bottom">Delivery Method</th>
                                                    <td class="text-right border-bottom">
                                                        <select id="est_delivery_method" name="est_delivery_method" class="form-control text-right p-2">
                                                            <option value="deliver">Deliver</option>
                                                            <option value="pickup">Pickup</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-right border-bottom">Delivery($)</th>
                                                    <td class="text-right border-bottom">
                                                        <input type="number" id="est_delivery_amt" name="est_delivery_amt" value="<?= number_format($delivery_price, 2) ?>" class="text-right form-control" placeholder="Delivery Amount">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-right border-bottom">Sales Tax</th>
                                                    <td class="text-right border-bottom">$<span id="est_sales_tax"><?= number_format((floatval($total_customer_price) + $delivery_price) * $tax, 2) ?></span></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-right border-bottom">Total Payable</th>
                                                    <td class="text-right border-bottom">$<span id="est_total_payable"><?= number_format((floatval($total_customer_price) + $delivery_price), 2) ?></span></td>
                                                    <input type="hidden" id="payable_amt" value="<?= number_format((floatval($total_customer_price) + $delivery_price), 2) ?>">
                                                </tr>
                                                <tr class="bg-primary text-white" style="font-size: 1.25rem;">
                                                    <th class="text-right">Change</th>
                                                    <td class="text-right">$<span id="change">0.00</span></td>
                                                </tr> 
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    <script>
        function init_select_estimate(){
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
        }
        $(document).ready(function() {
            init_select_estimate();

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
                            loadEstimateContents();
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
                });
            });

            $(document).on('click', '#customer_change_est', function(event) {
                $.ajax({
                    url: 'pages/cashier_ajax.php',
                    type: 'POST',
                    data: {
                        unset_customer: "unset_customer"
                    },
                    success: function(response) { 
                        loadEstimateContents();
                        $('#next_page_est').removeClass("d-none");
                        $('#prev_page_est').addClass("d-none");
                        $('#save_estimate').addClass("d-none");
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
                });
            });

            $(document).on('click', '#cancel_change_address_est', function(event) {
                loadEstimateContents();
            });

            $(document).on('click', '#address_change_est', function(event) {
                $('#deliverDetailsEst').removeClass('d-none');
                $('#defaultDeliverDetailsEst').addClass('d-none');
                $('#est_deliver_fname').val('');
                $('#est_deliver_lname').val('');
                $('#est_deliver_address').val('');
                $('#est_deliver_city').val('');
                $('#est_deliver_state').val('');
                $('#est_deliver_zip').val('');
            });
            
            

            $(document).on('change', '#est_delivery_amt', function() {
                var product_cost = parseFloat($('#est_total_amt').text()) || 0;
                console.log(product_cost)
                var delivery_cost = parseFloat($(this).val()) || 0;
                var total_payable = product_cost + delivery_cost;
                $('#est_total_payable').text(total_payable.toFixed(2));
                $('#est_cash').val(total_payable.toFixed(2));
            });

            let animating = false;

            var estPage1 = $('.est-page-1');
            var estPage2 = $('.est-page-2');

            function changePage(currentPage, nextPage, isNext) {
                if (animating) return false;
                animating = true;

                var current_fs = $(currentPage);
                var next_fs = $(nextPage);

                if (isNext) {
                    $('#next_page_est').addClass("d-none");
                    $('#prev_page_est').removeClass("d-none");
                    $('#save_estimate').removeClass("d-none");
                } else {
                    $('#next_page_est').removeClass("d-none");
                    $('#prev_page_est').addClass("d-none");
                    $('#save_estimate').addClass("d-none");
                }

                next_fs.show();
                current_fs.hide();

                animating = false;
            }

            $(document).on("click", "#next_page_est", function() {
                changePage(estPage1, estPage2, true);
            });

            $(document).on("click", "#prev_page_est", function() {
                changePage(estPage2, estPage1, false);
            });


            $('#est_job_name').select2({
                width: '100%',
                placeholder: "Select Job Name...",
                dropdownAutoWidth: true,
                dropdownParent: $('#est_checkout'),
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

            $('#est_job_name').on('select2:select', function (e) {
                const selectedValue = e.params.data.id;
                if (selectedValue === 'add_new_job_name') {
                    $('#prompt_job_name_modal').modal('show');
                    $('#est_job_name').val(null).trigger('change');
                }
            });

            $(".color-est").each(function() {
                if ($(this).data('select2')) {
                    $(this).select2('destroy');
                }
                $(this).select2({
                    width: '300px',
                    placeholder: "Select...",
                    dropdownAutoWidth: true,
                    dropdownParent: $('#estTable'),
                    templateResult: formatOption,
                    templateSelection: formatSelected
                });
            });

            $(".grade-est").each(function() {
                if ($(this).data('select2')) {
                    $(this).select2('destroy');
                }
                $(this).select2({
                    width: '300px',
                    placeholder: "Select...",
                    dropdownAutoWidth: true,
                    dropdownParent: $('#estTable')
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
                    dropdownParent: $('#estTable')
                });
            });  

            $(document).on('change', '.grade-est', function () {
                const selectedGrade = $(this).val();
                const no = $(this).attr('id').replace('grade', '');
                const colorSelect = $(`#color_est${no}`);

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
                        dropdownParent: $('#estTable'),
                        templateResult: formatOption,
                        templateSelection: formatSelected
                    });
                }
            });

            
        });
    </script>
    <?php
}