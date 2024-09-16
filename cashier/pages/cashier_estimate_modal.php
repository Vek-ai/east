<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_POST['fetch_estimate'])){
    $discount = 0.1;
    ?>
        <div class="card-body">
            <div class="product-details table-responsive text-nowrap">
                <table id="productTable" class="table table-hover mb-0 text-md-nowrap">
                    <thead>
                        <tr>
                            <th width="15%">Description</th>
                            <th width="13%" class="text-center">Color</th>
                            <th width="13%" class="text-center">Grade</th>
                            <th width="13%" class="text-center">Profile</th>
                            <th width="30%" class="text-center pl-3">Quantity</th>
                            <th width="5%" class="text-center">Stock</th>
                            <th width="5%" class="text-center">Price</i></th>
                            <th width="5%" class="text-center">Customer Price</i></th>
                            <th width="6%" class="text-center">Action</i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = 0;
                        $totalquantity = 0;
                        if (!empty($_SESSION["cart"])) {
                            foreach ($_SESSION["cart"] as $keys => $values) {
                                $data_id = $values["product_id"];

                                $totalstockquantity = $values["quantity_ttl"] + $values["quantity_in_stock"];

                                if ($totalstockquantity > 0) {
                                    $stock_text = '
                                        <a href="javascript:void(0);" id="view_product_details" data-id="' . htmlspecialchars($data_id, ENT_QUOTES, 'UTF-8') . '" class="d-flex align-items-center">
                                            <span class="text-bg-success p-1 rounded-circle"></span>
                                            <span class="ms-2">In Stock</span>
                                        </a>';
                                } else {
                                    $stock_text = '
                                        <div class="d-flex align-items-center">
                                            <span class="text-bg-danger p-1 rounded-circle"></span>
                                            <span class="ms-2">Out of Stock</span>
                                        </div>';
                                } 
                            ?>
                                <tr>
                                    <td>
                                        <?php echo $values["product_item"]; ?>
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
                                                <button class="btn btn-primary btn-icon p-1 mr-1" type="button" data-id="<?php echo $data_id; ?>" onClick="deductquantity(this)">
                                                    <i class="fa fa-minus"></i>
                                                </button>
                                            </span> 
                                            <input class="form-control" type="text" size="5" value="<?php echo $values["quantity_cart"]; ?>" style="color:#ffffff;" onchange="updatequantity(this)" data-id="<?php echo $data_id; ?>" id="item_quantity<?php echo $data_id;?>">
                                            <span class="input-group-btn">
                                                <button class="btn btn-primary btn-icon p-1 ml-1" type="button" data-id="<?php echo $data_id; ?>" onClick="addquantity(this)">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </span>
                                        </div>
                                    </td>
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
                                        <button class="btn btn-danger-gradient btn-sm" type="button" data-id="<?php echo $data_id; ?>" onClick="delete_item(this)"><i class="fa fa-trash"></i></button>
                                        <input type="hidden" class="form-control" data-id="<?php echo $data_id; ?>" id="item_id<?php echo $data_id; ?>" value="<?php echo $values["product_id"]; ?>">
                                        <input class="form-control" type="hidden" size="5" value="<?php echo $values["quantity_ttl"];?>" id="warehouse_stock<?php echo $data_id;?>">
                                        <input class="form-control" type="hidden" size="5" value="<?php echo $values["quantity_in_stock"];?>" id="store_stock<?php echo $data_id;?>">
                                    </td>
                                </tr>
                        <?php
                                $totalquantity += $values["quantity_cart"];
                                $total += $customer_price;
                            }
                            
                        }
                        $_SESSION["total_quantity"] = $totalquantity;
                        $_SESSION["grandtotal"] = $total;
                        ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="3"></td>
                            <td colspan="1" class="text-end">Total Quantity:</td>
                            <td colspan="1" class=""><span id="qty_ttl"><?= $totalquantity ?></span></td>
                            <td colspan="1" class="text-end">Amount Due:</td>
                            <td colspan="1" class="text-end"><span id="ammount_due"><?= $total ?> $</span></td>
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
                                    <label>Amount</label>
                                    <input type="text" class="form-control" id="cash_amount" onchange="update_cash()" value="<?= $_SESSION["grandtotal"] ?>">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body pricing">
                            <ul class="list-unstyled leading-loose">
                                <li><strong>Total: </strong>$ <span id="total_amt"> <?= $_SESSION["grandtotal"] ?? '0.00' ?></span></li>
                                <li><strong>Discount(-): </strong>$ <span id="total_discount">0.00</span></li>
                                <li><strong>Total Payable: </strong>$ <span id="total_payable"> <?= $_SESSION["grandtotal"] ?> </span></li>
                                <li class="list-group-item border-bottom-0 bg-primary" style="font-size:30px;">
                                    <strong>Change: </strong>$ 0.00
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
}