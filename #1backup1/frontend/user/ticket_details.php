<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
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
    <!-- Szczegóły zgłoszenia -->
    <div id="ticketInfo" class="mb-6"></div>

    <!-- Przycisk zamknięcia zgłoszenia -->
    <div class="mb-6">
        <button onclick="closeTicket()" id="closeBtn" class="hidden bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
            Zamknij zgłoszenie
        </button>
    </div>

    <!-- Wiadomości -->
    <div id="messages" class="space-y-3 mb-6"></div>

    <!-- Formularz odpowiedzi -->
    <form id="replyForm" class="bg-white p-4 rounded shadow space-y-3">
        <textarea id="reply" placeholder="Dodaj odpowiedź..." class="w-full p-2 border rounded"></textarea>
        <button class="bg-green-500 text-white px-4 py-2 rounded" type="submit">Wyślij</button>
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

            // Pokaż przycisk "Zamknij zgłoszenie" tylko jeśli nie jest zamknięte
            if (data.ticket.status !== "closed") {
                document.getElementById("closeBtn").classList.remove("hidden");
            } else {
                document.getElementById("closeBtn").classList.add("hidden");
            }
        }

        document.getElementById("replyForm").addEventListener("submit", async e => {
            e.preventDefault();
            const message = document.getElementById("reply").value;

            if (message.trim() === "") return;

            const res = await fetch("../../backend/tickets/respond.php", {
                method: "POST",
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ticket_id: ticketId, message})
            });

            const data = await res.json();
            if (data.success) {
                document.getElementById("reply").value = "";
                loadTicket();
            }
        });

        async function closeTicket() {
            if (!confirm("Czy na pewno chcesz zamknąć to zgłoszenie?")) return;

            await fetch("../../backend/support/update_status.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ ticket_id: ticketId, status: "closed" })
            });

            alert("Zgłoszenie zostało zamknięte.");
            loadTicket(); // Odśwież widok
        }

        // Start
        loadTicket();
    </script>
</body>
</html>
