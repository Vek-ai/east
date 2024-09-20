<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $staff_id = mysqli_real_escape_string($conn, $_POST['staff_id']);
        $staff_fname = mysqli_real_escape_string($conn, $_POST['staff_fname']);
        $staff_lname = mysqli_real_escape_string($conn, $_POST['staff_lname']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $city = mysqli_real_escape_string($conn, $_POST['city']);
        $state = mysqli_real_escape_string($conn, $_POST['state']);
        $zip = mysqli_real_escape_string($conn, $_POST['zip']);
        $emergency_contact_name = mysqli_real_escape_string($conn, $_POST['emergency_contact_name']);
        $emergency_contact_phone = mysqli_real_escape_string($conn, $_POST['emergency_contact_phone']);
        $driver_med_cert = mysqli_real_escape_string($conn, $_POST['driver_med_cert']);
        $driver_class = mysqli_real_escape_string($conn, $_POST['driver_class']);
        $license_renewal_date = mysqli_real_escape_string($conn, $_POST['license_renewal_date']);
        $warehouse = mysqli_real_escape_string($conn, $_POST['warehouse']);
    
        $checkQuery = "SELECT * FROM staff WHERE staff_id = '$staff_id'";
        $result = mysqli_query($conn, $checkQuery);
        $isInsert = false;
        if (mysqli_num_rows($result) > 0) {
            $isInsert = false;
            $updateQuery = "
                UPDATE staff 
                SET 
                    staff_fname = '$staff_fname', 
                    staff_lname = '$staff_lname', 
                    role = '$role', 
                    phone = '$phone', 
                    email = '$email', 
                    address = '$address', 
                    city = '$city', 
                    state = '$state', 
                    zip = '$zip', 
                    emergency_contact_name = '$emergency_contact_name', 
                    emergency_contact_phone = '$emergency_contact_phone', 
                    driver_med_cert = '$driver_med_cert', 
                    driver_class = '$driver_class', 
                    license_renewal_date = '$license_renewal_date'
                WHERE staff_id = '$staff_id'
            ";
    
            if (mysqli_query($conn, $updateQuery)) {

                // Add corresponding_user to warehouse table
                if(empty($warehouse)) {
                    echo "Staff updated successfully.";
                    
                } else {
                    $checkQuery = "SELECT * FROM warehouses WHERE WarehouseID = '$warehouse'";
                    $result = mysqli_query($conn, $checkQuery);
                    $isInsert = false;

                    if (mysqli_num_rows($result) > 0) {
                        $isInsert = false;
                        $updateQuery = "
                            UPDATE warehouses
                            SET 
                                corresponding_user = '$staff_id'
                            WHERE WarehouseID = '$warehouse'
                        ";

                        if (mysqli_query($conn, $updateQuery)) {
                            echo "Staff updated successfully.";
                        } else {
                            echo "Error updating staff: " . mysqli_error($conn);
                        }
                    }
                }
                
            } else {
                echo "Error updating staff: " . mysqli_error($conn);
            }
        } else {
            $isInsert = true;
            // Record does not exist, insert it
            $insertQuery = "
                INSERT INTO staff (
                    staff_fname, 
                    staff_lname, 
                    role, 
                    phone, 
                    email, 
                    address, 
                    city, 
                    state, 
                    zip, 
                    emergency_contact_name, 
                    emergency_contact_phone, 
                    driver_med_cert, 
                    driver_class, 
                    license_renewal_date
                ) VALUES (
                    '$staff_fname', 
                    '$staff_lname', 
                    '$role', 
                    '$phone', 
                    '$email', 
                    '$address', 
                    '$city', 
                    '$state', 
                    '$zip', 
                    '$emergency_contact_name', 
                    '$emergency_contact_phone', 
                    '$driver_med_cert', 
                    '$driver_class', 
                    '$license_renewal_date'
                )
            ";
    
            if (mysqli_query($conn, $insertQuery)) {
                $staff_id = $conn->insert_id;

                // Add corresponding_user to warehouse table
                if(empty($warehouse)) {
                    echo "New staff added successfully.";
                } else {
                    $checkQuery = "SELECT * FROM warehouses WHERE WarehouseID = '$warehouse'";
                    $result = mysqli_query($conn, $checkQuery);
                    $isInsert = false;

                    if (mysqli_num_rows($result) > 0) {
                        $isInsert = false;
                        $updateQuery = "
                            UPDATE warehouses 
                            SET 
                                corresponding_user = '$staff_id'
                            WHERE WarehouseID = '$warehouse'
                        ";

                        if (mysqli_query($conn, $updateQuery)) {
                            echo "New staff added successfully.";
                        } else {
                            echo "Error updating staff: " . mysqli_error($conn);
                        }
                    }
                }
                
            } else {
                echo "Error adding staff: " . mysqli_error($conn);
            }
        }

        $message = "";
        if (isset($_FILES['profile_path']) && $_FILES['profile_path']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_path']['tmp_name'];
            $fileName = $_FILES['profile_path']['name'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            $uploadFileDir = '../images/staff/';
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = $uploadFileDir . $newFileName;

            $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
            if (in_array($fileExtension, $allowedfileExtensions)) {
                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    $profile_path = mysqli_real_escape_string($conn, $dest_path);
                    $sql = "UPDATE staff SET profile_path='images/staff/$newFileName' WHERE staff_id='$staff_id'";
                    if (!$conn->query($sql)) {
                        echo "Error updating record: " . $conn->error;
                    }
                } else {
                    $message = 'Error moving the file to the upload directory.';
                }
            } else {
                $message = 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
            }
        } else {
            if($isInsert){
                $sql = "UPDATE staff SET profile_path='images/staff/user.jpg' WHERE staff_id='$staff_id'";
                if (!$conn->query($sql)) {
                    echo "Error updating record: " . $conn->error;
                }
            }
            
        }
    }

    if ($action == "change_status") {
        $staff_id = mysqli_real_escape_string($conn, $_POST['staff_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE staff SET status = '$new_status' WHERE staff_id = '$staff_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    

    if ($action == "fetch_modal") {
        $staff_id = mysqli_real_escape_string($conn, $_POST['staff_id']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM staff WHERE staff_id = '$staff_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            
            ?>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">Update Staff</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="update_staff" class="form-horizontal">
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body">
                                    <input type="hidden" id="staff_id" name="staff_id" class="form-control" value="<?= $row['staff_id'] ?>"/>
                                    
                                    <div class="row">
                                        <div class="card-body p-0">
                                            <h4 class="card-title text-center">Profile Picture</h4>
                                            <div class="text-center">
                                                <?php 
                                                if(!empty($row['profile_path'])){
                                                    $profile_path = $row['profile_path'];
                                                }else{
                                                    $profile_path = "../assets/images/profile/user-3.jpg";
                                                }
                                                ?>
                                                <img src="<?= $profile_path ?>" id="profile_img" alt="profile-picture" class="img-fluid rounded-circle" width="120" height="120">
                                                <div class="d-flex align-items-center justify-content-center my-4 gap-6">
                                                <button id="upload_profile" type="button" class="btn btn-primary">Upload</button>
                                                <button id="reset_profile" type="button" class="btn bg-danger-subtle text-danger">Reset</button>
                                                </div>
                                                <input type="file" id="profile_path" name="profile_path" class="form-control" style="display: none;"/>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Staff First Name</label>
                                                <input type="text" id="staff_fname" name="staff_fname" class="form-control" value="<?= $row['staff_fname'] ?>" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Staff Last Name</label>
                                                <input type="text" id="staff_lname" name="staff_lname" class="form-control" value="<?= $row['staff_lname'] ?>" />
                                            </div>
                                        </div>
                                        
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Role</label>
                                                <select id="role" class="form-control" name="role">
                                                    <option value="/">Select One...</option>
                                                    <?php
                                                    $query_roles = "SELECT * FROM staff_roles WHERE hidden = '0'";
                                                    $result_roles = mysqli_query($conn, $query_roles);            
                                                    while ($row_staff = mysqli_fetch_array($result_roles)) {
                                                        $selected = ($row_staff['emp_role_id'] == $row['role']) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $row_staff['emp_role_id'] ?>" <?= $selected ?>><?= $row_staff['emp_role'] ?></option>
                                                    <?php   
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-4 mb-7">
                                            <label class="form-label">Phone number</label>
                                            <input type="text" id="phone" name="phone" class="form-control phone-inputmask" value="<?= $row['phone'] ?>" />
                                        </div>
                                        <div class="col-4 mb-7">
                                            <label class="form-label">Email address</label>
                                            <input type="email" id="email" name="email" class="form-control" value="<?= $row['email'] ?>" />
                                        </div>
                                        
                                    </div>

                                    <div class="row">
                                        <div class="col-12 mb-9">
                                            <label class="form-label">Address</label>
                                            <input type="text" id="address" name="address" class="form-control" value="<?= $row['address'] ?>" />
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-4 mb-7">
                                            <label class="form-label">City</label>
                                            <input type="text" id="city" name="city" class="form-control" value="<?= $row['city'] ?>" />
                                        </div>
                                        <div class="col-4 mb-7">
                                            <label class="form-label">State</label>
                                            <input type="text" id="state" name="state" class="form-control" value="<?= $row['state'] ?>" />
                                        </div>
                                        <div class="col-4 mb-7">
                                            <label class="form-label">Zip</label>
                                            <input type="text" id="zip" name="zip" class="form-control" value="<?= $row['zip'] ?>" />
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6 mb-7">
                                            <label class="form-label">Emergency Contact Name</label>
                                            <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-control" value="<?= $row['emergency_contact_name'] ?>" />
                                        </div>
                                        <div class="col-6 mb-7">
                                            <label class="form-label">Emergency Contact Phone</label>
                                            <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" class="form-control phone-inputmask" value="<?= $row['emergency_contact_phone'] ?>" />
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-4 mb-7">
                                            <label class="form-label">Driver Medical Certificate</label>
                                            <input type="text" id="driver_med_cert" name="driver_med_cert" class="form-control" value="<?= $row['driver_med_cert'] ?>" />
                                        </div>
                                        <div class="col-4 mb-7">
                                            <label class="form-label">Driver Class</label>
                                            <input type="text" id="driver_class" name="driver_class" class="form-control" value="<?= $row['driver_class'] ?>" />
                                        </div>
                                        <div class="col-4 mb-7">
                                            <label class="form-label">License Renewal Date</label>
                                            <input type="date" id="license_renewal_date" name="license_renewal_date" class="form-control" value="<?= $row['license_renewal_date'] ?>" />
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
                <script>
                    $(".phone-inputmask").inputmask("(999) 999-9999");
                </script>
                <!-- /.modal-content -->
            </div>
            <?php
        }
    } 

    if ($action == "fetch_modal_view") {
        $staff_id = mysqli_real_escape_string($conn, $_POST['staff_id']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM staff WHERE staff_id = '$staff_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            
            ?>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">Staff Details</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-body">
                                <input type="hidden" id="staff_id" name="staff_id" value="<?= $row['staff_id'] ?>" />

                                <div class="text-center">
                                    <?php 
                                    if (!empty($row['profile_path'])) {
                                        $profile_path = $row['profile_path'];
                                    } else {
                                        $profile_path = "../assets/images/profile/user-3.jpg";
                                    }
                                    ?>
                                    <img src="<?= $profile_path ?>" id="profile_img" alt="profile-picture" class="img-fluid rounded-circle" width="120" height="120">
                                    <h4 class="modal-title" id="myLargeModalLabel"><?= $row['staff_fname'] ?> <?= $row['staff_lname'] ?></h4>
                                    <p id="role"><?= get_role_name($row['role']) ?></p>
                                </div>

                                <div class="row">
                                    <div class="col-6 mb-7">
                                        <label class="form-label">Phone Number</label>
                                        <p id="phone" class="pl-3"><?= $row['phone'] ?></p>
                                    </div>
                                    <div class="col-6 mb-7">
                                        <label class="form-label">Email Address</label>
                                        <p id="email" class="pl-3"><?= $row['email'] ?></p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 mb-9">
                                        <label class="form-label">Address</label>
                                        <p id="address" class="pl-3"><?= $row['address'] ?></p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-4 mb-7">
                                        <label class="form-label">City</label>
                                        <p id="city" class="pl-3"><?= $row['city'] ?></p>
                                    </div>
                                    <div class="col-4 mb-7">
                                        <label class="form-label">State</label>
                                        <p id="state" class="pl-3"><?= $row['state'] ?></p>
                                    </div>
                                    <div class="col-4 mb-7">
                                        <label class="form-label">Zip</label>
                                        <p id="zip" class="pl-3"><?= $row['zip'] ?></p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6 mb-7">
                                        <label class="form-label">Emergency Contact Name</label>
                                        <p id="emergency_contact_name" class="pl-3"><?= $row['emergency_contact_name'] ?></p>
                                    </div>
                                    <div class="col-6 mb-7">
                                        <label class="form-label">Emergency Contact Phone</label>
                                        <p id="emergency_contact_phone" class="pl-3"><?= $row['emergency_contact_phone'] ?></p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-4 mb-7">
                                        <label class="form-label">Driver Medical Certificate</label>
                                        <p id="driver_med_cert" class="pl-3"><?= $row['driver_med_cert'] ?></p>
                                    </div>
                                    <div class="col-4 mb-7">
                                        <label class="form-label">Driver Class</label>
                                        <p id="driver_class" class="pl-3"><?= $row['driver_class'] ?></p>
                                    </div>
                                    <div class="col-4 mb-7">
                                        <label class="form-label">License Renewal Date</label>
                                        <p id="license_renewal_date" class="pl-3"><?= $row['license_renewal_date'] ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    } 
    
    
    mysqli_close($conn);
}
?>
