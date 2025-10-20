<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require '../includes/dbconn.php';
require '../includes/functions.php';

$page_title = "Close Station";

$permission = $_SESSION['permission'];
?>
<style>
td.notes,  td.last-edit{
    white-space: normal;
    word-wrap: break-word;
}
.emphasize-strike {
    text-decoration: strike-through;
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
        <h4 class="font-weight-medium fs-14 mb-0"><?= $page_title ?></h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="?page=">Home
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page"><?= $page_title ?></li>
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
      <div class="col-12">
        <div id="selected-tags" class="mb-2"></div>
        <div class="datatables">
          <div class="table-responsive">
                <?php
                $today = date('Y-m-d');
                $station_id = intval($_SESSION['station']);

                if(empty($station_id)){
                ?>
                    <h3>Station is not set. Please <a href="logout.php">Login</a> Again</h3>
                <?php
                }else{

                    

                    $opening = 0;
                    $ob = mysqli_query($conn, "SELECT amount FROM cash_flow WHERE movement_type='opening_balance' AND DATE(date)='$today' AND station_id=$station_id LIMIT 1");
                    if ($ob && mysqli_num_rows($ob)) {
                        $row = mysqli_fetch_assoc($ob);
                        $opening = floatval($row['amount']);
                    }

                    $inflows = [];
                    $total_inflows = 0;
                    $ci = mysqli_query($conn, "SELECT cash_flow_type, SUM(amount) as total FROM cash_flow WHERE movement_type='cash_inflow' AND DATE(date)='$today' AND station_id=$station_id GROUP BY cash_flow_type");
                    while ($row = mysqli_fetch_assoc($ci)) {
                        $inflows[$row['cash_flow_type']] = floatval($row['total']);
                        $total_inflows += floatval($row['total']);
                    }

                    $outflows = [];
                    $total_outflows = 0;
                    $co = mysqli_query($conn, "SELECT cash_flow_type, SUM(amount) as total FROM cash_flow WHERE movement_type='cash_outflow' AND DATE(date)='$today' AND station_id=$station_id GROUP BY cash_flow_type");
                    while ($row = mysqli_fetch_assoc($co)) {
                        $outflows[$row['cash_flow_type']] = floatval($row['total']);
                        $total_outflows += floatval($row['total']);
                    }

                    $closing_balance = $opening + $total_inflows - $total_outflows;
                    ?>


                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Amount ($)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Opening Balance</td>
                                <td>Cash Float</td>
                                <td class="text-end">$<?= number_format($opening, 2) ?></td>
                            </tr>

                            <?php if (!empty($inflows)) { ?>
                                <tr><td colspan="3"><strong>Cash Inflows</strong></td></tr>
                                <?php foreach ($inflows as $desc => $amt) { ?>
                                    <tr>
                                        <td></td>
                                        <td><?= ucwords(str_replace('_',' ',$desc)) ?></td>
                                        <td class="text-end">$<?= number_format($amt, 2) ?></td>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td></td>
                                    <td><strong>Total Inflows</strong></td>
                                    <td class="text-end"><strong>$<?= number_format($total_inflows, 2) ?></strong></td>
                                </tr>
                            <?php } ?>

                            <?php if (!empty($outflows)) { ?>
                                <tr><td colspan="3"><strong>Cash Outflows</strong></td></tr>
                                <?php foreach ($outflows as $desc => $amt) { ?>
                                    <tr>
                                        <td></td>
                                        <td><?= ucwords(str_replace('_',' ',$desc)) ?></td>
                                        <td class="text-end">$<?= number_format($amt, 2) ?></td>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td></td>
                                    <td><strong>Total Outflows</strong></td>
                                    <td class="text-end"><strong>$<?= number_format($total_outflows, 2) ?></strong></td>
                                </tr>
                            <?php } ?>

                            <tr>
                                <td>Closing Balance</td>
                                <td>$<?= number_format($opening, 2) ?> + $<?= number_format($total_inflows, 2) ?> - $<?= number_format($total_outflows, 2) ?> =</td>
                                <td class="text-end"><strong>$<?= number_format($closing_balance, 2) ?></strong></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="text-end mt-3">
                        <button class="btn btn-danger">Confirm Closing</button>
                    </div>
                <?php
                }
                ?>
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
    document.title = "<?= $page_title ?>";
    
});
</script>