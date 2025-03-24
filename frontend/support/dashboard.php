<?php
session_start();
require_once "../../backend/config.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'support') {
    header("Location: ../login.php");
    exit;
}

$supports = $pdo->query("SELECT id, username, first_name, last_name, avatar FROM users WHERE role = 'support'")->fetchAll(PDO::FETCH_ASSOC);

$tickets = $pdo->query("
    SELECT t.*, u.username AS author_name 
    FROM tickets t
    JOIN users u ON t.user_id = u.id
    WHERE t.deleted_at IS NULL
    ORDER BY t.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Support</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .dropzone {
            min-height: 200px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            background-color: #f8fafc;
            transition: background-color 0.2s ease;
        }
        .dropzone.dragover {
            background-color: #e0f2fe;
            border-color: #3b82f6;
        }
        .ticket { user-select: auto; }
        .ticket.dragging {
            opacity: 0.8;
            transform: scale(1.03);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, opacity 0.2s ease;
            z-index: 50;
        }
    </style>
</head>
<body class="flex min-h-screen bg-gray-100">

<!-- Sidebar -->
<aside class="w-64 bg-white shadow-lg p-4 space-y-4">
    <h2 class="text-xl font-bold">📋 Menu</h2>
    <nav class="space-y-2">
        <a href="dashboard.php" class="block text-blue-600 font-bold">🏠 Tickety</a>
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
    <h1 class="text-2xl font-bold mb-6">Tickety</h1>
    <div class="grid grid-cols-<?= count($supports) + 2 ?> gap-6">
        <!-- NIEPRZYDZIELONE -->
        <div>
            <h2 class="text-lg font-semibold mb-2">📥 Nieprzydzielone</h2>
            <div class="dropzone" data-support-id="">
                <?php foreach ($tickets as $ticket): ?>
                    <?php if (empty($ticket['assigned_to'])): ?>
                        <?= renderTicket($ticket, $supports) ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- SUPPORTY -->
        <?php foreach ($supports as $support): ?>
            <div>
                <h2 class="text-lg font-semibold mb-2">
                    👤 <?= htmlspecialchars(trim($support['first_name'] . ' ' . $support['last_name']) ?: $support['username']) ?>
                </h2>
                <div class="dropzone" data-support-id="<?= $support['id'] ?>">
                    <?php foreach ($tickets as $ticket): ?>
                        <?php if ($ticket['assigned_to'] == $support['id']): ?>
                            <?= renderTicket($ticket, $supports) ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- KOSZ -->
        <div>
            <h2 class="text-lg font-semibold mb-2 text-red-600">🗑️ Kosz</h2>
            <div class="dropzone bg-red-50 border-red-300" data-support-id="delete">
                <p class="text-center text-sm text-gray-400">Przeciągnij tutaj, aby usunąć</p>
            </div>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="w-full text-center py-4 text-sm text-gray-500 absolute bottom-0">
    © Mateusz Fronc - 44905 - WSEI
</footer>

<script>
let dragged;

function showToast(msg) {
    const toast = document.createElement("div");
    toast.className = "fixed bottom-5 right-5 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-50";
    toast.innerText = msg;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

document.querySelectorAll('.drag-handle').forEach(handle => {
    handle.setAttribute('draggable', 'true');
    handle.addEventListener('dragstart', e => {
        dragged = handle.closest('.ticket');
        dragged.classList.add("dragging");
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', dragged.dataset.ticketId);
    });
});

document.querySelectorAll('.dropzone').forEach(zone => {
    zone.addEventListener('dragover', e => {
        e.preventDefault();
        zone.classList.add('dragover');
    });

    zone.addEventListener('dragleave', () => {
        zone.classList.remove('dragover');
    });

    zone.addEventListener('drop', async e => {
        e.preventDefault();
        zone.classList.remove('dragover');
        dragged.classList.remove("dragging");

        const ticketId = dragged.dataset.ticketId;
        const supportId = zone.dataset.supportId;

        if (supportId === "delete") {
            if (!confirm("Czy na pewno przenieść zgłoszenie do kosza?")) return;

            const res = await fetch("../../backend/tickets/delete.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `ticket_id=${ticketId}`
            });

            if (res.ok) {
                dragged.remove();
                showToast("Przeniesiono do kosza!");
            } else {
                showToast("❌ Błąd usuwania");
            }
        } else {
            const res = await fetch("../../backend/tickets/assign.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `ticket_id=${ticketId}&assigned_to=${supportId}`
            });

            if (res.ok) {
                zone.appendChild(dragged);

                const select = dragged.querySelector("select[name='assigned_to']");
                if (select) select.value = supportId;

                const assignedDiv = dragged.querySelector(".assigned-support");
                if (assignedDiv) {
                    const selectedOption = select.querySelector(`option[value='${supportId}']`);
                    const fullName = selectedOption ? selectedOption.textContent.trim() : '';
                    const avatarSrc = selectedOption.dataset.avatar || 'default.png';

                    assignedDiv.innerHTML = `
                        <img src="../../uploads/avatars/${avatarSrc}" class="w-6 h-6 rounded-full mr-2" alt="">
                        ${fullName}
                    `;
                }

                showToast("Zgłoszenie przypisane!");
            } else {
                showToast("❌ Błąd przypisania");
            }
        }
    });
});

function startSlaCountdown() {
    const timers = document.querySelectorAll('.sla-timer');

    timers.forEach(timer => {
        const deadline = new Date(timer.dataset.deadline);
        const status = timer.dataset.status;

        const id = timer.dataset.ticketId;

        const interval = setInterval(() => {
            const now = new Date();
            const diff = deadline - now;
            const minutes = Math.floor(diff / 1000 / 60);
            const hours = Math.floor(minutes / 60);
            const seconds = Math.floor((diff / 1000) % 60);

            // Zakończ jeśli ticket zamknięty
            if (['closed', 'resolved', 'waiting'].includes(status)) {
                clearInterval(interval);
                timer.innerHTML = '';
                return;
            }

            if (diff > 0) {
                // Wciąż przed czasem
                let color = 'text-green-600';
                let extra = '';

                if (diff <= 3 * 3600 * 1000 && diff > 3600 * 1000) {
                    color = 'text-orange-500';
                } else if (diff <= 3600 * 1000) {
                    color = 'text-red-600 font-bold animate-pulse';
                    extra = '<div class="text-[10px] text-red-600 mt-0.5">CZAS!</div>';
                }

                const pad = v => (v < 10 ? '0' + v : v);
                timer.className = `absolute top-2 right-2 text-xs font-medium sla-timer ${color}`;
                timer.innerHTML = `${pad(hours)}:${pad(minutes % 60)}:${pad(seconds)}${extra}`;
            } else {
                // Po czasie
                clearInterval(interval);
                timer.className = "absolute top-2 right-2 text-xs font-bold text-purple-600";
                timer.innerHTML = "PO CZASIE!";
            }
        }, 1000);
    });
}

document.addEventListener("DOMContentLoaded", startSlaCountdown);

</script>
<?php
function renderTicket($ticket, $supports) {
    ob_start();

    $assigned = null;
    if (!empty($ticket['assigned_to'])) {
        foreach ($supports as $s) {
            if ($s['id'] == $ticket['assigned_to']) {
                $assigned = $s;
                break;
            }
        }
    }

    $status_text = ucfirst(str_replace('_', ' ', $ticket['status']));
    $status_class = match($ticket['status']) {
        'open' => 'text-green-500',
        'in_progress' => 'text-green-700 font-bold',
        'waiting' => 'text-orange-500',
        'resolved' => 'text-blue-500',
        'closed' => 'text-black font-bold',
        default => 'text-gray-500'
    };

    $priority_class = match($ticket['priority']) {
        'high' => 'text-orange-500',
        'critical' => 'text-red-600 font-bold',
        default => 'text-gray-600'
    };
    ?>
    <div class="bg-white rounded shadow mb-3 ticket" data-ticket-id="<?= $ticket['id'] ?>">
        <div class="bg-gray-200 px-2 py-1 text-sm font-medium flex items-center cursor-move drag-handle rounded-t">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 9h16M4 15h16" />
            </svg>
            Przeciągnij
        </div>

        <div class="p-3 space-y-2 select-text relative">
        <div>
    <div class="flex justify-between items-center">
        <h3 class="font-semibold"><?= htmlspecialchars($ticket['title']) ?></h3>
        <a href="ticket_details.php?id=<?= $ticket['id'] ?>" class="text-blue-500 text-sm hover:underline">Szczegóły</a>
    </div>

    <div class="sla-timer text-right"
     style="display: block; margin-top: 25px;"

         data-deadline="<?= $ticket['sla_deadline'] ?>" 
         data-status="<?= $ticket['status'] ?>" 
         data-ticket-id="<?= $ticket['id'] ?>">
    </div>
</div>



            <p class="text-sm text-gray-600">Autor: <?= htmlspecialchars($ticket['author_name']) ?></p>
            <p class="text-xs">
                <span class="font-semibold">Status:</span> <span class="<?= $status_class ?>"><?= $status_text ?></span><br>
                <span class="font-semibold">Impact:</span> <span class="<?= $priority_class ?>"><?= ucfirst($ticket['priority']) ?></span>
            </p>

            <form action="../../backend/tickets/assign.php" method="POST">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <select name="assigned_to" class="w-full border p-1 rounded text-sm mt-1">
                    <option value="">-- Nieprzydzielony --</option>
                    <?php foreach ($supports as $s): ?>
                        <?php
                            $fullName = trim($s['first_name'] . ' ' . $s['last_name']);
                            $avatar = $s['avatar'] ?? 'default.png';
                        ?>
                        <option
                            value="<?= $s['id'] ?>"
                            data-avatar="<?= htmlspecialchars($avatar) ?>"
                            <?= $ticket['assigned_to'] == $s['id'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($fullName ?: $s['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="w-full bg-blue-500 text-white text-xs mt-1 py-1 rounded">Zapisz</button>
            </form>

            <?php if ($assigned): ?>
                <?php $fullName = trim($assigned['first_name'] . ' ' . $assigned['last_name']); ?>
                <div class="flex items-center mt-1 text-sm text-gray-700 assigned-support">
                    <img src="../../uploads/avatars/<?= $assigned['avatar'] ?? 'default.png' ?>" class="w-6 h-6 rounded-full mr-2" alt="">
                    <?= htmlspecialchars($fullName ?: $assigned['username']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>
</body>
</html>
