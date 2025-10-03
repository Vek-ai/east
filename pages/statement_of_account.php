<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

$page_title = "Statement of Accounts";
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

    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" style="width:90% !important">
            <div class="modal-content">
            <div class="modal-header align-items-center modal-colored-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="viewModalContent">

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">
                    Close
                </button>
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

    <div class="modal fade" id="viewOrderModal" tabindex="-1" aria-labelledby="viewOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewOrderModalLabel">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="order_modal_content">
                <p class="text-muted">Loading...</p>
            </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-center" role="document">
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
                        <div class="mb-4 p-3 rounded shadow-sm border">
                            <h6 class="mb-3 fw-semibold">Select Period</h6>
                            <div class="row g-3">
                                <div class="col-md-6 form-floating">
                                    <input type="date" class="form-control" id="date_from" placeholder="From">
                                    <label for="date_from">From</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="date" class="form-control" id="date_to" placeholder="To">
                                    <label for="date_to">To</label>
                                </div>
                            </div>
                        </div>

                        <h6 class="mb-3">Download Outputs</h6>
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

    <div class="modal fade" id="sendStatementModal" tabindex="-1" aria-labelledby="sendStatementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendStatementModalLabel">Send Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <h6 class="mb-3">How would you like to send the statement of account to the customer?</h6>

                    <form class="send_statement_form d-flex flex-column flex-md-row align-items-center justify-content-center gap-2" method="post">
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

    <div class="card card-body">
        <div class="row">
            <div class="col-3">
                <h3 class="card-title align-items-center mb-2">
                    Filter <?= $page_title ?>
                </h3>
                <div class="position-relative w-100 px-1 mr-0 mb-2">
                    <input type="text" class="form-control py-2 ps-5" data-filter-name="Customer Name" id="text-srh" placeholder="All Customers">
                    <i class="ti ti-user position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
                <div class="align-items-center">
                    
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
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" data-filter="type" data-filter-name="Type of Statement" id="select-type">
                            <option value="">All Types of Statement</option>
                            <option value="balance">Balance Due</option>
                            <option value="credit">Credit Available</option>
                        </select>
                    </div>
                    
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
                    <div class="product-details table-responsive text-wrap">
                        <?php
                        $sql = "SELECT 
                                    c.customer_id, 
                                    COALESCE(SUM(CASE WHEN l.entry_type = 'credit' THEN l.amount ELSE 0 END), 0) AS total_credit,
                                    COALESCE(SUM(CASE WHEN l.entry_type = 'usage' THEN l.amount ELSE 0 END), 0) AS total_payments,
                                    MIN(CASE WHEN l.entry_type = 'credit' THEN l.created_at ELSE NULL END) AS first_credit_date,
                                    (
                                        SELECT MAX(jp.created_at)
                                        FROM jobs j2
                                        LEFT JOIN job_ledger jl ON jl.job_id = j2.job_id
                                        LEFT JOIN job_payment jp ON jp.ledger_id = jl.ledger_id
                                        WHERE j2.customer_id = c.customer_id AND jp.status = '1'
                                    ) AS last_payment_date
                                FROM customer c
                                LEFT JOIN job_ledger l ON l.customer_id = c.customer_id
                                WHERE c.status = 1
                                GROUP BY c.customer_id
                                HAVING total_credit > 0 OR total_payments > 0";
                        $result = $conn->query($sql);
                        ?>

                        <table id="est_list_tbl" class="table table-hover mb-0 text-wrap">
                            <thead>
                                <tr>
                                    <?php if (showCol('customer', $visibleColumns)) : ?>
                                        <th style="color: #ffffff !important;">Customer</th>
                                    <?php endif; ?>

                                    <?php if (showCol('available_credit', $visibleColumns)) : ?>
                                        <th style="color: #ffffff !important;">Available Credit</th>
                                    <?php endif; ?>

                                    <?php if (showCol('balance_due', $visibleColumns)) : ?>
                                        <th style="color: #ffffff !important;">Balance Due</th>
                                    <?php endif; ?>

                                    <?php if (showCol('last_payment', $visibleColumns)) : ?>
                                        <th style="color: #ffffff !important;">Last Payment</th>
                                    <?php endif; ?>

                                    <?php if (showCol('date_outstanding', $visibleColumns)) : ?>
                                        <th style="color: #ffffff !important;">Date Outstanding</th>
                                    <?php endif; ?>

                                    <?php if (showCol('action', $visibleColumns)) : ?>
                                        <th style="color: #ffffff !important;" class="text-center">Action</th>
                                    <?php endif; ?>
                                </tr>
                                </thead>

                            <tbody>
                                <?php while ($row = $result->fetch_assoc()) : ?>
                                    <?php 
                                        $total_payments = floatval($row['total_payments']); 
                                        $total_credit = floatval($row['total_credit']); 
                                        $customer_id = floatval($row['customer_id']); 
                                        $customer_details = getCustomerDetails($customer_id);
                                        $credit_limit = number_format(floatval($customer_details['charge_net_30'] ?? 0), 2);

                                        $date_outstanding = '';

                                        if (!empty($row['first_credit_date'])) {
                                            $credit_date = new DateTime($row['first_credit_date']);
                                            $today = new DateTime();
                                            $interval = $today->diff($credit_date);
                                            $date_outstanding = $interval->days . ' days';
                                        }

                                        $last_payment = '';
                                        if (!empty($row['last_payment_date'])) {
                                            $last_payment = date("M d, Y", strtotime($row['last_payment_date']));
                                        }
                                    ?>
                                    <tr>
                                        <?php if (showCol('customer', $visibleColumns)) : ?>
                                            <td><?= get_customer_name($row['customer_id']) ?></td>
                                        <?php endif; ?>

                                        <?php if (showCol('available_credit', $visibleColumns)) : ?>
                                            <td style="color:green !important;">
                                                $<?= number_format(getCustomerTotalAvail($customer_id), 2) ?>
                                            </td>
                                        <?php endif; ?>

                                        <?php if (showCol('balance_due', $visibleColumns)) : ?>
                                            <td style="color:rgb(255, 21, 21) !important;">
                                                $<?= number_format(getCustomerCreditTotal($customer_id), 2) ?>
                                            </td>
                                        <?php endif; ?>

                                        <?php if (showCol('last_payment', $visibleColumns)) : ?>
                                            <td><?= $last_payment ?></td>
                                        <?php endif; ?>

                                        <?php if (showCol('date_outstanding', $visibleColumns)) : ?>
                                            <td><?= $date_outstanding ?></td>
                                        <?php endif; ?>

                                        <?php if (showCol('action', $visibleColumns)) : ?>
                                            <td class="text-center">
                                                <a href="?page=statement_of_account_details&customer_id=<?= $row["customer_id"]; ?>" 
                                                    class="btn btn-danger-gradient btn-sm p-0 me-1" 
                                                    data-bs-toggle="tooltip" 
                                                    title="View Details">
                                                        <i class="text-primary fa fa-eye fs-5"></i>
                                                </a>

                                                <a href="print_statement_account.php?id=<?= $customer_id; ?>" 
                                                class="btn-show-pdf btn btn-danger-gradient btn-sm p-0 me-1" 
                                                type="button" 
                                                data-id="<?= $row["orderid"]; ?>" 
                                                data-bs-toggle="tooltip" 
                                                title="Print/Download">
                                                        <i class="text-success fa fa-print fs-5"></i>
                                                </a>

                                                <?php if ($permission === 'edit') : ?>
                                                    <a href="javascript:void(0)" 
                                                    id="email_statement_btn" 
                                                    class="me-1 email_statement_btn" 
                                                    data-customer="<?= $customer_id ?>" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Send Confirmation">
                                                        <iconify-icon icon="solar:plain-linear" class="fs-6 text-info"></iconify-icon>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
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
        document.title = "<?= $page_title ?>";

        var pdfUrl = '';
        var isPrinting = false;

        var table = $('#est_list_tbl').DataTable({
            "order": [[0, "desc"]],
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

        function updatePdfUrlWithDates() {
            let iframe = $('#pdfFrame');
            let currentSrc = iframe.attr('src');
            let from = $('#date_from').val();
            let to = $('#date_to').val();

            let [baseUrl, queryString] = currentSrc.split('?');
            let params = new URLSearchParams(queryString || '');

            if (from) {
                params.set('date_from', from);
            } else {
                params.delete('date_from');
            }

            if (to) {
                params.set('date_to', to);
            } else {
                params.delete('date_to');
            }

            let newUrl = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;

            iframe.attr('src', newUrl);
        }

        $(document).on('click', '.btn-show-pdf', function (e) {
            e.preventDefault();

            pdfUrl = $(this).attr('href');
            $('#pdfFrame').attr('src', pdfUrl);
            $('#date_from, #date_to').val('');
            const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
            modal.show();
        });

        $('#date_from, #date_to').on('change', function () {
            updatePdfUrlWithDates();
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

        $(document).on('click', '.email_statement_btn', function () {
            const customerId = $(this).data('customer');

            $('#send_customer_id').val(customerId);
            $('#sendStatementModal').modal('show');
        });

        $(document).on('submit', '.send_statement_form', function (e) {
            e.preventDefault();

            const $form = $(this);
            const formData = new FormData(this);
            formData.append('action', 'send_email');

            $.ajax({
                url: 'pages/statement_of_account_ajax.php',
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

        $(document).on('click', '#view_details', function(event) {
            event.preventDefault(); 
            var customer_id = $(this).data('customer');
            $.ajax({
                url: 'pages/statement_of_account_ajax.php',
                type: 'POST',
                data: {
                    customer_id: customer_id,
                    action: "fetch_view_modal"
                },
                success: function(response) {
                    $('#viewModalContent').html(response);

                    $('#job_details_tbl').DataTable({
                        "order": [[0, "desc"]],
                        "pageLength": 100,
                        "columnDefs": [
                            { targets: '_all', orderable: true }
                        ]
                    });

                    $('#viewModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + jqXHR.responseText);
                }
            });
        });

        $(document).on('input change', '#text-srh, .filter-selection', function() {
            filterTable();
        });

        filterTable();

        function filterTable() {
            var textSearch = $('#text-srh').val().toLowerCase();
            var isActive = $('#toggleActive').is(':checked');

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

                return match;
            });

            table.draw();
            updateSelectedTags();
        }

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