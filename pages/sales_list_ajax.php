<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if (isset($_POST['search_customer'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search_customer']);

    $query = "
        SELECT 
            customer_id AS value, 
            CONCAT(customer_first_name, ' ', customer_last_name) AS label
        FROM 
            customer
        WHERE 
            (customer_first_name LIKE '%$search%' 
            OR 
            customer_last_name LIKE '%$search%')
            AND status != '3'
        LIMIT 15
    ";

    $result = mysqli_query($conn, $query);

    if ($result) {
        $response = array();

        $response[] = array(
            'value' => 'all_customers',
            'label' => 'All Customers'
        );

        while ($row = mysqli_fetch_assoc($result)) {
            $response[] = $row;
        }

        echo json_encode($response);
    } else {
        echo json_encode(array('error' => 'Query failed'));
    }
}

if (isset($_POST['search_orders'])) {
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);

    $query = "
        SELECT o.*, CONCAT(c.customer_first_name, ' ', c.customer_last_name) AS customer_name
        FROM orders AS o
        LEFT JOIN customer AS c ON c.customer_id = o.customerid
        WHERE 1 = 1
    ";

    if (!empty($customer_name) && $customer_name != 'all_customers') {
        $query .= " AND (c.customer_first_name LIKE '%$customer_name%' OR c.customer_last_name LIKE '%$customer_name%') ";
    }

    if (!empty($date_from) && !empty($date_to)) {
        $query .= " AND (o.order_date >= '$date_from' AND o.order_date <= '$date_to') ";
    }

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            ?>
            <tr>
                <td>
                    <?= htmlspecialchars($row['orderid']) ?>
                </td>
                <td>
                    <?= htmlspecialchars(date("F d, Y", strtotime($row['order_date']))) ?>
                </td>
                <td>
                    <?= htmlspecialchars(date("h:i A", strtotime($row['order_date']))) ?>
                </td>
                <td>
                    <?= get_staff_name($row['cashier']) ?>
                </td>
                <td>
                    <?= htmlspecialchars($row['customer_name']) ?>
                </td>
                <td class="text-end">
                    $ <?= number_format($row['discounted_price'], 2) ?>
                </td>
            </tr>
            <?php
        }
    } else {
        echo "<tr><td colspan='6'>No orders found</td></tr>";
    }
}


