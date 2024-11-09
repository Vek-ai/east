<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

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
$credit_limit = 0;
$lat = 0;
$lng = 0;

$customer_name = $customer_first_name . " " . $customer_last_name;

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if (!empty($_REQUEST['customer_id'])) {
  $product_line_id = $_REQUEST['customer_id'];
  $query = "SELECT * FROM customer WHERE customer_id = '$product_line_id'";
  $result = mysqli_query($conn, $query);
  while ($row = mysqli_fetch_array($result)) {
    $customer_id = $row['customer_id'];
    $customer_first_name = $row['customer_first_name'];
    $customer_last_name = $row['customer_last_name'];
    $customer_business_name = $row['customer_business_name'];
    $old_customer_type_id = $row['customer_type_id'];
    $contact_email = $row['contact_email'];
    $contact_phone = $row['contact_phone'];
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

  echo '<script>
              $(document).ready(function() {
                  $("#customerModal").modal("show");
              });
          </script>';
}

$message = "";
if (!empty($_REQUEST['result'])) {
  if ($_REQUEST['result'] == '1') {
    $message = "New customer added successfully.";
    $textColor = "text-success";
  } else if ($_REQUEST['result'] == '2') {
    $message = "Customer updated successfully.";
    $textColor = "text-success";
  } else if ($_REQUEST['result'] == '0') {
    $message = "Failed to Perform Operation";
    $textColor = "text-danger";
  }

}
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
    background-color: white !important;
    color: black !important;
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

<div class="row pt-3">
  <div class="col-md-12 text-end">
    <!-- Trigger Modal Button -->
    <button type="button" class="btn btn-primary me-10 mb-3 px-5" data-bs-toggle="modal"
      data-bs-target="#customerModal">
      Add
    </button>
  </div>
</div>

<!-- Modal Structure -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="customerModalLabel"><?= $addHeaderTxt ?> Customer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- The form -->
        <div class="card">
          <div class="card-body">
            <form id="lineForm" class="form-horizontal">
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
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">Customer Email</label>
                    <input type="text" id="contact_email" name="contact_email" class="form-control"
                      value="<?= $contact_email ?>" />
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">Customer Phone</label>
                    <input type="text" id="contact_phone" name="contact_phone" class="form-control"
                      value="<?= $contact_phone ?>" />
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">Customer Fax</label>
                    <input type="text" id="contact_fax" name="contact_fax" class="form-control"
                      value="<?= $contact_fax ?>" />
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

              <!-- CustomerTypeID -->

              <div class="row pt-3">
                <div class="col-md-12">
                <label class="form-label">Address</label>
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        
                        <div class="d-flex w-100">
                            <input type="text" id="address" name="address" class="form-control" value="<?= $address ?>" list="address-data-list"/>
                            <datalist id="address-data-list"></datalist>
                            <button type="button" class="btn btn-primary py-1 ms-2" id="showMapsBtn" style="border-radius: 10%;" data-bs-toggle="modal" data-bs-target="#map1Modal">Change</button>
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
                    <label class="form-label">Tax Status</label>
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
                <div class="col-md-6">
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

                <div class="col-6">
                  <label class="form-label">Credit Limit</label>
                  <input class="form-control" type="text" id="credit_limit" name="credit_limit" value="<?= $credit_limit ?>">
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

              <div class="form-actions">
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

<!-- Table -->
<div class="col-12">
  <div class="datatables">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title d-flex justify-content-between align-items-center">Customer List &nbsp;&nbsp;
          <?php if (!empty($_REQUEST['product_line_id'])) { ?>
            <a href="/?page=customer" class="btn btn-primary" style="border-radius: 10%;">Add New</a>
          <?php } ?>
          <div> <input type="checkbox" id="toggleActive" checked> Show Active Only</div>
        </h4>

        <div class="table-responsive">
          <table id="display_customer" class="table align-middle table-hover mb-0 text-md-nowrap">
            <thead>
              <!-- start row -->
              <tr>
                <th>Name</th>
                <th>Business Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Fax</th>
                <th>Address</th>
                <th>Loyalty</th>
                <th>
                  <div class="">
                    <!-- Add a Dropdown for Filtering -->
                    <?php
                    $query = "SELECT * FROM customer_types";
                    $result = mysqli_query($conn, $query);
                    ?>

                    <!-- Add a Dropdown for Filtering -->
                    <select id="customerTypeFilter" class="form-control select2">
                      <option value="">Category</option>
                      <?php
                      // Loop through the results and create dropdown options
                      while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <option value='<?= $row['customer_type_name'] ?>'><?= $row['customer_type_name'] ?></option>
                        <?php
                      }
                      ?>
                    </select>
                  </div>
                </th>
                <th>Status</th>

                <th>Action</th>
              </tr>
              <!-- end row -->
            </thead>
            <tbody>
              <?php
              $no = 1;
              // Fetch customer details along with the count of orders and loyalty information
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
                $order_count = $row_customer['order_count']; // Customer's order count
              
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
                <tr id="product-row-<?= $no ?>">
                  <td>
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
                  </td>
                  <td><?= $business_name ?></td>
                  <td><?= $email ?></td>
                  <td><?= $phone ?></td>
                  <td><?= $fax ?></td>
                  <td><?= $address ?></td>
                  <td><?= $loyalty ?></td>
                  <td><?= $customer_type_name ?></td>
                  <td><?= $status ?></td>
                  <td class="text-center fs-5" id="action-button-<?= $no ?>">
                    <?php if ($row_customer['status'] == '0') { ?>
                      <a href="#" class="py-1 text-dark hideCustomer" data-id="<?= $customer_id ?>" data-row="<?= $no ?>"
                        style='border-radius: 10%;' data-toggle="tooltip" data-placement="top" title="Archive"><i
                          class="fa fa-box-archive text-danger"></i></a>
                    <?php } else { ?>
                      <a href="?page=customer-dashboard&id=<?= $customer_id ?>" class="py-1 pe-1" style='border-radius: 10%;'
                        data-toggle="tooltip" data-placement="top" title="Dashboard"><i
                          class="fa fa-chart-bar text-light"></i>
                      </a>
                      <a href="?page=customer&customer_id=<?= $customer_id ?>" class="py-1 pe-1" style='border-radius: 10%;'
                        data-toggle="tooltip" data-placement="top" title="Edit"><i
                          class="fa fa-pencil text-warning"></i>
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
                          $('#action-button-' + no).html('<a href="#" class="btn btn-light py-1 text-dark hideCustomer" data-id="' + customer_id + '" data-row="' + no + '" style="border-radius: 10%;">Archive</a>');
                          $('#toggleActive').trigger('change');
                        } else {
                          $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                          $(".changeStatus[data-no='" + no + "']").data('status', "1");
                          $('.product' + no).removeClass('emphasize-strike'); // Remove emphasize-strike class
                          $('#action-button-' + no).html('<a href="/?page=customer&customer_id=' + customer_id + '" class="btn btn-primary py-1" style="border-radius: 10%;">Edit</a>');
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

  

  $(document).ready(function () {
    $('#map1Modal').on('shown.bs.modal', function () {
        if (!map1) {
            initMaps();
        }
    });

    $('#map1Modal').on('hidden.bs.modal', function () {
        $('#customerModal').modal('show');
    });

    var table = $('#display_customer').DataTable({
      columnDefs: [
        { orderable: false, targets: 6 }  // Disable sorting for the "Customer Type" column (index 6)
      ]
    });

    // Filter based on dropdown selection
    $('#customerTypeFilter').on('change', function () {
      var selectedValue = $(this).val();
      table.search(selectedValue).draw();  // Apply global search based on dropdown value
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

    $('.select2').select2();

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

    $('#lineForm').on('submit', function (event) {
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
  });
</script>