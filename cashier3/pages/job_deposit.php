<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require '../includes/dbconn.php';
require '../includes/functions.php';

$page_title = "Job Deposit";

?>
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
      <div class="col-md-12 col-xl-12 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0 gap-3">
          <button type="button" id="depositModalBtn" class="btn btn-primary d-flex align-items-center" data-id="">
              <i class="ti ti-plus text-white me-1 fs-5"></i> Add <?= $page_title ?>
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
                                    <th>Status</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total_deposit = 0;
                                $query = "SELECT * FROM job_deposits ORDER BY created_at DESC";
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

                                    $status_map = [
                                        0 => "<span class='badge bg-warning text-dark'>Pending</span>",
                                        1 => "<span class='badge bg-info'>Available</span>",
                                        2 => "<span class='badge bg-secondary'>Used</span>"
                                    ];

                                    $status_badge = $status_map[intval($row['deposit_status'])] ?? "<span class='badge bg-dark'>Unknown</span>";

                                    $total_deposit += $row['deposit_amount'];
                                    echo "<tr>
                                            <td>$customer_name</td>
                                            <td>$job_name</td>
                                            <td>$cashier</td>
                                            <td>$date</td>
                                            <td>$status_badge</td>
                                            <td class='text-end'> $" .number_format($deposit_amount,2) ."</td>
                                            <td class='text-center'>
                                                <a type='button' class='btnview' title='View Deposit Details' data-id='$deposit_id'>
                                                    <iconify-icon icon='mdi:eye' class='fs-7 text-primary'></iconify-icon>
                                                </a>
                                            </td>
                                        </tr>";
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-end">Total</th>
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
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Deposit Details</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewBody">
                <div class="text-center text-muted">Loading...</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="depositModal" tabindex="-1" aria-labelledby="depositModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h5 class="modal-title">Add Job Deposit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="depositForm" class="form-horizontal">
                <div class="modal-body" id="depositBody">
                    
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" style="border-radius: 10%;">Save</button>
                </div>
            </form>
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
            url: 'pages/job_deposit_ajax.php',
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

    $(document).on('click', '#depositModalBtn', function(event) {
        event.preventDefault();

         $.ajax({
            url: 'pages/job_deposit_ajax.php',
            type: 'POST',
            data: {
                action: 'fetch_deposit_modal'
            },
            success: function(response) {
                $('#depositBody').html(response);

                $(".select2").each(function () {
                    const $modalParent = $(this).closest('.modal');
                    $(this).select2({
                        dropdownParent: $modalParent.length ? $modalParent : $(this).parent()
                    });
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Failed');
                console.log(jqXHR.responseText);
            }
        });

        $('#depositModal').modal('show');
    });

    $(document).on('change', '#deposited_by', function () {
        const selectedCustomer = $(this).val();

        $('.job_details').removeClass('d-none');

        $('#job_id option').each(function () {
            const jobCustomer = $(this).data('customer');
            if (!jobCustomer || jobCustomer != selectedCustomer) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });

        $('#job_id').val('');
    });


    $(document).on('change', '#deposit_type', function () {
        const type = $(this).val();

        $('#deposit_details_group').removeClass('d-none');

        if (type === 'check') {
            $('#check_no_group').removeClass('d-none');
            $('#check_no').attr('required', true);
        } else {
            $('#check_no_group').addClass('d-none');
            $('#check_no').removeAttr('required').val('');
        }
    });

    $('#depositForm').on('submit', function(event) {
        event.preventDefault(); 
        var formData = new FormData(this);
        formData.append('action', 'deposit_job');
        $.ajax({
            url: 'pages/job_deposit_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('.modal').modal("hide");
                if (response == "success") {
                    $('#responseHeader').text("Success");
                    $('#responseMsg').text("Amount Deposited successfully!");
                    $('#responseHeaderContainer').removeClass("bg-danger");
                    $('#responseHeaderContainer').addClass("bg-success");
                    $('#response-modal').modal("show");

                    $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                    });
                } else {
                    $('#responseHeader').text("Failed");
                    $('#responseMsg').text("Process Failed");
                    console.log("Response: "+response);
                    $('#responseHeaderContainer').removeClass("bg-success");
                    $('#responseHeaderContainer').addClass("bg-danger");
                    $('#response-modal').modal("show");
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
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