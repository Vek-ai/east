<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

if(isset($_REQUEST['id'])){
  $customer_id = $_REQUEST['id'];
  $customer_details = getCustomerDetails($customer_id);
}
?>

<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0">Customer Dashboard</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Sales
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Sales List</li>
            </ol>
            </nav>
        </div>
        <div>
            <div class="d-sm-flex d-none gap-3 no-block justify-content-end align-items-center">
            <div class="d-flex gap-2">
                <div class="">
                <small>This Month</small>
                <?php
                    $query_curr_month = "SELECT SUM(discounted_price) as order_total 
                                        FROM orders 
                                        WHERE customerid = '$customer_id' 
                                        AND YEAR(order_date) = YEAR(CURDATE()) 
                                        AND MONTH(order_date) = MONTH(CURDATE())";

                    $result_curr_month = mysqli_query($conn, $query_curr_month);
                    
                    if ($result_curr_month) {
                        $row_curr_month = mysqli_fetch_array($result_curr_month, MYSQLI_ASSOC);
                        if ($row_curr_month) {
                            $order_total_curr_month = $row_curr_month['order_total'] ?? 0;
                        } else {
                            $order_total_curr_month = 0;
                        }
                    } else {
                        $order_total_curr_month = 0;
                    }
                ?>
                <h4 class="text-primary mb-0 ">$<?= number_format($order_total_curr_month,2) ?></h4>
                </div>
                <div class="">
                <div class="breadbar"></div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <div class="">
                <small>Last Month</small>
                <?php
                    $query_prev_month = "SELECT SUM(discounted_price) as order_total 
                                        FROM orders 
                                        WHERE customerid = '$customer_id' 
                                        AND YEAR(order_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) 
                                        AND MONTH(order_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";

                    $result_prev_month = mysqli_query($conn, $query_prev_month);
                    
                    if ($result_prev_month) {
                        $row_prev_month = mysqli_fetch_array($result_prev_month, MYSQLI_ASSOC);
                        if ($row_prev_month) {
                            $order_total_prev_month = $row_prev_month['order_total'] ?? 0;
                        } else {
                            $order_total_prev_month = 0;
                        }
                    } else {
                        $order_total_prev_month = 0;
                    }
                ?>
                <h4 class="text-secondary mb-0 ">$<?= number_format($order_total_prev_month,2) ?></h4>
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
    <div class="col-lg-3 col-md-6">
    <div class="card">
      <div class="card-body p-2">
        <img class="card-img-top w-100 profile-bg-height rounded overflow-hidden" src="assets/images/backgrounds/profile-bg.jpg" height="111" alt="Card image cap" />
        <div class="card-body little-profile text-center p-9">
          <div class="pro-img mb-3">
            <img src="assets/images/profile/user-2.jpg" alt="user" class="rounded-circle shadow-sm" width="112" />
          </div>
            <h3 class="mb-1 fs-14"><?= get_customer_name($customer_id) ?></h3>
            <p class="fs-3 mb-4"><?= getCustomerType($customer_details['customer_type_id']) ?></p>

            <?php
            $query_order_total = "
                SELECT 
                    subquery.customerid, 
                    subquery.order_total,
                    (SELECT COUNT(*) 
                    FROM (
                        SELECT SUM(discounted_price) AS order_total
                        FROM orders
                        WHERE YEAR(order_date) = YEAR(CURDATE())
                        GROUP BY customerid
                    ) AS totals
                    WHERE totals.order_total > subquery.order_total) + 1 AS customer_rank
                FROM (
                    SELECT 
                        o.customerid, 
                        SUM(o.discounted_price) AS order_total
                    FROM orders o
                    WHERE YEAR(o.order_date) = YEAR(CURDATE())
                    GROUP BY o.customerid
                ) AS subquery
                WHERE subquery.customerid = '$customer_id'
            ";
            
            $result_order_total = mysqli_query($conn, $query_order_total);
            if ($result_order_total) {
                $row_order_total = mysqli_fetch_array($result_order_total, MYSQLI_ASSOC);
                
                if ($row_order_total) {
                    $customer_order_total = $row_order_total['order_total'];
                    $customer_rank = $row_order_total['customer_rank'];
                    ?>
                    <a href="javascript:void(0)" class="btn btn-primary btn-md btn-rounded mb-7">Top <?= $customer_rank ?></a>
                    <?php
                } else {
                    ?>
                    <a href="javascript:void(0)" class="btn btn-primary btn-md btn-rounded mb-7">Unranked</a>
                    <?php
                }
            } else {
                echo "Error executing query: " . mysqli_error($conn);
            }
            ?>

            <div class="p-3 rounded shadow-sm">
                <?php if (!empty($customer_details['contact_phone'])): ?>
                <div class="d-flex flex-column align-items-center text-center p-2 flex-grow-1 mx-2">
                    <i class="fas fa-phone-alt fa-2x text-primary mb-2"></i> <small class="text-muted">Contact Phone</small>
                    <span class="fw-bold text-dark"><?= $customer_details['contact_phone'] ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($customer_details['contact_email'])): ?>
                <div class="d-flex flex-column align-items-center text-center p-2 flex-grow-1 mx-2">
                    <i class="fas fa-envelope fa-2x text-primary mb-2"></i> <small class="text-muted">Contact Email</small>
                    <span class="fw-bold text-dark"><?= $customer_details['contact_email'] ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="card-body">
                <div class="text-center">
                    <div class="align-self-center">
                        <h3 class="mb-1 fs-6">$<?= number_format($customer_details['store_credit'],2) ?></h3>
                        <span class="text-muted">Store Credit</span>
                    </div>
                </div>
            </div>
            
          <div class="row gx-lg-4 text-center pt-4 justify-content-center border-top">
            <div class="col-4">
              <?php
              $query_order_count = "SELECT count(*) as order_count FROM orders WHERE customerid = '$customer_id' AND YEAR(order_date) = YEAR(CURDATE())";
              $result_order_count = mysqli_query($conn, $query_order_count);
              
              if ($result_order_count) {
                  $row_order_count = mysqli_fetch_array($result_order_count, MYSQLI_ASSOC);
                  if ($row_order_count) {
                      $order_count = $row_order_count['order_count'];
                  } else {
                      $order_count = 0;
                  }
              }
              ?>
              <h3 class="mb-0 fs-14"><?= $order_count ?></h3>
              <small class="text-muted fs-3">Orders</small>
            </div>
            <div class="col-4">
              <?php
                $query_estimate_count = "SELECT count(*) as estimate_count FROM estimates WHERE customerid = '$customer_id' AND YEAR(estimated_date) = YEAR(CURDATE())";
                $result_estimate_count = mysqli_query($conn, $query_estimate_count);
                
                if ($result_estimate_count) {
                    $row_estimate_count = mysqli_fetch_array($result_estimate_count, MYSQLI_ASSOC);
                    if ($row_estimate_count) {
                        $estimate_count = $row_estimate_count['estimate_count'];
                    } else {
                        $estimate_count = 0;
                    }
                }
              ?>
              <h3 class="mb-0 fs-14"><?= $estimate_count ?></h3>
              <small class="text-muted fs-3">Estimates</small>
            </div>
            <div class="col-4">
              <?php
                $estimate_ordered = 0;
                $query_estimate = "SELECT * FROM estimates WHERE customerid = '$customer_id' AND YEAR(estimated_date) = YEAR(CURDATE())";
                $result_estimate = mysqli_query($conn, $query_estimate);
                
                if (mysqli_num_rows($result_estimate) > 0) {
                    while ($row_estimate = mysqli_fetch_array($result_estimate, MYSQLI_ASSOC)) {
                        $result_order = mysqli_query($conn, "SELECT * FROM orders WHERE estimateid = '{$row_estimate['estimateid']}' AND YEAR(order_date) = YEAR(CURDATE())");
                        if (mysqli_num_rows($result_order) > 0) {
                            $estimate_ordered++;
                        }
                    }
                }
              ?>
              <h3 class="mb-0 fs-14"><?= $estimate_ordered ?></h3>
              <small class="text-muted fs-3">Estimates - Orders</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="card card-body">
        <div class="card">
            <div class="card-body p-9">
                <div class="hstack gap-9">
                <div class="round-56 rounded-circle text-white d-flex align-items-center justify-content-center text-bg-primary">
                    <i class="ti ti-wallet fs-6"></i>
                </div>
                <div class="align-self-center">
                    <?php
                    $query_order_total = "SELECT SUM(discounted_price) as order_total FROM orders WHERE customerid = '$customer_id' AND YEAR(order_date) = YEAR(CURDATE())";
                    $result_order_total = mysqli_query($conn, $query_order_total);
                    
                    if ($result_order_total) {
                        $row_order_total = mysqli_fetch_array($result_order_total, MYSQLI_ASSOC);
                        if ($row_order_total) {
                            $order_total = $row_order_total['order_total'];
                        } else {
                            $order_total = 0;
                        }
                    }
                    ?>
                    
                    <h3 class="mb-1 fs-6">$<?= number_format($order_total,2) ?></h3>
                    <span class="text-muted">This Year Orders</span>
                </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body p-9">
                <div class="hstack gap-9">
                <div class="round-56 rounded-circle text-white d-flex align-items-center justify-content-center text-bg-secondary">
                    <i class="ti ti-users fs-6"></i>
                </div>
                <div class="align-self-center">
                    <?php
                    $query_order_total = "SELECT SUM(discounted_price) as order_total FROM orders WHERE customerid = '$customer_id' AND YEAR(order_date) = YEAR(CURDATE())";
                    $result_order_total = mysqli_query($conn, $query_order_total);
                    
                    if ($result_order_total) {
                        $row_order_total = mysqli_fetch_array($result_order_total, MYSQLI_ASSOC);
                        if ($row_order_total) {
                            $order_total = $row_order_total['order_total'];
                        } else {
                            $order_total = 0;
                        }
                    }
                    ?>
                    <h3 class="mb-1 fs-6">$<?= number_format($order_total,2) ?></h3>
                    <span class="text-muted">Total Orders</span>
                </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-9">
                <div class="hstack gap-9">
                <div class="round-56 rounded-circle text-white d-flex align-items-center justify-content-center text-bg-danger">
                    <i class="ti ti-calendar fs-6"></i>
                </div>
                <div class="align-self-center">
                    <?php
                    $query_credit_total = "SELECT SUM(credit_amt) as credit_total FROM orders WHERE customerid = '$customer_id' AND YEAR(order_date) = YEAR(CURDATE())";
                    $result_credit_total = mysqli_query($conn, $query_credit_total);
                    
                    if ($result_credit_total) {
                        $row_credit_total = mysqli_fetch_array($result_credit_total, MYSQLI_ASSOC);
                        if ($row_credit_total) {
                            $credit_total = $row_credit_total['credit_total'];
                        } else {
                            $credit_total = 0;
                        }
                    }
                    ?>
                    <h3 class="mb-1 fs-6">$<?= number_format($credit_total,2) ?></h3>
                    <span class="text-muted">Total Credit</span>
                </div>
                </div>
            </div>
        </div>
    </div>
    
    
  </div>
  <div class="col-lg-6 col-md-6">
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
                            <th scope="col">Size</th>
                            <th scope="col">Usage</th>
                            <th scope="col">Job Name</th>
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
  
 

 <!-- Orders -->
 <div class="col-lg-12">
    <div class="card">
      <div class="card-body pb-3">
        <div class="d-md-flex no-block">
          <h4 class="card-title">Orders</h4>
          <div class="ms-auto">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="date_from" class="form-label">Date From</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" class="form-control" id="date_from_order">
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="date_to" class="form-label">Date To</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" class="form-control" id="date_to_order">
                        </div>
                    </div>
                </div>
            </div>
          </div>
        </div>
        <div id="tbl-orders"></div>
        
      </div>
    </div>
  </div>
 
 <!-- Estimates -->
 <div class="col-lg-12">
    <div class="card">
      <div class="card-body pb-3">
        <div class="d-md-flex no-block">
          <h4 class="card-title">Estimates</h4>
          <div class="ms-auto">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="date_from" class="form-label">Date From</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" class="form-control" id="date_from_estimate">
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="date_to" class="form-label">Date To</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" class="form-control" id="date_to_estimate">
                        </div>
                    </div>
                </div>
            </div>
          </div>
        </div>
        <div id="tbl-estimates"></div>
      </div>
    </div>
  </div>

  <div class="col-lg-12">
    <div class="card">
      <div class="card-body pb-3">
        <div class="d-md-flex no-block">
          <h4 class="card-title">Jobs</h4>
          <div class="ms-auto">
            <div class="mb-2 text-end">
                <button type="button" id="addModalBtn" class="btn btn-primary" data-id="" data-customer-id="<?= $customer_id ?>" data-type="add">
                    <i class="fas fa-plus me-1"></i> Add Job
                </button>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="date_from" class="form-label">Date From</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" class="form-control" id="date_from_jobs">
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="date_to" class="form-label">Date To</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" class="form-control" id="date_to_jobs">
                        </div>
                    </div>
                </div>
            </div>
          </div>
        </div>
        <div id="tbl-jobs"></div>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="view_order_details_modal">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h6 class="modal-title">Order Details</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="order-details">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="view_job_dtls_modal">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h6 class="modal-title">Job Details</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="job-details">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

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
        <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">
          Close
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="add-header">
                    
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="jobForm" class="form-horizontal">
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">
                          <div id="add-fields" class=""></div>
                          <div class="form-actions">
                              <div class="border-top">
                                  <div class="row mt-2">
                                      <div class="col-6 text-start"></div>
                                      <div class="col-6 text-end ">
                                          <button type="submit" class="btn btn-primary" style="border-radius: 10%;">Save</button>
                                      </div>
                                  </div>
                              </div>
                          </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="depositModal" tabindex="-1" aria-labelledby="depositModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h5 class="modal-title">Add Job Deposit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="depositForm" class="form-horizontal">
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">
                            <input type="hidden" id="job_id" name="job_id">
                            <input type="hidden" id="deposited_by" name="deposited_by" value="<?= $customer_id ?>">

                            <div class="mb-3">
                                <label for="type" class="form-label">Deposit Type</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="cash">Cash</option>
                                    <option value="check">Check</option>
                                </select>
                            </div>

                            <div id="deposit_details_group" class="d-none">
                                <div class="mb-3">
                                    <label for="deposit_amount" class="form-label">Deposit Amount</label>
                                    <input type="number" step="0.01" class="form-control" id="deposit_amount" name="deposit_amount" >
                                </div>

                                <div class="mb-3">
                                    <label for="reference_no" class="form-label">Reference No</label>
                                    <input type="text" class="form-control" id="reference_no" name="reference_no" required>
                                </div>

                                <div class="mb-3 d-none" id="check_no_group">
                                    <label for="check_no" class="form-label">Check No</label>
                                    <input type="text" class="form-control" id="check_no" name="check_no">
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" style="border-radius: 10%;">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="viewEstimateModal" tabindex="-1" aria-labelledby="viewEstimateModalLabel" aria-hidden="true"></div>

<div class="modal fade" id="viewChangesModal" tabindex="-1" aria-labelledby="viewChangesModalLabel" aria-hidden="true"></div>

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
              url: 'pages/customer-dash_ajax.php',
              type: 'POST',
              data: {
                  query: query,
                  customerid: <?= $_REQUEST['id'] ?>
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
      
      function searchOrders() {
          var date_from = $('#date_from_order').val();
          var date_to = $('#date_to_order').val();

          $.ajax({
              url: 'pages/customer-dash_ajax.php',
              type: 'POST',
              data: {
                  customerid: <?= $_REQUEST['id'] ?>,
                  date_from: date_from,
                  date_to: date_to,
                  search_orders: 'search_orders'
              },
              success: function(response) {
                  $('#tbl-orders').html(response);
              },
              error: function(jqXHR, textStatus, errorThrown) {
                  alert('Error: ' + textStatus + ' - ' + errorThrown);
              }
          });
      }

      function searchEstimates() {
          var date_from = $('#date_from_estimate').val();
          var date_to = $('#date_to_estimate').val();

          $.ajax({
              url: 'pages/customer-dash_ajax.php',
              type: 'POST',
              data: {
                  customerid: <?= $_REQUEST['id'] ?>,
                  date_from: date_from,
                  date_to: date_to,
                  search_estimates: 'search_estimates'
              },
              success: function(response) {
                  $('#tbl-estimates').html(response);
              },
              error: function(jqXHR, textStatus, errorThrown) {
                  alert('Error: ' + textStatus + ' - ' + errorThrown);
              }
          });
      }

      function searchJobs() {
          var date_from = $('#date_from_jobs').val();
          var date_to = $('#date_to_jobs').val();

          $.ajax({
              url: 'pages/customer-dash_ajax.php',
              type: 'POST',
              data: {
                  customerid: <?= $_REQUEST['id'] ?>,
                  date_from: date_from,
                  date_to: date_to,
                  search_jobs: 'search_jobs'
              },
              success: function(response) {
                console.log(response);
                  $('#tbl-jobs').html(response);
              },
              error: function(jqXHR, textStatus, errorThrown) {
                  alert('Error: ' + textStatus + ' - ' + errorThrown);
              }
          });
      }

    

    // Toggle display based on type selected
    $(document).on('change', '#type', function () {
        const type = $(this).val();

        if (type === 'cash') {
            $('#deposit_details_group').removeClass('d-none');
            $('#check_no_group').addClass('d-none');
            $('#check_no').removeAttr('required').val('');
        } else if (type === 'check') {
            $('#deposit_details_group').removeClass('d-none');
            $('#check_no_group').removeClass('d-none');
            $('#check_no').attr('required', true);
        } else {
            $('#deposit_details_group').addClass('d-none');
            $('#check_no_group').addClass('d-none');
            $('#check_no').removeAttr('required').val('');
        }
    });

    $(document).on('click', '#addModalBtn', function(event) {
        event.preventDefault();
        var job_id = $(this).data('job-id') || '';
        var customer_id = $(this).data('customer-id') || '';
        var type = $(this).data('type') || '';

        if(type == 'edit'){
            $('#add-header').html('Update Job');
        }else{
            $('#add-header').html('Add Job');
        }

        $.ajax({
            url: 'pages/customer-dash_ajax.php',
            type: 'POST',
            data: {
                customer_id: customer_id,
                job_id : job_id,
                fetch_job_modal: 'fetch_job_modal'
            },
            success: function (response) {
                $('#add-fields').html(response);

                $(".select2-form").each(function () {
                    $(this).select2({
                        width: '100%',
                        dropdownParent: $(this).parent(),
                        templateResult: formatOption,
                        templateSelection: formatOption
                    });
                });

                $('#addModal').modal('show');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.error('Response:', jqXHR.responseText);

                $('#responseHeader').text("Error");
                $('#responseMsg').text("An error occurred while processing your request.");
                $('#responseHeaderContainer').removeClass("bg-success").addClass("bg-danger");
                $('#response-modal').modal("show");
            }
        });
    });

    $(document).on('click', '#depositModalBtn', function(event) {
        event.preventDefault();
        var job_id = $(this).data('job') || '';
        $('#job_id').val(job_id);
        $('#depositModal').modal('show');
    });

    $('#depositForm').on('submit', function(event) {
        event.preventDefault(); 
        var formData = new FormData(this);
        formData.append('deposit_job', 'deposit_job');
        $.ajax({
            url: 'pages/customer-dash_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('.modal').modal("hide");
                if (response == "success") {
                    $('#responseHeader').text("Success");
                    $('#responseMsg').text("Amount Deposited successfully!");
                    $('#responseHeaderContainer').removeClass("bg-danger");
                    $('#responseHeaderContainer').addClass("bg-success");
                    $('#response-modal').modal("show");

                    $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                    });
                } else {
                    $('#responseHeader').text("Failed");
                    $('#responseMsg').text("Process Failed");
                    console.log("Response: "+response);
                    $('#responseHeaderContainer').removeClass("bg-success");
                    $('#responseHeaderContainer').addClass("bg-danger");
                    $('#response-modal').modal("show");
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    $('#jobForm').on('submit', function(event) {
        event.preventDefault(); 
        var formData = new FormData(this);
        formData.append('save_job', 'save_job');
        $.ajax({
            url: 'pages/customer-dash_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              $('.modal').modal("hide");
              if (response == "success_add") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text("New Job added successfully!");
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");

                  $('#response-modal').on('hide.bs.modal', function () {
                        location.reload();
                  });
              } else if (response == "success_update") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text("Job updated successfully!");
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");

                  $('#response-modal').on('hide.bs.modal', function () {
                        location.reload();
                  });
              } else {
                  $('#responseHeader').text("Failed");
                  $('#responseMsg').text("Process Failed");
                  console.log("Response: "+response);
                  $('#responseHeaderContainer').removeClass("bg-success");
                  $('#responseHeaderContainer').addClass("bg-danger");
                  $('#response-modal').modal("show");
              }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

      $(document).on('click', '#view_order_btn', function(event) {
          event.preventDefault(); 
          var orderid = $(this).data('id');
          $.ajax({
                  url: 'pages/customer-dash_ajax.php',
                  type: 'POST',
                  data: {
                      orderid: orderid,
                      fetch_order_details: "fetch_order_details"
                  },
                  success: function(response) {
                      $('#order-details').html(response);
                      $('#view_order_details_modal').modal('show');
                  },
                  error: function(jqXHR, textStatus, errorThrown) {
                      alert('Error: ' + textStatus + ' - ' + errorThrown);
                  }
          });
      });

      $(document).on('click', '#view_estimate_btn', function(event) {
          event.preventDefault(); 
          var id = $(this).data('id');
          $.ajax({
                  url: 'pages/customer-dash_ajax.php',
                  type: 'POST',
                  data: {
                      id: id,
                      fetch_estimate_details: "fetch_estimate_details"
                  },
                  success: function(response) {
                      $('#viewEstimateModal').html(response);
                      $('#viewEstimateModal').modal('show');
                  },
                  error: function(jqXHR, textStatus, errorThrown) {
                      alert('Error: ' + textStatus + ' - ' + errorThrown);
                  }
          });
      });

      $(document).on('click', '#view_changes_btn', function(event) {
          event.preventDefault(); 
          var id = $(this).data('id');
          $.ajax({
                  url: 'pages/customer-dash_ajax.php',
                  type: 'POST',
                  data: {
                      id: id,
                      fetch_changes_modal: "fetch_changes_modal"
                  },
                  success: function(response) {
                      $('#viewChangesModal').html(response);
                      $('#viewChangesModal').modal('show');
                  },
                  error: function(jqXHR, textStatus, errorThrown) {
                      alert('Error: ' + textStatus + ' - ' + errorThrown);
                  }
          });
      });

      $(document).on('click', '#view_job_dtls_btn', function(event) {
          event.preventDefault(); 
          var job_name = $(this).data('name');
          var date_from = $(this).data('date-from');
          var date_to = $(this).data('date-to');
          $.ajax({
                url: 'pages/customer-dash_ajax.php',
                type: 'POST',
                data: {
                    customerid: <?= $_REQUEST['id'] ?>,
                    job_name: job_name,
                    date_from: date_from,
                    date_to: date_to,
                    fetch_job_details: "fetch_job_details"
                },
                success: function(response) {
                    $('#job-details').html(response);
                    $('#view_job_dtls_modal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
          });
      });

      $('#date_from_order, #date_to_order').on('change', searchOrders);
      $('#date_from_estimate, #date_to_estimate').on('change', searchEstimates);
      $('#date_from_jobs, #date_to_jobs').on('change', searchJobs);
      $('#text-srh').on('input', function() { searchProducts(this.value); });

      searchOrders();
      searchEstimates();
      searchJobs();
      searchProducts('');
  });
</script>
