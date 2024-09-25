<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $coil = mysqli_real_escape_string($conn, $_POST['coil']);
        $color = mysqli_real_escape_string($conn, $_POST['color']);
        $gauge = mysqli_real_escape_string($conn, $_POST['gauge']);
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
                        color = '$color', 
                        width = '$width', 
                        length = '$length', 
                        thickness = '$thickness', 
                        material_grade = '$material_grade', 
                        steel_coating = '$steel_coating', 
                        gauge = '$gauge', 
                        backer_color = '$backer_color', 
                        weight = '$weight', 
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
                    color, 
                    width, 
                    length, 
                    thickness, 
                    material_grade, 
                    steel_coating, 
                    gauge, 
                    backer_color, 
                    weight, 
                    added_date, 
                    added_by
                ) VALUES (
                    '$coil', 
                    '$grade', 
                    '$color', 
                    '$width', 
                    '$length', 
                    '$thickness', 
                    '$material_grade', 
                    '$steel_coating', 
                    '$gauge', 
                    '$backer_color', 
                    '$weight', 
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

    mysqli_close($conn);
}
?>
