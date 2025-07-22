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
                            <table id="defectiveCoilsList" class="table search-table align-middle text-nowrap">
                                <thead class="header-item">
                                <th>Coil #</th>
                                <th>Color</th>
                                <th>Grade</th>
                                <th>Remaining Feet</th>
                                <th>Date Tagged Defective</th>
                                <th>Note</th>
                                <th>Status Tagged</th>
                                <th>Action</th>
                                </thead>
                                <tbody>
                                <?php
                                    $no = 1;
                                    $query_coil = "
                                        SELECT 
                                            *
                                        FROM 
                                            coil_defective
                                        WHERE 
                                            status = '0'
                                        ORDER BY 
                                            tagged_date
                                    ";  

                                    $result_coil = mysqli_query($conn, $query_coil);            
                                    while ($row_coil = mysqli_fetch_array($result_coil)) {
                                        $coil_id = $row_coil['coil_id'];
                                        $db_status = $row_coil['status'];
                                        $remaining_feet = $row_coil['remaining_feet'] ?? 0;

                                        if(!empty($row_coil['main_image'])){
                                            $picture_path = $row_coil['main_image'];
                                        }else{
                                            $picture_path = "images/coils/product.jpg";
                                        }

                                        $color_details = getColorDetails($row_coil['color_sold_as']);
                                        $tagged_defective = $row_coil['tagged_defective'];

                                        $tag_html = '';
                                        if ($tagged_defective == 1) {
                                            $tag_html = '<span class="badge" style="background-color: #28a745; color: #fff;">Defective + Replaced</span>';
                                        } elseif ($tagged_defective == 2) {
                                            $tag_html = '<span class="badge" style="background-color: #dc3545; color: #fff;">Defective Only</span>';
                                        }

                                        $tagged_date_raw = $row_coil['tagged_date'] ?? '';

                                        if (!empty($tagged_date_raw) && strtotime($tagged_date_raw)) {
                                            $tagged_date_formatted = date('F j, Y', strtotime($tagged_date_raw));
                                        } else {
                                            $tagged_date_formatted = '';
                                        }
                                    ?>
                                        <tr class="search-items">
                                            <td>
                                                <a href="javascript:void(0)">
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?= $picture_path ?>" class="rounded-circle preview-image" alt="materialpro-img" width="56" height="56">
                                                        <div class="ms-3">
                                                            <h6 class="fw-semibold mb-0 fs-4"><?= $row_coil['entry_no'] ?></h6>
                                                        </div>
                                                    </div>
                                                </a>
                                            </td>
                                            <td>
                                                <div class="d-inline-flex align-items-center gap-2">
                                                    <a href="javascript:void(0)" id="viewAvailableBtn" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                                                        <span class="rounded-circle d-block" style="background-color:<?= $color_details['color_code'] ?>; width: 30px; height: 30px;"></span>
                                                        <?= $color_details['color_name'] ?>
                                                    </a>
                                                </div>
                                            </td>
                                            <td><?= getGradeName($row_coil['grade']) ?></td>
                                            
                                            <td><?= $remaining_feet ?></td>
                                            <td><?= $tagged_date_formatted ?></td>
                                            <th><?= $row_coil['tagged_note'] ?></th>
                                            <th><?= $tag_html ?></th>
                                            <td>
                                                <div class="action-btn d-flex align-items-center gap-2 text-center">
                                                    <a href="#" role="button" class="tag-rework-btn" data-id="<?= $row_coil['coil_defective_id'] ?>" title="Tag as For Rework">
                                                        <iconify-icon class="fs-7 text-warning" icon="mdi:tools"></iconify-icon>
                                                    </a>
                                                    <?php 
                                                    if ($tagged_defective == 2) {
                                                    ?>
                                                    <a href="#" role="button" class="tag-approve-btn" data-id="<?= $row_coil['coil_defective_id'] ?>" title="Approve Coil for Work Order">
                                                        <iconify-icon class="fs-7 text-success" icon="mdi:check-circle"></iconify-icon>
                                                    </a>
                                                    <?php
                                                    }
                                                    ?>
                                                </div>
                                            </td>
                                           
                                        </tr>
                                    <?php 
                                    $no++;
                                    } ?>
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

<script>
    $(document).ready(function() {
        document.title = "<?= $page_title ?>";

        var table = $('#defectiveCoilsList').DataTable({
            pageLength: 100
        });

        $('#defectiveCoilsList_filter').hide();

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
            if (!confirm('Tag this coil as For Rework?')) return;

            $.ajax({
                url: 'pages/coils_defective_ajax.php',
                type: 'POST',
                data: {
                    action: 'coil_tag_rework',
                    coil_defective_id: coil_defective_id
                },
                success: function (response) {
                    if (response.trim() === 'success') {
                        alert('Coil tagged as For Rework.');
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

        $(document).on('click', '.tag-approve-btn', function (e) {
            e.preventDefault();
            const coil_defective_id = $(this).data('id');
            if (!coil_defective_id) {
                alert('Invalid coil.');
                return;
            }
            if (!confirm('Approve this coil for work order?')) return;

            $.ajax({
                url: 'pages/coils_defective_ajax.php',
                type: 'POST',
                data: {
                    action: 'coil_tag_approve',
                    coil_defective_id: coil_defective_id
                },
                success: function (response) {
                    if (response.trim() === 'success') {
                        alert('Coil approved for work order.');
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