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

    <!-- Main -->
    <main class="flex-1 p-6 overflow-y-auto">
        <div id="ticketInfo" class="mb-6"></div>
        <div id="messages" class="space-y-3 mb-6"></div>

        <form id="replyForm" enctype="multipart/form-data" class="bg-white p-4 rounded shadow space-y-3">
            <textarea id="reply" placeholder="Dodaj odpowiedź..." class="w-full p-2 border rounded" required></textarea>
            <input type="file" id="attachment" class="w-full p-2 border rounded" />
            <button class="bg-blue-500 text-white px-4 py-2 rounded" type="submit">Wyślij odpowiedź</button>
            <p id="error" class="text-red-500 text-sm mt-2"></p>
        </form>
    </main>

    <script>
        const ticketId = <?= json_encode($ticket_id) ?>;

        async function loadTicket() {
            const res = await fetch("../../backend/tickets/view.php?id=" + ticketId);
            const data = await res.json();

            document.getElementById("ticketInfo").innerHTML = `
                <h1 class="text-xl font-bold">${data.ticket.title}</h1>
                <p class="text-sm text-gray-600">
                    Autor: <span class="text-black font-semibold">${data.ticket.username}</span> |
                    Status: ${data.ticket.status}, Priorytet: ${data.ticket.priority}
                </p>
                <p class="mt-2">${data.ticket.description}</p>
            `;

            const messages = document.getElementById("messages");
            messages.innerHTML = data.messages.map(msg => `
                <div class="bg-white p-3 rounded shadow">
                    <p class="font-semibold">${msg.username}</p>
                    <p>${msg.message}</p>
                    <p class="text-xs text-gray-500">${msg.created_at}</p>
                    ${msg.attachments && msg.attachments.length > 0 ? `
                        <div class="mt-2 text-sm">
                            <strong>Załączniki:</strong>
                            <ul class="list-disc ml-5">
                                ${msg.attachments.map(a => `
                                    <li>
                                        <a href="../../uploads/${a.file_path}" target="_blank" class="text-blue-500 underline">
                                            ${a.file_name}
                                        </a>
                                    </li>
                                `).join('')}
                            </ul>
                        </div>
                    ` : ''}
                </div>
            `).join('');
        }

        document.getElementById("replyForm").addEventListener("submit", async e => {
            e.preventDefault();

            const message = document.getElementById("reply").value.trim();
            const file = document.getElementById("attachment").files[0];
            const errorEl = document.getElementById("error");
            errorEl.textContent = "";

            if (!message) {
                errorEl.textContent = "Wiadomość nie może być pusta.";
                return;
            }

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

                    const uploadRes = await fetch("../../backend/tickets/upload_attachment.php", {
                        method: "POST",
                        body: formData
                    });

                    const uploadData = await uploadRes.json();
                    if (!uploadData.success) {
                        errorEl.textContent = "Błąd uploadu: " + uploadData.error;
                    }
                }

                document.getElementById("reply").value = "";
                document.getElementById("attachment").value = "";
                loadTicket();
            } else {
                errorEl.textContent = "Błąd zapisu wiadomości.";
            }
        });

        loadTicket();
    </script>
</body>
</html>
