<?php
$servername = "地址";
$username = "账号";
$password = "密码";
$dbname = "数据库名";

try {
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $conn->exec("USE $dbname");
    
    $sql = "CREATE TABLE IF NOT EXISTS messages (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        sender VARCHAR(50) NOT NULL,
        content TEXT NOT NULL,
        images JSON DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    
    $config = "<?php
    define('DB_HOST', '$servername');
    define('DB_NAME', '$dbname');
    define('DB_USER', '$username');
    define('DB_PASS', '$password');
    define('UPLOAD_DIR', __DIR__.'/uploads/');
    define('MAX_FILE_SIZE', 5); // 单位MB
    ?>";
    
    file_put_contents('config.php', $config);
    
    echo "安装成功！请执行以下操作：<br>
    1. 创建uploads目录：mkdir uploads<br>
    2. 设置目录权限：chmod 777 uploads<br>
    3. 立即删除本文件！";
} catch(PDOException $e) {
    die("安装失败: " . $e->getMessage());
}
?>