<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';
require '../includes/send_email.php';

$emailSender = new EmailTemplates();

if(isset($_POST['fetch_edit_modal'])){
    $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
    ?>
    <style>
        .high-zindex-select2 + .select2-container--open {
            z-index: 1055 !important;
        }

        .table-fixed {
            table-layout: fixed;
            width: 100%;
        }

        .table-fixed th,
        .table-fixed td {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
            word-wrap: break-word;
        }

        .table-fixed tbody tr:hover input[readonly] {
            background-color: transparent;
        }

        #msform {
            text-align: center;
            position: relative;
            margin-top: 30px;
            
        }

        #msform fieldset {
            border: 0 none;
            border-radius: 0px;
            padding: 20px 30px;
            box-sizing: border-box;

            position: relative;
        }

        #msform fieldset:not(:first-of-type) {
            display: none;
        }

        .select2-container--default .select2-results__option[aria-disabled=true] { 
            display: none;
        }
    </style>
    <div class="card-body datatables">
        <form id="msform">
            <div class="d-flex align-items-center justify-content-start mb-3">
                
            </div>
            <div id="product_details" class="product-details table-responsive text-nowrap">
                <table id="orderTable" class="table table-hover table-fixed mb-0 text-md-nowrap">
                    <thead>
                        <tr>
                            <th class="text-center small">Image</th>
                            <th>Description</th>
                            <th class="text-center">Color</th>
                            <th class="text-center pl-3">Quantity</th>
                            <th class="text-center" style="width: 10%;">Price</th>
                            <th class="text-center" style="width: 5%;"> </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $query = "SELECT * FROM supplier_temp_prod_orders WHERE supplier_id = '$supplier_id'";
                        $result = mysqli_query($conn, $query);
                
                        
                        $total = 0;
                        $total_customer_price = 0;
                        $totalquantity = 0;
                        $timestamp = time();
                        $no = $timestamp . 1;
                        $total_weight = 0;
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $data_id = $row["product_id"];
                                $row_id = $row["id"];
                                $product = getProductDetails($data_id);
                                $product_name = getProductName($data_id);
                                $category_id = $product["product_category"];
                                $default_image = 'images/product/product.jpg';
                                $picture_path = !empty($product['main_image'])
                                ? $product['main_image']
                                : $default_image;

                                $product_price = ($row["quantity"] * ($row["price"]));
                                $color_id = $row["color"];
                            ?>
                                <tr class="border-bottom border-3 border-white">
                                    <td>
                                        <div class="align-items-center text-center w-100">
                                            <img src="<?= $picture_path ?>" class="rounded-circle " alt="materialpro-img" width="56" height="56">
                                        </div>
                                    </td>
                                    <td>
                                        <h6 class="fw-semibold mb-0 fs-4"><?= $product_name ?></h6>
                                    </td>
                                    <td>
                                        <select id="color_order<?= $no ?>" class="form-control color-order text-start" name="color" onchange="updateColor(this)" data-key="<?= $row_id ?>" data-id="<?= $data_id; ?>">
                                            <option value="">Select Color...</option>
                                            <?php
                                            if (!empty($color_id)) {
                                                echo '<option value="' . $color_id . '" selected data-color="' . getColorHexFromColorID($color_id) . '">' . getColorName($color_id) . '</option>';
                                            }

                                            $query_color = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                            $result_color = mysqli_query($conn, $query_color);
                                            while ($row_color = mysqli_fetch_array($result_color)) {
                                                $selected = ($color_id == $row_color['color_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $row_color['color_id'] ?>" data-color="<?= $row_color['color_code'] ?>" <?= $selected ?>><?= $row_color['color_name'] ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-btn">
                                                <button class="btn btn-primary btn-icon p-1 mr-1" type="button" data-key="<?= $row_id ?>" data-id="<?= $data_id; ?>" onClick="deductquantity(this)">
                                                    <i class="fa fa-minus"></i>
                                                </button>
                                            </span> 
                                            <input class="form-control" type="text" size="5" value="<?php echo $row["quantity"]; ?>" style="color:#ffffff;" onchange="updatequantity(this)" data-key="<?= $row_id ?>" data-id="<?php echo $data_id; ?>" id="item_quantity<?php echo $key;?>">
                                            <span class="input-group-btn">
                                                <button class="btn btn-primary btn-icon p-1 ml-1" type="button" data-key="<?= $row_id ?>" data-id="<?= $data_id; ?>" onClick="addquantity(this)">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-end pl-3" style="width: 10%;">$
                                        <?php
                                        $subtotal = $product_price;
                                        echo number_format($subtotal, 2);
                                        ?>
                                    </td>
                                    
                                    <td class="text-center" style="width: 5%;">
                                        <button class="btn btn-danger-gradient btn-sm" type="button" data-key="<?= $row_id ?>" data-id="<?= $data_id; ?>" onClick="delete_item(this)"><i class="fa fa-trash text-danger fs-6"></i></button>
                                    </td>
                                </tr>
                        <?php
                                $totalquantity += $values["quantity"];
                                $total += $subtotal;
                                $total_customer_price += $customer_price;
                                $no++;
                            }
                        }
                        ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="1" class="text-center">
                                <a href="#" class="btn btn-sm" id="addProductModalBtn" style="background-color:rgb(1, 145, 189); color: #fff; border: none;" data-id="<?= $supplier_temp_order_id ?>">
                                    <i class="fas fa-plus"></i> Add Products
                                </a>
                            </td>
                            <td colspan="1" class="text-end">Total Quantity:</td>
                            <td colspan="1" class=""><span id="qty_ttl"><?= $totalquantity ?></span></td>
                            <td colspan="1" class="text-end">Amount Due:</td>
                            <td colspan="1" class="text-end"><span id="ammount_due"><?= number_format($total,2) ?> $</span></td>
                            <td colspan="1"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </form>
    </div>
    <?php
}

if (isset($_POST['modifyquantity'])) {
    $key = mysqli_real_escape_string($conn, $_POST['key']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

    if (isset($_POST['setquantity'])) {
        $query = "UPDATE supplier_temp_prod_orders SET quantity = '$quantity' WHERE id = '$key'";
    } elseif (isset($_POST['addquantity'])) {
        $query = "UPDATE supplier_temp_prod_orders SET quantity = quantity + '1' WHERE id = '$key'";
    } elseif (isset($_POST['deductquantity'])) {
        $query = "UPDATE supplier_temp_prod_orders SET quantity = GREATEST(quantity - '1', 0) WHERE id = '$key'";
    }

    if (isset($query)) {
        echo $quantity;
        mysqli_query($conn, $query);
    }
}

if (isset($_POST['deleteitem'])) {
    $key = mysqli_real_escape_string($conn, $_POST['key']);
    $query = "DELETE FROM supplier_temp_prod_orders WHERE id = '$key'";
    mysqli_query($conn, $query);
}

if (isset($_POST['set_color'])) {
    $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);
    $key = mysqli_real_escape_string($conn, $_POST['key']);
    $query = "UPDATE supplier_temp_prod_orders SET color = '$color_id' WHERE id = '$key'";
    mysqli_query($conn, $query);
}

if (isset($_POST['addToCart'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $product_details = getProductDetails($product_id);
    $supplier_id = $product_details['supplier_id'];
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $price = $product_details['unit_price'];
    $color = mysqli_real_escape_string($conn, $_POST['color']);

    $check_query = "SELECT id, quantity FROM supplier_temp_prod_orders 
                    WHERE product_id = '$product_id' 
                    AND color = '$color'";

    $result = mysqli_query($conn, $check_query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $new_quantity = $row['quantity'] + $quantity;

        $update_query = "UPDATE supplier_temp_prod_orders 
                         SET quantity = '$new_quantity' 
                         WHERE id = '{$row['id']}'";

        if (mysqli_query($conn, $update_query)) {
            echo "updated";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        $insert_query = "INSERT INTO supplier_temp_prod_orders (supplier_temp_order_id, product_id, supplier_id, quantity, price, color) 
                         VALUES ('$supplier_temp_order_id', '$product_id', '$supplier_id', '$quantity', '$price', '$color')";

        if (mysqli_query($conn, $insert_query)) {
            echo "success";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
    exit();
}

if (isset($_POST['fetch_products'])) {
    $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
    $query_product = "
        SELECT 
            p.*,
            COALESCE(SUM(i.quantity_ttl), 0) AS total_quantity
        FROM 
            product AS p
        LEFT JOIN 
            inventory AS i ON p.product_id = i.product_id
        WHERE 
            p.hidden = '0' AND 
            p.product_origin = '1' AND
            p.supplier_id = '$supplier_id'
        GROUP BY p.product_id
    ";
    $result_product = mysqli_query($conn, $query_product);

    $products = [];
    while ($row_product = mysqli_fetch_assoc($result_product)) {
        $row_product['main_image'] = !empty($row_product['main_image']) ? $row_product['main_image'] : "images/product/product.jpg";
        $row_product['product_category'] = !empty($row_product['product_category']) ? getProductCategoryName($row_product['product_category']) : "";
        $products[] = $row_product;
    }

    echo json_encode($products);
}

if (isset($_POST['order_supplier_products'])) {
    header('Content-Type: application/json');

    if (empty($_SESSION['userid'])) {
        echo json_encode(['error' => "Staff not logged in."]);
        exit;
    }

    $cashierid = intval($_SESSION['userid']);
    $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);

    $supplier_details = getSupplierDetails($supplier_id);
    $supplier_name = $supplier_details['supplier_name'];
    $supplier_email = $supplier_details['contact_email'];

    $query = "SELECT * FROM supplier_temp_prod_orders WHERE supplier_id = '$supplier_id'";
    $result = mysqli_query($conn, $query);

    if (!$result || mysqli_num_rows($result) == 0) {
        echo json_encode(['error' => "No products found for this supplier."]);
        exit;
    }

    $total_price = 0;
    $products = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $product_id = $row['product_id'];
        $product_details = getProductDetails($product_id);
        $price = floatval($product_details['unit_price']);
        $quantity = intval($row['quantity']);
        $color = intval($row['color']);

        $total_price += ($price * $quantity);

        $products[] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'price' => $price,
            'color' => $color
        ];
    }

    $order_key = 'ORD' . substr(hash('sha256', uniqid()), 0, 10);

    $query = "INSERT INTO supplier_orders (cashier, total_price, order_date, supplier_id, order_key) 
              VALUES ('$cashierid', '$total_price', NOW(), '$supplier_id', '$order_key')";
              

    if ($conn->query($query)) {
        $supplier_order_id = $conn->insert_id;
        $values = [];

        foreach ($products as $item) {
            $values[] = sprintf(
                "('%d', '%d', '%d', '%.2f', '%d')",
                $supplier_order_id,
                $item['product_id'],
                $item['quantity'],
                $item['price'],
                $item['color']
            );
        }

        $query = "INSERT INTO supplier_orders_prod (supplier_order_id, product_id, quantity, price, color) VALUES " . implode(', ', $values);

        if ($conn->query($query)) {
            $delete_query = "DELETE FROM supplier_temp_prod_orders WHERE supplier_id = '$supplier_id'";
            $conn->query($delete_query);

            $subject = "EKM has placed a New Order";
            $link = "https://metal.ilearnwebtech.com/supplier/index.php?id=$supplier_order_id&key=$order_key";

            $response = $emailSender->sendSupplierOrder($supplier_email, $subject, $link);

            if ($response['success'] === true) {
                echo json_encode([
                    'success' => true,
                    'email_success' => true,
                    'message' => "Successfully sent email to $supplier_name for confirmation on orders.",
                    'supplier_order_id' => $supplier_order_id,
                    'key' => $order_key
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'email_success' => false,
                    'message' => "Successfully saved, but email could not be sent to $supplier_name.",
                    'error' => $response['error'] ?? 'Unknown error',
                    'supplier_order_id' => $supplier_order_id,
                    'key' => $order_key
                ]);
            }

        } else {
            echo json_encode(['error' => "Error inserting order products: " . $conn->error]);
        }

    } else {
        echo json_encode(['error' => "Error inserting order: " . $conn->error]);
    }
}


mysqli_close($conn);
?>
