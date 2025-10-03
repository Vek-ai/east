<?php
include "calculate_price.php";
include "notifications.php";
require_once __DIR__ . '/../modules/EmailTemplates.php';

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
    $product_item = !empty($row['product_item']) ? $row['product_item'] : '';
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

function getCoilProductDetails($coil_id) {
    global $conn;
    $coil_id = mysqli_real_escape_string($conn, $coil_id);
    $query = "SELECT * FROM coil_product WHERE coil_id = '$coil_id'";
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

function getRollFormerDetails($roll_former_id) {
    global $conn;
    $roll_former_id = mysqli_real_escape_string($conn, $roll_former_id);
    $query = "SELECT * FROM roll_former WHERE roll_former_id = '$roll_former_id'";
    $result = mysqli_query($conn, $query);
    $roll_former = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $roll_former = $row;
    }
    return $roll_former;
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
    $query = "SELECT color_name FROM product_color WHERE id = '$id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $color_name = $row['color_name'] ?? '';
    return  $color_name;
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

    if (is_string($product_line_id) && $product_line_id !== '' && $product_line_id[0] === '[') {
        $product_line_id = json_decode($product_line_id, true);
    }

    if (!is_array($product_line_id)) {
        $product_line_id = [$product_line_id];
    }

    $product_line_id = array_map('intval', $product_line_id);
    $ids = implode(",", $product_line_id);

    if (empty($ids)) {
        return '';
    }

    $query = "SELECT product_line FROM product_line WHERE product_line_id IN ($ids)";
    $result = mysqli_query($conn, $query);

    $names = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $names[] = $row['product_line'];
    }

    return implode(", ", $names);
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

function getColorName($color_id){
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

function getProductSystemDetails($product_system_id) {
    global $conn;
    $product_system_id = mysqli_real_escape_string($conn, $product_system_id);
    $query = "SELECT * FROM product_system WHERE product_system_id = '$product_system_id'";
    $result = mysqli_query($conn, $query);
    $product_system = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $product_system = $row;
    }
    return $product_system;
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

function getProfileTypeDetails($profile_type_id) {
    global $conn;
    $profile_type_id = mysqli_real_escape_string($conn, $profile_type_id);
    $query = "SELECT * FROM profile_type WHERE profile_type_id = '$profile_type_id'";
    $result = mysqli_query($conn, $query);
    $profile_type = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $profile_type = $row;
    }
    return $profile_type;
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

function getContractorPointsRatio() {
    global $conn;
    $query = "SELECT value FROM settings WHERE setting_name = 'contractor_points'";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        $data = json_decode(trim($row['value']), true);
        $order_total     = $data['order_total'] ?? 0;
        $points_gained   = $data['points_gained'] ?? 0;
        return ($order_total > 0) ? ($points_gained / $order_total) : 0;
    }

    return 0;
}

function addPoints($customer_id, $order_id) {
    global $conn;

    $is_points_enabled = getSetting('is_points_enabled');
    if ($is_points_enabled != '1') {
        return false;
    }

    $res = mysqli_query($conn, "SELECT discounted_price FROM orders WHERE orderid = $order_id");
    $row = mysqli_fetch_assoc($res);
    $total_order_amount = floatval($row['discounted_price']);

    $ratio = getPointsRatio();
    $points_earned = floor($total_order_amount * $ratio);

    $query = "
        INSERT INTO customer_points (customer_id, order_id, total_order_amount, points_earned, date)
        VALUES ('$customer_id', '$order_id', '$total_order_amount', '$points_earned', NOW())
    ";
    $result = mysqli_query($conn, $query);

    if ($result) {
        return mysqli_insert_id($conn);
    }
    return false;
}

function getOrderPoints($order_id) {
    global $conn;

    $query = "
        SELECT points_earned 
        FROM customer_points 
        WHERE order_id = '$order_id'
        LIMIT 1
    ";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        return (int)$row['points_earned'];
    }
    return 0;
}

function getCustomerPoints($customer_id) {
    global $conn;

    $query = "
        SELECT IFNULL(SUM(points_earned), 0) AS total_points
        FROM customer_points
        WHERE customer_id = '$customer_id'
    ";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        return (int)$row['total_points'];
    }
    return 0;
}

function getContractorPointsFromOrder($orderid) {
    global $conn;

    $order_total = getOrderTotalsDiscounted($orderid);
    $ratio = getContractorPointsRatio();

    $points = $order_total * $ratio;

    return (int) floor($points);
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
                actual_price * 
                CASE 
                    WHEN custom_length > 0 OR custom_length2 > 0 
                    THEN (custom_length + (custom_length2 / 12))
                    ELSE 1
                END
            ) AS total_actual_price
        FROM order_product
        WHERE orderid = '$orderid'
    ";

    $result = mysqli_query($conn, $query);
    $total_actual_price = 0;

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $total_actual_price = floatval($row['total_actual_price']);
    }

    return round($total_actual_price, 2);
}

function getReturnTotals($orderid) {
    global $conn;
    $query = "
        SELECT 
            SUM(discounted_price) AS total_price,
            SUM((discounted_price) * stock_fee) AS total_fee
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
            SUM(discounted_price) AS total_discounted_price
        FROM order_product
        WHERE orderid = '$orderid'
    ";

    $result = mysqli_query($conn, $query);
    $total_discounted_price = 0;

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $total_discounted_price = floatval($row['total_discounted_price']);
    }

    return round($total_discounted_price, 2);
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
                actual_price
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
                discounted_price 
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
        WHERE j.customer_id = '$customer_id' AND jd.deposit_status = '1'
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
            AND status = '1'
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

function hasProductVariantLength($product_id) {
    global $conn;

    $product_id = (int)$product_id;

    $sql = "
        SELECT * 
        FROM product_variant_length pvl
        INNER JOIN inventory i ON pvl.inventory_id = i.Inventory_id
        WHERE i.Product_id = $product_id 
        LIMIT 1
    ";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        return true;
    }

    return false;
}

function getPrimaryKey($table) {
    static $cache = [];
    global $conn;

    if (isset($cache[$table])) {
        return $cache[$table];
    }

    $sql = "SELECT COLUMN_NAME 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = '$table' 
              AND COLUMN_KEY = 'PRI' 
            LIMIT 1";
    $result = $conn->query($sql);

    if ($row = $result->fetch_assoc()) {
        $cache[$table] = $row['COLUMN_NAME'];
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

function getTruckName($id) {
    global $conn;
    $id = intval($id);
    $query = "SELECT truck_name FROM trucks WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['truck_name'];
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
              WHERE ledger_id = $ledger_id
              AND status = '1'";

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

function checkTimer() {
    global $conn;

    $sql = "
        UPDATE work_order
        SET status = 3 -- Done
        WHERE 
            status = 2
            AND started_at IS NOT NULL
            AND completed_at IS NOT NULL
            AND NOW() >= completed_at
    ";

    mysqli_query($conn, $sql);
}

function getCustomerIDs() {
    global $conn;

    $query = "SELECT customer_id FROM customer WHERE status = 1";
    $result = mysqli_query($conn, $query);

    $ids = [];

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $ids[] = $row['customer_id'];
        }
    }

    return $ids;
}

function getAdminIDs() {
    global $conn;
    $ids = [];

    $query = "SELECT staff_id FROM staff WHERE status = 1 AND role = 6";
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $ids[] = $row['staff_id'];
    }

    return $ids;
}

function getCashierIDs() {
    global $conn;
    $ids = [];

    $query = "SELECT staff_id FROM staff WHERE status = 1 AND role = 5";
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $ids[] = $row['staff_id'];
    }

    return $ids;
}

function getWorkOrderIDs() {
    global $conn;
    $ids = [];

    $query = "SELECT staff_id FROM staff WHERE status = 1 AND role = 11";
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $ids[] = $row['staff_id'];
    }

    return $ids;
}

function logCoilDefectiveChange($coil_defective_id, $action_type, $change_text, $note = '') {
    global $conn;
    $coil_defective_id = intval($coil_defective_id);
    $user_id = intval($_SESSION['userid']);

    $q = mysqli_query($conn, "SELECT coil_id FROM coil_defective WHERE coil_defective_id = $coil_defective_id");
    if ($r = mysqli_fetch_assoc($q)) {
        $coil_id = intval($r['coil_id']);
        $change_text_escaped = mysqli_real_escape_string($conn, $change_text);
        $action_type_escaped = mysqli_real_escape_string($conn, $action_type);
        $note_escaped = mysqli_real_escape_string($conn, $note);

        $sql = "INSERT INTO coil_defective_history 
                    (coil_defective_id, coil_id, action_type, change_text, note, changed_by)
                VALUES 
                    ($coil_defective_id, $coil_id, '$action_type_escaped', '$change_text_escaped', '$note_escaped', $user_id)";
        mysqli_query($conn, $sql);
    }
}

function getAvailableCoils($color_id = '', $grade = '', $width = '') {
    global $conn;

    $assigned_ids = [];
    $res = mysqli_query($conn, "SELECT assigned_coils FROM work_order WHERE assigned_coils IS NOT NULL AND assigned_coils != '' AND status != 4");
    while ($row = mysqli_fetch_assoc($res)) {
        $decoded = json_decode($row['assigned_coils'], true);
        if (is_array($decoded)) {
            foreach ($decoded as $id) {
                $id = intval($id);
                if ($id > 0) $assigned_ids[$id] = true;
            }
        }
    }

    $where = "WHERE status = 0";

    if (!empty($color_id)) {
        if (is_array($color_id)) {
            $safe_ids = array_filter(array_map('intval', $color_id));
            if (!empty($safe_ids)) {
                $where .= " AND color_sold_as IN (" . implode(",", $safe_ids) . ")";
            }
        } else {
            $where .= " AND color_sold_as = '" . mysqli_real_escape_string($conn, $color_id) . "'";
        }
    }

    if (!empty($grade)) {
        if (is_array($grade)) {
            $safe_grades = array_map(function($g) use ($conn) {
                return "'" . mysqli_real_escape_string($conn, $g) . "'";
            }, array_filter($grade));
            if (!empty($safe_grades)) {
                $where .= " AND grade IN (" . implode(",", $safe_grades) . ")";
            }
        } else {
            $where .= " AND grade = '" . mysqli_real_escape_string($conn, $grade) . "'";
        }
    }

    if (!empty($width)) {
        $w = floatval($width);
        if ($w > 0) {
            $where .= " AND width IN (SELECT id FROM coil_width WHERE actual_width >= $w)";
        }
    }

    $sql = "SELECT * FROM coil_product $where ORDER BY date ASC";
    $res = mysqli_query($conn, $sql);

    $result = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $coil_id = intval($row['coil_id']);
        if (!isset($assigned_ids[$coil_id])) {
            $result[] = $row;
        }
    }

    return $result;
}

function getCoilWidth($id) {
    global $conn;

    $id = intval($id);

    $sql = "SELECT actual_width FROM coil_width WHERE id = $id LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['actual_width'];
    }

    return null;
}

function getJobID($job_name, $customer_id) {
    global $conn;

    $job_name = $conn->real_escape_string($job_name);
    $customer_id = intval($customer_id);

    $sql = "
        SELECT job_id 
        FROM jobs 
        WHERE job_name = '$job_name' 
          AND customer_id = $customer_id 
        LIMIT 1
    ";

    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        return intval($row['job_id']);
    }

    return null;
}

function getOrderProductBarcode($id) {
    global $conn;

    $id = intval($id);
    $sql = "SELECT orderid, id FROM order_product WHERE id = $id LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['orderid'] . $row['id'] . '0000';
    }

    return null;
}

function convertLengthToFeet($lengthStr) {
    $parts = explode(' ', trim($lengthStr));

    if (count($parts) !== 2) return 0;

    $value = floatval($parts[0]);
    $unit = strtolower(trim($parts[1]));

    switch ($unit) {
        case 'feet':
        case 'foot':
            return $value;

        case 'inches':
        case 'inch':
            return $value / 12;

        case 'meter':
        case 'meters':
            return $value * 3.28084;

        default:
            return 0;
    }
}

function getInventoryLengths($product_id) {
    global $conn;

    $lengths = [];

    $query = "
        SELECT iv.inventory_id, pvl.length 
        FROM inventory iv
        JOIN product_variant_length pvl ON iv.inventory_id = pvl.inventory_id
        WHERE iv.Product_id = '$product_id'
    ";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        return [];
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $lengths[] = [
            'inventory_id' => $row['inventory_id'],
            'length'       => $row['length'],
            'feet'         => convertLengthToFeet($row['length'])
        ];
    }

    usort($lengths, function ($a, $b) {
        return $a['feet'] <=> $b['feet'];
    });

    return $lengths;
}

function getProductAvailableLengths($product_id) {
    global $conn;

    $lengths = [];

    $query = "SELECT available_lengths FROM product WHERE product_id = '$product_id'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return [];
    }

    $row = mysqli_fetch_assoc($result);
    if (!$row || empty($row['available_lengths'])) {
        return [];
    }

    $available_ids = json_decode($row['available_lengths'], true);
    if (!is_array($available_ids) || empty($available_ids)) {
        return [];
    }

    $ids = implode(',', array_map('intval', $available_ids));
    $dim_query = "
        SELECT dimension_id, dimension, dimension_unit 
        FROM dimensions 
        WHERE dimension_id IN ($ids)
    ";
    $dim_result = mysqli_query($conn, $dim_query);

    if (!$dim_result) {
        return [];
    }

    while ($dim = mysqli_fetch_assoc($dim_result)) {
        $length_value = trim($dim['dimension'] . ' ' . $dim['dimension_unit']);

        $lengths[] = [
            'inventory_id' => null,
            'length'       => $length_value,
            'feet'         => convertLengthToFeet($length_value)
        ];
    }

    usort($lengths, function ($a, $b) {
        return $a['feet'] <=> $b['feet'];
    });

    return $lengths;
}

function getLumberLengths($product_id) {
    global $conn;

    $dimensions = [];

    $product_id = (int)$product_id;

    $query = "
        SELECT iv.inventory_id, d.dimension, d.dimension_unit
        FROM inventory iv
        JOIN dimensions d ON iv.dimension_id = d.dimension_id
        WHERE iv.Product_id = '$product_id'
          AND d.dimension_category = 1
    ";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        return [];
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $dimensionStr = trim($row['dimension'] . ' ' . $row['dimension_unit']);

        $dimensions[] = [
            'inventory_id' => $row['inventory_id'],
            'dimension'    => $row['dimension'],
            'unit'         => $row['dimension_unit'],
            'feet'         => convertLengthToFeet($dimensionStr)
        ];
    }

    usort($dimensions, function ($a, $b) {
        return $a['feet'] <=> $b['feet'];
    });

    return $dimensions;
}

function getAvailableInventory($product_id) {
    global $conn;

    $product_id = (int)$product_id;
    $inventory = [];

    $query = "
        SELECT 
            iv.Inventory_id,
            iv.color_id,
            iv.quantity,
            iv.quantity_ttl,
            iv.pack,
            iv.lumber_type,
            iv.cost,
            iv.price,
            iv.dimension_id,
            d.dimension,
            d.dimension_unit,
            pvl.length AS variant_length
        FROM inventory iv
        LEFT JOIN dimensions d ON iv.dimension_id = d.dimension_id
        LEFT JOIN product_variant_length pvl ON iv.Inventory_id = pvl.inventory_id
        WHERE iv.Product_id = '$product_id'
          AND iv.quantity > 0
        ORDER BY iv.Inventory_id ASC
    ";

    $result = mysqli_query($conn, $query);
    if (!$result) {
        return [];
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $lengthValue = '';
        $lengthUnit = '';
        $lengthFeet = 0;

        if (!empty($row['variant_length'])) {
            $parts = explode(' ', trim($row['variant_length']));
            if (count($parts) === 2) {
                $lengthValue = floatval($parts[0]);
                $lengthUnit = strtolower(trim($parts[1]));
                $lengthFeet = convertLengthToFeet($row['variant_length']);
            }
        }

        $inventory[] = [
            'inventory_id'   => $row['Inventory_id'],
            'color_id'       => $row['color_id'],
            'quantity'       => (int)$row['quantity'],
            'quantity_ttl'   => (int)$row['quantity_ttl'],
            'pack'           => (int)$row['pack'],
            'lumber_type'    => $row['lumber_type'],
            'cost'           => (float)$row['cost'],
            'price'          => (float)$row['price'],
            'dimension_id'   => (int)$row['dimension_id'],
            'dimension'      => $row['dimension'],
            'dimension_unit' => $row['dimension_unit'],
            'length'         => $row['variant_length'] ?? '',
            'length_value'   => $lengthValue,
            'length_unit'    => $lengthUnit,
            'length_feet'    => $lengthFeet
        ];
    }

    return $inventory;
}

function loadSupplierOrders($supplier_id) {
    global $conn;

    $_SESSION["order_cart"] = [];

    $query = "
        SELECT 
            sop.id AS supplier_order_prod_id,
            sop.supplier_order_id,
            sop.product_id,
            sop.quantity,
            sop.price,
            sop.color,
            p.supplier_id,
            p.product_item,
            p.unit_price,
            p.length,
            p.sold_by_feet
        FROM supplier_orders so
        JOIN supplier_orders_prod sop ON so.supplier_order_id = sop.supplier_order_id
        JOIN product p ON sop.product_id = p.product_id
        WHERE so.supplier_id = '$supplier_id' AND so.status = 1
    ";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $price = floatval($row['price']);

            if ($row['sold_by_feet'] == 1 && floatval($row['length']) > 0) {
                $price = $price / floatval($row['length']);
            }

            $_SESSION["order_cart"][] = [
                'product_id'              => $row['product_id'],
                'product_item'            => getProductName($row['product_id']),
                'supplier_id'             => $row['supplier_id'],
                'unit_price'              => $price,
                'quantity_cart'           => intval($row['quantity']),
                'custom_color'            => $row['color'],
                'supplier_order_id'       => $row['supplier_order_id'],
                'supplier_order_prod_id'  => $row['supplier_order_prod_id']
            ];
        }
    }
}

function updateSupplierOrders($supplier_id) {
    global $conn;

    if (!isset($_SESSION['order_cart']) || !is_array($_SESSION['order_cart']) || empty($_SESSION['order_cart'])) {
        return false;
    }

    $query = "SELECT supplier_order_id FROM supplier_orders WHERE supplier_id = '$supplier_id' AND status = 1 LIMIT 1";
    $result = mysqli_query($conn, $query);

    $supplier_order_id = null;
    $is_new_order = false;

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $supplier_order_id = intval($row['supplier_order_id']);
    } else {
        $is_new_order = true;
    }

    $insert_items = [];

    $new_orders_price = 0;
    foreach ($_SESSION['order_cart'] as &$item) {
        $product_id = intval($item['product_id']);
        $quantity = intval($item['quantity_cart']);
        $price = floatval($item['unit_price']);
        $color = mysqli_real_escape_string($conn, $item['custom_color']);

        if (!empty($item['supplier_order_prod_id']) && !$is_new_order) {
            $prod_id = intval($item['supplier_order_prod_id']);
            $update = "
                UPDATE supplier_orders_prod 
                SET quantity = '$quantity', price = '$price', color = '$color'
                WHERE id = '$prod_id' AND supplier_order_id = '$supplier_order_id'
            ";
            mysqli_query($conn, $update);
        } else {
            $insert_items[] = [
                'product_id' => $product_id,
                'quantity' => $quantity,
                'price' => $price,
                'color' => $color
            ];

            $new_orders_price += $price;
        }
    }

    if ($is_new_order && !empty($insert_items)) {
        $cashier_id = $_SESSION['staff_id'] ?? 0;
        $order_date = date("Y-m-d H:i:s");

        $insert_order = "
            INSERT INTO supplier_orders (supplier_id, cashier, total_price, order_date, status)
            VALUES ('$supplier_id', '$cashier_id', '$new_orders_price', '$order_date', 1)
        ";

        if (mysqli_query($conn, $insert_order)) {
            $supplier_order_id = mysqli_insert_id($conn);
        } else {
            return false;
        }
    }

    if (!$is_new_order) {
        $existing_ids = [];
        $query_existing = "SELECT id FROM supplier_orders_prod WHERE supplier_order_id = '$supplier_order_id'";
        $result_existing = mysqli_query($conn, $query_existing);

        while ($row = mysqli_fetch_assoc($result_existing)) {
            $existing_ids[] = $row['id'];
        }

        $session_ids = [];
        foreach ($_SESSION['order_cart'] as $item) {
            if (!empty($item['supplier_order_prod_id'])) {
                $session_ids[] = intval($item['supplier_order_prod_id']);
            }
        }

        $to_delete = array_diff($existing_ids, $session_ids);
        if (!empty($to_delete)) {
            $ids_to_delete = implode(",", array_map('intval', $to_delete));
            $delete_sql = "DELETE FROM supplier_orders_prod WHERE id IN ($ids_to_delete)";
            mysqli_query($conn, $delete_sql);
        }
    }

    foreach ($insert_items as $idx => $item_data) {
        $product_id = $item_data['product_id'];
        $quantity = $item_data['quantity'];
        $price = $item_data['price'];
        $color = $item_data['color'];

        $insert = "
            INSERT INTO supplier_orders_prod (supplier_order_id, product_id, quantity, price, color)
            VALUES ('$supplier_order_id', '$product_id', '$quantity', '$price', '$color')
        ";

        if (mysqli_query($conn, $insert)) {
            $_SESSION['order_cart'][$idx]['supplier_order_prod_id'] = mysqli_insert_id($conn);
            $_SESSION['order_cart'][$idx]['supplier_order_id'] = $supplier_order_id;
        }
    }

    return true;
}

function getReturnableProducts($orderid) {
    global $conn;

    $orderid = intval($orderid);
    $sql = "SELECT * FROM order_product WHERE orderid = $orderid AND status IN (2, 3, 4)";
    $result = mysqli_query($conn, $sql);

    $products = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }

    return $products;
}

function getUserAccess($staff_id) {
    global $conn;

    $staff_id = mysqli_real_escape_string($conn, $staff_id);
    $access = [];

    $profile_query = "
        SELECT access_profile_id
        FROM staff
        WHERE staff_id = '$staff_id'
        LIMIT 1
    ";
    $profile_result = mysqli_query($conn, $profile_query);
    $profile_row = mysqli_fetch_assoc($profile_result);
    $access_profile_id = (int)($profile_row['access_profile_id'] ?? 0);

    if ($access_profile_id > 0) {
        $profile_pages_query = "
            SELECT page_id, permission
            FROM access_profile_pages
            WHERE access_profile_id = '$access_profile_id'
        ";
        $profile_pages_result = mysqli_query($conn, $profile_pages_query);

        if ($profile_pages_result && mysqli_num_rows($profile_pages_result) > 0) {
            while ($row = mysqli_fetch_assoc($profile_pages_result)) {
                $access[$row['page_id']] = $row['permission'];
            }
        }
    }

    $user_pages_query = "
        SELECT page_id, permission
        FROM user_page_access
        WHERE staff_id = '$staff_id'
    ";
    $user_pages_result = mysqli_query($conn, $user_pages_query);

    if ($user_pages_result && mysqli_num_rows($user_pages_result) > 0) {
        while ($row = mysqli_fetch_assoc($user_pages_result)) {
            $access[$row['page_id']] = $row['permission'];
        }
    }

    return $access;
}

function getPageCategoryName($category_id) {
    global $conn;

    if (empty($category_id)) {
        return 'Uncategorized';
    }

    $category_id = mysqli_real_escape_string($conn, $category_id);

    $query = "SELECT category_name FROM page_categories WHERE id = '$category_id' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['category_name'];
    }

    return 'Unknown';
}

function getPageIdFromUrl($pageKey) {
    global $conn;

    $sql = "SELECT id 
            FROM pages 
            WHERE url = '$pageKey'
            LIMIT 1";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        return intval(mysqli_fetch_assoc($result)['id']);
    }

    return 0;
}

function getVisibleColumns($page_id, $profile_id) {
    global $conn;
    $visibleColumns = [];

    $sql = "
        SELECT 
            pc.id,
            pc.column_name,
            pc.display_name,
            pc.data_type,
            pc.default_visible,
            h.page_column_id IS NOT NULL AS is_hidden
        FROM page_columns pc
        LEFT JOIN hidden_page_column_roles h
            ON h.page_column_id = pc.id
            AND h.page_id = pc.page_id
            AND h.profile_id = ?
        WHERE pc.page_id = ?
        ORDER BY pc.sort_order ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $profile_id, $page_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $isVisible = ($row['default_visible'] && !$row['is_hidden']);
        $visibleColumns[$row['column_name']] = $isVisible;
    }

    return $visibleColumns;
}

function getChatUsers($currentUserId, $searchName = '') {
    global $conn;

    if (!empty($searchName)) {
        $searchName = mysqli_real_escape_string($conn, $searchName);
        $sql = "
            SELECT 
                s.staff_id,
                s.staff_fname,
                s.staff_lname,
                s.username,
                s.profile_path,
                MAX(m.body_text) AS last_message,
                MAX(m.created_at) AS last_time,
                SUM(CASE WHEN m.recipient_user_id = $currentUserId AND m.read_at IS NULL THEN 1 ELSE 0 END) AS unread_count
            FROM staff s
            LEFT JOIN messages m
                ON (m.sender_user_id = s.staff_id AND m.recipient_user_id = $currentUserId)
                OR (m.recipient_user_id = s.staff_id AND m.sender_user_id = $currentUserId)
            WHERE s.staff_id != $currentUserId
              AND CONCAT(s.staff_fname, ' ', s.staff_lname) LIKE '%$searchName%'
            GROUP BY s.staff_id
            ORDER BY (MAX(m.created_at) IS NULL), MAX(m.created_at) DESC
        ";
    } else {
        $sql = "
            SELECT 
                s.staff_id,
                s.staff_fname,
                s.staff_lname,
                s.username,
                s.profile_path,
                MAX(m.body_text) AS last_message,
                MAX(m.created_at) AS last_time,
                SUM(CASE WHEN m.recipient_user_id = $currentUserId AND m.read_at IS NULL THEN 1 ELSE 0 END) AS unread_count
            FROM staff s
            INNER JOIN messages m 
                ON (m.sender_user_id = s.staff_id AND m.recipient_user_id = $currentUserId)
                OR (m.recipient_user_id = s.staff_id AND m.sender_user_id = $currentUserId)
            WHERE s.staff_id != $currentUserId
            GROUP BY s.staff_id
            ORDER BY MAX(m.created_at) DESC
        ";
    }

    $result = mysqli_query($conn, $sql);
    $chatUsers = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $chatUsers[] = [
            'id' => $row['staff_id'],
            'full_name' => $row['staff_fname'] . ' ' . $row['staff_lname'],
            'username' => $row['username'],
            'avatar' => $row['profile_path'] ?: '../assets/images/profile/default.jpg',
            'last_message' => $row['last_message'] ?? '',
            'last_time' => $row['last_time'] ? date('h:i A', strtotime($row['last_time'])) : '',
            'unread_count' => $row['unread_count'] ?? 0
        ];
    }

    return $chatUsers;
}

function getChatMessages($chatUserId) {
    global $conn;

    $currentUserId = intval($_SESSION['userid'] ?? 0);
    $chatUserId = intval($chatUserId);

    $sql = "
        SELECT 
            m.id AS message_id,
            m.sender_user_id,
            m.recipient_user_id,
            m.body_text,
            m.created_at,
            s.staff_name,
            s.profile_path
        FROM messages m
        JOIN staff s ON s.staff_id = m.sender_user_id
        WHERE (m.sender_user_id = $currentUserId AND m.recipient_user_id = $chatUserId)
           OR (m.sender_user_id = $chatUserId AND m.recipient_user_id = $currentUserId)
        ORDER BY m.created_at ASC
    ";

    $result = mysqli_query($conn, $sql);
    $messages = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = [
            'message_id'   => $row['message_id'], // added here
            'sender_id'    => $row['sender_user_id'],
            'recipient_id' => $row['recipient_user_id'],
            'body_text'    => $row['body_text'],
            'created_at'   => $row['created_at'],
            'sender_name'  => $row['staff_name'],
            'sender_avatar'=> $row['profile_path'] ?: '../assets/images/profile/default.jpg'
        ];
    }

    return $messages;
}

function getMessageAttachments($messageId) {
    global $conn;

    $messageId = intval($messageId);
    $sql = "
        SELECT id, file_url, mime_type, file_size_bytes
        FROM message_attachments
        WHERE message_id = $messageId
    ";

    $result = mysqli_query($conn, $sql);
    $attachments = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $attachments[] = [
            'id'       => $row['id'],
            'file_url' => $row['file_url'],
            'mime_type'=> $row['mime_type'],
            'file_size'=> $row['file_size_bytes']
        ];
    }

    return $attachments;
}

function getUserMsgAttachments($userId) {
    global $conn;

    $userId = intval($userId);

    $sql = "
        SELECT 
            ma.id,
            ma.file_url,
            COALESCE(ma.mime_type, '') AS mime_type,  -- ensure it's always a string
            ma.file_size_bytes,
            ma.attachment_type,
            ma.created_at
        FROM message_attachments ma
        INNER JOIN messages m ON ma.message_id = m.id
        WHERE m.sender_user_id = $userId
           OR m.recipient_user_id = $userId
        ORDER BY ma.created_at DESC
    ";

    $result = mysqli_query($conn, $sql);
    $attachments = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $attachments[] = [
            'id'             => (int)$row['id'],
            'file_url'       => $row['file_url'],
            'mime_type'      => $row['mime_type'],
            'file_size'      => isset($row['file_size_bytes']) ? (int)$row['file_size_bytes'] : 0,
            'attachment_type'=> $row['attachment_type'],
            'created_at'     => $row['created_at']
        ];
    }

    return $attachments;
}

function getSaleItems() {
    global $conn;
    $items = [];

    $sql = "
        SELECT 
            sd.saleid,
            sd.category_id,
            sd.product_id,
            sd.date_started,
            sd.date_finished,
            sd.sale_price,
            p.product_item,
            p.unit_price
        FROM sales_discounts sd
        INNER JOIN product p 
            ON p.product_id = sd.product_id
        WHERE sd.sale_price > 0
    ";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
    }

    return $items;
}

function getSalePrice($id) {
    global $conn;
    $id = intval($id);

    $sql_product = "SELECT unit_price FROM product WHERE product_id = $id LIMIT 1";
    $res_product = mysqli_query($conn, $sql_product);
    $row_product = mysqli_fetch_assoc($res_product);
    $unit_price = $row_product ? floatval($row_product['unit_price']) : 0;

    $sql_sale = "
        SELECT sd.date_started, sd.date_finished, sd.sale_price 
        FROM sales_discounts sd
        WHERE sd.product_id = $id
        ORDER BY sd.saleid DESC 
        LIMIT 1
    ";
    $res_sale = mysqli_query($conn, $sql_sale);

    if ($row_sale = mysqli_fetch_assoc($res_sale)) {
        $start = $row_sale['date_started'];
        $end   = $row_sale['date_finished'];
        $sale_price = floatval($row_sale['sale_price']);

        $now = date('Y-m-d H:i:s');
        if (
            $sale_price > 0 && (
                $start == '0000-00-00 00:00:00' || $end == '0000-00-00 00:00:00' ||
                ($now >= $start && $now <= $end)
            )
        ) {
            return $sale_price;
        }
    }

    return $unit_price;
}

function parseNumber($input) {
    // Allow only digits, one space, slash, and dot
    $input = trim($input);

    // Reject if it has disallowed characters
    if (!preg_match('/^[0-9]+(\.[0-9]+)?$|^[0-9]+ [0-9]+\/[0-9]+$|^[0-9]+\/[0-9]+$/', $input)) {
        return 0;
    }

    // If it's already a decimal number (like 1.25)
    if (is_numeric($input)) {
        return floatval($input);
    }

    // If it contains a fraction (with optional whole number + ONE space)
    if (preg_match('/^(\d+)? ?(\d+)\/(\d+)$/', $input, $matches)) {
        $whole = isset($matches[1]) && $matches[1] !== '' ? intval($matches[1]) : 0;
        $numerator = intval($matches[2]);
        $denominator = intval($matches[3]);

        if ($denominator == 0) return 0; // prevent division by zero
        return $whole + ($numerator / $denominator);
    }

    return 0;
}

function getInventoryDimensions($inventory_id) {
    global $conn;
    $inventory_id = (int)$inventory_id;

    $query = "
        SELECT d.dimension, d.dimension_unit
        FROM inventory i
        LEFT JOIN dimensions d ON i.dimension_id = d.dimension_id
        WHERE i.inventory_id = '$inventory_id'
        LIMIT 1";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $dimension     = $row['dimension'] ?? '';
        $dimensionUnit = $row['dimension_unit'] ?? '';

        if ($dimension && $dimensionUnit) {
            return $dimension . ' ' . ucwords($dimensionUnit);
        }
    }

    return null;
}

function getEncryptionKey() {
    $hex = getenv('PASSWORD_ENCRYPTION_KEY');
    if (!$hex) throw new Exception('Encryption key not set in env');
    $key = hex2bin($hex);
    if ($key === false || strlen($key) !== 32) throw new Exception('Invalid encryption key length');
    return $key;
}

function encrypt_password_for_storage(string $plaintext): string {
    $key = getEncryptionKey();
    $method = 'AES-256-CBC';
    $ivlen = openssl_cipher_iv_length($method);
    $iv = random_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
    if ($ciphertext_raw === false) throw new Exception('Encryption failed');
    return base64_encode($iv . $ciphertext_raw);
}

function decrypt_password_from_storage(string $b64): string {
    $key = getEncryptionKey();
    $method = 'AES-256-CBC';
    $data = base64_decode($b64, true);
    if ($data === false) throw new Exception('Invalid base64 data');
    $ivlen = openssl_cipher_iv_length($method);
    if (strlen($data) < $ivlen) throw new Exception('Data too short');
    $iv = substr($data, 0, $ivlen);
    $ciphertext_raw = substr($data, $ivlen);
    $plaintext = openssl_decrypt($ciphertext_raw, $method, $key, OPENSSL_RAW_DATA, $iv);
    if ($plaintext === false) throw new Exception('Decryption failed');
    return $plaintext;
}

function fetchColorMultiplier($colorGroup, $grade = 0, $gauge = 0, $category = 0) {
    global $conn;

    $colorGroup    = intval($colorGroup);
    $grade         = intval($grade);
    $gauge         = intval($gauge);
    $category      = intval($category);

    $query = "SELECT multiplier 
              FROM product_color 
              WHERE color = $colorGroup";

    if ($grade > 0) {
        $query .= " AND grade = $grade";
    }
    if ($gauge > 0) {
        $query .= " AND gauge = $gauge";
    }
    if ($category > 0) {
        $query .= " AND product_category = $category";
    }

    $query .= " LIMIT 1";

    $result = mysqli_query($conn, $query);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        return floatval($row['multiplier']);
    }

    return 1.0;
}

function getMultiplierValue($color_id, $grade_id, $gauge_id) {
    global $conn;
    
    $color_id    = intval($color_id);
    $grade_id    = intval($grade_id);
    $gauge_id    = intval($gauge_id);
    $category_id = intval($category_id);

    $color_details = getColorDetails($color_id);
    $color_group = $color_details['color_group'];

    $multiplier = 1.0;

    if ($color_id > 0) {
        $sql = "SELECT multiplier FROM product_color WHERE id = '$color_group' LIMIT 1";
        $res = $conn->query($sql);
        if ($res && $row = $res->fetch_assoc()) {
            $multiplier *= floatval($row['multiplier']);
        }
    }

    if ($grade_id > 0) {
        $sql = "SELECT multiplier FROM product_grade WHERE product_grade_id = '$grade_id' LIMIT 1";
        $res = $conn->query($sql);
        if ($res && $row = $res->fetch_assoc()) {
            $multiplier *= floatval($row['multiplier']);
        }
    }

    if ($gauge_id > 0) {
        $sql = "SELECT multiplier FROM product_gauge WHERE product_gauge_id = '$gauge_id' LIMIT 1";
        $res = $conn->query($sql);
        if ($res && $row = $res->fetch_assoc()) {
            $multiplier *= floatval($row['multiplier']);
        }
    }

    return $multiplier;
}

function indexToColumnLetter($index) {
    $letters = '';
    while ($index >= 0) {
        $letters = chr($index % 26 + 65) . $letters;
        $index = floor($index / 26) - 1;
    }
    return $letters;
}

function getColumnFromTable($table, $column, $ids = null) {
    global $conn;

    $idColumn = getPrimaryKey($table);
    if (!$idColumn || empty($ids)) {
        return '';
    }

    if (is_string($ids) && $ids[0] === '[') {
        $ids = json_decode($ids, true);
    } elseif (!is_array($ids)) {
        $ids = [$ids];
    }

    $ids = array_filter($ids, 'is_numeric');
    if (!$ids) {
        return '';
    }

    $idList = implode(',', array_map('intval', $ids));
    $query  = "SELECT $column FROM $table WHERE $idColumn IN ($idList)";
    $result = mysqli_query($conn, $query);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row[$column];
    }

    return implode(', ', $data);
}
?>