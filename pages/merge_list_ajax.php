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

if (isset($_POST['search_merge'])) {
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);

    $query = "
        SELECT cm.*, 
            CONCAT(c1.customer_first_name, ' ', c1.customer_last_name) AS customer_name,
            CONCAT(c2.customer_first_name, ' ', c2.customer_last_name) AS merge_from_name
        FROM customer_merge cm
        LEFT JOIN customer c1 ON c1.customer_id = cm.customer_id
        LEFT JOIN customer c2 ON c2.customer_id = cm.merge_from
        WHERE 1 = 1
    ";

    if (!empty($customer_name) && $customer_name != 'All Customers') {
        $query .= " AND CONCAT(c1.customer_first_name, ' ', c1.customer_last_name) LIKE '%$customer_name%' ";
    }

    if (!empty($date_from) && !empty($date_to)) {
        $date_to .= ' 23:59:59';
        $query .= " AND (cm.merge_date >= '$date_from' AND cm.merge_date <= '$date_to') ";
    }

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $total_amount = 0;
        $total_count = 0;

        ?>
        <table id="merge_table" class="table table-hover mb-0 text-md-nowrap text-center">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Merged to Account</th>
                    <th>Merge Date</th>
                </tr>
            </thead>
            <tbody>
            <?php
            while ($row = mysqli_fetch_assoc($result)) {
                ?>
                <tr>
                    <td>
                        <?= get_customer_name($row['customer_id']) ?>
                    </td>
                    <td>
                        <?= get_customer_name($row['merge_from']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars(date("F d, Y", strtotime($row['merge_date']))) ?>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php
    } else {
        echo "<h4 class='text-center'>No customer account merges found</h4>";
    }
}


