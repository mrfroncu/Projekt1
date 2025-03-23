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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 p-6 max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Wszystkie zgłoszenia</h1>
        <button onclick="logout()" class="text-red-500 underline">Wyloguj</button>
    </div>

    <div id="ticketList" class="space-y-4 mb-12"></div>

    <h2 class="text-xl font-semibold mb-4">📊 Statystyki</h2>
    <select id="range" class="p-2 border rounded mb-4">
        <option value="day">Dzień</option>
        <option value="week">Tydzień</option>
        <option value="month" selected>Miesiąc</option>
    </select>
    <div id="stats" class="space-y-4 mb-10"></div>

    <h2 class="text-xl font-semibold mt-10 mb-2">📈 Wykresy</h2>

    <!-- Pie chart oddzielnie -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-1 mb-10">
        <canvas id="pieChart" style="max-height: 300px;"></canvas>
    </div>

    <!-- Dwa wykresy liniowe obok siebie -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <canvas id="lineChartResponse" style="max-height: 300px;"></canvas>
        <canvas id="lineChartResolution" style="max-height: 300px;"></canvas>
    </div>

    <script>
        let pieChartInstance, responseChart, resolutionChart;

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

            const pieCtx = document.getElementById("pieChart").getContext("2d");
            const colors = ['#4caf50', '#2196f3', '#ff9800', '#e91e63', '#9c27b0', '#00bcd4'];
            const pieData = {
                labels: stats.map(s => s.username),
                datasets: [{
                    data: stats.map(s => s.total_tickets),
                    backgroundColor: colors.slice(0, stats.length)
                }]
            };
            if (pieChartInstance) pieChartInstance.destroy();
            pieChartInstance = new Chart(pieCtx, {
                type: 'pie',
                data: pieData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        async function loadLineCharts() {
            const range = document.getElementById("range").value;
            const res = await fetch("../../backend/dispatcher/stats_details.php?range=" + range);
            const data = await res.json();

            const grouped = {};
            data.forEach(item => {
                if (!grouped[item.username]) grouped[item.username] = [];
                grouped[item.username].push(item);
            });

            const labels = [...new Set(data.map(d => d.created_at.slice(0, 10)))];

            const responseDatasets = Object.keys(grouped).map((user, i) => ({
                label: user,
                data: labels.map(date => {
                    const item = grouped[user].find(d => d.created_at.startsWith(date));
                    return item ? item.first_response_time : null;
                }),
                borderColor: `hsl(${i * 70}, 70%, 50%)`,
                fill: false,
                tension: 0.3
            }));

            if (responseChart) responseChart.destroy();
            responseChart = new Chart(document.getElementById("lineChartResponse"), {
                type: 'line',
                data: { labels, datasets: responseDatasets },
                options: {
                    plugins: { title: { display: true, text: 'Czas odpowiedzi (minuty)' }},
                    responsive: true
                }
            });

            const resolutionDatasets = Object.keys(grouped).map((user, i) => ({
                label: user,
                data: labels.map(date => {
                    const item = grouped[user].find(d => d.created_at.startsWith(date));
                    return item ? item.resolution_time : null;
                }),
                borderColor: `hsl(${i * 70 + 30}, 70%, 60%)`,
                fill: false,
                tension: 0.3
            }));

            if (resolutionChart) resolutionChart.destroy();
            resolutionChart = new Chart(document.getElementById("lineChartResolution"), {
                type: 'line',
                data: { labels, datasets: resolutionDatasets },
                options: {
                    plugins: { title: { display: true, text: 'Czas rozwiązania (minuty)' }},
                    responsive: true
                }
            });
        }

        async function logout() {
            await fetch("../../backend/auth/logout.php", { method: "POST" });
            window.location.href = "../login.php";
        }

        document.getElementById("range").addEventListener("change", () => {
            loadStats();
            loadLineCharts();
        });

        loadTickets();
        loadStats();
        loadLineCharts();
    </script>
</body>
</html>
