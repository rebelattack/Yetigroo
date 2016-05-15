<?php

    // Post header
    $username = "<strong>".$user["prenom"]." ".$user["nom"]."</strong>";    
    $edit = "";
    if($post["edit"] > 0){
        $edit = '<span style="font-size:10px;"> (Edité '.$post["edit"].' fois)</span>';
    }    
    $date = '<span style="font-size:10px;"> Le '.date('j/m/y à H\hi', $post["timestamp"]).'</span><br/>';
    
    $group = "";
    if($post["group"] != 0){
        $gData = $database->getGroupInfo($post["group"]);
        $group = '<a class="group" href="index.php?mod=group&g='.$post["group"].'">'.$gData['titre'].'</a><br/>';
    }
    
    if($post["tag"] == ""){
        $tag = "";
    }
    else{
        $tag_array = explode(' ',$post["tag"]);

        $tag = '<div class="listTags"><strong>';

        foreach($tag_array as $unique_tag) {
                 $tag .= '<a href="index.php?t='.trim($unique_tag, '#').'">'.$unique_tag.'</a> ';
        }
        $tag .= '</strong></div>';        
    }
    
    // Post content 
    $image = "";
    $youtube = "";
    if($post["image"] == 0){       
        if($post["youtube"] != ""){
            preg_match('/[\\?\\&]v=([^\\?\\&]+)/',$post["youtube"],$matches);
            $id = $matches[1];
            $youtube = '<center><iframe width="375" height="250" class="youtube" data-link="'.$post["youtube"].'" src="https://www.youtube.com/embed/' . $id . '" frameborder="0" allowfullscreen></iframe></center>';
        }
    }
    else{
        $imageData = $database->getImageInfo($post["image"]);
        if(file_exists("uploads/".$imageData["filename"])){
            $image= '<center><img class="post_image" src="uploads/'.$imageData["filename"].'"/></center><br/>';
        }
        else {
            $image= '<center><img class="post_image" src="img/static/noimage.png"/></center><br/>';
        }
    }
    
    $text = nl2br(mb_ereg_replace("([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])","<a href=\"\\1://\\2\\3\" target=\"_blank\">\\1://\\2\\3</a>",$post["text"]));

    // Post Footer
    $button = '<div class="likeButton">
    <input type="checkbox" id="like_'.$post["id"].'" class="inputLike">
    <label for="like_'.$post["id"].'" class="loadcheck">
    <span class="entypo-cancel">&#10008;</span>
    <span class="load"></span>
    <span class="load"></span>
    <span class="load"></span>
    <span class="entypo-check">&#10004;</span>
    </label>
    </div>';
    if($database->checkPostisLiked($post["id"])){
        $button = '<div class="likeButton">
                    <input type="checkbox" id="like_'.$post["id"].'" class="inputLike" checked="checked">
                    <label for="like_'.$post["id"].'" class="loadcheck">
                    <span class="entypo-cancel">&#10008;</span>
                    <span class="load"></span>
                    <span class="load"></span>
                    <span class="load"></span>
                    <span class="entypo-check">&#10004;</span>
                    </label>
                    </div>';
    }
    
    $like = "like";
    $nbLike = $database->getPostNbLike($post["id"]);
    if($nbLike > 1){
        $like = "likes";
    }
    
    $postLikers = $database->getPostLikers($post["id"]);
    if($nbLike > 0){
        $listLike = '<div class="likeList">
                        <a href="javascript:void(0)" class="popup">'.$nbLike.' '.$like.'<span class="corner-all">'.$this->printListLiker($postLikers).'</span>
                        </a>
                    </div>';
    } else {
        $listLike = '<div class="likeList">
                        <a href="javascript:void(0)">'.$nbLike.' '.$like.'
                        </a>
                    </div>';
    }
    
    $listComments = $database->getPostComments($post["id"]);
    $nbComments = count($listComments);
    if($nbComments == 0){
        $comments = "<center>Aucun commentaire</center>";
    } else {
        $comments = '<center><a href="javascript:void(0);" class="moreComment">Afficher plus de commentaires</a></center>';
        $allComments = "";
        foreach($listComments as $com){
            
            if($_SESSION["id"] == $com["owner"]){
                $who = "Vous";
            }else{
                $userData = $database->getUserDataById($com["owner"]);
                $who = "<strong>".$userData["prenom"]." ".$userData["nom"]."</strong>";    
            }
            $allComments .= '<div class="comment">
                <strong>'.$who.' :</strong> '.$com['text'].'<br>
                <span>Le '. date('j/m/y à H\hi',  $com['timestamp']).'</span>
                </div>';
        }        
        $comments .= '<div class="listComments">'.$allComments.'</div>';
    }
?>

<div class="post" data-post="<?php echo $post["id"] ?>" data-like="<?php echo $nbLike ?>">
    <div class="post-header">
        <?php
            echo $username;
            echo $date;
            echo $group;
            echo $tag;
        ?>
    </div>
    <div class="post-content">
        <?php
        echo $image;
        echo $youtube;
        echo $text;        
        ?>
    </div>
    <div class="post-footer">
        <div class="like">
            <?php
                echo $listLike;
                echo $button; 
            ?>
        </div>
        <div class="comments">
            <?php
                echo $comments;
            ?>
            <div class="newComment">
                <textarea placeholder="Commentez..."></textarea>
            </div>
        </div>
    </div>
</div>