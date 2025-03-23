<?php
require_once "../config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Nieprawidłowa metoda"]);
    exit;
}

$ticket_id = $_POST['ticket_id'] ?? null;

if (!$ticket_id || !isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(["error" => "Brak danych"]);
    exit;
}

$file = $_FILES['file'];
$upload_dir = "../../uploads/";
$file_name = time() . "_" . basename($file['name']);
$target_path = $upload_dir . $file_name;

if (!move_uploaded_file($file['tmp_name'], $target_path)) {
    http_response_code(500);
    echo json_encode(["error" => "Nie udało się przesłać pliku"]);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO ticket_attachments (ticket_id, file_path, file_name) VALUES (?, ?, ?)");
$stmt->execute([$ticket_id, $file_name, $file['name']]);

echo json_encode(["success" => true, "file" => $file['name']]);
