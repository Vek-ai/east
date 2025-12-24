<?php
/*
Plugin Name: Car Database Admin with CRUD and Preloaded Cars
Description: A plugin to manage car data with CRUD operations, QR codes, and status fields. Preloads car data during activation.
Version: 1.4
Author: Your Name
*/

defined('ABSPATH') || exit;

// Include the PHP QR Code library
include('qrlib.php');

// Create the SQLite database and table upon plugin activation
register_activation_hook(__FILE__, 'car_db_sqlite_init_db');

function car_db_sqlite_init_db() {
    $db_file = plugin_dir_path(__FILE__) . 'car-database.sqlite';

    // Check if SQLite database file exists, if not create it
    if (!file_exists($db_file)) {
        try {
            $dbh = new PDO('sqlite:' . $db_file);
            // Create a table for cars with default Status as 1
            $dbh->exec("
                CREATE TABLE IF NOT EXISTS cars (
                    Carid INTEGER PRIMARY KEY,
                    Plate_number TEXT NOT NULL,
                    Body_Type TEXT NOT NULL,
                    Make TEXT NOT NULL,
                    Model TEXT NOT NULL,
                    Colour TEXT NOT NULL,
                    Year_of_Manufacture INTEGER NOT NULL,
                    Garaged_Address TEXT NOT NULL,
                    Status INTEGER NOT NULL DEFAULT 1
                )
            ");

            // Insert the car data provided
            $cars = [
                [1, '1IK4JR', 'S WAG', 'VOLKS', 'TIGUAN', 'BLK', 2009, '36 WESTWOOD DRIVE, RAVENHALL', 1],
                [2, '1QV5ZK', 'VAN', 'HYNDAI', 'TQ', 'SIL', 2011, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [3, 'KGB99', 'WAGON', 'L ROV', 'VELAR', 'WHI', 2017, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [4, '1LJ6JC', 'SEDAN', 'FORD', 'FUTURA', 'SIL', 2003, '36 WESTWOOD DRIVE, RAVENHALL', 1],
                [5, '1UN2AU', 'SEDAN', 'HOLDEN', 'COMMOD', 'RED', 2001, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [6, '1YP5IO', 'SEDAN', 'HYNDAI', 'ACCENT', 'RED', 2018, '36 WESTWOOD DR, RAVENHALL', 1],
                [7, '1XD5RO', 'SEDAN', 'HOLDEN', 'CRUZE', 'SIL', 2013, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [8, '1NG3JL', 'SEDAN', 'HYNDAI', 'ACCENT', 'WHI', 2018, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [9, '1NA6JU', 'SEDAN', 'MERC B', 'C200', 'WHI', 2015, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [10, '1NO3RH', 'WAGON', 'TOYOTA', 'RAV4', 'WHI', 2018, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [11, '1PC2SM', 'SEDAN', 'HYNDAI', 'ELANTR', 'WHI', 2019, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [12, '1YK4OY', 'SEDAN', 'HYNDAI', 'ELANTR', 'WHI', 2019, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [13, '1BP5IY', 'WAGON', 'FORD', 'KUGA', 'BLU', 2013, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [14, 'ANL614', 'WAGON', 'MERC B', 'ML350', 'WHI', 2014, '36 WESTWOOD DRIVE, RAVENHALL', 1],
                [15, '1QQ6IK', 'SEDAN', 'TOYOTA', 'CAMRY', 'SIL', 2019, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [16, '1JL3OM', 'WAGON', 'SUBARU', 'XV', 'WHI', 2017, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [17, '1JL6LG', 'SEDAN', 'HYNDAI', 'ELANTR', 'WHI', 2017, '36 WESTWOOD DRIVE, RAVENHALL', 1],
                [18, '1RE6PE', 'SEDAN', 'HYNDAI', 'ELANTR', 'WHI', 2020, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [19, '1KU1CD', 'UTIL', 'TOYOTA', 'HILUX', 'WHI', 2017, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [20, 'KGB43', 'WAGON', 'MERC B', 'GLE43', 'WHI', 2017, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [21, '1ME8IQ', 'SEDAN', 'HYNDAI', 'ACCENT', 'RED', 2017, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [22, 'YJG117', 'UTIL', 'FORD', 'RANGER', 'WHI', 2010, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [23, '1VC1HW', 'WAGON', 'NISSAN', 'XTRAIL', 'WHI', 2018, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [24, '1YB1BT', 'SEDAN', 'HYNDAI', 'ACCENT', 'WHI', 2016, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [25, 'CUO837', 'UTIL', 'FORD', 'RANGER', 'GRY', 2023, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [26, '2AL4HA', 'WAGON', 'CHERY', 'TIGGO', 'WHI', 2023, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [27, '2AL4IU', 'WAGON', 'CHERY', 'TIGGO', 'WHI', 2024, '7 VALNERE ST, MARIBYRNONG VIC 3032, MARIBYRNONG, VIC', 1],
                [28, 'BTS589', 'SEDAN', 'TOYOTA', 'CAMRY', 'SIL', 2018, '7 VALNER', 1]
            ];

            $stmt = $dbh->prepare("INSERT INTO cars (Carid, Plate_number, Body_Type, Make, Model, Colour, Year_of_Manufacture, Garaged_Address, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($cars as $car) {
                $stmt->execute($car);
            }

        } catch (PDOException $e) {
            error_log('SQLite Error: ' . $e->getMessage());
        }
    }
}

// Add a menu item in the WordPress Admin area
add_action('admin_menu', 'car_db_admin_menu');

function car_db_admin_menu() {
    add_menu_page(
        'Car Database',          // Page title
        'Car Database',          // Menu title
        'manage_options',        // Capability
        'car-database-admin',    // Menu slug
        'car_db_display_data',   // Function to display data
        'dashicons-car',         // Icon
        6                        // Position in the menu
    );

    add_submenu_page(
        'car-database-admin',
        'Add Car',
        'Add Car',
        'manage_options',
        'car-database-admin-add',
        'car_db_add_car'
    );

    add_submenu_page(
        null, // Not displayed in the menu
        'Edit Car',
        'Edit Car',
        'manage_options',
        'car-database-admin-edit',
        'car_db_edit_car'
    );
	// Add submenu for displaying renters list
    add_submenu_page(
        'car-database-admin',   // Parent slug (main plugin menu)
        'Renters List',         // Page title
        'Renters List',         // Menu title
        'manage_options',       // Capability
        'car-database-renters', // Submenu slug
        'car_db_display_renters'// Function to display renters
    );
}

// Function to convert status codes to human-readable format
function car_db_get_status($status) {
    return $status == 1 ? 'Available' : 'Hired';
}

// Function to add a new car
function car_db_add_car() {
    global $wpdb;
    $db_file = plugin_dir_path(__FILE__) . 'car-database.sqlite';
    $dbh = new PDO('sqlite:' . $db_file);

    if ($_POST['submit']) {
        $stmt = $dbh->prepare("INSERT INTO cars (Plate_number, Body_Type, Make, Model, Colour, Year_of_Manufacture, Garaged_Address, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['Plate_number'], $_POST['Body_Type'], $_POST['Make'], $_POST['Model'], $_POST['Colour'], $_POST['Year_of_Manufacture'], $_POST['Garaged_Address'], $_POST['Status']]);
        wp_redirect(admin_url('admin.php?page=car-database-admin'));
        exit;
    }

    echo '<div class="wrap"><h1>Add New Car</h1>';
    echo '<form method="post" action="">';
    car_db_form_fields();
    submit_button('Add Car');
    echo '</form></div>';
}

// Function to edit car details
function car_db_edit_car() {
    global $wpdb;
    $db_file = plugin_dir_path(__FILE__) . 'car-database.sqlite';
    $dbh = new PDO('sqlite:' . $db_file);

    $Carid = intval($_GET['Carid']);
    $car = $dbh->query("SELECT * FROM cars WHERE Carid = $Carid")->fetch(PDO::FETCH_ASSOC);

    if ($_POST['submit']) {
        $stmt = $dbh->prepare("UPDATE cars SET Plate_number = ?, Body_Type = ?, Make = ?, Model = ?, Colour = ?, Year_of_Manufacture = ?, Garaged_Address = ?, Status = ? WHERE Carid = ?");
        $stmt->execute([$_POST['Plate_number'], $_POST['Body_Type'], $_POST['Make'], $_POST['Model'], $_POST['Colour'], $_POST['Year_of_Manufacture'], $_POST['Garaged_Address'], $_POST['Status'], $Carid]);
        wp_redirect(admin_url('admin.php?page=car-database-admin'));
        exit;
    }

    echo '<div class="wrap"><h1>Edit Car</h1>';
    echo '<form method="post" action="">';
    car_db_form_fields($car);
    submit_button('Update Car');
    echo '</form></div>';
}

// Function to generate form fields
function car_db_form_fields($car = null) {
    $car = (object) $car;
    echo '<table class="form-table">
        <tr><th>Plate Number</th><td><input type="text" name="Plate_number" value="' . esc_attr($car->Plate_number) . '" required></td></tr>
        <tr><th>Body Type</th><td><input type="text" name="Body_Type" value="' . esc_attr($car->Body_Type) . '" required></td></tr>
        <tr><th>Make</th><td><input type="text" name="Make" value="' . esc_attr($car->Make) . '" required></td></tr>
        <tr><th>Model</th><td><input type="text" name="Model" value="' . esc_attr($car->Model) . '" required></td></tr>
        <tr><th>Colour</th><td><input type="text" name="Colour" value="' . esc_attr($car->Colour) . '" required></td></tr>
        <tr><th>Year of Manufacture</th><td><input type="number" name="Year_of_Manufacture" value="' . esc_attr($car->Year_of_Manufacture) . '" required></td></tr>
        <tr><th>Garaged Address</th><td><input type="text" name="Garaged_Address" value="' . esc_attr($car->Garaged_Address) . '" required></td></tr>
        <tr><th>Status</th><td>
            <select name="Status" required>
                <option value="1" ' . selected($car->Status, 1, false) . '>Available</option>
                <option value="2" ' . selected($car->Status, 2, false) . '>Hired</option>
            </select>
        </td></tr>
    </table>';
}

// Display the car data in the admin panel with QR codes
function car_db_display_data() {
    $db_file = plugin_dir_path(__FILE__) . 'car-database.sqlite';

    try {
        $dbh = new PDO('sqlite:' . $db_file);
        $stmt = $dbh->query("SELECT * FROM cars");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo '<div class="wrap"><h1>Car Database with QR Codes and Status</h1>';
        echo '<a href="' . admin_url('admin.php?page=car-database-admin-add') . '" class="page-title-action">Add New Car</a>';
        if ($results) {
            echo '<table class="widefat fixed" cellspacing="0">';
            echo '<thead><tr><th>Car ID</th><th>Plate Number</th><th>Body Type</th><th>Make</th><th>Model</th><th>Colour</th><th>Year of Manufacture</th><th>Garaged Address</th><th>Status</th><th>QR Code</th><th>Actions</th></tr></thead>';
            echo '<tbody>';
            foreach ($results as $row) {
                // Generate the QR code for each car
                $data = home_url('/carhire/?carid=' . $row['Carid']);  // WordPress site URL
                
                // Get the WordPress uploads directory
                $upload_dir = wp_upload_dir();
                $upload_path = $upload_dir['basedir'] . '/qrcodes/';
                $upload_url = $upload_dir['baseurl'] . '/qrcodes/';

                // Ensure the directory exists
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0755, true);
                }

                // QR code file path
                $file = $upload_path . 'qrcode_' . $row['Carid'] . '.png';
                $size = 5;

                // Create the QR code if it doesn't already exist
                if (!file_exists($file)) {
                    QRcode::png($data, $file, QR_ECLEVEL_L, $size);
                }

                // Display the car data and the QR code
                echo '<tr>';
                echo '<td>' . esc_html($row['Carid']) . '</td>';
                echo '<td>' . esc_html($row['Plate_number']) . '</td>';
                echo '<td>' . esc_html($row['Body_Type']) . '</td>';
                echo '<td>' . esc_html($row['Make']) . '</td>';
                echo '<td>' . esc_html($row['Model']) . '</td>';
                echo '<td>' . esc_html($row['Colour']) . '</td>';
                echo '<td>' . esc_html($row['Year_of_Manufacture']) . '</td>';
                echo '<td>' . esc_html($row['Garaged_Address']) . '</td>';
                echo '<td>' . car_db_get_status($row['Status']) . '</td>';  // Display "Available" or "Hired"
                echo '<td><img src="' . esc_url($upload_url . 'qrcode_' . $row['Carid'] . '.png') . '" alt="QR Code"></td>';
                echo '<td><a href="' . admin_url('admin.php?page=car-database-admin-edit&Carid=' . $row['Carid']) . '">Edit</a></td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No car data found in the database.</p>';
        }
        echo '</div>';
    } catch (PDOException $e) {
        echo '<p>SQLite Error: ' . $e->getMessage() . '</p>';
    }
}
// Shortcode to display car details by Carid from URL
// Shortcode logic for renting or returning a car
function car_db_display_car_rental_shortcode() {
    // Get the carid from the URL
    $carid = isset($_GET['carid']) ? intval($_GET['carid']) : 0;

    // If no carid is provided, display a message
    if (!$carid) {
        return 'No car ID provided.';
    }

    // Connect to the SQLite database
    $db_file = plugin_dir_path(__FILE__) . 'car-database.sqlite';
    $dbh = new PDO('sqlite:' . $db_file);

    // Fetch the car status
    $stmt = $dbh->prepare("SELECT Status FROM cars WHERE Carid = ?");
    $stmt->execute([$carid]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$car) {
        return 'Car not found.';
    }

    // Check if car is available (status = 1)
    if ($car['Status'] == 1) {
        // If the form is submitted for renting the car
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rent_car'])) {
            // Handle driver’s license picture upload
            if (!empty($_FILES['driverslicense']['name'])) {
                $upload_dir = wp_upload_dir();
                $file_name = $_FILES['driverslicense']['name'];
                $file_tmp = $_FILES['driverslicense']['tmp_name'];
                $file_path = $upload_dir['path'] . '/' . $file_name;
                $file_url = $upload_dir['url'] . '/' . $file_name;
                
                move_uploaded_file($file_tmp, $file_path);

                // Save the renter information to the database
                $stmt = $dbh->prepare("INSERT INTO renters (Firstname, Lastname, Driverslicense_imgurl, Rentdatetime, Carid) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$_POST['firstname'], $_POST['lastname'], $file_url, date('Y-m-d H:i:s'), $carid]);

                // Update car status to hired (status = 2)
                $stmt = $dbh->prepare("UPDATE cars SET Status = 2 WHERE Carid = ?");
                $stmt->execute([$carid]);

                return 'Car rented successfully!';
            }
        }

        // Display the rental form for available cars
        ob_start();
        ?>
        <form method="post" enctype="multipart/form-data">
            <label for="firstname">First Name:</label>
            <input type="text" name="firstname" id="firstname" required><br>

            <label for="lastname">Last Name:</label>
            <input type="text" name="lastname" id="lastname" required><br>

            <label for="driverslicense">Take a Photo of Driver's License:</label>
            <input type="file" name="driverslicense" id="driverslicense" accept="image/*" value="Take a Photo" capture="user" required><br>

            <input type="submit" name="rent_car" value="Rent Car">
        </form>
        <?php
        return ob_get_clean();

    } else {
        // If the car is already hired, handle returning the car
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['return_car'])) {
            // Update car status to available (status = 1) and update return datetime in renters table
            $stmt = $dbh->prepare("UPDATE cars SET Status = 1 WHERE Carid = ?");
            $stmt->execute([$carid]);

            // Update the return date in the renters table
            $stmt = $dbh->prepare("UPDATE renters SET Returndatetime = ? WHERE Carid = ? AND Returndatetime IS NULL");
            $stmt->execute([date('Y-m-d H:i:s'), $carid]);

            return 'Car returned successfully!';
        }

        // Display return form for hired cars
        ob_start();
        ?>
        <form method="post">
            <input type="submit" name="return_car" value="Return Car">
        </form>
        <?php
        return ob_get_clean();
    }
}
add_shortcode('display_car_details', 'car_db_display_car_rental_shortcode');

function car_db_display_renters() {
    $db_file = plugin_dir_path(__FILE__) . 'car-database.sqlite';
    try {
        $dbh = new PDO('sqlite:' . $db_file);

        // Fetch renters and related car details
        $stmt = $dbh->query("
            SELECT 
                renters.Renterid,
                cars.Plate_number,
                renters.Firstname,
                renters.Lastname,
                renters.Driverslicense_imgurl,
                renters.Rentdatetime,
                renters.Returndatetime
            FROM renters
            INNER JOIN cars ON renters.Carid = cars.Carid
        ");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Display the renters data in a table format
        echo '<div class="wrap"><h1>Renters List</h1>';
        if ($results) {
            echo '<table class="widefat fixed" cellspacing="0">';
            echo '<thead><tr>
                    <th>Plate Number</th>
                    <th>Rented By (First Name Last Name)</th>
                    <th>Driver\'s License</th>
                    <th>Date/Time Rented</th>
                    <th>Date/Time Returned</th>
                  </tr></thead>';
            echo '<tbody>';
            foreach ($results as $row) {
                echo '<tr>';
                echo '<td>' . esc_html($row['Plate_number']) . '</td>';
                echo '<td>' . esc_html($row['Firstname'] . ' ' . $row['Lastname']) . '</td>';
                echo '<td><img src="' . esc_url($row['Driverslicense_imgurl']) . '" alt="License" width="100"></td>';
                echo '<td>' . esc_html($row['Rentdatetime']) . '</td>';
                echo '<td>' . ($row['Returndatetime'] ? esc_html($row['Returndatetime']) : 'Not yet returned') . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No rental records found.</p>';
        }
        echo '</div>';
    } catch (PDOException $e) {
        echo '<p>SQLite Error: ' . $e->getMessage() . '</p>';
    }
}

?>
