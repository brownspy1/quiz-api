<?php
session_start();

// If already logged in, redirect to admin panel
if (isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

// Otherwise redirect to login
header('Location: login.php');
exit;
?> 