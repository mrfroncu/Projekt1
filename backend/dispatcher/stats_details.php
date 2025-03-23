<?php
require_once "../config.php";

if ($_SESSION['user']['role'] !== 'dispatcher') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$range = $_GET['range'] ?? 'month';

switch ($range) {
    case 'day': $interval = '1 DAY'; break;
    case 'week': $interval = '7 DAY'; break;
    case 'month': default: $interval = '30 DAY'; break;
}

$query = "
    SELECT 
        u.username,
        t.id AS ticket_id,
        t.created_at,
        TIMESTAMPDIFF(MINUTE, t.created_at, (
            SELECT MIN(tm.created_at)
            FROM ticket_messages tm
            WHERE tm.ticket_id = t.id AND tm.sender_id = t.assigned_to
        )) AS first_response_time,
        TIMESTAMPDIFF(MINUTE, t.created_at, t.updated_at) AS resolution_time
    FROM tickets t
    JOIN users u ON t.assigned_to = u.id
    WHERE t.status IN ('resolved', 'closed')
      AND t.updated_at >= NOW() - INTERVAL $interval
";

$stmt = $pdo->query($query);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
