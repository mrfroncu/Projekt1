<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dispatcher') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dispatcher Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6 max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Wszystkie zgłoszenia</h1>
        <button onclick="logout()" class="text-red-500 underline">Wyloguj</button>
    </div>

    <div id="ticketList" class="space-y-4 mb-12"></div>

    <h2 class="text-xl font-semibold mb-4">Dashboard statystyk</h2>
    <select id="range" class="p-2 border rounded mb-4">
        <option value="day">Dzień</option>
        <option value="week">Tydzień</option>
        <option value="month" selected>Miesiąc</option>
    </select>
    <div id="stats" class="space-y-4"></div>

    <script>
        async function loadTickets() {
            const res = await fetch("../../backend/tickets/list.php");
            const tickets = await res.json();

            const usersRes = await fetch("../../backend/dispatcher/support_online.php");
            const supports = await usersRes.json();

            const list = document.getElementById("ticketList");
            list.innerHTML = '';

            tickets.forEach(t => {
                const assigned = t.assigned_to ? `Support ID: ${t.assigned_to}` : `
                    <select class="assign p-1 border rounded text-sm" data-id="${t.id}">
                        <option value="">Przypisz do...</option>
                        ${supports.map(s => `<option value="${s.id}">${s.username} (${s.is_online ? 'online' : 'offline'})</option>`).join('')}
                    </select>
                `;

                const item = document.createElement("div");
                item.className = "bg-white p-4 rounded shadow";

                item.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="font-semibold">${t.title}</h2>
                            <p class="text-sm text-gray-600">Status: ${t.status} | Priorytet: ${t.priority}</p>
                        </div>
                        <div class="text-right">
                            ${assigned}<br>
                            <a href="ticket_details.php?id=${t.id}" class="text-blue-500 text-sm">Szczegóły</a>
                        </div>
                    </div>
                `;

                list.appendChild(item);
            });

            document.querySelectorAll(".assign").forEach(select => {
                select.addEventListener("change", async (e) => {
                    const ticketId = e.target.getAttribute("data-id");
                    const supportId = e.target.value;
                    if (!supportId) return;

                    await fetch("../../backend/dispatcher/assign_ticket.php", {
                        method: "POST",
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ ticket_id: ticketId, support_id: supportId })
                    });

                    loadTickets();
                });
            });
        }

        async function loadStats() {
            const range = document.getElementById("range").value;
            const res = await fetch("../../backend/dispatcher/stats.php?range=" + range);
            const stats = await res.json();

            const out = document.getElementById("stats");
            out.innerHTML = stats.map(s => `
                <div class="bg-white p-4 rounded shadow">
                    <h3 class="font-semibold">${s.username}</h3>
                    <p>Rozwiązane tickety: ${s.total_tickets}</p>
                    <p>Średni czas odpowiedzi: ${Math.round(s.avg_response_time)} min</p>
                    <p>Średni czas rozwiązania: ${Math.round(s.avg_resolution_time)} min</p>
                </div>
            `).join('');
        }

        async function logout() {
            await fetch("../../backend/auth/logout.php", { method: "POST" });
            window.location.href = "../login.php";
        }

        loadTickets();
        loadStats();
        document.getElementById("range").addEventListener("change", loadStats);
    </script>
</body>
</html>
