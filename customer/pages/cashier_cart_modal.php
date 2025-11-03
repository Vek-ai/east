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
$show_prod_id_abbrev = 1;
$show_unit_price = 1;
$show_product_price = 1;
$show_unique_product_id = 1;

$show_linear_ft   = 1;
$show_per_panel   = 1;
$show_panel_price = 1;

$show_trim_per_ft   = 1;
$show_trim_per_each = 1;
$show_trim_price    = 1;

$show_screw_per_each = 1;
$show_screw_per_pack = 1;
$show_screw_price    = 1;

$show_each_per_each = 1;
$show_each_per_pack = 1;
$show_each_price    = 1;

$show_retail_price  = 1;
$show_total_price = 1;

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
        .table-fixed td:nth-child(1) { width: 15%; }
        .table-fixed th:nth-child(2),
        .table-fixed td:nth-child(2) { width: 8%; }
        .table-fixed th:nth-child(3),
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
        .table-fixed td:nth-child(12) { width: 7%; }

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
                <div class="d-flex flex-column gap-2">
                    <div>
                        <label class="fw-bold fs-5">Customer Name: <?= $customer_name ?></label>
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

        <?php } ?>
    </div>
    <input type='hidden' id='customer_id_cart' name="customer_id"/>
    <div class="card-body">
        <div class="product-details table-responsive text-nowrap">
            <table id="cartTable" class="table table-hover table-fixed mb-0">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-center">Color</th>
                        <th class="text-center">Grade</th>
                        <th class="text-center">Gauge</th>
                        <th class="text-center">Profile</th>
                        <th class="text-center pl-3">Quantity</th>
                        <th class="text-center">Length</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">
                            <span class="<?= $show_retail_price ? '' : 'd-none' ?>">
                                Price
                            </span>
                        </th>
                        <th class="text-center ">Customer<br>Price</th>
                        <th class="text-center"> </th>
                    </tr>
                </thead>
                <script>console.log(<?= print_r(getCartDataByCustomerId($customer_id)) ?>)</script>
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

                    $lumber_cart = [];
                    $trim_cart   = [];
                    $panel_cart  = [];
                    $screw_cart  = [];
                    $others_cart = [];
                    
                    $cart = getCartDataByCustomerId($customer_id);
                    
                    if (!empty($cart)) {
                        foreach ($cart as $values) {
                            $pid = $values["product_id"];
                            $product_details = getProductDetails($pid);
                            $category = (int) $product_details['product_category'];

                            $group_fields = [
                                'product_id' => $pid,
                                'color_id'   => (int) ($values['custom_color'] ?? 0),
                                'grade_id'   => (int) ($values['custom_grade'] ?? 0),
                                'gauge_id'   => (int) ($values['custom_gauge'] ?? 0),
                            ];
                            $group_key = implode('_', $group_fields);

                            if ($category === $lumber_id) {
                                $lumber_cart[$group_key][] = $values;
                            } elseif ($category === $trim_id) {
                                $trim_cart[$group_key][] = $values;
                            } elseif ($category === $panel_id) {
                                $panel_cart[$group_key][] = $values;
                            } elseif ($category === $screw_id) {
                                $screw_cart[$group_key][] = $values;
                            } else {
                                $others_cart[$group_key][] = $values;
                            }
                        }

                        foreach ($panel_cart as $group_key => $items) {
                            [$product_id, $color_id, $grade_id, $gauge_id] = explode('_', $group_key);
                            $group_id = uniqid('group_');
                            $total_qty = 0;
                            $total_length_cart = 0;
                            $total_price_actual = 0;
                            $total_customer_price = 0;

                            foreach ($items as $values) {
                                $calc = calculateCartItem($values);

                                $total_qty += $calc['quantity'];
                                $total_length_cart += $calc['quantity'] * $calc['total_length'];
                                $total_price_actual += $calc['product_price'];
                                $total_customer_price += $calc['customer_price'];
                            }

                            $first_calc = calculateCartItem($items[0]);
                            $product = $first_calc['product'];
                            $picture_path = $first_calc['picture_path'];
                            $stock_text = $first_calc['stock_text'];
                            $multiplier = $first_calc['multiplier'];
                            $parent_prod_id = $first_calc['parent_prod_id'];
                            $profile_type = $first_calc['profile'];

                            $profile_details = getProfileTypeDetails($profile_type);
                            $panel_type_1 = $profile_details['panel_type_1'];
                            $panel_type_2 = $profile_details['panel_type_2'];
                            $panel_type_3 = $profile_details['panel_type_3'];

                            $panel_style_1 = $profile_details['panel_style_1'];
                            $panel_style_2 = $profile_details['panel_style_2'];
                            $panel_style_3 = $profile_details['panel_style_3'];
                            ?>

                            <tr class="thick-border" data-mult="<?= $multiplier ?>">
                                <td>
                                    <a href="javascript:void(0);" data-id="<?= $product_id ?>" class="d-flex align-items-center view_product_details">
                                        <h6 class="fw-semibold mb-0 fs-4"><?= htmlspecialchars($items[0]['product_item']) ?></h6>
                                    </a>
                                    <br>
                                    <span class="<?= $show_prod_id_abbrev ? '' : 'd-none' ?>">
                                        <?= htmlspecialchars($parent_prod_id) ?>
                                    <span>
                                </td>

                                <td class="text-center">
                                    <select id="color_cart<?= $items[0]['line'] ?>" class="form-control color-cart" 
                                            name="color" onchange="updateColor(this)" 
                                            data-line="<?= $items[0]['line'] ?>" data-id="<?= $product_id ?>">
                                        <option value="">Select Color...</option>
                                        <?php
                                        $assigned_colors = getAssignedProductColors($product_id);

                                        if (!empty($items[0]['custom_color'])) {
                                            $custom_color_id = intval($items[0]['custom_color']);
                                            if (!in_array($custom_color_id, $assigned_colors)) {
                                                $assigned_colors[] = $custom_color_id;
                                            }
                                        }

                                        foreach ($assigned_colors as $color_id) {
                                            $colorDetails = getColorDetails($color_id);
                                            $colorHex = getColorHexFromColorID($color_id);
                                            $colorName = $colorDetails['color_name'] ?? '';
                                            $selected = ($first_calc['color_id'] == $color_id) ? 'selected' : '';
                                            echo "<option value='{$color_id}' data-color='{$colorHex}' data-grade='{$product['grade']}' {$selected}>{$colorName}</option>";
                                        }
                                        ?>
                                    </select>

                                </td>

                                <td class="text-center">
                                    <select id="grade<?= $items[0]['line'] ?>" class="form-control grade-cart" 
                                            name="grade" onchange="updateGrade(this)" 
                                            data-line="<?= $items[0]['line'] ?>" data-id="<?= $product_id ?>">
                                        <option value="">Select Grade...</option>
                                        <?php
                                        $query_grade = "SELECT * FROM product_grade WHERE status = 1";
                                        $result_grade = mysqli_query($conn, $query_grade);
                                        while ($row_grade = mysqli_fetch_array($result_grade)) {
                                            $selected = ($first_calc['grade'] == $row_grade['product_grade_id']) ? 'selected' : '';
                                            echo "<option value='{$row_grade['product_grade_id']}' {$selected}>{$row_grade['product_grade']}</option>";
                                        }
                                        ?>
                                    </select>
                                </td>

                                <td class="text-center">
                                    <select id="gauge<?= $items[0]['line'] ?>" class="form-control gauge-cart" 
                                            name="gauge" onchange="updateGauge(this)" 
                                            data-line="<?= $items[0]['line'] ?>" data-id="<?= $product_id ?>">
                                        <option value="">Select Gauge...</option>
                                        <?php
                                        $query_gauge = "SELECT * FROM product_gauge WHERE status = 1";
                                        $result_gauge = mysqli_query($conn, $query_gauge);
                                        while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                            $selected = ($first_calc['gauge'] == $row_gauge['product_gauge_id']) ? 'selected' : '';
                                            echo "<option value='{$row_gauge['product_gauge_id']}' {$selected}>{$row_gauge['product_gauge']}</option>";
                                        }
                                        ?>
                                    </select>
                                </td>

                                <td class="text-center"><?= getProfileTypeName($first_calc['profile']) ?></td>

                                <td class="text-center"><?= $total_qty ?></td>

                                <td class="text-center">
                                    <div class="mt-1"><?= number_format($total_length_cart,2) ?> ft</div>
                                </td>

                                <td class="text-center"><?= $stock_text ?></td>

                                <td class="text-center">
                                    <span class="<?= $show_retail_price ? '' : 'd-none' ?>">
                                        $ <?= number_format($total_price_actual,2) ?>
                                    </span>
                                </td>
                                <td class="text-center <?= $show_total_price ? '' : 'd-none' ?>">
                                    $ <?= number_format($total_customer_price,2) ?>
                                </td>
                                <td class="text-center">
                                    <a href="javascript:void(0)" data-id="<?= $product_id ?>" onClick="delete_product(this)"><i class="fa fs-6 fa-trash"></i></a>
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
                                if (!empty($bundle_name)) {
                                    ?>
                                    <tr class="thick-border">
                                        <td class="text-center" colspan="2">Bundle <?= $bundle_count ?></td>
                                        <td class="text-center" colspan="4"></td>
                                        <td class="text-center">Bundle <?= $bundle_count ?></td>
                                        <td class="text-center" colspan="3"></td>
                                    </tr>
                                    <?php
                                }
                                $bundle_count++;
                                ?>
                                <tr class="thick-border d-none bundleCartSection">
                                    <td class="text-center" colspan="11">
                                        <div class="text-end">
                                            <div class="card p-3 mb-0 d-inline-block text-center">
                                                <h6 class="fw-bold">Add Bundle Info</h6>
                                                <div class="mb-2">
                                                    <input type="text" class="form-control form-control-sm bundleNameCart" placeholder="Enter bundle name">
                                                </div>
                                                <button type="button" class="btn btn-success btn-sm w-100 addToBundleCartBtn">
                                                    Add to Bundles
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="thick-border create_bundle_row">
                                    <th class="text-center" colspan="2">
                                        <?php if (!empty($bundle_name)) : ?>
                                            (<?= $bundle_name ?>)
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-primary createBundleCartBtn">
                                                Create Bundles
                                            </button>
                                        <?php endif; ?>
                                    </th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-center">Length</th>
                                    <th class="text-center">Panel Type</th>
                                    <th class="text-center">Panel Style</th>
                                    <th class="text-center">
                                        <span class="<?= $show_linear_ft ? '' : 'd-none' ?>">Linear Ft $</span>
                                    </th>
                                    <th class="text-center">
                                        <span class="<?= $show_per_panel ? '' : 'd-none' ?>">Per Panel $</span>
                                    </th>
                                    <th class="text-center">
                                        <span class="<?= $show_retail_price ? '' : 'd-none' ?>">
                                            Price
                                        </span>
                                    </th>
                                    <th class="text-center <?= $show_panel_price ? '' : 'd-none' ?>">Price</th>
                                    <th class="text-center"></th>
                                </tr>

                                <?php
                                foreach ($bundle_items as $values) {

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
                                    $unique_prod_id = $item['unique_prod_id'];

                                    $bundle_actual_price += $product_price;
                                    $bundle_customer_price += $customer_price;
                                    ?>

                                    <tr data-abbrev="<?= $item['unique_prod_id'] ?>" data-id="<?= $product_id ?>" data-line="<?= $line ?>" data-line="<?= $line ?>" data-bundle="<?= $values['bundle_name'] ?? '' ?>">
                                        <td class="text-start align-middle">
                                            <div class="d-flex align-items-center gap-2 justify-content-start flex-nowrap text-truncate" style="overflow: hidden;">
                                                <i class="fa fa-bars fa-lg drag-handle" style="cursor: move; flex-shrink: 0;"></i>

                                                <div class="bundle-checkbox-cart d-none flex-shrink-0">
                                                    <div class="form-check m-0">
                                                        <input class="form-check-input bundle-checkbox-cart" 
                                                            type="checkbox" 
                                                            data-line="<?= $line; ?>" 
                                                            data-id="<?= $product_id; ?>" 
                                                            value="<?= $line; ?>">
                                                    </div>
                                                </div>

                                                <span class="<?= $show_unique_product_id ? '' : 'd-none' ?> text-truncate" style="flex-grow: 1; overflow: hidden;">
                                                    <?= htmlspecialchars($unique_prod_id) ?>
                                                </span>

                                                <?php if (!empty($values["note"])): ?>
                                                    <span class="text-muted small text-truncate" style="flex-grow: 1; overflow: hidden;">
                                                        Notes: <?= htmlspecialchars($values["note"]) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td></td>
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
                                                        <input class="form-control form-control-sm text-center px-1 flex-fill me-1" 
                                                            type="text" 
                                                            value="<?= round(floatval($values['estimate_length']), 2) ?>" 
                                                            placeholder="FT" 
                                                            data-line="<?= $line; ?>" 
                                                            data-id="<?= $product_id; ?>" 
                                                            onchange="updateEstimateLength(this)">

                                                        <input class="form-control form-control-sm text-center px-1 flex-fill" 
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
                                        <td class="text-center">
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
                                        <td class="text-center">
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
                                        <td class="text-center">
                                            <span class="<?= $show_linear_ft ? '' : 'd-none' ?>">
                                                <?php
                                                echo number_format($linear_price, 2);
                                                ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="<?= $show_per_panel ? '' : 'd-none' ?>">
                                                <?php
                                                echo number_format($panel_price, 2);
                                                ?>
                                            </span>
                                        </td>
                                        <td class="text-center pl-3">
                                            <span class="<?= $show_retail_price ? '' : 'd-none' ?>">
                                                $
                                                <?php
                                                echo number_format($product_price, 2);
                                                ?>
                                            </span>
                                        </td>
                                        
                                        <td class="text-end pl-3 <?= $show_panel_price ? '' : 'd-none' ?>">
                                            <span class="">
                                                $
                                                <?php
                                                echo number_format($customer_price, 2);
                                                ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="javascript:void(0)" 
                                                class="text-decoration-none btn-sm me-1 delete-item-btn" 
                                                data-line="<?php echo $line; ?>" 
                                                data-id="<?php echo $product_id; ?>" 
                                                onClick="delete_item(this)">
                                                <i class="fa fa-trash fs-6"></i>
                                            </a>

                                            <a href="javascript:void(0)" 
                                                class="text-decoration-none btn-sm duplicate-item-btn" 
                                                data-line="<?php echo $line; ?>" 
                                                data-id="<?php echo $product_id; ?>" 
                                                onClick="duplicate_item(this)">
                                                <i class="fa fa-plus fs-6"></i>
                                            </a>

                                            <input type="hidden" class="form-control" data-id="<?php echo $product_id; ?>" id="item_id<?php echo $product_id; ?>" value="<?php echo $values["product_id"]; ?>">
                                            <input class="form-control" type="hidden" size="5" value="<?php echo $values["quantity_ttl"];?>" id="warehouse_stock<?php echo $product_id;?>">
                                            <input class="form-control" type="hidden" size="5" value="<?php echo $line;?>" id="line<?php echo $product_id;?>">
                                            <input class="form-control" type="hidden" size="5" value="<?php echo $values["quantity_in_stock"];?>" id="store_stock<?php echo $product_id;?>">
                                        </td>
                                    </tr>

                                    <?php
                                    $total_qty           += $item["quantity"];
                                    $total_length_cart   += $item["quantity"] * $item["total_length"];
                                    $totalquantity       += $item["quantity"];
                                    $total_price_actual  += $item["subtotal"];
                                    $total_customer_price+= $item["customer_price"];
                                    $total_weight        += ($product["weight"] ?? 0) * $item["quantity"];
                                    $customer_savings    += $item["savings"];
                                    $grand_actual_price  += $item["subtotal"];
                                    $grand_customer_price+= $item["customer_price"];

                                    if ($category_id == $panel_id || $category_id == $trim_id) {
                                        $is_panel_present = 1;
                                    }
                                    ?>
                                <?php
                                }
                            }
                        }

                        foreach ($trim_cart as $group_key => $items) {
                            [$product_id, $color_id, $grade_id, $gauge_id] = explode('_', $group_key);
                            $total_qty = 0;
                            $total_length_cart = 0;
                            $total_price_actual = 0;
                            $total_customer_price = 0;

                            foreach ($items as $values) {
                                $calc = calculateCartItem($values);

                                $total_qty += $calc['quantity'];
                                $total_length_cart += $calc['quantity'] * $calc['total_length'];
                                $total_price_actual += $calc['product_price'];
                                $total_customer_price += $calc['customer_price'];
                            }

                            $first_calc = calculateCartItem($items[0]);
                            $product = $first_calc['product'];
                            $picture_path = $first_calc['picture_path'];
                            $stock_text = $first_calc['stock_text'];
                            $multiplier = $first_calc['multiplier'];
                            $parent_prod_id = $first_calc['parent_prod_id'];
                            ?>

                            <tr class="thick-border" data-mult="<?= $multiplier ?>">
                                <td>
                                    <a href="javascript:void(0);" data-id="<?= $product_id ?>" class="d-flex align-items-center view_product_details">
                                        <h6 class="fw-semibold mb-0 fs-4"><?= htmlspecialchars($items[0]['product_item']) ?></h6>
                                    </a>
                                    <br>
                                    <span class="<?= $show_prod_id_abbrev ? '' : 'd-none' ?>">
                                        <?= htmlspecialchars($parent_prod_id) ?>
                                    <span>
                                </td>

                                <td class="text-center">
                                    <select id="color_cart<?= $items[0]['line'] ?>" class="form-control color-cart" 
                                            name="color" onchange="updateColor(this)" 
                                            data-line="<?= $items[0]['line'] ?>" data-id="<?= $product_id ?>">
                                        <option value="">Select Color...</option>
                                        <?php
                                        $assigned_colors = getAssignedProductColors($product_id);

                                        if (!empty($items[0]['custom_color'])) {
                                            $custom_color_id = intval($items[0]['custom_color']);
                                            if (!in_array($custom_color_id, $assigned_colors)) {
                                                $assigned_colors[] = $custom_color_id;
                                            }
                                        }

                                        foreach ($assigned_colors as $color_id) {
                                            $colorDetails = getColorDetails($color_id);
                                            $colorHex = getColorHexFromColorID($color_id);
                                            $colorName = $colorDetails['color_name'] ?? '';
                                            $selected = ($first_calc['color_id'] == $color_id) ? 'selected' : '';
                                            echo "<option value='{$color_id}' data-color='{$colorHex}' data-grade='{$product['grade']}' {$selected}>{$colorName}</option>";
                                        }
                                        ?>
                                    </select>

                                </td>

                                <td class="text-center">
                                    <select id="grade<?= $items[0]['line'] ?>" class="form-control grade-cart" 
                                            name="grade" onchange="updateGrade(this)" 
                                            data-line="<?= $items[0]['line'] ?>" data-id="<?= $product_id ?>">
                                        <option value="">Select Grade...</option>
                                        <?php
                                        $query_grade = "SELECT * FROM product_grade WHERE status = 1";
                                        $result_grade = mysqli_query($conn, $query_grade);
                                        while ($row_grade = mysqli_fetch_array($result_grade)) {
                                            $selected = ($first_calc['grade'] == $row_grade['product_grade_id']) ? 'selected' : '';
                                            echo "<option value='{$row_grade['product_grade_id']}' {$selected}>{$row_grade['product_grade']}</option>";
                                        }
                                        ?>
                                    </select>
                                </td>

                                <td class="text-center">
                                    <select id="gauge<?= $items[0]['line'] ?>" class="form-control gauge-cart" 
                                            name="gauge" onchange="updateGauge(this)" 
                                            data-line="<?= $items[0]['line'] ?>" data-id="<?= $product_id ?>">
                                        <option value="">Select Gauge...</option>
                                        <?php
                                        $query_gauge = "SELECT * FROM product_gauge WHERE status = 1";
                                        $result_gauge = mysqli_query($conn, $query_gauge);
                                        while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                            $selected = ($first_calc['gauge'] == $row_gauge['product_gauge_id']) ? 'selected' : '';
                                            echo "<option value='{$row_gauge['product_gauge_id']}' {$selected}>{$row_gauge['product_gauge']}</option>";
                                        }
                                        ?>
                                    </select>
                                </td>

                                <td class="text-center"><?= getProfileTypeName($first_calc['profile']) ?></td>

                                <td class="text-center"><?= $total_qty ?></td>

                                <td class="text-center">
                                    <div class="mt-1"><?= number_format($total_length_cart,2) ?> ft</div>
                                </td>

                                <td class="text-center"><?= $stock_text ?></td>

                                <td class="text-center">
                                    <span class="<?= $show_retail_price ? '' : 'd-none' ?>">
                                        $ <?= number_format($total_price_actual,2) ?>
                                    </span>
                                </td>
                                <td class="text-center <?= $show_total_price ? '' : 'd-none' ?>">
                                    $ <?= number_format($total_customer_price,2) ?>
                                </td>

                                <td class="text-center">
                                    <a href="javascript:void(0)" data-id="<?= $product_id ?>" onClick="delete_product(this)"><i class="fa fs-6 fa-trash"></i></a>
                                </td>
                            </tr>

                            <tr class="thick-border create_bundle_row">
                                <th class="text-center" colspan="2"></th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Length</th>
                                <th class="text-center"></th>
                                <th class="text-center"></th>
                                <th class="text-center">
                                    <span class="<?= $show_trim_per_ft ? '' : 'd-none' ?>">Per Ft $</span>
                                </th>
                                <th class="text-center">
                                    <span class="<?= $show_trim_per_each ? '' : 'd-none' ?>">Per Each $</span>
                                </th>
                                <th class="text-center">
                                    <span class="<?= $show_retail_price ? '' : 'd-none' ?>">
                                        Price
                                    </span>
                                </th>
                                <th class="text-center <?= $show_trim_price ? '' : 'd-none' ?>">Price</th>
                                <th class="text-center"></th>
                            </tr>

                            <?php
                            foreach ($items as $values) {
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
                                $unique_prod_id = $item['unique_prod_id'];
                                ?>

                                <tr data-id="<?= $product_id ?>" data-line="<?= $line ?>" data-line="<?= $line ?>" data-bundle="<?= $values['bundle_name'] ?? '' ?>">
                                    <td class="text-start align-middle">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa fa-bars fa-lg drag-handle" style="cursor: move; flex-shrink: 0;"></i>
                                            
                                            <div class="bundle-checkbox-cart d-none">
                                                <div class="form-check m-0">
                                                    <input class="form-check-input bundle-checkbox-cart" 
                                                        type="checkbox" 
                                                        data-line="<?= $line; ?>" 
                                                        data-id="<?= $product_id; ?>" 
                                                        value="<?= $line; ?>">
                                                </div>
                                            </div>

                                            <span class="<?= $show_unique_product_id ? '' : 'd-none' ?>">
                                                <?= $unique_prod_id ?>
                                            </span>

                                            <?php if (!empty($values["note"])){ ?>
                                                <span class="text-muted small">
                                                    Notes: <?= htmlspecialchars($values["note"]) ?>
                                                </span>
                                            <?php } ?>
                                        </div>
                                    </td>
                                    <td></td>
                                    <td class="text-center">
                                        <div class="input-group d-inline-flex align-items-center flex-nowrap w-auto">
                                            <button class="btn btn-primary btn-sm p-1" type="button" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="deductquantity(this)">
                                                <i class="fa fa-minus"></i>
                                            </button>

                                            <input class="form-control form-control-sm text-center mx-0"
                                                type="text"
                                                value="<?= $values['quantity_cart']; ?>"
                                                onchange="updatequantity(this)"
                                                data-line="<?= $line; ?>"
                                                data-id="<?= $product_id; ?>"
                                                >

                                            <button class="btn btn-primary btn-sm p-1" type="button" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="addquantity(this)">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex flex-row align-items-center flex-nowrap w-100 justify-content-center">
                                            <fieldset class="border p-1 d-flex align-items-center flex-nowrap w-100 justify-content-center mb-0">
                                                <div class="input-group d-flex align-items-center flex-nowrap w-100">
                                                    <input class="form-control form-control-sm text-center px-1 flex-fill me-1" 
                                                        type="text" 
                                                        value="<?= round(floatval($values['estimate_length']), 2) ?>" 
                                                        placeholder="FT" 
                                                        data-line="<?= $line; ?>" 
                                                        data-id="<?= $product_id; ?>" 
                                                        onchange="updateEstimateLength(this)">

                                                    <input class="form-control form-control-sm text-center px-1 flex-fill" 
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
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center">
                                        <span class="<?= $show_trim_price ? '' : 'd-none' ?>">
                                            <?php
                                            echo number_format($linear_price, 2);
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="<?= $show_trim_per_each ? '' : 'd-none' ?>">
                                            <?php
                                            echo number_format($panel_price, 2);
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-center pl-3">
                                        <span class=" <?= $show_retail_price ? '' : 'd-none' ?>">
                                            $
                                            <?php
                                            echo number_format($product_price, 2);
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-end pl-3 <?= $show_trim_price ? '' : 'd-none' ?>">
                                        <span class="">
                                            $
                                            <?php
                                            echo number_format($customer_price, 2);
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="javascript:void(0)" class="text-decoration-none btn-sm me-1 delete-item-btn" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="delete_item(this)">
                                            <i class="fa fa-trash fs-6"></i>
                                        </a>
                                        <a href="javascript:void(0)" class="text-decoration-none btn-sm duplicate-item-btn" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="duplicate_item(this)">
                                            <i class="fa fa-plus fs-6"></i>
                                        </a>
                                        
                                    </td>
                                </tr>

                                <?php
                                $total_qty           += $item["quantity"];
                                $total_length_cart   += $item["quantity"] * $item["total_length"];
                                $totalquantity       += $item["quantity"];
                                $total_price_actual  += $item["subtotal"];
                                $total_customer_price+= $item["customer_price"];
                                $total_weight        += ($product["weight"] ?? 0) * $item["quantity"];
                                $customer_savings    += $item["savings"];
                                $grand_actual_price  += $item["subtotal"];
                                $grand_customer_price+= $item["customer_price"];

                                if ($category_id == $panel_id || $category_id == $trim_id) {
                                    $is_panel_present = 1;
                                }
                                ?>
                            <?php
                            }
                        }

                        foreach ($screw_cart as $group_key => $items) {
                            [$product_id, $color_id, $grade_id, $gauge_id] = explode('_', $group_key);
                            $total_qty = 0;
                            $total_length_cart = 0;
                            $total_price_actual = 0;
                            $total_customer_price = 0;

                            foreach ($items as $values) {
                                $calc = calculateCartItem($values);

                                $total_qty += $calc['quantity'];
                                $total_length_cart += $calc['quantity'] * $calc['total_length'];
                                $total_price_actual += $calc['product_price'];
                                $total_customer_price += $calc['customer_price'];
                            }

                            $first_calc = calculateCartItem($items[0]);
                            $product = $first_calc['product'];
                            $picture_path = $first_calc['picture_path'];
                            $stock_text = $first_calc['stock_text'];
                            $multiplier = $first_calc['multiplier'];
                            $product_id_abbrev = $first_calc['parent_prod_id'];
                            ?>

                            <tr class="thick-border" data-mult="<?= $multiplier ?>">
                                <td>
                                    <a href="javascript:void(0);" data-id="<?= $product_id ?>" class="d-flex align-items-center view_product_details">
                                        <h6 class="fw-semibold mb-0 fs-4"><?= htmlspecialchars($items[0]['product_item']) ?></h6>
                                    </a>
                                    <br>
                                    <span class="<?= $show_prod_id_abbrev ? '' : 'd-none' ?>">
                                        <?= htmlspecialchars($product_id_abbrev) ?>
                                    <span>
                                </td>

                                <td class="text-center">
                                    <select id="color_cart<?= $items[0]['line'] ?>" class="form-control color-cart" 
                                            name="color" onchange="updateColor(this)" 
                                            data-line="<?= $items[0]['line'] ?>" data-id="<?= $product_id ?>">
                                        <option value="">Select Color...</option>
                                        <?php
                                        $assigned_colors = getAssignedProductColors($product_id);

                                        if (!empty($items[0]['custom_color'])) {
                                            $custom_color_id = intval($items[0]['custom_color']);
                                            if (!in_array($custom_color_id, $assigned_colors)) {
                                                $assigned_colors[] = $custom_color_id;
                                            }
                                        }

                                        foreach ($assigned_colors as $color_id) {
                                            $colorDetails = getColorDetails($color_id);
                                            $colorHex = getColorHexFromColorID($color_id);
                                            $colorName = $colorDetails['color_name'] ?? '';
                                            $selected = ($first_calc['color_id'] == $color_id) ? 'selected' : '';
                                            echo "<option value='{$color_id}' data-color='{$colorHex}' data-grade='{$product['grade']}' {$selected}>{$colorName}</option>";
                                        }
                                        ?>
                                    </select>

                                </td>

                                <td class="text-center">
                                    <select id="grade<?= $items[0]['line'] ?>" class="form-control grade-cart" 
                                            name="grade" onchange="updateGrade(this)" 
                                            data-line="<?= $items[0]['line'] ?>" data-id="<?= $product_id ?>">
                                        <option value="">Select Grade...</option>
                                        <?php
                                        $query_grade = "SELECT * FROM product_grade WHERE status = 1";
                                        $result_grade = mysqli_query($conn, $query_grade);
                                        while ($row_grade = mysqli_fetch_array($result_grade)) {
                                            $selected = ($first_calc['grade'] == $row_grade['product_grade_id']) ? 'selected' : '';
                                            echo "<option value='{$row_grade['product_grade_id']}' {$selected}>{$row_grade['product_grade']}</option>";
                                        }
                                        ?>
                                    </select>
                                </td>

                                <td class="text-center">
                                    <select id="gauge<?= $items[0]['line'] ?>" class="form-control gauge-cart" 
                                            name="gauge" onchange="updateGauge(this)" 
                                            data-line="<?= $items[0]['line'] ?>" data-id="<?= $product_id ?>">
                                        <option value="">Select Gauge...</option>
                                        <?php
                                        $query_gauge = "SELECT * FROM product_gauge WHERE status = 1";
                                        $result_gauge = mysqli_query($conn, $query_gauge);
                                        while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                            $selected = ($first_calc['gauge'] == $row_gauge['product_gauge_id']) ? 'selected' : '';
                                            echo "<option value='{$row_gauge['product_gauge_id']}' {$selected}>{$row_gauge['product_gauge']}</option>";
                                        }
                                        ?>
                                    </select>
                                </td>

                                <td class="text-center"><?= getProfileTypeName($first_calc['profile']) ?></td>

                                <td class="text-center"><?= $total_qty ?></td>

                                <td class="text-center">
                                    <div class="mt-1"><?= number_format($total_length_cart,2) ?> ft</div>
                                </td>

                                <td class="text-center"><?= $stock_text ?></td>

                                <td class="text-center">
                                    <span class="<?= $show_each_per_each ? '' : 'd-none' ?>">
                                        $ <?= number_format($total_price_actual,2) ?>
                                    </span>
                                </td>
                                <td class="text-center <?= $show_total_price ? '' : 'd-none' ?>">
                                    $ <?= number_format($total_customer_price,2) ?>
                                </td>

                                <td class="text-center">
                                    <a href="javascript:void(0)" data-id="<?= $product_id ?>" onClick="delete_product(this)"><i class="fa fs-6 fa-trash"></i></a>
                                </td>
                            </tr>

                            <tr class="thick-border create_bundle_row">
                                <th class="text-center" colspan="2"></th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Length</th>
                                <th class="text-center">Type</th>
                                <th class="text-center">Pack Size</th>
                                <th class="text-center">
                                    <span class="<?= $show_screw_per_each ? '' : 'd-none' ?>">Per Screw $</span>
                                </th>
                                <th class="text-center">
                                    <span class="<?= $show_screw_per_pack ? '' : 'd-none' ?>">Per Pack $</span>
                                </th>
                                <th class="text-center">
                                    <span class="<?= $show_retail_price ? '' : 'd-none' ?>">
                                        Price
                                    </span>
                                </th>
                                <th class="text-center <?= $show_screw_price ? '' : 'd-none' ?>">Price</th>
                                <th class="text-center"></th>
                            </tr>

                            <?php
                            foreach ($items as $values) {
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
                                $product_id_abbrev = $item['unique_prod_id'];
                                ?>

                                <tr data-id="<?= $product_id ?>" data-line="<?= $line ?>" data-line="<?= $line ?>" data-bundle="<?= $values['bundle_name'] ?? '' ?>">
                                    <td class="text-start align-middle">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa fa-bars fa-lg drag-handle" style="cursor: move; flex-shrink: 0;"></i>

                                            <div class="bundle-checkbox-cart d-none">
                                                <div class="form-check m-0">
                                                    <input class="form-check-input bundle-checkbox-cart" 
                                                        type="checkbox" 
                                                        data-line="<?= $line; ?>" 
                                                        data-id="<?= $product_id; ?>" 
                                                        value="<?= $line; ?>">
                                                </div>
                                            </div>

                                            <span class="<?= $show_unique_product_id ? '' : 'd-none' ?>">
                                                <?= htmlspecialchars($product_id_abbrev) ?>
                                            </span>

                                            <?php if (!empty($values["note"])){ ?>
                                                <span class="text-muted small">
                                                    Notes: <?= htmlspecialchars($values["note"]) ?>
                                                </span>
                                            <?php } ?>
                                        </div>
                                    </td>
                                    <td></td>
                                    <td class="text-center">
                                        <div class="input-group d-inline-flex align-items-center flex-nowrap w-auto">
                                            <button class="btn btn-primary btn-sm p-1" type="button" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="deductquantity(this)">
                                                <i class="fa fa-minus"></i>
                                            </button>

                                            <input class="form-control form-control-sm text-center mx-0"
                                                type="text"
                                                value="<?= $values['quantity_cart']; ?>"
                                                onchange="updatequantity(this)"
                                                data-line="<?= $line; ?>"
                                                data-id="<?= $product_id; ?>"
                                                >

                                            <button class="btn btn-primary btn-sm p-1" type="button" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="addquantity(this)">
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
                                    <td class="text-center">
                                        <div class="d-flex flex-row align-items-center flex-nowrap w-auto">
                                            <fieldset class="border p-1 d-inline-flex align-items-center flex-nowrap">
                                                <div class="input-group d-flex align-items-center flex-nowrap w-100">
                                                    <input class="form-control form-control-sm text-center px-1" 
                                                        type="text" 
                                                        value="<?= round(floatval($values['estimate_length']),2) ?>" 
                                                        placeholder="PCS" 
                                                        style="" 
                                                        data-line="<?= $line; ?>" 
                                                        data-id="<?= $product_id; ?>" 
                                                        onchange="updateEstimateLength(this)">
                                                </div>
                                            </fieldset>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="<?= $show_screw_per_each ? '' : 'd-none' ?>">
                                            <?php
                                            echo number_format($linear_price, 2);
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="<?= $show_screw_per_pack ? '' : 'd-none' ?>">
                                            <?php
                                            echo number_format($panel_price, 2);
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-center pl-3">
                                        <span class="<?= $show_retail_price ? '' : 'd-none' ?>">
                                            $
                                            <?php
                                            echo number_format($product_price, 2);
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-end pl-3 <?= $show_total_price ? '' : 'd-none' ?>">
                                        <span class="">
                                            $
                                            <?php
                                            echo number_format($customer_price, 2);
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="javascript:void(0)" class="text-decoration-none btn-sm me-1 delete-item-btn" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="delete_item(this)">
                                            <i class="fa fa-trash fs-6"></i>
                                        </a>
                                        <a href="javascript:void(0)" class="text-decoration-none btn-sm duplicate-item-btn" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="duplicate_item(this)">
                                            <i class="fa fa-plus fs-6"></i>
                                        </a>
                                        
                                    </td>
                                </tr>

                                <?php
                                $total_qty           += $item["quantity"];
                                $total_length_cart   += $item["quantity"] * $item["total_length"];
                                $totalquantity       += $item["quantity"];
                                $total_price_actual  += $item["subtotal"];
                                $total_customer_price+= $item["customer_price"];
                                $total_weight        += ($product["weight"] ?? 0) * $item["quantity"];
                                $customer_savings    += $item["savings"];
                                $grand_actual_price  += $item["subtotal"];
                                $grand_customer_price+= $item["customer_price"];
                                ?>
                            <?php
                            }
                        }

                        foreach ($lumber_cart as $group_key => $items) {
                            [$product_id, $color_id, $grade_id, $gauge_id] = explode('_', $group_key);
                            $total_qty = 0;
                            $total_length_cart = 0;
                            $total_price_actual = 0;
                            $total_customer_price = 0;

                            foreach ($items as $values) {
                                $calc = calculateCartItem($values);

                                $total_qty += $calc['quantity'];
                                $total_length_cart += $calc['quantity'] * $calc['total_length'];
                                $total_price_actual += $calc['product_price'];
                                $total_customer_price += $calc['customer_price'];
                            }

                            $first_calc = calculateCartItem($items[0]);
                            $product = $first_calc['product'];
                            $picture_path = $first_calc['picture_path'];
                            $stock_text = $first_calc['stock_text'];
                            $multiplier = $first_calc['multiplier'];
                            $product_id_abbrev = $first_calc['parent_prod_id'];
                            ?>

                            <tr class="thick-border" data-mult="<?= $multiplier ?>">
                                <td>
                                    <a href="javascript:void(0);" data-id="<?= $product_id ?>" class="d-flex align-items-center view_product_details">
                                        <h6 class="fw-semibold mb-0 fs-4"><?= htmlspecialchars($items[0]['product_item']) ?></h6>
                                    </a>
                                    <br>
                                    <span class="<?= $show_prod_id_abbrev ? '' : 'd-none' ?>">
                                        <?= htmlspecialchars($product_id_abbrev) ?>
                                    <span>
                                </td>

                                <td class="text-center">
                                    <select id="color_cart<?= $items[0]['line'] ?>" class="form-control color-cart" 
                                            name="color" onchange="updateColor(this)" 
                                            data-line="<?= $items[0]['line'] ?>" data-id="<?= $product_id ?>">
                                        <option value="">Select Color...</option>
                                        <?php
                                        $assigned_colors = getAssignedProductColors($product_id);

                                        if (!empty($items[0]['custom_color'])) {
                                            $custom_color_id = intval($items[0]['custom_color']);
                                            if (!in_array($custom_color_id, $assigned_colors)) {
                                                $assigned_colors[] = $custom_color_id;
                                            }
                                        }

                                        foreach ($assigned_colors as $color_id) {
                                            $colorDetails = getColorDetails($color_id);
                                            $colorHex = getColorHexFromColorID($color_id);
                                            $colorName = $colorDetails['color_name'] ?? '';
                                            $selected = ($first_calc['color_id'] == $color_id) ? 'selected' : '';
                                            echo "<option value='{$color_id}' data-color='{$colorHex}' data-grade='{$product['grade']}' {$selected}>{$colorName}</option>";
                                        }
                                        ?>
                                    </select>

                                </td>

                                <td class="text-center">
                                    <select id="grade<?= $items[0]['line'] ?>" class="form-control grade-cart" 
                                            name="grade" onchange="updateGrade(this)" 
                                            data-line="<?= $items[0]['line'] ?>" data-id="<?= $product_id ?>">
                                        <option value="">Select Grade...</option>
                                        <?php
                                        $query_grade = "SELECT * FROM product_grade WHERE status = 1";
                                        $result_grade = mysqli_query($conn, $query_grade);
                                        while ($row_grade = mysqli_fetch_array($result_grade)) {
                                            $selected = ($first_calc['grade'] == $row_grade['product_grade_id']) ? 'selected' : '';
                                            echo "<option value='{$row_grade['product_grade_id']}' {$selected}>{$row_grade['product_grade']}</option>";
                                        }
                                        ?>
                                    </select>
                                </td>

                                <td class="text-center">
                                    <select id="gauge<?= $items[0]['line'] ?>" class="form-control gauge-cart" 
                                            name="gauge" onchange="updateGauge(this)" 
                                            data-line="<?= $items[0]['line'] ?>" data-id="<?= $product_id ?>">
                                        <option value="">Select Gauge...</option>
                                        <?php
                                        $query_gauge = "SELECT * FROM product_gauge WHERE status = 1";
                                        $result_gauge = mysqli_query($conn, $query_gauge);
                                        while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                            $selected = ($first_calc['gauge'] == $row_gauge['product_gauge_id']) ? 'selected' : '';
                                            echo "<option value='{$row_gauge['product_gauge_id']}' {$selected}>{$row_gauge['product_gauge']}</option>";
                                        }
                                        ?>
                                    </select>
                                </td>

                                <td class="text-center"><?= getProfileTypeName($first_calc['profile']) ?></td>

                                <td class="text-center"><?= $total_qty ?></td>

                                <td class="text-center">
                                    <div class="mt-1"><?= number_format($total_length_cart,2) ?> ft</div>
                                </td>

                                <td class="text-center"><?= $stock_text ?></td>

                                <td class="text-center">
                                    <span class="<?= $show_retail_price ? '' : 'd-none' ?>">
                                        $ <?= number_format($total_price_actual,2) ?>
                                    </span>
                                </td>
                                <td class="text-center <?= $show_total_price ? '' : 'd-none' ?>">
                                    $ <?= number_format($total_customer_price,2) ?>
                                </td>

                                <td class="text-center">
                                    <a href="javascript:void(0)" data-id="<?= $product_id ?>" onClick="delete_product(this)"><i class="fa fs-6 fa-trash"></i></a>
                                </td>
                            </tr>

                            <tr class="thick-border create_bundle_row">
                                <th class="text-center" colspan="2"></th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Qty in Pack</th>
                                <th class="text-center"></th>
                                <th class="text-center">Pack Size</th>
                                <th class="text-center">
                                    <span class="<?= $show_each_per_each ? '' : 'd-none' ?>">Per Each $</span>
                                </th>
                                <th class="text-center">
                                    <span class="<?= $show_each_per_pack ? '' : 'd-none' ?>">Per Pack $</span>
                                </th>
                                <th class="text-center">
                                    <span class="<?= $show_retail_price ? '' : 'd-none' ?>">
                                        Price
                                    </span>
                                </th>
                                <th class="text-center <?= $show_each_price ? '' : 'd-none' ?>">Price</th>
                                <th class="text-center"></th>
                            </tr>

                            <?php
                            foreach ($items as $values) {
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
                                $product_id_abbrev = $item['unique_prod_id'];
                                ?>

                                <tr data-id="<?= $product_id ?>" data-line="<?= $line ?>" data-line="<?= $line ?>" data-bundle="<?= $values['bundle_name'] ?? '' ?>">
                                    
                                    <td class="text-start align-middle">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa fa-bars fa-lg drag-handle" style="cursor: move; flex-shrink: 0;"></i>
                                            
                                            <div class="bundle-checkbox-cart d-none">
                                                <div class="form-check m-0">
                                                    <input class="form-check-input bundle-checkbox-cart" 
                                                        type="checkbox" 
                                                        data-line="<?= $line; ?>" 
                                                        data-id="<?= $product_id; ?>" 
                                                        value="<?= $line; ?>">
                                                </div>
                                            </div>

                                            <span class="<?= $show_unique_product_id ? '' : 'd-none' ?>">
                                                <?= htmlspecialchars($product_id_abbrev) ?>
                                            </span>

                                            <?php if (!empty($values["note"])){ ?>
                                                <span class="text-muted small">
                                                    Notes: <?= htmlspecialchars($values["note"]) ?>
                                                </span>
                                            <?php } ?>
                                        </div>
                                    </td>
                                    <td></td>
                                    <td class="text-center">
                                        <div class="input-group d-inline-flex align-items-center flex-nowrap w-auto">
                                            <button class="btn btn-primary btn-sm p-1" type="button" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="deductquantity(this)">
                                                <i class="fa fa-minus"></i>
                                            </button>

                                            <input class="form-control form-control-sm text-center mx-0"
                                                type="text"
                                                value="<?= $values['quantity_cart']; ?>"
                                                onchange="updatequantity(this)"
                                                data-line="<?= $line; ?>"
                                                data-id="<?= $product_id; ?>"
                                                >

                                            <button class="btn btn-primary btn-sm p-1" type="button" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="addquantity(this)">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-row align-items-center flex-nowrap w-auto">
                                            <fieldset class="border p-1 d-inline-flex align-items-center flex-nowrap">
                                                <div class="input-group d-flex align-items-center flex-nowrap w-100">
                                                    <input class="form-control form-control-sm text-center px-1" 
                                                        type="text" 
                                                        value="<?= round(floatval($values['estimate_length']),2) ?>" 
                                                        placeholder="PCS" 
                                                        style="" 
                                                        data-line="<?= $line; ?>" 
                                                        data-id="<?= $product_id; ?>" 
                                                        onchange="updateEstimateLength(this)">
                                                </div>
                                            </fieldset>
                                        </div>
                                    </td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center">
                                        <span class="<?= $show_unit_price ? '' : 'd-none' ?>">
                                            <?php
                                            echo number_format($linear_price, 2);
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="<?= $show_each_per_each ? '' : 'd-none' ?>">
                                            <?php
                                            echo number_format($panel_price, 2);
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-center pl-3 <?= $show_each_per_pack ? '' : 'd-none' ?>">
                                        <span class="">
                                            $
                                            <?php
                                            echo number_format($product_price, 2);
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-end pl-3 <?= $show_each_price ? '' : 'd-none' ?>">
                                        <span class="">
                                            $
                                            <?php
                                            echo number_format($customer_price, 2);
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="javascript:void(0)" class="text-decoration-none btn-sm me-1 delete-item-btn" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="delete_item(this)">
                                            <i class="fa fa-trash fs-6"></i>
                                        </a>
                                        <a href="javascript:void(0)" class="text-decoration-none btn-sm duplicate-item-btn" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="duplicate_item(this)">
                                            <i class="fa fa-plus fs-6"></i>
                                        </a>
                                        
                                    </td>
                                </tr>

                                <?php
                                $total_qty           += $item["quantity"];
                                $total_length_cart   += $item["quantity"] * $item["total_length"];
                                $totalquantity       += $item["quantity"];
                                $total_price_actual  += $item["subtotal"];
                                $total_customer_price+= $item["customer_price"];
                                $total_weight        += ($product["weight"] ?? 0) * $item["quantity"];
                                $customer_savings    += $item["savings"];
                                $grand_actual_price  += $item["subtotal"];
                                $grand_customer_price+= $item["customer_price"];
                                ?>
                            <?php
                            }
                        }

                        foreach ($others_cart as $group_key => $items) {
                            [$product_id, $color_id, $grade_id, $gauge_id] = explode('_', $group_key);
                            $total_qty = 0;
                            $total_length_cart = 0;
                            $total_price_actual = 0;
                            $total_customer_price = 0;

                            foreach ($items as $values) {
                                $calc = calculateCartItem($values);

                                $total_qty += $calc['quantity'];
                                $total_length_cart += $calc['quantity'] * $calc['total_length'];
                                $total_price_actual += $calc['product_price'];
                                $total_customer_price += $calc['customer_price'];
                            }

                            $first_calc = calculateCartItem($items[0]);
                            $product = $first_calc['product'];
                            $picture_path = $first_calc['picture_path'];
                            $stock_text = $first_calc['stock_text'];
                            $multiplier = $first_calc['multiplier'];
                            $product_id_abbrev = $first_calc['parent_prod_id'];
                            ?>

                            <tr class="thick-border" data-mult="<?= $multiplier ?>">
                                <td>
                                    <a href="javascript:void(0);" data-id="<?= $product_id ?>" class="d-flex align-items-center view_product_details">
                                        <h6 class="fw-semibold mb-0 fs-4"><?= htmlspecialchars($items[0]['product_item']) ?></h6>
                                    </a>
                                    <br>
                                    <span class="<?= $show_prod_id_abbrev ? '' : 'd-none' ?>">
                                        <?= htmlspecialchars($product_id_abbrev) ?>
                                    <span>
                                </td>

                                <td class="text-center">
                                    <select id="color_cart<?= $items[0]['line'] ?>" class="form-control color-cart" 
                                            name="color" onchange="updateColor(this)" 
                                            data-line="<?= $items[0]['line'] ?>" data-id="<?= $product_id ?>">
                                        <option value="">Select Color...</option>
                                        <?php
                                        $assigned_colors = getAssignedProductColors($product_id);

                                        if (!empty($items[0]['custom_color'])) {
                                            $custom_color_id = intval($items[0]['custom_color']);
                                            if (!in_array($custom_color_id, $assigned_colors)) {
                                                $assigned_colors[] = $custom_color_id;
                                            }
                                        }

                                        foreach ($assigned_colors as $color_id) {
                                            $colorDetails = getColorDetails($color_id);
                                            $colorHex = getColorHexFromColorID($color_id);
                                            $colorName = $colorDetails['color_name'] ?? '';
                                            $selected = ($first_calc['color_id'] == $color_id) ? 'selected' : '';
                                            echo "<option value='{$color_id}' data-color='{$colorHex}' data-grade='{$product['grade']}' {$selected}>{$colorName}</option>";
                                        }
                                        ?>
                                    </select>

                                </td>

                                <td class="text-center">
                                    <select id="grade<?= $items[0]['line'] ?>" class="form-control grade-cart" 
                                            name="grade" onchange="updateGrade(this)" 
                                            data-line="<?= $items[0]['line'] ?>" data-id="<?= $product_id ?>">
                                        <option value="">Select Grade...</option>
                                        <?php
                                        $query_grade = "SELECT * FROM product_grade WHERE status = 1";
                                        $result_grade = mysqli_query($conn, $query_grade);
                                        while ($row_grade = mysqli_fetch_array($result_grade)) {
                                            $selected = ($first_calc['grade'] == $row_grade['product_grade_id']) ? 'selected' : '';
                                            echo "<option value='{$row_grade['product_grade_id']}' {$selected}>{$row_grade['product_grade']}</option>";
                                        }
                                        ?>
                                    </select>
                                </td>

                                <td class="text-center">
                                    <select id="gauge<?= $items[0]['line'] ?>" class="form-control gauge-cart" 
                                            name="gauge" onchange="updateGauge(this)" 
                                            data-line="<?= $items[0]['line'] ?>" data-id="<?= $product_id ?>">
                                        <option value="">Select Gauge...</option>
                                        <?php
                                        $query_gauge = "SELECT * FROM product_gauge WHERE status = 1";
                                        $result_gauge = mysqli_query($conn, $query_gauge);
                                        while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                            $selected = ($first_calc['gauge'] == $row_gauge['product_gauge_id']) ? 'selected' : '';
                                            echo "<option value='{$row_gauge['product_gauge_id']}' {$selected}>{$row_gauge['product_gauge']}</option>";
                                        }
                                        ?>
                                    </select>
                                </td>

                                <td class="text-center"><?= getProfileTypeName($first_calc['profile']) ?></td>

                                <td class="text-center"><?= $total_qty ?></td>

                                <td class="text-center">
                                    <div class="mt-1"><?= number_format($total_length_cart,2) ?> ft</div>
                                </td>

                                <td class="text-center"><?= $stock_text ?></td>

                                <td class="text-center">
                                    <span class="<?= $show_retail_price ? '' : 'd-none' ?>">
                                        $ <?= number_format($total_price_actual,2) ?>
                                    </span>
                                </td>
                                <td class="text-center <?= $show_total_price ? '' : 'd-none' ?>">
                                    $ <?= number_format($total_customer_price,2) ?>
                                </td>

                                <td class="text-center">
                                    <a href="javascript:void(0)" data-id="<?= $product_id ?>" onClick="delete_product(this)"><i class="fa fs-6 fa-trash"></i></a>
                                </td>
                            </tr>

                            <tr class="thick-border create_bundle_row">
                                <th class="text-center" colspan="2"></th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Qty in Pack</th>
                                <th class="text-center"></th>
                                <th class="text-center">Pack Size</th>
                                <th class="text-center">
                                    <span class="<?= $show_each_per_each ? '' : 'd-none' ?>">Per Each $</span>
                                </th>
                                <th class="text-center">
                                    <span class="<?= $show_each_per_pack ? '' : 'd-none' ?>">Per Pack $</span>
                                </th>
                                <th class="text-center">
                                    <span class="<?= $show_retail_price ? '' : 'd-none' ?>">
                                        Price
                                    </span>
                                </th>
                                <th class="text-center <?= $show_each_price ? '' : 'd-none' ?>">Price</th>
                                <th class="text-center"></th>
                            </tr>

                            <?php
                            foreach ($items as $values) {
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
                                $product_id_abbrev = $item['unique_prod_id'];
                                ?>

                                <tr data-id="<?= $product_id ?>" data-line="<?= $line ?>" data-line="<?= $line ?>" data-bundle="<?= $values['bundle_name'] ?? '' ?>">
                                    
                                    <td class="text-start align-middle">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa fa-bars fa-lg drag-handle" style="cursor: move; flex-shrink: 0;"></i>
                                            
                                            <div class="bundle-checkbox-cart d-none">
                                                <div class="form-check m-0">
                                                    <input class="form-check-input bundle-checkbox-cart" 
                                                        type="checkbox" 
                                                        data-line="<?= $line; ?>" 
                                                        data-id="<?= $product_id; ?>" 
                                                        value="<?= $line; ?>">
                                                </div>
                                            </div>

                                            <span class="<?= $show_unique_product_id ? '' : 'd-none' ?>">
                                                <?= htmlspecialchars($product_id_abbrev) ?>
                                            </span>

                                            <?php if (!empty($values["note"])){ ?>
                                                <span class="text-muted small">
                                                    Notes: <?= htmlspecialchars($values["note"]) ?>
                                                </span>
                                            <?php } ?>
                                        </div>
                                    </td>
                                    <td></td>
                                    <td class="text-center">
                                        <div class="input-group d-inline-flex align-items-center flex-nowrap w-auto">
                                            <button class="btn btn-primary btn-sm p-1" type="button" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="deductquantity(this)">
                                                <i class="fa fa-minus"></i>
                                            </button>

                                            <input class="form-control form-control-sm text-center mx-0"
                                                type="text"
                                                value="<?= $values['quantity_cart']; ?>"
                                                onchange="updatequantity(this)"
                                                data-line="<?= $line; ?>"
                                                data-id="<?= $product_id; ?>"
                                                >

                                            <button class="btn btn-primary btn-sm p-1" type="button" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="addquantity(this)">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-row align-items-center flex-nowrap w-auto">
                                            <fieldset class="border p-1 d-inline-flex align-items-center flex-nowrap">
                                                <div class="input-group d-flex align-items-center flex-nowrap w-100">
                                                    <input class="form-control form-control-sm text-center px-1" 
                                                        type="text" 
                                                        value="<?= round(floatval($values['estimate_length']),2) ?>" 
                                                        placeholder="PCS" 
                                                        style="" 
                                                        data-line="<?= $line; ?>" 
                                                        data-id="<?= $product_id; ?>" 
                                                        onchange="updateEstimateLength(this)">
                                                </div>
                                            </fieldset>
                                        </div>
                                    </td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center">
                                        <span class="<?= $show_each_per_each ? '' : 'd-none' ?>">
                                            <?php
                                            echo number_format($linear_price, 2);
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="<?= $show_each_per_pack ? '' : 'd-none' ?>">
                                            <?php
                                            echo number_format($panel_price, 2);
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-center pl-3">
                                        <span class="<?= $show_retail_price ? '' : 'd-none' ?>">
                                            $
                                            <?php
                                            echo number_format($product_price, 2);
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-end pl-3 <?= $show_each_price  ? '' : 'd-none' ?>">
                                        <span class="">
                                            $
                                            <?php
                                            echo number_format($customer_price, 2);
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="javascript:void(0)" class="text-decoration-none btn-sm me-1 delete-item-btn" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="delete_item(this)">
                                            <i class="fa fa-trash fs-6"></i>
                                        </a>
                                        <a href="javascript:void(0)" class="text-decoration-none btn-sm duplicate-item-btn" data-line="<?= $line; ?>" data-id="<?= $product_id; ?>" onClick="duplicate_item(this)">
                                            <i class="fa fa-plus fs-6"></i>
                                        </a>
                                        
                                    </td>
                                </tr>

                                <?php
                                $total_qty           += $item["quantity"];
                                $total_length_cart   += $item["quantity"] * $item["total_length"];
                                $totalquantity       += $item["quantity"];
                                $total_price_actual  += $item["subtotal"];
                                $total_customer_price+= $item["customer_price"];
                                $total_weight        += ($product["weight"] ?? 0) * $item["quantity"];
                                $customer_savings    += $item["savings"];
                                $grand_actual_price  += $item["subtotal"];
                                $grand_customer_price+= $item["customer_price"];
                                ?>
                            <?php
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
                        <td colspan="2" class="text-start align-middle">Total Weight <?= number_format(floatval($total_weight), 2) ?> LBS</td>
                        <td></td>
                        <td colspan="2" class="text-center">Total Quantity: <span id="qty_ttl"><?= $totalquantity ?></span></td>
                        <td colspan="1"></td>
                        <td colspan="3" class="text-end">Customer Savings: </td>
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
        $(document).ready(function() {
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

        });
    </script>
    <?php
}