<?php

/**
 * Description of accueil
 *
 * @author Charlie
 */
class Module {
    
    function __construct() {
        global $form;
        if(isset($_POST["a"])){
            switch (@$_POST["a"]){
                case "newPost":
                    $this->createNewPost();
                    break;                               
            }
        }        
        include("templates/accueil/accueil.php");
         
    }
    
    private function createNewPost(){
        global $database, $form,$_YETIGROO;        
        $text = $this->sanitize(@$_POST["postText"]);
        $tags = $this->sanitize(@$_POST["postTag"]);
        $group = $this->sanitize(@$_POST["postGroup"]);
        $youtube = $this->sanitize(@$_POST["postYoutube"]);        
        $public = 0;
        if($group == 0){
            $public = 1;
        } else if(@$_POST['postPublic'] == 1){
            $public = 1;	
        }
        
        if(!isset($text) || $text == "") {
            $form->addError("text","Veuillez entrer un message");
        }
	
        if(!is_numeric($group)){
            $form->addError("group","Groupe invalide");
        } 
        else if(!in_array($group, $_SESSION["group"])){
            $form->addError("group","Vous n'appartenez à ce groupe");
        }
        
        if($youtube != '' && !preg_match('/[\\?\\&]v=([^\\?\\&]+)/',$youtube)){
                $form->addError("youtube","Erreur dans le lien youtube");
        }
        if(isset($_FILES["postFile"]["type"]) && $_FILES["postFile"]["type"] != "")
        {
            $temporary = explode(".", $_FILES["postFile"]["name"]);
            $file_extension = strtolower(end($temporary));

            if(!in_array($file_extension, $_YETIGROO["image_extension"]))
            {
                    $form->addError("image","Erreur dans le format (".trim(implode(', ',$_YETIGROO["image_extension"]),', ').")");
            }
            elseif($_FILES["postFile"]["size"] > IMAGE_SIZE)
            {
                    $form->addError("image","Fichier trop gros (".fileSize(IMAGE_SIZE)." MAX )");
            }
            elseif($form->returnErrors() > 0)
            {
                    $form->addError("image","Re-selectionner l'image");
            }
        }
        
        if($form->returnErrors() > 0) {
            $_SESSION['errorarray'] = $form->getErrors();
            $_SESSION['valuearray'] = $_POST;
            header("Location:index.php");
        }
        else{
                $pid = 0;
                if(isset($_FILES["postFile"]["type"]) && $_FILES["postFile"]["type"] != ""){
                        $pid = $this->processImage($_FILES["postFile"]);
                }
                $outTag = "";
                if($tags != ""){
                    $arrayTag = array_unique(preg_split( "/[\s,#]+/", $tags ));
                    
                    foreach($arrayTag as $tag){
                        if($tag != ""){
                            $outTag = $outTag.'#'.$tag.' ';
                            if(!$database->insertNewHashtag($tag)){
                                $database->updateUseHashtag('#'.$tag);
                            }                              
                        }
                    }
                }
                $outTag = trim($outTag);
                $database->insertNewPost($text,$outTag,$youtube,$pid,$group,$public);
                header("Location: index.php");
        }
    }
    
    private function processImage($file){
            global $database;
            $temporary = explode(".", $file["name"]);
            $file_extension = end($temporary);
            
            $new_file_name = rand(0, 9999999999) . '.' . $file_extension;
            if (file_exists("uploads/" . $new_file_name)) 
            {			
                    while(file_exists("uploads/". $new_file_name))
                    {
                            $new_file_name = rand(0, 9999999999) . '.' . $file_extension;
                    }
            }

            $targetPath = "uploads/".$new_file_name;
            if($file["type"] == "image/gif") // pas de redimensionnement pour les gifs
            {
                    $sourcePath = $file['tmp_name']; // Storing source path of the file in a variable
                    move_uploaded_file($sourcePath,$targetPath) ; 
            }
            else
            {
                    $fn = $file['tmp_name'];
                    $size = getimagesize($fn);
                    $ratio = $size[0]/$size[1]; // width/height
                    if( $ratio > 1) {
                            $width = SIZE_MAX;
                            $height = SIZE_MAX/$ratio;
                    }
                    else {
                            $width = SIZE_MAX;
                            $height = SIZE_MAX/$ratio;
                    }

                    $src = imagecreatefromstring(file_get_contents($fn));
                    $dst = imagecreatetruecolor($width,$height);
                    imagecopyresampled($dst,$src,0,0,0,0,$width,$height,$size[0],$size[1]);
                    imagedestroy($src);
                    if($file["type"] == "image/png")
                    {
                            imagepng($dst,$targetPath); // adjust format as needed
                    }
                    elseif($file["type"] == "image/jpg" || $file["type"] == "image/jpeg")
                    {
                            imagejpeg($dst,$targetPath); // adjust format as needed
                    }
                    imagedestroy($dst);
            }

            $id = $database->insertNewPhoto($new_file_name, $file["size"]);
            return $id;
		
	}
    
    public function printAllPost(){
        global $database;
        $listPost = $database->getAllPost(0);
        
        foreach($listPost as $post){    
            $user = $database->getUserDataById($post["owner"]);
            include("templates/accueil/post.php");       
        }
        
    }
        
    public function printGroupSelect(){
        global $database;
        $html= '<select name="postGroup" id="postGroup">';
        $html .= '<option value="0">Publique</option>';		   
        $groupes = $_SESSION['group'];
        foreach($groupes as $group_id){
             if(is_numeric($group_id) && $group_id != 0){
                     $infos_group = $database->getGroupInfo($group_id);
                     $html .= '<option value="'.$infos_group['id'].'">'.$infos_group['titre'].'</option>';
             }
        }
	$html .='</select>';		 
	return $html;
    }
    
    public function getGroupBar(){
        global $database;
        
        $html = "<ul class=\"listGroup\">";
        
        if($_SESSION['active_albums'] == true){
            $html .= '<li><a href="javascript:toggleGroup(\'albums\');"><img src="uploads/img.png" title="Album Photo"/><div>Albums Photo</div></a></li>';
        }
        else{
            $html .= '<li><a href="javascript:toggleGroup(\'albums\');"><img class="disable" src="uploads/img.png" title="Album Photo"/><div>Albums Photo</div></a></li>';
        }


        if(in_array(0, $_SESSION['active_group'])){
            $html .= '<li><a href="javascript:toggleGroup(0);"><img src="uploads/img.png" title="Public"/><div>Public</div></a></li>';
        }
        else{
            $html .= '<li><a href="javascript:toggleGroup(0);"><img class="disable" src="uploads/img.png" title="Public"/><div>Public</div></a></li>';
        }

        foreach($_SESSION['group'] as $group){
            if(in_array($group, $_SESSION['active_group'])){
                if($group != 0){
                    $group_info = $database->getGroupInfo($group);
                    $html .= '<li><a href="javascript:toggleGroup('.$group.');"><img src="uploads/img.png"/><div>'.$group_info['titre'].'</div></a></li>';
                }
            }
            else{
                if($group != 0){
                    $group_info = $database->getGroupInfo($group);
                    $html .= '<li><a href="javascript:toggleGroup('.$group.');"><img class="disable" src="uploads/img.png"/></a><div>'.$group_info['titre'].'</div></li>';
                }            
            }        
        }
        $html .= "</ul>";
        return $html;
    }
    
    private function printListLiker($postLikers){ 
        global $database;
        $out= "";        
        foreach ($postLikers as $user_id){
            $user = $database->getUserDataById($user_id);
            if($user['id'] == $_SESSION['id']){
                $out .= 'Vous, ';
            }
            else{
                $out .= $user['prenom'].' '.$user['nom'].', ';
            }            
        }
        $out = trim($out, ', ');
        return $out;        
    }
    
    private function fileSize($octets) {
        $resultat = $octets;
        for ($i=0; $i < 8 && $resultat >= 1024; $i++) {
            $resultat = $resultat / 1024;
        }
        
        if ($i > 0) {
            return preg_replace('/,00$/', '', number_format($resultat, 2, ',', '')). ' ' . substr('KMGTPEZY',$i-1,1) . 'o';
        } else {
            return $resultat . ' o';
        }
    }
    
    /**
     * Permet d'éviter les injections sql, toutes les variable $_POST et $_GET 
     * doivent passer par cette fonction !
     * 
     * @param type $string
     * @return type
     */
    private function sanitize($string){
        $out = trim(htmlspecialchars($string));
        return $out;
    }
}
