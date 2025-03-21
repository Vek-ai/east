<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

function findCartKey($cart, $product_id, $color) {
    foreach ($cart as $key => $item) {
        if ($item['product_id'] == $product_id && $item['custom_color'] == $color) {
            return $key;
        }
    }
    return false;
}

if (isset($_POST['modifyquantity'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
    $color = isset($_POST['qty']) ? (int)$_POST['color'] : 0;

    $key = mysqli_real_escape_string($conn, $_POST['key'] ?? '');

    $quantityInStock = getProductStockInStock($product_id);

    if (!isset($_SESSION["order_cart"])) {
        $_SESSION["order_cart"] = array();
    }

    if($key == ''){
        $key = findCartKey($_SESSION["order_cart"], $product_id, $color);
    }

    if ($key !== false) {
        if (isset($_POST['setquantity'])) {
            $requestedQuantity = max($qty, 1);
            $_SESSION["order_cart"][$key]['quantity_cart'] = $requestedQuantity;
            echo $_SESSION["order_cart"][$key]['quantity_cart'];
        } elseif (isset($_POST['addquantity'])) {
            $newQuantity = $_SESSION["order_cart"][$key]['quantity_cart'] + $qty;
            $_SESSION["order_cart"][$key]['quantity_cart'] = $newQuantity;
            echo $_SESSION["order_cart"][$key]['quantity_cart'];
        } elseif (isset($_POST['deductquantity'])) {
            $currentQuantity = $_SESSION["order_cart"][$key]['quantity_cart'];
            if ($currentQuantity <= 1) {
                array_splice($_SESSION["order_cart"], $key, 1);
                echo 'removed';
            } else {
                $_SESSION["order_cart"][$key]['quantity_cart'] = $currentQuantity - 1;
                echo $_SESSION["order_cart"][$key]['quantity_cart'];
            }
        }
    } else {
        $query = "SELECT * FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            $basePrice = floatval($row['unit_price']);
            if($row['sold_by_feet'] == '1'){
                $basePrice = $basePrice / floatval($row['length'] ?? 1);
            }

            $item_array = array(
                'product_id' => $row['product_id'],
                'product_item' => getProductName($row['product_id']),
                'unit_price' => $basePrice,
                'quantity_cart' => $qty,
                'custom_color' => $color
            );

            $_SESSION["order_cart"][] = $item_array;
        }

    }
}

if (isset($_POST['fetch_cart_count'])) {
    $cart_count = 0;
    if (isset($_SESSION['order_cart']) && is_array($_SESSION['order_cart'])) {
        foreach ($_SESSION['order_cart'] as $item) {
            $cart_count += isset($item['quantity_cart']) ? intval($item['quantity_cart']) : 0;
        }
    }
    echo $cart_count;
}

if(isset($_POST['fetch_order'])){
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
            <div id="supplier_order_section">
                <?php 
                $supplier_id = '';
                if(!empty($_SESSION["order_supplier_id"])){
                    $supplier_id = $_SESSION["order_supplier_id"];
                    $supplier_details = getSupplierDetails($supplier_id);
                }
                ?>
                <div class="form-group row align-items-center text-start">
                    <label>Supplier:</label>
                    <div class="col-6"> 
                        <select id="order_supplier_id" class="form-control select2_order" name="supplier_id">
                            <option value="" >Select Supplier...</option>
                            <optgroup label="Supplier">
                                <?php
                                $query_supplier = "SELECT * FROM supplier WHERE status = 1 ORDER BY `supplier_name` ASC";
                                $result_supplier = mysqli_query($conn, $query_supplier);            
                                while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                                    $selected = ($supplier_id == $row_supplier['supplier_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_supplier['supplier_id'] ?>" <?= $selected ?>><?= $row_supplier['supplier_name'] ?></option>
                                <?php   
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                </div>
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
                        $total = 0;
                        $total_customer_price = 0;
                        $totalquantity = 0;
                        $timestamp = time();
                        $no = $timestamp . 1;
                        $total_weight = 0;
                        if (!empty($_SESSION["order_cart"])) {
                            foreach ($_SESSION["order_cart"] as $keys => $values) {
                                $data_id = $values["product_id"];
                                $product = getProductDetails($data_id);
                                $category_id = $product["product_category"];

                                $default_image = 'images/product/product.jpg';
                                $picture_path = !empty($product['main_image'])
                                ?  $product['main_image']
                                : $default_image;

                                $product_price = ($values["quantity_cart"] * ($values["unit_price"]));

                                $color_id = $values["custom_color"];
                            ?>
                                <tr class="border-bottom border-3 border-white">
                                    <td>
                                        <div class="align-items-center text-center w-100">
                                            <img src="<?= $picture_path ?>" class="rounded-circle " alt="materialpro-img" width="56" height="56">
                                        </div>
                                    </td>
                                    <td>
                                        <h6 class="fw-semibold mb-0 fs-4"><?= $values["product_item"] ?></h6>
                                    </td>
                                    <td>
                                        <select id="color_order<?= $no ?>" class="form-control color-order text-start" name="color" onchange="updateColor(this)" data-key="<?= $keys ?>" data-id="<?= $data_id; ?>">
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
                                                <button class="btn btn-primary btn-icon p-1 mr-1" type="button" data-key="<?= $keys ?>" data-id="<?php echo $data_id; ?>" onClick="deductquantity(this)">
                                                    <i class="fa fa-minus"></i>
                                                </button>
                                            </span> 
                                            <input class="form-control" type="text" size="5" value="<?php echo $values["quantity_cart"]; ?>" style="color:#ffffff;" onchange="updatequantity(this)" data-key="<?= $keys ?>" data-id="<?php echo $data_id; ?>" id="item_quantity<?php echo $data_id;?>">
                                            <span class="input-group-btn">
                                                <button class="btn btn-primary btn-icon p-1 ml-1" type="button" data-key="<?= $keys ?>" data-id="<?php echo $data_id; ?>" onClick="addquantity(this)">
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
                                        <button class="btn btn-danger-gradient btn-sm" type="button" data-id="<?=$data_id; ?>" data-key="<?= $keys ?>" onClick="delete_item(this)"><i class="fa fa-trash text-danger fs-6"></i></button>
                                    </td>
                                </tr>
                        <?php
                                $totalquantity += $values["quantity_cart"];
                                $total += $subtotal;
                                $total_customer_price += $customer_price;
                                $no++;
                            }
                        }
                        ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-end">Total Quantity:</td>
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

    <script>
        $(document).ready(function() {
            $(".select2_order").each(function() {
                let $this = $(this);

                if ($this.hasClass("select2-hidden-accessible")) {
                    $this.select2('destroy');
                    $this.removeAttr('data-select2-id');
                    $this.next('.select2-container').remove();
                }

                $this.select2({
                    width: '100%',
                    dropdownParent: $this.parent()
                });
            });

            $(".color-order").each(function() {
                if ($(this).data('select2')) {
                    $(this).select2('destroy');
                }
                $(this).select2({
                    width: '300px',
                    placeholder: "Select...",
                    dropdownAutoWidth: true,
                    dropdownParent: $('#orderTable'),
                    templateResult: formatOption,
                    templateSelection: formatOption
                });
            });
        });
    </script>

    <?php
}

if (isset($_POST['set_color'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);
    $key = mysqli_real_escape_string($conn, $_POST['key']);

    if ($key !== false && isset($_SESSION["order_cart"][$key])) {
        $_SESSION["order_cart"][$key]['custom_color'] = !empty($color_id) ? $color_id : "";

        echo "Color id: $color_id, Prod id: $product_id, Line: $line, Key: $key";
    }
    
}

if (isset($_POST['deleteitem'])) {
    $key = mysqli_real_escape_string($conn, $_POST['key']);
    
    $key = (int) $key; 
    
    if (isset($_SESSION["order_cart"][$key])) {
        array_splice($_SESSION["order_cart"], $key, 1);
    } else {
        echo "Item not found in cart.";
    }
}

if (isset($_POST['change_supplier'])) {
    if (isset($_POST['supplier_id'])) {
        $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
        $_SESSION['order_supplier_id'] = $supplier_id;
        echo 'success';
    } else {
        echo 'Error: Customer ID not provided.';
    }
}

if (isset($_POST['order_supplier_products'])) {
    header('Content-Type: application/json');
    $response = [];

    if (empty($_SESSION['order_supplier_id'])) {
        echo json_encode(['error' => "Supplier is not set."]);
        exit;
    }

    if (empty($_SESSION['userid'])) {
        echo json_encode(['error' => "Staff not logged in."]);
        exit;
    }

    if (!isset($_SESSION['order_cart']) || empty($_SESSION['order_cart'])) {
        echo json_encode(['error' => "Order List is empty or does not exist."]);
        exit;
    } 
    
    $cashierid = intval($_SESSION['userid']);
    $supplier_id = $_SESSION['order_supplier_id'];
    $cart = $_SESSION['order_cart'];

    $total_price = array_reduce($cart, function ($sum, $item) {
        return $sum + (floatval($item['unit_price']) * intval($item['quantity_cart']));
    }, 0);

    $query = "INSERT INTO supplier_orders (cashier, total_price, order_date, supplier_id) 
              VALUES ('$cashierid', '$total_price', NOW(), '$supplier_id')";

    if ($conn->query($query)) {
        $supplier_order_id = $conn->insert_id;
        $values = [];

        foreach ($cart as $item) {
            $values[] = sprintf(
                "('%d', '%d', '%d', '%.2f', '%s')",
                $supplier_order_id,
                intval($item['product_id']),
                intval($item['quantity_cart']),
                floatval($item['unit_price']),
                $conn->real_escape_string($item['custom_color'])
            );
        }

        $query = "INSERT INTO supplier_orders_prod (supplier_order_id, product_id, quantity, price, color) VALUES " . implode(', ', $values);

        if ($conn->query($query)) {
            unset($_SESSION['order_cart']);
            echo json_encode(['success' => true, 'order_id' => $supplier_order_id]);
        } else {
            echo json_encode(['error' => "Error inserting order products: " . $conn->error]);
        }
    } else {
        echo json_encode(['error' => "Error inserting order: " . $conn->error]);
    }
}

if (isset($_POST['save_order'])) {
    header('Content-Type: application/json');
    $response = [];

    if (empty($_SESSION['order_supplier_id'])) {
        echo json_encode(['error' => "Supplier is not set."]);
        exit;
    }

    if (empty($_SESSION['userid'])) {
        echo json_encode(['error' => "Staff not logged in."]);
        exit;
    }

    if (!isset($_SESSION['order_cart']) || empty($_SESSION['order_cart'])) {
        echo json_encode(['error' => "Order List is empty or does not exist."]);
        exit;
    } 

    $cashierid = intval($_SESSION['userid']);
    $supplier_id = $_SESSION['order_supplier_id'];
    $cart = $_SESSION['order_cart'];

    $total_price = array_reduce($cart, function ($sum, $item) {
        return $sum + (floatval($item['unit_price']) * intval($item['quantity_cart']));
    }, 0);

    $query = "INSERT INTO supplier_temp_orders (supplier_id, cashier, total_price, order_date) 
              VALUES ('$supplier_id', '$cashierid', '$total_price', NOW())";

    if ($conn->query($query)) {
        $supplier_temp_order_id = $conn->insert_id;
        $values = [];

        foreach ($cart as $item) {
            $values[] = sprintf(
                "('%d', '%d', '%d', '%.2f', '%s')",
                $supplier_temp_order_id,
                intval($item['product_id']),
                intval($item['quantity_cart']),
                floatval($item['unit_price']),
                $conn->real_escape_string($item['custom_color'])
            );
        }

        $query = "INSERT INTO supplier_temp_prod_orders (supplier_temp_order_id, product_id, quantity, price, color) VALUES " . implode(', ', $values);
        if ($conn->query($query)) {
            unset($_SESSION['order_cart']);
            echo json_encode(['success' => true, 'temp_order_id' => $supplier_temp_order_id]);
        } else {
            echo json_encode(['error' => "Error inserting temp order products: " . $conn->error]);
        }
    } else {
        echo json_encode(['error' => "Error inserting temp order: " . $conn->error]);
    }
}

mysqli_close($conn);
?>
