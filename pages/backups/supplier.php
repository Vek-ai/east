<?php
require 'includes/dbconn.php';

$supplier_name = "";
$supplier_website = ""; 
$contact_name = "";
$contact_email = "";
$contact_phone = "";
$contact_fax = "";
$secondary_name = "";
$secondary_phone = "";
$secondary_email = "";
$address = "";
$last_ordered_date = "";
$products = "";
$freight_rate = "";
$payment_terms = "";
$comment = "";
$status = "";

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if(!empty($_REQUEST['supplier_id'])){
  $supplier_id = $_REQUEST['supplier_id'];
  $query = "SELECT * FROM supplier WHERE supplier_id = '$supplier_id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
    $supplier_id = $row['supplier_id'];
    $supplier_name = $row['supplier_name'];
    $supplier_website = $row['supplier_website'];
    $contact_name = $row['contact_name'];
    $contact_email = $row['contact_email'];
    $contact_phone = $row['contact_phone'];
    $contact_fax = $row['contact_fax'];
    $secondary_name = $row['secondary_name'];
    $secondary_phone = $row['secondary_phone'];
    $secondary_email = $row['secondary_email'];
    $address = $row['address'];
    $last_ordered_date = $row['last_ordered_date'];
    $products = $row['products'];
    $freight_rate = $row['freight_rate'];
    $payment_terms = $row['payment_terms'];
    $comment = $row['comment'];
    $status = $row['status'];
  }
  $saveBtnTxt = "Update";
  $addHeaderTxt = "Update";
}


if (isset($_REQUEST['result'])) {
    switch ($_REQUEST['result']) {
        case '0':
            $message = "Failed to Perform Operation.";
            $textColor = "text-danger";
            break;
        case '1':
            $message = "New supplier added successfully.";
            $textColor = "text-success";
            break;
        case '2':
            $message = "Supplier updated successfully.";
            $textColor = "text-success";
            break;
        default:
            $message = "Unknown operation.";
            $textColor = "text-warning";
            break;
    }
}



?>
<style>
    /* Ensure that the text within the notes column wraps properly */
    td.notes {
        white-space: normal;
        word-wrap: break-word;
    }
</style>
<div class="col-12">
  <!-- start Default Form Elements -->
  <div class="card card-body">
    <div class="row">
      <div class="col-3">
        <h4 class="card-title"><?= $addHeaderTxt ?> Supplier</h4>
      </div>
      <div class="col-9">
        <h4 class="card-title <?= $textColor ?>"><?= $message ?></h4>
      </div>
    </div>
    

    <form id="supplierForm" class="form-horizontal">
        <div class="row pt-3">
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Supplier Name</label>
                <input type="text" id="supplier_name" name="supplier_name" class="form-control" value="<?= $supplier_name ?>" />
            </div>
            </div>
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Supplier Website</label>
                <input type="text" id="supplier_website" name="supplier_website" class="form-control" value="<?= $supplier_website ?>" />
            </div>
            </div>
        </div>

        <div class="row pt-3">
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Contact Name</label>
                <input type="text" id="contact_name" name="contact_name" class="form-control" value="<?= $contact_name ?>"  />
            </div>
            </div>
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Contact Email</label>
                <input type="text" id="contact_email" name="contact_email" class="form-control" value="<?= $contact_email ?>" />
            </div>
            </div>
        </div>

        <div class="row pt-3">
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Contact Phone</label>
                <input type="text" id="contact_phone" name="contact_phone" class="form-control" value="<?= $contact_phone ?>" />
            </div>
            </div>
            
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Contact Fax</label>
                <input type="text" id="contact_fax" name="contact_fax" class="form-control" value="<?= $contact_fax ?>" />
            </div>
            </div>
        </div>

        
        <div class="mb-3">
            <label class="form-label">Secondary Name</label>
            <input type="text" id="secondary_name" name="secondary_name" class="form-control" value="<?= $secondary_name ?>" />
        </div>

        <div class="row pt-3">
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Secondary Phone</label>
                <input type="text" id="secondary_phone" name="secondary_phone" class="form-control" value="<?= $secondary_phone ?>"  />
            </div>
            </div>
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Secondary Email</label>
                <input type="text" id="secondary_email" name="secondary_email" class="form-control" value="<?= $secondary_email ?>" />
            </div>
            </div>
        </div>

        
        
        <div class="mb-3">
            <label class="form-label">Address</label>
            <input type="text" id="address" name="address" class="form-control" value="<?= $address ?>" />
        </div>

        <div class="row pt-3">
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Last Ordered Date</label>
                <input type="date" id="last_ordered_date" name="last_ordered_date" class="form-control" value="<?= $last_ordered_date ?>" />
            </div>
            </div>
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Products</label>
                <input type="text" id="products" name="products" class="form-control" value="<?= $products ?>" />
            </div>
            </div>
        </div>

        <div class="row pt-3">
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">FreightRate</label>
                <input type="text" id="freight_rate" name="freight_rate" class="form-control" value="<?= $freight_rate ?>" />
            </div>
            </div>
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">PaymentTerms</label>
                <input type="text" id="payment_terms" name="payment_terms" class="form-control" value="<?= $payment_terms ?>" />
            </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Comment</label>
            <textarea class="form-control" id="comment" name="comment" rows="5"><?= $comment ?></textarea>
        </div>

      <div class="form-actions">
        <div class="card-body border-top ">
          <input type="hidden" id="supplier_id" name="supplier_id" class="form-control"  value="<?= $supplier_id ?>"/>
          <div class="row">
            
            <div class="col-6 text-start">
            
            </div>
            <div class="col-6 text-end">
              <button type="submit" class="btn btn-primary" style="border-radius: 10%;"><?= $saveBtnTxt ?></button>
            </div>
          </div>
          
        </div>
      </div>

    </form>
  </div>
  <!-- end Default Form Elements -->
</div>
<div class="col-12">
  <div >
    <div class="card">
      <div class="card-body">
          <h4 class="card-title">Supplier List  &nbsp;&nbsp; <?php if(!empty($_REQUEST['supplier_id'])){ ?>
            <a href="/?page=product_supplier" class="btn btn-primary" style="border-radius: 10%;">Add New</a>
            <?php } ?>
          </h4>
        
        <div class="table-responsive">
          <table id="display_supplier" class="table table-striped table-bordered text-nowrap align-middle">
            <thead>
                <tr>
                    <th>Supplier Name</th>
                    <th>Supplier Website</th>
                    <th>Contact Name</th>
                    <th>Contact Email</th>
                    <th>Contact Phone</th>
                    <th>Contact Fax</th>
                    <th>Secondary Name</th>
                    <th>Secondary Phone</th>
                    <th>Secondary Email</th>
                    <th>Address</th>
                    <th>Last Ordered Date</th>
                    <th>Products</th>
                    <th>Freight Rate</th>
                    <th>Payment Terms</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
              <?php
                $no = 1;
                $query_supplier = "SELECT * FROM supplier";
                $result_supplier = mysqli_query($conn, $query_supplier);            
                while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                    $supplier_id = $row_supplier['supplier_id'];
                    $supplier_name = $row_supplier['supplier_name'];
                    $supplier_website = $row_supplier['supplier_website'];
                    $contact_name = $row_supplier['contact_name'];
                    $contact_email = $row_supplier['contact_email'];
                    $contact_phone = $row_supplier['contact_phone'];
                    $contact_fax = $row_supplier['contact_fax'];
                    $secondary_name = $row_supplier['secondary_name'];
                    $secondary_phone = $row_supplier['secondary_phone'];
                    $secondary_email = $row_supplier['secondary_email'];
                    $address = $row_supplier['address'];
                    $last_ordered_date = $row_supplier['last_ordered_date'];
                    $products = $row_supplier['products'];
                    $freight_rate = $row_supplier['freight_rate'];
                    $payment_terms = $row_supplier['payment_terms'];
                    $comment = $row_supplier['comment'];
                    $status = $row_supplier['status'];
                    if ($row_supplier['status'] == '0') {
                        $status = "<a href='#' class='changeStatus$no' data-id='$supplier_id' data-status='$status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                    } else {
                        $status = "<a href='#' class='changeStatus$no' data-id='$supplier_id' data-status='$status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                    }
                
                ?>
                <tr>
                <td><?= $supplier_name ?></td>
                <td><?= $supplier_website ?></td>
                <td><?= $contact_name ?></td>
                <td><?= $contact_email ?></td>
                <td><?= $contact_phone ?></td>
                <td><?= $contact_fax ?></td>
                <td><?= $secondary_name ?></td>
                <td><?= $secondary_phone ?></td>
                <td><?= $secondary_email ?></td>
                <td><?= $address ?></td>
                <td><?= $last_ordered_date ?></td>
                <td><?= $products ?></td>
                <td><?= $freight_rate ?></td>
                <td><?= $payment_terms ?></td>
                <td><?= $comment ?></td>
                <td><?= $status ?></td>
                  <td class="text-center">
                    <a href="/?page=product_supplier&supplier_id=<?= $supplier_id ?>" class="btn btn-primary py-1" style='border-radius: 10%;'>Edit</a> 
                  </td>
                </tr>
                <?php 
                
                ?>
                <script>
                    $('.changeStatus<?= $no ?>').on('click', function(event) {
                        event.preventDefault(); 
                        var supplier_id = $(this).data('id');
                        var status = $(this).data('status');
                        $.ajax({
                            url: 'pages/supplier_ajax.php',
                            type: 'POST',
                            data: {
                                supplier_id: supplier_id,
                                status: status,
                                action: 'change_status'
                            },
                            success: function(response) {
                              if(response == 'success'){
                                if (status == 1) {
                                    $('#status-alert<?= $no ?>').removeClass().addClass('alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0').text('Inactive');
                                    $(".changeStatus<?= $no ?>").data('status', "0");
                                } else {
                                    $('#status-alert<?= $no ?>').removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                                    $(".changeStatus<?= $no ?>").data('status', "1");
                                }
                              }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                alert('Error: ' + textStatus + ' - ' + errorThrown);
                            }
                        });
                    });
                </script>
                <?php
                $no++;
                } 
                ?>
            </tbody>
            
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="response-modal" tabindex="-1" aria-labelledby="vertical-center-modal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header d-flex align-items-center">
        
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <h4 id="responseHeader"></h4>
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
    document.addEventListener("DOMContentLoaded", function() {

        new DataTable('#display_supplier', {
        responsive: {
            details: {
                type: 'column'
            }
        }
    });
    });
    $(document).ready(function() {
        $('#supplierForm').on('submit', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update');

            var appendResult = "";

            $.ajax({
                url: 'pages/supplier_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                if(response == "Supplier updated successfully.") {
                    appendResult = "2";
                }else if(response == "New supplier added successfully.") {
                    appendResult = "1";
                } else {
                    appendResult = "0";
                }

                var currentUrl = new URL(window.location.href);

                currentUrl.searchParams.set('result', appendResult);

                window.location.href = currentUrl.toString();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });
    });
</script>