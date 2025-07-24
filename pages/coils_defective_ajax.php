<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])){
   $action = $_REQUEST['action'];

    if ($action == 'fetch_coil_defective') {
        $query = "
            SELECT * FROM coil_defective
            ORDER BY tagged_date DESC
        ";

        $result = mysqli_query($conn, $query);
        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $color_details = getColorDetails($row['color_sold_as']);
            $grade = getGradeName($row['grade']);
            $remaining_feet = $row['remaining_feet'] ?? 0;

            $picture = !empty($row['main_image']) ? $row['main_image'] : "images/coils/product.jpg";
            $entry_no = $row['entry_no'];
            $supplier_name = getSupplierName($row['supplier']);

            $tagged_date = '';
            if (!empty($row['tagged_date']) && strtotime($row['tagged_date'])) {
                $tagged_date = date('F j, Y', strtotime($row['tagged_date']));
            }

            $tag_html = '';
            if ($row['tagged_defective'] == 1) {
                $tag_html = '<span class="badge" style="background-color: #28a745; color: #fff;">Defective + Replaced</span>';
            } elseif ($row['tagged_defective'] == 2) {
                $tag_html = '<span class="badge" style="background-color: #dc3545; color: #fff;">Defective Only</span>';
            }

            $actions = '<div class="action-btn d-flex justify-content-center flex-wrap gap-2 text-center">';

            $actions .= '<a href="#" role="button" class="view-notes-btn" data-id="' . $row['coil_defective_id'] . '" title="View Notes">
                <i class="text-primary fa fa-eye fs-6"></i>
            </a>';
            $actions .= '<a href="print_coil.php?id=' . $row['coil_id'] . '" role="button" class="print-coil-btn btn-show-pdf" data-id="' . $row['coil_defective_id'] . '" title="Print/Download">
                <i class="fa fa-print fs-6"></i>
            </a>';
            $actions .= '<a href="#" role="button" class="view-history-btn" data-id="' . $row['coil_defective_id'] . '" title="View Coil History">
                <iconify-icon class="fs-7" style="color: #00ffbfff;" icon="solar:history-outline"></iconify-icon>
            </a>';
            $actions .= '<a href="#" role="button" class="tag-approve-btn change_status" data-id="' . $row['coil_defective_id'] . '" data-action="approve" title="Approve Coil as Good">
                <iconify-icon class="fs-7" style="color: #90ee90;" icon="solar:like-bold"></iconify-icon>
            </a>';
            $actions .= '<a href="#" role="button" class="tag-review-btn change_status" data-id="' . $row['coil_defective_id'] . '" data-action="review" title="Tag Coil for EKM Review">
                <iconify-icon class="fs-7" style="color: #ffd700;" icon="solar:tag-outline"></iconify-icon>
            </a>';
            $actions .= '<a href="#" role="button" class="tag-quarantine-btn change_status" data-id="' . $row['coil_defective_id'] . '" data-action="quarantine" title="Quarantine Coil">
                <iconify-icon class="fs-7" style="color: #ffa500;" icon="solar:danger-triangle-outline"></iconify-icon>
            </a>';
            $actions .= '<a href="#" role="button" class="tag-return-btn change_status" data-id="' . $row['coil_defective_id'] . '" data-action="return" title="Flag Coil for Return to Supplier">
                <iconify-icon class="fs-7" style="color: #ff0000;" icon="solar:box-outline"></iconify-icon>
            </a>';
            $actions .= '<a href="#" role="button" class="tag-claim-btn" data-id="' . $row['coil_defective_id'] . '" data-action="claim" title="Submit Claim against Coil"> 
                <iconify-icon class="fs-7" style="color: #ffffff;" icon="solar:clipboard-text-outline"></iconify-icon>
            </a>';
            $actions .= '<a href="#" role="button" class="tag-transfer-btn change_status" data-id="' . $row['coil_defective_id'] . '" data-action="transfer" title="Add Coil to Inventory">
                <iconify-icon class="fs-7" style="color: #28a745;" icon="solar:verified-check-outline"></iconify-icon>
            </a>';

            $actions .= '</div>';


            $status = (int)$row['status'];
            $status_badge = '';

            if ($status === 0) {
                $status_badge = '<span class="badge py-2" style="background-color: #add8e6; color: #000; white-space: normal;">New Defective</span>';
            } elseif ($status === 1) {
                $status_badge = '<span class="badge py-2" style="background-color: #ffd700; color: #000; white-space: normal;">Under Review</span>';
            } elseif ($status === 2) {
                $status_badge = '<span class="badge py-2" style="background-color: #ffa500; color: #000; white-space: normal;">Quarantined<br>Coil</span>';
            } elseif ($status === 3) {
                $status_badge = '<span class="badge py-2" style="background-color: #ff0000; color: #fff; white-space: normal;">Return to Supplier</span>';
            } elseif ($status === 4) {
                $status_badge = '<span class="badge py-2" style="background-color: #28a745; color: #fff; white-space: normal;">Awaiting Approval</span>';
            } elseif ($status === 5) {
                $status_badge = '<span class="badge py-2" style="background-color: #ffffff; color: #000; white-space: normal;">Claim Submitted</span>';
            } else {
                $status_badge = '<span class="badge py-2 bg-secondary" style="white-space: normal;">Unknown</span>';
            }


            $data[] = [
                'row' => [
                    '<div class="d-flex align-items-center">
                        <img src="' . $picture . '" class="rounded-circle preview-image" alt="materialpro-img" width="56" height="56">
                        <div class="ms-3"><h6 class="fw-semibold mb-0 fs-4">' . $entry_no . '</h6></div>
                    </div>',
                    '<div class="d-inline-flex align-items-center gap-2">
                        <span class="rounded-circle d-block" style="background-color:' . $color_details['color_code'] . '; width: 30px; height: 30px;"></span>
                        ' . $color_details['color_name'] . '
                    </div>',
                    $grade,
                    $remaining_feet,
                    $status_badge,
                    $tagged_date,
                    $supplier_name,
                    $actions
                ],
                'color' => $color_details['color_name'],
                'grade' => $grade,
                'status' => $status,
                'supplier' => $supplier_name
            ];
        }

        echo json_encode(['data' => $data]);
    }
    
    if ($action == 'fetch_coil_notes') {
        $coil_defective_id = intval($_POST['coil_defective_id']);
        $notes = [];

        $query = "
            SELECT 
                cn.note_text, 
                DATE_FORMAT(cn.created_at, '%M %e, %Y %l:%i %p') AS created_at,
                CONCAT(s.staff_fname, ' ', s.staff_lname) AS staff_name
            FROM 
                coil_notes cn
            LEFT JOIN 
                staff s ON cn.created_by = s.staff_id
            WHERE 
                cn.coil_defective_id = $coil_defective_id
            ORDER BY 
                cn.created_at DESC
        ";
        $res = mysqli_query($conn, $query);

        while ($row = mysqli_fetch_assoc($res)) {
            $notes[] = $row;
        }

        echo json_encode($notes);
        exit;
    }

    if ($action == 'add_coil_note') {
        $coil_defective_id = intval($_POST['coil_defective_id']);
        $note_text = mysqli_real_escape_string($conn, $_POST['note_text']);
        $staff_id = $_SESSION['userid'] ?? null;

        $getCoilQuery = "SELECT coil_id FROM coil_defective WHERE coil_defective_id = $coil_defective_id LIMIT 1";
        $result = mysqli_query($conn, $getCoilQuery);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $coil_id = intval($row['coil_id']);

            $insert = "
                INSERT INTO coil_notes (coil_defective_id, coil_id, note_text, created_by)
                VALUES ($coil_defective_id, $coil_id, '$note_text', " . ($staff_id ?: "NULL") . ")
            ";

            echo mysqli_query($conn, $insert) ? 'success' : 'error';
        } else {
            echo 'invalid';
        }

        exit;
    }

    if ($action == 'update_coil_status') {
        $coil_defective_id = intval($_POST['coil_id'] ?? 0);
        $change_action = $_POST['change_action'] ?? '';
        $new_grade = mysqli_real_escape_string($conn, $_POST['new_grade'] ?? '');
        $note_text = mysqli_real_escape_string($conn, $_POST['note_text'] ?? '');
        $staff_id = $_SESSION['staff_id'] ?? null;

        if ($coil_defective_id <= 0 || empty($change_action)) {
            echo "Invalid request.";
            exit;
        }

        $check = mysqli_query($conn, "SELECT * FROM coil_defective WHERE coil_defective_id = $coil_defective_id");
        if (!$check || mysqli_num_rows($check) == 0) {
            echo "Coil not found in defective list.";
            exit;
        }

        $coil = mysqli_fetch_assoc($check);
        $coil_id = intval($coil['coil_id']);

        $status_map = [
            'review'     => 1,
            'quarantine' => 2,
            'return'     => 3,
            'transfer'   => 4,
            'approve'    => 4
        ];

        $status = $status_map[$change_action] ?? null;
        if (!isset($status)) {
            echo "Unknown action.";
            exit;
        }

        if (!empty($new_grade)) {
            mysqli_query($conn, "UPDATE coil_defective SET grade = '$new_grade' WHERE coil_defective_id = $coil_defective_id");
            mysqli_query($conn, "UPDATE coil_product SET grade = '$new_grade' WHERE coil_id = $coil_id");
        }

        if (!empty($note_text)) {
            $staff_id = $_SESSION['userid'];
            mysqli_query($conn, "
                INSERT INTO coil_notes (coil_defective_id, coil_id, note_text, created_by)
                VALUES ($coil_defective_id, $coil_id, '$note_text', '$staff_id')
            ");
        }

        if ($change_action === 'review') {
            $update_defective = "UPDATE coil_defective SET status = 1 WHERE coil_defective_id = $coil_defective_id";
            $update_product   = "UPDATE coil_product SET status = 2 WHERE coil_id = $coil_id";

            if (mysqli_query($conn, $update_defective) && mysqli_query($conn, $update_product)) {
                $actorId = $_SESSION['userid'] ?? 0;
                $actor_name = get_staff_name($actorId);
                $actionType = 'review_coil';
                $targetId = $coil_id;
                $targetType = 'Coil Review';
                $message = "Coil #$targetId tagged as Under Review by $actor_name";
                $url = '?page=';
                $recipientRole = 'work_order';

                createNotification($actorId, $actionType, $targetId, $targetType, $message, $recipientRole, $url);

                echo "success";
            } else {
                echo "Error updating: " . mysqli_error($conn);
            }
            exit;
        }

        if ($change_action === 'transfer') {
            $update_defective = "UPDATE coil_defective SET status = 4 WHERE coil_defective_id = $coil_defective_id";

            if (mysqli_query($conn, $update_defective)) {
                $cols = [
                    'entry_no', 'warehouse', 'color_family', 'color_abbreviation', 'paint_supplier',
                    'paint_code', 'stock_availability', 'multiplier_category', 'actual_color', 'color_close',
                    'coil_no', 'date', 'supplier', 'supplier_name', 'color_sold_as', 'color_sold_name',
                    'product_id', 'og_length', 'weight', 'thickness', 'width', 'grade', 'coating', 'tag_no',
                    'invoice_no', 'remaining_feet', 'last_inventory_count', 'coil_class', 'gauge', 'grade_no',
                    'year', 'month', 'extracting_price', 'price', 'avg_by_color', 'total', 'current_weight',
                    'lb_per_ft', 'contract_ppf', 'contract_ppcwg', 'invoice_price', 'round_width',
                    'hidden', 'main_image', 'supplier_tag'
                ];

                $columns = implode(", ", $cols) . ", tagged_defective, tagged_date, tagged_note, status";
                $values = [];

                foreach ($cols as $col) {
                    $val = $coil[$col] ?? null;
                    $values[] = is_null($val) ? "NULL" : "'" . mysqli_real_escape_string($conn, $val) . "'";
                }

                $values[] = 0;
                $values[] = "NULL";
                $values[] = "NULL";
                $values[] = 0;

                $values_str = implode(", ", $values);
                $insert_sql = "INSERT INTO coil_product ($columns) VALUES ($values_str)";

                if (mysqli_query($conn, $insert_sql)) {
                    echo "success";
                } else {
                    echo "Error inserting into coil_product: " . mysqli_error($conn);
                }
            } else {
                echo "Error updating status: " . mysqli_error($conn);
            }
            exit;
        }

        if ($change_action === 'approve') {
            $res = mysqli_query($conn, "SELECT coil_id FROM coil_defective WHERE coil_defective_id = $coil_defective_id AND status != 4");
            if ($res && mysqli_num_rows($res) > 0) {
                $row = mysqli_fetch_assoc($res);
                $coil_id = intval($row['coil_id']);

                $update_coil_sql = "UPDATE coil_product SET status = 0 WHERE coil_id = $coil_id";
                mysqli_query($conn, $update_coil_sql);

                $archive_sql = "UPDATE coil_defective SET status = 4 WHERE coil_defective_id = $coil_defective_id";
                mysqli_query($conn, $archive_sql);

                $actorId = $_SESSION['userid'] ?? 0;
                $actor_name = get_staff_name($actorId);
                $actionType = 'approve_coil';
                $targetId = $coil_id;
                $targetType = 'Coil Approved';
                $message = "Coil #$targetId tagged as approved to use by $actor_name";
                $url = '?page=';
                $recipientRole = 'work_order';

                createNotification($actorId, $actionType, $targetId, $targetType, $message, $recipientRole, $url);

                echo "success";
            } else {
                echo "Invalid or already archived coil_defective_id";
            }
            exit;
        }

        $update = "UPDATE coil_defective SET status = '$status' WHERE coil_defective_id = $coil_defective_id";
        if (mysqli_query($conn, $update)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
        exit;
    }

    if ($action === 'submit_claim') {
        $coil_defective_id = intval($_POST['coil_defective_id']);
        $claim_type = mysqli_real_escape_string($conn, $_POST['claim_type']);
        $note_text = mysqli_real_escape_string($conn, $_POST['notes']);
        $staff_id = $_SESSION['userid'] ?? null;

        $getCoilQuery = "SELECT coil_id FROM coil_defective WHERE coil_defective_id = $coil_defective_id LIMIT 1";
        $result = mysqli_query($conn, $getCoilQuery);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $coil_id = intval($row['coil_id']);

            $insertClaim = "
                INSERT INTO coil_claim (coil_defective_id, claim_type, notes, status)
                VALUES ($coil_defective_id, '$claim_type', '$note_text', 0)
            ";

            $updateCoil = "
                UPDATE coil_defective
                SET status = 5
                WHERE coil_defective_id = $coil_defective_id
            ";

            $insertNote = "
                INSERT INTO coil_notes (coil_defective_id, coil_id, note_text, created_by)
                VALUES ($coil_defective_id, $coil_id, '$note_text', " . ($staff_id ?: "NULL") . ")
            ";

            if (
                mysqli_query($conn, $insertClaim) &&
                mysqli_query($conn, $updateCoil) &&
                mysqli_query($conn, $insertNote)
            ) {
                echo 'success';
            } else {
                echo 'error: ' . mysqli_error($conn);
            }

        } else {
            echo 'invalid';
        }
    }
}





