<?php
require_once "../config.php";
session_start();

$title = $_POST['title'] ?? null;
$description = $_POST['description'] ?? null;
$priority = $_POST['priority'] ?? 'medium';
$user_id = $_SESSION['user']['id'];

if (!$title || !$description) {
    die("Brak wymaganych danych");
}

// Utwórz ticket
$stmt = $pdo->prepare("INSERT INTO tickets (user_id, title, description, priority, status) VALUES (?, ?, ?, ?, 'open')");
$stmt->execute([$user_id, $title, $description, $priority]);
$ticket_id = $pdo->lastInsertId();

// Utwórz pierwszą wiadomość
$stmt = $pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, message) VALUES (?, ?, ?)");
$stmt->execute([$ticket_id, $user_id, $description]);
$message_id = $pdo->lastInsertId();

// Obsłuż załącznik
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['attachment'];
    $upload_path = __DIR__ . "/../../uploads/messages/$message_id/";

    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0777, true);
    }

    $file_name = time() . "_" . basename($file['name']);
    $full_path = $upload_path . $file_name;

    if (move_uploaded_file($file['tmp_name'], $full_path)) {
        $stmt = $pdo->prepare("INSERT INTO ticket_attachments (message_id, file_path, file_name) VALUES (?, ?, ?)");
        $stmt->execute([$message_id, "messages/$message_id/$file_name", $file['name']]);
    }
}

header("Location: ../../frontend/user/dashboard.php");
exit;
?>
