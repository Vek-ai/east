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
        $loyalty_id = mysqli_real_escape_string($conn, $_POST['loyalty_id']);
        $loyalty_program_name = mysqli_real_escape_string($conn, $_POST['loyalty_program_name']);
        $accumulated_total_orders = mysqli_real_escape_string($conn, $_POST['accumulated_total_orders']);
        $discount = mysqli_real_escape_string($conn, $_POST['discount']);
        $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
        $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);
        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        if (!empty($loyalty_id)) {
            $checkQuery = "SELECT * FROM loyalty_program WHERE loyalty_id = '$loyalty_id'";
            $result = mysqli_query($conn, $checkQuery);

            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $current_loyalty_program = $row['loyalty_program_name'];

                if ($loyalty_program_name != $current_loyalty_program) {
                    $checkLoyalty = "SELECT * FROM loyalty_program WHERE loyalty_program_name = '$loyalty_program_name'";
                    $resultLoyalty = mysqli_query($conn, $checkLoyalty);
                    if (mysqli_num_rows($resultLoyalty) > 0) {
                        echo "Loyalty Program Name already exists! Please choose a unique value.";
                        exit;
                    }
                }

                $updateQuery = "
                    UPDATE loyalty_program 
                    SET 
                        loyalty_program_name = '$loyalty_program_name', 
                        accumulated_total_orders = '$accumulated_total_orders', 
                        discount = '$discount', 
                        date_from = '$date_from', 
                        date_to = '$date_to', 
                        last_edit = NOW(), 
                        edited_by = '$userid' 
                    WHERE 
                        loyalty_id = '$loyalty_id'
                ";

                if (mysqli_query($conn, $updateQuery)) {
                    echo "success_update";
                } else {
                    echo "Error updating loyalty program: " . mysqli_error($conn);
                }
            }
        } else {
            $checkLoyalty = "SELECT * FROM loyalty_program WHERE loyalty_program_name = '$loyalty_program_name'";
            $resultLoyalty = mysqli_query($conn, $checkLoyalty);
            if (mysqli_num_rows($resultLoyalty) > 0) {
                echo "Loyalty Program Name already exists! Please choose a unique value.";
                exit;
            }

            $insertQuery = "
                INSERT INTO loyalty_program (
                    loyalty_program_name, 
                    accumulated_total_orders, 
                    discount, 
                    date_from, 
                    date_to, 
                    added_date, 
                    added_by
                ) VALUES (
                    '$loyalty_program_name', 
                    '$accumulated_total_orders', 
                    '$discount', 
                    '$date_from', 
                    '$date_to', 
                    NOW(), 
                    '$userid'
                )
            ";

            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error adding loyalty program: " . mysqli_error($conn);
            }
        }
    }

    if ($action == "change_status") {
        $loyalty_id = mysqli_real_escape_string($conn, $_POST['loyalty_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE loyalty_program SET status = '$new_status' WHERE loyalty_id = '$loyalty_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }

    if ($action == 'hide_loyalty_program') {
        $loyalty_id = mysqli_real_escape_string($conn, $_POST['loyalty_id']);
        $query = "UPDATE loyalty_program SET hidden = '1' WHERE loyalty_id = '$loyalty_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'Error hiding category: ' . mysqli_error($conn);
        }
    }

    mysqli_close($conn);
}
?>
