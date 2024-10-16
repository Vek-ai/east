<?php
require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $taxid = mysqli_real_escape_string($conn, $_POST['taxid']);
        $tax_status_desc = mysqli_real_escape_string($conn, $_POST['tax_status_desc']);
        $percentage = mysqli_real_escape_string($conn, $_POST['percentage']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM customer_tax WHERE taxid = '$taxid'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_tax_status_desc = $row['tax_status_desc'];
            $current_percentage = $row['percentage'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($tax_status_desc != $current_tax_status_desc) {
                $checkTaxStatusDesc = "SELECT * FROM customer_tax WHERE tax_status_desc = '$tax_status_desc'";
                $resultTaxStatusDesc = mysqli_query($conn, $checkTaxStatusDesc);
                if (mysqli_num_rows($resultTaxStatusDesc) > 0) {
                    $duplicates[] = "Tax Status Description";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE customer_tax SET tax_status_desc = '$tax_status_desc', percentage = '$percentage' WHERE taxid = '$taxid'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "Customer tax updated successfully.";
                } else {
                    echo "Error updating customer tax: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkTaxStatusDesc = "SELECT * FROM customer_tax WHERE tax_status_desc = '$tax_status_desc'";
            $resultTaxStatusDesc = mysqli_query($conn, $checkTaxStatusDesc);
            if (mysqli_num_rows($resultTaxStatusDesc) > 0) {
                $duplicates[] = "Tax Status Description";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO customer_tax (tax_status_desc, percentage) VALUES ('$tax_status_desc', '$percentage')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "New customer tax added successfully.";
                } else {
                    echo "Error adding customer tax: " . mysqli_error($conn);
                }
            }
        }
    } 

    if ($action == "delete") {
      $taxid = mysqli_real_escape_string($conn, $_POST['taxid']);

      // SQL query to delete the record
      $deleteQuery = "DELETE FROM customer_tax WHERE taxid = '$taxid'";
      if (mysqli_query($conn, $deleteQuery)) {
          echo "Customer tax deleted successfully.";
      } else {
          echo "Error deleting customer tax: " . mysqli_error($conn);
      }
    }
    mysqli_close($conn);
}
?>
