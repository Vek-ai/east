<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = 'test';
//4 = TRIM
$trim_id = 4;
$category_id = 4;

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
        
        $fields = [];
        foreach ($_POST as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
        
            $escapedValue = mysqli_real_escape_string($conn, $value);
        
            if ($key != 'product_id') {
                $fields[$key] = $escapedValue;
            }
        
            if ($key == 'cost') {
                $fields['unit_price'] = $escapedValue;
            }

            if ($key == 'retail_cost') {
                $fields['retail_cost'] = $escapedValue;
            }
        }
        
        $checkQuery = "SELECT * FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $checkQuery);
        
        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE product SET ";
            
            foreach ($fields as $column => $value) {
                $columnExists = mysqli_query($conn, "SHOW COLUMNS FROM product LIKE '$column'");
                if (mysqli_num_rows($columnExists) > 0) {
                    $updateQuery .= "$column = '$value', ";
                }
            }
            
            $updateQuery = rtrim($updateQuery, ", ");
            $updateQuery .= " WHERE product_id = '$product_id'";
            
            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating product: " . mysqli_error($conn);
            }
        } else {
            $columns = [];
            $values = [];
            
            foreach ($fields as $column => $value) {
                $columnExists = mysqli_query($conn, "SHOW COLUMNS FROM product LIKE '$column'");
                if (mysqli_num_rows($columnExists) > 0) {
                    $columns[] = $column;
                    $values[] = "'$value'";
                }
            }
            
            $columnsStr = implode(", ", $columns);
            $valuesStr = implode(", ", $values);
            
            $insertQuery = "INSERT INTO product (product_id, $columnsStr) VALUES ('$product_id', $valuesStr)";
            
            if (mysqli_query($conn, $insertQuery)) {
                $product_id = $conn->insert_id;

                $sql = "UPDATE product SET main_image='images/product/product.jpg' WHERE product_id='$product_id'";
                if (!$conn->query($sql)) {
                    echo "Error updating record: " . $conn->error;
                }

                echo "success_add";
            } else {
                echo "Error adding product: " . mysqli_error($conn);
            }
        }    

        if (!empty($_FILES['picture_path']['name'][0])) {
            if (is_array($_FILES['picture_path']['name']) && count($_FILES['picture_path']['name']) > 0) {
                $uploadFileDir = '../images/product/';
                
                for ($i = 0; $i < count($_FILES['picture_path']['name']); $i++) {
                    $fileTmpPath = $_FILES['picture_path']['tmp_name'][$i];
                    $fileName = $_FILES['picture_path']['name'][$i];
                    
                    if (empty($fileName)) {
                        continue;
                    }
        
                    $fileNameCmps = explode(".", $fileName);
                    $fileExtension = strtolower(end($fileNameCmps));
                    
                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                    $dest_path = $uploadFileDir . $newFileName;
        
                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $picture_path = mysqli_real_escape_string($conn, $dest_path);
        
                        if ($i == 0) {
                            $sql = "UPDATE product SET main_image='images/product/$newFileName' WHERE product_id='$product_id'";
                            if (!$conn->query($sql)) {
                                echo "Error updating record: " . $conn->error;
                            }
                        }
        
                        $sql = "INSERT INTO product_images (productid, image_url) VALUES ('$product_id', 'images/product/$newFileName')";
                        if (!$conn->query($sql)) {
                            echo "Error updating record: " . $conn->error;
                        }
                    } else {
                        echo 'Error moving the file to the upload directory.';
                    }
                }
            }
        }
    }

    if ($action == "fetch_view_modal") {
        $product_id = mysqli_real_escape_string($conn, $_POST['id']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            
            ?>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">
                            Add Product
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div id="update_product" class="form-horizontal">
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body">

                                    <div class="card card-body">
                                        <h4 class="card-title text-center">Product Image</h4>
                                        <div class="row pt-3">
                                            <?php
                                            $query_img = "SELECT * FROM product_images WHERE productid = '$product_id'";
                                            $result_img = mysqli_query($conn, $query_img); 
                                            if(mysqli_num_rows($result_img) > 0){
                                                while ($row_img = mysqli_fetch_array($result_img)) {
                                                ?>
                                                <div class="col-md">
                                                    <img src="<?= $row_img['image_url'] ?>" class="img-fluid" alt="Product Image" />
                                                </div>
                                                <?php
                                                }
                                            }else{
                                            ?>
                                            <p class="mb-0 fs-3 text-center">No image found.</p>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Product Name:</label>
                                                <p><?= $row['product_item'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Product SKU:</label>
                                                <p><?= $row['product_sku'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Product Category:</label>
                                                <p><?= getProductCategoryName($row['product_category']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Product Line:</label>
                                                <p><?= getProductLineName($row['product_line']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Product Type:</label>
                                                <p><?= getProductTypeName($row['product_type']) ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Description:</label>
                                        <p><?= $row['description'] ?></p>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Correlated Products:</label>
                                            <ul>
                                                <?php
                                                $correlated_product_ids = [];
                                                $product_id = mysqli_real_escape_string($conn, $row['product_id']);
                                                $query_correlated = "SELECT correlated_id FROM correlated_product WHERE main_correlated_product_id = '$product_id'";
                                                $result_correlated = mysqli_query($conn, $query_correlated);
                                                
                                                while ($row_correlated = mysqli_fetch_assoc($result_correlated)) {
                                                    $correlated_product_ids[] = $row_correlated['correlated_id'];
                                                }
                                                foreach ($correlated_product_ids as $correlated_id) {
                                                    // Assuming you fetch the correlated product name
                                                    echo "<li>" .getProductName($correlated_id) ."</li>";
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Stock Type:</label>
                                                <p><?= getStockTypeName($row['stock_type']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Material:</label>
                                                <p><?= $row['material'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Dimensions:</label>
                                                <p><?= $row['dimensions'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Thickness:</label>
                                                <p><?= $row['thickness'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Gauge:</label>
                                                <p><?= getGaugeName($row['gauge']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Grade:</label>
                                                <p><?= getGradeName($row['grade']) ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Color:</label>
                                                <p><?= getColorName($row['color']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Paint Provider:</label>
                                                <p><?= getPaintProviderName($row['paint_provider']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Coating:</label>
                                                <p><?= $row['coating'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Warranty Type:</label>
                                                <p><?= getWarrantyTypeName($row['warranty_type']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Profile:</label>
                                                <p><?= getProfileTypeName($row['profile']) ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Width:</label>
                                                <p><?= $row['width'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Length:</label>
                                                <p><?= $row['length'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Weight:</label>
                                                <p><?= $row['weight'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Unit of Measure:</label>
                                                <p><?= $row['unit_of_measure'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Unit Price:</label>
                                                <p><?= $row['unit_price'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Unit Cost:</label>
                                                <p><?= $row['unit_cost'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Unit Gross Margin:</label>
                                                <p><?= $row['unit_gross_margin'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Usage:</label>
                                                <p><?= $row['product_usage'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">UPC:</label>
                                                <p><?= $row['upc'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-1">
                                                <label class="form-label">Sold By Feet:</label>
                                                <p><?= $row['sold_by_feet'] == 1 ? 'Yes' : 'No' ?></p>
                                            </div>
                                            <div class="mb-1">
                                                <label class="form-label">Standing Seam Panel:</label>
                                                <p><?= $row['standing_seam'] == 1 ? 'Yes' : 'No' ?></p>
                                            </div>
                                            <div class="mb-1">
                                                <label class="form-label">Board & Batten Panel:</label>
                                                <p><?= $row['board_batten'] == 1 ? 'Yes' : 'No' ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Comment:</label>
                                        <p><?= $row['comment'] ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.modal-content -->
            </div>
            <script>
                $(document).ready(function() {
                    let uploadedUpdateFiles = [];

                    $('#myUpdateDropzone').dropzone({
                        addRemoveLinks: true,
                        dictRemoveFile: "X",
                        init: function() {
                            this.on("addedfile", function(file) {
                                uploadedUpdateFiles.push(file);
                                updateFileInput2();
                                displayFileNames2()
                            });

                            this.on("removedfile", function(file) {
                                uploadedUpdateFiles = uploadedUpdateFiles.filter(f => f.name !== file.name);
                                updateFileInput2();
                                displayFileNames2()
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

                    function displayFileNames2() {
                        let files = document.getElementById('picture_path_update').files;
                        let fileNames = '';

                        if (files.length > 0) {
                            for (let i = 0; i < files.length; i++) {
                                let file = files[i];
                                fileNames += `<p>${file.name}</p>`;
                            }
                        } else {
                            fileNames = '<p>No files selected</p>';
                        }

                        console.log(fileNames);
                    }
                });

            </script>

            <?php
        }
    } 

    if ($action == "remove_image") {
        $image_id = $_POST['image_id'];
    
        $delete_query = "DELETE FROM product_images WHERE prodimgid = '$image_id'";
        if (mysqli_query($conn, $delete_query)) {
            /* if (file_exists($image_url)) {
                unlink($image_url);
            } */
            echo 'success';
        } else {
            echo "Error removing image: " . mysqli_error($conn);
        }
    }

    if ($action == "change_status") {
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product SET status = '$new_status' WHERE product_id = '$product_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }

    if ($action == 'hide_category') {
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
        $query = "UPDATE product SET hidden='1' WHERE product_id='$product_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'fetch_product_fields') {
        $product_category_id = mysqli_real_escape_string($conn, $_POST['product_category_id']);
        $query = "SELECT * FROM product_fields WHERE product_category_id='$product_category_id'";
        $result = mysqli_query($conn, $query);
    
        if ($result) {
            $fields = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $fields[] = $row;
            }
            echo json_encode($fields);
        } else {
            echo 'error';
        }
    }

    if ($action == "fetch_uploaded_modal") {
        $table = "test";
        
        $sql = "SELECT * FROM $table";
        $result = $conn->query($sql);
    
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $columns = array_keys($row);
            $result->data_seek(0);
    
            $columnsWithData = [];
            while ($row = $result->fetch_assoc()) {
                foreach ($columns as $column) {
                    if (!empty($row[$column])) {
                        $columnsWithData[$column] = true;
                    }
                }
            }
    
            $result->data_seek(0);
            ?>
            
            <div class="card card-body shadow">
                <form id="tableForm">
                    <div style="overflow-x: auto; overflow-y: auto; max-height: 90vh; max-width: 100%;">
                        <table class="table table-bordered table-striped text-center">
                            <thead>
                                <tr>
                                    <?php
                                    foreach ($columns as $column) {
                                        if (isset($columnsWithData[$column])) {
                                            $formattedColumn = ucwords(str_replace('_', ' ', $column));
                                            echo "<th class='fs-4'>" . $formattedColumn . "</th>";
                                        }
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                while ($row = $result->fetch_assoc()) {
                                    $product_id = $row['product_id'];
                                    echo '<tr>';
                                    foreach ($columns as $column) {
                                        if (isset($columnsWithData[$column])) {
                                            $value = $row[$column] ?? '';
                                            if ($column == 'product_category') {
                                                ?>
                                                <td contenteditable="false" data-header-name="<?= $column ?>" data-id="<?=$product_id?>">
                                                    <?= getProductCategoryName($value) ?>
                                                </td>
                                                <?php
                                            } else if ($column == 'product_system') {
                                                ?>
                                                <td contenteditable="false" data-header-name="<?= $column ?>" data-id="<?=$product_id?>">
                                                    <?= getProductSystemName($value) ?>
                                                </td>
                                                <?php
                                            } else if ($column == 'product_type') {
                                                ?>
                                                <td contenteditable="false" data-header-name="<?= $column ?>" data-id="<?=$product_id?>">
                                                    <?= getProductTypeName($value) ?>
                                                </td>
                                                <?php
                                            } else if ($column == 'product_line') {
                                                ?>
                                                <td contenteditable="false" data-header-name="<?= $column ?>" data-id="<?=$product_id?>">
                                                    <?= getProductLineName($value) ?>
                                                </td>
                                                <?php
                                            } else if ($column == 'color') {
                                                ?>
                                                <td contenteditable="false" data-header-name="<?= $column ?>" data-id="<?=$product_id?>">
                                                    <?= getColorName($value) ?>
                                                </td>
                                                <?php
                                            } else {
                                                echo "<td contenteditable='true' class='table_data' data-header-name='".$column."' data-id='".$product_id."'>$value</td>";
                                            }
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
    

    if ($action == "upload_excel") {
        if (isset($_FILES['excel_file'])) {
            $fileTmpPath = $_FILES['excel_file']['tmp_name'];
            $fileName = $_FILES['excel_file']['name'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
    
            $table_test = 'test';
    
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
    
            $truncateSql = "TRUNCATE TABLE $table_test";
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
    
                $sql = "INSERT INTO $table_test ($columnNames) VALUES ('$columnValues')";
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
    
    if ($action == "save_table") {
        $table = "product";
    
        $columnsSql = "SHOW COLUMNS FROM test";
        $columnsResult = $conn->query($columnsSql);
    
        $columns = [];
        while ($row = $columnsResult->fetch_assoc()) {
            if ($row['Field'] !== 'product_id') {
                $columns[] = $row['Field'];
            }
        }
    
        $columnsList = implode(", ", $columns);
    
        $sql = "INSERT INTO $table ($columnsList) SELECT $columnsList FROM test";
    
        if ($conn->query($sql) === TRUE) {
            echo "Data has been successfully saved";
    
            $truncateSql = "TRUNCATE TABLE test";
            if ($conn->query($truncateSql) !== TRUE) {
                echo " but failed to clear test table: " . $conn->error;
            }
        } else {
            echo "Error: " . $conn->error;
        }
    }

    if ($action == "download_excel") {
        $product_category = mysqli_real_escape_string($conn, $_REQUEST['category'] ?? '');
        $category_name = strtoupper(getProductCategoryName($product_category));
    
        $includedColumns = array();
        $column_txt = '*';
    
        if($product_category == 4){ // TRIM
            $includedColumns = [ 
                'product_id',
                'product_category',
                'product_system',
                'product_line',
                'product_type',
                'flat_sheet_width',
                'current_retail_price',
                'cost_per_sq_in',
                'grade',
                'gauge',
                'color',
                'trim_multiplier',
                'length',
                'retail_cost',
                'retail',
                'description'
            ];
        } else if($product_category == 16){ // SCREW
            $includedColumns = [ 
                'product_id',
                'product_category',
                'product_type',
                'color',
                'size',
                'supplier_id',
                'pack',
                'cost',
                'price',
                'retail',
                'description'
            ];
        } else if($product_category == 3){ // PANELS
            $includedColumns = [ 
                'product_id',
                'product_category',
                'product_system',
                'product_line',
                'product_type',
                'gauge',
                'width',
                'hems',
                'bends',
                'thickness',
                'grade',
                'color',
                'color_multiplier',
                'stock_type',
                'cost',
                'retail',
                'description'
            ];
        } else if($product_category == 1){ // LUMBER
            $includedColumns = [ 
                'product_id',
                'product_category',
                'product_system',
                'product_line',
                'product_type',
                'width',
                'length',
                'thickness',
                'color',
                'color_paint',
                'cost',
                'retail',
                'description'
            ];
        } else if($product_category == 18){ // PIPE BOOTS
            $includedColumns = [ 
                'product_id',
                'product_category',
                'product_system',
                'product_line',
                'product_type',
                'width',
                'length',
                'thickness',
                'color',
                'color_paint',
                'cost',
                'retail',
                'description'
            ];
        } else if($product_category == 17){ // CAULK SEALANT
            $includedColumns = [ 
                'product_id',
                'product_category',
                'product_system',
                'product_line',
                'product_type',
                'width',
                'length',
                'thickness',
                'color',
                'color_paint',
                'cost',
                'retail',
                'description'
            ];
        } else { // OTHERS
            $includedColumns = [ 
                'product_id',
                'product_category',
                'product_type',
                'color',
                'size',
                'supplier_id',
                'pack',
                'cost',
                'price',
                'retail',
                'description'
            ];
        }

        $additionalColumns = [ 
            'product_item',
            'product_sku',
            'inv_id',
            'coil_part_no',
            'unit_of_measure',
            'product_origin',
            'material',
            'warranty_type',
            'profile',
            'weight',
            'product_usage',
            'upc',
            'comment'
        ];

        array_push($includedColumns, ...$additionalColumns);
        $column_txt = implode(', ', $includedColumns);
    
        $sql = "SELECT " . $column_txt . " FROM product";
        if (!empty($product_category)) {
            $sql .= " WHERE product_category = '$product_category'";
        }
        $result = $conn->query($sql);
    
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        $headers = [];
        $row = 1;

        function indexToColumnLetter($index) {
            $letter = '';
            
            while ($index >= 0) {
                $letter = chr($index % 26 + 65) . $letter;
                $index = floor($index / 26) - 1;
            }
            
            return $letter;
        }
        
        foreach ($includedColumns as $index => $column) {
            $header = ucwords(str_replace('_', ' ', $column));
            if ($index >= 26) {
                $columnLetter = indexToColumnLetter($index);
            } else {
                $columnLetter = chr(65 + $index);
            }

            if($column == 'cost_per_sq_in'){
                $headers[$columnLetter] = "$ Per square inch";
            }else{
                $headers[$columnLetter] = $header;
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
    
        $filename = "$category_name.xlsx";
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


    if ($action == "download_classifications") {
        $classification = mysqli_real_escape_string($conn, $_REQUEST['class'] ?? '');
    
        $classifications = [
            'category' => [
                'columns' => ['product_category_id', 'product_category'],
                'table' => 'product_category',
                'where' => "status = '1'"
            ],
            'system' => [
                'columns' => ['product_system_id', 'product_system'],
                'table' => 'product_system',
                'where' => "status = '1'"
            ],
            'line' => [
                'columns' => ['product_line_id', 'product_line'],
                'table' => 'product_line',
                'where' => "status = '1'"
            ],
            'type' => [
                'columns' => ['product_type_id', 'product_type'],
                'table' => 'product_type',
                'where' => "status = '1'"
            ],
            'grade' => [
                'columns' => ['product_grade_id', 'product_grade'],
                'table' => 'product_grade',
                'where' => "status = '1'"
            ],
            'color' => [
                'columns' => ['color_id', 'product_category', 'color_name'],
                'table' => 'paint_colors',
                'where' => "color_status = '1'"
            ],
            'profile' => [
                'columns' => ['profile_type_id', 'profile_type'],
                'table' => 'profile_type',
                'where' => "status = '1'"
            ],
            'flat_sheet_width' => [
                'columns' => ['id', 'product_system', 'product_category', 'product_line', 'product_type', 'width'],
                'table' => 'flat_sheet_width',
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
    
                    if ($column == 'product_category' && $class == 'color') {
                        $value = getProductCategoryName($data[$column] ?? '');
                    }

                    if ($column == 'product_system' && $class == 'flat_sheet_width') {
                        $value = getProductSystemName($data[$column] ?? '');
                    }

                    if ($column == 'product_category' && $class == 'flat_sheet_width') {
                        $value = getProductCategoryName($data[$column] ?? '');
                    }

                    if ($column == 'product_line' && $class == 'flat_sheet_width') {
                        $value = getProductLineName($data[$column] ?? '');
                    }

                    if ($column == 'product_type' && $class == 'flat_sheet_width') {
                        $value = getProductTypeName($data[$column] ?? '');
                    }
                        
                    $sheet->setCellValue($columnLetter . $row, $value);
                }
                $row++;
            }
        }

        if(empty($classification)){
            $classification = 'Classifications';
        }else{
            $classification = ucwords($classification);
        }
    
        $filename = "$classification.xlsx";
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

    if ($action == "update_product_data") {
        $column_name = $_POST['header_name'];
        $new_value = $_POST['new_value'];
        $product_id = $_POST['id'];
        
        if (empty($column_name) || empty($product_id)) {
            exit;
        }
        
        $column_name = mysqli_real_escape_string($conn, $column_name);
        $new_value = mysqli_real_escape_string($conn, $new_value);
        $product_id = mysqli_real_escape_string($conn, $product_id);
        
        $sql = "UPDATE test SET `$column_name` = '$new_value' WHERE product_id = '$product_id'";

        if ($conn->query($sql) === TRUE) {
            echo 'Success';
        } else {
            echo 'Error updating record: ' . $conn->error;
        }
    }

    if ($action == "fetch_add_inventory") {
        $product_id = mysqli_real_escape_string($conn, $_POST['id']);
        $prouct_details = getProductDetails($product_id);
        $supplier_id = $prouct_details['supplier_id'];
        ?>
        
        <form id="add_inventory" class="form-horizontal" action="#">
            <div class="card mb-3">
                <div class="card-body">
                    <h4 class="fw-bold"><?= getProductName($product_id)?></h4>
                    <input type="hidden" id="product_id_filter" class="form-control select2-add" name="Product_id" value="<?= $product_id ?>" />
                    <input type="hidden" id="operation" name="operation" value="add" />
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Color</label>
                            <div class="mb-3">
                                <select id="color<?= $no ?>" class="form-control color-cart select2-inventory" name="color_id">
                                    <option value="" >Select Color...</option>
                                    <?php
                                    $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                    $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                                    while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                    ?>
                                        <option value="<?= $row_paint_colors['color_id'] ?>" <?= $selected ?> data-color="<?= getColorHexFromColorID($row_paint_colors['color_id']) ?>"><?= $row_paint_colors['color_name'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <label class="form-label">Supplier</label>
                            <div class="mb-3">
                                <p><?= !empty($supplier_id) ? getSupplierName($supplier_id) : 'No Supplier Set for Product' ?></p>
                                <input type="hidden" id="supplier_id_update" name="supplier_id" value="<?= $supplier_id ?>" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Warehouse</label>
                            <div class="mb-3">
                            <select id="Warehouse_id" class="form-control select2-inventory" name="Warehouse_id">
                                <option value="" >Select Warehouse...</option>
                                <optgroup label="Warehouse">
                                    <?php
                                    $query_warehouse = "SELECT * FROM warehouses WHERE status = '1'";
                                    $result_warehouse = mysqli_query($conn, $query_warehouse);            
                                    while ($row_warehouse = mysqli_fetch_array($result_warehouse)) {
                                    ?>
                                        <option value="<?= $row_warehouse['WarehouseID'] ?>" ><?= $row_warehouse['WarehouseName'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                                
                            </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Shelf</label>
                            <div class="mb-3">
                            <select id="Shelves_id" class="form-control select2-inventory" name="Shelves_id">
                                <option value="" >Select Shelf...</option>
                                <optgroup label="Shelf">
                                    <?php
                                    $query_shelf = "SELECT * FROM shelves";
                                    $result_shelf = mysqli_query($conn, $query_shelf);            
                                    while ($row_shelf = mysqli_fetch_array($result_shelf)) {
                                    ?>
                                        <option value="<?= $row_shelf['ShelfID'] ?>" ><?= $row_shelf['ShelfCode'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bin</label>
                            <div class="mb-3">
                            <select id="Bin_id" class="form-control select2-inventory" name="Bin_id">
                                <option value="" >Select Bin...</option>
                                <optgroup label="Bin">
                                    <?php
                                    $query_bin = "SELECT * FROM bins";
                                    $result_bin = mysqli_query($conn, $query_bin);            
                                    while ($row_bin = mysqli_fetch_array($result_bin)) {
                                    ?>
                                        <option value="<?= $row_bin['BinID'] ?>" ><?= $row_bin['BinCode'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Row</label>
                            <div class="mb-3">
                            <select id="Row_id" class="form-control select2-inventory" name="Row_id">
                                <option value="" >Select Row...</option>
                                <optgroup label="Row">
                                    <?php
                                    $query_rows = "SELECT * FROM warehouse_rows";
                                    $result_rows = mysqli_query($conn, $query_rows);            
                                    while ($row_rows = mysqli_fetch_array($result_rows)) {
                                    ?>
                                        <option value="<?= $row_rows['WarehouseRowID'] ?>" ><?= $row_rows['WarehouseRowID'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quantity</label>
                            <input type="text" id="quantity_add" name="quantity" class="form-control"  />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pack</label>
                            <div class="mb-3">
                            <select id="pack_add" class="form-control select2-inventory pack_select" name="pack">
                                <option value="" >Select Pack...</option>
                                <optgroup label="Supplier Packs">
                                    <?php
                                    $query_packs = "SELECT * FROM supplier_pack WHERE supplierid = '$supplier_id'";
                                    $result_packs = mysqli_query($conn, $query_packs);            
                                    while ($row_packs = mysqli_fetch_array($result_packs)) {
                                    ?>
                                        <option value="<?= $row_packs['id'] ?>" data-count="<?= $row_packs['pack_count'] ?>" ><?= $row_packs['pack'] ?> ( <?= $row_packs['pack_count'] ?> )</option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total Quantity</label>
                            <input type="text" id="quantity_ttl_add" name="quantity_ttl" class="form-control"  />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" id="Date" name="Date" class="form-control"  />
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="form-buttons text-right">
                <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
            </div>
        </form>
        <?php
    }
    
    mysqli_close($conn);
}
?>
