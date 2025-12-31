<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require '../includes/dbconn.php';
require '../includes/functions.php';

$page_title = "Customer";

$customer_id = "";
$customer_first_name = "";
$customer_last_name = "";
$customer_business_name = "";
$old_customer_type_id = "";
$contact_email = "";
$contact_phone = "";
$contact_fax = "";
$address = "";
$city = "";
$state = "";
$zip = "";
$secondary_contact_name = "";
$secondary_contact_phone = "";
$ap_contact_name = "";
$ap_contact_email = "";
$ap_contact_phone = "";
$tax_status = "";
$tax_exempt_number = "";
$customer_notes = "";
$call_status = "";
$customer_pricing = 0;
$credit_limit = 0;
$lat = 0;
$lng = 0;
$primary_contact = 1;

$customer_name = $customer_first_name . " " . $customer_last_name;

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";
?>

<style>
  td.notes,
  td.last-edit {
    white-space: normal;
    word-wrap: break-word;
  }

  .emphasize-strike {
    text-decoration: line-through;
    font-weight: bold;
    color: #9a841c;
  }

  .dataTables_filter input {
    width: 100%;
    height: 50px;
    font-size: 16px;
    padding: 10px;
    border-radius: 5px;
  }

  .dataTables_filter {
    width: 100%;
  }

  #toggleActive {
    margin-bottom: 10px;
  }

  .inactive-row {
    display: none;
  }

  .tooltip-inner {
    font-size: calc(0.875rem + 2px) !important;
  }
</style>
<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
  <div class="card-body px-0">
    <div class="d-flex justify-content-between align-items-center">
      <div><br>
        <h4 class="font-weight-medium fs-14 mb-0">Customer</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="">Customers
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Customer</li>
          </ol>
        </nav>
      </div>
      <div>
        <div class="d-sm-flex d-none gap-3 no-block justify-content-end align-items-center">
          
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card card-body">
    <div class="row">
      <div class="col-md-12 col-xl-12 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0 gap-3">
          <button type="button" class="btn btn-primary d-flex align-items-center addModalBtn" data-id="" data-type="e">
              <i class="ti ti-plus text-white me-1 fs-5"></i> Add <?= $page_title ?>
          </button>
          <button type="button" id="downloadClassModalBtn" class="btn btn-primary d-flex align-items-center">
              <i class="ti ti-download text-white me-1 fs-5"></i> Download Classifications
          </button>
          <button type="button" id="downloadBtn" class="btn btn-primary d-flex align-items-center">
              <i class="ti ti-download text-white me-1 fs-5"></i> Download <?= $page_title ?>
          </button>
          <button type="button" id="uploadBtn" class="btn btn-primary d-flex align-items-center">
              <i class="ti ti-upload text-white me-1 fs-5"></i> Upload <?= $page_title ?>
          </button>
      </div>
    </div>
</div>

<div class="card card-body">
    <div class="row">
        <div class="col-3">
            <h3 class="card-title align-items-center mb-2">
                Filter Customers 
            </h3>
            <div class="position-relative w-100 px-0 mr-0 mb-2">
                <input type="text" class="form-control py-2 ps-5" data-filter-name="Customer Name" id="text-srh" placeholder="Search">
                <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
            </div>
            <div class="align-items-center">
                <div class="position-relative w-100 px-1 mb-2">
                    <select class="form-control py-0 ps-5 select2 filter-selection" data-filter="tax" data-filter-name="Tax" id="select-tax">
                        <option value="">All Tax Status</option>
                        <optgroup label="Tax Status">
                          <?php
                          $query_tax_status = "SELECT * FROM customer_tax";
                          $result_tax_status = mysqli_query($conn, $query_tax_status);
                          while ($row_tax_status = mysqli_fetch_array($result_tax_status)) {
                            ?>
                            <option value="<?= $row_tax_status['taxid'] ?>">
                              (<?= $row_tax_status['percentage'] ?>%) <?= $row_tax_status['tax_status_desc'] ?></option>
                          <?php
                          }
                          ?>
                        </optgroup>
                    </select>
                </div>
                <div class="position-relative w-100 px-1 mb-2">
                    <select class="form-control search-category py-0 ps-5 select2 filter-selection" data-filter="loyalty" data-filter-name="Loyalty" id="select-loyalty">
                        <option value="">All Loyalty Options</option>
                        <option value="1">ON</option>
                        <option value="0">OFF</option>
                    </select>
                </div>
                <div class="position-relative w-100 px-1 mb-2">
                    <select class="form-control search-category py-0 ps-5 select2 filter-selection" data-filter="pricing" data-filter-name="Pricing Category" id="select-pricing">
                        <option value="" data-category="">All Pricing Category</option>
                        <optgroup label="Pricing Category">
                            <?php
                            $query_pricing = "SELECT * FROM customer_pricing WHERE hidden = '0' AND status = '1'";
                            $result_pricing = mysqli_query($conn, $query_pricing);            
                            while ($row_pricing = mysqli_fetch_array($result_pricing)) {
                            ?>
                                <option value="<?= $row_pricing['id'] ?>"><?= $row_pricing['pricing_name'] ?></option>
                            <?php   
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
                <div class="position-relative w-100 px-1 mb-2">
                    <select class="form-control search-category py-0 ps-5 select2 filter-selection" data-filter="city" data-filter-name="City" id="select-city">
                        <option value="">All Cities</option>
                        <optgroup label="Cities">
                            <?php
                            $query_city = "SELECT DISTINCT LOWER(city) AS city_lower 
                                              FROM customer 
                                              WHERE city IS NOT NULL AND city <> '' AND status = '1' and hidden = '0'
                                              ORDER BY city_lower;";
                            $result_city = mysqli_query($conn, $query_city);            
                            while ($row_city = mysqli_fetch_array($result_city)) {
                            ?>
                                <option value="<?= $row_city['city_lower'] ?>"><?= ucwords($row_city['city_lower']) ?></option>
                            <?php   
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="px-3 mb-2"> 
                <input type="checkbox" id="toggleActive" checked> Show Active Only
            </div>
            <div class="d-flex justify-content-end py-2">
                <button type="button" class="btn btn-outline-primary reset_filters">
                    <i class="fas fa-sync-alt me-1"></i> Reset Filters
                </button>
            </div>
        </div>
        <div class="col-9">
            <div id="selected-tags" class="mb-2"></div>
            <div class="datatables">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title d-flex justify-content-between align-items-center">Customer List</h4>
                  <div class="table-responsive">
                    <table id="display_customer" class="table table-bordered align-middle table-hover mb-0 text-md-nowrap">
                      <thead>
                        <tr>
                          <th>Name</th>
                          <th>Business Name</th>
                          <th>Phone Number</th>
                          <th>Status</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $no = 1;
                        $query_customer = "
                          SELECT c.*, ct.customer_type_name, 
                          (SELECT COUNT(o.customerid) FROM orders o WHERE o.customerid = c.customer_id) AS order_count,
                          lp.accumulated_total_orders, lp.loyalty_program_name
                          FROM customer c
                          LEFT JOIN customer_types ct ON c.customer_type_id = ct.customer_type_id 
                          LEFT JOIN loyalty_program lp ON c.loyalty = 1 AND 
                          (SELECT COUNT(o.customerid) FROM orders o WHERE o.customerid = c.customer_id) >= lp.accumulated_total_orders
                          WHERE c.hidden = 0";

                        $result_customer = mysqli_query($conn, $query_customer);
                        while ($row_customer = mysqli_fetch_array($result_customer)) {
                          $customer_id = $row_customer['customer_id'];
                          $name = $row_customer['customer_first_name'] . " " . $row_customer['customer_last_name'];
                          $business_name = $row_customer['customer_business_name'];
                          $email = $row_customer['contact_email'];
                          $phone = $row_customer['contact_phone'];
                          $fax = $row_customer['contact_fax'];
                          $address = $row_customer['address'];
                          $customer_type_name = $row_customer['customer_type_name'];
                          $db_status = $row_customer['status'];
                          $order_count = $row_customer['order_count'];
                        
                          // Check loyalty field and display accordingly
                          if ($row_customer['loyalty'] == '1') {
                            // Show loyalty program name if loyalty is 1
                            if ($order_count >= $row_customer['accumulated_total_orders']) {
                              $loyalty = $row_customer['loyalty_program_name'];
                            } else {
                              $loyalty = "No Loyalty Level";
                            }
                          } else {
                            // Show "Off" if loyalty is not 1
                            $loyalty = "Off";
                          }

                          // Display status
                          if ($row_customer['status'] == '0' || $row_customer['status'] == '3') {
                            $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$customer_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                          } else {
                            $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$customer_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                          }
                          ?>
                          <tr id="product-row-<?= $no ?>"
                              data-tax="<?= $row_customer['tax_status'] ?>"
                              data-loyalty="<?= $row_customer['loyalty'] ?>"
                              data-city="<?= strtolower($row_customer['city']) ?>"
                              data-pricing="<?= $row_customer['customer_pricing'] ?>"
                          >
                            <td>
                              <a href="javascript:void(0)" class="text-decoration-none">
                                <span class="customer<?= $no ?><?php if ($row_customer['status'] == '0' || $row_customer['status'] == '3') {echo 'emphasize-strike';} ?>">
                                  <?php 
                                  if($row_customer['status'] == '3'){
                                    $merge_details = getCustomerDetails($customer_id);
                                    $merge_id = $merge_details['merge_from'];
                                    $current_details = getCustomerDetails($merge_id);
                                    echo "$name - Merge to " .$current_details['customer_first_name'] . " " . $current_details['customer_last_name'];
                                  }else{
                                    echo $name;
                                  }
                                  ?>
                                </span>
                              </a>
                            </td>
                            <td><?= $business_name ?></td>
                            <td><?= $phone ?></td>
                            <td><?= $status ?></td>
                            <td class="text-center fs-5" id="action-button-<?= $no ?>">
                              <?php if ($row_customer['status'] == '0') { ?>
                                <a href="?page=customer&customer_id=<?= $customer_id ?>&t=e" class="py-1 text-dark hideCustomer" data-id="<?= $customer_id ?>" data-row="<?= $no ?>"
                                  style='border-radius: 10%;' data-toggle="tooltip" data-placement="top" title="Archive"><i
                                    class="fa fa-box-archive text-danger"></i></a>
                              <?php } else { ?>
                                <a href="javascript:void(0)" data-id="<?= $customer_id ?>" data-type="v" class="py-1 pe-1 addModalBtn"
                                  data-toggle="tooltip" data-placement="top" title="View">
                                  <i class="fa fa-eye text-light"></i>
                                </a>
                                <a href="javascript:void(0)" data-id="<?= $customer_id ?>" data-type="e" class="py-1 pe-1 addModalBtn"
                                  data-toggle="tooltip" data-placement="top" title="Edit">
                                  <i class="fa fa-pencil text-light"></i>
                                </a>
                                <a href="?page=customer-dashboard&id=<?= $customer_id ?>" class="py-1 pe-1" style='border-radius: 10%;'
                                  data-toggle="tooltip" data-placement="top" title="Dashboard"><i
                                    class="fa fa-chart-bar text-warning"></i>
                                </a>
                                <a href="?page=customer_login_creds&id=<?= $customer_id ?>" class="py-1 pe-1" style='border-radius: 10%;'
                                  data-toggle="tooltip" data-placement="top" title="Username and Password">
                                  <i class="fa-solid fa-lock text-info"></i>
                                </a>
                                <a href="?page=estimate_list&customer_id=<?= $customer_id ?>" class="py-1 pe-1"
                                  style='border-radius: 10%;' data-toggle="tooltip" data-placement="top" title="Estimates"><i
                                    class="fa fa-calculator text-primary"></i>
                                </a>
                                <a href="?page=order_list&customer_id=<?= $customer_id ?>" class="py-1 pe-1"
                                  style='border-radius: 10%;' data-toggle="tooltip" data-placement="top" title="Orders"><i
                                    class="fa fa-cart-shopping text-success"></i>
                                </a>
                              <?php } ?>
                            </td>
                          </tr>
                          <?php
                          $no++;
                        }
                        ?>
                      </tbody>


                      <script>
                        $(document).ready(function () {
                          $('[data-toggle="tooltip"]').tooltip();
                          // Use event delegation for dynamically generated elements
                          $(document).on('click', '.changeStatus', function (event) {
                            event.preventDefault();
                            var customer_id = $(this).data('id');
                            var status = $(this).data('status');
                            var no = $(this).data('no');
                            $.ajax({
                              url: 'pages/customer_ajax.php',
                              type: 'POST',
                              data: {
                                customer_id: customer_id,
                                status: status,
                                action: 'change_status'
                              },
                              success: function (response) {
                                if (response == 'success') {
                                  if (status == 1) {
                                    $('#status-alert' + no).removeClass().addClass('alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0').text('Inactive');
                                    $(".changeStatus[data-no='" + no + "']").data('status', "0");
                                    $('.product' + no).addClass('emphasize-strike'); // Add emphasize-strike class
                                    $('#action-button-' + no).html('<a href="javascript:void(0)" class="py-1 text-dark hideCustomer" data-id="' + customer_id + '" data-row="' + no + '"><i class="fa fa-trash text-light"></i></a>');
                                    $('#toggleActive').trigger('change');
                                  } else {
                                    $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                                    $(".changeStatus[data-no='" + no + "']").data('status', "1");
                                    $('.product' + no).removeClass('emphasize-strike'); // Remove emphasize-strike class
                                    $('#action-button-' + no).html('<a href="javascript:void(0)" data-id=' + customer_id + ' data-type="e" class="py-1"><i class="fa fa-pencil text-light"></i></a>');
                                    $('#toggleActive').trigger('change');
                                  }
                                } else {
                                  alert('Failed to change status.');
                                }
                              },
                              error: function (jqXHR, textStatus, errorThrown) {
                                alert('Error: ' + textStatus + ' - ' + errorThrown);
                              }
                            });
                          });

                          $(document).on('click', '.hideCustomer', function (event) {
                            event.preventDefault();
                            var customer_id = $(this).data('id');
                            var rowId = $(this).data('row');
                            $.ajax({
                              url: 'pages/customer_ajax.php',
                              type: 'POST',
                              data: {
                                customer_id: customer_id,
                                action: 'hide_customer'
                              },
                              success: function (response) {
                                if (response == 'success') {
                                  $('#product-row-' + rowId).remove(); // Remove the row from the DOM
                                } else {
                                  alert('Failed to hide customer.');
                                }
                              },
                              error: function (jqXHR, textStatus, errorThrown) {
                                alert('Error: ' + textStatus + ' - ' + errorThrown);
                              }
                            });
                          });
                        });
                      </script>

                    </table>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="map1Modal" tabindex="-1" role="dialog" aria-labelledby="mapsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapsModalLabel">Search Address</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="mapForm" class="form-horizontal">
              <div class="modal-body">
                  <div class="mb-2">
                      <input id="searchBox1" class="form-control" placeholder="<?= $addressDetails ?>" list="address1-list" autocomplete="off">
                      <datalist id="address1-list"></datalist>
                  </div>
                  <div id="map1" class="map-container" style="height: 60vh; width: 100%;"></div>
              </div>
              <div class="modal-footer">
                  <div class="form-actions">
                      <div class="card-body">
                          <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Cancel</button>
                      </div>
                  </div>
              </div>
            </form>
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

<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="customerModalLabel"><?= $addHeaderTxt ?> Customer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="card">
          <div id="form_section" class="card-body">
            <form id="lineForm" class="form-horizontal">
              <?php
                $customer_id = $_SESSION['active_customer_id'] ?? '';
                $query = "SELECT * FROM customer WHERE customer_id = '$customer_id'";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_array($result)) {
                  $customer_id = $row['customer_id'];
                  $customer_first_name = $row['customer_first_name'];
                  $customer_last_name = $row['customer_last_name'];
                  $customer_business_name = $row['customer_business_name'];
                  $old_customer_type_id = $row['customer_type_id'];
                  $contact_email = $row['contact_email'];
                  $contact_phone = $row['contact_phone'];
                  $primary_contact = $row['primary_contact'];
                  $contact_fax = $row['contact_fax'];
                  $address = $row['address'];
                  $city = $row['city'];
                  $state = $row['state'];
                  $zip = $row['zip'];
                  $secondary_contact_name = $row['secondary_contact_name'];
                  $secondary_contact_phone = $row['secondary_contact_phone'];
                  $ap_contact_name = $row['ap_contact_name'];
                  $ap_contact_email = $row['ap_contact_email'];
                  $ap_contact_phone = $row['ap_contact_phone'];
                  $tax_status = $row['tax_status'];
                  $tax_exempt_number = $row['tax_exempt_number'];
                  $customer_notes = $row['customer_notes'];
                  $call_status = $row['call_status'];
                  $credit_limit = $row['credit_limit'] ?? 0;
                  $customer_pricing = $row['customer_pricing'] ?? 0;
                  $lat = !empty($row['lat']) ? $row['lat'] : 0;
                  $lng = !empty($row['lng']) ? $row['lng'] : 0;

                  $addressDetails = implode(', ', [
                    $address ?? '',
                    $city ?? '',
                    $state ?? '',
                    $zip ?? ''
                  ]);
                  $loyalty = $row['loyalty'];
                }
                $saveBtnTxt = "Update";
                $addHeaderTxt = "Update";
              
              ?>
              <div class="row pt-3">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" id="customer_first_name" name="customer_first_name" class="form-control"
                      value="<?= $customer_first_name ?>" />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" id="customer_last_name" name="customer_last_name" class="form-control"
                      value="<?= $customer_last_name ?>" />
                  </div>
                </div>
              </div>

              <div class="row pt-3">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Customer Email</label>
                    <input type="text" id="contact_email" name="contact_email" class="form-control"
                      value="<?= $contact_email ?>" />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Customer Phone</label>
                    <input type="text" id="contact_phone" name="contact_phone" class="form-control"
                      value="<?= $contact_phone ?>" />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Customer Fax</label>
                    <input type="text" id="contact_fax" name="contact_fax" class="form-control"
                      value="<?= $contact_fax ?>" />
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Primary Contact</label>
                    <div class="mb-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="primary_contact" id="contact_email_radio" value="email" <?= ($primary_contact != '2' ? 'checked' : '') ?>>
                            <label class="form-check-label" for="contact_email_radio">Email</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="primary_contact" id="contact_phone_radio" value="phone" <?= ($primary_contact == '2' ? 'checked' : '') ?>>
                            <label class="form-check-label" for="contact_phone_radio">Phone</label>
                        </div>
                    </div>
                </div>
              </div>

              <div class="row pt-3">
                <div class="col-md-12">
                  <div class="mb-3">
                    <label class="form-label">Customer Business Name</label>
                    <input type="text" id="customer_business_name" name="customer_business_name" class="form-control"
                      value="<?= $customer_business_name ?>" />
                  </div>
                </div>
              </div>

              <div class="row pt-3">
                <div class="col-md-12">
                <label class="form-label">Address</label>
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        
                        <div class="d-flex w-100">
                            <input type="text" id="address" name="address" class="form-control" value="<?= $address ?>" list="address-data-list"/>
                            <datalist id="address-data-list"></datalist>
                            <button type="button" class="btn btn-primary py-1 ms-2 toggleElements" id="showMapsBtn" style="border-radius: 10%;" data-bs-toggle="modal" data-bs-target="#map1Modal">Change</button>
                        </div>
                    </div>
                </div>
              </div>

              <div class="row pt-3">
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">City</label>
                    <input type="text" id="city" name="city" class="form-control" value="<?= $city ?>" />
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">State</label>
                    <input type="text" id="state" name="state" class="form-control" value="<?= $state ?>" />
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">Zip</label>
                    <input type="text" id="zip" name="zip" class="form-control" value="<?= $zip ?>" />
                  </div>
                </div>
              </div>

              <input type="hidden" id="lat" name="lat" class="form-control" value="<?= $lat ?>" />
              <input type="hidden" id="lng" name="lng" class="form-control" value="<?= $lng ?>" />

              <div class="row pt-3">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Secondary Contact Name</label>
                    <input type="text" id="secondary_contact_name" name="secondary_contact_name" class="form-control"
                      value="<?= $secondary_contact_name ?>" />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Secondary Contact Phone</label>
                    <input type="text" id="secondary_contact_phone" name="secondary_contact_phone" class="form-control"
                      value="<?= $secondary_contact_phone ?>" />
                  </div>
                </div>
              </div>

              <div class="row pt-3">
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">AP Contact Name</label>
                    <input type="text" id="ap_contact_name" name="ap_contact_name" class="form-control"
                      value="<?= $ap_contact_name ?>" />
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">AP Contact Email</label>
                    <input type="text" id="ap_contact_email" name="ap_contact_email" class="form-control"
                      value="<?= $ap_contact_email ?>" />
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">AP Contact Phone</label>
                    <input type="text" id="ap_contact_phone" name="ap_contact_phone" class="form-control"
                      value="<?= $ap_contact_phone ?>" />
                  </div>
                </div>
              </div>

              <!-- LastOrderDate -->
              <!-- LastQuoteDate -->

              <div class="row pt-3">
                <div class="col-md-6 opt_field_update">
                  <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label">Tax Status</label>
                        <a href="?page=customer_tax" target="_blank" class="text-decoration-none toggleElements">Edit</a>
                    </div>
                    <select id="tax_status" class="form-select form-control" name="tax_status">
                      <option value="">Select Tax Status...</option>
                      <?php
                      $query_tax_status = "SELECT * FROM customer_tax";
                      $result_tax_status = mysqli_query($conn, $query_tax_status);
                      while ($row_tax_status = mysqli_fetch_array($result_tax_status)) {
                        $selected = ($tax_status == $row_tax_status['taxid']) ? 'selected' : '';
                        ?>
                        <option value="<?= $row_tax_status['taxid'] ?>" <?= $selected ?>>
                          (<?= $row_tax_status['percentage'] ?>%) <?= $row_tax_status['tax_status_desc'] ?></option>
                      <?php
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Tax Exempt Number</label>
                    <input type="text" id="tax_exempt_number" name="tax_exempt_number" class="form-control"
                      value="<?= $tax_exempt_number ?>" />
                  </div>
                </div>

              </div>

              <div class="row pt-3">
                <div class="col-md-6 d-none">
                  <?php
                  // Fetch all customer types
                  $query = "SELECT * FROM customer_types";
                  $result = mysqli_query($conn, $query);

                  // Fetch the name for the old customer type ID
                  $default_customer_type_name = '';
                  if ($old_customer_type_id > 0) {
                    $default_query = "SELECT customer_type_name FROM customer_types WHERE customer_type_id = $old_customer_type_id";
                    $default_result = mysqli_query($conn, $default_query);
                    if ($default_row = mysqli_fetch_assoc($default_result)) {
                      $default_customer_type_name = htmlspecialchars($default_row['customer_type_name']);
                    }
                  }
                  ?>
                  <div class="mb-3">
                    <label class="form-label">Customer Type</label>
                    <select class="form-select form-control" id="customer_type" name="customer_type">
                      <option value="">
                        <?php echo $default_customer_type_name ? $default_customer_type_name : 'Choose...'; ?></option>
                      <?php
                      // Generate options for the dropdown
                      while ($row = mysqli_fetch_assoc($result)) {
                        $selected = ($old_customer_type_id == $row['customer_type_id']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($row['customer_type_id']) . '" ' . $selected . '>' . htmlspecialchars($row['customer_type_name']) . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>

                <?php if (!empty($customer_id)) { ?>
                  <div class="col-md-6">
                    <label for="loyalty">Loyalty</label>
                    <select name="loyalty" id="loyalty" class="form-select form-control">
                      <option value="0" <?php if ($loyalty == '0')
                        echo 'selected'; ?>>Off</option>
                      <option value="1" <?php if ($loyalty == '1')
                        echo 'selected'; ?>>On</option>
                    </select>
                  </div>
                <?php } ?>

                <div class="col-6 mb-3">
                  <label class="form-label">Credit Limit</label>
                  <input class="form-control" type="text" id="credit_limit" name="credit_limit" value="<?= $credit_limit ?>">
                </div>

                <div class="col-6">
                  <div class="d-flex justify-content-between align-items-center">
                      <label class="form-label">Customer Pricing</label>
                      <a href="?page=customer_pricing" target="_blank" class="text-decoration-none toggleElements">Edit</a>
                  </div>
                  <div class="mb-3" data-pricing="<?= $customer_pricing ?>">
                      <select id="customer_pricing" class="form-control" name="customer_pricing">
                          <option value="">Select One...</option>
                          <?php
                          $query_pricing = "SELECT * FROM customer_pricing WHERE hidden = '0' AND status = '1'";
                          $result_pricing = mysqli_query($conn, $query_pricing);            
                          while ($row_pricing = mysqli_fetch_array($result_pricing)) {
                              $selected = ($customer_pricing == $row_pricing['id']) ? 'selected' : '';
                          ?>
                              <option value="<?= $row_pricing['id'] ?>" <?= $selected ?>><?= $row_pricing['pricing_name'] ?></option>
                          <?php   
                          }
                          ?>
                      </select>
                  </div>
                </div>
              
              </div>

              <div class="mb-3">
                <label class="form-label">Customer Notes</label>
                <textarea class="form-control" id="customer_notes" name="customer_notes"
                  rows="5"><?= $customer_notes ?></textarea>
              </div>

              <div class="row mb-3">
                  <div class="col">
                    <label class="form-label">Customer Call Status</label>
                    <input type="checkbox" id="call_status" name="call_status" <?= $call_status ? 'checked' : '' ?>>
                  </div>
              </div>

              <div class="form-actions toggleElements">
                <div class="card-body border-top ">
                  <input type="hidden" id="customer_id" name="customer_id" class="form-control"
                    value="<?= $customer_id ?>" />
                  <div class="row">

                    <div class="col-6 text-start">

                    </div>
                    <div class="col-6 text-end">
                      <button type="submit" class="btn btn-primary"
                        style="border-radius: 10%;"><?= $saveBtnTxt ?></button>
                    </div>
                  </div>

                </div>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
      <div class="modal-content">
          <div class="modal-header d-flex align-items-center">
              <h4 class="modal-title" id="myLargeModalLabel">
                  Upload <?= $page_title ?>
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div class="card">
                  <div class="card-body">
                      <form id="upload_excel_form" action="#" method="post" enctype="multipart/form-data">
                          <div class="mb-3">
                              <label for="excel_file" class="form-label fw-semibold">Select Excel File</label>
                              <input type="file" class="form-control" name="excel_file" accept=".xls,.xlsx" required>
                          </div>
                          <div class="text-center">
                              <button type="submit" class="btn btn-primary">Upload & Read</button>
                          </div>
                      </form>
                  </div>
              </div>
              <div class="card mb-0 mt-2">
                  <div class="card-body d-flex justify-content-center align-items-center">
                      <button type="button" id="readUploadBtn" class="btn btn-primary fw-semibold">
                          <i class="fas fa-eye me-2"></i> View Uploaded File
                      </button>
                  </div>
              </div>    
          </div>
      </div>
  </div>
</div>

<div class="modal fade" id="readUploadModal" tabindex="-1" aria-labelledby="readUploadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
          <div class="modal-header d-flex align-items-center">
              <h4 class="modal-title" id="myLargeModalLabel">
                  Uploaded Excel <?= $page_title ?>
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div id="uploaded_excel" class="modal-body"></div>
      </div>
  </div>
</div>

<div class="modal fade" id="downloadClassModal" tabindex="-1" aria-labelledby="downloadClassModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
      <div class="modal-content">
          <div class="modal-header d-flex align-items-center">
              <h4 class="modal-title" id="myLargeModalLabel">
                  Download Classification
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <form id="download_class_form" class="form-horizontal">
                  <label for="select-category" class="form-label fw-semibold">Select Classification</label>
                  <div class="mb-3">
                      <select class="form-select select2" id="select-download-class" name="category">
                          <option value="">All Classifications</option>
                          <optgroup label="Classifications">
                              <option value="tax_status">Tax Status</option>
                              <option value="customer_pricing">Customer Pricing</option>
                          </optgroup>
                      </select>
                  </div>

                  <div class="d-grid">
                      <button type="submit" class="btn btn-primary fw-semibold">
                          <i class="fas fa-download me-2"></i> Download Classification
                      </button>
                  </div>
              </form>
          </div>
      </div>
  </div>
</div>

<div class="modal fade" id="downloadModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
      <div class="modal-content">
          <div class="modal-header d-flex align-items-center">
              <h4 class="modal-title" id="myLargeModalLabel">
                  Download <?= $page_title ?>
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <form id="download_excel_form" class="form-horizontal">
                  <label for="select-category" class="form-label fw-semibold">Select Supplier</label>
                  <div class="mb-3">
                      <select class="form-select select2" id="select-download-category" name="category">
                          <option value="">All Suppliers</option>
                          <optgroup label="Suppliers">
                              <?php
                              $query_supplier = "SELECT * FROM supplier WHERE status = 1 ORDER BY `supplier_name` ASC";
                              $result_supplier = mysqli_query($conn, $query_supplier);            
                              while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                                  $selected = (!empty($supplierid) && $supplierid == $row_supplier['supplier_id']) ? 'selected' : '';
                                  if(!empty($_REQUEST['supplier_id'])){
                                    $selected = (!empty($supplier_id) && $supplier_id == $row_supplier['supplier_id']) ? 'selected' : '';
                                  }
                              ?>
                                  <option value="<?= $row_supplier['supplier_id'] ?>" <?= $selected ?>><?= $row_supplier['supplier_name'] ?></option>
                              <?php   
                              }
                              ?>
                          </optgroup>
                      </select>
                  </div>

                  <div class="d-grid">
                      <button type="submit" class="btn btn-primary fw-semibold">
                          <i class="fas fa-download me-2"></i> Download Excel
                      </button>
                  </div>
              </form>
          </div>
      </div>
  </div>
</div>

<script>
  let map1;
  let marker1;
  let lat1 = <?= $lat ?>, lng1 = <?= $lng ?>;

  $('#searchBox1').on('input', function() {
      updateSuggestions('#searchBox1', '#address1-list');
  });

  $('#address').on('input', function() {
      updateSuggestions('#address', '#address-data-list');
  });

  function updateSuggestions(inputId, listId) {
      var query = $(inputId).val();
      if (query.length >= 2) {
          $.ajax({
              url: `https://nominatim.openstreetmap.org/search`,
              data: {
                  q: query,
                  format: 'json',
                  addressdetails: 1,
                  limit: 5
              },
              dataType: 'json',
              success: function(data) {
                  var datalist = $(listId);
                  datalist.empty();
                  data.forEach(function(item) {
                      console.log(item)
                      var option = $('<option>')
                          .attr('value', item.display_name)
                          .data('lat', item.lat)
                          .data('lon', item.lon);
                      datalist.append(option);
                  });
              }
          });
      }
  }

  function getPlaceName(lat, lng, inputId) {
      const url = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json&addressdetails=1`;

      $.ajax({
          url: url,
          dataType: 'json',
          success: function(data) {
              if (data && data.display_name) {
                  $(inputId).val(data.display_name);

                  let address = data.address;
                  $('#address').val(
                      address.road || 
                      address.neighbourhood || 
                      address.suburb || 
                      ''
                  );
                  $('#city').val(
                      address.city || 
                      address.town || 
                      address.village || 
                      ''
                  );
                  $('#state').val(
                      address.state || 
                      address.province || 
                      address.region || 
                      address.county || 
                      ''
                  );
                  $('#zip').val(address.postcode || '');

                  $('#lat').val(lat);
                  $('#lng').val(lng);

              } else {
                  console.error("Address not found for these coordinates.");
                  $(inputId).val("Address not found");
              }
          },
          error: function() {
              console.error("Error retrieving address from Nominatim.");
              $(inputId).val("Error retrieving address");
          }
      });
  }

  $('#searchBox1').on('change', function() {
      let selectedOption = $('#address1-list option[value="' + $(this).val() + '"]');
      lat1 = parseFloat(selectedOption.data('lat'));
      lng1 = parseFloat(selectedOption.data('lon'));
      
      updateMarker(map1, marker1, lat1, lng1, "Starting Point");
      getPlaceName(lat1, lng1, '#searchBox1');
  });

  $('#address').on('change', function() {
      let selectedOption = $('#address-data-list option[value="' + $(this).val() + '"]');
      lat1 = parseFloat(selectedOption.data('lat'));
      lng1 = parseFloat(selectedOption.data('lon'));
      
      updateMarker(map1, marker1, lat1, lng1, "Starting Point");
      getPlaceName(lat1, lng1, '#address');
  });

  function updateMarker(map, marker, lat, lng, title) {
      if (!map) return;
      const position = new google.maps.LatLng(lat, lng);
      if (marker) {
          marker.setMap(null);
      }
      marker = new google.maps.Marker({
          position: position,
          map: map,
          title: title
      });
      map.setCenter(position);
      return marker;
  }

  function initMaps() {
      map1 = new google.maps.Map(document.getElementById("map1"), {
          center: { lat: <?= $lat ?>, lng: <?= $lng ?> },
          zoom: 13,
      });
      marker1 = updateMarker(map1, marker1, lat1, lng1, "Starting Point");
      google.maps.event.addListener(map1, 'click', function(event) {
          lat1 = event.latLng.lat();
          lng1 = event.latLng.lng();
          marker1 = updateMarker(map1, marker1, lat1, lng1, "Starting Point");
          getPlaceName(lat1, lng1, '#searchBox1');
      });
  }

  function loadGoogleMapsAPI() {
      const script = document.createElement('script');
      script.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDFpFbxFFK7-daOKoIk9y_GB4m512Tii8M&callback=initMaps&libraries=geometry,places';
      script.async = true;
      script.defer = true;
      document.head.appendChild(script);
  }

  window.onload = loadGoogleMapsAPI;

  function toggleFormEditable(formId, enable = true, hideBorders = false, hideControls = false) {
    const $form = $("#" + formId);
    if ($form.length === 0) return;

    $form.find("input, select, textarea").each(function () {
      const $element = $(this);
      if (enable) {
        $element.removeAttr("readonly").removeAttr("disabled");
        $element.css("border", hideBorders ? "none" : "");
        $element.css("background-color", "");
        if ($element.is("select")) {
          $element.removeClass("hide-dropdown");
        }
      } else {
        $element.attr("readonly", true).attr("disabled", true);
        $element.css("border", hideBorders ? "none" : "1px solid #ccc");
        $element.css("background-color", "#f8f9fa");
        if ($element.is("select")) {
          $element.addClass("hide-dropdown");
        }
      }
    });

    $(".toggleElements").each(function () {
      $(this).toggleClass("d-none", !enable);
    });
  }

  function loadCustomerModal(type = 'v'){
    $("#form_section").load(location.href + " #form_section", function () {
        if (type === "e") {
          toggleFormEditable("lineForm", true, false);
        } else {
          toggleFormEditable("lineForm", false, true);
        }
    });
  }

  $(document).ready(function () {
    document.title = "<?= $page_title ?>";

    var table = $('#display_customer').DataTable({
        pageLength: 100
    });

    $('#display_customer_filter').hide();

    $(".select2").each(function () {
        $(this).select2({
            width: '100%',
            dropdownParent: $(this).parent()
        });
    });

    $('#map1Modal').on('shown.bs.modal', function () {
        if (!map1) {
            initMaps();
        }
    });

    $('#map1Modal').on('hidden.bs.modal', function () {
        $('#customerModal').modal('show');
    });

    $('.addModalBtn').on('click', function () {
        const id = $(this).data('id');
        const type = $(this).data('type');

        $.ajax({
          url: 'pages/customer_ajax.php',
          type: 'POST',
          data: {
            id: id,
            action: 'change_act_cust_id'
          },
          success: function (response) {
            loadCustomerModal(type);
            $('#customerModal').modal('show');
          },
          error: function (jqXHR, textStatus, errorThrown) {
            alert('Error: ' + textStatus + ' - ' + errorThrown);
          }
        });
    });

    $('#customerTypeFilter').on('change', function () {
      var selectedValue = $(this).val();
      table.search(selectedValue).draw();
    });

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
      var status = $(table.row(dataIndex).node()).find('a .alert').text().trim();
      var isActive = $('#toggleActive').is(':checked');

      if (!isActive || status === 'Active') {
        return true;
      }
      return false;
    });

    $('#toggleActive').on('change', function () {
      table.draw();
    });

    $('#toggleActive').trigger('change');

    function getCookie(name) {
      var nameEQ = name + "=";
      var ca = document.cookie.split(';');
      for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
      }
      return null;
    }

    $(document).on('submit', '#lineForm', function (event) {
      event.preventDefault();

      var userid = getCookie('userid');

      var formData = new FormData(this);
      formData.append('action', 'add_update');
      formData.append('userid', userid);

      var appendResult = "";

      $.ajax({
        url: 'pages/customer_ajax.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {

          if (response === "Customer updated successfully.") {
            $('#responseHeader').text("Success");
            $('#responseMsg').text(response);
            $('#responseHeaderContainer').removeClass("bg-danger");
            $('#responseHeaderContainer').addClass("bg-success");
            $('#response-modal').modal("show");

            $('#response-modal').on('hide.bs.modal', function () {
              window.location.href = "?page=customer";
            });
          } else if (response === "New customer added successfully.") {
            $('#responseHeader').text("Success");
            $('#responseMsg').text(response);
            $('#responseHeaderContainer').removeClass("bg-danger");
            $('#responseHeaderContainer').addClass("bg-success");
            $('#response-modal').modal("show");

            $('#response-modal').on('hide.bs.modal', function () {
              location.reload();
            });
          } else {
            $('#responseHeader').text("Failed");
            $('#responseMsg').text(response);

            $('#responseHeaderContainer').removeClass("bg-success");
            $('#responseHeaderContainer').addClass("bg-danger");
            $('#response-modal').modal("show");
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          alert('Error: ' + textStatus + ' - ' + errorThrown);
        }
      });
    });

    $(document).on('click', '#uploadBtn', function(event) {
        $('#uploadModal').modal('show');
    });

    $(document).on('click', '#downloadBtn', function(event) {
        window.location.href = "pages/customer_ajax.php?action=download_excel";
    });

    $("#download_class_form").submit(function (e) {
        e.preventDefault();
        window.location.href = "pages/customer_ajax.php?action=download_classifications&class=" + encodeURIComponent($("#select-download-class").val());
    });

    $(document).on('click', '#downloadClassModalBtn', function(event) {
        $('#downloadClassModal').modal('show');
    });

    $(document).on('click', '#readUploadBtn', function(event) {
        $.ajax({
            url: 'pages/customer_ajax.php',
            type: 'POST',
            data: {
                action: "fetch_uploaded_modal"
            },
            success: function(response) {
                $('#uploaded_excel').html(response);
                $('#readUploadModal').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    $('#upload_excel_form').on('submit', function (e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'upload_excel');

        $.ajax({
            url: 'pages/customer_ajax.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                $('.modal').modal('hide');
                response = response.trim();
                if (response.trim() === "success") {
                    $('#responseHeader').text("Success");
                    $('#responseMsg').text("Data Uploaded successfully.");
                    $('#responseHeaderContainer').removeClass("bg-danger");
                    $('#responseHeaderContainer').addClass("bg-success");
                    $('#response-modal').modal("show");
                    $('#response-modal').on('hide.bs.modal', function () {
                        location.reload();
                    });
                } else {
                    $('#responseHeader').text("Failed");
                    $('#responseMsg').text(response);
                    $('#responseHeaderContainer').removeClass("bg-success");
                    $('#responseHeaderContainer').addClass("bg-danger");
                    $('#response-modal').modal("show");
                }  
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

    $(document).on('blur', '.table_data', function() {
        let newValue;
        let updatedData = {};
        
        if ($(this)[0].tagName.toLowerCase() === 'select') {
            const selectedValue = $(this).val();
            const selectedText = $(this).find('option:selected').text();
            newValue = selectedValue ? selectedValue : selectedText;
        } 
        else if ($(this).is('td')) {
            newValue = $(this).text();
        }
        
        const headerName = $(this).data('header-name');
        const id = $(this).data('id');

        updatedData = {
            action: 'update_test_data',
            id: id,
            header_name: headerName,
            new_value: newValue,
        };

        $.ajax({
            url: 'pages/customer_ajax.php',
            type: 'POST',
            data: updatedData,
            success: function(response) {
                console.log(response);
            },
            error: function(xhr, status, error) {
                console.log('Error: ' + error);
                alert('Error updating data');
            }
        });
    });

    $(document).on('click', '#saveTable', function(event) {
        if (confirm("Are you sure you want to save this Excel data to the product lines data?")) {
            var formData = new FormData();
            formData.append("action", "save_table");

            $.ajax({
                url: "pages/customer_ajax.php",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('.modal').modal('hide');
                    response = response.trim();
                    $('#responseHeader').text("Success");
                    $('#responseMsg').text(response);
                    $('#responseHeaderContainer').removeClass("bg-danger").addClass("bg-success");
                    $('#response-modal').modal("show");
                }
            });
        }
    });

    function filterTable() {
        var textSearch = $('#text-srh').val().toLowerCase();
        var isActive = $('#toggleActive').is(':checked');

        $.fn.dataTable.ext.search = [];

        if (textSearch) {
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                return $(table.row(dataIndex).node()).text().toLowerCase().includes(textSearch);
            });
        }

        if (isActive) {
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                return $(table.row(dataIndex).node()).find('a .alert').text().trim() === 'Active';
            });
        }

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var row = $(table.row(dataIndex).node());
            var match = true;

            $('.filter-selection').each(function() {
                var filterValue = $(this).val()?.toString() || '';
                var rowValue = row.data($(this).data('filter'))?.toString() || '';

                if (filterValue && filterValue !== '/' && rowValue !== filterValue) {
                    match = false;
                    return false; // Exit loop early if mismatch is found
                }
            });

            return match;
        });

        table.draw();
        updateSelectedTags();
    }

    $(document).on('change', '.filter-selection', filterTable);

    $(document).on('input', '#text-srh', filterTable);

    $(document).on('change', '#toggleActive', filterTable);

    function updateSelectedTags() {
        var displayDiv = $('#selected-tags');
        displayDiv.empty();

        $('.filter-selection').each(function() {
            var selectedOption = $(this).find('option:selected');
            var selectedText = selectedOption.text().trim();
            var filterName = $(this).data('filter-name'); // Custom attribute for display

            if ($(this).val()) {
                displayDiv.append(`
                    <div class="d-inline-block p-1 m-1 border rounded bg-light">
                        <span class="text-dark">${filterName}: ${selectedText}</span>
                        <button type="button" 
                            class="btn-close btn-sm ms-1 remove-tag" 
                            style="width: 0.75rem; height: 0.75rem;" 
                            aria-label="Close" 
                            data-select="#${$(this).attr('id')}">
                        </button>
                    </div>
                `);
            }
        });

        $('.remove-tag').on('click', function() {
            $($(this).data('select')).val('').trigger('change');
            $(this).parent().remove();
        });
    }

    $(document).on('click', '.reset_filters', function () {
        $('.filter-selection').each(function () {
            $(this).val(null).trigger('change.select2');
        });

        $('#text-srh').val('');

        filterTable();
    });


  });
</script>