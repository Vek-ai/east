<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

if(isset($_REQUEST['id'])){
  $supplier_id = $_REQUEST['id'];
  $supplier_details = getSupplierDetails($supplier_id);
}
?>

<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0">Supplier Dashboard</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="?page=product_supplier">Supplier
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Supplier Dashboard</li>
            </ol>
            </nav>
        </div>
        <div>
            <div class="d-sm-flex d-none gap-3 no-block justify-content-end align-items-center">
            <div class="d-flex gap-2">
                <div class="">
                <small>This Month</small>
                <?php
                    $this_month = 0;
                ?>
                <h4 class="text-primary mb-0 ">$<?= number_format($this_month,2) ?></h4>
                </div>
                <div class="">
                <div class="breadbar"></div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <div class="">
                <small>Last Month</small>
                <?php
                    $last_month = 0;
                ?>
                <h4 class="text-secondary mb-0 ">$<?= number_format($last_month,2) ?></h4>
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

    <div class="row">
    <div class="col-lg-3 col-md-12">
    <div class="card">
      <div class="card-body p-2">
        <img class="card-img-top w-100 profile-bg-height rounded overflow-hidden" src="assets/images/backgrounds/profile-bg.jpg" height="111" alt="Card image cap" />
        <div class="card-body little-profile text-center p-9">
            <div class="pro-img mb-3">
            <?php
                $logoPath = 'assets/images/profile/user-2.jpg';

                if (!empty($supplier_details['logo_path']) && file_exists($supplier_details['logo_path'])) {
                    $logoPath = $supplier_details['logo_path'];
                }
                ?>

                <img src="<?= $logoPath ?>" alt="user" class="rounded-circle shadow-sm" width="112" />
            </div>
            <h3 class="mb-1 fs-14"><?= $supplier_details['supplier_name'] ?></h3>
            <p class="fs-4 mb-0"><?= $supplier_details['contact_email'] ?></p>
            <p class="fs-3 mb-4"><?= $supplier_details['contact_phone'] ?></p>
          <div class="row gx-lg-4 text-center pt-4 justify-content-center border-top">
            <div class="col-4">
              <?php
              $query_order_count = "SELECT COUNT(*) AS total_count
                                    FROM inventory AS i
                                    WHERE i.supplier_id = '$supplier_id'";
              $result_order_count = mysqli_query($conn, $query_order_count);

              $order_count = 0;
              if ($result_order_count) {
                  $row_order_count = mysqli_fetch_array($result_order_count, MYSQLI_ASSOC);
                  if ($row_order_count) {
                      $order_count = $row_order_count['total_count'];
                  }
              }
              ?>
              <h3 class="mb-0 fs-14"><?= $order_count ?></h3>
              <small class="text-muted fs-3">Orders</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-9 col-md-12">
  <div class="card">
        <div class="card-body text-left p-3">
            <div class="d-flex justify-content-between align-items-center  mb-9">
                <div class="position-relative w-100 col-12 px-0">
                    <input type="text" class="form-control search-chat py-2 ps-5 " id="text-srh" placeholder="Search Product">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
            </div>
            <div class="table-responsive border rounded">
                <table id="productTable" class="table align-middle text-wrap mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Products</th>
                            <th scope="col">Color</th>
                            <th scope="col">Category</th>
                            <th scope="col">Warehouse</th>
                            <th scope="col">Quantity</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody"></tbody>
                </table>
                    
                <div class="d-flex align-items-center justify-content-end py-1">
                    <p class="mb-0 fs-2">Rows per page:</p>
                    <select id="rowsPerPage" class="form-select w-auto ms-0 ms-sm-2 me-8 me-sm-4 py-1 pe-7 ps-2 border-0" aria-label="Rows per page">
                        <option value="5" selected>5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                    </select>
                    <p id="paginationInfo" class="mb-0 fs-2"></p>
                    <nav aria-label="...">
                        <ul id="paginationControls" class="pagination justify-content-center mb-0 ms-8 ms-sm-9">
                            <!-- Pagination buttons will be inserted here by JS -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
        
    </div>
  </div>

<script>
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip(); 

    var currentPage = 1,
        rowsPerPage = parseInt($('#rowsPerPage').val()),
        totalRows = 0,
        totalPages = 0,
        maxPageButtons = 5,
        stepSize = 5;

    function updateTable() {
        var $rows = $('#productTableBody tr');
        totalRows = $rows.length;
        totalPages = Math.ceil(totalRows / rowsPerPage);

        var start = (currentPage - 1) * rowsPerPage,
            end = Math.min(currentPage * rowsPerPage, totalRows);

        $rows.hide().slice(start, end).show();

        $('#paginationControls').html(generatePagination());
        $('#paginationInfo').text(`${start + 1}–${end} of ${totalRows}`);

        $('#paginationControls').find('a').click(function(e) {
            e.preventDefault();
            if ($(this).hasClass('page-link-next')) {
                currentPage = Math.min(currentPage + stepSize, totalPages);
            } else if ($(this).hasClass('page-link-prev')) {
                currentPage = Math.max(currentPage - stepSize, 1);
            } else {
                currentPage = parseInt($(this).text());
            }
            updateTable();
        });
    }

    function generatePagination() {
        var pagination = '';
        var startPage = Math.max(1, currentPage - Math.floor(maxPageButtons / 2));
        var endPage = Math.min(totalPages, startPage + maxPageButtons - 1);

        if (currentPage > 1) {
            pagination += `<li class="page-item p-1"><a class="page-link border-0 rounded-circle text-dark fs-6 round-32 d-flex align-items-center justify-content-center page-link-prev" href="#">‹</a></li>`;
        }

        for (var i = startPage; i <= endPage; i++) {
            pagination += `<li class="page-item p-1 ${i === currentPage ? 'active' : ''}"><a class="page-link border-0 rounded-circle text-dark fs-6 round-32 d-flex align-items-center justify-content-center" href="#">${i}</a></li>`;
        }

        if (currentPage < totalPages) {
            pagination += `<li class="page-item p-1"><a class="page-link border-0 rounded-circle text-dark fs-6 round-32 d-flex align-items-center justify-content-center page-link-next" href="#">›</a></li>`;
        }

        return pagination;
    }

    function searchProducts(query) {
        $.ajax({
            url: 'pages/supplier_dashboard_ajax.php',
            type: 'POST',
            data: {
                query: query,
                supplier_id: <?= $supplier_id ?>
            },
            success: function(response) {
                $('#productTableBody').html(response);
                currentPage = 1;
                updateTable();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    searchProducts('');

    $(document).on('input', '#text-srh', function() {
        searchProducts($(this).val());
    });
  });
</script>
