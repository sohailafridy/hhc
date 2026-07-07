<?php 
    if(!isset($_SESSION['fixit'])){
        if($_SESSION['fixit'] != true){
                header("Location: ".BASE_URL."fixit/login");
                exit();
        }
         header("Location: ".BASE_URL."fixit/login");
                exit();
    }
?>