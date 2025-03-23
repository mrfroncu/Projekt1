<?php
session_start();
require_once "../../backend/config.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'support') {
    header("Location: ../login.php");
    exit;
}

// Pobierz supportów
$supports = $pdo->query("SELECT id, username, first_name, last_name, avatar FROM users WHERE role = 'support'")->fetchAll(PDO::FETCH_ASSOC);

// Pobierz zgłoszenia
$tickets = $pdo->query("
    SELECT t.*, u.username AS author_name 
    FROM tickets t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Support</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .ticket-box { min-height: 150px; }
    </style>
</head>
<body class="flex h-screen bg-gray-100">

<!-- Sidebar -->
<aside class="w-64 bg-white shadow-lg p-4 space-y-4">
    <h2 class="text-xl font-bold">📋 Menu</h2>
    <nav class="space-y-2">
        <a href="dashboard.php" class="block text-blue-600 font-bold">🏠 Tickety</a>
        <a href="new_ticket.php" class="block text-blue-600">➕ Nowe zgłoszenie</a>
        <a href="profile.php" class="block text-blue-600">👤 Mój profil</a>
        <form method="POST" action="../../backend/auth/logout.php">
            <button type="submit" class="text-red-500 hover:underline mt-4">🚪 Wyloguj</button>
        </form>
    </nav>
</aside>

<main class="flex-1 p-6 overflow-x-auto">
    <h1 class="text-2xl font-bold mb-6">Przypisane tickety</h1>
    <div class="grid grid-cols-1 md:grid-cols-<?= count($supports) + 1 ?> gap-6">
        <!-- Nieprzypisane -->
        <div>
            <h2 class="text-lg font-semibold mb-2">📥 Nieprzydzielone</h2>
            <div class="space-y-3">
                <?php foreach ($tickets as $ticket): ?>
                    <?php if (empty($ticket['assigned_to'])): ?>
                        <?= renderTicket($ticket, $supports) ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Każdy support -->
        <?php foreach ($supports as $support): ?>
            <div>
                <h2 class="text-lg font-semibold mb-2">
                    👤 <?= htmlspecialchars($support['first_name'] ?: $support['username']) ?>
                </h2>
                <div class="space-y-3">
                    <?php foreach ($tickets as $ticket): ?>
                        <?php if ($ticket['assigned_to'] == $support['id']): ?>
                            <?= renderTicket($ticket, $supports) ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php
function renderTicket($ticket, $supports)
{
    ob_start();
    ?>
    <div class="bg-white p-4 rounded shadow">
        <h3 class="font-semibold"><?= htmlspecialchars($ticket['title']) ?></h3>
        <p class="text-sm text-gray-600">Autor: <?= htmlspecialchars($ticket['author_name']) ?></p>
        <p class="text-sm text-gray-400">Priorytet: <?= $ticket['priority'] ?> | Status: <?= $ticket['status'] ?></p>

        <form action="../../backend/tickets/assign.php" method="POST" class="mt-2 text-sm">
            <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
            <select name="assigned_to" class="w-full border p-1 rounded">
                <option value="">-- Nieprzydzielony --</option>
                <?php foreach ($supports as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $ticket['assigned_to'] == $s['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['first_name'] ?: $s['username']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="mt-1 bg-blue-500 text-white px-2 py-1 rounded text-xs">Zapisz</button>
        </form>

        <?php if ($ticket['assigned_to']): ?>
            <div class="flex items-center mt-2 text-sm">
                <?php
                $assigned = array_filter($supports, fn($s) => $s['id'] == $ticket['assigned_to']);
                $assigned = reset($assigned);
                ?>
                <img src="../../uploads/avatars/<?= $assigned['avatar'] ?? 'default.png' ?>" class="w-6 h-6 rounded-full mr-2" alt="">
                <?= htmlspecialchars($assigned['first_name'] ?: $assigned['username']) ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
?>
</body>
</html>
