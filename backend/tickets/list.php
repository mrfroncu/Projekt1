<?php
require_once "../config.php";

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];

if ($role === 'user') {
    $stmt = $pdo->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
} else if ($role === 'support') {
    $stmt = $pdo->prepare("SELECT * FROM tickets WHERE assigned_to = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
} else if ($role === 'dispatcher') {
    $stmt = $pdo->query("SELECT * FROM tickets ORDER BY created_at DESC");
}

$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($tickets);
?>
