<?php
require '../includes/dbconn.php';
require '../includes/functions.php';

$page_title = "New Work Orders";
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
                                <option value="" data-category="">All Colors</option>
                                <optgroup label="Product Colors">
                                    <?php
                                    $query_color = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                    $result_color = mysqli_query($conn, $query_color);
                                    while ($row_color = mysqli_fetch_array($result_color)) {
                                    ?>
                                        <option value="<?= $row_color['color_name'] ?>" data-category="category"><?= $row_color['color_name'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="px-3 mb-2"> 
                        <input type="checkbox" id="toggleBatch" checked> Toggle Batch Selection
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
                        <div class="product-details table-responsive text-wrap">
                            <?php
                                $query = "
                                    SELECT 
                                        wo.*, 
                                        p.product_item, 
                                        wo.work_order_id
                                    FROM 
                                        work_order AS wo
                                    LEFT JOIN 
                                        product AS p ON 
                                            p.product_id = wo.productid
                                    WHERE 
                                        wo.status = 1
                                ";

                                $result = mysqli_query($conn, $query);

                                if ($result && mysqli_num_rows($result) > 0) {
                                    $total_amount = 0;
                                    $total_count = 0;

                                    ?>
                                    <table id="work_order_table" class="table table-hover mb-0 text-md-nowrap">
                                        <thead>
                                            <tr>
                                                <th class="text-center align-middle">
                                                    <input type="checkbox" id="selectAll">
                                                </th>
                                                <th class="align-middle">Order #</th>
                                                <th class="w-20 align-middle">Description</th>
                                                <th class="text-center align-middle">Cashier</th>
                                                <th class="text-center align-middle">Color</th>
                                                <th class="text-center align-middle">Grade</th>
                                                <th class="text-center align-middle">Profile</th>
                                                <th class="text-center align-middle">Width</th>
                                                <th class="text-center align-middle">Length</th>
                                                <th class="text-center align-middle">Quantity</th>
                                                <th class="text-center align-middle">Details</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>     
                                        <?php
                                        $images_directory = "../images/drawing/";
                                        $no = 1;

                                        $default_image = '../images/product/product.jpg';
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $color_details = getColorDetails($row['custom_color']);
                                            $product_id = $row['productid'];
                                            $product_details = getProductDetails($product_id);
                                            $width = $row['custom_width'];
                                            $bend = $row['custom_bend'];
                                            $hem = $row['custom_hem'];
                                            $length = $row['custom_length'];
                                            $inch = $row['custom_length2'];
                                            $inventory_type = '';

                                            $order_no = $row['work_order_id'];

                                            $order_no = 'SO-' .$order_no;

                                            $picture_path = !empty($row['custom_img_src']) ? $images_directory.$row["custom_img_src"] : $default_image;
                                            ?>
                                            <tr data-id="<?= $product_id ?>"
                                                data-category="<?= getProductCategoryName($row['product_category']) ?>"
                                                data-type="<?= getProductTypeName($product_details['product_type']) ?>"
                                                data-inventory="<?= $inventory_type ?>"
                                                data-width="<?= $width ?>"
                                                data-grade="<?= getGradeName($row['custom_grade']) ?>"
                                                data-gauge="<?= getGaugeName($product_details['gauge']) ?>"
                                                data-color="<?= getColorName($row['custom_color']) ?>"
                                                data-profile="<?= getProfileTypeName($product_details['profile']) ?>"
                                                data-order="<?= $order_type ?>"

                                            >
                                                <td class="text-center align-middle">
                                                    <input type="checkbox" class="row-check" value="<?= $row['id'] ?>">
                                                </td>
                                                <td class="align-middle">
                                                    <?= $order_no ?>
                                                </td>
                                                <td class="align-middle text-wrap w-20"> 
                                                    <a href="javascript:void(0);" class="d-inline-flex align-items-center justify-content-start">
                                                            <img src="<?= $picture_path ?>" style="background-color: #fff; width: 56px; height: 56px;" class="rounded-circle img-thumbnail preview-image" width="56" height="56" style="background-color: #fff;">
                                                        <div class="mt-1 ms-2"><?= getProductName($product_id) ?></div>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?= get_name($row['user_id']); ?>
                                                </td>
                                                <td>
                                                <div class="d-inline-flex align-items-center gap-2">
                                                    <a 
                                                        href="javascript:void(0)" 
                                                        id="view_run_work_order" 
                                                        data-id="<?= $row['id'] ?>" 
                                                        class="d-inline-flex align-items-center gap-2 text-decoration-none">
                                                            <span class="rounded-circle d-block" style="background-color:<?= $color_details['color_code'] ?? '' ?>; width: 20px; height: 20px;"></span>
                                                            <?= $color_details['color_name'] ?? '' ?>
                                                    </a>
                                                </div>
                                                </td>
                                                <td>
                                                    <?php echo getGradeName($row['custom_grade']); ?>
                                                </td>
                                                <td>
                                                    <?php echo getProfileFromID($product_id); ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php 
                                                    if (!empty($width)) {
                                                        echo htmlspecialchars($width);
                                                    }
                                                    ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php 
                                                    if (!empty($length)) {
                                                        echo htmlspecialchars($length) . " ft";
                                                        
                                                        if (!empty($inch)) {
                                                            echo " " . htmlspecialchars($inch) . " in";
                                                        }
                                                    } elseif (!empty($inch)) {
                                                        echo htmlspecialchars($inch) . " in";
                                                    }
                                                    ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php echo $row['quantity']; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php 
                                                    if (!empty($bend)) {
                                                        echo "Bend: " . htmlspecialchars($bend) . "<br>";
                                                    }
                                                    
                                                    if (!empty($hem)) {
                                                        echo "Hem: " . htmlspecialchars($hem) . "<br>";
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="action-btn text-center">
                                                        <a href="javascript:void(0)" class="text-decoration-none" id="view_run_work_order" title="Run Work Order" data-id="<?= $row['id'] ?>">
                                                            <i class="fa fa-arrow-right-to-bracket"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            $no++;
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                    <?php
                                } else {
                                    echo "<h4 class='text-center'>No Requests found</h4>";
                                }
                                ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button id="bulk_run_work_order" class="btn ripple btn-success" type="button">Run Work Order</button>
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
    function loadWorkOrderDetails(approval_id){
        $.ajax({
            url: 'pages/work_order_new_ajax.php',
            type: 'POST',
            data: {
                approval_id: approval_id,
                fetch_order_details: "fetch_order_details"
            },
            success: function(response) {
                $('#approval-details').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    $(document).ready(function() {
        document.title = "<?= $page_title ?>";

        var table = $('#work_order_table').DataTable({
            pageLength: 100
        });

        $('#work_order_table_filter').hide();

        $(".select2").each(function() {
            $(this).select2({
                dropdownParent: $(this).parent()
            }).on('select2:select select2:unselect', function() {
                $(this).next('.select2-container').find('.select2-selection__rendered').removeAttr('title');
            });

            $(this).next('.select2-container').find('.select2-selection__rendered').removeAttr('title');
        });

        $('#toggleBatch').on('change', function () {
            const showBatch = $(this).is(':checked');

            if (showBatch) {
                $('#selectAll, .row-check').closest('th, td').show();
                $('#bulk_run_work_order').closest('.modal-footer').show();
            } else {
                $('#selectAll').prop('checked', false);
                $('.row-check').prop('checked', false);
                $('#selectAll, .row-check').closest('th, td').hide();
                $('#bulk_run_work_order').closest('.modal-footer').hide();
            }
        });

        $('#selectAll').on('change', function () {
            $('#work_order_table .row-check').prop('checked', $(this).is(':checked'));
        });

        $(document).on('click', '#bulk_run_work_order', function () {
            const selectedIds = $('#work_order_table .row-check:checked').map(function () {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) {
                alert('Please select at least one item to run.');
                return;
            }

            $.ajax({
                url: 'pages/work_order_new_ajax.php',
                type: 'POST',
                data: {
                    id: JSON.stringify(selectedIds),
                    fetch_coils: 'fetch_coils'
                },
                success: function(response) {
                    $('#coil_details').html(response);
                    $('#view_coils_modal').modal('toggle');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#view_run_work_order', function () {
            const id = $(this).data('id');
            const ids = Array.isArray(id) ? id : [id];

            $.ajax({
                url: 'pages/work_order_new_ajax.php',
                type: 'POST',
                data: {
                    id: JSON.stringify(ids),
                    fetch_coils: 'fetch_coils'
                },
                success: function(response) {
                    $('#coil_details').html(response);
                    $('#view_coils_modal').modal('toggle');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        let selectedCoils = [];

        $(document).on('change', '#coils_tbl .row-select', function () {
            const id = $(this).data('id');
            if (this.checked) {
                if (!selectedCoils.includes(id)) selectedCoils.push(id);
            } else {
                selectedCoils = selectedCoils.filter(i => i !== id);
            }
        });

        $(document).on('change', '#selectAllCoils', function () {
            const checked = this.checked;
            $('#coils_tbl .row-select').prop('checked', checked).trigger('change');
        });

        $(document).on('click', '#save_selected_coils', function () {
            const coils = $('#coils_tbl .row-select:checked').map((_, el) => $(el).data('id')).get();
            const selectedIds = $('#work_order_table .row-check:checked').map(function () {
                return $(this).val();
            }).get();

            if (coils.length === 0) {
                alert('Please select at least one coil to run.');
                return;
            }

            if (selectedIds.length === 0) {
                alert('Please select at least one work order to run.');
                return;
            }

            $.ajax({
                url: 'pages/work_order_new_ajax.php',
                method: 'POST',
                data: {
                    run_work_order: true,
                    selected_ids: selectedIds,
                    selected_coils: JSON.stringify(coils)
                },
                success: function (res) {
                    if (res.trim() === 'success') {
                        $('.modal').modal('hide');
                        alert('Successfully Saved!');
                        location.reload();
                    } else {
                        alert('Failed to Update!');
                        console.log(res);
                    }
                },
                error: function (xhr, status, error) {
                    alert('AJAX error occurred: ' + error);
                    console.error('AJAX Error:', xhr.responseText);
                }
            });
        });

        $(document).on('click', '.preview-image', function () {
            var imgSrc = $(this).attr('src');
            $('#modalImage').attr('src', imgSrc);
            $('#imageModal').modal('show');
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

        $(document).on('input change', '#text-srh, .filter-selection', filterTable);

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