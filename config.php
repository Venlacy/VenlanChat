<?php
// VenlanChat 配置文件
// 请根据实际需求修改以下配置

return [
    // 数据库文件配置
    'db_file' => 'data/messages.json',        // 消息存储文件
    'log_file' => 'data/log.txt',             // 访问日志文件
    
    // 消息相关配置
    'max_messages' => 100,                     // 最大保存消息数量
    'message_max_length' => 500,               // 单条消息最大长度
    
    // 安全配置
    'admin_password' => 'admin123',            // 管理员密码 - 请务必修改！
    'rate_limit' => 3,                         // 每分钟最多发送消息数
    
    // 站点配置
    'site_title' => 'VenlanChat',             // 站点标题
    'site_description' => '实时聊天室',        // 站点描述
    
    // 功能开关
    'enable_admin_delete' => true,             // 是否启用管理员删除功能
    'enable_rate_limit' => true,               // 是否启用发送频率限制
    'enable_access_log' => true,               // 是否启用访问日志
    'enable_emoji' => true,                    // 是否启用表情符号
    
    // 显示配置
    'auto_refresh_interval' => 5000,           // 自动刷新间隔（毫秒）
    'show_timestamp' => true,                  // 是否显示时间戳
    'show_ip_to_admin' => true,                // 是否向管理员显示IP地址
    
    // 文件上传配置（预留功能）
    'enable_file_upload' => false,             // 是否启用文件上传
    'max_file_size' => 1048576,                // 最大文件大小（字节）
    'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif'], // 允许的文件类型
    
    // 系统配置
    'timezone' => 'Asia/Shanghai',             // 时区设置
    'date_format' => 'Y-m-d H:i:s',           // 日期格式
    
    // 主题配置
    'theme' => [
        'primary_color' => '#667eea',          // 主色调
        'secondary_color' => '#764ba2',        // 辅色调
        'background_type' => 'gradient',       // 背景类型：gradient, solid, image
        'custom_css' => '',                    // 自定义CSS
    ],
    
    // 版本信息
    'version' => '2.0',
    'build_date' => '2024-01-01',
];
?>