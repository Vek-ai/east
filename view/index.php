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

                if(!empty($line_item)){
                    $query = "SELECT * FROM work_order WHERE upc IN ($line_item)";
                    $result = mysqli_query($conn, $query);
                    
                    if ($result && mysqli_num_rows($result) > 0) {
                        $response = array();
                        ?>
                        <div class="datatables">
                            <h3 class="fw-bold">View Line Items</h3>
                            <div class="table-responsive" style="overflow-y: hidden !important">
                                <table id="view_tbl" class="table table-hover mb-0 text-center align-middle">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th class="text-center">Color</th>
                                            <th class="text-center">Grade</th>
                                            <th class="text-center">Profile</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Dimensions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $totalquantity = 0;
                                            $total_actual_price = 0;
                                            $total_disc_price = 0;
                                            $total_amount = 0;
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                $orderid = $row['work_order_id'];
                                                $product_details = getProductDetails($row['productid']);

                                                $is_stockable = $product_details['product_origin'] == 1;

                                                $status_prod_db = (int)$row['status'];

                                                $price = $row['discounted_price'];

                                                $product_name = '';
                                                if(!empty($row['product_item'])){
                                                    $product_name = $row['product_item'];
                                                }else{
                                                    $product_name = getProductName($row['product_id']);
                                                }

                                                $status_prod_labels = [
                                                    0 => ['label' => 'New', 'class' => 'badge bg-primary'],
                                                    1 => ['label' => 'Processing', 'class' => 'badge bg-success'],
                                                    2 => ['label' => 'Waiting for Dispatch', 'class' => 'badge bg-warning'],
                                                    3 => ['label' => 'In Transit', 'class' => 'badge bg-secondary'],
                                                    4 => ['label' => 'Delivered', 'class' => 'badge bg-success'],
                                                    5 => ['label' => 'On Hold', 'class' => 'badge bg-danger'],
                                                    6 => ['label' => 'Returned', 'class' => 'badge bg-danger']
                                                ];

                                                $status_prod = $status_prod_labels[$status_prod_db];
                                                $filepath = !empty($product_details['main_image']) ? $product_details['main_image'] : "images/product/product.jpg";

                                                $picture_path = "../" . $filepath;
                                            ?> 
                                                <tr> 
                                                    <td class="text-start">
                                                        <a href='javascript:void(0)'>
                                                            <div class='d-flex align-items-center'>
                                                                <img src='<?= $picture_path ?>' class='rounded-circle' width='56' height='56'>
                                                                <div class='ms-3'>
                                                                    <h6 class='fw-semibold mb-0 fs-4'><?= $product_name ?></h6>
                                                                </div>
                                                            </div>
                                                        </a>
                                                        
                                                    </td>
                                                    <td>
                                                    <div class="d-flex mb-0 gap-8">
                                                        <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:<?= getColorHexFromProdID($product_details['color'])?>"></a>
                                                        <?= getColorFromID($product_details['color']); ?>
                                                    </div>
                                                    </td>
                                                    <td>
                                                        <?php echo getGradeName($product_details['grade']); ?>
                                                    </td>
                                                    <td>
                                                        <?php echo getProfileTypeName($product_details['profile']); ?>
                                                    </td>
                                                    <td><?= $row['quantity'] ?></td>
                                                    <td>
                                                        <span class="<?= $status_prod['class']; ?> fw-bond"><?= $status_prod['label']; ?></span>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $width = $row['custom_width'];
                                                        $height = $row['custom_height'];
                                                        
                                                        if (!empty($width) && !empty($height)) {
                                                            echo htmlspecialchars($width) . " X " . htmlspecialchars($height);
                                                        } elseif (!empty($width)) {
                                                            echo "Width: " . htmlspecialchars($width);
                                                        } elseif (!empty($height)) {
                                                            echo "Height: " . htmlspecialchars($height);
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                        <?php
                                                $totalquantity += $row['quantity'] ;
                                                $total_actual_price += $row['actual_price'];
                                                $total_disc_price += $row['discounted_price'];
                                                $total_amount += floatval($row['discounted_price']);
                                            }
                                        
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php
                    }
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