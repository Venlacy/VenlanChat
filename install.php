<?php
// 安装程序 - 只需运行一次
$installFile = 'data/.installed';

// 如果已经安装过，显示提示
if (file_exists($installFile)) {
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>VenlanChat - 已安装</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 40px; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
            .success { color: #4CAF50; text-align: center; }
            .button { display: inline-block; background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
            .button:hover { background: #5a6fd8; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1 class="success">✅ VenlanChat 已经安装完成！</h1>
            <p>系统已经成功安装并配置完成。</p>
            <p><strong>安装信息：</strong></p>
            <ul>
                <li>安装时间：<?php echo date('Y-m-d H:i:s', filemtime($installFile)); ?></li>
                <li>数据目录：data/</li>
                <li>消息存储：data/messages.json</li>
                <li>访问日志：data/log.txt</li>
            </ul>
            <div style="text-align: center; margin-top: 30px;">
                <a href="index.php" class="button">进入聊天室</a>
                <a href="?reinstall=1" class="button" style="background: #f44336;">重新安装</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 处理重新安装
if (isset($_GET['reinstall'])) {
    if (file_exists($installFile)) {
        unlink($installFile);
    }
    // 清理旧数据
    $files = ['data/messages.json', 'data/log.txt'];
    foreach ($files as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }
    // 删除频率限制文件
    $rateFiles = glob('data/rate_*.json');
    foreach ($rateFiles as $file) {
        unlink($file);
    }
}

// 处理安装表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // 检查目录权限
    if (!is_writable('.')) {
        $errors[] = '当前目录不可写，请检查目录权限';
    }
    
    // 创建数据目录
    if (!file_exists('data')) {
        if (!mkdir('data', 0755, true)) {
            $errors[] = '无法创建data目录，请检查权限';
        }
    }
    
    if (!is_writable('data')) {
        $errors[] = 'data目录不可写，请检查权限';
    }
    
    // 获取配置
    $adminPassword = trim($_POST['admin_password'] ?? '');
    $maxMessages = intval($_POST['max_messages'] ?? 100);
    $messageMaxLength = intval($_POST['message_max_length'] ?? 500);
    $rateLimit = intval($_POST['rate_limit'] ?? 3);
    
    // 验证配置
    if (empty($adminPassword)) {
        $errors[] = '管理员密码不能为空';
    }
    
    if ($maxMessages < 10 || $maxMessages > 1000) {
        $errors[] = '最大消息数应在10-1000之间';
    }
    
    if ($messageMaxLength < 50 || $messageMaxLength > 2000) {
        $errors[] = '消息最大长度应在50-2000之间';
    }
    
    if ($rateLimit < 1 || $rateLimit > 10) {
        $errors[] = '发送频率限制应在1-10之间';
    }
    
    // 如果没有错误，执行安装
    if (empty($errors)) {
        // 创建配置数组
        $config = [
            'admin_password' => $adminPassword,
            'max_messages' => $maxMessages,
            'message_max_length' => $messageMaxLength,
            'rate_limit' => $rateLimit,
            'install_time' => date('Y-m-d H:i:s'),
            'version' => '2.0'
        ];
        
        // 保存配置到index.php（需要手动修改）
        // 或者创建单独的配置文件
        file_put_contents('data/config.json', json_encode($config, JSON_PRETTY_PRINT));
        
        // 初始化消息文件
        file_put_contents('data/messages.json', json_encode([]));
        
        // 初始化日志文件
        $logEntry = "[" . date('Y-m-d H:i:s') . "] System installed successfully\n";
        file_put_contents('data/log.txt', $logEntry);
        
        // 创建安装标记文件
        file_put_contents($installFile, date('Y-m-d H:i:s'));
        
        // 显示成功页面
        ?>
        <!DOCTYPE html>
        <html lang="zh-CN">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>VenlanChat - 安装成功</title>
            <style>
                body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); margin: 0; padding: 40px; min-height: 100vh; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
                .success { color: #4CAF50; text-align: center; margin-bottom: 30px; }
                .success h1 { font-size: 2.5em; margin-bottom: 10px; }
                .config-info { background: #f9f9f9; padding: 20px; border-radius: 10px; margin: 20px 0; }
                .button { display: inline-block; background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; margin: 10px 5px; font-weight: bold; }
                .button:hover { transform: translateY(-2px); }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="success">
                    <h1>🎉 安装成功！</h1>
                    <p>VenlanChat 已经成功安装并配置完成</p>
                </div>
                
                <div class="config-info">
                    <h3>📋 配置信息</h3>
                    <ul>
                        <li><strong>管理员密码：</strong> <?php echo str_repeat('*', strlen($adminPassword)); ?></li>
                        <li><strong>最大消息数：</strong> <?php echo $maxMessages; ?></li>
                        <li><strong>消息最大长度：</strong> <?php echo $messageMaxLength; ?> 字符</li>
                        <li><strong>发送频率限制：</strong> 每分钟 <?php echo $rateLimit; ?> 条</li>
                        <li><strong>安装时间：</strong> <?php echo $config['install_time']; ?></li>
                    </ul>
                </div>
                
                <div class="warning">
                    <strong>⚠️ 重要提示：</strong><br>
                    1. 请记住您的管理员密码，用于删除不当消息<br>
                    2. 如需修改配置，请编辑 index.php 文件中的 $config 数组<br>
                    3. 建议定期备份 data/ 目录下的数据文件<br>
                    4. 为了安全，建议安装完成后删除 install.php 文件
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <a href="index.php" class="button">🚀 进入聊天室</a>
                    <a href="data/log.txt" class="button" target="_blank">📝 查看日志</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// 检查系统要求
$phpVersion = phpversion();
$requirements = [
    'PHP版本' => version_compare($phpVersion, '7.0', '>=') ? '✅' : '❌',
    'JSON支持' => function_exists('json_encode') ? '✅' : '❌',
    '文件写入权限' => is_writable('.') ? '✅' : '❌',
    '目录创建权限' => is_writable('.') ? '✅' : '❌'
];

$canInstall = !in_array('❌', $requirements);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VenlanChat - 安装程序</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            padding: 20px; 
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: rgba(255, 255, 255, 0.95); 
            border-radius: 20px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1); 
            overflow: hidden;
        }
        .header { 
            background: linear-gradient(135deg, #667eea, #764ba2); 
            color: white; 
            padding: 40px; 
            text-align: center; 
        }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; }
        .content { padding: 40px; }
        .section { margin-bottom: 30px; }
        .section h2 { color: #333; margin-bottom: 15px; font-size: 1.3em; }
        .requirements { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .req-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .req-item:last-child { border-bottom: none; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        .form-group input, .form-group textarea { 
            width: 100%; 
            padding: 12px; 
            border: 2px solid #e0e0e0; 
            border-radius: 8px; 
            font-size: 14px; 
            transition: border-color 0.3s; 
        }
        .form-group input:focus, .form-group textarea:focus { 
            outline: none; 
            border-color: #667eea; 
        }
        .form-group .help { font-size: 0.9em; color: #666; margin-top: 5px; }
        .install-btn { 
            background: linear-gradient(135deg, #667eea, #764ba2); 
            color: white; 
            border: none; 
            padding: 15px 40px; 
            border-radius: 25px; 
            font-size: 16px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: transform 0.2s; 
            width: 100%; 
        }
        .install-btn:hover { transform: translateY(-2px); }
        .install-btn:disabled { 
            background: #ccc; 
            cursor: not-allowed; 
            transform: none; 
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            border: 1px solid #f5c6cb; 
        }
        .two-column { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 768px) {
            .two-column { grid-template-columns: 1fr; }
            .container { margin: 10px; border-radius: 15px; }
            .header, .content { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 VenlanChat 安装程序</h1>
            <p>欢迎使用 VenlanChat 实时聊天系统</p>
        </div>
        
        <div class="content">
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <strong>❌ 安装失败，请检查以下问题：</strong>
                    <ul style="margin-top: 10px; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="section">
                <h2>📋 系统要求检查</h2>
                <div class="requirements">
                    <?php foreach ($requirements as $req => $status): ?>
                        <div class="req-item">
                            <span><?php echo $req; ?></span>
                            <span style="font-size: 1.2em;"><?php echo $status; ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div class="req-item">
                        <span>PHP版本</span>
                        <span><?php echo $phpVersion; ?></span>
                    </div>
                </div>
            </div>
            
            <?php if ($canInstall): ?>
                <form method="POST">
                    <div class="section">
                        <h2>⚙️ 基本配置</h2>
                        
                        <div class="form-group">
                            <label for="admin_password">管理员密码 *</label>
                            <input type="password" id="admin_password" name="admin_password" required>
                            <div class="help">用于删除不当消息的管理员密码，请妥善保管</div>
                        </div>
                    </div>
                    
                    <div class="section">
                        <h2>🔧 高级配置</h2>
                        
                        <div class="two-column">
                            <div class="form-group">
                                <label for="max_messages">最大消息数</label>
                                <input type="number" id="max_messages" name="max_messages" value="100" min="10" max="1000">
                                <div class="help">系统最多保存的消息数量 (10-1000)</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="message_max_length">消息最大长度</label>
                                <input type="number" id="message_max_length" name="message_max_length" value="500" min="50" max="2000">
                                <div class="help">单条消息最大字符数 (50-2000)</div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="rate_limit">发送频率限制</label>
                            <input type="number" id="rate_limit" name="rate_limit" value="3" min="1" max="10">
                            <div class="help">每分钟最多发送消息数量 (1-10)</div>
                        </div>
                    </div>
                    
                    <div class="section">
                        <button type="submit" class="install-btn">
                            🚀 开始安装 VenlanChat
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="error">
                    <strong>❌ 系统要求检查失败</strong><br>
                    请解决上述问题后再次运行安装程序。
                </div>
            <?php endif; ?>
            
            <div class="section" style="text-align: center; color: #666; font-size: 0.9em; border-top: 1px solid #eee; padding-top: 20px; margin-top: 30px;">
                <p>VenlanChat v2.0 - 现代化实时聊天系统</p>
                <p>支持实时消息、管理员管理、频率限制等功能</p>
            </div>
        </div>
    </div>
</body>
</html>