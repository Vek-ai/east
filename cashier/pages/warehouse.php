<?php
session_start();

require '../../includes/dbconn.php';
require '../../includes/functions.php';


$staff_id = $_SESSION['userid'];
$warehouse = "";

$checkQuery = "SELECT WarehouseID FROM warehouses WHERE corresponding_user = '$staff_id'";
$result = mysqli_query($conn, $checkQuery);

if ($result && mysqli_num_rows($result) > 0) {  
  $row = mysqli_fetch_assoc($result);
  $warehouse = $row['WarehouseID'];
}

if(!empty($warehouse)){
    $WarehouseID = $warehouse;
    $query = "SELECT * FROM warehouses WHERE WarehouseID = '$WarehouseID'";
    $result = mysqli_query($conn, $query);            
    while ($row = mysqli_fetch_array($result)) {
        $WarehouseID = $row['WarehouseID'];
        $WarehouseName = $row['WarehouseName'];
        $Location = $row['Location'];
        $contact_person = $row['contact_person'];
        $contact_phone = $row['contact_phone'];
        $contact_email = $row['contact_email'];
    }
  }
?>

<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"> Warehouse Details</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a class="text-muted text-decoration-none" href="/">
                        Home
                    </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">
                    <a class="text-muted text-decoration-none" href="/?page=warehouses">
                        Warehouses
                    </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Warehouse Details</li>
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
    <!-- layout here -->
        <div class="col-12 ">
            <div class="card">
                <div class="card-body">
                    
                    <div class="row">
                        <div class="col-12">
                            <h4 class="card-title d-flex justify-content-between align-items-center py-0 m-0">  
                                <a href="/?page=warehouses" class="btn btn-primary" style="border-radius: 10%; ">Back</a>
                            </h4>
                        </div>
                        <div class="col">
                            <div class="card-body">
                                <!-- Warehouse Details -->
                                <div class="row">
                                    <div class="col">
                                        <div class="card">
                                            <div class="card-body">
                                                
                                                <h3 ><?= $WarehouseName ?></h3>
                                                <h5 ><?= $Location ?></h5>

                                                <div class="row mt-5">
                                                    <div class="col-4 mb-7">
                                                        <label class="form-label">Contact Person</label>
                                                        <p id="contact_person"><?= $contact_person ?></p>
                                                    </div>
                                                    <div class="col-4 mb-7">
                                                        <label class="form-label">Contact Phone</label>
                                                        <p id="contact_phone"><?= $contact_phone ?></p>
                                                    </div>
                                                    <div class="col-4 mb-7">
                                                        <label class="form-label">Contact Email</label>
                                                        <p id="contact_email"><?= $contact_email ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tables -->
                                <div class="row">
                                    <div class="datatables col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title d-flex justify-content-between align-items-center">List of Bins  
                                                    <a href="#" class="btn btn-primary" style="border-radius: 10%; " data-bs-toggle="modal" data-bs-target="#addBinModal">Add New</a>
                                                </h4>
                                                
                                                <div class="table-responsive">
                                            
                                                    <table id="row_wh_bins" class="table table-striped table-bordered text-nowrap align-middle">
                                                        <thead>
                                                        <!-- start row -->
                                                        <tr>
                                                            <th>Bin Code</th>
                                                            <th>Description</th>
                                                        </tr>
                                                        <!-- end row -->
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $query_wh_bins = "SELECT * FROM bins WHERE WarehouseID = '$WarehouseID'";
                                                            $result_wh_bins = mysqli_query($conn, $query_wh_bins);            
                                                            while ($row_wh_bins = mysqli_fetch_array($result_wh_bins)) {
                                                            ?>
                                                                <tr>
                                                                    <td>
                                                                        <a href="#" id="bin-item" data-id="<?= $row_wh_bins['BinID'] ?>">
                                                                            <?= $row_wh_bins['BinCode'] ?>
                                                                        </a>
                                                                    </td>
                                                                    <td><?= $row_wh_bins['Description'] ?></td>
                                                                </tr>
                                                            <?php
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="datatables col-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title d-flex justify-content-between align-items-center">List of Rows  
                                                    <a href="#" class="btn btn-primary" style="border-radius: 10%;" data-bs-toggle="modal" data-bs-target="#addRowModal">Add New</a>
                                                </h4>
                                                
                                                <div class="table-responsive">
                                            
                                                    <table id="row_wh_rows" class="table table-striped table-bordered text-nowrap align-middle">
                                                        <thead>
                                                        <!-- start row -->
                                                        <tr>
                                                            <th>RowCode</th>
                                                            <th>Description</th>
                                                        </tr>
                                                        <!-- end row -->
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $query_wh_rows = "SELECT * FROM warehouse_rows WHERE WarehouseID = '$WarehouseID'";
                                                            $result_wh_rows = mysqli_query($conn, $query_wh_rows);   
                                                            while ($row_wh_rows = mysqli_fetch_array($result_wh_rows)) {
                                                            ?>
                                                                <tr>
                                                                    <td>
                                                                        <a href="#" id="row-item" data-id="<?= $row_wh_rows['WarehouseRowID'] ?>">
                                                                            <?= $row_wh_rows['RowCode'] ?></td>
                                                                        </a>
                                                                    <td><?= $row_wh_rows['Description'] ?></td>
                                                                </tr>
                                                            <?php
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="datatables col-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title d-flex justify-content-between align-items-center">List of Shelves  
                                                    <a href="#" class="btn btn-primary" style="border-radius: 10%;" data-bs-toggle="modal" data-bs-target="#addShelfModal">Add New</a>
                                                </h4>
                                                
                                                <div class="table-responsive">
                                            
                                                    <table id="row_wh_shelves" class="table table-striped table-bordered text-nowrap align-middle">
                                                        <thead>
                                                        <!-- start row -->
                                                        <tr>
                                                            <th>Shelf Code</th>
                                                            <th>Row Code</th>
                                                            <th>Description</th>
                                                        </tr>
                                                        <!-- end row -->
                                                        </thead>
                                                        <tbody>
                                                        <?php
                                                            $query_wh_shelves = "
                                                                SELECT s.* 
                                                                FROM shelves s
                                                                INNER JOIN warehouse_rows wr ON s.WarehouseRowID = wr.WarehouseRowID
                                                                WHERE wr.WarehouseID = '$WarehouseID'
                                                            ";
                                                            $result_wh_shelves = mysqli_query($conn, $query_wh_shelves);

                                                            if ($result_wh_shelves) {
                                                                while ($row_wh_shelves = mysqli_fetch_array($result_wh_shelves)) {
                                                                    ?>
                                                                    <tr>
                                                                        <td>
                                                                            <a href="#" id="shelf-item" data-id="<?= $row_wh_shelves['ShelfID'] ?>">
                                                                                <?= $row_wh_shelves['ShelfCode'] ?></td>
                                                                            </a>    
                                                                        <td><?= getWarehouseRowName($row_wh_shelves['WarehouseRowID']) ?></td>
                                                                        <td><?= $row_wh_shelves['Description'] ?></td>
                                                                    </tr>
                                                                    <?php
                                                                }
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="addBinModal" tabindex="-1" aria-labelledby="addBinModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header d-flex align-items-center">
                                                    <h4 class="modal-title" id="myLargeModalLabel">Add Bin</h4>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form id="add_bin" class="form-horizontal">
                                                    <div class="modal-body">
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <input type="hidden" id="BinID" name="BinID" class="form-control"/>
                                                                <input type="hidden" id="WarehouseID" name="WarehouseID" class="form-control" value="<?= $WarehouseID ?>" />

                                                                <div class="row pt-3">
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Bin Code</label>
                                                                            <input type="text" id="BinCode" name="BinCode" class="form-control" />
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row pt-3">
                                                                    <div class="col-md-12">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Description</label>
                                                                            <textarea class="form-control" id="Description" name="Description" rows="5"></textarea>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <div class="form-actions">
                                                            <div class="card-body">
                                                                <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                                                                <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <!-- /.modal-content -->
                                        </div>
                                        <!-- /.modal-dialog -->
                                    </div>

                                    <div class="modal fade" id="updateBinModal" tabindex="-1" aria-labelledby="updateBinModalLabel" aria-hidden="true"></div>

                                    <div class="modal fade" id="addRowModal" tabindex="-1" aria-labelledby="addRowModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header d-flex align-items-center">
                                                    <h4 class="modal-title" id="myLargeModalLabel">Add Row</h4>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form id="add_row" class="form-horizontal">
                                                    <div class="modal-body">
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <input type="hidden" id="WarehouseRowID" name="WarehouseRowID" class="form-control" />
                                                                <input type="hidden" id="WarehouseID" name="WarehouseID" class="form-control" value="<?= $WarehouseID ?>"/>

                                                                <div class="row pt-3">
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Row Code</label>
                                                                            <input type="text" id="RowCode" name="RowCode" class="form-control" />
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row pt-3">
                                                                    <div class="col-md-12">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Description</label>
                                                                            <textarea class="form-control" id="Description" name="Description" rows="5"></textarea>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <div class="form-actions">
                                                            <div class="card-body">
                                                                <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                                                                <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <!-- /.modal-content -->
                                        </div>
                                        <!-- /.modal-dialog -->
                                    </div>

                                    <div class="modal fade" id="updateRowModal" tabindex="-1" aria-labelledby="updateRowModalLabel" aria-hidden="true"></div>

                                    <div class="modal fade" id="addShelfModal" tabindex="-1" aria-labelledby="addShelfModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header d-flex align-items-center">
                                                    <h4 class="modal-title" id="myLargeModalLabel">Add Shelf</h4>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form id="add_shelf" class="form-horizontal">
                                                    <div class="modal-body">
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <input type="hidden" id="ShelfID" name="ShelfID" class="form-control"/>
                                                                <input type="hidden" id="WarehouseID" name="WarehouseID" class="form-control" value="<?= $WarehouseID ?>"/>

                                                                <div class="row pt-3">
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Shelf Code</label>
                                                                            <input type="text" id="ShelfCode" name="ShelfCode" class="form-control" required/>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Row Code</label>
                                                                            <select id="WarehouseRowID" class="form-control" name="WarehouseRowID" required>
                                                                                <option value="/" >Select One...</option>
                                                                                <?php
                                                                                $query_rows = "SELECT * FROM warehouse_rows WHERE WarehouseID = '" .$WarehouseID ."'";

                                                                                $result_rows = mysqli_query($conn, $query_rows);            
                                                                                while ($row_rows = mysqli_fetch_array($result_rows)) {
                                                                                ?>
                                                                                    <option value="<?= $row_rows['WarehouseRowID'] ?>" ><?= $row_rows['RowCode'] ?></option>
                                                                                <?php   
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row pt-3">
                                                                    <div class="col-md-12">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Description</label>
                                                                            <textarea class="form-control" id="Description" name="Description" rows="5"></textarea>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <div class="form-actions">
                                                            <div class="card-body">
                                                                <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                                                                <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <!-- /.modal-content -->
                                        </div>
                                        <!-- /.modal-dialog -->
                                    </div>

                                    <div class="modal fade" id="updateShelfModal" tabindex="-1" aria-labelledby="updateShelfModalLabel" aria-hidden="true"></div>

                                    <div class="modal fade" id="viewBinModal" tabindex="-1" aria-labelledby="viewBinModalLabel" aria-hidden="true"></div>

                                    <div class="modal fade" id="viewRowModal" tabindex="-1" aria-labelledby="viewRowModalLabel" aria-hidden="true"></div>

                                    <div class="modal fade" id="viewShelfModal" tabindex="-1" aria-labelledby="viewShelfModalLabel" aria-hidden="true"></div>

                                    <div class="modal fade" id="transferInventoryModal" tabindex="-1" aria-labelledby="transferInventoryModalLabel" aria-hidden="true"></div> 
                                    
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
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
            </div>
        </div>
    </div>
</div>

<script>
    function getUrlParameter(name) {
        const url = new URL(window.location.href);
        const params = new URLSearchParams(url.search);
        return params.get(name);
    }

    $(document).ready(function() {
        var LastModal;
        $('.modal').on('hidden.bs.modal', (e) => {
            LastModal = $(e.target).attr('id'); 
        });

        $(document).on('click', '#btn-reopen-modal', function(event) {
            $('.modal.show').modal('hide');
            $('#'+LastModal).modal('show');
        });

        $(document).on('click', '.transferInventory', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/warehouse_ajax_details.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_modal_transfer"
                    },
                    success: function(response) {
                        $('.modal.show').modal('hide');
                        $('#transferInventoryModal').html(response);
                        $(".select2-add").select2({
                            dropdownParent: $('#transferInventoryModal .modal-content')
                        });
                        $('#transferInventoryModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $('#row_wh_bins').DataTable();
        $('#row_wh_rows').DataTable();
        $('#row_wh_shelves').DataTable();

        $(document).on('submit', '#transfer_inventory', function(event) {
            event.preventDefault();

            var formData = new FormData(this);
            formData.append('action', 'transfer_product');
              
            $.ajax({
                url: 'pages/warehouse_ajax_details.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log(response)
                    $('#transferInventoryModal').modal('hide');
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Successfully transferred product.");
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

        $(document).on('submit', '#add_bin', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update_bin');

            $.ajax({
                url: 'pages/warehouse_ajax_details.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addBinModal').modal('hide');
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New bin added successfully.");
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

        $(document).on('submit', '#add_row', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update_row');

            $.ajax({
                url: 'pages/warehouse_ajax_details.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addRowModal').modal('hide');
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New row added successfully.");
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

        $(document).on('submit', '#add_shelf', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update_shelf');

            $.ajax({
                url: 'pages/warehouse_ajax_details.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addShelfModal').modal('hide');
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New shelf added successfully.");
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

        $(document).on('click', '#bin-item', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/warehouse_ajax_details.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_modal_bin"
                    },
                    success: function(response) {
                        $('#viewBinModal').html(response);
                        $('#tbl-bin-products').DataTable();
                        $('#viewBinModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $(document).on('click', '#row-item', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/warehouse_ajax_details.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_modal_row"
                    },
                    success: function(response) {
                        $('#viewRowModal').html(response);
                        $('#tbl-row-products').DataTable();
                        $('#viewRowModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $(document).on('click', '#shelf-item', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/warehouse_ajax_details.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_modal_shelf"
                    },
                    success: function(response) {
                        $('#viewShelfModal').html(response);
                        $('#tbl-shelf-products').DataTable();
                        $('#viewShelfModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });
    });
</script>



