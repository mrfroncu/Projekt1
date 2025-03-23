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
    <title>Nowe zgłoszenie</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6 max-w-xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Nowe zgłoszenie</h1>

    <form id="ticketForm" enctype="multipart/form-data" class="bg-white p-6 rounded shadow space-y-4">
        <input type="text" id="title" placeholder="Tytuł" class="w-full p-2 border rounded" required />
        <textarea id="description" placeholder="Opis zgłoszenia" class="w-full p-2 border rounded" required></textarea>

        <select id="priority" class="w-full p-2 border rounded" required>
            <option value="low">Niski</option>
            <option value="medium">Średni</option>
            <option value="high">Wysoki</option>
            <option value="critical">Krytyczny</option>
        </select>

        <input type="file" id="attachment" class="w-full p-2 border rounded" />

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Utwórz zgłoszenie</button>
    </form>

    <script>
        document.getElementById("ticketForm").addEventListener("submit", async function(e) {
            e.preventDefault();

            const res = await fetch("../../backend/tickets/create.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    title: document.getElementById("title").value,
                    description: document.getElementById("description").value,
                    priority: document.getElementById("priority").value
                })
            });

            const data = await res.json();

            if (data.success) {
                const file = document.getElementById("attachment").files[0];

                if (file) {
                    const formData = new FormData();
                    formData.append("file", file);
                    formData.append("ticket_id", data.ticket_id);

                    const uploadRes = await fetch("../../backend/tickets/upload_attachment.php", {
                        method: "POST",
                        body: formData
                    });

                    const uploadData = await uploadRes.json();
                    console.log("UPLOAD RESULT:", uploadData);
                }

                window.location.href = "dashboard.php";
            } else {
                alert("Błąd przy tworzeniu zgłoszenia.");
            }
        });
    </script>
</body>
</html>
