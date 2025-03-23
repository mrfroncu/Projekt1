<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dispatcher') {
    header("Location: ../login.php");
    exit;
}
$ticket_id = $_GET['id'] ?? null;
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edycja zgłoszenia</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6 max-w-3xl mx-auto">
    <div id="ticketInfo" class="mb-6"></div>

    <form id="updateForm" class="bg-white p-6 rounded shadow space-y-4">
        <input type="text" id="title" class="w-full p-2 border rounded" />
        <select id="priority" class="w-full p-2 border rounded">
            <option value="low">Niski</option>
            <option value="medium">Średni</option>
            <option value="high">Wysoki</option>
            <option value="critical">Krytyczny</option>
        </select>
        <select id="status" class="w-full p-2 border rounded">
            <option value="open">Otwarte</option>
            <option value="in_progress">W trakcie</option>
            <option value="waiting_for_user">Czekam na odpowiedź</option>
            <option value="resolved">Rozwiązane</option>
            <option value="closed">Zamknięte</option>
        </select>
        <button class="bg-blue-500 text-white px-4 py-2 rounded" type="submit">Zapisz zmiany</button>
    </form>

    <script>
        const ticketId = <?= json_encode($ticket_id) ?>;

        async function loadTicket() {
            const res = await fetch("../../backend/tickets/view.php?id=" + ticketId);
            const data = await res.json();
            const t = data.ticket;

            document.getElementById("ticketInfo").innerHTML = `
                <h1 class="text-xl font-bold">${t.title}</h1>
                <p class="text-sm text-gray-600">${t.description}</p>
            `;
            document.getElementById("title").value = t.title;
            document.getElementById("priority").value = t.priority;
            document.getElementById("status").value = t.status;
        }

        document.getElementById("updateForm").addEventListener("submit", async e => {
            e.preventDefault();
            const title = document.getElementById("title").value;
            const priority = document.getElementById("priority").value;
            const status = document.getElementById("status").value;

            await fetch("../../backend/dispatcher/update_ticket.php", {
                method: "POST",
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ticket_id: ticketId, title, priority, status})
            });

            alert("Zgłoszenie zaktualizowane!");
        });

        loadTicket();
    </script>
</body>
</html>
