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
        <link href="css/jquery-ui.min.css" rel="stylesheet" type="text/css">
        <meta name="viewport" content="width=device-width, user-scalable=no">
        <script type="text/javascript" language="javascript" src="js/jquery-1.11.3.min.js"></script>
        <script type="text/javascript" language="javascript" src="js/jquery-ui.min.js"></script>
        <script>
            function split( val ) {
                return val.split( /,\s*/ );
            }
            function extractLast( term ) {
                return split( term ).pop();
            }
            
            function previewPostImage(e) {		
		$('#preview').show();
		$('#preview').attr('src', e.target.result);
            };
            
            function getshout(){
                var nbShout = $('.shoutbox').attr('data-shout');                
                var  formData = 'a=getnewshout&nb='+nbShout;
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
            
            function toggleGroup(gid){                
                var  formData = 'a=toggleGroup&g='+gid;
                $.ajax({
                    url : "engine/ajax.engine.php",
                    type: "POST",
                    data : formData,
                    success: function(data){
                        window.location.href = "index.php";
                    }
                });
            }
        </script>
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
                if(isset($_GET['mod']))
                {
                    if(file_exists('engine/'.$_GET['mod'].'.module.php'))
                    {
                        include_once('engine/'.$_GET['mod'].'.module.php');
                            $module = new Module();
                    }
                    else
                    {
                    include_once('templates/404.php');
                    }
                }
                else
                {
                    include_once('engine/accueil.module.php');
                        $module = new Module();
                }
                ?>
            </div>
            
            
        </div>
        <div id="footer">
                footer
        </div>
        <script type="text/javascript" language="javascript">
        $(function() {
            
            
            $("input.inputLike").change(function(){
                post = $(this).parents(".post");
                likeButton = $(this);
                linkLikers = post.find("div.likeList a");
                listLikers = post.find("div.likeList a span");               
                postId = post.attr("data-post");
                nbLike = parseInt(post.attr("data-like"));          
                var  formData = 'a=insertpostlike&p='+postId; 
                $.ajax({
                    url : "engine/ajax.engine.php",
                    type: "POST",
                    data : formData,
                    success: function(){
                        if(likeButton.is(":checked")){
                            nbLike++;
                            like = "like";
                            if(nbLike > 1){
                                like = "likes";
                            }
                            if(listLikers.html()+"" === 'undefined'){
                                linkLikers.addClass("popup");
                                linkLikers.html(nbLike+" "+like+"<span>Vous</span>");
                            } else {
                                list = listLikers.html()+", Vous";
                                linkLikers.html(nbLike+" "+like+"<span>"+list+"</span>");
                            }                            
                        } else {
                            nbLike--;
                            like = "like";
                            if(nbLike > 1){
                                like = "likes";
                            }
                            if(listLikers.html()+"" == 'Vous'){
                                linkLikers.removeClass("popup");
                                linkLikers.html(nbLike+" "+like);
                            } else {
                                list = listLikers.html().replace.replace('Vous','');
                                linkLikers.html(nbLike+" "+like+"<span>"+list+"</span>");
                            }
                        }
                        post.attr("data-like",nbLike);  
                    }
                });
                
            });
            
            $("a.moreComment").click(function(){
               listComments = $(this).parents(".comments").find(".listComments");
               
               if(listComments.height() == 50){
                    listComments.animate({height:listComments.get(0).scrollHeight});
               }else{
                   listComments.animate({height:'50px'});
               }
               
               
            });
            
            $("#postGroup").change(function(){
                val = $(this).val();
                if(val == 0){
                    $("#postPublic+label").hide();
                    $("#postPublic").hide();
                } else {
                    $("#postPublic+label").show();
                    $("#postPublic").show();
                }
            })
                
            $( "#postTag" ).click(function(){ 
                if($(this).val() === ""){
                    $(this).val('#');
                }
            }).bind( "keydown", function( event ) {
              if ( event.keyCode === $.ui.keyCode.TAB && $( this ).autocomplete( "instance" ).menu.active ) {
                event.preventDefault();
              }
            }).autocomplete({
            source: function( request, response ) {
                $.getJSON( "engine/ajax.engine.php", {
                    term: extractLast( request.term ),
                    o: 'onlytag',
                    a: 'autocomplete'
                }, response );                
            },
            search: function() {
                // custom minLength
                var term = extractLast( this.value );
                if ( term.length < 1 ) {
                  return false;
                }
            },
              focus: function() {
                // prevent value inserted on focus
                return false;
              },
              select: function( event, ui ) {

                var terms = split( this.value );
                // remove the current input
                terms.pop();
                // add the selected item
                terms.push( ui.item.value );
                // add placeholder to get the comma-and-space at the end
                terms.push( "" );
                this.value = terms.join( ", " );
                return false;
              }
            });
            
            $("#postFile").change(function() {
                var file = this.files[0];
                var match= ["image/jpeg","image/png","image/jpg", "image/gif"];
                var imagefile = file.type;
                if(!((imagefile==match[0]) || (imagefile==match[1]) || (imagefile==match[2]) || (imagefile==match[3]))){
                        $('#preview').hide();
                        $('#preview').attr('src','img/static/noimage.png');
                }
                else{
                        var reader = new FileReader();
                        reader.onload = previewPostImage;
                        reader.readAsDataURL(file);
                }
            });
            
            var headbarToggle = false;
            $("div#headbar div.toggle").click(function(){
                if(headbarToggle){
                    $("div#headbar").removeClass("open");
                    $("div#headbar").animate({width:'60px'});
                    headbarToggle = false;
                } else {                    
                    $("div#headbar").animate({width:'200px'}, function() {$("div#headbar").addClass("open");});
                    
                    headbarToggle = true;
                }
            });
            
            
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
            
            
            
            
        });
        </script>
    </body>
</html>
