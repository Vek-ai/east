<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $id = mysqli_real_escape_string($conn, $_POST['id'] ?? null);
        $supplierid = mysqli_real_escape_string($conn, $_POST['supplierid'] ?? '');
        $pack = mysqli_real_escape_string($conn, $_POST['pack'] ?? '');
        $pack_abbreviation = mysqli_real_escape_string($conn, $_POST['pack_abbreviation'] ?? '');
        $pack_count = mysqli_real_escape_string($conn, $_POST['pack_count'] ?? 0);
        $userid = mysqli_real_escape_string($conn, $_POST['userid'] ?? '');

        $checkQuery = "SELECT * FROM supplier_pack WHERE id = '$id'";
        $result = mysqli_query($conn, $checkQuery);

        if ($result && mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE supplier_pack 
                            SET supplierid = '$supplierid', 
                                pack = '$pack', 
                                pack_abbreviation = '$pack_abbreviation', 
                                pack_count = '$pack_count', 
                                last_edit = NOW(), 
                                edited_by = '$userid' 
                            WHERE id = '$id'";

            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating supplier pack: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO supplier_pack (supplierid, pack, pack_abbreviation, pack_count, added_by) 
                            VALUES ('$supplierid', '$pack', '$pack_abbreviation', '$pack_count', '$userid')";

            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error adding supplier pack: " . mysqli_error($conn);
            }
        }
    } 
    
    if ($action == "change_status") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE supplier_pack SET status = '$new_status' WHERE id = '$id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_supplier_pack') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "UPDATE supplier_pack SET hidden='1' WHERE id='$id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
