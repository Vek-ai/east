<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

$trim_id = 4;
$panel_id = 3;

if(isset($_POST['return_product_modal'])){
    $id = intval($_REQUEST['id']);
    $order_product = getOrderProdDetails($id);
    $orderid = $order_product['orderid'];
    $order = getOrderDetails($orderid);
    $customer_id = $order['customerid'];

    $return_quantity = floatval($_POST['quantity'] ?? 0);
    $stock_percentage = floatval($_POST['stock_fee'] ?? 0);

    $ordered_quantity = floatval($order_product['quantity']);
    $full_discounted_price = floatval($order_product['discounted_price']);

    $unit_price = $ordered_quantity > 0 ? $full_discounted_price / $ordered_quantity : 0;

    $total_price = $unit_price * $return_quantity;

    $stock_fee_amount = ($total_price * $stock_percentage) / 100;
    $return_amount = $total_price - $stock_fee_amount;

    $order_date_str = $order['order_date'];
    $order_date = new DateTime($order_date_str);
    $today = new DateTime();
    $days_passed = $order_date->diff($today)->days;
    ?>
    <div class="modal-body flex-grow-1 overflow-auto">
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
        <div id="customer_cash_section">
            <?php 
            $customer_details = getCustomerDetails($customer_id);
            $charge_net_30 = floatval($customer_details['charge_net_30']);
            $credit_total = number_format(getCustomerCreditTotal($customer_id),2);
            $store_credit = number_format(floatval($customer_details['store_credit'] ?? 0),2);
            $customer_name = get_customer_name($customer_id); 
            ?>
            <div class="form-group row align-items-center" style="color: #ffffff !important;">
                <div class="col-6">
                    <label>Customer Name: <?= $customer_name ?></label>
                </div>
                <div class="col-6">
                    <div>
                        <span class="fw-bold">Charge Net 30:</span><br>
                        <span class="text-primary fs-4 fw-bold pl-3">$<?= number_format($charge_net_30,2) ?></span>
                    </div>
                    <div>
                        <span class="fw-bold">Unpaid Credit:</span><br>
                        <span class="text-primary fs-4 fw-bold pl-3">$<?= $credit_total ?></span>
                    </div>
                    <div>
                        <span class="fw-bold">Store Credit:</span><br>
                        <span class="text-primary fs-4 fw-bold pl-3">$<?= $store_credit ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body datatables">
            <form id="msform">
                <input type="hidden" id="hidden_id" name="id" value="<?= $id ?>">
                <input type="hidden" id="hidden_quantity" name="quantity" value="<?= $return_quantity ?>">
                <input type="hidden" id="hidden_stock_fee" name="stock_fee" value="<?= $stock_percentage ?>">
                <div class="row text-start">
                    <div class="col-12 mb-2" style="color: #ffffff !important;">
                        <h5 class="mb-1">Checkout</h5>
                        <p>Welcome, <strong><?= $customer_name ?></strong></p>
                    </div>
                    <!-- Left Side -->
                    <div class="col-lg-8" style="color: #ffffff !important;">

                    <!-- Payment -->
                        <div class="card mb-3" style="color: #ffffff !important;">
                            <div class="card-header bg-white">
                                <i class="fa fa-check-circle text-success me-2"></i>Payment
                            </div>
                            <div class="card-body">
                            
                                <div class="mb-3 text-white">
                                    <div id="paymentOptions">
                                        <label class="form-label fw-bold">Select Payment Method</label><br>

                                        <?php if ($days_passed <= 90): ?>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="payMethod" id="payCash" value="cash">
                                                <label class="form-check-label" for="payCash">
                                                    <i class="fa-solid fa-money-bill-wave me-1"></i>Cash
                                                </label>
                                            </div>

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="payMethod" id="payCard" value="card">
                                                <label class="form-check-label" for="payCard">
                                                    <i class="fa-brands fa-cc-visa me-1"></i>Credit/Debit Card
                                                </label>
                                            </div>
                                        <?php endif; ?>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="payMethod" id="payStoreCredit" value="store_credit">
                                            <label class="form-check-label" for="payStoreCredit">
                                                <i class="fa-solid fa-wallet me-1"></i>Apply Store Credit
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side (Order Summary) -->
                    <div class="col-lg-4">
                        <div class="card" style="color: #ffffff !important;">
                            <div class="card-header bg-white">
                                <strong>Return Summary</strong>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center flex-column flex-sm-row mb-2">
                                    <span>Amount (<?= $_SESSION["total_quantity"] ?? '0' ?>)</span>
                                    <div class="d-flex flex-column align-items-end">
                                        <span>$<?= number_format($total_price,2) ?></span>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center flex-column flex-sm-row mb-2">
                                    <span>Stocking Fee</span>
                                    <div class="d-flex flex-column align-items-end">
                                        <span>$<?= number_format($stock_fee_amount,2) ?></span>
                                        <div style="width: 100px; height: 2px; background-color: white; margin-top: 2px;"></div>
                                    </div>
                                </div>

                                <hr>
                                
                                <div class="d-flex justify-content-between">
                                    <strong>Estimated Total</strong>
                                    <p><strong id="order_total">$<?= number_format($return_amount,2) ?></strong></p>
                                </div>
                                <button class="btn btn-success w-100 mt-3" id="save_return">Return</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-footer justify-content-between p-3">
        <button class="btn ripple px-3" type="button" id="btnApprovalModal" style="background-color: #800080; color: white;">
            Submit Approval
        </button>

        <div>
            <button class="btn ripple btn-success" type="button" id="save_return_alt">
                Return
            </button>
            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
    </div>
    
    <?php
}