<?php
session_start();

// 配置
$adminPassword = 'admin123'; // 请修改为安全密码
$dataDir = 'data/';
$messagesFile = $dataDir . 'messages.json';
$logFile = $dataDir . 'log.txt';

// 验证管理员登录
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        if ($_POST['admin_password'] === $adminPassword) {
            $_SESSION['admin_logged_in'] = true;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $loginError = '密码错误';
        }
    }
    
    // 显示登录页面
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>VenlanChat - 管理员登录</title>
        <style>
            body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
            .login-container { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
            .login-header { text-align: center; margin-bottom: 30px; }
            .login-header h1 { color: #333; margin-bottom: 10px; }
            .form-group { margin-bottom: 20px; }
            .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
            .form-group input { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; box-sizing: border-box; }
            .form-group input:focus { outline: none; border-color: #667eea; }
            .login-btn { width: 100%; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; padding: 12px; border-radius: 8px; font-size: 16px; cursor: pointer; }
            .login-btn:hover { opacity: 0.9; }
            .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
            .back-link { text-align: center; margin-top: 20px; }
            .back-link a { color: #667eea; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="login-header">
                <h1>🔐 管理员登录</h1>
                <p>VenlanChat 管理面板</p>
            </div>
            
            <?php if (isset($loginError)): ?>
                <div class="error"><?php echo htmlspecialchars($loginError); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="admin_password">管理员密码</label>
                    <input type="password" id="admin_password" name="admin_password" required>
                </div>
                <button type="submit" name="login" class="login-btn">登录</button>
            </form>
            
            <div class="back-link">
                <a href="index.php">← 返回聊天室</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 处理管理操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'clear_messages':
            file_put_contents($messagesFile, json_encode([]));
            $success = '所有消息已清空';
            break;
            
        case 'clear_logs':
            file_put_contents($logFile, '');
            $success = '访问日志已清空';
            break;
            
        case 'logout':
            session_destroy();
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
            break;
    }
}

// 获取统计数据
function getStats() {
    global $messagesFile, $logFile, $dataDir;
    
    $stats = [
        'total_messages' => 0,
        'total_access' => 0,
        'unique_users' => 0,
        'unique_ips' => 0,
        'data_size' => 0,
        'recent_messages' => 0,
        'recent_access' => 0
    ];
    
    // 消息统计
    if (file_exists($messagesFile)) {
        $messages = json_decode(file_get_contents($messagesFile), true) ?: [];
        $stats['total_messages'] = count($messages);
        $stats['data_size'] += filesize($messagesFile);
        
        $uniqueUsers = [];
        $oneHourAgo = time() - 3600;
        
        foreach ($messages as $msg) {
            $uniqueUsers[$msg['username']] = true;
            if ($msg['timestamp'] > $oneHourAgo) {
                $stats['recent_messages']++;
            }
        }
        $stats['unique_users'] = count($uniqueUsers);
    }
    
    // 访问日志统计
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        $logLines = array_filter(explode("\n", $logContent));
        $stats['total_access'] = count($logLines);
        $stats['data_size'] += filesize($logFile);
        
        $uniqueIPs = [];
        $oneHourAgo = time() - 3600;
        
        foreach ($logLines as $line) {
            if (preg_match('/\[(.*?)\].*?IP: (.*?) \|/', $line, $matches)) {
                $timestamp = strtotime($matches[1]);
                $ip = trim($matches[2]);
                $uniqueIPs[$ip] = true;
                if ($timestamp > $oneHourAgo) {
                    $stats['recent_access']++;
                }
            }
        }
        $stats['unique_ips'] = count($uniqueIPs);
    }
    
    // 计算数据目录总大小
    if (is_dir($dataDir)) {
        $files = glob($dataDir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $stats['data_size'] += filesize($file);
            }
        }
    }
    
    return $stats;
}

$stats = getStats();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VenlanChat - 管理面板</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f6fa; color: #333; }
        
        .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 20px 0; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .header .container { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .header h1 { font-size: 1.8em; }
        .header .user-info { display: flex; align-items: center; gap: 15px; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 30px 20px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); text-align: center; }
        .stat-card .icon { font-size: 2.5em; margin-bottom: 15px; }
        .stat-card .number { font-size: 2em; font-weight: bold; margin-bottom: 5px; }
        .stat-card .label { color: #666; font-size: 0.9em; }
        
        .stat-card.messages .icon { color: #4CAF50; }
        .stat-card.users .icon { color: #2196F3; }
        .stat-card.access .icon { color: #FF9800; }
        .stat-card.size .icon { color: #9C27B0; }
        
        .actions-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .action-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .action-card h3 { margin-bottom: 15px; color: #333; }
        .action-card p { color: #666; margin-bottom: 20px; line-height: 1.6; }
        
        .btn { display: inline-block; padding: 12px 24px; border: none; border-radius: 8px; text-decoration: none; font-weight: bold; cursor: pointer; transition: all 0.3s; }
        .btn-primary { background: #667eea; color: white; }
        .btn-danger { background: #f44336; color: white; }
        .btn-success { background: #4CAF50; color: white; }
        .btn-info { background: #17a2b8; color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        
        .recent-activity { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .recent-activity h3 { margin-bottom: 20px; }
        .activity-item { padding: 12px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .activity-item:last-child { border-bottom: none; }
        .activity-text { flex: 1; }
        .activity-time { color: #666; font-size: 0.9em; }
        
        .success-message { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb; }
        
        .system-info { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); margin-top: 30px; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .info-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .info-item:last-child { border-bottom: none; }
        
        @media (max-width: 768px) {
            .header .container { flex-direction: column; gap: 15px; text-align: center; }
            .stats-grid, .actions-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1><i class="fas fa-cogs"></i> VenlanChat 管理面板</h1>
            <div class="user-info">
                <span><i class="fas fa-user-shield"></i> 管理员</span>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-danger" style="padding: 8px 16px; font-size: 0.9em;">
                        <i class="fas fa-sign-out-alt"></i> 退出
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($success)): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- 统计卡片 -->
        <div class="stats-grid">
            <div class="stat-card messages">
                <div class="icon"><i class="fas fa-comments"></i></div>
                <div class="number"><?php echo $stats['total_messages']; ?></div>
                <div class="label">总消息数</div>
                <div style="font-size: 0.8em; color: #4CAF50; margin-top: 5px;">
                    近1小时: <?php echo $stats['recent_messages']; ?> 条
                </div>
            </div>
            
            <div class="stat-card users">
                <div class="icon"><i class="fas fa-users"></i></div>
                <div class="number"><?php echo $stats['unique_users']; ?></div>
                <div class="label">独立用户</div>
                <div style="font-size: 0.8em; color: #2196F3; margin-top: 5px;">
                    独立IP: <?php echo $stats['unique_ips']; ?>
                </div>
            </div>
            
            <div class="stat-card access">
                <div class="icon"><i class="fas fa-chart-line"></i></div>
                <div class="number"><?php echo $stats['total_access']; ?></div>
                <div class="label">总访问次数</div>
                <div style="font-size: 0.8em; color: #FF9800; margin-top: 5px;">
                    近1小时: <?php echo $stats['recent_access']; ?> 次
                </div>
            </div>
            
            <div class="stat-card size">
                <div class="icon"><i class="fas fa-database"></i></div>
                <div class="number"><?php echo round($stats['data_size'] / 1024, 2); ?></div>
                <div class="label">数据大小 (KB)</div>
                <div style="font-size: 0.8em; color: #9C27B0; margin-top: 5px;">
                    磁盘占用
                </div>
            </div>
        </div>

        <!-- 操作面板 -->
        <div class="actions-grid">
            <div class="action-card">
                <h3><i class="fas fa-eye"></i> 查看与监控</h3>
                <p>查看聊天室状态、访问日志和用户活动情况</p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-comments"></i> 进入聊天室
                </a>
                <a href="log.php" class="btn btn-info" style="margin-left: 10px;">
                    <i class="fas fa-list"></i> 查看日志
                </a>
            </div>
            
            <div class="action-card">
                <h3><i class="fas fa-trash"></i> 数据清理</h3>
                <p>清空聊天记录或访问日志，释放存储空间</p>
                <form method="POST" style="display: inline;" onsubmit="return confirm('确定要清空所有消息吗？此操作不可恢复！');">
                    <input type="hidden" name="action" value="clear_messages">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-comments"></i> 清空消息
                    </button>
                </form>
                <form method="POST" style="display: inline; margin-left: 10px;" onsubmit="return confirm('确定要清空访问日志吗？');">
                    <input type="hidden" name="action" value="clear_logs">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-file-alt"></i> 清空日志
                    </button>
                </form>
            </div>
            
            <div class="action-card">
                <h3><i class="fas fa-download"></i> 数据备份</h3>
                <p>下载聊天记录和日志文件进行备份</p>
                <a href="<?php echo $messagesFile; ?>" download="messages_backup.json" class="btn btn-success">
                    <i class="fas fa-download"></i> 下载消息
                </a>
                <a href="<?php echo $logFile; ?>" download="access_log.txt" class="btn btn-success" style="margin-left: 10px;">
                    <i class="fas fa-download"></i> 下载日志
                </a>
            </div>
        </div>

        <!-- 最近活动 -->
        <div class="recent-activity">
            <h3><i class="fas fa-clock"></i> 最近活动</h3>
            <?php
            // 显示最近的一些活动
            $recentMessages = [];
            if (file_exists($messagesFile)) {
                $messages = json_decode(file_get_contents($messagesFile), true) ?: [];
                $recentMessages = array_slice($messages, 0, 5);
            }
            
            if (empty($recentMessages)) {
                echo '<p style="color: #666; text-align: center; padding: 20px;">暂无最近活动</p>';
            } else {
                foreach ($recentMessages as $msg) {
                    echo '<div class="activity-item">';
                    echo '<div class="activity-text">';
                    echo '<strong>' . htmlspecialchars($msg['username']) . '</strong> 发送了消息';
                    echo '<div style="color: #666; font-size: 0.9em; margin-top: 5px;">' . mb_substr(htmlspecialchars($msg['message']), 0, 50) . (mb_strlen($msg['message']) > 50 ? '...' : '') . '</div>';
                    echo '</div>';
                    echo '<div class="activity-time">' . date('m-d H:i', $msg['timestamp']) . '</div>';
                    echo '</div>';
                }
            }
            ?>
        </div>

        <!-- 系统信息 -->
        <div class="system-info">
            <h3><i class="fas fa-info-circle"></i> 系统信息</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span>PHP版本</span>
                    <span><?php echo phpversion(); ?></span>
                </div>
                <div class="info-item">
                    <span>服务器时间</span>
                    <span><?php echo date('Y-m-d H:i:s'); ?></span>
                </div>
                <div class="info-item">
                    <span>系统版本</span>
                    <span>VenlanChat v2.0</span>
                </div>
                <div class="info-item">
                    <span>运行状态</span>
                    <span style="color: #4CAF50;"><i class="fas fa-circle"></i> 正常运行</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 自动刷新统计数据
        setTimeout(function() {
            location.reload();
        }, 60000); // 1分钟刷新一次
        
        // 实时时间显示
        function updateTime() {
            const now = new Date();
            const timeStr = now.getFullYear() + '-' + 
                          String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                          String(now.getDate()).padStart(2, '0') + ' ' +
                          String(now.getHours()).padStart(2, '0') + ':' + 
                          String(now.getMinutes()).padStart(2, '0') + ':' + 
                          String(now.getSeconds()).padStart(2, '0');
            
            const timeElements = document.querySelectorAll('.info-item span:last-child');
            timeElements.forEach(el => {
                if (el.previousElementSibling.textContent === '服务器时间') {
                    el.textContent = timeStr;
                }
            });
        }
        
        setInterval(updateTime, 1000);
    </script>
</body>
</html>