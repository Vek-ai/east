<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require_once 'includes/dbconn.php';
require_once 'includes/functions.php';

$staff_id = intval($_SESSION['userid']);
$staff = getStaffDetails($staff_id);
$staff_name = get_staff_name($staff_id);
$role = get_role_name($staff['role']);
?>
<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
  <div class="card-body px-0">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h4 class="font-weight-medium fs-14 mb-0">Chat</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="">Home
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Chat</li>
          </ol>
        </nav>
      </div>
    </div>
  </div>
</div>

<div class="card overflow-hidden chat-application" >
  <div class="d-flex align-items-center justify-content-between gap-6 m-3 d-lg-none">
    <button class="btn btn-primary d-flex" type="button" data-bs-toggle="offcanvas" data-bs-target="#chat-sidebar" aria-controls="chat-sidebar">
      <i class="ti ti-menu-2 fs-5"></i>
    </button>
    <form class="position-relative w-100">
      <input type="text" class="form-control search-chat py-2 ps-5" id="text-srh" placeholder="Search Contact" />
      <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
    </form>
  </div>
  <div class="d-flex" style="height: 100%">
    <div class="w-30 d-none d-lg-block border-end user-chat-box">
      <div class="px-4 pt-9 pb-6">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div class="d-flex align-items-center">
            <div class="position-relative">
              <img src="../assets/images/profile/user-1.jpg" alt="user1" width="54" height="54" class="rounded-circle" />
              <span class="position-absolute bottom-0 end-0 p-1 badge rounded-pill bg-success">
                <span class="visually-hidden">New alerts</span>
              </span>
            </div>
            <div class="ms-3">
              <h6 class="fw-semibold mb-2"><?=$staff_name?></h6>
              <p class="mb-0 fs-2"><?= $role ?></p>
            </div>
          </div>
          <div class="dropdown">
            <a class="text-dark fs-6 nav-icon-hover" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="ti ti-dots-vertical"></i>
            </a>
            <ul class="dropdown-menu">
              <li>
                <a class="dropdown-item d-flex align-items-center gap-2 border-bottom" href="javascript:void(0)">
                  <span>
                    <i class="ti ti-settings fs-4"></i>
                  </span>Setting
                </a>
              </li>
              <li>
                <a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0)">
                  <span>
                    <i class="ti ti-help fs-4"></i>
                  </span>Help
                  and feedback
                </a>
              </li>
              <li>
                <a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0)">
                  <span>
                    <i class="ti ti-layout-board-split fs-4"></i>
                  </span>Enable split View mode
                </a>
              </li>
              <li>
                <a class="dropdown-item d-flex align-items-center gap-2 border-bottom" href="javascript:void(0)">
                  <span>
                    <i class="ti ti-table-shortcut fs-4"></i>
                  </span>Keyboard
                  shortcut
                </a>
              </li>
              <li>
                <a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0)">
                  <span>
                    <i class="ti ti-login fs-4"></i>
                  </span>Sign
                  Out
                </a>
              </li>
            </ul>
          </div>
        </div>
        <form class="position-relative mb-4">
          <input type="text" class="form-control search-chat py-2 ps-5" id="text-srh" placeholder="Search Staff" />
          <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
        </form>
        <div class="dropdown">
          <a class="text-muted fw-semibold d-flex align-items-center" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Recent Chats<i class="ti ti-chevron-down ms-1 fs-5"></i>
          </a>
          <ul class="dropdown-menu">
            <li>
              <a class="dropdown-item" href="javascript:void(0)">Sort by time</a>
            </li>
            <li>
              <a class="dropdown-item border-bottom" href="javascript:void(0)">Sort by Unread</a>
            </li>
            <li>
              <a class="dropdown-item" href="javascript:void(0)">Hide favourites</a>
            </li>
          </ul>
        </div>
      </div>
      <div class="app-chat">
        <ul class="chat-users mb-0 mh-n100" data-simplebar>
          
        </ul>
      </div>
    </div>
    <div class="w-70 w-xs-100 chat-container">
      <div class="chat-box-inner-part h-100">
        <div class="chat-not-selected h-100 d-none">
          <div class="d-flex align-items-center justify-content-center h-100 p-5">
            <div class="text-center">
              <span class="text-primary">
                <i class="ti ti-message-dots fs-10"></i>
              </span>
              <h6 class="mt-2">Open chat from the list</h6>
            </div>
          </div>
        </div>
        <div class="chatting-box d-block" style="height: 100%">
            <li class="chat-placeholder" style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; text-align:center; color:#888; gap:10px;">
                <i class="fas fa-comments" style="font-size:120px; opacity:0.6;"></i>
                <h5 style="margin:0; font-weight:600; font-size:1.1rem;">No conversation selected</h5>
                <p style="margin:0; font-size:0.85rem; color:#aaa;">Click on a user to start chatting</p>
            </li>
        </div>
      </div>
    </div>
  </div>
</div>

  <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/libs/simplebar/dist/simplebar.min.js"></script>
  <script src="assets/js/theme/app.dark.init.js"></script>
  <script src="assets/js/theme/theme.js"></script>
  <script src="assets/js/theme/app.min.js"></script>
  <script src="assets/js/theme/feather.min.js"></script>

<script>
$(document).ready(function() {
    var active_chat = 0;
    var chatRefreshTimer = null;

    function loadChatUsers(staff_name) {
        $.ajax({
            url: 'pages/chat_ajax.php',
            type: 'POST',
            data: { staff_name, action: 'fetch_chat_user' },
            success: function(res) { $('.chat-users').html(res); }
        });
    }

    function loadChatMessages() {
        if (!active_chat) return;
        $.ajax({
            url: 'pages/chat_ajax.php',
            type: 'POST',
            data: { 
              action: 'fetch_chat_messages',
              active_chat 
            },
            success: function(html) { 
                $('.chatting-box').html(html);

                scrollChatToBottom();
            }
        });
    }

    function openChat(userId) {
        active_chat = userId;
        loadChatMessages();
    }

    function scrollChatToBottom() {
      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          var root =
            document.querySelector('.chat-box-inner[data-simplebar]') ||
            document.querySelector('.custom-app-scroll[data-simplebar]');

          if (root) {
            var wrap = root.querySelector('.simplebar-content-wrapper');
            if (wrap) { wrap.scrollTop = wrap.scrollHeight; return; }
          }

          var el =
            document.querySelector('.chat-box-inner') ||
            document.querySelector('.chatting-box');
          if (el) { el.scrollTop = el.scrollHeight; return; }

          var last = document.querySelector('.chat-list > :last-child');
          if (last) { last.scrollIntoView({ block: 'end' }); }
        });
      });
    }

    loadChatUsers();
    setInterval(loadChatUsers, 30000);

    $(document).on('input', '.search-chat', function() {
        loadChatUsers($(this).val());
    });

    $(document).on('click', '.chat-user', function() {
        openChat($(this).data('user-id'));
    });

    $(document).on("click", ".chat-menu", function () {
      $(".parent-chat-box").toggleClass("app-chat-right");
      $(this).toggleClass("app-chat-active");
  });

    $(document).on('click', '.btn-attach', function () {
        $('#file-attachments').click();
    });
    
    $(document).on('click', '.btn-attach-image', function () {
        $('#image-attachments').click();
    });

    $(document).on('change', '#image-attachments', function (e) {
        let files = e.target.files;
        $('.attachment-preview-images').empty();

        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                let reader = new FileReader();
                reader.onload = function (ev) {
                    $('.attachment-preview-images').append(
                        `<img src="${ev.target.result}" 
                              class="rounded border me-1 mb-1" 
                              style="width:70px;height:70px;object-fit:cover;">`
                    );
                };
                reader.readAsDataURL(file);
            }
        });
    });

    $(document).on('change', '#file-attachments', function (e) {
        let files = e.target.files;
        $('.attachment-preview-files').empty();

        Array.from(files).forEach(file => {
            if (!file.type.startsWith('image/')) {
                $('.attachment-preview-files').append(
                    `<div class="border rounded p-1 bg-light small mb-1">
                        <i class="fa fa-file text-secondary me-1"></i> ${file.name}
                    </div>`
                );
            }
        });
    });

    $(document).on('click', '.btn-send-message', function () {
        let message = $('.message-type-box').val().trim();
        let imageFiles = $('#image-attachments')[0].files;
        let docFiles = $('#file-attachments')[0].files;

        if (!message && imageFiles.length === 0 && docFiles.length === 0) return;

        let formData = new FormData();
        formData.append('action', 'send_message');
        formData.append('recipient_id', active_chat);
        formData.append('message', message);

        Array.from(imageFiles).forEach(file => formData.append('images[]', file));
        Array.from(docFiles).forEach(file => formData.append('attachments[]', file));

        $.ajax({
            url: 'pages/chat_ajax.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                console.log('Server Response:', response);
                $('.message-type-box').val('');
                $('#image-attachments').val('');
                $('#file-attachments').val('');
                $('.attachment-preview-images').empty();
                $('.attachment-preview-files').empty();
                loadChatMessages();
            },
            error: function (xhr) {
                console.error('AJAX Error:', xhr.responseText);
            }
        });
    });

    $(document).on('keypress', '.message-type-box', function(e) {
        if (e.which === 13) {
            $('.btn-send-message').click();
            e.preventDefault();
        }
    });
});
</script>

