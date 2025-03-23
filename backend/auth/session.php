<?php
require_once "../config.php";

if (isset($_SESSION['user'])) {
    echo json_encode(["authenticated" => true, "user" => $_SESSION['user']]);
} else {
    echo json_encode(["authenticated" => false]);
}
?>
