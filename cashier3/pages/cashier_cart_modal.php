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
    $show_disc_price = 0;
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

        $show_disc_price = 1;
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
            padding-top: 20px;  
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

            <div class="form-group row align-items-center">
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
                        <th class="text-center <?= $show_disc_price ? 'd-none price_col' : '' ?>">Price</th>
                        <th class="text-center <?= $show_disc_price ? 'customer_price_col' : 'd-none' ?>" style="cursor: pointer;">Customer<br>Price</th>
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
                        $panel_cart = []; 

                        foreach ($_SESSION["cart"] as $values) {
                            $pid = $values["product_id"];
                            $product_details = getProductDetails($pid);
                            $category = $product_details['product_category'];
                            if ($category != $panel_id) continue;

                            if (!isset($panel_cart[$pid])) {
                                $panel_cart[$pid] = [];
                            }
                            $panel_cart[$pid][] = $values;
                        }

                        foreach ($panel_cart as $product_id => $items) {
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
                            ?>

                            <tr class="thick-border" data-mult="<?= $multiplier ?>">
                                <td class="text-center">
                                    <img src="<?= $picture_path ?>" class="rounded-circle" width="56" height="56" alt="product-img">
                                </td>
                                <td>
                                    <a href="javascript:void(0);" data-id="<?= $product_id ?>" class="d-flex align-items-center view_product_details">
                                        <h6 class="fw-semibold mb-0 fs-4"><?= htmlspecialchars($items[0]['product_item']) ?></h6>
                                    </a>
                                </td>

                                <td class="text-center">
                                    <select id="color_cart<?= $items[0]['line'] ?>" class="form-control color-cart" 
                                            name="color" onchange="updateColor(this)" 
                                            data-line="<?= $items[0]['line'] ?>" data-id="<?= $product_id ?>">
                                        <option value="">Select Color...</option>
                                        <?php
                                        $query_colors = "SELECT Product_id, color_id FROM inventory WHERE Product_id = '$product_id'";
                                        $result_colors = mysqli_query($conn, $query_colors);
                                        $inventory_color_ids = [];
                                        while ($row_colors = mysqli_fetch_array($result_colors)) {
                                            $inventory_color_ids[] = $row_colors['color_id'];
                                            $selected = ($first_calc['color_id'] == $row_colors['color_id']) ? 'selected' : '';
                                            $colorDetails = getColorDetails($row_colors['color_id']);
                                            $colorHex = getColorHexFromColorID($row_colors['color_id']);
                                            $colorName = $colorDetails['color_name'] ?? '';
                                            echo "<option value='{$row_colors['color_id']}' data-color='{$colorHex}' data-grade='{$product['grade']}' {$selected}>{$colorName}</option>";
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

                                <td class="text-center"><?= getProfileTypeName($first_calc['profile']) ?></td>

                                <td class="text-center"><?= $total_qty ?></td>

                                <td class="text-center">
                                    <div class="mt-1"><?= number_format($total_length_cart,2) ?> ft</div>
                                </td>

                                <td class="text-center"><?= $stock_text ?></td>

                                <td class="text-center <?= $show_disc_price ? 'd-none price_col' : '' ?>">
                                    $ <?= number_format($total_price_actual,2) ?>
                                </td>
                                <td class="text-center <?= $show_disc_price ? '' : 'd-none' ?>">
                                    $ <?= number_format($total_customer_price,2) ?>
                                </td>

                                <td class="text-center">
                                    <a href="javascript:void(0)" class="text-decoration-none me-2 toggleSortBtn" data-id="<?= $product_id ?>"><i class="fa fs-6 fa-sort"></i></a>
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
                                        <td class="text-center" colspan="3">Bundle <?= $bundle_count ?></td>
                                        <td class="text-center" colspan="4"></td>
                                        <td class="text-center">Bundle <?= $bundle_count ?></td>
                                        <td class="text-center" colspan="3"></td>
                                    </tr>
                                    <?php
                                }
                                $bundle_count++;
                                ?>
                                <tr class="thick-border d-none bundleCartSection">
                                    <td class="text-center" colspan="10">
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
                                    <th class="text-center" colspan="3">
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
                                        <?php if (!empty($bundle_name)) echo "($bundle_name)"; ?>
                                    </th>
                                    <th class="text-center <?= $show_disc_price ? 'd-none price_col' : '' ?>">Line Item Price</th>
                                    <th class="text-center <?= $show_disc_price ? '' : 'd-none' ?>">Line Item Price</th>
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

                                    $bundle_actual_price += $product_price;
                                    $bundle_customer_price += $customer_price;
                                    ?>

                                    <tr>
                                        <td>
                                            <?php
                                            if($category_id == $trim_id){
                                                if(!empty($values["custom_trim_src"])){
                                                ?>
                                                <a href="javascript:void(0);" class="drawingContainer" id="custom_trim_draw" data-line="<?php echo $line; ?>" data-id="<?php echo $product_id; ?>" data-drawing="<?= $drawing_data ?>">
                                                    <div class="align-items-center text-center w-100">
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
                                        <td>
                                            <div class="bundle-checkbox-cart d-none">
                                                <div class="form-check text-center">
                                                    <input class="form-check-input bundle-checkbox-cart" 
                                                            type="checkbox" 
                                                            data-line="<?= $line; ?>" 
                                                            data-id="<?= $product_id; ?>" 
                                                            value="<?= $line; ?>">
                                                </div>
                                            </div>
                                            <?php if (!empty($values["note"])): ?>
                                                <br>Notes: <?= htmlspecialchars($values["note"]) ?>
                                            <?php endif; ?>
                                        </td>
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
                                        <td class="text-center">
                                            <div class="d-flex flex-row align-items-center flex-nowrap w-auto">
                                                <input class="form-control form-control-sm text-center px-1" 
                                                    type="text" 
                                                    value="<?= $product['width'] ?? ''; ?>" 
                                                    placeholder="W" 
                                                    size="5" 
                                                    style="width: 40px;" 
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
                                                            style="width: 40px;" 
                                                            data-line="<?= $line; ?>" 
                                                            data-id="<?= $product_id; ?>" 
                                                            onchange="updateEstimateLength(this)">
                                                        
                                                        <input class="form-control form-control-sm text-center px-1" 
                                                            type="text" 
                                                            value="<?= round(floatval($values['estimate_length_inch']),2) ?>" 
                                                            step="0.001" 
                                                            placeholder="IN" 
                                                            size="5" 
                                                            style="width: 40px;" 
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
                                                <option value="solid" <?= $values['panel_type'] == 'solid' ? 'selected' : '' ?>>Solid</option>
                                                <option value="vented" <?= $values['panel_type'] == 'vented' ? 'selected' : '' ?>>Vented</option>
                                                <option value="drip_stop" <?= $values['panel_type'] == 'drip_stop' ? 'selected' : '' ?>>Drip Stop</option>
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
                                                    <option value="regular" <?= $values['panel_style'] == 'regular' ? 'selected' : '' ?>>Regular</option>
                                                    <option value="reversed" <?= $values['panel_style'] == 'reversed' ? 'selected' : '' ?>>Reversed</option>
                                                <?php endif; ?>
                                            </select>
                                        </td>
                                        <td class="text-center">

                                        </td>
                                        <td class="text-center pl-3 <?= $show_disc_price ? 'd-none price_col' : '' ?>">$
                                            <?php
                                            echo number_format($product_price, 2);
                                            ?>
                                        </td>
                                        <td class="text-end pl-3 <?= $show_disc_price ? '' : 'd-none' ?>">$
                                            <?php
                                            echo number_format($customer_price, 2);
                                            ?>
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

                                            <a href="javascript:void(0)" 
                                                class="text-decoration-none btn-sm me-1 sortArrowSec sort-up d-none"
                                                data-id="<?php echo $product_id; ?>" 
                                                data-line="<?php echo $line; ?>">
                                                <i class="fa fs-6 fa-arrow-up"></i>
                                            </a>

                                            <a href="javascript:void(0)" 
                                                class="text-decoration-none btn-sm sortArrowSec sort-down d-none"
                                                data-id="<?php echo $product_id; ?>" 
                                                data-line="<?php echo $line; ?>">
                                                <i class="fa fs-6 fa-arrow-down"></i>
                                            </a>
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


                        foreach ($_SESSION["cart"] as $keys => $values): ?>
                            <?php
                            $item = calculateCartItem($values);
                            $product = $item["product"];
                            $category_id = $item["category_id"];
                            $multiplier = $item['multiplier'];
                            ?>

                            <?php if ($category_id != $panel_id): ?>
                                <tr data-mult="<?= $multiplier ?>">
                                    <td data-color="<?= getColorName($item["color_id"]) ?>"
                                        data-pricing="<?= $item["customer_pricing_rate"] ?>"
                                        data-category="<?= $category_id ?>"
                                        data-customer-pricing="<?= $item["customer_pricing_rate"] ?>">

                                        <?php if ($category_id == $trim_id): ?>
                                            <?php if (!empty($values["custom_trim_src"])): ?>
                                                <a href="javascript:void(0);" class="drawingContainer" id="custom_trim_draw"
                                                data-line="<?= $item["line"] ?>" data-id="<?= $item["data_id"] ?>"
                                                data-drawing="<?= htmlspecialchars($values["drawing_data"] ?? '') ?>">
                                                    <div class="align-items-center text-center w-100">
                                                        <img src="<?= '../images/drawing/' . htmlspecialchars($values["custom_trim_src"], ENT_QUOTES) ?>"
                                                            class="rounded-circle" width="56" height="56" alt="">
                                                    </div>
                                                </a>
                                            <?php else: ?>
                                                <a href="javascript:void(0);" id="custom_trim_draw"
                                                class="drawingContainer btn btn-primary py-1 px-2 d-flex justify-content-center align-items-center"
                                                data-drawing="<?= htmlspecialchars($values["drawing_data"] ?? '') ?>"
                                                data-line="<?= $item["line"] ?>" data-id="<?= $item["data_id"] ?>">
                                                    Draw Here
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="align-items-center text-center w-100">
                                                <img src="<?= !empty($product['main_image']) ? '../'.$product['main_image'] : '../images/product/product.jpg' ?>"
                                                    class="rounded-circle" width="56" height="56" alt="">
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <a href="javascript:void(0);" data-id="<?= $item["data_id"] ?>"
                                        class="d-flex align-items-center view_product_details">
                                            <h6 class="fw-semibold mb-0 fs-4">
                                                <?= htmlspecialchars($values["product_item"]) ?>
                                                <?php if (!empty($values["is_pre_order"]) && $values["is_pre_order"] == 1): ?><br>(PREORDER)<?php endif; ?>
                                                <?php if (!empty($values["note"])): ?><br>Notes: <?= htmlspecialchars($values["note"]) ?><?php endif; ?>
                                            </h6>
                                        </a>
                                    </td>

                                    <!-- Color -->
                                    <td class="text-center">
                                        <select id="color_cart<?= $item["line"] ?>" class="form-control color-cart"
                                                data-line="<?= $item["line"] ?>" data-id="<?= $item["data_id"] ?>" onchange="updateColor(this)">
                                            <option value="">Select Color...</option>
                                            <?php if (!empty($item["color_id"])): ?>
                                                <option value="<?= $item["color_id"] ?>" selected
                                                        data-color="<?= getColorHexFromColorID($item["color_id"]) ?>">
                                                    <?= getColorName($item["color_id"]) ?>
                                                </option>
                                            <?php endif; ?>
                                            <?php
                                            $result_colors = mysqli_query($conn, "SELECT Product_id, color_id FROM inventory WHERE Product_id = '{$item["data_id"]}'");
                                            while ($row_colors = mysqli_fetch_assoc($result_colors)) {
                                                if ($item["color_id"] == $row_colors['color_id']) continue;
                                                $prod = getProductDetails($row_colors['Product_id']);
                                                $disabled = ($values['custom_grade'] ?? '') != ($prod['grade'] ?? '') ? 'disabled' : '';
                                                echo '<option value="' . $row_colors['color_id'] . '" ' . $disabled . '>' . getColorName($row_colors['color_id']) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>

                                    <!-- Grade -->
                                    <td class="text-center">
                                        <?php if (!empty($product['grade'])): ?>
                                            <div class="input-group text-start">
                                                <select id="grade<?= $item["line"] ?>" class="form-control grade-cart"
                                                        data-line="<?= $item["line"] ?>" data-id="<?= $item["data_id"] ?>" onchange="updateGrade(this)">
                                                    <option value="">Select Grade...</option>
                                                    <?php
                                                    $grades = mysqli_query($conn, "SELECT * FROM product_grade WHERE status = 1");
                                                    while ($row_grade = mysqli_fetch_assoc($grades)) {
                                                        $selected = (($values['custom_grade'] ?? '') == $row_grade['product_grade_id']) ? 'selected' : '';
                                                        echo '<option value="'.$row_grade['product_grade_id'].'" '.$selected.'>'.$row_grade['product_grade'].'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Profile -->
                                    <td class="text-center"><?= getProfileTypeName($item["profile"]); ?></td>

                                    <!-- Quantity -->
                                    <td class="text-center">
                                        <div class="input-group d-inline-flex align-items-center">
                                            <button class="btn btn-primary btn-sm p-1" type="button"
                                                    data-line="<?= $item["line"] ?>" data-id="<?= $item["data_id"] ?>"
                                                    onclick="deductquantity(this)"><i class="fa fa-minus"></i></button>

                                            <input class="form-control form-control-sm text-center mx-0"
                                                type="text" value="<?= htmlspecialchars($item['quantity']) ?>"
                                                onchange="updatequantity(this)"
                                                data-line="<?= $item["line"] ?>" data-id="<?= $item["data_id"] ?>"
                                                id="item_quantity<?= $item["data_id"] ?>" style="width: 45px;">

                                            <button class="btn btn-primary btn-sm p-1" type="button"
                                                    data-line="<?= $item["line"] ?>" data-id="<?= $item["data_id"] ?>"
                                                    onclick="addquantity(this)"><i class="fa fa-plus"></i></button>
                                        </div>
                                    </td>

                                    <!-- Length column (preserved behavior) -->
                                    <td class="text-center">
                                        <?php if ($category_id == $screw_id): ?>
                                            <div class="d-flex flex-column align-items-center d-none">
                                                <input class="form-control text-center mb-1" type="text"
                                                    value="<?= htmlspecialchars($values["estimate_width"] ?? $product["width"]) ?>"
                                                    placeholder="Width" size="5"
                                                    data-line="<?= $item['line'] ?>" data-id="<?= $item['data_id'] ?>"
                                                    onchange="updateEstimateWidth(this)">
                                                <span class="mx-1 text-center mb-1">X</span>
                                                <fieldset class="border p-1 position-relative">
                                                    <div class="input-group d-flex align-items-center">
                                                        <input class="form-control pr-0 pl-1 mr-1" type="text"
                                                            value="<?= round(floatval($values["estimate_length"] ?? 0),2) ?>" step="0.001"
                                                            placeholder="FT" size="5" data-line="<?= $item['line'] ?>"
                                                            data-id="<?= $item['data_id'] ?>" onchange="updateEstimateLength(this)">
                                                        <input class="form-control pr-0 pl-1" type="text"
                                                            value="<?= round(floatval($values["estimate_length_inch"] ?? 0),2) ?>" step="0.001"
                                                            placeholder="IN" size="5" data-line="<?= $item['line'] ?>"
                                                            data-id="<?= $item['data_id'] ?>" onchange="updateEstimateLengthInch(this)">
                                                    </div>
                                                </fieldset>
                                            </div>
                                            <?= htmlspecialchars($values["estimate_length"] ?? '') ?> pack (<?= htmlspecialchars($values["estimate_length"] ?? '') ?> pcs)

                                        <?php elseif ($category_id == $trim_id): ?>
                                            <div class="d-flex flex-row align-items-center flex-nowrap w-auto">
                                                <input class="form-control form-control-sm text-center px-1" type="text"
                                                    value="<?= htmlspecialchars($product['width'] ?? '') ?>" placeholder="W" size="5"
                                                    style="width:40px;" data-line="<?= $item['line'] ?>" data-id="<?= $item['data_id'] ?>"
                                                    <?= !empty($product['width']) ? 'readonly' : '' ?>>
                                                <span class="mx-1">X</span>
                                                <fieldset class="border p-1 d-inline-flex align-items-center flex-nowrap">
                                                    <div class="input-group d-flex align-items-center flex-nowrap w-auto">
                                                        <input class="form-control form-control-sm text-center px-1 mr-1" type="text"
                                                            value="<?= round(floatval($values['estimate_length'] ?? 0),2) ?>" step="0.001"
                                                            placeholder="FT" size="5" style="width:40px;" data-line="<?= $item['line'] ?>"
                                                            data-id="<?= $item['data_id'] ?>" onchange="updateEstimateLength(this)">
                                                        <input class="form-control form-control-sm text-center px-1" type="text"
                                                            value="<?= round(floatval($values['estimate_length_inch'] ?? 0),2) ?>" step="0.001"
                                                            placeholder="IN" size="5" style="width:40px;" data-line="<?= $item['line'] ?>"
                                                            data-id="<?= $item['data_id'] ?>" onchange="updateEstimateLengthInch(this)">
                                                    </div>
                                                </fieldset>
                                            </div>

                                        <?php elseif (hasProductVariantLength($item['data_id'])): ?>
                                            <fieldset class="border p-1 position-relative">
                                                <div class="input-group d-flex align-items-center">
                                                    <input class="form-control pr-0 pl-1 mr-1" type="text"
                                                        value="<?= round(floatval($values["estimate_length"] ?? 0),2) ?>"
                                                        placeholder="FT" size="5" data-line="<?= $item['line'] ?>" data-id="<?= $item['data_id'] ?>"
                                                        onchange="updateEstimateLength(this)">
                                                    <input class="form-control pr-0 pl-1" type="text"
                                                        value="<?= round(floatval($values["estimate_length_inch"] ?? 0),2) ?>"
                                                        placeholder="IN" size="5" data-line="<?= $item['line'] ?>" data-id="<?= $item['data_id'] ?>"
                                                        onchange="updateEstimateLengthInch(this)">
                                                </div>
                                            </fieldset>
                                        <?php else: ?>
                                            <!-- no length inputs -->
                                        <?php endif; ?>
                                    </td>

                                    <!-- Stock -->
                                    <td class="text-center"><?= $item["stock_text"] ?></td>

                                    <!-- Prices -->
                                    <td class="text-center pl-3 <?= $show_disc_price ? 'd-none price_col' : '' ?>">$
                                        <?= number_format($item["subtotal"], 2) ?>
                                    </td>
                                    <td class="text-end pl-3 <?= $show_disc_price ? '' : 'd-none' ?>">$
                                        <?= number_format($item["customer_price"], 2) ?>
                                    </td>

                                    <!-- Actions -->
                                    <td class="text-center">
                                        <button class="btn btn-danger-gradient btn-sm" type="button"
                                                data-line="<?= $item["line"] ?>" data-id="<?= $item["data_id"] ?>"
                                                onclick="delete_item(this)"><i class="fa fa-trash"></i></button>
                                        <button class="btn btn-danger-gradient btn-sm" type="button"
                                                data-line="<?= $item["line"] ?>" data-id="<?= $item["data_id"] ?>"
                                                onclick="duplicate_item(this)"><i class="fa fa-plus"></i></button>

                                        <input type="hidden" id="item_id<?= $item['data_id'] ?>" value="<?= $item["data_id"]; ?>">
                                        <input type="hidden" id="warehouse_stock<?= $item['data_id'] ?>" value="<?= htmlspecialchars($values["quantity_ttl"] ?? '') ; ?>">
                                        <input type="hidden" id="line<?= $item['data_id'] ?>" value="<?= $item['line']; ?>">
                                        <input type="hidden" id="store_stock<?= $item['data_id'] ?>" value="<?= htmlspecialchars($values["quantity_in_stock"] ?? '') ; ?>">
                                    </td>
                                </tr>

                                <?php
                                // Totals update (using item values)
                                $total_qty           += $item["quantity"];
                                $total_length_cart   += $item["quantity"] * $item["total_length"];
                                $totalquantity       += $item["quantity"];
                                $total_price_actual  += $item["subtotal"];
                                $total_customer_price+= $item["customer_price"];
                                $total_weight        += ($product["weight"] ?? 0) * $item["quantity"];
                                $customer_savings    += $item["savings"];
                                $grand_actual_price  += $item["subtotal"];
                                $grand_customer_price+= $item["customer_price"];

                                // mark panel presence
                                if ($category_id == $panel_id || $category_id == $trim_id) {
                                    $is_panel_present = 1;
                                }
                                ?>
                            <?php endif; ?>
                        <?php endforeach;


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
                        <td colspan="3" class="text-end">Total Weight</td>
                        <td><?= number_format(floatval($total_weight), 2) ?> LBS</td>
                        <td colspan="2" class="text-end">Total Quantity:</td>
                        <td colspan="1" class=""><span id="qty_ttl"><?= $totalquantity ?></span></td>
                        <td colspan="2" class="text-end">Customer Savings: </td>
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
        });
    </script>
    <?php
}