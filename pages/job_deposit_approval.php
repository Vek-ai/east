<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Job Deposit Approval";

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
                        <table id="job_deposit_tbl" class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Job</th>
                                    <th>Cashier</th>
                                    <th>Date</th>
                                    <th>Reference no.</th>
                                    <th>Check no</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total_deposit = 0;
                                $query = "SELECT * FROM job_deposits WHERE deposit_status = '0' ORDER BY created_at DESC";
                                $result = mysqli_query($conn, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $job_id = $row['job_id'];
                                    $job_details = getJobDetails($job_id);
                                    $customer_id = $job_details['customer_id'];
                                    $customer_name = get_customer_name($customer_id);
                                    $job_name = $job_details['job_name'];
                                    $cashier = get_staff_name($row['deposited_by']);
                                    $deposit_amount = $row['deposit_amount'];
                                    $reference_no = $row['reference_no'];
                                    $date = date('F d,Y', strtotime($row['created_at']));
                                    $deposit_id = $row['deposit_id'];
                                    $check_no = $row['check_no'];

                                    $total_deposit += $row['deposit_amount'];
                                    echo "<tr>
                                            <td>$customer_name</td>
                                            <td>$job_name</td>
                                            <td>$cashier</td>
                                            <td>$date</td>
                                            <td>$reference_no</td>
                                            <td>$check_no</td>
                                            <td class='text-end'> $" .number_format($deposit_amount,2) ."</td>
                                            <td class='text-center'>
                                                <a type='button' class='btnview' title='View Deposit Details' data-id='$deposit_id'>
                                                    <iconify-icon icon='mdi:eye' class='fs-7 text-primary'></iconify-icon>
                                                </a>
                                                <a type='button' class='btnApprove' title='Approve Deposit' data-id='$deposit_id'>
                                                    <iconify-icon icon='solar:like-bold' class='fs-7 text-success'></iconify-icon>
                                                </a>
                                                <a type='button' class='btnReject' title='Reject Deposit' data-id='$deposit_id'>
                                                    <iconify-icon icon='solar:dislike-bold' class='fs-7 text-danger'></iconify-icon>
                                                </a>
                                            </td>
                                        </tr>";
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="6" class="text-end">Total</th>
                                    <th class="text-end">$<?= number_format($total_deposit, 2) ?></th>
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

<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Deposit Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewBody">
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

    var table = $('#job_deposit_tbl').DataTable({
        pageLength: 100,
        order: []
    });

    $('#job_deposit_tbl_filter').hide();

    $(document).on('click', '.btnview', function () {
        const id = $(this).data('id');

        $('#viewBody').html("<div class='text-center text-muted py-5'>Loading...</div>");
        $('#viewModal').modal('show');

        $.ajax({
            url: 'pages/job_deposit_approval_ajax.php',
            type: 'POST',
            data: {
                action: 'view_deposit',
                id: id
            },
            success: function (response) {
                $('#viewBody').html(response);
            },
            error: function (xhr, status, error) {
                $('#viewBody').html("<div class='text-danger text-center'>Failed to load screenshots.</div>");
                console.error("View Proof Error:", xhr.responseText);
            }
        });
    });

    $(document).on('click', '.btnApprove', function () {
        const id = $(this).data('id');

        $.ajax({
            url: 'pages/job_deposit_approval_ajax.php',
            type: 'POST',
            data: {
                action: 'approve_deposit',
                id: id
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

    $(document).on('click', '.btnReject', function () {
        const id = $(this).data('id');

        $.ajax({
            url: 'pages/job_deposit_approval_ajax.php',
            type: 'POST',
            data: {
                action: 'reject_deposit',
                id: id
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