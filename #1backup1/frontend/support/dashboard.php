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
    <title>Moje zadania</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6 max-w-5xl mx-auto">
    <!-- Nagłówek -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Przypisane zgłoszenia</h1>
        <button onclick="logout()" class="text-red-500 underline">Wyloguj</button>
    </div>

    <!-- Lista zgłoszeń -->
    <div id="ticketList" class="space-y-4"></div>

    <script>
        async function loadTickets() {
            const res = await fetch("../../backend/tickets/list.php");
            const data = await res.json();

            const list = document.getElementById("ticketList");
            list.innerHTML = '';

            if (data.length === 0) {
                list.innerHTML = '<p>Brak przypisanych zgłoszeń</p>';
                return;
            }

            data.forEach(t => {
                const item = document.createElement("div");
                item.className = "bg-white p-4 rounded shadow";

                item.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="font-semibold">${t.title}</h2>
                            <p class="text-sm text-gray-600">Status: ${t.status}</p>
                        </div>
                        <a href="ticket_details.php?id=${t.id}" class="text-blue-500 hover:underline">Szczegóły</a>
                    </div>
                `;
                list.appendChild(item);
            });
        }

        async function logout() {
            await fetch("../../backend/auth/logout.php", { method: "POST" });
            window.location.href = "../login.php";
        }

        loadTickets();
    </script>
</body>
</html>
