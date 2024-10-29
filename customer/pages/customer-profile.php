<?php
include '../includes/dbconn.php';

if (!isset($_SESSION['userid'])) {
  header("Location: login.php");
  exit();
}

global $currentUser;

$customer_id = $_SESSION['userid'];

$sql = "SELECT *, customer_types.customer_type_name 
        FROM customer 
        LEFT JOIN customer_types ON customer.customer_type_id = customer_types.customer_type_id 
        WHERE customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  $currentUser = $result->fetch_assoc();
} else {
  $currentUser = null;
}

$stmt->close();

$firstName = isset($currentUser['customer_first_name']) ? htmlspecialchars($currentUser['customer_first_name']) : 'First Name';
$lastName = isset($currentUser['customer_last_name']) ? htmlspecialchars($currentUser['customer_last_name']) : 'Last Name';
?>

<div class="container-fluid">
  <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h4 class="font-weight-medium fs-14 mb-0">User Profile</h4>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Home
                </a>
              </li>
              <li class="breadcrumb-item text-muted" aria-current="page">User Profile</li>
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

  <div class="card overflow-hidden">
    <div class="card-body p-0">
      <img src="../../assets/images/backgrounds/profilebg.jpg" alt="materialpro-img" class="img-fluid">
      <div class="row align-items-center">
        <div class="col-lg-4 order-lg-1 order-2">
          <div class="d-flex align-items-center justify-content-around">
          <div class="text-center">
            <i class="ti ti-phone fs-6"></i>
            <h4 class="mb-0 fw-semibold lh-1">
              <?= htmlspecialchars($currentUser['call_status'] == 1 ? 'Enabled' : 'Disabled') ?>
            </h4>
            <p class="mb-0">Call Status</p>
          </div>
            <div class="text-center">
              <i class="ti ti-users fs-6"></i>
              <h4 class="mb-0 fw-semibold lh-1">
                <?= htmlspecialchars($currentUser['loyalty'] == 1 ? 'Active' : 'Inactive') ?>
              </h4>
              <p class="mb-0 ">Loyalty Status</p>
            </div>
            <div class="text-center">
              <i class="ti ti-user-check fs-6 d-block mb-2"></i>
              <h4 class="mb-0 fw-semibold lh-1">
              <?= htmlspecialchars($currentUser['customer_type_name']) ?>
              </h4>
              <p class="mb-0 ">Customer Type</p>
            </div>
          </div>
        </div>
        <div class="col-lg-4 mt-n3 order-lg-2 order-1">
          <div class="mt-n5">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <div class="d-flex align-items-center justify-content-center round-110">
                <div
                  class="border border-4 border-white d-flex align-items-center justify-content-center rounded-circle overflow-hidden round-100">
                  <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="w-100 h-100">
                </div>
              </div>
            </div>
            <div class="text-center">
              <h5 class="mb-0">
                <h5 class="mb-1 fs-4"><?php echo $firstName . ' ' . $lastName; ?></h5>
              </h5>
              <p class="mb-0">Customer</p>
            </div>
          </div>
        </div>
        <div class="col-lg-4 order-last">
          <ul
            class="list-unstyled d-flex align-items-center justify-content-center justify-content-lg-end my-3 mx-4 pe-4 gap-3">
            <li>
              <a class="d-flex align-items-center justify-content-center btn btn-primary p-2 fs-4 rounded-circle"
                href="javascript:void(0)" width="30" height="30">
                <i class="ti ti-brand-facebook"></i>
              </a>
            </li>
            <li>
              <a class="btn btn-secondary d-flex align-items-center justify-content-center p-2 fs-4 rounded-circle"
                href="javascript:void(0)">
                <i class="ti ti-brand-dribbble"></i>
              </a>
            </li>
            <li>
              <a class="btn btn-danger d-flex align-items-center justify-content-center p-2 fs-4 rounded-circle"
                href="javascript:void(0)">
                <i class="ti ti-brand-youtube"></i>
              </a>
            </li>
            <li>
              <button class="btn btn-primary text-nowrap">Add To Story</button>
            </li>
          </ul>
        </div>
      </div>
      <ul class="nav nav-pills user-profile-tab justify-content-end mt-2 bg-primary-subtle rounded-2 rounded-top-0"
        id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active hstack gap-2 rounded-0 fs-12 py-6" id="pills-address-tab" data-bs-toggle="pill"
            data-bs-target="#pills-address" type="button" role="tab" aria-controls="pills-address" aria-selected="true">
            <i class="ti ti-user-circle fs-5"></i>
            <span class="d-none d-md-block">Address</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link hstack gap-2 rounded-0 fs-12 py-6" id="pills-contacts-tab" data-bs-toggle="pill"
            data-bs-target="#pills-contacts" type="button" role="tab" aria-controls="pills-contacts"
            aria-selected="false">
            <i class="ti ti-heart fs-5"></i>
            <span class="d-none d-md-block">Contacts</span>
          </button>
        </li>
      </ul>
    </div>

    <div class="tab-content" id="pills-tabContent">
      <div class="tab-pane fade show active" id="pills-address" role="tabpanel" aria-labelledby="pills-address-tab"
        tabindex="0">
        <div class="row">
          <div class="col-lg-12 mt-4">
            <div class="card shadow-none">
              <div class="card-body">
                <h4>Address</h4>
                <div class="table-responsive border rounded">
                  <table id="productTable" class="table align-middle text-nowrap mb-0 table-fixed text-center">
                    <thead>
                      <tr>
                        <th scope="col">Address</th>
                        <th scope="col">City</th>
                        <th scope="col">State</th>
                        <th scope="col">Zip</th>
                      </tr>
                    </thead>
                    <tbody id="productTableBody">
                      <?php if ($currentUser): ?>
                        <tr>
                          <td><?= htmlspecialchars($currentUser['address']) ?></td>
                          <td><?= htmlspecialchars($currentUser['city']) ?></td>
                          <td><?= htmlspecialchars($currentUser['state']) ?></td>
                          <td><?= htmlspecialchars($currentUser['zip']) ?></td>
                        </tr>
                      <?php else: ?>
                        <tr>
                          <td colspan="4">No address data available.</td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>


      <div class="tab-pane fade" id="pills-contacts" role="tabpanel" aria-labelledby="pills-contacts-tab" tabindex="0">
        <div class="row">
          <div class="col-lg-12 mt-4">
            <div class="card shadow-none">
              <div class="card-body">
                <h4>Contacts</h4>
                <div class="table-responsive border rounded">
                  <table id="productTable" class="table align-middle text-nowrap mb-0 table-fixed text-center">
                    <thead>
                      <tr>
                        <th scope="col">Email</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Fax</th>
                      </tr>
                    </thead>
                    <tbody id="productTableBody">
                      <?php if ($currentUser): ?>
                        <tr>
                          <td><?= htmlspecialchars($currentUser['contact_email']) ?></td>
                          <td><?= htmlspecialchars($currentUser['contact_phone']) ?></td>
                          <td><?= htmlspecialchars($currentUser['contact_fax']) ?></td>
                        </tr>
                      <?php else: ?>
                        <tr>
                          <td colspan="4">No address data available.</td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-12 mt-4">
            <div class="card shadow-none">
              <div class="card-body">
                <h4>Secondary Contacts</h4>
                <div class="table-responsive border rounded">
                  <table id="productTable" class="table align-middle text-nowrap mb-0 table-fixed text-center">
                    <thead>
                      <tr>
                        <th scope="col">Secondary Contacts Name</th>
                        <th scope="col">Secondary Contacts Phone</th>
                      </tr>
                    </thead>
                    <tbody id="productTableBody">
                      <?php if ($currentUser): ?>
                        <tr>
                          <td><?= htmlspecialchars($currentUser['secondary_contact_name']) ?></td>
                          <td><?= htmlspecialchars($currentUser['secondary_contact_phone']) ?></td>
                        </tr>
                      <?php else: ?>
                        <tr>
                          <td colspan="4">No address data available.</td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-12 mt-4">
            <div class="card shadow-none">
              <div class="card-body">
                <h4>AP Contacts</h4>
                <div class="table-responsive border rounded">
                  <table id="productTable" class="table align-middle text-nowrap mb-0 table-fixed text-center">
                    <thead>
                      <tr>
                        <th scope="col">AP Contacts Name</th>
                        <th scope="col">AP Contacts Phone</th>
                      </tr>
                    </thead>
                    <tbody id="productTableBody">
                      <?php if ($currentUser): ?>
                        <tr>
                          <td><?= htmlspecialchars($currentUser['ap_contact_name']) ?></td>
                          <td><?= htmlspecialchars($currentUser['ap_contact_phone']) ?></td>
                        </tr>
                      <?php else: ?>
                        <tr>
                          <td colspan="4">No address data available.</td>
                        </tr>
                      <?php endif; ?>
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
</div>