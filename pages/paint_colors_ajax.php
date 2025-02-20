<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);
        $color_name = mysqli_real_escape_string($conn, $_POST['color_name']);
        $color_code = mysqli_real_escape_string($conn, $_POST['color_code']);
        $color_group = mysqli_real_escape_string($conn, $_POST['color_group']);
        $product_category = mysqli_real_escape_string($conn, $_POST['product_category']);
        $provider_id = mysqli_real_escape_string($conn, $_POST['provider']);
        $ekm_color_code = mysqli_real_escape_string($conn, $_POST['ekm_color_code']);
        $ekm_color_no = mysqli_real_escape_string($conn, $_POST['ekm_color_no']);
        $ekm_paint_code = mysqli_real_escape_string($conn, $_POST['ekm_paint_code'] ?? '');
        $color_abbreviation = mysqli_real_escape_string($conn, $_POST['color_abbreviation']);
        $stock_availability = mysqli_real_escape_string($conn, $_POST['stock_availability']);
        $multiplier_category = mysqli_real_escape_string($conn, $_POST['multiplier_category'] ?? '');

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM paint_colors WHERE color_id = '$color_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE paint_colors SET color_name = '$color_name', color_code = '$color_code', ekm_color_no = '$ekm_color_no', ekm_paint_code = '$ekm_paint_code', color_group = '$color_group', product_category = '$product_category', provider_id = '$provider_id', last_edit = NOW(), edited_by = '$userid', ekm_color_code = '$ekm_color_code', color_abbreviation = '$color_abbreviation', stock_availability = '$stock_availability', multiplier_category = '$multiplier_category'  WHERE color_id = '$color_id'";
            if (mysqli_query($conn, $updateQuery)) {
                echo "Paint color updated successfully.";
            } else {
                echo "Error updating paint color: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO paint_colors (color_name, color_code, ekm_color_no, ekm_paint_code, color_group, product_category, provider_id, added_date, added_by, ekm_color_code, color_abbreviation, stock_availability, multiplier_category) VALUES ('$color_name', '$color_code', '$ekm_color_no', '$ekm_paint_code', '$color_group', '$product_category', '$provider_id', NOW(), '$userid', '$ekm_color_code', '$color_abbreviation', '$stock_availability', '$multiplier_category')";
            if (mysqli_query($conn, $insertQuery)) {
                echo "New paint color added successfully.";
            } else {
                echo "Error adding paint color: " . mysqli_error($conn);
            }
        }
    } 
    
    if ($action == "change_status") {
        $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE paint_colors SET color_status = '$new_status' WHERE color_id = '$color_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_paint_color') {
        $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);
        $query = "UPDATE paint_colors SET hidden='1' WHERE color_id='$color_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
