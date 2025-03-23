<?php
require_once "../config.php";

if ($_SESSION['user']['role'] !== 'dispatcher') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, username, is_online FROM users WHERE role = 'support'");
$stmt->execute();
$supports = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($supports);
?>
