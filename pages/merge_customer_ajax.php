<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if (isset($_POST['merge'])) {
    $customer_original = floatval($_POST['customer_original']);
    $customer_merge = floatval($_POST['customer_merge']);

    $query="UPDATE customer 
            SET status = 3, 
                merge_from = '$customer_original', 
                merge_date = NOW() 
            WHERE customer_id = '$customer_merge';";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $query = "UPDATE orders SET customerid = '$customer_original', originalcustomerid = '$customer_original' WHERE customerid = '$customer_merge'";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            echo "Error updating orders: " . mysqli_error($conn);
            exit;
        }

        $query = "UPDATE estimates SET customerid = '$customer_original', originalcustomerid = '$customer_original' WHERE customerid = '$customer_merge'";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            echo "Error updating estimates: " . mysqli_error($conn);
            exit;
        }

        $query = "INSERT INTO customer_merge (customer_id, merge_from, merge_date) VALUES ('$customer_merge','$customer_original', NOW())";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            echo "Error updating estimates: " . mysqli_error($conn);
            exit;
        }
        
        echo "success";
    } else {
        echo "Database error: " . mysqli_error($conn);
    }
}

if (isset($_POST['fetch_data'])) {
    $customer_id = (int)($_POST['customer_id'] ?? 0);

    if ($customer_id > 0) {
        $query = "SELECT * FROM customer WHERE customer_id = '$customer_id' LIMIT 1";
        $result = mysqli_query($conn, $query);
        if ($row = mysqli_fetch_assoc($result)) {
            ?>
            <style>
                .detail-label {
                    font-weight: bold;
                }
                .detail-value {
                    margin-left: 10px;
                }
            </style>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Customer Details</h5>
                </div>
                <div class="card-body">
                    
                    <div class="mb-2">
                        <div class="detail-label">First Name:</div>
                        <div class="detail-value"><?= !empty($row['customer_first_name']) ? htmlspecialchars($row['customer_first_name']) : "-" ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">Last Name:</div>
                        <div class="detail-value"><?= !empty($row['customer_last_name']) ? htmlspecialchars($row['customer_last_name']) : "-" ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">Email:</div>
                        <div class="detail-value"><?= !empty($row['contact_email']) ? htmlspecialchars($row['contact_email']) : "-" ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">Phone:</div>
                        <div class="detail-value"><?= !empty($row['contact_phone']) ? htmlspecialchars($row['contact_phone']) : "-" ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">Fax:</div>
                        <div class="detail-value"><?= !empty($row['contact_fax']) ? htmlspecialchars($row['contact_fax']) : "-" ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">Primary Contact:</div>
                        <div class="detail-value"><?= ($row['primary_contact']=='2' ? 'Phone' : 'Email') ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">Business Name:</div>
                        <div class="detail-value"><?= !empty($row['customer_business_name']) ? htmlspecialchars($row['customer_business_name']) : "-" ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">Address:</div>
                        <div class="detail-value">
                            <?= (!empty($row['address']) ? htmlspecialchars($row['address']) : "-") ?>
                            <?= (!empty($row['city']) ? ", ".htmlspecialchars($row['city']) : "") ?>
                            <?= (!empty($row['state']) ? ", ".htmlspecialchars($row['state']) : "") ?>
                            <?= (!empty($row['zip']) ? " ".htmlspecialchars($row['zip']) : "") ?>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">Secondary Contact:</div>
                        <div class="detail-value"><?= !empty($row['secondary_contact_name']) ? htmlspecialchars($row['secondary_contact_name']) : "-" ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">Secondary Phone:</div>
                        <div class="detail-value"><?= !empty($row['secondary_contact_phone']) ? htmlspecialchars($row['secondary_contact_phone']) : "-" ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">AP Contact:</div>
                        <div class="detail-value"><?= !empty($row['ap_contact_name']) ? htmlspecialchars($row['ap_contact_name']) : "-" ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">AP Phone:</div>
                        <div class="detail-value"><?= !empty($row['ap_contact_phone']) ? htmlspecialchars($row['ap_contact_phone']) : "-" ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">AP Email:</div>
                        <div class="detail-value"><?= !empty($row['ap_contact_email']) ? htmlspecialchars($row['ap_contact_email']) : "-" ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">Tax Status:</div>
                        <div class="detail-value"><?= !empty($row['tax_status']) ? getCustomerTaxName($row['tax_status']) : "-" ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">Tax Exempt #:</div>
                        <div class="detail-value"><?= !empty($row['tax_exempt_number']) ? htmlspecialchars($row['tax_exempt_number']) : "-" ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">Notes:</div>
                        <div class="detail-value"><?= !empty($row['customer_notes']) ? nl2br(htmlspecialchars($row['customer_notes'])) : "-" ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">Loyalty:</div>
                        <div class="detail-value"><?= ($row['loyalty'] ? 'On' : 'Off') ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">Call Status:</div>
                        <div class="detail-value"><?= ($row['call_status'] ? 'Active' : 'Inactive') ?></div>
                    </div>

                    <div class="mb-2">
                        <div class="detail-label">Charge Net 30:</div>
                        <div class="detail-value"><?= !empty($row['charge_net_30']) ? htmlspecialchars($row['charge_net_30']) : "-" ?></div>
                    </div>
                </div>
            </div>
            <?php
        } else {
            echo "<div class='alert alert-warning'>Customer not found.</div>";
        }
    }
}


?>
