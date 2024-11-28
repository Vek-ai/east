<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

$trim_id = 4;
$panel_id = 3;

if (isset($_POST['fetch_cart'])) {
    $discount = 0;
    $tax = 0;
    $delivery_price = 0;

    if (isset($_SESSION['customer_id'])) {
        $customer_id = $_SESSION['customer_id'];
        $customer_details = getCustomerDetails($customer_id);
        
        $fullAddress = trim(implode(', ', array_filter([
            $customer_details['address'] ?? null,
            $customer_details['city'] ?? null,
            $customer_details['state'] ?? null,
            $customer_details['zip'] ?? null,
        ])));

        $fname = $customer_details['customer_first_name'] ?? '';
        $lname = $customer_details['customer_last_name'] ?? '';
        $discount = is_numeric(getCustomerDiscount($customer_id)) ? floatval(getCustomerDiscount($customer_id)) / 100 : 0;
        $tax = is_numeric(getCustomerTax($customer_id)) ? floatval(getCustomerTax($customer_id)) / 100 : 0;
    }

    $delivery_price = is_numeric(getDeliveryCost()) ? floatval(getDeliveryCost()) : 0;
    ?>
    <style>
        /* Style code remains unchanged */
    </style>
    <div id="customer_cart_section">
        <?php 
        if (!empty($_SESSION["customer_id"])) {
            $customer_id = $_SESSION["customer_id"];
            $customer_details = getCustomerDetails($customer_id);
            $credit_limit = number_format(floatval($customer_details['credit_limit'] ?? 0), 2);
            $credit_total = number_format(floatval(getCustomerCreditTotal($customer_id)), 2);
        ?>
            <div class="form-group row align-items-center">
                <div class="col-6">
                    <label class="mb-0 me-3">Customer Name: <?= htmlspecialchars(get_customer_name($_SESSION["customer_id"])) ?></label>
                    <button class="btn btn-primary btn-sm me-3" type="button" id="customer_change_cart">
                        <i class="fe fe-reload"></i> Change
                    </button>
                    <div class="mt-1"> 
                        <span class="fw-bold">Address: <?= htmlspecialchars(getCustomerAddress($_SESSION["customer_id"])) ?></span>
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
            <!-- Code for customers without a session -->
        <?php } ?>
    </div>
    <input type='hidden' id='customer_id_cart' name="customer_id" />
    <div class="card-body">
        <div class="product-details table-responsive text-nowrap">
            <table id="cartTable" class="table table-hover table-fixed mb-0 text-md-nowrap">
                <thead>
                    <tr>
                        <!-- Header Columns -->
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    $totalquantity = 0;
                    $no = 1;
                    $total_weight = 0;
                    $total_customer_price = 0;

                    if (!empty($_SESSION["cart"])) {
                        foreach ($_SESSION["cart"] as $keys => $values) {
                            $data_id = $values["product_id"];
                            $product = getProductDetails($data_id);

                            $totalstockquantity = (is_numeric($values["quantity_ttl"]) ? $values["quantity_ttl"] : 0) +
                                                  (is_numeric($values["quantity_in_stock"]) ? $values["quantity_in_stock"] : 0);

                            $stock_text = $totalstockquantity > 0 ? '<span class="text-bg-success p-1 rounded-circle">In Stock</span>' : 
                                                                    '<span class="text-bg-danger p-1 rounded-circle">Out of Stock</span>';

                            $default_image = '../images/product/product.jpg';

                            $picture_path = !empty($row_product['main_image'])
                                ? "../" . $row_product['main_image']
                                : $default_image;

                            $estimate_length = is_numeric($values["estimate_length"]) ? $values["estimate_length"] : 0;
                            $estimate_length_inch = is_numeric($values["estimate_length_inch"]) ? $values["estimate_length_inch"] : 0;
                            $total_length = $estimate_length + ($estimate_length_inch / 12);

                            $sold_by_feet = $product["sold_by_feet"];
                            $extra_cost_per_foot = (isset($values["panel_type"]) && $values["panel_type"] == 'vented') ? 0.50 : 0;

                            $amount_discount = is_numeric($values["amount_discount"]) ? $values["amount_discount"] : 0;

                            if ($sold_by_feet == 1) {
                                $product_price = ($values["quantity_cart"] * $total_length * $values["unit_price"] + ($extra_cost_per_foot * $total_length)) - $amount_discount;
                            } else {
                                $product_price = ($values["quantity_cart"] * $values["unit_price"] + $extra_cost_per_foot) - $amount_discount;
                            }

                            $color_id = $values["custom_color"];
                            $discount = isset($values["used_discount"]) ? floatval($values["used_discount"]) / 100 : $discount;

                            $subtotal = $product_price;
                            $customer_price = $product_price * (1 - $discount);

                            // Update totals
                            $totalquantity += is_numeric($values["quantity_cart"]) ? $values["quantity_cart"] : 0;
                            $total += $subtotal;
                            $total_customer_price += $customer_price;
                            $total_weight += is_numeric($values["weight"]) ? $values["weight"] * $values["quantity_cart"] : 0;
                            ?>
                            <tr>
                                <!-- Row Content -->
                            </tr>
                        <?php }
                    }
                    $_SESSION["total_quantity"] = $totalquantity;
                    $_SESSION["grandtotal"] = $total;
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-end">Total Weight</td>
                        <td><?= number_format($total_weight, 2) ?> LBS</td>
                        <td colspan="3" class="text-end">Total Quantity:</td>
                        <td colspan="1" class=""><span id="qty_ttl"><?= $totalquantity ?></span></td>
                        <td colspan="3" class="text-end">Amount Due:</td>
                        <td colspan="1" class="text-end"><span id="ammount_due"><?= number_format($total_customer_price, 2) ?> $</span></td>
                        <td colspan="1"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <!-- Checkout Section -->
    </div>
    <script>
        // JavaScript code remains unchanged
    </script>
    <?php
}
?>
