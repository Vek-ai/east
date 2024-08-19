<?php
require 'includes/dbconn.php';
require 'includes/functions.php';
if (!empty($_REQUEST['product_id'])) {
    $product_id_req = mysqli_real_escape_string($conn, $_REQUEST['product_id']);
    $query = "SELECT * FROM product WHERE product_id = '$product_id_req'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        $product_id = $row['product_id'];
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
                <div class="d-flex align-items-center gap-8 pb-4 border-bottom">
                <ul class="list-unstyled d-flex align-items-center mb-0">
                    <li>
                    <a class="me-1" href="javascript:void(0)">
                        <i class="ti ti-star text-warning fs-4"></i>
                    </a>
                    </li>
                    <li>
                    <a class="me-1" href="javascript:void(0)">
                        <i class="ti ti-star text-warning fs-4"></i>
                    </a>
                    </li>
                    <li>
                    <a class="me-1" href="javascript:void(0)">
                        <i class="ti ti-star text-warning fs-4"></i>
                    </a>
                    </li>
                    <li>
                    <a class="me-1" href="javascript:void(0)">
                        <i class="ti ti-star text-warning fs-4"></i>
                    </a>
                    </li>
                    <li>
                    <a href="javascript:void(0)">
                        <i class="ti ti-star text-warning fs-4"></i>
                    </a>
                    </li>
                </ul>
                <a href="javascript:void(0)">(236 reviews)</a>
                </div>
                <div class="d-flex align-items-center gap-8 py-7">
                <h6 class="mb-0 fs-4 fw-semibold">Colors:</h6>
                <a class="rounded-circle d-block text-bg-primary p-6" href="javascript:void(0)"></a>
                </div>
                <div class="d-flex align-items-center gap-7 pb-7 mb-7 border-bottom">
                <h6 class="mb-0 fs-4 fw-semibold">QTY:</h6>
                <div class="input-group input-group-sm rounded">
                    <button class="btn minus min-width-40 py-0 border-end border-muted fs-5 border-end-0 text-muted" type="button" id="add1">
                    <i class="ti ti-minus"></i>
                    </button>
                    <input type="text" class="min-width-40 flex-grow-0 border border-muted text-muted fs-4 fw-semibold form-control text-center qty" placeholder="" aria-label="Example text with button addon" aria-describedby="add1" value="1">
                    <button class="btn min-width-40 py-0 border border-muted fs-5 border-start-0 text-muted add" type="button" id="addo2">
                    <i class="ti ti-plus"></i>
                    </button>
                </div>
                </div>
                <div class="d-sm-flex align-items-center gap-6 pt-8 mb-7">
                <a href="javascript:void(0)" class="btn d-block btn-primary px-5 py-8 mb-6 mb-sm-0">Buy Now</a>
                <a href="javascript:void(0)" class="btn d-block btn-danger px-7 py-8">Add to Cart</a>
                </div>
                <p class="mb-0">Dispatched in 2-3 weeks</p>
                <a href="javascript:void(0)">Why the longer time for delivery?</a>
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
            $query_rel_prod = "SELECT * FROM product WHERE product_id != '$product_id' LIMIT 4";
            $result_rel_prod = mysqli_query($conn, $query_rel_prod);            
            while ($row_rel_prod = mysqli_fetch_array($result_rel_prod)) {
                if($row_rel_prod['main_image']){
                    $img_src = $row_rel_prod['main_image'];
                }else{
                    $img_src = "images/product/product.jpg";
                }
            ?>
            <div class="col-sm-6 col-xxl-3">
                <div class="card overflow-hidden rounded-2">
                <div class="position-relative">
                    <a href="/?page=product_details&product_id=<?=$row_rel_prod['product_id']?>" class="hover-img d-block overflow-hidden">
                    <img src="<?= $img_src ?>" class="card-img-top rounded-0" alt="materialpro-img">
                    </a>
                </div>
                <div class="card-body pt-3 p-4">
                    <h6 class="fw-semibold fs-4"><?= $row_rel_prod['product_item'] ?></h6>
                    <div class="d-flex align-items-center justify-content-between">
                    <h6 class="fw-semibold fs-4 mb-0">$<?= $row_rel_prod['unit_price'] ?>
                    </h6>
                    <ul class="list-unstyled d-flex align-items-center mb-0">
                        <li>
                        <a class="me-1" href="javascript:void(0)">
                            <i class="ti ti-star text-warning"></i>
                        </a>
                        </li>
                        <li>
                        <a class="me-1" href="javascript:void(0)">
                            <i class="ti ti-star text-warning"></i>
                        </a>
                        </li>
                        <li>
                        <a class="me-1" href="javascript:void(0)">
                            <i class="ti ti-star text-warning"></i>
                        </a>
                        </li>
                        <li>
                        <a class="me-1" href="javascript:void(0)">
                            <i class="ti ti-star text-warning"></i>
                        </a>
                        </li>
                        <li>
                        <a href="javascript:void(0)">
                            <i class="ti ti-star text-warning"></i>
                        </a>
                        </li>
                    </ul>
                    </div>
                </div>
                </div>
            </div>
            <?php 
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