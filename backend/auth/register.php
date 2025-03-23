<?php
require_once "../config.php";

// Odczytaj dane z żądania
$data = json_decode(file_get_contents("php://input"), true);

// Walidacja
$username = $data['username'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$role = $data['role'] ?? 'user';

if (!$username || !$email || !$password || !in_array($role, ['user', 'dispatcher', 'support'])) {
    http_response_code(400);
    echo json_encode(["error" => "Niepoprawne dane wejściowe"]);
    exit;
}

// Haszowanie hasła
$hashed = password_hash($password, PASSWORD_BCRYPT);

// Próba rejestracji
try {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $email, $hashed, $role]);
    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Błąd bazy danych: " . $e->getMessage()]);
}
