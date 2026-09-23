<?php
session_start();
include '../db/db_connect.php';

// Kiểm tra xác thực - yêu cầu đăng nhập
if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Xác định user_id - Luôn là user khi vào chat.php
// Dù admin đăng nhập vẫn chat với tư cách user

// Debug session
error_log("Chat.php Debug - Session data: " . print_r($_SESSION, true));

if (isset($_SESSION['user_id'])) {
    $user_id = 'user_' . $_SESSION['user_id'];
} elseif (isset($_SESSION['username'])) {
    // ĐỒNG BỘ 100% với chat_api.php
    if ($_SESSION['username'] === 'admin') {
        $user_id = 'admin'; // admin chuẩn - ĐỒNG BỘ
    } else {
        $user_id = 'user_' . $_SESSION['username'];
    }
} else {
    // Guest user - tạo ID từ session
    $user_id = 'guest_' . substr(session_id(), 0, 8);
}

// Debug final user_id (tắt trong production)
// error_log("Chat.php Debug - Final user_id: $user_id");

// Nếu là admin, chuyển đến admin chat
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: ../admin/admin_chat.php');
    exit;
}

if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin') {
    header('Location: ../admin/admin_chat.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat với Admin - Hỗ trợ</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .chat-container {
            width: 100%;
            max-width: 400px;
            height: 600px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }

        .chat-header {
            background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
        }

        .chat-header h2 {
            font-size: 1.2em;
            margin-bottom: 5px;
        }

        .chat-status {
            font-size: 12px;
            opacity: 0.9;
        }

        .online-indicator {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 12px;
            height: 12px;
            background: #4caf50;
            border-radius: 50%;
            border: 2px solid white;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .message {
            display: flex;
            flex-direction: column;
            max-width: 80%;
        }

        .message.user {
            align-self: flex-end;
            align-items: flex-end;
        }

        .message.admin {
            align-self: flex-start;
            align-items: flex-start;
        }

        .message-bubble {
            padding: 12px 16px;
            border-radius: 18px;
            word-wrap: break-word;
            position: relative;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .message.user .message-bubble {
            background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message.admin .message-bubble {
            background: white;
            color: #333;
            border-bottom-left-radius: 4px;
            border: 1px solid #e0e0e0;
        }

        .message-time {
            font-size: 10px;
            color: #999;
            margin-top: 5px;
            padding: 0 5px;
        }

        .message.user .message-time {
            text-align: right;
        }

        .typing-indicator {
            display: none;
            align-self: flex-start;
            padding: 10px 16px;
            background: white;
            border-radius: 18px;
            border-bottom-left-radius: 4px;
            border: 1px solid #e0e0e0;
            color: #666;
            font-style: italic;
            font-size: 14px;
        }

        .typing-indicator.show {
            display: block;
        }

        .typing-dots {
            display: inline-block;
        }

        .typing-dots::after {
            content: '...';
            animation: typing 1.5s infinite;
        }

        @keyframes typing {
            0%, 60%, 100% { opacity: 1; }
            30% { opacity: 0.3; }
        }

        .chat-input {
            padding: 20px;
            background: white;
            border-top: 1px solid #e0e0e0;
        }

        .input-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .message-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 25px;
            outline: none;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .message-input:focus {
            border-color: #2196f3;
        }

        .send-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .send-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(33, 150, 243, 0.4);
        }

        .send-btn:active {
            transform: scale(0.95);
        }

        .send-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .send-btn svg {
            width: 20px;
            height: 20px;
        }

        .welcome-message {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .welcome-message h3 {
            margin-bottom: 10px;
            color: #333;
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: none;
            background: rgba(255,255,255,0.2);
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
            font-size: 18px;
        }

        .close-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .unread-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #f44336;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
        }

        @media (max-width: 480px) {
            .chat-container {
                height: 100vh;
                max-width: 100%;
                border-radius: 0;
            }

            .message {
                max-width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <button class="close-btn" onclick="window.close()">×</button>
            <h2>💬 Chat với Admin</h2>
            <div class="chat-status">
                <span class="online-indicator"></span>
                Admin đang online
            </div>
            <div style="font-size: 11px; margin-top: 5px; opacity: 0.8;">
                Bạn là: <?= htmlspecialchars($user_id) ?> 
                <?php 
                if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin') {
                    echo '<span style="color: #ff6b6b;">(Admin đang test)</span>';
                }
                ?>
            </div>
        </div>

        <div class="chat-messages" id="chatMessages">
            <div class="welcome-message">
                <h3>👋 Xin chào!</h3>
                <p>Tôi có thể giúp gì cho bạn hôm nay?</p>
            </div>
        </div>

        <div class="typing-indicator" id="typingIndicator">
            <span class="typing-dots">Admin đang nhập</span>
        </div>

        <div class="chat-input">
            <div class="input-group">
                <input type="text" class="message-input" id="messageInput" 
                       placeholder="Nhập tin nhắn..." 
                       onkeypress="if(event.key === 'Enter') sendMessage()">
                <button class="send-btn" id="sendBtn" onclick="sendMessage()">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentUser = '<?= $user_id ?>';
        let lastMessageId = 0;
        let isTyping = false;
        let typingTimeout;

        // Gửi tin nhắn
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message) return;
            
            const sendBtn = document.getElementById('sendBtn');
            sendBtn.disabled = true;
            
            // ✅ HIỂN THỊ NGAY
            addMessage(message, 'user');
            
            input.value = '';
            
            fetch('chat_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=send&message=${encodeURIComponent(message)}&receiver=admin`
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    lastMessageId = Math.max(lastMessageId, data.message_id || 0);
                    // ✅ Reload để sync
                    fetchMessages();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .finally(() => {
                sendBtn.disabled = false;
            });
        }

        // Thêm tin nhắn vào chat
        function addMessage(message, sender, timestamp = null) {
            const messagesContainer = document.getElementById('chatMessages');
            
            // Xóa welcome message nếu có
            const welcomeMsg = messagesContainer.querySelector('.welcome-message');
            if (welcomeMsg) {
                welcomeMsg.remove();
            }
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;
            
            const time = timestamp || new Date().toLocaleTimeString('vi-VN', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            messageDiv.innerHTML = `
                <div class="message-bubble">${escapeHtml(message)}</div>
                <div class="message-time">${time}</div>
            `;
            
            messagesContainer.appendChild(messageDiv);
            scrollToBottom();
            
            return messageDiv;
        }

        // Lấy tin nhắn mới
        function fetchMessages() {
            fetch('chat_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=get&receiver=admin&last_id=${lastMessageId}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        if (msg.id > lastMessageId) {
                            addMessage(
                                msg.message, 
                                msg.is_me ? 'user' : 'admin',
                                new Date(msg.created_at).toLocaleTimeString('vi-VN', { 
                                    hour: '2-digit', 
                                    minute: '2-digit' 
                                })
                            );
                            lastMessageId = msg.id;
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching messages:', error);
            });
        }

        // Kiểm tra tin nhắn chưa đọc
        function checkUnreadCount() {
            fetch('chat_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=get_unread_count'
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.unread_count > 0) {
                    // Có thể hiển thị badge trên nút chat
                    console.log('Unread messages:', data.unread_count);
                }
            });
        }

        // Scroll xuống dưới cùng
        function scrollToBottom() {
            const messagesContainer = document.getElementById('chatMessages');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // Escape HTML để tránh XSS
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // Hiển thị/ẩn typing indicator
        function showTypingIndicator() {
            document.getElementById('typingIndicator').classList.add('show');
            scrollToBottom();
        }

        function hideTypingIndicator() {
            document.getElementById('typingIndicator').classList.remove('show');
        }

        // Theo dõi việc nhập liệu
        document.getElementById('messageInput').addEventListener('input', function() {
            if (!isTyping) {
                isTyping = true;
                // Có thể gửi signal đến admin rằng user đang nhập
            }
            
            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => {
                isTyping = false;
                hideTypingIndicator();
            }, 1000);
        });

        // Focus vào input khi load trang
        document.getElementById('messageInput').focus();

        // Polling để lấy tin nhắn mới mỗi 3 giây (giảm tải server)
        setInterval(fetchMessages, 3000);

        // Kiểm tra tin nhắn chưa đọc mỗi 5 giây
        setInterval(checkUnreadCount, 5000);

        // Lấy tin nhắn ban đầu
        fetchMessages();

        // Xử lý khi đóng cửa sổ
        window.addEventListener('beforeunload', function() {
            // Có thể gửi signal rằng user đã rời đi
        });
    </script>
</body>
</html>
