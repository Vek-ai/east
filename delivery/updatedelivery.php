<?php
// Database connection details
$host = "localhost";
$username = "benguetf_eastkentucky";                
$password = "O3K9-T6&{oW[";         
$dbname = "benguetf_eastkentucky";  

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the 'id' from the URL
$order_estimate_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : null;
    $datetime = date('Y-m-d H:i:s'); // Current server datetime
    $photoAddress = isset($_POST['photo_address']) ? $_POST['photo_address'] : '';

    // Check if a file was uploaded
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'deliverypictures/'; // Directory to save the uploaded files
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileTmpPath = $_FILES['picture']['tmp_name'];
        $fileName = basename($_FILES['picture']['name']);
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = 'order_' . $order_estimate_id . '.' . $fileExtension;
        $uploadPath = $uploadDir . $newFileName;

        // Move the uploaded file to the server directory
        if (move_uploaded_file($fileTmpPath, $uploadPath)) {
            // Update the database with the status, image filename, and location data
            $sql = "UPDATE order_estimate 
                    SET status = 3, image_url = ?, latitude = ?, longitude = ?, datetime = ?, photo_address = ? 
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $newFileName, $latitude, $longitude, $datetime, $photoAddress, $order_estimate_id);

            if ($stmt->execute()) {
                $successMessage = "Picture and location data uploaded successfully!";
            } else {
                $errorMessage = "Error updating database: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $errorMessage = "Failed to save the uploaded file. Please check file permissions.";
        }
    } else {
        $errorMessage = "No picture uploaded or an error occurred.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Picture Upload</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3 class="text-center">Upload Picture for Order Estimate ID: <?php echo htmlspecialchars($order_estimate_id); ?></h3>
        </div>
        <div class="card-body">
            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            <form action="" method="POST" enctype="multipart/form-data" onsubmit="return getLocation()">
                <div class="mb-3">
                    <label for="picture" class="form-label">Upload Picture:</label>
                    <input type="file" name="picture" id="picture" class="form-control" accept="image/*" capture="camera" required>
                    <div class="invalid-feedback">Please select a picture to upload.</div>
                </div>
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
                <input type="hidden" name="photo_address" id="photo_address">
                <button type="submit" class="btn btn-success w-100">Submit</button>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Get location data
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(position => {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;

            // Set latitude and longitude fields
            document.getElementById('latitude').value = latitude;
            document.getElementById('longitude').value = longitude;

            // Use a reverse geocoding API to get the address (optional)
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('photo_address').value = data.display_name || '';
                })
                .catch(error => console.error('Error fetching address:', error));
        }, error => {
            alert('Error getting location: ' + error.message);
        });
    } else {
        alert('Geolocation is not supported by this browser.');
        return false;
    }
    return true;
}
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            position => {
                const latitude = position.coords.latitude;
                const longitude = position.coords.longitude;

                // Debugging messages
                console.log("Latitude:", latitude);
                console.log("Longitude:", longitude);

                document.getElementById('latitude').value = latitude;
                document.getElementById('longitude').value = longitude;

                // Fetch address (optional)
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('photo_address').value = data.display_name || '';
                        console.log("Address:", data.display_name);
                    })
                    .catch(error => console.error('Error fetching address:', error));
            },
            error => {
                console.error("Error getting location:", error.message);
                alert("Error: " + error.message);
            }
        );
    } else {
        alert("Geolocation is not supported by this browser.");
    }
}

</script>
</body>
</html>
