<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$picture_path = "images/product/product.jpg";

$page_title = "Work Order List";

$status_labels = [
    1 => ['label' => 'New Order', 'class' => 'badge bg-primary'],
    2 => ['label' => 'Processing', 'class' => 'badge bg-warning'],
    3 => ['label' => 'In Transit', 'class' => 'badge bg-info'],
    4 => ['label' => 'Sent to Customer', 'class' => 'badge bg-success'],
    5 => ['label' => 'On Hold', 'class' => 'badge bg-danger'],
    6 => ['label' => 'Archived/Returned', 'class' => 'badge bg-secondary'],
];

if(isset($_REQUEST['customer_id'])){
    $customer_id = $_REQUEST['customer_id'];
    $customer_details = getCustomerDetails($customer_id);
}

$trim_id = 4;
$panel_id = 3;

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
        width: 100%;
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

    <div class="modal fade" id="viewTimerStatusModal" tabindex="-1" aria-labelledby="viewTimerStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 90%;">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h5 class="modal-title" id="viewTrimQueueModalLabel">Timer Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="update_product" class="form-horizontal">
                    <div  class="modal-body">
                        <div id="viewTimerStatusBody">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="viewPanelsQueueModal" tabindex="-1" aria-labelledby="viewPanelsQueueModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 90%;">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h5 class="modal-title" id="viewTrimQueueModalLabel">Panel Queue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="update_product" class="form-horizontal">
                    <div  class="modal-body">
                        <div id="viewPanelsQueueBody">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewTrimQueueModal" tabindex="-1" aria-labelledby="viewTrimQueueModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 90%;">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h5 class="modal-title" id="viewTrimQueueModalLabel">Trim Queue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="update_product" class="form-horizontal">
                    <div  class="modal-body">
                        <div id="viewTrimQueueBody">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewOrderModal" tabindex="-1" aria-labelledby="viewOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 90%;">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="update_product" class="form-horizontal">
                    <div  class="modal-body">
                        <div id="viewOrderBody">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="customerConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <form id="customerConfirmForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="shipFormModalLabel">Customer Confirmation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center customerConfirmBody mb-0 pb-0">
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
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

    <div class="modal fade" id="pickupFormModal" tabindex="-1" aria-labelledby="pickupFormModalLabel" aria-hidden="true" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pickupFormModalLabel">Pick-Up Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="pickupOrderForm">
                    <input type="hidden" id="pickup_order_id" name="id" value="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="pickup_name" class="form-label">Picked Up By</label>
                            <input type="text" class="form-control" id="pickup_name" name="pickup_name" placeholder="Enter name" required>
                        </div>

                        <div class="mb-3" id="payment_type_group">
                            <label for="type" class="form-label">Deposit Type</label>
                            <select class="form-select" id="payment_type" name="type" required>
                                <option value="">-- Select Type --</option>
                                <option value="cash">Cash</option>
                                <option value="check">Check</option>
                            </select>
                        </div>

                        <div id="payment_details_group" class="d-none">
                            <div class="mb-3">
                                <label for="payment_amount" class="form-label">Payment Amount</label>
                                <input type="number" step="0.0001" class="form-control" id="payment_amount" name="payment_amount" >
                            </div>

                            <div class="mb-3">
                                <label for="reference_no" class="form-label">Reference No</label>
                                <input type="text" class="form-control" id="reference_no" name="reference_no" required>
                            </div>

                            <div class="mb-3 d-none" id="check_no_group">
                                <label for="check_no" class="form-label">Check No</label>
                                <input type="text" class="form-control" id="check_no" name="check_no">
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
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

    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

            </div>
        </div>
    </div>

    <div class="card card-body d-none">
        <div class="text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0 gap-3">
            <button type="button" id="btnTimerStatus" class="btn btn-primary d-flex align-items-center" data-id="">
                <i class="ti ti-clock text-white me-1 fs-5"></i> Timer Status
            </button>
            <button type="button" id="btnViewPanelsQueue" class="btn btn-primary d-flex align-items-center">
                <i class="ti ti-list-details text-white me-1 fs-5"></i> View Panels in Queue
            </button>
            <button type="button" id="btnViewTrimQueue" class="btn btn-primary d-flex align-items-center">
                <i class="ti ti-cut text-white me-1 fs-5"></i> View Trim in Queue
            </button>
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
                    <input type="checkbox" id="toggleActive"> Show Processing Only
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
                            <table id="order_list_tbl" class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <?php if (showCol('orderid')): ?>
                                            <th>Invoice ID #</th>
                                        <?php endif; ?>

                                        <?php if (showCol('customer')): ?>
                                            <th>Customer</th>
                                        <?php endif; ?>

                                        <?php if (showCol('order_date')): ?>
                                            <th>Order Date</th>
                                            <th>Scheduled Date</th>
                                        <?php endif; ?>

                                        <?php if (showCol('status')): ?>
                                            <th>Pickup/Delivery</th>
                                            <th>Metal Panels</th>
                                            <th>Trim</th>
                                            <th>Status</th>
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

                                    $query = "SELECT
                                                o.*,
                                                COUNT(op.id) AS total_count,
                                                CASE WHEN SUM(CASE WHEN op.product_category = $trim_id THEN 1 END) > 0 THEN 1 ELSE 0 END AS has_trim,
                                                CASE WHEN SUM(CASE WHEN op.product_category = $panel_id THEN 1 END) > 0 THEN 1 ELSE 0 END AS has_panel
                                            FROM
                                                orders o
                                            LEFT JOIN order_product op ON
                                                o.orderid = op.orderid AND(
                                                    op.product_category = $trim_id OR op.product_category = $panel_id
                                                )
                                            WHERE
                                                o.status != 6
                                            GROUP BY
                                                o.orderid
                                            HAVING
                                                total_count > 0
                                            ORDER BY
                                                o.order_date
                                            DESC
                                                ";

                                    if (isset($customer_id) && !empty($customer_id)) {
                                        $query .= " AND customerid = '$customer_id'";
                                    }

                                    $result = mysqli_query($conn, $query);
                                
                                    if ($result && mysqli_num_rows($result) > 0) {
                                        $response = array();
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $orderid = $row['orderid'];
                                            $status_code = $row['status'];
                                            $customer_id = $row["customerid"];
                                            $customer_details = getCustomerDetails($customer_id);
                                        
                                            $status = $status_labels[$status_code];

                                            $show_send_email = getOrderChangeCount($orderid) > 0 ? '1' : '0';
                                        ?>
                                        <tr
                                            data-by="<?= $row['order_from'] ?>"
                                            data-tax="<?= $customer_details['tax_status'] ?>"
                                            data-cashier="<?= $row['cashier'] ?>"
                                            data-month="<?= date('m', strtotime($row['order_date'])) ?>"
                                            data-year="<?= date('Y', strtotime($row['order_date'])) ?>"
                                            data-status="<?= $status_code ?>"
                                            data-show-email="<?= $show_send_email ?>"
                                        >
                                            <?php if (showCol('orderid')): ?>
                                                <td>
                                                    <?= $row["orderid"]; ?>
                                                </td>
                                            <?php endif; ?>

                                            <?php if (showCol('customer')): ?>
                                                <td>
                                                    <?php echo get_customer_name($row["customerid"]) ?>
                                                </td>
                                            <?php endif; ?>

                                            <?php if (showCol('order_date')): ?>
                                                <td
                                                    <?php if (!empty($row["order_date"]) && $row["order_date"] !== '0000-00-00 00:00:00') : ?>
                                                        data-order="<?= date('Y-m-d H:i:s', strtotime($row["order_date"])) ?>"
                                                    <?php endif; ?>
                                                >
                                                    <?php 
                                                        if (!empty($row["order_date"]) && $row["order_date"] !== '0000-00-00 00:00:00') {
                                                            echo date("m/d/Y || h:i A", strtotime($row["order_date"]));
                                                        } else {
                                                            echo '';
                                                        }
                                                    ?>
                                                </td>
                                                <td
                                                    <?php if (!empty($row["scheduled_date"]) && $row["scheduled_date"] !== '0000-00-00 00:00:00') : ?>
                                                        data-order="<?= date('Y-m-d H:i:s', strtotime($row["scheduled_date"])) ?>"
                                                    <?php endif; ?>
                                                >
                                                    <?php 
                                                        if (!empty($row["scheduled_date"]) && $row["scheduled_date"] !== '0000-00-00 00:00:00') {
                                                            echo date("m/d/Y || h:i A", strtotime($row["scheduled_date"]));
                                                        } else {
                                                            echo '';
                                                        }
                                                    ?>
                                                </td>
                                            <?php endif; ?>

                                            <?php if (showCol('status')): ?>
                                                <td>
                                                    <?= ucwords($row["deliver_method"]) ?>
                                                </td>

                                                <td>
                                                    <?= !empty($row["has_panel"]) ? '<i class="fa fa-check text-success fs-8"></i>' : '' ?>
                                                </td>

                                                <td>
                                                    <?= !empty($row["has_trim"]) ? '<i class="fa fa-check text-success fs-8"></i>' : '' ?>
                                                </td>

                                                <td class="text-center">
                                                    <span class="estimate_status <?= $status['class']; ?> fw-bond"><?= $status['label']; ?></span>
                                                </td>
                                            <?php endif; ?>


                                            <?php if (showCol('salesperson')): ?>
                                                <td>
                                                    <?= ucwords(get_staff_name($row["cashier"])) ?>
                                                </td>
                                            <?php endif; ?>

                                            <?php if (showCol('action')): ?>
                                                <td class="text-center">
                                                    <button class="btn btn-sm p-0 me-1" id="view_order_btn" type="button" data-id="<?php echo $row["orderid"]; ?>" data-bs-toggle="tooltip" title="View Order">
                                                        <i class="text-primary fa fa-eye fs-5"></i>
                                                    </button>

                                                    <?php                                                    
                                                    if ($permission === 'edit') {
                                                    ?>
                                                        <button class="btn btn-sm p-0 me-1" id="edit_order_btn" type="button" data-id="<?= $row["orderid"]; ?>" data-bs-toggle="tooltip" title="Edit Order">
                                                            <i class="text-warning fa fa-pencil fs-5"></i>
                                                        </button>
                                                    <?php
                                                    }
                                                    ?>

                                                    <?php                                                    
                                                    if ($permission === 'edit') {
                                                        if ($show_send_email == '1'){ ?>
                                                            <a href="javascript:void(0)" type="button" id="email_order_btn" class="me-1 email_order_btn" data-customer="<?= $row["customerid"]; ?>" data-id="<?= $row["orderid"]; ?>" data-bs-toggle="tooltip" title="Send Confirmation">
                                                                <iconify-icon icon="solar:plain-linear" class="fs-6 text-info"></iconify-icon>
                                                            </a>
                                                        <?php 
                                                        }
                                                    }
                                                    ?>

                                                    <a href="print_work_order.php?id=<?= $row["orderid"]; ?>" 
                                                        class="btn-show-pdf btn btn-danger-gradient btn-sm p-0 me-1" 
                                                        type="button" 
                                                        data-id="<?= $row["orderid"]; ?>" 
                                                        data-bs-toggle="tooltip"
                                                        data-has-panel="<?= $row["has_panel"] ?>" 
                                                        data-has-trim="<?= $row["has_trim"] ?>" 
                                                        title="Print/Download">
                                                        <i class="text-success fa fa-print fs-5"></i>
                                                    </a>

                                                    <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="view_changes_btn" type="button" data-id="<?= $row["orderid"]; ?>" data-bs-toggle="tooltip" title="View Change History">
                                                        <i class="text-info fa fa-clock-rotate-left fs-5"></i>
                                                    </button>

                                                    <a href="customer/index.php?page=order&id=<?=$row["orderid"]?>&key=<?=$row["order_key"]?>" target="_blank" class="btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["orderid"]; ?>" data-bs-toggle="tooltip" title="Open Customer View">
                                                        <i class="text-info fa fa-sign-in-alt fs-5"></i>
                                                    </a>

                                                    <a href="javascript:void(0)" type="button" id="customerConfirmBtn" class="me-1" data-id="<?= $row["orderid"]; ?>" data-bs-toggle="tooltip" title="Customer Confirmation">
                                                        <iconify-icon icon="solar:check-read-linear" class="fs-6 text-info"></iconify-icon>
                                                    </a>

                                                    <?php                                                    
                                                    if ($permission === 'edit') {
                                                    ?>
                                                    <button class="btn btn-sm p-0 me-1" id="hold_order_btn" type="button" data-id="<?= $row["orderid"]; ?>" data-bs-toggle="tooltip" title="Place on Hold">
                                                        <iconify-icon icon="solar:shield-cross-outline" class="fs-6 text-primary"></iconify-icon>
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

<div class="modal fade" id="contractorModal" tabindex="-1" aria-hidden="true" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Contractor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <select class="form-select" id="contractor_select">
                    <?php
                    $query = "SELECT * FROM customer WHERE is_contractor = 1";
                    $result = mysqli_query($conn, $query);
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $id = $row['customer_id'];
                            $name = get_customer_name($id);
                            $contact = htmlspecialchars($row['contact_phone']);
                            echo "<option value='{$id}' data-name='{$name}' data-contact='{$contact}'>{$name} ({$contact})</option>";
                        }
                    } else {
                        echo "<option disabled selected>No contractors available</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="confirm_contractor">Confirm</button>
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
            <iframe id="pdfFrame" src="" style="width: 100%;" class="mb-3 border rounded"></iframe>

            <div class="container-fluid border rounded p-3">
                <div class="mt-3 text-center">
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <button type="button" class="btn btn-secondary btn-sm mx-1 my-1 view_print" id="btn_view_panel" data-type="panel">Metal Panel</button>
                        <button type="button" class="btn btn-secondary btn-sm mx-1 my-1 view_print" id="btn_view_trim" data-type="trim">Trim</button>
                    </div>
                </div>

                <div class="mt-3 d-flex justify-content-end flex-wrap">
                    
                    <div class="d-flex gap-2 flex-wrap">
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

<div class="modal fade" id="numericFilterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title">Numeric Filter</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label mb-1">Condition</label>
          <select id="numericCondition" class="form-select form-select-sm">
            <option value="=">Equal to ( = )</option>
            <option value=">=">Greater Than or Equal to ( >= )</option>
            <option value="<=">Less Than or Equal to ( <= )</option>
            <option value="between">Between</option>
          </select>
        </div>
        <div class="mb-2">
          <input type="number" class="form-control form-control-sm" id="numericValue1" placeholder="Enter value">
        </div>
        <div class="mb-2 d-none" id="numericValue2Container">
          <input type="number" class="form-control form-control-sm" id="numericValue2" placeholder="Enter second value">
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-sm btn-primary" id="applyNumericFilter">Apply</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="dateFilterModal" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Date Filter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label for="dateFrom" class="form-label">Date From</label>
          <input type="date" id="dateFrom" class="form-control">
        </div>
        <div class="mb-2">
          <label for="dateTo" class="form-label">Date To</label>
          <input type="date" id="dateTo" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-sm btn-primary" id="applyDateFilter">Apply</button>
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
        var print_order_id = '';

        let filterColumnIndex = null;
        let filterUniqueValues = [];
        let columnFilters = {};
        let numericFilters = {};
        let dateFilters = {};

        var active_order_id = 0;

        var table = $('#order_list_tbl').DataTable({
            "order": [],
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
            formData.append('action', 'send_email');

            $.ajax({
                url: 'pages/order_list_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    $form.find('button').prop('disabled', true).text('Sending...');
                },
                success: function (response) {
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

        $(document).on('click', '#btnTimerStatus', function(event) {
            event.preventDefault(); 
            $.ajax({
                url: 'pages/order_list_ajax.php',
                type: 'POST',
                data: {
                    action: "fetch_timer_status"
                },
                success: function(response) {
                    $('#viewTimerStatusBody').html(response);
                    $('#viewTimerStatusModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#btnViewPanelsQueue', function(event) {
            event.preventDefault(); 
            $.ajax({
                url: 'pages/order_list_ajax.php',
                type: 'POST',
                data: {
                    action: "fetch_panels_queue"
                },
                success: function(response) {
                    $('#viewPanelsQueueBody').html(response);
                    $('#viewPanelsQueueModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#btnViewTrimQueue', function(event) {
            event.preventDefault(); 
            $.ajax({
                url: 'pages/order_list_ajax.php',
                type: 'POST',
                data: {
                    action: "fetch_trim_queue"
                },
                success: function(response) {
                    $('#viewTrimQueueBody').html(response);
                    $('#viewTrimQueueModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        function fetchOrderView() {
            if (!active_order_id) {
                alert("No active order selected.");
                return;
            }

            $.ajax({
                url: 'pages/order_list_ajax.php',
                type: 'POST',
                data: {
                    id: active_order_id,
                    action: "fetch_view_modal"
                },
                success: function (response) {
                    $('#viewOrderBody').html(response);
                    $('#viewOrderModal').modal('show');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        }

        $(document).on('click', '#view_order_btn', function(event) {
            event.preventDefault(); 
            active_order_id = $(this).data('id');
            fetchOrderView();
        });

        $(document).on('click', '#edit_order_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/order_list_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_edit_modal"
                    },
                    success: function(response) {
                        $('#viewOrderBody').html(response);

                        if ($.fn.DataTable.isDataTable('#order_dtls_tbl')) {
                            $('#order_dtls_tbl').DataTable().clear().destroy();
                        }

                        $('#order_dtls_tbl').DataTable({
                            language: {
                                emptyTable: "Order Details not found"
                            },
                            autoWidth: false,
                            responsive: true,
                            lengthChange: false
                        });

                        $(".select2-edit").each(function () {
                            if ($(this).hasClass("select2-hidden-accessible")) {
                                $(this).select2("destroy");
                            }

                            $(this).select2({
                                width: '200px',
                                dropdownParent: $(this).parent()
                            });
                        });

                        $('#viewOrderModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $(document).on("click", "#saveEditOrderBtn", function (e) {
            e.preventDefault();

            if (!confirm("Are you sure you want to save these changes?")) {
                return;
            }

            const formData = {};

            $("#order_dtls_tbl tbody tr").each(function () {
                const row = $(this);
                const id = row.find(".delete-row").data("id");

                formData[id] = {
                    color: row.find(`[name="color[${id}]"]`).val(),
                    grade: row.find(`[name="grade[${id}]"]`).val(),
                    profile: row.find(`[name="profile[${id}]"]`).val(),
                    quantity: row.find(`[name="quantity[${id}]"]`).val(),
                    status: row.find(`[name="status[${id}]"]`).val(),
                    width: row.find(`[name="custom_width[${id}]"]`).val(),
                    length: row.find(`[name="custom_length[${id}]"]`).val(),
                    length2: row.find(`[name="custom_length_inch[${id}]"]`).val(),
                    discounted_price: row.find(`[name="discounted_price[${id}]"]`).val()
                };
            });

            $.ajax({
                url: 'pages/order_list_ajax.php',
                type: 'POST',
                data: {
                    action: 'save_edited_order',
                    order_data: JSON.stringify(formData)
                },
                success: function (response) {
                    if(response.trim() == 'success'){
                        alert("Successfully saved!");
                        location.reload();
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Save Error:", {
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                }
            });
        });

        $(document).on('click', '#hold_order_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/order_list_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_hold_modal"
                    },
                    success: function(response) {
                        $('#viewOrderBody').html(response);

                        if ($.fn.DataTable.isDataTable('#order_dtls_tbl')) {
                            $('#order_dtls_tbl').DataTable().clear().destroy();
                        }

                        $('#order_dtls_tbl').DataTable({
                            language: {
                                emptyTable: "Order Details not found"
                            },
                            autoWidth: false,
                            responsive: true,
                            lengthChange: false
                        });

                        $('#viewOrderModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $(document).on('click', '#customerConfirmBtn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                url: 'pages/order_list_ajax.php',
                type: 'POST',
                data: {
                    id: id,
                    action: "fetch_confirm_modal"
                },
                success: function(response) {
                    $('.customerConfirmBody').html(response);
                    $('#customerConfirmModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on("submit", "#customerConfirmForm", function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            formData.append("action", "save_customer_confirm");
            $.ajax({
                url: "pages/order_list_ajax.php",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $(".modal").modal("hide");
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Inventory saved successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger").addClass("bg-success");
                        $('#response-modal').modal("show");
                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    } else {
                        $('#responseHeader').text("Failed");
                        $('#responseMsg').text("Failed to save!");
                        $('#responseHeaderContainer').removeClass("bg-success").addClass("bg-danger");
                        $('#response-modal').modal("show");
                    }
                },

                error: function (xhr) {
                    console.log("Error: " + xhr.responseText);
                }
            });
        });

        $(document).on("click", "#view_changes_btn", function () {
            const orderid = $(this).data("id");

            $.ajax({
                url: 'pages/order_list_ajax.php',
                type: 'POST',
                data: {
                    action: 'fetch_order_history',
                    id: orderid
                },
                success: function (response) {
                    $("#historyModal .modal-content").html(response);
                    $("#historyModal").modal("show");
                },
                error: function (xhr) {
                    alert("Failed to load history.");
                    console.error(xhr.responseText);
                }
            });
        });

        $(document).on('click', '.btn-print-delivery', function(e) {
            e.preventDefault();
            pdfUrl = "print_order_delivery.php?id=" +print_order_id;
            document.getElementById('pdfFrame').src = pdfUrl;
            const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
            modal.show();
        });

        $(document).on('click', '.btn-show-pdf', function(e) {
            e.preventDefault();

            const has_panel = $(this).data("has-panel");
            const has_trim  = $(this).data("has-trim");

            if (has_panel) {
                $('#btn_view_panel').removeClass('d-none');
            } else {
                $('#btn_view_panel').addClass('d-none');
            }

            if (has_trim) {
                $('#btn_view_trim').removeClass('d-none');
            } else {
                $('#btn_view_trim').addClass('d-none');
            }

            print_order_id = $(this).data('id');
            pdfUrl = $(this).attr('href');
            const modalEl = document.getElementById('pdfModal');
            const iframe   = document.getElementById('pdfFrame');

            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            modalEl.addEventListener('shown.bs.modal', function() {
                iframe.src = pdfUrl;
            }, { once: true });
        });


        $(document).on('click', '.view_print', function(e) {
            e.preventDefault();
            const type = $(this).data('type');
            const $iframe = $('#pdfFrame');
            const baseUrl = 'print_work_order.php';
            const params = new URLSearchParams();
            params.set('id', print_order_id);
            params.set('type', type);

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

        $(document).on("click", "#pickupOrderBtn", function () {
            dataId = $(this).data("id");
            action = $(this).data("action");

            $("#pickup_order_id").val(dataId);

            selected_prods = getSelectedIDs();
            var unpaid_prods = getSelectedUnpaidIDs();
            var amount_to_pay = getSelectedAmountTotal();

            if (!Array.isArray(selected_prods) || selected_prods.length === 0) {
                alert("Select at least 1 product to pickup.");
                return;
            }

            if (unpaid_prods.length > 0) {
                $('#payment_type_group').removeClass('d-none');
            } else {
                $('#payment_type_group').addClass('d-none');
            }

            $('#payment_amount').val(amount_to_pay);

            $("#pickupFormModal").modal("show");

            $('#payment_amount').off('input').on('input', function () {
                let val = parseFloat($(this).val() || 0);
                if (val > amount_to_pay) {
                    alert(`Payment amount cannot exceed $${amount_to_pay.toFixed(2)}`);
                    $(this).val(amount_to_pay.toFixed(2));
                }
            });
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

        $(document).on('change', '#payment_type', function () {
            const type = $(this).val();

            if (type === 'cash') {
                $('#payment_details_group').removeClass('d-none');
                $('#check_no_group').addClass('d-none');
                $('#check_no').removeAttr('required').val('');
            } else if (type === 'check') {
                $('#payment_details_group').removeClass('d-none');
                $('#check_no_group').removeClass('d-none');
                $('#check_no').attr('required', true);
            } else {
                $('#payment_details_group').addClass('d-none');
                $('#check_no_group').addClass('d-none');
                $('#check_no').removeAttr('required').val('');
            }
        });

        $(document).on('click', '#select_contractor_btn', function () {
            $('#contractorModal').modal('show');
        });

        $(document).on('click', '#confirm_contractor', function () {
            var selected = $('#contractor_select option:selected');
            if (selected.length) {
                var contractorId = selected.val();
                var contractorName = selected.data('name');

                $.ajax({
                    url: 'pages/order_list_ajax.php',
                    type: 'POST',
                    data: {
                        orderid: active_order_id,
                        contractor_id: contractorId,
                        action: 'update_contractor'
                    },
                    success: function (response) {
                        fetchOrderView();
                        $('#contractorModal').modal('hide');
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(
                            'Error: ' + textStatus + ' - ' + errorThrown + '\n\n' +
                            'Response: ' + jqXHR.responseText
                        );
                    }
                });
            }
        });

        $(document).on("submit", "#pickupOrderForm", function (e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);

            formData.append('id', dataId);
            formData.append('method', action);
            formData.append('selected_prods', JSON.stringify(selected_prods));
            formData.append('action', 'pickup_order');

            $.ajax({
                url: 'pages/order_list_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
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
                        } else {
                            console.log("invalid url");
                        }
                    } else {
                        alert("Failed to update: " + (jsonResponse.message || ''));
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

        $('#order_list_tbl thead th').each(function (i) {
            const th = $(this);
            if (!th.find('.filter-trigger').length) {
                th.append(`<span class="filter-trigger ms-2" style="cursor:pointer; font-size:12px; color:#ccc;" title="Filter"><i class="fa fa-filter"></i></span>`);
            }

            th.find('.filter-trigger').on('click', function (e) {
                e.stopPropagation();
                currentColIndex = i;

                const thClasses = th.attr('class') || '';
                const colData = table
                    .cells(null, i, { search: 'applied' })
                    .nodes()
                    .toArray()
                    .map(td => {
                        const $td = $(td);
                        const searchAttr = $td.attr('data-search');
                        if (searchAttr) return searchAttr;

                        const childTexts = $td
                            .children(':visible')
                            .map(function () {
                                return $(this).text().trim();
                            })
                            .get()
                            .filter(Boolean);

                        return childTexts.length ? childTexts.join('||') : $td.text().trim();
                    })
                    .filter(Boolean);

                const values = [...new Set(
                    colData.flatMap(v => v.split('||').map(x => x.trim()))
                )].sort();

                const hasValues = values.length > 0;

                if (thClasses.includes('filter-date')) {
                    $('#dateFilterModal .modal-title').text('Filter: ' + th.text().trim());
                    $('#dateFilterModal').data('col-index', i);
                    new bootstrap.Modal('#dateFilterModal').show();
                    return;
                }

                if (thClasses.includes('filter-numeric')) {
                    $('#numericFilterModal .modal-title').text('Filter: ' + th.text().trim());
                    $('#numericFilterModal').data('col-index', i);
                    new bootstrap.Modal('#numericFilterModal').show();
                    return;
                }

                if (thClasses.includes('filter-select')) {
                    showSelectFilter(values, th, i);
                    return;
                }

                const looksNumeric = hasValues && values.every(v => /^\$?\s?-?\d+(\.\d+)?$/.test(v.replace(/[,$]/g, '')));
                const looksDate = hasValues && values.every(v => !isNaN(parseTextToDate(v)));

                if (looksNumeric) {
                    $('#numericFilterModal .modal-title').text('Filter: ' + th.text().trim());
                    $('#numericFilterModal').data('col-index', i);
                    new bootstrap.Modal('#numericFilterModal').show();
                } else if (looksDate) {
                    $('#dateFilterModal .modal-title').text('Filter: ' + th.text().trim());
                    $('#dateFilterModal').data('col-index', i);
                    new bootstrap.Modal('#dateFilterModal').show();
                } else {
                    showSelectFilter(values, th, i);
                }
            });
        });

        function showSelectFilter(values, th, i) {
            const prevSelected = columnFilters[i] || [];
            const allChecked = prevSelected.length === 0 || prevSelected.length === values.length;

            let html = `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAllFilters" ${allChecked ? 'checked' : ''}>
                    <label class="form-check-label fw-bold" for="selectAllFilters">Select All</label>
                </div><hr class="my-2">
            `;
            values.forEach((v, idx) => {
                const checked = prevSelected.length === 0 || prevSelected.includes(v) ? 'checked' : '';
                html += `
                    <div class="form-check">
                        <input class="form-check-input filter-option" type="checkbox" id="filterOpt${i}_${idx}" value="${v}" ${checked}>
                        <label class="form-check-label" for="filterOpt${i}_${idx}">${v}</label>
                    </div>`;
            });

            $('#filterOptions').html(html);
            $('#columnFilterModal .modal-title').text('Filter: ' + th.text().trim());
            new bootstrap.Modal('#columnFilterModal').show();
        }

        function updateSelectedTags() {
            const displayDiv = $('#selected-tags');
            displayDiv.empty();

            $('.filter-selection').each(function() {
                const $select = $(this);
                let selectedValues = $select.val();

                if (!selectedValues || selectedValues.length === 0) return;

                if (Array.isArray(selectedValues))
                    selectedValues = selectedValues.filter(v => v && v.trim() !== '');
                else if (typeof selectedValues === 'string' && selectedValues.trim() === '')
                    return;

                if (selectedValues.length === 0) return;

                const selectedTexts = $select.find('option:selected').map(function() {
                    return $(this).text().trim();
                }).get();

                const filterName = $select.data('filter-name');
                const joinedText = selectedTexts.join(', ');

                displayDiv.append(`
                    <div class="d-inline-block p-1 m-1 border rounded bg-light">
                        <span class="text-dark">${filterName}: ${joinedText}</span>
                        <button type="button" 
                            class="btn-close btn-sm ms-1 remove-tag" 
                            style="width: 0.75rem; height: 0.75rem;" 
                            aria-label="Close" 
                            data-select="#${$select.attr('id')}">
                        </button>
                    </div>
                `);
            });

            Object.keys(columnFilters).forEach(function(index) {
                const selected = columnFilters[index];
                if (selected && selected.length) {
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

            Object.keys(numericFilters).forEach(function (index) {
                const rule = numericFilters[index];
                if (rule && rule.condition) {
                    const colName = $('#order_list_tbl thead th').eq(index).text().trim();

                    let conditionText = '';
                    switch (rule.condition) {
                        case '=': conditionText = `Equal to:  ${rule.val1}`; break;
                        case '>=': conditionText = `Greater Than or Equal to: ${rule.val1}`; break;
                        case '<=': conditionText = `Less Than or Equal to: ${rule.val1}`; break;
                        case 'between': conditionText = `${rule.val1} – ${rule.val2}`; break;
                        default: conditionText = `${rule.condition} ${rule.val1}`; break;
                    }

                    displayDiv.append(`
                        <div class="d-inline-block p-1 m-1 border rounded bg-light">
                            <span class="text-dark">${colName}: ${conditionText}</span>
                            <button type="button" 
                                class="btn-close btn-sm ms-1 remove-num-filter" 
                                style="width: 0.75rem; height: 0.75rem;" 
                                aria-label="Close" 
                                data-col="${index}">
                            </button>
                        </div>
                    `);
                }
            });

            Object.keys(dateFilters).forEach(function (index) {
                const rule = dateFilters[index];
                if (rule && (rule.from || rule.to)) {
                    const colName = $('#sales_table thead th').eq(index).text().trim();
                    const text = `${rule.from || ''}${rule.from && rule.to ? ' – ' : ''}${rule.to || ''}`;

                    displayDiv.append(`
                        <div class="d-inline-block p-1 m-1 border rounded bg-light">
                            <span class="text-dark">${colName}: ${text}</span>
                            <button type="button" 
                                class="btn-close btn-sm ms-1 remove-date-filter" 
                                style="width: 0.75rem; height: 0.75rem;" 
                                aria-label="Close" 
                                data-col="${index}">
                            </button>
                        </div>
                    `);
                }
            });

            $('.remove-tag').on('click', function() {
                const $target = $($(this).data('select'));
                $target.val(null).trigger('change');
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

            $('.remove-num-filter').off('click').on('click', function () {
                const colIndex = $(this).data('col');
                delete numericFilters[colIndex];
                $(this).parent().remove();
                applyAllFilters();
            });

            $('.remove-date-filter').off('click').on('click', function () {
                const colIndex = $(this).data('col');
                delete dateFilters[colIndex];
                $(this).parent().remove();
                applyAllFilters();
            });

            if (displayDiv.children().length > 0) {
                displayDiv.show();
            } else {
                displayDiv.hide();
            }
        }

        $(document).on('click', '.reset_filters', function () {
            $('.filter-selection').each(function () {
                $(this).val(null).trigger('change.select2');
            });

            $('#text-srh').val('');

            columnFilters = {};
            dateFilters = {};
            numericFilters = {};
            table.columns().search('');
            table.search('').draw();

            $('#filterOptions').empty();
            $('#columnFilterModal .modal-title').text('Filter');

            filterTable();
        });

        $(document).on('keyup', '#filterSearchInput', function () {
            const query = $(this).val().toLowerCase();
            $('#filterOptions .form-check').each(function () {
                const label = $(this).find('label').text().toLowerCase();
                $(this).toggle(label.includes(query));
            });
        });

        $(document).on('change', '#selectAllFilters', function () {
            $('.filter-option').prop('checked', $(this).is(':checked'));
        });

        $('#applyFilterBtn').on('click', function () {
            const checkedVals = $('.filter-option:checked').map((_, el) => $(el).val()).get();
            columnFilters[currentColIndex] = checkedVals;
            bootstrap.Modal.getInstance('#columnFilterModal').hide();
            applyAllFilters();
        });

        $(document).on('change', '#numericCondition', function () {
            $('#numericValue2Container').toggleClass('d-none', $(this).val() !== 'between');
        });

        $('#applyNumericFilter').on('click', function () {
            const colIndex = $('#numericFilterModal').data('col-index');
            const condition = $('#numericCondition').val();
            const val1 = parseFloat($('#numericValue1').val());
            const val2 = parseFloat($('#numericValue2').val());
            if (isNaN(val1)) return alert('Enter a valid number.');

            numericFilters[colIndex] = { condition, val1, val2: isNaN(val2) ? null : val2 };
            bootstrap.Modal.getInstance('#numericFilterModal').hide();
            applyAllFilters();
        });

        $('#applyDateFilter').on('click', function () {
            const colIndex = $('#dateFilterModal').data('col-index');
            const from = $('#dateFrom').val();
            const to = $('#dateTo').val();

            if (!from && !to) {
                alert('Please select at least one date.');
                return;
            }

            dateFilters[colIndex] = { from, to };
            bootstrap.Modal.getInstance('#dateFilterModal').hide();
            applyAllFilters();
        });

        function resetDataTableFilters() {
            $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(f => !f._colFilter);
        }

        function parseTextToDate(value) {
            if (!value) return NaN;
            const raw = value.trim();
            if (!raw) return NaN;

            let parsed = Date.parse(raw);
            if (!isNaN(parsed)) return parsed;

            const match = raw.match(/^(\w+)\s+(\d{1,2}),\s*(\d{4})(?:\s+(\d{1,2}):(\d{2})(?::(\d{2}))?\s*(AM|PM)?)?$/i);
            if (match) {
                let [, month, day, year, hour, minute, second, ampm] = match;
                hour = parseInt(hour || "0", 10);
                minute = parseInt(minute || "0", 10);
                second = parseInt(second || "0", 10);

                if (ampm) {
                    if (ampm.toUpperCase() === "PM" && hour < 12) hour += 12;
                    if (ampm.toUpperCase() === "AM" && hour === 12) hour = 0;
                }

                const months = {
                    january: 0, february: 1, march: 2, april: 3, may: 4, june: 5,
                    july: 6, august: 7, september: 8, october: 9, november: 10, december: 11
                };
                const m = months[month.toLowerCase()];
                if (m === undefined) return NaN;

                return new Date(year, m, parseInt(day, 10), hour, minute, second).getTime();
            }

            const simple = raw.match(/^(\d{4})-(\d{2})-(\d{2})$/) || raw.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
            if (simple) {
                let [ , a, b, c ] = simple;
                let year, month, day;
                if (a.length === 4) {
                    year = parseInt(a, 10);
                    month = parseInt(b, 10) - 1;
                    day = parseInt(c, 10);
                } else {
                    year = parseInt(c, 10);
                    month = parseInt(a, 10) - 1;
                    day = parseInt(b, 10);
                }

                const d = new Date();
                d.setFullYear(year, month, day);
                d.setHours(0, 0, 0, 0);
                return d.getTime();
            }
            return NaN;
        }

        function applyAllFilters() {
            resetDataTableFilters();

            $.fn.dataTable.ext.search.push(Object.assign((settings, data, dataIndex) => {
                for (const [colIndex, selected] of Object.entries(columnFilters)) {
                    const idx = parseInt(colIndex);
                    const node = table.cell(dataIndex, idx).node();
                    const $td = $(node);

                    const searchAttr = $td.attr('data-search');
                    let vals = [];

                    if (searchAttr) {
                        vals = searchAttr.split('||').map(v => v.trim());
                    } else {
                        const childTexts = $td
                            .children(':visible')
                            .map(function () {
                                return $(this).text().trim();
                            })
                            .get()
                            .filter(Boolean);
                        const full = childTexts.length ? childTexts.join('||') : $td.text().trim();
                        vals = full.split('||').map(v => v.trim());
                    }

                    if (selected && selected.length > 0) {
                        const normalizedVals = vals.map(v => v.toLowerCase());
                        const normalizedSelected = selected.map(v => v.toLowerCase());
                        if (!normalizedSelected.some(v => normalizedVals.includes(v))) return false;
                    }
                }

                for (const [colIndex, rule] of Object.entries(numericFilters)) {
                    const idx = parseInt(colIndex);
                    const node = table.cell(dataIndex, idx).node();
                    const raw = $(node).attr('data-search') || $(node).text().trim();
                    const num = parseFloat(raw.replace(/[^\d.-]/g, '')) || 0;

                    switch (rule.condition) {
                        case '=': if (num !== rule.val1) return false; break;
                        case '>': if (num <= rule.val1) return false; break;
                        case '<': if (num >= rule.val1) return false; break;
                        case '>=': if (num < rule.val1) return false; break;
                        case '<=': if (num > rule.val1) return false; break;
                        case 'between':
                            if (rule.val2 === null || num < rule.val1 || num > rule.val2) return false;
                            break;
                    }
                }

                for (const [colIndex, rule] of Object.entries(dateFilters)) {
                    const idx = parseInt(colIndex);
                    const node = table.cell(dataIndex, idx).node();
                    const raw = $(node).attr('data-search') || $(node).text().trim();

                    const cellDate = parseTextToDate(raw);
                    if (isNaN(cellDate)) {
                        if (rule.from || rule.to) return false;
                        continue;
                    }

                    let fromDate = null, toDate = null;

                    if (rule.from) {
                        const [y, m, d] = rule.from.split('-').map(Number);
                        const f = new Date(y, m - 1, d, 0, 0, 0, 0);
                        fromDate = f.getTime();
                    }

                    if (rule.to) {
                        const [y, m, d] = rule.to.split('-').map(Number);
                        const t = new Date(y, m - 1, d, 23, 59, 59, 999);
                        toDate = t.getTime();
                    }

                    if (fromDate && cellDate < fromDate) return false;
                    if (toDate && cellDate > toDate) return false;
                }

                return true;
            }, { _colFilter: true }));

            table.draw();
            updateSelectedTags();
        }
    });
</script>