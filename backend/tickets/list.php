<?php
require_once "../config.php";
session_start();

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];

if ($role === 'user') {
    $stmt = $pdo->prepare("
        SELECT t.*, u.username 
        FROM tickets t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.user_id = ? 
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$user_id]);
} else if ($role === 'support') {
    $stmt = $pdo->prepare("
        SELECT t.*, u.username 
        FROM tickets t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.assigned_to = ? 
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$user_id]);
} else if ($role === 'dispatcher') {
    $stmt = $pdo->query("
        SELECT t.*, u.username 
        FROM tickets t 
        JOIN users u ON t.user_id = u.id 
        ORDER BY t.created_at DESC
    ");
}

$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($tickets);
exit;
?>
