<?php
session_start();

if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Staff Portal</title>
    <?php include 'chat-widget-header.php'; ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f7fa;
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
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .content {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .welcome-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .welcome-card h2 {
            color: #333;
            margin-bottom: 15px;
        }

        .welcome-card p {
            color: #666;
            line-height: 1.6;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .feature-card h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
            font-size: 14px;
        }

        .chat-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-bottom: 15px;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>Staff Dashboard</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="welcome-card">
            <h2>Welcome to Your Dashboard!</h2>
            <p>This is your main dashboard. Notice the chat icon in the lower right corner? Click it to start chatting with your team members. You can send messages, share files, and receive real-time notifications!</p>
        </div>

        <div class="features">
            <div class="feature-card">
                <div class="chat-icon">💬</div>
                <h3>Real-Time Messaging</h3>
                <p>Chat with any staff member instantly. Messages appear in real-time with automatic updates every 2 seconds.</p>
            </div>

            <div class="feature-card">
                <div class="chat-icon">🔔</div>
                <h3>Instant Notifications</h3>
                <p>Get notified when someone sends you a message. Visual badges and sound alerts keep you updated.</p>
            </div>

            <div class="feature-card">
                <div class="chat-icon">📎</div>
                <h3>File Sharing</h3>
                <p>Share documents, images, and files directly in your conversations. All files are securely stored.</p>
            </div>

            <div class="feature-card">
                <div class="chat-icon">👥</div>
                <h3>Staff Directory</h3>
                <p>View all team members and start conversations with anyone in your organization.</p>
            </div>
        </div>
    </div>

    <?php include 'chat-widget-footer.php'; ?>
</body>
</html>
