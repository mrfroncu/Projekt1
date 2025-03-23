<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['user']['role'];

switch ($role) {
    case 'user':
        header("Location: user/dashboard.php");
        break;
    case 'support':
        header("Location: support/dashboard.php");
        break;
    case 'dispatcher':
        header("Location: dispatcher/dashboard.php");
        break;
}
?>
