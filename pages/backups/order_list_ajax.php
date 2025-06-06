<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

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
                                </div>
                                <div class="d-flex justify-content-end align-items-center gap-3 p-3 flex-wrap">
                                    <div class="d-flex justify-content-end align-items-center gap-3">
                                        <?php if ($status_code == 1): ?>
                                            <button type="button" id="email_order_btn" class="btn btn-primary email_order_btn" data-customer="<?= $order_details["customerid"]; ?>" data-id="<?= $estimateid; ?>">
                                                <i class="fa fa-envelope fs-5"></i> Send Email
                                            </button>
                                        <?php elseif ($status_code == 2): ?>
                                            <button type="button" id="processOrderBtn" class="btn btn-info" data-id="<?=$estimateid?>" data-action="process_order">Process Order</button>
                                        <?php elseif ($status_code == 3): ?>
                                            <button type="button" id="shipOrderBtn" class="btn btn-primary" data-id="<?=$estimateid?>" data-action="ship_order">Ship Order</button>
                                        <?php endif; ?>
                                    </div>
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

                    $('#viewOrderModal').on('shown.bs.modal', function () {
                        $('#est_dtls_tbl').DataTable().columns.adjust().responsive.recalc();
                    });
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
                            View Order Changes
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div id="update_product" class="form-horizontal">
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body datatables">
                                    <div class="order-details table-responsive text-nowrap">
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
                                                $orderid = mysqli_real_escape_string($conn, $_POST['id']);
                                                $query = "SELECT * FROM order_changes WHERE orderid = '$orderid'";
                                                $result = mysqli_query($conn, $query);
                                                
                                                if ($result && mysqli_num_rows($result) > 0) {
                                                    $response = array();
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        $orderid = $row['orderid'];
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
                            emptyTable: "Order details unchanged"
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

    if ($action == "fetch_add_modal") {
        ?>
            <style>
                #add_est_form {
                    width: 100% !important;
                }

                #add_est_form td, #add_est_form th {
                    white-space: normal !important;
                    word-wrap: break-word;
                }
            </style>
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">
                            Add Order
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="add_est_form" method="POST" class="form-horizontal" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="card datatables">
                                <div class="card-body table-responsive">
                                    <table id="est_add_tbl" class="table table-hover mb-0 text-md-nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>Description</th>
                                                <th>Color</th>
                                                <th>Grade</th>
                                                <th>Profile</th>
                                                <th class="text-center">Quantity</th>
                                                <th class="text-center">Dimensions</th>
                                                <th class="text-center">Price</th>
                                                <th class="text-center">Customer Price</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="table-body">
                                            <tr>
                                                <td>
                                                    <select id="product" class="productAdd form-control" name="product[]">
                                                        <option value="" >Select Product...</option>
                                                        <?php
                                                        $query_product = "SELECT * FROM product WHERE hidden = '0'";
                                                        $result_product = mysqli_query($conn, $query_product);            
                                                        while ($row_product = mysqli_fetch_array($result_product)) {
                                                        ?>
                                                            <option value="<?= $row_product['product_id'] ?>" <?= $selected ?>><?= $row_product['product_item'] ?></option>
                                                        <?php   
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select id="color" class="colorAdd form-control" name="color[]">
                                                        <option value="" >Select Color...</option>
                                                        <?php
                                                        $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                                        $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                                                        while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                                            $selected = ($row['color'] == $row_paint_colors['color_id']) ? 'selected' : '';
                                                        ?>
                                                            <option value="<?= $row_paint_colors['color_id'] ?>" data-color="<?= getColorHexFromColorID($row_paint_colors['color_id']) ?>" <?= $selected ?>><?= $row_paint_colors['color_name'] ?></option>
                                                        <?php   
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select id="grade" class="gradeAdd form-control" name="grade[]">
                                                        <option value="" >Select Grade...</option>
                                                        <?php
                                                        $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY `product_grade` ASC";
                                                        $result_grade = mysqli_query($conn, $query_grade);            
                                                        while ($row_grade = mysqli_fetch_array($result_grade)) {
                                                            $selected = ($row['grade'] == $row_grade['product_grade_id']) ? 'selected' : '';
                                                        ?>
                                                            <option value="<?= $row_grade['product_grade_id'] ?>" <?= $selected ?>><?= $row_grade['product_grade'] ?></option>
                                                        <?php   
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select id="profile" class="profileAdd form-control" name="profile[]">
                                                        <option value="" >Select Profile...</option>
                                                        <?php
                                                        $query_profile_type = "SELECT * FROM profile_type WHERE hidden = '0'";
                                                        $result_profile_type = mysqli_query($conn, $query_profile_type);            
                                                        while ($row_profile_type = mysqli_fetch_array($result_profile_type)) {
                                                            $selected = ($row['profile'] == $row_profile_type['profile_type_id']) ? 'selected' : '';
                                                        ?>
                                                            <option value="<?= $row_profile_type['profile_type_id'] ?>" <?= $selected ?>><?= $row_profile_type['profile_type'] ?></option>
                                                        <?php   
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                                <td><input type="number" name="quantity[]" class="form-control"></td>
                                                <td>
                                                    <div class="d-flex flex-column align-items-center">
                                                        <input class="form-control text-center mb-1" type="text" value="" placeholder="Width" size="5" style="color:#ffffff; ">
                                                        <span class="mx-1 text-center mb-1">X</span>
                                                        <input class="form-control text-center mb-1" type="text" value="" placeholder="Bend" size="5" style="color:#ffffff;">
                                                        <span class="mx-1 text-center mb-1">X</span>
                                                        <input class="form-control text-center mb-1" type="text" value="" placeholder="Hem" size="5" style="color:#ffffff;">
                                                        <span class="mx-1 text-center mb-1">X</span>
                                                        <input class="form-control text-center" type="text" value="" placeholder="Length" size="5" style="color:#ffffff;">
                                                    </div>
                                                </td>
                                                <td><input type="text" name="actual_price[]" class="form-control"></td>
                                                <td><input type="text" name="discounted_price[]" class="form-control"></td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <button type="button" class="btn add-row btn-sm p-1 fs-5 me-1">
                                                            <i class="text-success ti ti-plus fs-7"></i>
                                                        </button>
                                                        <button type="button" class="btn minus-row btn-sm p-1 fs-5">
                                                            <i class="text-danger ti ti-minus fs-7"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="form-actions">
                                <div class="card-body">
                                    <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                                    <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <script>
                function formatOption(state) {
                    if (!state.id) {
                        return state.text;
                    }
                    var color = $(state.element).data('color');
                    var $state = $(
                        '<span class="d-flex align-items-center">' +
                        '<span class="rounded-circle d-block p-1 me-2" style="background-color:' + color + '; width: 16px; height: 16px;"></span>' +
                        state.text + '</span>'
                    );
                    return $state;
                }

                $('#est_add_tbl').DataTable({
                    language: {
                        emptyTable: "Order List not found"
                    },
                    autoWidth: false,
                    responsive: true,
                    lengthChange: false,
                    paging: false,
                    searching: false,
                    ordering: false,
                    info: false
                });

                $(document).on('click', '.add-row', function() {
                    var row = $('#table-body tr:last').clone();
                    row.find('input').val('');
                    row.appendTo('#table-body');
                });

                $('.colorAdd').select2({
                    placeholder: "Select Color",
                    templateResult: formatOption,
                    templateSelection: formatOption,
                    width: '300px'
                });

                $('.productAdd, .gradeAdd, .profileAdd').select2({
                    placeholder: "Select One",
                    width: '300px'
                });

                $(document).on('click', '.minus-row', function() {
                    var row = $(this).closest('tr');
                    if (confirm("Are you sure you want to remove this row?")) {
                        if ($('#table-body tr').length > 1) {
                            row.remove();
                        } else {
                            row.find('input').val('');
                        }
                    }
                });
            </script>
    <?php
    } 

    if ($action == "fetch_edit_modal") {
        ?>
        <style>
            .table-fixed-est {
                table-layout: fixed;
                width: 100%;
            }
    
            .table-fixed-est th,
            .table-fixed-est td {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: normal;
                word-wrap: break-word;
            }
    
            .table-fixed-est th:nth-child(1),
            .table-fixed-est td:nth-child(1) { width: 5%; }
            .table-fixed-est th:nth-child(2),
            .table-fixed-est td:nth-child(2) { width: 15%; }
            .table-fixed-est th:nth-child(3),
            .table-fixed-est td:nth-child(3) { width: 10%; }
            .table-fixed-est th:nth-child(4),
            .table-fixed-est td:nth-child(4) { width: 10%; }
            .table-fixed-est th:nth-child(5),
            .table-fixed-est td:nth-child(5) { width: 10%; }
            .table-fixed-est th:nth-child(6),
            .table-fixed-est td:nth-child(6) { width: 10%; }
            .table-fixed-est th:nth-child(7),
            .table-fixed-est td:nth-child(7) { width: 10%; }
            .table-fixed-est th:nth-child(8),
            .table-fixed-est td:nth-child(8) { width: 10%; }
            .table-fixed-est th:nth-child(9),
            .table-fixed-est td:nth-child(9) { width: 7%; }
            .table-fixed-est th:nth-child(10),
            .table-fixed-est td:nth-child(10) { width: 7%; }
            .table-fixed-est th:nth-child(11),
            .table-fixed-est td:nth-child(11) { width: 7%; }
            .table-fixed-est th:nth-child(12),
            .table-fixed-est td:nth-child(12) { width: 4%; }
    
            input[readonly] {
                border: none;               
                background-color: transparent;
                pointer-events: none;
                color: inherit;
            }
    
            .table-fixed-est tbody tr:hover input[readonly] {
                background-color: transparent;
            }
        </style>
        
        <?php
        $total = 0;
        $totalquantity = 0;
        $discount_amt = 0;
        $no = 1;
        $orderid = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM orders WHERE orderid = '$orderid'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $discount = 0;
            if (isset($row["customerid"])) {
                $discount = floatval(getCustomerDiscount($row["customerid"])) / 100;
            }
            ?>
            <div class="form-group col-4">
                <div id="customer_est_section">
                <?php 
                    if(!empty($row['customerid'])){
                    ?>
                    <div class="form-group">
                        <label>Customer Name: <?= get_customer_name($row["customerid"]);?></label>                                     
                    </div>
                    <?php } else {?>
                    <label>Customer Name</label>
                    <div class="input-group">
                        <input class="form-control" placeholder="Search Customer" type="text" id="customer_select_order">
                        <a class="input-group-text rounded-right m-0 p-0" href="/cashier/?page=customer" target="_blank">
                            <span class="input-group-text"> + </span>
                        </a>
                    </div>
                <?php } ?>
                </div>
                <input type='hidden' id='customer_id_order' name="customer_id" value="<?= $row['customerid'] ?>"/>
            </div>
            <div class="card-body datatables">
                <div class="product-details table-responsive text-nowrap">
                    <table id="orderTable" class="table table-hover table-fixed-est mb-0 text-md-nowrap">
                        <thead>
                            <tr>
                                <th width="5%">Image</th>
                                <th width="10%">Description</th>
                                <th width="5%" class="text-center">Color</th>
                                <th width="5%" class="text-center">Grade</th>
                                <th width="5%" class="text-center">Profile</th>
                                <th width="25%" class="text-center pl-3">Quantity</th>
                                <th width="25%" class="text-center pl-3">Usage</th>
                                <th width="30%" class="text-center">Dimensions</th>
                                <th width="5%" class="text-center">Stock</th>
                                <th width="7%" class="text-center">Price</th>
                                <th width="7%" class="text-center">Customer<br>Price</th>
                                <th width="1%" class="text-center"> </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $query_est_prod = "SELECT * FROM order_product WHERE orderid = '$orderid'";
                                $result_est_prod = mysqli_query($conn, $query_est_prod);
                                while ($row_order_prod = mysqli_fetch_assoc($result_est_prod)) {
                                    $data_id = $row_order_prod['productid'];
                                    $product = getProductDetails($data_id);
                                    $totalstockquantity = getProductStockInStock($data_id) + getProductStockTotal($data_id);
                                    $category_id = $product["product_category"];

                                    $color_id = $row_order_prod['custom_color'] ?? $product["color"];

                                    if ($totalstockquantity > 0) {
                                        $stock_text = '
                                            <a href="javascript:void(0);" id="view_in_stock" data-id="' . htmlspecialchars($data_id, ENT_QUOTES, 'UTF-8') . '" class="d-flex align-items-center">
                                                <span class="text-bg-success p-1 rounded-circle"></span>
                                                <span class="ms-2">In Stock</span>
                                            </a>';
                                    } else {
                                        $stock_text = '
                                            <a href="javascript:void(0);" id="view_out_of_stock" data-id="' . htmlspecialchars($data_id, ENT_QUOTES, 'UTF-8') . '" class="d-flex align-items-center">
                                                <span class="text-bg-danger p-1 rounded-circle"></span>
                                                <span class="ms-2">Out of Stock</span>
                                            </a>';
                                    } 
    
                                    $default_image = 'images/product/product.jpg';
    
                                    $picture_path = !empty($row_product['main_image'])
                                    ? $row_product['main_image']
                                    : $default_image;
    
                                    $images_directory = "images/drawing/";
                                    ?>
                                    <tr>
                                        <td>
                                            <?php
                                            if($data_id == '277'){
                                                if(!empty($row_order_prod["custom_trim_src"])){
                                                ?>
                                                <a href="javascript:void(0);" id="custom_trim_draw" data-id="<?php echo $data_id; ?>">
                                                    <div class="align-items-center text-center w-100" style="background: #ffffff">
                                                        <img src="<?= $images_directory.$row_order_prod["custom_trim_src"] ?>" class="rounded-circle " alt="materialpro-img" width="56" height="56">
                                                    </div>
                                                </a>
                                                <?php
                                                }else{
                                                ?>
                                                <a href="javascript:void(0);" id="custom_trim_draw" class="btn btn-primary py-1 px-2 d-flex justify-content-center align-items-center" data-id="<?php echo $data_id; ?>">
                                                    Draw Here
                                                </a>
                                                <?php
                                                }
                                                ?>
                                                
                                            <?php }else{
                                            ?>
                                            <div class="align-items-center text-center w-100">
                                                <img src="<?= $picture_path ?>" class="rounded-circle " alt="materialpro-img" width="56" height="56">
                                            </div>
                                            <?php
                                            } ?>
                                        </td>
                                        <td>
                                            <h6 class="fw-semibold mb-0 fs-4"><?= $product["product_item"] ?></h6>
                                        </td>
                                        <td>
                                            <select id="color<?= $no ?>" class="form-control select2-color text-start" name="color" onchange="updateColor(this)" data-id="<?= $row_order_prod['id']; ?>">
                                                <option value="" >Select Color...</option>
                                                <?php
                                                $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                                $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                                                while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                                    $selected = ($color_id == $row_paint_colors['color_id']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= $row_paint_colors['color_id'] ?>" <?= $selected ?> data-color="<?= getColorHexFromColorID($row_paint_colors['color_id']) ?>"><?= $row_paint_colors['color_name'] ?></option>
                                                <?php   
                                                }
                                                ?>
                                            </select>
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
                                                    <button class="btn btn-primary btn-icon p-1 mr-1" type="button" data-id="<?= $row_order_prod['id']; ?>" onClick="deductquantity(this)">
                                                        -
                                                    </button>
                                                </span> 
                                                <input class="form-control" type="text" size="5" value="<?= $row_order_prod["quantity"]; ?>" style="color:#ffffff;" onchange="updatequantity(this)" data-id="<?= $row_order_prod['id']; ?>" id="item_quantity<?php echo $data_id;?>">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-primary btn-icon p-1 ml-1" type="button" data-id="<?= $row_order_prod['id']; ?>" onClick="addquantity(this)">
                                                        +
                                                    </button>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group text-start">
                                                <select id="usage<?= $no ?>" class="form-control select2-usage" name="usage" onchange="updateUsage(this)" data-line="<?= $values['line']; ?>" data-id="<?= $row_order_prod['id']; ?>">
                                                    <option value="">Select Usage...</option>
                                                    <?php
                                                    $query_key = "SELECT * FROM key_components ORDER BY component_name ASC";
                                                    $result_key = mysqli_query($conn, $query_key);

                                                    while ($row_key = mysqli_fetch_array($result_key)) {
                                                        $componentid = $row_key['componentid'];
                                                        ?>
                                                        <optgroup label="<?= strtoupper($row_key['component_name']); ?>">
                                                            <?php 
                                                            $query_usage = "SELECT * FROM component_usage WHERE componentid = '$componentid' ORDER BY `usage_name` ASC";
                                                            $result_usage = mysqli_query($conn, $query_usage);

                                                            while ($row_usage = mysqli_fetch_array($result_usage)) {
                                                                $selected = ($row_order_prod['usageid'] == $row_usage['usageid']) ? 'selected' : '';
                                                                ?>
                                                                <option value="<?= $row_usage['usageid']; ?>" <?= $selected; ?>><?= $row_usage['usage_name']; ?></option>
                                                                <?php
                                                            }
                                                            ?>
                                                        </optgroup>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>

                                            </div>
                                        </td>
                                        <?php if($category_id == '46'){ // Panels ID
                                        ?>
                                        <td>
                                            <div class="input-group d-flex align-items-center">
                                                <input class="form-control" type="text" value="<?= $row_order_prod["custom_width"]; ?>" placeholder="W" size="5" style="color:#ffffff;" data-id="<?= $row_order_prod['id']; ?>" <?= !empty($product["width"]) ? 'readonly' : '' ?>>
                                                <span class="mr-3 ml-1"> X</span>
                                                <input class="form-control" type="text" value="<?= $row_order_prod["custom_length"]; ?>" placeholder="H" size="5" style="color:#ffffff;" data-id="<?= $row_order_prod['id']; ?>" onchange="updateOrderLength(this)">
                                            </div>
                                        </td>
                                        <?php
                                        }else if($category_id == '43'){
                                        ?>
                                        <td>
                                            <div class="d-flex flex-column align-items-center">
                                                <input class="form-control text-center mb-1" type="text" value="<?= isset($row_order_prod["custom_width"]) ? $row_order_prod["custom_width"] : $product["width"]; ?>" placeholder="Width" size="5" style="color:#ffffff; " data-id="<?= $row_order_prod['id']; ?>" onchange="updateOrderWidth(this)">
                                                <span class="mx-1 text-center mb-1">X</span>
                                                <input class="form-control text-center mb-1" type="text" value="<?= $row_order_prod["custom_bend"]; ?>" placeholder="Bend" size="5" style="color:#ffffff;" data-id="<?= $row_order_prod['id']; ?>" onchange="updateOrderBend(this)">
                                                <span class="mx-1 text-center mb-1">X</span>
                                                <input class="form-control text-center mb-1" type="text" value="<?= $row_order_prod["custom_bend"]; ?>" placeholder="Hem" size="5" style="color:#ffffff;" data-id="<?= $row_order_prod['id']; ?>" onchange="updateOrderHem(this)">
                                                <span class="mx-1 text-center mb-1">X</span>
                                                <input class="form-control text-center" type="text" value="<?= isset($row_order_prod["custom_length"]) ? $row_order_prod["custom_length"] : $product["length"]; ?>" placeholder="Length" size="5" style="color:#ffffff; " data-id="<?= $row_order_prod['id']; ?>" onchange="updateOrderLength(this)">
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
                                            $subtotal = $row_order_prod["quantity"] * $row_order_prod["actual_price"];
                                            echo number_format($subtotal, 2);
                                            ?>
                                        </td>
                                        <td class="text-end pl-3">$
                                            <?php
                                            $customer_price = $row_order_prod["quantity"] * $row_order_prod["actual_price"] * (1 - $discount);
                                            echo number_format($customer_price, 2);
                                            ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-danger-gradient btn-sm" type="button" data-id="<?php echo $row_order_prod["id"]; ?>" onClick="delete_item(this)"><i class="fa fa-trash"></i></button>
                                            <?php
                                            if (in_array($category_id, ['46', '43'])) {
                                            ?>
                                            <button class="btn btn-danger-gradient btn-sm" type="button" data-id="<?php echo $data_id; ?>" onClick="duplicate_item(this)"><i class="fa fa-plus"></i></button>
                                            <?php } ?>
                                            <input type="hidden" class="form-control" data-id="<?php echo $data_id; ?>" id="item_id<?php echo $data_id; ?>" value="<?php echo $row_order_prod["productid"]; ?>">
                                        </td>
                                    </tr>
                                    <?php
                                    $discount_amt += $subtotal - $customer_price;
                                    $totalquantity += $row_order_prod["quantity_cart"];
                                    $total += $subtotal;
                                    $no++;
                                }
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
                    <div class="d-flex justify-content-end">
                        <button type="button" id="productsModalBtn" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#productsModal">
                            <i class="fa fa-plus text-white me-1 fs-5"></i> Add Product
                        </button>
                    </div>
                    
                </div>
            </div>
            <script>
                function formatOption(state) {
                    if (!state.id) {
                        return state.text;
                    }
                    var color = $(state.element).data('color');
                    var $state = $(
                        '<span class="d-flex align-items-center">' +
                        '<span class="rounded-circle d-block p-1 me-2" style="background-color:' + color + '; width: 16px; height: 16px;"></span>' +
                        state.text + '</span>'
                    );
                    return $state;
                }
                $(document).ready(function() {
                    var table = $('#orderTable').DataTable({
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

                    $(".select2-usage").each(function() {
                        $(this).select2({
                            width: '300px',
                            placeholder: "Select...",
                            dropdownAutoWidth: true,
                            dropdownParent: $('#orderTable'),
                            templateResult: formatOption,
                            templateSelection: formatOption
                        });
                    });

                    $(".select2-color").each(function() {
                        $(this).select2({
                            width: '300px',
                            placeholder: "Select...",
                            dropdownAutoWidth: true,
                            dropdownParent: $('#orderTable'),
                            templateResult: formatOption,
                            templateSelection: formatOption
                        });
                    });
                });
            </script>
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
                        <button class="btn btn-primary btn-add-to-cart px-2 py-0" type="button" data-id="'.$row_product['product_id'].'" onClick="addtoorder(this)"> + </button>
                    </td>
                </tr>';
            }
        } else {
            $tableHTML .= '<tr><td colspan="8" class="text-center">No products found</td></tr>';
        }
        
        echo $tableHTML;
    }

    if ($action == 'setquantity') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['orderid']);
        $quantity = mysqli_real_escape_string($conn, $_POST['qty']);
        $query = "UPDATE order_product SET quantity = '$quantity' WHERE id ='$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $est_prod_details = getOrderProdDetails($est_prod_id);
            $action = "Set the quantity to $quantity";
            $log_result = log_order_changes($est_prod_details['orderid'], $est_prod_details['productid'], $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'add_to_order') {
        $productid = mysqli_real_escape_string($conn, $_POST['productid']);
        $orderid = mysqli_real_escape_string($conn, $_POST['orderid']);
        $product_details = getProductDetails($productid);

        $query="INSERT INTO order_product
                        (orderid, productid, quantity, custom_width, custom_bend, custom_hem, custom_length, actual_price, discounted_price) 
                VALUES 
                        ('$orderid', '$productid', '1', '".$product_details['width']."', '', '', '".$product_details['length']."', '".$product_details['unit_cost']."', '".$product_details['unit_cost']."')";

        $result = mysqli_query($conn, $query);
        if ($result) {
            $action = "Added product to product orders List";
            $log_result = log_order_changes($orderid, $productid, $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'addquantity') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['orderid']);
        $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
        $query = "UPDATE order_product SET quantity = (quantity + 1) WHERE id ='$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $est_prod_details = getOrderProdDetails($est_prod_id);
            $action = "Added 1 Quantity";
            $log_result = log_order_changes($est_prod_details['orderid'], $est_prod_details['productid'], $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'deductquantity') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['orderid']);
        $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
        $query = "UPDATE order_product SET quantity = (quantity - 1) WHERE id ='$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $est_prod_details = getOrderProdDetails($est_prod_id);
            $action = "Deducted 1 Quantity";
            $log_result = log_order_changes($est_prod_details['orderid'], $est_prod_details['productid'], $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'set_order_width') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['id']);
        $width = mysqli_real_escape_string($conn, $_POST['width']);
        $query = "UPDATE order_product SET custom_width = '$width' WHERE id ='$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $est_prod_details = getOrderProdDetails($est_prod_id);
            $action = "Adjusted custom width to $width";
            $log_result = log_order_changes($est_prod_details['orderid'], $est_prod_details['productid'], $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'set_order_length') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['id']);
        $length = mysqli_real_escape_string($conn, $_POST['length']);
        $query = "UPDATE order_product SET custom_length = '$length' WHERE id ='$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $est_prod_details = getOrderProdDetails($est_prod_id);
            $action = "Adjusted custom length to $length";
            $log_result = log_order_changes($est_prod_details['orderid'], $est_prod_details['productid'], $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'set_order_hem') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['id']);
        $hem = mysqli_real_escape_string($conn, $_POST['hem']);
        $query = "UPDATE order_product SET custom_hem = '$hem' WHERE id ='$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $est_prod_details = getOrderProdDetails($est_prod_id);
            $action = "Adjusted custom hem to $hem";
            $log_result = log_order_changes($est_prod_details['orderid'], $est_prod_details['productid'], $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'set_order_bend') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['id']);
        $bend = mysqli_real_escape_string($conn, $_POST['bend']);
        $query = "UPDATE order_product SET custom_bend = '$bend' WHERE id ='$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $est_prod_details = getOrderProdDetails($est_prod_id);
            $action = "Adjusted custom bend to $bend";
            $log_result = log_order_changes($est_prod_details['orderid'], $est_prod_details['productid'], $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'set_usage') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['id']);
        $usage = mysqli_real_escape_string($conn, $_POST['usage']);
        $query = "UPDATE order_product SET usageid = '$usage' WHERE id ='$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $est_prod_details = getOrderProdDetails($est_prod_id);
            $action = "Changed product usage to " .getUsageName($est_prod_details['usageid']);
            $log_result = log_order_changes($est_prod_details['orderid'], $est_prod_details['productid'], $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'set_color') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['id']);
        $color = mysqli_real_escape_string($conn, $_POST['color']);
        $query = "UPDATE order_product SET custom_color = '$color' WHERE id ='$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $est_prod_details = getOrderProdDetails($est_prod_id);
            $action = "Changed product color to " .getColorName($est_prod_details['custom_color']);
            $log_result = log_order_changes($est_prod_details['orderid'], $est_prod_details['productid'], $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'deleteitem') {
        $est_prod_id = mysqli_real_escape_string($conn, $_POST['orderid']);
        $est_prod_details = getOrderProdDetails($est_prod_id);

        $query = "DELETE FROM order_product WHERE id = '$est_prod_id'";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $action = "Removed product to product orders List";
            $log_result = log_order_changes($est_prod_details['orderid'], $est_prod_details['productid'], $action);
            echo 'success';
        } else {
            echo 'error';
        }
    }
    
    mysqli_close($conn);
}
?>
