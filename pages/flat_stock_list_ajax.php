<?php
include '../includes/dbconn.php'; // Include your database connection

if (isset($_POST['color_code'])) {
    $color_code = $_POST['color_code'];
    $quantity = $_POST['quantity']; // Quantity from the product
    $custom_length = $_POST['custom_length']; // Custom length from the product

    // Fetch coils based on the color code
    $query = "SELECT coil, length, tag_number, added_date FROM coil WHERE color = '$color_code'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $coil_length = $row['length'];
            // Calculate the number of products this coil can make
            $products_made = $coil_length /  $custom_length;

            echo "<tr>
                    <td><input type='checkbox' class='coil-checkbox' data-coil-length='{$coil_length}' data-quantity='{$quantity}' data-custom-length='{$custom_length}'></td>
                    <td>{$row['coil']}</td>
                    <td>{$coil_length}</td>
                    <td>{$row['tag_number']}</td>
                    <td>{$row['added_date']}</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No coils found with this color.</td></tr>";
    }
}
?>

