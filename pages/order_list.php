<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$picture_path = "images/product/product.jpg";

$page_title = "Order List";

$status_labels = [
    1 => ['label' => 'New Order', 'class' => 'badge bg-primary'],
    2 => ['label' => 'Processing', 'class' => 'badge bg-warning'],
    3 => ['label' => 'In Transit', 'class' => 'badge bg-info'],
    4 => ['label' => 'Sent to Customer', 'class' => 'badge bg-success']
];

if(isset($_REQUEST['customer_id'])){
    $customer_id = $_REQUEST['customer_id'];
    $customer_details = getCustomerDetails($customer_id);
}

?>
<style>
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
        z-index: 9999;
        cursor: pointer;
    }

    .tooltip-inner {
        background-color: #f8f9fa !important;
        color: #212529 !important;
        border: 1px solid #ced4da;
        font-size: 0.875rem;
        padding: 6px 10px;
        border-radius: 0.25rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .tooltip.bs-tooltip-top .tooltip-arrow::before,
    .tooltip.bs-tooltip-bottom .tooltip-arrow::before,
    .tooltip.bs-tooltip-start .tooltip-arrow::before,
    .tooltip.bs-tooltip-end .tooltip-arrow::before {
        border-top-color: #f8f9fa !important;
        border-bottom-color: #f8f9fa !important;
        border-left-color: #f8f9fa !important;
        border-right-color: #f8f9fa !important;
    }

    .select2-container .select2-dropdown .select2-results__options {
        max-height: 760px !important;
    }

    .modal.custom-size .modal-dialog {
        width: 80%;
        max-width: none;
        margin: 0 auto;
        height: 100vh;
    }

    .modal.custom-size .modal-content {
        height: 100%;
        border-radius: 0;
    }

    .modal.custom-size .modal-body {
        height: calc(100% - 56px);
        overflow: hidden;
    }

    .modal.custom-size iframe {
        width: 100%;
        height: 80%;
        border: none;
    }
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
            ?> <?= $page_title ?></h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Order
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page"><?= $page_title ?></li>
            </ol>
            </nav>
        </div>
        <div>
            
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
        <div class="row">
            <div class="col-3">
                <h3 class="card-title align-items-center mb-2">
                    Filter <?= $page_title ?>
                </h3>
                <div class="position-relative w-100 px-0 mr-0 mb-2">
                    <input type="text" class="form-control py-2 ps-5" data-filter-name="Customer Name" id="text-srh" placeholder="Search">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
                <div class="align-items-center">
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" data-filter="by" data-filter-name="Created By" id="select-created-by">
                            <option value="">All Created</option>
                            <option value="1">Created by EKM</option>
                            <option value="2">Created by Customer</option>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" data-filter="status" data-filter-name="Status" id="select-status">
                            <option value="">All Status</option>
                            <?php

                                foreach ($status_labels as $key => $value) {
                                    echo "<option value=\"$key\">{$value['label']}</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control py-0 ps-5 select2 filter-selection" data-filter="tax" data-filter-name="Tax" id="select-tax">
                            <option value="">All Tax Status</option>
                            <?php
                            $query_tax_status = "SELECT * FROM customer_tax WHERE status = 1 ORDER BY tax_status_desc ASC";
                            $result_tax_status = mysqli_query($conn, $query_tax_status);
                            while ($row_tax_status = mysqli_fetch_array($result_tax_status)) {
                                ?>
                                <option value="<?= $row_tax_status['taxid'] ?>">
                                (<?= $row_tax_status['percentage'] ?>%) <?= $row_tax_status['tax_status_desc'] ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" data-filter="month" data-filter-name="Month" id="select-month">
                            <option value="">All Months</option>
                            <option value="01">January</option>
                            <option value="02">February</option>
                            <option value="03">March</option>
                            <option value="04">April</option>
                            <option value="05">May</option>
                            <option value="06">June</option>
                            <option value="07">July</option>
                            <option value="08">August</option>
                            <option value="09">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>

                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" data-filter="year" data-filter-name="Year" id="select-year">
                            <option value="">All Years</option>
                            <?php
                                $currentYear = date("Y");
                                for ($year = $currentYear; $year >= $currentYear - 20; $year--) {
                                    echo "<option value=\"$year\">$year</option>";
                                }
                            ?>
                        </select>
                    </div>
                    
                </div>
                <div class="px-3 mb-2"> 
                    <input type="checkbox" id="toggleActive" checked> Show Processing Only
                </div>
                <div class="d-flex justify-content-end py-2">
                    <button type="button" class="btn btn-outline-primary reset_filters">
                        <i class="fas fa-sync-alt me-1"></i> Reset Filters
                    </button>
                </div>
            </div>
            <div class="col-9">
                <div id="selected-tags" class="mb-2"></div>
                    <div class="datatables">
                        <h4 class="card-title d-flex justify-content-between align-items-center"><?= $page_title ?></h4>
                        <div class="product-details table-responsive text-nowrap">
                            <table id="order_list_tbl" class="table table-hover mb-0 text-md-nowrap">
                                <thead>
                                    <tr>
                                        <th>OrderID</th>
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
                                            $customer_id = $row["customerid"];
                                            $customer_details = getCustomerDetails($customer_id);
                                        
                                            $status = $status_labels[$status_code];
                                        ?>
                                        <tr
                                            data-by="<?= $row['order_from'] ?>"
                                            data-tax="<?= $customer_details['tax_status'] ?>"
                                            data-month="<?= date('m', strtotime($row['order_date'])) ?>"
                                            data-year="<?= date('Y', strtotime($row['order_date'])) ?>"
                                            data-status="<?= $status_code ?>"
                                        >
                                            <td style="color: #ffffff !important;">
                                                <?= $row["orderid"]; ?>
                                            </td>
                                            <td style="color: #ffffff !important;">
                                                <?php echo get_customer_name($row["customerid"]) ?>
                                            </td>
                                            <td style="color: #ffffff !important;">
                                                $ <?php echo number_format($row["total_price"],2) ?>
                                            </td>
                                            <td style="color: #ffffff !important;">
                                                $ <?php echo number_format($row["discounted_price"],2) ?>
                                            </td>
                                            <td style="color: #ffffff !important;"
                                                <?php if (isset($row["order_date"]) && !empty($row["order_date"]) && $row["order_date"] !== '0000-00-00 00:00:00') : ?>
                                                    data-order="<?= date('Y-m-d', strtotime($row["order_date"])) ?>"
                                                <?php endif; ?>
                                            >
                                                <?php 
                                                    if (isset($row["order_date"]) && !empty($row["order_date"]) && $row["order_date"] !== '0000-00-00 00:00:00') {
                                                        echo date("F d, Y", strtotime($row["order_date"]));
                                                    } else {
                                                        echo '';
                                                    }
                                                ?>
                                            </td>
                                            <td class="text-center" style="color: #ffffff !important;">
                                                <span class="estimate_status <?= $status['class']; ?> fw-bond"><?= $status['label']; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="view_order_btn" type="button" data-id="<?php echo $row["orderid"]; ?>" data-bs-toggle="tooltip" title="View Order">
                                                    <i class="text-primary fa fa-eye fs-5"></i>
                                                </button>

                                                <?php if ($status_code == 1): ?>
                                                    <a href="javascript:void(0)" type="button" id="email_order_btn" class="me-1 email_order_btn" data-customer="<?= $row["customerid"]; ?>" data-id="<?= $row["orderid"]; ?>" data-bs-toggle="tooltip" title="Email Order">
                                                        <i class="fa fa-envelope fs-5 text-info"></i>
                                                    </a>
                                                <?php endif; ?>

                                                <a href="print_order_product.php?id=<?= $row["orderid"]; ?>" class="btn-show-pdf btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["orderid"]; ?>" data-bs-toggle="tooltip" title="Print Product">
                                                    <i class="text-success fa fa-print fs-5"></i>
                                                </a>

                                                <a href="print_order_total.php?id=<?= $row["orderid"]; ?>" class="btn-show-pdf btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["orderid"]; ?>" data-bs-toggle="tooltip" title="Print Total">
                                                    <i class="text-white fa fa-file-lines fs-5"></i>
                                                </a>

                                                <a href="customer/index.php?page=order&id=<?=$row["orderid"]?>&key=<?=$row["order_key"]?>" target="_blank" class="btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["orderid"]; ?>" data-bs-toggle="tooltip" title="Open Customer View">
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
    </div>
</div>

<div class="modal fade custom-size" id="pdfModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Print/View Outputs</h5>
        <button type="button" class="close" data-bs-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <iframe id="pdfFrame" src=""></iframe>

        <div class="container mt-3 border rounded p-3" style="width: 100%;">
        <h6 class="mb-3">Download Outputs</h6>
        <div class="row">
            <div class="col-md-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="officeCopy">
                <label class="form-check-label" style="color: #ffffff;" for="officeCopy">Cover Sheet (Office Copy)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="customerCopy">
                <label class="form-check-label" style="color: #ffffff;" for="customerCopy">Cover Sheet (Customer Copy)</label>
            </div>
            </div>
            <div class="col-md-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="ekmCost">
                <label class="form-check-label" style="color: #ffffff;" for="ekmCost">EKM Cost Breakdown</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="noPrice">
                <label class="form-check-label" style="color: #ffffff;" for="noPrice">Cover Sheet w/o Price</label>
            </div>
            </div>
            <div class="col-md-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="jobCsv">
                <label class="form-check-label" style="color: #ffffff;" for="jobCsv">Job Data CSV</label>
            </div>
            </div>
        </div>
        <div class="mt-3 text-end">
            <button id="printBtn" class="btn btn-success me-2">Print</button>
            <button id="downloadBtn" class="btn btn-primary me-2">Download</button>
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
    function isValidURL(str) {
        try {
            new URL(str);
            return true;
        } catch (_) {
            return false;
        }
    }
    $(document).ready(function() {
        document.title = "<?= $page_title ?>";

        var dataId = '';
        var action = '';
        var selected_prods = [];

        var pdfUrl = '';
        var isPrinting = false;

        var table = $('#order_list_tbl').DataTable({
            "order": [[3, "desc"]],
            "pageLength": 100,
            "columnDefs": [
                { targets: '_all', orderable: true }
            ]
        });

        $('#order_list_tbl_filter').hide();

        $(".select2").each(function () {
            $(this).select2({
                width: '100%',
                dropdownParent: $(this).parent()
            });
        });

        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            var statusSpan = $(table.row(dataIndex).node()).find('td').find('span.estimate_status');
            var isActive = $('#toggleActive').is(':checked');

            var statusText = statusSpan.text().trim();

            if (isActive && statusText === 'Sent to Customer') {
                return false;
            }
            
            return true;
        });

        $('#toggleActive').on('change', function () {
            table.draw();
        });

        $('#toggleActive').trigger('change');

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

        $(document).on('click', '.btn-show-pdf', function(e) {
            e.preventDefault();
            pdfUrl = $(this).attr('href');
            document.getElementById('pdfFrame').src = pdfUrl;
            const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
            modal.show();
        });

        $('#printBtn').on('click', function () {
            if (isPrinting) {
                return;
            }

            isPrinting = true;
            const $iframe = $('#pdfFrame');

            $iframe.off('load').one('load', function () {
                try {
                    this.contentWindow.focus();
                    this.contentWindow.print();
                } catch (e) {
                    alert("Failed to print PDF.");
                }
                isPrinting = false;
            });

            $iframe.attr('src', pdfUrl);

            const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
            modal.show();
        });


        $('#downloadBtn').on('click', function () {
            const link = document.createElement('a');
            link.href = pdfUrl;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
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

                        if (jsonResponse.url && isValidURL(jsonResponse.url)) {
                            window.open(jsonResponse.url, '_blank');
                        }else{
                            console.log("invalid url");
                        }
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

        function filterTable() {
            var textSearch = $('#text-srh').val().toLowerCase();
            var isActive = $('#toggleActive').is(':checked');

            $.fn.dataTable.ext.search = [];

            if (textSearch) {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    return $(table.row(dataIndex).node()).text().toLowerCase().includes(textSearch);
                });
            }

            if (isActive) {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    var statusText = $(table.row(dataIndex).node()).find('span.estimate_status').text().trim();
                    return statusText !== 'Sent to Customer';
                });
            }

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var row = $(table.row(dataIndex).node());
                var match = true;

                $('.filter-selection').each(function() {
                    var filterValue = $(this).val()?.toString() || '';
                    var rowValue = row.data($(this).data('filter'))?.toString() || '';

                    if (filterValue && filterValue !== '/' && rowValue !== filterValue) {
                        match = false;
                        return false;
                    }
                });

                return match;
            });

            table.draw();
            updateSelectedTags();
        }

        $(document).on('change', '.filter-selection', filterTable);

        $(document).on('input', '#text-srh', filterTable);

        $(document).on('change', '#toggleActive', filterTable);

        function updateSelectedTags() {
            var displayDiv = $('#selected-tags');
            displayDiv.empty();

            $('.filter-selection').each(function() {
                var selectedOption = $(this).find('option:selected');
                var selectedText = selectedOption.text().trim();
                var filterName = $(this).data('filter-name');

                if ($(this).val()) {
                    displayDiv.append(`
                        <div class="d-inline-block p-1 m-1 border rounded bg-light">
                            <span class="text-dark">${filterName}: ${selectedText}</span>
                            <button type="button" 
                                class="btn-close btn-sm ms-1 remove-tag" 
                                style="width: 0.75rem; height: 0.75rem;" 
                                aria-label="Close" 
                                data-select="#${$(this).attr('id')}">
                            </button>
                        </div>
                    `);
                }
            });

            $('.remove-tag').on('click', function() {
                $($(this).data('select')).val('').trigger('change');
                $(this).parent().remove();
            });
        }

        $(document).on('click', '.reset_filters', function () {
            $('.filter-selection').each(function () {
                $(this).val(null).trigger('change.select2');
            });

            $('#text-srh').val('');

            filterTable();
        });
    });
</script>