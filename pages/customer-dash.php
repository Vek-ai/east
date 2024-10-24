<?php
require 'includes/dbconn.php';
require 'includes/functions.php';
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
          <h3 class="mb-1 fs-14">Angelo Dominic</h3>
          <p class="fs-3 mb-4">Customer Type</p>
          <a href="javascript:void(0)" class="
              btn btn-primary btn-md btn-rounded mb-7
            ">Top 10</a>
          <div class="row gx-lg-4 text-center pt-4 justify-content-center border-top">
            <div class="col-4">
              <h3 class="mb-0 fs-14">1099</h3>
              <small class="text-muted fs-3">Orders</small>
            </div>
            <div class="col-4">
              <h3 class="mb-0 fs-14">23,469</h3>
              <small class="text-muted fs-3">Estimates</small>
            </div>
            <div class="col-4">
              <h3 class="mb-0 fs-14">6035</h3>
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
            <h3 class="mb-1 fs-6">$3249</h3>
            <span class="text-muted">Total Revenue</span>
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
            <h3 class="mb-1 fs-6">$2376</h3>
            <span class="text-muted">Online Revenue</span>
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
            <h3 class="mb-1 fs-6">$1795</h3>
            <span class="text-muted">Offline Products</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4 col-md-6">
    
  </div>
  
 

 <!-- Orders -->
 <div class="col-lg-12">
    <div class="card">
      <div class="card-body pb-3">
        <div class="d-md-flex no-block">
          <h4 class="card-title">Orders</h4>
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