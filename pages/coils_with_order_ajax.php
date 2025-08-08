<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

$trim_id = 43;
$panel_id = 46;

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $coil = mysqli_real_escape_string($conn, $_POST['coil']);
        $color = mysqli_real_escape_string($conn, $_POST['color']);
        $gauge = mysqli_real_escape_string($conn, $_POST['gauge']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $grade = mysqli_real_escape_string($conn, $_POST['grade']);
        $width = mysqli_real_escape_string($conn, $_POST['width']);
        $length = mysqli_real_escape_string($conn, $_POST['length']);
        $thickness = mysqli_real_escape_string($conn, $_POST['thickness']);
        $weight = mysqli_real_escape_string($conn, $_POST['weight']);
        $material_grade = mysqli_real_escape_string($conn, $_POST['material_grade']);
        $steel_coating = mysqli_real_escape_string($conn, $_POST['steel_coating']);
        $backer_color = mysqli_real_escape_string($conn, $_POST['backer_color']);
        $userid = mysqli_real_escape_string($conn, $_POST['userid']);
        $coil_id = mysqli_real_escape_string($conn, $_POST['coil_id']);

        $supplier = mysqli_real_escape_string($conn, $_POST['supplier_id']);
        $entry_number = mysqli_real_escape_string($conn, $_POST['entry_number']);
        $coil_number = mysqli_real_escape_string($conn, $_POST['coil_number']);
        $tag_number = mysqli_real_escape_string($conn, $_POST['tag_number']);
        $entry_date = mysqli_real_escape_string($conn, $_POST['entry_date']);
        $invoice = mysqli_real_escape_string($conn, $_POST['invoice']);
        $original_feet = mysqli_real_escape_string($conn, $_POST['original_feet']);
        $original_weight = mysqli_real_escape_string($conn, $_POST['original_weight']);
        $remaining_feet = mysqli_real_escape_string($conn, $_POST['remaining_feet']);
        $remaining_weight = mysqli_real_escape_string($conn, $_POST['remaining_weight']);
        $price_per_foot = mysqli_real_escape_string($conn, $_POST['price_per_foot']);
        $price_per_cwt = mysqli_real_escape_string($conn, $_POST['price_per_cwt']);
        $pounds_per_foot = mysqli_real_escape_string($conn, $_POST['pounds_per_foot']);
        $color_code = mysqli_real_escape_string($conn, $_POST['color_code']);
        $actual_width = mysqli_real_escape_string($conn, $_POST['actual_width']);
        $rounded_width = mysqli_real_escape_string($conn, $_POST['rounded_width']);
        $original_price = mysqli_real_escape_string($conn, $_POST['original_price']);
        $current_price = mysqli_real_escape_string($conn, $_POST['current_price']);

        if (!empty($coil_id)) {
            $checkQuery = "SELECT * FROM coil WHERE coil_id = '$coil_id'";
            $result = mysqli_query($conn, $checkQuery);

            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $current_coil = $row['coil'];

                if ($coil != $current_coil) {
                    $checkCategory = "SELECT * FROM coil WHERE coil = '$coil'";
                    $resultCategory = mysqli_query($conn, $checkCategory);
                    if (mysqli_num_rows($resultCategory) > 0) {
                        echo "Coil Name already exists! Please choose a unique value.";
                        exit;
                    }
                }

                $updateQuery = "
                    UPDATE coil 
                    SET 
                        coil = '$coil', 
                        grade = '$grade', 
                        category = '$category', 
                        color = '$color', 
                        width = '$width', 
                        length = '$length', 
                        thickness = '$thickness', 
                        material_grade = '$material_grade', 
                        steel_coating = '$steel_coating', 
                        gauge = '$gauge', 
                        backer_color = '$backer_color', 
                        weight = '$weight', 
                        supplier = '$supplier', 
                        entry_number = '$entry_number', 
                        coil_number = '$coil_number', 
                        tag_number = '$tag_number', 
                        entry_date = '$entry_date', 
                        invoice = '$invoice',
                        original_feet = '$original_feet', 
                        original_weight = '$original_weight', 
                        remaining_feet = '$remaining_feet', 
                        remaining_weight = '$remaining_weight', 
                        price_per_foot = '$price_per_foot', 
                        price_per_cwt = '$price_per_cwt', 
                        pounds_per_foot = '$pounds_per_foot', 
                        color_code = '$color_code', 
                        actual_width = '$actual_width', 
                        rounded_width = '$rounded_width', 
                        original_price = '$original_price', 
                        current_price = '$current_price', 
                        last_edit = NOW(), 
                        edited_by = '$userid' 
                    WHERE 
                        coil_id = '$coil_id'
                ";

                if (mysqli_query($conn, $updateQuery)) {
                    echo "success_update";
                } else {
                    echo "Error updating coil: " . mysqli_error($conn);
                }
            }
        } else {
            $checkCategory = "SELECT * FROM coil WHERE coil = '$coil'";
            $resultCategory = mysqli_query($conn, $checkCategory);
            if (mysqli_num_rows($resultCategory) > 0) {
                echo "Coil Name already exists! Please choose a unique value.";
                exit;
            }

            $insertQuery = "
                INSERT INTO coil (
                    coil, 
                    grade, 
                    category, 
                    color, 
                    width, 
                    length, 
                    thickness, 
                    material_grade, 
                    steel_coating, 
                    gauge, 
                    backer_color, 
                    weight, 
                    supplier,
                    entry_number, 
                    coil_number, 
                    tag_number, 
                    entry_date, 
                    invoice,
                    original_feet, 
                    original_weight, 
                    remaining_feet, 
                    remaining_weight, 
                    price_per_foot, 
                    price_per_cwt, 
                    pounds_per_foot, 
                    color_code, 
                    actual_width, 
                    rounded_width, 
                    original_price, 
                    current_price, 
                    added_date, 
                    added_by
                ) VALUES (
                    '$coil', 
                    '$grade', 
                    '$category', 
                    '$color', 
                    '$width', 
                    '$length', 
                    '$thickness', 
                    '$material_grade', 
                    '$steel_coating', 
                    '$gauge', 
                    '$backer_color', 
                    '$weight', 
                    '$supplier', 
                    '$entry_number', 
                    '$coil_number', 
                    '$tag_number', 
                    '$entry_date', 
                    '$invoice', 
                    '$original_feet', 
                    '$original_weight', 
                    '$remaining_feet', 
                    '$remaining_weight', 
                    '$price_per_foot', 
                    '$price_per_cwt', 
                    '$pounds_per_foot', 
                    '$color_code', 
                    '$actual_width', 
                    '$rounded_width', 
                    '$original_price', 
                    '$current_price', 
                    NOW(), 
                    '$userid'
                )
            ";

            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error adding coil: " . mysqli_error($conn);
            }
        }
    }

    if ($action == "change_status") {
        $coil_id = mysqli_real_escape_string($conn, $_POST['coil_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE coil SET status = '$new_status' WHERE coil_id = '$coil_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }

    if ($action == 'hide_coil') {
        $coil_id = mysqli_real_escape_string($conn, $_POST['coil_id']);
        $query = "UPDATE coil SET hidden = '1' WHERE coil_id = '$coil_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'Error hiding category: ' . mysqli_error($conn);
        }
    }

    if ($action == "order_coil") {
        $coil_id = mysqli_real_escape_string($conn, $_POST['coil_id']);
    
        if (!isset($_SESSION["orders"])) {
            $_SESSION["orders"] = array();
        }
    
        $query = "SELECT * FROM coil WHERE coil_id = '$coil_id'";
        $result = mysqli_query($conn, $query);
    
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
    
            $exists_in_cart = false;
            foreach ($_SESSION["orders"] as $key => $order) {
                if (isset($order['coil_id']) && $order['coil_id'] == $coil_id) {
                    $_SESSION["orders"][$key]['quantity_cart'] += 1;
                    $exists_in_cart = true;
                    break;
                }
            }
            if (!$exists_in_cart) {
                $item_array = array(
                    'coil_id' => $row['coil_id'],
                    'coil_item' => $row['coil'],
                    'color' => $row['color'],
                    'width' => $row['width'],
                    'length' => $row['length'],
                    'gauge' => $row['gauge'],
                    'line' => 1,
                    'quantity_cart' => 1
                );
    
                $_SESSION["orders"][] = $item_array;
            }
            
            $_SESSION["order_coil_id"] = $coil_id;
            echo "success";
        } else {
            echo "Coil not found.";
        }
    }
    
    mysqli_close($conn);
}
?>
