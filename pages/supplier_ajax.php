<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
        $supplier_name = mysqli_real_escape_string($conn, $_POST['supplier_name']);
        $supplier_website = mysqli_real_escape_string($conn, $_POST['supplier_website']);
        $supplier_type = mysqli_real_escape_string($conn, $_POST['supplier_type']);
        $contact_name = mysqli_real_escape_string($conn, $_POST['contact_name']);
        $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email']);
        $contact_phone = mysqli_real_escape_string($conn, $_POST['contact_phone']);
        $contact_fax = mysqli_real_escape_string($conn, $_POST['contact_fax']);
        $secondary_name = mysqli_real_escape_string($conn, $_POST['secondary_name']);
        $secondary_phone = mysqli_real_escape_string($conn, $_POST['secondary_phone']);
        $secondary_email = mysqli_real_escape_string($conn, $_POST['secondary_email']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $last_ordered_date = mysqli_real_escape_string($conn, $_POST['last_ordered_date']);
        $freight_rate = mysqli_real_escape_string($conn, $_POST['freight_rate']);
        $payment_terms = mysqli_real_escape_string($conn, $_POST['payment_terms']);
        $comment = mysqli_real_escape_string($conn, $_POST['comment']);

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);
    
        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM supplier WHERE supplier_id = '$supplier_id'";
        $result = mysqli_query($conn, $checkQuery);
        $isInsert = false;
        if (mysqli_num_rows($result) > 0) {
            $isInsert = false;
            $row = mysqli_fetch_assoc($result);
            $current_supplier_name = $row['supplier_name'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($supplier_name != $current_supplier_name) {
                $checkSupplierName = "SELECT * FROM supplier WHERE supplier_name like '%$supplier_name%'";
                $resultSupplierName = mysqli_query($conn, $checkSupplierName);
                if (mysqli_num_rows($resultSupplierName) > 0) {
                    $duplicates[] = "Supplier Name";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE supplier SET supplier_name = '$supplier_name', supplier_website = '$supplier_website', supplier_type = '$supplier_type', contact_name = '$contact_name', contact_email = '$contact_email', contact_phone = '$contact_phone', contact_fax = '$contact_fax', secondary_name = '$secondary_name', secondary_phone = '$secondary_phone', secondary_email = '$secondary_email', address = '$address', last_ordered_date = '$last_ordered_date', freight_rate = '$freight_rate', payment_terms = '$payment_terms', comment = '$comment', last_edit = NOW(), edited_by = '$userid' WHERE supplier_id = '$supplier_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "Supplier updated successfully.";
                } else {
                    echo "Error updating supplier: " . mysqli_error($conn);
                }
            }

            
        } else {
            $isInsert = true;
            $row = mysqli_fetch_assoc($result);
            $current_supplier_name = $row['supplier_name'] ?? '';

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($supplier_name != $current_supplier_name) {
                $checkSupplierName = "SELECT * FROM supplier WHERE supplier_name like '%$supplier_name%'";
                $resultSupplierName = mysqli_query($conn, $checkSupplierName);
                if (mysqli_num_rows($resultSupplierName) > 0) {
                    $duplicates[] = "Supplier Name";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with insert
                $insertQuery = "INSERT INTO supplier (supplier_name, supplier_website, supplier_type, contact_name, contact_email, contact_phone, contact_fax, secondary_name, secondary_phone, secondary_email, address, last_ordered_date, freight_rate, payment_terms, comment, added_date, added_by) VALUES ('$supplier_name', '$supplier_website', '$supplier_type', '$contact_name', '$contact_email', '$contact_phone', '$contact_fax', '$secondary_name', '$secondary_phone', '$secondary_email', '$address', '$last_ordered_date', '$freight_rate', '$payment_terms', '$comment', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    $supplier_id = $conn->insert_id;
                    echo "New supplier added successfully.";
                } else {
                    echo "Error adding supplier: " . mysqli_error($conn);
                }
            }
        }

        $message = "";
        if (isset($_FILES['logo_path']) && $_FILES['logo_path']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['logo_path']['tmp_name'];
            $fileName = $_FILES['logo_path']['name'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            $uploadFileDir = '../images/supplier/';
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = $uploadFileDir . $newFileName;

            $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
            if (in_array($fileExtension, $allowedfileExtensions)) {
                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    $logo_path = mysqli_real_escape_string($conn, $dest_path);
                    $sql = "UPDATE supplier SET logo_path='$logo_path' WHERE supplier_id='$supplier_id'";
                    if (!$conn->query($sql)) {
                        echo "Error updating record: " . $conn->error;
                    }
                } else {
                    $message = 'Error moving the file to the upload directory.';
                }
            } else {
                $message = 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
            }
        } else {
            if($isInsert){
                $sql = "UPDATE supplier SET logo_path='images/supplier/logo.jpg' WHERE supplier_id='$supplier_id'";
                if (!$conn->query($sql)) {
                    echo "Error updating record: " . $conn->error;
                }
            }
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
    

    if ($action == "fetch_modal") {
        $supplier_id = mysqli_real_escape_string($conn, $_POST['id']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM supplier WHERE supplier_id = '$supplier_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            
            ?>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">
                            Update Supplier
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="update_supplier" class="form-horizontal">
                        <div class="modal-body">
                            <input type="hidden" id="supplier_id" name="supplier_id" class="form-control" value="<?= $row['supplier_id'] ?? "" ?>" />

                            <div class="row">
                                <div class="card-body p-0">
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
                            </div>
                        
                            <div class="row pt-3">
                            <div class="col-md-12">
                                <div class="mb-3">
                                <label class="form-label">Supplier Name</label>
                                <input type="text" id="supplier_name" name="supplier_name" class="form-control"  value="<?= $row['supplier_name'] ?? "" ?>"/>
                                </div>
                            </div>
                            </div>

                            <div class="row pt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Supplier Website</label>
                                <input type="text" id="supplier_website" name="supplier_website" class="form-control" value="<?= $row['supplier_website'] ?? "" ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Supplier Type</label>
                                <select id="supplier_type" class="form-control" name="supplier_type">
                                    <option value="/">Select One...</option>
                                    <?php
                                    $query_roles = "SELECT * FROM supplier_type WHERE hidden = '0'";
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
                            </div>

                            

                            <div class="row pt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Contact Name</label>
                                <input type="text" id="contact_name" name="contact_name" class="form-control"  value="<?= $row['contact_name'] ?? "" ?>"/>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Contact Email</label>
                                <input type="text" id="contact_email" name="contact_email" class="form-control" value="<?= $row['contact_email'] ?? "" ?>" />
                                </div>
                            </div>
                            </div>

                            <div class="row pt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" id="contact_phone" name="contact_phone" class="form-control phone-inputmask" value="<?= $row['contact_phone'] ?? "" ?>" />
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Contact Fax</label>
                                <input type="text" id="contact_fax" name="contact_fax" class="form-control phone-inputmask" value="<?= $row['contact_fax'] ?? "" ?>" />
                                </div>
                            </div>
                            </div>


                            <div class="mb-3">
                            <label class="form-label">Secondary Name</label>
                            <input type="text" id="secondary_name" name="secondary_name" class="form-control" value="<?= $row['secondary_name'] ?? "" ?>" />
                            </div>

                            <div class="row pt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Secondary Phone</label>
                                <input type="text" id="secondary_phone" name="secondary_phone" class="form-control phone-inputmask" value="<?= $row['secondary_phone'] ?? "" ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Secondary Email</label>
                                <input type="text" id="secondary_email" name="secondary_email" class="form-control" value="<?= $row['secondary_email'] ?? "" ?>" />
                                </div>
                            </div>
                            </div>

                            <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" id="address" name="address" class="form-control" value="<?= $row['address'] ?? "" ?>" />
                            </div>

                            <div class="row pt-3">
                            <div class="col-md-4">
                                <div class="mb-3">
                                <label class="form-label">Last Ordered Date</label>
                                <input type="date" id="last_ordered_date" name="last_ordered_date" class="form-control" value="<?= $row['last_ordered_date'] ?? "" ?>" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                <label class="form-label">FreightRate</label>
                                <input type="text" id="freight_rate" name="freight_rate" class="form-control" value="<?= $row['freight_rate'] ?? "" ?>" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                <label class="form-label">PaymentTerms</label>
                                <input type="text" id="payment_terms" name="payment_terms" class="form-control" value="<?= $row['payment_terms'] ?? "" ?>" />
                                </div>
                            </div>
                            </div>

                            <div class="mb-3">
                            <label class="form-label">Comment</label>
                            <textarea class="form-control" id="comment" name="comment" rows="5"><?= trim($row['comment'] ?? "") ?></textarea>
                            </div>

                            

                            
                        </div>
                        <div class="modal-footer">
                            <div class="form-actions">
                                <div class="card-body">
                                    <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                                    <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <script>
                $(".phone-inputmask").inputmask("(999) 999-9999");
            </script>
            <?php
        }
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
    
    
    
    mysqli_close($conn);
}
?>
