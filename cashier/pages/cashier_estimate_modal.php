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
    <style>
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
        .table-fixed td:nth-child(5) { width: 15%; }
        .table-fixed th:nth-child(6),
        .table-fixed td:nth-child(6) { width: 15%; }
        .table-fixed th:nth-child(7),
        .table-fixed td:nth-child(7) { width: 10%; }
        .table-fixed th:nth-child(8),
        .table-fixed td:nth-child(8) { width: 7%; }
        .table-fixed th:nth-child(9),
        .table-fixed td:nth-child(9) { width: 10%; }
        .table-fixed th:nth-child(10),
        .table-fixed td:nth-child(10) { width: 4%; }

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
        <div class="card-body datatables">
            <div class="product-details table-responsive text-nowrap">
                <table id="estimateTable" class="table table-hover table-fixed mb-0 text-md-nowrap">
                    <thead>
                        <tr>
                            <th width="5%">Image</th>
                            <th width="10%">Description</th>
                            <th width="5%" class="text-center">Color</th>
                            <th width="5%" class="text-center">Grade</th>
                            <th width="5%" class="text-center">Profile</th>
                            <th width="25%" class="text-center pl-3">Quantity</th>
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
                        if (!empty($_SESSION["cart"])) {
                            foreach ($_SESSION["cart"] as $keys => $values) {
                                $data_id = $values["product_id"];
                                $product = getProductDetails($data_id);
                                $totalstockquantity = $values["quantity_ttl"] + $values["quantity_in_stock"];
                                $category_id = $product["product_category"];
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

                                $default_image = '../images/product/product.jpg';

                                $picture_path = !empty($row_product['main_image'])
                                ? "../" .$row_product['main_image']
                                : $default_image;
                            ?>
                                <tr>
                                    <td>
                                        <?php
                                        if($data_id == '277'){
                                        ?>
                                        <a href="javascript:void(0);" id="custom_trim_draw" class="btn btn-primary py-1 px-2 d-flex justify-content-center align-items-center" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>">
                                            Draw Here
                                        </a>
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
                                    <?php if($category_id == '46'){ // Panels ID
                                    ?>
                                    <td>
                                        <div class="input-group d-flex align-items-center">
                                            <input class="form-control" type="text" value="<?= $product["width"]; ?>" placeholder="W" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" readonly>
                                            <span class="mr-3 ml-1"> X</span>
                                            <input class="form-control" type="text" value="<?= $values["estimate_height"]; ?>" placeholder="H" size="5" style="color:#ffffff;" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onchange="updateEstimateHeight(this)">
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
                            }
                        }
                        $_SESSION["total_quantity"] = $totalquantity;
                        $_SESSION["grandtotal"] = $total;
                        ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="1"></td>
                            <td colspan="4" class="text-end">Total Quantity:</td>
                            <td colspan="1" class=""><span id="qty_ttl"><?= $totalquantity ?></span></td>
                            <td colspan="3" class="text-end">Amount Due:</td>
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
        <script>
            $(document).ready(function() {
                var table = $('#estimateTable').DataTable({
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
            });
        </script>
    <?php
}