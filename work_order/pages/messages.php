<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);
require_once '../includes/dbconn.php';
require_once '../includes/functions.php';

if(isset($_SESSION['work_order_user_id'])){
  $staff_id = $_SESSION['work_order_user_id'];
  $staff_details = getStaffDetails($staff_id);
?>

<style>
    #searchResults {
        max-height: 700px;
        overflow-y: auto;
        z-index: 9999;
        width: 100%;
        width: 400px;
        position: absolute;
        background-color: white;
    }

    #searchResults li {
        padding: 8px;
        cursor: pointer;
    }

    #searchResults li:hover {
        background-color: #f5f5f5;
    }
</style>

<div class="container-fluid mt-3">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
        <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
            <div>
            <h4 class="font-weight-medium fs-14 mb-0">Chat</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a class="text-muted text-decoration-none" href="?page=">Home
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
            <input type="text" class="form-control search-chat py-2 ps-5" id="text-srh-mob" placeholder="Search Contact" />
            <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
        </form>
        
        </div>
        <div class="d-flex">
        <div class="w-100 w-lg-100 d-lg-block border-end user-chat-box" style="height: 60vh;">
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
                    <h6 class="fw-semibold mb-2"><?= ucwords($staff_details['staff_fname'] .' ' .$customer_details['staff_lname']) ?></h6>
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
                <input type="text" class="form-control search-chat py-2 ps-5" id="text-srh" placeholder="Search Contact" />
                <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                <div id="searchResults" class="d-none mt-2 position-absolute bg-white shadow rounded" data-simplebar>
                    <!-- Results will be dynamically appended here -->
                </div>
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
            <?php
                $current_user_id = $staff_id;
                $sql = "SELECT sm.*, 
                            s1.staff_fname AS sender_fname, 
                            s1.staff_lname AS sender_lname, 
                            s2.staff_fname AS receiver_fname, 
                            s2.staff_lname AS receiver_lname
                        FROM staff_messages sm
                        LEFT JOIN staff s1 ON sm.sender_id = s1.staff_id
                        LEFT JOIN staff s2 ON sm.receiver_id = s2.staff_id
                        WHERE sm.id IN (
                            SELECT MAX(id)
                            FROM staff_messages
                            WHERE sender_id = $current_user_id OR receiver_id = $current_user_id
                            GROUP BY CASE
                                WHEN sender_id = $current_user_id THEN receiver_id
                                ELSE sender_id
                            END
                        )
                        ORDER BY sm.sent_at DESC";

                $result = $conn->query($sql);

                while ($row = $result->fetch_assoc()) {
                    $is_sender = $row['sender_id'] == $current_user_id;
                    $other_id = $is_sender ? $row['receiver_id'] : $row['sender_id'];
                    $other_name = $is_sender
                        ? $row['receiver_fname'] . ' ' . $row['receiver_lname']
                        : $row['sender_fname'] . ' ' . $row['sender_lname'];

                    $message_preview = substr($row['message'], 0, 30) . '...';
                    $time_ago = time_ago($row['sent_at']);

                    $sender_img = "../assets/images/profile/user-6.jpg";

                    ?>

                    <ul class="chat-users mb-0 mh-n100" data-simplebar>
                        <li>
                            <a href="javascript:void(0)" class="px-4 py-3 bg-hover-light-black d-flex align-items-start justify-content-between chat-user bg-light-subtle" data-user="<?= $other_id ?>">
                                <div class="d-flex align-items-center">
                                    <span class="position-relative">
                                        <img src="<?= $sender_img ?>" alt="user<?= $other_id ?>" width="48" height="48" class="rounded-circle" />
                                        <span class="position-absolute bottom-0 end-0 p-1 badge rounded-pill bg-success">
                                            <span class="visually-hidden">New alerts</span>
                                        </span>
                                    </span>
                                    <div class="ms-3 d-inline-block w-75">
                                        <h6 class="mb-1 fw-semibold chat-title" data-username="<?= htmlspecialchars($other_name) ?>">
                                            <?= htmlspecialchars($other_name) ?>
                                        </h6>
                                        <span class="fs-3 text-truncate text-body-color d-block"><?= htmlspecialchars($message_preview) ?></span>
                                    </div>
                                </div>
                                <p class="fs-2 mb-0 text-muted"><?= $time_ago ?></p>
                            </a>
                        </li>
                    </ul>

                <?php 
                } 
                ?>

            </div>
        </div>
        <div class="w-70 w-xs-100 chat-container d-none">
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
            <div class="chatting-box h-100">
                <div class="p-9 border-bottom chat-meta-user d-flex align-items-center justify-content-between">
                <div class="hstack gap-3 current-chat-user-name">
                    <div class="position-relative">
                    <img src="../assets/images/profile/user-6.jpg" alt="user1" width="48" height="48" class="rounded-circle" />
                    <span class="position-absolute bottom-0 end-0 p-1 badge rounded-pill bg-success">
                        <span class="visually-hidden">New alerts</span>
                    </span>
                    </div>
                    <div>
                    <h6 class="mb-1 name fw-semibold" id="friend-name"></h6>
                    </div>
                </div>
                <ul class="list-unstyled mb-0 d-flex align-items-center">
                    <li>
                    <a class="chat-menu text-dark px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" href="javascript:void(0)">
                        <i class="ti ti-menu-2"></i>
                    </a>
                    </li>
                </ul>
                </div>
                
                <div class="d-flex parent-chat-box h-100 app-chat-right">
                <div class="chat-box h-100 w-xs-100">
                    <div class="chat-box-inner p-9" id="chatBoxInner" data-simplebar>
                        <div class="chat-list chat active-chat">

                        </div>
                    </div>
                    <div class="px-9 py-6 border-top chat-send-message-footer">
                        <form id="sendMessageForm" class="d-flex align-items-center justify-content-between">
                            <input type="hidden" id="user_id" name="user_id" />
                            <div class="d-flex align-items-center gap-2 w-85">
                                <a class="position-relative nav-icon-hover z-index-5" href="javascript:void(0)" id="emojiTrigger">
                                    <i class="fa fa-face-smile text-dark bg-hover-primary fs-7"></i>
                                </a>
                                <input type="text" name="message" id="messageInput" class="form-control message-type-box text-muted border-0 rounded-0 p-0 ms-2" placeholder="Type a Message" required />
                            </div>
                            <ul class="list-unstyledn mb-0 d-flex align-items-center">
                                <li>
                                    <a href="javascript:void(0)" id="submitMessage" class="text-dark px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5">
                                        <i class="ti ti-send"></i>
                                    </a>
                                </li>
                            </ul>
                        </form>
                    </div>
                </div>
                </div>

            </div>
            </div>
        </div>
        </div>
    </div>
</div>


<script>
    function scrollChatToBottom() {
        const chatList = document.querySelector('.chat-list');
        const lastMessage = chatList.lastElementChild;

        if (lastMessage) {
            lastMessage.scrollIntoView({ behavior: 'auto', block: 'end' });
        }
    }

    function fetchChatHistory(userId) {
        $.ajax({
            url: 'pages/messages_ajax.php',
            type: 'POST',
            data: { 
                user_id: userId,
                fetch_chat_history: 'fetch_chat_history'
            },
            dataType: 'json',
            success: function(response) {
                const $chatList = $('.chat-list');
                $chatList.empty();

                const messages = response.messages;
                const friendName = response.friend_name;

                $('#friend-name').text(friendName);
                $('#user_id').val(userId); // set user id in hidden form field

                const fragment = $(document.createDocumentFragment());

                messages.forEach(function(messageData) {
                    let messageHtml = '';
                    const timeSent = messageData.time_sent;
                    const senderName = messageData.sender_name;
                    const message = messageData.message;
                    const isSender = messageData.is_sender;
                    const isWithMessage = messageData.isWithMessage;

                    if (!isWithMessage) {
                        messageHtml = `
                            <div class="d-flex justify-content-center align-items-center" style="height: 100%; text-align: center;">
                                <h6 class="fs-3 text-muted">${message}</h6>
                            </div>
                        `;
                    } else {
                        if (isSender) {
                            messageHtml = `
                                <div class="hstack gap-3 align-items-start mb-7 justify-content-end">
                                    <div class="text-end">
                                        <h6 class="fs-2 text-muted">${timeSent}</h6>
                                        <div class="p-2 bg-info-subtle text-dark mb-1 d-inline-block rounded-1 fs-3">
                                            ${message}
                                        </div>
                                    </div>
                                </div>
                            `;
                        } else {
                            messageHtml = `
                                <div class="hstack gap-3 align-items-start mb-7 justify-content-start">
                                    <img src="../assets/images/profile/user-12.jpg" alt="user" width="40" height="40" class="rounded-circle" />
                                    <div>
                                        <h6 class="fs-2 text-muted">${senderName}, ${timeSent}</h6>
                                        <div class="p-2 text-bg-light rounded-1 d-inline-block text-dark fs-3">
                                            ${message}
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                    }

                    fragment.append($(messageHtml));
                });

                $chatList.append(fragment);

                $('.chat-container').removeClass('d-none');

                scrollChatToBottom();
            },
            error: function(jqXHR, status, error) {
                console.log('Error fetching chat history: ' + error);
                console.log('Response Text: ' + jqXHR.responseText);
            }
        });
    }

    $(document).ready(function () {
        var current_active_id = 0;

        $('#submitMessage').on('click', function () {
            $('#sendMessageForm').submit();
        });

        $(document).on('click', '.chat-user', function () {
            current_active_id = $(this).data('user');
            fetchChatHistory(current_active_id);

            $('#searchResults').addClass('d-none');

            $('.chat-container').removeClass('d-none');
            $('.user-chat-box')
                .removeClass('w-100 w-lg-100')
                .addClass('w-30');
        });

        $(document).on('click', function (e) {
            var searchResults = $('#searchResults');
            var searchInput = $('#text-srh');

            if (!searchResults.is(e.target) && !searchInput.is(e.target) && searchResults.has(e.target).length === 0) {
                searchResults.addClass('d-none');
            }
        });

        $('#text-srh').on('input', function () {
            var query = $(this).val().trim();

            if (query.length > 0) {
                $.ajax({
                    url: 'pages/messages_ajax.php',
                    method: 'POST',
                    data: { 
                        search: query,
                        search_contact: 'search_contact'
                    },
                    success: function (response) {
                        var results = JSON.parse(response);
                        var resultsContainer = $('#searchResults');
                        resultsContainer.empty().removeClass('d-none');

                        if (results.length > 0) {
                            results.forEach(function (customer) {
                                var customerItem = `
                                    <a href="javascript:void(0)" class="d-flex align-items-center p-2 border-bottom chat-user" data-user="${customer.id}">
                                        <img src="${customer.image}" alt="${customer.name}" class="rounded-circle me-3" width="40" height="40">
                                        <div>
                                            <div class="fw-bold">${customer.name}</div>
                                            <small class="text-muted">${customer.email}</small>
                                        </div>
                                    </a>
                                `;
                                resultsContainer.append(customerItem);
                            });
                        } else {
                            resultsContainer.append(`
                                <div class="d-flex justify-content-center align-items-center" style="height: 100px;">
                                    <span class="text-muted">No customers found</span>
                                </div>
                            `);
                        }
                    }
                });
            } else {
                $('#searchResults').empty().addClass('d-none');
            }
        });

        $('#sendMessageForm').on('submit', function(event) {
            event.preventDefault();

            const message = $('#messageInput').val().trim();
            if (message === '') {
                return;
            }
            
            const formData = new FormData(this);
            formData.append('send_message', 'send_message');

            $.ajax({
                url: 'pages/messages_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    fetchChatHistory(current_active_id);
                    $('#messageInput').val('');
                },
                error: function(xhr, status, error) {
                    console.log('Error fetching chat history: ' + error);
                    console.log('Response Text: ' + jqXHR.responseText);
                }
            });
        });

    });

</script>
<?php
}
?>