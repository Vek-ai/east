<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Payment Approval";
$permission = $_SESSION['permission'];
?>
<style>
.dataTables_filter input {
    width: 100%;
    height: 50px;
    font-size: 16px;
    padding: 10px;
    border-radius: 5px;
}
.dataTables_filter {  width: 100%;}

.carousel.no-animation .carousel-item {
    transition: none !important;
}
</style>
<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
  <div class="card-body px-0">
    <div class="d-flex justify-content-between align-items-center">
      <div><br>
        <h4 class="font-weight-medium fs-14 mb-0"><?= $page_title ?></h4>
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
                <input type="text" class="form-control py-2 ps-5 " id="text-srh" placeholder="Search">
                <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
            </div>
            <div class="px-3 mb-2">
                <input type="checkbox" id="showApproved"> Show Approved
            </div>
            <div class="px-3 mb-2">
                <input type="checkbox" id="showRejected"> Show Rejected
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
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title d-flex justify-content-between align-items-center"><?= $page_title ?> List</h4>
                    <div class="table-responsive">
                        <table id="payments_tbl" class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Payment Method</th>
                                    <th>Cashier</th>
                                    <th>Reference No</th>
                                    <th>Description</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total_paid = 0;
                                $query = "SELECT * FROM job_payment ORDER BY created_at DESC";
                                $result = mysqli_query($conn, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $cashier = get_staff_name($row['cashier']);
                                    $payment_method = ucfirst($row['payment_method']);
                                    $amount = number_format($row['amount'], 2);
                                    $reference_no = $row['reference_no'];
                                    $description = $row['description'];
                                    $date = date('F d,Y', strtotime($row['created_at']));
                                    $payment_id = $row['payment_id'];

                                    $status = intval($row['status']);
                                    if ($status === 1) {
                                        $badge = "<span class='badge bg-success'>Paid</span>";
                                    } elseif ($status === 2) {
                                        $badge = "<span class='badge bg-danger'>Rejected</span>";
                                    } else {
                                        $badge = "<span class='badge bg-warning text-dark'>Pending</span>";
                                    }

                                    $total_paid += $row['amount'];
                                    echo "<tr data-status='$status'>
                                            <td>$date</td>
                                            <td>$payment_method</td>
                                            <td>$cashier</td>
                                            <td>$reference_no</td>
                                            <td>$description</td>
                                            <td class='text-center'>$badge</td>
                                            <td class='text-end'>$$amount</td>
                                            <td class='text-center'>
                                                <a type='button' class='btnViewProof' title='View Proof of Payment' data-payment-id='$payment_id'>
                                                    <iconify-icon icon='mdi:eye' class='fs-7 text-primary'></iconify-icon>
                                                </a>";

                                            if ($permission === 'edit') {
                                                echo "
                                                    <a type='button' class='btnApprove' title='Approve Payment' data-payment-id='$payment_id'>
                                                        <iconify-icon icon='solar:like-bold' class='fs-7 text-success'></iconify-icon>
                                                    </a>
                                                    <a type='button' class='btnReject' title='Reject Payment' data-payment-id='$payment_id'>
                                                        <iconify-icon icon='solar:dislike-bold' class='fs-7 text-danger'></iconify-icon>
                                                    </a>";
                                            }

                                        echo "</td></tr>";

                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="6" class="text-end">Total</th>
                                    <th class="text-end">$<?= number_format($total_paid, 2) ?></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
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

<script>
  $(document).ready(function() {
    document.title = "<?= $page_title ?>";

    var table = $('#payments_tbl').DataTable({
        pageLength: 100,
        order: []
    });

    $('#payments_tbl_filter').hide();

    $(document).on('click', '.btnViewProof', function () {
        const paymentId = $(this).data('payment-id');

        $('#viewProofBody').html("<div class='text-center text-muted py-5'>Loading...</div>");
        $('#viewProofModal').modal('show');

        $.ajax({
            url: 'pages/statement_payment_approval_ajax.php',
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

    $(document).on('click', '.btnApprove', function () {
        const paymentId = $(this).data('payment-id');

        $.ajax({
            url: 'pages/statement_payment_approval_ajax.php',
            type: 'POST',
            data: {
                action: 'approve_payment',
                payment_id: paymentId
            },
            success: function (response) {
                let res;
                try {
                    res = JSON.parse(response);
                } catch (e) {
                    alert('Failed');
                    console.error("Invalid JSON response:", response);
                    return;
                }

                if (res.status === 'success') {
                    alert(res.message);
                } else {
                    alert('Failed');
                    console.error(res.message || 'Unknown error', response);
                }
            },
            error: function (xhr) {
                alert('Failed');
                console.error("AJAX error:", xhr.responseText);
            }
        });
    });

    $(document).on('click', '.btnReject', function () {
        const paymentId = $(this).data('payment-id');

        $.ajax({
            url: 'pages/statement_payment_approval_ajax.php',
            type: 'POST',
            data: {
                action: 'reject_payment',
                payment_id: paymentId
            },
            success: function (response) {
                let res;
                try {
                    res = JSON.parse(response);
                } catch (e) {
                    alert('Failed');
                    console.error("Invalid JSON response:", response);
                    return;
                }

                if (res.status === 'success') {
                    alert(res.message);
                } else {
                    alert('Failed');
                    console.error(res.message || 'Unknown error', response);
                }

                location.reload();
            },
            error: function (xhr) {
                alert('Failed');
                console.error("AJAX error:", xhr.responseText);
            }
        });
    });



    $(document).on('click', '.preview-click', function () {
        const src = $(this).data('src');
        $('#modalPreviewImage').attr('src', src);
        $('#imagePreviewModal').modal('show');
    });

    function filterTable() {
        var textSearch = $('#text-srh').val().toLowerCase();
        var showApproved = $('#showApproved').is(':checked');
        var showRejected = $('#showRejected').is(':checked');

        $.fn.dataTable.ext.search = [];

        if (textSearch) {
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                return $(table.row(dataIndex).node()).text().toLowerCase().includes(textSearch);
            });
        }

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            const $row = $(table.row(dataIndex).node());
            const status = $row.data('status');

            if (showApproved && status == 1) return true;
            if (showRejected && status == 2) return true;

            return status != 1 && status != 2;
        });

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
            var filterName = $(this).data('filter-name'); // Custom attribute for display

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

    $(document).on('input change', '#text-srh, #showApproved, #showRejected, .filter-selection', filterTable);
    
    filterTable();
});
</script>