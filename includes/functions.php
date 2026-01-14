<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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

function getStationName($station_id){
    global $conn;
    $query = "SELECT station_name FROM station WHERE station_id = '$station_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $station_name = !empty($row['station_name']) ? $row['station_name'] : '';
    return  $station_name;
}

function getProductName($product_id){
    global $conn;
    $query = "SELECT product_item, description FROM product WHERE product_id = '$product_id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $product_item = !empty($row['product_item']) ? $row['product_item'] : '';
    return  $product_item;
}

function getCoilConditionName($id){
    global $conn;
    $query = "SELECT coil_condition FROM coil_condition WHERE id = '$id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $coil_condition = !empty($row['coil_condition']) ? $row['coil_condition'] : '';
    return  $coil_condition;
}
//Paki comment eto, seems not being used?
function getProductColorMultName($id){
    global $conn;
    $query = "SELECT color FROM color_multiplier WHERE id = '$id'";
    $result = mysqli_query($conn,$query);
    $row = mysqli_fetch_array($result); 
    $color = $row['color'] ?? '';
    return  $color;
}
//Paki comment eto, seems not being used?
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

    if (empty($id)) {
        return [];
    }

    $id = mysqli_real_escape_string($conn, (string)$id);

    $query = "SELECT * FROM product_availability WHERE product_availability_id = '$id'";
    $result = mysqli_query($conn, $query);

    $product_availability = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $product_availability = $row;
    }
    return $product_availability;
}

function getProductScrewTypeDetails($product_screw_type_id) {
    global $conn;
    $product_screw_type_id = mysqli_real_escape_string($conn, $product_screw_type_id);
    $query = "SELECT * FROM product_screw_type WHERE product_screw_type_id = '$product_screw_type_id'";
    $result = mysqli_query($conn, $query);
    $product_screw_type = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $product_screw_type = $row;
    }
    return $product_screw_type;
}

function getProductScrewCoatingDetails($product_screw_coating_id) {
    global $conn;
    $product_screw_coating_id = mysqli_real_escape_string($conn, $product_screw_coating_id);
    $query = "SELECT * FROM product_screw_coating WHERE product_screw_coating_id = '$product_screw_coating_id'";
    $result = mysqli_query($conn, $query);
    $product_screw_coating = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $product_screw_coating = $row;
    }
    return $product_screw_coating;
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

function getWorkOrderProductDetails($work_order_product_id) {
    global $conn;
    $work_order_product_id = mysqli_real_escape_string($conn, $work_order_product_id);
    $query = "SELECT * FROM work_order WHERE work_order_product_id = '$work_order_product_id'";
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
//Saan eto ginagamit?
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

function getCustomerPricingDetails($id) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT * FROM customer_pricing WHERE id = '$id' LIMIT 1";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
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

function getDimensionName($dimension_id){
    global $conn;
    $dimension_id = intval($dimension_id);
    $query = "SELECT * FROM dimensions WHERE dimension_id = '$dimension_id'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['dimension'];
    }

    return '';
}

function getDimensionID($dimension){
    global $conn;
    $query = "SELECT dimension_id FROM dimensions WHERE dimension = '$dimension'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['dimension_id'];
    }

    return '';
}

function getScrewDimensionID($dimension){
    global $conn;
    $screw_id = 16;
    $query = "SELECT dimension_id FROM dimensions WHERE dimension = '$dimension' AND dimension_category = '$screw_id'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['dimension_id'];
    }

    return '';
}

function getLumberDimensionID($dimension){
    global $conn;
    $lumber_id = 1;
    $query = "SELECT dimension_id FROM dimensions WHERE dimension = '$dimension' AND dimension_category = '$lumber_id'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['dimension_id'];
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
//same here->
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

function getDimensionDetails($dimension_id) {
    global $conn;
    $dimension_id = mysqli_real_escape_string($conn, $dimension_id);
    $query = "SELECT * FROM dimensions WHERE dimension_id = '$dimension_id'";
    $result = mysqli_query($conn, $query);
    $dimensions = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $dimensions = $row;
    }
    return $dimensions;
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

function getGaugeDetails($product_gauge_id) {
    global $conn;
    $product_gauge_id = mysqli_real_escape_string($conn, $product_gauge_id);
    $query = "SELECT * FROM product_gauge WHERE product_gauge_id = '$product_gauge_id'";
    $result = mysqli_query($conn, $query);
    $product_gauge = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $product_gauge = $row;
    }
    return $product_gauge;
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

function getProductScrewType($product_screw_type_id) {
    global $conn;
    $product_screw_type_id = mysqli_real_escape_string($conn, $product_screw_type_id);
    $query = "SELECT * FROM product_screw_type WHERE product_screw_type_id = '$product_screw_type_id'";
    $result = mysqli_query($conn, $query);
    $product_screw_type = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $product_screw_type = $row;
    }
    return $product_screw_type;
}

function getProductLumberType($product_lumber_type_id) {
    global $conn;
    $product_lumber_type_id = mysqli_real_escape_string($conn, $product_lumber_type_id);
    $query = "SELECT * FROM product_lumber_type WHERE product_lumber_type_id = '$product_lumber_type_id'";
    $result = mysqli_query($conn, $query);
    $product_lumber_type = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $product_lumber_type = $row;
    }
    return $product_lumber_type;
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

function getSupplierPackDetails($id) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT * FROM supplier_pack WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $supplier_pack = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $supplier_pack = $row;
    }
    return $supplier_pack;
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

function get_customer_name($customer_id) {
    global $conn;

    $customer_id = intval($customer_id);

    $query = "
        SELECT 
            customer_first_name,
            customer_last_name,
            customer_business_name,
            customer_farm_name,
            use_business,
            use_farm
        FROM customer
        WHERE customer_id = '$customer_id'
        LIMIT 1
    ";

    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {

        if ($row['use_business'] == 1 && !empty($row['customer_business_name'])) {
            return trim($row['customer_business_name']);
        }

        if ($row['use_farm'] == 1 && !empty($row['customer_farm_name'])) {
            return trim($row['customer_farm_name']);
        }

        if (!empty($row['customer_business_name'])) {
            return trim($row['customer_business_name']);
        }

        if (!empty($row['customer_farm_name'])) {
            return trim($row['customer_farm_name']);
        }

        $fullName = trim($row['customer_first_name'] . ' ' . $row['customer_last_name']);
        if (!empty($fullName)) {
            return $fullName;
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

function getOrderEstimateDetails($id) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT * FROM order_estimate WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $order_estimate = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $order_estimate = $row;
    }
    return $order_estimate;
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
        SELECT credit_amount AS amount
        FROM customer_store_credit_history
        WHERE customer_id = '$customer_id'
          AND credit_type = 'add'
          AND credit_amount > 0

        UNION ALL

        SELECT deposit_remaining AS amount
        FROM job_deposits jd
        
        WHERE jd.deposited_by = '$customer_id'
          AND deposit_status = 1
          AND deposit_remaining > 0
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

function getPricingCategory($product_category_id, $customer_pricing_id,$data_id) {
    global $conn;
    $percentage = 0;
    $query = "
        SELECT percentage 
        FROM pricing_category 
        WHERE 
            product_category_id = '$product_category_id' AND
            customer_pricing_id = '$customer_pricing_id'AND
            FIND_IN_SET('$data_id', product_items) > 0
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
        /* $customer_ttl_orders = getCustomerOrderTotal($customer_id);
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
        } */
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

function getIDByName($table, $column, $value) {
    global $conn;

    $primary = getPrimaryKey($table);
    if (!$primary) return null;

    $value = strtolower(trim($value));
    $value = mysqli_real_escape_string($conn, $value);

    $sql = "SELECT `$primary` 
            FROM `$table` 
            WHERE LOWER(TRIM(`$column`)) LIKE '%$value%' 
            LIMIT 1";

    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row[$primary];
    }

    return null;
}

function getCartDataByCustomerId($customer_id) {
    global $conn;

    $customer_id = (int)$customer_id;
    $cartData = [];

    $query = "SELECT * FROM customer_cart WHERE customer_id = $customer_id ORDER BY id ASC";
    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $lineKey = (int)$row['id'];
            $cartData[$lineKey] = $row;

            $cartData[$lineKey]['line'] = $lineKey;
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

function getCoilEntry($id) {
    global $conn;

    $id = intval($id);

    $sql = "SELECT entry_no FROM coil_product WHERE coil_id = $id LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['entry_no'];
    }

    return null;
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
    $lengthStr = trim(strtolower($lengthStr));
    if ($lengthStr === '') return 0;

    $feet = 0;
    $inches = 0;

    if (preg_match('/(\d+(\.\d+)?)\s*ft/', $lengthStr, $match)) {
        $feet = floatval($match[1]);
    }

    if (preg_match('/(\d+(\.\d+)?)\s*in/', $lengthStr, $match)) {
        $inches = floatval($match[1]);
    }

    return $feet + ($inches / 12);
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
        SELECT dimension_id, dimension 
        FROM dimensions 
        WHERE dimension_id IN ($ids)
    ";
    $dim_result = mysqli_query($conn, $dim_query);

    if (!$dim_result) {
        return [];
    }

    while ($dim = mysqli_fetch_assoc($dim_result)) {
        $length_value = trim($dim['dimension']);

        $lengths[] = [
            'inventory_id' => null,
            'dimension_id' => $dim['dimension_id'],
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
        $dimensionStr = trim($row['dimension']);

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
            iv.dimension_id,
            d.dimension,
            d.dimension_unit,
            pvl.length AS variant_length,
            p.unit_price as price,
            p.product_category as product_category
        FROM inventory iv
        LEFT JOIN dimensions d ON iv.dimension_id = d.dimension_id
        LEFT JOIN product_variant_length pvl ON iv.Inventory_id = pvl.inventory_id
        LEFT JOIN product p ON iv.Product_id = p.product_id
        WHERE iv.Product_id = '$product_id'
          AND iv.quantity_ttl > 0
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

        $price  = floatval($row['price']);

        $screw_id = 16;
        $category = $row['product_category'];

        if($category == $screw_id){
            $res = mysqli_query($conn, "SELECT * FROM product_screw_lengths WHERE product_id = '$product_id' AND dimension_id = '$dim_id' LIMIT 1");
            $row = mysqli_fetch_assoc($res);

            $price  = floatval($row['unit_price'] ?? 0);
        }

        $color_id = $row['color_id'];
        $multiplier = getMultiplierValue('',$color_id, '', '');

        $price *= $multiplier;

        $inventory[] = [
            'inventory_id'   => $row['Inventory_id'],
            'color_id'       => $row['color_id'],
            'quantity'       => (int)$row['quantity'],
            'quantity_ttl'   => (int)$row['quantity_ttl'],
            'pack'           => (int)$row['pack'],
            'lumber_type'    => $row['lumber_type'],
            'cost'           => (float)$row['cost'],
            'price'          => $price,
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
            return $dimension;
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
//
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

function getMultiplierValue($category_id,$color_id, $grade_id, $gauge_id) {
    global $conn;
    
    $color_id    = intval($color_id);
    $grade_id    = intval($grade_id);
    $gauge_id    = intval($gauge_id);
    $category_id = intval($gauge_id);

    $color_details = getColorDetails($color_id);
    $color_group = $color_details['color_group'] ?? '';

    $multiplier = 1.0;

    if ($color_id > 0) {
        //$sql = "SELECT multiplier FROM product_color WHERE id = '$color_group' LIMIT 1";
           $multiplier = 1.0;
            $sql = "
            SELECT multiplier 
            FROM product_color 
            WHERE id = '$color_group'
            AND FIND_IN_SET('$category_id', product_category)
            AND FIND_IN_SET('$grade_id', grade)
            AND FIND_IN_SET('$gauge_id', gauge)
            LIMIT 1
             ";

            $res = $conn->query($sql);

             if ($res && $row = $res->fetch_assoc()) {
            $multiplier = floatval($row['multiplier']); // override if found
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

function getIdsFromColumnValues($table, $column, $values = null) {
    global $conn;

    $idColumn = getPrimaryKey($table);
    if (!$idColumn) return '[]';

    if (is_string($values)) {
        $values = explode(',', $values);
    } elseif (!is_array($values)) {
        $values = [$values];
    }

    $values = array_map('trim', $values);
    $values = array_filter($values, fn($v) => $v !== '' && $v !== null);

    if (empty($values)) return '[]';

    $escapedValues = array_map(function($val) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $val) . "'";
    }, $values);

    $valueList = implode(',', $escapedValues);

    $query = "SELECT $idColumn FROM $table WHERE $column IN ($valueList)";
    $result = mysqli_query($conn, $query);

    $ids = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ids[] = (int)$row[$idColumn];
    }

    return json_encode($ids);
}

function getDimensions($ids) {
    global $conn;

    if (empty($ids)) return '';

    if (is_string($ids) && $ids[0] === '[') $ids = json_decode($ids, true);
    elseif (!is_array($ids)) $ids = [$ids];

    $ids = array_filter($ids, 'is_numeric');
    if (!$ids) return '';

    $idList = implode(',', array_map('intval', $ids));
    $query = "SELECT dimension FROM dimensions WHERE dimension_id IN ($idList)";
    $result = mysqli_query($conn, $query);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $dim = trim($row['dimension'] ?? '');
        if ($dim !== '') $data[] = $dim;
    }

    return implode(',', $data);
}

function calculateCartItem($values) {
    global $conn;

    $panel_id = 3;
    $trim_id  = 4;
    $screw_id = 16;
    $lumber_id = 1;
    $service_chrg_id = 27;

    $customer_id = $_SESSION['customer_id'];
    $customer_details = getCustomerDetails($customer_id);
    $customer_details_pricing = $customer_details['customer_pricing'];

    $data_id     = $values["product_id"] ?? '';
    $line        = $values["line"] ?? '';
    $length_id   = $values["length_id"] ?? '';
    $product     = getProductDetails($data_id);
    $stock_qty   = getProductStockTotal($data_id);
    $category_id = $product["product_category"];
    $product_type= $product['product_type'];

    $customer_pricing_rate = getPricingCategory($category_id, $customer_details_pricing,$data_id) / 100;
    $pack = isset($values['pack']) && is_numeric($values['pack']) ? floatval($values['pack']) : 1;
    $estimate_length      = isset($values["estimate_length"]) && is_numeric($values["estimate_length"]) ? floatval($values["estimate_length"]) : 1;
    $estimate_length_inch = isset($values["estimate_length_inch"]) && is_numeric($values["estimate_length_inch"]) ? floatval($values["estimate_length_inch"]) : 0;
    $total_length = $estimate_length + ($estimate_length_inch / 12);
    if ($total_length <= 0) $total_length = 1;

    $amount_discount= isset($values["amount_discount"]) ? floatval($values["amount_discount"]) : 0;
    $quantity       = isset($values["quantity_cart"]) ? floatval($values["quantity_cart"]) : 0;

    $color_id       = intval($values["custom_color"] ?? 0);
    $grade          = intval($values["custom_grade"] ?? 0);
    $gauge          = intval($values["custom_gauge"] ?? 0);
    $profile        = intval($values["custom_profile"] ?? 0);
    $dimension_id   = intval($values["dimension_id"] ?? 0);

    $trim_no  = trim($values["trim_no"] ?? 0);

    if ($category_id == $panel_id || $category_id == $trim_id) {

        $query_coil = "
            SELECT 1
            FROM coil_product
            WHERE
                color_sold_as = '" . mysqli_real_escape_string($conn, $color_id) . "'
                AND grade_sold_as = '" . mysqli_real_escape_string($conn, $grade) . "'
                AND gauge_sold_as = '" . mysqli_real_escape_string($conn, $gauge) . "'
            LIMIT 1
        ";
        $result = mysqli_query($conn, $query_coil);

        if (mysqli_num_rows($result) > 0) {

            $stock_text = '
            <a href="javascript:void(0);" id="view_in_stock"
            data-id="' . htmlspecialchars($data_id, ENT_QUOTES, 'UTF-8') . '"
            data-color="' . htmlspecialchars($color_id, ENT_QUOTES, 'UTF-8') . '"
            data-grade="' . htmlspecialchars($grade, ENT_QUOTES, 'UTF-8') . '"
            data-gauge="' . htmlspecialchars($gauge, ENT_QUOTES, 'UTF-8') . '"
            class="d-flex justify-content-center align-items-center">
                <span class="text-bg-warning p-1 rounded-circle"></span>
                <span class="ms-2 fs-3">Available</span>
            </a>';
        } else {

            $stock_text = '
            <a href="javascript:void(0);" id="view_out_of_stock"
            class="d-flex justify-content-center align-items-center">
                <span class="text-bg-danger p-1 rounded-circle"></span>
                <span class="ms-2 fs-3">Out of Stock</span>
            </a>';
        }

    } else {

        $stock_text = ($stock_qty > 0)
            ? '<a href="javascript:void(0);" id="view_in_stock" data-id="' . htmlspecialchars($data_id, ENT_QUOTES, 'UTF-8') . '" class="d-flex justify-content-center align-items-center">
                    <span class="text-bg-success p-1 rounded-circle"></span>
                    <span class="ms-2 fs-3">In Stock</span>
            </a>'
            : '<a href="javascript:void(0);" id="view_out_of_stock" class="d-flex justify-content-center align-items-center">
                    <span class="text-bg-danger p-1 rounded-circle"></span>
                    <span class="ms-2 fs-3">Out of Stock</span>
            </a>';
    }

    $panel_type = $values["panel_type"] ?? '';
    $panel_style= $values["panel_style"] ?? '';

    $width    = floatval($values["width"] ?? 0);
    $hems      = floatval($values["hem"] ?? 0);
    $bends     = floatval($values["bend"] ?? 0);

    $bulk_price     = isset($product["bulk_price"]) ? floatval($product["bulk_price"]) : 0;
    $bulk_starts_at = isset($product["bulk_starts_at"]) ? floatval($product["bulk_starts_at"]) : 0;
    $base_price     = $product["unit_price"] ?? 0;

    if(!empty($values["manual_unit_price"])){
        $base_price = $values["manual_unit_price"];
    }

    if ($bulk_price > 0 && $bulk_starts_at > 0 && $quantity >= $bulk_starts_at) {
        $base_price = $bulk_price;
    }

    $unit_price = calculateUnitPrice(
        $base_price,
        $estimate_length,
        $estimate_length_inch,
        $panel_type,
        $product["sold_by_feet"] ?? 0,
        $bends,
        $hems,
        $color_id,
        $grade,
        $gauge,
        $width
    );

    $product_id_abbrev = fetchSingleProductABR(
        $category_id,
        $profile,
        $grade,
        $gauge,
        '',
        $color_id,
        $length_id
    );

    $parent_prod_id = '';
    $unique_prod_id = '';

    

    if($category_id == $panel_id){
        $parent_prod_id = getProdID([
            'category'   => $category_id,
            'profile'    => $profile,
            'grade'      => $grade,
            'gauge'      => $gauge,
            'color'      => $color_id,
            'product_id' => $data_id 
        ]);

        $unique_prod_id = getProdID([
            'category' => $category_id,
            'profile'  => $profile,
            'grade'    => $grade,
            'gauge'    => $gauge,
            'color'    => $color_id,
            'length'       => $total_length,
            'panel_type'   => $panel_type,
            'panel_style'  => $panel_style,
            'product_id' => $data_id
        ]);
    }else if($category_id == $trim_id){
        $parent_prod_id = getProdID([
            'category' => $category_id,
            'type'     => $product_type,
            'line'     => $data_id,
            'grade'    => $grade,
            'gauge'    => $gauge,
            'color'    => $color_id,
            'product_id' => $data_id,
            'trim_no' => $trim_no
        ]);

        $unique_prod_id = getProdID([
            'category' => $category_id,
            'type'     => $product_type,
            'line'     => $data_id,
            'grade'    => $grade,
            'gauge'    => $gauge,
            'color'    => $color_id,
            'length'   => $total_length,
            'product_id' => $data_id,
            'trim_no' => $trim_no
        ]);
    }else if($category_id == $screw_id){
        $screw_length = $values["screw_length"] ?? '';
        $screw_type   = $values["screw_type"] ?? '';

        $estimate_length       = $total_length;
        $estimate_length_inch  = 0;

        $parent_prod_id = getProdID([
            'category' => $category_id,
            'type'     => $product_type,
            'color'    => $color_id,
            'product_id' => $data_id
        ]);

        $unique_prod_id = getProdID([
            'category'      => $category_id,
            'type'          => $product_type,
            'screw_type'    => $screw_type,
            'color'         => $color_id,
            'screw_length'  => $screw_length,
            'product_id' => $data_id
        ]);
        $res = mysqli_query($conn, "SELECT * FROM product_screw_lengths WHERE product_id = '$data_id' AND dimension_id = '$dimension_id' LIMIT 1");
        $row = mysqli_fetch_assoc($res);

        $base_price  = floatval($row['unit_price'] ?? 0);
        $bulk_price  = floatval($row['bulk_price'] ?? 0);

        if ($bulk_price > 0 && $bulk_starts_at > 0 && $quantity >= $bulk_starts_at) {
            $base_price = $bulk_price;
        }

        $unit_price = calculateUnitPrice(
            $base_price,
            1,
            '',
            '',
            '',
            '',
            '',
            $color_id,
            '',
            ''
        ) * $pack;
    }else if($category_id == $lumber_id){
        $res = mysqli_query($conn, "SELECT * FROM product_lumber_lengths WHERE product_id = '$data_id' AND dimension_id = '$dimension_id' LIMIT 1");
        $row = mysqli_fetch_assoc($res);

        $base_price  = floatval($row['unit_price'] ?? 0);
        $bulk_price  = floatval($row['bulk_price'] ?? 0);

        if ($bulk_price > 0 && $bulk_starts_at > 0 && $quantity >= $bulk_starts_at && $bulk_starts_at > 0) {
            $base_price = $bulk_price;
        }

        $unit_price = calculateUnitPrice(
            $base_price,
            1,
            '',
            '',
            '',
            '',
            '',
            $color_id,
            '',
            ''
        );
    }else if($category_id == $service_chrg_id){
        $base_price = $values["unit_price"];
        $unit_price = $values["unit_price"];
    }

    $linear_price = $base_price;
    $panel_price  = $unit_price;

    $product_price = ($quantity * $unit_price) - $amount_discount;

    if (!empty($values["is_custom"])) {
        $custom_multiplier = floatval(getCustomMultiplier($category_id));
        $product_price += $product_price * $custom_multiplier;
    }
//added category_id->
    $multiplier = getMultiplierValue($category_id, $color_id, $grade, $gauge);
    $discount = isset($values["used_discount"]) ? floatval($values["used_discount"]) / 100 : 0;

    $subtotal       = $product_price;
    $customer_price = $product_price * (1 - $discount) * (1 - $customer_pricing_rate);
    $savings        = $product_price - $customer_price;

    return [
        "data_id"           => $data_id,
        "line"              => $line,
        "product"           => $product,
        "category_id"       => $category_id,
        "stock_qty"         => $stock_qty,
        "stock_text"        => $stock_text,
        "default_image"     => '../images/product/product.jpg',
        "picture_path"      => !empty($product['main_image']) ? "../" . $product['main_image'] : "../images/product/product.jpg",
        "images_directory"  => "../images/drawing/",
        "quantity"          => $quantity,
        "base_price"        => $base_price,
        "unit_price"        => $unit_price,
        "linear_price"      => $linear_price,
        "panel_price"       => $panel_price,
        "total_length"      => $total_length,
        "amount_discount"   => $amount_discount,
        "product_price"     => $product_price,
        "subtotal"          => $subtotal,
        "customer_price"    => $customer_price,
        "savings"           => $savings,
        "color_id"          => $color_id,
        "grade"             => $grade,
        "gauge"             => $gauge,
        "profile"           => $profile,
        "discount"          => $discount,
        "multiplier"        => $multiplier,
        "customer_pricing_rate" => $customer_pricing_rate,
        "sold_by_feet"      => $product["sold_by_feet"] ?? 0,
        "drawing_data"      => $values["drawing_data"] ?? '',
        "panel_type"        => $panel_type,
        "bends"             => $bends,
        "hems"              => $hems,
        "length_id"         => $length_id,
        "product_id_abbrev" => $product_id_abbrev,
        "parent_prod_id"    => $parent_prod_id,
        "unique_prod_id"    => $unique_prod_id,
        "pack"    => $pack,
    ];
}

function getScrewPrice(int $product_id, int $dimension_id)
{
    global $conn;

    if ($product_id <= 0 || $dimension_id <= 0) return 0;

    $product_id   = mysqli_real_escape_string($conn, $product_id);
    $dimension_id = mysqli_real_escape_string($conn, $dimension_id);

    $sql = "
        SELECT unit_price
        FROM product_screw_lengths
        WHERE product_id = '{$product_id}'
        AND dimension_id = '{$dimension_id}'
        LIMIT 1
    ";

    $res = mysqli_query($conn, $sql);
    if (!$res) return 0;

    $row = mysqli_fetch_assoc($res);
    return (float)($row['unit_price'] ?? 0);
}

function getLumberPrice(int $product_id, int $dimension_id)
{
    global $conn;

    if ($product_id <= 0 || $dimension_id <= 0) return 0;

    $product_id   = mysqli_real_escape_string($conn, $product_id);
    $dimension_id = mysqli_real_escape_string($conn, $dimension_id);

    $sql = "
        SELECT unit_price
        FROM product_lumber_lengths
        WHERE product_id = '{$product_id}'
        AND dimension_id = '{$dimension_id}'
        LIMIT 1
    ";

    $res = mysqli_query($conn, $sql);
    if (!$res) return 0;

    $row = mysqli_fetch_assoc($res);
    return (float)($row['unit_price'] ?? 0);
}

function getProductPrice(int $product_id)
{
    if ($product_id <= 0) return 0;

    $product = getProductDetails($product_id);
    return (float)($product['unit_price'] ?? 0);
}

function createNet30Approval($customerid, $cashierid, $pay_type, $charge_net_30, $job_info = [], $delivery_info = [], $tax_rate = 0.0, $discount_default = 0.0) {
    global $conn;

    if ($pay_type !== 'net30') {
        return ['success' => false, 'error' => 'Payment type is not net30.'];
    }

    $total_price = 0;
    $total_discounted_price = 0;
    $discount_percent = $discount_default;

    if (empty($_SESSION['cart'])) {
        return ['success' => false, 'error' => 'Cart is empty.'];
    }

    foreach ($_SESSION['cart'] as $cart_item) {
        $calc = calculateCartItem($cart_item);

        $discount = isset($cart_item['used_discount']) ? floatval($cart_item['used_discount']) / 100 : $discount_default;

        $quantity = $calc['quantity'] ?? 0;
        $total_length = $calc['total_length'] ?? 1;
        $unit_price = $calc['product_price'] / $total_length / max($quantity,1);
        $actual_price = $unit_price * $quantity * $total_length;

        $price_after_discount = ($actual_price * (1 - $discount) * (1 - ($calc['customer_pricing_rate'] ?? 0))) - ($calc['amount_discount'] ?? 0);
        $price_after_discount = max(0, $price_after_discount);

        $discounted_price = $price_after_discount * (1 + $tax_rate);

        $total_price += $actual_price;
        $total_discounted_price += $discounted_price;
    }

    if ($charge_net_30 >= $total_discounted_price) {
        return ['success' => true, 'message' => 'Net30 balance sufficient. No approval needed.'];
    }

    $job_po = mysqli_real_escape_string($conn, $job_info['job_po'] ?? '');
    $job_name = mysqli_real_escape_string($conn, $job_info['job_name'] ?? '');
    $deliver_address = mysqli_real_escape_string($conn, $delivery_info['deliver_address'] ?? '');
    $deliver_city = mysqli_real_escape_string($conn, $delivery_info['deliver_city'] ?? '');
    $deliver_state = mysqli_real_escape_string($conn, $delivery_info['deliver_state'] ?? '');
    $deliver_zip = mysqli_real_escape_string($conn, $delivery_info['deliver_zip'] ?? '');
    $delivery_amt = mysqli_real_escape_string($conn, $delivery_info['delivery_amt'] ?? '');
    $deliver_fname = mysqli_real_escape_string($conn, $delivery_info['deliver_fname'] ?? '');
    $deliver_lname = mysqli_real_escape_string($conn, $delivery_info['deliver_lname'] ?? '');

    $insert_approval = "
        INSERT INTO approval (
            status, cashier, total_price, discounted_price, discount_percent,
            cash_amt, disc_amount, submitted_date, customerid, originalcustomerid,
            job_name, job_po, deliver_address, deliver_city, deliver_state,
            deliver_zip, delivery_amt, deliver_fname, deliver_lname, type_approval, pay_type
        ) VALUES (
            1, '$cashierid', '$total_price', '$total_discounted_price', '$discount_percent',
            0, 0, NOW(), '$customerid', '$customerid',
            '$job_name', '$job_po', '$deliver_address', '$deliver_city', '$deliver_state',
            '$deliver_zip', '$delivery_amt', '$deliver_fname', '$deliver_lname', 2 , '$pay_type'
        )
    ";

    if (!mysqli_query($conn, $insert_approval)) {
        return ['success' => false, 'error' => 'Approval insert failed: ' . mysqli_error($conn)];
    }

    $approval_id = mysqli_insert_id($conn);

    foreach ($_SESSION['cart'] as $cart_item) {
        $calc = calculateCartItem($cart_item);

        $sql = "
            INSERT INTO approval_product (
                approval_id, productid, product_item, status, quantity, custom_color,
                custom_grade, custom_profile, custom_width, custom_height, custom_bend, custom_hem,
                custom_length, custom_length2, actual_price, discounted_price,
                product_category, usageid, current_customer_discount, current_loyalty_discount,
                used_discount, stiff_stand_seam, stiff_board_batten, panel_type, panel_style
            ) VALUES (
                '$approval_id',
                '" . intval($calc['data_id']) . "',
                '" . mysqli_real_escape_string($conn, $calc['product']['product_name']) . "',
                0,
                '" . mysqli_real_escape_string($conn, $calc['quantity']) . "',
                '" . (isset($calc['color_id']) ? intval($calc['color_id']) : '') . "',
                '" . (isset($calc['grade']) ? intval($calc['grade']) : '') . "',
                '" . (isset($calc['profile']) ? intval($calc['profile']) : '') . "',
                '" . mysqli_real_escape_string($conn, $calc['product']['width'] ?? '') . "',
                '',
                '',
                '',
                '" . mysqli_real_escape_string($conn, $calc['total_length']) . "',
                '',
                '" . floatval($calc['product_price']) . "',
                '" . floatval($calc['customer_price']) . "',
                '" . intval($calc['category_id']) . "',
                0,
                '',
                '',
                '" . (isset($calc['discount']) ? floatval($calc['discount'] * 100) : '') . "',
                '" . intval($calc['product']['standing_seam'] ?? 0) . "',
                '" . intval($calc['product']['board_batten'] ?? 0) . "',
                '" . intval($calc['product']['panel_type'] ?? 0) . "',
                '" . intval($calc['product']['panel_style'] ?? 0) . "'
            )
        ";

        if (!mysqli_query($conn, $sql)) {
            return [
                'success' => false,
                'error' => 'Product approval insert failed!',
                'error_query' => mysqli_error($conn),
            ];
        }
    }

    $actorId = $cashierid;
    $targetId = $approval_id;
    $targetType = "Request Approval(Net Balance)";
    $message = "Approval #$targetId requested due to insufficient Net balance";
    $url = '?page=approval_list';
    createNotification($actorId, 'request_approval', $targetId, $targetType, $message, 'admin', $url);

    unset($_SESSION['cart']);

    return [
        'success' => true,
        'approval_id' => $approval_id,
        'total_price' => $total_price,
        'total_discounted_price' => $total_discounted_price,
        'message' => 'Approval request created due to insufficient Net balance.'
    ];
}

function generateProductAbr(
    $category_ids, 
    $profile_ids, 
    $grade_ids, 
    $gauge_ids, 
    $type_ids, 
    $color_ids, 
    $length_ids, 
    $product_id_from_table = null
) {
    global $conn;

    $panel_id = 3;
    $trim_id  = 4;

    if (
        empty($category_ids) && empty($profile_ids) && empty($grade_ids) &&
        empty($gauge_ids) && empty($type_ids) && empty($color_ids) && empty($length_ids)
    ) {
        return 0;
    }

    $maps = [
        'category' => getAbbrMap('product_category', 'product_category_id', 'category_abreviations', $category_ids),
        'profile'  => getAbbrMap('profile_type', 'profile_type_id', 'profile_abbreviations', $profile_ids),
        'grade'    => getAbbrMap('product_grade', 'product_grade_id', 'grade_id_no', $grade_ids),
        'gauge'    => getAbbrMap('product_gauge', 'product_gauge_id', 'gauge_id_no', $gauge_ids),
        'type'     => getAbbrMap('product_type', 'product_type_id', 'type_abreviations', $type_ids),
        'color'    => getAbbrMap('paint_colors', 'color_id', 'ekm_color_no', $color_ids),
    ];

    $idGroups = [
        'category' => !empty($category_ids) ? $category_ids : [null],
        'profile'  => !empty($profile_ids) ? $profile_ids : [null],
        'grade'    => !empty($grade_ids) ? $grade_ids : [null],
        'gauge'    => !empty($gauge_ids) ? $gauge_ids : [null],
        'type'     => !empty($type_ids) ? $type_ids : [null],
        'color'    => !empty($color_ids) ? $color_ids : [null],
        'length'   => !empty($length_ids) ? $length_ids : [null],
    ];

    $combinations = [[]];
    foreach ($idGroups as $key => $ids) {
        $new = [];
        foreach ($combinations as $combo) {
            foreach ($ids as $id) {
                $combo[$key] = $id;
                $new[] = $combo;
            }
        }
        $combinations = $new;
    }

    $inserted = 0;
    $product_id_from_table_sql = is_numeric($product_id_from_table) ? intval($product_id_from_table) : 'NULL';

    foreach ($combinations as $c) {
        $category_id = $c['category'];
        $categoryAbbr = $category_id && isset($maps['category'][$category_id]) ? $maps['category'][$category_id] : '';

        $profileAbbr = $c['profile'] && isset($maps['profile'][$c['profile']]) ? $maps['profile'][$c['profile']] : '';
        $gradeAbbr   = $c['grade']   && isset($maps['grade'][$c['grade']])     ? $maps['grade'][$c['grade']]     : '';
        $gaugeAbbr   = $c['gauge']   && isset($maps['gauge'][$c['gauge']])     ? $maps['gauge'][$c['gauge']]     : '';
        $typeAbbr    = $c['type']    && isset($maps['type'][$c['type']])       ? $maps['type'][$c['type']]       : '';
        $colorAbbr   = $c['color']   && isset($maps['color'][$c['color']])     ? $maps['color'][$c['color']]     : '';
        $lengthAbbr  = $c['length']  && isset($maps['length'][$c['length']])   ? $maps['length'][$c['length']]   : '';

        $abbr = '';
        if ($categoryAbbr !== '') $abbr .= $categoryAbbr;

        if ($category_id == $panel_id) {
            if ($profileAbbr !== '' || $gradeAbbr !== '' || $gaugeAbbr !== '') {
                $abbr .= '-' . $profileAbbr . $gradeAbbr . $gaugeAbbr;
            }
        } elseif ($category_id == $trim_id) {
            if ($typeAbbr !== '') {
                $abbr .= '-' . $typeAbbr;
                if (!empty($product_id_from_table_sql) && $product_id_from_table_sql !== 'NULL') {
                    $abbr .= $product_id_from_table_sql;
                }
            }
            if ($gradeAbbr !== '' || $gaugeAbbr !== '') {
                $abbr .= '-' . $gradeAbbr . $gaugeAbbr;
            }
        } else {
            $abbr .= $profileAbbr . $typeAbbr . $gradeAbbr . $gaugeAbbr;
        }

        if ($colorAbbr !== '') {
            $abbr .= '-' . $colorAbbr;
        }

        if ($lengthAbbr !== '') {
            $abbr .= $lengthAbbr;
        }

        if ($abbr === '') continue;

        $abbr_escaped = mysqli_real_escape_string($conn, $abbr);

        $check_sql = "SELECT 1 FROM product_abr WHERE product_id='$abbr_escaped'";
        if ($product_id_from_table_sql !== 'NULL') {
            $check_sql .= " AND product_id_from_table=$product_id_from_table_sql";
        }
        $check_sql .= " LIMIT 1";
        $check = mysqli_query($conn, $check_sql);
        if (mysqli_num_rows($check) > 0) continue;

        $sql = sprintf(
            "INSERT INTO product_abr (product_id, product_id_from_table, category, profile, grade, gauge, type, color, length)
            VALUES ('%s', %s, %s, %s, %s, %s, %s, %s, %s)",
            $abbr_escaped,
            $product_id_from_table_sql,
            $category_id ? intval($category_id) : 'NULL',
            $c['profile'] ? intval($c['profile']) : 'NULL',
            $c['grade'] ? intval($c['grade']) : 'NULL',
            $c['gauge'] ? intval($c['gauge']) : 'NULL',
            $c['type'] ? intval($c['type']) : 'NULL',
            $c['color'] ? intval($c['color']) : 'NULL',
            $c['length'] ? intval($c['length']) : 'NULL'
        );

        mysqli_query($conn, $sql);
        $inserted += mysqli_affected_rows($conn);
    }

    return $inserted;
}

function regenerateABR($table, $id) {
    global $conn;

    $panel_id = 3;
    $trim_id  = 4;

    // Mapping for column and abbreviation fields
    $cfgs = [
        'product_category' => ['col' => 'category', 'id' => 'product_category_id', 'abbr' => 'category_abreviations'],
        'profile_type'     => ['col' => 'profile',  'id' => 'profile_type_id',     'abbr' => 'profile_abbreviations'],
        'product_grade'    => ['col' => 'grade',    'id' => 'product_grade_id',    'abbr' => 'grade_abbreviations'],
        'product_gauge'    => ['col' => 'gauge',    'id' => 'product_gauge_id',    'abbr' => 'gauge_abbreviations'],
        'product_type'     => ['col' => 'type',     'id' => 'product_type_id',     'abbr' => 'type_abreviations'],
        'paint_colors'     => ['col' => 'color',    'id' => 'color_id',            'abbr' => 'color_abbreviation'],
        'dimensions'       => ['col' => 'length',   'id' => 'dimension_id',        'abbr' => 'dimension_abbreviation']
    ];

    if (!isset($cfgs[$table])) return;

    $cfg = $cfgs[$table];
    $col = $cfg['col'];

    // Get the new abbreviation
    $res = mysqli_query($conn, "SELECT {$cfg['abbr']} FROM $table WHERE {$cfg['id']} = $id");
    $new = mysqli_fetch_assoc($res)[$cfg['abbr']] ?? '';
    if (!$new) return;

    // Get affected rows from product_abr
    $rows = mysqli_query($conn, "SELECT * FROM product_abr WHERE $col = $id");
    $inserted = 0;

    $srcs = [
        'category' => ['product_category','product_category_id','category_abreviations'],
        'profile'  => ['profile_type','profile_type_id','profile_abbreviations'],
        'grade'    => ['product_grade','product_grade_id','grade_abbreviations'],
        'gauge'    => ['product_gauge','product_gauge_id','gauge_abbreviations'],
        'type'     => ['product_type','product_type_id','type_abreviations'],
        'color'    => ['paint_colors','color_id','color_abbreviation'],
        'length'   => ['dimensions','dimension_id','dimension_abbreviation']
    ];

    while ($r = mysqli_fetch_assoc($rows)) {
        $abbrs = [];
        foreach ($srcs as $k => [$tbl, $idc, $abbrc]) {
            $val = $r[$k];
            if (!$val) { 
                $abbrs[$k] = ''; 
                continue;
            }

            // Use new abbreviation if the updated column matches
            if ($k === $col) {
                $abbrs[$k] = $new;
            } else {
                $s = mysqli_query($conn, "SELECT $abbrc FROM $tbl WHERE $idc = $val");
                $abbrs[$k] = ($sr = mysqli_fetch_assoc($s)) ? $sr[$abbrc] : '';
            }
        }

        $category_id = $r['category'];
        $pid = '';

        // === BUILD PRODUCT ID ===
        if ($abbrs['category'] !== '') $pid .= $abbrs['category'];

        if ($category_id == $panel_id) {
            // PANEL FORMAT: CAT - PROFILE GRADE GAUGE - COLOR LENGTH
            if ($abbrs['profile'] !== '' || $abbrs['grade'] !== '' || $abbrs['gauge'] !== '') {
                $pid .= '-' . $abbrs['profile'] . $abbrs['grade'] . $abbrs['gauge'];
            }
        } elseif ($category_id == $trim_id) {
            // TRIM FORMAT: CAT - TYPE + product_id_from_table - GRADE GAUGE - COLOR LENGTH
            if ($abbrs['type'] !== '') {
                $pid .= '-' . $abbrs['type'];
                if (!empty($r['product_id_from_table'])) {
                    $pid .= $r['product_id_from_table'];
                }
            }
            if ($abbrs['grade'] !== '' || $abbrs['gauge'] !== '') {
                $pid .= '-' . $abbrs['grade'] . $abbrs['gauge'];
            }
        } else {
            // DEFAULT FORMAT: CAT + PROFILE TYPE GRADE GAUGE - COLOR LENGTH
            if ($abbrs['profile'] !== '' || $abbrs['type'] !== '' || $abbrs['grade'] !== '' || $abbrs['gauge'] !== '') {
                $pid .= '-' . $abbrs['profile'] . $abbrs['type'] . $abbrs['grade'] . $abbrs['gauge'];
            }
        }

        if ($abbrs['color'] !== '') {
            $pid .= '-' . $abbrs['color'];
        }

        if ($abbrs['length'] !== '') {
            $pid .= $abbrs['length'];
        }

        if (!$pid) continue;

        $pid_esc = mysqli_real_escape_string($conn, $pid);
        $chk = mysqli_query($conn, "SELECT 1 FROM product_abr WHERE product_id = '$pid_esc' LIMIT 1");
        if (mysqli_num_rows($chk) > 0) continue;

        $sql = sprintf(
            "INSERT INTO product_abr (product_id, product_id_from_table, category, profile, grade, gauge, type, color, length)
             VALUES ('%s', %s, %s, %s, %s, %s, %s, %s, %s)",
            $pid_esc,
            $r['product_id_from_table'] ?: 'NULL',
            $r['category'] ?: 'NULL',
            $r['profile']  ?: 'NULL',
            $r['grade']    ?: 'NULL',
            $r['gauge']    ?: 'NULL',
            $r['type']     ?: 'NULL',
            $r['color']    ?: 'NULL',
            $r['length']   ?: 'NULL'
        );

        mysqli_query($conn, $sql);
        $inserted += mysqli_affected_rows($conn);
    }

    return $inserted;
}

function generateProductAbrString(
    $category_ids,
    $profile_ids,
    $grade_ids,
    $gauge_ids,
    $type_ids,
    $color_ids,
    $length_ids,
    $product_id = null
) {
    global $conn;

    $panel_id = 3;
    $trim_id  = 4;

    if (
        empty($category_ids) && empty($profile_ids) && empty($grade_ids) &&
        empty($gauge_ids) && empty($type_ids) && empty($color_ids) &&
        empty($length_ids) && empty($product_id)
    ) {
        return '';
    }

    $maps = [
        'category' => getAbbrMap('product_category', 'product_category_id', 'category_abreviations', $category_ids),
        'profile'  => getAbbrMap('profile_type', 'profile_type_id', 'profile_abbreviations', $profile_ids),
        'grade'    => getAbbrMap('product_grade', 'product_grade_id', 'grade_id_no', $grade_ids),
        'gauge'    => getAbbrMap('product_gauge', 'product_gauge_id', 'gauge_id_no', $gauge_ids),
        'type'     => getAbbrMap('product_type', 'product_type_id', 'type_abreviations', $type_ids),
        'color'    => getAbbrMap('paint_colors', 'color_id', 'ekm_color_no', $color_ids),
    ];

    $idGroups = [
        'category' => !empty($category_ids) ? $category_ids : [null],
        'profile'  => !empty($profile_ids) ? $profile_ids : [null],
        'grade'    => !empty($grade_ids) ? $grade_ids : [null],
        'gauge'    => !empty($gauge_ids) ? $gauge_ids : [null],
        'type'     => !empty($type_ids) ? $type_ids : [null],
        'color'    => !empty($color_ids) ? $color_ids : [null],
        'length'   => !empty($length_ids) ? $length_ids : [null],
    ];

    $combinations = [[]];
    foreach ($idGroups as $key => $ids) {
        $new = [];
        foreach ($combinations as $combo) {
            foreach ($ids as $id) {
                $combo[$key] = $id;
                $new[] = $combo;
            }
        }
        $combinations = $new;
    }

    $abrList = [];

    foreach ($combinations as $c) {
        $pid = '';

        $cat_id = $c['category'];
        $cat_abbr = ($cat_id && isset($maps['category'][$cat_id])) ? $maps['category'][$cat_id] : '';

        if ($cat_abbr !== '') {
            $pid .= $cat_abbr;
        }

        // PANEL
        if ($cat_id == $panel_id) {
            $profile_abbr = ($c['profile'] && isset($maps['profile'][$c['profile']])) ? $maps['profile'][$c['profile']] : '';
            $grade_abbr   = ($c['grade'] && isset($maps['grade'][$c['grade']])) ? $maps['grade'][$c['grade']] : '';
            $gauge_abbr   = ($c['gauge'] && isset($maps['gauge'][$c['gauge']])) ? $maps['gauge'][$c['gauge']] : '';

            if ($profile_abbr !== '' || $grade_abbr !== '' || $gauge_abbr !== '') {
                $pid .= '-' . $profile_abbr . $grade_abbr . $gauge_abbr;
            }

        // TRIM
        } elseif ($cat_id == $trim_id) {
            $type_abbr  = ($c['type'] && isset($maps['type'][$c['type']])) ? $maps['type'][$c['type']] : '';
            $grade_abbr = ($c['grade'] && isset($maps['grade'][$c['grade']])) ? $maps['grade'][$c['grade']] : '';
            $gauge_abbr = ($c['gauge'] && isset($maps['gauge'][$c['gauge']])) ? $maps['gauge'][$c['gauge']] : '';

            if ($type_abbr !== '') {
                $pid .= '-' . $type_abbr;
                if (!empty($product_id)) {
                    $pid .= $product_id;
                }
            } elseif (!empty($product_id)) {
                $pid .= '-' . $product_id;
            }

            if ($grade_abbr !== '' || $gauge_abbr !== '') {
                $pid .= '-' . $grade_abbr . $gauge_abbr;
            }

        // DEFAULT
        } else {
            $profile_abbr = ($c['profile'] && isset($maps['profile'][$c['profile']])) ? $maps['profile'][$c['profile']] : '';
            $type_abbr    = ($c['type'] && isset($maps['type'][$c['type']])) ? $maps['type'][$c['type']] : '';
            $grade_abbr   = ($c['grade'] && isset($maps['grade'][$c['grade']])) ? $maps['grade'][$c['grade']] : '';
            $gauge_abbr   = ($c['gauge'] && isset($maps['gauge'][$c['gauge']])) ? $maps['gauge'][$c['gauge']] : '';

            $middle = '';
            if (!empty($product_id)) {
                $middle .= $product_id;
            }
            $middle .= $grade_abbr . $gauge_abbr;

            if ($profile_abbr !== '' || $type_abbr !== '' || $middle !== '') {
                $pid .= '-' . $profile_abbr . $type_abbr . $middle;
            }
        }

        $color_abbr  = ($c['color'] && isset($maps['color'][$c['color']])) ? $maps['color'][$c['color']] : '';
        $length_abbr = ($c['length'] && isset($maps['length'][$c['length']])) ? $maps['length'][$c['length']] : '';

        if ($color_abbr !== '') {
            $pid .= '-' . $color_abbr;
        }
        if ($length_abbr !== '') {
            $pid .= $length_abbr;
        }

        if ($pid === '' && !empty($product_id)) {
            $pid = $product_id;
            if ($color_abbr !== '') $pid .= '-' . $color_abbr;
            if ($length_abbr !== '') $pid .= $length_abbr;
        }

        if ($pid !== '') $abrList[] = $pid;
    }

    $abrList = array_unique($abrList);
    return implode(',', $abrList);
}

function getAbbrMap($table, $id_col, $abbr_col, $ids = []) {
    global $conn;
    $map = [];
    if (empty($ids)) return $map;

    if (!is_array($ids)) {
        $ids = [$ids];
    }

    $ids = array_map('intval', $ids);
    $in  = implode(',', $ids);
    $res = mysqli_query($conn, "SELECT `$id_col`, `$abbr_col` FROM `$table` WHERE `$id_col` IN ($in)");
    while ($row = mysqli_fetch_assoc($res)) {
        $map[$row[$id_col]] = $row[$abbr_col] ?? '';
    }
    return $map;
}

function getAbbr($table, $abbr_col, $id) {
    static $cache = [];
    global $conn;

    if (!$id) return '';

    $cacheKey = "$table|$abbr_col|$id";
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    $pk = getPrimaryKey($table);
    if (!$pk) return '';

    $id = intval($id);

    $sql = "SELECT `$abbr_col` FROM `$table` WHERE `$pk` = $id LIMIT 1";
    $res = mysqli_query($conn, $sql);

    $value = '';
    if ($row = mysqli_fetch_assoc($res)) {
        $value = $row[$abbr_col] ?? '';
    }
    $cache[$cacheKey] = $value;
    return $value;
}

function getProdID(array $d) {
    global $conn;

    $panel_id = 3;
    $trim_id  = 4;
    $screw_id = 16;

    $v = fn($k) => $d[$k] ?? null;

    $category = $v('category');
    $profile  = $v('profile');
    $type     = $v('type');
    $line     = $v('line');
    $grade    = $v('grade');
    $gauge    = $v('gauge');
    $color    = $v('color');
    $panel_type  = $v('panel_type');
    $panel_style = $v('panel_style');
    $length      = $v('length');
    $product_id  = $v('product_id');
    $screw_type  = $v('screw_type');
    $screw_coating = $v('screw_coating');
    $screw_length  = $v('screw_length');
    $customer_id  = $_SESSION['customer_id'];
    $special_trim_no  = $v('trim_no');

    $categoryAbbr = getAbbr('product_category', 'category_abreviations', $category);
    $profileAbbr  = getAbbr('profile_type', 'profile_abbreviations', $profile);
    $typeAbbr     = getAbbr('product_type', 'type_abreviations', $type);
    $lineAbbr     = getAbbr('product_line', 'line_abreviations', $line);
    $gradeAbbr    = getAbbr('product_grade', 'grade_id_no', $grade);
    $gaugeAbbr    = getAbbr('product_gauge', 'gauge_id_no', $gauge);
    $colorAbbr    = getAbbr('paint_colors', 'ekm_color_no', $color);
    $screwTypeAbbr = getAbbr('product_screw_type', 'type_abreviations', $screw_type);
    $screwCoatingAbbr = getAbbr('product_screw_coating', 'abbreviation', $screw_coating);

    $prodDescAbbr = '';
    $is_special_trim = 0;
    if ($product_id) {
        $p = getProductDetails($product_id);
        $prodDescAbbr = $p['abbreviation'] ?? '';
        $is_special_trim = $p['is_special_trim'];
    }

    $panelTypeAbbr  = ($profile && $panel_type) ? getProfileTypeAbbrev($profile, $panel_type) : '';
    $panelStyleAbbr = ($profile && $panel_style) ? getProfileStyleAbbrev($profile, $panel_style) : '';
    $lengthAbbr     = $length ? formatLengthAbbr(floatval($length)) : '';

    $abbr = '';

    if ($category == $panel_id) {
        $abbr .= $categoryAbbr;
        $abbr .= $profileAbbr . $gradeAbbr . $gaugeAbbr;
        if ($colorAbbr !== '') $abbr .= "-$colorAbbr";
        $abbr .= $panelStyleAbbr;
        $abbr .= $lengthAbbr;
        $abbr .= $panelTypeAbbr;
    }

    else if ($category == $trim_id) {
        $abbr .= $categoryAbbr;
        $abbr .= $typeAbbr . $gradeAbbr . $gaugeAbbr;
        if ($colorAbbr !== '') $abbr .= "-$colorAbbr";
        $abbr .= $prodDescAbbr;
        $abbr .= $lengthAbbr;
        if($is_special_trim){
            if (!empty($customer_id)) $abbr .= "/$customer_id";
            if (!empty($special_trim_no)) $abbr .= "/$special_trim_no";
        } 
    }

    else if ($category == $screw_id) {
        $abbr .= $categoryAbbr;
        $abbr .= $typeAbbr;
        $abbr .= $screwCoatingAbbr;
        $abbr .= $screwTypeAbbr;
        if ($screw_length) $abbr .= "({$screw_length})";
        if ($colorAbbr !== '') $abbr .= "-$colorAbbr";
    }

    else {
        $abbr .= $categoryAbbr;
        $abbr .= $lineAbbr;
        $abbr .= $typeAbbr;
        $abbr .= $prodDescAbbr;
        $abbr .= $lengthAbbr;
        if ($colorAbbr !== '') $abbr .= "-$colorAbbr";
    }

    return $abbr;
}

function getTrimProdID(
    $category_id,
    $type_id,
    $product_id,
    $grade_ids,
    $gauge_ids,
    $color_ids,
    $length
) {
    global $conn;

    $category_ids = empty($category_id) ? [] : [$category_id];
    $type_ids     = empty($type_id)     ? [] : [$type_id];
    $grade_ids    = is_array($grade_ids) ? $grade_ids : (empty($grade_ids) ? [] : [$grade_ids]);
    $gauge_ids    = is_array($gauge_ids) ? $gauge_ids : (empty($gauge_ids) ? [] : [$gauge_ids]);
    $color_ids    = is_array($color_ids) ? $color_ids : (empty($color_ids) ? [] : [$color_ids]);

    $maps = [
        'category' => getAbbrMap('product_category', 'product_category_id', 'category_abreviations', $category_ids),
        'type'     => getAbbrMap('product_type', 'product_type_id', 'type_abreviations', $type_ids),
        'grade'    => getAbbrMap('product_grade', 'product_grade_id', 'grade_id_no', $grade_ids),
        'gauge'    => getAbbrMap('product_gauge', 'product_gauge_id', 'gauge_id_no', $gauge_ids),
        'color'    => getAbbrMap('paint_colors', 'color_id', 'ekm_color_no', $color_ids),
    ];

    $idGroups = [
        'category' => !empty($category_ids) ? $category_ids : [null],
        'type'     => !empty($type_ids)     ? $type_ids     : [null],
        'grade'    => !empty($grade_ids)    ? $grade_ids    : [null],
        'gauge'    => !empty($gauge_ids)    ? $gauge_ids    : [null],
        'color'    => !empty($color_ids)    ? $color_ids    : [null],
    ];

    $combinations = [[]];
    foreach ($idGroups as $key => $ids) {
        $new = [];
        foreach ($combinations as $combo) {
            foreach ($ids as $id) {
                $combo[$key] = $id;
                $new[] = $combo;
            }
        }
        $combinations = $new;
    }

    $lengthAbbr = '';
    if (!empty($length)) {
        $lengthAbbr = formatLengthAbbr(floatval($length));
    }

    $abrList = [];
    foreach ($combinations as $c) {
        $categoryAbbr = ($c['category'] && isset($maps['category'][$c['category']])) ? $maps['category'][$c['category']] : '';
        $typeAbbr     = ($c['type'] && isset($maps['type'][$c['type']])) ? $maps['type'][$c['type']] : '';
        $gradeAbbr    = ($c['grade'] && isset($maps['grade'][$c['grade']])) ? $maps['grade'][$c['grade']] : '';
        $gaugeAbbr    = ($c['gauge'] && isset($maps['gauge'][$c['gauge']])) ? $maps['gauge'][$c['gauge']] : '';
        $colorAbbr    = ($c['color'] && isset($maps['color'][$c['color']])) ? $maps['color'][$c['color']] : '';

        $abbr = '';

        if ($categoryAbbr !== '') {
            $abbr .= $categoryAbbr;
        }

        if ($typeAbbr !== '' || !empty($product_id)) {
            $abbr .= $typeAbbr . $product_id;
        }

        if ($gradeAbbr !== '' || $gaugeAbbr !== '') {
            $abbr .= '-' . $gradeAbbr . $gaugeAbbr;
        }

        if ($colorAbbr !== '') {
            $abbr .= '-' . $colorAbbr;
        }

        if ($lengthAbbr !== '') {
            $abbr .= $lengthAbbr;
        }

        if ($abbr !== '') {
            $abrList[] = $abbr;
        }
    }

    $abrList = array_unique($abrList);
    return !empty($abrList) ? reset($abrList) : '';
}

function getScrewProdID(
    $category_id,
    $type_id,
    $screw_type,
    $color_ids,
    $screw_length
) {
    global $conn;

    $category_ids = empty($category_id) ? [] : [$category_id];
    $type_ids     = empty($type_id)     ? [] : [$type_id];
    $color_ids    = is_array($color_ids) ? $color_ids : (empty($color_ids) ? [] : [$color_ids]);

    $maps = [
        'category' => getAbbrMap('product_category', 'product_category_id', 'category_abreviations', $category_ids),
        'type'     => getAbbrMap('product_type', 'product_type_id', 'type_abreviations', $type_ids),
        'color'    => getAbbrMap('paint_colors', 'color_id', 'ekm_color_no', $color_ids),
    ];

    $idGroups = [
        'category' => !empty($category_ids) ? $category_ids : [null],
        'type'     => !empty($type_ids)     ? $type_ids     : [null],
        'color'    => !empty($color_ids)    ? $color_ids    : [null],
    ];

    $combinations = [[]];
    foreach ($idGroups as $key => $ids) {
        $new = [];
        foreach ($combinations as $combo) {
            foreach ($ids as $id) {
                $combo[$key] = $id;
                $new[] = $combo;
            }
        }
        $combinations = $new;
    }

    $abrList = [];
    foreach ($combinations as $c) {
        $categoryAbbr = ($c['category'] && isset($maps['category'][$c['category']])) ? $maps['category'][$c['category']] : '';
        $typeAbbr     = ($c['type'] && isset($maps['type'][$c['type']])) ? $maps['type'][$c['type']] : '';
        $colorAbbr    = ($c['color'] && isset($maps['color'][$c['color']])) ? $maps['color'][$c['color']] : '';

        $abbrParts = [];

        if ($categoryAbbr !== '') {
            $abbrParts[] = $categoryAbbr;
        }

        $typeAndScrew = $typeAbbr . ($screw_type ?? '');
        if ($typeAndScrew !== '') {
            $abbrParts[] = $typeAndScrew;
        }

        if ($colorAbbr !== '') {
            $abbrParts[] = $colorAbbr;
        }

        $abbrStr = implode('-', $abbrParts);

        if (!empty($screw_length)) {
            $abbrStr .= "({$screw_length})";
        }

        if ($abbrStr !== '') {
            $abrList[] = $abbrStr;
        }
    }

    $abrList = array_unique($abrList);
    return !empty($abrList) ? reset($abrList) : '';
}

function getDefaultProdID(
    $category_ids,
    $product_id,
    $grade_ids,
    $gauge_ids,
    $color_ids,
    $length
) {
    global $conn;

    $category_ids = is_array($category_ids) ? $category_ids : (empty($category_ids) ? [] : [$category_ids]);
    $grade_ids    = is_array($grade_ids)    ? $grade_ids    : (empty($grade_ids)    ? [] : [$grade_ids]);
    $gauge_ids    = is_array($gauge_ids)    ? $gauge_ids    : (empty($gauge_ids)    ? [] : [$gauge_ids]);
    $color_ids    = is_array($color_ids)    ? $color_ids    : (empty($color_ids)    ? [] : [$color_ids]);

    $maps = [
        'category' => getAbbrMap('product_category', 'product_category_id', 'category_abreviations', $category_ids),
        'profile'  => getAbbrMap('profile_type', 'profile_type_id', 'profile_abbreviations', $profile_ids),
        'grade'    => getAbbrMap('product_grade', 'product_grade_id', 'grade_id_no', $grade_ids),
        'gauge'    => getAbbrMap('product_gauge', 'product_gauge_id', 'gauge_id_no', $gauge_ids),
        'type'     => getAbbrMap('product_type', 'product_type_id', 'type_abreviations', $type_ids),
        'color'    => getAbbrMap('paint_colors', 'color_id', 'ekm_color_no', $color_ids),
    ];

    $idGroups = [
        'category' => !empty($category_ids) ? $category_ids : [null],
        'grade'    => !empty($grade_ids) ? $grade_ids : [null],
        'gauge'    => !empty($gauge_ids) ? $gauge_ids : [null],
        'color'    => !empty($color_ids) ? $color_ids : [null],
    ];

    $combinations = [[]];
    foreach ($idGroups as $key => $ids) {
        $new = [];
        foreach ($combinations as $combo) {
            foreach ($ids as $id) {
                $combo[$key] = $id;
                $new[] = $combo;
            }
        }
        $combinations = $new;
    }

    $lengthAbbr = '';
    if (!empty($length)) {
        $lengthAbbr = formatLengthAbbr(floatval($length));
    }

    $abrList = [];
    foreach ($combinations as $c) {
        $categoryAbbr = ($c['category'] && isset($maps['category'][$c['category']])) ? $maps['category'][$c['category']] : '';
        $gradeAbbr    = ($c['grade']    && isset($maps['grade'][$c['grade']]))       ? $maps['grade'][$c['grade']]       : '';
        $gaugeAbbr    = ($c['gauge']    && isset($maps['gauge'][$c['gauge']]))       ? $maps['gauge'][$c['gauge']]       : '';
        $colorAbbr    = ($c['color']    && isset($maps['color'][$c['color']]))       ? $maps['color'][$c['color']]       : '';

        $abbr = '';
        if ($categoryAbbr !== '') $abbr .= $categoryAbbr;

        if (!empty($product_id) || $gradeAbbr !== '' || $gaugeAbbr !== '') {
            $abbr .= '-' . $product_id . $gradeAbbr . $gaugeAbbr;
        }

        if ($colorAbbr !== '') {
            $abbr .= '-' . $colorAbbr;
        }

        if ($lengthAbbr !== '') {
            $abbr .= $lengthAbbr;
        }

        if ($abbr !== '') $abrList[] = $abbr;
    }

    $abrList = array_unique($abrList);
    return !empty($abrList) ? reset($abrList) : '';
}

function formatLengthAbbr($decimalValue) {
    $inches = round($decimalValue * 12);
    return $inches ;
}

function fetchProductIDs($product_id_from_table) {
    global $conn;

    if (empty($product_id_from_table) || !is_numeric($product_id_from_table)) {
        return '';
    }

    $product_id_from_table = intval($product_id_from_table);

    $sql = "
        SELECT pa.product_id
        FROM product_abr pa
        WHERE pa.id IN (
            SELECT MAX(id)
            FROM product_abr
            WHERE product_id_from_table = $product_id_from_table
            GROUP BY category, profile, grade, gauge, type, color, length
        )
        ORDER BY pa.date_added DESC
    ";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return '';
    }

    $ids = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ids[] = $row['product_id'];
    }

    return implode(',', $ids);
}


function fetchSingleProductABR($category_id = null, $profile_id = null, $grade_id = null, $gauge_id = null, $type_id = null, $color_id = null, $length_id = null) {
    global $conn;

    $conditions = [];

    if (!empty($category_id)) $conditions[] = "category = " . intval($category_id);
    if (!empty($profile_id))  $conditions[] = "profile = "  . intval($profile_id);
    if (!empty($grade_id))    $conditions[] = "grade = "    . intval($grade_id);
    if (!empty($gauge_id))    $conditions[] = "gauge = "    . intval($gauge_id);
    if (!empty($type_id))     $conditions[] = "type = "     . intval($type_id);
    if (!empty($color_id))    $conditions[] = "color = "    . intval($color_id);
    if (!empty($length_id))   $conditions[] = "length = "   . intval($length_id);

    if (empty($conditions)) return '';

    $where = implode(' AND ', $conditions);

    $sql = "SELECT product_id FROM product_abr WHERE $where LIMIT 1";
    $res = mysqli_query($conn, $sql);

    if ($res && $row = mysqli_fetch_assoc($res)) {
        return $row['product_id'];
    }

    return '';
}

function recordCashInflow($payment_method, $cash_flow_type, $amount = 0, $orderid = 0) {
    global $conn;

    $received_by = isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
    $station_id  = isset($_SESSION['station']) ? intval($_SESSION['station']) : 0;

    $movement_type   = 'cash_inflow';
    $payment_method  = mysqli_real_escape_string($conn, $payment_method);
    $cash_flow_type  = mysqli_real_escape_string($conn, $cash_flow_type);
    $orderid  = intval($orderid);
    $amount          = ($amount === null || $amount === '') ? 0 : floatval($amount);
    $date            = date('Y-m-d H:i:s');

    $sql = "
        INSERT INTO cash_flow (orderid, movement_type, payment_method, date, received_by, station_id, cash_flow_type, amount)
        VALUES ('$orderid', '$movement_type', '$payment_method', '$date', '$received_by', '$station_id', '$cash_flow_type', '$amount')
    ";

    return mysqli_query($conn, $sql);
}

function recordCashOutflow($payment_method, $cash_flow_type, $amount = 0) {
    global $conn;

    $received_by = isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
    $station_id  = isset($_SESSION['station']) ? intval($_SESSION['station']) : 0;

    $movement_type   = 'cash_outflow';
    $payment_method  = mysqli_real_escape_string($conn, $payment_method);
    $cash_flow_type  = mysqli_real_escape_string($conn, $cash_flow_type);
    $amount          = ($amount === null || $amount === '') ? 0 : floatval($amount);
    $date            = date('Y-m-d H:i:s');

    $sql = "
        INSERT INTO cash_flow (movement_type, payment_method, date, received_by, station_id, cash_flow_type, amount)
        VALUES ('$movement_type', '$payment_method', '$date', '$received_by', '$station_id', '$cash_flow_type', '$amount')
    ";

    return mysqli_query($conn, $sql);
}

function getProfileTypeAbbrev($profile_id, $profile_type_char = '') {
    global $conn;

    $profile_id = intval($profile_id);
    $profile_type_char = trim($profile_type_char);

    if ($profile_id <= 0 || $profile_type_char === '') {
        return '';
    }

    $profile_id_esc = mysqli_real_escape_string($conn, (string)$profile_id);
    $sql = "SELECT panel_type_1, panel_type_abbrev_1,
                   panel_type_2, panel_type_abbrev_2,
                   panel_type_3, panel_type_abbrev_3
            FROM profile_type
            WHERE profile_type_id = '$profile_id_esc'
            LIMIT 1";

    $res = mysqli_query($conn, $sql);
    if (!$res) return '';

    $row = mysqli_fetch_assoc($res);
    if (!$row) return '';

    for ($i = 1; $i <= 3; $i++) {
        $typeCol = "panel_type_" . $i;
        $abbrCol = "panel_type_abbrev_" . $i;
        if (isset($row[$typeCol]) && strtoupper($row[$typeCol]) === strtoupper($profile_type_char)) {
            return $row[$abbrCol] ?? '';
        }
    }

    return '';
}

function getProfileStyleAbbrev($profile_id, $profile_style_char = '') {
    global $conn;

    $profile_id = intval($profile_id);
    $profile_style_char = trim($profile_style_char);

    if ($profile_id <= 0 || $profile_style_char === '') {
        return '';
    }

    $profile_id_esc = mysqli_real_escape_string($conn, (string)$profile_id);
    $sql = "SELECT panel_style_1, panel_style_abbrev_1,
                   panel_style_2, panel_style_abbrev_2,
                   panel_style_3, panel_style_abbrev_3
            FROM profile_type
            WHERE profile_type_id = '$profile_id_esc'
            LIMIT 1";

    $res = mysqli_query($conn, $sql);
    if (!$res) return '';

    $row = mysqli_fetch_assoc($res);
    if (!$row) return '';

    for ($i = 1; $i <= 3; $i++) {
        $styleCol = "panel_style_" . $i;
        $abbrCol = "panel_style_abbrev_" . $i;
        if (isset($row[$styleCol]) && strtoupper($row[$styleCol]) === strtoupper($profile_style_char)) {
            return $row[$abbrCol] ?? '';
        }
    }

    return '';
}

function processCoilTransaction($coil_id, $length_used, $work_orders, $is_waste = false) {
    global $conn;

    if (!is_array($work_orders)) {
        $work_orders = [$work_orders];
    }

    $coil_before = getCoilProductDetails($coil_id);
    if (!$coil_before) return false;

    $length_before_use = floatval($coil_before['remaining_feet']);
    $length_used = floatval($length_used);

    $update_product = mysqli_query($conn, "
        UPDATE coil_product 
        SET remaining_feet = GREATEST(remaining_feet - $length_used, 0)
        WHERE coil_id = $coil_id
    ");
    if (!$update_product) return false;

    $coil_after = getCoilProductDetails($coil_id);
    $remaining_length = floatval($coil_after['remaining_feet']);

    $work_order_ids_str = implode(',', array_unique($work_orders));

    $date_now = date('Y-m-d H:i:s');
    $insert_tx = mysqli_query($conn, "
        INSERT INTO coil_transaction 
        (coilid, date, remaining_length, length_before_use, used_in_workorders, is_waste)
        VALUES ($coil_id, '$date_now', $remaining_length, $length_before_use, '$work_order_ids_str', " . ($is_waste ? 1 : 0) . ")
    ");

    if (!$insert_tx) return false;

    return true;
}

function getAssignedProductColors($product_id) {
    global $conn;

    $product_id = intval($product_id);
    $colorIds = [];

    $query = "SELECT DISTINCT color_id 
              FROM product_color_assign 
              WHERE product_id = $product_id 
              AND status = 1";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $colorIds[] = intval($row['color_id']);
        }
    }

    return $colorIds;
}

function getAssignedProductGrades($product_id) {
    global $conn;

    $product_id = (int)$product_id;
    $grades = [];

    $query = "
        SELECT grade
        FROM product
        WHERE product_id = $product_id
          AND status = 1
        LIMIT 1
    ";

    $result = mysqli_query($conn, $query);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        $grade_str = $row['grade'] ?? '';
        $grade_str = str_replace(['[', ']', '"', "'"], '', $grade_str);

        if ($grade_str !== '') {
            $grades = array_map('intval', array_filter(explode(',', $grade_str)));
        }
    }

    return $grades;
}

function getAssignedProductGauges($product_id) {
    global $conn;

    $product_id = (int)$product_id;
    $gauges = [];

    $query = "
        SELECT gauge
        FROM product
        WHERE product_id = $product_id
          AND status = 1
        LIMIT 1
    ";

    $result = mysqli_query($conn, $query);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        $gauge_str = $row['gauge'] ?? '';
        $gauge_str = str_replace(['[', ']', '"', "'"], '', $gauge_str);

        if ($gauge_str !== '') {
            $gauges = array_map('intval', array_filter(explode(',', $gauge_str)));
        }
    }

    return $gauges;
}


function getProductAttributes($product_id) {
    global $conn;

    $product_id = mysqli_real_escape_string($conn, $product_id);
    $query = "SELECT category, profile, grade, gauge, type, color 
              FROM product_abr 
              WHERE product_id = '$product_id' 
              LIMIT 1";
              
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }

    return [
        'category' => null,
        'profile' => null,
        'grade' => null,
        'gauge' => null,
        'type' => null,
        'color' => null
    ];
}

function getBulkData($product_id) {
    global $conn;

    $product_id = mysqli_real_escape_string($conn, $product_id);

    $query = "
        SELECT 
            bulk_price, 
            bulk_starts_at 
        FROM product 
        WHERE product_id = '$product_id'
        LIMIT 1
    ";

    $result = mysqli_query($conn, $query);

    if (!$result || mysqli_num_rows($result) === 0) {
        return [
            'bulk_price' => 0,
            'bulk_starts_at' => 0
        ];
    }

    $row = mysqli_fetch_assoc($result);

    return [
        'bulk_price' => floatval($row['bulk_price'] ?? 0),
        'bulk_starts_at' => floatval($row['bulk_starts_at'] ?? 0)
    ];
}

function array_combinations($arrays) {
    $result = [[]];
    foreach ($arrays as $key => $values) {
        $tmp = [];
        foreach ($result as $res) {
            foreach ((array)$values as $val) {
                $tmp[] = array_merge($res, [$key => $val]);
            }
        }
        $result = $tmp;
    }
    return $result;
}

function getFlatSheetWidthDetails($id) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT * FROM flat_sheet_width WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $flat_sheet_width = [];
    if ($row = mysqli_fetch_assoc($result)) {
        $flat_sheet_width = $row;
    }
    return $flat_sheet_width;
}

function sanitizeSheetTitle($title) {
    $invalidChars = ['\\', '/', '?', '*', '[', ']'];
    $title = str_replace($invalidChars, '_', $title);
    return mb_substr($title, 0, 31);
}

function normalizeIds($value) {
    if ($value === null || $value === '') return [];
    if (is_numeric($value)) return [(int)$value];

    $value = trim($value);

    if ($value[0] === '[') {
        $arr = json_decode($value, true);
        return is_array($arr) ? array_map('intval', $arr) : [];
    }

    return [(int)$value];
}

function cartesian(array $input) {
    $result = [[]];
    foreach ($input as $key => $values) {
        $append = [];
        foreach ($result as $product) {
            foreach ($values as $value) {
                $product[$key] = $value;
                $append[] = $product;
            }
        }
        $result = $append;
    }
    return $result;
}

function generateProductAbbr(int $product_id) {
    global $conn;

    $res = $conn->query("SELECT * FROM product WHERE product_id = $product_id LIMIT 1");
    if (!$res || $res->num_rows === 0) return false;

    $row = $res->fetch_assoc();
    $category_id = (int)($row['product_category'] ?? 0);

    $relatedSets = [
        'profile' => normalizeIds($row['profile'] ?? null),
        'type'    => normalizeIds($row['product_type'] ?? null),
        'line'    => normalizeIds($row['product_line'] ?? null),
        'grade'   => normalizeIds($row['grade'] ?? null),
        'gauge'   => normalizeIds($row['gauge'] ?? null),
        'color'   => normalizeIds($row['color_paint'] ?? null),
        'length'  => normalizeIds($row['available_lengths'] ?? null)
    ];

    foreach ($relatedSets as $k => $v) if (empty($v)) unset($relatedSets[$k]);

    if (empty($relatedSets)) return 0;

    $combinations = cartesian($relatedSets);

    $existing = [];
    $checkRes = $conn->query("SELECT product_id FROM product_abr");
    if ($checkRes) {
        while ($r = $checkRes->fetch_assoc()) $existing[$r['product_id']] = true;
    }

    $inserted = 0;
    $batch = [];

    foreach ($combinations as $c) {
        $abbr = getProdID([
            'category' => $category_id,
            'profile'  => $c['profile'] ?? null,
            'type'     => $c['type'] ?? null,
            'line'     => $c['line'] ?? null,
            'grade'    => $c['grade'] ?? null,
            'gauge'    => $c['gauge'] ?? null,
            'color'    => $c['color'] ?? null,
            'length'   => $c['length'] ?? null
        ]);

        if (!$abbr || isset($existing[$abbr])) continue;

        $existing[$abbr] = true;
        $abbr_escaped = $conn->real_escape_string($abbr);

        $batch[] = "('$abbr_escaped', $category_id, "
                 . ($c['profile'] ?? 'NULL') . ", "
                 . ($c['grade'] ?? 'NULL') . ", "
                 . ($c['gauge'] ?? 'NULL') . ", "
                 . ($c['type'] ?? 'NULL') . ", "
                 . ($c['color'] ?? 'NULL') . ", "
                 . ($c['length'] ?? 'NULL') . ")";
    }

    foreach (array_chunk($batch, 500) as $chunk) {
        $sql = "INSERT IGNORE INTO product_abr
            (product_id, category, profile, grade, gauge, type, color, length)
            VALUES " . implode(',', $chunk);
        $conn->query($sql);
        $inserted += $conn->affected_rows;
    }

    return $inserted;
}

function updateWorkOrderStatus($orderid) {
    global $conn;

    $orderid = intval($orderid);
    if (!$orderid) return false;

    $sql = "SELECT COUNT(*) AS pending_count 
            FROM order_product 
            WHERE orderid = $orderid 
              AND status < 2";

    $result = mysqli_query($conn, $sql);
    if (!$result) return false;

    $row = mysqli_fetch_assoc($result);

    if (intval($row['pending_count']) === 0) {
        $updateSql = "UPDATE orders SET wo_status = 2 WHERE orderid = $orderid";
        if (!mysqli_query($conn, $updateSql)) return false;
        return true;
    }

    return false;
}

function fetchInventoryQuantity($params = []) {
    global $conn;

    $product_id   = intval($params['product_id'] ?? 0);
    $product_line = isset($params['product_line']) ? intval($params['product_line']) : null;
    $product_type = isset($params['product_type']) ? intval($params['product_type']) : null;
    $color_id     = isset($params['color_id']) ? intval($params['color_id']) : null;
    $grade        = isset($params['grade']) ? intval($params['grade']) : null;
    $gauge        = isset($params['gauge']) ? intval($params['gauge']) : null;

    if (!$product_id) return 0;

    $where = ["product_id = $product_id"];

    if ($product_line !== null) $where[] = "product_line = $product_line";
    if ($product_type !== null) $where[] = "product_type = $product_type";
    if ($color_id !== null) $where[] = "color_id = $color_id";
    if ($grade !== null) $where[] = "grade = $grade";
    if ($gauge !== null) $where[] = "gauge = $gauge";

    $where_sql = implode(' AND ', $where);

    $sql = "SELECT COALESCE(SUM(quantity_ttl),0) AS total_quantity
            FROM inventory
            WHERE $where_sql";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    return intval($row['total_quantity']);
}

function getInventoryId(
    int $product_id,
    $product_type = null,
    $product_line = null,
    $grade = null,
    $gauge = null,
    $color_id = null
) {
    global $conn;

    $conditions = ["Product_id = " . intval($product_id)];

    if (!empty($product_type)) {
        $conditions[] = "product_type = " . intval($product_type);
    }

    if (!empty($product_line)) {
        $conditions[] = "product_line = " . intval($product_line);
    }

    if (!empty($grade)) {
        $conditions[] = "grade = " . intval($grade);
    }

    if (!empty($gauge)) {
        $conditions[] = "gauge = " . intval($gauge);
    }

    if (empty($color_id) || $color_id == 0) {
        $conditions[] = "color_id IS NULL";
    } else {
        $conditions[] = "color_id = " . intval($color_id);
    }

    $where = implode(" AND ", $conditions);

    $sql = "SELECT Inventory_id FROM inventory WHERE $where LIMIT 1";
    $res = mysqli_query($conn, $sql);

    if (!$res || mysqli_num_rows($res) === 0) return null;

    $row = mysqli_fetch_assoc($res);
    return (int)$row['Inventory_id'];
}

function getInventoryCount(int $order_product_id) {
    global $conn;

    $order_product_id = intval($order_product_id);

    $sql = "
        SELECT productid, custom_grade AS grade, custom_gauge AS gauge, custom_color AS color_id,
               product_category
        FROM order_product
        WHERE id = $order_product_id
        LIMIT 1
    ";
    $res = mysqli_query($conn, $sql);
    if (!$res || mysqli_num_rows($res) === 0) return null;

    $row = mysqli_fetch_assoc($res);

    $productid        = intval($row['productid']);
    $grade            = intval($row['grade']);
    $gauge            = intval($row['gauge']);
    $color_id         = $row['color_id'] ?? null;
    $product_category = intval($row['product_category']);

    if (in_array($product_category, [3, 4])) {
        $colorList = $color_id ? intval($color_id) : 'NULL';
        $gradeList = $grade;
        $gaugeList = $gauge;

        $sql_coil = "
            SELECT * 
            FROM coil_product
            WHERE color_sold_as IN ($colorList)
              AND grade_sold_as IN ($gradeList)
              AND gauge_sold_as IN ($gaugeList)
              AND status = 0
              AND hidden = 0
              AND remaining_feet > 0
            ORDER BY entry_no ASC
            LIMIT 1
        ";
        $coil_res = mysqli_query($conn, $sql_coil);
        if ($coil_row = mysqli_fetch_assoc($coil_res)) {
            return $coil_row;
        }
    } else {
        $inventory_id = getInventoryId($productid, null, null, $grade, $gauge, $color_id);
        if ($inventory_id) {
            $inv_res = mysqli_query($conn, "SELECT * FROM inventory WHERE Inventory_id = $inventory_id LIMIT 1");
            if ($inv_row = mysqli_fetch_assoc($inv_res)) {
                return $inv_row;
            }
        }
    }

    return null;
}

function getInvoiceNumName($orderid) {
    global $conn;
    $order = getOrderDetails($orderid);
    if (!$order) return null;
    $customer_id = $order['customerid'] ?? null;
    if (!$customer_id) return null;
    $customer = getCustomerDetails($customer_id);
    if (!$customer) return null;
    $customer_pricing = $customer['customer_pricing'] ?? '';
    $year_suffix = date('y');
    $invoice_number = "INV{$year_suffix}-{$customer_pricing}-{$orderid}";

    return $invoice_number;
}

function getEstimateNumName($estimateid) {
    global $conn;
    $estimate = getEstimateDetails($estimateid);
    if (!$estimate) return null;
    $customer_id = $estimate['customerid'] ?? null;
    if (!$customer_id) return null;
    $customer = getCustomerDetails($customer_id);
    if (!$customer) return null;
    $customer_pricing = $customer['customer_pricing'] ?? '';
    $year_suffix = date('y');
    $invoice_number = "EST{$year_suffix}-{$customer_pricing}-{$estimateid}";

    return $invoice_number;
}

function recalcOrderTotals($orderid){
    global $conn;
    $resp = ['success'=>true];
    $query = "SELECT * FROM order_product WHERE orderid='$orderid'";
    $result = mysqli_query($conn,$query);
    if(!$result){$resp=['success'=>false,'error'=>mysqli_error($conn)];return $resp;}
    $total_discounted = 0;
    $line_totals = [];
    while($row=mysqli_fetch_assoc($result)){
        $qty = floatval($row['quantity']);
        $unit_price = $qty>0 ? floatval($row['discounted_price'])/$qty : 0;
        $line_totals[$row['id']] = $unit_price * $qty;
        $total_discounted += $unit_price * $qty;
    }
    foreach($line_totals as $line_id => $line_total){
        $upd_sql = "UPDATE order_product SET discounted_price='$line_total' WHERE id='$line_id'";
        mysqli_query($conn,$upd_sql);
    }
    $upd_order_sql = "UPDATE orders SET discounted_price='$total_discounted' WHERE orderid='$orderid'";
    if(!mysqli_query($conn,$upd_order_sql)){$resp=['success'=>false,'error'=>mysqli_error($conn)];}
    $query_payments = "SELECT cash_amt, credit_amt, pay_pickup, pay_delivery, pay_net30 FROM orders WHERE orderid='$orderid' LIMIT 1";
    $res = mysqli_query($conn,$query_payments);
    if($res && mysqli_num_rows($res)){
        $row=mysqli_fetch_assoc($res);
        $total_payment = floatval($row['cash_amt'])+floatval($row['credit_amt'])+floatval($row['pay_pickup'])+floatval($row['pay_delivery'])+floatval($row['pay_net30']);
        $factor = $total_discounted>0 ? $total_discounted/$total_payment : 1;
        $ledger_types = ['cash_amt'=>'cash','credit_amt'=>'credit','pay_pickup'=>'pickup','pay_delivery'=>'delivery','pay_net30'=>'net30'];
        foreach($ledger_types as $col=>$method){
            $old_val = floatval($row[$col]);
            $new_val = $old_val * $factor;
            $upd_sql = "UPDATE job_ledger SET amount='$new_val' WHERE reference_no='$orderid' AND payment_method='$method'";
            mysqli_query($conn,$upd_sql);
        }
    }
    return $resp;
}
?>