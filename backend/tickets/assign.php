<?php
require_once "../config.php";
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'support') {
    http_response_code(403);
    die("Brak dostępu");
}

$ticket_id = $_POST['ticket_id'] ?? null;
$assigned_to = $_POST['assigned_to'] ?? null;

if (!$ticket_id) {
    die("Brak ID ticketa");
}

if ($assigned_to === "") {
    $assigned_to = null;
}

$stmt = $pdo->prepare("UPDATE tickets SET assigned_to = ? WHERE id = ?");
$stmt->execute([$assigned_to, $ticket_id]);

header("Location: ../../frontend/support/dashboard.php");
exit;
?>
