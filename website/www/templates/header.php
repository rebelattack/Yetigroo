<?php
    if($account->isLogged){
        include("header/online.php");
    }
    else{
        include("header/offline.php");
    }
?>