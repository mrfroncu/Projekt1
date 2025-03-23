<?php
session_start();
require_once "../../backend/config.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Nowe zgłoszenie</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen bg-gray-100">

<!-- Sidebar -->
<aside class="w-64 bg-white shadow-lg p-4 space-y-4">
    <h2 class="text-xl font-bold">📋 Menu</h2>
    <nav class="space-y-2">
        <a href="dashboard.php" class="block text-blue-600">🏠 Moje zgłoszenia</a>
        <a href="new_ticket.php" class="block text-blue-600 font-bold">➕ Nowe zgłoszenie</a>
        <form method="POST" action="../../backend/auth/logout.php">
            <button type="submit" class="text-red-500 hover:underline mt-4">🚪 Wyloguj</button>
        </form>
    </nav>
</aside>

<!-- Main -->
<main class="flex-1 p-6">
    <h1 class="text-2xl font-bold mb-6">Utwórz nowe zgłoszenie</h1>

    <form action="../../backend/tickets/create.php" method="POST" enctype="multipart/form-data" class="bg-white p-4 rounded shadow space-y-4 max-w-xl">
        <div>
            <label class="block font-medium">Tytuł</label>
            <input type="text" name="title" required class="w-full border p-2 rounded">
        </div>

        <div>
            <label class="block font-medium">Opis</label>
            <textarea name="description" rows="4" required class="w-full border p-2 rounded"></textarea>
        </div>

        <div>
            <label class="block font-medium">Priorytet</label>
            <select name="priority" class="w-full border p-2 rounded">
                <option value="low">Niski</option>
                <option value="medium">Średni</option>
                <option value="high">Wysoki</option>
                <option value="critical">Krytyczny</option>
            </select>
        </div>

        <div>
            <label class="block font-medium">Załącznik</label>
            <input type="file" name="attachment" class="w-full border p-2 rounded">
        </div>

        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Utwórz zgłoszenie</button>
    </form>
</main>

<!-- Footer -->
<footer class="w-full text-center py-4 text-sm text-gray-500 absolute bottom-0">
    © Mateusz Fronc - 44905 - WSEI
</footer>

</body>
</html>
