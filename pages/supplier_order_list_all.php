<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

if($_REQUEST['supplier_id']){
    $supplier_id = $_REQUEST['supplier_id'];
    $supplier_details = getSupplierName($supplier_id);
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
</style>

<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"><?php
            if(isset($customer_details)){
                echo $supplier_details['supplier_name'];
            }else {
                echo "Supplier";
            }
            ?> Order List</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="?page=">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Supplier Order List</li>
            </ol>
            </nav>
        </div>
        <div>
            <div class="d-sm-flex d-none gap-3 no-block justify-content-end align-items-center">
            
            </div>
        </div>
        </div>
    </div>
    </div>

    <div class="widget-content searchable-container list">
    
    
    <div class="modal fade" id="viewSupplierOrderModal" tabindex="-1" aria-labelledby="viewSupplierOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title">
                        View Supplier Order
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="supplier_order_details_section">
                    
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
                            <th>Supplier</th>
                            <th>Amount</th>
                            <th>Time</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 

                        $query = "SELECT * FROM supplier_orders ORDER BY order_date DESC";

                        if (isset($supplier_id) && !empty($supplier_id)) {
                            $query .= " AND supplier_id = '$supplier_id'";
                        }

                        $result = mysqli_query($conn, $query);
                    
                        if ($result && mysqli_num_rows($result) > 0) {
                            $response = array();
                            while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr>
                                <td>
                                    <?php echo getSupplierName($row["supplier_id"]) ?>
                                </td>
                                <td >
                                    $ <?php echo getSupplierOrderedTotals($row["supplier_order_id"],2) ?>
                                </td>
                                <td>
                                    <?php echo date("h:i A", strtotime($row["order_date"])); ?>
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
                                <td>
                                    <?php
                                    $status_labels = [
                                        1 => ['label' => 'New Order', 'class' => 'badge bg-primary'],
                                        2 => ['label' => 'Pending EKM Approval', 'class' => 'badge bg-warning text-dark'],
                                        3 => ['label' => 'Pending Supplier Approval', 'class' => 'badge bg-warning text-dark'],
                                        4 => ['label' => 'Approved, Waiting to Process', 'class' => 'badge bg-secondary'],
                                        5 => ['label' => 'Processing', 'class' => 'badge bg-success'],
                                        6 => ['label' => 'In Transit', 'class' => 'badge bg-info'],
                                        7 => ['label' => 'Delivered', 'class' => 'badge bg-success']
                                    ];

                                    $status = intval($row["status"]);
                                    $status_info = $status_labels[$status] ?? ['label' => 'Unknown', 'class' => 'badge bg-secondary'];
                                    ?>
                                    <span class="<?= $status_info['class'] ?>"><?= $status_info['label'] ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="view_order_btn" type="button" data-id="<?php echo $row["supplier_order_id"]; ?>"><i class="text-white fa fa-eye fs-5"></i></button>
                                    <a href="print_supplier_order.php?id=<?= $row["supplier_order_id"]; ?>" target="_blank" class="btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["supplier_order_id"]; ?>"><i class="text-info fa fa-print fs-5"></i></a>
                                    <a href="supplier/index.php?id=<?$row["supplier_order_id"]?>&key=<?$row["order_key"]?>" target="_blank" class="btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["supplier_order_id"]; ?>">
                                        <i class="text-info fa fa-sign-in-alt fs-5"></i>
                                    </a>
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
    function loadOrderProducts(id){
        $.ajax({
            url: 'pages/supplier_order_list_all_ajax.php',
            type: 'POST',
            data: {
                id: id,
                action: "fetch_view_modal"
            },
            success: function(response) {
                $('#supplier_order_details_section').html(response);
                $('#viewSupplierOrderModal').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    $(document).ready(function() {
        var table = $('#est_list_tbl').DataTable();
        var order_id = 0;

        $(document).on('click', '#view_order_btn', function(event) {
            event.preventDefault(); 
            order_id = $(this).data('id');
            loadOrderProducts(order_id);
            $('#viewSupplierOrderModal').modal('show');
        });

        $(document).on("click", "#returnBtn, #finalizeBtn, #markDelivered", function () {
            var dataId = $(this).data("id");
            var action = $(this).data("action");

            var confirmMessage = action.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });

            if (confirm("Are you sure you want to " + confirmMessage + "?")) {
                $.ajax({
                    url: 'pages/supplier_order_list_all_ajax.php',
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

        $(document).on("click", ".btn-edit", function () {
            let Id = $(this).data("id");
            let productName = $(this).data("name");
            let productQuantity = $(this).data("quantity");
            let productPrice = $(this).data("price");
            let productColor = $(this).data("color");

            $("#editId").val(Id);
            $("#editProductName").val(productName);
            $("#editProductQuantity").val(productQuantity);
            $("#editProductPrice").val(productPrice);
            $("#editProductColor").val(productColor).trigger("change");
            $("#editProductModal").modal("show");
        });

        $(document).on("submit", "#editProductForm", function (e) { 
            e.preventDefault();
            let formData = new FormData(this);
            formData.append("action", "update_product");

            $.ajax({
                url: 'pages/supplier_order_list_all_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    if (response.success) {
                        alert("Product updated successfully!");
                        $("#editProductModal").modal("hide");
                        location.reload();
                    } else {
                        alert("Error updating product.");
                        location.reload();
                    }
                },
                error: function(xhr, status, error) {
                    console.log("AJAX Error:", xhr.responseText);
                }
            });
        });
    });
</script>