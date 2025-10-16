<?php
session_start();
require '../includes/dbconn.php';
require '../includes/functions.php';

$permission = $_SESSION['permission'];

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == 'fetch_table') {
        $query = "SELECT * FROM product_merge_history ORDER BY date_merged DESC";
        $result = mysqli_query($conn, $query);

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $merged_product = getProductName($row['product_merged']);
            $kept_product = getProductName($row['product_original']);
            $staff = get_staff_name($row['staff_id']);
            $date_merged = date('m/d/Y h:i A', strtotime($row['date_merged']));

            $data[] = [
                'merged_product' => $merged_product,
                'kept_product' => $kept_product,
                'staff' => $staff,
                'date_merged' => $date_merged
            ];
        }

        echo json_encode(['data' => $data]);
        exit;
    }

    mysqli_close($conn);
}
?>
