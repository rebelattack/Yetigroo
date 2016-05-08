<?php

    if($_SESSION["id"] == $shout["owner"]){
        $who = "Vous";
    }
    else{
        $userData = $database->getUserDataById($shout["owner"]);
        $who = '<a href="index.php?mod=profile&id='.$shout['owner'].'">'.$user['prenom'].' '.$user['nom'].'</a>';
    }
    
    $style = "";
    if(!in_array(ADMIN_GROUP_ID,$_SESSION['group'])){
        $style = 'style="color:red;"';   
    }
    
    $text = nl2br($shout['text']);
    
    foreach($_SMILEYS as $smiley => $img)
    {
        $text = str_replace($smiley,'<img src="../img/smiley/'. $img.'" style="width:20px;margin-bottom:-5px;" alt="'.$smiley.'" />',$text);
    }
?>

<div class="shout" <?php echo $text; ?>>
    <span class="time"> <?php echo date('H\hi',  $shout['timestamp']); ?> </span>
    <strong><?php echo $who; ?> :</strong><br/>
    <?php echo $text; ?>
</div>