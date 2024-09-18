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

function get_staff_name($staff_id){
    global $conn;
    $query = "SELECT staff_fname, staff_lname FROM staff WHERE staff_id = '$staff_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $fname = $row['staff_fname'] ?? '';
    $lname = $row['staff_lname'] ?? '';
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

function getProductName($product_id){
    global $conn;
    $query = "SELECT product_item FROM product WHERE product_id = '$product_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $product_item = $row['product_item'] ?? '';
    return  $product_item;
}

function getProductDetails($product_id) {
    global $conn;
    $product_id = mysqli_real_escape_string($conn, $product_id);
    $query = "SELECT * FROM product WHERE product_id = '$product_id'";
    $result = mysqli_query($conn, $query);
    $product = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $product = $row;
    }
    return $product;
}

function getWarehouseName($WarehouseID){
    global $conn;
    $query = "SELECT WarehouseName FROM warehouses WHERE WarehouseID = '$WarehouseID'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $WarehouseName = $row['WarehouseName'] ?? '';
    return  $WarehouseName;
}

function getWarehouseBinName($BinID){
    global $conn;
    $query = "SELECT BinCode FROM bins WHERE BinID = '$BinID'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $BinCode = $row['BinCode'] ?? '';
    return  $BinCode;
}

function getWarehouseShelfName($ShelfID){
    global $conn;
    $query = "SELECT ShelfCode FROM shelves WHERE ShelfID = '$ShelfID'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $ShelfCode = $row['ShelfCode'] ?? '';
    return  $ShelfCode;
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

function getStockTypeName($stock_type_id){
    global $conn;
    $query = "SELECT stock_type FROM stock_type WHERE stock_type_id = '$stock_type_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $stock_type = $row['stock_type'] ?? '';
    return  $stock_type;
}

function getColorName($color_id ){
    global $conn;
    $query = "SELECT color_name FROM paint_colors WHERE color_id  = '$color_id '";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $color_name = $row['color_name'] ?? '';
    return  $color_name;
}

function getColorFromID($product_id){
    global $conn;
    $query = "SELECT color FROM product WHERE product_id  = '$product_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $color = $row['color'] ?? '';
    return getColorName($color);
}

function getColorHexFromColorID($color_id){
    global $conn;
    $query = "SELECT color_code FROM paint_colors WHERE color_id = '" .$color_id ."'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);
    $color_code = $row['color_code'] ?? '';
    return $color_code;
}

function getColorHexFromProdID($product_id){
    global $conn;
    $query = "SELECT color FROM product WHERE product_id  = '$product_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $color = $row['color'] ?? '';
    return getColorHexFromColorID($color);
}

function getGaugeName($product_gauge_id){
    global $conn;
    $query = "SELECT product_gauge FROM product_gauge WHERE product_gauge_id = '$product_gauge_id  '";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $product_gauge = $row['product_gauge'] ?? '';
    return  $product_gauge;
}

function getGradeName($product_grade_id){
    global $conn;
    $query = "SELECT product_grade FROM product_grade WHERE product_grade_id = '$product_grade_id  '";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $product_grade = $row['product_grade'] ?? '';
    return  $product_grade;
}

function getGradeFromID($product_id){
    global $conn;
    $query = "SELECT grade FROM product WHERE product_id  = '$product_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $grade = $row['grade'] ?? '';
    return getGradeName($grade);
}

function getWarrantyTypeName($product_warranty_type_id ){
    global $conn;
    $query = "SELECT product_warranty_type FROM product_warranty_type WHERE product_warranty_type_id = '$product_warranty_type_id   '";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $product_warranty_type = $row['product_warranty_type'] ?? '';
    return  $product_warranty_type;
}

function getProfileTypeName($profile_type_id){
    global $conn;
    $query = "SELECT profile_type FROM profile_type WHERE profile_type_id = '$profile_type_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $profile_type = $row['profile_type'] ?? '';
    return  $profile_type;
}

function getProfileFromID($product_id){
    global $conn;
    $query = "SELECT profile FROM product WHERE product_id  = '$product_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $profile = $row['profile'] ?? '';
    return getProfileTypeName($profile);
}

function getPaintProviderName($provider_id){
    global $conn;
    $query = "SELECT provider_name FROM paint_providers WHERE provider_id = '$provider_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $provider_name = $row['provider_name'] ?? '';
    return  $provider_name;
}

function getPackPieces($id){
    global $conn;
    $query = "SELECT pieces_count FROM product_pack WHERE id = '$id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $pieces_count = $row['pieces_count'] ?? '';
    return  $pieces_count;
}

function getPackName($id){
    global $conn;
    $query = "SELECT pack_name FROM product_pack WHERE id = '$id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $pack_name = $row['pack_name'] ?? '';
    return  $pack_name;
}

function getSupplierType($supplier_type_id){
    global $conn;
    $query = "SELECT supplier_type FROM supplier_type WHERE supplier_type_id = '$supplier_type_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $supplier_type = $row['supplier_type'] ?? '';
    return  $supplier_type;
}

function get_customer_name($customer_id){
    global $conn;
    $query = "SELECT customer_first_name, customer_last_name FROM customer WHERE customer_id = '$customer_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $customer_name = $row['customer_first_name'] . ' ' .$row['customer_last_name'];
    return  $customer_name;
}

function generateRandomUPC() {
    global $conn;

    do {
        $upc_code = '';
        for ($i = 0; $i < 11; $i++) {
            $upc_code .= mt_rand(0, 9);
        }

        $odd_sum = 0;
        $even_sum = 0;
        for ($i = 0; $i < 11; $i++) {
            if ($i % 2 === 0) {
                $odd_sum += $upc_code[$i];
            } else {
                $even_sum += $upc_code[$i];
            }
        }

        $total_sum = (3 * $odd_sum) + $even_sum;
        $check_digit = (10 - ($total_sum % 10)) % 10;

        $upc_code .= $check_digit;

        $query = "SELECT COUNT(*) as count FROM product WHERE upc = '$upc_code'";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        $count = $row['count'];

    } while ($count > 0);

    return $upc_code;
}

function getProductStockInStock($product_id) {
    global $conn;
    $product_id = mysqli_real_escape_string($conn, $product_id);
    $query = "SELECT quantity_in_stock
              FROM product
              WHERE product_id = '$product_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['quantity_in_stock'];
}

function getProductStockTotal($product_id) {
    global $conn;
    $product_id = mysqli_real_escape_string($conn, $product_id);
    $query = "SELECT COALESCE(SUM(quantity_ttl), 0) as total_quantity
              FROM inventory
              WHERE product_id = '$product_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total_quantity'];
}
?>