<?php
session_start();

require("../config.php");

require_once("engine/database.engine.php");
    $database = new Database();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Yétigroo - API</title>       
    </head>
    <body>
        <?php
        require_once("engine/api.engine.php");
        $Api = new Api();        
        ?>
    </body>
</html>
