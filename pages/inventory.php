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
                <a class="text-muted text-decoration-none" href="">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Inventory</li>
            </ol>
            </nav>
        </div>
        <div>
            <div class="d-sm-flex d-none gap-3 no-block justify-content-end align-items-center">
            <div class="d-flex gap-2">
                <div class="">
                <small>This Month</small>
                <h4 class="text-primary mb-0 ">$58,256</h4>
                </div>
                <div class="">
                <div class="breadbar"></div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <div class="">
                <small>Last Month</small>
                <h4 class="text-secondary mb-0 ">$58,256</h4>
                </div>
                <div class="">
                <div class="breadbar2"></div>
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
    </div>

    <div class="widget-content searchable-container list">
    <?php                                                    
    if ($permission === 'edit') {
    ?>
    <div class="card card-body">
        <div class="row">
        <div class="col-md-12 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0">
            <button type="button" id="add_inventory_btn" class="btn btn-primary d-flex align-items-center" data-id="">
                <i class="ti ti-users text-white me-1 fs-5"></i> Add Inventory
            </button>
        </div>
        </div>
    </div>
    <?php
    }
    ?>

    <div class="modal fade" id="inventoryModal" tabindex="-1" aria-labelledby="inventoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
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
                </div>
                <div class="px-3"> 
                    <input type="checkbox" id="toggleActive" checked> Show New Only
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
                        <?php
                            $no = 1;
                            $query_inventory = "
                                SELECT 
                                    Inventory_id,
                                    Product_id,
                                    color_id,
                                    grade,
                                    gauge,
                                    SUM(quantity_ttl) AS total_quantity,
                                    MAX(last_edit) AS last_edit,
                                    MAX(addedby) AS added_by,
                                    MAX(edited_by) AS edited_by,
                                    MAX(Warehouse_id) AS Warehouse_id,
                                    MAX(status) AS status
                                FROM inventory
                                GROUP BY Product_id, color_id, grade, gauge
                                ORDER BY Product_id, color_id, grade, gauge;
                            ";
                            $result_inventory = mysqli_query($conn, $query_inventory);            
                            while ($row_inventory = mysqli_fetch_array($result_inventory)) {
                                $Inventory_id = $row_inventory['Inventory_id'];
                                $Product_id = $row_inventory['Product_id'];
                                $color_id = $row_inventory['color_id'];
                                $Warehouse_id = $row_inventory['Warehouse_id'];
                                $Shelves_id = $row_inventory['Shelves_id'];
                                $Bin_id = $row_inventory['Bin_id'];
                                $Row_id = $row_inventory['Row_id'];
                                $Date = $row_inventory['Date'];
                                $quantity = $row_inventory['quantity'];
                                $quantity_ttl = $row_inventory['total_quantity'];
                                $addedby = $row_inventory['addedby'];
                                $db_status = $row_inventory['status'];

                                if (trim($db_status) == '0') {
                                    $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$Inventory_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-primary bg-primary text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>New</div></a>";
                                } else if (trim($db_status) == '1'){
                                    $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$Inventory_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Transferred</div></a>";
                                }else{
                                    $status = "";
                                }

                                $last_edit = $row_inventory['last_edit'];
                                if (!empty($row['last_edit']) && strtotime($row['last_edit']) !== false) {
                                    $last_edit = date('m/d/Y', strtotime($row['last_edit']));
                                }

                                $added_by = $row_inventory['added_by'];
                                $edited_by = $row_inventory['edited_by'];
                        
                                if ($edited_by != "0") {
                                    $last_user_name = get_staff_name($edited_by);
                                } elseif ($added_by != "0") {
                                    $last_user_name = get_staff_name($added_by);
                                } else {
                                    $last_user_name = "";
                                }

                                $product = getProductDetails($Product_id);
                                $product_category = $product['product_category'] ?? 0;
                                $lumber_type = ucwords($row_inventory['lumber_type']);

                                $color_id = $row_inventory['color_id'];
                                $grade = $row_inventory['grade'];
                                $gauge = $row_inventory['gauge'];

                                $product_type_json = $product['product_type'] ?? '[]';
                                $product_type_arr = json_decode($product_type_json, true);
                                $product_type = !empty($product_type_arr) ? end($product_type_arr) : 0;
                                ?>
                                <tr class="search-items"
                                    data-color="<?= $row_inventory['color_id'] ?? 0 ?>"
                                    data-supplier="<?= $row_inventory['supplier_id'] ?? 0 ?>"
                                    data-warehouse="<?= $row_inventory['Warehouse_id'] ?? 0 ?>"
                                    data-shelf="<?= $row_inventory['Shelves_id'] ?? 0 ?>"
                                    data-bin="<?= $row_inventory['Bin_id'] ?? 0 ?>"
                                    data-row="<?= $row_inventory['Row_id'] ?? 0 ?>"
                                    data-new="<?= $row_inventory['status'] == 0 ? 1 : 0 ?>"
                                >
                                    <td>
                                        <?php 
                                            $product_id_abbrev = fetchSingleProductABR(
                                                $product_category,
                                                '',
                                                $grade,
                                                $gauge,
                                                $product_type,
                                                $color_id,
                                                ''
                                            );

                                            echo $product_id_abbrev;
                                        ?>
                                    </td>
                                    <td><?= getProductName($Product_id) ?></td>
                                    <td><?= getColorName($color_id) ?></td>
                                    <td><?= getGradeName($grade) ?></td>
                                    <td><?= getGaugeName($gauge) ?></td>
                                    <td><?= getWarehouseName($Warehouse_id) ?></td>
                                    <td><?= $quantity_ttl ?></td>
                                    <td><?= $last_edit ?></td>
                                    <td><?= $last_user_name ?></td>
                                    <td><?= $status ?></td>
                                    <td>
                                        <div class="action-btn text-center">
                                            <?php                                                    
                                            if ($permission === 'edit') {
                                            ?>
                                            <a href="#" id="view_inventory_btn" title="Edit" class="text-primary edit" data-id="<?= $Inventory_id ?>">
                                                <i class="ti ti-pencil fs-5"></i>
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
                        <script>
                            $(document).ready(function() {
                                // Use event delegation for dynamically generated elements
                                $(document).on('click', '.changeStatus', function(event) {
                                    event.preventDefault(); 
                                    var inventory_id = $(this).data('id');
                                    var status = $(this).data('status');
                                    var no = $(this).data('no');
                                    $.ajax({
                                        url: 'pages/inventory_ajax.php',
                                        type: 'POST',
                                        data: {
                                            inventory_id: inventory_id,
                                            status: status,
                                            action: 'change_status'
                                        },
                                        success: function(response) {
                                            console.log(response.trim())
                                            if (response.trim() == 'success') {
                                                if (status == 0) {
                                                    $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Transferred');
                                                    $(".changeStatus[data-no='" + no + "']").data('status', "1");
                                                    $('.inventory' + no).addClass('emphasize-strike'); // Add emphasize-strike class
                                                    $('#toggleActive').trigger('change');
                                                } else {
                                                    $('#status-alert' + no).removeClass().addClass('alert alert-primary bg-primary text-white border-0 text-center py-1 px-2 my-0').text('New');
                                                    $(".changeStatus[data-no='" + no + "']").data('status', "0");
                                                    $('.inventory' + no).removeClass('emphasize-strike'); // Remove emphasize-strike class
                                                    $('#toggleActive').trigger('change');
                                                }
                                            } else {
                                                alert('Failed to change status.');
                                            }
                                        },
                                        error: function(jqXHR, textStatus, errorThrown) {
                                            alert('Error: ' + textStatus + ' - ' + errorThrown);
                                        }
                                    });
                                });
                            });
                            </script>
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

            $.ajax({
                url: 'pages/inventory_ajax.php',
                type: 'POST',
                data: { 
                    id: id, 
                    action: "fetch_modal" 
                },
                success: function(response) {
                    $(".inventory_from_body").html(response);

                    $('.dimension_id').each(function() {
                        filterLengthsByProduct($(this));
                    });

                    $("#inventoryModal").modal("show");
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Failed to fetch' + textStatus + ' - ' + errorThrown);
                    console.log(jqXHR.responseText);
                }
            });
        });

        $(document).on('change', '#product_id', function() {
            $('.dimension_id').each(function() {
                filterLengthsByProduct($(this));
            });
        });

        $(document).on('click', '#add_length_row', function() {
            const $clone = $('#length_row_template .length-row').clone();
            $('#length_rows_wrapper').append($clone);
            filterLengthsByProduct($clone.find('.dimension_id'));
        });

        $('#length_rows_wrapper').on('click', '.remove_length_row', function() {
            $(this).closest('.length-row').remove();
        });

        $(document).on('change', '#quantity_add, #pack_add', function(event) {
            var qty = parseFloat($('#quantity_add').val());
            var selectedOption = $('#pack_add').find('option:selected');
            var pack = selectedOption.length ? parseFloat(selectedOption.data('count')) : 1;

            pack = isNaN(pack) ? 1 : pack;

            if (!isNaN(qty) && qty > 0) {
                $('#quantity_ttl_add').val(qty * pack);
            } else {
                $('#quantity_ttl_add').val('');
            }
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
            "order": [],
            "dom": 'ltp'
        });

        $('#color_filter, #supplier_filter, #warehouse_filter, #shelves_filter, #bin_filter, #row_filter').on('change', filterTable);
        $('#text-srh').on('keyup', filterTable);
        $('#toggleActive').on('change', filterTable);

        function filterTable() {
            var color = $('#color_filter').val()?.toString() || '';
            var supplier = $('#supplier_filter').val()?.toString() || '';
            var warehouse = $('#warehouse_filter').val()?.toString() || '';
            var shelf = $('#shelves_filter').val()?.toString() || '';
            var bin = $('#bin_filter').val()?.toString() || '';
            var rowFilter = $('#row_filter').val()?.toString() || '';
            var textSearch = $('#text-srh').val().toLowerCase().trim();
            var isNew = $('#toggleActive').prop('checked') ? 1 : 0;

            $.fn.dataTable.ext.search = [];

            if (textSearch) {
                $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                    var rowText = $(table.row(dataIndex).node()).text().toLowerCase();
                    return rowText.includes(textSearch);
                });
            }

            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                var row = $(table.row(dataIndex).node());

                if (color && color !== '/' && row.data('color')?.toString() !== color) {
                    return false;
                }
                if (supplier && supplier !== '/' && row.data('supplier')?.toString() !== supplier) {
                    return false;
                }
                if (warehouse && warehouse !== '/' && row.data('warehouse')?.toString() !== warehouse) {
                    return false;
                }
                if (shelf && shelf !== '/' && row.data('shelf')?.toString() !== shelf) {
                    return false;
                }
                if (bin && bin !== '/' && row.data('bin')?.toString() !== bin) {
                    return false;
                }
                if (rowFilter && rowFilter !== '/' && row.data('row')?.toString() !== rowFilter) {
                    return false;
                }
                if (isNew && row.data('new') != isNew) return false;

                return true;
            }); 

            table.draw();
            updateSelectedTags();
        }

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

        filterTable();

        $(document).on('click', '.reset_filters', function () {
            $('.filter-selection').each(function () {
                $(this).val(null).trigger('change.select2');
            });

            $('#text-srh').val('');

            filterTable();
        });
    });
</script>



