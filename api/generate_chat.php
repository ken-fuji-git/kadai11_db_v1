<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/AiService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$diaryId = $input['diary_id'] ?? 0;

if (!$diaryId) {
    http_response_code(400);
    echo json_encode(['error' => 'Diary ID is required']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    // 1. Fetch Diary Content
    $stmt = $conn->prepare("SELECT content FROM diaries WHERE id = :id");
    $stmt->bindParam(':id', $diaryId);
    $stmt->execute();
    $diary = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$diary) {
        http_response_code(404);
        echo json_encode(['error' => 'Diary not found']);
        exit;
    }

    // 2. Generate Chat via AI Service
    $aiService = new AiService();
    $chatData = $aiService->generateChat($diary['content']);

    // 3. Save Chat Log
    $stmt = $conn->prepare("INSERT INTO chat_logs (diary_id, speaker_name, message, sequence_order) VALUES (:diary_id, :speaker, :message, :sequence)"); // Corrected column name 'speaker_name' from setup.sql

    foreach ($chatData as $chat) {
        $stmt->bindParam(':diary_id', $diaryId);
        $stmt->bindParam(':speaker', $chat['speaker']);
        $stmt->bindParam(':message', $chat['message']);
        $stmt->bindParam(':sequence', $chat['sequence']);
        $stmt->execute();
    }

    echo json_encode(['success' => true, 'chat' => $chatData]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
