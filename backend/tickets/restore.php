<?php
require_once "../config.php";
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'support') {
    http_response_code(403);
    die("Brak dostępu");
}

$ticket_id = $_POST['ticket_id'] ?? null;

if (!$ticket_id) {
    http_response_code(400);
    echo "Brak ID ticketa";
    exit;
}

$pdo->prepare("UPDATE tickets SET deleted_at = NULL WHERE id = ?")->execute([$ticket_id]);
echo "restored";
?>