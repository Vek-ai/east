<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require '../includes/dbconn.php';
require '../includes/functions.php';

$page_title = "Work Order Finished";
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
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control py-0 ps-5 select2 filter-selection" id="select-category" data-filter="category" data-filter-name="Product Category">
                            <option value="" data-category="">All Categories</option>
                            <optgroup label="Category">
                                <?php
                                $query_category = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                                $result_category = mysqli_query($conn, $query_category);
                                while ($row_category = mysqli_fetch_array($result_category)) {
                                ?>
                                    <option value="<?= $row_category['product_category'] ?>" data-category="<?= $row_category['product_category'] ?>"><?= $row_category['product_category'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="select-type" data-filter="type" data-filter-name="Product Type">
                            <option value="" data-category="">All Product Types</option>
                            <optgroup label="Product Type">
                                <?php
                                $query_type = "SELECT * FROM product_type WHERE hidden = '0' AND status = '1' ORDER BY `product_type` ASC";
                                $result_type = mysqli_query($conn, $query_type);
                                while ($row_type = mysqli_fetch_array($result_type)) {
                                ?>
                                    <option value="<?= $row_type['product_type'] ?>" data-category="<?= $row_type['product_category'] ?>"><?= $row_type['product_type'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-width" data-filter="width" data-filter-name="Product Width">
                            <option value="" data-category="">All Widths</option>
                            <optgroup label="Product Widths">
                                <?php
                                $query_grade = "SELECT DISTINCT(width) FROM flat_sheet_width WHERE hidden = '0' AND status = '1' ORDER BY CAST(width AS DECIMAL(10,2)) ASC";
                                $result_grade = mysqli_query($conn, $query_grade);
                                while ($row_grade = mysqli_fetch_array($result_grade)) {
                                ?>
                                    <option value="<?= $row_grade['width'] ?>"><?= $row_grade['width'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-grade" data-filter="grade" data-filter-name="Product Grade">
                            <option value="" data-category="">All Grades</option>
                            <optgroup label="Product Grades">
                                <?php
                                $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY `product_grade` ASC";
                                $result_grade = mysqli_query($conn, $query_grade);
                                while ($row_grade = mysqli_fetch_array($result_grade)) {
                                ?>
                                    <option value="<?= $row_grade['product_grade'] ?>" data-category="grade"><?= $row_grade['product_grade'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-gauge" data-filter="gauge" data-filter-name="Product Gauge">
                            <option value="" data-category="">All Gauges</option>
                            <optgroup label="Product Gauges">
                                <?php
                                $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1' ORDER BY `product_gauge` ASC";
                                $result_gauge = mysqli_query($conn, $query_gauge);
                                while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                ?>
                                    <option value="<?= $row_gauge['product_gauge'] ?>" data-category="gauge"><?= $row_gauge['product_gauge'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
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
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="select-profile" data-filter="profile" data-filter-name="Product Profile">
                            <option value="" data-category="">All Profile Types</option>
                            <optgroup label="Profile Types">
                                <?php
                                $query_profile = "SELECT * FROM profile_type WHERE hidden = '0' AND status = '1' ORDER BY `profile_type` ASC";
                                $result_profile = mysqli_query($conn, $query_profile);
                                while ($row_profile = mysqli_fetch_array($result_profile)) {
                                ?>
                                    <option value="<?= $row_profile['profile_type'] ?>" data-category="<?= $row_profile['product_category'] ?>"><?= $row_profile['profile_type'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="select-status" data-filter="status" data-filter-name="Status">
                            <option value="" data-category="">All Status</option>
                            <optgroup label="Status">
                                <option value="New">New</option>
                                <option value="Processing">Processing</option>
                                <option value="Done">Done</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="select-order" data-filter="order" data-filter-name="Order Type">
                            <option value="" data-category="">All Orders</option>
                            <optgroup label="Order Types">
                                <option value="1">Estimate Order</option>
                                <option value="2">Sales Order</option>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class="px-3 mb-2"> 
                    <input type="checkbox" id="toggleActive"> Show Done
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
                        <?php
                        $query = "
                            SELECT 
                                wo.*, 
                                p.product_item 
                            FROM 
                                work_order AS wo
                            LEFT JOIN 
                                product AS p ON p.product_id = wo.productid
                            WHERE 
                                wo.submitted_date >= DATE_SUB(CURDATE(), INTERVAL 2 WEEK)
                                AND wo.submitted_date <= NOW()
                                AND wo.status IN (2,3)
                            ORDER BY 
                                wo.batch_id, wo.id
                        ";

                        $result = mysqli_query($conn, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            $grouped = [];

                            while ($row = mysqli_fetch_assoc($result)) {
                                $batch_id = $row['batch_id'] ?? 0;
                                $grouped[$batch_id][] = $row;
                            }
                        ?>
                        <table id="work_order_table" class="table table-hover mb-0 text-md-nowrap align-middle">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Products</th>
                                    <th>Cashier</th>
                                    <th>Customer</th>
                                    <th>Job Name</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $images_directory = "../images/drawing/";
                            $default_image = "../images/product/product.jpg";

                            foreach ($grouped as $batch_id => $items):
                                $first = $items[0];

                                $order_ids = array_unique(array_column($items, 'work_order_id'));
                                $order_nos = 'SO-' . implode(', SO-', $order_ids);

                                $has_done = false;
                                $all_done = true;

                                foreach ($items as $it) {
                                    if ((int)$it['status'] == 3) {
                                        $has_done = true;
                                    }
                                    if ((int)$it['status'] < 3) {
                                        $all_done = false;
                                    }
                                }

                                if (!$has_done) {
                                    continue;
                                }

                                if ($all_done) {
                                    $statusText = 'Done';
                                    $statusClass = 'badge bg-success';
                                } else {
                                    $statusText = 'Processing';
                                    $statusClass = 'badge bg-warning text-dark';
                                }

                                $cashier = get_name($first['user_id']);
                                $order_details = getOrderDetails($first['work_order_id']);
                                $customer_name = get_customer_name($order_details['customerid']);
                                $job_name = $order_details['job_name'];
                            ?>
                            <tr data-batch-id="<?= $batch_id ?>">
                                <td><?= htmlspecialchars($order_nos) ?></td>

                                <td class="text-wrap w-20">
                                    <?php foreach ($items as $row): 
                                        $product_id = $row['productid'];
                                        $product_name = getProductName($product_id);
                                        $picture_path = !empty($row['custom_img_src']) ? $images_directory . $row["custom_img_src"] : $default_image;
                                    ?>
                                    <div class="d-flex align-items-center mb-1">
                                        <img src="<?= $picture_path ?>" class="rounded-circle img-thumbnail me-2" width="40" height="40">
                                        <div><?= htmlspecialchars($product_name) ?></div>
                                    </div>
                                    <?php endforeach; ?>
                                </td>

                                <td><?= htmlspecialchars($cashier) ?></td>
                                <td><?= htmlspecialchars($customer_name) ?></td>
                                <td><?= htmlspecialchars($job_name) ?></td>

                                <td class="text-center"><span class="<?= $statusClass ?>"><?= $statusText ?></span></td>

                                <td class="text-center">
                                    <div class="action-btn">
                                        <a href="javascript:void(0)" class="text-decoration-none" id="viewBtn" title="View" data-id="<?= $batch_id ?>">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <?php if ($all_done): 
                                            $work_orders_str = implode(',', $order_ids);

                                            $coil_ids = [];
                                            foreach ($items as $it) {
                                                if (!empty($it['assigned_coils'])) {
                                                    $decoded = json_decode($it['assigned_coils'], true);
                                                    if (is_array($decoded)) {
                                                        $coil_ids = array_merge($coil_ids, $decoded);
                                                    }
                                                }
                                            }
                                            $coil_ids = array_unique($coil_ids);
                                            $coil_ids_str = implode(',', $coil_ids);
                                        ?>
                                            <a href="javascript:void(0)" class="text-decoration-none ms-2 addWasteBtn" 
                                                data-batch-id="<?= $batch_id ?>" 
                                                data-work-orders="<?= $work_orders_str ?>"
                                                data-coil-ids="<?= $coil_ids_str ?>"
                                                title="Add Waste">
                                                <i class="fa fa-recycle text-info"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php } else { ?>
                            <h4 class="text-center">No Requests found</h4>
                        <?php } ?>
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

<div class="modal fade" id="addWasteModal" tabindex="-1" aria-labelledby="addWasteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title" id="addWasteModalLabel">Add Waste</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addWasteForm">
                <div class="modal-body">
                    <input type="hidden" name="batch_id" id="wasteBatchId">
                    <input type="hidden" name="work_order_ids" id="wasteWorkOrders">
                    <input type="hidden" name="coil_ids" id="wasteCoilIds">

                    <div class="mb-3">
                        <label for="wasteLength" class="form-label">Length</label>
                        <input type="number" step="0.01" class="form-control" name="length" id="wasteLength" required>
                    </div>
                    <div class="mb-3">
                        <label for="wasteNotes" class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" id="wasteNotes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" id="saveWasteBtn">Save</button>
                </div>
            </form>
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
            url: 'pages/work_order_finish_ajax.php',
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

        $('#toggleActive').on('change', function() {
            table.draw();
        });

        $('#toggleActive').trigger('change');

        $(".select2").each(function() {
            $(this).select2({
                dropdownParent: $(this).parent()
            }).on('select2:select select2:unselect', function() {
                $(this).next('.select2-container').find('.select2-selection__rendered').removeAttr('title');
            });

            $(this).next('.select2-container').find('.select2-selection__rendered').removeAttr('title');
        });

        $('#select-status').on('change', function() {
            const selectedStatus = $(this).val();

            if (selectedStatus === 'Done') {
                $('#toggleActive').prop('checked', true);
            } else {
                $('#toggleActive').prop('checked', false);
            }
        });

        $(document).on('click', '#viewBtn', function(event) {
            var id = $(this).data('id');

            $.ajax({
                url: 'pages/work_order_finish_ajax.php',
                type: 'POST',
                data: {
                    id: id,
                    fetch_view: 'fetch_view'
                },
                success: function(response) {
                    $('#view-details').html(response);

                    if ($.fn.DataTable.isDataTable('#work_order_table_dtls')) {
                        $('#work_order_table_dtls').DataTable().clear().destroy();
                    }

                    var table = $('#work_order_table_dtls').DataTable({
                        pageLength: 100
                    });

                    $('#view_modal').modal('toggle');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#viewAvailableBtn', function(event) {
            var id = $(this).data('app-prod-id');

            $.ajax({
                url: 'pages/work_order_finish_ajax.php',
                type: 'POST',
                data: {
                    id: id,
                    fetch_available: 'fetch_available'
                },
                success: function(response) {
                    $('#available-details').html(response);
                    $('#view_available_modal').modal('toggle');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '.addWasteBtn', function() {
            const batchId = $(this).data('batch-id');
            const workOrders = $(this).data('work-orders');
            const coilIds = $(this).data('coil-ids');

            $('#wasteBatchId').val(batchId);
            $('#wasteWorkOrders').val(workOrders);
            $('#wasteCoilIds').val(coilIds);
            $('#wasteLength').val('');
            $('#wasteNotes').val('');

            const wasteModal = new bootstrap.Modal(document.getElementById('addWasteModal'));
            wasteModal.show();
        });

        $(document).on('submit', '#addWasteForm', function(e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);

            formData.append('add_waste', true);

            $.ajax({
                url: 'pages/work_order_finish_ajax.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.trim() === 'success') {
                        alert('Waste successfully added.');
                        location.reload();
                    } else {
                        alert('Failed to add waste.');
                        console.log(res);
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.statusText);
                    console.error(xhr.responseText);
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