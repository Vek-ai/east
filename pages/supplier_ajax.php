<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = 'supplier';
$test_table = 'supplier_excel';
$main_primary_key = getPrimaryKey($table);

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $supplier_id       = mysqli_real_escape_string($conn, $_POST['supplier_id'] ?? '');
        $supplier_name     = mysqli_real_escape_string($conn, $_POST['supplier_name'] ?? '');
        $supplier_website  = mysqli_real_escape_string($conn, $_POST['supplier_website'] ?? '');
        $supplier_type     = mysqli_real_escape_string($conn, $_POST['supplier_type'] ?? '');
        $supplier_colors   = isset($_POST['supplier_color']) ? $_POST['supplier_color'] : [];
        $supplier_code     = mysqli_real_escape_string($conn, $_POST['supplier_code'] ?? '');
        $supplier_paint_id = mysqli_real_escape_string($conn, $_POST['supplier_paint_id'] ?? '');
        $contact_name      = mysqli_real_escape_string($conn, $_POST['contact_name'] ?? '');
        $contact_email     = mysqli_real_escape_string($conn, $_POST['contact_email'] ?? '');
        $contact_phone     = mysqli_real_escape_string($conn, $_POST['contact_phone'] ?? '');
        $contact_fax       = mysqli_real_escape_string($conn, $_POST['contact_fax'] ?? '');
        $secondary_name    = mysqli_real_escape_string($conn, $_POST['secondary_name'] ?? '');
        $secondary_phone   = mysqli_real_escape_string($conn, $_POST['secondary_phone'] ?? '');
        $secondary_email   = mysqli_real_escape_string($conn, $_POST['secondary_email'] ?? '');
        $address           = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
        $city              = mysqli_real_escape_string($conn, $_POST['city'] ?? '');
        $state             = mysqli_real_escape_string($conn, $_POST['state'] ?? '');
        $zip               = mysqli_real_escape_string($conn, $_POST['zip'] ?? '');
        $lat               = mysqli_real_escape_string($conn, $_POST['lat'] ?? '');
        $lng               = mysqli_real_escape_string($conn, $_POST['lng'] ?? '');
        $last_ordered_date = mysqli_real_escape_string($conn, $_POST['last_ordered_date'] ?? '');
        $freight_rate      = mysqli_real_escape_string($conn, $_POST['freight_rate'] ?? '');
        $payment_terms     = mysqli_real_escape_string($conn, $_POST['payment_terms'] ?? '');
        $comment           = mysqli_real_escape_string($conn, $_POST['comment'] ?? '');
        $userid            = mysqli_real_escape_string($conn, $_POST['userid'] ?? '');

        $different_ship = isset($_POST['different_ship_address']) ? 1 : 0;
        if ($different_ship == 0) {
            $ship_address = $address;
            $ship_city    = $city;
            $ship_state   = $state;
            $ship_zip     = $zip;
            $ship_lat     = $lat;
            $ship_lng     = $lng;
        } else {
            $ship_address = mysqli_real_escape_string($conn, $_POST['ship_address']);
            $ship_city    = mysqli_real_escape_string($conn, $_POST['ship_city']);
            $ship_state   = mysqli_real_escape_string($conn, $_POST['ship_state']);
            $ship_zip     = mysqli_real_escape_string($conn, $_POST['ship_zip']);
            $ship_lat     = mysqli_real_escape_string($conn, $_POST['ship_lat']);
            $ship_lng     = mysqli_real_escape_string($conn, $_POST['ship_lng']);
        }

        function isDuplicateSupplier($supplier_name, $supplier_id = null) {
            global $conn;
            $sql = "SELECT * FROM supplier WHERE supplier_name LIKE '%$supplier_name%'";
            if ($supplier_id) $sql .= " AND supplier_id != '$supplier_id'";
            $result = mysqli_query($conn, $sql);
            return mysqli_num_rows($result) > 0;
        }

        $isInsert = false;
        $checkQuery = "SELECT * FROM supplier WHERE supplier_id='$supplier_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $isInsert = false;

            if (isDuplicateSupplier($supplier_name, $supplier_id)) {
                echo "Supplier Name already exists! Please use a unique value";
                exit;
            }

            $query = "UPDATE supplier SET 
                supplier_name='$supplier_name', 
                supplier_website='$supplier_website', 
                supplier_type='$supplier_type', 
                contact_name='$contact_name', 
                contact_email='$contact_email', 
                contact_phone='$contact_phone', 
                contact_fax='$contact_fax', 
                secondary_name='$secondary_name', 
                secondary_phone='$secondary_phone', 
                secondary_email='$secondary_email', 
                address='$address', city='$city', state='$state', zip='$zip', lat='$lat', lng='$lng',
                different_ship_address='$different_ship', 
                ship_address='$ship_address', ship_city='$ship_city', ship_state='$ship_state', ship_zip='$ship_zip', ship_lat='$ship_lat', ship_lng='$ship_lng',
                last_ordered_date='$last_ordered_date', 
                freight_rate='$freight_rate', 
                payment_terms='$payment_terms', 
                comment='$comment', 
                last_edit=NOW(), 
                edited_by='$userid', 
                supplier_code='$supplier_code', 
                supplier_paint_id='$supplier_paint_id' 
                WHERE supplier_id='$supplier_id'";

            $resultMsg = mysqli_query($conn, $query) ? "success_update" : "Error updating supplier: " . mysqli_error($conn);

        } else {
            $isInsert = true;

            if (isDuplicateSupplier($supplier_name)) {
                echo "Supplier Name already exists! Please use a unique value";
                exit;
            }

            $query = "INSERT INTO supplier (
                supplier_name, supplier_website, supplier_type, contact_name, contact_email, contact_phone, contact_fax, 
                secondary_name, secondary_phone, secondary_email, address, city, state, zip, lat, lng, 
                different_ship_address, ship_address, ship_city, ship_state, ship_zip, ship_lat, ship_lng, 
                last_ordered_date, freight_rate, payment_terms, comment, added_date, added_by, supplier_code, supplier_paint_id
            ) VALUES (
                '$supplier_name', '$supplier_website', '$supplier_type', '$contact_name', '$contact_email', '$contact_phone', '$contact_fax',
                '$secondary_name', '$secondary_phone', '$secondary_email', '$address', '$city', '$state', '$zip', '$lat', '$lng',
                '$different_ship', '$ship_address', '$ship_city', '$ship_state', '$ship_zip', '$ship_lat', '$ship_lng',
                '$last_ordered_date', '$freight_rate', '$payment_terms', '$comment', NOW(), '$userid', '$supplier_code', '$supplier_paint_id'
            )";

            $resultMsg = mysqli_query($conn, $query) ? "success_add" : "Error adding supplier: " . mysqli_error($conn);
            if ($resultMsg === "success_add") $supplier_id = $conn->insert_id;
        }

        echo $resultMsg;

        if (!empty($supplier_colors)) {
            mysqli_query($conn, "DELETE FROM supplier_color WHERE supplierid='$supplier_id'");
            foreach ($supplier_colors as $color) {
                list($color_name, $color_code) = explode('|', $color);
                mysqli_query($conn, "INSERT INTO supplier_color (supplierid, color, color_code, added_date, added_by) 
                    VALUES ('$supplier_id', '$color_name', '$color_code', NOW(), '$userid')");
            }
        }

        if (isset($_FILES['logo_path']) && $_FILES['logo_path']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['logo_path']['tmp_name'];
            $fileName    = $_FILES['logo_path']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $uploadFileDir = '../images/supplier/';
            $newFileName = $fileName . '.' . $fileExtension;
            $dest_path = $uploadFileDir . $newFileName;

            $allowedfileExtensions = ['jpg', 'gif', 'png', 'jpeg'];
            if (in_array($fileExtension, $allowedfileExtensions) && move_uploaded_file($fileTmpPath, $dest_path)) {
                $logo_path = mysqli_real_escape_string($conn, $dest_path);
                mysqli_query($conn, "UPDATE supplier SET logo_path='$logo_path' WHERE supplier_id='$supplier_id'");
            } elseif (!in_array($fileExtension, $allowedfileExtensions)) {
                echo 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
            }
        } elseif ($isInsert) {
            mysqli_query($conn, "UPDATE supplier SET logo_path='images/supplier/logo.jpg' WHERE supplier_id='$supplier_id'");
        }
    }

    if ($action == "change_status") {
        $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE supplier SET status = '$new_status' WHERE supplier_id = '$supplier_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    
    if ($action == "fetch_modal_content") {
        $supplier_id = mysqli_real_escape_string($conn, $_POST['id']);

        $checkQuery = "SELECT * FROM supplier WHERE supplier_id = '$supplier_id'";
        $result = mysqli_query($conn, $checkQuery);

        $supplier_colors = array();

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
  
            $check_supplier_colors = "SELECT * FROM supplier_color WHERE supplierid = '$supplier_id'";
            $result_supplier_colors = mysqli_query($conn, $check_supplier_colors);

            if (mysqli_num_rows($result_supplier_colors) > 0) {
                while($row_supplier_colors = mysqli_fetch_assoc($result_supplier_colors)){
                    $supplier_colors[] = $row_supplier_colors['color'];
                }
            }

            $address = $row['address'] ?? '';
            $city = $row['city'] ?? '';
            $state = $row['state'] ?? '';
            $zip = $row['zip'] ?? '';
            $lat = !empty($row['lat']) ? $row['lat'] : 0;
            $lng = !empty($row['lng']) ? $row['lng'] : 0;

            $different_ship_address = $row['different_ship_address'] ?? 0;
            $ship_address = $row['ship_address'] ?? '';
            $ship_city = $row['ship_city'] ?? '';
            $ship_state = $row['ship_state'] ?? '';
            $ship_zip = $row['ship_zip'] ?? '';
            $ship_lat = !empty($row['ship_lat']) ? $row['ship_lat'] : 0;
            $ship_lng = !empty($row['ship_lng']) ? $row['ship_lng'] : 0;

            $addressDetails = implode(', ', [
                $address ?? '',
                $city ?? '',
                $state ?? '',
                $zip ?? ''
            ]);

            $shipAddressDetails = implode(', ', [
                $ship_address ?? '',
                $ship_City ?? '',
                $ship_state ?? '',
                $ship_zip ?? ''
            ]);
        }
            ?>

            <div class="card shadow-sm rounded-3 mb-3">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 fw-bold">Contact Information</h5>
                </div>
                <div class="card-body border rounded p-3">
                    <div class="row">
                        <div class="col-12 card-body p-0">
                            <h4 class="card-title text-center">Supplier Logo</h4>
                            <div class="text-center">
                                <?php 
                                if(!empty($row['logo_path'])){
                                    $logo_path = $row['logo_path'];
                                }else{
                                    $logo_path = "images/supplier/logo.jpg";
                                }
                                ?>
                                <img src="<?= $logo_path ?>" id="logo_img" alt="logo-picture" class="img-fluid rounded-circle" width="120" height="120">
                                <div class="d-flex align-items-center justify-content-center my-4 gap-6">
                                <button id="upload_logo" type="button" class="btn btn-primary">Upload</button>
                                <button id="reset_logo" type="button" class="btn bg-danger-subtle text-danger">Reset</button>
                                </div>
                                <input type="file" id="logo_path" name="logo_path" class="form-control" style="display: none;"/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                            <label class="form-label">Supplier Name</label>
                            <input type="text" id="supplier_name" name="supplier_name" class="form-control"  value="<?= $row['supplier_name'] ?? "" ?>"/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                            <label class="form-label">Supplier Website</label>
                            <input type="text" id="supplier_website" name="supplier_website" class="form-control" value="<?= $row['supplier_website'] ?? "" ?>" />
                            </div>
                        </div>
                        <div class="col-md-4"></div>

                        <div class="col-md-4">
                            <div class="mb-3">
                            <label class="form-label">Primary Contact Name</label>
                            <input type="text" id="contact_name" name="contact_name" class="form-control"  value="<?= $row['contact_name'] ?? "" ?>"/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                            <label class="form-label">Primary Contact Phone</label>
                            <input type="text" id="contact_phone" name="contact_phone" class="form-control phone-inputmask" value="<?= $row['contact_phone'] ?? "" ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                            <label class="form-label">Primary Contact Email</label>
                            <input type="text" id="contact_email" name="contact_email" class="form-control" value="<?= $row['contact_email'] ?? "" ?>" />
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                            <label class="form-label">Primary Contact Fax</label>
                            <input type="text" id="contact_fax" name="contact_fax" class="form-control phone-inputmask" value="<?= $row['contact_fax'] ?? "" ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                            <label class="form-label">Secondary Phone</label>
                            <input type="text" id="secondary_phone" name="secondary_phone" class="form-control phone-inputmask" value="<?= $row['secondary_phone'] ?? "" ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                            <label class="form-label">Secondary Email</label>
                            <input type="text" id="secondary_email" name="secondary_email" class="form-control" value="<?= $row['secondary_email'] ?? "" ?>" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded-3 mb-3">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 fw-bold">Address Information</h5>
                </div>
                <div class="card-body border rounded p-3">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label">Billing Address</label>
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                
                                <div class="d-flex w-100">
                                    <input type="text" id="address" name="address" class="form-control" value="<?= $address ?? '' ?>" list="address-data-list"/>
                                    <datalist id="address-data-list"></datalist>
                                    <button type="button" class="btn btn-primary py-1 ms-2 toggleElements" id="showMapsBtn" data-address="<?=$addressDetails ?? ''?>" style="border-radius: 10%;" data-bs-toggle="modal" data-bs-target="#map1Modal">Change</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                            <label class="form-label">City</label>
                            <input type="text" id="city" name="city" class="form-control" value="<?= $city ?? '' ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                            <label class="form-label">State</label>
                            <input type="text" id="state" name="state" class="form-control" value="<?= $state ?? '' ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                            <label class="form-label">Zip</label>
                            <input type="text" id="zip" name="zip" class="form-control" value="<?= $zip ?? '' ?>" />
                            </div>
                        </div>
                        <input type="hidden" id="lat" name="lat" class="form-control" value="<?= $lat ?? '' ?>" />
                        <input type="hidden" id="lng" name="lng" class="form-control" value="<?= $lng ?? '' ?>" />
                        
                        
                        <div class="col-md-12">
                            <label class="form-label">Shipping Address different than Billing Address?</label>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="different_ship_address" id="different_ship_address" value="1" <?= ($different_ship_address ?? '' == '1' ? 'checked' : '') ?>><br>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Shipping Address</label>
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                
                                <div class="d-flex w-100">
                                    <input type="text" id="ship_address" name="ship_address" class="form-control" value="<?= $ship_address ?? '' ?>" list="address-data-list"/>
                                    <datalist id="address-data-list"></datalist>
                                    <button type="button" class="btn btn-primary py-1 ms-2 toggleElements" id="showMapsShipBtn" style="border-radius: 10%;" data-bs-toggle="modal" data-bs-target="#map2Modal">Change</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                            <label class="form-label">City</label>
                            <input type="text" id="ship_city" name="ship_city" class="form-control" value="<?= $ship_city ?? '' ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                            <label class="form-label">State</label>
                            <input type="text" id="ship_state" name="ship_state" class="form-control" value="<?= $ship_state ?? '' ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                            <label class="form-label">Zip</label>
                            <input type="text" id="ship_zip" name="ship_zip" class="form-control" value="<?= $ship_zip ?? '' ?>" />
                            </div>
                        </div>
                        <input type="hidden" id="ship_lat" name="ship_lat" class="form-control" value="<?= $ship_lat ?? '' ?>" />
                        <input type="hidden" id="ship_lng" name="ship_lng" class="form-control" value="<?= $ship_lng ?? '' ?>" />
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded-3 mb-3">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 fw-bold">Supplier Product Tracking</h5>
                </div>
                <div class="card-body border rounded p-3">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Supplier ID #</label>
                                <h4><?= $row['supplier_id'] ?? "" ?></h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="col-md-12 d-flex justify-content-between align-items-center">
                                    <label class="form-label">Supplier Type</label>
                                    <a href="?page=supplier_type&supplier_id=<?=$supplier_id?>" target="_blank" class="text-decoration-none toggleElements">Edit Types</a>
                                </div>
                                <select id="supplier_type" class="form-control" name="supplier_type">
                                    <option value="">Select One...</option>
                                    <?php
                                    $query_roles = "SELECT * FROM supplier_type WHERE hidden = '0' ORDER BY `supplier_type` ASC";
                                    $result_roles = mysqli_query($conn, $query_roles);            
                                    while ($row_supplier = mysqli_fetch_array($result_roles)) {
                                        $selected = ($row_supplier['supplier_type_id'] == $row['supplier_type']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_supplier['supplier_type_id'] ?>" <?= $selected ?>><?= $row_supplier['supplier_type'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4"></div>

                        <div class="col-md-4">
                            <div class="mb-3">
                            <label class="form-label">Supplier Paint ID</label>
                            <input type="text" id="supplier_paint_id" name="supplier_paint_id" class="form-control" value="<?= $row['supplier_paint_id'] ?? "" ?>" />
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <div class="col-md-12 d-flex justify-content-between align-items-center">
                                    <label class="form-label">Supplier Color</label>
                                    <a href="?page=supplier_color&supplier_id=<?=$supplier_id?>" target="_blank" class="text-decoration-none toggleElements">Edit Colors</a>
                                </div>
                                <div id="color_upd">
                                    <select id="supplier_color_update" class="form-control supplier_color select2" name="supplier_color[]" multiple>
                                        <?php
                                        $query_color = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                        $result_color = mysqli_query($conn, $query_color);            
                                        while ($row_color = mysqli_fetch_array($result_color)) {
                                            $selected = (in_array($row_color['color_name'], $supplier_colors)) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $row_color['color_name'] . '|' . $row_color['color_code'] ?>" <?= $selected ?> data-color="<?= $row_color['color_code'] ?>"><?= $row_color['color_name'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="col-md-12 d-flex justify-content-between align-items-center">
                                    <label class="form-label">Supplier Cases</label>
                                    <a href="?page=supplier_case&supplier_id=<?=$supplier_id?>" target="_blank" class="text-decoration-none toggleElements">Edit Cases</a>
                                </div>
                                <div id="case_upd" class="bg-light p-2 rounded">
                                    
                                    <?php
                                    $query_case = "SELECT * FROM supplier_case WHERE supplierid = '$supplier_id' AND hidden = '0'";
                                    $result_case = mysqli_query($conn, $query_case);            
                                    if (mysqli_num_rows($result_case) > 0) {
                                        while ($row_case = mysqli_fetch_array($result_case)) {
                                    ?>
                                        <span class="badge bg-primary me-1"><?= $row_case['case'] ?>(<?=$row_case['case_count']?>)</span>
                                    <?php   
                                        }
                                    } else {
                                    ?>
                                        <span>No cases found</span>
                                    <?php
                                    }
                                    ?>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="col-md-12 d-flex justify-content-between align-items-center">
                                    <label class="form-label">Supplier Packs</label>
                                    <a href="?page=supplier_pack&supplier_id=<?=$supplier_id?>" target="_blank" class="text-decoration-none toggleElements">Edit Packs</a>
                                </div>
                                <div id="pack_upd" class="bg-light p-2 rounded">
                                    
                                    <?php
                                    $query_pack = "SELECT * FROM supplier_pack WHERE supplierid = '$supplier_id' AND hidden = '0'";
                                    $result_pack = mysqli_query($conn, $query_pack);            
                                    if (mysqli_num_rows($result_pack) > 0) {
                                        while ($row_pack = mysqli_fetch_array($result_pack)) {
                                    ?>
                                        <span class="badge bg-primary me-1"><?= $row_pack['pack'] ?>(<?=$row_pack['pack_count']?>)</span>
                                    <?php   
                                        }
                                    } else {
                                    ?>
                                        <span>No packs found</span>
                                    <?php
                                    }
                                    ?>
                                    
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                            <label class="form-label">Supplier Code</label>
                            <input type="text" id="supplier_code" name="supplier_code" class="form-control" value="<?= $row['supplier_code'] ?? "" ?>" />
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded-3 mb-3">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 fw-bold">Supplier Payment Information</h5>
                </div>
                <div class="card-body border rounded p-3">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Minimum Freight Charge</label>
                                <input type="text" id="min_freight_charge" name="min_freight_charge" class="form-control" value="<?= $row['min_freight_charge'] ?? "" ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Freight Type</label>
                                <select id="freight_type" name="freight_type" class="form-control">
                                    <option value="">Select Freight Type</option>
                                    <option value="Semi delivery" <?= ($row['freight_type'] ?? '') == "Semi delivery" ? "selected" : "" ?>>Semi delivery</option>
                                    <option value="Hot Shot Truck" <?= ($row['freight_type'] ?? '') == "Hot Shot Truck" ? "selected" : "" ?>>Hot Shot Truck</option>
                                    <option value="FedEx" <?= ($row['freight_type'] ?? '') == "FedEx" ? "selected" : "" ?>>FedEx</option>
                                    <option value="UPS" <?= ($row['freight_type'] ?? '') == "UPS" ? "selected" : "" ?>>UPS</option>
                                    <option value="Other" <?= ($row['freight_type'] ?? '') == "Other" ? "selected" : "" ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Freight Tracking Website</label>
                                <input type="text" id="freight_website" name="freight_website" class="form-control" value="<?= $row['freight_website'] ?? "" ?>" />
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                            <label class="form-label">PaymentTerms</label>
                            <input type="text" id="payment_terms" name="payment_terms" class="form-control" value="<?= $row['payment_terms'] ?? "" ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                            <label class="form-label">Last Ordered Date</label>
                            <input type="date" id="last_ordered_date" name="last_ordered_date" class="form-control" value="<?= $row['last_ordered_date'] ?? "" ?>" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded-3 mb-3">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 fw-bold">Supplier Payment Information</h5>
                </div>
                <div class="card-body border rounded p-3">
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <textarea class="form-control" id="comment" name="comment" rows="3"><?= trim($row['comment'] ?? "") ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                $(document).ready(function () {
                    $(".phone-inputmask").inputmask("(999) 999-9999");

                    $(".supplier_color").each(function () {
                        let parentContainer = $(this).parent();
                        $(this).select2({
                            dropdownParent: parentContainer,
                            templateResult: formatOption, 
                            templateSelection: formatSelected,
                            escapeMarkup: function (markup) {
                                return markup;
                            }
                        });
                    });
                });
            </script>
            <?php
        
    } 

    if ($action == 'get_place_name') {
        $lat = $_POST['lat'];
        $lng = $_POST['lng'];
        $url = "https://nominatim.openstreetmap.org/reverse?lat=$lat&lon=$lng&format=json&addressdetails=1";

        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: Metal/1.0\r\n"
            ]
        ];
        $context = stream_context_create($opts);
        $response = @file_get_contents($url, false, $context);

        if ($response) {
            header('Content-Type: application/json');
            echo $response;
        } else {
            echo json_encode(['error' => 'Unable to fetch address']);
        }
        exit;
    }

    if ($action == 'search_address') {
        $query = urlencode($_POST['query']);
        $url = "https://nominatim.openstreetmap.org/search?q=$query&format=json&addressdetails=1&limit=5";

        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: Metal/1.0\r\n"
            ]
        ];
        $context = stream_context_create($opts);
        $response = @file_get_contents($url, false, $context);

        if ($response) {
            header('Content-Type: application/json');
            echo $response;
        } else {
            echo json_encode([]);
        }
        exit;
    }

    if ($action == "fetch_product") {
        $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
    
        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM inventory as i LEFT JOIN product as p ON p.product_id = i.product_id WHERE i.supplier_id = '$supplier_id'";
        $result = mysqli_query($conn, $checkQuery);
    
        ?>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        Products List
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body datatables">
                    <div class="table-responsive">
                        <table id="productList" class="table search-table align-middle text-nowrap">
                            <thead class="header-item">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                            
                            <?php
                            if (mysqli_num_rows($result) > 0) {
                                while ($row_product = mysqli_fetch_array($result)) {
                                    $product_id = $row_product['product_id'];
                                    $db_status = $row_product['status'];
                                    
                                    $picture_path = !empty($row_product['main_image']) ? $row_product['main_image'] : "images/product/product.jpg";
                                    ?>
                                        
                                    <tr>
                                        <td>
                                            <a href="/?page=product_details&product_id=<?= $row_product['product_id'] ?>">
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= $picture_path ?>" class="rounded-circle" alt="product-img" width="56" height="56">
                                                    <div class="ms-3">
                                                        <h6 class="fw-semibold mb-0 fs-4"><?= $row_product['product_item'] ?></h6>
                                                    </div>
                                                </div>
                                            </a>
                                        </td>
                                        <td>
                                            <?php 
                                                $quantity = 0;
                                                $query_inv = "SELECT * FROM inventory WHERE Product_id = '$product_id' AND supplier_id = '$supplier_id'";
                                                $result_inv = mysqli_query($conn, $query_inv);
                                                while ($row_inv = mysqli_fetch_array($result_inv)) {
                                                    $quantity += $row_inv['quantity_ttl'];
                                                }
                                                echo $quantity;
                                            ?>
                                        </td>
                                    </tr>
                                    <?php 
                                } 
                            } else {
                                ?>
                                <tr class="text-center fs-4">
                                    <td colspan="2">Supplier has no product listed in the inventory.</td>
                                </tr>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-actions">
                        <div class="card-body">
                            <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $('#productList').DataTable();
            });
        </script>
        <?php
    } 
 
    if ($action == "download_excel") {
        $supplier_type = mysqli_real_escape_string($conn, $_REQUEST['supplier_type'] ?? '');
        $supplier_name = strtoupper(getSupplierType($supplier_type));

        $includedColumns = array();
        $column_txt = '*';

        $includedColumns = [ 
            'supplier_id',
            'supplier_name',
            'supplier_website',
            'supplier_type',
            'contact_name',
            'contact_email',
            'contact_fax',
            'secondary_name',
            'secondary_phone',
            'secondary_email',
            'address',
            'city',
            'state',
            'zip',
            'last_ordered_date',
            'products',
            'freight_rate',
            'payment_terms',
            'comment',
            'supplier_color',
            'supplier_code',
            'supplier_paint_id',
        ];

        $column_txt = implode(', ', $includedColumns);

        $sql = "SELECT " . $column_txt . " FROM $table WHERE status = '1'";
        if (!empty($supplier_type)) {
            $sql .= " AND supplier_type = '$supplier_type'";
        }
        $result = $conn->query($sql);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [];
        $row = 1;
        
        foreach ($includedColumns as $index => $column) {
            $header = ucwords(str_replace('_', ' ', $column));
        
            if ($index >= 26) {
                $columnLetter = indexToColumnLetter($index);
            } else {
                $columnLetter = chr(65 + $index);
            }
        
            $sheet->setCellValue($columnLetter . $row, $header);
        }        

        $row = 2;
        while ($data = $result->fetch_assoc()) {
            foreach ($includedColumns as $index => $column) {
                if ($index >= 26) {
                    $columnLetter = indexToColumnLetter($index);
                } else {
                    $columnLetter = chr(65 + $index);
                }
                $sheet->setCellValue($columnLetter . $row, $data[$column] ?? '');
            }
            $row++;
        }

        $name = strtoupper(str_replace('_', ' ', $table));

        $filename = "$supplier_name $name.xlsx";
        $filePath = $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: max-age=0');

        readfile($filePath);

        unlink($filePath);
        exit;
    }

    if ($action == "upload_excel") {
        if (isset($_FILES['excel_file'])) {
            $fileTmpPath = $_FILES['excel_file']['tmp_name'];
            $fileName = $_FILES['excel_file']['name'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            if ($fileExtension != "xlsx" && $fileExtension != "xls") {
                echo "Please upload a valid Excel file.";
                exit;
            }

            $spreadsheet = IOFactory::load($fileTmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $columns = $rows[0];
            $dbColumns = [];
            $columnMapping = [];

            foreach ($columns as $col) {
                $dbColumn = strtolower(str_replace(' ', '_', $col));

                $dbColumns[] = $dbColumn;
                $columnMapping[$dbColumn] = $col;
            }

            $truncateSql = "TRUNCATE TABLE $test_table";
            $truncateResult = $conn->query($truncateSql);

            if (!$truncateResult) {
                echo "Error truncating table: " . $conn->error;
                exit;
            }

            foreach ($rows as $index => $row) {
                if ($index == 0) {
                    continue;
                }

                $data = array_combine($dbColumns, $row);

                $columnNames = implode(", ", array_keys($data));
                $columnValues = implode("', '", array_map(function($value) { return $value ?? ''; }, array_values($data)));

                $sql = "INSERT INTO $test_table ($columnNames) VALUES ('$columnValues')";
                $result = $conn->query($sql);

                if (!$result) {
                    echo "Error inserting data: " . $conn->error;
                    exit;
                }
            }

            echo "success";
        } else {
            echo "No file uploaded.";
            exit;
        }
    }   
    
    if ($action == "update_test_data") {
        $column_name = $_POST['header_name'];
        $new_value = $_POST['new_value'];
        $id = $_POST['id'];
        
        if (empty($column_name) || empty($id)) {
            exit;
        }

        $test_primary = getPrimaryKey($test_table);
        
        $column_name = mysqli_real_escape_string($conn, $column_name);
        $new_value = mysqli_real_escape_string($conn, $new_value);
        $id = mysqli_real_escape_string($conn, $id);
        
        $sql = "UPDATE $test_table SET `$column_name` = '$new_value' WHERE $test_primary = '$id'";

        if ($conn->query($sql) === TRUE) {
            echo 'success';
        } else {
            echo 'Error updating record: ' . $conn->error;
        }
    }

    if ($action == "save_table") {
        $main_primary = getPrimaryKey($table);
        $test_primary = getPrimaryKey($test_table);
        
        $selectSql = "SELECT * FROM $test_table";
        $result = $conn->query($selectSql);
    
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $main_primary_id = trim($row[$main_primary] ?? ''); 
    
                unset($row[$test_primary]);
    
                if (!empty($main_primary_id)) {
                    $checkSql = "SELECT COUNT(*) as count FROM $table WHERE $main_primary = '$main_primary_id'";
                    $checkResult = $conn->query($checkSql);
                    $exists = $checkResult->fetch_assoc()['count'] > 0;
    
                    if ($exists) {
                        $updateFields = [];
                        foreach ($row as $column => $value) {
                            if ($column !== $main_primary && $value !== null && $value !== '') {
                                $updateFields[] = "$column = '$value'";
                            }
                        }
                        if (!empty($updateFields)) {
                            $updateSql = "UPDATE $table SET " . implode(", ", $updateFields) . " WHERE $main_primary = '$main_primary_id'";
                            $conn->query($updateSql);
                        }
                        continue;
                    }
                }
    
                $columns = [];
                $values = [];
                foreach ($row as $column => $value) {
                    if ($value !== null && $value !== '') {
                        $columns[] = $column;
                        $values[] = "'$value'";
                    }
                }
                if (!empty($columns)) {
                    $insertSql = "INSERT INTO $table (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ")";
                    $conn->query($insertSql);
                }
            }
    
            echo "Data has been successfully saved";
    
            $truncateSql = "TRUNCATE TABLE $test_table";
            if ($conn->query($truncateSql) !== TRUE) {
                echo " but failed to clear test color table: " . $conn->error;
            }
        } else {
            echo "No data found in test color table.";
        }
    }     

    if ($action == "fetch_uploaded_modal") {
        $test_primary = getPrimaryKey($test_table);
        
        $sql = "SELECT * FROM $test_table";
        $result = $conn->query($sql);
    
        if ($result->num_rows > 0) {
            $columns = [];
            while ($field = $result->fetch_field()) {
                $columns[] = $field->name;
            }
    
            $includedColumns = [ 
                'supplier_id',
                'supplier_name',
                'supplier_website',
                'supplier_type',
                'contact_name',
                'contact_email',
                'contact_fax',
                'secondary_name',
                'secondary_phone',
                'secondary_email',
                'address',
                'city',
                'state',
                'zip',
                'last_ordered_date',
                'products',
                'freight_rate',
                'payment_terms',
                'comment',
                'supplier_color',
                'supplier_code',
                'supplier_paint_id',
            ];
    
            $columns = array_filter($columns, function ($col) use ($includedColumns) {
                return in_array($col, $includedColumns, true);
            });
    
            $columnsWithData = [];
            while ($row = $result->fetch_assoc()) {
                foreach ($columns as $column) {
                    if (!empty(trim($row[$column] ?? ''))) {
                        $columnsWithData[$column] = true;
                    }
                }
            }
    
            $result->data_seek(0);
            ?>
    
            <div class="card card-body shadow" data-table="<?=$table?>">
                <form id="tableForm">
                    <div style="overflow-x: auto; overflow-y: auto; max-height: 80vh; max-width: 100%;">
                        <table class="table table-bordered table-striped text-center">
                            <thead>
                                <tr>
                                    <?php
                                    foreach ($columns as $column) {
                                        if (isset($columnsWithData[$column])) {
                                            $formattedColumn = ucwords(str_replace('_', ' ', $column));
                                            echo "<th class='fs-4'>$formattedColumn</th>";
                                        }
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                while ($row = $result->fetch_assoc()) {
                                    $primaryValue = $row[$test_primary] ?? '';
                                    echo '<tr>';
                                    foreach ($columns as $column) {
                                        if (isset($columnsWithData[$column])) {
                                            $value = htmlspecialchars($row[$column] ?? '', ENT_QUOTES, 'UTF-8');
                                            echo "<td contenteditable='true' class='table_data' data-header-name='$column' data-id='$primaryValue'>$value</td>";
                                        }
                                    }
                                    echo '</tr>';
                                }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <button type="button" id="saveTable" class="btn btn-primary mt-3">Save</button>
                    </div>
                </form>
            </div>
            <?php
        } else {
            echo "<p>No data found in the table.</p>";
        }
    }
    
    if ($action == "download_classifications") {
        $classification = mysqli_real_escape_string($conn, $_REQUEST['class'] ?? '');

        $classifications = [
            'supplier_type' => [
                'columns' => ['supplier_type_id', 'supplier_type'],
                'table' => 'supplier_type',
                'where' => "status = '1'"
            ],
            'supplier_color' => [
                'columns' => ['id', 'color'],
                'table' => 'supplier_color',
                'where' => "status = '1'"
            ],
            'supplier_pack' => [
                'columns' => ['id', 'pack'],
                'table' => 'supplier_pack',
                'where' => "status = '1'"
            ],
            'supplier_case' => [
                'columns' => ['id', 'case'],
                'table' => 'supplier_case',
                'where' => "status = '1'"
            ]
        ];

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $selectedClassifications = empty($classification) ? array_keys($classifications) : [$classification];

        foreach ($selectedClassifications as $class) {
            if (!isset($classifications[$class])) {
                continue;
            }

            $includedColumns = $classifications[$class]['columns'];
            $table = $classifications[$class]['table'];
            $where = $classifications[$class]['where'];
            $column_txt = implode(', ', array_map(fn($col) => "`$col`", $includedColumns));
            $sql = "SELECT $column_txt FROM $table WHERE $where";
            $result = $conn->query($sql);

            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle(ucwords($class));

            $row = 1;
            foreach ($includedColumns as $index => $column) {
                $header = ucwords(str_replace('_', ' ', $column));
                $columnLetter = chr(65 + $index);
                $sheet->setCellValue($columnLetter . $row, $header);
            }

            $row = 2;
            while ($data = $result->fetch_assoc()) {
                foreach ($includedColumns as $index => $column) {
                    $columnLetter = chr(65 + $index);

                    $value = $data[$column] ?? '';
                        
                    $sheet->setCellValue($columnLetter . $row, $value);
                }
                $row++;
            }
        }

        if(empty($classification)){
            $classification = 'All';
        }else{
            $classification = ucwords($classification);
        }

        $filename = "$classification Classifications.xlsx";
        $filePath = $filename;
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: max-age=0');

        readfile($filePath);
        unlink($filePath);
        exit;
    }

    mysqli_close($conn);
}
?>
