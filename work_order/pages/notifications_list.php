<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require '../includes/dbconn.php';
require '../includes/functions.php';

$page_title = "Notifications";
?>
<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
  <div class="card-body px-0">
    <div class="d-flex justify-content-between align-items-center">
      <div><br>
        <h4 class="font-weight-medium fs-14 mb-0"><?= $page_title ?></h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="/?page=">Home
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
      </div>
      <div class="col-9">
        <div id="selected-tags" class="mb-2"></div>
        <div class="datatables">
          <div class="card">
            <div class="card-body">
                <h4 class="card-title d-flex justify-content-between align-items-center"><?= $page_title ?> List</h4>
              <div class="table-responsive">
                <?php
                $userId = intval($_SESSION['work_order_user_id']);

                $notifications = getUserNotifications($userId, '2');
                ?>

                <table id="notif_table" class="table table-striped table-bordered text-nowrap align-middle">
                    <thead>
                        <tr>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notifications as $row) : ?>
                        <?php
                            $title = getNotifDetails($row['action_type'])['title'];

                            $time = date("h:i A", strtotime($row['created_at']));
                            $date = date("m/d/Y", strtotime($row['created_at']));
                            $msg = htmlspecialchars($row['message']);
                            $url = htmlspecialchars($row['url'] ?? 'javascript:void(0)');
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($title) ?></td>
                            <td><?= $msg ?></td>
                            <td><?= $date ?></td>
                            <td><?= $time ?></td>
                            <td class="text-center">
                            <a href='javascript:void(0)'
                                class='notification-link'
                                data-id='<?= $row['id']?>'
                                data-url='<?= $url ?>'
                                target="_blank" 
                                title="View Notification">
                                <i class="fa fa-eye fs-5 text-primary"></i>
                            </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
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

    var table = $('#notif_table').DataTable({
        pageLength: 100,
        order: []
    });

    $('#notif_table_filter').hide();

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
                var filterValue = $(this).val()?.toString().toLowerCase() || '';
                var rowValue = row.data($(this).data('filter'))?.toString().toLowerCase() || '';

                if (filterValue && filterValue !== '/') {
                    if (!rowValue.includes(filterValue)) {
                        match = false;
                        return false;
                    }
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

    $(document).on('input change', '#text-srh, #toggleActive, .filter-selection', filterTable);
    
    filterTable();
});
</script>