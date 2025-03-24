<?php
require_once "../config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ticket_id = $_POST['ticket_id'] ?? null;
    $status = $_POST['status'] ?? null;

    if (!$ticket_id || !$status) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Brak danych."]);
        exit;
    }

    // Upewnij się, że status należy do dozwolonych
    $allowed_statuses = ['open', 'in_progress', 'waiting', 'resolved', 'closed'];
    if (!in_array($status, $allowed_statuses)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Nieprawidłowy status."]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE tickets SET status = ? WHERE id = ?");
    $stmt->execute([$status, $ticket_id]);

    echo json_encode(["success" => true]);
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Metoda niedozwolona."]);
}
