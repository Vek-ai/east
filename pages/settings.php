<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$setting_name = "";
$value = "";

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if(!empty($_REQUEST['settingid'])){
  $settingid = $_REQUEST['settingid'];
  $query = "SELECT * FROM settings WHERE settingid = '$settingid'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
      $settingid = $row['settingid'];
      $setting_name = $row['setting_name'];
      $value = $row['value'];
  }
  $saveBtnTxt = "Update";
  $addHeaderTxt = "Update";
}

$message = "";
if(!empty($_REQUEST['result'])){
  if($_REQUEST['result'] == '1'){
    $message = "New setting added successfully.";
    $textColor = "text-success";
  }else if($_REQUEST['result'] == '2'){
    $message = "Setting updated successfully.";
    $textColor = "text-success";
  }else if($_REQUEST['result'] == '0'){
    $message = "Failed to Perform Operation";
    $textColor = "text-danger";
  }
}
$no = 1;

$addressSettings = getSettingAddressDetails();
$address = $addressSettings['address'];
$city = $addressSettings['city'];
$state = $addressSettings['state'];
$zip = $addressSettings['zip'];
$lat = !empty($addressSettings['lat']) ? $addressSettings['lat'] : 0;
$lng = !empty($addressSettings['lng']) ? $addressSettings['lng'] : 0;

$addressDetails = implode(', ', [
  $addressSettings['address'] ?? '',
  $addressSettings['city'] ?? '',
  $addressSettings['state'] ?? '',
  $addressSettings['zip'] ?? ''
]);

$points_details = getSetting('points');
$data = json_decode($points_details, true);
$points_order_total = isset($data['order_total']) ? $data['order_total'] : 0;
$points_gained = isset($data['points_gained']) ? $data['points_gained'] : 0;

$permission = $_SESSION['permission'];

$is_points_enabled = getSetting('is_points_enabled');
?>
<style>
        /* Ensure that the text within the notes column wraps properly */
        td.notes,  td.last-edit{
            white-space: normal;
            word-wrap: break-word;
        }
        .emphasize-strike {
            text-decoration: line-through;
            font-weight: bold;
            color: #9a841c; /* You can choose any color you like for emphasis */
        }
      .dataTables_filter input {
    width: 100%; /* Adjust the width as needed */
    height: 50px; /* Adjust the height as needed */
    font-size: 16px; /* Adjust the font size as needed */
    padding: 10px; /* Adjust the padding as needed */
    border-radius: 5px; /* Adjust the border-radius as needed */
}
.dataTables_filter {  width: 100%;}
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
                  <h4 class="font-weight-medium fs-14 mb-0">Settings</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="">Settings
                        </a>
                      </li>
                      <li class="breadcrumb-item text-muted" aria-current="page">Settings</li>
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
<?php                                                    
if ($permission === 'edit') {
?>
<div class="col-12">
  <!-- start Default Form Elements -->
  <div class="card card-body">
    <div class="row">
      <div class="col-3">
        <h4 class="card-title"><?= $addHeaderTxt ?> Settings</h4>
      </div>
      <div class="col-9">
        <h4 class="card-title <?= $textColor ?>"><?= $message ?></h4>
      </div>
    </div>
    

    <form id="lineForm" class="form-horizontal">
      <div class="row pt-3">
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Setting Name</label>
            <input type="text" id="setting_name" name="setting_name" class="form-control"  value="<?= $setting_name ?>"/>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Value</label>
            <input type="text" id="value" name="value" class="form-control" value="<?= $value ?>" />
          </div>
        </div>
      </div>

      <div class="form-actions">
        <div class="card-body border-top ">
          <input type="hidden" id="settingid" name="settingid" class="form-control"  value="<?= $settingid ?>"/>
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
<?php
}
?>

<div class="col-12">
  <div class="datatables">
    <div class="card">
      <div class="card-body">
          <h4 class="card-title d-flex justify-content-between align-items-center">Customer Tax List  &nbsp;&nbsp; <?php if(!empty($_REQUEST['settingid'])){ ?>
            <a href="/?page=settings" class="btn btn-primary" style="border-radius: 10%;">Add New</a>
             <?php } ?> <!-- <div> <input type="checkbox" id="toggleActive" checked> Show Active Only</div> -->
          </h4>
        
        <div class="table-responsive">
       
          <table id="display_settings" class="table table-striped table-bordered text-nowrap align-middle">
            <thead>
              <!-- start row -->
              <tr>
                <th>Setting Name</th>
                <th>Value</th>
              
                <th>Action</th>
              </tr>
              <!-- end row -->
            </thead>
            <tbody>
              <tr id="settings-row-<?= $no ?>">
                  <td>Address</td>
                  <td>
                    <?= $addressDetails ?>
                  </td>
                  <td class="text-center" id="action-button-<?= $no ?>">
                      <?php                                                    
                      if ($permission === 'edit') {
                      ?>
                      <a href="#" class="py-1" id="showMapsBtn" style="border-radius: 10%;" data-bs-toggle="modal" title="Edit" data-bs-target="#map1Modal"><i class="ti ti-pencil fs-7"></i></a>
                      <?php
                      }
                      ?>
                    </td>
                  
              </tr>
              <tr id="settings-row-<?= $no ?>">
                  <td>Order Points</td>
                  <td>
                    <div class="row">
                        <div class="col-6 text-center">
                            Total Order Required<br>
                            <span class="ms-3"><?= $points_order_total ?></span>
                        </div>
                        <div class="col-6 text-center">
                            Points Gained<br>
                            <span class="ms-3"><?= $points_gained ?></span>
                        </div>
                    </div>
                  </td>
                  <td class="text-center" id="action-button-<?= $no ?>">
                    <?php                                                    
                    if ($permission === 'edit') {
                    ?>
                      <a href="#" class="py-1" id="pointsBtn" style="border-radius: 10%;" data-bs-toggle="modal" title="Edit" data-bs-target="#orderPointsModal"><i class="ti ti-pencil fs-7"></i></a>
                    <?php
                    }
                    ?>
                    </td>
              </tr>
              <tr id="points-row">
                  <td>Points</td>
                  <td class="text-center">
                      <?php if ($is_points_enabled == 1) { ?>
                          <span class="badge bg-success">Enabled</span>
                      <?php } else { ?>
                          <span class="badge bg-danger">Disabled</span>
                      <?php } ?>
                  </td>
                  <td class="text-center">
                      <?php if ($permission === 'edit') { ?>
                          <?php if ($is_points_enabled == 1) { ?>
                              <a href="javascript:void(0)" 
                                id="togglePoints" 
                                data-status="disable" 
                                class="btn btn-sm btn-danger text-decoration-none">
                                Disable
                              </a>
                          <?php } else { ?>
                              <a href="javascript:void(0)" 
                                id="togglePoints" 
                                data-status="enable" 
                                class="btn btn-sm btn-success text-decoration-none">
                                Enable
                              </a>
                          <?php } ?>
                      <?php } ?>
                  </td>
              </tr>
              <?php
              $query_setting_name = "SELECT * FROM settings WHERE setting_name != 'address' AND setting_name != 'points'";
              $result_setting_name = mysqli_query($conn, $query_setting_name);            
              while ($row_setting = mysqli_fetch_array($result_setting_name)) {
                  $settingid = $row_setting['settingid'];
                  $setting_name = $row_setting['setting_name'];
                  $value = $row_setting['value'];
              ?>
              <tr id="settings-row-<?= $no ?>">
                  <td><?= $setting_name ?></td>
                  <td><?= $value ?></td>
                  <td class="text-center" id="action-button-<?= $no ?>">
                    <?php                                                    
                    if ($permission === 'edit') {
                    ?>
                      <a href="/?page=settings&settingid=<?= $settingid ?>" title="Edit" class="py-1" style='border-radius: 10%;'><i class="ti ti-pencil fs-7"></i></a>
                      <a class="py-1 text-light deleteSettings" title="Archive" data-settingid="<?= $settingid ?>" data-row="<?= $no ?>" style='border-radius: 10%;'><i class="text-danger ti ti-trash fs-7"></i></a>
                    <?php
                    }
                    ?>

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
                      <input type="hidden" name="address" id="address" value="<?= $address ?>"/> 
                      <input type="hidden" name="city" id="city" value="<?= $city ?>"/> 
                      <input type="hidden" name="state" id="state" value="<?= $state ?>"/> 
                      <input type="hidden" name="zip" id="zip" value="<?= $zip ?>"/> 
                      <input type="hidden" name="lat" id="lat" value="<?= $lat ?>"/> 
                      <input type="hidden" name="lng" id="lng" value="<?= $lng ?>"/> 
                  </div>
                  <div id="map1" class="map-container" style="height: 60vh; width: 100%;"></div>
              </div>
              <div class="modal-footer">
                  <div class="form-actions">
                      <div class="card-body">
                          <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                          <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Close</button>
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

<div class="modal fade" id="orderPointsModal" tabindex="-1" aria-labelledby="orderPointsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="orderPointsForm" method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="orderPointsModalLabel">Order Points</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body">
          <input type="hidden" name="setting_no" id="setting_no">

          <div class="mb-3">
            <label for="edit_total_order" class="form-label">Total Order Required</label>
            <input type="number" step="0.01" class="form-control" id="edit_total_order" value="<?= $points_order_total ?>" name="order_total" required>
          </div>

          <div class="mb-3">
            <label for="edit_points_gained" class="form-label">Points Gained</label>
            <input type="number" class="form-control" id="edit_points_gained" value="<?= $points_gained ?>" name="points_gained" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
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

  $('#map1Modal').on('shown.bs.modal', function () {
      if (!map1) {
          initMaps();
      }
  });

  $(document).ready(function() {
    var table = $('#display_settings').DataTable({
      order: []
    });
    
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var status = $(table.row(dataIndex).node()).find('a .alert').text().trim();
        var isActive = $('#toggleActive').is(':checked');

        if (!isActive || status === 'Active') {
            return true;
        }
        return false;
    });

    $('#toggleActive').on('change', function() {
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

    $('#mapForm').on('submit', function(event) {
        event.preventDefault();

        var userid = getCookie('userid');

        var formData = new FormData(this);
        formData.append('action', 'update_address');
        formData.append('userid', userid);

        $.ajax({
            url: 'pages/settings_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Corrected the condition
                if (response.trim() === "success_update_address") {
                    $('#responseHeader').text("Success");
                    $('#responseMsg').text("Setting updated successfully.");
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

    $('#lineForm').on('submit', function(event) {
        event.preventDefault(); 

        var userid = getCookie('userid');

        var formData = new FormData(this);
        formData.append('action', 'add_update');
        formData.append('userid', userid);

        var appendResult = "";

        $.ajax({
            url: 'pages/settings_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              
              if (response === "Setting updated successfully.") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text(response);
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");

                  $('#response-modal').on('hide.bs.modal', function () {
                    window.location.href = "?page=settings";
                  });
              } else if (response === "New setting added successfully.") {
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
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    $(document).on('click', '.deleteSettings', function(event) {
        event.preventDefault();
        var settingid = $(this).data('settingid');
        var row = $(this).data('row');
        $.ajax({
            url: 'pages/settings_ajax.php',
            type: 'POST',
            data: {
              settingid: settingid,
                action: 'delete'
            },
            success: function(response) {
                if (response == "Setting deleted successfully.") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text(response);
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");

                  $('#response-modal').on('hide.bs.modal', function () {
                    window.location.href = "?page=settings";
                  });
                } else {
                    alert('Failed to hide product line.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    $(document).on('submit', '#orderPointsForm', function (e) {
        e.preventDefault();

        const form = document.getElementById('orderPointsForm');
        const formData = new FormData(form);
        formData.append('action', 'update_order_points');

        $.ajax({
            url: 'pages/settings_ajax.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                $('.modal').modal("hide");
                try {
                    const result = JSON.parse(res);
                    if (result.success) {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Setting updated successfully.");
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
                        console.log(result.message);
                    }
                } catch (e) {
                    $('#responseHeader').text("Failed");
                    $('#responseMsg').text(response);
                    $('#responseHeaderContainer').removeClass("bg-success");
                    $('#responseHeaderContainer').addClass("bg-danger");
                    $('#response-modal').modal("show");
                    console.error(res);
                }
            },
            error: function () {
                alert('AJAX request failed.');
            }
        });
    });

    $(document).on("click", "#togglePoints", function () {
        let $btn = $(this);
        let status = $btn.data("status");

        $.ajax({
            url: "pages/settings_ajax.php",
            type: "POST",
            data: { 
                status: status,
                action: "toggle_points"
            },
            success: function (response) {
                if (status === "enable") {
                    $("#points-row td:nth-child(2)").html('<span class="badge bg-success">Enabled</span>');
                    $btn.text("Disable")
                        .removeClass("btn-success")
                        .addClass("btn-danger")
                        .data("status", "disable");
                } else {
                    $("#points-row td:nth-child(2)").html('<span class="badge bg-secondary">Disabled</span>');
                    $btn.text("Enable")
                        .removeClass("btn-danger")
                        .addClass("btn-success")
                        .data("status", "enable");
                }
            }
        });
    });


});
</script>