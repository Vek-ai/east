<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(!empty($_REQUEST['warehouse_id'])){
    $WarehouseID = $_REQUEST['warehouse_id'];
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
                                <a href="?page=warehouses" class="btn btn-primary" style="border-radius: 10%; ">Back</a>
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

                                <div class="datatables col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title d-flex justify-content-between align-items-center">List of Sections
                                                <a href="#" class="btn btn-primary addEditSectionBtn" style="border-radius: 10%;" data-id="" data-warehouse-id="<?=$WarehouseID?>">Add New</a>
                                            </h4>
                                            
                                            <div class="table-responsive">
                                        
                                                <table id="row_wh_sections" class="table table-striped table-bordered text-nowrap align-middle">
                                                    <thead>
                                                        <tr>
                                                            <th>Section Name</th>
                                                            <th>Row Code</th>
                                                            <th>Shelf Code</th>
                                                            <th>Bin Code</th>
                                                            <th>Description</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $query_wh_section ="SELECT 
                                                                                ws.*, 
                                                                                wr.RowCode, 
                                                                                s.ShelfCode, 
                                                                                b.BinCode 
                                                                            FROM warehouse_section ws
                                                                            LEFT JOIN warehouse_rows wr ON ws.WarehouseRowID = wr.WarehouseRowID
                                                                            LEFT JOIN shelves s ON ws.ShelfID = s.ShelfID
                                                                            LEFT JOIN bins b ON ws.BinID = b.BinID
                                                                            WHERE 
                                                                                ws.WarehouseID = '$WarehouseID' AND ws.hidden = '0'";
                                                        $result_wh_section = mysqli_query($conn, $query_wh_section);   
                                                        while ($row_wh_section = mysqli_fetch_array($result_wh_section)) {
                                                        ?>
                                                            <tr>
                                                                <td>
                                                                    <?= $row_wh_section['section_name'] ?>
                                                                </td>
                                                                <td>
                                                                    <?= $row_wh_section['RowCode'] ?>
                                                                </td>
                                                                <td>
                                                                    <?= $row_wh_section['ShelfCode'] ?>
                                                                </td>
                                                                <td>
                                                                    <?= $row_wh_section['BinCode'] ?>
                                                                </td>
                                                                <td><?= $row_wh_section['Description'] ?></td>
                                                                <td>
                                                                    <div class="action-btn text-center">
                                                                        <a href="#" id="section-edit" 
                                                                                    class="text-primary addEditSectionBtn" 
                                                                                    data-id="<?= $row_wh_section['id'] ?>"
                                                                                    title="Edit"
                                                                                    data-warehouse-id="<?= $WarehouseID ?>">
                                                                            <i class="text-warning ti ti-pencil fs-7"></i>
                                                                        </a>
                                                                        <a href="#" id="section-delete" class="text-danger" title="Archive" data-id="<?= $row_wh_section['id'] ?>">
                                                                            <i class="text-danger ti ti-trash fs-7"></i>
                                                                        </a>
                                                                    </div>
                                                                </td>
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

                                <div class="datatables col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title d-flex justify-content-between align-items-center">List of Rows  
                                                <a href="#" class="btn btn-primary addEditRowBtn" style="border-radius: 10%;" data-id="" data-warehouse-id="<?=$WarehouseID?>">Add New</a>
                                            </h4>
                                            
                                            <div class="table-responsive">
                                        
                                                <table id="row_wh_rows" class="table table-striped table-bordered text-nowrap align-middle">
                                                    <thead>
                                                    <!-- start row -->
                                                    <tr>
                                                        <th>RowCode</th>
                                                        <th>Description</th>
                                                        <th>Action</th>
                                                    </tr>
                                                    <!-- end row -->
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $query_wh_rows = "SELECT * FROM warehouse_rows WHERE WarehouseID = '$WarehouseID' AND hidden = '0'";
                                                        $result_wh_rows = mysqli_query($conn, $query_wh_rows);   
                                                        while ($row_wh_rows = mysqli_fetch_array($result_wh_rows)) {
                                                        ?>
                                                            <tr>
                                                                <td>
                                                                    <?= $row_wh_rows['RowCode'] ?></td>
                                                                <td><?= $row_wh_rows['Description'] ?></td>
                                                                <td>
                                                                    <div class="action-btn text-center">
                                                                        <a href="#" id="row-item" data-id="<?= $row_wh_rows['WarehouseRowID'] ?>">
                                                                            <i class="text-primary ti ti-eye fs-7"></i>
                                                                        </a>
                                                                        <a href="#" id="row-edit" 
                                                                                    class="text-primary addEditRowBtn" 
                                                                                    data-id="<?= $row_wh_rows['WarehouseRowID'] ?>"
                                                                                    title="Edit"
                                                                                    data-warehouse-id="<?= $WarehouseID ?>">
                                                                            <i class="text-warning ti ti-pencil fs-7"></i>
                                                                        </a>
                                                                        <a href="#" id="row-delete" class="text-danger" title="Archive" data-id="<?= $row_wh_rows['WarehouseRowID'] ?>">
                                                                            <i class="text-danger ti ti-trash fs-7"></i>
                                                                        </a>
                                                                    </div>
                                                                </td>
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

                                <div class="datatables col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title d-flex justify-content-between align-items-center">List of Shelves  
                                                <a href="#" class="btn btn-primary addEditShelfBtn" style="border-radius: 10%;" data-id="" data-warehouse-id="<?=$WarehouseID?>">Add New</a>
                                            </h4>
                                            
                                            <div class="table-responsive">
                                        
                                                <table id="row_wh_shelves" class="table table-striped table-bordered text-nowrap align-middle">
                                                    <thead>
                                                    <!-- start row -->
                                                    <tr>
                                                        <th>Shelf Code</th>
                                                        <th>Row Code</th>
                                                        <th>Description</th>
                                                        <th>Action</th>
                                                    </tr>
                                                    <!-- end row -->
                                                    </thead>
                                                    <tbody>
                                                    <?php
                                                        $query_wh_shelves = "
                                                            SELECT s.* 
                                                            FROM shelves s
                                                            INNER JOIN warehouse_rows wr ON s.WarehouseRowID = wr.WarehouseRowID
                                                            WHERE wr.WarehouseID = '$WarehouseID' AND s.hidden = '0'
                                                        ";
                                                        $result_wh_shelves = mysqli_query($conn, $query_wh_shelves);

                                                        if ($result_wh_shelves) {
                                                            while ($row_wh_shelves = mysqli_fetch_array($result_wh_shelves)) {
                                                                ?>
                                                                <tr>
                                                                    <td>
                                                                        <?= $row_wh_shelves['ShelfCode'] ?></td>  
                                                                    <td><?= getWarehouseRowName($row_wh_shelves['WarehouseRowID']) ?></td>
                                                                    <td><?= $row_wh_shelves['Description'] ?></td>
                                                                    <td>
                                                                        <div class="action-btn text-center">
                                                                            <a href="#" id="shelf-item" data-id="<?= $row_wh_shelves['ShelfID'] ?>">
                                                                                <i class="text-primary ti ti-eye fs-7"></i>
                                                                            </a>
                                                                            <a href="#" id="shelf-edit" 
                                                                                        class="text-primary addEditShelfBtn" 
                                                                                        data-id="<?= $row_wh_shelves['ShelfID'] ?>"
                                                                                        title="Edit"
                                                                                        data-warehouse-id="<?= $WarehouseID ?>">
                                                                                <i class="text-warning ti ti-pencil fs-7"></i>
                                                                            </a>
                                                                            <a href="#" id="shelf-delete" class="text-danger" title="Archive" data-id="<?= $row_wh_shelves['ShelfID'] ?>">
                                                                                <i class="text-danger ti ti-trash fs-7"></i>
                                                                            </a>
                                                                        </div>
                                                                    </td>
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

                                <div class="datatables col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title d-flex justify-content-between align-items-center">List of Bins  
                                                <a href="#" class="btn btn-primary addEditBinBtn" style="border-radius: 10%;" data-id="" data-warehouse-id="<?=$WarehouseID?>">Add New</a>
                                            </h4>
                                            
                                            <div class="table-responsive">
                                        
                                                <table id="row_wh_bins" class="table table-striped table-bordered text-nowrap align-middle">
                                                    <thead>
                                                    <!-- start row -->
                                                    <tr>
                                                        <th>Bin Code</th>
                                                        <th>Description</th>
                                                        <th>Action</th>
                                                    </tr>
                                                    <!-- end row -->
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $query_wh_bins = "SELECT * FROM bins WHERE WarehouseID = '$WarehouseID' AND hidden = '0'";
                                                        $result_wh_bins = mysqli_query($conn, $query_wh_bins);            
                                                        while ($row_wh_bins = mysqli_fetch_array($result_wh_bins)) {
                                                        ?>
                                                            <tr>
                                                                <td>
                                                                    <?= $row_wh_bins['BinCode'] ?>
                                                                </td>
                                                                <td><?= $row_wh_bins['Description'] ?></td>
                                                                <td>
                                                                    <div class="action-btn text-center">
                                                                        <a href="#" id="bin-item" data-id="<?= $row_wh_bins['BinID'] ?>">
                                                                            <i class="text-primary ti ti-eye fs-7"></i>
                                                                        </a>
                                                                        <a href="#" id="bin-edit" 
                                                                                    class="text-primary addEditBinBtn" 
                                                                                    data-id="<?= $row_wh_bins['BinID'] ?>"
                                                                                    title="Edit"
                                                                                    data-warehouse-id="<?= $WarehouseID ?>">
                                                                            <i class="text-warning ti ti-pencil fs-7"></i>
                                                                        </a>
                                                                        <a href="#" id="bin-delete" class="text-danger" title="Archive" data-id="<?= $row_wh_bins['BinID'] ?>">
                                                                            <i class="text-danger ti ti-trash fs-7"></i>
                                                                        </a>
                                                                    </div>
                                                                </td>
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

                                <!-- Tables -->
                                <div class="row">
                                    

                                    <div class="modal fade" id="addSectionModal" tabindex="-1" aria-labelledby="addSectionModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header d-flex align-items-center">
                                                    <h4 class="modal-title" id="myLargeModalLabel">Add Section</h4>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form id="add_section" class="form-horizontal">
                                                    <div id="section-div" class="modal-body">
                                                        
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

                                    <div class="modal fade" id="updateSectionModal" tabindex="-1" aria-labelledby="updateSectionModalLabel" aria-hidden="true"></div>

                                    <div class="modal fade" id="addBinModal" tabindex="-1" aria-labelledby="addBinModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header d-flex align-items-center">
                                                    <h4 class="modal-title" id="myLargeModalLabel">Add Bin</h4>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form id="add_bin" class="form-horizontal">
                                                    <div id="bin-section" class="modal-body">
                                                        
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
                                                    <div id="row-section" class="modal-body">
                                                        
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
                                                    <div id="shelf-section" class="modal-body">
                                                        
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

        $('#row_wh_sections').DataTable();
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

        $(document).on('click', '.addEditSectionBtn', function(event) {
            event.preventDefault(); 

            var id = $(this).data('id');
            var warehouse_id = $(this).data('warehouse-id');

            $.ajax({
                url: 'pages/warehouse_ajax_details.php',
                type: 'POST',
                data: {
                    id: id,
                    warehouse_id: warehouse_id,
                    action: 'add_edit_section'
                },
                success: function(response) {
                    $('#section-div').html(response);
                    $('#addSectionModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '.addEditRowBtn', function(event) {
            event.preventDefault(); 

            var row_id = $(this).data('id');
            var warehouse_id = $(this).data('warehouse-id');

            $.ajax({
                url: 'pages/warehouse_ajax_details.php',
                type: 'POST',
                data: {
                    row_id: row_id,
                    warehouse_id: warehouse_id,
                    action: 'add_edit_row'
                },
                success: function(response) {
                    $('#row-section').html(response);
                    $('#addRowModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '.addEditShelfBtn', function(event) {
            event.preventDefault(); 

            var shelf_id = $(this).data('id');
            var warehouse_id = $(this).data('warehouse-id');

            $.ajax({
                url: 'pages/warehouse_ajax_details.php',
                type: 'POST',
                data: {
                    shelf_id: shelf_id,
                    warehouse_id: warehouse_id,
                    action: 'add_edit_shelf'
                },
                success: function(response) {
                    $('#shelf-section').html(response);
                    $('#addShelfModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '.addEditBinBtn', function(event) {
            event.preventDefault(); 

            var bin_id = $(this).data('id');
            var warehouse_id = $(this).data('warehouse-id');

            $.ajax({
                url: 'pages/warehouse_ajax_details.php',
                type: 'POST',
                data: {
                    bin_id: bin_id,
                    warehouse_id: warehouse_id,
                    action: 'add_edit_bin'
                },
                success: function(response) {
                    $('#bin-section').html(response);
                    $('#addBinModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('submit', '#add_section', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update_section');

            $.ajax({
                url: 'pages/warehouse_ajax_details.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addRowModal').modal('hide');
                    if (response.trim() === "success_add") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New section added successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");
                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    }else if (response.trim() === "success_update") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Section updated successfully.");
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
                    if (response.trim() === "success_add") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New bin added successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");
                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    }else if (response.trim() === "success_update") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Bin updated successfully.");
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
                    if (response.trim() === "success_add") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New row added successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");
                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    }else if (response.trim() === "success_update") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Row updated successfully.");
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
                    if (response.trim() === "success_add") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New shelf added successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");
                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    }else if (response.trim() === "success_update") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Shelf updated successfully.");
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

        $(document).on('click', '#section-delete', function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            var confirmDelete = confirm("Are you sure you want to delete this section?");
            
            if (confirmDelete) {
                $.ajax({
                    url: 'pages/warehouse_ajax_details.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: 'section_delete'
                    },
                    success: function(response) {
                        if (response.trim() === "success") {
                            $('#responseHeader').text("Success");
                            $('#responseMsg').text("Successfully Deleted Section.");
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
            }
        });

        $(document).on('click', '#row-delete', function(event) {
            event.preventDefault();
            var row_id = $(this).data('id');
            var confirmDelete = confirm("Are you sure you want to delete this row?");
            
            if (confirmDelete) {
                $.ajax({
                    url: 'pages/warehouse_ajax_details.php',
                    type: 'POST',
                    data: {
                        row_id: row_id,
                        action: 'row_delete'
                    },
                    success: function(response) {
                        if (response.trim() === "success") {
                            $('#responseHeader').text("Success");
                            $('#responseMsg').text("Successfully Deleted Row.");
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
            }
        });

        $(document).on('click', '#shelf-delete', function(event) {
            event.preventDefault();
            var shelf_id = $(this).data('id');
            var confirmDelete = confirm("Are you sure you want to delete this shelf?");
            
            if (confirmDelete) {
                $.ajax({
                    url: 'pages/warehouse_ajax_details.php',
                    type: 'POST',
                    data: {
                        shelf_id: shelf_id,
                        action: 'shelf_delete'
                    },
                    success: function(response) {
                        if (response.trim() === "success") {
                            $('#responseHeader').text("Success");
                            $('#responseMsg').text("Successfully Deleted Shelf.");
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
            }
        });

        $(document).on('click', '#bin-delete', function(event) {
            event.preventDefault();
            var bin_id = $(this).data('id');
            var confirmDelete = confirm("Are you sure you want to delete this bin?");
            
            if (confirmDelete) {
                $.ajax({
                    url: 'pages/warehouse_ajax_details.php',
                    type: 'POST',
                    data: {
                        bin_id: bin_id,
                        action: 'bin_delete'
                    },
                    success: function(response) {
                        if (response.trim() === "success") {
                            $('#responseHeader').text("Success");
                            $('#responseMsg').text("Successfully Deleted Bin.");
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
            }
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



