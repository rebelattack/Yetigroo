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
            <div id="shoutbar">
                <?php
                    include ("engine/shoutbox.engine.php");
                        $shoutbox = new Shoutbox();
                    include("templates/shoutbox/shoutbox.php");
                    include("templates/shoutbox/form.php");
                ?>
            </div>
            <div id="wrapper-content">
                <?php
                    var_dump($_SESSION);
                ?>
            </div>
            <div id="headbar">
                head bar
            </div>
            
            
        </div>
        <div id="footer">
                footer
        </div>
        <script type="text/javascript" language="javascript">
        $(function() {
            var lastShout = null;
            var countSpam = 0;            
            $("#shoutInput").keypress(function(e) {
                shoutInput = $(this);
                if(e.which == 13 && $(this).val() != '' && $(this).val() != lastShout) {
                    shoutmessage = $(this).val();
                    var  formData = 'a=insertnewshout&m='+shoutmessage; 
                    $.ajax({
                        url : "engine/ajax.engine.php",
                        type: "POST",
                        data : formData,
                        success: function(){
                            shoutInput.val("");
                            shoutInput.focus();
                            getshout();
                        }
                    });
                    lastShout = shoutmessage;
                    countSpam = 0;
                }else if(e.which == 13 && $(this).val() == lastShout) {
                    countSpam++;
                    alert("Pas de spam !\n Chat bloqué pour "+(5*countSpam)+" secondes");
                    shoutInput.val("");
                    shoutInput.addClass("disabled");
                    shoutInput.prop("disabled", true);
                    setTimeout(function() {
                        shoutInput.removeClass("disabled");
                       shoutInput.prop("disabled", false);
                    }, (5*countSpam)*1000);
                }
            });
            
            $( document ).ready(function() {
                setInterval(getshout, 2000);
            });
            function getshout(){
                var nbShout = $('.shoutbox').attr('data-shout');                
                var  formData = 'a=getnewshout&nb='+nbShout;  //Name value Pair
                $.ajax({
                    url : "engine/ajax.engine.php",
                    type: "POST",
                    data : formData,
                    success: function(data){
                        if(data != "-1"){
                            var htmlString = data + $('.shoutbox').html() ;
                            $('.shoutbox').html(htmlString);
                            var total_item = parseInt(nbShout) + 1;
                            $('.shoutbox').attr('data-shout', total_item);
                        }
                    }
                });
            }
        });
        </script>
    </body>
</html>
