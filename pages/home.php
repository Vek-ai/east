<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require 'includes/dbconn.php';
require 'includes/functions.php';

$reorder_level = 1;
?>
<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
  <div class="card-body px-0">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h4 class="font-weight-medium fs-14 mb-0">Dashboard</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="">Home
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Dashboard</li>
          </ol>
        </nav>
      </div>
      <div>
      </div>
    </div>
  </div>
</div>

<!-- -------------------------------------------------------------- -->
<!-- Breadcrumb End -->
<!-- -------------------------------------------------------------- -->
<div class="row">
  <!-- text-cards -->
  <!-- 
  <div class="col-lg-3 col-md-6">
    <div class="card">
      <div class="card-body p-9">
        <div class="hstack gap-9">
          <div class="round-56 rounded-circle text-white d-flex align-items-center justify-content-center text-bg-primary">
            <i class="ti ti-credit-card fs-6"></i>
          </div>
          <div class="align-self-center">
            <h3 class="mb-1 fs-6">$3249</h3>
            <span class="text-muted">Total Revenue</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="card">
      <div class="card-body p-9">
        <div class="hstack gap-9">
          <div class="round-56 rounded-circle text-white d-flex align-items-center justify-content-center text-bg-secondary">
            <i class="ti ti-users fs-6"></i>
          </div>
          <div class="align-self-center">
            <h3 class="mb-1 fs-6">$2376</h3>
            <span class="text-muted">Online Revenue</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="card">
      <div class="card-body p-9">
        <div class="hstack gap-9">
          <div class="round-56 rounded-circle text-white d-flex align-items-center justify-content-center text-bg-danger">
            <i class="ti ti-calendar fs-6"></i>
          </div>
          <div class="align-self-center">
            <h3 class="mb-1 fs-6">$1795</h3>
            <span class="text-muted">Offline Products</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="card">
      <div class="card-body p-9">
        <div class="hstack gap-9">
          <div class="round-56 rounded-circle text-white d-flex align-items-center justify-content-center text-bg-warning">
            <i class="ti ti-settings fs-6"></i>
          </div>
          <div class="align-self-center">
            <h3 class="mb-1 fs-6">$687</h3>
            <span class="text-muted">Ad. Expense</span>
          </div>
        </div>
      </div>
    </div>
  </div> 
  -->

  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-body mt-3">
        <div class="d-flex align-items-center flex-wrap mb-9">
          <div class="w-100">
            <h3 class="card-title">Products Need to Reorder</h3>
            <div class="ms-auto align-self-center">
              <div class="datatables">
                <div class="table-responsive">
                  <table id="productList" class="table search-table table-sm align-middle text-wrap text-center">
                      <thead class="header-item">
                        <th class="text-center">Description</th>
                        <th class="text-center">Category</th>
                        <th class="text-center">Inventory</th>
                      </thead>
                      <tbody>
                      <?php
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
                                  AND p.status = '1' 
                              GROUP BY 
                                  p.product_id
                              HAVING 
                                  total_quantity <= CAST(COALESCE(p.reorder_level, 0) AS DECIMAL(10,2))
                          ";

                          $result_product = mysqli_query($conn, $query_product);            
                          while ($row_product = mysqli_fetch_array($result_product)) {
                              $product_id = $row_product['product_id'];

                              if (!empty($row_product['main_image'])) {
                                  $image_path = ltrim($row_product['main_image'], '../');
                                  
                                  if (file_exists($image_path)) {
                                      $picture_path = $image_path;
                                  } else {
                                      $picture_path = "images/product/product.jpg";
                                  }
                              } else {
                                  $picture_path = "images/product/product.jpg";
                              }

                          ?>
                              <!-- start row -->
                              <tr class="search-items" 
                                  >
                                  <td>
                                      <a href="?page=product_details&product_id=<?= $row_product['product_id'] ?>">
                                          <div class="d-flex align-items-center">
                                              <img src="<?= $picture_path ?>" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                                              <div class="ms-3">
                                                  <h6 class="fw-semibold mb-0 fs-4 text-start"><?= $row_product['product_item'] ?></h6>
                                              </div>
                                          </div>
                                      </a>
                                  </td>
                                  <td><?= getProductCategoryName($row_product['product_category']) ?></td>
                                  <td><?= number_format(floatval($row_product['total_quantity'])) ?></td>
                              </tr>
                          <?php 
                          } ?>
                      </tbody>
                      
                  </table>
                </div>
              </div>
              
            </div>
          </div>
          
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100 ">
      <div class="card-body mt-3">
        <div class="d-flex align-items-center flex-wrap mb-9">
          <div class="w-100">
            <h3 class="card-title">Most Ordered Products</h3>
            <div class="ms-auto align-self-center">
              <div class="datatables">
                <div class="table-responsive">
                  <table id="productMostOrderedList" class="table search-table table-sm align-middle text-wrap text-center">
                      <thead class="header-item">
                        <th class="text-center">Description</th>
                        <th class="text-center">Category</th>
                        <th class="text-center">Qty Ordered</th>
                      </thead>
                      <tbody>
                      <?php
                          $query_product = "
                              SELECT 
                                  op.productid, 
                                  p.product_item,
                                  p.product_category,
                                  p.main_image,
                                  SUM(op.quantity) AS total_quantity_purchased
                              FROM 
                                  order_product AS op
                              LEFT JOIN 
                                  product AS p ON op.productid = p.product_id
                              GROUP BY 
                                  op.productid
                              ORDER BY 
                                  total_quantity_purchased DESC
                              LIMIT 10
                          ";

                          $result_product = mysqli_query($conn, $query_product);            
                          while ($row_product = mysqli_fetch_array($result_product)) {
                              $product_id = $row_product['product_id'];

                              if (!empty($row_product['main_image'])) {
                                  $image_path = ltrim($row_product['main_image'], '../');
                                  
                                  if (file_exists($image_path)) {
                                      $picture_path = $image_path;
                                  } else {
                                      $picture_path = "images/product/product.jpg";
                                  }
                              } else {
                                  $picture_path = "images/product/product.jpg";
                              }

                          ?>
                              <!-- start row -->
                              <tr class="search-items" 
                                  >
                                  <td>
                                      <a href="?page=product_details&product_id=<?= $row_product['product_id'] ?>">
                                          <div class="d-flex align-items-center">
                                              <img src="<?= $picture_path ?>" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                                              <div class="ms-3">
                                                  <h6 class="fw-semibold mb-0 fs-4 text-start"><?= $row_product['product_item'] ?></h6>
                                              </div>
                                          </div>
                                      </a>
                                  </td>
                                  <td><?= getProductCategoryName($row_product['product_category']) ?></td>
                                  <td><?= number_format(floatval($row_product['total_quantity_purchased'])) ?></td>
                              </tr>
                          <?php 
                          } ?>
                      </tbody>
                      
                  </table>
                </div>
              </div>
              
            </div>
          </div>
          
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100 my-3">
      <div class="card-body mt-3">
        <div class="d-flex align-items-center flex-wrap mb-9">
          <div class="w-100">
            <h3 class="card-title">Supplier Orders for Approval</h3>
            <div class="ms-auto align-self-center">
              <div class="datatables">
                <div class="table-responsive">
                  <table id="order_supplier_list_tbl" class="table search-table table-sm align-middle text-wrap text-center">
                      <thead class="header-item">
                        <th class="text-center">Supplier</th>
                        <th class="text-center">Amount</th>
                        <th></th>
                      </thead>
                      <tbody>
                        <?php 
                        $query = "SELECT supplier_id, 
                                        SUM(price * quantity) AS total_amount, 
                                        MAX(supplier_temp_order_id) AS supplier_temp_order_id
                                  FROM supplier_temp_prod_orders 
                                  WHERE supplier_id != 0
                                  GROUP BY supplier_id";
                        $result = mysqli_query($conn, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                ?>
                                <tr>
                                    <td>
                                        <?= getSupplierName($row["supplier_id"]) ?>
                                    </td>
                                    <td>
                                        $ <?= number_format($row["total_amount"], 2) ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="?page=supplier_order_list&id=<?= $row["supplier_id"] ?>" target="_blank" class="py-1 pe-1 fs-7" title="View"><i class="fa fa-eye"></i></a>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                      </tbody> 
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100 my-3">
      <div class="card-body mt-3">
        <div class="d-flex align-items-center flex-wrap mb-9">
          <div class="w-100">
            <h3 class="card-title">Customer Orders for Approval</h3>
            <div class="ms-auto align-self-center">
              <div class="datatables">
                <div class="table-responsive">
                <table id="approval_customer_table" class="table table-hover mb-0 text-md-nowrap">
                  <thead>
                      <tr>
                          <th>Cashier</th>
                          <th>Customer</th>
                          <th>Amount</th>
                          <th> </th>
                      </tr>
                  </thead>
                  <tbody>     
                  <?php
                  $query = "
                    SELECT 
                      a.*, 
                      CONCAT(c.customer_first_name, ' ', c.customer_last_name) AS customer_name
                    FROM approval AS a
                    LEFT JOIN customer AS c ON c.customer_id = a.originalcustomerid
                    ";
                  $total_amount = 0;
                  $total_count = 0;
                  $result = mysqli_query($conn, $query);
                  while ($row = mysqli_fetch_assoc($result)) {
                      $total_amount += $row['discounted_price'];
                      $total_count += 1;

                      $submitted_date = $row['submitted_date'];
                      $customer_name = $row['customer_name'];
                  
                      ?>
                      <tr>
                          <td>
                              <?= get_staff_name($row['cashier']) ?>
                          </td>
                          <td>
                              <?= htmlspecialchars($customer_name) ?>
                          </td>
                          <td class="text-end">
                              $ <?= number_format($row['discounted_price'], 2) ?>
                          </td>
                          <td>
                              <a href="?page=approval_details&id=<?= $row["approval_id"] ?>" target="_blank" class="py-1 pe-1 fs-5" data-id="<?php echo $row["approval_id"]; ?>" data-toggle="tooltip" data-placement="top" title="View"><i class="fa fa-eye"></i></a>
                          </td>
                      </tr>
                      <?php
                  }
                  ?>
                  </tbody>
              </table>
                </div>
              </div>
              
            </div>
          </div>
          
        </div>
      </div>
    </div>
    <div class="modal fade" id="view_order_product_details_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Saved Order Details</h6>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="order-saved-details">
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card my-5">
      <div class="card-body mt-3">
        <div class="d-flex align-items-center flex-wrap mb-9">
          <div class="w-100">
            <h3 class="card-title">New Customer Estimates</h3>
            <div class="ms-auto align-self-center">
              <div class="datatables">
                <div class="table-responsive">
                <table id="estimate_customer_table" class="table table-hover mb-0 text-md-nowrap">
                  <thead>
                      <tr>
                          <th>Customer</th>
                          <th>Amount</th>
                          <th> </th>
                      </tr>
                  </thead>
                  <tbody>     
                  <?php
                  $query = "
                    SELECT 
                      e.*, 
                      CONCAT(c.customer_first_name, ' ', c.customer_last_name) AS customer_name
                    FROM estimates AS e
                    LEFT JOIN customer AS c ON c.customer_id = e.customerid
                    WHERE e.status = 1
                    ";
                  $total_amount = 0;
                  $total_count = 0;
                  $result = mysqli_query($conn, $query);
                  while ($row = mysqli_fetch_assoc($result)) {
                      $total_amount += $row['discounted_price'];
                      $total_count += 1;

                      $submitted_date = $row['submitted_date'];
                      $customer_name = $row['customer_name'];
                  
                      ?>
                      <tr>
                          <td>
                              <?= htmlspecialchars($customer_name) ?>
                          </td>
                          <td class="text-end">
                              $ <?= number_format($row['discounted_price'], 2) ?>
                          </td>
                          <td>
                              <a href="?page=estimate_list&id=<?= $row["approval_id"] ?>" target="_blank" class="py-1 pe-1 fs-5" data-id="<?php echo $row["estimateid"]; ?>" data-toggle="tooltip" data-placement="top" title="View"><i class="fa fa-eye"></i></a>
                          </td>
                      </tr>
                      <?php
                  }
                  ?>
                  </tbody>
              </table>
                </div>
              </div>
              
            </div>
          </div>
          
        </div>
      </div>
    </div>
    <div class="modal fade" id="view_order_product_details_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Saved Order Details</h6>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="order-saved-details">
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card my-5">
      <div class="card-body mt-3">
        <div class="d-flex align-items-center flex-wrap mb-9">
          <div class="w-100">
            <h3 class="card-title">Pre-Ordered Products</h3>
            <div class="ms-auto align-self-center">
              <div class="datatables">
                <div class="table-responsive">
                  <table id="productPreOrderedList" class="table search-table table-sm align-middle text-wrap text-center">
                      <thead class="header-item">
                        <th class="text-center">Description</th>
                        <th class="text-center">Category</th>
                      </thead>
                      <tbody>
                      <?php
                          $query_product = "
                              SELECT 
                                  *
                              FROM 
                                  product_preorder
                              LIMIT 10
                          ";

                          $result_product = mysqli_query($conn, $query_product);            
                          while ($row_product = mysqli_fetch_array($result_product)) {
                              $product_id = $row_product['product_id'];

                              $product_details = getProductDetails($product_id);

                              if (!empty($row_product['main_image'])) {
                                  $image_path = ltrim($row_product['main_image'], '../');
                                  
                                  if (file_exists($image_path)) {
                                      $picture_path = $image_path;
                                  } else {
                                      $picture_path = "images/product/product.jpg";
                                  }
                              } else {
                                  $picture_path = "images/product/product.jpg";
                              }

                          ?>
                              <!-- start row -->
                              <tr class="search-items" 
                                  >
                                  <td>
                                      <a href="?page=product_details&product_id=<?= $row_product['product_id'] ?>">
                                          <div class="d-flex align-items-center">
                                              <img src="<?= $picture_path ?>" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                                              <div class="ms-3">
                                                  <h6 class="fw-semibold mb-0 fs-4 text-start"><?= $product_details['product_item'] ?></h6>
                                              </div>
                                          </div>
                                      </a>
                                  </td>
                                  <td><?= getProductCategoryName($row_product['product_category']) ?></td>
                              </tr>
                          <?php 
                          } ?>
                      </tbody>
                      
                  </table>
                </div>
              </div>
              
            </div>
          </div>
          
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100 my-3">
      <div class="card-body mt-3">
        <div class="d-flex align-items-center flex-wrap mb-9">
          <div class="w-100">
            <h3 class="card-title">Store Credit History</h3>
            <div class="ms-auto align-self-center">
              <div class="datatables">
                <div class="table-responsive">
                  <table id="store_credit_tbl" class="table search-table table-sm align-middle text-wrap text-center">
                    <thead class="header-item">
                      <tr>
                        <th class="text-center">Customer</th>
                        <th class="text-center">Amount</th>
                        <th class="text-center">Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $query = "SELECT customer_id, credit_amount, credit_type, created_at, reference_id as orderid FROM customer_store_credit_history ORDER BY created_at DESC";
                      $result = mysqli_query($conn, $query);

                      if ($result && mysqli_num_rows($result) > 0) {
                          while ($row = mysqli_fetch_assoc($result)) {
                              $amount = number_format(abs($row['credit_amount']), 2);
                              $sign = $row['credit_type'] == 'add' ? '+' : '-';
                              $color = $row['credit_type'] == 'add' ? 'green' : 'red';
                              $orderid = $row['orderid'];
                              ?>
                              <tr>
                                <td><?= get_customer_name($row['customer_id']) ?></td>
                                <td style="color:<?= $color ?> !important"><strong><?= $sign . ' $' . $amount ?></strong></td>
                                <td><?= date('F d, Y', strtotime($row['created_at'])) ?></td>
                              </tr>
                              <?php
                          }
                      }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered" style="width:90% !important">
          <div class="modal-content">
          <div class="modal-header align-items-center modal-colored-header">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div id="viewModalContent">

              </div>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">
                  Close
              </button>
          </div>
          </div>
      </div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100 my-3">
      <div class="card-body mt-3">
        <div class="d-flex align-items-center flex-wrap mb-9">
          <div class="w-100">
            <h3 class="card-title">Job Ledger History</h3>
            <div class="ms-auto align-self-center">
              <div class="datatables">
                <div class="table-responsive">
                  <table id="job_ledger_tbl" class="table search-table table-sm align-middle text-wrap text-center">
                    <thead class="header-item">
                      <tr>
                        <th class="text-center">Customer</th>
                        <th class="text-center">Job</th>
                        <th class="text-center">Amount</th>
                        <th class="text-center">Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $query = "SELECT ledger_id, job_id, customer_id, amount, entry_type, created_at FROM job_ledger WHERE entry_type IN ('deposit', 'usage') ORDER BY created_at DESC";
                      $result = mysqli_query($conn, $query);

                      if ($result && mysqli_num_rows($result) > 0) {
                          while ($row = mysqli_fetch_assoc($result)) {
                              $job_id = $row['job_id'];
                              $job_details = getJobDetails($job_id);
                              $customer_id = $row['customer_id'];
                              $customer_name = get_customer_name($customer_id);
                              $job_name = $job_details['job_name'];
                              $amount = number_format(abs($row['amount']), 2);
                              $type = $row['entry_type'];
                              $color = $type == 'deposit' ? 'green' : 'red';
                              $sign = $type == 'deposit' ? '+' : '-';
                              $ledger_id = $row['ledger_id'];
                              ?>
                              <tr>
                                <td><?= $customer_name ?></td>
                                <td><?= $job_name ?></td>
                                <td style="color:<?= $color ?> !important"><strong><?= $sign . ' $' . $amount ?></strong></td>
                                <td><?= date('F d, Y', strtotime($row['created_at'])) ?></td>
                              </tr>
                              <?php
                          }
                      }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <div class="col-lg-6 col-md-12">
    <!-- Column -->
    <div class="card earning-widget my-5">
      <div class="card-body py-3 d-flex align-items-center">
        <h4 class="card-title mb-0">Top 10 Customers</h4>
        <div class="card-actions hstack gap-2 ms-auto">
          <a href="javascript:void(0)" class="text-muted fs-5 d-flex" data-action="collapse">
            <i class="ti ti-minus"></i>
          </a>
        </div>
      </div>
      <div class="card-body border-top collapse show table-responsive no-wrap p-0">
        <div class="message-box py-7 contact-box position-relative">
          <div class="message-widget vstack gap-7 contact-widget position-relative">
            <?php
              $query_order_total = "
                  WITH customer_totals AS (
                      SELECT 
                          customerid, 
                          SUM(discounted_price) AS order_total
                      FROM orders
                      WHERE YEAR(order_date) = YEAR(CURDATE())
                      GROUP BY customerid
                  )
                  SELECT 
                      ct.customerid, 
                      ct.order_total,
                      (SELECT COUNT(*) FROM customer_totals WHERE order_total > ct.order_total) + 1 AS customer_rank
                  FROM customer_totals ct
                  ORDER BY customer_rank ASC
                  LIMIT 10;
              ";
              
              $result_order_total = mysqli_query($conn, $query_order_total);
              if ($result_order_total) {
                  $row_order_total = mysqli_fetch_array($result_order_total, MYSQLI_ASSOC);
                  
                  if ($row_order_total) {
                      $customer_order_total = $row_order_total['order_total'];
                      $customer_rank = $row_order_total['customer_rank'];
                      $customer_details = getCustomerDetails($row_order_total['customerid']);
                      ?>
                        <!-- contact -->
                        <a href="javascript:void(0)" class="hstack px-7 gap-3">
                          <div class="user-img position-relative d-inline-block">
                            <img src="../assets/images/profile/user-2.jpg" alt="user" class="rounded-circle w-100" />
                            <span class="
                                profile-status
                                pull-right
                                d-inline-block
                                position-absolute
                                text-bg-secondary
                                rounded-circle
                              "></span>
                          </div>
                          <div class="v-middle d-md-flex align-items-center w-100">
                            <div class="text-truncate">
                              <h5 class="mb-1">
                                <?= $customer_details['customer_first_name'] . ' ' .$customer_details['customer_last_name'] ?>
                              </h5>
                              <span class="text-muted fs-3"><?= $customer_details['customer_business_name'] ?></span>
                            </div>
                            <div class="ms-auto">
                              <span class="badge px-2 py-1 bg-primary-subtle text-primary">$ <?= number_format(floatval($customer_order_total),2) ?></span>
                            </div>
                          </div>
                        </a>
                      <?php
                  }
              }
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6 col-md-12">
    <div class="card earning-widget my-5">
      <div class="card-body py-3 d-flex align-items-center justify-content-between">
        <h4 class="card-title mb-0">New Customers for Approval</h4>
      </div>
      <div class="card-body border-top collapse show table-responsive no-wrap p-0">
        <div class="message-box py-4 contact-box position-relative">
          <div id="approval_customer" class="message-widget vstack gap-4 contact-widget position-relative">
            <?php
            $query_customer = "SELECT * FROM customer WHERE is_approved = 0 AND status = 0 AND hidden = 0 LIMIT 10";
            $result_customer = mysqli_query($conn, $query_customer);
            if ($result_customer && mysqli_num_rows($result_customer) > 0) {
                while ($row_customer = mysqli_fetch_array($result_customer, MYSQLI_ASSOC)) {
                    $customer_name = $row_customer['customer_first_name'] . ' ' . $row_customer['customer_last_name'];
                    $customer_email = $row_customer['contact_email'];
                    $customer_id = $row_customer['customer_id'];
                    ?>
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 gap-3">
                      <div class="d-flex align-items-center gap-3 w-100">
                        <div class="user-img position-relative">
                          <img src="../assets/images/profile/user-2.jpg" alt="user" class="rounded-circle" width="45" height="45" />
                          <span class="profile-status position-absolute text-bg-secondary rounded-circle" style="width:10px; height:10px; bottom:0; right:0;"></span>
                        </div>
                        <div class="text-truncate">
                          <h5 class="mb-1"><?= ucwords($customer_name) ?></h5>
                          <span class="text-muted"><?= $customer_email ?></span>
                        </div>
                      </div>
                      <div class="d-flex justify-content-center gap-3">
                        <a href="javascript:void(0)" data-id="<?= $customer_id ?>" id="approve_customer" title="Approve">
                          <i class="fa-solid text-success fa-check fs-7"></i>
                        </a>
                        <a href="javascript:void(0)" data-id="<?= $customer_id ?>" id="reject_customer" title="Reject">
                          <i class="fa-solid text-danger fa-xmark fs-7"></i>
                        </a>
                      </div>
                    </div>
                    <?php
                }
            }else{
              ?>
              <h4 class="d-flex justify-content-center align-items-center">No approval requests.</h4>
              <?php
            }
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>


  <!-- Bandwidth cards -->
  <div class="col-lg-4">
    <div class="card overflow-hidden my-5">
      <div class="card-body bg-purple">
        <div class="hstack gap-6 mb-7">
          <div class="bg-black bg-opacity-10 round-48 rounded-circle d-flex align-items-center justify-content-center">
            <iconify-icon icon="solar:server-square-linear" class="fs-7 icon-center text-white"></iconify-icon>
          </div>
          <div>
            <h4 class="card-title text-white">Bandwidth usage</h4>
            <p class="card-subtitle text-white opacity-70">March
              2024</p>
          </div>
        </div>
        <div class="row align-items-center">
          <div class="col-6">
            <h2 class="mb-0 text-white text-nowrap">50 GB</h2>
          </div>
          <div class="col-6">
            <div id="bandwidth-usage"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="card  overflow-hidden">
      <div class="card-body bg-secondary">
        <div class="hstack gap-6 mb-7">
          <div class="bg-white bg-opacity-20 round-48 rounded-circle d-flex align-items-center justify-content-center">
            <iconify-icon icon="solar:chart-2-linear" class="fs-7 icon-center text-white"></iconify-icon>
          </div>
          <div>
            <h4 class="card-title text-white">Download count</h4>
            <p class="card-subtitle text-white opacity-70">March
              2024</p>
          </div>
        </div>
        <div class="row align-items-center">
          <div class="col-5">
            <h2 class="mb-0 text-white text-nowrap">35487</h2>
          </div>
          <div class="col-7">
            <div id="download-count"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Our Visitors -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-body pb-2">
        <h4 class="card-title">Our Visitors</h4>
        <p class="card-subtitle">Different Devices Used to
          Visit</p>
        <div id="our-visitors" class="mt-6"></div>
      </div>
      <div class="card-body pt-4 d-flex align-items-center justify-content-center border-top">
        <ul class="list-inline mb-0 hstack justify-content-center">
          <li class="list-inline-item px-2 me-0">
            <div class="text-primary d-flex align-items-center gap-2 fs-3">
              <iconify-icon icon="ri:circle-fill" class="fs-2"></iconify-icon>Mobile
            </div>
          </li>
          <li class="list-inline-item px-2 me-0">
            <div class="text-secondary d-flex align-items-center gap-2 fs-3">
              <iconify-icon icon="ri:circle-fill" class="fs-2"></iconify-icon>Tablet
            </div>
          </li>
          <li class="list-inline-item px-2 me-0">
            <div class="text-purple d-flex align-items-center gap-2 fs-3">
              <iconify-icon icon="ri:circle-fill" class="fs-2"></iconify-icon>Desktop
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <!-- Current Visitors -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Current Visitors</h4>
        <p class="card-subtitle">Different Devices Used to Visit</p>
        <div id="usa" class="h-280"></div>
        <div class="text-center">
          <ul class="list-inline mb-0 hstack justify-content-center">
            <li class="list-inline-item px-2 me-0">
              <div class="text-secondary d-flex align-items-center gap-2 fs-3">
                <iconify-icon icon="ri:circle-fill" class="fs-2"></iconify-icon>Valley
              </div>
            </li>
            <li class="list-inline-item px-2 me-0">
              <div class="text-primary d-flex align-items-center gap-2 fs-3">
                <iconify-icon icon="ri:circle-fill" class="fs-2"></iconify-icon>New York
              </div>
            </li>
            <li class="list-inline-item px-2 me-0">
              <div class="text-danger d-flex align-items-center gap-2 fs-3">
                <iconify-icon icon="ri:circle-fill" class="fs-2"></iconify-icon>Kansas
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Blog Card -->
  <div class="col-lg-4">
    <div class="card blog-widget w-100">
      <div class="card-body p-2">
        <div class="blog-image">
          <img src="assets/images/backgrounds/blog-bg.jpg" height="273" alt="img" class="w-100 rounded" />
        </div>
        <div class="p-9">
          <div class="
            badge badge-pill
            bg-primary-subtle
            text-primary
            mb-6
          ">Technology</div>
          <h4 class="card-title">Business development new rules for 2023</h4>

          <p class="mb-6 truncate-2 text-muted">
            Lorem ipsum dolor sit amet, this is a consectetur
            adipisicing elit, sed do eiusmod tempor incididunt ut
          </p>
          <div class="d-flex justify-content-between align-items-center">
            <button class="
            btn btn-primary
            ">
              Read more
            </button>
            <div class="ms-auto">
              <a href="javascript:void(0)" class="link" data-bs-toggle="tooltip" title="Share"><iconify-icon icon="solar:share-linear" class="fs-7"></iconify-icon></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Newsletter Campaign -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center flex-wrap mb-9">
          <div>
            <h4 class="card-title">Newsletter Campaign</h4>
            <p class="card-subtitle">
              Overview of Newsletter Campaign
            </p>
          </div>
          <div class="ms-auto align-self-center">
            <ul class="d-flex align-items-center gap-3 mb-0">
              <li class="d-flex">
                <div class="text-primary d-flex align-items-center gap-2 fs-3">
                  <iconify-icon icon="ri:circle-fill" class="fs-2"></iconify-icon>Open Rate
                </div>
              </li>
              <li class="d-flex ">
                <div class="text-secondary d-flex align-items-center gap-2 fs-3">
                  <iconify-icon icon="ri:circle-fill" class="fs-2"></iconify-icon>Recurring
                  Payments
                </div>
              </li>
            </ul>

          </div>
        </div>
        <div class="me-n4 me-rtl-n4">
          <div id="newsletter-campaign"></div>
        </div>
        <div class="row text-center">
          <div class="col-lg-4 col-md-4 mt-2">
            <h2 class="mb-0">5098</h2>
            <small class="fs-3 text-muted">Total Sent</small>
          </div>
          <div class="col-lg-4 col-md-4 mt-2">
            <h2 class="mb-0">4156</h2>
            <small class="fs-3 text-muted">Mail Open Rate</small>
          </div>
          <div class="col-lg-4 col-md-4 mt-2">
            <h2 class="mb-0">1369</h2>
            <small class="fs-3 text-muted">Click Rate</small>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Projects of the Month -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body pb-3">
        <div class="d-md-flex no-block">
          <h4 class="card-title">Projects of the Month</h4>
          <div class="ms-auto">
            <select class="form-select">
              <option selected>January</option>
              <option value="1">February</option>
              <option value="2">March</option>
              <option value="3">April</option>
            </select>
          </div>
        </div>
        <div class="month-table">
          <div class="table-responsive mt-3">
            <table class="table align-middle  mb-0 no-wrap">
              <thead>
                <tr>
                  <th class="border-0 ps-0">
                    Client
                  </th>
                  <th class="border-0">Name</th>
                  <th class="border-0">
                    Priority
                  </th>
                  <th class="border-0 text-end">
                    Budget
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="ps-0">
                    <div class="hstack gap-3">
                      <span class="round-48 rounded-circle overflow-hidden flex-shrink-0 hstack justify-content-center">
                        <img src="assets/images/profile/user-2.jpg" alt class="img-fluid">
                      </span>
                      <div>
                        <h5 class="mb-1">Sunil Joshi</h5>
                        <p class="mb-0 fs-3">Web Designer</p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <p class="mb-0">Digital Agency</p>
                  </td>
                  <td>
                    <span class="badge bg-primary-subtle text-primary">Low</span>
                  </td>
                  <td class="text-end">
                    <p class="mb-0 fs-3">$3.9K</p>
                  </td>
                </tr>
                <tr>
                  <td class="ps-0">
                    <div class="hstack gap-3">
                      <span class="round-48 rounded-circle overflow-hidden flex-shrink-0 hstack justify-content-center">
                        <img src="assets/images/profile/user-4.jpg" alt class="img-fluid">
                      </span>
                      <div>
                        <h5 class="mb-1">Andrew Liock</h5>
                        <p class="mb-0 fs-3">Project Manager</p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <p class="mb-0">Real Homes</p>
                  </td>
                  <td>
                    <span class="badge bg-info-subtle text-info">Medium</span>
                  </td>
                  <td class="text-end">
                    <p class="mb-0 fs-3">$23.9K</p>
                  </td>
                </tr>
                <tr>
                  <td class="ps-0">
                    <div class="hstack gap-3">
                      <span class="round-48 rounded-circle overflow-hidden flex-shrink-0 hstack justify-content-center">
                        <img src="assets/images/profile/user-5.jpg" alt class="img-fluid">
                      </span>
                      <div>
                        <h5 class="mb-1">Biaca George</h5>
                        <p class="mb-0 fs-3">Developer</p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <p class="mb-0">MedicalPro Theme</p>
                  </td>
                  <td>
                    <span class="badge bg-secondary-subtle text-secondary">High</span>
                  </td>
                  <td class="text-end">
                    <p class="mb-0 fs-3">$12.9K</p>
                  </td>
                </tr>
                <tr>
                  <td class="border-bottom-0 ps-0">
                    <div class="hstack gap-3">
                      <span class="round-48 rounded-circle overflow-hidden flex-shrink-0 hstack justify-content-center">
                        <img src="assets/images/profile/user-6.jpg" alt class="img-fluid">
                      </span>
                      <div>
                        <h5 class="mb-1">Nirav Joshi</h5>
                        <p class="mb-0 fs-3">Frontend Eng</p>
                      </div>
                    </div>
                  </td>
                  <td class="border-bottom-0">
                    <p class="mb-0">Elite Admin</p>
                  </td>
                  <td class="border-bottom-0">
                    <span class="badge bg-danger-subtle text-danger">Very
                      High</span>
                  </td>
                  <td class="text-end border-bottom-0">
                    <p class="mb-0 fs-3">$2.6K</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Profile card -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-body p-2">
        <img class="card-img-top w-100 profile-bg-height rounded overflow-hidden" src="assets/images/backgrounds/profile-bg.jpg" height="111" alt="Card image cap" />
        <div class="card-body little-profile text-center p-9">
          <div class="pro-img mb-3">
            <img src="assets/images/profile/user-2.jpg" alt="user" class="rounded-circle shadow-sm" width="112" />
          </div>
          <h3 class="mb-1 fs-14">Angelo Dominic</h3>
          <p class="fs-3 mb-4">Web Designer &amp; Developer</p>
          <a href="javascript:void(0)" class="
              btn btn-primary btn-md btn-rounded mb-7
            ">Follow</a>
          <div class="row gx-lg-4 text-center pt-4 justify-content-center border-top">
            <div class="col-4">
              <h3 class="mb-0 fs-14">1099</h3>
              <small class="text-muted fs-3">Articles</small>
            </div>
            <div class="col-4">
              <h3 class="mb-0 fs-14">23,469</h3>
              <small class="text-muted fs-3">Followers</small>
            </div>
            <div class="col-4">
              <h3 class="mb-0 fs-14">6035</h3>
              <small class="text-muted fs-3">Following</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Recent Comments -->
  <div class="col-lg-6">
    <div class="card">
      <div class="card-body">
        <div class="d-md-flex align-items-center justify-content-between mb-4">
          <div class="mb-3 mb-md-0">
            <h4 class="card-title">Recent Comments</h4>
            <p class="card-subtitle">
              Latest Comments on users from Material
            </p>
          </div>
          <a href="javascript:void(0)" class="btn btn-primary">View
            All</a>
        </div>
        <!-- ============================================================== -->
        <!-- Comment widgets -->
        <!-- ============================================================== -->
        <div class="comment-widgets widgets">
          <!-- Comment Row -->
          <div class="comment-row hstack align-items-start gap-6 pb-9 border-bottom">
            <span class="round flex-shrink-0">
              <img src="assets/images/profile/user-10.jpg" class="rounded-circle" alt="user" width="44" height="44">
            </span>
            <div class="comment-text w-100">
              <h5 class="text-nowrap">
                James Anderson
              </h5>
              <p class="fs-3 mb-8">
                Lorem Ipsum is simply dummy text of the printing and
                type setting industry.
              </p>
              <div class="comment-footer d-md-flex align-items-center justify-content-between">
                <div class="hstack gap-6 mb-2 mb-md-0">
                  <span class="
                    badge
                    bg-warning-subtle
                    text-warning
                  ">Pending</span>
                  <ul class="action-icons list-unstyled mb-0 hstack gap-6">
                    <li>
                      <a href="javascript:void(0)" class="d-flex fs-5"><iconify-icon icon="solar:pen-new-square-linear"></iconify-icon></a>
                    </li>
                    <li>
                      <a href="javascript:void(0)" class="d-flex fs-5"><iconify-icon icon="solar:check-circle-linear"></iconify-icon></a>
                    </li>
                    <li>
                      <a href="javascript:void(0)" class="d-flex fs-5"><iconify-icon icon="solar:heart-linear"></iconify-icon></a>
                    </li>
                  </ul>
                </div>
                <div class="ms-auto">
                  <span class="fs-3">April 14, 2024</span>
                </div>
              </div>
            </div>
          </div>
          <!-- Comment Row -->
          <div class="comment-row hstack align-items-start gap-6 py-9 border-bottom">
            <span class="round flex-shrink-0">
              <img src="assets/images/profile/user-6.jpg" class="rounded-circle" alt="user" width="44" height="44">
            </span>
            <div class="comment-text w-100">
              <h5 class="text-nowrap">
                Michael Jorden
              </h5>
              <p class="fs-3 mb-8">
                Lorem Ipsum is simply dummy text of the printing and
                type setting industry.
              </p>
              <div class="comment-footer d-md-flex align-items-center justify-content-between">
                <div class="hstack gap-6 mb-2 mb-md-0">
                  <span class="
                  badge
                  bg-secondary-subtle
                  text-secondary
                ">Approved</span>
                  <ul class="action-icons list-unstyled mb-0 hstack gap-6">
                    <li>
                      <a href="javascript:void(0)" class="d-flex fs-5"><iconify-icon icon="solar:pen-new-square-linear"></iconify-icon></a>
                    </li>
                    <li>
                      <a href="javascript:void(0)" class="d-flex fs-5"><iconify-icon icon="solar:check-circle-linear"></iconify-icon></a>
                    </li>
                    <li>
                      <a href="javascript:void(0)" class="d-flex fs-5"><iconify-icon icon="solar:heart-linear"></iconify-icon></a>
                    </li>
                  </ul>
                </div>
                <div class="ms-auto">
                  <span class="fs-3">April 14, 2024</span>
                </div>
              </div>
            </div>
          </div>
          <!-- Comment Row -->
          <div class="comment-row hstack align-items-start gap-6 pt-9">
            <span class="round flex-shrink-0">
              <img src="assets/images/profile/user-12.jpg" class="rounded-circle" alt="user" width="44" height="44">
            </span>
            <div class="comment-text w-100">
              <h5 class="text-nowrap">
                Johnathan Doeting
              </h5>
              <p class="fs-3 mb-8">
                Lorem Ipsum is simply dummy text of the printing and
                type setting industry.
              </p>
              <div class="comment-footer d-md-flex align-items-center justify-content-between">
                <div class="hstack gap-6 mb-2 mb-md-0">
                  <span class="
                  badge
                  bg-danger-subtle
                  text-danger
                ">Rejected</span>
                  <ul class="action-icons list-unstyled mb-0 hstack gap-6">
                    <li>
                      <a href="javascript:void(0)" class="d-flex fs-5"><iconify-icon icon="solar:pen-new-square-linear"></iconify-icon></a>
                    </li>
                    <li>
                      <a href="javascript:void(0)" class="d-flex fs-5"><iconify-icon icon="solar:check-circle-linear"></iconify-icon></a>
                    </li>
                    <li>
                      <a href="javascript:void(0)" class="d-flex fs-5"><iconify-icon icon="solar:heart-linear"></iconify-icon></a>
                    </li>
                  </ul>
                </div>
                <div class="ms-auto">
                  <span class="fs-3">April 14, 2024</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- To Do list -->
  <div class="col-lg-6">
    <div class="card">
      <div class="card-body pb-4">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <h4 class="card-title">To Do list</h4>
            <p class="card-subtitle">
              List of your next task to complete
            </p>
          </div>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal">
            Add Task
          </button>
          <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header d-flex">
                  <h4 class="modal-title">Add Task</h4>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <form>
                    <div class="mb-3">
                      <label>Task name</label>
                      <input type="text" class="form-control" placeholder="Enter Task Name" />
                    </div>
                    <div class="mb-3">
                      <label>Assign to</label>
                      <select class="form-select">
                        <option selected>Sachin</option>
                        <option value="1">Sehwag</option>
                      </select>
                    </div>
                  </form>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                  </button>
                  <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                    Submit
                  </button>
                </div>
              </div>
              <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
          </div>
        </div>

        <!-- -------------------------------------------------------------- -->
        <!-- To do list widgets -->
        <!-- -------------------------------------------------------------- -->
        <div class="to-do-widget common-widget">
          <!-- .modal for add task -->
          <!-- /.modal -->
          <ul class="list-task todo-list list-group mb-0" data-role="tasklist">
            <li class="list-group-item py-4 px-0 border-0 border-bottom" data-role="task">
              <div class="form-check form-check-inline w-100 me-0 mb-0 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                  <input type="checkbox" class="form-check-input primary check-light-primary" id="inputSchedule" name="inputCheckboxesSchedule" />
                  <div class="ms-3">
                    <label for="inputSchedule" class="form-check-label hstack gap-1">
                      <h5 class="mb-0 todo-desc">Schedule meeting
                        with</h5>
                      <span class="badge bg-primary-subtle text-primary lh-base">Today</span>
                    </label>
                    <p class="mb-0 fs-3 text-body mt-1">Phasellus
                      quis rutrum leo quis vulputate tortor...</p>
                  </div>
                </div>
                <div class="round-56 rounded-1 overflow-hidden">
                  <img src="assets/images/blog/blog-img2.jpg" alt="user" class="img-fluid h-100">
                </div>
              </div>
            </li>
            <li class="list-group-item py-4 px-0 border-0 border-bottom" data-role="task">
              <div class="form-check form-check-inline w-100 me-0 mb-0 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                  <input type="checkbox" class="form-check-input primary check-light-primary" id="inputSchedule2" name="inputCheckboxesSchedule" checked />
                  <div class="ms-3">
                    <label for="inputSchedule2" class="form-check-label hstack gap-1">
                      <h5 class="mb-0 todo-desc text-decoration-line-through">Forward
                        all tasks</h5>
                      <span class="badge bg-secondary-subtle text-secondary lh-base">Yesterday</span>
                    </label>
                    <p class="mb-0 fs-3 text-body mt-1">Mauris
                      cursus scelerisque felis et ultricies...</p>
                  </div>
                </div>
                <div class="round-56 rounded-1 overflow-hidden">
                  <img src="assets/images/blog/blog-img4.jpg" alt="user" class="img-fluid h-100">
                </div>
              </div>
            </li>
            <li class="list-group-item py-4 px-0 border-0 border-bottom" data-role="task">
              <div class="form-check form-check-inline w-100 me-0 mb-0 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                  <input type="checkbox" class="form-check-input primary check-light-primary" id="inputSchedule3" name="inputCheckboxesSchedule" />
                  <div class="ms-3">
                    <label for="inputSchedule3" class="form-check-label hstack gap-1">
                      <h5 class="mb-0 todo-desc">Give Purchase
                        report to</h5>
                      <span class="badge bg-danger-subtle text-danger lh-base">Tomorrow</span>
                    </label>
                    <p class="mb-0 fs-3 text-body mt-1">Vestibulum
                      non aliquet mi vitae mollis lorem...</p>
                  </div>
                </div>
                <div class="round-56 rounded-1 overflow-hidden">
                  <img src="assets/images/blog/blog-img3.jpg" alt="user" class="img-fluid h-100">
                </div>
              </div>
            </li>
            <li class="list-group-item py-4 px-0 border-0 border-bottom" data-role="task">
              <div class="form-check form-check-inline w-100 me-0 mb-0 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                  <input type="checkbox" class="form-check-input primary check-light-primary" id="inputSchedule4" name="inputCheckboxesSchedule" />
                  <div class="ms-3">
                    <label for="inputSchedule4" class="form-check-label hstack gap-1">
                      <h5 class="mb-0 todo-desc">Book flight for
                        holiday</h5>
                      <span class="badge bg-warning-subtle text-warning lh-base">1
                        Week</span>
                    </label>
                    <p class="mb-0 fs-3 text-body mt-1">Aenean
                      interdum auctor massa ut scelerisque...</p>
                  </div>
                </div>
                <div class="round-56 rounded-1 overflow-hidden">
                  <img src="assets/images/blog/blog-img1.jpg" alt="user" class="img-fluid h-100">
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="assets/js/breadcrumb/breadcrumbChart.js"></script>
<script src="assets/js/theme/sidebarmenu.js"></script>
<script src="assets/js/dashboards/dashboard2.js"></script>

<script>
function loadOrderSupplierDetails(orderid){
    $.ajax({
        url: 'pages/index_ajax.php',
        type: 'POST',
        data: {
            orderid: orderid,
            fetch_order_product_details: "fetch_order_product_details"
        },
        success: function(response) {
            $('#order-saved-details').html(response);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('Error: ' + textStatus + ' - ' + errorThrown);
        }
    });
}

$(document).ready(function() {
    var reorder_table = $('#productList').DataTable({
        "order": [[1, "asc"]],
        "pageLength": 5,
        "lengthMenu": [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ],
        "dom": 'lftp',
    });

    var most_ordered_table = $('#productMostOrderedList').DataTable({
        "order": [[2, "desc"]],
        "pageLength": 5,
        "lengthMenu": [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ],
        "dom": 'lftp',
    });

    var approval_order_table = $('#order_supplier_list_tbl').DataTable({
        "order": [[1, "desc"]],
        "pageLength": 5,
        "lengthMenu": [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ],
        "dom": 'lftp',
    });

    var approval_customer_table = $('#approval_customer_table').DataTable({
        "order": [[1, "asc"]],
        "pageLength": 5,
        "lengthMenu": [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ],
        "dom": 'lftp',
    });

    var estimate_customer_table = $('#estimate_customer_table').DataTable({
        "order": [[1, "asc"]],
        "pageLength": 5,
        "lengthMenu": [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ],
        "dom": 'lftp',
    });

    var pre_ordered_table = $('#productPreOrderedList').DataTable({
        "order": [[1, "asc"]],
        "pageLength": 5,
        "lengthMenu": [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ],
        "dom": 'lftp',
    });

    var store_credit_tbl = $('#store_credit_tbl').DataTable({
        "order": [[2, "asc"]],
        "pageLength": 5,
        "dom": 'lftp',
    });

    var job_ledger_tbl = $('#job_ledger_tbl').DataTable({
        "order": [[3, "asc"]],
        "pageLength": 5,
        "dom": 'lftp',
    });

    $(document).on('click', '#view_order_product_details', function(event) {
        let orderId = $(this).data('id');
        loadOrderSupplierDetails(orderId);
        $('#view_order_product_details_modal').modal('show');
    });

    $(document).on('click', '#approve_customer', function(event) {
        let customer_id = $(this).data('id');

        if (!confirm("Are you sure you want to approve this customer?")) return;

        $.ajax({
            url: 'pages/index_ajax.php',
            type: 'POST',
            data: {
                customer_id: customer_id,
                approve_customer: "approve_customer"
            },
            success: function(response) {
                console.log(response);
                if(response == 'success'){
                  alert('Customer Approved Successfully');
                }
                $('#approval_customer').load(location.href + ' #approval_customer');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    $(document).on('click', '#reject_customer', function(event) {
        let customer_id = $(this).data('id');

        if (!confirm("Are you sure you want to reject this customer?")) return;

        $.ajax({
            url: 'pages/index_ajax.php',
            type: 'POST',
            data: {
                customer_id: customer_id,
                reject_customer: "reject_customer"
            },
            success: function(response) {
                console.log(response);
                if(response == 'success'){
                  alert('Customer Rejected Successfully');
                }
                $('#approval_customer').load(location.href + ' #approval_customer');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });
});
</script>