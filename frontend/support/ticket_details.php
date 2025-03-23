<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'support') {
    header("Location: ../login.php");
    exit;
}
$ticket_id = $_GET['id'] ?? null;
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Szczegóły zgłoszenia</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6 max-w-3xl mx-auto">
    <div id="ticketInfo" class="mb-6"></div>
    <div id="messages" class="space-y-3 mb-6"></div>

    <form id="replyForm" class="bg-white p-4 rounded shadow space-y-3">
        <textarea id="reply" placeholder="Dodaj odpowiedź..." class="w-full p-2 border rounded"></textarea>

        <div class="flex items-center gap-3">
            <label for="status" class="text-sm">Status:</label>
            <select id="status" class="p-2 border rounded">
                <option value="open">Otwarte</option>
                <option value="in_progress">W trakcie</option>
                <option value="waiting_for_user">Czekam na odpowiedź</option>
                <option value="resolved">Rozwiązane</option>
                <option value="closed">Zamknięte</option>
            </select>
        </div>

        <button class="bg-green-500 text-white px-4 py-2 rounded" type="submit">Odpowiedz i zmień status</button>
    </form>

    <script>
        const ticketId = <?= json_encode($ticket_id) ?>;

        async function loadTicket() {
            const res = await fetch("../../backend/tickets/view.php?id=" + ticketId);
            const data = await res.json();

            document.getElementById("ticketInfo").innerHTML = `
                <h1 class="text-xl font-bold">${data.ticket.title}</h1>
                <p class="text-sm text-gray-600">Status: ${data.ticket.status}, Priorytet: ${data.ticket.priority}</p>
                <p class="mt-2">${data.ticket.description}</p>
            `;

            const messages = document.getElementById("messages");
            messages.innerHTML = data.messages.map(msg => `
                <div class="bg-white p-3 rounded shadow">
                    <p class="font-semibold">${msg.username}</p>
                    <p>${msg.message}</p>
                    <p class="text-xs text-gray-500">${msg.created_at}</p>
                </div>
            `).join('');
        }

        document.getElementById("replyForm").addEventListener("submit", async e => {
            e.preventDefault();
            const message = document.getElementById("reply").value;
            const status = document.getElementById("status").value;

            await fetch("../../backend/tickets/respond.php", {
                method: "POST",
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ticket_id: ticketId, message})
            });

            await fetch("../../backend/support/update_status.php", {
                method: "POST",
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ticket_id: ticketId, status})
            });

            document.getElementById("reply").value = "";
            loadTicket();
        });

        loadTicket();
    </script>
</body>
</html>
