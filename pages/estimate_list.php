<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$generate_rend_upc = generateRandomUPC();
$picture_path = "images/product/product.jpg";

if($_REQUEST['customer_id']){
    $customer_id = $_REQUEST['customer_id'];
    $customer_details = getCustomerDetails($customer_id);
}

?>
<style>
    .select2-container {
        z-index: 9999 !important; 
    }
    .dz-preview {
        position: relative;
    }

    .dz-remove {
        position: absolute;
        top: 10px;
        right: 10px;
        background: red;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        font-weight: bold;
        font-size: 12px;
        z-index: 9999; /* Ensure the remove button is on top of the image */
        cursor: pointer; /* Make sure it looks clickable */
    }

    .table-fixed {
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
    .table-fixed td:nth-child(1) { width: 30% !important; }
    .table-fixed th:nth-child(2),
    .table-fixed td:nth-child(2) { width: 10% !important; }
    .table-fixed th:nth-child(3),
    .table-fixed td:nth-child(3) { width: 10% !important; }
    .table-fixed th:nth-child(4),
    .table-fixed td:nth-child(4) { width: 10% !important; }
    .table-fixed th:nth-child(5),
    .table-fixed td:nth-child(5) { width: 15% !important; }
    .table-fixed th:nth-child(6),
    .table-fixed td:nth-child(6) { width: 15% !important; }
    .table-fixed th:nth-child(7),
    .table-fixed td:nth-child(7) { width: 10% !important; }
</style>

<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"><?php
            if(isset($customer_details)){
                echo "Customer " .$customer_details['customer_first_name'] .' ' .$customer_details['customer_last_name'];
            }
            ?> Estimate List</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Estimate
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Estimate List</li>
            </ol>
            </nav>
        </div>
        <div>
            <div class="d-sm-flex d-none gap-3 no-block justify-content-end align-items-center">
            <div class="d-flex gap-2">
                <div class="">
                <small>This Month</small>
                <h4 class="text-primary mb-0 ">$58,256</h4>
                </div>
                <div class="">
                <div class="breadbar"></div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <div class="">
                <small>Last Month</small>
                <h4 class="text-secondary mb-0 ">$58,256</h4>
                </div>
                <div class="">
                <div class="breadbar2"></div>
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
    </div>

    <div class="widget-content searchable-container list">
    <?php
    if(empty($customer_details)){
    ?>   
    <div class="card card-body">
        <div class="row">
        <div class="col-md-4 col-xl-3">
            <!-- <form class="position-relative">
            <input type="text" class="form-control product-search ps-5" id="input-search" placeholder="Search Contacts..." />
            <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
            </form> -->
        </div>
        <div class="col-md-8 col-xl-9 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0">
            <div class="action-btn show-btn">
            <a href="javascript:void(0)" class="delete-multiple bg-danger-subtle btn me-2 text-danger d-flex align-items-center ">
                <i class="ti ti-trash me-1 fs-5"></i> Delete All Row
            </a>
            </div>
            <button type="button" id="add_estimate_btn" class="btn btn-primary d-flex align-items-center">
                <i class="ti ti-users text-white me-1 fs-5"></i> Add Estimate
            </button>
        </div>
        </div>
    </div>
    <?php } ?>

    <div class="modal fade" id="viewEstimateModal" tabindex="-1" aria-labelledby="viewEstimateModalLabel" aria-hidden="true"></div>

    <div class="modal fade" id="viewChangesModal" tabindex="-1" aria-labelledby="viewChangesModalLabel" aria-hidden="true"></div>
    
    <div class="modal fade" id="addEstimateModal" tabindex="-1" aria-labelledby="addEstimateModalLabel" aria-hidden="true"></div>

    <div class="modal fade" id="updateEstimateModal" tabindex="-1" role="dialog" aria-labelledby="updateEstimateModal" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Save Estimate</h6>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="estimate-tbl"></div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary d-flex align-items-center mb-2 me-2" id="save_estimate">
                        <i class="fa fa-save fs-4 me-2"></i>
                        Save
                    </button>
                    <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="productsModal" tabindex="-1" role="dialog" aria-labelledby="productsModal" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Add Products</h6>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div id="products-tbl" class="card">
                        <div class="card-body text-start p-3">
                            <div class="row pb-3">
                                <div class="col-2">
                                    <button type="button" id="updateEstimateBtn" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#updateEstimateModal">
                                        <i class="ti ti-users text-white me-1 fs-5"></i> Back
                                    </button>
                                </div>
                                <div class="col-10">
                                    <div class="p-2 text-end">
                                        <input type="checkbox" id="toggleActive" checked> Show only In Stock
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center  mb-9">
                                <div class="position-relative w-100 col-4">
                                    <input type="text" class="form-control search-chat py-2 ps-5 " id="text-srh" placeholder="Search Product">
                                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                                </div>
                                <div class="position-relative w-100 px-1 col-2">
                                    <select class="form-control search-chat py-0 ps-5" id="select-color" data-category="">
                                        <option value="" data-category="">All Colors</option>
                                        <optgroup label="Product Colors">
                                            <?php
                                            $query_color = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                            $result_color = mysqli_query($conn, $query_color);
                                            while ($row_color = mysqli_fetch_array($result_color)) {
                                            ?>
                                                <option value="<?= $row_color['color_id'] ?>" data-category="category"><?= $row_color['color_name'] ?></option>
                                            <?php
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                </div>
                                <div class="position-relative w-100 px-1 col-2">
                                    <select class="form-control search-chat py-0 ps-5" id="select-category" data-category="">
                                        <option value="" data-category="">All Categories</option>
                                        <optgroup label="Category">
                                            <?php
                                            $query_category = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                                            $result_category = mysqli_query($conn, $query_category);
                                            while ($row_category = mysqli_fetch_array($result_category)) {
                                            ?>
                                                <option value="<?= $row_category['product_category_id'] ?>" data-category="category"><?= $row_category['product_category'] ?></option>
                                            <?php
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                </div>
                                <div class="position-relative w-100 px-1 col-2">
                                    <select class="form-control search-chat py-0 ps-5" id="select-line" data-category="">
                                        <option value="" data-category="">All Product Lines</option>
                                        <optgroup label="Product Line">
                                            <?php
                                            $query_line = "SELECT * FROM product_line WHERE hidden = '0' AND status = '1' ORDER BY `product_line` ASC";
                                            $result_line = mysqli_query($conn, $query_line);
                                            while ($row_line = mysqli_fetch_array($result_line)) {
                                            ?>
                                                <option value="<?= $row_line['product_line_id'] ?>" data-category="line"><?= $row_line['product_line'] ?></option>
                                            <?php
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                </div>
                                <div class="position-relative w-100 px-1 col-2">
                                    <select class="form-control search-chat py-0 ps-5" id="select-type" data-category="">
                                        <option value="" data-category="">All Product Types</option>
                                        <optgroup label="Product Type">
                                            <?php
                                            $query_type = "SELECT * FROM product_type WHERE hidden = '0' AND status = '1' ORDER BY `product_type` ASC";
                                            $result_type = mysqli_query($conn, $query_type);
                                            while ($row_type = mysqli_fetch_array($result_type)) {
                                            ?>
                                                <option value="<?= $row_type['product_type_id'] ?>" data-category="type"><?= $row_type['product_type'] ?></option>
                                            <?php
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                            <div class="table-responsive border rounded">
                                <table id="productTable" class="table align-middle text-nowrap mb-0 table-fixed text-center">
                                    <thead>
                                        <tr>
                                            <th scope="col">Products</th>
                                            <th scope="col">Color</th>
                                            <th scope="col">Type</th>
                                            <th scope="col">Line</th>
                                            <th scope="col">Category</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Price</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="productTableBody"></tbody>
                                </table>
                                    
                                <div class="d-flex align-items-center justify-content-end py-1">
                                    <p class="mb-0 fs-2">Rows per page:</p>
                                    <select id="rowsPerPage" class="form-select w-auto ms-0 ms-sm-2 me-8 me-sm-4 py-1 pe-7 ps-2 border-0" aria-label="Rows per page">
                                        <option value="5" selected>5</option>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                    </select>
                                    <p id="paginationInfo" class="mb-0 fs-2"></p>
                                    <nav aria-label="...">
                                        <ul id="paginationControls" class="pagination justify-content-center mb-0 ms-8 ms-sm-9">
                                            <!-- Pagination buttons will be inserted here by JS -->
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="response-modal" tabindex="-1" aria-labelledby="vertical-center-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div id="responseHeaderContainer" class="modal-header align-items-center modal-colored-header">
                <h4 id="responseHeader" class="m-0"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                <p id="responseMsg"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">
                Close
                </button>
            </div>
            </div>
        </div>
    </div>

    
    <div class="card card-body">
        <div class="card-body datatables">
            <div class="product-details table-responsive text-nowrap">
                <table id="est_list_tbl" class="table table-hover mb-0 text-md-nowrap">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Total Price</th>
                            <th>Discounted Price</th>
                            <th>Estimate Date</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 

                        $query = "SELECT * FROM estimates WHERE 1";

                        if (isset($customer_id) && !empty($customer_id)) {
                            $query .= " AND customerid = '$customer_id'";
                        }

                        $result = mysqli_query($conn, $query);
                    
                        if ($result && mysqli_num_rows($result) > 0) {
                            $response = array();
                            while ($row = mysqli_fetch_assoc($result)) {
                                $status_code = $row['status'];

                                $status_labels = [
                                    1 => ['label' => 'New Estimate', 'class' => 'badge bg-primary'],
                                    2 => ['label' => 'Sent to Customer', 'class' => 'badge bg-success text-dark'],
                                    3 => ['label' => 'Modified by Customer', 'class' => 'badge bg-warning text-dark'],
                                    4 => ['label' => 'Approved', 'class' => 'badge bg-secondary'],
                                    5 => ['label' => 'Processing', 'class' => 'badge bg-success'],
                                    6 => ['label' => 'In Transit', 'class' => 'badge bg-info'],
                                    7 => ['label' => 'Delivered', 'class' => 'badge bg-success']
                                ];
                              
                                $status = $status_labels[$status_code];
                            ?>
                            <tr>
                                <td>
                                    <?php echo get_customer_name($row["customerid"]) ?>
                                </td>
                                <td >
                                    $ <?php echo number_format($row["total_price"],2) ?>
                                </td>
                                <td >
                                    $ <?php echo number_format($row["discounted_price"],2) ?>
                                </td>
                                <td>
                                    <?php echo date("F d, Y", strtotime($row["estimated_date"])); ?>
                                </td>
                                <td>
                                    <?php 
                                        if (isset($row["order_date"]) && !empty($row["order_date"]) && $row["order_date"] !== '0000-00-00 00:00:00') {
                                            echo date("F d, Y", strtotime($row["order_date"]));
                                        } else {
                                            echo '';
                                        }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <span class="<?= $status['class']; ?> fw-bond"><?= $status['label']; ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="view_estimate_btn" type="button" data-id="<?php echo $row["estimateid"]; ?>"><i class="text-primary fa fa-eye fs-5"></i></button>
                                    <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="edit_estimate_btn" type="button" data-id="<?php echo $row["estimateid"]; ?>"><i class="text-warning fa fa-pencil fs-5"></i></button>
                                    <button class="btn btn-danger-gradient btn-sm p-0 me-1 email_estimate_btn" data-customer="<?php echo $row["customerid"]; ?>" id="email_estimate_btn" type="button" data-id="<?php echo $row["estimateid"]; ?>"><i class="text-info fa fa-envelope fs-5"></i></button>
                                    <a href="print_estimate_product.php?id=<?= $row["estimateid"]; ?>" target="_blank" class="btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["estimateid"]; ?>"><i class="text-success fa fa-print fs-5"></i></a>
                                    <a href="print_estimate_total.php?id=<?= $row["estimateid"]; ?>" target="_blank" class="btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["estimateid"]; ?>"><i class="text-white fa fa-file-lines fs-5"></i></a>
                                    <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="view_changes_btn" type="button" data-id="<?php echo $row["estimateid"]; ?>"><i class="text-info fa fa-clock-rotate-left fs-5"></i></button>
                                    <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="delete_estimate_btn" type="button" data-id="<?php echo $row["estimateid"]; ?>"><i class="text-danger fa fa-trash fs-5"></i></button>
                                    <a href="customer/index.php?page=estimate&id=<?=$row["estimateid"]?>&key=<?=$row["est_key"]?>" target="_blank" class="btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["estimateid"]; ?>"><i class="text-info fa fa-sign-in-alt fs-5"></i></a>
                                </td>
                            </tr>
                            <?php
                            }
                        } else {
                        ?>
                        
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
</div>

<script>
    function updateEstimateBend(element){
        var bend = $(element).val();
        var id = $(element).data('id');
        $.ajax({
            url: 'pages/estimate_list_ajax.php',
            type: 'POST',
            data: {
                bend: bend,
                id: id,
                action: "set_estimate_bend"
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updateEstimateHem(element){
        var hem = $(element).val();
        var id = $(element).data('id');

        $.ajax({
            url: 'pages/estimate_list_ajax.php',
            type: 'POST',
            data: {
                hem: hem,
                id: id,
                action: "set_estimate_hem"
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updateUsage(element){
        var usage = $(element).val();
        var id = $(element).data('id');

        $.ajax({
            url: 'pages/estimate_list_ajax.php',
            type: 'POST',
            data: {
                usage: usage,
                id: id,
                action: "set_usage"
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updateColor(element){
        var color = $(element).val();
        var id = $(element).data('id');

        $.ajax({
            url: 'pages/estimate_list_ajax.php',
            type: 'POST',
            data: {
                color: color,
                id: id,
                action: "set_color"
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function addtoestimate(element) {
        var product_id = $(element).data('id');
        var estimate_id = sessionStorage.getItem('estimateid');
        $.ajax({
            url: "pages/estimate_list_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                estimate_id: estimate_id,
                action: 'add_to_estimate'
            },
            success: function(data) {
                console.log(data);
                loadEditModal();
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }

    function updateEstimateLength(element){
        var length = $(element).val();
        var id = $(element).data('id');

        $.ajax({
            url: 'pages/estimate_list_ajax.php',
            type: 'POST',
            data: {
                length: length,
                id: id,
                action: "set_estimate_length"
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updateEstimateHeight(element){
        var height = $(element).val();
        var id = $(element).data('id');

        $.ajax({
            url: 'pages/estimate_list_ajax.php',
            type: 'POST',
            data: {
                height: height,
                id: id,
                action: "set_estimate_height"
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updateEstimateWidth(element){
        var width = $(element).val();
        var id = $(element).data('id');
        $.ajax({
            url: 'pages/estimate_list_ajax.php',
            type: 'POST',
            data: {
                width: width,
                id: id,
                action: "set_estimate_width"
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updatequantity(element) {
        var estimate_id = $(element).data('id');
        var qty = $(element).val();
        $.ajax({
            url: "pages/estimate_list_ajax.php",
            type: "POST",
            data: {
                estimate_id: estimate_id,
                qty: qty,
                action: 'setquantity'
            },
            success: function(data) {
                loadEditModal();
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }

    function addquantity(element) {
        var estimate_id = $(element).data('id');
        var input_quantity = $('input[data-id="' + estimate_id + '"][id="item_quantity' + estimate_id + '"]');
        var quantity = Number(input_quantity.val());
        $.ajax({
            url: "pages/estimate_list_ajax.php",
            type: "POST",
            data: {
                estimate_id: estimate_id,
                quantity: quantity,
                action: 'addquantity'
            },
            success: function(data) {
                loadEditModal();
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }

    function loadEditModal(estimate_id = null) {
        var estimate = estimate_id || sessionStorage.getItem('estimateid');
        
        if (!estimate) {
            alert('No estimate ID provided.');
            return;
        }

        $.ajax({
            url: 'pages/estimate_list_ajax.php',
            type: 'POST',
            data: {
                id: estimate,
                action: "fetch_edit_modal"
            },
            success: function(response) {
                $('#estimate-tbl').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function deductquantity(element) {
        var estimate_id = $(element).data('id');
        var input_quantity = $('input[data-id="' + estimate_id + '"][id="item_quantity' + estimate_id + '"]');
        var quantity = Number(input_quantity.val());
        $.ajax({
            url: "pages/estimate_list_ajax.php",
            type: "POST",
            data: {
                estimate_id: estimate_id,
                quantity: quantity,
                action: 'deductquantity'
            },
            success: function(data) {
                loadEditModal();
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }

    function delete_item(element) {
        var estimate_id = $(element).data('id');
        var line = $(element).data('line');
        $.ajax({
            url: "pages/estimate_list_ajax.php",
            data: {
                estimate_id: estimate_id,

                action: 'deleteitem'
            },
            type: "POST",
            success: function(data) {
                loadEditModal();
            },
            error: function() {}
        });
    }
    
    $(document).ready(function() {
        var table = $('#est_list_tbl').DataTable({
            "order": [[1, "asc"]]
        });

        $(document).on('click', '#view_estimate_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/estimate_list_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_view_modal"
                    },
                    success: function(response) {
                        $('#viewEstimateModal').html(response);
                        $('#viewEstimateModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $(document).on("click", "#resendBtn, #AcceptBtn, #processOrderBtn , #shipOrderBtn", function () {
            var dataId = $(this).data("id");
            var action = $(this).data("action");

            var confirmMessage = action.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });

            if (confirm("Are you sure you want to " + confirmMessage + "?")) {
                $.ajax({
                    url: 'pages/estimate_list_ajax.php',
                    type: 'POST',
                    data: {
                        id: dataId,
                        method: action,
                        action: 'update_status'
                    },
                    success: function (response) {
                        console.log(response);
                        try {
                            var jsonResponse = JSON.parse(response);  
                        } catch (e) {
                            var jsonResponse = response;
                        }

                        if (jsonResponse.success) {
                            alert(jsonResponse.message);
                            location.reload();
                        } else {
                            alert("Update Success, but email failed to send");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("AJAX Error:", xhr.responseText);
                    }
                });
            }
        });

        $(document).on('click', '#view_changes_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/estimate_list_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_changes_modal"
                    },
                    success: function(response) {
                        $('#viewChangesModal').html(response);
                        $('#viewChangesModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $(document).on('click', '#edit_estimate_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            loadEditModal(id);
            $('#updateEstimateModal').modal('show');

            sessionStorage.setItem('estimateid', id);
        });

        $(document).on('click', '#add_estimate_btn', function(event) {
            event.preventDefault(); 
            $.ajax({
                url: 'pages/estimate_list_ajax.php',
                type: 'POST',
                data: {
                    action: "fetch_add_modal"
                },
                success: function(response) {
                    $('#addEstimateModal').html(response);
                    $('#addEstimateModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('submit', '#update_product', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update');

            $.ajax({
                url: 'pages/estimate_list_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#updateEstimateModal').modal('hide');
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Product updated successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    } else {
                        $('#responseHeader').text("Failed");
                        $('#responseMsg').text(response);

                        $('#responseHeaderContainer').removeClass("bg-success");
                        $('#responseHeaderContainer').addClass("bg-danger");
                        $('#response-modal').modal("show");
                    }

                    
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '.email_estimate_btn', function(event) {
            event.preventDefault(); 

            if (!confirm("Are you sure you want to send this estimate email?")) {
                return;
            }

            var id = $(this).data("id");
            var customerid = $(this).data("customer");

            console.log(customerid);

            $.ajax({
                url: 'pages/estimate_list_ajax.php',
                type: 'POST',
                data: {
                    id: id,
                    customerid: customerid,
                    action: 'send_email'
                },
                success: function(response) {
                    $('.modal').modal('hide');
                    console.log(response);
                    try {
                        var jsonResponse = (typeof response === "string") ? JSON.parse(response) : response;
                    } catch (e) {
                        var jsonResponse = { success: false, message: "Invalid JSON response" };
                    }

                    if (jsonResponse?.success === true) {
                        alert(jsonResponse?.message);
                        location.reload();
                    } else {
                        alert(jsonResponse?.message || "An unknown error occurred.");
                        location.reload();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('submit', '#add_product', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update');

            $.ajax({
                url: 'pages/estimate_list_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addEstimateModal').modal('hide');
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New product added successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    } else {
                        $('#responseHeader').text("Failed");
                        $('#responseMsg').text(response);

                        $('#responseHeaderContainer').removeClass("bg-success");
                        $('#responseHeaderContainer').addClass("bg-danger");
                        $('#response-modal').modal("show");
                    }

                    
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        var currentPage = 1,
            rowsPerPage = parseInt($('#rowsPerPage').val()),
            totalRows = 0,
            totalPages = 0,
            maxPageButtons = 5,
            stepSize = 5;

        function updateTable() {
            var $rows = $('#productTableBody tr');
            totalRows = $rows.length;
            totalPages = Math.ceil(totalRows / rowsPerPage);

            var start = (currentPage - 1) * rowsPerPage,
                end = Math.min(currentPage * rowsPerPage, totalRows);

            $rows.hide().slice(start, end).show();

            $('#paginationControls').html(generatePagination());
            $('#paginationInfo').text(`${start + 1}–${end} of ${totalRows}`);

            $('#paginationControls').find('a').click(function(e) {
                e.preventDefault();
                if ($(this).hasClass('page-link-next')) {
                    currentPage = Math.min(currentPage + stepSize, totalPages);
                } else if ($(this).hasClass('page-link-prev')) {
                    currentPage = Math.max(currentPage - stepSize, 1);
                } else {
                    currentPage = parseInt($(this).text());
                }
                updateTable();
            });
        }

        function generatePagination() {
            var pagination = '';
            var startPage = Math.max(1, currentPage - Math.floor(maxPageButtons / 2));
            var endPage = Math.min(totalPages, startPage + maxPageButtons - 1);

            if (currentPage > 1) {
                pagination += `<li class="page-item p-1"><a class="page-link border-0 rounded-circle text-dark fs-6 round-32 d-flex align-items-center justify-content-center page-link-prev" href="#">‹</a></li>`;
            }

            for (var i = startPage; i <= endPage; i++) {
                pagination += `<li class="page-item p-1 ${i === currentPage ? 'active' : ''}"><a class="page-link border-0 rounded-circle text-dark fs-6 round-32 d-flex align-items-center justify-content-center" href="#">${i}</a></li>`;
            }

            if (currentPage < totalPages) {
                pagination += `<li class="page-item p-1"><a class="page-link border-0 rounded-circle text-dark fs-6 round-32 d-flex align-items-center justify-content-center page-link-next" href="#">›</a></li>`;
            }

            return pagination;
        }

        function performSearch(query) {
            var color_id = $('#select-color').find('option:selected').val();
            var type_id = $('#select-type').find('option:selected').val();
            var line_id = $('#select-line').find('option:selected').val();
            var category_id = $('#select-category').find('option:selected').val();
            var onlyInStock = $('#toggleActive').prop('checked');
            $.ajax({
                url: 'pages/estimate_list_ajax.php',
                type: 'POST',
                data: {
                    query: query,
                    color_id: color_id,
                    type_id: type_id,
                    line_id: line_id,
                    category_id: category_id,
                    onlyInStock: onlyInStock,
                    action: 'search_product',
                },
                success: function(response) {
                    $('#productTableBody').html(response);
                    currentPage = 1;
                    updateTable();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        }

        $('#rowsPerPage').change(function() {
            rowsPerPage = parseInt($(this).val());
            currentPage = 1;
            updateTable();
        });


        $(document).on('input change', '#text-srh, #select-category, #select-type, #select-line', function() {
            performSearch($('#text-srh').val());
        });

        $('#select-color').select2();
        $('#select-type').select2();
        $('#select-line').select2();
        $('#select-category').select2();

        $(document).on('input change', '#text-srh, #select-color, #select-category, #select-type, #select-line, #toggleActive', function() {
            performSearch($('#text-srh').val());
        });

        performSearch('');
        updateTable();
    });
</script>