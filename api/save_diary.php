<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../src/Database.php';

// Allow CORS for local development if needed (or keep strict if same origin)
// header("Access-Control-Allow-Origin: *");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$content = $input['content'] ?? '';

if (empty($content)) {
    http_response_code(400);
    echo json_encode(['error' => 'Content is required']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    // 1. Get or Create User (For now, just use the test user 'test_user')
    // In a real app, user auth would happen here.
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = 'test_user' LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Fallback if setup.sql wasn't fully run or user deleted
        $conn->exec("INSERT INTO users (username) VALUES ('test_user')");
        $userId = $conn->lastInsertId();
    } else {
        $userId = $user['id'];
    }

    // 2. Save Diary
    $stmt = $conn->prepare("INSERT INTO diaries (user_id, content, created_at) VALUES (:user_id, :content, CURDATE())");
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':content', $content);
    $stmt->execute();

    $diaryId = $conn->lastInsertId();

    echo json_encode(['success' => true, 'diary_id' => $diaryId]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
