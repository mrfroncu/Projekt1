<?php
require_once "../config.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$ticket_id = $data['ticket_id'] ?? null;
$message = $data['message'] ?? null;
$user_id = $_SESSION['user']['id'];

if (!$ticket_id || !$message) {
    echo json_encode(["success" => false, "error" => "Brak danych"]);
    exit;
}

// Zapisz wiadomość i zwróć ID
$stmt = $pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, message) VALUES (?, ?, ?)");
$stmt->execute([$ticket_id, $user_id, $message]);
$message_id = $pdo->lastInsertId();

echo json_encode(["success" => true, "message_id" => $message_id]);
exit;
?>
