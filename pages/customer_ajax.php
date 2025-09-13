<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = 'customer';
$test_table = 'customer_excel';
$main_primary_key = getPrimaryKey($table);

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
        $customer_first_name = mysqli_real_escape_string($conn, $_POST['customer_first_name'] ?? '');
        $customer_last_name = mysqli_real_escape_string($conn, $_POST['customer_last_name'] ?? '');
        $customer_business_name = mysqli_real_escape_string($conn, $_POST['customer_business_name'] ?? '');
        $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email'] ?? '');
        $contact_phone = mysqli_real_escape_string($conn, $_POST['contact_phone'] ?? '');
        $primary_contact = mysqli_real_escape_string($conn, $_POST['primary_contact'] ?? '');
        $contact_fax = mysqli_real_escape_string($conn, $_POST['contact_fax'] ?? '');
        $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
        $city = mysqli_real_escape_string($conn, $_POST['city'] ?? '');
        $state = mysqli_real_escape_string($conn, $_POST['state'] ?? '');
        $zip = mysqli_real_escape_string($conn, $_POST['zip'] ?? '');
        $lat = mysqli_real_escape_string($conn, $_POST['lat'] ?? '');
        $lng = mysqli_real_escape_string($conn, $_POST['lng'] ?? '');
        $secondary_contact_name = mysqli_real_escape_string($conn, $_POST['secondary_contact_name'] ?? '');
        $secondary_contact_phone = mysqli_real_escape_string($conn, $_POST['secondary_contact_phone'] ?? '');
        $ap_contact_name = mysqli_real_escape_string($conn, $_POST['ap_contact_name'] ?? '');
        $ap_contact_email = mysqli_real_escape_string($conn, $_POST['ap_contact_email'] ?? '');
        $ap_contact_phone = mysqli_real_escape_string($conn, $_POST['ap_contact_phone'] ?? '');
        $tax_status = mysqli_real_escape_string($conn, $_POST['tax_status'] ?? '');
        $tax_exempt_number = mysqli_real_escape_string($conn, $_POST['tax_exempt_number'] ?? '');
        $customer_notes = mysqli_real_escape_string($conn, $_POST['customer_notes']);
        $new_customer_type_id = mysqli_real_escape_string($conn, $_POST['customer_type'] ?? '');
        $call_status = mysqli_real_escape_string($conn, $_POST['call_status'] ?? 0);
        $is_charge_net = mysqli_real_escape_string($conn, $_POST['is_charge_net'] ?? 0);
        $is_contractor = mysqli_real_escape_string($conn, $_POST['is_contractor'] ?? 0);
        $charge_net_30 = mysqli_real_escape_string($conn, $_POST['charge_net_30']);
        $credit_limit = mysqli_real_escape_string($conn, $_POST['credit_limit'] ?? 0);
        $loyalty = mysqli_real_escape_string($conn, $_POST['loyalty'] ?? 0);
        $customer_pricing = mysqli_real_escape_string($conn, $_POST['customer_pricing'] ?? 0);
        $is_approved = mysqli_real_escape_string($conn, $_POST['portal_access'] ?? 0);

        $different_ship_address = isset($_POST['different_ship_address']) ? 1 : 0;
        if ($different_ship_address == 0) {
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

        $checkQuery = "SELECT * FROM customer WHERE customer_id = '$customer_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "
                UPDATE customer SET  
                    customer_first_name = '$customer_first_name', 
                    customer_last_name = '$customer_last_name', 
                    customer_business_name = '$customer_business_name', 
                    contact_email = '$contact_email', 
                    contact_phone = '$contact_phone', 
                    primary_contact = '$primary_contact', 
                    contact_fax = '$contact_fax', 
                    address = '$address', 
                    city = '$city', 
                    state = '$state',
                    zip = '$zip',
                    lat = '$lat',
                    lng = '$lng',
                    different_ship_address = '$different_ship_address',
                    ship_address = '$ship_address',
                    ship_city = '$ship_city',
                    ship_state = '$ship_state',
                    ship_zip = '$ship_zip',
                    ship_lat = '$ship_lat',
                    ship_lng = '$ship_lng',
                    secondary_contact_name = '$secondary_contact_name',
                    secondary_contact_phone = '$secondary_contact_phone',
                    ap_contact_name = '$ap_contact_name',
                    ap_contact_email = '$ap_contact_email',
                    ap_contact_phone = '$ap_contact_phone',
                    tax_status = '$tax_status',
                    tax_exempt_number = '$tax_exempt_number',
                    customer_notes = '$customer_notes',
                    call_status = '$call_status',
                    is_charge_net = '$is_charge_net',
                    is_contractor = '$is_contractor',
                    charge_net_30 = '$charge_net_30',
                    credit_limit = '$credit_limit',
                    customer_type_id = '$new_customer_type_id',
                    loyalty = '$loyalty',
                    customer_pricing = '$customer_pricing',
                    is_approved = '$is_approved',
                    updated_at = NOW()
                WHERE customer_id = '$customer_id'";
            mysqli_query($conn, $updateQuery) or die("Error updating customer: " . mysqli_error($conn));
            echo "Customer updated successfully.";

            $isUpdate = true;
        } else {
            $insertQuery = "
                INSERT INTO customer (
                    customer_first_name, customer_last_name, customer_business_name,
                    contact_email, contact_phone, primary_contact, contact_fax,
                    address, city, state, zip, lat, lng,
                    different_ship_address, ship_address, ship_city, ship_state, ship_zip, ship_lat, ship_lng,
                    secondary_contact_name, secondary_contact_phone,
                    ap_contact_name, ap_contact_email, ap_contact_phone,
                    tax_status, tax_exempt_number, customer_notes,
                    customer_type_id, call_status, is_charge_net, is_contractor, charge_net_30,
                    credit_limit, loyalty, customer_pricing, is_approved, created_at, updated_at
                ) VALUES (
                    '$customer_first_name', '$customer_last_name', '$customer_business_name',
                    '$contact_email', '$contact_phone', '$primary_contact', '$contact_fax',
                    '$address', '$city', '$state', '$zip', '$lat', '$lng',
                    '$different_ship_address', '$ship_address', '$ship_city', '$ship_state', '$ship_zip', '$ship_lat', '$ship_lng',
                    '$secondary_contact_name', '$secondary_contact_phone',
                    '$ap_contact_name', '$ap_contact_email', '$ap_contact_phone',
                    '$tax_status', '$tax_exempt_number', '$customer_notes',
                    '$new_customer_type_id', '$call_status', '$is_charge_net', '$is_contractor', '$charge_net_30',
                    '$credit_limit', '$loyalty', '$customer_pricing', '$is_approved',NOW(), NOW()
                )";
            mysqli_query($conn, $insertQuery) or die("Error adding customer: " . mysqli_error($conn));
            echo "New customer added successfully.";

            $isUpdate = false;
        }

        if (!empty($_POST['password'])) {
            $encryptedPassword = encrypt_password_for_storage($_POST['password']);
            $passwordQuery = "
                UPDATE customer 
                SET password = '" . mysqli_real_escape_string($conn, $encryptedPassword) . "', 
                    updated_at = NOW()
                WHERE customer_id = '" . mysqli_real_escape_string($conn, $customer_id) . "'
            ";
            mysqli_query($conn, $passwordQuery) or die("Error updating password: " . mysqli_error($conn));
        }

        if (!empty($_FILES['picture_path']['name'][0])) {
            $uploadDir = __DIR__ . "/../images/customer_tax_documents/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['picture_path']['tmp_name'] as $key => $tmpName) {
                if (!empty($tmpName)) {
                    $filename = time() . "_" . preg_replace("/[^A-Za-z0-9\._-]/", "_", $_FILES['picture_path']['name'][$key]);
                    $targetFile = $uploadDir . $filename;

                    if (move_uploaded_file($tmpName, $targetFile)) {
                        $filePath = "images/customer_tax_documents/" . $filename;
                        $insertImg = "INSERT INTO customer_tax_images (customer_id, image_url) 
                                    VALUES ('$customer_id', '$filePath')";
                        mysqli_query($conn, $insertImg);
                    }
                }
            }
        }
    }

    if ($action == "change_status") {
        $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE customer SET status = '$new_status' WHERE customer_id = '$customer_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }

    if ($action == 'hide_customer') {
        $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
        $query = "UPDATE customer SET hidden='1' WHERE customer_id='$customer_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == "remove_image") {
        $image_id = $_POST['image_id'];
    
        $delete_query = "DELETE FROM customer_tax_images WHERE taximgid = '$image_id'";
        if (mysqli_query($conn, $delete_query)) {
            /* if (file_exists($image_url)) {
                unlink($image_url);
            } */
            echo 'success';
        } else {
            echo "Error removing image: " . mysqli_error($conn);
        }
    }

    if ($action == 'change_act_cust_id') {
        $customer_id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM customer WHERE customer_id = '$customer_id'";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_array($result)) {
            $customer_id = $row['customer_id'] ?? 0;
            $customer_first_name = $row['customer_first_name'] ?? '';
            $customer_last_name = $row['customer_last_name'] ?? '';
            $customer_business_name = $row['customer_business_name'] ?? '';
            $old_customer_type_id = $row['customer_type_id'] ?? '';
            $contact_email = $row['contact_email'] ?? '';
            $contact_phone = $row['contact_phone'] ?? '';
            $primary_contact = $row['primary_contact'] ?? '';
            $contact_fax = $row['contact_fax'] ?? '';
            $address = $row['address'] ?? '';
            $city = $row['city'] ?? '';
            $state = $row['state'] ?? '';
            $zip = $row['zip'] ?? '';
            $ship_address = $row['ship_address'] ?? '';
            $ship_city = $row['ship_city'] ?? '';
            $ship_state = $row['ship_state'] ?? '';
            $ship_zip = $row['ship_zip'] ?? '';
            $secondary_contact_name = $row['secondary_contact_name'] ?? '';
            $secondary_contact_phone = $row['secondary_contact_phone'] ?? '';
            $tax_status = $row['tax_status'] ?? '';
            $tax_exempt_number = $row['tax_exempt_number'] ?? '';
            $customer_notes = $row['customer_notes'] ?? '';
            $call_status = $row['call_status'] ?? 0;
            $charge_net_30 = $row['charge_net_30'] ?? 0;
            $credit_limit = $row['credit_limit'] ?? 0;
            $customer_pricing = $row['customer_pricing'] ?? 0;
            $lat = !empty($row['lat']) ? $row['lat'] : 0;
            $lng = !empty($row['lng']) ? $row['lng'] : 0;
            $ship_lat = !empty($row['ship_lat']) ? $row['ship_lat'] : 0;
            $ship_lng = !empty($row['ship_lng']) ? $row['ship_lng'] : 0;
            $portal_access = $row['is_approved'] ?? 0;
            $different_ship_address = $row['different_ship_address'] ?? 0;
            $is_charge_net = $row['is_charge_net'] ?? 0;
            $username = $row['username'] ?? '';
            $password = $row['password'] ?? '';
            $is_contractor = $row['is_contractor'] ?? '';

            $decryptedPassword = '';
            if (!empty($password)) {
                try {
                    $decryptedPassword = decrypt_password_from_storage($password);
                } catch (Exception $e) {
                    $decryptedPassword = '';
                }
            }

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
            $loyalty = $row['loyalty'];
            }
        ?>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Contact Information</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" id="customer_first_name" name="customer_first_name" class="form-control"
                            value="<?= $customer_first_name ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" id="customer_last_name" name="customer_last_name" class="form-control"
                            value="<?= $customer_last_name ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Business Name</label>
                        <input type="text" id="customer_business_name" name="customer_business_name" class="form-control"
                            value="<?= $customer_business_name ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" id="contact_phone" name="contact_phone" class="form-control"
                            value="<?= $contact_phone ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="text" id="contact_email" name="contact_email" class="form-control"
                            value="<?= $contact_email ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Fax Number</label>
                        <input type="text" id="contact_fax" name="contact_fax" class="form-control"
                            value="<?= $contact_fax ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Preferred Method of Contact</label>
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="primary_contact" id="contact_phone_radio" value="phone" <?= ($primary_contact ?? '' == '2' ? 'checked' : '') ?>>
                                <label class="form-check-label" for="contact_phone_radio">Phone</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="primary_contact" id="contact_email_radio" value="email" <?= ($primary_contact ?? '' != '1' ? 'checked' : '') ?>>
                                <label class="form-check-label" for="contact_email_radio">Email</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="primary_contact" id="contact_call_radio" value="call" <?= ($primary_contact ?? '' == '3' ? 'checked' : '') ?>>
                                <label class="form-check-label" for="contact_phone_radio">Call</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Secondary Contact Name</label>
                        <input type="text" id="secondary_contact_name" name="secondary_contact_name" class="form-control"
                            value="<?= $secondary_contact_name ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Secondary Contact Phone</label>
                        <input type="text" id="secondary_contact_phone" name="secondary_contact_phone" class="form-control"
                            value="<?= $secondary_contact_phone ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Is this Customer a Contractor?</label>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_contractor" id="is_contractor" value="1" <?= ($is_contractor ?? '' == '1' ? 'checked' : '') ?>><br>
                            </div>
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
                    <div class="shipping_address_section">
                        <div class="row">
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
                        </div>
                        
                        <input type="hidden" id="ship_lat" name="ship_lat" value="<?= $ship_lat ?? '' ?>" />
                        <input type="hidden" id="ship_lng" name="ship_lng" value="<?= $ship_lng ?? '' ?>" />
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Tax Information</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-6 opt_field_update">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label">Tax Status</label>
                            <a href="?page=customer_tax" target="_blank" class="text-decoration-none toggleElements">Edit</a>
                        </div>
                        <select id="tax_status" class="form-select form-control" name="tax_status">
                        <option value="">Select Tax Status...</option>
                        <?php
                        $query_tax_status = "SELECT * FROM customer_tax";
                        $result_tax_status = mysqli_query($conn, $query_tax_status);
                        while ($row_tax_status = mysqli_fetch_array($result_tax_status)) {
                            $selected = (($tax_status ?? 0) == $row_tax_status['taxid']) ? 'selected' : '';
                            ?>
                            <option value="<?= $row_tax_status['taxid'] ?>" <?= $selected ?>>
                            (<?= $row_tax_status['percentage'] ?>%) <?= $row_tax_status['tax_status_desc'] ?></option>
                        <?php
                        }
                        ?>
                        </select>
                    </div>
                    </div>
                    <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tax Exempt Number</label>
                        <input type="text" id="tax_exempt_number" name="tax_exempt_number" class="form-control"
                        value="<?= $tax_exempt_number ?? '' ?>" />
                    </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card-body p-0">
                            <h4 class="card-title text-center">Tax Documents</h4>
                            <p action="#" id="myUpdateDropzone" class="dropzone">
                                <div class="fallback">
                                <input type="file" id="picture_path_update" name="picture_path[]" class="form-control" style="display: none" multiple/>
                                </div>
                            </p>
                        </div>
                    </div>

                    <?php
                    $query_img = "SELECT * FROM customer_tax_images WHERE customer_id = '" . ($customer_id ?? 0) . "'";
                    $result_img = mysqli_query($conn, $query_img);
                    if (mysqli_num_rows($result_img) > 0) { ?>
                        <div class="col-md-12">
                            <h5>Tax Documents</h5>
                            <div class="row pt-3">
                                <?php while ($row_img = mysqli_fetch_array($result_img)) { 
                                    $image_id = $row_img['taximgid'];
                                    ?>
                                    <div class="col-md-2 position-relative">
                                        <div class="mb-3">
                                            <img src="<?= $row_img['image_url'] ?>" class="img-fluid" alt="Tax Image" />
                                            <button class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image-btn" data-image-id="<?= $image_id ?>">X</button>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>

                    <script>
                        window.uploadedUpdateFiles = window.uploadedUpdateFiles || [];
                        $('#myUpdateDropzone').dropzone({
                            addRemoveLinks: true,
                            dictRemoveFile: "X",
                            init: function() {
                                this.on("addedfile", function(file) {
                                    uploadedUpdateFiles.push(file);
                                    updateFileInput2();
                                });

                                this.on("removedfile", function(file) {
                                    uploadedUpdateFiles = uploadedUpdateFiles.filter(f => f.name !== file.name);
                                    updateFileInput2();
                                });
                            }
                        });
                        function updateFileInput2() {
                            const fileInput = document.getElementById('picture_path_update');
                            const dataTransfer = new DataTransfer();
                            uploadedUpdateFiles.forEach(file => {
                                const fileBlob = new Blob([file], { type: file.type });
                                dataTransfer.items.add(new File([fileBlob], file.name, { type: file.type }));
                            });
                            fileInput.files = dataTransfer.files;
                        }
                    </script>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Pricing Information</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label">Customer Pricing</label>
                            <a href="?page=customer_pricing" target="_blank" class="text-decoration-none toggleElements">Edit</a>
                        </div>
                        <div class="mb-3" data-pricing="<?= $customer_pricing ?? '' ?>">
                            <select id="customer_pricing" class="form-control" name="customer_pricing">
                                <option value="">Select One...</option>
                                <?php
                                $query_pricing = "SELECT * FROM customer_pricing WHERE hidden = '0' AND status = '1'";
                                $result_pricing = mysqli_query($conn, $query_pricing);            
                                while ($row_pricing = mysqli_fetch_array($result_pricing)) {
                                    $selected = (($customer_pricing ?? '') == $row_pricing['id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_pricing['id'] ?>" <?= $selected ?>><?= $row_pricing['pricing_name'] ?></option>
                                <?php   
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="loyalty">Loyalty</label>
                        <select name="loyalty" id="loyalty" class="form-select form-control">
                        <option value="0" <?php if ($loyalty ?? '' == '0')
                            echo 'selected'; ?>>Off</option>
                        <option value="1" <?php if ($loyalty ?? '' == '1')
                            echo 'selected'; ?>>On</option>
                        </select>
                    </div>
                    <div class="col-4"></div>
                    <div class="col-4 mb-3">
                        <label class="form-label">Charge Net 30</label>
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_charge_net" id="is_charge_net" value="1" <?= ($is_charge_net ?? '' == '1' ? 'checked' : '') ?>><br>
                            </div>
                        </div>
                    </div>
                    <div class="col-8">
                        <div class="chargeNetLimitSection row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Charge Net 30 Limit</label>
                                <input class="form-control" type="number" step="0.001" id="charge_net_30" name="charge_net_30" value="<?= $charge_net_30 ?? '' ?>">
                            </div>
                            <div class="col-6"></div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Portal Access</label>
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            
                            <div class="form-check">
                                <input class="form-check-input" 
                                        type="checkbox" 
                                        name="portal_access" 
                                        id="portal_access" 
                                        value="1" 
                                        <?= !empty($portal_access) && $portal_access == '1' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="portal_access">
                                    Portal Access
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-8">
                        <div class="portal_user_pass_section row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="username">Username</label>
                                <input
                                    class="form-control"
                                    type="text"
                                    name="username"
                                    id="username"
                                    placeholder="Enter Username" 
                                    value="<?= $username ?? ''  ?>"
                                    />
                            </div>
                            <div class="mb-3 col-md-6 form-password-toggle">
                                <label class="form-label" for="password">Password</label>
                                <div class="input-group input-group-merge">
                                <input
                                    class="form-control"
                                    type="password"
                                    name="password"
                                    id="password"
                                    value="<?= $decryptedPassword  ?? ''  ?>"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Customer Notes</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <textarea class="form-control" id="customer_notes" name="customer_notes"
                            rows="3"><?= $customer_notes ?? '' ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" id="customer_id" name="customer_id" class="form-control" value="<?= $customer_id ?? 0 ?>" />

        <div class="form-actions toggleElements">
            <div class="card-body border-top ">
                <div class="row">
                    <div class="col text-end">
                    <button type="submit" class="btn btn-primary"
                        style="border-radius: 10%;">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function () {
                if ($('#different_ship_address').is(':checked')) {
                    $('.shipping_address_section').removeClass('d-none');
                } else {
                    $('.shipping_address_section').addClass('d-none');
                }

                $(document).on('change', '#different_ship_address', function () {
                    if ($(this).is(':checked')) {
                        $('.shipping_address_section').removeClass('d-none');
                    } else {
                        $('.shipping_address_section').addClass('d-none');
                    }
                });

                if ($('#is_charge_net').is(':checked')) {
                    $('.chargeNetLimitSection').removeClass('d-none');
                } else {
                    $('.chargeNetLimitSection').addClass('d-none');
                }

                $(document).on('change', '#is_charge_net', function () {
                    if ($(this).is(':checked')) {
                        $('.chargeNetLimitSection').removeClass('d-none');
                    } else {
                        $('.chargeNetLimitSection').addClass('d-none');
                    }
                });

                if ($('#portal_access').is(':checked')) {
                    $('.portal_user_pass_section').removeClass('d-none');
                } else {
                    $('.portal_user_pass_section').addClass('d-none');
                }

                $(document).on('change', '#portal_access', function () {
                    if ($(this).is(':checked')) {
                        $('.portal_user_pass_section').removeClass('d-none');
                    } else {
                        $('.portal_user_pass_section').addClass('d-none');
                    }
                });

                $('.input-group-text').on('click', function () {
                    const $input = $(this).siblings('input');
                    const $icon = $(this).find('i');
                    
                    if ($input.attr('type') === 'password') {
                        $input.attr('type', 'text');
                        $icon.removeClass('ti-eye-off').addClass('ti-eye');
                    } else {
                        $input.attr('type', 'password');
                        $icon.removeClass('ti-eye').addClass('ti-eye-off');
                    }
                });
            });
        </script>
    <?php
    }

    if ($action == "download_excel") {
        $includedColumns = array();
        $column_txt = '*';

        $includedColumns = [ 
            'customer_id',
            'customer_notes',
            'customer_first_name',
            'customer_last_name',
            'customer_business_name',
            'customer_type_id',
            'contact_email',
            'contact_phone',
            'primary_contact',
            'contact_fax',
            'address',
            'city',
            'state',
            'zip',
            'lat',
            'lng',
            'secondary_contact_name',
            'secondary_contact_phone',
            'ap_contact_name',
            'ap_contact_email',
            'ap_contact_phone',
            'tax_status',
            'tax_exempt_number',
            'call_status',
            'charge_net_30',
            'credit_limit',
            'customer_pricing'
        ];

        $column_txt = implode(', ', $includedColumns);

        $sql = "SELECT " . $column_txt . " FROM $table WHERE hidden = '0' AND status = '1'";
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

        $filename = "$name.xlsx";
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
                'customer_id',
                'customer_notes',
                'customer_first_name',
                'customer_last_name',
                'customer_business_name',
                'customer_type_id',
                'contact_email',
                'contact_phone',
                'primary_contact',
                'contact_fax',
                'address',
                'city',
                'state',
                'zip',
                'lat',
                'lng',
                'secondary_contact_name',
                'secondary_contact_phone',
                'ap_contact_name',
                'ap_contact_email',
                'ap_contact_phone',
                'tax_status',
                'tax_exempt_number',
                'call_status',
                'charge_net_30',
                'credit_limit',
                'customer_pricing'
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
            'tax_status' => [
                'columns' => ['taxid', 'tax_status_desc'],
                'table' => 'customer_tax',
                'where' => "1"
            ],
            'customer_pricing' => [
                'columns' => ['id', 'pricing_name'],
                'table' => 'customer_pricing',
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
            $column_txt = implode(', ', $includedColumns);
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
