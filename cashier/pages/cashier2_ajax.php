<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if (isset($_POST['modifyquantity'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

    $quantityInStock = getProductStockInStock($product_id);
    $totalQuantity = getProductStockTotal($product_id);
    $totalStock = $quantityInStock + $totalQuantity;

    if (!isset($_SESSION["cart"])) {
        $_SESSION["cart"] = array();
    }

    $key = array_search($product_id, array_column($_SESSION["cart"], 'product_id'));

    if ($key !== false) {
        if (isset($_POST['setquantity'])) {
            $requestedQuantity = $qty;
            if ($requestedQuantity < 1) $requestedQuantity = 1;
            $_SESSION["cart"][$key]['quantity_cart'] = min($requestedQuantity, $totalStock);
            echo $_SESSION["cart"][$key]['quantity_cart'];

        } elseif (isset($_POST['addquantity'])) {
            $newQuantity = $_SESSION["cart"][$key]['quantity_cart'] + 1;
            $_SESSION["cart"][$key]['quantity_cart'] = ($newQuantity > $totalStock) ? $totalStock : $newQuantity;
            echo $_SESSION["cart"][$key]['quantity_cart'];

        } elseif (isset($_POST['deductquantity'])) {
            $currentQuantity = $_SESSION["cart"][$key]['quantity_cart'];
            if ($currentQuantity <= 1) {
                array_splice($_SESSION["cart"], $key, 1);
                echo 'removed';
            } else {
                $_SESSION["cart"][$key]['quantity_cart'] = $currentQuantity - 1;
                echo $_SESSION["cart"][$key]['quantity_cart'];
            }
        }
    } else {
        $query = "SELECT 
                    product_id,
                    product_item,
                    unit_price
                  FROM product
                  WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $item_quantity = min($qty, $totalStock);

            $item_array = array(
                'product_id' => $row['product_id'],
                'product_item' => $row['product_item'],
                'unit_price' => $row['unit_price'],
                'quantity_ttl' => $totalStock,
                'quantity_in_stock' => $quantityInStock,
                'quantity_cart' => $item_quantity
            );

            $_SESSION["cart"][] = $item_array;
            echo $item_quantity;
        }
    }
}

if(isset($_REQUEST['deleteitem'])){
    $key = array_search($_REQUEST['product_id_del'], array_column($_SESSION["cart"], 'product_id'));
    array_splice($_SESSION["cart"], $key, 1);
}

if (isset($_REQUEST['query'])) {
    $searchQuery = isset($_REQUEST['query']) ? mysqli_real_escape_string($conn, $_REQUEST['query']) : '';
    $color_id = isset($_REQUEST['type_id']) ? mysqli_real_escape_string($conn, $_REQUEST['color_id']) : '';
    $type_id = isset($_REQUEST['type_id']) ? mysqli_real_escape_string($conn, $_REQUEST['type_id']) : '';
    $line_id = isset($_REQUEST['line_id']) ? mysqli_real_escape_string($conn, $_REQUEST['line_id']) : '';
    $category_id = isset($_REQUEST['category_id']) ? mysqli_real_escape_string($conn, $_REQUEST['category_id']) : '';
    $onlyInStock = isset($_REQUEST['onlyInStock']) ? filter_var($_REQUEST['onlyInStock'], FILTER_VALIDATE_BOOLEAN) : false;
    

    $query_product = "
        SELECT 
            p.*,
            COALESCE(SUM(i.quantity_ttl), 0) AS total_quantity
        FROM 
            product AS p
        LEFT JOIN 
            inventory AS i ON p.product_id = i.product_id
        WHERE 
            p.hidden = '0'
    ";

    if (!empty($searchQuery)) {
        $query_product .= " AND (p.product_item LIKE '%$searchQuery%' OR p.description LIKE '%$searchQuery%')";
    }

    if (!empty($color_id)) {
        $query_product .= " AND p.color = '$color_id'";
    }

    if (!empty($type_id)) {
        $query_product .= " AND p.product_type = '$type_id'";
    }

    if (!empty($line_id)) {
        $query_product .= " AND p.product_line = '$line_id'";
    }

    if (!empty($category_id)) {
        $query_product .= " AND p.product_category = '$category_id'";
    }

    $query_product .= " GROUP BY p.product_id";

    if ($onlyInStock) {
        $query_product .= " HAVING total_quantity > 1";
    }

    $result_product = mysqli_query($conn, $query_product);

    $tableHTML = "";

    if (mysqli_num_rows($result_product) > 0) {
        while ($row_product = mysqli_fetch_array($result_product)) {

            $product_length = $row_product['length'];
            $product_width = $row_product['width'];

            $dimensions = "";

            if (!empty($product_length) || !empty($product_width)) {
                $dimensions = '';
            
                if (!empty($product_length)) {
                    $dimensions .= $product_length;
                }
            
                if (!empty($product_width)) {
                    if (!empty($dimensions)) {
                        $dimensions .= " X ";
                    }
                    $dimensions .= $product_width;
                }
            
                if (!empty($dimensions)) {
                    $dimensions = " - " . $dimensions;
                }
            }

            if ($row_product['total_quantity'] > 0) {
                $stock_text = '
                    <a href="javascript:void(0);" id="view_in_stock" data-id="' . $row_product['product_id'] . '" class="d-flex align-items-center">
                        <span class="text-bg-success p-1 rounded-circle"></span>
                        <span class="ms-2">In Stock</span>
                    </a>';
            } else {
                $stock_text = '
                    <a href="javascript:void(0);" id="view_out_of_stock" data-id="' . $row_product['product_id'] . '" class="d-flex align-items-center">
                        <span class="text-bg-danger p-1 rounded-circle"></span>
                        <span class="ms-2">Out of Stock</span>
                    </a>';
            }            
            

            $default_image = '../images/product/product.jpg';

            $picture_path = !empty($row_product['main_image']) && file_exists($row_product['main_image'])
            ? "../" .$row_product['main_image']
            : $default_image;

            $tableHTML .= '
            <tr>
                <td>
                    <a href="javascript:void(0);" id="view_product_details" data-id="' . $row_product['product_id'] . '" class="d-flex align-items-center">
                        <div class="d-flex align-items-center">
                            <img src="'.$picture_path.'" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                            <div class="ms-3">
                                <h6 class="fw-semibold mb-0 fs-4">'. $row_product['product_item'] .' ' .$dimensions .'</h6>
                            </div>
                        </div>
                    </a>
                </td>
                <td>
                    <div class="d-flex mb-0 gap-8">
                        <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:' .getColorHexFromColorID($row_product['color']) .'"></a> '
                        .getColorName($row_product['color']) .'
                    </div>
                </td>
                <td><p class="mb-0">'. getProductTypeName($row_product['product_type']) .'</p></td>
                <td><p class="mb-0">'. getProductLineName($row_product['product_line']) .'</p></td>
                <td><p class="mb-0">'. getProductCategoryName($row_product['product_category']) .'</p></td>
                <td>
                    <div class="d-flex align-items-center">'.$stock_text.'</div>
                </td>
                <td><h6 class="mb-0 fs-4">$'. $row_product['unit_cost'] .'</h6></td>
                <td>
                    <button class="btn btn-primary btn-add-to-cart" type="button" data-id="'.$row_product['product_id'].'" onClick="addtocart(this)">Add to Cart</button>
                </td>
            </tr>';
        }
    } else {
        $tableHTML .= '<tr><td colspan="8" class="text-center">No products found</td></tr>';
    }
    
    //echo $tableHTML;
    echo $tableHTML;
}

if(isset($_POST['fetch_details_modal'])){
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);

    $checkQuery = "SELECT * FROM product WHERE product_id = '$product_id'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        ?>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Product Details</h6>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                <div class="card">
                    <div class="card-body p-4">
                    <div class="row">
                        <div class="col-lg-6">
                        <div id="sync1" class="owl-carousel owl-theme">
                        <?php
                            $query_prod_img1 = "SELECT * FROM product_images WHERE productid = '$product_id'";
                            $result_prod_img1 = mysqli_query($conn, $query_prod_img1);  

                            if ($result_prod_img1 && mysqli_num_rows($result_prod_img1) > 0) {
                                while ($row_prod_img1 = mysqli_fetch_array($result_prod_img1)) {
                                    $image_url = !empty($row_prod_img1['image_url']) && file_exists("../" . $row_prod_img1['image_url'])
                                        ? "../" . $row_prod_img1['image_url']
                                        : "../images/product/product.jpg";
                                    ?>
                                    <div class="item rounded overflow-hidden">
                                        <img src="<?=$image_url?>" alt="materialpro-img" class="img-fluid">
                                    </div>
                                    <?php 
                                }
                            } else {
                                ?>
                                <div class="item rounded overflow-hidden">
                                    <img src="../images/product/product.jpg" alt="materialpro-img" class="img-fluid">
                                </div>
                                <?php
                            } 
                        ?> 
                        </div>

                        <div id="sync2" class="owl-carousel owl-theme">
                            <?php
                                $query_prod_img2 = "SELECT * FROM product_images WHERE productid = '$product_id'";
                                $result_prod_img2 = mysqli_query($conn, $query_prod_img2);  

                                if ($result_prod_img2 && mysqli_num_rows($result_prod_img2) > 0) {
                                    while ($row_prod_img2 = mysqli_fetch_array($result_prod_img2)) {
                                        $image_url = !empty($row_prod_img2['image_url']) && file_exists($row_prod_img2['image_url'])
                                            ? '../' .$row_prod_img2['image_url']
                                            : "../images/product/product.jpg";
                                        ?>
                                        <div class="item rounded overflow-hidden">
                                            <img src="<?=$image_url?>" alt="materialpro-img" class="img-fluid">
                                        </div>
                                        <?php 
                                    }
                                } else {
                                    ?>
                                    <div class="item rounded overflow-hidden">
                                        <img src="../images/product/product.jpg" alt="materialpro-img" class="img-fluid">
                                    </div>
                                    <?php
                                } 
                            ?>
                        </div>
                        </div>
                        <div class="col-lg-6">
                        <div class="shop-content">
                            <div class="d-flex align-items-center gap-2 mb-2">
                            <?php
                            $totalQuantity = getProductStockTotal($row['product_id']);
                            if($totalQuantity > 0){
                            ?>
                                <span class="badge text-bg-success fs-2 fw-semibold">In Stock</span>
                            <?php
                            }else{
                            ?>
                                <span class="badge text-bg-danger fs-2 fw-semibold">Out of Stock</span>
                            <?php
                            }
                            ?>
                            
                            <span class="fs-2"><?= getProductCategoryName($row['product_category']) ?></span>
                            </div>
                            <h4><?= $row['product_item'] ?></h4>
                            <p class="mb-3"><?= $row['description'] ?></p>
                            <h4 class="fw-semibold mb-3">
                                $<?= $row['unit_price'] ?> 
                            </h4>
                            <div class="d-flex align-items-center gap-8 py-7">
                                <?php
                                if (!empty($row['color'])) {
                                ?>
                                    <h6 class="mb-0 fs-4 fw-semibold">Colors:</h6>
                                    <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color: <?= getColorHexFromColorID($row['color']) ?>"></a>
                                <?php 
                                }
                                ?> 
                            </div>

                        </div>
                        </div>
                    </div>
                    </div>
                </div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
        <script>
            $(function () {
                // product detail

                var sync1 = $("#sync1");
                var sync2 = $("#sync2");
                var slidesPerPage = 4;
                var syncedSecondary = true;

                sync1
                    .owlCarousel({
                    items: 1,
                    slideSpeed: 2000,
                    nav: false,
                    autoplay: false,
                    dots: true,
                    loop: true,
                    rtl: true,
                    responsiveRefreshRate: 200,
                    navText: [
                        '<svg width="12" height="12" height="100%" viewBox="0 0 11 20"><path style="fill:none;stroke-width: 3px;stroke: #fff;" d="M9.554,1.001l-8.607,8.607l8.607,8.606"/></svg>',
                        '<svg width="12" height="12" viewBox="0 0 11 20" version="1.1"><path style="fill:none;stroke-width: 3px;stroke: #fff;" d="M1.054,18.214l8.606,-8.606l-8.606,-8.607"/></svg>',
                    ],
                    })
                    .on("changed.owl.carousel", syncPosition);

                sync2
                    .on("initialized.owl.carousel", function () {
                    sync2.find(".owl-item").eq(0).addClass("current");
                    })
                    .owlCarousel({
                    items: slidesPerPage,
                    items: 6,
                    margin: 16,
                    dots: true,
                    nav: false,
                    rtl: true,
                    smartSpeed: 200,
                    slideSpeed: 500,
                    slideBy: slidesPerPage,
                    responsiveRefreshRate: 100,
                    })
                    .on("changed.owl.carousel", syncPosition2);

                function syncPosition(el) {
                    var count = el.item.count - 1;
                    var current = Math.round(el.item.index - el.item.count / 2 - 0.5);

                    if (current < 0) {
                    current = count;
                    }
                    if (current > count) {
                    current = 0;
                    }

                    sync2
                    .find(".owl-item")
                    .removeClass("current")
                    .eq(current)
                    .addClass("current");
                    var onscreen = sync2.find(".owl-item.active").length - 1;
                    var start = sync2.find(".owl-item.active").first().index();
                    var end = sync2.find(".owl-item.active").last().index();

                    if (current > end) {
                    sync2.data("owl.carousel").to(current, 100, true);
                    }
                    if (current < start) {
                    sync2.data("owl.carousel").to(current - onscreen, 100, true);
                    }
                }

                function syncPosition2(el) {
                    if (syncedSecondary) {
                    var number = el.item.index;
                    sync1.data("owl.carousel").to(number, 100, true);
                    }
                }

                sync2.on("click", ".owl-item", function (e) {
                    e.preventDefault();
                    var number = $(this).index();
                    sync1.data("owl.carousel").to(number, 300, true);
                });
                });
        </script>
<?php
    }
}

if(isset($_POST['fetch_in_stock_modal'])){
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);

    $checkQuery = "SELECT * FROM product WHERE product_id = '$product_id'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        ?>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Stock Details</h6>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container py-4">
                        <h5 class="mb-3 fs-6 fw-semibold text-center">Inventory</h5>
                        <?php
                        $query_inventory = "SELECT DISTINCT Warehouse_id FROM inventory WHERE Product_id = '$product_id' AND Warehouse_id != '0'";
                        $result_inventory = mysqli_query($conn, $query_inventory);

                        if ($result_inventory && mysqli_num_rows($result_inventory) > 0) {
                            echo '<div class="row">';
                            while ($row_inventory = mysqli_fetch_assoc($result_inventory)) {
                                $WarehouseID = $row_inventory['Warehouse_id'];

                                $query_inventory_details = "
                                    SELECT Bin_id, Row_id, Shelves_id, pack, quantity ,quantity_ttl
                                    FROM inventory 
                                    WHERE Warehouse_id = '$WarehouseID' AND Product_id = '$product_id'";
                                $result_inventory_details = mysqli_query($conn, $query_inventory_details);

                                if ($result_inventory_details && mysqli_num_rows($result_inventory_details) > 0) {
                                    $total_quantity = 0;
                                    while ($inventory = mysqli_fetch_assoc($result_inventory_details)) {
                                        $packs = $inventory['pack'];
                                        $quantity = $inventory['quantity'];
                                        $item_quantity = $inventory['quantity_ttl'];
                                        $total_quantity += $inventory['quantity_ttl'];

                                        $details[] = [
                                            'type' => 'BIN',
                                            'id' => $inventory['Bin_id'],
                                            'name' => getWarehouseBinName($inventory['Bin_id']),
                                            'quantity' => $item_quantity
                                        ];
                                        $details[] = [
                                            'type' => 'ROW',
                                            'id' => $inventory['Row_id'],
                                            'name' => getWarehouseRowName($inventory['Row_id']),
                                            'quantity' => $item_quantity
                                        ];
                                        $details[] = [
                                            'type' => 'SHELF',
                                            'id' => $inventory['Shelves_id'],
                                            'name' => getWarehouseShelfName($inventory['Shelves_id']),
                                            'quantity' => $item_quantity
                                        ];
                                    }

                                    echo "<div class='col-12 mt-3'>
                                            <div class='row p-3 border rounded bg-light'>
                                                <div class='col'>
                                                    <h5 class='mb-0 fs-5 fw-bold'>" . htmlspecialchars(getWarehouseName($WarehouseID)) . "</h5>
                                                </div>
                                                <div class='col text-end'>
                                                    <p class='mb-0 fs-3'><span class='badge bg-primary fs-3'>" . htmlspecialchars($total_quantity) . " PCS</span></p>
                                                </div>
                                            </div>
                                        </div>";

                                    foreach ($details as $detail) {
                                        if (!empty($detail['id']) && $detail['id'] != '0') {
                                            echo "<div class='col'>
                                                    <div class='row mb-0 p-2 border rounded bg-light'>
                                                        <h5 class='mb-0 fs-3 fw-bold'>{$detail['type']}: " . htmlspecialchars($detail['name']) . "</h5>
                                                        <p class='mb-0 fs-3'>{$packs} " . getPackName($packs) . " - " . htmlspecialchars($detail['quantity']) . " PCS</p>
                                                    </div>
                                                </div>";
                                        }
                                    }
                                    unset($details);
                                }
                            }
                            echo '</div>';
                        } else {
                            echo '<p class="mb-3 fs-4 fw-semibold text-center">This Product is not listed in the <a href="/?page=inventory">Inventory</a></p>';
                        }
                        ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
<?php
    }
}

if(isset($_POST['fetch_out_of_stock_modal'])){
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);

    $checkQuery = "SELECT * FROM product WHERE product_id = '$product_id'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        ?>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Stock Details</h6>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5 class="mb-3 fs-6 fw-semibold text-center">Out of Stock</h5>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
<?php
    }
}

if(isset($_POST['fetch_cart'])){
    ?>
    <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Cart Contents</h6>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="demo">
                        <div class="card-body">
                            <div class="product-details table-responsive text-nowrap">
                                <table id="productTable" class="table table-hover mb-0 text-md-nowrap">
                                    <thead>
                                        <tr>
                                            <th width="20%">Description</th>
                                            <th width="13%" class="text-center">Color</th>
                                            <th width="13%" class="text-center">Grade</th>
                                            <th width="13%" class="text-center">Profile</th>
                                            <th width="20%" class="text-center pl-3">Quantity</th>
                                            <th width="5%" class="text-center">Stock</th>
                                            <th width="10%" class="text-center">Price</i></th>
                                            <th width="6%" class="text-center">Action</i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $total = 0;
                                        $totalquantity = 0;
                                        if (!empty($_SESSION["cart"])) {
                                            foreach ($_SESSION["cart"] as $keys => $values) {
                                                $data_id = $values["product_id"];

                                                $totalstockquantity = $values["quantity_ttl"] + $values["quantity_in_stock"];

                                                if ($totalstockquantity > 0) {
                                                    $stock_text = '
                                                        <a href="javascript:void(0);" id="view_product_details" data-id="' . htmlspecialchars($data_id, ENT_QUOTES, 'UTF-8') . '" class="d-flex align-items-center">
                                                            <span class="text-bg-success p-1 rounded-circle"></span>
                                                            <span class="ms-2">In Stock</span>
                                                        </a>';
                                                } else {
                                                    $stock_text = '
                                                        <div class="d-flex align-items-center">
                                                            <span class="text-bg-danger p-1 rounded-circle"></span>
                                                            <span class="ms-2">Out of Stock</span>
                                                        </div>';
                                                } 
                                            ?>
                                                <tr>
                                                    <td>
                                                        <?php echo $values["product_item"]; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo getColorFromID($data_id); ?>
                                                        
                                                    </td>
                                                    <td>
                                                        <?php echo getGradeFromID($data_id); ?>
                                                        
                                                    </td>
                                                    <td>
                                                        <?php echo getProfileFromID($data_id); ?>
                                                        
                                                    </td>
                                                    <td>
                                                        <div class="input-group">
                                                            <span class="input-group-btn">
                                                                <button class="btn btn-primary btn-icon p-1 mr-1" type="button" data-id="<?php echo $data_id; ?>" onClick="deductquantity(this)">
                                                                    <i class="fa fa-minus"></i>
                                                                </button>
                                                            </span> 
                                                            <input class="form-control" type="text" size="5" value="<?php echo $values["quantity_cart"]; ?>" style="color:#ffffff;" onchange="updatequantity(this)" data-id="<?php echo $data_id; ?>" id="item_quantity<?php echo $data_id;?>">
                                                            <span class="input-group-btn">
                                                                <button class="btn btn-primary btn-icon p-1 ml-1" type="button" data-id="<?php echo $data_id; ?>" onClick="addquantity(this)">
                                                                    <i class="fa fa-plus"></i>
                                                                </button>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td><?= $stock_text ?></td>
                                                    <td class="text-end pl-3">$
                                                        <?php
                                                        $subtotal = ($values["quantity_cart"] * $values["unit_price"]);
                                                        echo number_format($subtotal, 2);
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-danger-gradient btn-sm" type="button" data-id="<?php echo $data_id; ?>" onClick="delete_item(this)"><i class="fa fa-trash"></i></button>
                                                        <input type="hidden" class="form-control" data-id="<?php echo $data_id; ?>" id="item_id<?php echo $data_id; ?>" value="<?php echo $values["product_id"]; ?>">
                                                        <input class="form-control" type="hidden" size="5" value="<?php echo $values["quantity_ttl"];?>" id="warehouse_stock<?php echo $data_id;?>">
                                                        <input class="form-control" type="hidden" size="5" value="<?php echo $values["quantity_in_stock"];?>" id="store_stock<?php echo $data_id;?>">
                                                    </td>
                                                </tr>
                                        <?php
                                                $totalquantity += $values["quantity_cart"];
                                                $total += $subtotal;
                                            }
                                            
                                        }
                                        $_SESSION["total_quantity"] = $totalquantity;
                                        $_SESSION["grandtotal"] = $total;
                                        ?>
                                    </tbody>

                                    <tfoot>
                                        <tr>
                                            <td colspan="2"></td>
                                            <td colspan="1" class="text-end">Total Quantity:</td>
                                            <td colspan="1" class=""><span id="qty_ttl"><?= $totalquantity ?></span></td>
                                            <td colspan="1" class="text-end">Amount Due:</td>
                                            <td colspan="1" class="text-end"><span id="ammount_due"><?= $total ?> $</span></td>
                                            <td colspan="1"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    <?php
}

if (isset($_POST['search_customer'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search_customer']);

    $query = "
        SELECT 
            customer_id AS value, 
            CONCAT(customer_first_name, ' ', customer_last_name) AS label
        FROM 
            customer
        WHERE 
            customer_first_name LIKE '%$search%' 
            OR customer_last_name LIKE '%$search%'
        LIMIT 15
    ";

    $result = mysqli_query($conn, $query);

    if ($result) {
        $response = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $response[] = $row;
        }
        echo json_encode($response);
    } else {
        echo json_encode(array('error' => 'Query failed'));
    }
}



