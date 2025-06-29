<?php
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
                    <?php if ($status_code == 2): ?>
                        <div class="d-flex justify-content-end align-items-center gap-3 flex-wrap mt-3">
                            <button type="button" id="shipOrderBtn" class="btn btn-primary" data-id="<?=$orderid?>" data-action="ship_order">Ship Order</button>
                        </div>
                    <?php endif; ?>
                </div>
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
                                                <div class="d-flex flex-column">
                                                    <input type="text" class="form-control mb-1" placeholder="Width" name="custom_width[<?= $row['id'] ?>]" value="<?= htmlspecialchars($row['custom_width']) ?>">
                                                    <input type="text" class="form-control" placeholder="Height" name="custom_height[<?= $row['id'] ?>]" value="<?= htmlspecialchars($row['custom_height']) ?>">
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
            <?php
        }
    } 

    if ($action == 'save_edited_order') {
        $rawData = $_POST['order_data'] ?? '';
        
        $decoded = json_decode($rawData, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<pre>";
            print_r($decoded);
            echo "</pre>";
        } else {
            echo "Invalid JSON: " . json_last_error_msg();
        }
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

            $response['success'] = true;
            $response['id'] = $id;
            $response['key'] = $est_key;

            echo json_encode($response);
    }

    if ($action == "update_status") {
        $orderid = mysqli_real_escape_string($conn, $_POST['id']);
        $method = mysqli_real_escape_string($conn, $_POST['method']); 

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

            } elseif ($method == "ship_order") {
                $subject = "EKM has shipped your order";

                $selectedProds = $_POST['selected_prods'] ?? [];
                $selectedProds = is_string($selectedProds) ? json_decode($selectedProds, true) : $selectedProds;
                $selectedProds = is_array($selectedProds) ? $selectedProds : [];
                $cleanedProds = array_map('intval', $selectedProds);

                $newStatus = 3;

                if (!empty($cleanedProds)) {
                    $idList = implode(',', $cleanedProds);
                    
                    $sql = "UPDATE order_product SET status = 3 WHERE id IN ($idList)";
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
            
            $sql = "UPDATE orders SET " . implode(", ", $updateParts) . " WHERE orderid = $orderid";
            
            if (mysqli_query($conn, $sql)) {
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

    mysqli_close($conn);
}
?>
