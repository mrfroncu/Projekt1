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
<aside class="w-64 bg-white shadow-lg p-4 space-y-4">
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
<main class="flex-1 p-6 pb-24">
    <div id="ticketInfo" class="mb-6"></div>
    <div id="messages" class="space-y-3 mb-6"></div>

    <form id="replyForm" enctype="multipart/form-data" class="bg-white p-4 rounded shadow space-y-3">
        <textarea id="reply" placeholder="Dodaj odpowiedź..." class="w-full p-2 border rounded" required></textarea>
        <input type="file" id="attachment" class="w-full p-2 border rounded" />
        <button class="bg-green-500 text-white px-4 py-2 rounded" type="submit">Wyślij</button>
    </form>
</main>

<!-- Footer -->
<footer class="w-full text-center py-4 text-sm text-gray-500 absolute bottom-0">
    © Mateusz Fronc - 44905 - WSEI
</footer>

<script>
const ticketId = <?= json_encode($ticket_id) ?>;

async function loadTicket() {
    const res = await fetch("../../backend/tickets/view.php?id=" + ticketId);
    const data = await res.json();

    const ticket = data.ticket;
    const messages = data.messages;

    document.getElementById("ticketInfo").innerHTML = `
        <h1 class="text-2xl font-bold mb-2">${ticket.title}</h1>
        <p><strong>Opis:</strong> ${ticket.description}</p>
        <p><strong>Status:</strong>
            <select id="statusSelect" class="border p-1 rounded">
                <option value="open" ${ticket.status === 'open' ? 'selected' : ''}>Open</option>
                <option value="in_progress" ${ticket.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                <option value="waiting" ${ticket.status === 'waiting' ? 'selected' : ''}>Oczekiwanie</option>
                <option value="resolved" ${ticket.status === 'resolved' ? 'selected' : ''}>Rozwiązane</option>
                <option value="closed" ${ticket.status === 'closed' ? 'selected' : ''}>Zamknięte</option>
            </select>
        </p>
        <p><strong>Priorytet:</strong> ${ticket.priority}</p>
        <p class="text-sm text-gray-500">Autor: ${ticket.username}</p>
    `;

    document.getElementById("statusSelect").addEventListener("change", async (e) => {
        const newStatus = e.target.value;
        await fetch("../../backend/tickets/update_status.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `ticket_id=${ticketId}&status=${newStatus}`
        });
    });

    const messagesContainer = document.getElementById("messages");
    messagesContainer.innerHTML = messages.map(msg => `
        <div class="bg-white p-3 rounded shadow">
            <p class="font-semibold">${msg.username}</p>
            <p>${msg.message}</p>
            <p class="text-xs text-gray-500">${msg.created_at}</p>
            ${msg.attachments?.length ? `
                <div class="mt-2">
                    <strong>Załączniki:</strong>
                    <ul class="list-disc ml-5 text-sm">
                        ${msg.attachments.map(a => `
                            <li><a href="../../uploads/${a.file_path}" target="_blank" class="text-blue-500 underline">${a.file_name}</a></li>
                        `).join('')}
                    </ul>
                </div>
            ` : ''}
        </div>
    `).join('');
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
