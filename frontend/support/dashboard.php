<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'support') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Support</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-lg p-4 space-y-4">
        <h2 class="text-xl font-bold">📋 Menu</h2>
        <nav class="space-y-2">
            <a href="dashboard.php" class="block text-blue-600 hover:underline">🏠 Wszystkie tickety</a>
            <a href="new_ticket.php" class="block text-blue-600 hover:underline">➕ Nowe zgłoszenie</a>
            <form method="POST" action="../../backend/auth/logout.php">
                <button type="submit" class="text-red-500 hover:underline">🚪 Wyloguj</button>
            </form>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-6 overflow-y-auto">
        <h1 class="text-2xl font-bold mb-4">Przypisane tickety</h1>
        <div id="ticketList" class="space-y-4"></div>
    </main>

    <script>
        async function loadTickets() {
            const res = await fetch("../../backend/tickets/list.php");
            const data = await res.json();

            const list = document.getElementById("ticketList");
            list.innerHTML = '';

            if (!data.length) {
                list.innerHTML = '<p class="text-gray-500">Brak zgłoszeń</p>';
                return;
            }

            data.forEach(t => {
                const item = document.createElement("div");
                item.className = "bg-white p-4 rounded shadow";

                const statusClass = {
                    closed: 'text-green-600',
                    in_progress: 'text-green-800',
                    waiting: 'text-orange-500',
                    resolved: 'text-black font-bold',
                    zamkniete: 'text-black font-bold'
                }[t.status] || 'text-gray-600';

                const priorityClass = {
                    high: 'text-orange-500',
                    critical: 'text-red-600 font-bold'
                }[t.priority] || 'text-gray-600';

                item.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="font-semibold">${t.title}</h2>
                            <p class="text-sm">
                                Autor: <span class="text-gray-800">${t.username ?? 'Nieznany'}</span> |
                                Status: <span class="${statusClass}">${t.status}</span> |
                                Priorytet: <span class="${priorityClass}">${t.priority}</span>
                            </p>
                        </div>
                        <a href="ticket_details.php?id=${t.id}" class="text-blue-500 hover:underline">Szczegóły</a>
                    </div>
                `;
                list.appendChild(item);
            });
        }

        loadTickets();
    </script>
</body>
</html>
