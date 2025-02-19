<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $table = "product_color";
    
        $primaryKeyQuery = "SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'";
        $primaryKeyResult = mysqli_query($conn, $primaryKeyQuery);
        $primaryKeyRow = mysqli_fetch_assoc($primaryKeyResult);
        $primaryKey = $primaryKeyRow['Column_name'];
    
        if (!$primaryKey) {
            echo "Error: Unable to retrieve primary key for table $table";
            exit;
        }
    
        $primaryKeyValue = mysqli_real_escape_string($conn, $_POST[$primaryKey]);
    
        $fields = [];
        foreach ($_POST as $key => $value) {
            if ($key != $primaryKey) {
                $fields[$key] = mysqli_real_escape_string($conn, $value);
            }
        }
    
        $checkQuery = "SELECT * FROM $table WHERE $primaryKey = '$primaryKeyValue'";
        $result = mysqli_query($conn, $checkQuery);
    
        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE $table SET ";
    
            foreach ($fields as $column => $value) {
                $columnExists = mysqli_query($conn, "SHOW COLUMNS FROM $table LIKE '$column'");
                if (mysqli_num_rows($columnExists) > 0) {
                    $updateQuery .= "$column = '$value', ";
                }
            }
    
            $updateQuery = rtrim($updateQuery, ", ");
            $updateQuery .= " WHERE $primaryKey = '$primaryKeyValue'";
    
            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating product: " . mysqli_error($conn);
            }
        } else {
            $columns = [];
            $values = [];
    
            foreach ($fields as $column => $value) {
                $columnExists = mysqli_query($conn, "SHOW COLUMNS FROM $table LIKE '$column'");
                if (mysqli_num_rows($columnExists) > 0) {
                    $columns[] = $column;
                    $values[] = "'$value'";
                }
            }
    
            $columnsStr = implode(", ", $columns);
            $valuesStr = implode(", ", $values);
    
            $insertQuery = "INSERT INTO $table ($primaryKey, $columnsStr) VALUES ('$primaryKeyValue', $valuesStr)";
    
            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error adding product: " . mysqli_error($conn);
            }
        }
    }    
    
    mysqli_close($conn);
}
?>
