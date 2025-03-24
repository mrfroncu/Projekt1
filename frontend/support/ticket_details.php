<?php
session_start();
require_once "../../backend/config.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'support') {
    header("Location: ../login.php");
    exit;
}

$ticket_id = $_GET['id'] ?? null;
if (!$ticket_id) die("Brak ID ticketa.");
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Szczegóły zgłoszenia</title>
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
        <a href="profile.php" class="block text-blue-600">👤 Mój profil</a>
        <form method="POST" action="../../backend/auth/logout.php">
            <button type="submit" class="text-red-500 hover:underline mt-4">🚪 Wyloguj</button>
        </form>
    </nav>
</aside>

<!-- Main -->
<div class="flex flex-col flex-1 px-8 py-6 w-full">
    <main class="flex-grow">
        <div id="ticketInfo" class="mb-6"></div>
        <div id="messages" class="space-y-3 mb-6"></div>

        <form id="replyForm" enctype="multipart/form-data" class="bg-white p-4 rounded shadow space-y-3">
            <textarea id="reply" placeholder="Dodaj odpowiedź..." class="w-full p-2 border rounded" required></textarea>
            <input type="file" id="attachment" class="w-full p-2 border rounded" />
            <button class="bg-green-500 text-white px-4 py-2 rounded" type="submit">Wyślij</button>
        </form>
    </main>

    <footer class="text-center py-4 text-sm text-gray-500 mt-auto">
        © Mateusz Fronc - 44905 - WSEI
    </footer>
</div>

<script>
const ticketId = <?= json_encode($ticket_id) ?>;

async function loadTicket() {
    const res = await fetch("../../backend/tickets/view.php?id=" + ticketId);
    const data = await res.json();

    const ticket = data.ticket;
    const messages = data.messages;

    const statusLabels = {
        open: "Otwarte",
        in_progress: "W trakcie",
        waiting: "Oczekiwanie na odpowiedź",
        resolved: "Rozwiązane",
        closed: "Zamknięte"
    };

    document.getElementById("ticketInfo").innerHTML = `
        <h1 class="text-2xl font-bold mb-2">${ticket.title}</h1>
        <p><strong>Opis:</strong> ${ticket.description}</p>
        <p><strong>Status:</strong>
            <select id="statusSelect" class="border p-1 rounded">
                ${Object.entries(statusLabels).map(([val, label]) => `
                    <option value="${val}" ${ticket.status === val ? "selected" : ""}>${label}</option>
                `).join('')}
            </select>
        </p>
        <p><strong>Priorytet:</strong> ${ticket.priority}</p>
        <p class="text-sm text-gray-500">Autor: ${ticket.username}</p>
    `;

    document.getElementById("statusSelect").addEventListener("change", async (e) => {
        const newStatus = e.target.value;
        const res = await fetch("../../backend/tickets/update_status.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `ticket_id=${ticketId}&status=${newStatus}`
        });

        const result = await res.json();
        if (!result.success) {
            alert("Błąd zmiany statusu");
        }
    });

    const container = document.getElementById("messages");
    container.innerHTML = "";

    messages.forEach(msg => {
        const isUserMessage = msg.role === 'user';

        const wrapper = document.createElement("div");
        wrapper.className = "flex";

        const bubble = document.createElement("div");
        bubble.className =
            "p-4 rounded shadow w-[80%] " +
            (isUserMessage
                ? "bg-blue-100 text-right ml-auto"
                : "bg-white text-left mr-auto");

        bubble.innerHTML = `
            <p class="font-semibold">${msg.username}</p>
            <p>${msg.message}</p>
            <p class="text-xs text-gray-500 mt-1">${msg.created_at}</p>
            ${msg.attachments?.length ? `
                <div class="mt-2 text-sm">
                    <strong>Załączniki:</strong>
                    <ul class="list-disc ml-5">
                        ${msg.attachments.map(a => `
                            <li><a href="../../uploads/${a.file_path}" target="_blank" class="text-blue-500 underline">${a.file_name}</a></li>
                        `).join('')}
                    </ul>
                </div>
            ` : ''}
        `;
        wrapper.appendChild(bubble);
        container.appendChild(wrapper);
    });
}

document.getElementById("replyForm").addEventListener("submit", async e => {
    e.preventDefault();
    const message = document.getElementById("reply").value;
    const file = document.getElementById("attachment").files[0];

    const response = await fetch("../../backend/tickets/respond.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ticket_id: ticketId, message })
    });

    const data = await response.json();
    if (data.success && data.message_id) {
        if (file) {
            const formData = new FormData();
            formData.append("file", file);
            formData.append("message_id", data.message_id);

            await fetch("../../backend/tickets/upload_attachment.php", {
                method: "POST",
                body: formData
            });
        }

        document.getElementById("reply").value = "";
        document.getElementById("attachment").value = "";
        loadTicket();
    }
});

loadTicket();
</script>
</body>
</html>
