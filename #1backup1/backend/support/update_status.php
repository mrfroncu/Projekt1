<?php
require_once "../config.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'support') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$ticket_id = $data['ticket_id'] ?? null;
$new_status = $data['status'] ?? null;

$allowed_statuses = ['open', 'in_progress', 'waiting_for_user', 'resolved', 'closed'];

if (!$ticket_id || !in_array($new_status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid status or ticket ID"]);
    exit;
}

// sprawdzenie, czy ticket jest przypisany do supporta
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ? AND assigned_to = ?");
$stmt->execute([$ticket_id, $_SESSION['user']['id']]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    http_response_code(403);
    echo json_encode(["error" => "Not your ticket"]);
    exit;
}

// aktualizacja statusu
$update = $pdo->prepare("UPDATE tickets SET status = ?, updated_at = NOW() WHERE id = ?");
$update->execute([$new_status, $ticket_id]);

echo json_encode(["success" => true]);
?>
