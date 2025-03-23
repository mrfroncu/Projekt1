<?php
require_once "../config.php";

if ($_SESSION['user']['role'] !== 'dispatcher') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$ticket_id = $data['ticket_id'] ?? null;
$support_id = $data['support_id'] ?? null;

if (!$ticket_id || !$support_id) {
    http_response_code(400);
    echo json_encode(["error" => "Missing ticket or support ID"]);
    exit;
}

// przypisanie zgłoszenia
$stmt = $pdo->prepare("UPDATE tickets SET assigned_to = ?, status = 'in_progress' WHERE id = ?");
$stmt->execute([$support_id, $ticket_id]);

echo json_encode(["success" => true]);
?>
