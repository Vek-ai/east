<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if (isset($_POST['search_orders'])) {
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);
?>
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
<?php
}

if (isset($_POST['search_estimates'])) {
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);
?>
    <div class="month-table">
        <div class="table-responsive mt-3">
            <table class="table align-middle  mb-0 no-wrap text-center">
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
                    <?php
                    $query = "SELECT * FROM estimates WHERE 1 = 1";

                    if (!empty($date_from) && !empty($date_to)) {
                        $date_to .= ' 23:59:59';
                        $query .= " AND (order_date >= '$date_from' AND order_date <= '$date_to')";
                    }
                    $query .= " ORDER BY order_date DESC";
                    if (empty($date_from) || empty($date_to)) {
                        $query .= " LIMIT 10";
                    }

                    $result = mysqli_query($conn, $query);
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $estimate_id = $row['estimateid'];
                            $total_changes = 0;
                            $query_est_changes = "SELECT count(*) as total_changes FROM estimate_changes WHERE estimate_id = '$estimate_id'";
                            $result_est_changes = mysqli_query($conn, $query_est_changes);
                            if ($result_est_changes && mysqli_num_rows($result_est_changes) > 0) {
                                $row_est_changes = mysqli_fetch_assoc($result_est_changes);
                                $total_changes = $row_est_changes['total_changes'];
                            }

                            $status_html = "";
                            if(intval($row['status']) == 1){
                                $status_html = '<span class="badge bg-primary text-light">Not Ordered</span>';
                            }else if(intval($row['estimateid']) == 2){
                                $status_html = '<span class="badge bg-success text-light">Ordered</span>';
                            }
                        ?>
                        <tr>
                            <td class="ps-0">
                            <div class="hstack gap-3">
                                <span class="round-48 rounded-circle overflow-hidden flex-shrink-0 hstack justify-content-center">
                                <img src="assets/images/profile/user-2.jpg" alt class="img-fluid">
                                </span>
                                <div>
                                <h5 class="mb-1"><?= $row['estimateid'] ?></h5>
                                </div>
                            </div>
                            </td>
                            <td>
                                <?= $status_html ?>
                            </td>
                            <td>
                                <p class="mb-0"><?= $total_changes ?></p>
                            </td>
                            <td class="text-end">
                                <p class="mb-0 fs-3">$<?= number_format($row['discounted_price'],2) ?></p>
                            </td>
                        </tr>
                    <?php
                        }
                    }else{
                    ?>
                        <tr>
                            <td colspan="4">No estimates found</td>
                        </tr>
                    <?php  
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<?php
}