<?php
    session_start();
    require_once("../config.php"); // including configuration file
    
    require_once("engine/form.engine.php");
        $form = new Form();
        
    require_once("engine/database.engine.php");
        $database = new Database();
    
    require_once("engine/account.engine.php");
        $account = new Account();    
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Yétigroo</title>
        <link rel="icon" type="image/png" href="yeti.ico" />
        <link href="css/design.css" rel="stylesheet" type="text/css">
        <meta name="viewport" content="width=device-width, user-scalable=no">
        <script type="text/javascript" language="javascript" src="js/jquery-1.11.3.min.js"></script>
        <script type="text/javascript" language="javascript" src="js/jquery-ui.min.js"></script>
    </head>
    <body>
        <div id="header">
            <?php
                include("templates/header.php");
            ?>
        </div>
        <div id="wrapper">            
            <div id="wrapper-center">
                <?php
                    include("templates/account/recovery.php");
                ?>
            </div>        
        </div>
        <div id="footer">
            <?php
                include("templates/footer.php");
            ?>
        </div>        
        <script type="text/javascript" language="javascript">
        $(function() {
            // Submit form
            $("#btn-recovery").click(function(){$("div.box.recovery form").submit();});
            
        });
        </script>        
    </body>
</html>
