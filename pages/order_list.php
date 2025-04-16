<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$picture_path = "images/product/product.jpg";

if(isset($_REQUEST['customer_id'])){
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
            ?> Order List</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Order
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Order List</li>
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

    <div class="modal fade" id="viewOrderModal" tabindex="-1" aria-labelledby="viewOrderModalLabel" aria-hidden="true"></div>

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

    <div class="modal fade" id="shipFormModal" tabindex="-1" aria-labelledby="shipFormModalLabel" aria-hidden="true" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shipFormModalLabel">Shipping Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="shipOrderForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tracking_number" class="form-label">Tracking Number</label>
                            <input type="text" class="form-control" id="tracking_number" name="tracking_number" required>
                        </div>

                        <div>
                        <label for="shipping_company" class="form-label">Shipping Company</label>
                        <div class="mb-3">
                            <select class="form-select select2" id="shipping_company" name="shipping_company" required>
                                <option value="">Select Shipping Company...</option>
                                <?php
                                $query_shipping_company = "SELECT * FROM shipping_company WHERE status = '1' AND hidden = '0' ORDER BY `shipping_company` ASC";
                                $result_shipping_company = mysqli_query($conn, $query_shipping_company);            
                                while ($row_shipping_company = mysqli_fetch_array($result_shipping_company)) {
                                ?>
                                    <option value="<?= $row_shipping_company['shipping_company_id'] ?>"><?= $row_shipping_company['shipping_company'] ?></option>
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

    <div class="card card-body">
        <div class="card-body datatables">
            <div class="product-details table-responsive text-nowrap">
                <table id="order_list_tbl" class="table table-hover mb-0 text-md-nowrap">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Total Price</th>
                            <th>Discounted Price</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 

                        $query = "SELECT * FROM orders";

                        if (isset($customer_id) && !empty($customer_id)) {
                            $query .= " AND customerid = '$customer_id'";
                        }

                        $result = mysqli_query($conn, $query);
                    
                        if ($result && mysqli_num_rows($result) > 0) {
                            $response = array();
                            while ($row = mysqli_fetch_assoc($result)) {
                                $status_code = $row['status'];

                                $status_labels = [
                                    1 => ['label' => 'New Order', 'class' => 'badge bg-primary'],
                                    2 => ['label' => 'Processing', 'class' => 'badge bg-warning'],
                                    3 => ['label' => 'In Transit', 'class' => 'badge bg-info'],
                                    4 => ['label' => 'Delivered', 'class' => 'badge bg-success']
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
                                    <?php echo date("F d, Y", strtotime($row["order_date"])); ?>
                                </td>
                                <td class="text-center">
                                    <span class="<?= $status['class']; ?> fw-bond"><?= $status['label']; ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="view_order_btn" type="button" data-id="<?php echo $row["orderid"]; ?>"><i class="text-primary fa fa-eye fs-5"></i></button>
                                    <?php if ($status_code == 1): ?>
                                        <a href="javascript:void(0)" type="button" id="email_order_btn" class="me-1 email_order_btn" data-customer="<?= $row["customerid"]; ?>" data-id="<?= $row["orderid"]; ?>">
                                            <i class="fa fa-envelope fs-5 text-info"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="print_order_product.php?id=<?= $row["orderid"]; ?>" target="_blank" class="btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["orderid"]; ?>"><i class="text-success fa fa-print fs-5"></i></a>
                                    <a href="print_order_total.php?id=<?= $row["orderid"]; ?>" target="_blank" class="btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["orderid"]; ?>"><i class="text-white fa fa-file-lines fs-5"></i></a>
                                    <a href="customer/index.php?page=order&id=<?=$row["orderid"]?>&key=<?=$row["order_key"]?>" target="_blank" class="btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["orderid"]; ?>"><i class="text-info fa fa-sign-in-alt fs-5"></i></a>
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
    $(document).ready(function() {
        var dataId = '';
        var action = '';
        var selected_prods = [];
        
        var table = $('#order_list_tbl').DataTable({
            "order": [[1, "asc"]]
        });

        $(document).on('click', '#view_order_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/order_list_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_view_modal"
                    },
                    success: function(response) {
                        $('#viewOrderModal').html(response);
                        $('#viewOrderModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $(document).on('click', '.email_order_btn', function(event) {
            event.preventDefault(); 

            if (!confirm("Are you sure you want to accept and send confirmation message to customer?")) {
                return;
            }

            var id = $(this).data("id");
            var customerid = $(this).data("customer");

            console.log(customerid);

            $.ajax({
                url: 'pages/order_list_ajax.php',
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

        $(document).on("click", "#processOrderBtn", function () {
            dataId = $(this).data("id");
            action = $(this).data("action");
            selected_prods = getSelectedIDs();

            var confirmMessage = action.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });

            if (confirm("Are you sure you want to " + confirmMessage + "?")) {
                $.ajax({
                    url: 'pages/order_list_ajax.php',
                    type: 'POST',
                    data: {
                        id: dataId,
                        method: action,
                        selected_prods: selected_prods,
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
                            location.reload();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("AJAX Error:", xhr.responseText);
                    }
                });
            }
        });

        $(document).on("click", "#shipOrderBtn", function () {
            dataId = $(this).data("id");
            action = $(this).data("action");
            selected_prods = getSelectedIDs();

            if (!Array.isArray(selected_prods) || selected_prods.length === 0) {
                alert("Select at least 1 product to deliver.");
                return;
            }

            $("#shipFormModal").modal("show");
        });

        $(document).on("submit", "#shipOrderForm", function (e) {
            e.preventDefault();

            var tracking_number = $('#tracking_number').val();
            var shipping_company = $('#shipping_company').val();

            $.ajax({
                url: 'pages/order_list_ajax.php',
                type: 'POST',
                data: {
                    id: dataId,
                    method: action,
                    selected_prods: selected_prods,
                    tracking_number: tracking_number,
                    shipping_company: shipping_company,
                    action: 'update_status'
                },
                success: function (response) {
                    console.log(response);

                    try {
                        var jsonResponse = JSON.parse(response);
                    } catch (e) {
                        console.error("Invalid JSON:", e);
                        alert("Unexpected response from server.");
                        return;
                    }

                    if (jsonResponse.success) {
                        alert("Status updated successfully!");
                    } else {
                        alert("Failed to update");
                    }

                    location.reload();
                },
                error: function (xhr, status, error) {
                    console.log("AJAX Error:", xhr.responseText);
                    alert("An error occurred. Please try again.");
                }
            });
        });
    });
</script>