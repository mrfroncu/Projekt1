<?php
session_start();
session_unset();
session_destroy();

// Przekieruj użytkownika na ekran logowania
header("Location: ../../frontend/login.php");
exit;
