<?php
session_start();
require_once "../../backend/config.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'support') {
    header("Location: ../login.php");
    exit;
}

$tickets = $pdo->query("
    SELECT t.*, u.username AS author_name 
    FROM tickets t
    JOIN users u ON t.user_id = u.id
    WHERE t.deleted_at IS NOT NULL
    ORDER BY t.deleted_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Kosz</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen bg-gray-100">

<!-- Sidebar -->
<aside class="w-64 bg-white shadow-lg p-4 space-y-4 flex-shrink-0">
    <h2 class="text-xl font-bold">📋 Menu</h2>
    <nav class="space-y-2">
        <a href="dashboard.php" class="block text-blue-600">🏠 Tickety</a>
        <a href="new_ticket.php" class="block text-blue-600">➕ Nowe zgłoszenie</a>
        <a href="trash.php" class="block text-blue-600 font-bold">🗑️ Kosz</a>
        <a href="profile.php" class="block text-blue-600">👤 Mój profil</a>
        <form method="POST" action="../../backend/auth/logout.php">
            <button type="submit" class="text-red-500 hover:underline mt-4">🚪 Wyloguj</button>
        </form>
    </nav>
</aside>

<!-- Main -->
<div class="flex flex-col flex-1 p-6">
    <main class="flex-grow">
        <h1 class="text-2xl font-bold mb-6">🗑️ Kosz</h1>

        <?php if (!$tickets): ?>
            <p class="text-gray-500">Brak zgłoszeń w koszu.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($tickets as $ticket): ?>
                    <div class="bg-white p-4 rounded shadow">
                        <h3 class="font-semibold"><?= htmlspecialchars($ticket['title']) ?></h3>
                        <p class="text-sm text-gray-600">Autor: <?= htmlspecialchars($ticket['author_name']) ?></p>
                        <p class="text-xs text-gray-400">Usunięto: <?= $ticket['deleted_at'] ?></p>
                        <form method="POST" action="../../backend/tickets/restore.php">
                            <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                            <button class="mt-2 bg-blue-500 text-white px-3 py-1 rounded text-sm">Przywróć</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="text-center py-4 text-sm text-gray-500 mt-auto">
        © Mateusz Fronc - 44905 - WSEI
    </footer>
</div>

</body>
</html>
