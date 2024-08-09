<?php
require 'includes/dbconn.php';
require 'includes/functions.php';
?>
<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
        <div class="card-body px-0">
          <div class="d-flex justify-content-between align-items-center">
            <div><br>
              <h4 class="font-weight-medium fs-14 mb-0">Contact Form</h4>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a class="text-muted text-decoration-none" href="">Home
                    </a>
                  </li>
                  <li class="breadcrumb-item text-muted" aria-current="page">Contact Form</li>
                </ol>
              </nav>
            </div>
          </div>
        </div>
      </div>
<div class="col-12">
  <!-- start Default Form Elements -->
  <div class="card card-body">
    <div class="row">
      <div class="col-3">
        <h4 class="card-title">Contact Form</h4>
      </div>
    </div>

    <form id="contactForm" class="form-horizontal">
      <div class="row pt-3">
        <div class="col-md-12">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" id="name" name="name" class="form-control"/>
          </div>
        </div>
      </div>

      <div class="row pt-3">
        <div class="col-md-12">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control"/>
          </div>
        </div>
      </div>

      <div class="row pt-3">
        <div class="col-md-12">
          <div class="mb-3">
            <label class="form-label">Subject</label>
            <input type="text" id="subject" name="subject" class="form-control"/>
          </div>
        </div>
      </div>

      
      <div class="row pt-3">
        <div class="col-md-12">
          <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea class="form-control" id="message" name="message" rows="5"><?= $notes ?></textarea>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <div class="card-body border-top ">
          <div class="row">
            
            <div class="col-6 text-start">
            
            </div>
            <div class="col-6 text-end">
              <button type="submit" class="btn btn-primary" style="border-radius: 10%;">Send Message</button>
            </div>
          </div>
          
        </div>
      </div>

    </form>
  </div>
  <!-- end Default Form Elements -->
</div>

<div class="col-12">
  <div class="datatables">
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
       
          <table id="display_contact_form" class="table table-striped table-bordered text-nowrap align-middle">
            <thead>
              <!-- start row -->
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Message</th>
              </tr>
              <!-- end row -->
            </thead>
            <tbody>
            <?php
            $no = 1;
            $query_contact_form = "SELECT * FROM contact_form";
            $result_contact_form = mysqli_query($conn, $query_contact_form);            
            while ($row_contact_form = mysqli_fetch_array($result_contact_form)) {
                $name = $row_contact_form['name'];
                $email = $row_contact_form['email'];
                $subject = $row_contact_form['subject'];
                $message = $row_contact_form['message'];
            ?>
            <tr>
                <td><?= $name ?></td>
                <td><?= $email ?></td>
                <td><?= $subject ?></td>
                <td><?= $message ?></td>
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

<script>
  $(document).ready(function() {
    var table = $('#display_contact_form').DataTable();

    $('#contactForm').on('submit', function(event) {
        event.preventDefault(); 

        var formData = new FormData(this);
        formData.append('action', 'add_update');

        $.ajax({
            url: 'pages/contact_form_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              
              if (response === "add-success") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text("Message sent successfully.");
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
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    
});
</script>