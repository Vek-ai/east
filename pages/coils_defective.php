<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Defective Coils";
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
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-color" data-filter="color" data-filter-name="Product Color">
                            <option value="">All Colors</option>
                            <optgroup label="Product Colors">
                                <?php
                                $query_color = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                $result_color = mysqli_query($conn, $query_color);
                                while ($row_color = mysqli_fetch_array($result_color)) {
                                ?>
                                    <option value="<?= $row_color['color_name'] ?>"><?= $row_color['color_name'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-grade" data-filter="grade" data-filter-name="Product Grade">
                            <option value="">All Grades</option>
                            <optgroup label="Product Grades">
                                <?php
                                $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY `product_grade` ASC";
                                $result_grade = mysqli_query($conn, $query_grade);
                                while ($row_grade = mysqli_fetch_array($result_grade)) {
                                ?>
                                    <option value="<?= $row_grade['product_grade'] ?>"><?= $row_grade['product_grade'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-supplier" data-filter="supplier" data-filter-name="Supplier">
                            <option value="">All Suppliers</option>
                            <optgroup label="Suppliers">
                                <?php
                                $query_grade = "SELECT * FROM supplier WHERE status = '1' ORDER BY `supplier_name` ASC";
                                $result_grade = mysqli_query($conn, $query_grade);
                                while ($row_grade = mysqli_fetch_array($result_grade)) {
                                ?>
                                    <option value="<?= $row_grade['supplier_name'] ?>"><?= $row_grade['supplier_name'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
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
                <h4 class="card-title d-flex justify-content-between align-items-center"><?= $page_title ?> List</h4>
                <div class="datatables">
                    <div class="table-responsive">
                        <div id="tbl-work-order" class="product-details table-responsive">
                            <table id="defectiveCoilsList" class="table search-table align-middle ">
                                <thead class="header-item">
                                    <tr>
                                        <th>Coil #</th>
                                        <th>Color</th>
                                        <th>Grade</th>
                                        <th>Remaining Feet</th>
                                        <th>Status</th>
                                        <th>Date Tagged Defective</th>
                                        <th>Supplier</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
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
        <div class="mb-3">
          <label for="newNote" class="form-label">Add a Note:</label>
          <textarea id="newNote" class="form-control" rows="3" placeholder="Write your note here..."></textarea>
          <div class="text-end mt-2">
            <button class="btn btn-sm btn-primary" id="saveNoteBtn">Save Note</button>
        </div>
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

        var table = $('#defectiveCoilsList').DataTable({
            ajax: {
                url: 'pages/coils_defective_ajax.php',
                type: 'POST',
                data: { action: 'fetch_coil_defective' },
                dataSrc: function (json) {
                    return json.data.map(function (item) {
                        return item.row;
                    });
                }
            },
            pageLength: 100,
            createdRow: function (row, data, dataIndex) {
                var item = table.ajax.json().data[dataIndex];
                $(row).attr('data-color', item.color || '');
                $(row).attr('data-grade', item.grade || '');
                $(row).attr('data-supplier', item.supplier || '');
            }
        });

        function reloadTable() {
            table.ajax.reload(null, false);
        }

        $('#defectiveCoilsList_filter').hide();

        $(".select2").each(function() {
            $(this).select2({
                dropdownParent: $(this).parent()
            }).on('select2:select select2:unselect', function() {
                $(this).next('.select2-container').find('.select2-selection__rendered').removeAttr('title');
            });

            $(this).next('.select2-container').find('.select2-selection__rendered').removeAttr('title');
        });

        let selectedDefectiveId = 0;

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

        $('#saveNoteBtn').on('click', function () {
            const noteText = $('#newNote').val().trim();
            if (!noteText) {
                alert('Please add note.');
                return;
            }
            $.ajax({
                url: 'pages/coils_defective_ajax.php',
                type: 'POST',
                data: {
                    action: 'add_coil_note',
                    coil_defective_id: selectedDefectiveId,
                    note_text: noteText
                },
                success: function (response) {
                    if (response.trim() === 'success') {
                        $('#newNote').val('');
                        $('.view-notes-btn[data-id="' + selectedDefectiveId + '"]').click();
                    } else {
                        alert('Failed to save note: ' + response);
                    }
                },
                error: function () {
                    alert('Error saving note.');
                }
            });
        });

        $(document).on('click', '.preview-image', function () {
            var imgSrc = $(this).attr('src');
            $('#modalImage').attr('src', imgSrc);
            $('#imageModal').modal('show');
        });

        $(document).on('click', '.change_status', function (e) {
            e.preventDefault();
            const coilId = $(this).data('id');
            const action = $(this).data('action');
            const displayAction = action.charAt(0).toUpperCase() + action.slice(1).replace(/_/g, ' ');
            if (!confirm(`Are you sure you want to ${displayAction} this coil?`)) return;

            $.ajax({
                url: 'pages/coils_defective_ajax.php',
                type: 'POST',
                data: {
                    action: 'update_coil_status',
                    coil_id: coilId,
                    change_action: action
                },
                success: function (response) {
                    console.log(response);
                    reloadTable();
                },
                error: function () {
                    alert('Error processing request.');
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