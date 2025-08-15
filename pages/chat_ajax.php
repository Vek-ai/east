<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/functions.php';

$staff_id = $_SESSION['userid'] ?? 0;

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == 'fetch_chat_user') {
        $searchName = trim($_REQUEST['staff_name'] ?? '');
        $chatUsers = getChatUsers($staff_id, $searchName);

        foreach ($chatUsers as $user): 
            $avatar = $user['avatar'];
            $displayName = $user['full_name'];
            $lastMessage = mb_strimwidth($user['last_message'] ?? '', 0, 50, '...');
            $lastTime = $user['last_time'];
            $unreadCount = $user['unread_count'];
            $messageClass = $unreadCount > 0 ? 'text-body-color' : 'text-muted';
            ?>
            <li class="mb-1">
                <a href="javascript:void(0)" class="px-4 py-3 bg-hover-light-black d-flex align-items-start justify-content-between chat-user bg-light-subtle" id="chat_user_<?= $user['id'] ?>" data-user-id="<?= $user['id'] ?>">
                    <div class="d-flex align-items-center">
                        <span class="position-relative">
                            <img src="<?= $avatar ?>" alt="user<?= $user['id'] ?>" width="48" height="48" class="rounded-circle" />
                            <?php if ($unreadCount > 0): ?>
                                <span class="position-absolute bottom-0 end-0 p-1 badge rounded-pill bg-success">
                                    <span class="visually-hidden">New alerts</span>
                                </span>
                            <?php endif; ?>
                        </span>
                        <div class="ms-3 d-inline-block w-75">
                            <h6 class="mb-1 fw-semibold chat-title <?= $messageClass ?>" data-username="<?= $user['username'] ?>"><?= $displayName ?></h6>
                            <span class="fs-3 text-truncate <?= $messageClass ?> d-block"><?= $lastMessage ?></span>
                        </div>
                    </div>
                    <p class="fs-2 mb-0 text-muted"><?= $lastTime ?></p>
                </a>
            </li>
            <?php
        endforeach;
    }

    if ($action == 'fetch_chat_messages') {
        $chatUserId = $_REQUEST['active_chat'] ?? 0;
        $messages = getChatMessages($chatUserId);
        $chatUser = get_staff_name($chatUserId);

        $staff = getStaffDetails($chatUserId);
        $role = get_role_name($staff['role']);
        ?>
        <div class="p-9 border-bottom chat-meta-user d-flex align-items-center justify-content-between">
            <div class="hstack gap-3 current-chat-user-name">
              <div class="position-relative">
                <img src="../assets/images/profile/user-6.jpg" alt="user1" width="48" height="48" class="rounded-circle" />
                <span class="position-absolute bottom-0 end-0 p-1 badge rounded-pill bg-success">
                  <span class="visually-hidden">New alerts</span>
                </span>
              </div>
              <div>
                <h6 class="mb-1 name fw-semibold"><?=$chatUser?></h6>
                <p class="mb-0"><?=$role?></p>
              </div>
            </div>
            <ul class="list-unstyled mb-0 d-flex align-items-center">
              <li>
                <a class="text-dark px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" href="javascript:void(0)">
                  <i class="ti ti-phone"></i>
                </a>
              </li>
              <li>
                <a class="text-dark px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" href="javascript:void(0)">
                  <i class="ti ti-video"></i>
                </a>
              </li>
              <li>
                <a class="chat-menu text-dark px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" href="javascript:void(0)">
                  <i class="ti ti-menu-2"></i>
                </a>
              </li>
            </ul>
          </div>
          <div class="d-flex parent-chat-box app-chat-right">
            <div class="chat-box w-xs-100">
              <div class="chat-box-inner p-9" data-simplebar>
                <div class="chat-list chat active-chat" data-user-id="<?= $chatUserId ?>">
                    <?php
                        foreach ($messages as $msg):
                            $isCurrentUser = $msg['sender_id'] == $_SESSION['userid'];
                            $sender_name = get_staff_name($msg['sender_id']);
                            $msgClass = $isCurrentUser ? 'justify-content-end' : 'justify-content-start';
                            $time = date('h:i A', strtotime($msg['created_at']));
                            $avatar = $msg['sender_avatar'];

                            // Get attachments
                            $attachments = getMessageAttachments($msg['message_id']);

                            // Skip if no text and no attachments
                            if (empty(trim($msg['body_text'])) && empty($attachments)) {
                                continue;
                            }
                            ?>
                            <div class="hstack gap-3 align-items-start mb-3 <?= $msgClass ?>">
                                <?php if (!$isCurrentUser): ?>
                                    <img src="<?= $avatar ?>" alt="<?= htmlspecialchars($sender_name) ?>" width="40" height="40" class="rounded-circle" />
                                <?php endif; ?>

                                <div class="<?= $isCurrentUser ? 'text-end' : '' ?>">
                                    <h6 class="fs-2 text-muted mb-1">
                                        <?php if (!$isCurrentUser): ?>
                                            <?= htmlspecialchars($sender_name) ?>,
                                        <?php endif; ?>
                                        <?= $time ?>
                                    </h6>

                                    <?php if (!empty(trim($msg['body_text']))): ?>
                                        <div class="p-2 rounded-3 mb-2 <?= $isCurrentUser ? 'bg-primary text-white' : 'bg-light text-dark' ?>" style="max-width: 340px;">
                                            <?= htmlspecialchars($msg['body_text']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($attachments)): ?>
                                        <div class="d-flex flex-column <?= $isCurrentUser ? 'align-items-end' : 'align-items-start' ?>" style="gap: 8px;">
                                            <?php foreach ($attachments as $att): ?>
                                                <?php if (strpos($att['mime_type'], 'image/') === 0): ?>
                                                    <a href="<?= htmlspecialchars($att['file_url']) ?>" target="_blank" class="image-attachment d-inline-block">
                                                        <img src="<?= htmlspecialchars($att['file_url']) ?>" 
                                                            alt="attachment"
                                                            style="width: 200px; height: auto; border-radius: 10px; display: block;" />
                                                    </a>
                                                <?php else: ?>
                                                    <a href="<?= htmlspecialchars($att['file_url']) ?>" target="_blank" 
                                                    class="d-flex align-items-center p-2 rounded-3 border text-decoration-none bg-light"
                                                    style="width: 200px;">
                                                        <i class="fa fa-file fa-lg me-2 text-secondary"></i>
                                                        <span style="font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                            <?= htmlspecialchars(basename($att['file_url'])) ?>
                                                        </span>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
              </div>
            <div class="px-9 py-6 border-top chat-send-message-footer">
                <div class="attachment-preview-images mb-2 d-flex flex-wrap"></div>
                <div class="attachment-preview d-flex flex-wrap gap-2 mb-2"></div>
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2 w-85">
                    <a class="position-relative nav-icon-hover z-index-5" href="javascript:void(0)">
                        <i class="ti ti-mood-smile text-dark bg-hover-primary fs-7"></i>
                    </a>
                    <input type="text" class="form-control message-type-box text-muted border-0 rounded-0 p-0 ms-2" placeholder="Type a Message" />
                    </div>
                    <ul class="list-unstyled mb-0 d-flex align-items-center gap-2">
                        <li>
                            <a class="text-dark px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5 btn-attach-image" href="javascript:void(0)">
                                <i class="ti ti-photo-plus"></i>
                            </a>
                        </li>
                        <li>
                            <a class="text-dark px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5 btn-attach" href="javascript:void(0)">
                            <i class="ti ti-paperclip"></i>
                            </a>
                        </li>
                        <li>
                            <a class="text-dark px-2 fs-7 fs-hover-primary nav-icon-hover position-relative z-index-5" href="javascript:void(0)">
                            <i class="ti ti-microphone"></i>
                            </a>
                        </li>
                        <!-- Send button -->
                        <li>
                            <a href="javascript:void(0)" class="btn-send-message text-white bg-primary px-3 py-1 rounded d-flex align-items-center gap-1">
                            <i class="ti ti-send"></i> Send
                            </a>
                        </li>
                    </ul>
                    <input type="file" id="file-attachments" multiple style="display:none;">
                    <input type="file" id="image-attachments" accept="image/*" multiple style="display:none;">
                </div>
            </div>
            </div>
            <?php
            $attachments = getUserMsgAttachments($staff_id);

            $imageAttachments = [];
            $fileAttachments  = [];

            foreach ($attachments as $att) {
                $mime = $att['mime_type'] ?? '';

                if ($mime !== '' && strpos($mime, 'image/') === 0) {
                    $imageAttachments[] = $att;
                } else {
                    $fileAttachments[] = $att;
                }
            }
            ?>

            <div class="app-chat-offcanvas border-start" style="max-height: 900px">
                <div class="custom-app-scroll mh-n100" data-simplebar>
                    <div class="p-3 d-flex align-items-center justify-content-between">
                        <h6 class="fw-semibold mb-0 text-nowrap">
                            Media <span class="text-muted">(<?= count($imageAttachments) ?>)</span>
                        </h6>
                        <a class="chat-menu d-lg-none d-block text-dark fs-6 bg-hover-primary nav-icon-hover position-relative z-index-5" href="javascript:void(0)">
                            <i class="ti ti-x"></i>
                        </a>
                    </div>
                    <div class="offcanvas-body p-9">

                        <!-- IMAGES -->
                        <div class="row mb-7 text-nowrap">
                            <?php foreach ($imageAttachments as $img): ?>
                                <div class="col-4 px-1 mb-2">
                                    <img src="<?= htmlspecialchars($img['file_url']) ?>" 
                                        width="88" height="65" 
                                        alt="media-img" 
                                        class="rounded" />
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- FILES -->
                        <div class="files-chat">
                            <h6 class="fw-semibold mb-3 text-nowrap">
                                Files <span class="text-muted">(<?= count($fileAttachments) ?>)</span>
                            </h6>

                            <?php foreach ($fileAttachments as $file): 
                                $filename = basename($file['file_url']);
                                $filesizeMB = round($file['file_size'] / 1024 / 1024, 2) . ' MB';
                                $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                            ?>
                            <a href="<?= htmlspecialchars($file['file_url']) ?>" download 
                            class="hstack gap-3 file-chat-hover justify-content-between text-nowrap mb-9">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-1 text-bg-light p-6">
                                        <i class="fa fa-file fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-semibold" title="<?= htmlspecialchars($filename) ?>"><?= htmlspecialchars(strlen($filename) > 20 ? substr($filename, 0, 17) . '...' : $filename) ?></h6>
                                        <div class="d-flex align-items-center gap-3 fs-2 text-muted">
                                            <span><?= $filesizeMB ?></span>
                                            <span><?= date('d M Y', strtotime($file['uploaded_at'] ?? 'now')) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <span class="position-relative nav-icon-hover download-file">
                                    <i class="ti ti-download text-dark fs-6 bg-hover-primary"></i>
                                </span>
                            </a>
                            <?php endforeach; ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    if ($action == 'send_message') {
        $sender_id    = intval($_SESSION['userid']);
        $recipient_id = intval($_POST['recipient_id'] ?? 0);
        $message      = trim($_POST['message'] ?? '');

        $hasImages = !empty($_FILES['images']['name'][0]);
        $hasDocs   = !empty($_FILES['attachments']['name'][0]);
        if (!$recipient_id || ($message === '' && !$hasImages && !$hasDocs)) {
            echo "No message or attachments.";
            exit;
        }

        $message_safe = mysqli_real_escape_string($conn, $message);

        $query = "
            INSERT INTO messages (sender_user_id, recipient_user_id, body_text, created_at)
            VALUES ($sender_id, $recipient_id, '$message_safe', NOW())
        ";
        mysqli_query($conn, $query) or die(mysqli_error($conn));
        $message_id = mysqli_insert_id($conn);

        $uploadDir = '../chat_attachments/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        function saveAttachment($conn, $message_id, $tmpName, $name, $isImage = false) {
            $uploadDir = '../chat_attachments/';
            $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', $name);
            $targetFile = $uploadDir . $fileName;

            if (move_uploaded_file($tmpName, $targetFile)) {
                $mimeType = mime_content_type($targetFile);
                $fileSize = filesize($targetFile);
                $sha256   = hash_file('sha256', $targetFile);

                $width = $height = 'NULL';
                if ($isImage) {
                    $imgInfo = getimagesize($targetFile);
                    if ($imgInfo) {
                        $width  = intval($imgInfo[0]);
                        $height = intval($imgInfo[1]);
                    }
                }

                $fileUrl = mysqli_real_escape_string($conn, "chat_attachments/$fileName");
                $mimeType_safe = mysqli_real_escape_string($conn, $mimeType);
                $fileSize_safe = intval($fileSize);
                $sha256_safe   = mysqli_real_escape_string($conn, $sha256);

                mysqli_query($conn, "
                    INSERT INTO message_attachments 
                        (message_id, file_url, mime_type, file_size_bytes, width_px, height_px, sha256_hex, storage_provider) 
                    VALUES 
                        ($message_id, '$fileUrl', '$mimeType_safe', $fileSize_safe, 
                        " . ($width === 'NULL' ? "NULL" : $width) . ", 
                        " . ($height === 'NULL' ? "NULL" : $height) . ", 
                        '$sha256_safe', '')
                ") or die(mysqli_error($conn));
            }
        }

        if ($hasImages) {
            foreach ($_FILES['images']['name'] as $key => $name) {
                saveAttachment($conn, $message_id, $_FILES['images']['tmp_name'][$key], $name, true);
            }
        }

        if ($hasDocs) {
            foreach ($_FILES['attachments']['name'] as $key => $name) {
                saveAttachment($conn, $message_id, $_FILES['attachments']['tmp_name'][$key], $name, false);
            }
        }

        echo "OK";
        exit;
    }

    mysqli_close($conn);
}
?>
