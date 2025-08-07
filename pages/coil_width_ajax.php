<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $id = mysqli_real_escape_string($conn, $_POST['id'] ?? null);
        $actual_width = mysqli_real_escape_string($conn, $_POST['actual_width'] ?? 0);
        $decimal_conversion = mysqli_real_escape_string($conn, $_POST['decimal_conversion'] ?? 0);
        $rounded_width = mysqli_real_escape_string($conn, $_POST['rounded_width'] ?? 0);
        $rounded_conversion = mysqli_real_escape_string($conn, $_POST['rounded_conversion'] ?? 0);
        $classification = mysqli_real_escape_string($conn, $_POST['classification'] ?? '');
        $main_profile = mysqli_real_escape_string($conn, $_POST['main_profile'] ?? '');
        $second_profile = mysqli_real_escape_string($conn, $_POST['second_profile'] ?? '');
        $third_profile = mysqli_real_escape_string($conn, $_POST['third_profile'] ?? '');
        $stock = mysqli_real_escape_string($conn, $_POST['stock'] ?? '');
        $userid = mysqli_real_escape_string($conn, $_POST['userid'] ?? '');
    
        $gauge_systems = $_POST['gauge_systems'] ?? [];
        if (is_array($gauge_systems)) {
            $escaped_gauge_systems = array_map(fn($gauge) => mysqli_real_escape_string($conn, $gauge), $gauge_systems);
            $gauge_systems_str = implode(';', $escaped_gauge_systems);
        } else {
            $gauge_systems_str = '';
        }
    
        $checkQuery = "SELECT * FROM coil_width WHERE id = '$id'";
        $result = mysqli_query($conn, $checkQuery);
    
        if ($result && mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE coil_width 
                            SET actual_width = '$actual_width', 
                                decimal_conversion = '$decimal_conversion', 
                                rounded_width = '$rounded_width', 
                                rounded_conversion = '$rounded_conversion', 
                                classification = '$classification',
                                main_profile = '$main_profile',
                                second_profile = '$second_profile',
                                third_profile = '$third_profile',
                                stock = '$stock',
                                gauge_systems = '$gauge_systems_str',
                                last_edit = NOW(), 
                                edited_by = '$userid' 
                            WHERE id = '$id'";
    
            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating coil width: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO coil_width (actual_width, decimal_conversion, rounded_width, rounded_conversion, classification, main_profile, second_profile, third_profile, stock, gauge_systems, added_by, last_edit) 
                            VALUES ('$actual_width', '$decimal_conversion', '$rounded_width', '$rounded_conversion', '$classification', '$main_profile', '$second_profile', '$third_profile', '$stock', '$gauge_systems_str', '$userid', NOW())";
    
            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error adding coil width: " . mysqli_error($conn);
            }
        }
    }    
    
    if ($action == "change_status") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE coil_width SET status = '$new_status' WHERE id = '$id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_coil_width') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "UPDATE coil_width SET hidden='1' WHERE id='$id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
