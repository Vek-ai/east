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
                                        <?php if ($status_code == 3): ?>
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

    if ($action == "fetch_changes_modal") {
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
                        View Estimate Changes
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="update_product" class="form-horizontal">
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-body datatables">
                                <div class="estimate-details table-responsive text-nowrap">
                                    <table id="est_changes_tbl" class="table table-hover mb-0 text-md-nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Action</th>
                                                <th>User</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $estimateid = mysqli_real_escape_string($conn, $_POST['id']);
                                            $query = "SELECT * FROM estimate_changes WHERE estimate_id = '$estimateid'";
                                            $result = mysqli_query($conn, $query);
                                            
                                            if ($result && mysqli_num_rows($result) > 0) {
                                                $response = array();
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    $estimateid = $row['estimateid'];
                                                    $product_details = getProductDetails($row['product_id']);
                                                ?> 
                                                    <tr> 
                                                        <td>
                                                            <?php echo getProductName($row['product_id']) ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $row['action'] ?>
                                                        </td>
                                                        <td>
                                                            <?php echo get_staff_name($row['user']) ?>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                                if (isset($row["date_changed"]) && !empty($row["date_changed"]) && $row["date_changed"] !== '0000-00-00 00:00:00') {
                                                                    echo date("m/d/Y", strtotime($row["date_changed"]));
                                                                } else {
                                                                    echo '';
                                                                }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                                if (isset($row["date_changed"]) && !empty($row["date_changed"]) && $row["date_changed"] !== '0000-00-00 00:00:00') {
                                                                    echo date("h:i A", strtotime($row["date_changed"]));
                                                                } else {
                                                                    echo '';
                                                                }
                                                            ?>
                                                        </td>
                                                        
                                                    </tr>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $('#est_changes_tbl').DataTable({
                    language: {
                        emptyTable: "Estimate details unchanged"
                    },
                    autoWidth: false,
                    responsive: true,
                    lengthChange: false
                });

                $('#viewChangesModal').on('shown.bs.modal', function () {
                    $('#est_changes_tbl').DataTable().columns.adjust().responsive.recalc();
                });
            });
        </script>
        <?php
    } 

    if ($action == "fetch_edit_modal") {
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
                                                <td colspan="5" class="text-end">Total</td>
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
                                    <button type="button" id="editEstimate" class="btn btn-info" data-id="<?=$estimateid?>">Edit Estimate</button>
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

        <?php
    }
    }

    if ($action == 'fetch_product_fields') {
        $product_category_id = mysqli_real_escape_string($conn, $_POST['product_category_id']);
        $query = "SELECT * FROM product_fields WHERE product_category_id='$product_category_id'";
        $result = mysqli_query($conn, $query);
    
        if ($result) {
            $fields = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $fields[] = $row;
            }
            echo json_encode($fields);
        } else {
            echo 'error';
        }
    }

    if ($action == 'search_product') {
        $searchQuery = isset($_REQUEST['query']) ? mysqli_real_escape_string($conn, $_REQUEST['query']) : '';
        $color_id = isset($_REQUEST['type_id']) ? mysqli_real_escape_string($conn, $_REQUEST['color_id']) : '';
        $type_id = isset($_REQUEST['type_id']) ? mysqli_real_escape_string($conn, $_REQUEST['type_id']) : '';
        $line_id = isset($_REQUEST['line_id']) ? mysqli_real_escape_string($conn, $_REQUEST['line_id']) : '';
        $category_id = isset($_REQUEST['category_id']) ? mysqli_real_escape_string($conn, $_REQUEST['category_id']) : '';
        $onlyInStock = isset($_REQUEST['onlyInStock']) ? filter_var($_REQUEST['onlyInStock'], FILTER_VALIDATE_BOOLEAN) : false;
        
        $query_product = "
            SELECT 
                p.*,
                COALESCE(SUM(i.quantity_ttl), 0) AS total_quantity
            FROM 
                product AS p
            LEFT JOIN 
                inventory AS i ON p.product_id = i.product_id
            WHERE 
                p.hidden = '0'
        ";
    
        if (!empty($searchQuery)) {
            $query_product .= " AND (p.product_item LIKE '%$searchQuery%' OR p.description LIKE '%$searchQuery%')";
        }
    
        if (!empty($color_id)) {
            $query_product .= " AND p.color = '$color_id'";
        }
    
        if (!empty($type_id)) {
            $query_product .= " AND p.product_type = '$type_id'";
        }
    
        if (!empty($line_id)) {
            $query_product .= " AND p.product_line = '$line_id'";
        }
    
        if (!empty($category_id)) {
            $query_product .= " AND p.product_category = '$category_id'";
        }
    
        $query_product .= " GROUP BY p.product_id";
    
        if ($onlyInStock) {
            $query_product .= " HAVING total_quantity > 1";
        }
    
        $result_product = mysqli_query($conn, $query_product);
    
        $tableHTML = "";
    
        if (mysqli_num_rows($result_product) > 0) {
            while ($row_product = mysqli_fetch_array($result_product)) {
    
                $product_length = $row_product['length'];
                $product_width = $row_product['width'];
                $product_color = $row_product['color'];
    
                $dimensions = "";
    
                if (!empty($product_length) || !empty($product_width)) {
                    $dimensions = '';
                
                    if (!empty($product_length)) {
                        $dimensions .= $product_length;
                    }
                
                    if (!empty($product_width)) {
                        if (!empty($dimensions)) {
                            $dimensions .= " X ";
                        }
                        $dimensions .= $product_width;
                    }
                
                    if (!empty($dimensions)) {
                        $dimensions = " - " . $dimensions;
                    }
                }
    
                if ($row_product['total_quantity'] > 0) {
                    $stock_text = '
                        <a href="javascript:void(0);" id="view_in_stock" data-id="' . $row_product['product_id'] . '" class="d-flex align-items-center">
                            <span class="text-bg-success p-1 rounded-circle"></span>
                            <span class="ms-2">In Stock</span>
                        </a>';
                } else {
                    $stock_text = '
                        <a href="javascript:void(0);" id="view_out_of_stock" data-id="' . $row_product['product_id'] . '" class="d-flex align-items-center">
                            <span class="text-bg-danger p-1 rounded-circle"></span>
                            <span class="ms-2">Out of Stock</span>
                        </a>';
                
                    if ($row_product['product_category'] == $trim_id || $row_product['product_category'] == $panel_id) {
                        $sql = "SELECT COUNT(*) AS count FROM coil WHERE color = '$product_color'";
                        $result = mysqli_query($conn, $sql);
    
                        if ($result) {
                            $row = mysqli_fetch_assoc($result);
                            if ($row['count'] > 0) {
                                $stock_text = '
                                <a href="javascript:void(0);" id="view_available" data-color="' . htmlspecialchars($product_color, ENT_QUOTES) . '" data-width="' . htmlspecialchars($product_width, ENT_QUOTES) . '" class="d-flex align-items-center">
                                    <span class="text-bg-warning p-1 rounded-circle"></span>
                                    <span class="ms-2">Available</span>
                                </a>';
                            }
                        }
                    }
                }
                         
                $default_image = 'images/product/product.jpg';
    
                $picture_path = !empty($row_product['main_image'])
                ?  $row_product['main_image']
                : $default_image;
    
                $tableHTML .= '
                <tr>
                    <td>
                        <a href="javascript:void(0);" id="view_product_details" data-id="' . $row_product['product_id'] . '" class="d-flex align-items-center text-start">
                            <div class="d-flex align-items-center">
                                <img src="'.$picture_path.'" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                                <div class="ms-3">
                                    <h6 class="fw-semibold mb-0 fs-4">'. $row_product['product_item'] .' ' .$dimensions .'</h6>
                                </div>
                            </div>
                        </a>
                    </td>
                    <td>
                        <div class="d-flex mb-0 gap-8 text-center">
                            <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:' .getColorHexFromColorID($row_product['color']) .'" data-toggle="tooltip" data-placement="top" title="'
                            .getColorName($row_product['color']) .'"></a> 
                        </div>
                    </td>
                    <td><p class="mb-0">'. getProductTypeName($row_product['product_type']) .'</p></td>
                    <td><p class="mb-0">'. getProductLineName($row_product['product_line']) .'</p></td>
                    <td><p class="mb-0">'. getProductCategoryName($row_product['product_category']) .'</p></td>
                    <td>
                        <div class="d-flex align-items-center">'.$stock_text.'</div>
                    </td>
                    <td><h6 class="mb-0 fs-4">$'. $row_product['unit_cost'] .'</h6></td>
                    <td>
                        <button class="btn btn-primary btn-add-to-cart px-2 py-0" type="button" data-id="'.$row_product['product_id'].'" onClick="addtoestimate(this)"> + </button>
                    </td>
                </tr>';
            }
        } else {
            $tableHTML .= '<tr><td colspan="8" class="text-center">No products found</td></tr>';
        }
        
        echo $tableHTML;
    }

    if ($action == 'setquantity') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['estimate_id']);
        $quantity = mysqli_real_escape_string($conn, $_POST['qty']);
        $query = "UPDATE estimate_prod SET quantity = '$quantity' WHERE id ='$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $est_prod_details = getEstimateProdDetails($est_prod_id);
            $action = "Set the quantity to $quantity";
            $log_result = log_estimate_changes($est_prod_details['estimateid'], $est_prod_details['product_id'], $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'add_to_estimate') {
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
        $estimate_id = mysqli_real_escape_string($conn, $_POST['estimate_id']);
        $product_details = getProductDetails($product_id);

        $query="INSERT INTO estimate_prod
                        (estimateid, product_id, quantity, custom_width, custom_bend, custom_hem, custom_length, actual_price, discounted_price) 
                VALUES 
                        ('$estimate_id', '$product_id', '1', '".$product_details['width']."', '', '', '".$product_details['length']."', '".$product_details['unit_cost']."', '".$product_details['unit_cost']."')";

        $result = mysqli_query($conn, $query);
        if ($result) {
            $action = "Added product to product estimates List";
            $log_result = log_estimate_changes($estimate_id, $product_id, $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'addquantity') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['estimate_id']);
        $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
        $query = "UPDATE estimate_prod SET quantity = (quantity + 1) WHERE id ='$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $est_prod_details = getEstimateProdDetails($est_prod_id);
            $action = "Added 1 Quantity";
            $log_result = log_estimate_changes($est_prod_details['estimateid'], $est_prod_details['product_id'], $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'deductquantity') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['estimate_id']);
        $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
        $query = "UPDATE estimate_prod SET quantity = (quantity - 1) WHERE id ='$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $est_prod_details = getEstimateProdDetails($est_prod_id);
            $action = "Deducted 1 Quantity";
            $log_result = log_estimate_changes($est_prod_details['estimateid'], $est_prod_details['product_id'], $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'set_estimate_width') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['id']);
        $width = mysqli_real_escape_string($conn, $_POST['width']);
        $query = "UPDATE estimate_prod SET custom_width = '$width' WHERE id ='$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $est_prod_details = getEstimateProdDetails($est_prod_id);
            $action = "Adjusted custom width to $width";
            $log_result = log_estimate_changes($est_prod_details['estimateid'], $est_prod_details['product_id'], $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'set_estimate_length') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['id']);
        $length = mysqli_real_escape_string($conn, $_POST['length']);
        $query = "UPDATE estimate_prod SET custom_length = '$length' WHERE id ='$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $est_prod_details = getEstimateProdDetails($est_prod_id);
            $action = "Adjusted custom length to $length";
            $log_result = log_estimate_changes($est_prod_details['estimateid'], $est_prod_details['product_id'], $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'set_estimate_hem') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['id']);
        $hem = mysqli_real_escape_string($conn, $_POST['hem']);
        $query = "UPDATE estimate_prod SET custom_hem = '$hem' WHERE id ='$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $est_prod_details = getEstimateProdDetails($est_prod_id);
            $action = "Adjusted custom hem to $hem";
            $log_result = log_estimate_changes($est_prod_details['estimateid'], $est_prod_details['product_id'], $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'set_estimate_bend') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['id']);
        $bend = mysqli_real_escape_string($conn, $_POST['bend']);
        $query = "UPDATE estimate_prod SET custom_bend = '$bend' WHERE id ='$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $est_prod_details = getEstimateProdDetails($est_prod_id);
            $action = "Adjusted custom bend to $bend";
            $log_result = log_estimate_changes($est_prod_details['estimateid'], $est_prod_details['product_id'], $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'set_usage') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['id']);
        $usage = mysqli_real_escape_string($conn, $_POST['usage']);
        $query = "UPDATE estimate_prod SET usageid = '$usage' WHERE id ='$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $est_prod_details = getEstimateProdDetails($est_prod_id);
            $action = "Changed product usage to " .getUsageName($est_prod_details['usageid']);
            $log_result = log_estimate_changes($est_prod_details['estimateid'], $est_prod_details['product_id'], $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'set_color') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['id']);
        $color = mysqli_real_escape_string($conn, $_POST['color']);
        $query = "UPDATE estimate_prod SET custom_color = '$color' WHERE id ='$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $est_prod_details = getEstimateProdDetails($est_prod_id);
            $action = "Changed product color to " .getColorName($est_prod_details['custom_color']);
            $log_result = log_estimate_changes($est_prod_details['estimateid'], $est_prod_details['product_id'], $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'deleteitem') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['estimate_id']);
        $est_prod_details = getEstimateProdDetails($est_prod_id);

        $query = "DELETE FROM estimate_prod WHERE id = '$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $action = "Removed product to product estimates List";
            $log_result = log_estimate_changes($est_prod_details['estimateid'], $est_prod_details['product_id'], $action);
            echo 'success';
        } else {
            echo 'error';
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

        $est_key = 'EST' . substr(hash('sha256', uniqid()), 0, 10);

        $sql = "UPDATE estimates SET est_key = '$est_key', status = '2' WHERE estimateid = $id";

        if (!mysqli_query($conn, $sql)) {
            echo json_encode([
                'success' => false,
                'email_success' => false,
                'message' => "Query Failed.",
                "error" => mysqli_error($conn)
            ]);
        }

        $est_url = "https://metal.ilearnwebtech.com/customer/index.php?page=estimate&id=$id&key=$est_key";

        $subject = "EKM has sent an Estimate List for Approval";

        $sms_message = "Hi $customer_name,\n\n$subject\nClick this link to view your receipt:\n$est_url";

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
                        <a href='https://metal.ilearnwebtech.com/customer/index.php?page=estimate&id=$id&key=$est_key' class='link' target='_blank'>To estimate order details, click this link</a>
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
        $estimateid = mysqli_real_escape_string($conn, $_POST['id']);
        $method = mysqli_real_escape_string($conn, $_POST['method']); 
        $tracking_number = mysqli_real_escape_string($conn, $_POST['tracking_number'] ?? ''); 
        $shipping_company = mysqli_real_escape_string($conn, $_POST['shipping_company'] ?? ''); 
        
        $is_edited = '0';

        $query = "SELECT * FROM estimates WHERE estimateid = '$estimateid'";
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
        
            if ($method == "submit_for_approval") {
                $newStatus = 3;
                $subject = "EKM requested for Estimate approval";
            } elseif ($method == "accept_estimate") {
                $newStatus = 4;
                $subject = "EKM has accepted the estimate";
            } elseif ($method == "process_order") {
                $newStatus = 5;
                $subject = "EKM has started to process order";

                $sql = "UPDATE estimate_prod SET status = 1 WHERE estimateid = $estimateid";
                if (!mysqli_query($conn, $sql)) {
                    $response['message'] = 'Error updating order status.';
                }

            } elseif ($method == "ship_order") {
                $subject = "EKM has shipped the order";

                $selectedProds = $_POST['selected_prods'] ?? [];
                $selectedProds = is_string($selectedProds) ? json_decode($selectedProds, true) : $selectedProds;
                $selectedProds = is_array($selectedProds) ? $selectedProds : [];
                $cleanedProds = array_map('intval', $selectedProds);

                if (!empty($cleanedProds)) {
                    $idList = implode(',', $cleanedProds);
                    
                    $sql = "UPDATE estimate_prod SET status = 3 WHERE id IN ($idList)";
                    if (!mysqli_query($conn, $sql)) {
                        $response['message'] = 'Error updating product status.';
                    }
                
                    $sql = "SELECT COUNT(*) AS count FROM estimate_prod WHERE status = 1 AND estimateid = '$estimateid'";
                    $result = mysqli_query($conn, $sql);
                
                    if ($row = mysqli_fetch_assoc($result)) {
                        $newStatus = ($row['count'] > 0) ? 5 : 6;
                    } else {
                        $newStatus = 5;
                    }
                } else {
                    $newStatus = 6;
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
            
            $sql = "UPDATE estimates SET " . implode(", ", $updateParts) . " WHERE estimateid = $estimateid";
            
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
                            <a href='https://metal.ilearnwebtech.com/customer/index.php?page=estimate&id=$id&key=$est_key' class='link' target='_blank'>To estimate order details, click this link</a>
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
                                    'id' => $id,
                                    'key' => $est_key,
                                    'url' => $shipping_url
                                ]);
                            } else {
                                echo json_encode([
                                    'success' => true,
                                    'msg_success' => false,
                                    'message' => "Successfully saved, but message could not be sent to $customer_name.",
                                    'error' => $response['error'],
                                    'id' => $id,
                                    'key' => $est_key,
                                    'url' => $shipping_url
                                ]);
                            }
                        } else {
                            echo json_encode([
                                'success' => true,
                                'msg_success' => false,
                                'message' => "Successfully saved, but message could not be sent to $customer_name.",
                                'error' => $response['error'],
                                'id' => $id,
                                'key' => $est_key,
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
                                    'id' => $id,
                                    'key' => $est_key,
                                    'url' => $shipping_url
                                ]);
                            } else {
                                echo json_encode([
                                    'success' => true,
                                    'email_success' => false,
                                    'message' => "Successfully updated status, but email could not be sent to $customer_name.",
                                    'error' => $response['error'],
                                    'id' => $id,
                                    'key' => $est_key,
                                    'url' => $shipping_url
                                ]);
                            }
            
                        }else {
                            echo json_encode([
                                'success' => true,
                                'email_success' => false,
                                'message' => "Successfully updated status, but email could not be sent to $customer_name.",
                                'error' => $response['error'],
                                'id' => $id,
                                'key' => $est_key,
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
                    'id' => $id,
                    'key' => $est_key,
                    'url' => ''
                ]);
            }
        }
    }

    if ($action == "update_product") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
        $discounted_price = mysqli_real_escape_string($conn, $_POST['price']);
        $custom_color = mysqli_real_escape_string($conn, $_POST['color']);

        $estimateid = 0;
        $query = "SELECT * FROM estimate_prod WHERE id = '$id'";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $estimateid = $row['estimateid'];
        }

        $sql = "UPDATE estimates SET is_edited = '1' WHERE estimateid = $estimateid";
        mysqli_query($conn, $sql);
    
        $query = "UPDATE estimate_prod 
                  SET quantity = '$quantity', discounted_price = '$discounted_price', custom_color = '$custom_color' 
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
