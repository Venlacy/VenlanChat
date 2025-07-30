# VenlanChat v2.0 - 现代化PHP聊天室

![VenlanChat](https://img.shields.io/badge/VenlanChat-v2.0-blue) ![PHP](https://img.shields.io/badge/PHP-7.0+-green) ![License](https://img.shields.io/badge/License-MIT-yellow)

一个基于PHP的轻量级实时聊天室系统，专为虚拟主机和宝塔面板部署优化。

## ✨ 主要特性

- 🚀 **零数据库依赖** - 使用JSON文件存储，部署简单
- 💬 **实时聊天** - 自动刷新消息，流畅聊天体验
- 🎨 **现代化UI** - 响应式设计，支持移动端
- 🛡️ **安全防护** - XSS防护、频率限制、管理员权限
- 📊 **数据统计** - 访问统计、用户分析、日志记录
- ⚙️ **易于管理** - 完整的管理面板，一键操作
- 🔧 **高度可配置** - 灵活的配置选项

## 🎯 适用场景

- 个人网站聊天室
- 小型团队沟通平台
- 临时活动交流区
- 教学演示项目
- 快速原型开发

## 📦 快速部署

### 1. 环境要求
- PHP 7.0+
- Web服务器（Apache/Nginx）
- 文件写入权限

### 2. 宝塔面板部署
```bash
# 1. 下载文件到网站根目录
# 2. 设置目录权限为 755
# 3. 访问 http://your-domain.com/install.php
# 4. 按照向导完成安装
```

### 3. 文件结构
```
your-website/
├── index.php          # 主聊天界面
├── install.php        # 安装程序
├── admin.php          # 管理面板
├── log.php            # 日志查看器
├── config.php         # 配置文件
└── data/              # 数据目录
    ├── messages.json  # 消息存储
    ├── log.txt       # 访问日志
    └── config.json   # 运行配置
```

## 🎮 功能说明

### 用户功能
- ✅ 设置用户名发送消息
- ✅ 实时查看聊天记录
- ✅ 响应式界面适配
- ✅ 表情符号支持
- ✅ 消息时间显示

### 管理功能
- 🔧 消息删除管理
- 📊 访问统计分析
- 🗂️ 数据备份下载
- 🚫 发送频率限制
- 📋 系统日志查看

### 安全特性
- 🔒 XSS攻击防护
- 🔒 管理员密码验证
- 🔒 发送频率限制
- 🔒 IP地址记录
- 🔒 访问日志监控

## ⚙️ 配置选项

编辑 `index.php` 中的 `$config` 数组：

```php
$config = [
    'admin_password' => 'your_password',    // 管理员密码
    'max_messages' => 100,                  // 最大消息数
    'message_max_length' => 500,            // 消息最大长度
    'rate_limit' => 3,                      // 频率限制（每分钟）
];
```

## 🔧 管理指南

### 访问管理面板
1. 访问：`http://your-domain.com/admin.php`
2. 输入管理员密码
3. 查看统计数据和管理消息

### 查看访问日志
1. 访问：`http://your-domain.com/log.php`
2. 输入管理员密码
3. 查看详细的访问记录

### 数据备份
- 定期备份 `data/` 目录
- 通过管理面板下载数据文件
- 建议设置自动备份计划

## 🛠️ 自定义开发

### 修改主题样式
在 `index.php` 的 `<style>` 标签中修改CSS：
```css
/* 修改主色调 */
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
}
```

### 添加新功能
1. 在 `$_POST` 处理部分添加新的 action
2. 在前端添加对应的JavaScript处理
3. 更新数据结构（如需要）

### 集成其他系统
- 可以通过API接口集成
- 支持单点登录集成
- 数据库迁移支持

## 📈 性能优化

### 服务器优化
- 开启Gzip压缩
- 设置静态资源缓存
- 使用CDN加速

### 代码优化
- 定期清理日志文件
- 限制消息历史数量
- 优化JSON文件读写

## 🔍 故障排除

### 常见问题

**Q: 无法发送消息？**
A: 检查目录权限，确保data目录可写

**Q: 管理员密码忘记了？**
A: 编辑index.php文件，修改$config['admin_password']

**Q: 页面显示异常？**
A: 检查PHP版本是否符合要求（7.0+）

**Q: 数据丢失怎么办？**
A: 从备份文件恢复data目录

### 日志分析
```bash
# 查看错误日志
tail -f /www/wwwroot/your-domain/data/log.txt

# 分析访问量
grep "Page accessed" data/log.txt | wc -l
```

## 🤝 贡献指南

欢迎提交Issue和Pull Request！

1. Fork 项目
2. 创建功能分支
3. 提交更改
4. 发起Pull Request

## 📝 更新日志

### v2.0 (2024-01-01)
- ✨ 全新现代化UI设计
- 🔧 完整的管理面板
- 📊 详细的统计功能
- 🛡️ 增强的安全特性
- 📱 完美的移动端适配

### v1.0 (Initial)
- 基础聊天功能
- 简单的消息管理
- 基本的日志记录

## 📄 开源协议

本项目采用 MIT 协议开源，详见 [LICENSE](LICENSE) 文件。

## 🙋‍♂️ 技术支持

- 📧 Email: support@venlanchat.com
- 💬 QQ群: 123456789
- 🌐 官网: https://venlanchat.com

---

**⭐ 如果这个项目对你有帮助，请给我们一个Star！**

Made with ❤️ by VenlanChat Team
