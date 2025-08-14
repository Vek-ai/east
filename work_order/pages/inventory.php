<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);
require '../includes/dbconn.php';
require '../includes/functions.php';
$permission = $_SESSION['permission'];
?>
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
    <div class="modal fade" id="updateInventoryModal" tabindex="-1" role="dialog" aria-labelledby="updateInventoryModal" aria-hidden="true">
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
                        <select class="form-control search-category py-0 ps-5 select2-filter filter-selection" id="select-inventory" data-filter="inventory" data-filter-name="Inventory Type">
                            <option value="" data-category="">All Inventory Types</option>
                            <optgroup label="Inventory Types">
                                <option value="Coils">Coils</option>
                                <option value="Flat-Stock">Flat-Stock</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select id="color_filter" class="form-control color-cart select2-filter filter-selection" name="color_id" data-filter="color" data-filter-name="Color">
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
                        <select id="supplier_filter" class="form-control select2-filter filter-selection" name="supplier_id" data-filter="supplier" data-filter-name="Supplier">
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
                        <select id="warehouse_filter" class="form-control select2-filter filter-selection" name="Warehouse_id" data-filter="warehouse" data-filter-name="Warehouse">
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
                        <select id="shelves_filter" class="form-control select2-filter filter-selection" name="Shelves_id" data-filter="shelf" data-filter-name="Shelf">
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
                        <select id="bin_filter" class="form-control select2-filter filter-selection" name="Bin_id" data-filter="bin" data-filter-name="Bin">
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
                        <select id="row_filter" class="form-control select2-filter filter-selection" name="Row_id" data-filter="row" data-filter-name="Row">
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
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th>Date</th>
                        <th>Quantity</th>
                        <th>Added by</th>
                        <th>Action</th>
                        </thead>
                        <tbody>
                        <?php
                            $no = 1;
                            $query_inventory = "SELECT * FROM inventory";
                            $result_inventory = mysqli_query($conn, $query_inventory);            
                            while ($row_inventory = mysqli_fetch_array($result_inventory)) {
                                $Inventory_id = $row_inventory['Inventory_id'];
                                $Product_id = $row_inventory['Product_id'];
                                $product_details = getProductDetails($Product_id);
                                $Warehouse_id = $row_inventory['Warehouse_id'];
                                $Shelves_id = $row_inventory['Shelves_id'];
                                $Bin_id = $row_inventory['Bin_id'];
                                $Row_id = $row_inventory['Row_id'];
                                $Date = $row_inventory['Date'];
                                $quantity_ttl = $row_inventory['quantity_ttl'];
                                $addedby = $row_inventory['addedby'];
                                $db_status = $row_inventory['status'];
                                $inventory = '';

                                $picture_path = !empty($product_details['main_image']) ? $product_details['main_image'] : "../images/product/product.jpg";
                            ?>
                                <!-- start row -->
                                <tr class="search-items"
                                    data-color="<?= $row_inventory['color_id'] ?? 0 ?>"
                                    data-supplier="<?= $row_inventory['supplier_id'] ?? 0 ?>"
                                    data-warehouse="<?= $row_inventory['Warehouse_id'] ?? 0 ?>"
                                    data-shelf="<?= $row_inventory['Shelves_id'] ?? 0 ?>"
                                    data-bin="<?= $row_inventory['Bin_id'] ?? 0 ?>"
                                    data-row="<?= $row_inventory['Row_id'] ?? 0 ?>"
                                    data-new="<?= $row_inventory['status'] == 0 ? 1 : 0 ?>"
                                    data-inventory="<?= $inventory ?>"
                                >
                                    <td>
                                        <a href="javascript:void(0)">
                                            <div class="d-flex align-items-center">
                                                <img src="<?= $picture_path ?>" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                                                <div class="ms-3">
                                                    <h6 class="fw-semibold mb-0 fs-4"><?= $product_details['product_item'] ?></h6>
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                    <td><?= getWarehouseName($Warehouse_id) ?></td>
                                    <td><?= $Date ?></td>
                                    <td><?= $quantity_ttl ?></td>
                                    <td><?= get_name($addedby) ?></td>
                                    <td>
                                        <div class="action-btn text-center">
                                            <?php                                                    
                                            if ($permission === 'edit') {
                                            ?>
                                            <a href="#" id="view_inventory_btn" title="View" class="text-primary edit" data-id="<?= $Inventory_id ?>">
                                                <i class="ti ti-eye fs-5"></i>
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
    
    $(document).ready(function() {
        $(".select2-filter").each(function() {
            $(this).select2({
                dropdownParent: $(this).parent()
            });
        });

        $(document).on('click', '#view_inventory_btn', function(event) {
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
                        $('#updateInventoryModal').html(response);
                        $('#updateInventoryModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        var table = $('#inventoryList').DataTable({
            "order": [[1, "asc"]],
            "dom": 'ltp'
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



