<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);
require 'includes/dbconn.php';
require 'includes/functions.php';
$permission = $_SESSION['permission'];

$staff_id = intval($_SESSION['userid']);
$profileSql = "SELECT access_profile_id FROM staff WHERE staff_id = $staff_id";
$profileRes = mysqli_query($conn, $profileSql);
$profile_id = 0;
if ($profileRes && mysqli_num_rows($profileRes) > 0) {
    $profile_id = intval(mysqli_fetch_assoc($profileRes)['access_profile_id']);
}
$page_id = getPageIdFromUrl($_GET['page'] ?? '');

$visibleColumns = getVisibleColumns($page_id, $profile_id);
function showCol($name) {
    global $visibleColumns;
    return !empty($visibleColumns[$name]);
}
?>
<style>
    .select2-container--default .select2-results__option[aria-disabled=true] {
        display: none;
    }
</style>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
        <div class="card-body px-0">
            <div class="d-flex justify-content-between align-items-center">
                <div><br>
                    <h4 class="font-weight-medium fs-14 mb-0"> Inventory</h4>
                    <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="?page=">Home
                        </a>
                        </li>
                        <li class="breadcrumb-item text-muted" aria-current="page">Inventory</li>
                    </ol>
                    </nav>
                </div>
            
            </div>
        </div>
    </div>

    <div class="widget-content searchable-container list">
    <div class="modal fade" id="inventoryModal" tabindex="-1" aria-labelledby="inventoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="inventoryModalLabel">Add Inventory</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="inventory_form" class="form-horizontal">
                    <div class="modal-body inventory_from_body">
                        
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
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

    
    <div class="card card-body">
        <div class="row">
            <div class="col-3">
                <h3 class="card-title align-items-center mb-2">
                    Filter Inventory 
                </h3>
                <div class="position-relative w-100 px-1 mr-0 mb-2">
                    <input type="text" class="form-control search-chat py-2 ps-5 " id="text-srh" placeholder="Search Product">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
                <div class="align-items-center">
                    <div class="position-relative w-100 px-1 mb-2">
                        <select id="category_filter" class="form-control color-cart select2-filter filter-selection" name="color_id">
                            <option value="" >All Categories...</option>
                            <?php
                            $query_product_category = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                            $result_product_category = mysqli_query($conn, $query_product_category);            
                            while ($row_product_category = mysqli_fetch_array($result_product_category)) {
                            ?>
                                <option value="<?= $row_product_category['product_category_id'] ?>"><?= $row_product_category['product_category'] ?></option>
                            <?php   
                            }
                            ?>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select id="line_filter" class="form-control color-cart select2-filter filter-selection" name="color_id">
                            <option value="" >All Product Lines...</option>
                            <?php
                            $query_product_line = "SELECT * FROM product_line WHERE hidden = '0' AND status = '1' ORDER BY `product_line` ASC";
                            $result_product_line = mysqli_query($conn, $query_product_line);            
                            while ($row_product_line = mysqli_fetch_array($result_product_line)) {
                            ?>
                                <option value="<?= $row_product_line['product_line_id'] ?>" <?= $selected ?>><?= $row_product_line['product_line'] ?></option>
                            <?php   
                            }
                            ?>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select id="type_filter" class="form-control color-cart select2-filter filter-selection" name="color_id">
                            <option value="" >All Product Types...</option>
                            <?php
                            $query_product_type = "SELECT * FROM product_type WHERE hidden = '0' AND status = '1' ORDER BY `product_type` ASC";
                            $result_product_type = mysqli_query($conn, $query_product_type);            
                            while ($row_product_type = mysqli_fetch_array($result_product_type)) {
                            ?>
                                <option value="<?= $row_product_type['product_type_id'] ?>" <?= $selected ?>><?= $row_product_type['product_type'] ?></option>
                            <?php   
                            }
                            ?>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select id="color_filter" class="form-control color-cart select2-filter filter-selection" name="color_id">
                            <option value="" >All Colors...</option>
                            <?php
                            $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                            $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                            while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                            ?>
                                <option value="<?= $row_paint_colors['color_id'] ?>" <?= $selected ?> data-color="<?= getColorHexFromColorID($row_paint_colors['color_id']) ?>"><?= $row_paint_colors['color_name'] ?></option>
                            <?php   
                            }
                            ?>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select id="grade_filter" class="form-control color-cart select2-filter filter-selection" name="color_id">
                            <option value="" >All Grades...</option>
                            <?php
                            $query_grades = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY `product_grade` ASC";
                            $result_grades = mysqli_query($conn, $query_grades);            
                            while ($row_grades = mysqli_fetch_array($result_grades)) {
                            ?>
                                <option value="<?= $row_grades['product_grade_id'] ?>" <?= $selected ?>><?= $row_grades['product_grade'] ?></option>
                            <?php   
                            }
                            ?>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select id="gauge_filter" class="form-control color-cart select2-filter filter-selection" name="color_id">
                            <option value="" >All Gauges...</option>
                            <?php
                            $query_product_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1' ORDER BY `product_gauge` ASC";
                            $result_product_gauge = mysqli_query($conn, $query_product_gauge);            
                            while ($row_product_gauge = mysqli_fetch_array($result_product_gauge)) {
                            ?>
                                <option value="<?= $row_product_gauge['product_gauge_id'] ?>" <?= $selected ?>><?= $row_product_gauge['product_gauge'] ?></option>
                            <?php   
                            }
                            ?>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select id="supplier_filter" class="form-control select2-filter filter-selection" name="supplier_id">
                            <option value="" >All Suppliers...</option>
                            <optgroup label="Supplier">
                                <?php
                                $query_supplier = "SELECT * FROM supplier WHERE status = 1 ORDER BY `supplier_name` ASC";
                                $result_supplier = mysqli_query($conn, $query_supplier);            
                                while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                                ?>
                                    <option value="<?= $row_supplier['supplier_id'] ?>" ><?= $row_supplier['supplier_name'] ?></option>
                                <?php   
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select id="warehouse_filter" class="form-control select2-filter filter-selection" name="Warehouse_id">
                            <option value="" >All Warehouses...</option>
                            <optgroup label="Warehouse">
                                <?php
                                $query_warehouse = "SELECT * FROM warehouses WHERE status = '1'";
                                $result_warehouse = mysqli_query($conn, $query_warehouse);            
                                while ($row_warehouse = mysqli_fetch_array($result_warehouse)) {
                                ?>
                                    <option value="<?= $row_warehouse['WarehouseID'] ?>" ><?= $row_warehouse['WarehouseName'] ?></option>
                                <?php   
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <!-- 
                    <div class="position-relative w-100 px-1 mb-2">
                        <select id="shelves_filter" class="form-control select2-filter filter-selection" name="Shelves_id">
                            <option value="" >All Shelfs...</option>
                            <optgroup label="Shelf">
                                <?php
                                $query_shelf = "SELECT * FROM shelves";
                                $result_shelf = mysqli_query($conn, $query_shelf);            
                                while ($row_shelf = mysqli_fetch_array($result_shelf)) {
                                ?>
                                    <option value="<?= $row_shelf['ShelfID'] ?>" ><?= $row_shelf['ShelfCode'] ?></option>
                                <?php   
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select id="bin_filter" class="form-control select2-filter filter-selection" name="Bin_id">
                            <option value="" >All Bins...</option>
                            <optgroup label="Bin">
                                <?php
                                $query_bin = "SELECT * FROM bins";
                                $result_bin = mysqli_query($conn, $query_bin);            
                                while ($row_bin = mysqli_fetch_array($result_bin)) {
                                ?>
                                    <option value="<?= $row_bin['BinID'] ?>" ><?= $row_bin['BinCode'] ?></option>
                                <?php   
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select id="row_filter" class="form-control select2-filter filter-selection" name="Row_id">
                            <option value="" >All Rows...</option>
                            <optgroup label="Row">
                                <?php
                                $query_rows = "SELECT * FROM warehouse_rows";
                                $result_rows = mysqli_query($conn, $query_rows);            
                                while ($row_rows = mysqli_fetch_array($result_rows)) {
                                ?>
                                    <option value="<?= $row_rows['WarehouseRowID'] ?>" ><?= $row_rows['WarehouseRowID'] ?></option>
                                <?php   
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div> 
                    -->
                </div>
                <div class="px-3"> 
                    <input type="checkbox" id="toggleActive" checked> Show Instock Only
                </div>
                <div class="d-flex justify-content-end py-2">
                    <button type="button" class="btn btn-outline-primary reset_filters">
                        <i class="fas fa-sync-alt me-1"></i> Reset Filters
                    </button>
                </div>
            </div>
            <div class="col-9">
                <div class="datatables">
                    <div class="table-responsive">
                    <h3 class="card-title d-flex justify-content-between align-items-center">
                        Inventory List 
                    </h3>
                    <div id="selected-tags" class="mb-2"></div>
                    <table id="inventoryList" class="table search-table align-middle text-wrap">
                        <thead class="header-item">
                            <tr>
                                <th>Product ID #</th>
                                <th>Product Name</th>
                                <th>Color</th>
                                <th>Grade</th>
                                <th>Gauge</th>
                                <th>Warehouse</th>
                                <th>Qty On Hand</th>
                                <th>Last Update</th>
                                <th>Last Edit By</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            
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
    function formatOption(state) {
        if (!state.id) {
            return state.text;
        }
        var color = $(state.element).data('color');
        var $state = $(
            '<span class="d-flex align-items-center">' +
                '<span class="rounded-circle d-block p-1 me-2" style="background-color:' + color + '; width: 16px; height: 16px;"></span>' +
                state.text + 
            '</span>'
        );
        return $state;
    }

    function formatSelected(state) {
        if (!state.id) {
            return state.text;
        }
        var color = $(state.element).data('color');
        var $state = $( 
            '<span class="d-flex align-items-center justify-content-center">' + 
                '<span class="rounded-circle d-block p-1" style="background-color:' + color + '; width: 25px; height: 25px;"></span>' +
                '&nbsp;' +
            '</span>'
        );
        return $state;
    }

    function filterLengthsByProduct($select) {
        const selectedCategory = String($('#product_id').find(':selected').data('category'));

        $select.find('option').each(function() {
            const categories = ($(this).attr('data-category') || '').replace(/[\[\]\s]/g, '').split(',').filter(Boolean);
            const match = categories.includes(selectedCategory);

            $(this).toggle(match);
        });
    }

    $(document).ready(function() {
        $(document).on('change', '#supplier_id', function() {
            $('#pack_add').val('').trigger('change');
        });

        $(".select2-filter").each(function() {
            $(this).select2({
                dropdownParent: $(this).parent()
            });
        });

        $(document).on('click', '#view_inventory_btn, #add_inventory_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            var type = $(this).data('type');
            var line = $(this).data('line');
            var grade = $(this).data('grade');
            var gauge = $(this).data('gauge');
            var color = $(this).data('color');
            var dim = $(this).data('dim');
            var inv = $(this).data('inv');

            $.ajax({
                url: 'pages/inventory_ajax.php',
                type: 'POST',
                data: { 
                    id: id, 
                    type: type, 
                    line: line, 
                    grade: grade, 
                    gauge: gauge, 
                    color: color, 
                    dim: dim, 
                    inv: inv, 
                    action: "fetch_modal" 
                },
                success: function(response) {
                    $(".inventory_from_body").html(response);
                    $(".select2-inventory").each(function() {
                        $(this).select2({
                            dropdownParent: $(this).parent()
                        });
                    });
                    updateWarehouseLocation();
                    $("#inventoryModal").modal("show");
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Failed to fetch' + textStatus + ' - ' + errorThrown);
                    console.log(jqXHR.responseText);
                }
            });
        });

        function updateWarehouseLocation() {
            var location = $('#Warehouse_id option:selected').data('location') || '';
            $('.warehouse_location').text(location);
        }

        $(document).on('change', '#Warehouse_id', updateWarehouseLocation);

        updateWarehouseLocation();

        $('#length_rows_wrapper').on('click', '.remove_length_row', function() {
            $(this).closest('.length-row').remove();
        });

        $(document).on('submit', '#inventory_form', function(event) {
            event.preventDefault();

            var formData = new FormData(this);
            formData.append('action', 'add_update');

            $.ajax({
                url: 'pages/inventory_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#inventoryModal').modal('hide');
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Inventory saved successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger").addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    } else {
                        $('#responseHeader').text("Failed");
                        $('#responseMsg').text(response);
                        $('#responseHeaderContainer').removeClass("bg-success").addClass("bg-danger");
                        $('#response-modal').modal("show");
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        var table = $('#inventoryList').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 100,
            lengthMenu: [[100, 250, 500], [100, 250, 500]],
            ajax: {
                url: "pages/inventory_ajax.php",
                type: "POST",
                data: function (d) {
                    d.action = "fetch_table";

                    d.category = $('#category_filter').val()?.toString() || '';
                    d.line = $('#line_filter').val()?.toString() || '';
                    d.type = $('#type_filter').val()?.toString() || '';
                    d.color = $('#color_filter').val()?.toString() || '';
                    d.grade = $('#grade_filter').val()?.toString() || '';
                    d.gauge = $('#gauge_filter').val()?.toString() || '';
                    
                    d.supplier = $('#supplier_filter').val()?.toString() || '';
                    d.warehouse = $('#warehouse_filter').val()?.toString() || '';
                    d.shelf = $('#shelves_filter').val()?.toString() || '';
                    d.bin = $('#bin_filter').val()?.toString() || '';
                    d.rowFilter = $('#row_filter').val()?.toString() || '';
                    d.textSearch = $('#text-srh').val()?.toLowerCase().trim() || '';
                    d.isStock = $('#toggleActive').prop('checked') ? 1 : 0;
                }
            },
            drawCallback: function(settings) {
                updateSelectedTags();
            }
        });

        table.on('xhr', function(e, settings, json, xhr) {
            console.log("DataTables server response:", json);
        });

        table.on('error.dt', function(e, settings, techNote, message) {
            console.error("Server response text:", settings.jqXHR.responseText);
            alert("There was an error loading the table.");
        });


        $('#color_filter, #supplier_filter, #warehouse_filter, #shelves_filter, #bin_filter, #row_filter')
            .on('change', function () { table.ajax.reload(); });

        $('#text-srh').on('keyup', function () { table.ajax.reload(); });

        $('#toggleActive').on('change', function () { table.ajax.reload(); });

        function updateSelectedTags() {
            const sections = [
                { id: '#color_filter', title: 'Color' },
                { id: '#supplier_filter', title: 'Supplier' },
                { id: '#warehouse_filter', title: 'Warehouse' },
                { id: '#shelves_filter', title: 'Shelves' },
                { id: '#bin_filter', title: 'Bin' },
                { id: '#row_filter', title: 'Row' },
            ];

            const displayDiv = $('#selected-tags');
            displayDiv.empty();

            sections.forEach((section) => {
                const selectedOption = $(`${section.id} option:selected`);
                const selectedText = selectedOption.text().trim();

                if (selectedOption.val() && selectedText !== 'All') {
                    displayDiv.append(`
                        <div class="d-inline-block p-1 m-1 border rounded bg-light">
                            <span class="text-dark">${section.title}: ${selectedText}</span>
                            <button type="button" 
                                class="btn-close btn-sm ms-1 remove-tag" 
                                style="width: 0.75rem; height: 0.75rem;" 
                                aria-label="Close" 
                                data-tag="${selectedText}" 
                                data-select="${section.id}">
                            </button>
                        </div>
                    `);
                }
            });

            $('.remove-tag').on('click', function() {
                const selectId = $(this).data('select');
                $(selectId).val('').trigger('change');
                $(this).parent().remove();
            });
        }

        $(document).on('change', '.filter-selection', function () {
            table.ajax.reload();
        });

        $(document).on('click', '.reset_filters', function () {
            $('.filter-selection').each(function () {
                $(this).val(null).trigger('change.select2');
            });

            $('#text-srh').val('');

            table.ajax.reload();
        });
    });
</script>



