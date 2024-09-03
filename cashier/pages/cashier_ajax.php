<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require '../../includes/dbconn.php';

if(isset($_REQUEST['barcode'])){
    $upc = mysqli_real_escape_string($conn, $_REQUEST['barcode']);

    $query = "
        SELECT 
            p.product_item AS item_name,
            p.unit_price AS price,
            i.quantity_ttl AS quantity
        FROM 
            product p
        INNER JOIN 
            inventory i
        ON 
            p.product_id = i.product_id
        WHERE p.upc = '$upc'
    ";

    $result = mysqli_query($conn, $query);

    if($result){
        $data = mysqli_fetch_assoc($result);

        if($data){
            echo json_encode(array(
                'status' => 'success',
                'data' => $data
            ));
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'No product found with the provided barcode.'
            ));
        }
    } else {
        echo json_encode(array(
            'status' => 'error',
            'message' => 'Database query failed.'
        ));
    }
} else {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Barcode not provided.'
    ));
}
?>