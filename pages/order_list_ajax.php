<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/send_email.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "fetch_view_modal") {
        $orderid = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM order_product WHERE orderid = '$orderid'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $order_details = getOrderDetails($orderid);
            $totalquantity = $total_actual_price = $total_disc_price = 0;
            $status_code = $order_details['status'];

            $tracking_number = $order_details['tracking_number'];
            $shipping_comp_details = getShippingCompanyDetails($order_details['shipping_company']);
            $shipping_company = $shipping_comp_details['shipping_company'];

            $response = array();
            ?>
            <div class="card">
                <div class="card-body datatables">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        View Order Details
                    </h4>
                    <div class="order-details table-responsive">
                        <div class="col-12 col-md-4 col-lg-4 text-md-start mt-3 fs-5" id="shipping-info">
                            <?php if (!empty($shipping_company)) : ?>
                            <div>
                                <strong>Shipping Company:</strong>
                                <span id="shipping-company"><?= htmlspecialchars($shipping_company) ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($tracking_number)) : ?>
                            <div>
                                <strong>Tracking #:</strong>
                                <span id="tracking-number"><?= htmlspecialchars($tracking_number) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <table id="order_dtls_tbl" class="table table-hover mb-0 w-100">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select_all"></th>
                                    <th>Description</th>
                                    <th>Color</th>
                                    <th>Grade</th>
                                    <th>Profile</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Payment Status</th>
                                    <th class="text-center">Dimensions</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">Customer Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $is_pickup = false;
                                    $is_paid = 1;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $orderid = $row['orderid'];
                                        $product_details = getProductDetails($row['productid']);

                                        $is_stockable = $product_details['product_origin'] == 1;

                                        $status_prod_db = (int)$row['status'];
                                        $payment_db = (int)$row['paid_status'];

                                        $price = $row['discounted_price'];

                                        $product_name = '';
                                        if(!empty($row['product_item'])){
                                            $product_name = $row['product_item'];
                                        }else{
                                            $product_name = getProductName($row['product_id']);
                                        }

                                        if($status_prod_db == '2'){
                                            $is_pickup = true;
                                        }

                                        if($payment_db == '0'){
                                            $is_paid = 0;
                                        }

                                        $payment_labels = [
                                            0 => ['label' => 'Unpaid', 'class' => 'badge bg-danger'],
                                            1 => ['label' => 'Paid', 'class' => 'badge bg-success']
                                        ];
                                        $payment_prod = $payment_labels[$payment_db];

                                        $status_prod_labels = [
                                            0 => ['label' => 'New', 'class' => 'badge bg-primary'],
                                            1 => ['label' => 'Processing', 'class' => 'badge bg-success'],
                                            2 => ['label' => 'Waiting for Dispatch', 'class' => 'badge bg-warning'],
                                            3 => ['label' => 'In Transit', 'class' => 'badge bg-secondary'],
                                            4 => ['label' => 'Delivered', 'class' => 'badge bg-success'],
                                            5 => ['label' => 'On Hold', 'class' => 'badge bg-danger'],
                                            6 => ['label' => 'Returned', 'class' => 'badge bg-danger']
                                        ];

                                        if ($is_stockable && isset($status_prod_labels[2])) {
                                            $status_prod_labels[2]['label'] = 'Ready for Pick-up';
                                            $status_prod_labels[4]['label'] = 'Picked Up';
                                        }

                                        $status_prod = $status_prod_labels[$status_prod_db];
                                    ?> 
                                        <tr> 
                                            <td class="text-center">
                                                <?= $is_pickup ? "<input type='checkbox' class='row-checkbox' value='{$row['id']}' data-amount='$price' data-paid='$payment_db'>" : "" ?>
                                            </td>
                                            <td>
                                                <?= $product_name ?>
                                            </td>
                                            <td>
                                            <div class="d-flex mb-0 gap-8">
                                                <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:<?= getColorHexFromProdID($product_details['color'])?>"></a>
                                                <?= getColorFromID($product_details['color']); ?>
                                            </div>
                                            </td>
                                            <td>
                                                <?php echo getGradeName($product_details['grade']); ?>
                                            </td>
                                            <td>
                                                <?php echo getProfileTypeName($product_details['profile']); ?>
                                            </td>
                                            <td><?= $row['quantity'] ?></td>
                                            <td>
                                                <span class="<?= $status_prod['class']; ?> fw-bond"><?= $status_prod['label']; ?></span>
                                            </td>
                                            <td>
                                                <span class="<?= $payment_prod['class']; ?> fw-bond"><?= $payment_prod['label']; ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                $width = $row['custom_width'];
                                                $height = $row['custom_height'];
                                                
                                                if (!empty($width) && !empty($height)) {
                                                    echo htmlspecialchars($width) . " X " . htmlspecialchars($height);
                                                } elseif (!empty($width)) {
                                                    echo "Width: " . htmlspecialchars($width);
                                                } elseif (!empty($height)) {
                                                    echo "Height: " . htmlspecialchars($height);
                                                }
                                                ?>
                                            </td>
                                            <td class="text-end">$ <?= number_format($row['actual_price'],2) ?></td>
                                            <td class="text-end">$ <?= number_format($row['discounted_price'],2) ?></td>
                                        </tr>
                                <?php
                                        $totalquantity += $row['quantity'] ;
                                        $total_actual_price += $row['actual_price'];
                                        $total_disc_price += $row['discounted_price'];
                                        $total_amount += floatval($row['discounted_price']);
                                    }
                                
                                ?>
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="7" class="text-end">Total</td>
                                    <td><?= $totalquantity ?></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-end">$ <?= number_format($total_amount,2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php if ($is_pickup == 2): ?>
                        <div class="d-flex justify-content-end align-items-center gap-3 flex-wrap mt-3">
                            <button type="button" id="pickupOrderBtn" class="btn btn-primary" data-id="<?=$orderid?>" data-paid="<?=$is_paid?>" data-action="pickup_order">Pickup Order</button>
                            <button type="button" id="shipOrderBtn" class="btn btn-primary" data-id="<?=$orderid?>" data-action="ship_order">Ship Order</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <script>
                $(document).ready(function() {

                    if ($.fn.DataTable.isDataTable('#order_dtls_tbl')) {
                        $('#order_dtls_tbl').DataTable().clear().destroy();
                    }

                    $('#order_dtls_tbl').DataTable({
                        language: {
                            emptyTable: "Order Details not found"
                        },
                        autoWidth: false,
                        responsive: true,
                        lengthChange: false
                    });

                    $('#select_all').on('change', function () {
                        $('.row-checkbox').prop('checked', this.checked);
                    });

                    $(document).on('change', '.row-checkbox', function () {
                        const allChecked = $('.row-checkbox').length === $('.row-checkbox:checked').length;
                        $('#select_all').prop('checked', allChecked);
                    });

                    window.getSelectedIDs = function () {
                        let ids = [];
                        $('.row-checkbox:checked').each(function () {
                            ids.push($(this).val());
                        });
                        return ids;
                    };

                    window.getSelectedUnpaidIDs = function () {
                        let unpaidIds = [];
                        $('.row-checkbox:checked').each(function () {
                            if ($(this).data('paid') == '0') {
                                unpaidIds.push($(this).val());
                            }
                        });
                        return unpaidIds;
                    };

                    window.getSelected = function (dataAttribute = 'paid') {
                        let filteredIds = [];
                        $('.row-checkbox:checked').each(function () {
                            const dataValue = $(this).data(dataAttribute);
                            if (dataValue == '0') {
                                filteredIds.push($(this).val());
                            }
                        });
                        return filteredIds;
                    };

                    window.getSelectedAmountTotal = function () {
                        let total = 0;

                        $('.row-checkbox:checked').each(function () {
                            const amount = parseFloat($(this).data('amount')) || 0;
                            total += amount;
                        });

                        return total;
                    };

                });
            </script>
            

            <?php
        }
    } 

    if ($action == "fetch_edit_modal") {
        $orderid = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM order_product WHERE orderid = '$orderid'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $order_details = getOrderDetails($orderid);
            $totalquantity = $total_actual_price = $total_disc_price = 0;
            $status_code = $order_details['status'];

            $tracking_number = $order_details['tracking_number'];
            $shipping_comp_details = getShippingCompanyDetails($order_details['shipping_company']);
            $shipping_company = $shipping_comp_details['shipping_company'];

            $response = array();
            ?>
            <div class="card">
                <div class="card-body datatables">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        Edit Order
                    </h4>
                    <div class="order-details table-responsive">
                        <table id="order_dtls_tbl" class="table table-hover mb-0 w-100">
                            <thead>
                                <tr>
                                    <th style="max-width: 20%;">Description</th>
                                    <th>Color</th>
                                    <th>Grade</th>
                                    <th>Profile</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Dimensions</th>
                                    <th class="text-center">Customer Price</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $is_processing = false;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $orderid = $row['orderid'];
                                        $product_details = getProductDetails($row['productid']);
                                        
                                        $status_prod_db = $row['status'];

                                        $product_name = '';
                                        if(!empty($row['product_item'])){
                                            $product_name = $row['product_item'];
                                        }else{
                                            $product_name = getProductName($row['product_id']);
                                        }

                                        if($status_prod_db == '1'){
                                            $is_processing = true;
                                        }

                                        $status_prod_labels = [
                                            0 => ['label' => 'New', 'class' => 'badge bg-primary'],
                                            1 => ['label' => 'Processing', 'class' => 'badge bg-success'],
                                            2 => ['label' => 'Waiting for Dispatch', 'class' => 'badge bg-warning'],
                                            3 => ['label' => 'In Transit', 'class' => 'badge bg-secondary'],
                                            4 => ['label' => 'Delivered', 'class' => 'badge bg-success'],
                                            5 => ['label' => 'On Hold', 'class' => 'badge bg-danger']
                                        ];

                                        $status_prod = $status_prod_labels[$status_prod_db];
                                    ?> 
                                        <tr> 
                                            <td style="max-width: 20%;">
                                                <h6><?= htmlspecialchars($product_name) ?></h6>
                                            </td>
                                            <td>
                                                <select class="form-control search-chat py-0 ps-5 select2-edit" name="color[<?= $row['id'] ?>]" id="edit-color-<?= $row['id'] ?>">
                                                    <option value="" data-category="">All Colors</option>
                                                    <optgroup label="Product Colors">
                                                        <?php
                                                        $query_color = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                                        $result_color = mysqli_query($conn, $query_color);
                                                        while ($row_color = mysqli_fetch_array($result_color)) {
                                                            $selected = ($row_color['color_id'] == getColorFromID($product_details['color'])) ? 'selected' : '';
                                                            ?>
                                                            <option value="<?= $row_color['color_id'] ?>" data-category="category" <?= $selected ?>>
                                                                <?= $row_color['color_name'] ?>
                                                            </option>
                                                        <?php } ?>
                                                    </optgroup>
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-control search-chat py-0 ps-5 select2-edit" name="grade[<?= $row['id'] ?>]" id="edit-grade-<?= $row['id'] ?>">
                                                    <option value="" data-category="">All Grades</option>
                                                    <optgroup label="Product Grades">
                                                        <?php
                                                        $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY `product_grade` ASC";
                                                        $result_grade = mysqli_query($conn, $query_grade);
                                                        while ($row_grade = mysqli_fetch_array($result_grade)) {
                                                            $selected = ($row_grade['product_grade_id'] == getGradeName($product_details['grade'])) ? 'selected' : '';
                                                            ?>
                                                            <option value="<?= $row_grade['product_grade_id'] ?>" data-category="grade" <?= $selected ?>>
                                                                <?= $row_grade['product_grade'] ?>
                                                            </option>
                                                        <?php } ?>
                                                    </optgroup>
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-control search-category py-0 ps-5 select2-edit" name="profile[<?= $row['id'] ?>]" id="edit-profile-<?= $row['id'] ?>">
                                                    <option value="" data-category="">All Profile Types</option>
                                                    <optgroup label="Product Line">
                                                        <?php
                                                        $query_profile = "SELECT * FROM profile_type WHERE hidden = '0' AND status = '1' ORDER BY `profile_type` ASC";
                                                        $result_profile = mysqli_query($conn, $query_profile);
                                                        while ($row_profile = mysqli_fetch_array($result_profile)) {
                                                            $selected = ($row_profile['profile_type_id'] == getProfileTypeName($product_details['profile'])) ? 'selected' : '';
                                                            ?>
                                                            <option value="<?= $row_profile['profile_type_id'] ?>" data-category="<?= $v['product_category'] ?? '' ?>" <?= $selected ?>>
                                                                <?= $row_profile['profile_type'] ?>
                                                            </option>
                                                        <?php } ?>
                                                    </optgroup>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control text-center" name="quantity[<?= $row['id'] ?>]" value="<?= $row['quantity'] ?>">
                                            </td>
                                            <td>
                                                <select class="form-select select2-edit" name="status[<?= $row['id'] ?>]">
                                                    <?php foreach ($status_prod_labels as $code => $info): ?>
                                                        <option value="<?= $code ?>" <?= $code == $status_prod_db ? 'selected' : '' ?>>
                                                            <?= $info['label'] ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column align-items-center">
                                                    <input type="text"
                                                        class="form-control text-center mb-1"
                                                        name="custom_width[<?= $row['id'] ?>]"
                                                        value="<?= htmlspecialchars($row['custom_width']) ?>"
                                                        placeholder="Width"
                                                        size="5">

                                                    <span class="mx-1 text-center mb-1">X</span>

                                                    <fieldset class="border p-1 position-relative">
                                                        <div class="input-group d-flex align-items-center">
                                                            <input type="number"
                                                                class="form-control pr-0 pl-1 mr-1"
                                                                name="custom_length[<?= $row['id'] ?>]"
                                                                value="<?= htmlspecialchars($row['custom_length']) ?>"
                                                                step="0.001"
                                                                placeholder="FT"
                                                                size="5">

                                                            <input type="number"
                                                                class="form-control pr-0 pl-1"
                                                                name="custom_length_inch[<?= $row['id'] ?>]"
                                                                value="<?= htmlspecialchars($row['custom_length2']) ?>"
                                                                step="0.001"
                                                                placeholder="IN"
                                                                size="5">
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control text-end" name="discounted_price[<?= $row['id'] ?>]" value="<?= $row['discounted_price'] ?>">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm delete-row" data-id="<?= $row['id'] ?>">
                                                    <i class="fa fa-trash text-danger"></i>
                                                </button>
                                            </td>
                                        </tr>

                                <?php
                                    }
                                
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button type="button" id="saveEditOrderBtn" class="btn btn-success">
                    Save Changes
                </button>
            </div>
            <?php
        }
    } 

    if ($action === 'save_edited_order') {
        $order_data_json = $_POST['order_data'] ?? '';
        $order_data = json_decode($order_data_json, true);

        if (!is_array($order_data)) {
            echo 'invalid_data';
            exit;
        }

        $success = true;
        $affected_orders = [];
        $current_user = $_SESSION['userid'] ?? 'System';

        foreach ($order_data as $id => $data) {
            $id = intval($id);

            $get_old_sql = "SELECT * FROM order_product WHERE id = '$id' LIMIT 1";
            $old_result = mysqli_query($conn, $get_old_sql);
            if (!$old_result || !mysqli_num_rows($old_result)) continue;
            $old_data = mysqli_fetch_assoc($old_result);

            $custom_color = intval($data['color']);
            $custom_grade = intval($data['grade']);
            $profile = intval($data['profile']);
            $quantity = mysqli_real_escape_string($conn, $data['quantity']);
            $status = intval($data['status']);
            $custom_width = mysqli_real_escape_string($conn, $data['width']);
            $custom_length = mysqli_real_escape_string($conn, $data['length']);
            $custom_length2 = mysqli_real_escape_string($conn, $data['length2']);
            $discounted_price = floatval($data['discounted_price']);

            $new_data = [
                'custom_color' => $custom_color,
                'custom_grade' => $custom_grade,
                'profile' => $profile,
                'quantity' => $quantity,
                'status' => $status,
                'custom_width' => $custom_width,
                'custom_length' => $custom_length,
                'custom_length2' => $custom_length2,
                'discounted_price' => $discounted_price
            ];

            $changes = [];
            foreach ($new_data as $key => $new_value) {
                $old_value = $old_data[$key];
                if ((string)$old_value !== (string)$new_value) {
                    $changes[$key] = [
                        'old' => $old_value,
                        'new' => $new_value
                    ];
                }
            }

            $update_sql = "
                UPDATE order_product 
                SET 
                    custom_color = '$custom_color',
                    custom_grade = '$custom_grade',
                    quantity = '$quantity',
                    status = '$status',
                    custom_width = '$custom_width',
                    custom_length = '$custom_length',
                    custom_length2 = '$custom_length2',
                    discounted_price = '$discounted_price'
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $update_sql)) {
                $success = false;
                break;
            }

            if (!empty($changes)) {
                $orderid = $old_data['orderid'];
                $old_json = json_encode(array_map(fn($v) => $v['old'], $changes));
                $new_json = json_encode(array_map(fn($v) => $v['new'], $changes));

                $log_sql = "
                    INSERT INTO order_history 
                        (orderid, order_product_id, action_type, old_value, new_value, updated_by) 
                    VALUES 
                        ('$orderid', '$id', 'update_product', 
                        '" . mysqli_real_escape_string($conn, $old_json) . "', 
                        '" . mysqli_real_escape_string($conn, $new_json) . "', 
                        '" . mysqli_real_escape_string($conn, $current_user) . "')
                ";
                mysqli_query($conn, $log_sql);
            }

            $affected_orders[] = $old_data['orderid'];
        }

        $affected_orders = array_unique($affected_orders);

        foreach ($affected_orders as $orderid) {
            $get_statuses_sql = "SELECT status FROM order_product WHERE orderid = '$orderid'";
            $status_result = mysqli_query($conn, $get_statuses_sql);

            $statuses = [];
            while ($row = mysqli_fetch_assoc($status_result)) {
                $statuses[] = (int)$row['status'];
            }

            if (!empty($statuses)) {
                $status_priority = [
                    1 => 1,
                    2 => 2,
                    5 => 3,
                    3 => 4,
                    4 => 5
                ];

                $highest_status = 1;
                $max_priority = 0;

                foreach ($statuses as $s) {
                    $priority = $status_priority[$s] ?? 0;
                    if ($priority > $max_priority) {
                        $max_priority = $priority;
                        $highest_status = $s;
                    }
                }

                $update_order_sql = "UPDATE orders SET status = '$highest_status' WHERE orderid = '$orderid'";
                if (!mysqli_query($conn, $update_order_sql)) {
                    $success = false;
                    break;
                }
            }
        }

        echo $success ? 'success' : 'error';
        exit;
    }

    if ($action == "fetch_hold_modal") {
        $orderid = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM order_product WHERE orderid = '$orderid'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $order_details = getOrderDetails($orderid);
            $totalquantity = $total_actual_price = $total_disc_price = 0;
            $status_code = $order_details['status'];

            $tracking_number = $order_details['tracking_number'];
            $shipping_comp_details = getShippingCompanyDetails($order_details['shipping_company']);
            $shipping_company = $shipping_comp_details['shipping_company'];

            $response = array();
            ?>
            

            <div class="card">
                <div class="card-body datatables">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        Place on Hold
                    </h4>
                    <div class="order-details table-responsive text-nowrap">
                        <div class="col-12 col-md-4 col-lg-4 text-md-start mt-3 fs-5" id="shipping-info">
                            <?php if (!empty($shipping_company)) : ?>
                            <div>
                                <strong>Shipping Company:</strong>
                                <span id="shipping-company"><?= htmlspecialchars($shipping_company) ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($tracking_number)) : ?>
                            <div>
                                <strong>Tracking #:</strong>
                                <span id="tracking-number"><?= htmlspecialchars($tracking_number) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <table id="order_dtls_tbl" class="table table-hover mb-0 text-md-nowrap w-100">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select_all"></th>
                                    <th>Description</th>
                                    <th>Color</th>
                                    <th>Grade</th>
                                    <th>Profile</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Dimensions</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">Customer Price</th>
                                    <th class="text-center">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $is_processing = false;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $orderid = $row['orderid'];
                                        $product_details = getProductDetails($row['productid']);
                                        
                                        $status_prod_db = $row['status'];

                                        $product_name = '';
                                        if(!empty($row['product_item'])){
                                            $product_name = $row['product_item'];
                                        }else{
                                            $product_name = getProductName($row['product_id']);
                                        }

                                        if($status_prod_db == '1'){
                                            $is_processing = true;
                                        }

                                        $status_prod_labels = [
                                            0 => ['label' => 'New', 'class' => 'badge bg-primary'],
                                            1 => ['label' => 'Processing', 'class' => 'badge bg-success'],
                                            2 => ['label' => 'Waiting for Dispatch', 'class' => 'badge bg-warning'],
                                            3 => ['label' => 'In Transit', 'class' => 'badge bg-secondary'],
                                            4 => ['label' => 'Delivered', 'class' => 'badge bg-success'],
                                            5 => ['label' => 'On Hold', 'class' => 'badge bg-danger']
                                        ];

                                        $status_prod = $status_prod_labels[$status_prod_db];
                                    ?> 
                                        <tr> 
                                            <td class="text-center">
                                                <input type="checkbox" class="row-checkbox" value="<?= $row['id'] ?>" data-status="">
                                            </td>
                                            <td>
                                                <?= $product_name ?>
                                            </td>
                                            <td>
                                            <div class="d-flex mb-0 gap-8">
                                                <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:<?= getColorHexFromProdID($product_details['color'])?>"></a>
                                                <?= getColorFromID($product_details['color']); ?>
                                            </div>
                                            </td>
                                            <td>
                                                <?php echo getGradeName($product_details['grade']); ?>
                                            </td>
                                            <td>
                                                <?php echo getProfileTypeName($product_details['profile']); ?>
                                            </td>
                                            <td><?= $row['quantity'] ?></td>
                                            <td>
                                                <span class="<?= $status_prod['class']; ?> fw-bond"><?= $status_prod['label']; ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                $width = $row['custom_width'];
                                                $height = $row['custom_height'];
                                                
                                                if (!empty($width) && !empty($height)) {
                                                    echo htmlspecialchars($width) . " X " . htmlspecialchars($height);
                                                } elseif (!empty($width)) {
                                                    echo "Width: " . htmlspecialchars($width);
                                                } elseif (!empty($height)) {
                                                    echo "Height: " . htmlspecialchars($height);
                                                }
                                                ?>
                                            </td>
                                            <td class="text-end">$ <?= number_format($row['actual_price'],2) ?></td>
                                            <td class="text-end">$ <?= number_format($row['discounted_price'],2) ?></td>
                                            <td class="text-end">$ <?= number_format(floatval($row['discounted_price'] * $row['quantity']),2) ?></td>
                                        </tr>
                                <?php
                                        $totalquantity += $row['quantity'] ;
                                        $total_actual_price += $row['actual_price'];
                                        $total_disc_price += $row['discounted_price'];
                                        $total_amount += floatval($row['discounted_price']) * $row['quantity'];
                                    }
                                
                                ?>
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="7" class="text-end">Total</td>
                                    <td><?= $totalquantity ?></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-end">$ <?= number_format($total_amount,2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button type="button" id="holdOrderBtn" class="btn btn-success">
                    Place on Hold
                </button>
            </div>
            <script>
                $(document).ready(function() {

                    $('#select_all').on('change', function () {
                        $('.row-checkbox').prop('checked', this.checked);
                    });

                    $(document).on('change', '.row-checkbox', function () {
                        const allChecked = $('.row-checkbox').length === $('.row-checkbox:checked').length;
                        $('#select_all').prop('checked', allChecked);
                    });

                    window.getSelectedIDs = function () {
                        let ids = [];
                        $('.row-checkbox:checked').each(function () {
                            ids.push($(this).val());
                        });
                        return ids;
                    };

                    $(document).on('click', '#holdOrderBtn', function () {
                        const selectedIDs = getSelectedIDs();

                        if (selectedIDs.length === 0) {
                            alert("Please select at least one product to put on hold.");
                            return;
                        }

                        if (!confirm("Are you sure you want to place the selected items on hold?")) {
                            return;
                        }

                        $.ajax({
                            url: 'pages/order_list_ajax.php',
                            type: 'POST',
                            data: {
                                action: 'place_on_hold',
                                product_ids: selectedIDs
                            },
                            success: function (response) {
                                if (response.trim() === 'success') {
                                    alert("Selected items have been placed on hold.");
                                    location.reload();
                                } else {
                                    alert("Failed to update: " + response);
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error("AJAX Error:", xhr.responseText);
                                alert("An error occurred while updating the status.");
                            }
                        });
                    });
                });
            </script>
            

            <?php
        }
    }

    if ($action == "place_on_hold") {
        $ids = $_POST['product_ids'] ?? [];

        if (!empty($ids) && is_array($ids)) {
            $escaped_ids = array_map(function ($id) use ($conn) {
                return (int) mysqli_real_escape_string($conn, $id);
            }, $ids);

            $id_list = implode(',', $escaped_ids);

            $orderid_query = "SELECT DISTINCT orderid FROM order_product WHERE id IN ($id_list)";
            $orderid_result = mysqli_query($conn, $orderid_query);

            $order_ids = [];
            while ($row = mysqli_fetch_assoc($orderid_result)) {
                $order_ids[] = (int) $row['orderid'];
            }

            if (!empty($order_ids)) {
                $update_products_query = "UPDATE order_product SET status = 5 WHERE id IN ($id_list)";
                mysqli_query($conn, $update_products_query);

                $escaped_order_ids = implode(',', $order_ids);
                $update_orders_query = "UPDATE orders SET status = 5 WHERE orderid IN ($escaped_order_ids)";
                mysqli_query($conn, $update_orders_query);

                echo 'success';
            } else {
                echo 'No matching order IDs found';
            }
        } else {
            echo 'No product IDs received';
        }

        exit;
    }
    
    if ($action == "send_email") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $customerid = mysqli_real_escape_string($conn, $_POST['customerid']);
        $customer_details= getCustomerDetails($customerid);
        $customer_name = $customer_details['customer_first_name'] .' ' .$customer_details['customer_last_name'];
        $customer_email = $customer_details['contact_email'];
        $customer_phone = $customer_details['contact_phone'];
        $primary_contact = $customer_details['primary_contact'];

        $send_option = mysqli_real_escape_string($conn, $_POST['send_option']);

        $order_key = 'ORD' . substr(hash('sha256', uniqid()), 0, 10);

        $sql = "UPDATE orders SET order_key = '$order_key', status = '1' WHERE orderid = $id";

        if (!mysqli_query($conn, $sql)) {
            echo json_encode([
                'success' => false,
                'email_success' => false,
                'message' => "Query Failed.",
                "error" => mysqli_error($conn)
            ]);
        }

        $order_url = "https://metal.ilearnwebtech.com/customer/index.php?page=order&id=$id&key=$order_key";

        $subject = "EKM has confirmed your order.";

        $sms_message = "Hi $customer_name,\n\n$subject\nClick this link to view your order details:\n$est_url";

        $html_message = "
                <html>
                <head>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            line-height: 1.6;
                            color: #333;
                        }
                        .container {
                            padding: 20px;
                            border: 1px solid #e0e0e0;
                            background-color: #f9f9f9;
                            width: 80%;
                            margin: 0 auto;
                            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                        }
                        h2 {
                            color: #0056b3;
                            margin-bottom: 20px;
                        }
                        p {
                            margin: 5px 0;
                        }
                        .link {
                            font-weight: bold;
                        }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h2>$subject</h2>
                        <a href='$order_url' class='link' target='_blank'>To view order details, click this link</a>
                    </div>
                </body>
                </html>
                ";

            $email_success = false;
            $sms_success = false;
            $email_error = '';
            $sms_error = '';

            if ($send_option === 'email' || $send_option === 'both') {
                if (!empty($customer_email)) {
                    $email_result = sendEmail($customer_email, $customer_name, $subject, $html_message);
                    $email_success = $email_result['success'];
                    $response['email_success'] = $email_success;

                    if (!$email_success) {
                        $email_error = $email_result['error'] ?? 'Unknown email error';
                        $response['email_error'] = $email_error;
                    }
                } else {
                    $response['email_success'] = false;
                    $response['email_error'] = 'Missing email';
                }
            }

            if ($send_option === 'sms' || $send_option === 'both') {
                if (!empty($customer_phone)) {
                    $sms_result = sendPhoneMessage($customer_phone, $customer_name, $subject, $sms_message);
                    $sms_success = $sms_result['success'];
                    $response['sms_success'] = $sms_success;

                    if (!$sms_success) {
                        $sms_error = $sms_result['error'] ?? 'Unknown SMS error';
                        $response['sms_error'] = $sms_error;
                    }
                } else {
                    $response['sms_success'] = false;
                    $response['sms_error'] = 'Missing phone number';
                }
            }

            if ($email_success || $sms_success) {
                $response['message'] = "Successfully sent to $customer_name.";
            } else {
                $response['message'] = "Message could not be sent to $customer_name.";
            }

            $query = "SELECT * FROM orders WHERE orderid = '$id'";
            $result = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($result)) {

                $sql = "UPDATE order_product SET status = 1 WHERE orderid = $id";
                if (!mysqli_query($conn, $sql)) {
                    $response['message'] = 'Error updating order status.';
                }
            }

            $sql = "UPDATE orders SET status = '2', is_edited = '0' WHERE orderid = $id";
            if (!mysqli_query($conn, $sql)) {
                $response['message'] = 'Error updating order status.';
            }

            $response['success'] = true;
            $response['id'] = $id;
            $response['key'] = $est_key;

            echo json_encode($response);
    }

    if ($action == "update_status") {
        $orderid = mysqli_real_escape_string($conn, $_POST['id']);
        $method = mysqli_real_escape_string($conn, $_POST['method']); 

        $pickup_name = mysqli_real_escape_string($conn, $_POST['pickup_name'] ?? '');
        $tracking_number = mysqli_real_escape_string($conn, $_POST['tracking_number'] ?? ''); 
        $shipping_company = mysqli_real_escape_string($conn, $_POST['shipping_company'] ?? ''); 
        
        $is_edited = '0';

        $query = "SELECT * FROM orders WHERE orderid = '$orderid'";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $customerid = $row['customerid'];
            $customer_details= getCustomerDetails($customerid);
            $customer_name = $customer_details['customer_first_name'] .' ' .$customer_details['customer_last_name'];
            $customer_email = $customer_details['contact_email'];
            $customer_phone = $customer_details['contact_phone'];
            $primary_contact = $customer_details['primary_contact'];
            
            $order_key = $row['order_key'];
        
            $response = ['success' => false, 'message' => 'Unknown error'];
        
            if ($method == "process_order") {
                $newStatus = 2;
                $subject = "EKM has started to process your order";

                $sql = "UPDATE order_product SET status = 1 WHERE orderid = $orderid";
                if (!mysqli_query($conn, $sql)) {
                    $response['message'] = 'Error updating order status.';
                }

            } elseif ($method == "ship_order" || $method == "pickup_order") {
                $selectedProds = $_POST['selected_prods'] ?? [];
                $selectedProds = is_string($selectedProds) ? json_decode($selectedProds, true) : $selectedProds;
                $selectedProds = is_array($selectedProds) ? $selectedProds : [];
                $cleanedProds = array_map('intval', $selectedProds);

                $newStatus = 3;

                if (!empty($cleanedProds)) {
                    $idList = implode(',', $cleanedProds);

                    if($method == 'pickup_order'){
                        $subject = "EKM has completed your order and is waiting for pickup";
                        $order_prod_status = 4;
                    }else{
                        $subject = "EKM has shipped your order";
                        $order_prod_status = 3;
                    }
                    
                    $sql = "UPDATE order_product SET status = $order_prod_status WHERE id IN ($idList)";
                    if (!mysqli_query($conn, $sql)) {
                        $response['message'] = 'Error updating product status.';
                    }
                
                    $sql = "SELECT COUNT(*) AS count FROM order_product WHERE status = 1 AND orderid = '$orderid'";
                    $result = mysqli_query($conn, $sql);
                
                    if ($row = mysqli_fetch_assoc($result)) {
                        $newStatus = ($row['count'] > 0) ? 2 : 3;
                    } else {
                        $newStatus = 2;
                    }
                }               
            } else {
                $response['message'] = 'Invalid action';
                echo json_encode($response);
                exit();
            }

            $updateParts = [
                "status = '$newStatus'",
                "is_edited = '0'"
            ];

            if (!empty($tracking_number)) {
                $updateParts[] = "tracking_number = '$tracking_number'";
            }
        
            if (!empty($shipping_company)) {
                $updateParts[] = "shipping_company = '$shipping_company'";
            }

            if (!empty($pickup_name)) {
                $updateParts[] = "pickup_name = '$pickup_name'";
            }

            $updateParts[] = "delivered_date = NOW()";
            
            $sql = "UPDATE orders SET " . implode(", ", $updateParts) . " WHERE orderid = $orderid";
            
            if (mysqli_query($conn, $sql)) {

                if($method == 'pickup_order'){
                    echo json_encode([
                        'success' => true,
                        'email_success' => false,
                        'message' => "Successfully saved"
                    ]);
                    exit(); //skip email
                }

                $message = "
                    <html>
                    <head>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                line-height: 1.6;
                                color: #333;
                            }
                            .container {
                                padding: 20px;
                                border: 1px solid #e0e0e0;
                                background-color: #f9f9f9;
                                width: 80%;
                                margin: 0 auto;
                                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                            }
                            h2 {
                                color: #0056b3;
                                margin-bottom: 20px;
                            }
                            p {
                                margin: 5px 0;
                            }
                            .link {
                                font-weight: bold;
                            }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <h2>$subject</h2>
                            <a href='https://metal.ilearnwebtech.com/customer/index.php?page=order&id=$orderid&key=$order_key' class='link' target='_blank'>To view order details, click this link</a>
                        </div>
                    </body>
                    </html>
                ";
                $shipping_url = '';
                $shipping_comp_details = getShippingCompanyDetails($shipping_company);
                if(!empty($shipping_comp_details['url'])){
                    $shipping_url = $shipping_comp_details['url'];
                }

                if($primary_contact == 2){
                    if(!empty($customer_phone)){
                        $response = sendPhoneMessage($customer_email, $customer_name, $subject, $message);
                        if ($response['success'] == true) {
                            echo json_encode([
                                'success' => true,
                                'msg_success' => true,
                                'message' => "Successfully sent message to $customer_name for confirmation on orders.",
                                'id' => $orderid,
                                'key' => $order_key,
                                'url' => $shipping_url
                            ]);
                        } else {
                            echo json_encode([
                                'success' => true,
                                'msg_success' => false,
                                'message' => "Successfully saved, but message could not be sent to $customer_name.",
                                'error' => $response['error'],
                                'id' => $orderid,
                                'key' => $order_key,
                                'url' => $shipping_url
                            ]);
                        }
                    } else {
                        echo json_encode([
                            'success' => true,
                            'msg_success' => false,
                            'message' => "Successfully saved, but message could not be sent to $customer_name.",
                            'error' => $response['error'],
                            'id' => $orderid,
                            'key' => $order_key,
                            'url' => $shipping_url
                        ]);
                    }
                }else{
                    if(!empty($customer_email)){
                        $response = sendEmail($customer_email, $customer_name, $subject, $message);
                        if ($response['success'] == true) {
                            echo json_encode([
                                'success' => true,
                                'email_success' => true,
                                'message' => "Successfully updated status and sent email confirmation to $customer_name",
                                'id' => $orderid,
                                'key' => $order_key,
                                'url' => $shipping_url
                            ]);
                        } else {
                            echo json_encode([
                                'success' => true,
                                'email_success' => false,
                                'message' => "Successfully updated status, but email could not be sent to $customer_name.",
                                'error' => $response['error'],
                                'id' => $orderid,
                                'key' => $order_key,
                                'url' => $shipping_url
                            ]);
                        }
        
                    }else {
                        echo json_encode([
                            'success' => true,
                            'email_success' => false,
                            'message' => "Successfully updated status, but email could not be sent to $customer_name.",
                            'error' => $response['error'],
                            'id' => $orderid,
                            'key' => $order_key,
                            'url' => $shipping_url
                        ]);
                    }
                }
            } else {
                echo json_encode([
                    'success' => true,
                    'email_success' => false,
                    'message' => "Failed to save!",
                    'error' => mysqli_error($conn),
                    'id' => $orderid,
                    'key' => $order_key,
                    'url' => ''
                ]);
            }
        }
    }

    if ($action === 'fetch_order_history') {
        $orderid = intval($_POST['id']);

        $history_query = "SELECT * FROM order_history WHERE order_product_id IN (
                            SELECT id FROM order_product WHERE orderid = '$orderid'
                        ) ORDER BY created_at DESC";

        $history_result = mysqli_query($conn, $history_query);

        $status_prod_labels = [
            0 => ['label' => 'New', 'class' => 'badge bg-primary'],
            1 => ['label' => 'Processing', 'class' => 'badge bg-success'],
            2 => ['label' => 'Waiting for Dispatch', 'class' => 'badge bg-warning'],
            3 => ['label' => 'In Transit', 'class' => 'badge bg-secondary'],
            4 => ['label' => 'Delivered', 'class' => 'badge bg-success'],
            5 => ['label' => 'On Hold', 'class' => 'badge bg-danger']
        ];
        ?>
        <div class="modal-header">
            <h5 class="modal-title">Change History</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <?php if (mysqli_num_rows($history_result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th style="max-width: 20%;">Product</th>
                                <th>Action</th>
                                <th>Updated By</th>
                                <th>Date</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($history_result)): ?>
                                <?php
                                    $old = json_decode($row['old_value'], true);
                                    $new = json_decode($row['new_value'], true);

                                    $prod_details = getOrderProdDetails($row['order_product_id']);
                                    $product_id = $prod_details['productid'];
                                    $product_name = getProductName($product_id);
                                    $staff_name = get_staff_name($row['updated_by']);
                                    $datetime = new DateTime($row['created_at']);
                                    $date = $datetime->format('M j, Y');
                                    $time = $datetime->format('h:i A');

                                    foreach ($new as $key => $new_val):
                                        $old_val = $old[$key] ?? '';

                                        switch ($key) {
                                            case 'custom_color':
                                                $old_val = getColorName($old_val);
                                                $new_val = getColorName($new_val);
                                                break;
                                            case 'custom_grade':
                                                $old_val = getGradeName($old_val);
                                                $new_val = getGradeName($new_val);
                                                break;
                                            case 'profile':
                                                $old_val = getProfileTypeName($old_val);
                                                $new_val = getProfileTypeName($new_val);
                                                break;
                                            case 'status':
                                                $old_badge = $status_prod_labels[$old_val] ?? ['label' => $old_val, 'class' => 'badge bg-secondary'];
                                                $new_badge = $status_prod_labels[$new_val] ?? ['label' => $new_val, 'class' => 'badge bg-secondary'];
                                                $old_val = "<span class='{$old_badge['class']}'>" . htmlspecialchars($old_badge['label']) . "</span>";
                                                $new_val = "<span class='{$new_badge['class']}'>" . htmlspecialchars($new_badge['label']) . "</span>";
                                                break;
                                        }

                                        if ((string)$new_val !== (string)$old_val):
                                            $field_name = ucwords(str_replace('_', ' ', $key));
                                ?>
                                    <tr>
                                        <td style="max-width: 20%;"><?= htmlspecialchars($product_name) ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($field_name) ?>:</strong>
                                            <?= $key === 'status' ? "$old_val → $new_val" : "<span class='text-danger'>" . htmlspecialchars($old_val) . "</span> → <span class='text-success'>" . htmlspecialchars($new_val) . "</span>" ?>
                                        </td>
                                        <td><?= htmlspecialchars($staff_name) ?></td>
                                        <td><?= htmlspecialchars($date) ?></td>
                                        <td><?= htmlspecialchars($time) ?></td>
                                    </tr>
                                <?php
                                        endif;
                                    endforeach;
                                ?>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center">No change history found.</p>
            <?php endif; ?>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
        <?php
    }

    if ($action === 'pickup_order') {
        $orderid = mysqli_real_escape_string($conn, $_POST['id'] ?? '');
        $order_details = getOrderDetails($orderid);
        $pickup_name = mysqli_real_escape_string($conn, $_POST['pickup_name'] ?? '');
        $selectedProds = $_POST['selected_prods'] ?? [];
        $selectedProds = is_string($selectedProds) ? json_decode($selectedProds, true) : $selectedProds;
        $cleanedProds = array_map('intval', is_array($selectedProds) ? $selectedProds : []);

        if (empty($cleanedProds)) {
            echo json_encode(['success' => false, 'message' => 'No products selected.']);
            exit();
        }

        $idList = implode(',', $cleanedProds);

        // 1. Mark selected products as Picked Up
        $sql = "UPDATE order_product SET status = 4 WHERE id IN ($idList)";
        if (!mysqli_query($conn, $sql)) {
            echo json_encode(['success' => false, 'message' => 'Error updating product status.']);
            exit();
        }

        // 2. Determine new overall order status
        $check_sql = "SELECT COUNT(*) AS count FROM order_product WHERE status IN ('0','1') AND orderid = '$orderid'";
        $result = mysqli_query($conn, $check_sql);
        $newStatus = (mysqli_fetch_assoc($result)['count'] ?? 0) > 0 ? 2 : 4;

        // 3. Update the order itself
        $updateParts = [
            "status = '$newStatus'",
            "pickup_name = '$pickup_name'",
            "delivered_date = NOW()",
            "is_edited = '0'"
        ];
        $update_sql = "UPDATE orders SET " . implode(", ", $updateParts) . " WHERE orderid = '$orderid'";
        if (!mysqli_query($conn, $update_sql)) {
            echo json_encode(['success' => false, 'message' => 'Failed to update order.', 'error' => mysqli_error($conn)]);
            exit();
        }

        // 4. Manual payment (optional)
        $payment_amount = floatval($_POST['payment_amount'] ?? 0);
        $payment_method = mysqli_real_escape_string($conn, $_POST['type'] ?? 'cash');
        $reference_no = mysqli_real_escape_string($conn, $_POST['reference_no'] ?? '');
        $check_number = mysqli_real_escape_string($conn, $_POST['check_no'] ?? '');
        $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
        $paid_by = mysqli_real_escape_string($conn, $order_details['customerid']);
        $cashier = $_SESSION['userid'] ?? 0;
        $check_no_sql = $payment_method === 'check' ? "'$check_number'" : "NULL";

        $success = true;

        // Handle job payment ledger
        if ($payment_amount > 0) {
            $ledger_query = "
                SELECT l.ledger_id, l.amount AS credit_amount,
                    IFNULL(SUM(p.amount), 0) AS total_paid
                FROM job_ledger l
                LEFT JOIN job_payment p ON l.ledger_id = p.ledger_id
                WHERE l.reference_no = '$orderid'
                GROUP BY l.ledger_id
                ORDER BY l.created_at ASC
            ";
            $ledger_result = mysqli_query($conn, $ledger_query);

            if (!$ledger_result) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to fetch ledger entries.',
                    'error' => mysqli_error($conn)
                ]);
                exit();
            }

            $remaining_payment = $payment_amount;

            while ($row = mysqli_fetch_assoc($ledger_result)) {
                $ledger_id = $row['ledger_id'];
                $credit = floatval($row['credit_amount']);
                $paid = floatval($row['total_paid']);
                $balance = max(0, $credit - $paid);

                if ($balance <= 0 || $remaining_payment <= 0) continue;

                $to_pay = min($balance, $remaining_payment);

                $insert = "
                    INSERT INTO job_payment (
                        ledger_id, amount, payment_method, check_number,
                        reference_no, description, created_by, cashier
                    ) VALUES (
                        '$ledger_id',
                        '$to_pay',
                        '$payment_method',
                        $check_no_sql,
                        '$orderid',
                        '$description',
                        '$paid_by',
                        '$cashier'
                    )
                ";
                if (!mysqli_query($conn, $insert)) {
                    $success = false;
                    break;
                }

                $remaining_payment -= $to_pay;
            }

            if (!$success) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to record payment.',
                    'error' => mysqli_error($conn)
                ]);
                exit();
            }
        }

        //query to check order product
        $product_query = "
            SELECT op.id, op.discounted_price
            FROM order_product op
            INNER JOIN orders o ON op.orderid = o.orderid
            WHERE op.id IN ($idList)
            ORDER BY op.discounted_price ASC, o.order_date ASC
        ";
        $product_result = mysqli_query($conn, $product_query);

        $remaining_payment = $payment_amount;

        while ($row = mysqli_fetch_assoc($product_result)) {
            $product_id = $row['id'];
            $price = floatval($row['discounted_price']);

            if (bccomp($remaining_payment, $price, 2) >= 0) {
                mysqli_query($conn, "
                    UPDATE order_product
                    SET paid_status = 1
                    WHERE id = $product_id
                ");
                $remaining_payment = bcsub($remaining_payment, $price, 2); // precise subtraction
            } else {
                break;
            }
        }

        // Final success response
        echo json_encode([
            'success' => true,
            'message' => "Order marked as picked up" . ($payment_amount > 0 ? " and payment recorded." : " with $0 due."),
            'id' => $orderid
        ]);

    }

    mysqli_close($conn);
}
?>
