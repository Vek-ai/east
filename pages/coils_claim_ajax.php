<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])){
   $action = $_REQUEST['action'];

   if($action == 'coil_tag_done'){
        $coil_defective_id = intval($_POST['coil_defective_id']);

        if ($coil_defective_id <= 0) {
            echo "Invalid coil ID";
            exit;
        }

        $check = mysqli_query($conn, "SELECT * FROM coil_defective WHERE coil_defective_id = $coil_defective_id");
        if (mysqli_num_rows($check) > 0) {
            $update = "
                UPDATE coil_defective 
                SET status = 2 
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

   

}





