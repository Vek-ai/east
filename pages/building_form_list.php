<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Building Form List";

$permission = $_SESSION['permission'];
?>
<style>
td.notes,  td.last-edit{
    white-space: normal;
    word-wrap: break-word;
}
.emphasize-strike {
    text-decoration: line-through;
    font-weight: bold;
    color: #9a841c;
}
.dataTables_filter input {
    width: 100%;
    height: 50px;
    font-size: 16px;
    padding: 10px;
    border-radius: 5px;
}
.dataTables_filter {  width: 100%;}
#toggleActive {
    margin-bottom: 10px;
}

.inactive-row {
    display: none;
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
      <div>
        
      </div>
    </div>
  </div>
</div>

<?php                                                    
if ($permission === 'edit') {
?>
<div class="card card-body">
    <div class="row">
      <div class="col-md-12 col-xl-12 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0 gap-3">
            <a href="?page=building_order_form" target="_blank" id="addModalBtn" class="btn btn-primary d-flex align-items-center">
                <i class="ti ti-plus text-white me-1 fs-5"></i> Add <?= $page_title ?>
            </a>
      </div>
    </div>
</div>
<?php                                                    
}
?>

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
              <input type="checkbox" id="toggleActive" checked> Show Active Only
          </div>
      </div>
      <div class="col-9">
        <div id="selected-tags" class="mb-2"></div>
        <div class="datatables">
          <div class="card">
            <div class="card-body">
                <h4 class="card-title d-flex justify-content-between align-items-center"><?= $page_title ?> List</h4>
              <div class="table-responsive">
                <table id="display_building_form" class="table table-striped table-bordered text-nowrap align-middle">
                    <thead>
                        <tr>
                        <th>Customer</th>
                        <th>Date Submitted</th>
                        <th>Status</th>
                        <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $id = 1;
                    $query_building_form = "SELECT * FROM building_form ORDER BY created_at DESC";
                    $result_building_form = mysqli_query($conn, $query_building_form);            
                    while ($row_building_form = mysqli_fetch_array($result_building_form)) {
                        $id            = $row_building_form['id'];
                        $customer_name = $row_building_form['customer_name'];
                        $created_at    = !empty($row_building_form['created_at']) 
                                            ? date("M d, Y", strtotime($row_building_form['created_at'])) 
                                            : '';
                        $db_status     = $row_building_form['status'] ?? 0;

                        if ($db_status == 0) {
                            $status_html = '<span class="badge bg-primary">New</span>';
                        } elseif ($db_status == 1) {
                            $status_html = '<span class="badge bg-warning text-dark">Quoting</span>';
                        } else {
                            $status_html = '<span class="badge bg-secondary">Unknown</span>';
                        }
                    ?>
                        <tr id="product-row-<?= $id ?>">
                            <td><?= ucwords($customer_name) ?></td>
                            <td><?= $created_at ?></td>
                            <td><?= $status_html ?></td>
                            <td class="text-center d-flex justify-content-center" id="action-button-<?= $id ?>">
                              <a href="?page=building_order_form&id=<?= $id ?>" target="_blank" 
                                  class="d-flex align-items-center justify-content-center text-decoration-none" title="View">
                                  <i class="ti ti-eye fs-7"></i>
                              </a>
                              <a href="javascript:void(0);" 
                                  class="d-flex align-items-center justify-content-center text-decoration-none quote-request-btn"
                                  data-id="<?= $id ?>" title="Quote Request">
                                  <i class="ti ti-file-text text-warning fs-7"></i>
                              </a>
                            </td>
                        </tr>
                    <?php
                        $id++;
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

<script>
  $(document).ready(function() {
    document.title = "<?= $page_title ?>";

    var table = $('#display_building_form').DataTable({
        pageLength: 100,
        order: []
    });

    $('#display_building_form_filter').hide();

    $(document).on('click', '.quote-request-btn', function () {
        let id = $(this).data('id');

        if (!confirm("Are you sure you want to Quote this Request?")) {
            return;
        }

        $.ajax({
            url: 'pages/building_form_list_ajax.php',
            type: 'POST',
            data: { 
              action: 'quote_request',
              id: id 
            },
            dataType: 'json',
            success: function (response) {
                console.log(response);
                if (response.success) {
                    let row = $('#product-row-' + response.row_no + ' td:nth-child(3)');
                    row.html('<span class="badge bg-warning text-dark">Quoting</span>');
                } else {
                    alert(response.message || 'Error updating status');
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.log('AJAX error:', textStatus, errorThrown);
                console.log('Response Text:', xhr.responseText);
                alert('Request failed!');
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

    $(document).on('input change', '#text-srh, #toggleActive, .filter-selection', filterTable);
    
    filterTable();
});
</script>