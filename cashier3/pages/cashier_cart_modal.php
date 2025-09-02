<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

$trim_id = 4;
$panel_id = 3;
$screw_id = 16;

if(isset($_POST['fetch_cart'])){
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
        $discount = is_numeric(getCustomerDiscount($customer_id)) ? floatval(getCustomerDiscount($customer_id)) / 100 : 0;
        $tax = is_numeric(getCustomerTax($customer_id)) ? floatval(getCustomerTax($customer_id)) / 100 : 0;
        $customer_details_pricing = $customer_details['customer_pricing'];
    }
    $delivery_price = is_numeric(getDeliveryCost()) ? floatval(getDeliveryCost()) : 0;
    ?>
    <script>
        console.log("<pre><?= print_r($_SESSION["cart"]) ?></pre>");
    </script>
    <style>
        input[type="number"]::-webkit-inner-spin-button, 
        input[type="number"]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }

        input[type="number"] {
            -moz-appearance: textfield;
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
        .table-fixed td:nth-child(5) { width: 15%; }
        .table-fixed th:nth-child(6),
        .table-fixed td:nth-child(6) { width: 7%; }
        .table-fixed th:nth-child(7),
        .table-fixed td:nth-child(7) { width: 7%; }
        .table-fixed th:nth-child(8),
        .table-fixed td:nth-child(8) { width: 9%; }
        .table-fixed th:nth-child(9),
        .table-fixed td:nth-child(9) { width: 7%; }
        .table-fixed th:nth-child(10),
        .table-fixed td:nth-child(10) { width: 7%; }
        .table-fixed th:nth-child(11),
        .table-fixed td:nth-child(11) { width: 7%; }
        .table-fixed th:nth-child(12),
        .table-fixed td:nth-child(12) { width: 3%; }

        input[readonly] {
            border: none;               
            background-color: transparent;
            pointer-events: none;
            color: inherit;
        }

        .table-fixed tbody tr:hover input[readonly] {
            background-color: transparent;
        }

        .select2-container--default .select2-results__option[aria-disabled=true] { 
            display: none;
        }

        .short-border {
            display: inline-block;
            padding-bottom: 2px;
            border-bottom: 2px solid #000;
            width: 120px;
            text-align: right;
        }

        .thick-border td {
            border-top: 5px solid #fff !important;
            padding-top: 20px;  
        }

        .thick-border th {
            border-top: 5px solid #fff !important;
            padding-top: 0px;  
        }
    </style>
    <div id="customer_cart_section">
        <?php 
            if(!empty($_SESSION["customer_id"])){
                $customer_id = $_SESSION["customer_id"];
                $customer_details = getCustomerDetails($customer_id);
                $charge_net_30 = floatval($customer_details['charge_net_30'] ?? 0);
                $credit_total = getCustomerCreditTotal($customer_id);
                $store_credit = floatval($customer_details['store_credit'] ?? 0);

                $customer_name = get_customer_name($_SESSION["customer_id"]);
            ?>

            <div class="form-group row align-items-center" style="color: #ffffff !important;">
                <div class="d-flex flex-column gap-2">
                    <div>
                        <label class="fw-bold fs-5">Customer Name: <?= $customer_name ?></label>
                        <button class="btn btn-primary btn-sm me-3" type="button" id="customer_change_cart">
                            <i class="fe fe-reload"></i> Change
                        </button>
                    </div>
                    <div class="d-flex flex-wrap gap-5">
                        <div class="d-flex flex-column align-items-start">
                            <span class="fw-bold">Charge Net 30 Limit:</span>
                            <span class="text-primary fs-4 fw-bold">$<?= number_format($charge_net_30,2) ?></span>
                        </div>
                        <div class="d-flex flex-column align-items-start">
                            <span class="fw-bold">Current Balance Due:</span>
                            <span class="text-primary fs-4 fw-bold">$<?= $credit_total ?></span>
                        </div>
                        <div class="d-flex flex-column align-items-start">
                            <span class="fw-bold">Available Credit:</span>
                            <span class="text-primary fs-4 fw-bold">$<?= $store_credit ?></span>
                        </div>
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
            </div>
        <?php } ?>
    </div>
    <input type='hidden' id='customer_id_cart' name="customer_id"/>
    <div class="card-body">
        <div class="product-details table-responsive text-nowrap">
            <table id="cartTable" class="table table-hover table-fixed mb-0 text-md-nowrap">
                <thead>
                    <tr>
                        <th class="small">Image</th>
                        <th>Description</th>
                        <th class="text-center">Color</th>
                        <th class="text-center">Grade</th>
                        <th class="text-center">Profile</th>
                        <th class="text-center pl-3">Quantity</th>
                        <th class="text-center">Length</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Price</th>
                        <th class="text-center small">Customer<br>Price</th>
                        <th class="text-center"> </th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    $totalquantity = 0;
                    $line = 1;
                    $total_weight = 0;
                    $customer_savings = 0;
                    $grand_actual_price = 0;
                    $grand_customer_price = 0;
                    $is_panel_present = 0;
                    if (!empty($_SESSION["cart"])) {
                        $grouped_cart = [];

                        foreach ($_SESSION["cart"] as $values) {
                            $pid = $values["product_id"];
                            if (!isset($grouped_cart[$pid])) {
                                $grouped_cart[$pid] = [];
                            }
                            $grouped_cart[$pid][] = $values;
                        }

                        foreach ($grouped_cart as $product_id => $items) {
                            $product = getProductDetails($product_id);
                            $totalstockquantity = getProductStockTotal($product_id);
                            $category_id = $product["product_category"];
                            $sold_by_feet = $product['sold_by_feet'];
                            $customer_pricing = getPricingCategory($category_id, $customer_details_pricing) / 100;

                            $images_directory = "../images/drawing/";
                            $default_image = '../images/product/product.jpg';

                            if ($totalstockquantity > 0) {
                                $stock_text = '
                                    <a href="javascript:void(0);" id="view_in_stock" data-id="' . htmlspecialchars($product_id, ENT_QUOTES, 'UTF-8') . '" class="d-flex align-items-center">
                                        <span class="text-bg-success p-1 rounded-circle"></span>
                                        <span class="ms-2 fs-3">In Stock</span>
                                    </a>';
                            } else {
                                $stock_text = '
                                    <a href="javascript:void(0);" id="view_out_of_stock" data-id="' . htmlspecialchars($product_id, ENT_QUOTES, 'UTF-8') . '" class="d-flex align-items-center">
                                        <span class="text-bg-danger p-1 rounded-circle"></span>
                                        <span class="ms-2 fs-3">Out of Stock</span>
                                    </a>';
                            }

                            $total_qty = 0;
                            $total_price_product = 0;
                            $total_length = 0;
                            $total_length_cart = 0;
                            $is_preorder = 0;
                            $total_price_actual = 0;
                            $total_customer_price = 0;

                            foreach ($items as $values) {
                                $product_id = $values["product_id"];
                                $line = $values["line"];
                                $product = getProductDetails($product_id);
                                $totalstockquantity = getProductStockTotal($product_id);
                                $category_id = $product["product_category"];
                                if ($totalstockquantity > 0) {
                                    $stock_text = '
                                        <a href="javascript:void(0);" id="view_in_stock" data-id="' . htmlspecialchars($product_id, ENT_QUOTES, 'UTF-8') . '" class="d-flex justify-content-center align-items-center">
                                            <span class="text-bg-success p-1 rounded-circle"></span>
                                            <span class="ms-2 fs-3">In Stock</span>
                                        </a>';
                                } else {
                                    $stock_text = '
                                        <a href="javascript:void(0);" id="view_out_of_stock" data-id="' . htmlspecialchars($product_id, ENT_QUOTES, 'UTF-8') . '" class="d-flex justify-content-center align-items-center">
                                            <span class="text-bg-danger p-1 rounded-circle"></span>
                                            <span class="ms-2 fs-3">Out of Stock</span>
                                        </a>';
                                } 

                                $picture_path = !empty($row_product['main_image'])
                                ? "../" .$row_product['main_image']
                                : $default_image;

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

                                $total_qty += $values["quantity_cart"];
                                $total_length_cart += $quantity * $total_length;
                                $totalquantity += $values["quantity_cart"];
                                $total_price_actual += $product_price;
                                $customer_price = $product_price * (1 - $discount) * (1 - $customer_pricing);
                                $total_customer_price += $customer_price;
                                $total_weight += $product["weight"] * $values["quantity_cart"];
                                $customer_savings += $product_price - $customer_price;
                                $grand_actual_price += $product_price;
                                $grand_customer_price += $customer_price;
                            }

                            $default_image = '../images/product/product.jpg';

                            $picture_path = !empty($product['main_image'])
                            ? "../" .$product['main_image']
                            : $default_image;

                            ?>
                            <tr class="thick-border">
                                <td>
                                    <div class="align-items-center text-center w-100">
                                        <img src="<?= $picture_path ?>" class="rounded-circle " alt="materialpro-img" width="56" height="56">
                                    </div>
                                </td>
                                <td>
                                    <h6 class="fw-semibold mb-0 fs-4">
                                        <?= $values["product_item"] ?>
                                        <?php if ($is_preorder == 1): ?>
                                            <br>( PREORDER )
                                        <?php endif; ?>
                                    </h6>
                                </td>
                                <td class="text-center">
                                    <select id="color_cart<?= $line ?>" class="form-control color-cart text-start" name="color" onchange="updateColor(this)" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>">
                                        <option value="">Select Color...</option>
                                        <?php
                                        $query_colors = "SELECT Product_id, color_id FROM inventory WHERE Product_id = '$product_id'";
                                        $result_colors = mysqli_query($conn, $query_colors);

                                        if (mysqli_num_rows($result_colors) > 0) {
                                            while ($row_colors = mysqli_fetch_array($result_colors)) {
                                                echo '<option value="' . $row_colors['color_id'] . '" data-color="' . getColorHexFromColorID($row_colors['color_id']) . '" data-grade="' . $product_details['grade'] . '">' . getColorName($row_colors['color_id']) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <div class="input-group text-start">
                                        <select id="grade<?= $line ?>" class="form-control grade-cart" name="grade" onchange="updateGrade(this)" data-line="<?= $values['line']; ?>" data-id="<?= $product_id; ?>">
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
                                <td class="text-center">
                                    <?= getProfileFromID($product_id); ?>
                                </td>
                                <td class="text-center">
                                    <?= $total_qty ?>
                                </td>
                                <td class="text-center">
                                    <?= number_format($total_length_cart,2) ?>
                                </td>
                                <td class="text-center">
                                    <?= $stock_text ?>
                                </td>
                                <td class="text-center">
                                    $ <?= number_format($total_price_actual,2) ?>
                                </td>
                                <td class="text-center">
                                    $ <?= number_format($total_customer_price,2) ?>
                                </td>
                                <td class="text-center">
                                    
                                </td>
                            </tr>
                            
                            <?php
                            $bundle_count = 1;
                            $groupedBundles = [];
                            foreach ($items as $values) {
                                $bundle_name = $values['bundle_name'] ?? '';
                                if (!isset($groupedBundles[$bundle_name])) {
                                    $groupedBundles[$bundle_name] = [];
                                }
                                $groupedBundles[$bundle_name][] = $values;
                            }

                            $bundle_actual_price = 0;
                            $bundle_customer_price = 0;
                            foreach ($groupedBundles as $bundle_name => $bundle_items) {
                                $first = true;

                                if(!empty($bundle_name)) {
                                ?>
                                <tr class="thick-border">
                                    <td class="text-center" colspan="3">Bundle <?= $bundle_count ?></td>
                                    <td class="text-center" colspan="4"></td>
                                    <td class="text-center">Bundle <?= $bundle_count ?></td>
                                    <td class="text-center" colspan="3"></td>
                                </tr>
                                <?php
                                }
                                $bundle_count++;
                                ?>
                                <tr class="thick-border">
                                    <th class="text-center" colspan="3">
                                        <?php 
                                        if(!empty($bundle_name)) {
                                            echo "($bundle_name)";
                                        }
                                        ?>
                                    </th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-center">Length</th>
                                    <th class="text-center">Panel Type</th>
                                    <th class="text-center">Panel Style</th>
                                    <th class="text-center">
                                        <?php 
                                        if(!empty($bundle_name)) {
                                            echo "($bundle_name)";
                                        }
                                        ?>
                                    </th>
                                    <th class="text-center">Line Item Price</th>
                                    <th class="text-center">Line Item Price</th>
                                    <th class="text-center"></th>
                                </tr>
                                <?php
                                

                                foreach ($bundle_items as $values) {
                                    $line = $values["line"];
                                    $estimate_length = floatval($values["estimate_length"] ?? 1);
                                    $estimate_length_inch = floatval($values["estimate_length_inch"] ?? 0);
                                    $total_length = $estimate_length + ($estimate_length_inch / 12);

                                    $quantity   = floatval($values["quantity_cart"] ?? 0);
                                    $unit_price = floatval($product["unit_price"] ?? 0);
                                    $amount_discount = floatval($values["amount_discount"] ?? 0);

                                    if($total_length == 0){
                                        $total_length = 1;
                                    }

                                    $product_price = ($quantity * $unit_price * $total_length) - $amount_discount;
                                    $discount = isset($values["used_discount"]) ? floatval($values["used_discount"]) / 100 : 0;
                                    $customer_pricing = getPricingCategory($category_id, $customer_details_pricing) / 100;
                                    $customer_price = $product_price * (1 - $discount) * (1 - $customer_pricing);

                                    $bundle_actual_price += $product_price;
                                    $bundle_customer_price += $customer_price;

                                    $drawing_data = $values['drawing_data'];
                                    ?>
                                    <tr>
                                        <td data-color="<?= getColorName($color_id) ?>" data-pricing="<?=$customer_pricing?>" data-category="<?=$category_id?>" data-customer-pricing="<?=$customer_details_pricing?>">
                                            <?php
                                            if($category_id == $trim_id){
                                                if(!empty($values["custom_trim_src"])){
                                                ?>
                                                <a href="javascript:void(0);" class="drawingContainer" id="custom_trim_draw" data-line="<?php echo $line; ?>" data-id="<?php echo $product_id; ?>" data-drawing="<?= $drawing_data ?>">
                                                    <div class="align-items-center text-center w-100" style="background: #ffffff">
                                                        <img src="<?= $images_directory.$values["custom_trim_src"] ?>" class="rounded-circle " alt="materialpro-img" width="56" height="56">
                                                    </div>
                                                </a>
                                                <?php
                                                }else{
                                                ?>
                                                <a href="javascript:void(0);" id="custom_trim_draw" class="drawingContainer btn btn-primary py-1 px-2 d-flex justify-content-center align-items-center" data-drawing="<?= $drawing_data ?>" data-line="<?php echo $line; ?>" data-id="<?php echo $product_id; ?>">
                                                    Draw Here
                                                </a>
                                                <?php
                                                }
                                                ?>
                                                
                                            <?php } ?>
                                        </td>
                                        <td></td>
                                        <td class="text-center">
                                            
                                        </td>
                                        <td class="text-center">
                                            <div class="input-group d-inline-flex align-items-center flex-nowrap w-auto">
                                                <button class="btn btn-primary btn-sm p-1" type="button"
                                                    data-line="<?php echo $line; ?>" 
                                                    data-id="<?php echo $product_id; ?>" 
                                                    onClick="deductquantity(this)">
                                                    <i class="fa fa-minus"></i>
                                                </button>

                                                <input class="form-control form-control-sm text-center mx-0"
                                                    type="text"
                                                    value="<?php echo $values['quantity_cart']; ?>"
                                                    onchange="updatequantity(this)"
                                                    data-line="<?php echo $line; ?>"
                                                    data-id="<?php echo $product_id; ?>"
                                                    id="item_quantity<?php echo $product_id;?>"
                                                    style="width: 45px;">

                                                <button class="btn btn-primary btn-sm p-1" type="button"
                                                    data-line="<?php echo $line; ?>" 
                                                    data-id="<?php echo $product_id; ?>" 
                                                    onClick="addquantity(this)">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <?php 
                                        if($category_id == $panel_id){ // Panels ID
                                        ?>
                                        <td class="text-center">
                                            <div class="d-flex flex-row align-items-center flex-nowrap w-auto">
                                                <input class="form-control form-control-sm text-center px-1" 
                                                    type="text" 
                                                    value="<?= $product['width']; ?>" 
                                                    placeholder="W" 
                                                    size="5" 
                                                    style="width: 40px; color:#ffffff;" 
                                                    data-line="<?= $line; ?>" 
                                                    data-id="<?= $product_id; ?>" 
                                                    <?= !empty($product['width']) ? 'readonly' : '' ?>>

                                                <span class="mx-1">X</span>

                                                <?php if ($sold_by_feet == 1): ?>
                                                    <fieldset class="border p-1 d-inline-flex align-items-center flex-nowrap">
                                                        <div class="input-group d-flex align-items-center flex-nowrap w-auto">
                                                            <input class="form-control form-control-sm text-center px-1 mr-1" 
                                                                type="text" 
                                                                value="<?= round(floatval($values['estimate_length']),2) ?>" 
                                                                step="0.001" 
                                                                placeholder="FT" 
                                                                size="5" 
                                                                style="width: 40px; color:#ffffff;" 
                                                                data-line="<?= $line; ?>" 
                                                                data-id="<?= $product_id; ?>" 
                                                                onchange="updateEstimateLength(this)">
                                                            
                                                            <input class="form-control form-control-sm text-center px-1" 
                                                                type="text" 
                                                                value="<?= round(floatval($values['estimate_length_inch']),2) ?>" 
                                                                step="0.001" 
                                                                placeholder="IN" 
                                                                size="5" 
                                                                style="width: 60px; color:#ffffff;" 
                                                                data-line="<?= $line; ?>" 
                                                                data-id="<?= $product_id; ?>" 
                                                                onchange="updateEstimateLengthInch(this)">
                                                        </div>
                                                    </fieldset>
                                                <?php else: ?>
                                                    <input class="form-control form-control-sm text-center px-1" 
                                                        type="text" 
                                                        value="<?= round(floatval($values['estimate_length']),2) ?>" 
                                                        placeholder="H" 
                                                        size="5" 
                                                        style="width: 70px; color:#ffffff;" 
                                                        data-line="<?= $line; ?>" 
                                                        data-id="<?= $product_id; ?>" 
                                                        onchange="updateEstimateLength(this)">
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <?php
                                        }else if($category_id == $screw_id){
                                        ?>
                                        <td>
                                            <div class="d-flex flex-column align-items-center d-none">
                                                <input class="form-control text-center mb-1" type="text" value="<?= isset($values["estimate_width"]) ? $values["estimate_width"] : $product["width"]; ?>" placeholder="Width" size="5" style="color:#ffffff; " data-line="<?php echo $line; ?>" data-id="<?php echo $product_id; ?>" onchange="updateEstimateWidth(this)">
                                                <span class="mx-1 text-center mb-1">X</span>
                                                <fieldset class="border p-1 position-relative">
                                                    <div class="input-group d-flex align-items-center">
                                                        <input class="form-control pr-0 pl-1 mr-1" type="text" value="<?= round(floatval($values["estimate_length"]),2) ?>" step="0.001" placeholder="FT" size="5" style="color:#ffffff;" data-line="<?php echo $line; ?>" data-id="<?php echo $product_id; ?>" onchange="updateEstimateLength(this)">
                                                        <input class="form-control pr-0 pl-1" type="text" value="<?= round(floatval($values["estimate_length_inch"]),2) ?>" step="0.001" placeholder="IN" size="5" style="color:#ffffff;" data-line="<?php echo $line; ?>" data-id="<?php echo $product_id; ?>" onchange="updateEstimateLengthInch(this)">
                                                    </div>
                                                </fieldset>
                                            </div>
                                            <?= $values["estimate_length"] ?> pack (<?= $values["estimate_length"] ?> pcs)
                                        </td>
                                        <?php
                                        }else if($category_id == $trim_id){
                                        ?>
                                        <td class="text-center">
                                            <div class="d-flex flex-row align-items-center flex-nowrap w-auto">
                                                <input class="form-control form-control-sm text-center px-1" 
                                                    type="text" 
                                                    value="<?= $product['width']; ?>" 
                                                    placeholder="W" 
                                                    size="5" 
                                                    style="width: 40px; color:#ffffff;" 
                                                    data-line="<?= $line; ?>" 
                                                    data-id="<?= $product_id; ?>" 
                                                    <?= !empty($product['width']) ? 'readonly' : '' ?>>

                                                <span class="mx-1">X</span>

                                                <fieldset class="border p-1 d-inline-flex align-items-center flex-nowrap">
                                                    <div class="input-group d-flex align-items-center flex-nowrap w-auto">
                                                        <input class="form-control form-control-sm text-center px-1 mr-1" 
                                                            type="text" 
                                                            value="<?= round(floatval($values['estimate_length']),2) ?>" 
                                                            step="0.001" 
                                                            placeholder="FT" 
                                                            size="5" 
                                                            style="width: 40px; color:#ffffff;" 
                                                            data-line="<?= $line; ?>" 
                                                            data-id="<?= $product_id; ?>" 
                                                            onchange="updateEstimateLength(this)">
                                                        
                                                        <input class="form-control form-control-sm text-center px-1" 
                                                            type="text" 
                                                            value="<?= round(floatval($values['estimate_length_inch']),2) ?>" 
                                                            step="0.001" 
                                                            placeholder="IN" 
                                                            size="5" 
                                                            style="width: 40px; color:#ffffff;" 
                                                            data-line="<?= $line; ?>" 
                                                            data-id="<?= $product_id; ?>" 
                                                            onchange="updateEstimateLengthInch(this)">
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </td>
                                        <?php
                                        }else if(hasProductVariantLength($product_id)){
                                        ?>
                                        <td class="text-center">
                                            <fieldset class="border p-1 position-relative">
                                                <div class="input-group d-flex align-items-center">
                                                    <input class="form-control pr-0 pl-1 mr-1" type="text" value="<?= round(floatval($values["estimate_length"]),2) ?>" placeholder="FT" size="5" style="color:#ffffff;" data-line="<?php echo $line; ?>" data-id="<?php echo $product_id; ?>" onchange="updateEstimateLength(this)">
                                                    <input class="form-control pr-0 pl-1" type="text" value="<?= round(floatval($values["estimate_length_inch"]),2) ?>" placeholder="IN" size="5" style="color:#ffffff;" data-line="<?php echo $line; ?>" data-id="<?php echo $product_id; ?>" onchange="updateEstimateLengthInch(this)">
                                                </div>
                                            </fieldset>
                                        </td>
                                        <?php
                                        }else{
                                        ?>
                                        <td></td>
                                        <?php
                                        }
                                        ?>
                                        <td class="text-center">
                                            <select class="form-control panel_type_cart" name="panel_type" onchange="updatePanelType(this)" data-line="<?= $values['line']; ?>" data-id="<?= $product_id; ?>">
                                                <option value="">Select...</option>
                                                <option value="solid" <?= $values['panel_type'] == 'solid' ? 'selected' : '' ?>>Solid</option>
                                                <option value="vented" <?= $values['panel_type'] == 'vented' ? 'selected' : '' ?>>Vented</option>
                                                <option value="drip_stop" <?= $values['panel_type'] == 'drip_stop' ? 'selected' : '' ?>>Drip Stop</option>
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <select class="form-control panel_style_cart" name="panel_style" onchange="updatePanelStyle(this)" data-line="<?= $values['line']; ?>" data-id="<?= $product_id; ?>">
                                                <option value="">Select...</option>
                                                <option value="regular" <?= $values['panel_style'] == 'regular' ? 'selected' : '' ?>>Regular</option>
                                                <option value="reversed" <?= $values['panel_style'] == 'reversed' ? 'selected' : '' ?>>Reversed</option>
                                            </select>
                                        </td>
                                        <td class="text-center">

                                        </td>
                                        <td class="text-center pl-3">$
                                            <?php
                                            echo number_format($product_price, 2);
                                            ?>
                                        </td>
                                        <td class="text-end pl-3">$
                                            <?php
                                            echo number_format($customer_price, 2);
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-danger-gradient btn-sm" type="button" data-line="<?php echo $line; ?>" data-id="<?php echo $product_id; ?>" onClick="delete_item(this)"><i class="fa fa-trash"></i></button>
                                            <button class="btn btn-danger-gradient btn-sm" type="button" data-line="<?php echo $line; ?>" data-id="<?php echo $product_id; ?>" onClick="duplicate_item(this)"><i class="fa fa-plus"></i></button>
                                            <input type="hidden" class="form-control" data-id="<?php echo $product_id; ?>" id="item_id<?php echo $product_id; ?>" value="<?php echo $values["product_id"]; ?>">
                                            <input class="form-control" type="hidden" size="5" value="<?php echo $values["quantity_ttl"];?>" id="warehouse_stock<?php echo $product_id;?>">
                                            <input class="form-control" type="hidden" size="5" value="<?php echo $line;?>" id="line<?php echo $product_id;?>">
                                            <input class="form-control" type="hidden" size="5" value="<?php echo $values["quantity_in_stock"];?>" id="store_stock<?php echo $product_id;?>">
                                            <?php 
                                            if ($category_id == $panel_id || $category_id == $trim_id) { // Panels ID
                                                $is_panel_present = 1;
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php
                                }

                                if(!empty($bundle_name)) {
                                    $bundle_actual_price += $product_price;
                                    $bundle_customer_price += $customer_price;
                                    ?>
                                    <tr>
                                        <th class="text-center" colspan="8"></th>
                                        <th class="text-right">Bundle Price</th>
                                        <th class="text-right">Bundle Price</th>
                                        <th class="text-center"></th>
                                    </tr>
                                    <tr>
                                        <th class="text-center" colspan="8"></th>
                                        <th class="text-right">$ <?= number_format($bundle_actual_price,2) ?></th>
                                        <th class="text-right">$ <?= number_format($bundle_customer_price,2) ?></th>
                                        <th class="text-center"></th>
                                    </tr>
                                    <?php
                                }

                                

                            }

                            
                        }
                        
                        
                    }
                    
                    ?>
                </tbody>

                <tfoot>
                    <?php 
                    if ($is_panel_present) {
                    ?>
                    <tr>
                        <td colspan="11">
                            <div class="d-flex flex-column align-items-end justify-content-end">
                                <button type="button" class="btn btn-sm btn-info btn-add-screw" data-id="<?= $product_id; ?>">
                                    Add Screw
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php 
                    }
                    ?>
                    <tr>
                        <td colspan="2" class="text-end">Total Weight</td>
                        <td><?= number_format(floatval($total_weight), 2) ?> LBS</td>
                        <td colspan="2" class="text-end">Total Quantity:</td>
                        <td colspan="1" class=""><span id="qty_ttl"><?= $totalquantity ?></span></td>
                        <td colspan="3" class="text-end">Customer Savings:</td>
                        <td colspan="1" class="text-end"><span id="ammount_due">$<?= number_format($customer_savings,2) ?></span></td>
                        <td colspan="1"></td>
                    </tr>
                    <tr>
                        <th colspan="8" style="border-bottom: none; border-top: none;"></th>
                        <th class="text-end" style="border-bottom: 1px solid #dee2e6;">Materials Price:</th>
                        <td class="text-end" style="border-bottom: 1px solid #dee2e6;">
                            $<span id="total_amt"><?= number_format(floatval($grand_customer_price), 2) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="8" style="border-bottom: none; border-top: none;"></th>
                        <th class="text-end" style="border-bottom: 1px solid #dee2e6;">Sales Tax:</th>
                        <td class="text-end" style="border-bottom: 1px solid #dee2e6;">
                            $<span id="sales_tax"><?= number_format($total_tax = floatval($grand_customer_price) * $tax, 2) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="8" style="border-bottom: none; border-top: none;"></th>
                        <th class="text-end fw-bold" style="border-bottom: 1px solid #dee2e6;">Total Due:</th>
                        <td class="text-end fw-bold" style="border-bottom: 1px solid #dee2e6;">
                            $<span id="total_payable_est"><?= number_format((floatval($grand_customer_price) + $total_tax), 2) ?></span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>   
    <script>
        function initAutocomplete(){
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
        }
        $(document).ready(function() {
            initAutocomplete();

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
                            loadCart();
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
                        loadCart();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
                });
            });

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

            $(".grade-cart").each(function() {
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

            $(document).on('change', '.grade-cart', function () {
                const selectedGrade = $(this).val();
                const no = $(this).attr('id').replace('grade', '');
                const colorSelect = $(`#color_cart${no}`);

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
                        dropdownParent: $('#cartTable'),
                        templateResult: formatOption,
                        templateSelection: formatSelected
                    });
                }
            });

        });
    </script>
    <?php
}