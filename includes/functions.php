<?php
include "calculate_price.php";
function get_userid(){
    if (isset($_COOKIE['userid'])) {
        $userId = $_COOKIE['userid'];
        return $userId;
    }
}

function get_name($staff_id){
    global $conn;
    $query = "SELECT staff_fname, staff_lname FROM staff WHERE staff_id = '$staff_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $fname = $row['staff_fname'] ?? '';
    $lname = $row['staff_lname'] ?? '';
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
    $query = "SELECT product_item, description FROM product WHERE product_id = '$product_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $product_item = !empty($row['product_item']) ? $row['product_item'] : $row['description'];
    return  $product_item;
}

function getProductColorMultName($id){
    global $conn;
    $query = "SELECT color FROM color_multiplier WHERE id = '$id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $color = $row['color'] ?? '';
    return  $color;
}

function getProductColorMultValue($id) {
    global $conn;
    $query = "SELECT multiplier FROM color_multiplier WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_array($result)) {
        return $row['multiplier'];
    }
    return 1;
}

function getStaffDetails($staff_id) {
    global $conn;
    $staff_id = mysqli_real_escape_string($conn, $staff_id);
    $query = "SELECT * FROM staff WHERE staff_id = '$staff_id'";
    $result = mysqli_query($conn, $query);
    $staff = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $staff = $row;
    }
    return $staff;
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

function getAvailabilityDetails($id) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT * FROM product_availability WHERE product_availability_id = '$id'";
    $result = mysqli_query($conn, $query);
    $product_availability = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $product_availability = $row;
    }
    return $product_availability;
}

function getCoilDetails($coil_id) {
    global $conn;
    $coil_id = mysqli_real_escape_string($conn, $coil_id);
    $query = "SELECT * FROM coil WHERE coil_id = '$coil_id'";
    $result = mysqli_query($conn, $query);
    $coil = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $coil = $row;
    }
    return $coil;
}

function getApprovalDetails($approval_id) {
    global $conn;
    $approval_id = mysqli_real_escape_string($conn, $approval_id);
    $query = "SELECT * FROM approval WHERE approval_id = '$approval_id'";
    $result = mysqli_query($conn, $query);
    $approval = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $approval = $row;
    }
    return $approval;
}

function getApprovalProductDetails($id) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT * FROM approval_product WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $approval_product = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $approval_product = $row;
    }
    return $approval_product;
}

function getWorkOrderDetails($id) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT * FROM work_order WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $work_order = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $work_order = $row;
    }
    return $work_order;
}

function getSubmitWorkOrderDetails($id) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT * FROM work_order WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $work_order = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $work_order = $row;
    }
    return $work_order;
}

function getShippingCompanyDetails($shipping_company_id ) {
    global $conn;
    $shipping_company_id = mysqli_real_escape_string($conn, $shipping_company_id);
    $query = "SELECT * FROM shipping_company WHERE shipping_company_id = '$shipping_company_id'";
    $result = mysqli_query($conn, $query);
    $shipping_company = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $shipping_company = $row;
    }
    return $shipping_company;
}

function getStaffAssignedWarehouse($corresponding_user) {
    global $conn;
    $corresponding_user = mysqli_real_escape_string($conn, $corresponding_user);
    $query = "SELECT * FROM warehouses WHERE corresponding_user = '$corresponding_user'";
    $result = mysqli_query($conn, $query);
    $warehouse = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $warehouse = $row;
    }
    return $warehouse;
}

function getWarehouseName($WarehouseID){
    global $conn;
    $query = "SELECT WarehouseName FROM warehouses WHERE WarehouseID = '$WarehouseID'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $WarehouseName = $row['WarehouseName'] ?? '';
    return  $WarehouseName;
}

function getColorGroupName($id){
    global $conn;
    $query = "SELECT color_group_name FROM color_group_name WHERE color_group_name_id = '$id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $color_group_name = $row['color_group_name'] ?? '';
    return  $color_group_name;
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

function getCustomMultiplier($product_category_id){
    global $conn;
    $query = "SELECT custom_multiplier FROM product_category WHERE product_category_id = '$product_category_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $custom_multiplier = $row['product_category'] ?? 100;
    return floatval($custom_multiplier/100);
}

function getCustomerPricingName($id){
    global $conn;
    $query = "SELECT pricing_name FROM customer_pricing WHERE id = '$id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $pricing_name = $row['pricing_name'] ?? '';
    return  $pricing_name;
}

function getProductTypeName($product_type_id){
    global $conn;
    $product_type_id = intval($product_type_id);
    $query = "SELECT product_type FROM product_type WHERE product_type_id = '$product_type_id'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['product_type'];
    }

    return '';
}

function getProductSystemName($id){
    global $conn;
    $query = "SELECT product_system FROM product_system WHERE product_system_id = '$id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $product_system = $row['product_system'] ?? '';
    return  $product_system;
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

function getColorMultiplierName($id){
    global $conn;
    $query = "SELECT color FROM color_multiplier WHERE id  = '$id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $color = $row['color'] ?? '';
    return  $color;
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

function getCategoryDetails($product_category_id) {
    global $conn;
    $product_category_id = mysqli_real_escape_string($conn, $product_category_id);
    $query = "SELECT * FROM product_category WHERE product_category_id = '$product_category_id'";
    $result = mysqli_query($conn, $query);
    $product_category = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $product_category = $row;
    }
    return $product_category;
}

function getGradeDetails($product_grade_id) {
    global $conn;
    $product_grade_id = mysqli_real_escape_string($conn, $product_grade_id);
    $query = "SELECT * FROM product_grade WHERE product_grade_id = '$product_grade_id'";
    $result = mysqli_query($conn, $query);
    $product_grade = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $product_grade = $row;
    }
    return $product_grade;
}

function getColorDetails($color_id) {
    global $conn;
    $color_id = mysqli_real_escape_string($conn, $color_id);
    $query = "SELECT * FROM paint_colors WHERE color_id = '$color_id'";
    $result = mysqli_query($conn, $query);
    $color = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $color = $row;
    }
    return $color;
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
    $query = "SELECT pack_count FROM supplier_pack WHERE id = '$id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $pack_count = $row['pack_count'] ?? 0;
    return  $pack_count;
}

function getPackName($id){
    global $conn;
    $query = "SELECT pack FROM supplier_pack WHERE id = '$id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $pack = $row['pack'] ?? '';
    return  $pack;
}

function getSupplierType($supplier_type_id){
    global $conn;
    $query = "SELECT supplier_type FROM supplier_type WHERE supplier_type_id = '$supplier_type_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $supplier_type = $row['supplier_type'] ?? '';
    return  $supplier_type;
}

function getSupplierName($supplier_id){
    global $conn;
    $query = "SELECT supplier_name FROM supplier WHERE supplier_id = '$supplier_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $supplier_name = $row['supplier_name'] ?? '';
    return  $supplier_name;
}

function getSupplierDetails($supplier_id) {
    global $conn;
    $supplier_id = mysqli_real_escape_string($conn, $supplier_id);
    $query = "SELECT * FROM supplier WHERE supplier_id = '$supplier_id'";
    $result = mysqli_query($conn, $query);
    $supplier = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $supplier = $row;
    }
    return $supplier;
}

function getSetting($settingName) {
    global $conn;
    $query = "SELECT value FROM settings WHERE setting_name = '$settingName'";
    $result = mysqli_query($conn, $query);
    $setting = 0;

    if ($row = mysqli_fetch_assoc($result)) {
        $setting = trim($row['value']);
    }

    return $setting;
}

function getPointsRatio() {
    global $conn;
    $query = "SELECT value FROM settings WHERE setting_name = 'points'";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        $data = json_decode(trim($row['value']), true);
        $order_total = isset($data['order_total']) ? $data['order_total'] : 0;
        $points_gained = isset($data['points_gained']) ? $data['points_gained'] : 0;
        return ($order_total > 0) ? ($points_gained / $order_total) : 0;
    }

    return 0;
}

function getPaymentSetting($payment_setting_name) {
    global $conn;
    $query = "SELECT value FROM payment_settings WHERE payment_setting_name = '$payment_setting_name'";
    $result = mysqli_query($conn, $query);
    $value = 0;
    if ($row = mysqli_fetch_assoc($result)) {
        $value = floatval($row['value']);
    }
    return $value;
}

function getSettingAddressDetails() {
    global $conn;
    $query = "SELECT * FROM settings WHERE setting_name = 'address'";
    $result = mysqli_query($conn, $query);
    $address = [];

    if ($row = mysqli_fetch_assoc($result)) {
        $address = json_decode($row['value'], true);
    }
    
    return $address;
}

function getSettingAmtPerMile() {
    global $conn;
    $query = "SELECT * FROM settings WHERE setting_name = 'amount_per_mile'";
    $result = mysqli_query($conn, $query);
    $amount_per_mile = 0;
    if ($row = mysqli_fetch_assoc($result)) {
        $amount_per_mile = $row['value'];
    }
    return $amount_per_mile;
}

function get_customer_name($customer_id){
    global $conn;

    $query = "SELECT customer_first_name, customer_last_name, customer_business_name FROM customer WHERE customer_id = '$customer_id'";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_array($result)) {
        $name = trim($row['customer_first_name'] . ' ' . $row['customer_last_name']);
        $business = trim($row['customer_business_name']);

        if (!empty($name) && !empty($business)) {
            return $name . ' (' . $business . ')';
        } elseif (!empty($name)) {
            return $name;
        } elseif (!empty($business)) {
            return $business;
        }
    }

    return '';
}

function getCustomerAddress($customer_id) {
    global $conn;
    $query = "SELECT address, city, state, zip FROM customer WHERE customer_id = '$customer_id'";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_array($result)) {
        $addressParts = [];

        if (!empty($row['address'])) {
            $addressParts[] = $row['address'];
        }
        if (!empty($row['city'])) {
            $addressParts[] = $row['city'];
        }
        if (!empty($row['state'])) {
            $addressParts[] = $row['state'];
        }
        if (!empty($row['zip'])) {
            $addressParts[] = $row['zip'];
        }

        $address = implode(', ', $addressParts);
        return $address;
    }

    return null;
}

function log_approval_changes($approval_id, $product_id, $action, $approval_product_id = 0){
    global $conn;
    session_start();
    $approval_id = mysqli_real_escape_string($conn, $approval_id);
    $approval_product_id = mysqli_real_escape_string($conn, $approval_product_id);
    $product_id = mysqli_real_escape_string($conn, $product_id);
    $action = mysqli_real_escape_string($conn, $action);
    $user_id = $_SESSION['userid'];
    $query = "INSERT INTO approval_changes (approval_id, approval_product_id, user, product_id, action) VALUES ('$approval_id', '$approval_product_id', '$user_id', '$product_id', '$action')";
    $result = mysqli_query($conn, $query);
    if ($result) {
        return true;
    } else {
        return false;
    }
}

function log_estimate_changes($estimate_id, $product_id, $action, $estimate_prod_id = 0){
    global $conn;
    session_start();
    $estimate_id = mysqli_real_escape_string($conn, $estimate_id);
    $estimate_prod_id = mysqli_real_escape_string($conn, $estimate_prod_id);
    $product_id = mysqli_real_escape_string($conn, $product_id);
    $action = mysqli_real_escape_string($conn, $action);
    $user_id = $_SESSION['userid'];
    $query = "INSERT INTO estimate_changes (estimate_id, estimate_prod_id, user, product_id, action) VALUES ('$estimate_id', '$estimate_prod_id', '$user_id', '$product_id', '$action')";
    $result = mysqli_query($conn, $query);
    if ($result) {
        return true;
    } else {
        return false;
    }
}

function log_order_changes($orderid, $product_id, $action, $order_product_id = 0){
    global $conn;
    session_start();
    $orderid = mysqli_real_escape_string($conn, $orderid);
    $order_product_id = mysqli_real_escape_string($conn, $order_product_id);
    $product_id = mysqli_real_escape_string($conn, $product_id);
    $action = mysqli_real_escape_string($conn, $action);
    $user_id = $_SESSION['userid'];
    $query = "INSERT INTO order_changes (orderid, order_product_id, user, product_id, action) VALUES ('$orderid', '$order_product_id', '$user_id', '$product_id', '$action')";
    $result = mysqli_query($conn, $query);
    if ($result) {
        return true;
    } else {
        return false;
    }
}

function getEstimateDetails($estimateid) {
    global $conn;
    $estimateid = mysqli_real_escape_string($conn, $estimateid);
    $query = "SELECT * FROM estimates WHERE estimateid = '$estimateid'";
    $result = mysqli_query($conn, $query);
    $estimates = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $estimates = $row;
    }
    return $estimates;
}

function getEstimateProdDetails($id) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT * FROM estimate_prod WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $estimate_prod = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $estimate_prod = $row;
    }
    return $estimate_prod;
}

function getOrderDetails($orderid) {
    global $conn;
    $orderid = mysqli_real_escape_string($conn, $orderid);
    $query = "SELECT * FROM orders WHERE orderid = '$orderid'";
    $result = mysqli_query($conn, $query);
    $orders = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $orders = $row;
    }
    return $orders;
}

function getOrderChangeCount($orderid) {
    global $conn;

    $orderid = mysqli_real_escape_string($conn, $orderid);

    $query = "SELECT COUNT(*) AS total_changes FROM order_history WHERE orderid = '$orderid'";
    $result = mysqli_query($conn, $query);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        return intval($row['total_changes']);
    }

    return 0;
}

function getOrderProdDetails($id) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT * FROM order_product WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $order_product = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $order_product = $row;
    }
    return $order_product;
}

function getProductTypeDetails($product_type_id) {
    global $conn;
    $product_type_id = mysqli_real_escape_string($conn, $product_type_id);
    $query = "SELECT * FROM product_type WHERE product_type_id = '$product_type_id'";
    $result = mysqli_query($conn, $query);
    $product_type = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $product_type = $row;
    }
    return $product_type;
}

function getSupplierTempOrderDetails($supplier_temp_order_id) {
    global $conn;
    $supplier_temp_order_id = mysqli_real_escape_string($conn, $supplier_temp_order_id);
    $query = "SELECT * FROM supplier_temp_orders WHERE supplier_temp_order_id = '$supplier_temp_order_id'";
    $result = mysqli_query($conn, $query);
    $product_type = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $supplier_temp_order = $row;
    }
    return $supplier_temp_order;
}

function getSupplierOrderedTotals($supplier_order_id) {
    global $conn;
    $total_price = 0;

    $query = "
        SELECT 
            SUM(price * quantity) AS total_price
        FROM 
            supplier_orders_prod
        WHERE 
            supplier_order_id = '$supplier_order_id'";

    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $total_price = floatval($row['total_price']);
    }
    return number_format($total_price, 2);
}

function getSupplierOrderTotals($supplier_temp_order_id) {
    global $conn;
    $total_price = 0;

    $query = "
        SELECT 
            SUM(price * quantity) AS total_price
        FROM 
            supplier_temp_prod_orders
        WHERE 
            supplier_temp_order_id = '$supplier_temp_order_id'";

    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $total_price = floatval($row['total_price']);
    }
    return number_format($total_price, 2);
}

function getOrderTotals($orderid) {
    global $conn;

    $query = "
        SELECT 
            SUM(
                quantity * actual_price * 
                CASE 
                    WHEN custom_length > 0 OR custom_length2 > 0 
                    THEN (custom_length + (custom_length2 / 12))
                    ELSE 1
                END
            ) AS total_actual_price
        FROM 
            order_product
        WHERE 
            orderid = '$orderid'";

    $result = mysqli_query($conn, $query);
    $total_actual_price = 0;

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $total_actual_price = floatval($row['total_actual_price']);
    }

    return number_format($total_actual_price, 2);
}

function getReturnTotals($orderid) {
    global $conn;
    $query = "
        SELECT 
            SUM(discounted_price * quantity) AS total_price,
            SUM((discounted_price * quantity) * stock_fee) AS total_fee
        FROM 
            product_returns
        WHERE 
            orderid = '$orderid'";

    $result = mysqli_query($conn, $query);
    $net_total = 0;

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $total_price = floatval($row['total_price']);
        $total_fee = floatval($row['total_fee']);
        $net_total = $total_price - $total_fee;
    }

    return number_format($net_total, 2);
}

function getOrderTotalsDiscounted($orderid) {
    global $conn;

    $query = "
        SELECT 
            SUM(
                quantity * discounted_price * 
                CASE 
                    WHEN custom_length > 0 OR custom_length2 > 0 
                    THEN (custom_length + (custom_length2 / 12))
                    ELSE 1
                END
            ) AS total_discounted_price
        FROM 
            order_product
        WHERE 
            orderid = '$orderid'";

    $result = mysqli_query($conn, $query);
    $total_discounted_price = 0;

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $total_discounted_price = floatval($row['total_discounted_price']);
    }

    return number_format($total_discounted_price, 2);
}


function setOrderTotals($orderid) {
    global $conn;

    $query = "
        SELECT 
            SUM(discounted_price * quantity) AS total_discounted_price,
            SUM(actual_price * quantity) AS total_actual_price
        FROM 
            order_product
        WHERE 
            orderid = '$orderid'";

    $result = mysqli_query($conn, $query);
    $total_discounted_price = 0;
    $total_actual_price = 0;

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $total_discounted_price = floatval($row['total_discounted_price']);
        $total_actual_price = floatval($row['total_actual_price']);
    }

    $query_update = "
        UPDATE orders
        SET 
            total_price = '$total_actual_price',
            discounted_price = '$total_discounted_price'
        WHERE 
            orderid = '$orderid'";

    if (mysqli_query($conn, $query_update)) {
        return "success";
    } else {
        return "Error updating order totals: " . mysqli_error($conn);
    }
}

function getEstimateTotals($estimateid) {
    global $conn;

    $estimateid = intval($estimateid);

    $query = "
        SELECT 
            SUM(
                actual_price * quantity *
                CASE 
                    WHEN custom_length > 0 OR custom_length2 > 0 
                    THEN (custom_length + (custom_length2 / 12))
                    ELSE 1
                END
            ) AS total_actual_price
        FROM 
            estimate_prod
        WHERE 
            estimateid = '$estimateid'
    ";

    $result = mysqli_query($conn, $query);
    $total_actual_price = 0;

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $total_actual_price = floatval($row['total_actual_price']);
    }

    return number_format($total_actual_price, 2);
}


function getEstimateTotalsDiscounted($estimateid) {
    global $conn;

    $estimateid = intval($estimateid);

    $query = "
        SELECT 
            SUM(
                discounted_price * quantity *
                CASE 
                    WHEN custom_length > 0 OR custom_length2 > 0 
                    THEN (custom_length + (custom_length2 / 12))
                    ELSE 1
                END
            ) AS total_discounted_price
        FROM 
            estimate_prod
        WHERE 
            estimateid = '$estimateid'
    ";

    $result = mysqli_query($conn, $query);
    $total_discounted_price = 0;

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $total_discounted_price = floatval($row['total_discounted_price']);
    }

    return number_format($total_discounted_price, 2);
}


function getCustomerType($customer_type_id){
    global $conn;
    $query = "SELECT customer_type_name FROM customer_types WHERE customer_type_id = '$customer_type_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $customer_type_name = $row['customer_type_name'] ?? '';
    return  $customer_type_name;
}

function getCustomerTaxName($taxid){
    global $conn;
    $query = "SELECT tax_status_desc FROM customer_tax WHERE taxid = '$taxid'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $tax_status_desc = $row['tax_status_desc'] ?? '';
    return  $tax_status_desc;
}

function getCustomerDetails($customer_id) {
    global $conn;
    $customer_id = mysqli_real_escape_string($conn, $customer_id);
    $query = "SELECT * FROM customer WHERE customer_id = '$customer_id'";
    $result = mysqli_query($conn, $query);
    $customer = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $customer = $row;
    }
    return $customer;
}

function getCustomerTax($customer_id) {
    global $conn;
    $customer_id = mysqli_real_escape_string($conn, $customer_id);

    $query = "SELECT percentage
              FROM customer AS c
              LEFT JOIN customer_tax AS ct
              ON c.tax_status = ct.taxid
              WHERE c.customer_id = '$customer_id'";

    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['percentage'] ?? 0;
    } else {
        return 0;
    }
}

function getCustomerTaxById($taxid) {
    global $conn;
    $taxid = mysqli_real_escape_string($conn, $taxid);

    $query = "SELECT percentage 
              FROM customer_tax 
              WHERE taxid = '$taxid' 
              LIMIT 1";

    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['percentage'] ?? 0;
    } else {
        return 0;
    }
}

function getCustomerTotalAvail($customer_id) {
    global $conn;

    $query = "
        SELECT c.credit_amount AS amount
        FROM customer_store_credit_history c
        WHERE c.customer_id = '$customer_id'

        UNION ALL

        SELECT jd.deposit_remaining AS amount
        FROM job_deposits jd
        JOIN jobs j ON jd.job_id = j.job_id
        WHERE j.customer_id = '$customer_id'
    ";

    $result = mysqli_query($conn, $query);

    $total = 0;

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $amount = (float)$row['amount'];
            if ($amount > 0) {
                $total += $amount;
            }
        }
    }

    return $total;
}

function getFirstCreditDate($customer_id) {
    global $conn;

    $query = "
        SELECT 
            l.created_at AS first_credit_date
        FROM job_ledger l
        LEFT JOIN jobs j ON l.job_id = j.job_id
        WHERE l.customer_id = '$customer_id' AND l.entry_type = 'credit'
        ORDER BY l.created_at ASC
        LIMIT 1;
    ";

    $result = $conn->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        return $row['first_credit_date'];
    }

    return null;
}

function getCustomerCreditTotal($customer_id) {
    global $conn;
    $customer_id = mysqli_real_escape_string($conn, $customer_id);
    $total_credit = 0;

    $ledger_query = "
        SELECT ledger_id, amount
        FROM job_ledger
        WHERE entry_type = 'credit' AND customer_id = '$customer_id'
    ";

    $ledger_result = mysqli_query($conn, $ledger_query);

    $ledger_ids = [];
    if ($ledger_result && mysqli_num_rows($ledger_result) > 0) {
        while ($row = mysqli_fetch_assoc($ledger_result)) {
            $total_credit += floatval($row['amount']);
            $ledger_ids[] = intval($row['ledger_id']);
        }
    }

    if (!empty($ledger_ids)) {
        $ledger_id_list = implode(',', $ledger_ids);

        $payment_query = "
            SELECT SUM(amount) AS total_payment 
            FROM job_payment 
            WHERE ledger_id IN ($ledger_id_list)
        ";

        $payment_result = mysqli_query($conn, $payment_query);
        if ($payment_result && $payment_row = mysqli_fetch_assoc($payment_result)) {
            $total_credit -= floatval($payment_row['total_payment']);
        }
    }

    return $total_credit;
}

function getCustomerDiscount($customer_id) {
    global $conn;
    $customer_id = mysqli_real_escape_string($conn, $customer_id);
    $customer_details = getCustomerDetails($customer_id);
    $isLoyalty = intval($customer_details['loyalty']);

    $discount_loyalty = 0;
    if(!empty($isLoyalty)){
        $customer_ttl_orders = getCustomerOrderTotal($customer_id);
        $query = "
            SELECT discount 
            FROM loyalty_program 
            WHERE accumulated_total_orders <= '$customer_ttl_orders' 
            ORDER BY discount DESC 
            LIMIT 1";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $discount_loyalty = floatval($row['discount']) ?? 0;
        }
    }

    $discount_customer = 0;
    /* $query = "
        SELECT ct.customer_price_cat
        FROM customer AS c
        LEFT JOIN customer_types AS ct
        ON c.customer_type_id = ct.customer_type_id
        WHERE c.customer_id = '$customer_id'";
    
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $discount_customer = floatval($row['customer_price_cat']) ?? 0;
    } */

    return max($discount_loyalty, $discount_customer);
}

function getPricingCategory($product_category_id, $customer_pricing_id) {
    global $conn;
    $percentage = 0;
    $query = "
        SELECT percentage 
        FROM pricing_category 
        WHERE 
            product_category_id = '$product_category_id' AND
            customer_pricing_id = '$customer_pricing_id'
        ";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $percentage = floatval($row['percentage']) ?? 0;
    }

    return $percentage;
}

function getCustomerDiscountLoyalty($customer_id) {
    global $conn;
    $customer_id = mysqli_real_escape_string($conn, $customer_id);
    $customer_details = getCustomerDetails($customer_id);
    $isLoyalty = intval($customer_details['loyalty']);

    $discount_loyalty = 0;
    if(!empty($isLoyalty)){
        $customer_ttl_orders = getCustomerOrderTotal($customer_id);
        $query = "
            SELECT discount 
            FROM loyalty_program 
            WHERE accumulated_total_orders <= '$customer_ttl_orders' 
            ORDER BY discount DESC 
            LIMIT 1";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $discount_loyalty = floatval($row['discount']) ?? 0;
        }
    }

    return $discount_loyalty;
}

function getCustomerDiscountProfile($customer_id) {
    global $conn;
    $customer_id = mysqli_real_escape_string($conn, $customer_id);

    $query = "
        SELECT ct.customer_price_cat
        FROM customer AS c
        LEFT JOIN customer_types AS ct
        ON c.customer_type_id = ct.customer_type_id
        WHERE c.customer_id = '$customer_id'";
    
    $result = mysqli_query($conn, $query);
    $discount_customer = 0;
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $discount_customer = floatval($row['customer_price_cat']) ?? 0;
    }

    return $discount_customer;
}

function getCustomerOrderTotal($customerid){
    global $conn;
    $query = "SELECT SUM(discounted_price) AS total_orders FROM orders WHERE customerid = '$customerid'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $total_orders = $row['total_orders'];
    return  $total_orders;
}

function getUsageName($usageid){
    global $conn;
    $query = "SELECT usage_name FROM component_usage WHERE usageid = '$usageid'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $usage_name = $row['usage_name'];
    return  $usage_name;
}

function getDeliveryCost(){
    global $conn;
    $query = "SELECT value FROM settings WHERE setting_name = 'delivery'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $delivery = $row['value'];
    return  $delivery;
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

function getPrimaryKey($table) {
    global $conn;

    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND COLUMN_KEY = 'PRI' 
            LIMIT 1";
    $result = $conn->query($sql);

    if ($row = $result->fetch_assoc()) {
        return $row['COLUMN_NAME'];
    }

    return null;
}

function getCartDataByCustomerId($customer_id) {
    global $conn;

    $customer_id = (int)$customer_id;
    
    $query = "SELECT * FROM customer_cart WHERE customer_id = $customer_id";
    $result = mysqli_query($conn, $query);

    $cartData = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $cartData[] = $row;
        }
    }

    return $cartData;
}

function deleteCustomerCart() {
    global $conn;

    $customer_id = intval($_SESSION['customer_id']);
    if ($customer_id <= 0) {
        return;
    }

    $query = "DELETE FROM customer_cart WHERE customer_id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
    }
}

function getTrussTypeName($id) {
    global $conn;
    $id = intval($id);
    $query = "SELECT truss_type FROM truss_type WHERE truss_type_id = '$id'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['truss_type'];
    }
    return '';
}

function getTrussMaterialName($id) {
    global $conn;
    $id = intval($id);
    $query = "SELECT truss_material FROM truss_material WHERE truss_material_id = '$id'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['truss_material'];
    }
    return '';
}

function getTrussCeilingLoadName($id) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT truss_ceiling_load FROM truss_ceiling_load WHERE truss_ceiling_load = '$id'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['truss_ceiling_load'];
    }
    return '';
}

function getTrussSpacingName($id) {
    global $conn;
    $id = intval($id);
    $query = "SELECT truss_spacing, unit_of_measure FROM truss_spacing WHERE truss_spacing_id = '$id'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        return trim($row['truss_spacing'] . ' ' . $row['unit_of_measure']);
    }
    return '';
}

function getTrussOverhangName($id) {
    global $conn;
    $id = intval($id);
    $query = "SELECT truss_overhang, unit_of_measure FROM truss_overhang WHERE truss_overhang_id = '$id'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        return trim($row['truss_overhang'] . ' ' . $row['unit_of_measure']);
    }
    return '';
}

function getTrussPitchName($id) {
    global $conn;
    $id = intval($id);
    $query = "SELECT numerator, denominator FROM truss_pitch WHERE truss_pitch_id = '$id'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $numerator = $row['numerator'];
        $denominator = $row['denominator'];
        if (!empty($numerator) && !empty($denominator)) {
            return "{$numerator}/{$denominator}";
        }
    }
    return '';
}

function time_ago($timestamp) {

    $time_ago = strtotime($timestamp);
    $current_time = time();

    if ($time_ago === false) {
        return "Invalid timestamp: $timestamp";
    }

    $time_difference = $current_time - $time_ago;

    if ($time_difference < 0) {
        return "Just Now";
    }

    $seconds = $time_difference;
    
    $minutes = round($seconds / 60);
    $hours   = round($seconds / 3600);
    $days    = round($seconds / 86400);
    $weeks   = round($seconds / 604800);
    $months  = round($seconds / 2629440);
    $years   = round($seconds / 31553280);

    if ($seconds <= 60) {
        return "Just Now";
    } else if ($minutes <= 60) {
        return ($minutes == 1) ? "one minute ago" : "$minutes minutes ago";
    } else if ($hours <= 24) {
        return ($hours == 1) ? "an hour ago" : "$hours hours ago";
    } else if ($days <= 7) {
        return ($days == 1) ? "yesterday" : "$days days ago";
    } else if ($weeks <= 4.3) {
        return ($weeks == 1) ? "one week ago" : "$weeks weeks ago";
    } else if ($months <= 12) {
        return ($months == 1) ? "one month ago" : "$months months ago";
    } else {
        return ($years == 1) ? "one year ago" : "$years years ago";
    }
}

function getOrderDateByProductId($productId) {
    global $conn;
    $productId = intval($productId);

    $query = "SELECT o.order_date
              FROM order_product op
              INNER JOIN orders o ON op.orderid = o.orderid
              WHERE op.id = $productId
              LIMIT 1";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['order_date'];
    } else {
        return null;
    }
}

function getJobDetails($job_id) {
    global $conn;
    $job_id = mysqli_real_escape_string($conn, $job_id);
    $query = "SELECT * FROM jobs WHERE job_id = '$job_id'";
    $result = mysqli_query($conn, $query);
    $jobs = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $jobs = $row;
    }
    return $jobs;
}

function getJobDepositTotal($job_id) { 
    global $conn;

    $job_id = intval($job_id);
    $total = 0;

    $sql = "SELECT SUM(deposit_remaining) AS total_deposit 
            FROM job_deposits 
            WHERE job_id = $job_id AND deposit_status = 1";

    $result = mysqli_query($conn, $sql);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        $total = floatval($row['total_deposit']);
    }

    return $total;
}

function getJobUsageTotal($job_id) { 
    global $conn;

    $job_id = intval($job_id);
    $total = 0;

    $sql = "SELECT SUM(amount) AS total_usage 
            FROM job_ledger 
            WHERE job_id = $job_id AND entry_type = 'usage'";

    $result = mysqli_query($conn, $sql);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        $total = floatval($row['total_usage']);
    }

    return $total;
}

function getJobBalance($job_id) {
    return getJobDepositTotal($job_id);
}

function getOrderProductPricing($order_product_id) {
    global $conn;

    $order_product_id = mysqli_real_escape_string($conn, $order_product_id);

    $query = "SELECT id, quantity, actual_price, discounted_price, custom_length, custom_length2 
              FROM order_product 
              WHERE id = '$order_product_id' 
              LIMIT 1";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        $quantity = is_numeric($row['quantity']) ? floatval($row['quantity']) : 0;
        $actual_price = floatval($row['actual_price']);
        $discounted_price = floatval($row['discounted_price']);
        $length_ft = is_numeric($row['custom_length']) ? floatval($row['custom_length']) : 0;
        $length_in = is_numeric($row['custom_length2']) ? floatval($row['custom_length2']) : 0;

        $total_length = $length_ft + ($length_in / 12);

        if ($total_length > 0) {
            $total_actual_price = $actual_price * $quantity * $total_length;
            $total_discounted_price = $discounted_price * $quantity * $total_length;
        } else {
            $total_actual_price = $actual_price * $quantity;
            $total_discounted_price = $discounted_price * $quantity;
        }

        return [
            'quantity' => $quantity,
            'actual_price' => $actual_price,
            'discounted_price' => $discounted_price,
            'custom_length_ft' => $length_ft,
            'custom_length_in' => $length_in,
            'total_length' => $total_length,
            'total_actual_price' => $total_actual_price,
            'total_discounted_price' => $total_discounted_price
        ];
    }

    return false;
}

function getTotalJobPayments($ledger_id) {
    global $conn;

    $ledger_id = intval($ledger_id);
    $total = 0;

    $query = "SELECT SUM(amount) AS total_payment 
              FROM job_payment
              WHERE ledger_id = $ledger_id";

    $result = mysqli_query($conn, $query);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        $total = floatval($row['total_payment']);
    }

    return $total;
}

function log_order_product_changes($conn, $old_data, $new_data, $updated_by = 'System') {
    $changes = [];

    foreach ($new_data as $key => $new_value) {
        $old_value = $old_data[$key] ?? null;

        if ((string)$old_value !== (string)$new_value) {
            $changes[$key] = [
                'old' => $old_value,
                'new' => $new_value
            ];
        }
    }

    if (!empty($changes)) {
        $orderid = intval($old_data['orderid']);
        $order_product_id = intval($old_data['id']);

        $old_json = json_encode(array_map(fn($v) => $v['old'], $changes));
        $new_json = json_encode(array_map(fn($v) => $v['new'], $changes));

        $old_json_escaped = mysqli_real_escape_string($conn, $old_json);
        $new_json_escaped = mysqli_real_escape_string($conn, $new_json);
        $updated_by_escaped = mysqli_real_escape_string($conn, $updated_by);

        $log_sql = "
            INSERT INTO order_history 
                (orderid, order_product_id, action_type, old_value, new_value, updated_by) 
            VALUES 
                ('$orderid', '$order_product_id', 'update_product', 
                 '$old_json_escaped', '$new_json_escaped', '$updated_by_escaped')
        ";

        mysqli_query($conn, $log_sql);
    }
}

function getOrderTotalPayments($orderid) {
    global $conn;

    $orderid = mysqli_real_escape_string($conn, $orderid);
    $total = 0;

    $sql = "
        SELECT ledger_id
        FROM job_ledger
        WHERE reference_no = '$orderid'
          AND entry_type IN ('credit', 'usage')
    ";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $ledger_id = intval($row['ledger_id']);
            $total += getTotalJobPayments($ledger_id);
        }
    }

    return $total;
}

?>