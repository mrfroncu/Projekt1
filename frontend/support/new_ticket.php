<?php
session_start();
require_once "../../backend/config.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'support') {
    header("Location: ../login.php");
    exit;
}

// Pobierz użytkowników do wyboru
$stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'user'");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Nowe zgłoszenie (support)</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-lg p-4 space-y-4">
        <h2 class="text-xl font-bold">📋 Menu</h2>
        <nav class="space-y-2">
            <a href="dashboard.php" class="block text-blue-600 hover:underline">🏠 Wszystkie tickety</a>
            <a href="new_ticket.php" class="block text-blue-600 hover:underline font-bold">➕ Nowe zgłoszenie</a>
            <form method="POST" action="../../backend/auth/logout.php">
                <button type="submit" class="text-red-500 hover:underline">🚪 Wyloguj</button>
            </form>
        </nav>
    </aside>

    <main class="flex-1 p-6 overflow-y-auto">
        <h1 class="text-2xl font-bold mb-4">Nowe zgłoszenie w imieniu użytkownika</h1>
        <form action="../../backend/tickets/create_by_support.php" method="POST" enctype="multipart/form-data" class="space-y-4 bg-white p-4 rounded shadow">
            <label for="user_id" class="block font-medium">Wybierz użytkownika:</label>
            <select name="user_id" id="user_id" required class="w-full p-2 border rounded">
                <option value="">-- wybierz --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                <?php endforeach; ?>
            </select>

            <input type="text" name="title" placeholder="Tytuł" required class="w-full p-2 border rounded">
            <textarea name="description" placeholder="Opis zgłoszenia" required class="w-full p-2 border rounded"></textarea>

            <select name="priority" class="w-full p-2 border rounded">
                <option value="low">Niski</option>
                <option value="medium" selected>Średni</option>
                <option value="high">Wysoki</option>
                <option value="critical">Krytyczny</option>
            </select>

            <input type="file" name="attachment" class="w-full p-2 border rounded">
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Utwórz zgłoszenie</button>
        </form>
    </main>
</body>
</html>
