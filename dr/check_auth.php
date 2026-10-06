<?php


    if(!isset($_SESSION['type'])){
         // Redirect based on user type or to dashboard
        header('Location: ' . BASE_URL . 'login');
                exit();
    }
?>