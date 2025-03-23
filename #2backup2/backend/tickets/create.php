<?php
require_once "../config.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$title = $data['title'] ?? '';
$description = $data['description'] ?? '';
$priority = $data['priority'] ?? 'low';

if (!$title || !$description) {
    http_response_code(400);
    echo json_encode(["error" => "Title and description required"]);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO tickets (user_id, title, description, priority) VALUES (?, ?, ?, ?)");
$stmt->execute([$_SESSION['user']['id'], $title, $description, $priority]);

echo json_encode(["success" => true, "ticket_id" => $pdo->lastInsertId()]);
?>
