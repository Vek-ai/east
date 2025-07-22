<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])){
   $action = $_REQUEST['action'];

    if($action == 'coil_tag_rework'){
        $coil_defective_id = intval($_POST['coil_defective_id']);

        if ($coil_defective_id <= 0) {
            echo "Invalid coil ID";
            exit;
        }

        $check = mysqli_query($conn, "SELECT * FROM coil_defective WHERE coil_defective_id = $coil_defective_id");
        if (mysqli_num_rows($check) > 0) {
            $update = "
                UPDATE coil_defective 
                SET status = 1 
                WHERE coil_defective_id = $coil_defective_id
            ";
            if (mysqli_query($conn, $update)) {
                echo "success";
            } else {
                echo "Error updating status: " . mysqli_error($conn);
            }
        } else {
            echo "Coil not found in defective list.";
        }

        exit;
    }

    if ($action == 'coil_tag_approve') {
        $defective_id = intval($_POST['coil_defective_id']);

        $res = mysqli_query($conn, "SELECT coil_id FROM coil_defective WHERE coil_defective_id = $defective_id AND status != 4");
        if ($res && mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            $coil_id = intval($row['coil_id']);

            $update_coil_sql = "
                UPDATE coil_product
                SET status = 0
                WHERE coil_id = $coil_id
            ";
            mysqli_query($conn, $update_coil_sql);

            $archive_sql = "
                UPDATE coil_defective
                SET status = 4
                WHERE id = $defective_id
            ";
            mysqli_query($conn, $archive_sql);

            echo "success";
        } else {
            echo "Invalid or already archived coil_defective_id";
        }
    }
}





