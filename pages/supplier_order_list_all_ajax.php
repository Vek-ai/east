<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/send_email.php';

$emailSender = new EmailTemplates();

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "fetch_view_modal") {
        $supplier_order_id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM supplier_orders WHERE supplier_order_id = '$supplier_order_id'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            $supplier_id = $row['supplier_id'];
            $status_code = $row['status'];

            $is_edited = $row['is_edited'];

            $status_labels = [
                1 => ['label' => 'New Order', 'class' => 'badge bg-primary'],
                2 => ['label' => 'Pending EKM Approval', 'class' => 'badge bg-warning text-dark'],
                3 => ['label' => 'Pending Supplier Approval', 'class' => 'badge bg-warning text-dark'],
                4 => ['label' => 'Approved, Waiting to Process', 'class' => 'badge bg-secondary'],
                5 => ['label' => 'Processing', 'class' => 'badge bg-success'],
                6 => ['label' => 'In Transit', 'class' => 'badge bg-info'],
                7 => ['label' => 'Delivered', 'class' => 'badge bg-success']
            ];
        
            $status = $status_labels[$status_code];
                        
            $query = "SELECT * FROM supplier_orders_prod WHERE supplier_order_id = '$supplier_order_id'";
            $result = mysqli_query($conn, $query);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $totalquantity = $total_actual_price = $total_disc_price = 0;
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
                <div id="update_product" class="form-horizontal">
                    <div class="modal-body mt-0 pt-0">
                        <div class="card">
                            <div class="card-body datatables">
                                <div class="order-details table-responsive text-nowrap">
                                    <table id="sup_ord_tbl" class="table table-hover mb-0 text-md-nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>Description</th>
                                                <th>Color</th>
                                                <th>Quantity</th>
                                                <th class="text-right">Unit Price</th>
                                                <th class="text-right">Amount</th>
                                                <?php
                                                if($status_code == 2){
                                                ?>
                                                    <th class="text-center">Action</th>
                                                <?php
                                                }
                                                ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    $product_details = getProductDetails($row['product_id']);
                                                ?> 
                                                    <tr> 
                                                        <td>
                                                            <?= getProductName($row['product_id']) ?>
                                                        </td>
                                                        <td>
                                                        <div class="d-flex mb-0 gap-8">
                                                            <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:<?= getColorHexFromColorID($row['color'])?>"></a>
                                                            <?= getColorName($row['color']); ?>
                                                        </div>
                                                        </td>
                                                        <td>
                                                            <?= $row['quantity'] ?>
                                                        </td>
                                                        <td class="text-end">
                                                            $ <?= number_format($product_details['unit_price'],2) ?>
                                                        </td>
                                                        <td class="text-end">
                                                            $ <?= number_format(floatval($row['quantity']) * floatval($product_details['unit_price']), 2) ?>
                                                        </td>
                                                        <?php
                                                        if($status_code == 2){
                                                        ?>
                                                            <td class="text-center">
                                                                <a class="fs-6 text-muted btn-edit" href="javascript:void(0)" 
                                                                data-id="<?= $row['id'] ?>" 
                                                                data-name="<?= getProductName($row['product_id']) ?>" 
                                                                data-quantity="<?= $row['quantity'] ?>"
                                                                data-price="<?= $row['price'] ?>" 
                                                                data-color="<?= $row['color'] ?>">
                                                                    <i class="ti ti-edit"></i>
                                                                </a>
                                                            </td>
                                                        <?php
                                                        }
                                                        ?>
                                                    </tr>
                                            <?php
                                                    $totalquantity += $row['quantity'] ;
                                                    $total_actual_price += $product_details['unit_price'] * $row['quantity'];
                                                }
                                            
                                            ?>
                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <td colspan="2" class="text-end">Total Quantity:</td>
                                                <td><?= $totalquantity ?></td>
                                                <td class="text-end">Total Amount:</td>
                                                <td class="text-end">$ <?= number_format($total_actual_price,2) ?></td>
                                                <?php
                                                if($status_code == 2){
                                                ?>
                                                    <td></td>
                                                <?php
                                                }
                                                ?>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end align-items-center gap-3 p-3">
                            <?php if ($status_code == 1): ?>
                            <?php elseif ($status_code == 2): ?>
                                <button type="button" id="returnBtn" class="btn btn-warning <?= $is_edited != 1 ? 'd-none' : '' ?>" data-id="<?=$supplier_order_id?>" data-action="return_for_approval">Return for Approval</button>
                                <button type="button" id="finalizeBtn" class="btn btn-success" data-id="<?=$supplier_order_id?>" data-action="finalize_order">Finalize</button>
                            <?php elseif ($status_code == 3): ?>
                            <?php elseif ($status_code == 4): ?>
                            <?php elseif ($status_code == 5): ?>
                            <?php elseif ($status_code == 6): ?>
                                <button type="button" id="markDelivered" class="btn btn-success" data-id="<?=$supplier_order_id?>" data-action="order_delivered">Mark as Received</button>
                            <?php endif; ?>
                        </div>

                        <div class="modal fade" id="editProductModal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
                            <div class="modal-dialog modal-md">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editProductModalLabel">Edit Order</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form id="editProductForm">
                                        <div class="modal-body">
                                            <input type="hidden" id="editId" name="id">

                                            <h4 class="fw-bold" id="editProductName"></h4>

                                            <div class="mb-3">
                                                <label for="editProductQuantity" class="form-label">Quantity</label>
                                                <input type="number" class="form-control" id="editProductQuantity" name="quantity" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="editProductPrice" class="form-label">Unit Price</label>
                                                <input type="text" class="form-control" id="editProductPrice" name="price" required>
                                            </div>

                                            <div>
                                            <label for="editProductColor" class="form-label">Color</label>
                                            <div class="mb-3">
                                                <select class="form-select select2" id="editProductColor" name="color">
                                                    <option value="">Select Color...</option>
                                                    <?php
                                                    $query_roles = "SELECT * FROM supplier_color WHERE status = '1' AND hidden = '0' AND supplierid = '$supplier_id' ORDER BY `color` ASC";
                                                    $result_roles = mysqli_query($conn, $query_roles);            
                                                    while ($row_color = mysqli_fetch_array($result_roles)) {
                                                    ?>
                                                        <option value="<?= $row_color['color_id'] ?>" data-color="<?= $row_color['color_hex'] ?>"><?= $row_color['color'] ?></option>
                                                    <?php   
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            </div>
                                            
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    $(document).ready(function() {
                        $('#sup_ord_tbl').DataTable({
                            language: {
                                emptyTable: "Supplier Order Details not found"
                            },
                            autoWidth: false,
                            responsive: true,
                            lengthChange: false
                        });
                    });
                </script>

                <?php
            }
        }
    } 

    if ($action == "update_status") {
        $orderId = mysqli_real_escape_string($conn, $_POST['id']);
        $method = mysqli_real_escape_string($conn, $_POST['method']); 

        $is_edited = '0';

        $query = "SELECT * FROM supplier_orders WHERE supplier_order_id = '$orderId'";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $supplier_id = $row['supplier_id'];
            $supplier_details = getSupplierDetails($supplier_id);
            $supplier_name = $supplier_details['supplier_name'];
            $supplier_email = $supplier_details['contact_email'];
            $key = $row['order_key'];
        
            if ($method == "return_for_approval") {
                $newStatus = 3;
                $subject = "EKM has made adjustments and requests for approval";
            } elseif ($method == "finalize_order") {
                $newStatus = 4;
                $subject = "EKM has confirmed the order and awaiting for processing";
            } elseif ($method == "order_delivered") {
                $newStatus = 7;
                $subject = "EKM has received the order";

                $query_prod = "SELECT * FROM supplier_orders_prod WHERE supplier_order_id = '$orderId'";
                $result_prod = mysqli_query($conn, $query_prod);

                if (!$result_prod) {
                    echo json_encode([
                        'success' => false,
                        'email_success' => false,
                        'message' => "Failed to fetch supplier order products",
                        'error' => mysqli_error($conn)
                    ]);
                    exit;
                }

                while ($row_prod = mysqli_fetch_assoc($result_prod)) {
                    $supplier_orders_prod_id = $row_prod['id'];
                    $product_id = $row_prod['product_id'];
                    $quantity = $row_prod['quantity'];

                    $sql = "INSERT INTO staging_bin (supplier_orders_prod_id, product_id, quantity)
                            VALUES ('$supplier_orders_prod_id', '$product_id', '$quantity')";
                    
                    if (!mysqli_query($conn, $sql)) {
                        echo json_encode([
                            'success' => false,
                            'email_success' => false,
                            'message' => "Failed to add products to staging bin",
                            'error' => mysqli_error($conn)
                        ]);
                        exit;
                    }
                }

            } else {
                echo json_encode([
                    'success' => false,
                    'email_success' => false,
                    'message' => "Invalid action",
                    'error' => "Invalid action"
                ]);
                exit();
            }
            
            $sql = "UPDATE supplier_orders SET status = $newStatus, is_edited = '0' WHERE supplier_order_id = $orderId";
            
            if (mysqli_query($conn, $sql)) {
                $link = "https://metal.ilearnwebtech.com/supplier/index.php?id=$orderId&key=$key";
                $response_email = $emailSender->sendSupplierNotif($supplier_email, $subject, $link);

                if ($response_email['success'] == true) {
                    echo json_encode([
                        'success' => true,
                        'email_success' => true,
                        'message' => "Successfully sent email to $supplier_name for confirmation on orders."
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'email_success' => false,
                        'message' => "Successfully saved, but email could not be sent to $supplier_name.",
                        'error' => addslashes($response_email['error'])
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'email_success' => false,
                    'message' => "Error updating order status.",
                    'error' => "Error updating order status."
                ]);
            }
        }
    }

    if ($action == "update_product") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
        $price = mysqli_real_escape_string($conn, $_POST['price']);
        $color = mysqli_real_escape_string($conn, $_POST['color']);

        $orderId = 0;
        $query = "SELECT * FROM supplier_orders_prod WHERE id = '$id'";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $orderId = $row['supplier_order_id'];
        }

        $sql = "UPDATE supplier_orders SET is_edited = '1' WHERE supplier_order_id = $orderId";
        mysqli_query($conn, $sql);
    
        $query = "UPDATE supplier_orders_prod 
                  SET quantity = '$quantity', price = '$price', color = '$color' 
                  WHERE id = '$id'";
    
        if (mysqli_query($conn, $query)) {
            echo json_encode(["success" => true, "sql" => $query]);
        } else {
            echo json_encode(["success" => false, "error" => mysqli_error($conn)]);
        }
    }    
    
    mysqli_close($conn);
}
?>
