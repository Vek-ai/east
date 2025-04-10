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
            $response = array();
            ?>
            <style>
                #est_dtls_tbl {
                    width: 100% !important;
                }

                #est_dtls_tbl td, #est_dtls_tbl th {
                    white-space: normal !important;
                    word-wrap: break-word;
                }
            </style>
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">
                            View Order
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div id="update_product" class="form-horizontal">
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body datatables">
                                    <div class="order-details table-responsive text-nowrap">
                                        <table id="est_dtls_tbl" class="table table-hover mb-0 text-md-nowrap w-100">
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

                                                        if($status_prod_db == '1'){
                                                            $is_processing = true;
                                                        }

                                                        $status_prod_labels = [
                                                            0 => ['label' => 'New', 'class' => 'badge bg-primary'],
                                                            1 => ['label' => 'Processing', 'class' => 'badge bg-success'],
                                                            2 => ['label' => 'Waiting for Dispatch', 'class' => 'badge bg-warning'],
                                                            3 => ['label' => 'In Transit', 'class' => 'badge bg-secondary'],
                                                            4 => ['label' => 'Delivered', 'class' => 'badge bg-success']
                                                        ];

                                                        $status_prod = $status_prod_labels[$status_prod_db];
                                                    ?> 
                                                        <tr> 
                                                            <td class="text-center">
                                                                <input type="checkbox" class="row-checkbox" value="<?= $row['id'] ?>" data-status="">
                                                            </td>
                                                            <td>
                                                                <?php echo getProductName($row['productid']) ?>
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
                                    <?php if ($status_code == 1): ?>
                                        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mt-3">
                                            <button type="button" id="email_order_btn" class="btn btn-primary email_order_btn" data-customer="<?= $order_details["customerid"]; ?>" data-id="<?= $orderid; ?>">
                                                <i class="fa fa-envelope fs-5"></i> Send Confirmation Email
                                            </button>
                                            <button type="button" id="processOrderBtn" class="btn btn-info" data-id="<?=$orderid?>" data-action="process_order">Process Order</button>
                                        </div>
                                    <?php elseif ($status_code == 2): ?>
                                        <div class="d-flex justify-content-end align-items-center gap-3 flex-wrap mt-3">
                                            <button type="button" id="shipOrderBtn" class="btn btn-primary" data-id="<?=$orderid?>" data-action="ship_order">Ship Order</button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                $(document).ready(function() {
                    $('#est_dtls_tbl').DataTable({
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
                        console.log("Selected IDs:", ids);
                        return ids;
                    };
                });
            </script>

            <?php
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

        $subject = "EKM has confirmed your order.";
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
                        <a href='https://metal.ilearnwebtech.com/customer/index.php?page=order&id=$id&key=$order_key' class='link' target='_blank'>To view order details, click this link</a>
                    </div>
                </body>
                </html>
                ";

            if($primary_contact == 2){
                if(!empty($customer_phone)){
                    $response = sendPhoneMessage($customer_email, $customer_name, $subject, $message);
                    if ($response['success'] == true) {
                        echo json_encode([
                            'success' => true,
                            'msg_success' => true,
                            'message' => "Successfully sent message to $customer_name for confirmation on orders.",
                            'id' => $id,
                            'key' => $order_key
                        ]);
                    } else {
                        echo json_encode([
                            'success' => true,
                            'msg_success' => false,
                            'message' => "Successfully saved, but message could not be sent to $customer_name.",
                            'error' => $response['error'],
                            'id' => $id,
                            'key' => $order_key
                        ]);
                    }
                } else {
                    echo json_encode([
                        'success' => true,
                        'msg_success' => false,
                        'message' => "Successfully saved, but message could not be sent to $customer_name.",
                        'error' => $response['error'],
                        'id' => $id,
                        'key' => $order_key
                    ]);
                }
            }else{
                if(!empty($customer_email)){
                    $response = sendEmail($customer_email, $customer_name, $subject, $message);
                    if ($response['success'] == true) {
                        echo json_encode([
                            'success' => true,
                            'email_success' => true,
                            'message' => "Successfully sent email to $customer_name for confirmation on orders.",
                            'id' => $id,
                            'key' => $order_key
                        ]);
                    } else {
                        echo json_encode([
                            'success' => true,
                            'email_success' => false,
                            'message' => "Successfully saved, but email could not be sent to $customer_name.",
                            'error' => $response['error'],
                            'id' => $id,
                            'key' => $order_key
                        ]);
                    }
    
                }else {
                    echo json_encode([
                        'success' => true,
                        'email_success' => false,
                        'message' => "Successfully saved, but email could not be sent to $customer_name.",
                        'error' => $response['error'],
                        'id' => $id,
                        'key' => $order_key
                    ]);
                }
            }
    }

    if ($action == "update_status") {
        $orderid = mysqli_real_escape_string($conn, $_POST['id']);
        $method = mysqli_real_escape_string($conn, $_POST['method']); 
        
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
            
            $key = $row['order_key'];
        
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
            
            $sql = "UPDATE orders SET status = $newStatus, is_edited = '0' WHERE orderid = $orderid";
            
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

                    if($primary_contact == 2){
                        if(!empty($customer_phone)){
                            $response = sendPhoneMessage($customer_email, $customer_name, $subject, $message);
                            if ($response['success'] == true) {
                                echo json_encode([
                                    'success' => true,
                                    'msg_success' => true,
                                    'message' => "Successfully sent message to $customer_name for confirmation on orders.",
                                    'id' => $orderid,
                                    'key' => $order_key
                                ]);
                            } else {
                                echo json_encode([
                                    'success' => true,
                                    'msg_success' => false,
                                    'message' => "Successfully saved, but message could not be sent to $customer_name.",
                                    'error' => $response['error'],
                                    'id' => $orderid,
                                    'key' => $order_key
                                ]);
                            }
                        } else {
                            echo json_encode([
                                'success' => true,
                                'msg_success' => false,
                                'message' => "Successfully saved, but message could not be sent to $customer_name.",
                                'error' => $response['error'],
                                'id' => $orderid,
                                'key' => $order_key
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
                                    'key' => $order_key
                                ]);
                            } else {
                                echo json_encode([
                                    'success' => true,
                                    'email_success' => false,
                                    'message' => "Successfully updated status, but email could not be sent to $customer_name.",
                                    'error' => $response['error'],
                                    'id' => $orderid,
                                    'key' => $order_key
                                ]);
                            }
            
                        }else {
                            echo json_encode([
                                'success' => true,
                                'email_success' => false,
                                'message' => "Successfully updated status, but email could not be sent to $customer_name.",
                                'error' => $response['error'],
                                'id' => $orderid,
                                'key' => $order_key
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
                    'key' => $order_key
                ]);
            }
        }
    }

    mysqli_close($conn);
}
?>
