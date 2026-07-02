<?php
session_start();
// Kiểm tra đăng nhập admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Thử kiểm tra bằng username admin
    if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
        echo "<h2>Bạn cần đăng nhập với tài khoản admin</h2>";
        echo "<a href='login.php'>Đăng nhập</a>";
        exit;
    }
}

include '../db/db_connect.php';

// Xử lý trả lời tin nhắn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'reply') {
        $message = trim($_POST['message'] ?? '');
        $user_id = $_POST['user_id'] ?? '';
        
        if (!empty($message) && !empty($user_id)) {
            $sql = "INSERT INTO messages (sender, receiver, message) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sss", 'admin', $user_id, $message);
            
            if (mysqli_stmt_execute($stmt)) {
                // ✅ Trả về ID của message vừa insert
                $message_id = mysqli_insert_id($conn);
                echo json_encode(['status' => 'success', 'message_id' => $message_id]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Không thể gửi tin nhắn']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'mark_read') {
        $user_id = $_POST['user_id'] ?? '';
        if (!empty($user_id)) {
            $sql = "UPDATE messages SET is_read = 1 
                   WHERE sender = ? AND receiver = 'admin' AND is_read = 0";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $user_id);
            mysqli_stmt_execute($stmt);
        }
        echo json_encode(['status' => 'success']);
        exit;
    }
}

// Lấy danh sách user đã chat
$users_sql = "SELECT DISTINCT sender as user_id, 
                     MAX(created_at) as last_message,
                     (SELECT COUNT(*) FROM messages m2 
                      WHERE m2.sender = m1.sender AND m2.receiver = 'admin' AND m2.is_read = 0) as unread_count
              FROM messages m1 
              WHERE sender != 'admin' 
              GROUP BY sender 
              ORDER BY last_message DESC";
$users_result = mysqli_query($conn, $users_sql);

$users = array();
if ($users_result && mysqli_num_rows($users_result) > 0) {
    while ($row = mysqli_fetch_assoc($users_result)) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Chat - Hỗ trợ khách hàng</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 1.5em;
        }

        .header .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 8px 15px;
            border-radius: 5px;
            background: rgba(255,255,255,0.2);
            transition: background 0.3s;
        }

        .header .nav-links a:hover {
            background: rgba(255,255,255,0.3);
        }

        .chat-container {
            display: flex;
            flex: 1;
            max-width: 1200px;
            margin: 20px auto;
            gap: 20px;
            padding: 0 20px;
            width: 100%;
        }

        .users-panel {
            width: 300px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .users-header {
            background: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #ddd;
            font-weight: 600;
            color: #333;
        }

        .user-list {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }

        .user-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background 0.3s;
            position: relative;
        }

        .user-item:hover {
            background: #f8f9fa;
        }

        .user-item.active {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
        }

        .user-name {
            font-weight: 500;
            color: #333;
        }

        .user-time {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .unread-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #f44336;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
        }

        .chat-panel {
            flex: 1;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            font-weight: 600;
            color: #333;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #fafafa;
        }
        .message-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            word-wrap: break-word;
            position: relative;
        }

        .message.admin .message-bubble {
            background: #e3f2fd;
            color: #333;
            border-bottom-left-radius: 4px;
        }

        .message.user .message-bubble {
            background: #2196f3;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message-time {
            font-size: 11px;
            color: #999;
            margin-top: 5px;
            padding: 0 5px;
        }

        .message-input {
            padding: 20px;
            border-top: 1px solid #ddd;
            background: white;
        }

        .input-group {
            display: flex;
            gap: 10px;
        }

        .message-input input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 25px;
            outline: none;
            font-size: 14px;
        }

        .message-input input:focus {
            border-color: #2196f3;
        }

        .send-btn {
            padding: 12px 20px;
            background: #2196f3;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s;
        }

        .send-btn:hover {
            background: #1976d2;
        }

        .send-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state img {
            width: 100px;
            opacity: 0.3;
            margin-bottom: 20px;
        }

        .typing-indicator {
            display: none;
            padding: 10px 20px;
            color: #666;
            font-style: italic;
            font-size: 12px;
        }

        .typing-indicator.show {
            display: block;
        }

        /* Message bubbles */
       .message {
            margin: 10px 0;
            display: flex;
            align-items: flex-end;
        }

        .message.user {
            justify-content: flex-start;  /* bên trái */
        }

        .message.admin {
            justify-content: flex-end;    /* bên phải */
        }

        .message-content {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 18px;
            word-wrap: break-word;
        }

        .message.user .message-content {
            background: #e3f2fd;  /* Xanh nhạt - tin nhận */
            color: #1976d2;
            border-bottom-left-radius: 4px;
        }

        .message.admin .message-content {
            background: #4caf50;  /* Xanh lá - tin gửi */
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message-time {
            font-size: 11px;
            color: #999;
            margin: 0 10px;
        }

        .message.user .message-time {
            order: 1;
        }

        .message.admin .message-time {
            order: -1;
        }

        .new-message-alert {
            background: #ff9800;
            color: white;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }

        @media (max-width: 768px) {
            .chat-container {
                flex-direction: column;
                padding: 10px;
            }

            .users-panel {
                width: 100%;
                max-height: 200px;
            }

            .message-bubble {
                max-width: 85%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Chat - Hỗ trợ khách hàng</h1>
        <div class="nav-links">
            <a href="admin_dashboard.php">Quay lại</a>
        </div>
    </div>

    <div class="chat-container">
        <div class="users-panel">
            <div class="users-header">
                Danh sách khách hàng
            </div>
            <div class="user-list">
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <div class="user-item" 
                             data-user="<?= htmlspecialchars($user['user_id']) ?>"
                             onclick="selectUser('<?= $user['user_id'] ?>')">
                            <div class="user-name"><?= htmlspecialchars($user['user_id']) ?></div>
                            <div class="user-time"><?= date('H:i', strtotime($user['last_message'])) ?></div>
                            <div class="unread-badge" style="<?= $user['unread_count'] > 0 ? 'display: block;' : 'display: none;' ?>">
                                <?= $user['unread_count'] ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-users">Chưa có tin nhắn nào</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="chat-panel">
            <div class="chat-header" id="chatHeader">
                 Chọn khách hàng để bắt đầu chat
            </div>
            
            <div class="chat-messages" id="chatMessages">
                <div class="empty-state" id="emptyState">
                    <h3>👋 Chọn khách hàng để bắt đầu chat</h3>
                    <p>Chọn một khách hàng từ danh sách bên trái để xem và trả lời tin nhắn</p>
                </div>
            </div>
            
            <div class="typing-indicator" id="typingIndicator">
                Khách hàng đang nhập...
            </div>
            <div class="message-input">
                <div class="input-group">
                    <input type="text" id="messageInput" placeholder="Nhập tin nhắn..." 
                           disabled
                           onkeypress="if(event.key === 'Enter') sendMessage()">
                    <button class="send-btn" onclick="sendMessage()" id="sendBtn"
                            disabled>
                        Gửi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedUser = '';
        let lastMessageId = 0;

        function addMessage(message, sender, timestamp = null) {
            const messagesContainer = document.getElementById('chatMessages');
            
            // 👉 FIX: Kiểm tra element tồn tại
            if (!messagesContainer) {
                console.warn('chatMessages không tồn tại, không thể add message');
                return;
            }
            
            // Xóa welcome message nếu có
            const welcomeMsg = messagesContainer.querySelector('.welcome-message');
            if (welcomeMsg) {
                welcomeMsg.remove();
            }
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.textContent = message;
            
            const timeDiv = document.createElement('div');
            timeDiv.className = 'message-time';
            timeDiv.textContent = timestamp || new Date().toLocaleTimeString('vi-VN', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            messageDiv.appendChild(contentDiv);
            messageDiv.appendChild(timeDiv);
            messagesContainer.appendChild(messageDiv);
            
            // Scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function selectUser(userId) {
            selectedUser = userId;
            lastMessageId = 0;

            // Update header
            const chatHeader = document.getElementById('chatHeader');
            if (chatHeader) {
                chatHeader.innerHTML = `💬 Đang chat với: ${userId}`;
            }

            // Show loading
            const messagesContainer = document.getElementById('chatMessages');
            if (messagesContainer) {
                messagesContainer.innerHTML = '<p style="text-align:center; padding:20px; color:#666;">⏳ Đang tải...</p>';
            }

            // Active UI
            document.querySelectorAll('.user-item').forEach(item => {
                item.classList.remove('active');
            });

            const userElement = document.querySelector(`[data-user="${userId}"]`);
            if (userElement) userElement.classList.add('active');

            // Enable input
            document.getElementById('messageInput').disabled = false;
            document.getElementById('sendBtn').disabled = false;
            document.getElementById('messageInput').focus();

            // Load tin nhắn lần đầu (full)
            loadMessages(true);
        }

        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message || !selectedUser) return;
            
            const sendBtn = document.getElementById('sendBtn');
            sendBtn.disabled = true;
            
            // ✅ HIỂN THỊ NGAY - chỉ 1 lần (giống Messenger)
            addMessage(message, 'admin');
            
            // ✅ Lấy timestamp hiện tại làm lastMessageId (tránh duplicate)
            lastMessageId = Date.now();
            
            input.value = '';
            
            fetch('admin_chat_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=reply&user_id=${selectedUser}&message=${encodeURIComponent(message)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success') {
                    alert('Lỗi: ' + data.message);
                    // Rollback lastMessageId nếu lỗi
                    lastMessageId = 0;
                } else if (data.message_id) {
                    // ✅ CẬP NHẬT lastMessageId từ DB nếu có
                    lastMessageId = data.message_id;
                }
            })
            .catch(() => {
                lastMessageId = 0; // Rollback nếu fetch fail
            })
            .finally(() => {
                sendBtn.disabled = false;
            });
        }

        function loadMessages(isFirstLoad = false) {
            if (!selectedUser) return;

            const messagesContainer = document.getElementById('chatMessages');
            if (!messagesContainer) return;

            // Xác định last_id dựa vào isFirstLoad
            const lastId = isFirstLoad ? 0 : lastMessageId;

            fetch(`admin_chat_api.php?action=get_messages&user_id=${selectedUser}&last_id=${lastId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Nếu load lần đầu → xóa hết (bao gồm empty state)
                    if (isFirstLoad) {
                        messagesContainer.innerHTML = '';
                    }

                    data.messages.forEach(msg => {
                        addMessage(
                            msg.message,
                            msg.is_me ? 'admin' : 'user',
                            new Date(msg.created_at).toLocaleTimeString('vi-VN', {
                                hour: '2-digit',
                                minute: '2-digit'
                            })
                        );

                        lastMessageId = Math.max(lastMessageId, msg.id);
                    });

                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            });
        }

        function loadUsers() {
            fetch('admin_chat_api.php?action=get_users')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Cập nhật số unread count
                    data.users.forEach(user => {
                        const badge = document.querySelector(`[data-user="${user.user_id}"] .unread-badge`);
                        if (badge) {
                            badge.textContent = user.unread_count;
                            badge.style.display = user.unread_count > 0 ? 'block' : 'none';
                        }
                    });
                }
            });
        }

        // Auto refresh mỗi 3 giây để nhận tin nhắn mới
        setInterval(() => {
            if (selectedUser) {
                loadMessages();
            }
            loadUsers(); // Cập nhật danh sách user
        }, 3000);
    </script>
</body>
</html>
