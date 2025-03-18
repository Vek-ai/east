<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$tax_status_desc = "";
$percentage = "";
$notes = "";

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if (!empty($_REQUEST['taxid'])) {
  $taxid = $_REQUEST['taxid'];
  $query = "SELECT * FROM customer_tax WHERE taxid = '$taxid'";
  $result = mysqli_query($conn, $query);
  while ($row = mysqli_fetch_array($result)) {
    $taxid = $row['taxid'];
    $tax_status_desc = $row['tax_status_desc'];
    $percentage = $row['percentage'];
  }
  $saveBtnTxt = "Update";
  $addHeaderTxt = "Update";
}

$message = "";
if (!empty($_REQUEST['result'])) {
  if ($_REQUEST['result'] == '1') {
    $message = "New customer tax added successfully.";
    $textColor = "text-success";
  } else if ($_REQUEST['result'] == '2') {
    $message = "Customer tax updated successfully.";
    $textColor = "text-success";
  } else if ($_REQUEST['result'] == '0') {
    $message = "Failed to Perform Operation";
    $textColor = "text-danger";
  }

}

?>
<style>
  /* Ensure that the text within the notes column wraps properly */
  td.notes,
  td.last-edit {
    white-space: normal;
    word-wrap: break-word;
  }

  .emphasize-strike {
    text-decoration: line-through;
    font-weight: bold;
    color: #9a841c;
    /* You can choose any color you like for emphasis */
  }

  .dataTables_filter input {
    width: 100%;
    /* Adjust the width as needed */
    height: 30px;
    /* Adjust the height as needed */
    font-size: 16px;
    /* Adjust the font size as needed */
    padding: 10px;
    /* Adjust the padding as needed */
    border-radius: 5px;
    /* Adjust the border-radius as needed */
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
</style>
<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
  <div class="card-body px-0">
    <div class="d-flex justify-content-between align-items-center">
      <div><br>
        <h4 class="font-weight-medium fs-14 mb-0">Customer Tax</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="">Customer
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Customer Tax</li>
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

<div class="col-12">
  <div class="datatables">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title d-flex justify-content-between align-items-center">Customer Tax List &nbsp;&nbsp;
          <button type="button" id="addModalBtn" class="btn btn-primary d-flex align-items-center" data-id="" data-type="add">
              <i class="ti ti-plus text-white me-1 fs-5"></i> Add Customer Tax
          </button>
        </h4>

        <div class="table-responsive">

          <table id="display_customer_tax" class="table table-striped table-bordered text-nowrap align-middle">
            <thead>
              <!-- start row -->
              <tr>
                <th>Tax Status Description</th>
                <th>Percentage</th>

                <th>Action</th>
              </tr>
              <!-- end row -->
            </thead>
            <tbody>
              <?php
              $no = 1;
              $query_tax_status_desc = "SELECT * FROM customer_tax";
              $result_tax_status_desc = mysqli_query($conn, $query_tax_status_desc);
              while ($row_tax_status_desc = mysqli_fetch_array($result_tax_status_desc)) {
                $taxid = $row_tax_status_desc['taxid'];
                $tax_status_desc = $row_tax_status_desc['tax_status_desc'];
                $percentage = $row_tax_status_desc['percentage'];
                ?>
                <tr id="customer-tax-row-<?= $no ?>">
                  <td><?= $tax_status_desc ?></td>
                  <td><?= $percentage ?></td>
                  <td class="text-center d-flex align-items-center justify-content-center" id="action-button-<?= $no ?>">
                    <a href="#" id="addModalBtn" class="d-flex align-items-center justify-content-center text-decoration-none" data-id="<?= $taxid ?>" data-type="edit">
                      <i class="ti ti-pencil fs-7"></i>
                    </a>
                    <a class="py-1 text-decoration-none deleteCustomerTax" data-taxid="<?= $taxid ?>" data-row="<?= $no ?>">
                      <i class="ti ti-trash text-danger fs-7"></i>
                    </a>

                  </td>
                </tr>
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
                    Add
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="taxForm" class="form-horizontal">
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

<script>
  $(document).ready(function () {
    document.title = "Customer Tax";

    var table = $('#display_customer_tax').DataTable({
        pageLength: 100
    });

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

    $('#taxForm').on('submit', function (event) {
      event.preventDefault();

      var userid = getCookie('userid');

      var formData = new FormData(this);
      formData.append('action', 'add_update');
      formData.append('userid', userid);

      var appendResult = "";

      $.ajax({
        url: 'pages/customer_tax_ajax.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
          $('.modal').modal("hide");
          if (response === "Customer tax updated successfully.") {
            $('#responseHeader').text("Success");
            $('#responseMsg').text(response);
            $('#responseHeaderContainer').removeClass("bg-danger");
            $('#responseHeaderContainer').addClass("bg-success");
            $('#response-modal').modal("show");

            $('#response-modal').on('hide.bs.modal', function () {
              window.location.href = "?page=customer_tax";
            });
          } else if (response === "New customer tax added successfully.") {
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

    $(document).on('click', '.deleteCustomerTax', function (event) {
        event.preventDefault();
        
        var confirmation = confirm("Are you sure you want to delete this customer tax?");
        if (confirmation) {
            var taxid = $(this).data('taxid');
            var row = $(this).data('row');
            
            $.ajax({
                url: 'pages/customer_tax_ajax.php',
                type: 'POST',
                data: {
                    taxid: taxid,
                    action: 'delete'
                },
                success: function (response) {
                    if (response.trim() === "Customer tax deleted successfully.") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text(response);
                        $('#responseHeaderContainer').removeClass("bg-danger").addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            window.location.href = "?page=customer_tax";
                        });
                    } else {
                        alert('Failed to delete customer tax.');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        }
    });


    $(document).on('click', '#addModalBtn', function(event) {
        event.preventDefault();
        var id = $(this).data('id') || '';
        var type = $(this).data('type') || '';

        if(type == 'edit'){
          $('#add-header').html('Update Customer Tax');
        }else{
          $('#add-header').html('Add Customer Tax');
        }

        $.ajax({
            url: 'pages/customer_tax_ajax.php',
            type: 'POST',
            data: {
              id : id,
              action: 'fetch_modal_content'
            },
            success: function (response) {
                $('#add-fields').html(response);
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
  });
</script>