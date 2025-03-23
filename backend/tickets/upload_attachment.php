<?php
require_once "../config.php";
header("Content-Type: application/json");

session_start();

$message_id = $_POST['message_id'] ?? null;

if (!$message_id || !isset($_FILES['file'])) {
    echo json_encode(["success" => false, "error" => "Brak danych"]);
    exit;
}

$file = $_FILES['file'];
$upload_base = __DIR__ . "/../../uploads/messages/";
$upload_path = $upload_base . $message_id . "/";

if (!is_dir($upload_path)) {
    mkdir($upload_path, 0777, true);
}

$file_name = time() . "_" . basename($file['name']);
$target_path = $upload_path . $file_name;

if (!move_uploaded_file($file['tmp_name'], $target_path)) {
    echo json_encode(["success" => false, "error" => "Nie udało się zapisać pliku"]);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO ticket_attachments (message_id, file_path, file_name) VALUES (?, ?, ?)");
$stmt->execute([$message_id, "messages/$message_id/$file_name", $file['name']]);

echo json_encode(["success" => true]);
exit;
?>
