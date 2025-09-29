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
    </style>
    <div id="customer_cart_section">
        <?php 
            if(!empty($_SESSION["customer_id"])){
                $customer_id = $_SESSION["customer_id"];
                $customer_details = getCustomerDetails($customer_id);
                $credit_limit = number_format(floatval($customer_details['credit_limit'] ?? 0), 2);
                $credit_total = number_format(getCustomerCreditTotal($customer_id),2);
                $charge_net_30 = floatval($customer_details['charge_net_30'] ?? 0);
                $store_credit = number_format(floatval($customer_details['store_credit'] ?? 0),2);
            ?>

            <div class="form-group row align-items-center">
                <div class="col-6">
                    <label class="mb-0 me-3">Customer Name: <?= get_customer_name($_SESSION["customer_id"]);?></label>
                </div>
                <div class="col-6">
                    <div>
                        <span class="fw-bold">Charge Net 30:</span><br>
                        <span class="text-primary fs-5 fw-bold pl-3">$<?= $charge_net_30 ?></span>
                    </div>
                    <div>
                        <span class="fw-bold">Unpaid Credit:</span><br>
                        <span class="text-primary fs-5 fw-bold pl-3">$<?= $credit_total ?></span>
                    </div>
                    <div>
                        <span class="fw-bold">Store Credit:</span><br>
                        <span class="text-primary fs-5 fw-bold pl-3">$<?= $store_credit ?></span>
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
                        <th class="text-center">Dimensions<br>(Width x Length)</th>
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
                    $no = 1;
                    $is_panel_present = 0;
                    $total_weight = 0;
                    $cart = getCartDataByCustomerId($customer_id);
                    if (!empty($cart)) {
                        foreach ($cart as $keys => $values) {
                            $data_id = $values["product_id"];
                            $product = getProductDetails($data_id);
                            $totalstockquantity = $values["quantity_ttl"] + $values["quantity_in_stock"];
                            $category_id = $product["product_category"];
                            if (getProductStockTotal($data_id)) {
                                $stock_text = '
                                    <a href="javascript:void(0);" id="view_in_stock" data-id="' . htmlspecialchars($data_id, ENT_QUOTES, 'UTF-8') . '" class="d-flex align-items-center">
                                        <span class="text-bg-success p-1 rounded-circle"></span>
                                        <span class="ms-2 fs-3">In Stock</span>
                                    </a>';
                            } else {
                                $stock_text = '
                                    <a href="javascript:void(0);" id="view_out_of_stock" data-id="' . htmlspecialchars($data_id, ENT_QUOTES, 'UTF-8') . '" class="d-flex align-items-center">
                                        <span class="text-bg-danger p-1 rounded-circle"></span>
                                        <span class="ms-2 fs-3">Out of Stock</span>
                                    </a>';
                            } 

                            $default_image = '../images/product/product.jpg';

                            $picture_path = !empty($row_product['main_image'])
                            ? "../" .$row_product['main_image']
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

                            $drawing_data = $values['drawing_data'];
                        ?>
                            <tr>
                                <td data-color="<?= getColorName($color_id) ?>" data-pricing="<?=$customer_pricing?>" data-category="<?=$category_id?>" data-customer-pricing="<?=$customer_details_pricing?>">
                                    <?php
                                    if($category_id == $trim_id){
                                        if(!empty($values["custom_img_src"])){
                                        ?>
                                        <a href="javascript:void(0);" class="drawingContainer" id="custom_trim_draw" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" data-drawing="<?= $drawing_data ?>">
                                            <div class="align-items-center text-center w-100" style="background: #ffffff">
                                                <img src="<?= $images_directory.$values["custom_img_src"] ?>" class="rounded-circle " alt="materialpro-img" width="56" height="56">
                                            </div>
                                        </a>
                                        <?php
                                        }else{
                                        ?>
                                        <a href="javascript:void(0);" id="custom_trim_draw" class="drawingContainer btn btn-primary py-1 px-2 d-flex justify-content-center align-items-center" data-drawing="<?= $drawing_data ?>" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>">
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
                                    <select id="color_cart<?= $no ?>" class="form-control color-cart text-start" name="color" onchange="updateColor(this)" data-line="<?= $values["line"]; ?>" data-id="<?= $data_id; ?>">
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
                                    <?php
                                    if(!empty($product['grade'])){
                                    ?>
                                    <div class="input-group text-start">
                                        <select id="grade<?= $no ?>" class="form-control grade-cart" name="grade" onchange="updateGrade(this)" data-line="<?= $values['line']; ?>" data-id="<?= $data_id; ?>">
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
                                    <?php
                                    }
                                    ?>
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
                                                        <input class="form-control pr-0 pl-1 mr-1" type="number" value="<?= $values["estimate_length"] ?>" step="0.001" placeholder="FT" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateLength(this)">
                                                        <input class="form-control pr-0 pl-1" type="number" value="<?= $values["estimate_length_inch"]; ?>" step="0.001" placeholder="IN" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateLengthInch(this)">
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
                                            <fieldset class="border p-1 position-relative">
                                                <div class="input-group d-flex align-items-center">
                                                    <input class="form-control pr-0 pl-1 mr-1" type="number" value="<?= $values["estimate_length"] ?>" step="0.001" placeholder="FT" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateLength(this)">
                                                    <input class="form-control pr-0 pl-1" type="number" value="<?= $values["estimate_length_inch"]; ?>" step="0.001" placeholder="IN" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateLengthInch(this)">
                                                </div>
                                            </fieldset>
                                        </div>
                                    </td>
                                    <?php
                                    }else if($category_id == $screw_id){
                                    ?>
                                    <td>
                                        <div class="d-flex flex-column align-items-center d-none">
                                            <input class="form-control text-center mb-1" type="text" value="<?= isset($values["estimate_width"]) ? $values["estimate_width"] : $product["width"]; ?>" placeholder="Width" size="5" style="color:#ffffff; " data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateWidth(this)">
                                            <span class="mx-1 text-center mb-1">X</span>
                                            <fieldset class="border p-1 position-relative">
                                                <div class="input-group d-flex align-items-center">
                                                    <input class="form-control pr-0 pl-1 mr-1" type="number" value="<?= $values["estimate_length"] ?>" step="0.001" placeholder="FT" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateLength(this)">
                                                    <input class="form-control pr-0 pl-1" type="number" value="<?= $values["estimate_length_inch"]; ?>" step="0.001" placeholder="IN" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateLengthInch(this)">
                                                </div>
                                            </fieldset>
                                        </div>
                                        <?= $values["estimate_length"] ?> pack (<?= $values["estimate_length"] ?> pcs)
                                    </td>
                                    <?php
                                    }else if(hasProductVariantLength($data_id)){
                                    ?>
                                    <td class="text-center">
                                        <fieldset class="border p-1 position-relative">
                                            <div class="input-group d-flex align-items-center">
                                                <input class="form-control pr-0 pl-1 mr-1" type="number" value="<?= $values["estimate_length"] ?>" step="0.001" placeholder="FT" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateLength(this)">
                                                <input class="form-control pr-0 pl-1" type="number" value="<?= $values["estimate_length_inch"]; ?>" step="0.001" placeholder="IN" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateLengthInch(this)">
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
                                    <?php 
                                    if ($category_id == $panel_id) { // Panels ID
                                    $is_panel_present = 1;
                                    }
                                    ?>
                                </td>
                            </tr>
                    <?php
                            $totalquantity += $values["quantity_cart"];
                            $total += $subtotal;
                            $total_customer_price += $customer_price;
                            $no++;
                            $total_weight += $product["weight"] * $values["quantity_cart"];
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
                                <button type="button" class="btn btn-sm btn-info btn-add-screw" data-id="<?= $data_id; ?>">
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
                </tfoot>
            </table>
        </div>
        <div id="checkout" class="row mt-3">
            <div class="col-md-8">
                
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body pricing">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <th class="text-right border-bottom">Materials Price</th>
                                        <td class="text-right border-bottom">$ <span id="total_amt"><?= number_format(floatval($total_customer_price), 2) ?></span></td>
                                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <th class="text-right border-bottom">Sales Tax</th>
                                        <td class="text-right border-bottom">$ <span id="sales_tax"><?= number_format((floatval($total_customer_price)) * $tax, 2) ?></span></td>
                                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <th class="text-right border-bottom">Total Due</th>
                                        <td class="text-right border-bottom">$ <span id="total_payable_est"><?= number_format((floatval($total_customer_price)), 2) ?></span></td>
                                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>   
    <script>
        $(document).ready(function() {
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