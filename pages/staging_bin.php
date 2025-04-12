<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Staging Bin";
?>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0">Staging Bin</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="?page=">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Staging Bin</li>
            </ol>
            </nav>
        </div>
        </div>
    </div>
    </div>

    <div class="widget-content searchable-container list">

    <div class="modal fade" id="response-modal" tabindex="-1" aria-labelledby="vertical-center-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div id="responseHeaderContainer" class="modal-header align-items-center modal-colored-header">
                <h4 id="responseHeader" class="m-0"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                <p id="responseMsg"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary waves-effect text-start" data-bs-dismiss="modal">
                Close
                </button>
            </div>
            </div>
        </div>
    </div>
    
    <div class="card card-body">
        <div class="card-body datatables">
            <div class="product-details table-responsive text-nowrap">
                <table id="staging_bin_tbl" class="table table-hover mb-0 text-md-nowrap">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-center">Date</th>
                            <!-- <th>Action</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $no = 1;
                            $query_product = "
                                SELECT
                                    *
                                FROM
                                    staging_bin AS sb
                                LEFT JOIN product AS p
                                ON
                                    sb.product_id = p.product_id
                                WHERE
                                    1
                            ";

                            $result_product = mysqli_query($conn, $query_product);            
                            while ($row_product = mysqli_fetch_array($result_product)) {
                                $product_id = $row_product['product_id'];

                                $quantity = $row_product['quantity'];
                                $date = date('F j, Y \a\t g:i A', strtotime($row_product['date']));

                                if(!empty($row_product['main_image'])){
                                    $picture_path = $row_product['main_image'];
                                }else{
                                    $picture_path = "images/product/product.jpg";
                                }
            
                            ?>
                                <tr class="search-items" 
                                    >
                                    <td>
                                        <a href="/?page=product_details&product_id=<?= $row_product['product_id'] ?>">
                                            <div class="d-flex align-items-center">
                                                <img src="<?= $picture_path ?>" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                                                <div class="ms-3">
                                                    <h6 class="fw-semibold mb-0 fs-4"><?= $row_product['product_item'] ?></h6>
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?= $quantity ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?= $date ?>
                                    </td>
                                    <!-- <td>
                                        <div class="action-btn text-center">
                                            
                                        </div>
                                    </td> -->
                                </tr>
                            <?php 
                            $no++;
                            } ?>
                        </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        document.title = "<?= $page_title ?>";

        var table = $('#staging_bin_tbl').DataTable();
    });
</script>