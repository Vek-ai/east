<?php
include '../includes/dbconn.php'; // Include your database connection

if(isset($_POST['color_code'])) {
    $color_code = $_POST['color_code'];

    $query = "SELECT coil, length, tag_number, added_date FROM coil WHERE color = '$color_code'";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td>{$row['coil']}</td>
                    <td>{$row['length']}</td>
                    <td>{$row['tag_number']}</td>
                    <td>{$row['added_date']}</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No coils found with this color.</td></tr>";
    }
}
?>
