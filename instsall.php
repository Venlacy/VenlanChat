<?php
$servername = "地址";
$username = "账户";
$password = "密码";
$dbname = "数据库名";

try {
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 创建数据库
    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    
    // 创建数据表
    $conn->exec("USE $dbname");
    $sql = "CREATE TABLE IF NOT EXISTS messages (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        sender VARCHAR(50) NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    
    // 创建配置文件
    $config = "<?php
    define('DB_HOST', '$servername');
    define('DB_NAME', '$dbname');
    define('DB_USER', '$username');
    define('DB_PASS', '$password');
    ?>";
    
    file_put_contents('config.php', $config);
    
    echo "安装成功！请删除install.php文件";
} catch(PDOException $e) {
    die("安装失败: " . $e->getMessage());
}
?>