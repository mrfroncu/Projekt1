<?php
session_start();
require_once "../../backend/config.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'support') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Mój profil</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex bg-gray-100 h-screen">
    <aside class="w-64 bg-white shadow-lg p-4 space-y-4">
        <h2 class="text-xl font-bold">📋 Menu</h2>
        <nav class="space-y-2">
            <a href="dashboard.php" class="block text-blue-600">🏠 Tickety</a>
            <a href="new_ticket.php" class="block text-blue-600">➕ Nowe zgłoszenie</a>
            <a href="profile.php" class="block text-blue-600 font-bold">👤 Mój profil</a>
            <form method="POST" action="../../backend/auth/logout.php">
                <button type="submit" class="text-red-500 hover:underline mt-4">🚪 Wyloguj</button>
            </form>
        </nav>
    </aside>

    <main class="flex-1 p-6">
        <h1 class="text-2xl font-bold mb-4">Mój profil</h1>
        <form action="../../backend/support/update_profile.php" method="POST" enctype="multipart/form-data" class="bg-white p-4 rounded shadow space-y-4 max-w-md">
            <div>
                <label class="block mb-1 font-medium">Imię</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" class="w-full p-2 border rounded">
            </div>
            <div>
                <label class="block mb-1 font-medium">Nazwisko</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" class="w-full p-2 border rounded">
            </div>
            <div>
                <label class="block mb-1 font-medium">Zdjęcie profilowe</label>
                <input type="file" name="avatar" accept="image/*" class="w-full p-2 border rounded">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="../../uploads/avatars/<?= $user['avatar'] ?>" class="w-20 h-20 rounded-full mt-2">
                <?php endif; ?>
            </div>
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Zapisz</button>
        </form>
    </main>
</body>
</html>
