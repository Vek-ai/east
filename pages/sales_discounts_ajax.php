<?php
session_start();
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
        
            if ($key == 'retail') {
                $fields['unit_price'] = $escapedValue;
            }

            if ($key == 'color_paint') {
                $fields['color'] = $escapedValue;
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
    
    if ($action == 'fetch_products') {
        $permission = $_SESSION['permission'];
        $data = [];

        $saleItems = getSaleItems();
        $saleMap = [];
        foreach ($saleItems as $s) {
            $saleMap[$s['product_id']] = $s;
        }

        $query = "SELECT product_id FROM product WHERE hidden = 0";
        $result = mysqli_query($conn, $query);
        $no = 1;

        while ($row = mysqli_fetch_assoc($result)) {
            $product_id = $row['product_id'];
            $details    = getProductDetails($product_id);

            $current_price = floatval($details['price']);
            $discount      = 0;
            $sale_price    = "";
            
            if (isset($saleMap[$product_id])) {
                $sale_price = $saleMap[$product_id]['sale_price'];
                if ($current_price > 0) {
                    $discount = round((1 - ($sale_price / $current_price)) * 100);
                } else {
                    $discount = 0;
                }
            }

            $picture_path = !empty($details['main_image']) ? $details['main_image'] : "images/product/product.jpg";
            $product_name_html = "
                <a href='?page=product_details&product_id={$product_id}'>
                    <div class='d-flex align-items-center'>
                        <img src='{$picture_path}' class='rounded-circle' width='56' height='56'>
                        <div class='ms-3'>
                            <h6 class='fw-semibold mb-0 fs-4'>{$details['product_item']}</h6>";
                            
            if (!empty($details['product_sku'])) {
                $product_name_html .= "
                            <div class='text-muted fs-3'>{$details['product_sku']}</div>";
            }

            $product_name_html .= "
                        </div>
                    </div>
                </a>";

            $status_html = '';
            $active = 0;

            if ($details['status'] == 0) {
                $status_html = "<div id='status-alert$no' class='changeStatus alert alert-danger text-white text-center py-1 px-2 my-0' data-no='$no' data-id='$product_id' data-status='0'>Inactive</div>";
                $active = 0;
            } else if (isset($saleMap[$product_id])) {
                $now = new DateTime();
                $start = new DateTime($saleMap[$product_id]['date_started']);
                $end   = new DateTime($saleMap[$product_id]['date_finished']);

                if ($now >= $start && $now <= $end) {
                    $status_html = "<div id='status-alert$no' class='changeStatus alert alert-success text-white text-center py-1 px-2 my-0' data-no='$no' data-id='$product_id' data-status='1'>On Sale</div>";
                    $active = 1;
                } else {
                    $status_html = "<div id='status-alert$no' class='changeStatus alert alert-warning text-white text-center py-1 px-2 my-0' data-no='$no' data-id='$product_id' data-status='0'>Sale Ended</div>";
                    $active = 1;
                }
            } else {
                $status_html = "<div id='status-alert$no' class='changeStatus alert alert-info text-white text-center py-1 px-2 my-0' data-no='$no' data-id='$product_id' data-status='1'>Active</div>";
                $active = 1;
            }

            $action_html = "<div class='action-btn text-center'>"; 
            $action_html .= "<a href='javascript:void(0)' id='view_product_btn' title='View Sale' class='text-primary' data-id='{$product_id}'><i class='ti ti-eye fs-7'></i></a>"; 
            if ($permission === 'edit') {
                $action_html .= "<a href='javascript:void(0)' id='add_sale_btn' title='Add Sale' class='text-warning' data-id='{$product_id}'><i class='ti ti-plus fs-7'></i></a>";
            }
            $action_html .= "</div>"; 

            $data[] = [
                'product_name_html' => $product_name_html,
                'product_category'  => getProductCategoryName($details['product_category']),
                'product_line'      => getProductLineName($details['product_line']),
                'product_type'      => getProductTypeName($details['product_type']),
                'current_price'     => number_format($current_price, 2),
                'discount'          => $discount > 0 ? $discount . "%" : "",
                'sale_price'        => $sale_price !== "" ? number_format($sale_price, 2) : "",
                'instock'           => $details['quantity_ttl'] > 1 ? 1 : 0,
                'status'            => $status,
                'active'            => $active,
                'status_html'       => $status_html,
                'action_html'       => $action_html
            ];

            $no++;
        }

        echo json_encode(['data' => $data]);
    }

    if ($action == 'fetch_view_modal') {
        $id = intval($_POST['id']);
        
        $query = "SELECT * FROM product WHERE product_id = $id";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);

        if ($row) {
            $picture_path = !empty($row['main_image']) ? $row['main_image'] : "images/product/product.jpg";
            ?>
            <a href='javascript:void(0)'>
                <div class='d-flex align-items-center px-4 mb-3'>
                    <img src='{$picture_path}' class='rounded-circle' width='56' height='56'>
                    <div class='ms-3'>
                        <h6 class='fw-semibold mb-0 fs-4'><?=$row['product_item']?></h6>";
                        <div class='text-muted fs-3'>Current Price: <?=$row['unit_price']?></div> 
                        <input type="hidden" id="unit_price" 
                                value="<?= $row['unit_price'] ?>" 
                                data-product-id="<?= $row['product_id'] ?>" 
                                data-category-id="<?= $row['product_category'] ?>"> 
                    </div>
                </div>
            </a>
            
            <div class="mb-3">
                <label class="form-label">Discount Type</label>
                <select class="form-select" name="discount_type">
                    <option value="fixed">Set Fixed Price</option>
                    <option value="percent">Percentage Off</option>
                </select>
            </div>
            
            <div class="row mb-3">
                <div class="col-6">
                    <label class="form-label">Discount Value</label>
                    <div class="input-group">
                    <input type="number" step="0.01" class="form-control" name="discount_value">
                    <span class="input-group-text">$</span>
                    </div>
                </div>
                <div class="col-6">
                    <label class="form-label">New Price</label>
                    <input type="text" class="form-control" name="new_price" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-6">
                    <label class="form-label">Start Date</label>
                    <input type="datetime-local" class="form-control" name="start_date">
                </div>
                <div class="col-6">
                    <label class="form-label">End Date</label>
                    <input type="datetime-local" class="form-control" name="end_date">
                </div>
            </div>

            <!-- 
            <div class="mb-3">
                <label class="form-label">Minimum Quantity (Optional)</label>
                <input type="number" class="form-control" name="min_qty" value="1">
                <small class="text-muted">Minimum quantity required for discount to apply</small>
            </div> 
            -->

            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" name="apply_category" id="apply_category">
                <label class="form-check-label" for="apply_category">Apply to entire category</label>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="applyDiscountBtn" class="btn btn-success">Apply Discount</button>
            </div>
            <?php
        } else {
            echo "<div class='alert alert-warning'>Product not found.</div>";
        }
    }

    if ($action == 'apply_discount') {
        $product_id   = intval($_POST['product_id']);
        $category_id  = intval($_POST['category_id']);
        $new_price    = floatval($_POST['new_price']);
        $start_date   = mysqli_real_escape_string($conn, $_POST['start_date']);
        $end_date     = mysqli_real_escape_string($conn, $_POST['end_date']);
        $added_by     = $_SESSION['userid'] ?? 0;

        $sql = "INSERT INTO sales_discounts 
                (category_id, product_id, date_started, date_finished, added_by)
                VALUES 
                ('$category_id', '$product_id', '$start_date', '$end_date', '$added_by')";
        
        if (mysqli_query($conn, $sql)) {
            $update = "UPDATE inventory 
                        SET on_sale = 1, sale_price = '$new_price' 
                        WHERE Product_id = '$product_id'";
            mysqli_query($conn, $update);

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        exit;
    }
    
    mysqli_close($conn);
}
?>
