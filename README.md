
# VenlanChat - 实时聊天室应用
![VenlanChat](https://img.shields.io/badge/VenlanChat-v3.0-blue) ![PHP](https://img.shields.io/badge/PHP-7.0+-green) ![License](https://img.shields.io/badge/License-MIT-yellow)  
> [!IMPORTANT]
> 本篇README由AI撰写，故需注意部分BUG  
> 本项目的`install.php`文件暂时有些问题,故对于部署请按照下文
> 解压压缩包到目录
> 在phpmyadmin中运行
> ```sql
> CREATE TABLE IF NOT EXISTS users (
>     id INT AUTO_INCREMENT PRIMARY KEY,
>     username VARCHAR(50) NOT NULL UNIQUE,
>     password VARCHAR(255) NOT NULL,
>     email VARCHAR(100) NOT NULL UNIQUE,
>     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
> );
>
> CREATE TABLE IF NOT EXISTS private_messages (
>     id INT AUTO_INCREMENT PRIMARY KEY,
>     sender_id INT NOT NULL,
>     receiver_id INT NOT NULL,
>     message TEXT NOT NULL,
>     timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
>     is_read BOOLEAN DEFAULT FALSE,
>     FOREIGN KEY (sender_id) REFERENCES users(id),
>     FOREIGN KEY (receiver_id) REFERENCES users(id)
> );
> ```
> 然后顺手填下`config.php`中的内容就好啦

一个轻量级、功能强大的实时聊天应用，支持公共聊天和私聊！  
欢迎使用 VenlanChat，一个基于 PHP 和 MySQL 构建的开源聊天室应用。支持用户注册、登录、公共聊天、私聊以及管理员管理功能，界面响应式设计，兼容桌面和移动设备。使用 Markdown 和 LaTeX 增强消息格式，适合个人项目或小型社区使用。

## ✨ 功能特性

公共聊天：实时发送和查看公共消息，支持 Markdown 和 LaTeX 语法。  
私聊功能：选择特定用户，查看与该用户的历史对话，保持私密性。  
用户系统：提供注册、登录和退出功能，确保安全访问。  
管理员面板：管理员可删除消息、查看日志和管理用户。  
消息管理：支持消息长度限制、发送频率限制，防止滥用。  
实时通知：新消息或私聊时通过浏览器弹窗提醒，显示未读标记。  
响应式设计：适配各种设备，界面美观简洁。  
数据存储：公共消息使用 JSON 文件，私聊和用户信息存储在 MySQL 数据库。  


## 🛠️ 技术栈

后端：PHP 7.4+  
数据库：MySQL（通过 phpMyAdmin 管理）  
前端：HTML, CSS, JavaScript（使用 Font Awesome 和 MathJax）  
Markdown 解析：Parsedown 库  
依赖：无复杂框架，适合轻量部署  


## 🚀 安装与运行
环境要求  

PHP 7.4 或更高版本  
MySQL 数据库  
Web 服务器（如 Apache 或 Nginx）  
写权限的 data/ 目录  

### 安装步骤

### 克隆项目
git clone https://github.com/<your-username>/VenlanChat.git  
cd VenlanChat  


### 配置数据库

在 phpMyAdmin 中创建一个数据库（如 venlanchat）。  

编辑 config.php，更新数据库配置：  
```
'db' => [  
    'host' => 'localhost',  
    'user' => 'your_db_username',  
    'pass' => 'your_db_password',  
    'name' => 'venlanchat',  
    'charset' => 'utf8mb4'  
]  
```



### 设置目录权限

创建并授权 data/ 目录：  
mkdir data  
chmod 755 data  




### 运行安装脚本

访问 install.php（如 http://your-domain/VenlanChat/install.php）。  
按照提示设置管理员密码和消息限制，脚本会自动初始化数据库和文件。  


### 启动应用

打开 index.php（如 http://your-domain/VenlanChat/），注册或登录使用。




## 🎨 使用方法
1. 注册与登录

访问 register.php 创建账号。  
使用用户名和密码登录 login.php。  
登录后进入 index.php 参与聊天。  

2. 公共聊天

在输入框输入消息（支持 Markdown 如 **加粗** 或 LaTeX 如 $x^2$）。  
点击“发送消息”或按 Enter 提交。  
新消息会实时显示，并通过通知提醒。  

3. 私聊功能

点击“公共聊天”切换到“私有聊天”。  
从下拉菜单选择聊天对象，自动加载历史记录。  
输入消息并发送，记录仅与选定用户相关。  
新私聊消息会显示未读标记（5秒后自动关闭）。  

4. 管理员功能

访问 admin.php，输入管理员密码登录。  
可删除消息、查看日志或管理用户。  



## 🤝 贡献指南
我们欢迎社区成员为 VenlanChat 贡献代码！请遵循以下步骤：  

### Fork 仓库

在 GitHub 上 Fork 本项目。  


### 创建分支
```
git checkout -b feature/your-feature  
```

### 提交更改
```
git commit -m "添加新功能: 描述"  
```

### 推送代码
```
git push origin feature/your-feature  
```

### 提交 Pull Request

在 GitHub 上提交 PR，描述您的更改。  



### 开发建议

更新 README.md 和相关文档。  
确保代码兼容 PHP 7.4+ 和 MySQL。  
测试所有功能，避免引入 bug。  


## 📄 开源协议

本项目采用 MIT 协议开源，详见 [LICENSE](LICENSE) 文件。

## 🌟 致谢
感谢所有贡献者！特别感谢开源社区提供的工具和灵感。  
