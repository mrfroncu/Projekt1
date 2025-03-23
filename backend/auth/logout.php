<?php
require_once "../config.php";

if (isset($_SESSION['user'])) {
    $pdo->prepare("UPDATE users SET is_online = 0 WHERE id = ?")->execute([$_SESSION['user']['id']]);
    session_destroy();
}

echo json_encode(["success" => true]);
