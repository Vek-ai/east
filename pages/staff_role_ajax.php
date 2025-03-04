<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $emp_role_id = mysqli_real_escape_string($conn, $_POST['emp_role_id']);
        $emp_role = mysqli_real_escape_string($conn, $_POST['emp_role']);
        $role_desc = mysqli_real_escape_string($conn, $_POST['role_desc']);

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM staff_roles WHERE emp_role_id = '$emp_role_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $updateQuery = "UPDATE staff_roles SET emp_role = '$emp_role', role_desc = '$role_desc', last_edit = NOW(), edited_by = '$userid'  WHERE emp_role_id = '$emp_role_id'";
            if (mysqli_query($conn, $updateQuery)) {
                echo "Employee role updated successfully.";
            } else {
                echo "Error updating employee role: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO staff_roles (emp_role, role_desc, added_date, added_by) VALUES ('$emp_role', '$role_desc', NOW(), '$userid')";
            if (mysqli_query($conn, $insertQuery)) {
                echo "New employee role added successfully.";
            } else {
                echo "Error adding employee role: " . mysqli_error($conn);
            }
        }
    } 
    
    if ($action == "change_status") {
        $emp_role_id = mysqli_real_escape_string($conn, $_POST['emp_role_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE staff_roles SET status = '$new_status' WHERE emp_role_id = '$emp_role_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }

    if ($action == 'hide_employee_role') {
        $emp_role_id = mysqli_real_escape_string($conn, $_POST['emp_role_id']);
        $query = "UPDATE staff_roles SET hidden='1' WHERE emp_role_id='$emp_role_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'fetch_emp_list') {
        $emp_role_id = mysqli_real_escape_string($conn, $_POST['emp_role_id']);
        $query = "SELECT * FROM staff WHERE role = '$emp_role_id'";
        $result = mysqli_query($conn, $query);
    
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_array($result)) {
                $profile_path = !empty($row['profile_path']) ? $row['profile_path'] : "../assets/images/profile/user-3.jpg";
                ?>
                <div class="d-flex align-items-center mb-3 col">
                    <img src="<?= $profile_path ?>" class="rounded-circle" alt="profile-img" width="56" height="56">
                    <div class="ms-3">
                        <h6 class="fw-semibold mb-0 fs-4"><?= $row['staff_fname'] . ' ' . $row['staff_lname'] ?></h6>
                    </div>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="d-flex align-items-center">
                <h4>No employee found with this role</h4>
            </div>
            <?php
        }
    }

    
    mysqli_close($conn);
}
?>
