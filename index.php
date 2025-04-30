
<?php
// 记录访问日志功能
function logVisitorIP() {
    // 在logVisitorIP函数内添加
    $maxSize = 10 * 1024 * 1024; // 10MB
    if (filesize($logFile) > $maxSize) {
        $backupFile = $logFile . '.' . date('YmdHis');
        rename($logFile, $backupFile);
    }
    // 获取客户端真实IP
    $ip = $_SERVER['HTTP_CLIENT_IP'] ?? 
          $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
          $_SERVER['REMOTE_ADDR'] ?? 
          'unknown';

    // 过滤非法字符
    $ip = filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'invalid_ip';
    
    // 获取其他信息
    $timestamp = date('Y-m-d H:i:s');
    $page = $_SERVER['REQUEST_URI'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'No User Agent';
    
    // 构造日志条目
    $logEntry = sprintf(
        "[%s] IP: %-15s | Page: %-30s | Agent: %s\n",
        $timestamp,
        $ip,
        substr($page, 0, 30),
        $userAgent
    );
    
    // 写入日志文件
    $logFile = __DIR__.'/log.txt';
    
    try {
        $fp = fopen($logFile, 'a');
        if (flock($fp, LOCK_EX)) { // 排他锁
            fwrite($fp, $logEntry);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    } catch (Exception $e) {
        // 静默处理错误，避免影响主程序
    }
}

// 执行日志记录
logVisitorIP();
?>
<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>留言</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-color: #ffffff;
            --text-color: #212529;
            --card-bg: #f8f9fa;
            --base-font-size: 18px;
        }

        @media (max-width: 768px) {
    .message-images {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        grid-auto-rows: minmax(150px, auto);
    }
    
    .message-image img {
        max-height: 200px;
    }
}

        [data-theme="dark"] {
            --bg-color: #212529;
            --text-color: #f8f9fa;
            --card-bg: #343a40;
        }

        html {
            font-size: var(--base-font-size);
        }

        body {
            background: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            transition: all 0.3s ease;
            line-height: 1.6;
        }

        .theme-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .theme-switch {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            border: none;
            background: var(--card-bg);
            color: var(--text-color);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .theme-switch:hover {
            transform: scale(1.1) rotate(15deg);
        }

        .size-control {
            height: 120px;
            padding: 15px 10px;
            background: rgba(0,0,0,0.1);
            border-radius: 30px;
            backdrop-filter: blur(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .size-control input[type="range"] {
            -webkit-appearance: slider-vertical;
            appearance: slider-vertical;
            width: 6px;
            height: 100px;
            padding: 0 15px;
            background: linear-gradient(
                to bottom,
                var(--text-color) 0%,
                var(--text-color) calc(var(--value, 0.5) * 100%),
                rgba(0,0,0,0.1) calc(var(--value, 0.5) * 100%),
                rgba(0,0,0,0.1) 100%
            );
            border-radius: 3px;
            transition: background 0.2s;
        }

        .message-images {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); /* 增加最小列宽 */
    grid-auto-rows: minmax(200px, auto); /* 固定行高 */
    margin-top: 1.5rem;
}

        .message-image {
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            cursor: zoom-in;
            transition: transform 0.2s;
        }

        .message-image img {
    width: 100%;
    height: auto;
    max-height: 300px;
    object-fit: contain;
    object-position: left top; /* 新增：内容左对齐 */
    border-radius: 6px;
    display: block;
    margin-right: auto; /* 新增：左侧外边距自动填充 */
}

        .image-preview {
            max-width: 200px;
            margin: 0.5rem;
            position: relative;
        }

        .image-preview img {
            width: 100%;
            border-radius: 6px;
        }

        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0,0,0,0.5);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="theme-controls">
        <button class="theme-switch">💡</button>
        <div class="size-control">
            <input type="range" id="fontSize" min="14" max="24" step="2" value="18">
        </div>
    </div>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card mb-5" style="background: var(--card-bg)">
                    <div class="card-body p-4">
                        <form id="messageForm" enctype="multipart/form-data">
                            <div class="mb-4">
                                <input type="text" class="form-control" name="sender" 
                                       placeholder="昵称" required autocomplete="name">
                            </div>
                            <div class="mb-4">
                                <textarea class="form-control" name="content" 
                                          rows="6" placeholder="留言..." required></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="btn btn-outline-secondary w-100">
                                    📷 选择图片（最多5张）
                                    <input type="file" name="images[]" multiple 
                                           accept="image/*" hidden
                                           onchange="previewImages(event)">
                                </label>
                                <div class="image-previews mt-3"></div>
                                <small class="text-muted">支持格式：JPG/PNG/GIF，每张不超过5MB</small>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">发送</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="messages">
                    <?php
                    try {
                        $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
                        $stmt = $conn->query("SELECT * FROM messages ORDER BY created_at DESC");
                        
                        if ($stmt->rowCount() > 0) {
                            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<div class="card mb-4 message-card" style="background: var(--card-bg)" data-id="'.$row['id'].'">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="card-title mb-0">'.htmlspecialchars($row['sender']).'</h5>
                                                <small class="text-muted">'.date('Y-m-d H:i', strtotime($row['created_at'])).'</small>
                                            </div>
                                            <hr style="border-color: var(--text-color)">
                                            <p class="card-text mt-3">'.nl2br(htmlspecialchars($row['content'])).'</p>';
                                
                                if (!empty($row['images'])) {
                                    $images = json_decode($row['images']);
                                    echo '<div class="message-images">';
                                    foreach ($images as $img) {
                                        echo '<div class="message-image">
                                                <img src="uploads/'.$img.'" 
                                                     loading="lazy"
                                                     onclick="showFullImage(this)">
                                              </div>';
                                    }
                                    echo '</div>';
                                }
                                
                                echo '</div></div>';
                            }
                        } else {
                            echo '<div class="text-center text-muted py-5 display-6">暂无留言，快来第一个发言吧！</div>';
                        }
                    } catch(PDOException $e) {
                        echo '<div class="alert alert-danger p-4">暂时无法加载留言</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" onclick="this.style.display='none'">
        <img id="fullImage" style="max-width: 90%; max-height: 90%; border-radius: 8px;">
    </div>

    <script>
        const fontSizeControl = document.getElementById('fontSize');
        const initFontSize = () => {
            const savedSize = localStorage.getItem('fontSize') || 18;
            document.documentElement.style.fontSize = savedSize + 'px';
            fontSizeControl.value = savedSize;
            updateRangeStyle();
        };

        const updateRangeStyle = () => {
            const range = fontSizeControl;
            const max = parseFloat(range.max) || 24;
            const min = parseFloat(range.min) || 14;
            range.style.setProperty('--value', (range.value - min) / (max - min));
        };

        fontSizeControl.addEventListener('input', (e) => {
            updateRangeStyle();
            document.documentElement.style.fontSize = e.target.value + 'px';
            localStorage.setItem('fontSize', e.target.value);
        });

        const updateThemeButton = () => {
            const theme = document.documentElement.getAttribute('data-theme');
            const btn = document.querySelector('.theme-switch');
            btn.style.background = theme === 'dark' ? '#495057' : '#e9ecef';
            btn.style.color = theme === 'dark' ? '#f8f9fa' : '#212529';
        };

        (function() {
            const themeSwitch = document.querySelector('.theme-switch');
            const htmlEl = document.documentElement;
            
            const initTheme = () => {
                const savedTheme = localStorage.getItem('theme');
                const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const initialTheme = savedTheme || (systemDark ? 'dark' : 'light');
                htmlEl.setAttribute('data-theme', initialTheme);
                updateThemeButton();
            };
            
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                if (!localStorage.getItem('theme')) {
                    htmlEl.setAttribute('data-theme', e.matches ? 'dark' : 'light');
                    updateThemeButton();
                }
            });
            
            themeSwitch.addEventListener('click', () => {
                const currentTheme = htmlEl.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                htmlEl.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeButton();
            });
            
            initTheme();
            initFontSize();
        })();

        function previewImages(event) {
            const previews = document.querySelector('.image-previews');
            previews.innerHTML = '';
            
            Array.from(event.target.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'image-preview';
                    div.innerHTML = `
                        <div class="remove-image" onclick="removePreview(this)">×</div>
                        <img src="${e.target.result}">
                    `;
                    previews.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        }

        function removePreview(btn) {
            const index = Array.from(btn.parentNode.parentNode.children).indexOf(btn.parentNode);
            const files = document.querySelector('[name="images[]"]').files;
            const newFiles = new DataTransfer();
            
            Array.from(files).forEach((file, i) => {
                if(i !== index) newFiles.items.add(file);
            });
            
            document.querySelector('[name="images[]"]').files = newFiles.files;
            btn.parentNode.remove();
        }

        function showFullImage(img) {
            document.getElementById('fullImage').src = img.src;
            document.querySelector('.modal-overlay').style.display = 'flex';
        }

        document.getElementById('messageForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> 上传中...';

            try {
                const response = await fetch('submit.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                if (!response.ok) throw new Error(result.error || '上传失败');
                location.reload();
            } catch (error) {
                alert(error.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '发布留言';
            }
        });
    </script>
</body>
</html>