<?php 
    if(!isset($_SESSION['type'])){
        if($_SESSION['type'] != 'admin'){
                header("Location: ".BASE_URL."login");
                exit();
        }
         header("Location: ".BASE_URL."login");
                exit();
    }
?>