<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_POST['fetch_order'])){
    $discount = 0;
    $tax = 0;
    if(isset($_SESSION['customer_id'])){
        $customer_id = $_SESSION['customer_id'];
        $discount = floatval(getCustomerDiscount($customer_id)) / 100;
        $tax = floatval(getCustomerTax($customer_id)) / 100;
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
        .table-fixed td:nth-child(1) { width: 8%; }
        .table-fixed th:nth-child(2),
        .table-fixed td:nth-child(2) { width: 15%; }
        .table-fixed th:nth-child(3),
        .table-fixed td:nth-child(3) { width: 8%; }
        .table-fixed th:nth-child(4),
        .table-fixed td:nth-child(4) { width: 8%; }
        .table-fixed th:nth-child(5),
        .table-fixed td:nth-child(5) { width: 10%; }
        .table-fixed th:nth-child(6),
        .table-fixed td:nth-child(6) { width: 10%; }
        .table-fixed th:nth-child(7),
        .table-fixed td:nth-child(7) { width: 10%; }
        .table-fixed th:nth-child(8),
        .table-fixed td:nth-child(8) { width: 10%; }
        .table-fixed th:nth-child(9),
        .table-fixed td:nth-child(9) { width: 7%; }
        .table-fixed th:nth-child(10),
        .table-fixed td:nth-child(10) { width: 7%; }
        .table-fixed th:nth-child(11),
        .table-fixed td:nth-child(11) { width: 7%; }

        input[readonly] {
            border: none;               
            background-color: transparent;
            pointer-events: none;
            color: inherit;
        }

        .table-fixed tbody tr:hover input[readonly] {
            background-color: transparent;
        }
    </style>
        <div class="card-body">
            <div class="product-details table-responsive text-nowrap">
                <table id="orderTable" class="table table-hover table-fixed mb-0 text-md-nowrap">
                    <thead>
                        <tr>
                            <th width="5%">Image</th>
                            <th width="10%">Description</th>
                            <th width="5%" class="text-center">Color</th>
                            <th width="5%" class="text-center">Grade</th>
                            <th width="5%" class="text-center">Profile</th>
                            <th width="25%" class="text-center pl-3">Quantity</th>
                            <th width="15%" class="text-center pl-3">Usage</th>
                            <th width="30%" class="text-center">Dimensions<br>(Width X Height)</th>
                            <th width="5%" class="text-center">Stock</th>
                            <th width="7%" class="text-center">Price</th>
                            <th width="7%" class="text-center">Customer<br>Price</th>
                            <th width="1%" class="text-center"> </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = 0;
                        $totalquantity = 0;
                        $total_customer_price = 0;
                        $no = 1;
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
                                            <span class="ms-2">Out of Stock</span>
                                        </a>';
                                } 

                                $default_image = '../images/product/product.jpg';

                                $picture_path = !empty($row_product['main_image'])
                                ? "../" .$row_product['main_image']
                                : $default_image;

                                $images_directory = "../images/drawing/";
                            ?>
                                <tr>
                                    <td>
                                        <?php
                                        if($data_id == '277'){
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
                                            <a href="javascript:void(0);" id="custom_trim_draw" class="btn btn-primary py-1 px-2 d-flex justify-content-center align-items-center" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>">
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
                                        <h6 class="fw-semibold mb-0 fs-4"><?= $values["product_item"] ?></h6>
                                    </td>
                                    <td>
                                        <?php echo getColorFromID($data_id); ?>
                                        
                                    </td>
                                    <td>
                                        <?php echo getGradeFromID($data_id); ?>
                                        
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
                                            <select id="usage<?= $no ?>" class="form-control select2-order" name="usage" onchange="updateUsage(this)" data-line="<?= $values['line']; ?>" data-id="<?= $data_id; ?>">
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
                                    <?php if($category_id == '46'){ // Panels ID
                                    ?>
                                    <td>
                                        <div class="input-group d-flex align-items-center">
                                            <input class="form-control" type="text" value="<?= $product["width"]; ?>" placeholder="W" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" <?= !empty($product["width"]) ? 'readonly' : '' ?>>
                                            <span class="mr-3 ml-1"> X</span>
                                            <input class="form-control" type="text" value="<?= $values["estimate_length"]; ?>" placeholder="H" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateLength(this)">
                                        </div>
                                    </td>
                                    <?php
                                    }else if($category_id == '43'){
                                    ?>
                                    <td>
                                        <div class="d-flex flex-column align-items-center">
                                            <input class="form-control text-center mb-1" type="text" value="<?= isset($values["estimate_width"]) ? $values["estimate_width"] : $product["width"]; ?>" placeholder="Width" size="5" style="color:#ffffff; " data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateWidth(this)">
                                            <span class="mx-1 text-center mb-1">X</span>
                                            <input class="form-control text-center mb-1" type="text" value="<?= $values["estimate_bend"]; ?>" placeholder="Bend" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateBend(this)">
                                            <span class="mx-1 text-center mb-1">X</span>
                                            <input class="form-control text-center mb-1" type="text" value="<?= $values["estimate_hem"]; ?>" placeholder="Hem" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateHem(this)">
                                            <span class="mx-1 text-center mb-1">X</span>
                                            <input class="form-control text-center" type="text" value="<?= isset($values["estimate_length"]) ? $values["estimate_length"] : $product["length"]; ?>" placeholder="Length" size="5" style="color:#ffffff; " data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateLength(this)">
                                        </div>
                                    </td>
                                    <?php
                                    }else{
                                    ?>
                                    <td class="text-center">N/A</td>
                                    <?php
                                    }
                                    ?>
                                    <td><?= $stock_text ?></td>
                                    <td class="text-end pl-3">$
                                        <?php
                                        $subtotal = $values["quantity_cart"] * $values["unit_price"];
                                        echo number_format($subtotal, 2);
                                        ?>
                                    </td>
                                    <td class="text-end pl-3">$
                                        <?php
                                        $customer_price = $values["quantity_cart"] * $values["unit_price"] * (1 - $discount);
                                        echo number_format($customer_price, 2);
                                        ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-danger-gradient btn-sm" type="button" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onClick="delete_item(this)"><i class="fa fa-trash"></i></button>
                                        <?php
                                        if (in_array($category_id, ['46', '43'])) {
                                        ?>
                                        <button class="btn btn-danger-gradient btn-sm" type="button" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onClick="duplicate_item(this)"><i class="fa fa-plus"></i></button>
                                        <?php } ?>
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
                            }
                        }
                        $_SESSION["total_quantity"] = $totalquantity;
                        $_SESSION["grandtotal"] = $total;
                        ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="1"></td>
                            <td colspan="5" class="text-end">Total Quantity:</td>
                            <td colspan="1" class=""><span id="qty_ttl"><?= $totalquantity ?></span></td>
                            <td colspan="3" class="text-end">Amount Due:</td>
                            <td colspan="1" class="text-end"><span id="ammount_due"><?= $total_customer_price ?> $</span></td>
                            <td colspan="1"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div id="checkout" class="row mt-3">
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
                                    <input type="text" class="form-control" id="order_discount" placeholder="%" value="<?= $discount * 100 ?>">
                                </div>
                                <div class="form-group">
                                    <label>Amount</label>
                                    <input type="text" class="form-control" id="cash_amount" onchange="update_cash()" value="<?= $total_customer_price ?>">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body pricing">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <th class="text-right border-bottom">Total</th>
                                            <td class="text-right border-bottom">$ <span id="total_amt"><?= number_format(floatval($total_customer_price), 2) ?></span></td>
                                        </tr>
                                        <tr>
                                            <th class="text-right border-bottom">Discount(-)</th>
                                            <td class="text-right border-bottom">$ <span id="total_discount"><?= number_format(floatval($total_customer_price) * floatval($discount), 2) ?></span></td>
                                        </tr>
                                        <tr>
                                            <th class="text-right border-bottom">Delivery</th>
                                            <td class="text-right border-bottom">$ <span id="delivery_amt"><?= number_format($delivery_price, 2) ?></span></td>
                                        </tr>
                                        <tr>
                                            <th class="text-right border-bottom">Sales Tax</th>
                                            <td class="text-right border-bottom">$ <span id="sales_tax"><?= number_format((floatval($total_customer_price) + $delivery_price) * $tax, 2) ?></span></td>
                                        </tr>
                                        <tr>
                                            <th class="text-right border-bottom">Total Payable</th>
                                            <td class="text-right border-bottom">$ <span id="total_payable"><?= number_format((floatval($total_customer_price) + $delivery_price), 2) ?></span></td>
                                        </tr>
                                        <tr class="bg-primary text-white" style="font-size: 1.25rem;">
                                            <th class="text-right">Change</th>
                                            <td class="text-right">$ 0.00</td>
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
                var table = $('#orderTable').DataTable({
                    language: {
                        emptyTable: "No products added to cart"
                    },
                    paging: false,
                    searching: false,
                    info: false,
                    ordering: false,
                    autoWidth: false,
                    responsive: true
                });

                $(".select2-order").each(function() {
                    $(this).select2({
                        width: '300px',
                        placeholder: "Select...",
                        dropdownAutoWidth: true,
                        dropdownParent: $('#orderTable')
                    });
                });
            });
        </script>
    <?php
}