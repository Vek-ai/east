<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$picture_path = "images/product/product.jpg";

$page_title = "Estimate List";

$status_labels = [
    1 => ['label' => 'New Estimate', 'class' => 'badge bg-primary'],
    2 => ['label' => 'Email Sent to Customer', 'class' => 'badge bg-success'],
    3 => ['label' => 'Modified by Customer', 'class' => 'badge bg-warning'],
    4 => ['label' => 'Approved', 'class' => 'badge bg-secondary'],
    5 => ['label' => 'Processing', 'class' => 'badge bg-success'],
    6 => ['label' => 'In Transit', 'class' => 'badge bg-info'],
    7 => ['label' => 'Delivered', 'class' => 'badge bg-success']
];

if(isset($_REQUEST['customer_id'])){
    $customer_id = $_REQUEST['customer_id'];
    $customer_details = getCustomerDetails($customer_id);
}

$permission = $_SESSION['permission'];

$staff_id = intval($_SESSION['userid']);
$profileSql = "SELECT access_profile_id FROM staff WHERE staff_id = $staff_id";
$profileRes = mysqli_query($conn, $profileSql);
$profile_id = 0;
if ($profileRes && mysqli_num_rows($profileRes) > 0) {
    $profile_id = intval(mysqli_fetch_assoc($profileRes)['access_profile_id']);
}
$page_id = getPageIdFromUrl($_GET['page'] ?? '');

$visibleColumns = getVisibleColumns($page_id, $profile_id);
function showCol($name) {
    global $visibleColumns;
    return !empty($visibleColumns[$name]);
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
                <a class="text-muted text-decoration-none" href="?page=">Home
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

    <div class="modal fade" id="viewEstimateModal" tabindex="-1" aria-labelledby="viewEstimateModalLabel" aria-hidden="true"></div>

    <div class="modal fade" id="editEstimateModal" tabindex="-1" aria-labelledby="editEstimateModalLabel" aria-hidden="true"></div>

    <div class="modal fade" id="viewChangesModal" tabindex="-1" aria-labelledby="viewChangesModalLabel" aria-hidden="true"></div>
    
    <div class="modal fade" id="addEstimateModal" tabindex="-1" aria-labelledby="addEstimateModalLabel" aria-hidden="true"></div>

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
                        <select class="form-control py-0 ps-5 select2 filter-selection" data-filter="cashier" data-filter-name="Salesperson" id="select-cashier">
                            <option value="">All Salespersons</option>
                            <?php
                            $query_staff = "SELECT staff_id, staff_fname, staff_lname FROM staff WHERE status = 1 ORDER BY staff_fname ASC";
                            $result_staff = mysqli_query($conn, $query_staff);
                            while ($row_staff = mysqli_fetch_assoc($result_staff)) {
                                ?>
                                <option value="<?= $row_staff['staff_id'] ?>">
                                    <?= htmlspecialchars($row_staff['staff_fname'] . ' ' . $row_staff['staff_lname']) ?>
                                </option>
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
                    <div class="product-details table-responsive text-wrap">
                        <table id="est_list_tbl" class="table table-hover mb-0 text-wrap">
                            <thead>
                                <tr>
                                    <?php if (showCol('estimateid')): ?>
                                        <th>Estimate ID</th>
                                    <?php endif; ?>

                                    <?php if (showCol('customer_name')): ?>
                                        <th>Customer Name</th>
                                    <?php endif; ?>

                                    <?php if (showCol('total_discounted')): ?>
                                        <th>Total</th>
                                    <?php endif; ?>

                                    <?php if (showCol('estimated_date')): ?>
                                        <th>Estimated Date</th>
                                    <?php endif; ?>

                                    <?php if (showCol('order_date')): ?>
                                        <th>Order Date</th>
                                    <?php endif; ?>

                                    <?php if (showCol('status')): ?>
                                        <th>Status</th>
                                    <?php endif; ?>

                                    <?php if (showCol('cashier')): ?>
                                        <th>Cashier</th>
                                    <?php endif; ?>

                                    <?php if (showCol('actions')): ?>
                                        <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 

                                $query = "SELECT * FROM estimates WHERE 1=1";

                                if (isset($customer_id) && !empty($customer_id)) {
                                    $query .= " AND customerid = '$customer_id'";
                                }

                                $query .= " ORDER BY estimated_date DESC";

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
                                        data-cashier="<?= $row['cashier'] ?>"
                                        data-month="<?= date('m', strtotime($row['estimated_date'])) ?>"
                                        data-year="<?= date('Y', strtotime($row['estimated_date'])) ?>"
                                        data-status="<?= $status_code ?>"
                                    >
                                        <?php if (showCol('estimateid')): ?>
                                            <td>
                                                <?= $row["estimateid"] ?>
                                            </td>
                                        <?php endif; ?>

                                        <?php if (showCol('customer_name')): ?>
                                            <td>
                                                <?= ucwords(get_customer_name($row["customerid"])) ?>
                                            </td>
                                        <?php endif; ?>

                                        <?php if (showCol('total_discounted')): ?>
                                            <td>
                                                $ <?= getEstimateTotalsDiscounted($row["estimateid"]) ?>
                                            </td>
                                        <?php endif; ?>

                                        <?php if (showCol('estimated_date')): ?>
                                            <td 
                                                <?php if (!empty($row["estimated_date"]) && $row["estimated_date"] !== '0000-00-00 00:00:00') : ?>
                                                    data-order="<?= date('Y-m-d', strtotime($row["estimated_date"])) ?>"
                                                <?php endif; ?>
                                            >
                                                <?php 
                                                    if (!empty($row["estimated_date"]) && $row["estimated_date"] !== '0000-00-00 00:00:00') {
                                                        echo date("F d, Y", strtotime($row["estimated_date"]));
                                                    } else {
                                                        echo '';
                                                    }
                                                ?>
                                            </td>
                                        <?php endif; ?>

                                        <?php if (showCol('order_date')): ?>
                                            <td
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
                                        <?php endif; ?>

                                        <?php if (showCol('status')): ?>
                                            <td class="text-center">
                                                <span class="estimate_status <?= $status['class']; ?> fw-bond"><?= $status['label']; ?></span>
                                            </td>
                                        <?php endif; ?>

                                        <?php if (showCol('cashier')): ?>
                                            <td>
                                                <?= ucwords(get_staff_name($row["cashier"])) ?>
                                            </td>
                                        <?php endif; ?>

                                        <?php if (showCol('actions')): ?>
                                            <td class="text-center">
                                                <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="view_estimate_btn" type="button" data-id="<?= $row["estimateid"]; ?>" data-bs-toggle="tooltip" title="View Estimate">
                                                    <i class="text-primary fa fa-eye fs-5"></i>
                                                </button>
                                                
                                                <?php                                                    
                                                if ($permission === 'edit') {
                                                ?>
                                                <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="edit_estimate_btn" type="button" data-id="<?= $row["estimateid"]; ?>" data-bs-toggle="tooltip" title="Edit Estimate">
                                                    <i class="text-warning fa fa-pencil fs-5"></i>
                                                </button>
                                                <?php
                                                }
                                                ?>

                                                <?php                                                    
                                                if ($permission === 'edit') {
                                                ?>
                                                <button class="btn btn-danger-gradient btn-sm p-0 me-1 email_estimate_btn" data-customer="<?= $row["customerid"]; ?>" id="email_estimate_btn" type="button" data-id="<?= $row["estimateid"]; ?>" data-bs-toggle="tooltip" title="Send Email to Customer">
                                                    <iconify-icon icon="solar:plain-linear" class="fs-5 text-info"></iconify-icon>
                                                </button>
                                                <?php
                                                }
                                                ?>
                                                
                                                <a href="print_estimate_product.php?id=<?= $row["estimateid"]; ?>" class="btn-show-pdf btn btn-danger-gradient btn-sm p-0 me-1" data-id="<?= $row["estimateid"]; ?>" data-bs-toggle="tooltip" title="Print/Download">
                                                    <i class="text-success fa fa-print fs-5"></i>
                                                </a>
                                                
                                                <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="view_changes_btn" type="button" data-id="<?= $row["estimateid"]; ?>" data-bs-toggle="tooltip" title="View Change History">
                                                    <i class="text-info fa fa-clock-rotate-left fs-5"></i>
                                                </button>

                                                <a href="customer/index.php?page=estimate&id=<?= $row["estimateid"]; ?>&key=<?= $row["est_key"]; ?>" target="_blank" class="btn btn-danger-gradient btn-sm p-0 me-1" data-id="<?= $row["estimateid"]; ?>" data-bs-toggle="tooltip" title="Customer View">
                                                    <i class="text-info fa fa-sign-in-alt fs-5"></i>
                                                </a>
                                                
                                                <?php                                                    
                                                if ($permission === 'edit') {
                                                ?>
                                                <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="delete_estimate_btn" type="button" data-id="<?= $row["estimateid"]; ?>" data-bs-toggle="tooltip" title="Delete Estimate">
                                                    <i class="text-danger fa fa-trash fs-5"></i>
                                                </button>
                                                <?php
                                                }
                                                ?>
                                                
                                            </td>
                                        <?php endif; ?>
                                        
                                        
                                        
                                        
                                        
                                        
                                        
                                        
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
        <div class="modal-body" style="overflow: auto;">
            <iframe id="pdfFrame" src="" style="height: 70vh; width: 100%;" class="mb-3 border rounded"></iframe>

            <div class="container-fluid border rounded p-3">
                <?php
                $sql = "SELECT id, pricing_name FROM customer_pricing WHERE status = 1 AND hidden = 0 ORDER BY pricing_name ASC";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    echo '<div class="mt-3 text-center">';
                    echo '<div class="d-flex flex-wrap justify-content-center">';
                    while ($row = $result->fetch_assoc()) {
                        echo '<button type="button" class="btn btn-secondary btn-sm mx-1 my-1 pricing-btn" style="color:#000;" id="view_customer_pricing" data-id="' . $row['id'] . '">'
                            . htmlspecialchars($row['pricing_name']) .
                            '</button>';
                    }
                    echo '</div></div>';
                } else {
                    echo '<p>No active pricing types found.</p>';
                }
                ?>

                <div class="mt-3 d-flex flex-wrap justify-content-end gap-2">
                    <button id="printBtn" class="btn btn-success">Print</button>
                    <button id="downloadBtn" class="btn btn-primary">Download</button>
                    <button class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>

      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="sendOrderModal" tabindex="-1" aria-labelledby="sendOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendOrderModalLabel">Send Options</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <h6 class="mb-3">How would you like to send the order to the customer?</h6>

                <form class="send_order_form d-flex flex-column flex-md-row align-items-center justify-content-center gap-2" method="post">
                    <input id="send_order_id" type="hidden" name="id" value="">
                    <input id="send_customer_id" type="hidden" name="customerid" value="">

                    <select name="send_option" class="form-select form-select-sm w-auto">
                        <option value="email">Email</option>
                        <option value="sms">Text Message</option>
                        <option value="both">Both</option>
                    </select>

                    <button type="submit" class="btn btn-sm btn-primary">Send</button>
                </form>
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
        var pdfUrl = '';
        var isPrinting = false;

        var table = $('#est_list_tbl').DataTable({
            "order": [],
            "pageLength": 100,
            "columnDefs": [
                { targets: '_all', orderable: true }
            ]
        });

        $('#est_list_tbl_filter').hide();

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

            if (isActive && statusText === 'Delivered') {
                return false;
            }
            
            return true;
        });

        $('#toggleActive').on('change', function () {
            table.draw();
        });

        $('#toggleActive').trigger('change');

        $(document).on('click', '.email_estimate_btn', function () {
            const orderId = $(this).data('id');
            const customerId = $(this).data('customer');

            $('#send_order_id').val(orderId);
            $('#send_customer_id').val(customerId);

            $('#sendOrderModal').modal('show');
        });

        $(document).on('submit', '.send_order_form', function (e) {
            e.preventDefault();

            const $form = $(this);
            const formData = new FormData(this);
            formData.append('action', 'send_email');

            $.ajax({
                url: 'pages/estimate_list_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    $form.find('button').prop('disabled', true).text('Sending...');
                },
                success: function (response) {
                    console.log(response);
                    let jsonResponse;

                    try {
                        jsonResponse = (typeof response === "string") ? JSON.parse(response) : response;
                    } catch (e) {
                        jsonResponse = { success: false, message: "Invalid JSON response" };
                    }

                    const emailOk = jsonResponse?.email_success === true;
                    const smsOk = jsonResponse?.sms_success === true;

                    if (emailOk || smsOk) {
                        alert(jsonResponse.message || "Message sent successfully.");
                    } else {
                        alert(jsonResponse.message || "Message failed to send.");
                    }

                    location.reload();
                },
                error: function () {
                    alert('Failed to send message.');
                },
                complete: function () {
                    $('.modal').modal('hide');
                }
            });
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

        $(document).on('click', '#editEstimate', function(event) {
            event.preventDefault();
            var id = $(this).data('id');

            if (confirm('Are you sure you want to edit this estimate?')) {
                localStorage.setItem('editEstimateId', id);
                window.open('cashier3/?editestimate=' + encodeURIComponent(id), '_blank');
            }
        });

        $(document).on('click', '#edit_estimate_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/estimate_list_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_edit_modal"
                    },
                    success: function(response) {
                        $('#editEstimateModal').html(response);
                        $('#editEstimateModal').modal('show');
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

        $(document).on("click", "#resendBtn, #AcceptBtn, #processOrderBtn", function () {
            var dataId = $(this).data("id");
            var action = $(this).data("action");
            var selected_prods = getSelectedIDs();

            var confirmMessage = action.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });

            if (confirm("Are you sure you want to " + confirmMessage + "?")) {
                $.ajax({
                    url: 'pages/estimate_list_ajax.php',
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
                url: 'pages/estimate_list_ajax.php',
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
                url: 'pages/estimate_list_ajax.php',
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

        $(document).on('click', '#view_customer_pricing', function(e) {
            e.preventDefault();

            const pricing_id = $(this).data('id');
            const $iframe = $('#pdfFrame');
            let src = $iframe.attr('src');

            const [baseUrl, queryString] = src.split('?');
            const params = new URLSearchParams(queryString || '');

            params.set('pricing_id', pricing_id);

            const newSrc = baseUrl + '?' + params.toString();
            $iframe.attr('src', newSrc);
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
                    return statusText !== 'Delivered';
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