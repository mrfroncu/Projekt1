<?php
require_once "../config.php";
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'support') {
    http_response_code(403);
    die("Brak dostępu");
}

$user_id = $_SESSION['user']['id'];
$first_name = $_POST['first_name'] ?? null;
$last_name = $_POST['last_name'] ?? null;

// Obsługa pliku
$avatar = null;
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $dir = __DIR__ . "/../../uploads/avatars/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $filename = time() . "_" . basename($_FILES['avatar']['name']);
    $path = $dir . $filename;

    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $path)) {
        $avatar = $filename;
    }
}

// Zaktualizuj dane
$query = "UPDATE users SET first_name = ?, last_name = ?" . ($avatar ? ", avatar = ?" : "") . " WHERE id = ?";
$params = $avatar ? [$first_name, $last_name, $avatar, $user_id] : [$first_name, $last_name, $user_id];

$stmt = $pdo->prepare($query);
$stmt->execute($params);

header("Location: ../../frontend/support/profile.php");
exit;
?>
