<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Statement of Accounts";

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
                                    COALESCE(o.total_balance_due, 0) AS balance_due,
                                    COALESCE(j.total_credit_available, 0) AS credit_available
                                FROM customer c
                                LEFT JOIN (
                                    SELECT customerid, SUM(credit_amt) AS total_balance_due
                                    FROM orders
                                    GROUP BY customerid
                                ) o ON o.customerid = c.customer_id
                                LEFT JOIN (
                                    SELECT customer_id, SUM(deposit_amount) AS total_credit_available
                                    FROM jobs
                                    GROUP BY customer_id
                                ) j ON j.customer_id = c.customer_id
                                WHERE c.status = 1
                                HAVING balance_due > 0 OR credit_available > 0";
                        $result = $conn->query($sql);
                        ?>

                        <table id="est_list_tbl" class="table table-hover mb-0 text-wrap">
                            <thead>
                                <tr>
                                    <th style="color: #ffffff !important;">Customer</th>
                                    <th style="color: #ffffff !important;">Amount</th>
                                    <th style="color: #ffffff !important;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()) : ?>
                                    <?php if ($row['balance_due'] > 0) : ?>
                                        <tr>
                                            <td><?= get_customer_name($row['customer_id']) ?></td>
                                            <td style="color:rgb(255, 21, 21) !important;">$<?= number_format($row['balance_due'], 2) ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="view_balance_due" type="button" data-customer="<?= $row["customer_id"]; ?>" data-bs-toggle="tooltip" title="View Balance Due">
                                                    <i class="text-primary fa fa-eye fs-5"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                    <?php if ($row['credit_available'] > 0) : ?>
                                        <tr data-id="<?=$row['credit_available'];?>">
                                            <td><?= get_customer_name($row['customer_id']) ?></td>
                                            <td style="color:rgb(0, 255, 136) !important;">$<?= number_format($row['credit_available'], 2) ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="view_credit_avail" type="button" data-customer="<?= $row["customer_id"]; ?>" data-bs-toggle="tooltip" title="View Credit Available">
                                                    <i class="text-primary fa fa-eye fs-5"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
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

        $(document).on('click', '#view_balance_due', function(event) {
            event.preventDefault(); 
            var customer_id = $(this).data('customer');
            $.ajax({
                    url: 'pages/statement_of_account_ajax.php',
                    type: 'POST',
                    data: {
                        customer_id: customer_id,
                        action: "fetch_balance_modal"
                    },
                    success: function(response) {
                        $('#viewModalContent').html(response);

                        $('#balance_due_tbl').DataTable({
                            language: {
                                emptyTable: "No Orders with Balance Due found"
                            },
                            responsive: true,
                            lengthChange: false
                        });

                        $('#viewModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + jqXHR.responseText);
                    }
            });
        });

        $(document).on('click', '#view_credit_avail', function(event) {
            event.preventDefault(); 
            var customer_id = $(this).data('customer');
            $.ajax({
                    url: 'pages/statement_of_account_ajax.php',
                    type: 'POST',
                    data: {
                        customer_id: customer_id,
                        action: "fetch_credit_modal"
                    },
                    success: function(response) {
                        $('#viewModalContent').html(response);

                        $('#credit_avail_tbl').DataTable({
                            language: {
                                emptyTable: "No Jobs found"
                            },
                            responsive: true,
                            lengthChange: false
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