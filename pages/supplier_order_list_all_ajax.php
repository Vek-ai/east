<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "fetch_view_modal") {
        $supplier_order_id = mysqli_real_escape_string($conn, $_POST['id']);
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
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php
                            $query = "SELECT * FROM supplier_orders WHERE supplier_order_id = '$supplier_order_id'";
                            $result = mysqli_query($conn, $query);
                            
                            if ($result && mysqli_num_rows($result) > 0) {
                                $row = mysqli_fetch_assoc($result);
                            
                                $status_code = $row['status'];

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
                                ?>
                                <div class="d-flex justify-content-end align-items-center gap-3 p-3">
                                    <?php if ($status_code == 1): ?>
                                    <?php elseif ($status_code == 2): ?>
                                        <button type="button" id="returnBtn" class="btn btn-warning" data-id="<?=$supplier_order_id?>" data-action="return_for_approval">Return for Approval</button>
                                        <button type="button" id="finalizeBtn" class="btn btn-success" data-id="<?=$supplier_order_id?>" data-action="finalize_order">Finalize</button>
                                    <?php elseif ($status_code == 3): ?>
                                    <?php elseif ($status_code == 4): ?>
                                    <?php elseif ($status_code == 5): ?>
                                    <?php elseif ($status_code == 6): ?>
                                        <button type="button" id="markDelivered" class="btn btn-success" data-id="<?=$supplier_order_id?>" data-action="order_delivered">Mark as Delivered</button>
                                    <?php endif; ?>
                                </div>
                            <?php 
                            } 
                            ?>
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

    if ($action == "update_status") {
        $orderId = mysqli_real_escape_string($conn, $_POST['id']);
        $method = mysqli_real_escape_string($conn, $_POST['method']); 
    
        $response = ['success' => false, 'message' => 'Unknown error'];
    
        if ($method == "return_for_approval") {
            $newStatus = 3;
        } elseif ($method == "finalize_order") {
            $newStatus = 4;
        } elseif ($method == "order_delivered") {
            $newStatus = 7;
        } else {
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            exit();
        }
        
        $sql = "UPDATE supplier_orders SET status = $newStatus WHERE supplier_order_id = $orderId";
        
        if (mysqli_query($conn, $sql)) {
            $response['success'] = true;
            $response['message'] = 'Order status updated successfully!';
        } else {
            $response['message'] = 'Error updating order status.';
        }
    
        echo json_encode($response);
    }
    
    mysqli_close($conn);
}
?>
