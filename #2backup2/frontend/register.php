<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja</title>
    <script>
        async function register(event) {
            event.preventDefault();
            const res = await fetch("../backend/auth/register.php", {
                method: "POST",
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    username: document.getElementById("username").value,
                    email: document.getElementById("email").value,
                    password: document.getElementById("password").value,
                    role: document.getElementById("role").value
                })
            });

            const data = await res.json();
            if (data.success) {
                alert("Rejestracja udana. Możesz się zalogować.");
                window.location.href = "login.php";
            } else {
                alert(data.error);
            }
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    <form class="bg-white p-6 rounded shadow-md" style="width: 300px" onsubmit="register(event)">
        <h2 class="text-xl font-bold mb-4">Rejestracja</h2>
        <input id="username" placeholder="Nazwa użytkownika" class="mb-2 w-full p-2 border rounded" required />
        <input id="email" placeholder="Email" class="mb-2 w-full p-2 border rounded" required />
        <input type="password" id="password" placeholder="Hasło" class="mb-2 w-full p-2 border rounded" required />
        <select id="role" class="mb-4 w-full p-2 border rounded" required>
            <option value="user">Użytkownik</option>
            <option value="dispatcher">Dispatcher</option>
            <option value="support">Support</option>
        </select>
        <button type="submit" class="w-full bg-green-500 text-white p-2 rounded hover:bg-green-600">Zarejestruj się</button>
    </form>
</body>
</html>
