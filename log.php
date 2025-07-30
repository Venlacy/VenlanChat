<?php
// 简单的日志查看器
$logFile = 'data/log.txt';
$adminPassword = 'admin123'; // 应该与index.php中的密码一致

// 验证管理员密码
if (!isset($_POST['admin_password']) || $_POST['admin_password'] !== $adminPassword) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $error = '管理员密码错误';
    }
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VenlanChat - 日志查看器</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; }
        .submit-btn { background: #667eea; color: white; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer; }
        .error { color: #f44336; background: #ffebee; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .back-btn { display: inline-block; background: #4CAF50; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">← 返回聊天室</a>
        <h1>🔐 日志查看器</h1>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="admin_password">管理员密码:</label>
                <input type="password" id="admin_password" name="admin_password" required>
            </div>
            <button type="submit" class="submit-btn">查看日志</button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}

// 显示日志内容
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VenlanChat - 访问日志</title>
    <style>
        body { font-family: 'Courier New', monospace; background: #1e1e1e; color: #d4d4d4; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #333; padding: 20px; border-radius: 8px 8px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .log-content { background: #2d2d2d; padding: 20px; border-radius: 0 0 8px 8px; max-height: 600px; overflow-y: auto; }
        .log-line { padding: 5px 0; border-bottom: 1px solid #444; }
        .log-line:hover { background: #3d3d3d; }
        .timestamp { color: #4CAF50; }
        .ip { color: #2196F3; }
        .message { color: #FFC107; }
        .back-btn { background: #4CAF50; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; }
        .clear-btn { background: #f44336; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; margin-left: 10px; }
        .stats { display: flex; gap: 20px; margin-bottom: 20px; }
        .stat-item { background: #333; padding: 15px; border-radius: 8px; text-align: center; flex: 1; }
        .stat-number { font-size: 1.5em; font-weight: bold; color: #4CAF50; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 VenlanChat 访问日志</h1>
            <div>
                <a href="index.php" class="back-btn">← 返回聊天室</a>
                <a href="?clear=1&admin_password=<?php echo urlencode($_POST['admin_password']); ?>" class="clear-btn" onclick="return confirm('确定要清空日志吗？')">🗑️ 清空日志</a>
            </div>
        </div>

        <?php
        // 处理清空日志
        if (isset($_GET['clear']) && $_GET['admin_password'] === $adminPassword) {
            file_put_contents($logFile, '');
            echo '<div style="background: #4CAF50; color: white; padding: 15px; margin-bottom: 20px; border-radius: 8px;">日志已清空</div>';
        }

        // 读取日志
        if (file_exists($logFile)) {
            $logContent = file_get_contents($logFile);
            $logLines = array_filter(explode("\n", $logContent));
            
            // 统计信息
            $totalAccess = count($logLines);
            $uniqueIPs = [];
            $recentAccess = 0;
            $oneHourAgo = time() - 3600;
            
            foreach ($logLines as $line) {
                if (preg_match('/\[(.*?)\].*?IP: (.*?) \|/', $line, $matches)) {
                    $timestamp = strtotime($matches[1]);
                    $ip = trim($matches[2]);
                    $uniqueIPs[$ip] = true;
                    if ($timestamp > $oneHourAgo) {
                        $recentAccess++;
                    }
                }
            }
            ?>
            
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $totalAccess; ?></div>
                    <div>总访问次数</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo count($uniqueIPs); ?></div>
                    <div>独立访客</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $recentAccess; ?></div>
                    <div>近1小时访问</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo file_exists($logFile) ? round(filesize($logFile) / 1024, 2) : 0; ?> KB</div>
                    <div>日志文件大小</div>
                </div>
            </div>

            <div class="log-content">
                <?php
                // 显示最新的100条日志
                $displayLines = array_slice(array_reverse($logLines), 0, 100);
                
                if (empty($displayLines)) {
                    echo '<div style="text-align: center; color: #888; padding: 40px;">暂无日志记录</div>';
                } else {
                    foreach ($displayLines as $line) {
                        if (empty(trim($line))) continue;
                        
                        // 解析日志行
                        $line = htmlspecialchars($line);
                        $line = preg_replace('/\[(.*?)\]/', '<span class="timestamp">[$1]</span>', $line);
                        $line = preg_replace('/IP: ([^|]+)/', 'IP: <span class="ip">$1</span>', $line);
                        $line = preg_replace('/\| ([^|]+) \|/', '| <span class="message">$1</span> |', $line);
                        
                        echo '<div class="log-line">' . $line . '</div>';
                    }
                }
                ?>
            </div>
        <?php } else { ?>
            <div class="log-content">
                <div style="text-align: center; color: #888; padding: 40px;">
                    日志文件不存在或为空
                </div>
            </div>
        <?php } ?>
    </div>

    <script>
        // 自动刷新日志
        setTimeout(function() {
            location.reload();
        }, 30000); // 30秒刷新一次
    </script>
</body>
</html>