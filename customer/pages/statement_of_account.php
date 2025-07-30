<?php
$page_title = "Statement of Account";

if(isset($_REQUEST['customer_id'])){
  $customer_id = $_REQUEST['customer_id'];
  $customer_details = getCustomerDetails($customer_id);
}
?>

<style>
    /* Dark themed modal */
    #wireTransferModal .modal-content {
        background-color: #1e1e1e;
        color: #ffffff;
        border: none;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6);
        padding: 16px;
        font-family: 'Segoe UI', sans-serif;
    }

    #wireTransferModal .modal-header {
        border-bottom: none;
        padding-bottom: 0;
    }

    #wireTransferModal .modal-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #ffffff;
    }

    #wireTransferModal .modal-body {
        padding-top: 8px;
        font-size: 0.95rem;
        color: #ffffff;
    }

    #wireTransferModal ul {
        padding-left: 20px;
        margin-bottom: 1rem;
    }

    #wireTransferModal ul li {
        margin-bottom: 6px;
    }

    #wireTransferModal strong {
        color: #ffffff;
    }

    #wireTransferModal p {
        margin-bottom: 10px;
        line-height: 1.6;
        color: #ffffff;
    }

    #wireTransferModal .btn-close {
        filter: invert(1);
    }
</style>

<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"><?= $page_title ?></h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="?page=statement_of_account">Home
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

    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h5 class="modal-title">Add Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <button type="button" class="btn btn-primary me-2" id="btnWire">Wire Transfer</button>
                    <button type="button" class="btn btn-secondary" id="btnCredit">Credit Card</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="wireTransferModal" tabindex="-1" aria-labelledby="wireTransferModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="wireTransferModalLabel">Wire Transfer Instructions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Please use the following bank details to complete your wire transfer:</p>
                    <ul>
                        <li><strong>Bank Name:</strong> ABC Bank</li>
                        <li><strong>Account Name:</strong> East Kentucky Metal</li>
                        <li><strong>Account Number:</strong> 123456789</li>
                    </ul>
                    <p>After making the payment, please upload screenshots as proof of payments.</p>

                    <div class="text-center mb-3">
                        <button type="button" class="btn btn-outline-primary btnUploadProof">
                            Upload Payment Screenshots
                        </button>
                    </div>

                    <p>You may also upload your payment screenshots later in the payment history section on this page.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentHistoryModal" tabindex="-1" aria-labelledby="paymentHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentHistoryModalLabel">Payment History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div id="paymentHistoryBody">
                    
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadScreenshotModal" tabindex="-1" aria-labelledby="uploadScreenshotModalLabel" aria-hidden="true" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Payment Screenshots</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="uploadScreenshotForm" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="payment_id" id="upload_payment_id">
                        <input type="hidden" name="ledger_id" id="upload_ledger_id">

                        <div class="mb-3 text-center">
                            <input type="file" id="payment_screenshots" name="screenshots[]" multiple accept="image/*" class="d-none">
                            <button type="button" class="btn btn-primary mb-3" id="uploadTriggerBtn">
                                <iconify-icon icon="mdi:upload" class="me-1" style="vertical-align: -2px;"></iconify-icon>
                                Upload Screenshots
                            </button>

                            <div class="form-text">You can upload multiple JPG, PNG, or PDF files.</div>
                        </div>

                        <div id="uploadedPreview" class="d-flex flex-wrap gap-3"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewProofModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Screenshots</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewProofBody">
                    <div class="text-center text-muted">Loading...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="width: 80%; max-width: none; height: 80%;">
            <div class="modal-content bg-dark text-white" style="height: 100%;">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="imagePreviewModalLabel">Preview</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center d-flex justify-content-center align-items-center" style="height: calc(100% - 56px);">
                    <img id="modalPreviewImage" src="" class="img-fluid rounded shadow" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>


    <div class="card card-body">
        <div class="row">
            <div class="col-3">
                <div class="modal-header d-flex align-items-center">
                    <h3 class="card-title align-items-center mb-2">
                        Filter <?= $page_title ?>
                    </h3>
                </div>
                <div class="position-relative w-100 px-1 mr-0 mb-2">
                    <input type="text" class="form-control py-2 ps-5" id="text-srh" placeholder="Search">
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
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" data-filter="type" data-filter-name="Type of Record" id="select-type">
                            <option value="">All Types</option>
                            <option value="payment">Payments</option>
                            <option value="receivable">Receivable</option>
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
                    <h5 class="fw-bold"><?= $page_title ?></h5>
                    <div class="product-details table-responsive text-wrap">
                        <?php
                        $query = "
                            SELECT 
                                l.ledger_id,
                                l.job_id,
                                l.created_at AS date,
                                l.description,
                                j.job_name,
                                l.po_number,
                                l.entry_type,
                                l.reference_no AS orderid,
                                l.payment_method,
                                CASE WHEN l.entry_type = 'usage' THEN l.amount ELSE NULL END AS payments,
                                CASE WHEN l.entry_type = 'credit' THEN l.amount ELSE NULL END AS credit
                            FROM job_ledger l
                            LEFT JOIN jobs j ON l.job_id = j.job_id
                            WHERE l.customer_id = '$customer_id'
                            ORDER BY l.created_at DESC;
                        ";
                        $result = mysqli_query($conn, $query);
                        $balance = 0;

                        if ($result && mysqli_num_rows($result) > 0){
                        ?>

                        <table id="acct_dtls_tbl" class="table table-hover mb-0 text-wrap text-center">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAllCredits"></th>
                                    <th style="color: #ffffff !important;">Invoice ID #</th>
                                    <th style="color: #ffffff !important;">Date</th>
                                    <th style="color: #ffffff !important;">Job Name</th>
                                    <th style="color: #ffffff !important;">PO #</th>
                                    <th style="color: #ffffff !important;">Date Outstanding</th>
                                    <th style="color: #ffffff !important;">Invoice/Credit</th>
                                    <th style="color: #ffffff !important;" class="text-end">Credit Amount</th>
                                    <th style="color: #ffffff !important;" class="text-end">Balance Due</th>
                                    <th style="color: #ffffff !important;" class="text-end">Remaining Balance</th>
                                    <th>&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $balance_due = 0;
                                $avail_credit = 0;

                                /* $pay_labels = [
                                    'pickup'   => ['label' => 'Pay at Pick-up'],
                                    'delivery' => ['label' => 'Pay at Delivery'],
                                    'cash'     => ['label' => 'Cash'],
                                    'check'    => ['label' => 'Check'],
                                    'card'     => ['label' => 'Credit/Debit Card'],
                                    'net30'    => ['label' => 'Charge Net 30'],
                                    'job_deposit'    => ['label' => 'Job Deposit'],
                                ]; */

                                $pay_labels = [
                                    'pickup'   => ['label' => 'Invoice'],
                                    'delivery' => ['label' => 'Invoice'],
                                    'cash'     => ['label' => 'Invoice'],
                                    'check'    => ['label' => 'Invoice'],
                                    'card'     => ['label' => 'Invoice'],
                                    'net30'    => ['label' => 'Credit'],
                                    'job_deposit'    => ['label' => 'Credit'],
                                ];

                                while ($row = mysqli_fetch_assoc($result)) {
                                    $is_credit = false;
                                    $ledger_id = $row['ledger_id'];
                                    $job_details = getJobDetails($row['job_id']);
                                    $order_id = $row['orderid'];
                                    $order_details = getOrderDetails($order_id);
                                    $payments = $row['payments'] !== null ? floatval($row['payments']) : 0;
                                    $credit = $row['credit'] !== null ? floatval($row['credit']) : 0;

                                    if ($payments == 0 && $credit == 0) continue;

                                    $pay_type_key = strtolower(trim($row['payment_method']));
                                    $pay_type = $pay_labels[$pay_type_key]['label'] ?? ucfirst($pay_type_key);

                                    $type = 'payment';
                                    $balance = 0;
                                    if ($credit > 0) {
                                        $avail_credit += $payments;
                                    }

                                    if ($credit > 0) {
                                        $is_credit = true;
                                        $total_payments = getTotalJobPayments($ledger_id);
                                        $balance = max($credit - $total_payments, 0);
                                        $type = 'receivable';
                                    }
                                    $balance_due += $balance;
                                    ?>
                                    <tr
                                        data-tax="<?= $customer_details['tax_status'] ?>"
                                        data-month="<?= date('m', strtotime($row['date'])) ?>"
                                        data-type="<?= $type ?>"
                                    >
                                        <?php if($is_credit){
                                            ?>
                                            <td>
                                                <input type="checkbox" class="credit-checkbox" value="<?= $ledger_id ?>">
                                            </td>
                                            <?php
                                        }else{
                                            ?>
                                            <td></td>
                                            <?php
                                        }
                                        ?>
                                        <td class="text-start"><?= htmlspecialchars($order_id) ?></td>
                                        <td><?= date('F j, Y', strtotime($row['date'])) ?></td>
                                        <td><?= htmlspecialchars($row['job_name']) ?></td>
                                        <td>
                                            <a href="javascript:void(0);" 
                                            class="view-order-details" 
                                            data-job="<?= htmlspecialchars($row['job_name']) ?>" 
                                            data-po="<?= htmlspecialchars($row['po_number']) ?>"
                                            data-orderid="<?= htmlspecialchars($order_id) ?>">
                                                <?= htmlspecialchars($row['po_number']) ?>
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            <?php
                                            if ($is_credit && $balance > 0) {
                                                $created_ts = strtotime($row['date']);
                                                $now_ts = time();
                                                $diff_secs = $now_ts - $created_ts;

                                                $days_outstanding = max(0, floor($diff_secs / 86400));
                                                echo $days_outstanding . ' days';
                                            } else {
                                                echo '';
                                            }
                                            ?>
                                        </td>

                                        <td><?= $pay_type ?></td>
                                        <td class="text-end"><?= $payments > 0 ? '$' .number_format($payments, 2) : '' ?></td>
                                        <td class="text-end"><?= $credit > 0 ? '$' .number_format($credit, 2) : '' ?></td>
                                        <td class="text-end">
                                            <?= $balance > 0 ? '$' .number_format($balance, 2) : '' ?>
                                        </td>
                                        <td>
                                            <?php
                                            if($is_credit){
                                                ?>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a id="paymentBtn" title="Payment" role="button" class="py-1" data-id="<?= $ledger_id ?>">
                                                        <iconify-icon icon="solar:hand-money-outline" class="text-success fs-6"></iconify-icon>
                                                    </a>
                                                    <a id="paymentHistoryBtn" title="Payment History" role="button" class="py-1" data-id="<?= $ledger_id ?>">
                                                        <i class="fas fa-history text-primary fs-5"></i>
                                                    </a>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="text-end fw-bold" colspan="6">Total Credit Available</td>
                                    <td class="text-end fw-bold" colspan="1">$<?= number_format(getCustomerTotalAvail($customer_id), 2) ?></td>
                                    <td class="text-end fw-bold" colspan="2">Total Balance Due</td>
                                    <td class="text-end fw-bold" colspan="1">$<?= number_format($balance_due, 2) ?></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>

                        <?php
                        } 
                        ?>
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

        let uploadedImages = [];
        var active_ledger_id = '';

        var table = $('#acct_dtls_tbl').DataTable({
            "order": [],
            "pageLength": 100,
            "columnDefs": [
                { targets: '_all', orderable: true }
            ]
        });

        window.getSelectedLedgerIds = function() {
            return $('.credit-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
        }

        $('#acct_dtls_tbl_filter').hide();

        $(".select2").each(function () {
            $(this).select2({
                width: '100%',
                dropdownParent: $(this).parent()
            });
        });

        $('#selectAllCredits').on('change', function() {
            const checked = $(this).is(':checked');
            $('.credit-checkbox').prop('checked', checked);
        });

        $(document).on('click', '#paymentBtn', function(event) {
            event.preventDefault();
            active_ledger_id = $(this).data('id');
            $('#paymentModal').modal('show');
        });

        $('#btnWire').on('click', function() {
            $('#wireTransferModal').modal('show');
        });

        $(document).on('click', '.btnUploadProof', function () {
            var ledger_id = $(this).data('id');

            if(!ledger_id){
                ledger_id = active_ledger_id;
            }

            $('#upload_ledger_id').val(ledger_id);
            $('#uploadScreenshotModal').modal('show');

            $('#payment_screenshots').val('');
            $('#uploadedPreview').empty();
            uploadedImages = [];
        });

        $(document).on('click', '#paymentHistoryBtn', function(event) {
            event.preventDefault();
            var ledger_id = $(this).data('id') || '';
            $.ajax({
                url: 'pages/statement_of_account_ajax.php',
                type: 'POST',
                data: {
                    ledger_id: ledger_id,
                    action: 'payment_history'
                },
                success: function(response) {
                    $('#paymentHistoryBody').html('');
                    $('#paymentHistoryBody').html(response);

                    if ($.fn.DataTable.isDataTable('#payment_history_tbl')) {
                        $('#payment_history_tbl').DataTable().clear().destroy();
                    }

                    $('#payment_history_tbl').DataTable({
                        order: [],
                        lengthChange: false
                    });

                    $('#paymentHistoryModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#uploadTriggerBtn', function () {
            $('#payment_screenshots').click();
        });

        $(document).on('click', '.btnUploadProofRow', function () {
            const paymentId = $(this).data('payment-id');
            $('#upload_payment_id').val(paymentId);
            $('#uploadScreenshotModal').modal('show');

            $('#payment_screenshots').val('');
            $('#uploadedPreview').empty();
            uploadedImages = [];
        });

        $(document).on('change', '#payment_screenshots', function (e) {
            const files = Array.from(e.target.files);
            uploadedImages = uploadedImages.concat(files);
            renderImagePreviews();
        });

        function renderImagePreviews() {
            $('#uploadedPreview').empty();

            uploadedImages.forEach((file, index) => {
                const reader = new FileReader();

                reader.onload = function (e) {
                    const preview = $(`
                        <div class="position-relative" style="width: 120px; cursor: pointer;">
                            <img src="${e.target.result}" class="img-thumbnail preview-click" data-src="${e.target.result}" style="height: 100px; object-fit: cover;">
                            <button class="btn btn-sm btn-danger position-absolute top-0 end-0" data-index="${index}" style="border-radius: 50%; padding: 2px 6px;">&times;</button>
                        </div>
                    `);
                    $('#uploadedPreview').append(preview);
                };

                reader.readAsDataURL(file);
            });
        }

        $(document).on('click', '#uploadedPreview .btn-danger', function () {
            const index = $(this).data('index');
            uploadedImages.splice(index, 1);
            renderImagePreviews();
        });

        $(document).on('click', '.btnViewProofRow', function () {
            const paymentId = $(this).data('payment-id');

            $('#viewProofBody').html("<div class='text-center text-muted py-5'>Loading...</div>");
            $('#viewProofModal').modal('show');

            $.ajax({
                url: 'pages/statement_of_account_ajax.php',
                type: 'POST',
                data: {
                    action: 'view_payment_proof',
                    payment_id: paymentId
                },
                success: function (response) {
                    $('#viewProofBody').html(response);
                },
                error: function (xhr, status, error) {
                    $('#viewProofBody').html("<div class='text-danger text-center'>Failed to load screenshots.</div>");
                    console.error("View Proof Error:", xhr.responseText);
                }
            });
        });


        $(document).on('submit', '#uploadScreenshotForm', function (e) {
            e.preventDefault();

            const form = $(this)[0];
            const formData = new FormData(form);
            formData.append('action', 'upload_payment_screenshot');

            $.ajax({
                url: 'pages/statement_of_account_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    try {
                        const res = JSON.parse(response);

                        if (res.status === 'success') {
                            alert('Upload successful!');
                            console.log(res);
                            $('#uploadScreenshotModal').modal('hide');
                        } else {
                            alert('Upload failed: ' + (res.message || 'Unknown error'));
                            console.log(res);
                        }
                    } catch (err) {
                        alert('Unexpected response format.');
                        console.error(response);
                    }
                },
                error: function (xhr, status, error) {
                    alert('Upload failed due to server error.');
                    console.error(xhr.responseText);
                }
            });
        });


        $(document).on('click', '.preview-click', function () {
            const src = $(this).data('src');
            $('#modalPreviewImage').attr('src', src);
            $('#imagePreviewModal').modal('show');
        });
        
        $(document).on('click', '.view-order-details', function(event) {
            event.preventDefault(); 
            const jobName = $(this).data('job');
            const poNumber = $(this).data('po');
            const orderid = $(this).data('orderid');
            const customer_id = '<?= $customer_id ?>';
            $.ajax({
                url: 'pages/statement_of_account_ajax.php',
                type: 'POST',
                data: {
                    job_name: jobName,
                    po_number: poNumber,
                    customer_id : customer_id,
                    orderid: orderid,
                    action: "fetch_order_details"
                },
                success: function(response) {
                    console.log(response);
                    $('#viewModalContent').html(response);
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