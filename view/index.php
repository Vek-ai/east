<?php
require '../includes/dbconn.php';
require '../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="dark" data-color-theme="Blue_Theme" data-layout="vertical">

<head>
  <!-- Required meta tags -->
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Favicon icon-->
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.14.0/jquery-ui.min.js"></script>

  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="../assets/libs/bootstrap/dist/css/bootstrap-multiselect.css">
  <link rel="stylesheet" href="../assets/libs/select2/dist/css/select2.min.css">
  <link rel="stylesheet" href="../assets/libs/owl.carousel/dist/assets/owl.carousel.min.css">
  <link href="https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone.css" rel="stylesheet" type="text/css" />
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/smoothness/jquery-ui.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
  

  <!-- Core Css -->
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <link rel="stylesheet" href="css/cashier.css" />

  <style>
    .page-wrapper{
        margin: 0 !important;
    }
    .datatables {
        overflow-x: auto;
    }
    .table-container {
        width: 100%;
        overflow-x: auto !important;
    }
  </style>

  <title>View</title>

</head>

<body>
  <!-- Preloader -->
  <div class="preloader">
    <img src="../assets/images/logos/logo-icon.svg" alt="loader" class="lds-ripple img-fluid" />
  </div>
  <div id="main-wrapper">
    <div class="page-wrapper">

      <div class="body-wrapper">
        <div class="container-fluid">
            <?php
            $line_item = mysqli_real_escape_string($conn, $_REQUEST['line_item'] ?? '');

            if (!empty($line_item)) {
                $query = "SELECT * FROM work_order WHERE upc = '$line_item'";
                $result = mysqli_query($conn, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $product_details = getProductDetails($row['productid']);
                    $status_prod_db = (int)$row['status'];

                    $status_prod_labels = [
                        0 => ['label' => 'New Pending', 'class' => 'badge bg-primary'],
                        1 => ['label' => 'Approved', 'class' => 'badge bg-success'],
                        2 => ['label' => 'Running on Machine', 'class' => 'badge bg-warning'],
                        3 => ['label' => 'Waiting for Dispatch', 'class' => 'badge bg-secondary']
                    ];

                    $status_prod = $status_prod_labels[$status_prod_db];
                    $filepath = !empty($product_details['main_image']) ? $product_details['main_image'] : "images/product/product.jpg";
                    $picture_path = "../" . $filepath;
                    $product_name = !empty($row['product_item']) ? $row['product_item'] : getProductName($row['product_id']);

                    $color_details = getColorDetails($row['custom_color']);
                    $color_name = ucwords($color_details['color_name'] ?? '');
                    $color_hex = $color_details['color_code'] ?? '';
                    ?>

                    <div class="container py-4">
                        <h3 class="fw-bold mb-4">Line Item Details</h3>

                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="row g-4 align-items-center">
                                    <div class="col-md-2 text-center">
                                        <img src="<?= $picture_path ?>" class="rounded-circle img-fluid" style="max-width: 100px;" alt="Product Image">
                                    </div>
                                    <div class="col-md-10">
                                        <h5 class="fw-semibold mb-2"><?= $product_name ?></h5>

                                        <div class="row">
                                            <div class="col-sm-6 mb-2">
                                                <div class="fw-bold">Color:</div>
                                                <div class="ps-3">
                                                    <span class="d-inline-block rounded-circle me-2" style="width: 16px; height: 16px; background-color: <?= $color_hex ?>;"></span>
                                                    <?= $color_name ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mb-2">
                                                <div class="fw-bold">Grade:</div>
                                                <div class="ps-3"><?= getGradeName($product_details['grade']) ?></div>
                                            </div>
                                            <div class="col-sm-6 mb-2">
                                                <div class="fw-bold">Profile:</div>
                                                <div class="ps-3"><?= getProfileTypeName($product_details['profile']) ?></div>
                                            </div>
                                            <div class="col-sm-6 mb-2">
                                                <div class="fw-bold">Quantity:</div>
                                                <div class="ps-3"><?= $row['quantity'] ?></div>
                                            </div>
                                            <div class="col-sm-6 mb-2">
                                                <div class="fw-bold">Status:</div>
                                                <div class="ps-3"><span class="<?= $status_prod['class']; ?>"><?= $status_prod['label']; ?></span></div>
                                            </div>
                                            <div class="col-sm-6 mb-2">
                                                <div class="fw-bold">Dimensions:</div>
                                                <div class="ps-3">
                                                    <?php 
                                                        $width = $row['custom_width'];
                                                        $height = $row['custom_height'];
                                                        if (!empty($width) && !empty($height)) {
                                                            echo htmlspecialchars($width) . " x " . htmlspecialchars($height);
                                                        } elseif (!empty($width)) {
                                                            echo "Width: " . htmlspecialchars($width);
                                                        } elseif (!empty($height)) {
                                                            echo "Height: " . htmlspecialchars($height);
                                                        } else {
                                                            echo "N/A";
                                                        }
                                                    ?>
                                                </div>
                                            </div>
                                        </div> <!-- /.row -->
                                    </div> <!-- /.col-md-10 -->
                                </div> <!-- /.row -->
                            </div> <!-- /.card-body -->
                        </div> <!-- /.card -->
                    </div>

                    <?php
                } else {
                    echo "<div class='alert alert-warning'>No line item found for this UPC.</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>No UPC provided.</div>";
            }
            ?>

        </div>
      </div>
      <script>
  function handleColorTheme(e) {
    $("html").attr("data-color-theme", e);
    $(e).prop("checked", !0);
  }
</script>
      

</div>
  </div>
  <div class="dark-transparent sidebartoggler"></div>
  <script src="../assets/js/vendor.min.js"></script>
  <!-- Import Js Files -->
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.min.js"></script>
  <script src="../assets/js/theme/app.dark.init.js"></script>
  <script src="../assets/js/theme/theme.js"></script>
  <script src="../assets/js/theme/app.min.js"></script>
  <script src="../assets/js/theme/feather.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap-multiselect.min.js"></script>

  <!-- solar icons -->
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  <script src="../assets/libs/jvectormap/jquery-jvectormap.min.js"></script>
  <script src="../assets/js/extra-libs/jvectormap/jquery-jvectormap-us-aea-en.js"></script>
  <script src="../assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
  <script src="../assets/libs/inputmask/dist/jquery.inputmask.min.js"></script>
  <script src="../assets/libs/select2/dist/js/select2.min.js"></script>
  <script src="../assets/libs/owl.carousel/dist/owl.carousel.min.js"></script>
  <script src="https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone-min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
</body>

<script>
    $(document).ready(function () {
        $('#view_tbl').DataTable({
            pageLength: 100,
            lengthChange: false,
            order: [],
            responsive: true,
            autoWidth: false
        });
    });
</script>

</html>