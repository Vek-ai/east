<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

$lumber_id = 1;
$trim_id   = 4;
$panel_id  = 3;
$screw_id  = 16;

$staff_id = $_SESSION['userid'] ?? 0;
$show_prod_id_abbrev = 0;
$show_unit_price = 0;
$show_product_price = 0;
$show_total_price = 0;

if ($staff_id) {
    $query = "SELECT setting_key, setting_value 
              FROM staff_settings 
              WHERE staff_id = '$staff_id'";
    $result = $conn->query($query);

    $show_prod_id_abbrev    = 0;
    $show_unique_product_id = 0;

    $show_linear_ft   = 0;
    $show_per_panel   = 0;
    $show_panel_price = 0;

    $show_trim_per_ft   = 0;
    $show_trim_per_each = 0;
    $show_trim_price    = 0;

    $show_screw_per_each = 0;
    $show_screw_per_pack = 0;
    $show_screw_price    = 0;

    $show_each_per_each = 0;
    $show_each_per_pack = 0;
    $show_each_price    = 0;

    $show_retail_price  = 0;
    $show_profile  = 0;
    $show_total_price = 1;
    $show_drag_handle = 0;

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            ${$row['setting_key']} = (int)$row['setting_value'];
        }
    }
}

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

    <script>console.log(<?= print_r($_SESSION['cart']) ?>)</script>
    
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
        .table-fixed td:nth-child(1) { width: 8%; }
        .table-fixed th:nth-child(2),
        .table-fixed td:nth-child(2) { width: 10%; }
        /* .table-fixed th:nth-child(3),
        .table-fixed td:nth-child(3) { width: 8%; }
        .table-fixed th:nth-child(4),
        .table-fixed td:nth-child(4) { width: 8%; }
        .table-fixed th:nth-child(5),
        .table-fixed td:nth-child(5) { width: 8%; }
        .table-fixed th:nth-child(6),
        .table-fixed td:nth-child(6) { width: 8%; }
        .table-fixed th:nth-child(7),
        .table-fixed td:nth-child(7) { width: 8%; }
        .table-fixed th:nth-child(8),
        .table-fixed td:nth-child(8) { width: 7%; }
        .table-fixed th:nth-child(9),
        .table-fixed td:nth-child(9) { width: 7%; }
        .table-fixed th:nth-child(10),
        .table-fixed td:nth-child(10) { width: 7%; }
        .table-fixed th:nth-child(11),
        .table-fixed td:nth-child(11) { width: 7%; }
        .table-fixed th:nth-child(12),
        .table-fixed td:nth-child(12) { width: 7%; } */

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
            padding-top: 20px;  
        }
    </style>

    <div id="customer_cart_section">
        <?php 
            if (!empty($_SESSION["customer_id"])) {
                $customer_id = $_SESSION["customer_id"];
                $customer_details = getCustomerDetails($customer_id);
                $charge_net_30 = floatval($customer_details['charge_net_30'] ?? 0);
                $credit_total = getCustomerCreditTotal($customer_id);
                $store_credit = floatval($customer_details['store_credit'] ?? 0);
                $customer_name = get_customer_name($_SESSION["customer_id"]);
        ?>
            <div class="form-group row align-items-center">
                <div class="d-flex flex-column gap-1">
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
                        <a class="input-group-text rounded-right m-0 p-0 add_new_customer_btn" href="javascript:void(0)">
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
            <table id="cartTable" class="table table-hover table-fixed mb-0">
                <thead>
                    <tr>
                        <th></th>
                        <th>Description</th>
                        <th class="text-center">Color</th>
                        <th class="text-center">Grade</th>
                        <th class="text-center">Gauge</th>
                        <th class="text-center">
                            <span class="<?= $show_profile ? '' : 'd-none' ?>">
                                Profile
                            </span>
                        </th>
                        <th class="text-center pl-3">Quantity</th>
                        <th class="text-center">Length</th>
                        <th class="text-center">Panel Type</th>
                        <th class="text-center">Panel Style</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">
                            <span class="<?= $show_retail_price ? '' : 'd-none' ?>">
                                Retail<br>Price
                            </span>
                        </th>
                        <th class="text-center ">Total<br>Price</th>
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
                        foreach ($_SESSION["cart"] as $values) {
                            $product_id = $values["product_id"];
                            $product_details = getProductDetails($product_id);
                            $category = (int) $product_details['product_category'];
                            $bundle_name = $values['bundle_name'] ?? '';
                            $custom_color = $values['custom_color'];
                            $custom_grade = $values['custom_grade'];
                            $custom_gauge = $values['custom_gauge'];
                            $custom_profile = $values['custom_profile'];

                            $item = calculateCartItem($values);  
                            $product = $item['product'];
                            $line = $item['line'];
                            $quantity = $item['quantity'];
                            $unit_price = $item['unit_price'];
                            $total_length = $item['total_length'];
                            $product_price = $item['product_price'];
                            $customer_price = $item['customer_price'];
                            $drawing_data = $item['drawing_data'];
                            $sold_by_feet = $item['sold_by_feet'];
                            $linear_price =$item['linear_price'];
                            $panel_price = $item['panel_price'];

                            $total_price_actual = $item['product_price'];
                            $total_customer_price = $item['customer_price'];

                            $unique_prod_id = $item['unique_prod_id'];
                            $multiplier = $item['multiplier'];
                            $parent_prod_id = $item['parent_prod_id'];
                            $profile_type = $item['profile'];
                            $stock_text = $item['stock_text'];

                            $profile_details = getProfileTypeDetails($profile_type);
                            $panel_type_1 = $profile_details['panel_type_1'];
                            $panel_type_2 = $profile_details['panel_type_2'];
                            $panel_type_3 = $profile_details['panel_type_3'];

                            $panel_style_1 = $profile_details['panel_style_1'];
                            $panel_style_2 = $profile_details['panel_style_2'];
                            $panel_style_3 = $profile_details['panel_style_3'];

                            if ($category == $panel_id) {
                                ?>
                                <tr class="thick-border" data-line="<?= $line ?>" data-bundle="<?= $bundle_name ?>" data-mult="<?= $multiplier ?>"> 
                                    <td class="text-center align-middle">
                                        <i class="fa fa-bars fa-lg drag-handle <?= $show_drag_handle ? '' : 'd-none' ?>" style="cursor: move; flex-shrink: 0;"></i>
                                        <div class="d-flex align-items-center gap-1 justify-content-start flex-wrap" style="overflow: hidden;">
                                            
                                            <div class="bundle-checkbox-cart d-none flex-shrink-0">
                                                <div class="form-check m-0">
                                                    <input class="form-check-input bundle-checkbox-cart" 
                                                        type="checkbox" 
                                                        data-line="<?= $line; ?>" 
                                                        data-id="<?= $product_id; ?>" 
                                                        value="<?= $line; ?>">
                                                </div>
                                            </div>

                                            <span class="<?= $show_unique_product_id ? '' : 'd-none' ?>" style="flex-grow: 1; overflow: hidden;">
                                                <?= htmlspecialchars($unique_prod_id) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="javascript:void(0);" data-id="<?= $product_id ?>" class="d-flex align-items-center view_product_details">
                                            <h6 class="fw-semibold mb-0 fs-4"><?= $product['product_item'] ?></h6>
                                        </a>
                                        <?php if (!empty($values["note"])): ?>
                                            <span class="text-muted small" style="flex-grow: 1; overflow: hidden;">
                                                Notes: <?= htmlspecialchars($values["note"]) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center align-middle">
                                        <select id="color_cart<?= $line ?>" class="form-control color-cart" 
                                                name="color" onchange="updateColor(this)" 
                                                data-line="<?= $line ?>" data-id="<?= $product_id ?>">
                                            <option value="">Select Color...</option>
                                            <?php
                                            $assigned_colors = getAssignedProductColors($product_id);

                                            if (!empty($custom_color)) {
                                                $custom_color_id = intval($custom_color);
                                                if (!in_array($custom_color_id, $assigned_colors)) {
                                                    $assigned_colors[] = $custom_color_id;
                                                }
                                            }

                                            foreach ($assigned_colors as $color_id) {
                                                $colorDetails = getColorDetails($color_id);
                                                $colorHex = getColorHexFromColorID($color_id);
                                                $colorName = $colorDetails['color_name'] ?? '';
                                                $selected = ($custom_color == $color_id) ? 'selected' : '';
                                                echo "<option value='{$color_id}' data-color='{$colorHex}' data-grade='{$product['grade']}' {$selected}>{$colorName}</option>";
                                            }
                                            ?>
                                        </select>

                                    </td>

                                    <td class="text-center align-middle">
                                        <select id="grade<?= $line ?>" class="form-control grade-cart" 
                                                name="grade" onchange="updateGrade(this)" 
                                                data-line="<?= $line ?>" data-id="<?= $product_id ?>">
                                            <option value="">Select Grade...</option>
                                            <?php
                                            $query_grade = "SELECT * FROM product_grade WHERE status = 1";
                                            $result_grade = mysqli_query($conn, $query_grade);
                                            while ($row_grade = mysqli_fetch_array($result_grade)) {
                                                $selected = ($custom_grade == $row_grade['product_grade_id']) ? 'selected' : '';
                                                echo "<option value='{$row_grade['product_grade_id']}' {$selected}>{$row_grade['product_grade']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>

                                    <td class="text-center align-middle">
                                        <select id="gauge<?= $line ?>" class="form-control gauge-cart" 
                                                name="gauge" onchange="updateGauge(this)" 
                                                data-line="<?= $line ?>" data-id="<?= $product_id ?>">
                                            <option value="">Select Gauge...</option>
                                            <?php
                                            $query_gauge = "SELECT * FROM product_gauge WHERE status = 1";
                                            $result_gauge = mysqli_query($conn, $query_gauge);
                                            while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                                $selected = ($custom_gauge == $row_gauge['product_gauge_id']) ? 'selected' : '';
                                                echo "<option value='{$row_gauge['product_gauge_id']}' {$selected}>{$row_gauge['product_gauge']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>

                                    <td class="text-center align-middle">
                                        <span class="<?= $show_profile ? '' : 'd-none' ?>">
                                            <?= getProfileTypeName($custom_profile) ?>
                                        </span>
                                    </td>

                                    <td class="text-center align-middle">
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
                                                >

                                            <button class="btn btn-primary btn-sm p-1" type="button"
                                                data-line="<?php echo $line; ?>" 
                                                data-id="<?php echo $product_id; ?>" 
                                                onClick="addquantity(this)">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </td>

                                    <td class="text-center align-middle">
                                        <div class="d-flex flex-row align-items-center flex-nowrap w-100 justify-content-center">
                                            <fieldset class="border p-1 d-flex align-items-center flex-nowrap w-100 justify-content-center mb-0">
                                                <div class="input-group d-flex align-items-center flex-nowrap w-100">
                                                    <input class="form-control form-control-sm text-center px-0 flex-fill me-1" 
                                                        type="text" 
                                                        value="<?= round(floatval($values['estimate_length']), 2) ?>" 
                                                        placeholder="FT" 
                                                        data-line="<?= $line; ?>" 
                                                        data-id="<?= $product_id; ?>" 
                                                        onchange="updateEstimateLength(this)">

                                                    <input class="form-control form-control-sm text-center px-0 flex-fill" 
                                                        type="text" 
                                                        value="<?= round(floatval($values['estimate_length_inch']), 2) ?>" 
                                                        placeholder="IN" 
                                                        data-line="<?= $line; ?>" 
                                                        data-id="<?= $product_id; ?>" 
                                                        onchange="updateEstimateLengthInch(this)">
                                                </div>
                                            </fieldset>
                                        </div>
                                    </td>

                                    <td class="text-center align-middle">
                                        <select class="form-control panel_type_cart" name="panel_type" onchange="updatePanelType(this)" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>">
                                            <option value="">Select...</option>
                                            <?php
                                            $panel_types = array_filter([$panel_type_1 ?? '', $panel_type_2 ?? '', $panel_type_3 ?? '']);
                                            $selected_type = $values['panel_type'] ?? '';

                                            if (!empty($panel_types)) {
                                                foreach ($panel_types as $type) {
                                                    $selected = ($selected_type === $type) ? 'selected' : '';
                                                    echo "<option value=\"{$type}\" {$selected}>" . ucwords(str_replace('_', ' ', $type)) . "</option>";
                                                }
                                            } else {
                                                $static_options = [
                                                    'Solid' => 'Solid',
                                                    'Vented' => 'Vented',
                                                    'Drip Stop' => 'Drip Stop'
                                                ];
                                                foreach ($static_options as $val => $label) {
                                                    $selected = ($selected_type === $val) ? 'selected' : '';
                                                    echo "<option value=\"{$val}\" {$selected}>{$label}</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php
                                        $standing_seam = $product["standing_seam"];
                                        $board_batten  = $product["board_batten"];
                                        ?>

                                        <select class="form-control panel_style_cart" name="panel_style" onchange="updatePanelStyle(this)" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>">
                                            <?php if (!empty($standing_seam)): ?>
                                                <option value="striated" <?= $values['panel_style'] == 'striated' ? 'selected' : '' ?>>Striated</option>
                                                <option value="flat" <?= $values['panel_style'] == 'flat' ? 'selected' : '' ?>>Flat</option>
                                                <option value="minor_rib" <?= $values['panel_style'] == 'minor_rib' ? 'selected' : '' ?>>Minor Rib</option>
                                            <?php elseif (!empty($board_batten)): ?>
                                                <option value="flat" <?= $values['panel_style'] == 'flat' ? 'selected' : '' ?>>Flat</option>
                                                <option value="minor_rib" <?= $values['panel_style'] == 'minor_rib' ? 'selected' : '' ?>>Minor Rib</option>
                                            <?php else: ?>
                                                <?php
                                                $panel_styles = array_filter([$panel_style_1 ?? '', $panel_style_2 ?? '', $panel_style_3 ?? '']);
                                                $selected_style = $values['panel_style'] ?? '';

                                                if (!empty($panel_styles)) {
                                                    foreach ($panel_styles as $style) {
                                                        $selected = ($selected_style === $style) ? 'selected' : '';
                                                        echo "<option value=\"{$style}\" {$selected}>" . ucwords(str_replace('_', ' ', $style)) . "</option>";
                                                    }
                                                } 
                                                ?>
                                            <?php endif; ?>
                                        </select>
                                    </td>

                                    <td class="text-center align-middle"><?= $stock_text ?></td>

                                    <td class="text-center align-middle">
                                        <span class="<?= $show_linear_ft ? '' : 'd-none' ?>">
                                            <?php
                                            echo number_format($linear_price, 2);
                                            ?>
                                        </span>
                                    </td>

                                    <td class="text-center align-middle">
                                        <span class="<?= $show_retail_price ? '' : 'd-none' ?>">
                                            $ <?= number_format($total_price_actual,2) ?>
                                        </span>
                                    </td>
                                    <td class="text-center <?= $show_total_price ? '' : 'd-none' ?> align-middle">
                                        $ <?= number_format($total_customer_price,2) ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <a href="javascript:void(0)" data-id="<?= $product_id ?>" data-line="<?= $line ?>" onClick="delete_product(this)"><i class="fa fs-6 fa-trash"></i></a>
                                        <a href="javascript:void(0)" class="text-decoration-none btn-sm duplicate-item-btn" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="duplicate_item(this)">
                                            <i class="fa fa-plus fs-6"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php

                            }else if ($category == $trim_id) {
                                ?>
                                <tr class="thick-border" data-line="<?= $line ?>" data-bundle="<?= $bundle_name ?>" data-mult="<?= $multiplier ?>"> 
                                    <td class="text-center align-middle">
                                        <i class="fa fa-bars fa-lg drag-handle <?= $show_drag_handle ? '' : 'd-none' ?>" style="cursor: move; flex-shrink: 0;"></i>
                                        <div class="d-flex align-items-center gap-1 justify-content-start flex-wrap" style="overflow: hidden;">
                                            
                                            <div class="bundle-checkbox-cart d-none flex-shrink-0">
                                                <div class="form-check m-0">
                                                    <input class="form-check-input bundle-checkbox-cart" 
                                                        type="checkbox" 
                                                        data-line="<?= $line; ?>" 
                                                        data-id="<?= $product_id; ?>" 
                                                        value="<?= $line; ?>">
                                                </div>
                                            </div>

                                            <span class="<?= $show_unique_product_id ? '' : 'd-none' ?>" style="flex-grow: 1; overflow: hidden;">
                                                <?= htmlspecialchars($unique_prod_id) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="javascript:void(0);" data-id="<?= $product_id ?>" class="d-flex align-items-center view_product_details">
                                            <h6 class="fw-semibold mb-0 fs-4"><?= $product['product_item'] ?></h6>
                                        </a>
                                        <?php if (!empty($values["note"])): ?>
                                            <span class="text-muted small" style="flex-grow: 1; overflow: hidden;">
                                                Notes: <?= htmlspecialchars($values["note"]) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center align-middle">
                                        <select id="color_cart<?= $line ?>" class="form-control color-cart" 
                                                name="color" onchange="updateColor(this)" 
                                                data-line="<?= $line ?>" data-id="<?= $product_id ?>">
                                            <option value="">Select Color...</option>
                                            <?php
                                            $assigned_colors = getAssignedProductColors($product_id);

                                            if (!empty($custom_color)) {
                                                $custom_color_id = intval($custom_color);
                                                if (!in_array($custom_color_id, $assigned_colors)) {
                                                    $assigned_colors[] = $custom_color_id;
                                                }
                                            }

                                            foreach ($assigned_colors as $color_id) {
                                                $colorDetails = getColorDetails($color_id);
                                                $colorHex = getColorHexFromColorID($color_id);
                                                $colorName = $colorDetails['color_name'] ?? '';
                                                $selected = ($custom_color == $color_id) ? 'selected' : '';
                                                echo "<option value='{$color_id}' data-color='{$colorHex}' data-grade='{$product['grade']}' {$selected}>{$colorName}</option>";
                                            }
                                            ?>
                                        </select>

                                    </td>

                                    <td class="text-center align-middle">
                                        <select id="grade<?= $line ?>" class="form-control grade-cart" 
                                                name="grade" onchange="updateGrade(this)" 
                                                data-line="<?= $line ?>" data-id="<?= $product_id ?>">
                                            <option value="">Select Grade...</option>
                                            <?php
                                            $query_grade = "SELECT * FROM product_grade WHERE status = 1";
                                            $result_grade = mysqli_query($conn, $query_grade);
                                            while ($row_grade = mysqli_fetch_array($result_grade)) {
                                                $selected = ($custom_grade == $row_grade['product_grade_id']) ? 'selected' : '';
                                                echo "<option value='{$row_grade['product_grade_id']}' {$selected}>{$row_grade['product_grade']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>

                                    <td class="text-center align-middle">
                                        <select id="gauge<?= $line ?>" class="form-control gauge-cart" 
                                                name="gauge" onchange="updateGauge(this)" 
                                                data-line="<?= $line ?>" data-id="<?= $product_id ?>">
                                            <option value="">Select Gauge...</option>
                                            <?php
                                            $query_gauge = "SELECT * FROM product_gauge WHERE status = 1";
                                            $result_gauge = mysqli_query($conn, $query_gauge);
                                            while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                                $selected = ($custom_gauge == $row_gauge['product_gauge_id']) ? 'selected' : '';
                                                echo "<option value='{$row_gauge['product_gauge_id']}' {$selected}>{$row_gauge['product_gauge']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>

                                    <td class="text-center align-middle">
                                        <span class="<?= $show_profile ? '' : 'd-none' ?>">
                                            <?= getProfileTypeName($custom_profile) ?>
                                        </span>
                                    </td>

                                    <td class="text-center align-middle">
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
                                                >

                                            <button class="btn btn-primary btn-sm p-1" type="button"
                                                data-line="<?php echo $line; ?>" 
                                                data-id="<?php echo $product_id; ?>" 
                                                onClick="addquantity(this)">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </td>

                                    <td class="text-center align-middle">
                                        <div class="d-flex flex-row align-items-center flex-nowrap w-100 justify-content-center">
                                            <fieldset class="border p-1 d-flex align-items-center flex-nowrap w-100 justify-content-center mb-0">
                                                <div class="input-group d-flex align-items-center flex-nowrap w-100">
                                                    <input class="form-control form-control-sm text-center px-0 flex-fill me-1" 
                                                        type="text" 
                                                        value="<?= round(floatval($values['estimate_length']), 2) ?>" 
                                                        placeholder="FT" 
                                                        data-line="<?= $line; ?>" 
                                                        data-id="<?= $product_id; ?>" 
                                                        onchange="updateEstimateLength(this)">

                                                    <input class="form-control form-control-sm text-center px-0 flex-fill" 
                                                        type="text" 
                                                        value="<?= round(floatval($values['estimate_length_inch']), 2) ?>" 
                                                        placeholder="IN" 
                                                        data-line="<?= $line; ?>" 
                                                        data-id="<?= $product_id; ?>" 
                                                        onchange="updateEstimateLengthInch(this)">
                                                </div>
                                            </fieldset>
                                        </div>
                                    </td>

                                    <td class="text-center align-middle"></td>
                                    <td class="text-center align-middle"></td>

                                    <td class="text-center align-middle"><?= $stock_text ?></td>

                                    <td class="text-center align-middle">
                                        <span class="<?= $show_linear_ft ? '' : 'd-none' ?>">
                                            <?php
                                            echo number_format($linear_price, 2);
                                            ?>
                                        </span>
                                    </td>

                                    <td class="text-center align-middle">
                                        <span class="<?= $show_retail_price ? '' : 'd-none' ?>">
                                            $ <?= number_format($total_price_actual,2) ?>
                                        </span>
                                    </td>
                                    <td class="text-center <?= $show_total_price ? '' : 'd-none' ?> align-middle">
                                        $ <?= number_format($total_customer_price,2) ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <a href="javascript:void(0)" data-id="<?= $product_id ?>" data-line="<?= $line ?>" onClick="delete_product(this)"><i class="fa fs-6 fa-trash"></i></a>
                                        <a href="javascript:void(0)" class="text-decoration-none btn-sm duplicate-item-btn" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="duplicate_item(this)">
                                            <i class="fa fa-plus fs-6"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php

                            }else if ($category == $screw_id) {
                                ?>
                                <tr class="thick-border" data-line="<?= $line ?>" data-bundle="<?= $bundle_name ?>" data-mult="<?= $multiplier ?>"> 
                                    <td class="text-center align-middle">
                                        <i class="fa fa-bars fa-lg drag-handle <?= $show_drag_handle ? '' : 'd-none' ?>" style="cursor: move; flex-shrink: 0;"></i>
                                        <div class="d-flex align-items-center gap-1 justify-content-start flex-wrap" style="overflow: hidden;">
                                            
                                            <div class="bundle-checkbox-cart d-none flex-shrink-0">
                                                <div class="form-check m-0">
                                                    <input class="form-check-input bundle-checkbox-cart" 
                                                        type="checkbox" 
                                                        data-line="<?= $line; ?>" 
                                                        data-id="<?= $product_id; ?>" 
                                                        value="<?= $line; ?>">
                                                </div>
                                            </div>

                                            <span class="<?= $show_unique_product_id ? '' : 'd-none' ?>" style="flex-grow: 1; overflow: hidden;">
                                                <?= htmlspecialchars($unique_prod_id) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="javascript:void(0);" data-id="<?= $product_id ?>" class="d-flex align-items-center view_product_details">
                                            <h6 class="fw-semibold mb-0 fs-4"><?= $product['product_item'] ?></h6>
                                        </a>
                                        <?php if (!empty($values["note"])): ?>
                                            <span class="text-muted small" style="flex-grow: 1; overflow: hidden;">
                                                Notes: <?= htmlspecialchars($values["note"]) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center align-middle">
                                        <select id="color_cart<?= $line ?>" class="form-control color-cart" 
                                                name="color" onchange="updateColor(this)" 
                                                data-line="<?= $line ?>" data-id="<?= $product_id ?>">
                                            <option value="">Select Color...</option>
                                            <?php
                                            $assigned_colors = getAssignedProductColors($product_id);

                                            if (!empty($custom_color)) {
                                                $custom_color_id = intval($custom_color);
                                                if (!in_array($custom_color_id, $assigned_colors)) {
                                                    $assigned_colors[] = $custom_color_id;
                                                }
                                            }

                                            foreach ($assigned_colors as $color_id) {
                                                $colorDetails = getColorDetails($color_id);
                                                $colorHex = getColorHexFromColorID($color_id);
                                                $colorName = $colorDetails['color_name'] ?? '';
                                                $selected = ($custom_color == $color_id) ? 'selected' : '';
                                                echo "<option value='{$color_id}' data-color='{$colorHex}' data-grade='{$product['grade']}' {$selected}>{$colorName}</option>";
                                            }
                                            ?>
                                        </select>

                                    </td>

                                    <td class="text-center align-middle">
                                        <select id="grade<?= $line ?>" class="form-control grade-cart" 
                                                name="grade" onchange="updateGrade(this)" 
                                                data-line="<?= $line ?>" data-id="<?= $product_id ?>">
                                            <option value="">Select Grade...</option>
                                            <?php
                                            $query_grade = "SELECT * FROM product_grade WHERE status = 1";
                                            $result_grade = mysqli_query($conn, $query_grade);
                                            while ($row_grade = mysqli_fetch_array($result_grade)) {
                                                $selected = ($custom_grade == $row_grade['product_grade_id']) ? 'selected' : '';
                                                echo "<option value='{$row_grade['product_grade_id']}' {$selected}>{$row_grade['product_grade']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>

                                    <td class="text-center align-middle">
                                        <select id="gauge<?= $line ?>" class="form-control gauge-cart" 
                                                name="gauge" onchange="updateGauge(this)" 
                                                data-line="<?= $line ?>" data-id="<?= $product_id ?>">
                                            <option value="">Select Gauge...</option>
                                            <?php
                                            $query_gauge = "SELECT * FROM product_gauge WHERE status = 1";
                                            $result_gauge = mysqli_query($conn, $query_gauge);
                                            while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                                $selected = ($custom_gauge == $row_gauge['product_gauge_id']) ? 'selected' : '';
                                                echo "<option value='{$row_gauge['product_gauge_id']}' {$selected}>{$row_gauge['product_gauge']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>

                                    <td class="text-center align-middle">
                                        <span class="<?= $show_profile ? '' : 'd-none' ?>">
                                            <?= getProfileTypeName($custom_profile) ?>
                                        </span>
                                    </td>

                                    <td class="text-center align-middle">
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
                                                >

                                            <button class="btn btn-primary btn-sm p-1" type="button"
                                                data-line="<?php echo $line; ?>" 
                                                data-id="<?php echo $product_id; ?>" 
                                                onClick="addquantity(this)">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </td>

                                    <?php 
                                    $inventoryItems = getAvailableInventory($product_id);
                                    ?>
                                    <td class="text-center">
                                        <select class="form-control screw_length_cart" 
                                                name="screw_length" 
                                                onchange="updateScrewLength(this)" 
                                                data-line="<?= $line; ?>" 
                                                data-id="<?= $product_id; ?>">
                                            <option value="" hidden>Select Length</option>
                                            <?php foreach ($inventoryItems as $item) { 
                                                $dimension = trim($item['dimension'] ?? '');
                                                $unit      = trim($item['dimension_unit'] ?? '');
                                                
                                                if ($dimension !== '') { 
                                                    $selected = ($values['screw_length'] ?? '') === $dimension ? 'selected' : '';
                                            ?>
                                                    <option 
                                                        value="<?= $item['dimension'] ?>"
                                                        <?= $selected ?>
                                                    >
                                                        <?= htmlspecialchars($dimension) ?> <?= htmlspecialchars($unit) ?>
                                                    </option>
                                            <?php } } ?>
                                        </select>
                                    </td>

                                    <td class="text-center">
                                        <select class="form-control screw_type_cart" 
                                                name="screw_type" 
                                                onchange="updateScrewType(this)" 
                                                data-line="<?= $line; ?>" 
                                                data-id="<?= $product_id; ?>">
                                            <?php 
                                            $screwTypes = [
                                                'SD'  => 'Self-Driller',
                                                'PT'  => 'Pointed-Tip',
                                                'ZXL' => 'ProZ ZXL Long Life',
                                                'STL' => 'Stainless Steel'
                                            ];
                                            $selectedType = $values['screw_type'] ?? '';
                                            ?>
                                            <option value="" hidden>Select Type</option>
                                            <?php foreach ($screwTypes as $key => $label): ?>
                                                <option value="<?= $key ?>" <?= ($selectedType === $key) ? 'selected' : '' ?>>
                                                    <?= $label ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>

                                    <td class="text-center align-middle"></td>

                                    <td class="text-center align-middle"><?= $stock_text ?></td>

                                    <td class="text-center align-middle">
                                        <span class="<?= $show_linear_ft ? '' : 'd-none' ?>">
                                            <?php
                                            echo number_format($linear_price, 2);
                                            ?>
                                        </span>
                                    </td>

                                    <td class="text-center align-middle">
                                        <span class="<?= $show_retail_price ? '' : 'd-none' ?>">
                                            $ <?= number_format($total_price_actual,2) ?>
                                        </span>
                                    </td>
                                    <td class="text-center <?= $show_total_price ? '' : 'd-none' ?> align-middle">
                                        $ <?= number_format($total_customer_price,2) ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <a href="javascript:void(0)" data-id="<?= $product_id ?>" data-line="<?= $line ?>" onClick="delete_product(this)"><i class="fa fs-6 fa-trash"></i></a>
                                        <a href="javascript:void(0)" class="text-decoration-none btn-sm duplicate-item-btn" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="duplicate_item(this)">
                                            <i class="fa fa-plus fs-6"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php

                            }else {
                                ?>
                                <tr class="thick-border" data-line="<?= $line ?>" data-bundle="<?= $bundle_name ?>" data-mult="<?= $multiplier ?>"> 
                                    <td class="text-center align-middle">
                                        <i class="fa fa-bars fa-lg drag-handle <?= $show_drag_handle ? '' : 'd-none' ?>" style="cursor: move; flex-shrink: 0;"></i>
                                        <div class="d-flex align-items-center gap-1 justify-content-start flex-wrap" style="overflow: hidden;">
                                            
                                            <div class="bundle-checkbox-cart d-none flex-shrink-0">
                                                <div class="form-check m-0">
                                                    <input class="form-check-input bundle-checkbox-cart" 
                                                        type="checkbox" 
                                                        data-line="<?= $line; ?>" 
                                                        data-id="<?= $product_id; ?>" 
                                                        value="<?= $line; ?>">
                                                </div>
                                            </div>

                                            <span class="<?= $show_unique_product_id ? '' : 'd-none' ?>" style="flex-grow: 1; overflow: hidden;">
                                                <?= htmlspecialchars($unique_prod_id) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="javascript:void(0);" data-id="<?= $product_id ?>" class="d-flex align-items-center view_product_details">
                                            <h6 class="fw-semibold mb-0 fs-4"><?= $product['product_item'] ?></h6>
                                        </a>
                                        <?php if (!empty($values["note"])): ?>
                                            <span class="text-muted small" style="flex-grow: 1; overflow: hidden;">
                                                Notes: <?= htmlspecialchars($values["note"]) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center align-middle">
                                        <select id="color_cart<?= $line ?>" class="form-control color-cart" 
                                                name="color" onchange="updateColor(this)" 
                                                data-line="<?= $line ?>" data-id="<?= $product_id ?>">
                                            <option value="">Select Color...</option>
                                            <?php
                                            $assigned_colors = getAssignedProductColors($product_id);

                                            if (!empty($custom_color)) {
                                                $custom_color_id = intval($custom_color);
                                                if (!in_array($custom_color_id, $assigned_colors)) {
                                                    $assigned_colors[] = $custom_color_id;
                                                }
                                            }

                                            foreach ($assigned_colors as $color_id) {
                                                $colorDetails = getColorDetails($color_id);
                                                $colorHex = getColorHexFromColorID($color_id);
                                                $colorName = $colorDetails['color_name'] ?? '';
                                                $selected = ($custom_color == $color_id) ? 'selected' : '';
                                                echo "<option value='{$color_id}' data-color='{$colorHex}' data-grade='{$product['grade']}' {$selected}>{$colorName}</option>";
                                            }
                                            ?>
                                        </select>

                                    </td>

                                    <td class="text-center align-middle">
                                        <select id="grade<?= $line ?>" class="form-control grade-cart" 
                                                name="grade" onchange="updateGrade(this)" 
                                                data-line="<?= $line ?>" data-id="<?= $product_id ?>">
                                            <option value="">Select Grade...</option>
                                            <?php
                                            $query_grade = "SELECT * FROM product_grade WHERE status = 1";
                                            $result_grade = mysqli_query($conn, $query_grade);
                                            while ($row_grade = mysqli_fetch_array($result_grade)) {
                                                $selected = ($custom_grade == $row_grade['product_grade_id']) ? 'selected' : '';
                                                echo "<option value='{$row_grade['product_grade_id']}' {$selected}>{$row_grade['product_grade']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>

                                    <td class="text-center align-middle">
                                        <select id="gauge<?= $line ?>" class="form-control gauge-cart" 
                                                name="gauge" onchange="updateGauge(this)" 
                                                data-line="<?= $line ?>" data-id="<?= $product_id ?>">
                                            <option value="">Select Gauge...</option>
                                            <?php
                                            $query_gauge = "SELECT * FROM product_gauge WHERE status = 1";
                                            $result_gauge = mysqli_query($conn, $query_gauge);
                                            while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                                $selected = ($custom_gauge == $row_gauge['product_gauge_id']) ? 'selected' : '';
                                                echo "<option value='{$row_gauge['product_gauge_id']}' {$selected}>{$row_gauge['product_gauge']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>

                                    <td class="text-center align-middle">
                                        <span class="<?= $show_profile ? '' : 'd-none' ?>">
                                            <?= getProfileTypeName($custom_profile) ?>
                                        </span>
                                    </td>

                                    <td class="text-center align-middle">
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
                                                >

                                            <button class="btn btn-primary btn-sm p-1" type="button"
                                                data-line="<?php echo $line; ?>" 
                                                data-id="<?php echo $product_id; ?>" 
                                                onClick="addquantity(this)">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </td>

                                    <?php 
                                    $inventoryItems = getAvailableInventory($product_id);
                                    ?>
                                    <td class="text-center"></td>

                                    <td class="text-center"></td>

                                    <td class="text-center align-middle"></td>

                                    <td class="text-center align-middle"><?= $stock_text ?></td>

                                    <td class="text-center align-middle">
                                        <span class="<?= $show_linear_ft ? '' : 'd-none' ?>">
                                            <?php
                                            echo number_format($linear_price, 2);
                                            ?>
                                        </span>
                                    </td>

                                    <td class="text-center align-middle">
                                        <span class="<?= $show_retail_price ? '' : 'd-none' ?>">
                                            $ <?= number_format($total_price_actual,2) ?>
                                        </span>
                                    </td>
                                    <td class="text-center <?= $show_total_price ? '' : 'd-none' ?> align-middle">
                                        $ <?= number_format($total_customer_price,2) ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <a href="javascript:void(0)" data-id="<?= $product_id ?>" data-line="<?= $line ?>" onClick="delete_product(this)"><i class="fa fs-6 fa-trash"></i></a>
                                        <a href="javascript:void(0)" class="text-decoration-none btn-sm duplicate-item-btn" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="duplicate_item(this)">
                                            <i class="fa fa-plus fs-6"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php

                            }

                            $total_qty           += $item["quantity"];
                            $total_length_cart   += $item["quantity"] * $item["total_length"];
                            $totalquantity       += $item["quantity"];
                            $total_price_actual  += $item["subtotal"];
                            $total_customer_price+= $item["customer_price"];
                            $total_weight        += ($product["weight"] ?? 0) * $item["quantity"];
                            $customer_savings    += $item["savings"];
                            $grand_actual_price  += $item["subtotal"];
                            $grand_customer_price+= $item["customer_price"];
                        }
                    }
                    
                    ?>
                </tbody>

                <tfoot>
                    <?php 
                    if ($is_panel_present) {
                    ?>
                    <tr>
                        <td colspan="13">
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
                        <td colspan="4" class="text-start align-middle">Total Weight <?= number_format(floatval($total_weight), 2) ?> LBS</td>
                        <td></td>
                        <td colspan="2" class="text-center">Total Quantity: <span id="qty_ttl"><?= $totalquantity ?></span></td>
                        <td colspan="1"></td>
                        <td colspan="5" class="text-end">Customer Savings: </td>
                        <td colspan="1" class="text-end"><span id="ammount_due">$<?= number_format($customer_savings,2) ?></span></td>
                        <td colspan="1"></td>
                    </tr>
                    <tr>
                        <th colspan="11" style="border-bottom: none; border-top: none;"></th>
                        <th colspan="2" class="text-end" style="border-bottom: 1px solid #dee2e6;">Materials Price:</th>
                        <td class="text-end" style="border-bottom: 1px solid #dee2e6;">
                            $<span id="total_amt"><?= number_format(floatval($grand_customer_price), 2) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="11" style="border-bottom: none; border-top: none;"></th>
                        <th colspan="2" class="text-end" style="border-bottom: 1px solid #dee2e6;">Sales Tax:</th>
                        <td class="text-end" style="border-bottom: 1px solid #dee2e6;">
                            $<span id="sales_tax"><?= number_format($total_tax = floatval($grand_customer_price) * $tax, 2) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="11" style="border-bottom: none; border-top: none;"></th>
                        <th colspan="2" class="text-end fw-bold" style="border-bottom: 1px solid #dee2e6;">Total Due:</th>
                        <td class="text-end fw-bold" style="border-bottom: 1px solid #dee2e6;">
                            $<span id="total_payable_est"><?= number_format((floatval($grand_customer_price) + $total_tax), 2) ?></span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>   

    <div class="modal fade" id="cartColumnModal" tabindex="-1" aria-labelledby="cartColumnModalLabel" aria-hidden="true" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="cartColumnForm" method="POST" action="pages/cashier_ajax.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cartColumnModalLabel">Cart Content Settings</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="card shadow-sm rounded-3 mb-3">
                            <div class="card-header bg-light border-bottom">
                                <h5 class="mb-0 fw-bold">Drag Handle</h5>
                            </div>
                            <div class="card-body border rounded p-3">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_drag_handle" name="show_drag_handle" value="1" <?php if ($show_prod_id_abbrev) echo 'checked'; ?>>
                                            <label class="form-check-label" for="show_drag_handle">Drag/Drop Handle</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm rounded-3 mb-3">
                            <div class="card-header bg-light border-bottom">
                                <h5 class="mb-0 fw-bold">Product ID</h5>
                            </div>
                            <div class="card-body border rounded p-3">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_prod_id_abbrev" name="show_prod_id_abbrev" value="1" <?php if ($show_prod_id_abbrev) echo 'checked'; ?>>
                                            <label class="form-check-label" for="show_prod_id_abbrev">Parent ID #</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_unique_product_id" name="show_unique_product_id" value="1" <?php if ($show_unique_product_id) echo 'checked'; ?>>
                                            <label class="form-check-label" for="show_unique_product_id">Unique Product ID #</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm rounded-3 mb-3">
                            <div class="card-header bg-light border-bottom">
                                <h5 class="mb-0 fw-bold">Metal Panels</h5>
                            </div>
                            <div class="card-body border rounded p-3">
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_linear_ft" name="show_linear_ft" value="1" <?php if ($show_linear_ft) echo 'checked'; ?>>
                                            <label class="form-check-label" for="show_linear_ft">Linear Ft $</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_per_panel" name="show_per_panel" value="1" <?php if ($show_per_panel) echo 'checked'; ?>>
                                            <label class="form-check-label" for="show_per_panel">Per Panel $</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_panel_price" name="show_panel_price" value="1" <?php if ($show_panel_price) echo 'checked'; ?>>
                                            <label class="form-check-label" for="show_panel_price">Price</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm rounded-3 mb-3">
                            <div class="card-header bg-light border-bottom">
                                <h5 class="mb-0 fw-bold">Trim</h5>
                            </div>
                            <div class="card-body border rounded p-3">
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_trim_per_ft" name="show_trim_per_ft" value="1" <?php if ($show_trim_per_ft) echo 'checked'; ?>>
                                            <label class="form-check-label" for="show_trim_per_ft">Per Ft $</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_trim_per_each" name="show_trim_per_each" value="1" <?php if ($show_trim_per_each) echo 'checked'; ?>>
                                            <label class="form-check-label" for="show_trim_per_each">Per Each $</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_trim_price" name="show_trim_price" value="1" <?php if ($show_trim_price) echo 'checked'; ?>>
                                            <label class="form-check-label" for="show_trim_price">Price</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm rounded-3 mb-3">
                            <div class="card-header bg-light border-bottom">
                                <h5 class="mb-0 fw-bold">Screws</h5>
                            </div>
                            <div class="card-body border rounded p-3">
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_screw_per_each" name="show_screw_per_each" value="1" <?php if ($show_screw_per_each) echo 'checked'; ?>>
                                            <label class="form-check-label" for="show_screw_per_each">Per Screw $</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_screw_per_pack" name="show_screw_per_pack" value="1" <?php if ($show_screw_per_pack) echo 'checked'; ?>>
                                            <label class="form-check-label" for="show_screw_per_pack">Per Pack $</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_screw_price" name="show_screw_price" value="1" <?php if ($show_screw_price) echo 'checked'; ?>>
                                            <label class="form-check-label" for="show_screw_price">Price</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm rounded-3 mb-3">
                            <div class="card-header bg-light border-bottom">
                                <h5 class="mb-0 fw-bold">Each Items</h5>
                            </div>
                            <div class="card-body border rounded p-3">
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_each_per_each" name="show_each_per_each" value="1" <?php if ($show_each_per_each) echo 'checked'; ?>>
                                            <label class="form-check-label" for="show_each_per_each">Per Each $</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_each_per_pack" name="show_each_per_pack" value="1" <?php if ($show_each_per_pack) echo 'checked'; ?>>
                                            <label class="form-check-label" for="show_each_per_pack">Per Pack $</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_each_price" name="show_each_price" value="1" <?php if ($show_each_price) echo 'checked'; ?>>
                                            <label class="form-check-label" for="show_each_price">Price</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm rounded-3 mb-3">
                            <div class="card-header bg-light border-bottom">
                                <h5 class="mb-0 fw-bold">Customer Settings</h5>
                            </div>
                            <div class="card-body border rounded p-3">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_retail_price" name="show_retail_price" value="1" <?php if ($show_retail_price) echo 'checked'; ?>>
                                            <label class="form-check-label" for="show_retail_price">Always Show Retail Price Column</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_profile" name="show_profile" value="1" <?php if ($show_profile) echo 'checked'; ?>>
                                            <label class="form-check-label" for="show_profile">Always Show Profile</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
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

            $('tbody').sortable({
                handle: '.drag-handle',
                axis: 'y',
                placeholder: 'sortable-placeholder',

                start: function (event, ui) {
                    ui.item.data('start-bundle', ui.item.data('bundle') || '');
                },

                stop: function (event, ui) {
                    const $moved = ui.item;
                    let targetBundle = '';

                    const $prevRow = $moved.prev('tr[data-line]');
                    const $nextRow = $moved.next('tr[data-line]');

                    if ($prevRow.length && $prevRow.data('bundle') !== '') {
                        targetBundle = $prevRow.data('bundle');
                    }
                    else if ($nextRow.length && $nextRow.data('bundle') !== '') {
                        targetBundle = $nextRow.data('bundle');
                    }
                    else {
                        targetBundle = '';
                    }

                    $moved.data('bundle', targetBundle);

                    let currentBundle = '';
                    $('tr[data-line]').each(function () {
                        const $row = $(this);
                        const rowBundle = $row.data('bundle') || '';

                        if (rowBundle !== '' && $row.hasClass('bundle-header')) {
                            currentBundle = rowBundle;
                            return;
                        }

                        if (rowBundle === '' && currentBundle !== '') {
                            currentBundle = '';
                        }
                    });

                    const order = [];
                    $('tr[data-line]').each(function () {
                        order.push({
                            line: $(this).data('line'),
                            bundle: $(this).data('bundle') || ''
                        });
                    });

                    $.ajax({
                        url: 'pages/cashier_ajax.php',
                        type: 'POST',
                        data: { reorder_cart: 1, order: order },
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                loadCart();
                            } else {
                                console.error('Error reordering cart', response);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('AJAX error:', error);
                            console.log(xhr.responseText);
                        }
                    });
                }
            });

            const savedData = sessionStorage.getItem('new_customer');
            if (savedData && <?= empty($_SESSION["customer_id"]) ? 'true' : 'false' ?>) {
                const data = JSON.parse(savedData);

                $('#customer_cart_section').html(`
                    <div class="form-group row align-items-center">
                        <div class="d-flex flex-column gap-1">
                            <div>
                                <label class="fw-bold fs-5">Customer Name: ${data.first_name} ${data.last_name}</label>
                                <button class="btn btn-primary btn-sm me-3" type="button" id="customer_change_cart">
                                    <i class="fe fe-reload"></i> Change
                                </button>
                            </div>
                            <div class="d-flex flex-wrap gap-5">
                                <div class="d-flex flex-column align-items-start">
                                    <span class="fw-bold">Charge Net 30 Limit:</span>
                                    <span class="text-primary fs-4 fw-bold">$0.00</span>
                                </div>
                                <div class="d-flex flex-column align-items-start">
                                    <span class="fw-bold">Current Balance Due:</span>
                                    <span class="text-primary fs-4 fw-bold">$0.00</span>
                                </div>
                                <div class="d-flex flex-column align-items-start">
                                    <span class="fw-bold">Available Credit:</span>
                                    <span class="text-primary fs-4 fw-bold">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            }

            $(document).on('click', '#customer_change_cart', function() {
                sessionStorage.removeItem('new_customer');
                $('#customer_cart_section').html(`
                    <div class="form-group row align-items-center">
                        <div class="col-6">
                            <label>Customer Name</label>
                            <div class="input-group">
                                <input class="form-control" placeholder="Search Customer" type="text" id="customer_select_cart">
                                <a class="input-group-text rounded-right m-0 p-0 add_new_customer_btn" href="javascript:void(0)">
                                    <span class="input-group-text"> + </span>
                                </a>
                            </div>
                        </div>
                    </div>
                `);

                initAutocomplete();
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
                    dropdownParent: $('.modal.show'),
                    templateResult: formatOption,
                    language: {
                        noResults: function() {
                            return "No paint color";
                        }
                    }
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
                    dropdownParent: $('.modal.show')
                });
            });

            $(".gauge-cart").each(function() {
                if ($(this).data('select2')) {
                    $(this).select2('destroy');
                }

                $(this).select2({
                    width: '300px',
                    placeholder: "Select...",
                    dropdownAutoWidth: true,
                    dropdownParent: $('.modal.show')
                });
            });

            $(".panel_type_cart").each(function() {
                if ($(this).data('select2')) {
                    $(this).select2('destroy');
                }

                $(this).select2({
                    width: '300px',
                    placeholder: "Select...",
                    dropdownAutoWidth: true,
                    dropdownParent: $('.modal.show')
                });
            });

            $(".panel_style_cart").each(function() {
                if ($(this).data('select2')) {
                    $(this).select2('destroy');
                }

                $(this).select2({
                    width: '300px',
                    placeholder: "Select...",
                    dropdownAutoWidth: true,
                    dropdownParent: $('.modal.show')
                });
            });

            $(".screw_type_cart").each(function() {
                if ($(this).data('select2')) {
                    $(this).select2('destroy');
                }

                $(this).select2({
                    width: '300px',
                    placeholder: "Select...",
                    dropdownAutoWidth: true,
                    dropdownParent: $('.modal.show')
                });
            });

            $(".screw_length_cart").each(function() {
                if ($(this).data('select2')) {
                    $(this).select2('destroy');
                }

                $(this).select2({
                    width: '300px',
                    placeholder: "Select...",
                    dropdownAutoWidth: true,
                    dropdownParent: $('.modal.show')
                });
            });

        });
    </script>
    <?php
}