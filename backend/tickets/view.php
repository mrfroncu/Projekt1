<?php
require_once "../config.php";
header("Content-Type: application/json");

$ticket_id = $_GET['id'] ?? null;

if (!$ticket_id) {
    echo json_encode(["error" => "Brak ID zgłoszenia"]);
    exit;
}

// Pobierz zgłoszenie + dane autora
$stmt = $pdo->prepare("SELECT t.*, u.username FROM tickets t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    echo json_encode(["error" => "Zgłoszenie nie istnieje"]);
    exit;
}

// Pobierz wiadomości + nadawców
$messagesStmt = $pdo->prepare("
    SELECT m.*, u.username 
    FROM ticket_messages m 
    JOIN users u ON m.sender_id = u.id 
    WHERE m.ticket_id = ? 
    ORDER BY m.created_at ASC
");
$messagesStmt->execute([$ticket_id]);
$messages = $messagesStmt->fetchAll(PDO::FETCH_ASSOC);

// Załączniki dla wiadomości
foreach ($messages as &$msg) {
    $attachmentsStmt = $pdo->prepare("SELECT file_path, file_name FROM ticket_attachments WHERE message_id = ?");
    $attachmentsStmt->execute([$msg['id']]);
    $msg['attachments'] = $attachmentsStmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode([
    "ticket" => $ticket,
    "messages" => $messages
]);
exit;
?>
