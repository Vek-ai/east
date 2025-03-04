<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);
require 'includes/dbconn.php';
require 'includes/functions.php';
$user_warehouse = "";
$staff_id = $_SESSION['userid'];
$query_warehouse = "SELECT * FROM warehouses WHERE corresponding_user = '$staff_id'";
$result_warehouse = mysqli_query($conn, $query_warehouse);            
while ($row_warehouse = mysqli_fetch_array($result_warehouse)) {
    $user_warehouse = $row_warehouse['WarehouseID'];
    $warehouse_name = $row_warehouse['WarehouseName'];
}
?>
<style>
    .select2-container {
        z-index: 9999 !important;
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
    <div class="card card-body">
        <div class="row">
        <div class="col-md-4 col-xl-3 align-middle">
            <h3 class="m-0"><a href="/?page=warehouse_details&warehouse_id=<?= $user_warehouse ?>"><?= $warehouse_name ?></a></h3>
            <!-- <form class="position-relative">
            <input type="text" class="form-control inventory-search ps-5" id="input-search" placeholder="Search Contacts..." />
            <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
            </form> -->
        </div>
        <div class="col-md-8 col-xl-9 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0">
            <div class="action-btn show-btn">
            <a href="javascript:void(0)" class="delete-multiple bg-danger-subtle btn me-2 text-danger d-flex align-items-center ">
                <i class="ti ti-trash me-1 fs-5"></i> Delete All Row
            </a>
            </div>
            <button type="button" id="addInventoryModalLabel" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                <i class="ti ti-users text-white me-1 fs-5"></i> Add Inventory
            </button>
        </div>
        </div>
    </div>

    <!-- Add Inventory -->
    <div class="modal fade" id="addInventoryModal" tabindex="-1" aria-labelledby="addInventoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        Add Inventory
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="add_inventory" class="form-horizontal">
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-body">
                            <input type="hidden" id="Inventory_id" name="Inventory_id" class="form-control"  />
                            <input type="hidden" id="Warehouse_id" name="Warehouse_id" class="form-control" value="<?= $user_warehouse ?>" />

                            <div class="row pt-3">
                            <div class="col-md-6">
                                <label class="form-label">Product</label>
                                <div class="mb-3">
                                <select id="Product_id" class="form-control select2-add" name="Product_id">
                                    <option value="" hidden>Select Product...</option>
                                    <optgroup label="Product">
                                        <?php
                                        $query_product = "SELECT * FROM product WHERE hidden = '0'";
                                        $result_product = mysqli_query($conn, $query_product);            
                                        while ($row_product = mysqli_fetch_array($result_product)) {
                                        ?>
                                            <option value="<?= $row_product['product_id'] ?>" ><?= $row_product['product_item'] ?></option>
                                        <?php
                                        }
                                        ?>
                                    </optgroup>
                                </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Supplier</label>
                                <div class="mb-3">
                                <select id="supplier_id" class="form-control select2-add" name="supplier_id">
                                    <option value="" >Select Supplier...</option>
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
                            </div>
                            </div>

                            <div class="row pt-3">
                            <div class="col-md-4">
                                <label class="form-label">Shelf</label>
                                <div class="mb-3">
                                <select id="Shelves_id" class="form-control select2-add" name="Shelves_id">
                                    <option value="" >Select Shelf...</option>
                                    <optgroup label="Shelf">
                                        <?php
                                        $query_shelf = "SELECT * 
                                                        FROM shelves s
                                                        INNER JOIN warehouse_rows wr ON s.WarehouseRowID = wr.WarehouseRowID
                                                        WHERE wr.WarehouseID = '$user_warehouse'";
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
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bin</label>
                                <div class="mb-3">
                                <select id="Bin_id" class="form-control select2-add" name="Bin_id">
                                    <option value="" >Select Bin...</option>
                                    <optgroup label="Bin">
                                        <?php
                                        $query_bin = "SELECT * FROM bins WHERE WarehouseID = '$user_warehouse'";
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
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Row</label>
                                <div class="mb-3">
                                <select id="Row_id" class="form-control select2-add" name="Row_id">
                                    <option value="" >Select Row...</option>
                                    <optgroup label="Row">
                                        <?php
                                        $query_rows = "SELECT * FROM warehouse_rows WHERE WarehouseID = '$user_warehouse'";
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
                            </div>
                            <div class="row pt-3">
                                <div class="col-md-4">
                                    <label class="form-label">Quantity</label>
                                    <input type="text" id="quantity_add" name="quantity" class="form-control"  />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Pack</label>
                                    <div class="mb-3">
                                    <select id="pack_add" class="form-control select2-add" name="pack">
                                        <option value="" >Select Pack...</option>
                                        <optgroup label="Pack">
                                            <?php
                                            $query_pack = "SELECT * FROM product_pack WHERE hidden = '0'";
                                            $result_pack = mysqli_query($conn, $query_pack);            
                                            while ($row_pack = mysqli_fetch_array($result_pack)) {
                                            ?>
                                                <option value="<?= $row_pack['id'] ?>" data-count="<?= $row_pack['pieces_count'] ?>" ><?= $row_pack['pack_name'] ?></option>
                                            <?php   
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Total Quantity</label>
                                    <input type="text" id="quantity_ttl_add" name="quantity_ttl" class="form-control"  />
                                </div>
                            </div>  
                            <div class="row pt-3">
                                <div class="col-md-6">
                                    <label class="form-label">Date</label>
                                    <input type="date" id="Date" name="Date" class="form-control"  />
                                </div>
                            </div>      
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-actions">
                            <div class="card-body">
                                <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                                <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- /.modal-content -->
        </div>
    <!-- /.modal-dialog -->
    </div>
    <!-- End of Add Inventory -->
     
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
        <div class="table-responsive">
        <h3 class="card-title d-flex justify-content-between align-items-center">
            Inventory List 
            <div class="px-3"> 
                <input type="checkbox" id="toggleActive" checked> Show New Only
            </div>
        </h3>
        <table id="inventoryList" class="table search-table align-middle text-nowrap">
            <thead class="header-item">
            <th>Product</th>
            <th>Date</th>
            <th>Quantity</th>
            <th>Total Quantity</th>
            <th>Added by</th>
            <th>Status</th>
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
                    $Warehouse_id = $row_inventory['Warehouse_id'];
                    $Shelves_id = $row_inventory['Shelves_id'];
                    $Bin_id = $row_inventory['Bin_id'];
                    $Row_id = $row_inventory['Row_id'];
                    $Date = $row_inventory['Date'];
                    $quantity = $row_inventory['quantity'];
                    $quantity_ttl = $row_inventory['quantity_ttl'];
                    $addedby = $row_inventory['addedby'];
                    $db_status = $row_inventory['status'];

                    if (trim($db_status) == '0') {
                        $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$Inventory_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-primary bg-primary text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>New</div></a>";
                    } else if (trim($db_status) == '1'){
                        $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$Inventory_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Transferred</div></a>";
                    }else{
                        $status = "";
                    }
   
                ?>
                    <!-- start row -->
                    <tr class="search-items">
                        <td><?= getProductName($Product_id) ?></td>
                        <td><?= $Date ?></td>
                        <td><?= $quantity ?></td>
                        <td><?= $quantity_ttl ?></td>
                        <td><?= get_name($addedby) ?></td>
                        <td><?= $status ?></td>
                        <td>
                            <div class="action-btn text-center">
                                <a href="#" id="view_inventory_btn" class="text-primary edit" data-id="<?= $Inventory_id ?>">
                                    <i class="ti ti-eye fs-5"></i>
                                </a>
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
                            url: 'pages/inventory_staff_ajax.php',
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

<script>
    $(document).ready(function() {
        var table = $('#inventoryList').DataTable({
            "order": [[1, "asc"]]
        });

        $(".select2-add").select2({
            dropdownParent: $('#addInventoryModal .modal-content'),
            placeholder: "Select One...",
            allowClear: true
        });
        
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var status = $(table.row(dataIndex).node()).find('a .alert').text().trim();
            var isActive = $('#toggleActive').is(':checked');

            console.log(status)

            if (!isActive || status === 'New') {
                return true;
            }
            return false;
        });

        $('#toggleActive').on('change', function() {
            table.draw();
        });

        $('#toggleActive').trigger('change');

        // Show the View Inventory modal and log the inventory ID
        $(document).on('click', '#view_inventory_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            var Warehouse_id = "<?= $user_warehouse ?>";
            $.ajax({
                    url: 'pages/inventory_staff_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        Warehouse_id, Warehouse_id,
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

        $(document).on('change', '#quantity_add, #pack_add', function(event) {
            var qty = parseFloat($('#quantity_add').val());
            var selectedOption = $('#pack_add').find('option:selected');
            var pack = selectedOption.length ? parseFloat(selectedOption.data('count')) : 1; // Default to 1 if no pack selected

            pack = isNaN(pack) ? 1 : pack;

            if (!isNaN(qty) && qty > 0) {
                $('#quantity_ttl_add').val(qty * pack);
            } else {
                $('#quantity_ttl_add').val('');
            }
        });

        $(document).on('change', '#quantity_update, #pack_update', function(event) {
            var qty = parseFloat($('#quantity_update').val());
            var selectedOption = $('#pack_update').find('option:selected');
            var pack = selectedOption.length ? parseFloat(selectedOption.data('count')) : 1; // Default to 1 if no pack selected

            pack = isNaN(pack) ? 1 : pack;

            if (!isNaN(qty) && qty > 0) {
                $('#quantity_ttl_update').val(qty * pack);
            } else {
                $('#quantity_ttl_update').val('');
            }
        });

        $(document).on('submit', '#update_inventory', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update');

            $.ajax({
                url: 'pages/inventory_staff_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log(response);
                    $('#updateInventoryModal').modal('hide');
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Inventory updated successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    } else {
                        $('#responseHeader').text("Failed");
                        $('#responseMsg').text(response);

                        $('#responseHeaderContainer').removeClass("bg-success");
                        $('#responseHeaderContainer').addClass("bg-danger");
                        $('#response-modal').modal("show");
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('submit', '#add_inventory', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update');
              
            $.ajax({
                url: 'pages/inventory_staff_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addInventoryModal').modal('hide');
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New inventory added successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    } else {
                        $('#responseHeader').text("Failed");
                        $('#responseMsg').text(response);

                        $('#responseHeaderContainer').removeClass("bg-success");
                        $('#responseHeaderContainer').addClass("bg-danger");
                        $('#response-modal').modal("show");
                    }

                    
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });


    });
</script>



