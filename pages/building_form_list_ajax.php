<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];  

    if ($action == "quote_request") {
        $id     = (int)($_POST['id'] ?? 0);
        $sql = "UPDATE building_form SET status = 1 WHERE id = $id";
        $res = mysqli_query($conn, $sql);

        if ($res) {
            echo json_encode([
                'success' => true,
                'row_no'  => $id
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'DB update failed'
            ]);
        }
        
    }

    mysqli_close($conn);
}
?>
