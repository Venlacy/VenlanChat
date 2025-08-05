# VenlanChat - 实时聊天室应用

![VenlanChat](https://img.shields.io/badge/VenlanChat-v3.0-blue) ![PHP](https://img.shields.io/badge/PHP-7.4+-green) ![License](https://img.shields.io/badge/License-MIT-yellow)

> **一个轻量级、功能强大的实时聊天应用，支持公共聊天和私聊！**

VenlanChat 是一个基于 **PHP** 和 **MySQL** 构建的开源聊天室应用，适合个人项目或小型社区。支持用户注册、登录、公共聊天、私聊以及管理员管理功能，界面采用**响应式设计**，兼容桌面和移动设备。消息支持 **Markdown** 和 **LaTeX** 格式化，带来更丰富的表达方式。

---

## ✨ 功能亮点

- **公共聊天**：实时发送和查看消息，支持 **Markdown**（如 **加粗**、*斜体*）和 **LaTeX**（如 $x^2$）。
- **私聊功能**：选择特定用户进行私密对话，自动加载历史记录。
- **用户系统**：安全注册、登录和退出功能，保护用户隐私。
- **管理员面板**：删除消息、查看日志、管理用户，掌控全局。
- **消息管理**：支持消息长度限制和发送频率限制，防止滥用。
- **实时通知**：新消息或私聊时通过浏览器弹窗提醒，显示未读标记。
- **响应式设计**：适配桌面和移动设备，界面简洁美观。
- **数据存储**：公共消息存储在 JSON 文件，私聊和用户信息存储在 MySQL 数据库。

---

## 🛠️ 技术栈

| 组件       | 技术                     |
|------------|--------------------------|
| **后端**   | PHP 7.4+                |
| **数据库** | MySQL（通过 phpMyAdmin 管理） |
| **前端**   | HTML, CSS, JavaScript（集成 Font Awesome 和 MathJax） |
| **Markdown 解析** | Parsedown 库       |
| **依赖**   | 无复杂框架，轻量部署     |

---

## 🚀 快速开始

### 📋 环境要求
- PHP 7.4 或更高版本
- MySQL 数据库
- Web 服务器（推荐 Apache 或 Nginx）
- `data/` 目录需具有写权限

### 📦 安装步骤

1. **克隆项目**
   ```bash
   git clone https://github.com/<your-username>/VenlanChat.git
   cd VenlanChat
   ```

2. **配置数据库**
   - 在 phpMyAdmin 中创建数据库（如 `venlanchat`）。
   - 编辑 `config.php`，更新数据库配置：
     ```php
     'db' => [
         'host' => 'localhost',
         'user' => 'your_db_username',
         'pass' => 'your_db_password',
         'name' => 'your_db_name',
         'charset' => 'utf8mb4'
     ]
     ```

3. **设置目录权限**
   ```bash
   mkdir data
   chmod 755 data
   ```

4. **运行安装脚本**
   - 访问 `install.php`（如 `http://your-domain/VenlanChat/install.php`）。
   - 按照提示设置管理员密码和消息限制，脚本会自动初始化数据库和文件。

5. **启动应用**
   - 打开 `index.php`（如 `http://your-domain/VenlanChat/`）。
   - 注册或登录即可开始使用！

---

## 🎨 使用指南

### 1. 注册与登录
- 访问 `register.php` 创建账号。
- 使用用户名和密码登录 `login.php`。
- 登录后进入 `index.php` 参与聊天。

### 2. 公共聊天
- 在输入框输入消息，支持 **Markdown**（如 `**加粗**`）和 **LaTeX**（如 `$x^2$`）。
- 点击“发送消息”或按 Enter 提交。
- 新消息实时显示，并通过浏览器通知提醒。

### 3. 私聊功能
- 点击“公共聊天”切换至“私有聊天”。
- 从下拉菜单选择聊天对象，自动加载历史记录。
- 输入消息并发送，仅与选定用户相关。
- 新私聊消息会显示未读标记（5秒后自动关闭）。

### 4. 管理员功能
- 访问 `admin.php`，输入管理员密码登录。
- 可删除消息、查看日志或管理用户。

---

## 🤝 贡献指南

欢迎为 VenlanChat 贡献代码！请按照以下步骤操作：

1. **Fork 仓库**
   在 GitHub 上 Fork 本项目。

2. **创建分支**
   ```bash
   git checkout -b feature/your-feature
   ```

3. **提交更改**
   ```bash
   git commit -m "添加新功能: 描述"
   ```

4. **推送代码**
   ```bash
   git push origin feature/your-feature
   ```

5. **提交 Pull Request**
   在 GitHub 上提交 PR，详细描述您的更改。

### 💡 开发建议
- 更新 `README.md` 和相关文档。
- 确保代码兼容 PHP 7.4+ 和 MySQL。
- 充分测试，避免引入 bug。

---

## 📄 开源协议

本项目采用 **MIT 协议**，详情请见 [LICENSE](LICENSE) 文件。

---

## 🌟 致谢

感谢所有贡献者！特别鸣谢开源社区提供的工具和灵感，包括 **Parsedown**、**Font Awesome** 和 **MathJax**。

> **加入我们，打造更强大的聊天社区！** 🚀

---

*最后更新：2025年8月5日*
