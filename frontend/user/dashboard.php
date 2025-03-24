<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Moje zgłoszenia</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="p-6 max-w-4xl mx-auto">
        <!-- Nagłówek -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Twoje zgłoszenia</h1>
            <div class="flex items-center gap-4">
                <a href="new_ticket.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">+ Nowe zgłoszenie</a>
                <button onclick="logout()" class="text-red-500 underline">Wyloguj</button>
            </div>
        </div>

        <!-- Lista zgłoszeń -->
        <div id="ticketList" class="space-y-4"></div>
    </div>

    <!-- Skrypty -->
    <script>
        async function loadTickets() {
            const res = await fetch("../../backend/tickets/list.php");
            const data = await res.json();

            const list = document.getElementById("ticketList");
            list.innerHTML = '';

            if (data.length === 0) {
                list.innerHTML = '<p class="text-gray-600">Brak zgłoszeń</p>';
                return;
            }

            data.forEach(t => {
                const item = document.createElement("div");
                item.className = "bg-white p-4 rounded shadow";

                item.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="font-semibold text-lg">${t.title}</h2>
                            <p class="text-sm text-gray-600">Status: ${t.status} | Priorytet: ${t.priority}</p>
                            <p class="text-xs text-gray-400 mt-1">Utworzone: ${t.created_at}</p>
                        </div>
                        <a href="ticket_details.php?id=${t.id}" class="text-blue-500 hover:underline text-sm">Szczegóły</a>
                    </div>
                `;
                list.appendChild(item);
            });
        }

        async function logout() {
            await fetch("../../backend/auth/logout.php", { method: "POST" });
            window.location.href = "../login.php";
        }

        // Wczytaj zgłoszenia przy starcie
        loadTickets();
    </script>
</body>
</html>
