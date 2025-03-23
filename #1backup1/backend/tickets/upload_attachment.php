<?php
require_once "../config.php";

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$ticket_id = $_POST['ticket_id'] ?? null;

if (!$ticket_id || !isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing file or ticket ID"]);
    exit;
}

$upload_dir = "../uploads/";
if (!is_dir($upload_dir)) mkdir($upload_dir);

$file = $_FILES['file'];
$filename = time() . "_" . basename($file['name']);
$target_path = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $target_path)) {
    $stmt = $pdo->prepare("INSERT INTO ticket_attachments (ticket_id, file_path, file_name) VALUES (?, ?, ?)");
    $stmt->execute([$ticket_id, $filename, $file['name']]);
    echo json_encode(["success" => true, "file" => $filename]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to upload file"]);
}
?>
