<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$picture_path = "images/product/product.jpg";

$page_title = "Invoices";

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
            <div class="col-9">
                <div id="selected-tags" class="mb-2"></div>
                    <div class="datatables">
                        <h4 class="card-title d-flex justify-content-between align-items-center"><?= $page_title ?></h4>
                        <div class="product-details table-responsive">
                            <table id="order_list_tbl" class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Customer</th>
                                        <th style="text-align: right">Total Price</th>
                                        <th class="text-nowrap" style="text-align: center">Order Date</th>
                                        <th style="text-align: center">Pick-up/Delivery</th>
                                        <th style="text-align: center">Payment Method</th>
                                        <th style="text-align: center">Scheduled Pick-up/Delivery Date</th>
                                        <th style="text-align: center">Pick-up/Delivery Date Completed</th>
                                        <th style="color: #ffffff !important;">Salesperson</th>
                                        <th>Action</th>
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

                                    $query = "SELECT * FROM orders ORDER BY order_date DESC";
                                    $result = mysqli_query($conn, $query);
                                
                                    if ($result && mysqli_num_rows($result) > 0) {
                                        $response = array();
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $status_code = $row['status'];
                                            $customer_id = $row["customerid"];
                                            $customer_details = getCustomerDetails($customer_id);

                                            $deliver_method = $row['delivery_amt'] > 0 ? 'delivery' : 'pickup';
                                        
                                            $pay_type = $row['pay_type'];
                                            $pay_type = strtolower(trim($pay_type));
                                            $label_info = $status_labels[$pay_type] ?? ['label' => '', 'style' => ''];
                                        ?>
                                        <tr
                                            data-by="<?= $row['order_from'] ?>"
                                            data-tax="<?= $customer_details['tax_status'] ?>"
                                            data-month="<?= date('m', strtotime($row['order_date'])) ?>"
                                            data-year="<?= date('Y', strtotime($row['order_date'])) ?>"
                                            data-payment="<?= $row['pay_type'] ?>"
                                            data-cashier="<?= $row['cashier'] ?>"
                                            data-status="<?= $status_code ?>"
                                            data-completed="<?= $row['status'] == '4' ? '1' : '0' ?>"
                                        >
                                            <td style="color: #ffffff !important;">
                                                <?= $row["orderid"] ?>
                                            </td>
                                            <td style="color: #ffffff !important;">
                                                <?php echo get_customer_name($row["customerid"]) ?>
                                            </td>
                                            <td style="color: #ffffff !important; text-align: right;">
                                                $ <?php echo number_format($row["discounted_price"],2) ?>
                                            </td>
                                            <td class="text-center" style="color: #ffffff !important;"
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
                                            <td style="color: #ffffff !important;">
                                                <?= $deliver_method == 'delivery' ? "Delivery" : "Pick-up" ?>
                                            </td>
                                            <td class="text-center" style="color: #ffffff !important;">
                                                <span class="badge" style="<?= $label_info['style'] ?>">
                                                    <?= $label_info['label'] ?>
                                                </span>
                                            </td>
                                            <td style="color: #ffffff !important;">
                                                
                                            </td>
                                            <td style="color: #ffffff !important;">
                                                
                                            </td>
                                            <td style="color: #ffffff !important;">
                                                <?= ucwords(get_staff_name($row["cashier"])) ?>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="view_order_btn" type="button" data-id="<?php echo $row["orderid"]; ?>" data-bs-toggle="tooltip" title="View Order">
                                                    <i class="text-primary fa fa-eye fs-5"></i>
                                                </button>

                                                <a href="print_order_product.php?id=<?= $row["orderid"]; ?>" class="btn-show-pdf btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["orderid"]; ?>" title="Print/Download">
                                                    <i class="text-success fa fa-print fs-5"></i>
                                                </a>

                                                <a href="javascript:void(0)" type="button" id="email_order_btn" class="me-1 email_order_btn" data-customer="<?= $row["customerid"]; ?>" data-id="<?= $row["orderid"]; ?>" title="Send to Customer">
                                                    <iconify-icon icon="solar:plain-linear" class="fs-5 text-info"></iconify-icon>
                                                </a>

                                                <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="view_changes_btn" type="button" data-id="<?= $row["orderid"]; ?>" data-bs-toggle="tooltip" title="View Change History">
                                                    <i class="text-info fa fa-clock-rotate-left fs-5"></i>
                                                </button>

                                                <a href="javascript:void(0)" type="button" id="email_order_btn" class="me-1 email_order_btn" data-customer="<?= $row["customerid"]; ?>" data-id="<?= $row["orderid"]; ?>" title="Send to Customer">
                                                    <iconify-icon icon="solar:streets-map-point-outline" class="fs-6 text-info"></iconify-icon>
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
        <div class="modal-body" style="overflow: auto;">
            <iframe id="pdfFrame" src="" style="height: 70vh; width: 100%;" class="mb-3 border rounded"></iframe>

            <div class="container-fluid border rounded p-3">
                <h6 class="mb-3">Download Outputs</h6>
                <div class="row">
                    <div class="col-12 col-md-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="officeCopy">
                            <label class="form-check-label text-white" for="officeCopy">Cover Sheet (Office Copy)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="customerCopy">
                            <label class="form-check-label text-white" for="customerCopy">Cover Sheet (Customer Copy)</label>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="ekmCost">
                            <label class="form-check-label text-white" for="ekmCost">EKM Cost Breakdown</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="noPrice">
                            <label class="form-check-label text-white" for="noPrice">Cover Sheet w/o Price</label>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="jobCsv">
                            <label class="form-check-label text-white" for="jobCsv">Job Data CSV</label>
                        </div>
                    </div>
                </div>

                <?php
                $sql = "SELECT id, pricing_name FROM customer_pricing WHERE status = 1 AND hidden = 0 ORDER BY pricing_name ASC";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    echo '<div class="mt-3 text-center">';
                    echo '<div class="d-flex flex-wrap justify-content-center">';
                    while ($row = $result->fetch_assoc()) {
                        echo '<button type="button" class="btn btn-outline-primary btn-sm mx-1 my-1 pricing-btn" id="view_customer_pricing" data-id="' . $row['id'] . '">'
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
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
            "order": [],
            "pageLength": 100
        });

        $('#order_list_tbl_filter').hide();

        $(".select2").each(function () {
            $(this).select2({
                width: '100%',
                dropdownParent: $(this).parent()
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

        $(document).on('click', '.btn-show-pdf', function(e) {
            e.preventDefault();
            pdfUrl = $(this).attr('href');
            document.getElementById('pdfFrame').src = pdfUrl;
            const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
            modal.show();
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

        filterTable();
    });
</script>