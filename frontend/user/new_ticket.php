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
    <form action="../../backend/tickets/create.php" method="POST" enctype="multipart/form-data" class="space-y-4 bg-white p-4 rounded shadow">
        <input type="text" name="title" placeholder="Tytuł" required class="w-full p-2 border rounded">
        <textarea name="description" placeholder="Opis zgłoszenia" required class="w-full p-2 border rounded"></textarea>
        <select name="priority" class="w-full p-2 border rounded">
            <option value="low">Niski</option>
            <option value="medium" selected>Średni</option>
            <option value="high">Wysoki</option>
            <option value="critical">Krytyczny</option>
        </select>
        <label>Załącznik (opcjonalnie):</label>
        <input type="file" name="attachment" class="w-full p-2 border rounded">
        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Utwórz zgłoszenie</button>
    </form>
</body>
</html>
