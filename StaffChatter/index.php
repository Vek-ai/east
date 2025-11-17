<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php';

$db = new Database();
$conn = $db->connect();

// Get all staff members except current user
// Get all staff members except current user
$staff_sql = "
    SELECT 
        staff_id, 
        staff_fname, 
        staff_lname, 
        username
    FROM staff
    WHERE staff_id != :current_user
    ORDER BY staff_fname, staff_lname
";

$stmt = $conn->prepare($staff_sql);
$stmt->bindValue(':current_user', $_SESSION['userid'], PDO::PARAM_INT);
$stmt->execute();
$staff_members = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Get conversations with unread message counts
$conversations_query = "
    SELECT 
        c.conversation_id,
        c.staff1_id,
        c.staff2_id,
        CASE 
            WHEN c.staff1_id = :current_user THEN s2.staff_id
            ELSE s1.staff_id
        END AS other_staff_id,
        CASE 
            WHEN c.staff1_id = :current_user THEN CONCAT(s2.staff_fname, ' ', s2.staff_lname)
            ELSE CONCAT(s1.staff_fname, ' ', s1.staff_lname)
        END AS other_staff_name,
        (
            SELECT COUNT(*) 
            FROM messages m
            WHERE m.conversation_id = c.conversation_id
              AND m.sender_id != :current_user
              AND m.is_read = 0
        ) AS unread_count,
        (
            SELECT m2.message_text
            FROM messages m2
            WHERE m2.conversation_id = c.conversation_id
            ORDER BY m2.created_at DESC
            LIMIT 1
        ) AS last_message,
        (
            SELECT m3.created_at
            FROM messages m3
            WHERE m3.conversation_id = c.conversation_id
            ORDER BY m3.created_at DESC
            LIMIT 1
        ) AS last_message_time
    FROM conversations c
    JOIN staff s1 ON c.staff1_id = s1.staff_id
    JOIN staff s2 ON c.staff2_id = s2.staff_id
    WHERE c.staff1_id = :current_user OR c.staff2_id = :current_user
    ORDER BY COALESCE(last_message_time, '1970-01-01') DESC
";

$stmt = $conn->prepare($conversations_query);
$stmt->bindValue(':current_user', $_SESSION['userid'], PDO::PARAM_INT);
$stmt->execute();
$recent_conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Chat - Messages</title>
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
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .card-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
            color: #333;
        }

        .staff-list, .conversation-list {
            max-height: 600px;
            overflow-y: auto;
        }

        .staff-item, .conversation-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .staff-item:hover, .conversation-item:hover {
            background: #f8f9fa;
        }

        .staff-item:last-child, .conversation-item:last-child {
            border-bottom: none;
        }

        .staff-name {
            font-weight: 500;
            color: #333;
            margin-bottom: 3px;
        }

        .staff-username {
            font-size: 13px;
            color: #666;
        }

        .chat-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .chat-btn:hover {
            background: #5568d3;
        }

        .conversation-info {
            flex: 1;
        }

        .last-message {
            font-size: 13px;
            color: #666;
            margin-top: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 300px;
        }

        .unread-badge {
            background: #667eea;
            color: white;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: #999;
        }

        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
            opacity: 0.3;
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>Staff Chat System</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header">Recent Conversations</div>
            <div class="conversation-list">
                <?php if (empty($recent_conversations)): ?>
                    <div class="empty-state">
                        <p>No conversations yet<br>Start chatting with your team!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_conversations as $conv): ?>
                        <div class="conversation-item" onclick="window.location.href='chat.php?staff_id=<?php echo $conv['other_staff_id']; ?>'">
                            <div class="conversation-info">
                                <div class="staff-name"><?php echo htmlspecialchars($conv['other_staff_name']); ?></div>
                                <?php if ($conv['last_message']): ?>
                                    <div class="last-message"><?php echo htmlspecialchars($conv['last_message']); ?></div>
                                <?php endif; ?>
                            </div>
                            <?php if ($conv['unread_count'] > 0): ?>
                                <span class="unread-badge"><?php echo $conv['unread_count']; ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">All Staff Members</div>
            <div class="staff-list">
                <?php if (empty($staff_members)): ?>
                    <div class="empty-state">
                        <p>No other staff members found</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($staff_members as $staff): ?>
                        <div class="staff-item">
                            <div>
                                <div class="staff-name">
                                    <?php echo htmlspecialchars($staff['staff_fname'] . ' ' . $staff['staff_lname']); ?>
                                </div>
                                <div class="staff-username">@<?php echo htmlspecialchars($staff['username']); ?></div>
                            </div>
                            <button class="chat-btn" onclick="window.location.href='chat.php?staff_id=<?php echo $staff['staff_id']; ?>'">
                                Chat
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
