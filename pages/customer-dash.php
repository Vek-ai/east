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

    <div class="row">
  <!-- text-cards -->
    <!-- Profile card -->
    <div class="col-lg-3 col-md-6">
    <div class="card">
      <div class="card-body p-2">
        <img class="card-img-top w-100 profile-bg-height rounded overflow-hidden" src="assets/images/backgrounds/profile-bg.jpg" height="111" alt="Card image cap" />
        <div class="card-body little-profile text-center p-9">
          <div class="pro-img mb-3">
            <img src="assets/images/profile/user-2.jpg" alt="user" class="rounded-circle shadow-sm" width="112" />
          </div>
          <h3 class="mb-1 fs-14"><?= $customer_details['customer_first_name'] ?> <?= $customer_details['customer_last_name'] ?></h3>
          <p class="fs-3 mb-4"><?= getCustomerType($customer_details['customer_type_id']) ?></p>
          <a href="javascript:void(0)" class="
              btn btn-primary btn-md btn-rounded mb-7
            ">Top 10</a>
          <div class="row gx-lg-4 text-center pt-4 justify-content-center border-top">
            <div class="col-4">
              <?php
              $query_order_count = "SELECT count(*) as order_count FROM orders WHERE customerid = '$customer_id'";
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
                $query_estimate_count = "SELECT count(*) as estimate_count FROM estimates WHERE customerid = '$customer_id'";
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
                $query_estimate = "SELECT * FROM estimates WHERE customerid = '$customer_id'";
                $result_estimate = mysqli_query($conn, $query_estimate);
                
                if (mysqli_num_rows($result_estimate) > 0) {
                    while ($row_estimate = mysqli_fetch_array($result_estimate, MYSQLI_ASSOC)) {
                        $result_order = mysqli_query($conn, "SELECT * FROM orders WHERE estimateid = '{$row_estimate['estimateid']}'");
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
    <div class="card">
      <div class="card-body p-9">
        <div class="hstack gap-9">
          <div class="round-56 rounded-circle text-white d-flex align-items-center justify-content-center text-bg-primary">
            <i class="ti ti-credit-card fs-6"></i>
          </div>
          <div class="align-self-center">
            <?php
              $query_order_total = "SELECT SUM(discounted_price) as order_total FROM orders WHERE customerid = '$customer_id'";
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
            <span class="text-muted">Total Orders Amount</span>
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
              $query_estimate_total = "SELECT SUM(discounted_price) as estimate_total FROM estimates WHERE customerid = '$customer_id'";
              $result_estimate_total = mysqli_query($conn, $query_estimate_total);
              
              if ($result_estimate_total) {
                  $row_estimate_total = mysqli_fetch_array($result_estimate_total, MYSQLI_ASSOC);
                  if ($row_estimate_total) {
                      $estimate_total = $row_estimate_total['estimate_total'];
                  } else {
                      $estimate_total = 0;
                  }
              }
            ?>
            <h3 class="mb-1 fs-6">$<?= number_format($estimate_total,2) ?></h3>
            <span class="text-muted">Total Estimates Amount</span>
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
              $estimate_ordered_amt = 0;
              $query_estimate = "SELECT * FROM estimates WHERE customerid = '$customer_id'";
              $result_estimate = mysqli_query($conn, $query_estimate);
              
              if ($result_estimate && mysqli_num_rows($result_estimate) > 0) {
                  while ($row_estimate = mysqli_fetch_array($result_estimate, MYSQLI_ASSOC)) {
                      $result_order = mysqli_query($conn, "SELECT * FROM orders WHERE estimateid = '{$row_estimate['estimateid']}'");
                      
                      if ($result_order && mysqli_num_rows($result_order) > 0) {
                          while ($row_order = mysqli_fetch_array($result_order, MYSQLI_ASSOC)) {
                              $estimate_ordered_amt += $row_order['discounted_price']; 
                          }
                      }
                  }
              }
            ?>
            <h3 class="mb-1 fs-6">$<?= number_format($estimate_ordered_amt,2) ?></h3>
            <span class="text-muted">Total Estimates to Orders Amount</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-6 col-md-6">
    
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
        <div class="month-table">
          <div class="table-responsive mt-3">
            <table class="table align-middle  mb-0 no-wrap">
              <thead>
                <tr>
                  <th class="border-0 ps-0">
                  Sales Person
                  </th>
                  <th class="border-0">Date</th>
                  <th class="border-0">
                    Total Amount
                  </th>
                  <th class="border-0 text-end">
                    Discount
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
        <div class="month-table">
          <div class="table-responsive mt-3">
            <table class="table align-middle  mb-0 no-wrap">
              <thead>
                <tr>
                  <th class="border-0 ps-0">
                    Estimate ID
                  </th>
                  <th class="border-0">Status</th>
                  <th class="border-0">
                    No. of changes
                  </th>
                  <th class="border-0 text-end">
                    Total Amount
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


</div>