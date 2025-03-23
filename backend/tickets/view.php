<?php
require_once "../config.php";

$ticket_id = $_GET['id'] ?? null;

if (!$ticket_id || !isset($_SESSION['user'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request"]);
    exit;
}

// Pobranie szczegółów zgłoszenia
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

// Sprawdzenie uprawnień
$user = $_SESSION['user'];
if (
    $user['role'] === 'user' && $ticket['user_id'] !== $user['id'] ||
    $user['role'] === 'support' && $ticket['assigned_to'] !== $user['id']
) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Wiadomości
$msg_stmt = $pdo->prepare("
    SELECT tm.*, u.username 
    FROM ticket_messages tm 
    JOIN users u ON tm.sender_id = u.id 
    WHERE ticket_id = ? ORDER BY created_at ASC
");
$msg_stmt->execute([$ticket_id]);
$messages = $msg_stmt->fetchAll(PDO::FETCH_ASSOC);

// Załączniki
$att_stmt = $pdo->prepare("
    SELECT * FROM ticket_attachments WHERE ticket_id = ?
");
$att_stmt->execute([$ticket_id]);
$attachments = $att_stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "ticket" => $ticket,
    "messages" => $messages,
    "attachments" => $attachments
]);
?>
