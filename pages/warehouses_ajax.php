<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $WarehouseID = mysqli_real_escape_string($conn, $_POST['WarehouseID']);
        $WarehouseName = mysqli_real_escape_string($conn, $_POST['WarehouseName']);
        $Location = mysqli_real_escape_string($conn, $_POST['Location']);
        $contact_person = mysqli_real_escape_string($conn, $_POST['contact_person']);
        $contact_phone = mysqli_real_escape_string($conn, $_POST['contact_phone']);
        $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email']);
    
        $checkQuery = "SELECT * FROM warehouses WHERE WarehouseID = '$WarehouseID'";
        $result = mysqli_query($conn, $checkQuery);
    
        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "
                UPDATE warehouses 
                SET 
                    WarehouseName = '$WarehouseName', 
                    Location = '$Location', 
                    contact_person = '$contact_person', 
                    contact_phone = '$contact_phone', 
                    contact_email = '$contact_email'
                WHERE WarehouseID = '$WarehouseID'
            ";
    
            if (mysqli_query($conn, $updateQuery)) {
                echo "success";
            } else {
                echo "Error updating warehouse: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "
                INSERT INTO warehouses (
                    WarehouseID,
                    WarehouseName, 
                    Location, 
                    contact_person, 
                    contact_phone, 
                    contact_email
                ) VALUES (
                    '$WarehouseID', 
                    '$WarehouseName', 
                    '$Location', 
                    '$contact_person', 
                    '$contact_phone', 
                    '$contact_email'
                )
            ";
    
            if (mysqli_query($conn, $insertQuery)) {
                echo "success";
            } else {
                echo "Error adding warehouse: " . mysqli_error($conn);
            }
        }
    }

    if ($action == "add_update_bin") {
        $BinID = mysqli_real_escape_string($conn, $_POST['BinID']);
        $WarehouseID = mysqli_real_escape_string($conn, $_POST['WarehouseID']);
        $BinCode = mysqli_real_escape_string($conn, $_POST['BinCode']);
        $Description = mysqli_real_escape_string($conn, $_POST['Description']);
    
        $checkQuery = "SELECT * FROM bins WHERE BinID = '$BinID'";
        $result = mysqli_query($conn, $checkQuery);
    
        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "
                UPDATE bins 
                SET 
                    BinCode = '$BinCode', 
                    Description = '$Description'
                WHERE BinID = '$BinID'
            ";
    
            if (mysqli_query($conn, $updateQuery)) {
                echo "$updateQuery";
            } else {
                echo "Error updating warehouse: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "
                INSERT INTO bins (
                    BinCode,
                    WarehouseID,
                    Description
                ) VALUES (
                    '$BinCode', 
                    '$WarehouseID', 
                    '$Description'
                )
            ";
    
            if (mysqli_query($conn, $insertQuery)) {
                echo "$insertQuery";
            } else {
                echo "Error adding warehouse: " . mysqli_error($conn);
            }
        }
    }

    if ($action == "add_update_row") {
        $WarehouseRowID = mysqli_real_escape_string($conn, $_POST['WarehouseRowID']);
        $WarehouseID = mysqli_real_escape_string($conn, $_POST['WarehouseID']);
        $RowCode = mysqli_real_escape_string($conn, $_POST['RowCode']);
        $Description = mysqli_real_escape_string($conn, $_POST['Description']);
    
        $checkQuery = "SELECT * FROM warehouse_rows WHERE RowCode = '$WarehouseRowID'";
        $result = mysqli_query($conn, $checkQuery);
    
        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "
                UPDATE warehouse_rows 
                SET 
                    RowCode = '$RowCode', 
                    Description = '$Description'
                WHERE WarehouseRowID = '$WarehouseRowID'
            ";
    
            if (mysqli_query($conn, $updateQuery)) {
                echo "success";
            } else {
                echo "Error updating warehouse: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "
                INSERT INTO warehouse_rows (
                    RowCode,
                    WarehouseID,
                    Description
                ) VALUES (
                    '$RowCode', 
                    '$WarehouseID', 
                    '$Description'
                )
            ";
    
            if (mysqli_query($conn, $insertQuery)) {
                echo "success";
            } else {
                echo "Error adding warehouse: " . mysqli_error($conn);
            }
        }
    }

    if ($action == "add_update_shelf") {
        $ShelfID = mysqli_real_escape_string($conn, $_POST['ShelfID']);
        $WarehouseRowID = mysqli_real_escape_string($conn, $_POST['WarehouseRowID']);
        $ShelfCode = mysqli_real_escape_string($conn, $_POST['ShelfCode']);
        $Description = mysqli_real_escape_string($conn, $_POST['Description']);
    
        $checkQuery = "SELECT * FROM shelves WHERE ShelfID = '$ShelfID'";
        $result = mysqli_query($conn, $checkQuery);
    
        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "
                UPDATE shelves 
                SET 
                    WarehouseRowID = '$WarehouseRowID', 
                    ShelfCode = '$ShelfCode', 
                    Description = '$Description'
                WHERE WarehouseRowID = '$WarehouseRowID'
            ";
    
            if (mysqli_query($conn, $updateQuery)) {
                echo "success";
            } else {
                echo "Error updating warehouse: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "
                INSERT INTO shelves (
                    WarehouseRowID,
                    ShelfCode,
                    Description
                ) VALUES (
                    '$WarehouseRowID', 
                    '$ShelfCode', 
                    '$Description'
                )
            ";
    
            if (mysqli_query($conn, $insertQuery)) {
                echo "success";
            } else {
                echo "Error adding warehouse: " . mysqli_error($conn);
            }
        }
    }
    

    if ($action == "fetch_modal") {
        $warehouse_id = mysqli_real_escape_string($conn, $_POST['warehouse_id']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM warehouses WHERE WarehouseID  = '$warehouse_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            
            ?>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">Update Warehouse</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="update_warehouse" class="form-horizontal">
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body">
                                    <input type="hidden" id="WarehouseID" name="WarehouseID" class="form-control" value="<?= $row['WarehouseID'] ?>"/>

                                    <div class="row pt-3">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">Warehouse Name</label>
                                                <input type="text" id="WarehouseName" name="WarehouseName" class="form-control" value="<?= $row['WarehouseName'] ?>" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">Location</label>
                                                <input type="text" id="Location" name="Location" class="form-control" value="<?= $row['Location'] ?>"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-4 mb-12">
                                            <label class="form-label">Contact Person</label>
                                            <input type="text" id="contact_person" name="contact_person" class="form-control" value="<?= $row['contact_person'] ?>" />
                                        </div>
                                        <div class="col-8 mb-6">
                                            <label class="form-label">Contact Phone</label>
                                            <input type="text" id="contact_phone" name="contact_phone" class="form-control" value="<?= $row['contact_phone'] ?>" />
                                        </div>
                                        <div class="col-12 mb-6">
                                            <label class="form-label">Contact Email</label>
                                            <input type="text" id="contact_email" name="contact_email" class="form-control" value="<?= $row['contact_email'] ?>" />
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
            <?php
        }
    } 
    

    if ($action == "fetch_info") {
        $warehouse_id = mysqli_real_escape_string($conn, $_REQUEST['warehouse_id']);

        $checkQuery = "SELECT * FROM warehouses WHERE WarehouseID = '$warehouse_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
        ?>
            <div class="hstack align-items-start mb-7 pb-1 align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                
                <div>
                    <h6 class="fw-semibold fs-4 mb-0"><?= $row['WarehouseName'] ?></h6>
                    <p class="mb-0"><?= $row['Location'] ?></p>
                </div>
                </div>
            </div>
            <div class="row">
                <div class="col-4 mb-7">
                <p class="mb-1 fs-2">Contact Person</p>
                <h6 class="fw-semibold mb-0"><?= $row['contact_person'] ?></h6>
                </div>
                <div class="col-4 mb-7">
                <p class="mb-1 fs-2">Contact Email</p>
                <h6 class="fw-semibold mb-0"><?= $row['contact_email'] ?></h6>
                </div>
                <div class="col-4 mb-7">
                <p class="mb-1 fs-2">Contact Phone</p>
                <h6 class="fw-semibold mb-0"><?= $row['contact_phone'] ?></h6>
                </div>
                
            </div>

            <div class="card my-3">
                <div class="card-body">
                    <div class="col-12">
                        <div class="datatables">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title d-flex justify-content-between align-items-center">List of Bins  

                                        <a href="#" class="btn btn-primary" style="border-radius: 10%; " data-bs-toggle="modal" data-bs-target="#addBinModal">Add New</a>
                                        <div><input type="checkbox" id="toggleActive" checked> Show Active Only</div>
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
                                                $query_wh_bins = "SELECT * FROM bins WHERE WarehouseID = '$warehouse_id'";
                                                $result_wh_bins = mysqli_query($conn, $query_wh_bins);            
                                                while ($row_wh_bins = mysqli_fetch_array($result_wh_bins)) {
                                                ?>
                                                    <tr>
                                                        <td><?= $row_wh_bins['BinCode'] ?></td>
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
                    </div>

                    <div class="col-12">
                        <div class="datatables">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title d-flex justify-content-between align-items-center">List of Rows  

                                        <a href="#" class="btn btn-primary" style="border-radius: 10%;" data-bs-toggle="modal" data-bs-target="#addRowModal">Add New</a>
                                        <div><input type="checkbox" id="toggleActive" checked> Show Active Only</div>
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
                                                $query_wh_rows = "SELECT * FROM warehouse_rows WHERE WarehouseID = '$warehouse_id'";
                                                $result_wh_rows = mysqli_query($conn, $query_wh_rows);            
                                                while ($row_wh_rows = mysqli_fetch_array($result_wh_rows)) {
                                                ?>
                                                    <tr>
                                                        <td><?= $row_wh_rows['RowCode'] ?></td>
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
                    </div>

                    <div class="col-12">
                        <div class="datatables">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title d-flex justify-content-between align-items-center">List of Shelves  

                                        <a href="#" class="btn btn-primary" style="border-radius: 10%;" data-bs-toggle="modal" data-bs-target="#addShelfModal">Add New</a>
                                        <div><input type="checkbox" id="toggleActive" checked> Show Active Only</div>
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
                                                    WHERE wr.WarehouseID = '$warehouse_id'
                                                ";
                                                $result_wh_shelves = mysqli_query($conn, $query_wh_shelves);

                                                if ($result_wh_shelves) {
                                                    while ($row_wh_shelves = mysqli_fetch_array($result_wh_shelves)) {
                                                        ?>
                                                        <tr>
                                                            <td><?= $row_wh_shelves['ShelfCode'] ?></td>
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
                                                <input type="hidden" id="WarehouseID" name="WarehouseID" class="form-control" value="<?= $row['WarehouseID'] ?>" />

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
                                                <input type="hidden" id="WarehouseID" name="WarehouseID" class="form-control" value="<?= $row['WarehouseID'] ?>"/>

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
                                                <input type="hidden" id="WarehouseID" name="WarehouseID" class="form-control" value="<?= $row['WarehouseID'] ?>"/>

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
                                                                $query_rows = "SELECT * FROM warehouse_rows WHERE WarehouseID = '" .$row['WarehouseID'] ."'";

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

                </div>
            </div>

            
        <?php
        }

    }
    
    mysqli_close($conn);
}
?>

