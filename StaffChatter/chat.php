<?php
session_start();

if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['staff_id'])) {
    header('Location: index.php');
    exit();
}

$other_staff_id = (int)$_GET['staff_id'];

if ($other_staff_id == $_SESSION['userid']) {
    header('Location: index.php');
    exit();
}

require_once 'config/database.php';

$db = new Database();
$conn = $db->connect();

// Get other staff info
$stmt = $conn->prepare("SELECT staff_id, staff_fname, staff_lname, username FROM staff WHERE staff_id = :staff_id");
$stmt->bindParam(':staff_id', $other_staff_id);
$stmt->execute();
$other_staff = $stmt->fetch();

if (!$other_staff) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo htmlspecialchars($other_staff['staff_fname'] . ' ' . $other_staff['staff_lname']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .chat-header-info h1 {
            font-size: 20px;
            margin-bottom: 3px;
        }

        .chat-header-info p {
            font-size: 13px;
            opacity: 0.9;
        }

        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            min-height: 400px;
            max-height: calc(100vh - 250px);
        }

        .message {
            display: flex;
            gap: 10px;
            max-width: 70%;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.sent {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .message.received {
            align-self: flex-start;
        }

        .message-bubble {
            padding: 10px 15px;
            border-radius: 18px;
            word-wrap: break-word;
        }

        .message.sent .message-bubble {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message.received .message-bubble {
            background: #f1f3f5;
            color: #333;
            border-bottom-left-radius: 4px;
        }

        .message-time {
            font-size: 11px;
            opacity: 0.7;
            margin-top: 3px;
            display: block;
        }

        .message-input-container {
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }

        .message-input-form {
            display: flex;
            gap: 10px;
        }

        #messageInput {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 24px;
            font-size: 15px;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        #messageInput:focus {
            outline: none;
            border-color: #667eea;
        }

        .send-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 24px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .send-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .send-btn:active {
            transform: translateY(0);
        }

        .send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #999;
            font-size: 14px;
        }

        .typing-indicator {
            display: none;
            padding: 10px;
            color: #999;
            font-size: 13px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="index.php" class="back-btn">← Back</a>
        <div class="chat-header-info">
            <h1><?php echo htmlspecialchars($other_staff['staff_fname'] . ' ' . $other_staff['staff_lname']); ?></h1>
            <p>@<?php echo htmlspecialchars($other_staff['username']); ?></p>
        </div>
    </div>

    <div class="chat-container">
        <div class="messages-container" id="messagesContainer">
            <div class="loading" id="loadingIndicator">Loading messages...</div>
        </div>

        <div class="message-input-container">
            <form class="message-input-form" id="messageForm">
                <input 
                    type="text" 
                    id="messageInput" 
                    placeholder="Type a message..." 
                    autocomplete="off"
                    required
                >
                <button type="submit" class="send-btn" id="sendBtn">Send</button>
            </form>
        </div>
    </div>

    <script>
        const currentUserId = <?php echo $_SESSION['userid']; ?>;
        const otherStaffId = <?php echo $other_staff_id; ?>;
        const messagesContainer = document.getElementById('messagesContainer');
        const messageForm = document.getElementById('messageForm');
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        const loadingIndicator = document.getElementById('loadingIndicator');
        
        let lastMessageId = 0;
        let isLoadingMessages = false;

        function formatTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            
            if (diff < 86400000) { // Less than 24 hours
                return `${hours}:${minutes}`;
            } else if (diff < 172800000) { // Less than 48 hours
                return `Yesterday ${hours}:${minutes}`;
            } else {
                const day = date.getDate().toString().padStart(2, '0');
                const month = (date.getMonth() + 1).toString().padStart(2, '0');
                return `${day}/${month} ${hours}:${minutes}`;
            }
        }

        function displayMessage(message) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${message.sender_id == currentUserId ? 'sent' : 'received'}`;
            messageDiv.innerHTML = `
                <div class="message-bubble">
                    ${escapeHtml(message.message_text)}
                    <span class="message-time">${formatTime(message.created_at)}</span>
                </div>
            `;
            
            if (loadingIndicator.style.display !== 'none') {
                loadingIndicator.style.display = 'none';
            }
            
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        async function loadMessages() {
            if (isLoadingMessages) return;
            isLoadingMessages = true;

            try {
                const response = await fetch(`api/get_messages.php?other_staff_id=${otherStaffId}&last_message_id=${lastMessageId}`);
                const data = await response.json();

                if (data.success && data.messages.length > 0) {
                    data.messages.forEach(message => {
                        displayMessage(message);
                        if (message.message_id > lastMessageId) {
                            lastMessageId = message.message_id;
                        }
                    });
                } else if (lastMessageId === 0) {
                    loadingIndicator.innerHTML = 'No messages yet. Start the conversation!';
                    loadingIndicator.style.display = 'block';
                }
            } catch (error) {
                console.error('Error loading messages:', error);
            }

            isLoadingMessages = false;
        }

        async function sendMessage(e) {
            e.preventDefault();
            
            const messageText = messageInput.value.trim();
            if (!messageText) return;

            sendBtn.disabled = true;
            messageInput.disabled = true;

            try {
                const formData = new FormData();
                formData.append('other_staff_id', otherStaffId);
                formData.append('message_text', messageText);

                const response = await fetch('api/send_message.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    messageInput.value = '';
                    await loadMessages();
                } else {
                    alert('Error sending message: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error sending message:', error);
                alert('Error sending message. Please try again.');
            }

            sendBtn.disabled = false;
            messageInput.disabled = false;
            messageInput.focus();
        }

        messageForm.addEventListener('submit', sendMessage);

        // Initial load
        loadMessages();

        // Poll for new messages every 2 seconds
        setInterval(loadMessages, 2000);

        // Focus input on load
        messageInput.focus();
    </script>
</body>
</html>
