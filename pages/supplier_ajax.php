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
        $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
        $supplier_name = mysqli_real_escape_string($conn, $_POST['supplier_name']);
        $supplier_website = mysqli_real_escape_string($conn, $_POST['supplier_website']);
        $supplier_type = mysqli_real_escape_string($conn, $_POST['supplier_type']);
        $supplier_colors = isset($_POST['supplier_color']) ? $_POST['supplier_color'] : [];
        $json_supplier_colors = json_encode($supplier_colors);
        $supplier_code = mysqli_real_escape_string($conn, $_POST['supplier_code']);
        $supplier_paint_id = mysqli_real_escape_string($conn, $_POST['supplier_paint_id']);
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
                $updateQuery = "UPDATE supplier SET supplier_name = '$supplier_name', supplier_website = '$supplier_website', supplier_type = '$supplier_type', contact_name = '$contact_name', contact_email = '$contact_email', contact_phone = '$contact_phone', contact_fax = '$contact_fax', secondary_name = '$secondary_name', secondary_phone = '$secondary_phone', secondary_email = '$secondary_email', address = '$address', last_ordered_date = '$last_ordered_date', freight_rate = '$freight_rate', payment_terms = '$payment_terms', comment = '$comment', last_edit = NOW(), edited_by = '$userid', supplier_code = '$supplier_code', supplier_paint_id = '$supplier_paint_id' WHERE supplier_id = '$supplier_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "success_update";
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
                $insertQuery = "INSERT INTO supplier (supplier_name, supplier_website, supplier_type, contact_name, contact_email, contact_phone, contact_fax, secondary_name, secondary_phone, secondary_email, address, last_ordered_date, freight_rate, payment_terms, comment, added_date, added_by, supplier_code, supplier_paint_id) VALUES ('$supplier_name', '$supplier_website', '$supplier_type', '$contact_name', '$contact_email', '$contact_phone', '$contact_fax', '$secondary_name', '$secondary_phone', '$secondary_email', '$address', '$last_ordered_date', '$freight_rate', '$payment_terms', '$comment', NOW(), '$userid', '$supplier_code', '$supplier_paint_id')";
                if (mysqli_query($conn, $insertQuery)) {
                    $supplier_id = $conn->insert_id;
                    echo "success_add";
                } else {
                    echo "Error adding supplier: " . mysqli_error($conn);
                }
            }
        }

        if (!empty($supplier_colors)) {
            $deleteQuery = "DELETE FROM supplier_color WHERE supplierid = '$supplier_id'";
            if (!mysqli_query($conn, $deleteQuery)) {
                echo "Error deleting colors for supplierid $supplier_id: " . mysqli_error($conn);
                exit;
            }
        
            foreach ($supplier_colors as $color) {
                list($color_name, $color_code) = explode('|', $color);
                $insertQuery = "INSERT INTO supplier_color (supplierid, color, color_code, added_date, added_by) 
                                VALUES ('$supplier_id', '$color_name', '$color_code', NOW(), '$userid')";
                if (!mysqli_query($conn, $insertQuery)) {
                    echo "Error adding color: " . mysqli_error($conn);
                    exit;
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
            $newFileName = ($fileName) . '.' . $fileExtension;
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
        }
            ?>
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
                </div>

                <div class="row pt-3">
                <div class="col-md-12">
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
                <div class="col-md-12">
                    <div class="mb-3">
                        <div class="col-md-12 d-flex justify-content-between align-items-center">
                            <label class="form-label">Supplier Packs</label>
                            <a href="?page=supplier_pack&supplier_id=<?=$supplier_id?>" target="_blank" class="text-decoration-none toggleElements">Edit Packs</a>
                        </div>
                        <div id="pack_upd" class="bg-light p-3 rounded">
                            
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
                <div class="col-md-12">
                    <div class="mb-3">
                        <div class="col-md-12 d-flex justify-content-between align-items-center">
                            <label class="form-label">Supplier Cases</label>
                            <a href="?page=supplier_case&supplier_id=<?=$supplier_id?>" target="_blank" class="text-decoration-none toggleElements">Edit Cases</a>
                        </div>
                        <div id="case_upd" class="bg-light p-3 rounded">
                            
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
                </div>

                <div class="row pt-3">
                <div class="col-md-6">
                    <div class="mb-3">
                    <label class="form-label">Supplier Code</label>
                    <input type="text" id="supplier_code" name="supplier_code" class="form-control" value="<?= $row['supplier_code'] ?? "" ?>" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                    <label class="form-label">Supplier Paint ID</label>
                    <input type="text" id="supplier_paint_id" name="supplier_paint_id" class="form-control"  />
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
            <script>
                $(document).ready(function () {
                    $(".phone-inputmask").inputmask("(999) 999-9999");

                    $(".supplier_color").each(function () {
                        let parentContainer = $(this).parent(); // Get the parent container
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
