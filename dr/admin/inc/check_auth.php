<?php
    if (!isset($_SESSION['type']) || $_SESSION['type'] != 'admin') {
        header("Location: " . BASE_URL . "login");
        exit();
    }
?>