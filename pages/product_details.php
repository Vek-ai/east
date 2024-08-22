<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);
require 'includes/dbconn.php';
require 'includes/functions.php';
function isValidHexColor($color) {
    return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color);
}
if (!empty($_REQUEST['product_id'])) {
    $product_id_req = mysqli_real_escape_string($conn, $_REQUEST['product_id']);
    $query = "SELECT * FROM product WHERE product_id = '$product_id_req'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        $product_id = $row['product_id'];
        $color = $row['color'];
?>

<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="font-weight-medium fs-14 mb-0">Product Detail</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Product Detail</li>
            </ol>
            </nav>
        </div>
        <div>
            <div class="d-sm-flex d-none gap-3 no-block justify-content-end align-items-center">
            <div class="d-flex gap-2">
                <div class="">
                <small>This Month</small>
                <h4 class="text-primary mb-0 ">$58,256</h4>
                </div>
                <div class="">
                <div class="breadbar"></div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <div class="">
                <small>Last Month</small>
                <h4 class="text-secondary mb-0 ">$58,256</h4>
                </div>
                <div class="">
                <div class="breadbar2"></div>
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
    </div>

    <div class="shop-detail">
    <div class="card">
        <div class="card-body p-4">
        <div class="row">
            <div class="col-lg-6">
            <div id="sync1" class="owl-carousel owl-theme">
                <?php
                $query_prod_img1 = "SELECT * FROM product_images WHERE productid = '$product_id'";
                $result_prod_img1 = mysqli_query($conn, $query_prod_img1);  
                if ($result && mysqli_num_rows($result_prod_img1) > 0) {
                    while ($row_prod_img1 = mysqli_fetch_array($result_prod_img1)) {
                    ?>
                    <div class="item rounded overflow-hidden">
                        <img src="<?=$row_prod_img1['image_url']?>" alt="materialpro-img" class="img-fluid">
                    </div>
                    <?php 
                    }
                }else{
                    ?>
                    <div class="item rounded overflow-hidden">
                        <img src="images/product/product.jpg" alt="materialpro-img" class="img-fluid">
                    </div>
                    <?php
                } 
                ?> 
            </div>

            <div id="sync2" class="owl-carousel owl-theme">
                <?php
                $query_prod_img2 = "SELECT * FROM product_images WHERE productid = '$product_id'";
                $result_prod_img2 = mysqli_query($conn, $query_prod_img2);  
                if ($result && mysqli_num_rows($result_prod_img2) > 0) {
                    while ($row_prod_img2 = mysqli_fetch_array($result_prod_img2)) {
                    ?>
                    <div class="item rounded overflow-hidden">
                        <img src="<?=$row_prod_img2['image_url']?>" alt="materialpro-img" class="img-fluid">
                    </div>
                    <?php 
                    }
                }else{
                    ?>
                    <div class="item rounded overflow-hidden">
                        <img src="images/product/product.jpg" alt="materialpro-img" class="img-fluid">
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
                if($row['quantity_in_stock'] > 0){
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
                    $query_color = "SELECT * FROM paint_colors WHERE color_id = '$color'";
                    $result_color = mysqli_query($conn, $query_color);  
                    if ($result_color && mysqli_num_rows($result_color) > 0) {
                    ?>
                        <h6 class="mb-0 fs-4 fw-semibold">Colors:</h6>
                    <?php
                        while ($row_color = mysqli_fetch_array($result_color)) {
                            if(isValidHexColor($row_color['color_code'])){
                        ?>
                        <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color: <?= $row_color['color_code'] ?>"></a>
                        <?php 
                            }
                        }
                    }
                    ?> 
                </div>

                <div class="container py-4">
                    <h5 class="mb-3 fs-6 fw-semibold text-center">Inventory</h5>
                    <?php
                    $query_inventory = "SELECT DISTINCT Warehouse_id FROM inventory WHERE Product_id = '$product_id' AND Warehouse_id != '0'";
                    $result_inventory = mysqli_query($conn, $query_inventory);

                    if ($result_inventory && mysqli_num_rows($result_inventory) > 0) {
                        ?>
                        <div class="row">
                        <?php
                        while ($row_inventory = mysqli_fetch_array($result_inventory)) {
                            $WarehouseID = $row_inventory['Warehouse_id'];
                            
                            $total_quantity = 0;
                            $query_warehouse = "SELECT * FROM inventory WHERE Warehouse_id = '$WarehouseID' AND Product_id = '$product_id'";
                            $result_warehouse = mysqli_query($conn, $query_warehouse);
                            while ($row_warehouse = mysqli_fetch_array($result_warehouse)) {
                                $packs = $row_warehouse['pack'];
                                $quantity = $row_warehouse['quantity'];
                                $total_quantity += getPackPieces($packs) ? getPackPieces($packs) * $quantity : $quantity;

                            }
                            
                            ?>
                            <div class="col-12 mt-3">
                                <div class="row p-3 border rounded bg-light">
                                    <div class="col">
                                        <h5 class="mb-0 fs-5 fw-bold"><?= getWarehouseName($WarehouseID) ?></h5>
                                    </div>
                                    <div class="col text-end">
                                        <p class="mb-0 fs-3">
                                            <span id class="badge bg-primary fs-3">
                                                <?= $total_quantity ?? '0' ?> PCS
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <?php
                            // Query to get inventory details for bins, rows, and shelves
                            $query_inventory_details = "
                                SELECT Bin_id, Row_id, Shelves_id, pack, quantity 
                                FROM inventory 
                                WHERE Warehouse_id = '$WarehouseID' AND Product_id = '$product_id'";
                            $result_inventory_details = mysqli_query($conn, $query_inventory_details);

                            if ($result_inventory_details && mysqli_num_rows($result_inventory_details) > 0) {
                                while ($inventory = mysqli_fetch_array($result_inventory_details)) {
                                    $bin_id = $inventory['Bin_id'];
                                    $row_id = $inventory['Row_id'];
                                    $shelves_id = $inventory['Shelves_id'];
                                    $packs = $inventory['pack'];
                                    $quantity = $inventory['quantity'];
                                    $total_quantity = getPackPieces($packs) ? getPackPieces($packs) * $quantity : $quantity;
                                    
                                    // Display bin details
                                    if (!empty($bin_id) && $bin_id != '0') {
                                        ?>
                                        <div class="col">
                                            <div class="row mb-0 p-2 border rounded bg-light">
                                                <h5 class="mb-0 fs-3 fw-bold">BIN: <?= getWarehouseBinName(htmlspecialchars($bin_id)) ?></h5>
                                                <p class="mb-0 fs-3">
                                                    <?php echo ($packs != '0') ? $packs ." " .getPackName($packs) ." - " : '' ?><?= htmlspecialchars($total_quantity) ?> PCS
                                                </p>
                                            </div>
                                        </div>
                                        <?php
                                    }

                                    // Display row details
                                    if (!empty($row_id) && $row_id != '0') {
                                        ?>
                                        <div class="col">
                                            <div class="row mb-0 p-2 border rounded bg-light">
                                                <h5 class="mb-0 fs-3 fw-bold">ROW: <?= getWarehouseRowName(htmlspecialchars($row_id)) ?></h5>
                                                <p class="mb-0 fs-3">
                                                    <?php echo ($packs != '0') ? $packs ." " .getPackName($packs) ." - " : '' ?><?= htmlspecialchars($total_quantity) ?> PCS
                                                </p>
                                            </div>
                                        </div>
                                        <?php
                                    }

                                    // Display shelf details
                                    if (!empty($shelves_id) && $shelves_id != '0') {
                                        ?>
                                        <div class="col">
                                            <div class="row mb-0 p-2 border rounded bg-light">
                                                <h5 class="mb-0 fs-3 fw-bold">SHELF: <?= getWarehouseShelfName(htmlspecialchars($shelves_id)) ?></h5>
                                                <p class="mb-0 fs-3">
                                                    <?php echo ($packs != '0') ? $packs ." " .getPackName($packs) ." - " : '' ?><?= htmlspecialchars($total_quantity) ?> PCS
                                                </p>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                            }
                        }
                        ?>
                        </div>
                        <?php
                    } else {
                        ?>
                        <p class="mb-3 fs-4 fw-semibold text-center">
                            This Product is not listed in the <a href="/?page=inventory">Inventory</a>
                        </p>
                        <?php
                    }
                    ?> 
                </div>


            </div>
            </div>
        </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body p-4">
        <ul class="nav nav-pills user-profile-tab border-bottom" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
            <button class="nav-link position-relative rounded-0 active d-flex align-items-center justify-content-center bg-transparent fs-3 py-6" id="pills-description-tab" data-bs-toggle="pill" data-bs-target="#pills-description" type="button" role="tab" aria-controls="pills-description" aria-selected="true">
                Description
            </button>
            </li>
            <li class="nav-item" role="presentation">
            <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-6" id="pills-reviews-tab" data-bs-toggle="pill" data-bs-target="#pills-reviews" type="button" role="tab" aria-controls="pills-reviews" aria-selected="false">
                Reviews
            </button>
            </li>
        </ul>
        <div class="tab-content pt-4" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-description" role="tabpanel" aria-labelledby="pills-description-tab" tabindex="0">
            <h5 class="fs-5 fw-semibold mb-7">
                <?= $row['description'] ?>
            </h5>
            </div>
            <div class="tab-pane fade" id="pills-reviews" role="tabpanel" aria-labelledby="pills-reviews-tab" tabindex="0">
            <div class="row">
                <div class="col-lg-4 d-flex align-items-stretch">
                <div class="card shadow-none border w-100 mb-7 mb-lg-0">
                    <div class="card-body text-center p-4 d-flex flex-column justify-content-center">
                    <h6 class="mb-3">Average Rating</h6>
                    <h2 class="text-primary mb-3 fw-semibold fs-9">4/5</h2>
                    <ul class="list-unstyled d-flex align-items-center justify-content-center mb-0">
                        <li>
                        <a class="me-1" href="javascript:void(0)">
                            <i class="ti ti-star text-warning fs-6"></i>
                        </a>
                        </li>
                        <li>
                        <a class="me-1" href="javascript:void(0)">
                            <i class="ti ti-star text-warning fs-6"></i>
                        </a>
                        </li>
                        <li>
                        <a class="me-1" href="javascript:void(0)">
                            <i class="ti ti-star text-warning fs-6"></i>
                        </a>
                        </li>
                        <li>
                        <a class="me-1" href="javascript:void(0)">
                            <i class="ti ti-star text-warning fs-6"></i>
                        </a>
                        </li>
                        <li>
                        <a href="javascript:void(0)">
                            <i class="ti ti-star text-warning fs-6"></i>
                        </a>
                        </li>
                    </ul>
                    </div>
                </div>
                </div>
                <div class="col-lg-4 d-flex align-items-stretch">
                <div class="card shadow-none border w-100 mb-7 mb-lg-0">
                    <div class="card-body p-4 d-flex flex-column justify-content-center">
                    <div class="d-flex align-items-center gap-9 mb-3">
                        <span class="flex-shrink-0 fs-2">1 Stars</span>
                        <div class="progress bg-primary-subtle w-100 h-4">
                        <div class="progress-bar" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: 45%;"></div>
                        </div>
                        <h6 class="mb-0">(485)</h6>
                    </div>
                    <div class="d-flex align-items-center gap-9 mb-3">
                        <span class="flex-shrink-0 fs-2">2 Stars</span>
                        <div class="progress bg-primary-subtle w-100 h-4">
                        <div class="progress-bar" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width: 25%;"></div>
                        </div>
                        <h6 class="mb-0">(215)</h6>
                    </div>
                    <div class="d-flex align-items-center gap-9 mb-3">
                        <span class="flex-shrink-0 fs-2">3 Stars</span>
                        <div class="progress bg-primary-subtle w-100 h-4">
                        <div class="progress-bar" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 20%;"></div>
                        </div>
                        <h6 class="mb-0">(110)</h6>
                    </div>
                    <div class="d-flex align-items-center gap-9 mb-3">
                        <span class="flex-shrink-0 fs-2">4 Stars</span>
                        <div class="progress bg-primary-subtle w-100 h-4">
                        <div class="progress-bar" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: 80%;"></div>
                        </div>
                        <h6 class="mb-0">(620)</h6>
                    </div>
                    <div class="d-flex align-items-center gap-9">
                        <span class="flex-shrink-0 fs-2">5 Stars</span>
                        <div class="progress bg-primary-subtle w-100 h-4">
                        <div class="progress-bar" role="progressbar" aria-valuenow="12" aria-valuemin="0" aria-valuemax="100" style="width: 12%;"></div>
                        </div>
                        <h6 class="mb-0">(160)</h6>
                    </div>
                    </div>
                </div>
                </div>
                <div class="col-lg-4 d-flex align-items-stretch">
                <div class="card shadow-none border w-100 mb-7 mb-lg-0">
                    <div class="card-body p-4 d-flex flex-column justify-content-center">
                    <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-2 mx-auto">
                        <i class="ti ti-pencil fs-7"></i>Write an Review
                    </button>
                    </div>
                </div>
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
    <div class="related-products pt-7">
        <h4 class="mb-3 fw-semibold">Related Products</h4>
        <div class="row">
        <?php
            $correlated = array();
            $query_correlated_prod = "SELECT correlated_id FROM correlated_product WHERE main_correlated_product_id = '$product_id' LIMIT 4";
            $result_correlated_prod = mysqli_query($conn, $query_correlated_prod);

            while ($row_correlated_prod = mysqli_fetch_array($result_correlated_prod)) {
                $correlated[] = $row_correlated_prod['correlated_id'];
            }

            if (!empty($correlated)) {
                $correlated_ids = implode(',', array_map('intval', $correlated));
                $query_rel_prod = "SELECT * FROM product WHERE product_id IN ($correlated_ids) AND product_id != '$product_id' LIMIT 4";
                $result_rel_prod = mysqli_query($conn, $query_rel_prod);

                while ($row_rel_prod = mysqli_fetch_array($result_rel_prod)) {
                    $img_src = $row_rel_prod['main_image'] ? $row_rel_prod['main_image'] : "images/product/product.jpg";
            ?>
                    <div class="col-sm-6 col-xxl-3">
                        <div class="card overflow-hidden rounded-2">
                            <div class="position-relative">
                                <a href="/?page=product_details&product_id=<?= $row_rel_prod['product_id'] ?>" class="hover-img d-block overflow-hidden">
                                    <img src="<?= $img_src ?>" class="card-img-top rounded-0" alt="materialpro-img">
                                </a>
                            </div>
                            <div class="card-body pt-3 p-4">
                                <h6 class="fw-semibold fs-4"><?= htmlspecialchars($row_rel_prod['product_item']) ?></h6>
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="fw-semibold fs-4 mb-0">$<?= htmlspecialchars($row_rel_prod['unit_price']) ?></h6>
                                    <ul class="list-unstyled d-flex align-items-center mb-0">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <li>
                                                <a class="me-1" href="javascript:void(0)">
                                                    <i class="ti ti-star text-warning"></i>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
            <?php
                }
            }
            ?>

            
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
?>