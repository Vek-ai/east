<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Account Details";

if(isset($_REQUEST['customer_id'])){
  $customer_id = $_REQUEST['customer_id'];
  $customer_details = getCustomerDetails($customer_id);
}
?>

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

    <div class="card card-body">
        <div class="row">
            <div class="col-3">
                <h3 class="card-title align-items-center mb-2">
                    Filter <?= $page_title ?>
                </h3>
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
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" data-filter="type" data-filter-name="Type of Statement" id="select-type">
                            <option value="">All Types of Statement</option>
                            <option value="deposit">Deposit</option>
                            <option value="usage">Usage</option>
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
                    <h5 class="fw-bold">Ledger Data for <?= get_customer_name($customer_id) ?></h5>
                    <div class="product-details table-responsive text-wrap">
                        <?php
                        $query = "
                            SELECT 
                                j.job_id,
                                l.created_at AS date,
                                l.description,
                                j.job_name,
                                l.po_number,
                                l.entry_type,
                                CASE WHEN l.entry_type = 'usage' THEN l.amount ELSE NULL END AS debit,
                                CASE WHEN l.entry_type = 'deposit' THEN l.amount ELSE NULL END AS credit
                            FROM jobs j
                            INNER JOIN job_ledger l ON l.job_id = j.job_id
                            WHERE j.customer_id = '$customer_id'
                            ORDER BY l.created_at ASC
                        ";
                        $result = mysqli_query($conn, $query);
                        $balance = 0;

                        if ($result && mysqli_num_rows($result) > 0){
                        ?>

                        <table id="acct_dtls_tbl" class="table table-hover mb-0 text-wrap">
                            <thead>
                                <tr>
                                    <th style="color: #ffffff !important;">Date</th>
                                    <th style="color: #ffffff !important;">Description</th>
                                    <th style="color: #ffffff !important;">Job</th>
                                    <th style="color: #ffffff !important;">PO Number</th>
                                    <th style="color: #ffffff !important;" class="text-end">Debit</th>
                                    <th style="color: #ffffff !important;" class="text-end">Credit</th>
                                    <th style="color: #ffffff !important;" class="text-end">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)) {
                                    $job_details = getJobDetails($row['job_id']);
                                    $debit = $row['debit'] !== null ? floatval($row['debit']) : 0;
                                    $credit = $row['credit'] !== null ? floatval($row['credit']) : 0;

                                    if ($debit == 0 && $credit == 0) continue;

                                    $balance += ($debit - $credit);
                                ?>
                                    <tr
                                        data-tax="<?= $customer_details['tax_status'] ?>"
                                        data-month="<?= date('m', strtotime($row['date'])) ?>"
                                        data-type="<?= $row['entry_type'] ?>"
                                    >
                                        <td><?= date('Y-m-d', strtotime($row['date'])) ?></td>
                                        <td><?= htmlspecialchars($row['description']) ?></td>
                                        <td><?= htmlspecialchars($row['job_name']) ?></td>
                                        <td>
                                            <a href="javascript:void(0);" 
                                            class="view-order-details" 
                                            data-job="<?= htmlspecialchars($row['job_name']) ?>" 
                                            data-po="<?= htmlspecialchars($row['po_number']) ?>">
                                                <?= htmlspecialchars($row['po_number']) ?>
                                            </a>
                                        </td>
                                        <td class="text-end"><?= $debit > 0 ? '$' .number_format($debit, 2) : '' ?></td>
                                        <td class="text-end"><?= $credit > 0 ? '$' .number_format($credit, 2) : '' ?></td>
                                        <td class="text-end">
                                            <?= ($balance < 0 ? '- $' : '$') . number_format(abs($balance), 2) ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
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

        var table = $('#acct_dtls_tbl').DataTable({
            "order": [[0, "desc"]],
            "pageLength": 100,
            "columnDefs": [
                { targets: '_all', orderable: true }
            ]
        });

        $('#acct_dtls_tbl_filter').hide();

        $(".select2").each(function () {
            $(this).select2({
                width: '100%',
                dropdownParent: $(this).parent()
            });
        });

        $(document).on('click', '.view-order-details', function(event) {
            event.preventDefault(); 
            const jobName = $(this).data('job');
            const poNumber = $(this).data('po');
            const customer_id = '<?= $customer_id ?>';
            $.ajax({
                url: 'pages/statement_of_account_details_ajax.php',
                type: 'POST',
                data: {
                    job_name: jobName,
                    po_number: poNumber,
                    customer_id : customer_id,
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