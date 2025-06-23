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
        $estimateid = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM estimate_prod WHERE estimateid = '$estimateid'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $estimate_details = getEstimateDetails($estimateid);
            $status_code = $estimate_details['status'];

            $tracking_number = $estimate_details['tracking_number'];
            $shipping_comp_details = getShippingCompanyDetails($estimate_details['shipping_company']);
            $shipping_company = $shipping_comp_details['shipping_company'];

            $totalquantity = $total_actual_price = $total_disc_price = $total_amount = 0;
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
            <div class="modal-dialog modal-xl" style="width:90% !important">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">
                            View Estimate <?= $tracking_number ?> || <?= $shipping_company ?>
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div id="update_product" class="form-horizontal">
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body datatables">
                                    <div class="estimate-details table-responsive text-nowrap">
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
                                                    <th class="text-end">Actual Price</th>
                                                    <th class="text-end">Disc Price</th>
                                                    <th class="text-end">Total</th>
                                                    <?php if($status_code == 3){ ?>
                                                        <th class="text-center">Action</th>
                                                    <?php } ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                    $is_processing = false;
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        $estimateid = $row['estimateid'];
                                                        $product_details = getProductDetails($row['product_id']);

                                                        $product_name = '';
                                                        if(!empty($row['product_item'])){
                                                            $product_name = $row['product_item'];
                                                        }else{
                                                            $product_name = getProductName($row['product_id']);
                                                        }

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
                                                    <td><?= $product_name ?></td>
                                                    <td>
                                                        <div class="d-flex mb-0 gap-8">
                                                            <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:<?= getColorHexFromProdID($row['custom_color'])?>"></a>
                                                            <?= getColorFromID($row['custom_color']); ?>
                                                        </div>
                                                    </td>
                                                    <td><?= getGradeName($product_details['grade']); ?></td>
                                                    <td><?= getProfileTypeName($product_details['profile']); ?></td>
                                                    <td><?= $row['quantity'] ?></td>
                                                    <td>
                                                        <span class="<?= $status_prod['class']; ?> fw-bond"><?= $status_prod['label']; ?></span>
                                                    </td>
                                                    <td class="text-end">$ <?= number_format(floatval($row['actual_price']),2) ?></td>
                                                    <td class="text-end">$ <?= number_format(floatval($row['discounted_price']),2) ?></td>
                                                    <td class="text-end">$ <?= number_format(floatval($row['discounted_price'] * $row['quantity']),2) ?></td>
                                                    <?php if($status_code == 3){ ?>
                                                        <td class="text-center">
                                                            <a class="fs-6 text-muted btn-edit" href="javascript:void(0)" 
                                                            data-id="<?= $row['id'] ?>" 
                                                            data-name="<?= getProductName($row['product_id']) ?>" 
                                                            data-quantity="<?= $row['quantity'] ?>"
                                                            data-price="<?= $row['discounted_price'] ?>" 
                                                            data-color="<?= $row['custom_color'] ?>">
                                                                <i class="ti ti-edit"></i>
                                                            </a>
                                                        </td>
                                                    <?php } ?>
                                                </tr>
                                                <?php
                                                        $totalquantity += $row['quantity'] ;
                                                        $total_actual_price += floatval($row['actual_price']);
                                                        $total_disc_price += floatval($row['discounted_price']);
                                                        $total_amount += floatval($row['discounted_price']) * $row['quantity'];
                                                    }
                                                ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="6" class="text-end">Total</td>
                                                    <td><?= $totalquantity ?></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td class="text-end">$ <?= number_format($total_amount,2) ?></td>
                                                    <?php
                                                    if($status_code == 3){
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
                                <div class="d-flex justify-content-end align-items-center gap-3 p-3 flex-wrap">
                                    <div class="d-flex justify-content-end align-items-center gap-3">
                                        <?php if ($status_code == 1): ?>
                                            <button type="button" id="email_estimate_btn" class="btn btn-primary email_estimate_btn" data-customer="<?= $estimate_details["customerid"]; ?>" data-id="<?= $estimate_details["estimateid"]; ?>">
                                                <i class="fa fa-envelope fs-5"></i> Send Email
                                            </button>
                                        <?php elseif ($status_code == 3): ?>
                                            <button type="button" id="resendBtn" class="btn btn-warning <?= $is_edited != 1 ? 'd-none' : '' ?>" data-id="<?=$estimateid?>" data-action="submit_for_approval">Return for Approval</button>
                                            <button type="button" id="AcceptBtn" class="btn btn-success" data-id="<?=$estimateid?>" data-action="accept_estimate">Accept</button>
                                        <?php elseif ($status_code == 4): ?>
                                            <button type="button" id="processOrderBtn" class="btn btn-info" data-id="<?=$estimateid?>" data-action="process_order">Process Order</button>
                                        <?php elseif ($status_code == 5 || $is_processing): ?>
                                            <button type="button" id="shipOrderBtn" class="btn btn-primary" data-id="<?=$estimateid?>" data-action="ship_order">Ship Order</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                                        $query_paint_colors = "SELECT * FROM paint_colors 
                                                            WHERE hidden = '0' AND color_status = '1' 
                                                            GROUP BY color_name 
                                                            ORDER BY color_name ASC";

                                        $result_paint_colors = mysqli_query($conn, $query_paint_colors);            

                                        while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                        ?>
                                            <option value="<?= $row_paint_colors['color_id'] ?>" 
                                                    data-color="<?= getColorHexFromColorID($row_paint_colors['color_id']) ?>">
                                                <?= $row_paint_colors['color_name'] ?>
                                            </option>
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
            <script>
                $(document).ready(function() {
                    $('#est_dtls_tbl').DataTable({
                        language: {
                            emptyTable: "Estimate Details not found"
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
    
    mysqli_close($conn);
}
?>
