<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? 'create';

    try {
        $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($action === 'delete') {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            $sender = $data['sender'] ?? '';

            if (!$id || !is_numeric($id)) {
                http_response_code(400);
                echo json_encode(['error' => '无效的请求']);
                exit;
            }

            $stmt = $conn->prepare("SELECT sender FROM messages WHERE id = ?");
            $stmt->execute([$id]);
            $message = $stmt->fetch();

            if (!$message || $message['sender'] !== $sender) {
                http_response_code(403);
                echo json_encode(['error' => '身份验证失败']);
                exit;
            }

            $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true]);

        } else {
            $sender = trim($_POST['sender'] ?? '');
            $content = trim($_POST['content'] ?? '');
            
            if (empty($sender) || empty($content)) {
                http_response_code(400);
                echo json_encode(['error' => '姓名和内容不能为空']);
                exit;
            }

            $stmt = $conn->prepare("INSERT INTO messages (sender, content) VALUES (:sender, :content)");
            $stmt->execute([
                ':sender' => $sender,
                ':content' => $content
            ]);
            
            echo json_encode(['success' => true]);
        }
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => '数据库错误：'.$e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => '方法不允许']);
}
?>