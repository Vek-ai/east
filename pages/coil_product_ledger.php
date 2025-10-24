<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Coil Product Ledger";

$permission = $_SESSION['permission'];

$coilid = $_REQUEST['coil'] ?? '';
$coil_details = getCoilProductDetails($coilid);

?>

<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"><?= $page_title . (!empty($coilid) ? ": Coil # " . $coil_details['entry_no'] : "") ?></h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="?page=coil_product">Coil Product
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page"><?= $page_title ?></li>
            </ol>
            </nav>
        </div>
        </div>
    </div>
    </div>

    <div class="widget-content searchable-container list">

    <div class="card card-body">
        <?php
        if(empty($coilid)){
        ?>
        <h4 class="fw-bold text-center">Coil Product Not Selected</h4>
        <?php
        }else{
        ?>
        <div class="coil_usage_div row g-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold mb-0">Coil Process</h4>
                    <div class="d-flex gap-2">
                        <div class="form-group mb-0">
                            <label for="date_from_tx" class="form-label">Date From</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                <input type="date" class="form-control" id="date_from_tx">
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label for="date_to_tx" class="form-label">Date To</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                <input type="date" class="form-control" id="date_to_tx">
                            </div>
                        </div>
                    </div>
                </div>
                <div id="coil_tx_result" class="mt-2"></div>
            </div>

            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold mb-0">Coil Defective History</h4>
                    <div class="d-flex gap-2">
                        <div class="form-group mb-0">
                            <label for="date_from_def" class="form-label">Date From</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                <input type="date" class="form-control" id="date_from_def">
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label for="date_to_def" class="form-label">Date To</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                <input type="date" class="form-control" id="date_to_def">
                            </div>
                        </div>
                    </div>
                </div>
                <div id="coil_def_result" class="mt-2"></div>
            </div>
        </div>
        <?php
        }
        ?>
    </div>
</div>

<div class="modal" id="view_coil_details_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h6 class="modal-title">Coil Details</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="coil-details">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="view_coil_usage_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document" style="max-width: 80%;">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h6 class="modal-title">Work Order</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="usage-details">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="view_invoice_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document" style="max-width: 80%;">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h6 class="modal-title">Invoice Details</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="invoice-details">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        document.title = "<?= $page_title ?>";

        var isPrinting = false;
        var coilid = '<?= $coilid ?>';

        function loadCoilTransactions() {
            const from = $('#date_from_tx').val();
            const to = $('#date_to_tx').val();

            $.ajax({
                url: 'pages/coil_product_ledger_ajax.php',
                type: 'POST',
                data: {
                    search_tx: 1,
                    coilid: coilid,
                    date_from: from,
                    date_to: to
                },
                beforeSend: function () {
                    $('#coil_tx_result').html('<div class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin"></i> Loading transactions...</div>');
                },
                success: function (response) {
                    $('#coil_tx_result').html(response);
                },
                error: function (xhr) {
                    $('#coil_tx_result').html('<div class="alert alert-danger">Error loading transactions. (' + xhr.status + ')</div>');
                }
            });
        }

        function loadCoilDefective() {
            const from = $('#date_from_def').val();
            const to = $('#date_to_def').val();

            $.ajax({
                url: 'pages/coil_product_ledger_ajax.php',
                type: 'POST',
                data: {
                    search_defective: 1,
                    coilid: coilid,
                    date_from: from,
                    date_to: to
                },
                beforeSend: function () {
                    $('#coil_def_result').html('<div class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin"></i> Loading defective history...</div>');
                },
                success: function (response) {
                    $('#coil_def_result').html(response);
                },
                error: function (xhr) {
                    $('#coil_def_result').html('<div class="alert alert-danger">Error loading defective history. (' + xhr.status + ')</div>');
                }
            });
        }

        $('#date_from_tx, #date_to_tx').on('change', loadCoilTransactions);
        $('#date_from_def, #date_to_def').on('change', loadCoilDefective);

        loadCoilTransactions();
        loadCoilDefective();

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

        $(document).on('click', '.view_invoice_details', function(event) {
            var orderid = $(this).data('orderid');
            $.ajax({
                url: 'pages/sales_list_ajax.php',
                type: 'POST',
                data: {
                    orderid: orderid,
                    fetch_order_details: "fetch_order_details"
                },
                success: function(response) {
                    console.log(response);
                    $('#invoice-details').html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Failed!');
                    console.log(jqXHR.responseText)
                }
            });
            $('#view_invoice_modal').modal('toggle');
        });
    });
</script>