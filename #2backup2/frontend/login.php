<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie</title>
    <script>
        async function login(event) {
            event.preventDefault();
            const res = await fetch("../backend/auth/login.php", {
                method: "POST",
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    username: document.getElementById("username").value,
                    password: document.getElementById("password").value
                })
            });

            const data = await res.json();
            if (data.success) {
                window.location.href = "index.php";
            } else {
                alert(data.error);
            }
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    <form class="bg-white p-6 rounded shadow-md" style="width: 300px" onsubmit="login(event)">
        <h2 class="text-xl font-bold mb-4">Logowanie</h2>
        <input id="username" placeholder="Nazwa użytkownika" class="mb-2 w-full p-2 border rounded" required />
        <input type="password" id="password" placeholder="Hasło" class="mb-4 w-full p-2 border rounded" required />
        <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Zaloguj się</button>
    </form>
</body>
</html>
