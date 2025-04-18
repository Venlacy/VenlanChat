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

        @media (prefers-color-scheme: dark) {
            :root:not([data-theme]) {
                --bg-color: #212529;
                --text-color: #f8f9fa;
                --card-bg: #343a40;
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

        /* 字体调节条 */
        .size-control {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1001;
            width: 220px;
            background: rgba(var(--card-bg-rgb), 0.95);
            padding: 10px 20px;
            border-radius: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            backdrop-filter: blur(5px);
        }

        .size-control input[type="range"] {
            width: 100%;
            height: 6px;
            background: var(--text-color);
            border-radius: 3px;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .size-control input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            background: var(--text-color);
            border-radius: 50%;
            cursor: pointer;
        }

        /* 主题切换按钮 */
        .theme-switch {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
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

        .container {
            padding-top: 5rem;
            padding-bottom: 3rem;
        }

        .message-card {
            transition: all 0.3s ease;
            cursor: pointer;
            margin-bottom: 1.5rem;
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .message-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
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
            overflow-y: auto;
            padding: 1rem;
        }

        .modal-content {
            background: var(--card-bg);
            color: var(--text-color);
            width: 100%;
            max-width: 700px;
            margin: 2rem auto;
            padding: 2.5rem;
            border-radius: 15px;
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            cursor: pointer;
            font-size: 2.2rem;
            line-height: 1;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .close-modal:hover {
            opacity: 1;
        }

        .delete-btn {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            margin-top: 2rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .form-control {
            padding: 1.2rem;
            font-size: 1.1rem;
            border-radius: 10px;
            border: 2px solid rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        textarea.form-control {
            min-height: 180px;
        }

        .btn-primary {
            padding: 1.2rem 2.4rem;
            font-size: 1.2rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        @media (max-width: 768px) {
            html {
                font-size: 18px;
            }
            
            .size-control {
                width: 180px;
                top: 15px;
                padding: 8px 15px;
            }
            
            .theme-switch {
                width: 45px;
                height: 45px;
                font-size: 22px;
                top: 15px;
                right: 15px;
            }
            
            .modal-content {
                padding: 1.5rem;
                border-radius: 12px;
            }
            
            .form-control {
                padding: 1rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- 字体调节条 -->
    <div class="size-control">
        <input type="range" id="fontSize" min="14" max="24" step="2" value="18">
    </div>

    <!-- 主题切换按钮 -->
    <button class="theme-switch">💡</button>

    <!-- 模态框 -->
    <div class="modal-overlay">
        <div class="modal-content">
            <div class="close-modal">×</div>
            <div id="modal-body"></div>
            <button class="btn btn-danger delete-btn">删除</button>
        </div>
    </div>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <!-- 留言表单 -->
                <div class="card mb-5" style="background: var(--card-bg)">
                    <div class="card-body p-4">
                        <form id="messageForm">
                            <div class="mb-4">
                                <input type="text" class="form-control" name="sender" 
                                       placeholder="昵称" required autocomplete="name">
                            </div>
                            <div class="mb-4">
                                <textarea class="form-control" name="content" 
                                          rows="6" placeholder="留言内容..." required></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">发送</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 留言列表 -->
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
                                            <p class="card-text mt-3">'.nl2br(htmlspecialchars($row['content'])).'</p>
                                        </div>
                                      </div>';
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

    <script>
        // 字体大小控制
        const fontSizeControl = document.getElementById('fontSize');
        const initFontSize = () => {
            const savedSize = localStorage.getItem('fontSize') || 18;
            document.documentElement.style.fontSize = savedSize + 'px';
            fontSizeControl.value = savedSize;
        };
        
        fontSizeControl.addEventListener('input', (e) => {
            const size = e.target.value;
            document.documentElement.style.fontSize = size + 'px';
            localStorage.setItem('fontSize', size);
        });

        // 主题管理
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

        // 留言交互功能
        document.querySelectorAll('.message-card').forEach(card => {
            card.addEventListener('click', (e) => {
                const modal = document.querySelector('.modal-overlay');
                const content = card.querySelector('.card-text').innerHTML;
                const sender = card.querySelector('.card-title').textContent;
                const time = card.querySelector('small').textContent;
                
                document.getElementById('modal-body').innerHTML = `
                    <h3 class="mb-3">${sender}</h3>
                    <small class="text-muted d-block mb-4">${time}</small>
                    <div class="content-box">${content}</div>
                `;
                modal.style.display = 'block';
                modal.dataset.id = card.dataset.id;
            });
        });

        // 关闭模态框
        document.querySelector('.close-modal').addEventListener('click', () => {
            document.querySelector('.modal-overlay').style.display = 'none';
        });

        document.querySelector('.modal-overlay').addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });

        // 删除功能
        document.querySelector('.delete-btn').addEventListener('click', async () => {
            const id = document.querySelector('.modal-overlay').dataset.id;
            const sender = prompt('请输入您的昵称以确认删除：');
            
            if (sender) {
                try {
                    const response = await fetch('submit.php?action=delete', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id, sender: sender.trim() })
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        document.querySelector(`.message-card[data-id="${id}"]`)?.remove();
                        document.querySelector('.modal-overlay').style.display = 'none';
                    } else {
                        alert(data.error || '删除失败');
                    }
                } catch (error) {
                    alert('请求失败');
                }
            }
        });

        // 表单提交
        document.getElementById('messageForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm" role="status"></span>
                提交中...
            `;

            try {
                const response = await fetch('submit.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) throw new Error(await response.text());
                location.reload();
            } catch (error) {
                alert(error.message || '提交失败，请稍后重试');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '发送';
            }
        });

        // 移动端优化
        window.addEventListener('resize', () => {
            if (window.visualViewport) {
                document.activeElement.scrollIntoView({ 
                    behavior: 'auto', 
                    block: 'center',
                    inline: 'center'
                });
            }
        });
    </script>
</body>
</html>