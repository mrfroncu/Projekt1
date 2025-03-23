<?php
require_once "../config.php";
header("Content-Type: application/json");

// LOG: start
file_put_contents(__DIR__ . "/upload_log.txt", "\n\n==== NEW UPLOAD ====\n", FILE_APPEND);

$message_id = $_POST['message_id'] ?? null;
file_put_contents(__DIR__ . "/upload_log.txt", "message_id: $message_id\n", FILE_APPEND);

// LOG: brak danych
if (!$message_id || !isset($_FILES['file'])) {
    file_put_contents(__DIR__ . "/upload_log.txt", "Brak message_id lub FILES\n", FILE_APPEND);
    echo json_encode(["success" => false, "error" => "Brak danych"]);
    exit;
}

// Dane pliku
$file = $_FILES['file'];
file_put_contents(__DIR__ . "/upload_log.txt", "FILES: " . print_r($file, true), FILE_APPEND);

$upload_base = __DIR__ . "/../../uploads/messages/";
$upload_path = $upload_base . $message_id . "/";

if (!is_dir($upload_path)) {
    mkdir($upload_path, 0777, true);
    file_put_contents(__DIR__ . "/upload_log.txt", "Stworzono folder: $upload_path\n", FILE_APPEND);
}

$file_name = time() . "_" . basename($file['name']);
$target_path = $upload_path . $file_name;

// LOG: zapis pliku
if (!move_uploaded_file($file['tmp_name'], $target_path)) {
    file_put_contents(__DIR__ . "/upload_log.txt", "move_uploaded_file nie powiodło się\n", FILE_APPEND);
    echo json_encode(["success" => false, "error" => "Nie udało się zapisać pliku"]);
    exit;
} else {
    file_put_contents(__DIR__ . "/upload_log.txt", "Plik zapisany jako: $target_path\n", FILE_APPEND);
}

// Zapis do bazy danych
$stmt = $pdo->prepare("INSERT INTO ticket_attachments (message_id, file_path, file_name) VALUES (?, ?, ?)");
$executed = $stmt->execute([$message_id, "messages/$message_id/$file_name", $file['name']]);

if ($executed) {
    file_put_contents(__DIR__ . "/upload_log.txt", "INSERT OK\n", FILE_APPEND);
    echo json_encode(["success" => true]);
} else {
    file_put_contents(__DIR__ . "/upload_log.txt", "Błąd INSERT\n", FILE_APPEND);
    echo json_encode(["success" => false, "error" => "Błąd zapisu do bazy"]);
}
exit;
?>
