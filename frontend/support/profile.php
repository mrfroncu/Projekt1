<?php
session_start();
require_once "../../backend/config.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'support') {
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Mój profil</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen bg-gray-100">

<!-- Sidebar -->
<aside class="w-64 bg-white shadow-lg p-4 space-y-4 flex-shrink-0">
    <h2 class="text-xl font-bold">📋 Menu</h2>
    <nav class="space-y-2">
        <a href="dashboard.php" class="block text-blue-600">🏠 Tickety</a>
        <a href="new_ticket.php" class="block text-blue-600">➕ Nowe zgłoszenie</a>
        <a href="trash.php" class="block text-blue-600">🗑️ Kosz</a>
        <a href="profile.php" class="block text-blue-600 font-bold">👤 Mój profil</a>
        <form method="POST" action="../../backend/auth/logout.php">
            <button type="submit" class="text-red-500 hover:underline mt-4">🚪 Wyloguj</button>
        </form>
    </nav>
</aside>

<!-- Main -->
<div class="flex flex-col flex-1 p-6">
    <main class="flex-grow max-w-xl">
        <h1 class="text-2xl font-bold mb-6">👤 Mój profil</h1>

        <form action="../../backend/users/update_profile.php" method="POST" enctype="multipart/form-data" class="bg-white p-4 rounded shadow space-y-4">
            <div>
                <label class="block font-medium">Imię</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" class="w-full border p-2 rounded">
            </div>

            <div>
                <label class="block font-medium">Nazwisko</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" class="w-full border p-2 rounded">
            </div>

            <div>
                <label class="block font-medium">Avatar</label>
                <input type="file" name="avatar" class="w-full border p-2 rounded">
                <?php if (!empty($user['avatar'])): ?>
                    <p class="text-sm mt-2">Aktualny: <img src="../../uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" class="h-12 mt-1 rounded"></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Zapisz zmiany</button>
        </form>
    </main>

    <footer class="text-center py-4 text-sm text-gray-500 mt-auto">
        © Mateusz Fronc - 44905 - WSEI
    </footer>
</div>

</body>
</html>
