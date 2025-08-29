<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_POST['fetch_details_modal'])){
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);

    $checkQuery = "SELECT * FROM product WHERE product_id = '$product_id'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        ?>
        <style>
        #sync1 .item img {
            width: 100%;
            height: 400px;
            object-fit: contain;
            object-position: center;
            border-radius: 6px;
            display: block;
        }
        #sync2 .item img {
            width: 100%;
            height: 50px;
            object-fit: contain;
            object-position: center;
            border-radius: 4px;
            display: block;
        }
        </style>
        <div class="row">
            <div class="col-lg-6">
                <div id="sync1" class="owl-carousel owl-theme">
                    <?php
                        $query_prod_img = "SELECT * FROM product_images WHERE productid = '$product_id'";
                        $result_prod_img = mysqli_query($conn, $query_prod_img);  

                        if ($result_prod_img && mysqli_num_rows($result_prod_img) > 0) {
                            while ($row_prod_img = mysqli_fetch_array($result_prod_img)) {
                                $image_url = !empty($row_prod_img['image_url'])
                                    ? "../" . $row_prod_img['image_url']
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
                        if ($result_prod_img && mysqli_num_rows($result_prod_img) > 0) {
                            mysqli_data_seek($result_prod_img, 0);
                            while ($row_prod_img = mysqli_fetch_array($result_prod_img)) {
                                $image_url = !empty($row_prod_img['image_url'])
                                    ? "../" . $row_prod_img['image_url']
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
                        if ($totalQuantity > 0) {
                            ?>
                            <span class="badge text-bg-success fs-2 fw-semibold">In Stock</span>
                            <?php
                        } else {
                            ?>
                            <span class="badge text-bg-danger fs-2 fw-semibold">Out of Stock</span>
                            <?php
                        }
                        ?>

                        <span class="fs-2"><?= getProductCategoryName($row['product_category']) ?></span>
                    </div>

                    <h4><?= $row['product_item'] ?></h4>
                    <p class="mb-3"><?= $row['description'] ?></p>

                    <?php
                    $inventoryList = getAvailableInventory($row['product_id']);
                    if (!empty($inventoryList)) {
                        ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Color</th>
                                        <th>Dimensions</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inventoryList as $inv) { ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($inv['color_id'])) { ?>
                                                    <span class="d-inline-block rounded-circle me-2" 
                                                        style="width:20px; height:20px; background-color:<?= getColorHexFromColorID($inv['color_id']) ?>;">
                                                    </span>
                                                <?php } else { ?>
                                                    None
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($inv['dimension'] . ' ' . $inv['dimension_unit']) ?>
                                            </td>
                                            <td>$<?= number_format($inv['price'], 2) ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>

            <div class="col-lg-12">
                <?php
                    $statusMessages = [];
                    if (!empty($row['on_sale']) && $row['on_sale'] == 1) {
                        $statusMessages[] = "<span class='badge bg-success me-1'>On Sale</span>";
                    }
                    if (!empty($row['on_promotion']) && $row['on_promotion'] == 1) {
                        $statusMessages[] = "<span class='badge bg-warning text-dark'>On Promotion</span>";
                    }
                    
                    if (!empty($statusMessages)) {
                        echo "<h5>Status: " . implode(" & ", $statusMessages) . "</h5>";

                        if (!empty($row['reason'])) {
                            echo "<p class='mb-0'><em>Reason:</em> " . htmlspecialchars($row['reason']) . "</p>";
                        }
                    }
                ?>
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