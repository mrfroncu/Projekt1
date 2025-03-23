<?php
require_once "../config.php";

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$ticket_id = $data['ticket_id'] ?? null;
$message = $data['message'] ?? '';

if (!$ticket_id || !$message) {
    http_response_code(400);
    echo json_encode(["error" => "Missing ticket ID or message"]);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, message) VALUES (?, ?, ?)");
$stmt->execute([$ticket_id, $_SESSION['user']['id'], $message]);

echo json_encode(["success" => true]);
?>
