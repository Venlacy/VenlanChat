<?php
// 开启错误报告（上线后应关闭）
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

try {
    // 验证请求方法
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("只支持POST请求", 405);
    }

    // 数据库连接
    $conn = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // 输入验证
    $sender = trim($_POST['sender'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (empty($sender)) {
        throw new Exception("昵称不能为空", 400);
    }
    if (empty($content)) {
        throw new Exception("留言内容不能为空", 400);
    }
    if (mb_strlen($sender) > 50) {
        throw new Exception("昵称不能超过50个字符", 400);
    }
    if (mb_strlen($content) > 2000) {
        throw new Exception("留言内容不能超过2000个字符", 400);
    }

    // 处理文件上传
    $uploadedFiles = [];
    if (!empty($_FILES['images']['name'][0])) {
        // 验证文件数量
        if (count($_FILES['images']['name']) > 5) {
            throw new Exception("最多上传5张图片", 400);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            // 跳过空文件
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            // 错误检查
            if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                throw new Exception("文件上传失败（错误代码：".$_FILES['images']['error'][$key]."）", 400);
            }

            // 文件类型验证
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($tmpName);
            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif'
            ];
            
            if (!array_key_exists($mime, $allowedTypes)) {
                throw new Exception("不支持的文件类型：".$_FILES['images']['name'][$key], 400);
            }

            // 文件大小验证
            if ($_FILES['images']['size'][$key] > MAX_FILE_SIZE * 1024 * 1024) {
                throw new Exception("文件大小超过限制：".$_FILES['images']['name'][$key], 400);
            }

            // 生成安全文件名
            $extension = $allowedTypes[$mime];
            $filename = sha1_file($tmpName).".$extension";
            $targetPath = UPLOAD_DIR.$filename;

            // 移动文件
            if (!move_uploaded_file($tmpName, $targetPath)) {
                throw new Exception("文件保存失败", 500);
            }

            $uploadedFiles[] = $filename;
        }
    }

    // 写入数据库
    $stmt = $conn->prepare("
        INSERT INTO messages (sender, content, images, created_at)
        VALUES (:sender, :content, :images, NOW())
    ");
    
    $stmt->execute([
        ':sender' => htmlspecialchars($sender, ENT_QUOTES, 'UTF-8'),
        ':content' => htmlspecialchars($content, ENT_QUOTES, 'UTF-8'),
        ':images' => !empty($uploadedFiles) ? json_encode($uploadedFiles) : null
    ]);

    // 返回成功响应
    echo json_encode([
        'success' => true,
        'message' => '留言发布成功',
        'data' => [
            'id' => $conn->lastInsertId(),
            'images' => $uploadedFiles
        ]
    ]);

} catch (PDOException $e) {
    // 回滚已上传文件
    if (!empty($uploadedFiles)) {
        foreach ($uploadedFiles as $file) {
            @unlink(UPLOAD_DIR.$file);
        }
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => '数据库错误：'.$e->getMessage()
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>