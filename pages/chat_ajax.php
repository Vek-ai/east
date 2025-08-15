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
                    ?>
                        <div class="hstack gap-3 align-items-start mb-7 <?= $msgClass ?>">
                            <?php if (!$isCurrentUser): ?>
                                <img src="<?= $avatar ?>" alt="<?= htmlspecialchars($sender_name) ?>" width="40" height="40" class="rounded-circle" />
                            <?php endif; ?>
                            <div class="<?= $isCurrentUser ? 'text-end' : '' ?>">
                                <?php if (!$isCurrentUser): ?>
                                    <h6 class="fs-2 text-muted"><?= htmlspecialchars($sender_name) ?>, <?= $time ?></h6>
                                    <div class="p-2 text-bg-light rounded-1 d-inline-block text-dark fs-3"><?= htmlspecialchars($msg['body_text']) ?></div>
                                <?php else: ?>
                                    <h6 class="fs-2 text-muted"><?= $time ?></h6>
                                    <div class="p-2 bg-info-subtle text-dark rounded-1 d-inline-block fs-3"><?= htmlspecialchars($msg['body_text']) ?></div>
                                <?php endif; ?>
                            </div>
                            <?php if ($isCurrentUser): ?>
                                <!-- Optional: add spacing to match layout -->
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    </div>
              </div>
              <div class="px-9 py-6 border-top chat-send-message-footer">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2 w-85">
                    <a class="position-relative nav-icon-hover z-index-5" href="javascript:void(0)">
                        <i class="ti ti-mood-smile text-dark bg-hover-primary fs-7"></i>
                    </a>
                    <input type="text" class="form-control message-type-box text-muted border-0 rounded-0 p-0 ms-2" placeholder="Type a Message" />
                    </div>
                    <ul class="list-unstyled mb-0 d-flex align-items-center gap-2">
                    <li>
                        <a class="text-dark px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" href="javascript:void(0)">
                        <i class="ti ti-photo-plus"></i>
                        </a>
                    </li>
                    <li>
                        <a class="text-dark px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" href="javascript:void(0)">
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
                </div>
                </div>
            </div>
            <div class="app-chat-offcanvas border-start">
              <div class="custom-app-scroll mh-n100" data-simplebar>
                <div class="p-3 d-flex align-items-center justify-content-between">
                  <h6 class="fw-semibold mb-0 text-nowrap">
                    Media <span class="text-muted">(36)</span>
                  </h6>
                  <a class="chat-menu d-lg-none d-block text-dark fs-6 bg-hover-primary nav-icon-hover position-relative z-index-5" href="javascript:void(0)">
                    <i class="ti ti-x"></i>
                  </a>
                </div>
                <div class="offcanvas-body p-9">

                  <div class="row mb-7 text-nowrap">
                    <div class="col-4 px-1 mb-2">

                      <img src="../assets/images/products/product-1.jpg" width="88" height="65" alt="materialpro-img" class="rounded" />

                    </div>
                    <div class="col-4 px-1 mb-2">

                      <img src="../assets/images/products/product-2.jpg" width="88" height="65" alt="materialpro-img" class="rounded" />

                    </div>
                    <div class="col-4 px-1 mb-2">

                      <img src="../assets/images/products/product-3.jpg" width="88" height="65" alt="materialpro-img" class="rounded" />

                    </div>
                    <div class="col-4 px-1 mb-2">

                      <img src="../assets/images/products/product-4.jpg" width="88" height="65" alt="materialpro-img" class="rounded" />

                    </div>
                    <div class="col-4 px-1 mb-2">

                      <img src="../assets/images/products/product-1.jpg" width="88" height="65" alt="materialpro-img" class="rounded" />

                    </div>
                    <div class="col-4 px-1 mb-2">

                      <img src="../assets/images/products/product-2.jpg" width="88" height="65" alt="materialpro-img" class="rounded" />

                    </div>

                  </div>
                  <div class="files-chat">
                    <h6 class="fw-semibold mb-3 text-nowrap">
                      Files <span class="text-muted">(36)</span>
                    </h6>
                    <a href="javascript:void(0)" class="hstack gap-3 file-chat-hover justify-content-between text-nowrap mb-9">
                      <div class="d-flex align-items-center gap-3">
                        <div class="rounded-1 text-bg-light p-6">
                          <img src="../assets/images/chat/icon-adobe.svg" alt="materialpro-img" width="24" height="24" />
                        </div>
                        <div>
                          <h6 class="fw-semibold">
                            service-task.pdf
                          </h6>
                          <div class="d-flex align-items-center gap-3 fs-2 text-muted">
                            <span>2 MB</span>
                            <span>2 Dec 2023</span>
                          </div>
                        </div>
                      </div>
                      <span class="position-relative nav-icon-hover download-file">
                        <i class="ti ti-download text-dark fs-6 bg-hover-primary"></i>
                      </span>
                    </a>
                    <a href="javascript:void(0)" class="hstack gap-3 file-chat-hover justify-content-between text-nowrap mb-9">
                      <div class="d-flex align-items-center gap-3">
                        <div class="rounded-1 text-bg-light p-6">
                          <img src="../assets/images/chat/icon-figma.svg" alt="materialpro-img" width="24" height="24" />
                        </div>
                        <div>
                          <h6 class="fw-semibold">
                            homepage-design.fig
                          </h6>
                          <div class="d-flex align-items-center gap-3 fs-2 text-muted">
                            <span>2 MB</span>
                            <span>2 Dec 2023</span>
                          </div>
                        </div>
                      </div>
                      <span class="position-relative nav-icon-hover download-file">
                        <i class="ti ti-download text-dark fs-6 bg-hover-primary"></i>
                      </span>
                    </a>
                    <a href="javascript:void(0)" class="hstack gap-3 file-chat-hover justify-content-between text-nowrap mb-9">
                      <div class="d-flex align-items-center gap-3">
                        <div class="rounded-1 text-bg-light p-6">
                          <img src="../assets/images/chat/icon-chrome.svg" alt="materialpro-img" width="24" height="24" />
                        </div>
                        <div>
                          <h6 class="fw-semibold">about-us.html</h6>
                          <div class="d-flex align-items-center gap-3 fs-2 text-muted">
                            <span>2 MB</span>
                            <span>2 Dec 2023</span>
                          </div>
                        </div>
                      </div>
                      <span class="position-relative nav-icon-hover download-file">
                        <i class="ti ti-download text-dark fs-6 bg-hover-primary"></i>
                      </span>
                    </a>
                    <a href="javascript:void(0)" class="hstack gap-3 file-chat-hover justify-content-between text-nowrap mb-9">
                      <div class="d-flex align-items-center gap-3">
                        <div class="rounded-1 text-bg-light p-6">
                          <img src="../assets/images/chat/icon-zip-folder.svg" alt="materialpro-img" width="24" height="24" />
                        </div>
                        <div>
                          <h6 class="fw-semibold">
                            work-project.zip
                          </h6>
                          <div class="d-flex align-items-center gap-3 fs-2 text-muted">
                            <span>2 MB</span>
                            <span>2 Dec 2023</span>
                          </div>
                        </div>
                      </div>
                      <span class="position-relative nav-icon-hover download-file">
                        <i class="ti ti-download text-dark fs-6 bg-hover-primary"></i>
                      </span>
                    </a>
                    <a href="javascript:void(0)" class="hstack gap-3 file-chat-hover justify-content-between text-nowrap">
                      <div class="d-flex align-items-center gap-3">
                        <div class="rounded-1 text-bg-light p-6">
                          <img src="../assets/images/chat/icon-javascript.svg" alt="materialpro-img" width="24" height="24" />
                        </div>
                        <div>
                          <h6 class="fw-semibold">custom.js</h6>
                          <div class="d-flex align-items-center gap-3 fs-2 text-muted">
                            <span>2 MB</span>
                            <span>2 Dec 2023</span>
                          </div>
                        </div>
                      </div>
                      <span class="position-relative nav-icon-hover download-file">
                        <i class="ti ti-download text-dark fs-6 bg-hover-primary"></i>
                      </span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
        </div>
        <?php
    }

    if ($action == 'send_message') {
        $sender_id = intval($_SESSION['userid']);
        $recipient_id = intval($_POST['recipient_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');

        if ($recipient_id && $message !== '') {
            $sender_id = mysqli_real_escape_string($conn, $sender_id);
            $recipient_id = mysqli_real_escape_string($conn, $recipient_id);
            $message = mysqli_real_escape_string($conn, $message);

            $query = "
                INSERT INTO messages (sender_user_id, recipient_user_id, body_text, created_at)
                VALUES ('$sender_id', '$recipient_id', '$message', NOW())
            ";
            mysqli_query($conn, $query) or die(mysqli_error($conn));
        }
    }

    mysqli_close($conn);
}
?>
