<?php
require_once "../config.php";

if ($_SESSION['user']['role'] !== 'dispatcher') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$ticket_id = $data['ticket_id'] ?? null;

if (!$ticket_id) {
    http_response_code(400);
    echo json_encode(["error" => "Missing ticket ID"]);
    exit;
}

$fields = [];
$params = [];

if (isset($data['title'])) {
    $fields[] = "title = ?";
    $params[] = $data['title'];
}
if (isset($data['priority'])) {
    $fields[] = "priority = ?";
    $params[] = $data['priority'];
}
if (isset($data['status'])) {
    $fields[] = "status = ?";
    $params[] = $data['status'];
}

$params[] = $ticket_id;

$query = "UPDATE tickets SET " . implode(", ", $fields) . " WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute($params);

echo json_encode(["success" => true]);
?>
