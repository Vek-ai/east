<?php
function get_userid(){
    if (isset($_COOKIE['userid'])) {
        $userId = $_COOKIE['userid'];
        return $userId;
    }
}

function get_name($userid){
    global $conn;
    $query = "SELECT fname, lname FROM users WHERE userid = '$userid'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $fname = $row['fname'] ?? '';
    $lname = $row['lname'] ?? '';
    return  "$fname $lname";
}

function get_role_name($emp_role_id){
    global $conn;
    $query = "SELECT emp_role FROM staff_roles WHERE emp_role_id = '$emp_role_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $emp_role = $row['emp_role'] ?? '';
    return  $emp_role;
}

function getWarehouseRowName($WarehouseRowID){
    global $conn;
    $query = "SELECT RowCode FROM warehouse_rows WHERE WarehouseRowID = '$WarehouseRowID'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $RowCode = $row['RowCode'] ?? '';
    return  $RowCode;
}

function getProductLineName($product_line_id){
    global $conn;
    $query = "SELECT product_line FROM product_line WHERE product_line_id = '$product_line_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $product_line = $row['product_line'] ?? '';
    return  $product_line;
}

function getProductCategoryName($product_category_id){
    global $conn;
    $query = "SELECT product_category FROM product_category WHERE product_category_id = '$product_category_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $product_category = $row['product_category'] ?? '';
    return  $product_category;
}

function getProductTypeName($product_type_id){
    global $conn;
    $query = "SELECT product_type FROM product_type WHERE product_type_id = '$product_type_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $product_type = $row['product_type'] ?? '';
    return  $product_type;
}
?>