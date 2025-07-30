<?php
session_start();

// 配置文件
$config = [
    'db_file' => 'data/messages.json',
    'log_file' => 'data/log.txt',
    'max_messages' => 100,
    'message_max_length' => 500,
    'rate_limit' => 3, // 每分钟最多发送消息数
    'admin_password' => '123456' // 建议修改
];

// 确保数据目录存在
if (!file_exists('data')) {
    mkdir('data', 0755, true);
}

// 初始化消息文件
if (!file_exists($config['db_file'])) {
    file_put_contents($config['db_file'], json_encode([]));
}

// 记录访问日志
function logAccess($message) {
    global $config;
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $logEntry = "[$timestamp] IP: $ip | $message | User-Agent: $userAgent\n";
    file_put_contents($config['log_file'], $logEntry, FILE_APPEND | LOCK_EX);
}

// 获取用户IP
function getUserIP() {
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

// 检查发送频率限制
function checkRateLimit() {
    global $config;
    $ip = getUserIP();
    $rateFile = "data/rate_$ip.json";
    $now = time();
    
    if (file_exists($rateFile)) {
        $rateData = json_decode(file_get_contents($rateFile), true);
        $rateData = array_filter($rateData, function($timestamp) use ($now) {
            return ($now - $timestamp) < 60; // 1分钟内的记录
        });
        
        if (count($rateData) >= $config['rate_limit']) {
            return false;
        }
    } else {
        $rateData = [];
    }
    
    $rateData[] = $now;
    file_put_contents($rateFile, json_encode($rateData), LOCK_EX);
    return true;
}

// 清理旧的频率限制文件
function cleanOldRateFiles() {
    $files = glob('data/rate_*.json');
    $now = time();
    foreach ($files as $file) {
        if ($now - filemtime($file) > 3600) { // 1小时后删除
            unlink($file);
        }
    }
}

// 处理AJAX请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'send_message':
            if (!checkRateLimit()) {
                echo json_encode(['success' => false, 'message' => '发送太频繁，请稍后再试']);
                exit;
            }
            
            $username = trim($_POST['username'] ?? '');
            $message = trim($_POST['message'] ?? '');
            
            if (empty($username) || empty($message)) {
                echo json_encode(['success' => false, 'message' => '用户名和消息内容不能为空']);
                exit;
            }
            
            if (strlen($username) > 20) {
                echo json_encode(['success' => false, 'message' => '用户名长度不能超过20个字符']);
                exit;
            }
            
            if (strlen($message) > $config['message_max_length']) {
                echo json_encode(['success' => false, 'message' => '消息长度不能超过' . $config['message_max_length'] . '个字符']);
                exit;
            }
            
            // 过滤恶意内容
            $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
            $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
            
            // 读取现有消息
            $messages = json_decode(file_get_contents($config['db_file']), true) ?: [];
            
            // 添加新消息
            $newMessage = [
                'id' => uniqid(),
                'username' => $username,
                'message' => $message,
                'timestamp' => time(),
                'ip' => getUserIP()
            ];
            
            array_unshift($messages, $newMessage);
            
            // 限制消息数量
            if (count($messages) > $config['max_messages']) {
                $messages = array_slice($messages, 0, $config['max_messages']);
            }
            
            file_put_contents($config['db_file'], json_encode($messages), LOCK_EX);
            
            logAccess("Message sent by $username");
            echo json_encode(['success' => true, 'message' => '消息发送成功']);
            break;
            
        case 'get_messages':
            $messages = json_decode(file_get_contents($config['db_file']), true) ?: [];
            echo json_encode(['success' => true, 'messages' => $messages]);
            break;
            
        case 'delete_message':
            $messageId = $_POST['message_id'] ?? '';
            $adminPassword = $_POST['admin_password'] ?? '';
            
            if ($adminPassword !== $config['admin_password']) {
                echo json_encode(['success' => false, 'message' => '管理员密码错误']);
                exit;
            }
            
            $messages = json_decode(file_get_contents($config['db_file']), true) ?: [];
            $messages = array_filter($messages, function($msg) use ($messageId) {
                return $msg['id'] !== $messageId;
            });
            
            file_put_contents($config['db_file'], json_encode(array_values($messages)), LOCK_EX);
            
            logAccess("Message deleted: $messageId");
            echo json_encode(['success' => true, 'message' => '消息删除成功']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => '无效的操作']);
    }
    exit;
}

// 清理旧文件
cleanOldRateFiles();
logAccess("Page accessed");
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VenlanChat - 实时聊天室</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .container {
            max-width: 1400px; /* Increased max-width for larger layout */
            margin: 0 auto;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .chat-container {
            display: flex;
            flex: 1;
            gap: 30px; /* Increased gap for better spacing */
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px; /* Increased padding for more spacious feel */
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }

        .chat-messages {
            flex: 3; /* Increased flex value to make chat window larger */
            display: flex;
            flex-direction: column;
        }

        .messages-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .messages-title {
            font-size: 1.5em;
            font-weight: bold;
            color: #555;
        }

        .online-count {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9em;
        }

        .messages-list {
            flex: 1;
            max-height: 600px; /* Increased max-height for larger chat window */
            overflow-y: auto;
            padding: 15px; /* Increased padding */
            background: #f9f9f9;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .message {
            background: white;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            position: relative;
        }

        .message:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .username {
            font-weight: bold;
            color: #667eea;
            font-size: 1.1em;
        }

        .timestamp {
            color: #888;
            font-size: 0.85em;
        }

        .message-content {
            line-height: 1.6;
            color: #444;
        }

        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff4757;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 12px;
        }

        .message:hover .delete-btn {
            opacity: 1;
        }

        .chat-input {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        .input-group input, .input-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .input-group input:focus, .input-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .send-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
            width: 100%;
        }

        .send-btn:hover {
            transform: translateY(-2px);
        }

        .sidebar {
            width: 350px; /* Slightly wider sidebar for better spacing */
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 20px;
            height: fit-content;
        }

        .sidebar h3 {
            margin-bottom: 15px;
            color: #555;
        }

        .admin-panel {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }

        .admin-input {
            margin-bottom: 10px;
        }

        .admin-input input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            z-index: 1000;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s;
        }

        .notification.show {
            opacity: 1;
            transform: translateX(0);
        }

        .notification.success {
            background: #4CAF50;
        }

        .notification.error {
            background: #f44336;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
            color: #667eea;
        }

        .emoji-picker {
            display: none;
            position: absolute;
            bottom: 60px;
            right: 20px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 100;
        }

        .emoji {
            cursor: pointer;
            font-size: 20px;
            margin: 5px;
            transition: transform 0.2s;
        }

        .emoji:hover {
            transform: scale(1.2);
        }

        @media (max-width: 768px) {
            .chat-container {
                flex-direction: column;
                padding: 15px;
            }
            
            .sidebar {
                width: 100%;
                margin-top: 20px;
            }
            
            .header h1 {
                font-size: 2em;
            }

            .chat-messages {
                flex: 1; /* Ensure full width on mobile */
            }

            .messages-list {
                max-height: 400px; /* Adjusted for mobile */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-comments"></i> VenlanChat</h1>
            <p>实时聊天室 - 与朋友畅聊无阻</p>
        </div>
        
        <div class="chat-container">
            <div class="chat-messages">
                <div class="messages-header">
                    <div class="messages-title">
                        <i class="fas fa-comment-dots"></i> 聊天记录
                    </div>
                    <div class="online-count">
                        <i class="fas fa-users"></i> 在线聊天
                    </div>
                </div>
                
                <div class="messages-list" id="messagesList">
                    <div class="loading" id="loading">
                        <i class="fas fa-spinner fa-spin"></i> 加载中...
                    </div>
                </div>
                
                <div class="chat-input">
                    <form id="messageForm">
                        <div class="input-group">
                            <label for="message">
                                <i class="fas fa-comment"></i> 消息内容
                            </label>
                            <textarea id="message" name="message" rows="3" maxlength="500" required placeholder="输入你的消息..."></textarea>
                        </div>
                        
                        <button type="submit" class="send-btn">
                            <i class="fas fa-paper-plane"></i> 发送消息
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="sidebar">
                <h3><i class="fas fa-info-circle"></i> 使用说明</h3>
                <ul style="padding-left: 20px; line-height: 1.8;">
                    <li>输入用户名和消息内容</li>
                    <li>点击发送消息按钮</li>
                    <li>消息会实时显示在聊天区域</li>
                    <li>管理员可以删除不当消息</li>
                </ul>
                
                <div class="admin-panel">
                    <h3><i class="fas fa-shield-alt"></i> 管理面板</h3>
                    <div class="admin-input">
                        <input type="password" id="adminPassword" placeholder="管理员密码">
                    </div>
                    <p style="font-size: 0.9em; color: #666;">
                        输入管理员密码后，可以点击消息上的删除按钮删除消息
                    </p>
                </div>
                
                <div class="username-input" style="margin-top: 20px;">
                    <h3><i class="fas fa-user"></i> 用户名</h3>
                    <div class="input-group">
                        <input type="text" id="username" name="username" maxlength="20" required>
                    </div>
                </div>
                
                <div style="margin-top: 20px; text-align: center; font-size: 0.9em; color: #666;">
                    <i class="fas fa-heart" style="color: #e74c3c;"></i> 
                    VenlanChat v2.0
                </div>
            </div>
        </div>
    </div>

    <div class="notification" id="notification"></div>

    <script>
        let lastMessageCount = 0;
        let adminPassword = '';

        // 获取管理员密码
        document.getElementById('adminPassword').addEventListener('input', function() {
            adminPassword = this.value;
        });

        // 显示通知
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // 格式化时间
        function formatTime(timestamp) {
            const date = new Date(timestamp * 1000);
            const now = new Date();
            const diff = now - date;
            
            if (diff < 60000) { // 小于1分钟
                return '刚刚';
            } else if (diff < 3600000) { // 小于1小时
                return Math.floor(diff / 60000) + '分钟前';
            } else if (diff < 86400000) { // 小于24小时
                return Math.floor(diff / 3600000) + '小时前';
            } else {
                return date.toLocaleDateString() + ' ' + date.toLocaleTimeString().substr(0, 5);
            }
        }

        // 加载消息
        function loadMessages() {
            const loading = document.getElementById('loading');
            loading.style.display = 'block';
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_messages'
            })
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                
                if (data.success) {
                    displayMessages(data.messages);
                }
            })
            .catch(error => {
                loading.style.display = 'none';
                console.error('Error:', error);
            });
        }

        // 显示消息
        function displayMessages(messages) {
            const messagesList = document.getElementById('messagesList');
            const loading = document.getElementById('loading');
            
            messagesList.innerHTML = '';
            messagesList.appendChild(loading);
            
            if (messages.length === 0) {
                messagesList.innerHTML = '<div style="text-align: center; color: #888; padding: 40px;">暂无消息，快来发送第一条消息吧！</div>';
                return;
            }
            
            messages.forEach(msg => {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message';
                messageDiv.innerHTML = `
                    <div class="message-header">
                        <span class="username">${msg.username}</span>
                        <span class="timestamp">${formatTime(msg.timestamp)}</span>
                    </div>
                    <div class="message-content">${msg.message}</div>
                    <button class="delete-btn" onclick="deleteMessage('${msg.id}')" title="删除消息">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                messagesList.appendChild(messageDiv);
            });
            
            // 如果有新消息，滚动到顶部
            if (messages.length > lastMessageCount) {
                messagesList.scrollTop = 0;
            }
            lastMessageCount = messages.length;
        }

        // 发送消息
        document.getElementById('messageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value.trim();
            const message = document.getElementById('message').value.trim();
            
            if (!username || !message) {
                showNotification('用户名和消息内容不能为空', 'error');
                return;
            }
            
            const sendBtn = document.querySelector('.send-btn');
            const originalText = sendBtn.innerHTML;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 发送中...';
            sendBtn.disabled = true;
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send_message&username=${encodeURIComponent(username)}&message=${encodeURIComponent(message)}`
            })
            .then(response => response.json())
            .then(data => {
                sendBtn.innerHTML = originalText;
                sendBtn.disabled = false;
                
                if (data.success) {
                    document.getElementById('message').value = '';
                    showNotification('消息发送成功');
                    loadMessages();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                sendBtn.innerHTML = originalText;
                sendBtn.disabled = false;
                showNotification('发送失败，请重试', 'error');
                console.error('Error:', error);
            });
        });

        // 删除消息
        function deleteMessage(messageId) {
            if (!adminPassword) {
                showNotification('请先输入管理员密码', 'error');
                return;
            }
            
            if (!confirm('确定要删除这条消息吗？')) {
                return;
            }
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete_message&message_id=${messageId}&admin_password=${encodeURIComponent(adminPassword)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('消息删除成功');
                    loadMessages();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('删除失败，请重试', 'error');
                console.error('Error:', error);
            });
        }

        // 自动刷新消息
        function autoRefresh() {
            loadMessages();
        }

        // 初始化
        document.addEventListener('DOMContentLoaded', function() {
            loadMessages();
            
            // 每5秒自动刷新消息
            setInterval(autoRefresh, 5000);
            
            // 保存用户名到本地存储
            const usernameInput = document.getElementById('username');
            const savedUsername = localStorage.getItem('venlanchat_username');
            if (savedUsername) {
                usernameInput.value = savedUsername;
            }
            
            usernameInput.addEventListener('input', function() {
                localStorage.setItem('venlanchat_username', this.value);
            });
        });

        // 按回车发送消息（Ctrl+Enter换行）
        document.getElementById('message').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.ctrlKey) {
                e.preventDefault();
                document.getElementById('messageForm').dispatchEvent(new Event('submit'));
            }
        });
    </script>
</body>
</html>