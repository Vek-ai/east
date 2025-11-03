<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$picture_path = "images/product/product.jpg";

$page_title = "Invoices";

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

    <div class="card card-body">
        <div class="row">
            <div class="col-md-12 col-xl-12 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0 gap-3">
                <button type="button" id="downloadExcelBtn" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-file-spreadsheet text-white me-1 fs-5"></i> Excel Download
                </button>
                <button type="button" id="downloadPDFBtn" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-file-text text-white me-1 fs-5"></i> PDF Download
                </button>
                <button type="button" id="PrintBtn" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-printer text-white me-1 fs-5"></i> Print
                </button>
            </div>
        </div>
    </div>

    <div class="card card-body">
        <div class="d-flex">
            <div class="flex-shrink-0" style="width: 250px;">
                <h3 class="card-title align-items-center mb-2">
                    Filter <?= $page_title ?>
                </h3>
                <div class="position-relative w-100 px-0 mr-0 mb-2">
                    <input type="text" class="form-control py-2 ps-5" data-filter-name="Customer Name" id="text-srh" placeholder="Search">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
                <div class="align-items-center">
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

                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" data-filter="payment" data-filter-name="Payment Method" id="select-payment">
                            <option value="">All Payment Methods</option>
                            <option value="pickup">Pay at Pick-up</option>
                            <option value="delivery">Pay at Delivery</option>
                            <option value="cash">Cash</option>
                            <option value="check">Check</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="net30">Charge Net 30</option>
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
                </div>
                <div class="px-3 mb-2"> 
                    <input type="checkbox" id="toggleCompleted"> Pick-up/Deliveries Completed
                </div>
                <div class="d-flex justify-content-end py-2">
                    <button type="button" class="btn btn-outline-primary reset_filters">
                        <i class="fas fa-sync-alt me-1"></i> Reset Filters
                    </button>
                </div>
            </div>
            <div class="flex-grow-1 ms-3">
                <div id="selected-tags" class="mb-2"></div>
                    <div class="datatables">
                        <h4 class="card-title d-flex justify-content-between align-items-center"><?= $page_title ?></h4>
                        <div class="product-details table-responsive">
                            <table id="order_list_tbl" class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <?php if (showCol('invoice_no')): ?>
                                            <th>Invoice #</th>
                                        <?php endif; ?>
                                        <?php if (showCol('customer')): ?>
                                            <th>Customer</th>
                                        <?php endif; ?>
                                        <?php if (showCol('total_price')): ?>
                                            <th style="text-align: right">Total Price</th>
                                        <?php endif; ?>
                                        <?php if (showCol('order_date')): ?>
                                            <th class="text-nowrap" style="text-align: center">Order Date</th>
                                        <?php endif; ?>
                                        <?php if (showCol('deliver_method')): ?>
                                            <th style="text-align: center">Pick-up/Delivery</th>
                                        <?php endif; ?>
                                        <?php if (showCol('payment_method')): ?>
                                            <th style="text-align: center">Payment Method</th>
                                        <?php endif; ?>
                                        <?php if (showCol('scheduled_delivery')): ?>
                                            <th style="text-align: center">Scheduled Pick-up/Delivery Date</th>
                                        <?php endif; ?>
                                        <?php if (showCol('completed_delivery')): ?>
                                            <th style="text-align: center">Pick-up/Delivery Date Completed</th>
                                        <?php endif; ?>
                                        <?php if (showCol('salesperson')): ?>
                                            <th>Salesperson</th>
                                        <?php endif; ?>
                                        <?php if (showCol('action')): ?>
                                            <th>Action</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 

                                    $status_labels = [
                                        'pickup'   => ['label' => 'Pay at Pick-up',      'style' => 'color: #fff; background-color: #0d6efd;'],
                                        'delivery' => ['label' => 'Pay at Delivery',     'style' => 'color: #fff; background-color: #0dcaf0;'],
                                        'cash'     => ['label' => 'Cash',                'style' => 'color: #fff; background-color: #198754;'],
                                        'check'    => ['label' => 'Check',               'style' => 'color: #fff; background-color: #6c757d;'],
                                        'card'     => ['label' => 'Credit/Debit Card',   'style' => 'color: #212529; background-color: #ffc107;'],
                                        'net30'    => ['label' => 'Charge Net 30',       'style' => 'color: #fff; background-color: #dc3545;'],
                                    ];

                                    $query = "SELECT * FROM orders WHERE status != 6 ORDER BY order_date DESC";
                                    $result = mysqli_query($conn, $query);
                                
                                    if ($result && mysqli_num_rows($result) > 0) {
                                        $response = array();
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $status_code = $row['status'];
                                            $customer_id = $row["customerid"];
                                            $customer_details = getCustomerDetails($customer_id);
                                        
                                            $pay_type = $row['pay_type'];
                                            $pay_type = strtolower(trim($pay_type));
                                            $label_info = $status_labels[$pay_type] ?? ['label' => '', 'style' => ''];
                                        ?>
                                        <tr
                                            data-by="<?= htmlspecialchars($row['order_from']) ?>"
                                            data-tax="<?= htmlspecialchars($customer_details['tax_status']) ?>"
                                            data-month="<?= date('m', strtotime($row['order_date'])) ?>"
                                            data-year="<?= date('Y', strtotime($row['order_date'])) ?>"
                                            data-payment="<?= htmlspecialchars($row['pay_type']) ?>"
                                            data-cashier="<?= htmlspecialchars($row['cashier']) ?>"
                                            data-status="<?= htmlspecialchars($status_code) ?>"
                                            data-completed="<?= $row['status'] == '4' ? '1' : '0' ?>"
                                        >
                                            <?php if (showCol('invoice_no')): ?>
                                                <td style="color: #ffffff !important;" data-search="<?= $row["orderid"] ?>">
                                                    <?= $row["orderid"] ?>
                                                </td>
                                            <?php endif; ?>

                                            <?php if (showCol('customer')): ?>
                                                <?php $customer_name = get_customer_name($row["customerid"]); ?>
                                                <td style="color: #ffffff !important;" data-search="<?= htmlspecialchars($customer_name) ?>">
                                                    <?= $customer_name ?>
                                                </td>
                                            <?php endif; ?>

                                            <?php if (showCol('total_price')): ?>
                                                <?php $formatted_price = number_format($row["discounted_price"], 2); ?>
                                                <td style="color: #ffffff !important; text-align: right;" data-search="<?= "$ " .$formatted_price ?>">
                                                    $ <?= $formatted_price ?>
                                                </td>
                                            <?php endif; ?>

                                            <?php if (showCol('order_date')): ?>
                                                <?php
                                                    $order_date_val = '';
                                                    if (!empty($row["order_date"]) && $row["order_date"] !== '0000-00-00 00:00:00') {
                                                        $order_date_val = date("F d, Y", strtotime($row["order_date"]));
                                                    }
                                                ?>
                                                <td class="text-center" style="color: #ffffff !important;"
                                                    data-order="<?= date('Y-m-d', strtotime($row["order_date"])) ?>"
                                                    data-search="<?= htmlspecialchars($order_date_val) ?>">
                                                    <?= $order_date_val ?>
                                                </td>
                                            <?php endif; ?>

                                            <?php if (showCol('deliver_method')): ?>
                                                <td style="color: #ffffff !important;" data-search="<?= htmlspecialchars($row['deliver_method']) ?>">
                                                    <?= ucwords($row['deliver_method']); ?>
                                                </td>
                                            <?php endif; ?>

                                            <?php if (showCol('payment_method')): 
                                                $payment_methods = array_map('trim', explode(',', $row['pay_type']));
                                                $payment_labels = [];

                                                foreach ($payment_methods as $method) {
                                                    $method_key = strtolower($method);
                                                    $label_info = $status_labels[$method_key] 
                                                        ?? ['label' => ucfirst($method), 'style' => 'color: #fff; background-color: #6c757d;'];
                                                    $payment_labels[] = $label_info['label'];
                                                }

                                                $search_payment = implode(' || ', $payment_labels);
                                                ?>
                                                <td class="text-center" style="color: #ffffff !important;" 
                                                    data-search="<?= htmlspecialchars($search_payment) ?>">
                                                    <?php foreach ($payment_methods as $method): 
                                                        $method_key = strtolower($method);
                                                        $label_info = $status_labels[$method_key] 
                                                            ?? ['label' => ucfirst($method), 'style' => 'color: #fff; background-color: #6c757d;'];
                                                    ?>
                                                        <span class="badge me-1" style="<?= $label_info['style'] ?>">
                                                            <?= $label_info['label'] ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </td>
                                            <?php endif; ?>

                                            <?php if (showCol('scheduled_delivery')): ?>
                                                <?php
                                                    $sched_val = '';
                                                    if (!empty($row["scheduled_date"]) && $row["scheduled_date"] !== '0000-00-00 00:00:00') {
                                                        $sched_val = date("F d, Y h:i A", strtotime($row["scheduled_date"]));
                                                    }
                                                ?>
                                                <td style="color: #ffffff !important;" data-search="<?= htmlspecialchars($sched_val) ?>">
                                                    <?= $sched_val ?>
                                                </td>
                                            <?php endif; ?>

                                            <?php if (showCol('completed_delivery')): ?>
                                                <?php
                                                    $delivered_val = '';
                                                    if (!empty($row["delivered_date"]) && $row["delivered_date"] !== '0000-00-00 00:00:00') {
                                                        $delivered_val = date("F d, Y", strtotime($row["delivered_date"]));
                                                    }
                                                ?>
                                                <td style="color: #ffffff !important;" data-search="<?= htmlspecialchars($delivered_val) ?>">
                                                    <?= $delivered_val ?>
                                                </td>
                                            <?php endif; ?>

                                            <?php if (showCol('salesperson')): ?>
                                                <?php $staff_name = ucwords(get_staff_name($row["cashier"])); ?>
                                                <td style="color: #ffffff !important;" data-search="<?= htmlspecialchars($staff_name) ?>">
                                                    <?= $staff_name ?>
                                                </td>
                                            <?php endif; ?>

                                            <?php if (showCol('action')): ?>
                                                <td class="text-center">
                                                    <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="view_order_btn" type="button" data-id="<?php echo $row["orderid"]; ?>" data-bs-toggle="tooltip" title="View Order">
                                                        <i class="text-primary fa fa-eye fs-5"></i>
                                                    </button>

                                                    <a href="print_order_product.php?id=<?= $row["orderid"]; ?>" data-type="<?= $customer_details['customer_pricing'] ?>" class="btn-show-pdf btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["orderid"]; ?>" title="Print/Download">
                                                        <i class="text-success fa fa-print fs-5"></i>
                                                    </a>

                                                    <?php                                                    
                                                    if ($permission === 'edit') {
                                                    ?>
                                                    <a href="javascript:void(0)" type="button" id="email_order_btn" class="me-1 email_order_btn" data-customer="<?= $row["customerid"]; ?>" data-id="<?= $row["orderid"]; ?>" title="Send to Customer">
                                                        <iconify-icon icon="solar:plain-linear" class="fs-5 text-info"></iconify-icon>
                                                    </a>
                                                    <?php                                                    
                                                    }
                                                    ?>

                                                    <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="view_changes_btn" type="button" data-id="<?= $row["orderid"]; ?>" data-bs-toggle="tooltip" title="View Change History">
                                                        <i class="text-info fa fa-clock-rotate-left fs-5"></i>
                                                    </button>

                                                    <?php                                                    
                                                    if ($permission === 'edit') {
                                                    ?>

                                                    <a href="javascript:void(0)" type="button" id="email_order_btn" class="me-1 email_order_btn" data-customer="<?= $row["customerid"]; ?>" data-id="<?= $row["orderid"]; ?>" title="Send to Customer">
                                                        <iconify-icon icon="solar:streets-map-point-outline" class="fs-6 text-info"></iconify-icon>
                                                    </a>

                                                    <button 
                                                        class="btn btn-danger-gradient btn-sm p-0 me-1 view-method-btn" 
                                                        type="button"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deliveryMethodModal"
                                                        data-orderid="<?= $row["orderid"]; ?>"
                                                        data-delivery="<?= $row["deliver_method"]; ?>"
                                                        data-payment="<?= $row["pay_type"]; ?>"
                                                        title="Change Pick-up/Delivery">
                                                        <iconify-icon icon="mdi:package-variant-closed" class="text-warning fs-7"></iconify-icon>
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
                    <input id="send_order_id" type="hidden" name="send_order_id" value="">
                    <input id="send_customer_id" type="hidden" name="send_customer_id" value="">

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

<div class="modal fade" id="deliveryMethodModal" tabindex="-1" aria-labelledby="deliveryMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="updateDeliveryPaymentForm">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="deliveryMethodModalLabel">Change Pick-up/Delivery</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="orderid" id="modal_order_id">

                    <div class="mb-3">
                        <label for="modal_delivery_method" class="form-label">Delivery Method</label>
                        <select class="form-select" id="modal_delivery_method" name="delivery_method" required>
                            <option value="pickup">Pick-up</option>
                            <option value="deliver">Delivery</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="modal_payment_option" class="form-label">Payment Option</label>
                        <select class="form-select" id="modal_payment_option" name="payment_option" required>
                            <option value="pickup">Pay at Pick-Up</option>
                            <option value="delivery">Pay at Delivery</option>
                            <option value="cash">Cash</option>
                            <option value="check">Check</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="net30">Charge Net 30</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

        </div>
    </div>
</div>

<div class="modal fade" id="columnFilterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title">Filter Column</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="max-height:400px; overflow:auto;">
        <input type="text" id="filterSearchInput" class="form-control form-control-sm mb-2" placeholder="Search options...">

        <div id="filterOptions"></div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-sm btn-primary" id="applyFilterBtn">Apply</button>
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
            ordering: false,
            pageLength: 100
        });

        let filterColumnIndex = null;
        let filterUniqueValues = [];
        let columnFilters = {};

        $(document).on('keyup', '#filterSearchInput', function () {
            const query = $(this).val().toLowerCase();
            $('#filterOptions .form-check').each(function () {
                const label = $(this).find('.form-check-label').text().toLowerCase();
                $(this).toggle(label.includes(query));
            });
        });

        $('#order_list_tbl thead th').each(function (index) {
            const th = $(this);
            th.css('cursor', 'pointer');

            th.on('click', function () {
                filterColumnIndex = index;

                const columnData = table
                    .cells(null, index, { search: 'applied' })
                    .nodes()
                    .toArray()
                    .map(td => $(td).attr('data-search') || $(td).text().trim())
                    .filter(v => v !== '');

                const expandedValues = columnData.flatMap(v => v.split('||').map(x => x.trim()));
                filterUniqueValues = [...new Set(expandedValues)].sort();

                const prevSelected = columnFilters[index] || filterUniqueValues;

                let html = `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAllFilters">
                        <label class="form-check-label fw-bold" for="selectAllFilters">Select All</label>
                    </div>
                    <hr class="my-2">
                `;

                filterUniqueValues.forEach((v, i) => {
                    const checked = prevSelected.includes(v) ? 'checked' : '';
                    html += `
                        <div class="form-check">
                            <input class="form-check-input filter-option" type="checkbox"
                                value="${v.replace(/"/g, '&quot;')}" id="filterOpt${i}" ${checked}>
                            <label class="form-check-label" for="filterOpt${i}">${v}</label>
                        </div>
                    `;
                });

                $('#filterOptions').html(html);
                const allChecked = prevSelected.length === filterUniqueValues.length;
                $('#selectAllFilters').prop('checked', allChecked);

                const modal = new bootstrap.Modal(document.getElementById('columnFilterModal'));
                $('#columnFilterModal .modal-title').text('Filter: ' + th.text().trim());
                modal.show();
            });
        });

        $(document).on('change', '#selectAllFilters', function () {
            $('.filter-option').prop('checked', $(this).is(':checked'));
        });

        $('#applyFilterBtn').on('click', function () {
            const checkedVals = $('.filter-option:checked').map(function () {
                return $(this).val();
            }).get();

            columnFilters[filterColumnIndex] = checkedVals;

            const modalEl = document.getElementById('columnFilterModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();

            $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(f => !f._colFilter);

            $.fn.dataTable.ext.search.push(Object.assign(function (settings, data, dataIndex) {
                if (filterColumnIndex === null) return true;

                const cellNode = table.cell(dataIndex, filterColumnIndex).node();
                const rawVal = $(cellNode).attr('data-search') || $(cellNode).text().trim();
                const cellValues = rawVal.split('||').map(v => v.trim());

                const selected = columnFilters[filterColumnIndex];
                if (!selected || selected.length === 0 || selected.length === filterUniqueValues.length)
                    return true;

                return selected.some(v => cellValues.includes(v));
            }, { _colFilter: true }));

            table.draw();
            updateSelectedTags();
        });

        $('#order_list_tbl_filter').hide();

        $(".select2").each(function () {
            $(this).select2({
                width: '100%',
                dropdownParent: $(this).parent()
            });
        });

        $(document).on("click", "#view_changes_btn", function () {
            const orderid = $(this).data("id");

            $.ajax({
                url: 'pages/invoice_ajax.php',
                type: 'POST',
                data: {
                    action: 'fetch_order_history',
                    id: orderid
                },
                success: function (response) {
                    $("#historyModal .modal-content").html(response);

                    if ($.fn.DataTable.isDataTable('#history_tbl')) {
                        $('#history_tbl').DataTable().clear().destroy();
                    }

                    $('#history_tbl').DataTable({
                        order: [],
                        lengthChange: false,
                        pageLength: 100,
                    });

                    $("#historyModal").modal("show");
                },
                error: function (xhr) {
                    alert("Failed to load history.");
                    console.error(xhr.responseText);
                }
            });
        });

        $(document).on('click', '#view_order_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                url: 'pages/invoice_ajax.php',
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

        $(document).on('click', '.btn-show-pdf', function (e) {
            e.preventDefault();

            print_order_id = $(this).data('id');

            pdfUrl = $(this).attr('href');
            document.getElementById('pdfFrame').src = pdfUrl;
            const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
            modal.show();

            const type = $(this).data('type');
            $('.pricing-btn').addClass('d-none');
            $('.pricing-btn[data-id="1"]').removeClass('d-none');

            if (type && type != 1) {
                $(`.pricing-btn[data-id="${type}"]`).removeClass('d-none');
            }
        });

        $(document).on('click', '#view_customer_pricing', function(e) {
            e.preventDefault();

            const pricing_id = $(this).data('id');
            const $iframe = $('#pdfFrame');

            const baseUrl = 'print_order_product.php';
            const params = new URLSearchParams();
            params.set('id', print_order_id);
            params.set('pricing_id', pricing_id);

            const newSrc = baseUrl + '?' + params.toString();
            $iframe.attr('src', newSrc);
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

        function filterTable() {
            var textSearch = $('#text-srh').val().toLowerCase();

            $.fn.dataTable.ext.search = [];

            if (textSearch) {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    return $(table.row(dataIndex).node()).text().toLowerCase().includes(textSearch);
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

                if (!$('#toggleCompleted').is(':checked')) {
                    if (String(row.data('completed')) === '1') {
                        return false;
                    }
                }

                return match;
            });

            table.draw();
            updateSelectedTags();
        }

        $(document).on('change', '.filter-selection', filterTable);

        $(document).on('change', '#toggleCompleted', filterTable);

        $(document).on('input', '#text-srh', filterTable);

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

            Object.keys(columnFilters).forEach(function(index) {
                const selected = columnFilters[index];
                if (selected && selected.length && selected.length < filterUniqueValues.length) {
                    const colName = $('#order_list_tbl thead th').eq(index).text().trim();
                    const text = selected.join(', ');
                    displayDiv.append(`
                        <div class="d-inline-block p-1 m-1 border rounded bg-light">
                            <span class="text-dark">${colName}: ${text}</span>
                            <button type="button" 
                                class="btn-close btn-sm ms-1 remove-col-filter" 
                                style="width: 0.75rem; height: 0.75rem;" 
                                aria-label="Close" 
                                data-col="${index}">
                            </button>
                        </div>
                    `);
                }
            });

            $('.remove-tag').on('click', function() {
                $($(this).data('select')).val('').trigger('change');
                $(this).parent().remove();
            });

            $('.remove-col-filter').on('click', function() {
                const colIndex = $(this).data('col');
                delete columnFilters[colIndex];
                $(this).parent().remove();

                table.columns().every(function(i) {
                    const col = this;
                    const selectedVals = columnFilters[i];

                    if (selectedVals && selectedVals.length) {
                        const regex = selectedVals
                            .map(val => $.fn.dataTable.util.escapeRegex(
                                $('<div>').html(val).text().trim()
                            ))
                            .join('|');

                        col.search(regex, true, false);
                    } else {
                        col.search('');
                    }
                });

                table.draw();
            });
        }

        $(document).on('click', '.reset_filters', function () {
            $('.filter-selection').each(function () {
                $(this).val(null).trigger('change.select2');
            });

            $('#text-srh').val('');

            columnFilters = {};
            table.columns().search('');
            table.search('').draw();

            $('#filterOptions').empty();
            $('#columnFilterModal .modal-title').text('Filter');

            filterTable();
        });

        $(document).on('click', '#downloadExcelBtn', function () {
            window.open('pages/invoice_ajax.php?action=download_excel', '_blank');
        });

        $(document).on('click', '#downloadPDFBtn', function () {
            window.open('pages/invoice_ajax.php?action=download_pdf', '_blank');
        });

        $(document).on('click', '#PrintBtn', function () {
            window.open('pages/invoice_ajax.php?action=print_result', '_blank');
        });

        $(document).on('click', '.email_order_btn', function () {
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
            formData.append('action', 'send_order');

            $.ajax({
                url: 'pages/invoice_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    $form.find('button').prop('disabled', true).text('Sending...');
                },
                success: function (response) {
                    console.log(response);
                    let res = {};
                    try {
                        res = JSON.parse(response);
                    } catch (e) {
                        alert('Invalid response from server.');
                        return;
                    }

                    let msg = '';
                    if (res.results) {
                        if (res.results.email) {
                            msg += 'Email: ' + res.results.email.message + '\n';
                        }
                        if (res.results.sms) {
                            msg += 'SMS: ' + res.results.sms.message + '\n';
                        }
                    } else {
                        msg = res.message || 'Operation complete.';
                    }

                    alert(msg);
                },
                error: function () {
                    alert('Failed to send message.');
                },
                complete: function () {
                    $('.modal').modal('hide');
                }
            });
        });

        $(document).on('click', '.view-method-btn', function () {
            const orderId = $(this).data('orderid');
            const delivery = $(this).data('delivery');
            const payment = $(this).data('payment');

            $('#modal_order_id').val(orderId);
            $('#modal_delivery_method').val(delivery);
            $('#modal_payment_option').val(payment);
        });

        $(document).on('submit', '#updateDeliveryPaymentForm', function (e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);
            formData.append('action', 'update_delivery_payment');

            $.ajax({
                type: 'POST',
                url: 'pages/invoice_ajax.php',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    $('.modal').modal('hide');

                    if (response.success === true || response.status === 'success') {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Delivery method and Payment method successfully updated!");
                        $('#responseHeaderContainer')
                            .removeClass("bg-danger")
                            .addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    } else {
                        $('#responseHeader').text("Failed");
                        $('#responseMsg').text(response.message || "Update failed.");
                        $('#responseHeaderContainer')
                            .removeClass("bg-success")
                            .addClass("bg-danger");
                        $('#response-modal').modal("show");
                    }
                },
                error: function () {
                    $('#responseHeader').text("Error");
                    $('#responseMsg').text("Something went wrong. Please try again.");
                    $('#responseHeaderContainer')
                        .removeClass("bg-success")
                        .addClass("bg-danger");
                    $('#response-modal').modal("show");
                }
            });
        });

        filterTable();
    });
</script>