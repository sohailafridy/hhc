<?php
require_once 'config.php';

// Clear session data
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: " . BASE_URL . "/login");
exit;
?>
