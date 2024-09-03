<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require '../../includes/dbconn.php';

if (isset($_REQUEST['barcode'])) {
    $item_quantity = 1;
    $upc = mysqli_real_escape_string($conn, $_REQUEST['barcode']);
    $query_p = "SELECT * FROM product WHERE upc = '" . $upc . "'";
    $result_p = mysqli_query($conn, $query_p);
    $row_p = mysqli_fetch_array($result_p);
    $product_id = $row_p['product_id'];

    $cart_array = array(
        'vatexempt' => '0',
        'cash_amount' => '0',
        'creditcalculate' => '0',
        'creditcash_amount' => '0',
        'credit_amount' => '0',
        'discount' => '0'
    );
    $_SESSION["cart_data"][0] = $cart_array;

    if (isset($_REQUEST['qty']) && $_REQUEST['qty'] != '') {
        $item_quantity = (int)$_REQUEST['qty'];
    }

    $query = "
        SELECT 
            p.product_id,
            p.product_item,
            p.unit_price,
            COALESCE(SUM(i.quantity_ttl), 0) as quantity_ttl
        FROM 
            product p
        LEFT JOIN 
            inventory i
        ON 
            p.product_id = i.product_id
        WHERE 
            p.upc = '$upc'
        GROUP BY 
            p.product_id, p.product_item, p.unit_price";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 0) {
        echo "wrong";
    } else {
        while ($row = mysqli_fetch_array($result)) {
            $available_quantity = $row['quantity_ttl'];

            if ($item_quantity > $available_quantity) {
                $item_quantity = $available_quantity;
                echo "0";
            }

            if (isset($_SESSION["cart"])) {
                $item_array_id = array_column($_SESSION["cart"], "product_id");

                if (!in_array($row['product_id'], $item_array_id)) {
                    $item_array = array(
                        'product_id' => $row['product_id'],
                        'product_item' => $row['product_item'],
                        'unit_price' => $row['unit_price'],
                        'quantity_ttl' => $item_quantity
                    );
                    array_unshift($_SESSION["cart"], $item_array);
                } else {
                    $key = array_search($row['product_id'], array_column($_SESSION["cart"], 'product_id'));

                    if (isset($_REQUEST['qty']) && $_REQUEST['qty'] != '') {
                        if (($_SESSION["cart"][$key]['quantity_ttl'] + $item_quantity) > $available_quantity) {
                            $_SESSION["cart"][$key]['quantity_ttl'] = $available_quantity;
                            echo "0";
                        } else {
                            $_SESSION["cart"][$key]['quantity_ttl'] += $item_quantity;
                        }
                    } else {
                        if (($_SESSION["cart"][$key]['quantity_ttl'] + 1) > $available_quantity) {
                            $_SESSION["cart"][$key]['quantity_ttl'] = $available_quantity;
                            echo "0";
                        } else {
                            $_SESSION["cart"][$key]['quantity_ttl'] += 1;
                        }
                    }
                }
            } else {
                $item_array = array(
                    'product_id' => $row['product_id'],
                    'product_item' => $row['product_item'],
                    'unit_price' => $row['unit_price'],
                    'quantity_ttl' => $item_quantity
                );
                $_SESSION["cart"] = array($item_array);
            }
        }
    }
}

if (isset($_REQUEST['product_id'])) {
    $product_id = mysqli_real_escape_string($conn, $_REQUEST['product_id']);
    
    $cart_array = array(
        'vatexempt' => '0',
        'cash_amount' => '0',
        'creditcalculate' => '0',
        'creditcash_amount' => '0',
        'credit_amount' => '0',
        'discount' => '0'
    );
    $_SESSION["cart_data"][0] = $cart_array;

    $item_quantity = isset($_REQUEST['qty']) && $_REQUEST['qty'] != '' ? (int)$_REQUEST['qty'] : 1;

    $query = "SELECT 
            p.product_id,
            p.product_item,
            p.unit_price,
            COALESCE(SUM(i.quantity_ttl), 0) as quantity_ttl
        FROM 
            product p
        LEFT JOIN 
            inventory i
        ON 
            p.product_id = i.product_id
        WHERE 
            p.product_id = '$product_id'
        GROUP BY 
            p.product_id, p.product_item, p.unit_price";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        $total_stock = $row['quantity_ttl'];
        
        if ($item_quantity > $total_stock) {
            $item_quantity = $total_stock;
        }

        if (isset($_SESSION["cart"])) {
            $item_array_id = array_column($_SESSION["cart"], "product_id");

            if (!in_array($product_id, $item_array_id)) {
                $item_array = array(
                    'product_id' => $row['product_id'],
                    'product_item' => $row['product_item'],
                    'unit_price' => $row['unit_price'],
                    'quantity_ttl' => $item_quantity
                );
                array_unshift($_SESSION["cart"], $item_array);
            } else {
                $key = array_search($product_id, array_column($_SESSION["cart"], 'product_id'));

                $current_quantity = $_SESSION["cart"][$key]['quantity_ttl'];
                $new_quantity = $item_quantity + $current_quantity;

                if ($new_quantity > $total_stock) {
                    $_SESSION["cart"][$key]['quantity_ttl'] = $total_stock;
                    echo "0"; // Quantity adjusted to the maximum available stock
                } else {
                    $_SESSION["cart"][$key]['quantity_ttl'] = $new_quantity;
                }
            }
        } else {
            $item_array = array(
                'product_id' => $row['product_id'],
                'product_item' => $row['product_item'],
                'unit_price' => $row['unit_price'],
                'quantity_ttl' => $item_quantity
            );
            $_SESSION["cart"] = array($item_array);
        }
    } else {
        echo "Product not found";
    }
}

if(isset($_REQUEST['deleteitem'])){
    $key = array_search($_REQUEST['product_id_del'], array_column($_SESSION["cart"], 'product_id'));
    array_splice($_SESSION["cart"], $key, 1);
}





if (isset($_POST['search'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search']);

    $query = "
        SELECT product_id AS value, product_item AS label
        FROM product
        WHERE product_item LIKE '%$search%' OR upc LIKE '%$search%'
        LIMIT 15
    ";

    $result = mysqli_query($conn, $query);

    if ($result) {
        $response = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $response[] = $row;
        }
        echo json_encode($response);
    } else {
        echo json_encode(array('error' => 'Query failed'));
    }
}
?>