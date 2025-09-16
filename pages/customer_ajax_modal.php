<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/functions.php';

$table = 'supplier';
$test_table = 'supplier_excel';
$main_primary_key = getPrimaryKey($table);

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == 'customer_personal_modal') {
        $customer_id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM customer WHERE customer_id = '$customer_id'";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_array($result)) {
            $customer_id = $row['customer_id'] ?? 0;
            $customer_first_name = $row['customer_first_name'] ?? '';
            $customer_last_name = $row['customer_last_name'] ?? '';
            $customer_business_name = $row['customer_business_name'] ?? '';
            $customer_type_id = $row['customer_type_id'] ?? '';
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

            $payment_pickup   = $row['payment_pickup']   ?? 0;
            $payment_delivery = $row['payment_delivery'] ?? 0;
            $payment_cash     = $row['payment_cash']     ?? 0;
            $payment_check    = $row['payment_check']    ?? 0;
            $payment_card     = $row['payment_card']     ?? 0;

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

        <div class="modal fade" id="map1Modal" tabindex="-1" role="dialog" aria-labelledby="mapsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mapsModalLabel">Search Address</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="mapForm" class="form-horizontal">
                    <div class="modal-body">
                        <div class="mb-2">
                            <input id="searchBox1" class="form-control" list="address1-list" autocomplete="off">
                            <datalist id="address1-list"></datalist>
                        </div>
                        <div id="map1" class="map-container" style="height: 60vh; width: 100%;"></div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-actions">
                            <div class="card-body">
                                <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="map2Modal" tabindex="-1" role="dialog" aria-labelledby="mapsModalLabel2" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mapsModalLabel2">Search Shipping Address</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="mapForm2" class="form-horizontal">
                    <div class="modal-body">
                        <div class="mb-2">
                            <input id="searchBox2" class="form-control" list="address2-list" autocomplete="off">
                            <datalist id="address2-list"></datalist>
                        </div>
                        <div id="map2" class="map-container" style="height: 60vh; width: 100%;"></div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-actions">
                            <div class="card-body">
                                <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>

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
                    <div class="col-md-4"></div>

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
                    <div class="col-md-4"></div>

                    <div class="col-md-4">
                        <label class="form-label">Preferred Method of Contact</label>
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" 
                                    type="radio" 
                                    name="primary_contact" 
                                    id="contact_phone_radio" 
                                    value="phone" 
                                    <?= (($primary_contact ?? 'phone') === 'phone' || ($primary_contact ?? '') == '2' || ($primary_contact ?? '') == '0') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="contact_phone_radio">Phone</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" 
                                    type="radio" 
                                    name="primary_contact" 
                                    id="contact_email_radio" 
                                    value="email" 
                                    <?= (($primary_contact ?? '') === 'email' || ($primary_contact ?? '') == '1') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="contact_email_radio">Email</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" 
                                    type="radio" 
                                    name="primary_contact" 
                                    id="contact_call_radio" 
                                    value="call" 
                                    <?= (($primary_contact ?? '') === 'call' || ($primary_contact ?? '') == '3') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="contact_call_radio">Call</label>
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
                                <button type="button" class="btn btn-primary py-1 ms-2 toggleElements" id="showMapsBtn" data-address="<?=$addressDetails ?? ''?>" style="border-radius: 10%;">Change</button>
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
                                        <button type="button" class="btn btn-primary py-1 ms-2 toggleElements" id="showMapsShipBtn" style="border-radius: 10%;">Change</button>
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
                            <label class="form-label">Tax Exemption #</label>
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

                    <div class="col-12">
                        <div class="container mb-3">
                            <h5 class="mb-3 text-center">Allowable Payment Methods</h5>
                            <div class="row text-center">

                                <!-- Pay at Pick-Up -->
                                <div class="col">
                                    <label class="fw-bold d-block">Pay at Pick-Up</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_pickup" name="payment_pickup"
                                            <?= (($payment_pickup ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <!-- Pay at Delivery -->
                                <div class="col">
                                    <label class="fw-bold d-block">Pay at Delivery</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_delivery" name="payment_delivery"
                                            <?= (($payment_delivery ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <!-- Cash -->
                                <div class="col">
                                    <label class="fw-bold d-block">Cash</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_cash" name="payment_cash"
                                            <?= (($payment_cash ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <!-- Check -->
                                <div class="col">
                                    <label class="fw-bold d-block">Check</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_check" name="payment_check"
                                            <?= (($payment_check ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <!-- Credit/Debit Card -->
                                <div class="col">
                                    <label class="fw-bold d-block">Credit/Debit Card</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_card" name="payment_card"
                                            <?= (($payment_card ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                            </div>
                        </div>        
                    </div>

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
        <input type="hidden" id="customer_type_id" name="customer_type_id" class="form-control" value="<?= $customer_type_id ?? 0 ?>" />

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
            class CustomerMaps {
                constructor(apiKey) {
                    this.apiKey = apiKey;

                    function safeFloat(val, fallback) {
                        if (val === undefined || val === null || val === "") return fallback;
                        const num = parseFloat(val);
                        return isNaN(num) ? fallback : num;
                    }

                    const DEFAULT_LAT = 37.8393;
                    const DEFAULT_LNG = -84.2700;

                    this.lat1 = safeFloat($('#lat').val(), DEFAULT_LAT);
                    this.lng1 = safeFloat($('#lng').val(), DEFAULT_LNG);

                    this.lat2 = safeFloat($('#ship_lat').val(), DEFAULT_LAT);
                    this.lng2 = safeFloat($('#ship_lng').val(), DEFAULT_LNG);

                    this.map1 = null;
                    this.marker1 = null;
                    this.map2 = null;
                    this.marker2 = null;

                    this.debounce = (func, wait) => {
                        let timeout;
                        return (...args) => {
                            clearTimeout(timeout);
                            timeout = setTimeout(() => func.apply(this, args), wait);
                        };
                    };

                    $(document).ready(() => this.initUI());

                    this.loadGoogleMapsAPI();
                }

                initUI() {
                    $('#searchBox1').on('input', this.debounce(() => this.updateSuggestions('#searchBox1', '#address1-list'), 400));
                    $('#searchBox2').on('input', this.debounce(() => this.updateSuggestions('#searchBox2', '#address2-list'), 400));
                    $('#address').on('input', this.debounce(() => this.updateSuggestions('#address', '#address-data-list'), 400));

                    $('#searchBox1').on('change', () => this.onAddressChange('#searchBox1', '#address1-list', 'main'));
                    $('#searchBox2').on('change', () => this.onAddressChange('#searchBox2', '#address2-list', 'ship'));
                    $('#address').on('change', () => this.onAddressChange('#address', '#address-data-list', 'main'));

                    $('#map1Modal, #map2Modal').on('shown.bs.modal', (e) => {
                        if (e.target.id === 'map1Modal' && !this.map1) this.initMap1();
                        if (e.target.id === 'map2Modal' && !this.map2) this.initMap2();
                    });

                    $('#map1Modal, #map2Modal').on('hidden.bs.modal', () => $('#customerModal').modal('show'));

                    $(document).on('click', '#showMapsBtn', (e) => {
                        let address = $(e.currentTarget).data('address') || "";
                        $('#searchBox1').val(address).trigger(address ? 'change' : '');
                        $('#map1Modal').modal('show');
                    });

                    $(document).on('click', '#showMapsShipBtn', (e) => {
                        let address = $(e.currentTarget).data('address') || "";
                        $('#searchBox2').val(address).trigger(address ? 'change' : '');
                        $('#map2Modal').modal('show');
                    });
                }

                updateSuggestions(inputId, listId) {
                    let query = $(inputId).val();
                    if (query.length < 2) return;

                    $.ajax({
                        url: 'pages/supplier_ajax.php',
                        method: 'POST',
                        data: { action: 'search_address', query },
                        dataType: 'json',
                        success: (data) => {
                            let datalist = $(listId).empty();
                            data.forEach((item) => {
                                $('<option>')
                                    .attr('value', item.display_name)
                                    .data('lat', item.lat)
                                    .data('lon', item.lon)
                                    .appendTo(datalist);
                            });
                        },
                        error: (xhr, status, err) => console.error("Suggestion error:", status, err, xhr.responseText)
                    });
                }

                onAddressChange(inputSelector, listSelector, type) {
                    let selectedOption = $(`${listSelector} option[value="${$(inputSelector).val()}"]`);
                    let lat = parseFloat(selectedOption.data('lat'));
                    let lng = parseFloat(selectedOption.data('lon'));

                    if (type === 'main') {
                        this.lat1 = lat; this.lng1 = lng;
                        this.marker1 = this.updateMarker(this.map1, this.marker1, lat, lng, "Starting Point");
                    } else {
                        this.lat2 = lat; this.lng2 = lng;
                        this.marker2 = this.updateMarker(this.map2, this.marker2, lat, lng, "Shipping Address");
                    }
                    this.getPlaceName(lat, lng, type);
                }

                updateMarker(map, marker, lat, lng, title) {
                    if (!map) return marker;
                    if (marker) marker.setMap(null);
                    let pos = new google.maps.LatLng(lat, lng);
                    marker = new google.maps.Marker({ position: pos, map, title });
                    map.setCenter(pos);
                    return marker;
                }

                getPlaceName(lat, lng, type = "main") {
                    $.ajax({
                        url: 'pages/supplier_ajax.php',
                        method: 'POST',
                        data: { action: 'get_place_name', lat, lng, type },
                        dataType: 'json',
                        success: (data) => {
                            if (!data || !data.display_name) return;
                            if (type === "main") {
                                $('#searchBox1').val(data.display_name);
                                $('#address').val(data.address.road || data.address.suburb || '');
                                $('#city').val(data.address.city || data.address.town || '');
                                $('#state').val(data.address.state || data.address.region || '');
                                $('#zip').val(data.address.postcode || '');
                                $('#lat').val(lat); $('#lng').val(lng);
                            } else {
                                $('#searchBox2').val(data.display_name);
                                $('#ship_address').val(data.address.road || data.address.suburb || '');
                                $('#ship_city').val(data.address.city || data.address.town || '');
                                $('#ship_state').val(data.address.state || data.address.region || '');
                                $('#ship_zip').val(data.address.postcode || '');
                                $('#ship_lat').val(lat); $('#ship_lng').val(lng);
                            }
                        },
                        error: () => console.error("Error retrieving place name")
                    });
                }

                initMap1() {
                    const lat = parseFloat($('#lat').val()) || 37.8393;
                    const lng = parseFloat($('#lng').val()) || -84.2700;

                    this.map1 = new google.maps.Map(document.getElementById("map1"), {
                        center: { lat: lat, lng: lng },
                        zoom: 13
                    });
                    this.marker1 = this.updateMarker(this.map1, this.marker1, lat, lng, "Starting Point");

                    google.maps.event.addListener(this.map1, 'click', (e) => {
                        this.lat1 = e.latLng.lat();
                        this.lng1 = e.latLng.lng();
                        this.marker1 = this.updateMarker(this.map1, this.marker1, this.lat1, this.lng1, "Starting Point");
                        this.getPlaceName(this.lat1, this.lng1, "main");
                    });
                }

                initMap2() {
                    const lat = parseFloat($('#ship_lat').val()) || 37.8393;
                    const lng = parseFloat($('#ship_lng').val()) || -84.2700;

                    console.log("Init Map2 with values:", lat, lng);

                    this.lat2 = lat;
                    this.lng2 = lng;

                    this.map2 = new google.maps.Map(document.getElementById("map2"), {
                        center: { lat: lat, lng: lng },
                        zoom: 13
                    });

                    this.marker2 = this.updateMarker(this.map2, this.marker2, lat, lng, "Shipping Address");

                    google.maps.event.addListener(this.map2, 'click', (e) => {
                        this.lat2 = e.latLng.lat();
                        this.lng2 = e.latLng.lng();
                        this.marker2 = this.updateMarker(this.map2, this.marker2, this.lat2, this.lng2, "Shipping Address");
                        this.getPlaceName(this.lat2, this.lng2, "ship");
                    });
                }


                loadGoogleMapsAPI() {
                    const script = document.createElement('script');
                    script.src = `https://maps.googleapis.com/maps/api/js?key=${this.apiKey}&callback=initDummy&libraries=places`;
                    script.async = true;
                    script.defer = true;
                    document.head.appendChild(script);
                }
            }

            // init
            const customerMaps = new CustomerMaps("<?= $google_api ?>");
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

    if ($action == 'customer_business_modal') {
        $customer_id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM customer WHERE customer_id = '$customer_id'";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_array($result)) {
            $customer_id = $row['customer_id'] ?? 0;
            $customer_first_name = $row['customer_first_name'] ?? '';
            $customer_last_name = $row['customer_last_name'] ?? '';
            $customer_business_name = $row['customer_business_name'] ?? '';
            $customer_business_website = $row['customer_business_website'] ?? '';
            $customer_type_id = $row['customer_type_id'] ?? '';
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
            $corpo_address = $row['corpo_address'] ?? '';
            $corpo_city = $row['corpo_city'] ?? '';
            $corpo_state = $row['corpo_state'] ?? '';
            $corpo_zip = $row['corpo_zip'] ?? '';
            $secondary_contact_name = $row['secondary_contact_name'] ?? '';
            $secondary_contact_phone = $row['secondary_contact_phone'] ?? '';
            $secondary_contact_email = $row['secondary_contact_email'] ?? '';
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
            $corpo_lat = !empty($row['corpo_lat']) ? $row['corpo_lat'] : 0;
            $corpo_lng = !empty($row['corpo_lng']) ? $row['corpo_lng'] : 0;
            $portal_access = $row['is_approved'] ?? 0;
            $different_ship_address = $row['different_ship_address'] ?? 0;
            $is_charge_net = $row['is_charge_net'] ?? 0;
            $username = $row['username'] ?? '';
            $password = $row['password'] ?? '';
            $is_contractor = $row['is_contractor'] ?? '';
            $is_corporate_parent = $row['is_corporate_parent'] ?? '';
            $is_bill_corpo_address = $row['is_bill_corpo_address'] ?? '';
            $corpo_parent_name = $row['corpo_parent_name'] ?? '';
            $corpo_phone_no = $row['corpo_phone_no'] ?? '';

            $payment_pickup   = $row['payment_pickup']   ?? 0;
            $payment_delivery = $row['payment_delivery'] ?? 0;
            $payment_cash     = $row['payment_cash']     ?? 0;
            $payment_check    = $row['payment_check']    ?? 0;
            $payment_card     = $row['payment_card']     ?? 0;

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

        <div class="modal fade" id="map1Modal" tabindex="-1" role="dialog" aria-labelledby="mapsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mapsModalLabel">Search Address</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="mapForm" class="form-horizontal">
                    <div class="modal-body">
                        <div class="mb-2">
                            <input id="searchBox1" class="form-control" list="address1-list" autocomplete="off">
                            <datalist id="address1-list"></datalist>
                        </div>
                        <div id="map1" class="map-container" style="height: 60vh; width: 100%;"></div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-actions">
                            <div class="card-body">
                                <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="map2Modal" tabindex="-1" role="dialog" aria-labelledby="mapsModalLabel2" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mapsModalLabel2">Search Shipping Address</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="mapForm2" class="form-horizontal">
                    <div class="modal-body">
                        <div class="mb-2">
                            <input id="searchBox2" class="form-control" list="address2-list" autocomplete="off">
                            <datalist id="address2-list"></datalist>
                        </div>
                        <div id="map2" class="map-container" style="height: 60vh; width: 100%;"></div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-actions">
                            <div class="card-body">
                                <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Contact Information</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Business Name</label>
                        <input type="text" id="customer_business_name" name="customer_business_name" class="form-control"
                            value="<?= $customer_business_name ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Business Website</label>
                        <input type="text" id="customer_business_website" name="customer_business_website" class="form-control"
                            value="<?= $customer_business_website ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4"></div>

                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Primary Contact Name</label>
                        <input type="text" id="customer_first_name" name="customer_first_name" class="form-control"
                            value="<?= $customer_first_name ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Primary Contact Phone</label>
                        <input type="text" id="contact_phone" name="contact_phone" class="form-control"
                            value="<?= $contact_phone ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Primary Contact Email</label>
                        <input type="text" id="contact_email" name="contact_email" class="form-control"
                            value="<?= $contact_email ?? '' ?>" />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Primary Contact Fax</label>
                        <input type="text" id="contact_fax" name="contact_fax" class="form-control"
                            value="<?= $contact_fax ?? '' ?>" />
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
                        <div class="mb-3">
                        <label class="form-label">Secondary Contact Email</label>
                        <input type="text" id="secondary_contact_email" name="secondary_contact_email" class="form-control"
                            value="<?= $secondary_contact_email ?? '' ?>" />
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Add Corporate/Parent Company Information</label>
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            
                            <div class="form-check">
                                <input class="form-check-input" 
                                        type="checkbox" 
                                        name="is_corporate_parent" 
                                        id="is_corporate_parent" 
                                        value="1" 
                                        <?= !empty($is_corporate_parent) && $is_corporate_parent == '1' ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="mb-3">
                        <label class="form-label">Corporate Name/Parent Company Name</label>
                        <input type="text" id="corpo_parent_name" name="corpo_parent_name" class="form-control"
                            value="<?= $corpo_parent_name ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Primary Contact Phone</label>
                        <input type="text" id="corpo_phone_no" name="corpo_phone_no" class="form-control"
                            value="<?= $corpo_phone_no ?? '' ?>" />
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Address</label>
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            
                            <div class="d-flex w-100">
                                <input type="text" id="corpo_address" name="corpo_address" class="form-control" value="<?= $corpo_address ?? '' ?>" list="address-data-list"/>
                                <datalist id="address-data-list"></datalist>
                                <button type="button" class="btn btn-primary py-1 ms-2 toggleElements" id="showMapsBtn" data-address="<?=$addressDetails ?? ''?>" style="border-radius: 10%;">Change</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">City</label>
                        <input type="text" id="corpo_city" name="corpo_city" class="form-control" value="<?= $corpo_city ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">State</label>
                        <input type="text" id="corpo_state" name="corpo_state" class="form-control" value="<?= $corpo_state ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Zip</label>
                        <input type="text" id="corpo_zip" name="corpo_zip" class="form-control" value="<?= $corpo_zip ?? '' ?>" />
                        </div>
                    </div>
                    <input type="hidden" id="corpo_lat" name="corpo_lat" class="form-control" value="<?= $corpo_lat ?? '' ?>" />
                    <input type="hidden" id="corpo_lng" name="corpo_lng" class="form-control" value="<?= $corpo_lng ?? '' ?>" />
                    
                    <div class="col-md-12">
                        <label class="form-label">Use Corporate/Parent Company Address for Billing address</label>
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            
                            <div class="form-check">
                                <input class="form-check-input" 
                                        type="checkbox" 
                                        name="is_bill_corpo_address" 
                                        id="is_bill_corpo_address" 
                                        value="1" 
                                        <?= !empty($is_bill_corpo_address) && $is_bill_corpo_address == '1' ? 'checked' : '' ?>>
                            </div>
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
                                <button type="button" class="btn btn-primary py-1 ms-2 toggleElements" id="showMapsBtn" data-address="<?=$addressDetails ?? ''?>" style="border-radius: 10%;">Change</button>
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
                                        <button type="button" class="btn btn-primary py-1 ms-2 toggleElements" id="showMapsShipBtn" style="border-radius: 10%;">Change</button>
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
                            <label class="form-label">Tax Exemption #</label>
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

                    <div class="col-12">
                        <div class="container mb-3">
                            <h5 class="mb-3 text-center">Allowable Payment Methods</h5>
                            <div class="row text-center">

                                <!-- Pay at Pick-Up -->
                                <div class="col">
                                    <label class="fw-bold d-block">Pay at Pick-Up</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_pickup" name="payment_pickup"
                                            <?= (($payment_pickup ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <!-- Pay at Delivery -->
                                <div class="col">
                                    <label class="fw-bold d-block">Pay at Delivery</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_delivery" name="payment_delivery"
                                            <?= (($payment_delivery ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <!-- Cash -->
                                <div class="col">
                                    <label class="fw-bold d-block">Cash</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_cash" name="payment_cash"
                                            <?= (($payment_cash ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <!-- Check -->
                                <div class="col">
                                    <label class="fw-bold d-block">Check</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_check" name="payment_check"
                                            <?= (($payment_check ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <!-- Credit/Debit Card -->
                                <div class="col">
                                    <label class="fw-bold d-block">Credit/Debit Card</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_card" name="payment_card"
                                            <?= (($payment_card ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                            </div>
                        </div>        
                    </div>

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
        <input type="hidden" id="customer_type_id" name="customer_type_id" class="form-control" value="<?= $customer_type_id ?? 0 ?>" />

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
            class CustomerMaps {
                constructor(apiKey) {
                    this.apiKey = apiKey;

                    function safeFloat(val, fallback) {
                        if (val === undefined || val === null || val === "") return fallback;
                        const num = parseFloat(val);
                        return isNaN(num) ? fallback : num;
                    }

                    const DEFAULT_LAT = 37.8393;
                    const DEFAULT_LNG = -84.2700;

                    this.lat1 = safeFloat($('#lat').val(), DEFAULT_LAT);
                    this.lng1 = safeFloat($('#lng').val(), DEFAULT_LNG);

                    this.lat2 = safeFloat($('#ship_lat').val(), DEFAULT_LAT);
                    this.lng2 = safeFloat($('#ship_lng').val(), DEFAULT_LNG);

                    this.map1 = null;
                    this.marker1 = null;
                    this.map2 = null;
                    this.marker2 = null;

                    this.debounce = (func, wait) => {
                        let timeout;
                        return (...args) => {
                            clearTimeout(timeout);
                            timeout = setTimeout(() => func.apply(this, args), wait);
                        };
                    };

                    $(document).ready(() => this.initUI());

                    this.loadGoogleMapsAPI();
                }

                initUI() {
                    $('#searchBox1').on('input', this.debounce(() => this.updateSuggestions('#searchBox1', '#address1-list'), 400));
                    $('#searchBox2').on('input', this.debounce(() => this.updateSuggestions('#searchBox2', '#address2-list'), 400));
                    $('#address').on('input', this.debounce(() => this.updateSuggestions('#address', '#address-data-list'), 400));

                    $('#searchBox1').on('change', () => this.onAddressChange('#searchBox1', '#address1-list', 'main'));
                    $('#searchBox2').on('change', () => this.onAddressChange('#searchBox2', '#address2-list', 'ship'));
                    $('#address').on('change', () => this.onAddressChange('#address', '#address-data-list', 'main'));

                    $('#map1Modal, #map2Modal').on('shown.bs.modal', (e) => {
                        if (e.target.id === 'map1Modal' && !this.map1) this.initMap1();
                        if (e.target.id === 'map2Modal' && !this.map2) this.initMap2();
                    });

                    $('#map1Modal, #map2Modal').on('hidden.bs.modal', () => $('#customerModal').modal('show'));

                    $(document).on('click', '#showMapsBtn', (e) => {
                        let address = $(e.currentTarget).data('address') || "";
                        $('#searchBox1').val(address).trigger(address ? 'change' : '');
                        $('#map1Modal').modal('show');
                    });

                    $(document).on('click', '#showMapsShipBtn', (e) => {
                        let address = $(e.currentTarget).data('address') || "";
                        $('#searchBox2').val(address).trigger(address ? 'change' : '');
                        $('#map2Modal').modal('show');
                    });
                }

                updateSuggestions(inputId, listId) {
                    let query = $(inputId).val();
                    if (query.length < 2) return;

                    $.ajax({
                        url: 'pages/supplier_ajax.php',
                        method: 'POST',
                        data: { action: 'search_address', query },
                        dataType: 'json',
                        success: (data) => {
                            let datalist = $(listId).empty();
                            data.forEach((item) => {
                                $('<option>')
                                    .attr('value', item.display_name)
                                    .data('lat', item.lat)
                                    .data('lon', item.lon)
                                    .appendTo(datalist);
                            });
                        },
                        error: (xhr, status, err) => console.error("Suggestion error:", status, err, xhr.responseText)
                    });
                }

                onAddressChange(inputSelector, listSelector, type) {
                    let selectedOption = $(`${listSelector} option[value="${$(inputSelector).val()}"]`);
                    let lat = parseFloat(selectedOption.data('lat'));
                    let lng = parseFloat(selectedOption.data('lon'));

                    if (type === 'main') {
                        this.lat1 = lat; this.lng1 = lng;
                        this.marker1 = this.updateMarker(this.map1, this.marker1, lat, lng, "Starting Point");
                    } else {
                        this.lat2 = lat; this.lng2 = lng;
                        this.marker2 = this.updateMarker(this.map2, this.marker2, lat, lng, "Shipping Address");
                    }
                    this.getPlaceName(lat, lng, type);
                }

                updateMarker(map, marker, lat, lng, title) {
                    if (!map) return marker;
                    if (marker) marker.setMap(null);
                    let pos = new google.maps.LatLng(lat, lng);
                    marker = new google.maps.Marker({ position: pos, map, title });
                    map.setCenter(pos);
                    return marker;
                }

                getPlaceName(lat, lng, type = "main") {
                    $.ajax({
                        url: 'pages/supplier_ajax.php',
                        method: 'POST',
                        data: { action: 'get_place_name', lat, lng, type },
                        dataType: 'json',
                        success: (data) => {
                            if (!data || !data.display_name) return;
                            if (type === "main") {
                                $('#searchBox1').val(data.display_name);
                                $('#address').val(data.address.road || data.address.suburb || '');
                                $('#city').val(data.address.city || data.address.town || '');
                                $('#state').val(data.address.state || data.address.region || '');
                                $('#zip').val(data.address.postcode || '');
                                $('#lat').val(lat); $('#lng').val(lng);
                            } else {
                                $('#searchBox2').val(data.display_name);
                                $('#ship_address').val(data.address.road || data.address.suburb || '');
                                $('#ship_city').val(data.address.city || data.address.town || '');
                                $('#ship_state').val(data.address.state || data.address.region || '');
                                $('#ship_zip').val(data.address.postcode || '');
                                $('#ship_lat').val(lat); $('#ship_lng').val(lng);
                            }
                        },
                        error: () => console.error("Error retrieving place name")
                    });
                }

                initMap1() {
                    const lat = parseFloat($('#lat').val()) || 37.8393;
                    const lng = parseFloat($('#lng').val()) || -84.2700;

                    this.map1 = new google.maps.Map(document.getElementById("map1"), {
                        center: { lat: lat, lng: lng },
                        zoom: 13
                    });
                    this.marker1 = this.updateMarker(this.map1, this.marker1, lat, lng, "Starting Point");

                    google.maps.event.addListener(this.map1, 'click', (e) => {
                        this.lat1 = e.latLng.lat();
                        this.lng1 = e.latLng.lng();
                        this.marker1 = this.updateMarker(this.map1, this.marker1, this.lat1, this.lng1, "Starting Point");
                        this.getPlaceName(this.lat1, this.lng1, "main");
                    });
                }

                initMap2() {
                    const lat = parseFloat($('#ship_lat').val()) || 37.8393;
                    const lng = parseFloat($('#ship_lng').val()) || -84.2700;

                    console.log("Init Map2 with values:", lat, lng);

                    this.lat2 = lat;
                    this.lng2 = lng;

                    this.map2 = new google.maps.Map(document.getElementById("map2"), {
                        center: { lat: lat, lng: lng },
                        zoom: 13
                    });

                    this.marker2 = this.updateMarker(this.map2, this.marker2, lat, lng, "Shipping Address");

                    google.maps.event.addListener(this.map2, 'click', (e) => {
                        this.lat2 = e.latLng.lat();
                        this.lng2 = e.latLng.lng();
                        this.marker2 = this.updateMarker(this.map2, this.marker2, this.lat2, this.lng2, "Shipping Address");
                        this.getPlaceName(this.lat2, this.lng2, "ship");
                    });
                }


                loadGoogleMapsAPI() {
                    const script = document.createElement('script');
                    script.src = `https://maps.googleapis.com/maps/api/js?key=${this.apiKey}&callback=initDummy&libraries=places`;
                    script.async = true;
                    script.defer = true;
                    document.head.appendChild(script);
                }
            }

            // init
            const customerMaps = new CustomerMaps("<?= $google_api ?>");
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

    if ($action == 'customer_farm_modal') {
        $customer_id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM customer WHERE customer_id = '$customer_id'";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_array($result)) {
            $customer_id = $row['customer_id'] ?? 0;
            $customer_first_name = $row['customer_first_name'] ?? '';
            $customer_last_name = $row['customer_last_name'] ?? '';
            $customer_business_name = $row['customer_business_name'] ?? '';
            $customer_type_id = $row['customer_type_id'] ?? '';
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

            $payment_pickup   = $row['payment_pickup']   ?? 0;
            $payment_delivery = $row['payment_delivery'] ?? 0;
            $payment_cash     = $row['payment_cash']     ?? 0;
            $payment_check    = $row['payment_check']    ?? 0;
            $payment_card     = $row['payment_card']     ?? 0;

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

        <div class="modal fade" id="map1Modal" tabindex="-1" role="dialog" aria-labelledby="mapsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mapsModalLabel">Search Address</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="mapForm" class="form-horizontal">
                    <div class="modal-body">
                        <div class="mb-2">
                            <input id="searchBox1" class="form-control" list="address1-list" autocomplete="off">
                            <datalist id="address1-list"></datalist>
                        </div>
                        <div id="map1" class="map-container" style="height: 60vh; width: 100%;"></div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-actions">
                            <div class="card-body">
                                <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="map2Modal" tabindex="-1" role="dialog" aria-labelledby="mapsModalLabel2" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mapsModalLabel2">Search Shipping Address</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="mapForm2" class="form-horizontal">
                    <div class="modal-body">
                        <div class="mb-2">
                            <input id="searchBox2" class="form-control" list="address2-list" autocomplete="off">
                            <datalist id="address2-list"></datalist>
                        </div>
                        <div id="map2" class="map-container" style="height: 60vh; width: 100%;"></div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-actions">
                            <div class="card-body">
                                <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Contact Information</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Farm Name</label>
                        <input type="text" id="customer_business_name" name="customer_business_name" class="form-control"
                            value="<?= $customer_business_name ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-4 mb-3">
                        <label class="form-label">Use Farm Name</label>
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="use_business_name" id="use_business_name" value=""><br>
                            </div>
                        </div>
                    </div>
                    <div class="col-4 mb-3"></div>

                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Farmer's First Name</label>
                        <input type="text" id="customer_first_name" name="customer_first_name" class="form-control"
                            value="<?= $customer_first_name ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Farmer's Last Name</label>
                        <input type="text" id="customer_last_name" name="customer_last_name" class="form-control"
                            value="<?= $customer_last_name ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4"></div>

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
                    <div class="col-md-4"></div>

                    <div class="col-md-4">
                        <label class="form-label">Preferred Method of Contact</label>
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" 
                                    type="radio" 
                                    name="primary_contact" 
                                    id="contact_phone_radio" 
                                    value="phone" 
                                    <?= (($primary_contact ?? 'phone') === 'phone' || ($primary_contact ?? '') == '2' || ($primary_contact ?? '') == '0') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="contact_phone_radio">Phone</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" 
                                    type="radio" 
                                    name="primary_contact" 
                                    id="contact_email_radio" 
                                    value="email" 
                                    <?= (($primary_contact ?? '') === 'email' || ($primary_contact ?? '') == '1') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="contact_email_radio">Email</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" 
                                    type="radio" 
                                    name="primary_contact" 
                                    id="contact_call_radio" 
                                    value="call" 
                                    <?= (($primary_contact ?? '') === 'call' || ($primary_contact ?? '') == '3') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="contact_call_radio">Call</label>
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
                                <button type="button" class="btn btn-primary py-1 ms-2 toggleElements" id="showMapsBtn" data-address="<?=$addressDetails ?? ''?>" style="border-radius: 10%;">Change</button>
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
                                        <button type="button" class="btn btn-primary py-1 ms-2 toggleElements" id="showMapsShipBtn" style="border-radius: 10%;">Change</button>
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
                            <label class="form-label">Tax Exemption #</label>
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

                    <div class="col-12">
                        <div class="container mb-3">
                            <h5 class="mb-3 text-center">Allowable Payment Methods</h5>
                            <div class="row text-center">

                                <!-- Pay at Pick-Up -->
                                <div class="col">
                                    <label class="fw-bold d-block">Pay at Pick-Up</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_pickup" name="payment_pickup"
                                            <?= (($payment_pickup ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <!-- Pay at Delivery -->
                                <div class="col">
                                    <label class="fw-bold d-block">Pay at Delivery</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_delivery" name="payment_delivery"
                                            <?= (($payment_delivery ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <!-- Cash -->
                                <div class="col">
                                    <label class="fw-bold d-block">Cash</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_cash" name="payment_cash"
                                            <?= (($payment_cash ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <!-- Check -->
                                <div class="col">
                                    <label class="fw-bold d-block">Check</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_check" name="payment_check"
                                            <?= (($payment_check ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <!-- Credit/Debit Card -->
                                <div class="col">
                                    <label class="fw-bold d-block">Credit/Debit Card</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_card" name="payment_card"
                                            <?= (($payment_card ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                            </div>
                        </div>        
                    </div>

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
        <input type="hidden" id="customer_type_id" name="customer_type_id" class="form-control" value="<?= $customer_type_id ?? 0 ?>" />

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
            class CustomerMaps {
                constructor(apiKey) {
                    this.apiKey = apiKey;

                    function safeFloat(val, fallback) {
                        if (val === undefined || val === null || val === "") return fallback;
                        const num = parseFloat(val);
                        return isNaN(num) ? fallback : num;
                    }

                    const DEFAULT_LAT = 37.8393;
                    const DEFAULT_LNG = -84.2700;

                    this.lat1 = safeFloat($('#lat').val(), DEFAULT_LAT);
                    this.lng1 = safeFloat($('#lng').val(), DEFAULT_LNG);

                    this.lat2 = safeFloat($('#ship_lat').val(), DEFAULT_LAT);
                    this.lng2 = safeFloat($('#ship_lng').val(), DEFAULT_LNG);

                    this.map1 = null;
                    this.marker1 = null;
                    this.map2 = null;
                    this.marker2 = null;

                    this.debounce = (func, wait) => {
                        let timeout;
                        return (...args) => {
                            clearTimeout(timeout);
                            timeout = setTimeout(() => func.apply(this, args), wait);
                        };
                    };

                    $(document).ready(() => this.initUI());

                    this.loadGoogleMapsAPI();
                }

                initUI() {
                    $('#searchBox1').on('input', this.debounce(() => this.updateSuggestions('#searchBox1', '#address1-list'), 400));
                    $('#searchBox2').on('input', this.debounce(() => this.updateSuggestions('#searchBox2', '#address2-list'), 400));
                    $('#address').on('input', this.debounce(() => this.updateSuggestions('#address', '#address-data-list'), 400));

                    $('#searchBox1').on('change', () => this.onAddressChange('#searchBox1', '#address1-list', 'main'));
                    $('#searchBox2').on('change', () => this.onAddressChange('#searchBox2', '#address2-list', 'ship'));
                    $('#address').on('change', () => this.onAddressChange('#address', '#address-data-list', 'main'));

                    $('#map1Modal, #map2Modal').on('shown.bs.modal', (e) => {
                        if (e.target.id === 'map1Modal' && !this.map1) this.initMap1();
                        if (e.target.id === 'map2Modal' && !this.map2) this.initMap2();
                    });

                    $('#map1Modal, #map2Modal').on('hidden.bs.modal', () => $('#customerModal').modal('show'));

                    $(document).on('click', '#showMapsBtn', (e) => {
                        let address = $(e.currentTarget).data('address') || "";
                        $('#searchBox1').val(address).trigger(address ? 'change' : '');
                        $('#map1Modal').modal('show');
                    });

                    $(document).on('click', '#showMapsShipBtn', (e) => {
                        let address = $(e.currentTarget).data('address') || "";
                        $('#searchBox2').val(address).trigger(address ? 'change' : '');
                        $('#map2Modal').modal('show');
                    });
                }

                updateSuggestions(inputId, listId) {
                    let query = $(inputId).val();
                    if (query.length < 2) return;

                    $.ajax({
                        url: 'pages/supplier_ajax.php',
                        method: 'POST',
                        data: { action: 'search_address', query },
                        dataType: 'json',
                        success: (data) => {
                            let datalist = $(listId).empty();
                            data.forEach((item) => {
                                $('<option>')
                                    .attr('value', item.display_name)
                                    .data('lat', item.lat)
                                    .data('lon', item.lon)
                                    .appendTo(datalist);
                            });
                        },
                        error: (xhr, status, err) => console.error("Suggestion error:", status, err, xhr.responseText)
                    });
                }

                onAddressChange(inputSelector, listSelector, type) {
                    let selectedOption = $(`${listSelector} option[value="${$(inputSelector).val()}"]`);
                    let lat = parseFloat(selectedOption.data('lat'));
                    let lng = parseFloat(selectedOption.data('lon'));

                    if (type === 'main') {
                        this.lat1 = lat; this.lng1 = lng;
                        this.marker1 = this.updateMarker(this.map1, this.marker1, lat, lng, "Starting Point");
                    } else {
                        this.lat2 = lat; this.lng2 = lng;
                        this.marker2 = this.updateMarker(this.map2, this.marker2, lat, lng, "Shipping Address");
                    }
                    this.getPlaceName(lat, lng, type);
                }

                updateMarker(map, marker, lat, lng, title) {
                    if (!map) return marker;
                    if (marker) marker.setMap(null);
                    let pos = new google.maps.LatLng(lat, lng);
                    marker = new google.maps.Marker({ position: pos, map, title });
                    map.setCenter(pos);
                    return marker;
                }

                getPlaceName(lat, lng, type = "main") {
                    $.ajax({
                        url: 'pages/supplier_ajax.php',
                        method: 'POST',
                        data: { action: 'get_place_name', lat, lng, type },
                        dataType: 'json',
                        success: (data) => {
                            if (!data || !data.display_name) return;
                            if (type === "main") {
                                $('#searchBox1').val(data.display_name);
                                $('#address').val(data.address.road || data.address.suburb || '');
                                $('#city').val(data.address.city || data.address.town || '');
                                $('#state').val(data.address.state || data.address.region || '');
                                $('#zip').val(data.address.postcode || '');
                                $('#lat').val(lat); $('#lng').val(lng);
                            } else {
                                $('#searchBox2').val(data.display_name);
                                $('#ship_address').val(data.address.road || data.address.suburb || '');
                                $('#ship_city').val(data.address.city || data.address.town || '');
                                $('#ship_state').val(data.address.state || data.address.region || '');
                                $('#ship_zip').val(data.address.postcode || '');
                                $('#ship_lat').val(lat); $('#ship_lng').val(lng);
                            }
                        },
                        error: () => console.error("Error retrieving place name")
                    });
                }

                initMap1() {
                    const lat = parseFloat($('#lat').val()) || 37.8393;
                    const lng = parseFloat($('#lng').val()) || -84.2700;

                    this.map1 = new google.maps.Map(document.getElementById("map1"), {
                        center: { lat: lat, lng: lng },
                        zoom: 13
                    });
                    this.marker1 = this.updateMarker(this.map1, this.marker1, lat, lng, "Starting Point");

                    google.maps.event.addListener(this.map1, 'click', (e) => {
                        this.lat1 = e.latLng.lat();
                        this.lng1 = e.latLng.lng();
                        this.marker1 = this.updateMarker(this.map1, this.marker1, this.lat1, this.lng1, "Starting Point");
                        this.getPlaceName(this.lat1, this.lng1, "main");
                    });
                }

                initMap2() {
                    const lat = parseFloat($('#ship_lat').val()) || 37.8393;
                    const lng = parseFloat($('#ship_lng').val()) || -84.2700;

                    console.log("Init Map2 with values:", lat, lng);

                    this.lat2 = lat;
                    this.lng2 = lng;

                    this.map2 = new google.maps.Map(document.getElementById("map2"), {
                        center: { lat: lat, lng: lng },
                        zoom: 13
                    });

                    this.marker2 = this.updateMarker(this.map2, this.marker2, lat, lng, "Shipping Address");

                    google.maps.event.addListener(this.map2, 'click', (e) => {
                        this.lat2 = e.latLng.lat();
                        this.lng2 = e.latLng.lng();
                        this.marker2 = this.updateMarker(this.map2, this.marker2, this.lat2, this.lng2, "Shipping Address");
                        this.getPlaceName(this.lat2, this.lng2, "ship");
                    });
                }


                loadGoogleMapsAPI() {
                    const script = document.createElement('script');
                    script.src = `https://maps.googleapis.com/maps/api/js?key=${this.apiKey}&callback=initDummy&libraries=places`;
                    script.async = true;
                    script.defer = true;
                    document.head.appendChild(script);
                }
            }

            // init
            const customerMaps = new CustomerMaps("<?= $google_api ?>");
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

    if ($action == 'customer_exempt_modal') {
        $customer_id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM customer WHERE customer_id = '$customer_id'";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_array($result)) {
            $customer_id = $row['customer_id'] ?? 0;
            $customer_first_name = $row['customer_first_name'] ?? '';
            $customer_last_name = $row['customer_last_name'] ?? '';
            $customer_business_name = $row['customer_business_name'] ?? '';
            $customer_business_website = $row['customer_business_website'] ?? '';
            $customer_type_id = $row['customer_type_id'] ?? '';
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
            $corpo_address = $row['corpo_address'] ?? '';
            $corpo_city = $row['corpo_city'] ?? '';
            $corpo_state = $row['corpo_state'] ?? '';
            $corpo_zip = $row['corpo_zip'] ?? '';
            $secondary_contact_name = $row['secondary_contact_name'] ?? '';
            $secondary_contact_phone = $row['secondary_contact_phone'] ?? '';
            $secondary_contact_email = $row['secondary_contact_email'] ?? '';
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
            $corpo_lat = !empty($row['corpo_lat']) ? $row['corpo_lat'] : 0;
            $corpo_lng = !empty($row['corpo_lng']) ? $row['corpo_lng'] : 0;
            $portal_access = $row['is_approved'] ?? 0;
            $different_ship_address = $row['different_ship_address'] ?? 0;
            $is_charge_net = $row['is_charge_net'] ?? 0;
            $username = $row['username'] ?? '';
            $password = $row['password'] ?? '';
            $is_contractor = $row['is_contractor'] ?? '';
            $is_corporate_parent = $row['is_corporate_parent'] ?? '';
            $is_bill_corpo_address = $row['is_bill_corpo_address'] ?? '';
            $corpo_parent_name = $row['corpo_parent_name'] ?? '';
            $corpo_phone_no = $row['corpo_phone_no'] ?? '';
            $tax_exempt_type = $row['tax_exempt_type'] ?? '';

            $payment_pickup   = $row['payment_pickup']   ?? 0;
            $payment_delivery = $row['payment_delivery'] ?? 0;
            $payment_cash     = $row['payment_cash']     ?? 0;
            $payment_check    = $row['payment_check']    ?? 0;
            $payment_card     = $row['payment_card']     ?? 0;

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

        <div class="modal fade" id="map1Modal" tabindex="-1" role="dialog" aria-labelledby="mapsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mapsModalLabel">Search Address</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="mapForm" class="form-horizontal">
                    <div class="modal-body">
                        <div class="mb-2">
                            <input id="searchBox1" class="form-control" list="address1-list" autocomplete="off">
                            <datalist id="address1-list"></datalist>
                        </div>
                        <div id="map1" class="map-container" style="height: 60vh; width: 100%;"></div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-actions">
                            <div class="card-body">
                                <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="map2Modal" tabindex="-1" role="dialog" aria-labelledby="mapsModalLabel2" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mapsModalLabel2">Search Shipping Address</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="mapForm2" class="form-horizontal">
                    <div class="modal-body">
                        <div class="mb-2">
                            <input id="searchBox2" class="form-control" list="address2-list" autocomplete="off">
                            <datalist id="address2-list"></datalist>
                        </div>
                        <div id="map2" class="map-container" style="height: 60vh; width: 100%;"></div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-actions">
                            <div class="card-body">
                                <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Contact Information</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" id="customer_business_name" name="customer_business_name" class="form-control"
                            value="<?= $customer_business_name ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Website</label>
                        <input type="text" id="customer_business_website" name="customer_business_website" class="form-control"
                            value="<?= $customer_business_website ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4"></div>

                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Primary Contact Name</label>
                        <input type="text" id="customer_first_name" name="customer_first_name" class="form-control"
                            value="<?= $customer_first_name ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Primary Contact Phone</label>
                        <input type="text" id="contact_phone" name="contact_phone" class="form-control"
                            value="<?= $contact_phone ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Primary Contact Email</label>
                        <input type="text" id="contact_email" name="contact_email" class="form-control"
                            value="<?= $contact_email ?? '' ?>" />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                        <label class="form-label">Primary Contact Fax</label>
                        <input type="text" id="contact_fax" name="contact_fax" class="form-control"
                            value="<?= $contact_fax ?? '' ?>" />
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
                        <div class="mb-3">
                        <label class="form-label">Secondary Contact Email</label>
                        <input type="text" id="secondary_contact_email" name="secondary_contact_email" class="form-control"
                            value="<?= $secondary_contact_email ?? '' ?>" />
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="container mb-3">
                            <h5 class="mb-3 text-center">Type of Tax Exempt Customer</h5>
                            <div class="row justify-content-center text-center">

                            <div class="col-2">
                                <label class="fw-bold d-block" for="tax_church">Church</label>
                                <div class="form-check d-flex justify-content-center">
                                <input class="form-check-input" type="radio" 
                                        id="tax_church" 
                                        name="tax_exempt_type" 
                                        value="church"
                                        <?php echo (($tax_exempt_type ?? '') === 'church') ? 'checked' : ''; ?>>
                                </div>
                            </div>

                            <div class="col-2">
                                <label class="fw-bold d-block" for="tax_school">School</label>
                                <div class="form-check d-flex justify-content-center">
                                <input class="form-check-input" type="radio" 
                                        id="tax_school" 
                                        name="tax_exempt_type" 
                                        value="school"
                                        <?php echo (($tax_exempt_type ?? '') === 'school') ? 'checked' : ''; ?>>
                                </div>
                            </div>

                            <div class="col-2">
                                <label class="fw-bold d-block" for="tax_municipal">Municipal</label>
                                <div class="form-check d-flex justify-content-center">
                                <input class="form-check-input" type="radio" 
                                        id="tax_municipal" 
                                        name="tax_exempt_type" 
                                        value="municipal"
                                        <?php echo (($tax_exempt_type ?? '') === 'municipal') ? 'checked' : ''; ?>>
                                </div>
                            </div>

                            </div>
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
                                <button type="button" class="btn btn-primary py-1 ms-2 toggleElements" id="showMapsBtn" data-address="<?=$addressDetails ?? ''?>" style="border-radius: 10%;">Change</button>
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
                                        <button type="button" class="btn btn-primary py-1 ms-2 toggleElements" id="showMapsShipBtn" style="border-radius: 10%;">Change</button>
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
                            <label class="form-label">Tax Exemption #</label>
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

                    <div class="col-12">
                        <div class="container mb-3">
                            <h5 class="mb-3 text-center">Allowable Payment Methods</h5>
                            <div class="row text-center">

                                <!-- Pay at Pick-Up -->
                                <div class="col">
                                    <label class="fw-bold d-block">Pay at Pick-Up</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_pickup" name="payment_pickup"
                                            <?= (($payment_pickup ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <!-- Pay at Delivery -->
                                <div class="col">
                                    <label class="fw-bold d-block">Pay at Delivery</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_delivery" name="payment_delivery"
                                            <?= (($payment_delivery ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <!-- Cash -->
                                <div class="col">
                                    <label class="fw-bold d-block">Cash</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_cash" name="payment_cash"
                                            <?= (($payment_cash ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <!-- Check -->
                                <div class="col">
                                    <label class="fw-bold d-block">Check</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_check" name="payment_check"
                                            <?= (($payment_check ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <!-- Credit/Debit Card -->
                                <div class="col">
                                    <label class="fw-bold d-block">Credit/Debit Card</label>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" 
                                            id="payment_card" name="payment_card"
                                            <?= (($payment_card ?? 0)== 1) ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                            </div>
                        </div>        
                    </div>

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
        <input type="hidden" id="customer_type_id" name="customer_type_id" class="form-control" value="<?= $customer_type_id ?? 0 ?>" />

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
            class CustomerMaps {
                constructor(apiKey) {
                    this.apiKey = apiKey;

                    function safeFloat(val, fallback) {
                        if (val === undefined || val === null || val === "") return fallback;
                        const num = parseFloat(val);
                        return isNaN(num) ? fallback : num;
                    }

                    const DEFAULT_LAT = 37.8393;
                    const DEFAULT_LNG = -84.2700;

                    this.lat1 = safeFloat($('#lat').val(), DEFAULT_LAT);
                    this.lng1 = safeFloat($('#lng').val(), DEFAULT_LNG);

                    this.lat2 = safeFloat($('#ship_lat').val(), DEFAULT_LAT);
                    this.lng2 = safeFloat($('#ship_lng').val(), DEFAULT_LNG);

                    this.map1 = null;
                    this.marker1 = null;
                    this.map2 = null;
                    this.marker2 = null;

                    this.debounce = (func, wait) => {
                        let timeout;
                        return (...args) => {
                            clearTimeout(timeout);
                            timeout = setTimeout(() => func.apply(this, args), wait);
                        };
                    };

                    $(document).ready(() => this.initUI());

                    this.loadGoogleMapsAPI();
                }

                initUI() {
                    $('#searchBox1').on('input', this.debounce(() => this.updateSuggestions('#searchBox1', '#address1-list'), 400));
                    $('#searchBox2').on('input', this.debounce(() => this.updateSuggestions('#searchBox2', '#address2-list'), 400));
                    $('#address').on('input', this.debounce(() => this.updateSuggestions('#address', '#address-data-list'), 400));

                    $('#searchBox1').on('change', () => this.onAddressChange('#searchBox1', '#address1-list', 'main'));
                    $('#searchBox2').on('change', () => this.onAddressChange('#searchBox2', '#address2-list', 'ship'));
                    $('#address').on('change', () => this.onAddressChange('#address', '#address-data-list', 'main'));

                    $('#map1Modal, #map2Modal').on('shown.bs.modal', (e) => {
                        if (e.target.id === 'map1Modal' && !this.map1) this.initMap1();
                        if (e.target.id === 'map2Modal' && !this.map2) this.initMap2();
                    });

                    $('#map1Modal, #map2Modal').on('hidden.bs.modal', () => $('#customerModal').modal('show'));

                    $(document).on('click', '#showMapsBtn', (e) => {
                        let address = $(e.currentTarget).data('address') || "";
                        $('#searchBox1').val(address).trigger(address ? 'change' : '');
                        $('#map1Modal').modal('show');
                    });

                    $(document).on('click', '#showMapsShipBtn', (e) => {
                        let address = $(e.currentTarget).data('address') || "";
                        $('#searchBox2').val(address).trigger(address ? 'change' : '');
                        $('#map2Modal').modal('show');
                    });
                }

                updateSuggestions(inputId, listId) {
                    let query = $(inputId).val();
                    if (query.length < 2) return;

                    $.ajax({
                        url: 'pages/supplier_ajax.php',
                        method: 'POST',
                        data: { action: 'search_address', query },
                        dataType: 'json',
                        success: (data) => {
                            let datalist = $(listId).empty();
                            data.forEach((item) => {
                                $('<option>')
                                    .attr('value', item.display_name)
                                    .data('lat', item.lat)
                                    .data('lon', item.lon)
                                    .appendTo(datalist);
                            });
                        },
                        error: (xhr, status, err) => console.error("Suggestion error:", status, err, xhr.responseText)
                    });
                }

                onAddressChange(inputSelector, listSelector, type) {
                    let selectedOption = $(`${listSelector} option[value="${$(inputSelector).val()}"]`);
                    let lat = parseFloat(selectedOption.data('lat'));
                    let lng = parseFloat(selectedOption.data('lon'));

                    if (type === 'main') {
                        this.lat1 = lat; this.lng1 = lng;
                        this.marker1 = this.updateMarker(this.map1, this.marker1, lat, lng, "Starting Point");
                    } else {
                        this.lat2 = lat; this.lng2 = lng;
                        this.marker2 = this.updateMarker(this.map2, this.marker2, lat, lng, "Shipping Address");
                    }
                    this.getPlaceName(lat, lng, type);
                }

                updateMarker(map, marker, lat, lng, title) {
                    if (!map) return marker;
                    if (marker) marker.setMap(null);
                    let pos = new google.maps.LatLng(lat, lng);
                    marker = new google.maps.Marker({ position: pos, map, title });
                    map.setCenter(pos);
                    return marker;
                }

                getPlaceName(lat, lng, type = "main") {
                    $.ajax({
                        url: 'pages/supplier_ajax.php',
                        method: 'POST',
                        data: { action: 'get_place_name', lat, lng, type },
                        dataType: 'json',
                        success: (data) => {
                            if (!data || !data.display_name) return;
                            if (type === "main") {
                                $('#searchBox1').val(data.display_name);
                                $('#address').val(data.address.road || data.address.suburb || '');
                                $('#city').val(data.address.city || data.address.town || '');
                                $('#state').val(data.address.state || data.address.region || '');
                                $('#zip').val(data.address.postcode || '');
                                $('#lat').val(lat); $('#lng').val(lng);
                            } else {
                                $('#searchBox2').val(data.display_name);
                                $('#ship_address').val(data.address.road || data.address.suburb || '');
                                $('#ship_city').val(data.address.city || data.address.town || '');
                                $('#ship_state').val(data.address.state || data.address.region || '');
                                $('#ship_zip').val(data.address.postcode || '');
                                $('#ship_lat').val(lat); $('#ship_lng').val(lng);
                            }
                        },
                        error: () => console.error("Error retrieving place name")
                    });
                }

                initMap1() {
                    const lat = parseFloat($('#lat').val()) || 37.8393;
                    const lng = parseFloat($('#lng').val()) || -84.2700;

                    this.map1 = new google.maps.Map(document.getElementById("map1"), {
                        center: { lat: lat, lng: lng },
                        zoom: 13
                    });
                    this.marker1 = this.updateMarker(this.map1, this.marker1, lat, lng, "Starting Point");

                    google.maps.event.addListener(this.map1, 'click', (e) => {
                        this.lat1 = e.latLng.lat();
                        this.lng1 = e.latLng.lng();
                        this.marker1 = this.updateMarker(this.map1, this.marker1, this.lat1, this.lng1, "Starting Point");
                        this.getPlaceName(this.lat1, this.lng1, "main");
                    });
                }

                initMap2() {
                    const lat = parseFloat($('#ship_lat').val()) || 37.8393;
                    const lng = parseFloat($('#ship_lng').val()) || -84.2700;

                    console.log("Init Map2 with values:", lat, lng);

                    this.lat2 = lat;
                    this.lng2 = lng;

                    this.map2 = new google.maps.Map(document.getElementById("map2"), {
                        center: { lat: lat, lng: lng },
                        zoom: 13
                    });

                    this.marker2 = this.updateMarker(this.map2, this.marker2, lat, lng, "Shipping Address");

                    google.maps.event.addListener(this.map2, 'click', (e) => {
                        this.lat2 = e.latLng.lat();
                        this.lng2 = e.latLng.lng();
                        this.marker2 = this.updateMarker(this.map2, this.marker2, this.lat2, this.lng2, "Shipping Address");
                        this.getPlaceName(this.lat2, this.lng2, "ship");
                    });
                }


                loadGoogleMapsAPI() {
                    const script = document.createElement('script');
                    script.src = `https://maps.googleapis.com/maps/api/js?key=${this.apiKey}&callback=initDummy&libraries=places`;
                    script.async = true;
                    script.defer = true;
                    document.head.appendChild(script);
                }
            }

            // init
            const customerMaps = new CustomerMaps("<?= $google_api ?>");
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

    mysqli_close($conn);
}
?>
