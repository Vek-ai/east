<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

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

    if ($action == "change_status") {
        $warehouse_id = mysqli_real_escape_string($conn, $_POST['warehouse_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE warehouses SET status = '$new_status' WHERE WarehouseID = '$warehouse_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    

    if ($action == "fetch_modal") {
        $warehouse_id = mysqli_real_escape_string($conn, $_POST['id']);

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
                                            <input type="text" id="contact_phone" name="contact_phone" class="form-control phone-inputmask" value="<?= $row['contact_phone'] ?>" />
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
            <script>
                $(".phone-inputmask").inputmask("(999) 999-9999");
            </script>
            <?php
        }
    } 
    
    mysqli_close($conn);
}
?>
