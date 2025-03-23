<?php
require_once "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user'] = [
        "id" => $user['id'],
        "username" => $user['username'],
        "role" => $user['role']
    ];

    // oznaczenie jako online
    $pdo->prepare("UPDATE users SET is_online = 1 WHERE id = ?")->execute([$user['id']]);

    echo json_encode(["success" => true, "user" => $_SESSION['user']]);
} else {
    http_response_code(401);
    echo json_encode(["error" => "Invalid credentials"]);
}
?>
