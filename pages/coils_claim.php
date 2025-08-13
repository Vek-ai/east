<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Coil Claims";
$permission = $_SESSION['permission'];
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

    <div class="widget-content searchable-container list">

    <div class="card card-body">
        <div class="row">
            <div class="col-3">
                <h3 class="card-title align-items-center mb-2">
                    Filter <?= $page_title ?>
                </h3>
                <div class="position-relative w-100 px-1 mr-0 mb-2">
                    <input type="text" class="form-control py-2 ps-5 " id="text-srh" placeholder="Search">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
                <div class="align-items-center">
                    
                </div>

                <div class="d-flex justify-content-end py-2">
                    <button type="button" class="btn btn-outline-primary reset_filters">
                        <i class="fas fa-sync-alt me-1"></i> Reset Filters
                    </button>
                </div>
            </div>
            <div class="col-9">
                <div id="selected-tags" class="mb-2"></div>
                <h4 class="card-title d-flex justify-content-between align-items-center"><?= $page_title ?> List</h4>
                <div class="datatables">
                    <div class="table-responsive">
                        <div id="tbl-work-order" class="product-details table-responsive">
                            <table id="coilClaimsTable" class="table search-table align-middle text-nowrap">
                                <thead class="header-item">
                                    <th>Coil #</th>
                                    <th>Color</th>
                                    <th>Grade</th>
                                    <th>Remaining Feet</th>
                                    <th>Claim Type</th>
                                    <th>Date Submitted</th>
                                    <th>Claim Notes</th>
                                    <th>Action</th>
                                </thead>
                                <tbody>
                                <?php
                                    $query = "
                                        SELECT cd.*, cc.claim_type, cc.notes AS claim_notes, cc.submitted_at
                                        FROM coil_defective cd
                                        LEFT JOIN coil_claim cc ON cd.coil_defective_id = cc.coil_defective_id
                                        WHERE cd.hidden = 0 AND cd.status = 5
                                        ORDER BY cc.submitted_at DESC
                                    ";

                                    $result = mysqli_query($conn, $query);
                                    while ($row = mysqli_fetch_array($result)) {
                                        $remaining_feet = $row['remaining_feet'] ?? 0;
                                        $color_details = getColorDetails($row['color_sold_as']);
                                        $submitted_date = !empty($row['submitted_at']) ? date('F j, Y', strtotime($row['submitted_at'])) : '';
                                ?>
                                    <tr>
                                        <td>
                                            <span class="fw-semibold"><?= htmlspecialchars($row['entry_no']) ?></span>
                                        </td>
                                        <td>
                                            <div class="d-inline-flex align-items-center gap-2">
                                                <span class="rounded-circle d-block" style="background-color:<?= $color_details['color_code'] ?>; width: 30px; height: 30px;"></span>
                                                <?= $color_details['color_name'] ?>
                                            </div>
                                        </td>

                                        <td><?= getGradeName($row['grade']) ?></td>

                                        <td><?= $remaining_feet ?></td>

                                        <td>
                                            <?= htmlspecialchars($row['claim_type']) ?>
                                        </td>

                                        <td><?= $submitted_date ?></td>

                                        <td><?= nl2br(htmlspecialchars($row['claim_notes'])) ?></td>

                                        <td class="text-center">
                                            <a href="#" class="view-notes-btn" data-id="<?= $row['coil_defective_id'] ?>" title="View Claim Notes">
                                                <i class="text-primary fa fa-eye fs-6"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<div class="modal" id="view_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog" role="document" style="width: 90%; max-width: none;">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h4 class="modal-title">Work Order Details</h4>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="view-details"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="view_available_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog" role="document" style="width: 90%; max-width: none;">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h4 class="modal-title">Assigned Coils</h4>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="available-details"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="view_coils_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog" role="document" style="width: 90%; max-width: none;">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h4 class="modal-title">Coils List</h4>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="coil_details"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-transparent border-0" >
      <div class="modal-body text-center p-0">
        <img id="modalImage" style="background-color: #fff;" src="" alt="Full Size" class="img-fluid w-100">
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="coilNotesModal" tabindex="-1" aria-labelledby="coilNotesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="coilNotesModalLabel">Coil Notes</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <?php                                                    
        if ($permission === 'edit') {
        ?>
        <div class="mb-3">
          <label for="newNote" class="form-label">Add a Note:</label>
          <textarea id="newNote" class="form-control" rows="3" placeholder="Write your note here..."></textarea>
          <div class="text-end mt-2">
            <button class="btn btn-sm btn-primary" id="saveNoteBtn">Save Note</button>
        </div>
        <?php                                                    
        }
        ?>

        </div>

        <table class="table table-bordered table-striped mt-3" id="notesTable">
          <thead>
            <tr>
              <th>Staff</th>
              <th>Note</th>
              <th>Date/Time</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
    $(document).ready(function() {
        document.title = "<?= $page_title ?>";

        var table = $('#coilClaimsTable').DataTable({
            pageLength: 100
        });

        $('#coilClaimsTable_filter').hide();

        $(".select2").each(function() {
            $(this).select2({
                dropdownParent: $(this).parent()
            }).on('select2:select select2:unselect', function() {
                $(this).next('.select2-container').find('.select2-selection__rendered').removeAttr('title');
            });

            $(this).next('.select2-container').find('.select2-selection__rendered').removeAttr('title');
        });

        $(document).on('click', '.preview-image', function () {
            var imgSrc = $(this).attr('src');
            $('#modalImage').attr('src', imgSrc);
            $('#imageModal').modal('show');
        });

        $(document).on('click', '.tag-rework-btn', function (e) {
            e.preventDefault();
            const coil_defective_id = $(this).data('id');
            if (!coil_defective_id) {
                alert('Invalid coil.');
                return;
            }
            if (!confirm('Tag this coil as Rework Done?')) return;

            $.ajax({
                url: 'pages/coils_claim_ajax.php',
                type: 'POST',
                data: {
                    action: 'coil_tag_done',
                    coil_defective_id: coil_defective_id
                },
                success: function (response) {
                    if (response.trim() === 'success') {
                        alert('Coil tagged as Done Rework.');
                        location.reload();
                    } else {
                        alert('An Error occurred');
                        console.log('Error: ' + response);
                    }
                },
                error: function () {
                    alert('An Error occurred');
                    console.log('AJAX request failed.');
                }
            });
        });

        $(document).on('click', '.view-notes-btn', function (e) {
            e.preventDefault();
            selectedDefectiveId = $(this).data('id');
            $('#newNote').val('');
            $('#notesTable tbody').empty();

            $.ajax({
                url: 'pages/coils_defective_ajax.php',
                type: 'POST',
                data: {
                    action: 'fetch_coil_notes',
                    coil_defective_id: selectedDefectiveId
                },
                success: function (response) {
                    const notes = JSON.parse(response);

                    if (Array.isArray(notes) && notes.length > 0) {
                        notes.forEach(note => {
                            $('#notesTable tbody').append(`
                                <tr>
                                    <td>${note.staff_name ?? 'Unknown'}</td>
                                    <td>${note.note_text}</td>
                                    <td>${note.created_at}</td>
                                </tr>
                            `);
                        });
                    } else {
                        $('#notesTable tbody').append(`
                            <tr>
                                <td colspan="3" class="text-center text-muted">No notes added</td>
                            </tr>
                        `);
                    }

                    $('#coilNotesModal').modal('show');
                },
                error: function () {
                    alert('Failed to load notes.');
                }
            });
        });

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
        }

        function updateSearchCategory() {
            let selectedCategory = $('#select-category option:selected').data('category');
            let hasCategory = !!selectedCategory;

            $('.search-category').each(function () {
                let $select2Element = $(this);

                if (!$select2Element.data('all-options')) {
                    $select2Element.data('all-options', $select2Element.find('option').clone(true));
                }

                let allOptions = $select2Element.data('all-options');

                $select2Element.empty();

                if (hasCategory) {
                    allOptions.each(function () {
                        let optionCategory = $(this).data('category');
                        if (String(optionCategory) === String(selectedCategory)) {
                            $select2Element.append($(this).clone(true));
                        }
                    });
                } else {
                    allOptions.each(function () {
                        $select2Element.append($(this).clone(true));
                    });
                }

                $select2Element.select2('destroy');

                let parentContainer = $select2Element.parent();
                $select2Element.select2({
                    width: '100%',
                    dropdownParent: parentContainer
                });
            });

            $('.category_selection').toggleClass('d-none', !hasCategory);
        }

        $(document).on('input change', '#text-srh, #toggleActive, .filter-selection', filterTable);

        $(document).on('click', '.reset_filters', function () {
            $('.filter-selection').each(function () {
                $(this).val(null).trigger('change.select2');
            });

            $('#text-srh').val('');

            filterTable();
        });
        
        filterTable();
    });
</script>