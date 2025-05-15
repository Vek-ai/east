<?php
require '../includes/dbconn.php';
require '../includes/functions.php';

$page_title = "Work Order";
?>

<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0">Work Orders</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="?page=">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Work Orders</li>
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
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="select-inventory" data-filter="inventory" data-filter-name="Inventory Type">
                            <option value="" data-category="">All Inventory Types</option>
                            <optgroup label="Inventory Types">
                                <option value="Coils">Coils</option>
                                <option value="Flat-Stock">Flat-Stock</option>
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
                                SELECT wo.*, p.product_item
                                FROM work_order AS wo
                                LEFT JOIN product AS p ON p.product_id = wo.productid
                                WHERE 
                                (wo.submitted_date >= DATE_SUB(curdate(), INTERVAL 2 WEEK) AND wo.submitted_date <= NOW())
                            ";

                            $result = mysqli_query($conn, $query);

                            if ($result && mysqli_num_rows($result) > 0) {
                                $total_amount = 0;
                                $total_count = 0;

                                ?>
                                <table id="work_order_table" class="table table-hover mb-0 text-md-nowrap">
                                    <thead>
                                        <tr>
                                            <th class="w-20 align-middle">Description</th>
                                            <th class="text-center align-middle">Cashier</th>
                                            <th class="text-center align-middle">Color</th>
                                            <th class="text-center align-middle">Grade</th>
                                            <th class="text-center align-middle">Profile</th>
                                            <th class="text-center align-middle">Width</th>
                                            <th class="text-center align-middle">Length</th>
                                            <th class="text-center align-middle">Status</th>
                                            <th class="text-center align-middle">Quantity</th>
                                            <th class="text-center align-middle">Details</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>     
                                    <?php
                                    $images_directory = "../images/drawing/";

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
                                        $status = $row['status'];

                                        $status = (int)$row['status'];
                                        $statusText = '';

                                        switch ($status) {
                                            case 1:
                                                $statusText = 'New';
                                                break;
                                            case 2:
                                                $statusText = 'Processing';
                                                break;
                                            case 3:
                                                $statusText = 'Done';
                                                break;
                                            default:
                                                $statusText = 'Unknown';
                                        }


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
                                            data-status="<?= $statusText ?>"
                                        >
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
                                                    id="viewAvailableBtn" 
                                                    data-app-prod-id="<?= $row['id'] ?>" 
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
                                                <?php
                                                $status = (int)$row['status'];
                                                $statusText = '';
                                                $statusClass = '';

                                                switch ($status) {
                                                    case 1:
                                                        $statusText = 'New';
                                                        $statusClass = 'badge bg-primary';
                                                        break;
                                                    case 2:
                                                        $statusText = 'Processing';
                                                        $statusClass = 'badge bg-warning text-dark';
                                                        break;
                                                    case 3:
                                                        $statusText = 'Done';
                                                        $statusClass = 'badge bg-success';
                                                        break;
                                                    default:
                                                        $statusText = 'Unknown';
                                                        $statusClass = 'badge bg-secondary';
                                                }
                                                ?>
                                                <span class="<?= $statusClass ?>"><?= $statusText ?></span>
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
                                                    <a href="javascript:void(0)" class="text-decoration-none" id="viewAvailableBtn" title="Run Work Order" data-app-prod-id="<?= $row['id'] ?>">
                                                        <i class="fa fa-arrow-right-to-bracket"></i>
                                                    </a>
                                                    <a href="javascript:void(0)" class="text-decoration-none" id="viewBtn" title="View" data-app-prod-id="<?= $row['id'] ?>">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
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
    </div>
    </div>
</div>

<div class="modal" id="view_available_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h4 class="modal-title">Assigned Coils for Work Order</h4>
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
            url: 'pages/work_order_ajax.php',
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

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var status = $(table.row(dataIndex).node()).find('span .badge').text().trim();
            var isActive = $('#toggleActive').is(':checked');

            if (isActive || status != 'Done') {
                return true;
            }
            return false;
        });

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

        $(document).on('click', '#viewAvailableBtn', function(event) {
            var id = $(this).data('app-prod-id');

            $.ajax({
                url: 'pages/work_order_ajax.php',
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

        $(document).on('click', '#viewBtn', function(event) {
            var id = $(this).data('app-prod-id');

            $.ajax({
                url: 'pages/work_order_ajax.php',
                type: 'POST',
                data: {
                    id: id,
                    fetch_details: 'fetch_details'
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

            if (!isActive) {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    return $(table.row(dataIndex).node()).find('span.badge').text().trim() != 'Done';
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