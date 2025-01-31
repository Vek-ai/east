<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_grade_id = mysqli_real_escape_string($conn, $_POST['product_grade_id']);
        $product_grade = mysqli_real_escape_string($conn, $_POST['product_grade']);
        $grade_abbreviations = mysqli_real_escape_string($conn, $_POST['grade_abbreviations']);
        $multiplier = floatval(mysqli_real_escape_string($conn, $_POST['multiplier']));
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_grade WHERE product_grade_id = '$product_grade_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_product_grade = $row['product_grade'];
            $current_grade_abbreviations = $row['grade_abbreviations'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($product_grade != $current_product_grade) {
                $checkProductGrade = "SELECT * FROM product_grade WHERE product_grade = '$product_grade'";
                $resultProductGrade = mysqli_query($conn, $checkProductGrade);
                if (mysqli_num_rows($resultProductGrade) > 0) {
                    $duplicates[] = "Product grade";
                }
            }

            if ($grade_abbreviations != $current_grade_abbreviations) {
                $checkAbreviations = "SELECT * FROM product_grade WHERE grade_abbreviations = '$grade_abbreviations'";
                $resultAbreviations = mysqli_query($conn, $checkAbreviations);
                if (mysqli_num_rows($resultAbreviations) > 0) {
                    $duplicates[] = "Grade Abbreviations";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE product_grade SET product_grade = '$product_grade', grade_abbreviations = '$grade_abbreviations', multiplier = '$multiplier', notes = '$notes', last_edit = NOW(), edited_by = '$userid'  WHERE product_grade_id = '$product_grade_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "update-success";
                } else {
                    echo "Error updating product grade: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkProductGrade = "SELECT * FROM product_grade WHERE product_grade = '$product_grade'";
            $resultProductGrade = mysqli_query($conn, $checkProductGrade);
            if (mysqli_num_rows($resultProductGrade) > 0) {
                $duplicates[] = "Product Grade";
            }

            $checkAbreviations = "SELECT * FROM product_grade WHERE grade_abbreviations = '$grade_abbreviations'";
            $resultAbreviations = mysqli_query($conn, $checkAbreviations);
            if (mysqli_num_rows($resultAbreviations) > 0) {
                $duplicates[] = "Grade Abbreviations";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO product_grade (product_grade, grade_abbreviations, multiplier, notes, added_date, added_by) VALUES ('$product_grade', '$grade_abbreviations', '$multiplier', '$notes', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "add-success";
                } else {
                    echo "Error adding product grade: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $product_grade_id = mysqli_real_escape_string($conn, $_POST['product_grade_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product_grade SET status = '$new_status' WHERE product_grade_id = '$product_grade_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_product_grade') {
        $product_grade_id = mysqli_real_escape_string($conn, $_POST['product_grade_id']);
        $query = "UPDATE product_grade SET hidden='1' WHERE product_grade_id='$product_grade_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
