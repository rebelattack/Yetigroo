<?php

class Database {
    
    var $sql;              
    function __construct(){
        $options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
	$this->sql = new PDO( SQL_DNS, SQL_USER, SQL_PASS, $options );
    }
    
    /* ---------------------------- Autocomplete Request ------------------------------- */
    
    public function autocompleteUser($input)
    {
            $query = $this->sql->prepare("SELECT `id`,`nom`, `prenom` FROM `".BETA_MODE."yeti_user` WHERE `mail` LIKE :input OR `prenom` LIKE :input OR `nom` LIKE :input AND `nom` != '' AND `prenom` != '' AND `promo` != '';");
            $input = '%'.$input.'%';		
            $query->bindParam(':input', $input);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);		
    }

    public function autocompleteTag($input){
            $query = $this->sql->prepare("SELECT * FROM `".BETA_MODE."yeti_tag` WHERE `tag` LIKE :input ORDER by `use` DESC;");
            $input = '%'.$input.'%';
            $query->bindParam(':input', $input);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /* ---------------------------- Get Request ------------------------------- */
    
    
    public function getAllPost($p){        
        $group = join("','",$_SESSION['active_group']);

        if(in_array(ADMIN_GROUP_ID,$_SESSION['group'])){
            $query = $this->sql->prepare("SELECT * FROM `".BETA_MODE."yeti_post` WHERE `visible` = 1 ORDER BY `timestamp` DESC LIMIT ".$p.",".POST_LIMIT.";");
        }
        else{
            $query = $this->sql->prepare("SELECT * FROM `".BETA_MODE."yeti_post` WHERE `visible` = 1 AND ( `group` IN ('".$group."') OR `owner` = '".$_SESSION['id']."' OR `public` = '1') ORDER BY `timestamp` DESC LIMIT ".$p.",".POST_LIMIT.";");
        }
        $query->execute();
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    /**
     * Permet de recuperer tous les shouts depuis une heure.
     * @return type
     */
    public function getShouts(){
        $time = time() - SHOUT_TIME_LIMIT;
        $query = $this->sql->prepare("SELECT * FROM `".BETA_MODE."yeti_shout` WHERE `timestamp` > :time AND `visible` = '1' ORDER by `timestamp` DESC");
        $query->bindParam(':time', $time);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);        
        return $result;
    }
    
    /**
     * Permet de recuperer un array des id des groupes d'un utilisateur
     * 
     * @param type $uid
     * @return type
     */
    public function getGroupsByUser($uid){
        $query = $this->sql->prepare("SELECT `group` FROM `".BETA_MODE."yeti_group_membres` WHERE `owner` = :uid AND `visible` = 1 GROUP BY `group`;");
        $query->bindParam(':uid', $uid);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    
    public function getImageInfo($id){
        $query = $this->sql->prepare("SELECT *  FROM `".BETA_MODE."yeti_photo` WHERE `id` = :id LIMIT 1;");
        $query->bindParam(':id', $id);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    
    public function getPostLikers($id){
        $query = $this->sql->prepare("SELECT `owner` FROM `".BETA_MODE."yeti_post_like` WHERE  `post` = :id;");
        $query->bindParam(':id', $id);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_COLUMN,0);
        return $result;
    }
    
    public function getPostNbLike($id){
            $query_count = $this->sql->prepare("SELECT * FROM `".BETA_MODE."yeti_post_like` WHERE `post` = :post_id;");
            $query_count->bindParam(':post_id', $id);
            $query_count->execute();
            $nb = $query_count->rowCount();
            return $nb;
    }
    
    public function getPostComments($id){
        $query = $this->sql->prepare("SELECT * FROM `".BETA_MODE."yeti_post_comment` WHERE `post` = :post_id AND `visible` = '1' ORDER BY `timestamp` DESC LIMIT 0,30;");
        $query->bindParam(':post_id', $id);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    /**
     * Permet de recuperer les informations d'un groupe par son id
     * 
     * @param type $id
     * @return type
     */
    public function getGroupInfo($id)
    {
        $query = $this->sql->prepare("SELECT *  FROM `".BETA_MODE."yeti_group` WHERE `id` = :id LIMIT 1;");
        $query->bindParam(':id', $id);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    
    /**
     * Permet de recuperer les informations de l'utilisateur grâce à son email
     * et mot de passe.
     * 
     * @param type $mail
     * @param type $password
     * @return type
     */
    public function getUserDataByLogin($mail,$password){
        $pass = hash('sha256',$password);
        $query = $this->sql->prepare("SELECT * FROM `".BETA_MODE."yeti_user` WHERE `pass` = :pass AND (`mail` = :mail1 XOR `mail` = :mail2) LIMIT 1;");
        $query->bindParam(':mail1', $mail);
        $mail2 = $mail."@ensta-bretagne.org";
        $query->bindParam(':mail2', $mail2);
        $query->bindParam(':pass', $pass);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    
    /**
     * Permet de recuperer les informations de l'utilisateur grâce à son id
     * 
     * @param type $id
     * @return type
     */
    public function getUserDataById($uid){
        $query = $this->sql->prepare("SELECT * FROM `".BETA_MODE."yeti_user` WHERE `id` = :uid LIMIT 1;");
        $query->bindParam(':uid', $uid);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    
    /* -------------------------- Insert Request ------------------------------ */
    
    public function insertNewHashtag($hashtag){
        $tag = htmlspecialchars($hashtag, ENT_QUOTES);
        $tag = '#'.trim( $tag, '#');

        $query = $this->sql->prepare("SELECT * FROM `".BETA_MODE."yeti_tag` WHERE `tag` = :tag;");
        $query->bindParam(':tag', $tag);
        $query->execute();
        $nb = $query->rowCount();

        if($nb == 0)
        {
            $query_1 = $this->sql->prepare("INSERT INTO  `".BETA_MODE."yeti_tag` (`id` ,`tag`, `use`)	VALUES (NULL ,  :tag, 1);");
            $query_1->bindParam(':tag', $tag);
            $query_1->execute();
            return true;
        }

        return false;
    }
    
    public function updateUseHashtag($hashtag){
        $query = $this->sql->prepare("UPDATE `".BETA_MODE."yeti_tag` SET `use`= `use`+1 WHERE `tag`= :hashtag");
        $query->bindParam(':hashtag', $hashtag);
        $query->execute();
    }
	
    
    public function insertNewPhoto($filename, $size){
            $md5 = md5_file("uploads/".$filename);		
            $time = time();
            $query = $this->sql->prepare("INSERT INTO `".BETA_MODE."yeti_photo` (`id`,`md5`, `size`, `owner`, `filename`, `timestamp`) VALUES (NULL, :md5, :size, :user_id, :filename, :time);");
            $query->bindParam(':user_id', $_SESSION['id']);
            $query->bindParam(':md5', $md5);
            $query->bindParam(':size', $size);
            $query->bindParam(':filename', $filename);
            $query->bindParam(':time', $time);
            $query->execute();
            return $this->sql->lastInsertId();
    }
    
    public function insertNewPost($postText,$postTag,$postYoutube,$postImage,$postGroup,$public){
        $time = time();
        $query_1 = $this->sql->prepare("INSERT INTO `".BETA_MODE."yeti_post` (`id`, `owner`, `timestamp`, `text`, `group`, `tag`, `image`, `edit`, `public`, `youtube`, `visible`)
                                                        VALUES (NULL, :user_id, :time, :text, :group_id, :tag, :image_id, '0', :public, :youtube, '1');");
        $query_1->bindParam(':user_id', $_SESSION['id']);
        $query_1->bindParam(':time', $time);
        $query_1->bindParam(':text', $postText);
        $query_1->bindParam(':group_id', $postGroup);
        $query_1->bindParam(':tag', $postTag);
        $query_1->bindParam(':image_id', $postImage);
        $query_1->bindParam(':public', $public);
        $query_1->bindParam(':youtube', $postYoutube);
        $query_1->execute();
    }
    
    public function insertNewShout($msg){
        $time = time();
        $query = $this->sql->prepare("INSERT INTO  `".BETA_MODE."yeti_shout` (`id` ,`owner` ,`text` ,`timestamp` ,`visible`) VALUES (NULL , :user,  :msg,  :time,  '1');");
        $query->bindParam(':user', $_SESSION['id']);
        $query->bindParam(':msg', $msg);
        $query->bindParam(':time', $time);
        $query->execute();        
    }
    
    public function insertNewUser($mail, $password, $hash){
        $pass = hash('sha256',$password,false);
        $time = time();
        $user = strstr($mail, '@', true); 
        $user = str_replace("_", " ", $user);
        $data = explode('.', $user);
        $data = array_map( 'ucfirst', $data);
        $query = $this->sql->prepare("INSERT INTO `".BETA_MODE."yeti_user` (`id`, `mail`, `nom`, `prenom`, `pass`, `hash`, `timestamp`) VALUES (NULL, :mail, :nom, :prenom, :pass, :hash, :time);");
        $query->bindParam(':mail', $mail);
        $query->bindParam(':pass', $pass);
        $query->bindParam(':nom', $data[1]);
        $query->bindParam(':prenom', $data[0]);
        $query->bindParam(':hash', $hash);
        $query->bindParam(':time', $time);
        $query->execute();
    }
    
    public function deleteLike($id){
        $query = $this->sql->prepare("DELETE FROM `".BETA_MODE."yeti_post_like` WHERE  `owner` = :user AND `post` = :post_id;");
        $query->bindParam(':user', $_SESSION['id']);
        $query->bindParam(':post_id', $id);
        $query->execute();
    }
    
    public function createLike($id){       
        $time = time();
        $query = $this->sql->prepare("INSERT INTO `".BETA_MODE."yeti_post_like` (`id` ,`owner` ,`post` ,`timestamp`) VALUES (NULL , :user_id, :post_id, :time);");
        $query->bindParam(':post_id', $id);
        $query->bindParam(':user_id', $_SESSION['id']);
        $query->bindParam(':time', $time);
        $query->execute();
    }
    
    /* --------------------------- Check Request ------------------------------ */
    
    
    
    public function checkPostisLiked($id){
        $query_count = $this->sql->prepare("SELECT * FROM `".BETA_MODE."yeti_post_like` WHERE `owner` = :user AND `post` = :post_id;");
        $query_count->bindParam(':user', $_SESSION['id']);
        $query_count->bindParam(':post_id', $id);
        $query_count->execute();
        $nb = $query_count->rowCount();
        if($nb == 1){
            return true;
        }
        return false;   
    }
    
    /**
     * Permet de verifier si le couple email/password est correct
     * 
     * @param type $mail
     * @param type $password
     * @return boolean
     */
    public function checkCredentials($mail,$password)
    {
        $pass = hash('sha256',$password);
        $query = $this->sql->prepare("SELECT * FROM `".BETA_MODE."yeti_user` WHERE `pass` = :pass AND (`mail` = :mail1 XOR `mail` = :mail2);");
        $query->bindParam(':mail1', $mail);
        $mail2 = $mail."@ensta-bretagne.org";
        $query->bindParam(':mail2', $mail2);
        $query->bindParam(':pass', $pass);
        $query->execute();
        $nb = $query->rowCount();
        if($nb == 1){
            return true;
        }
        return false;
    }
    
    
    /**
     * Permet de verifier si un utilisateur a activé son compte 
     * 
     * @param type $mail
     * @param type $password
     * @return boolean
     */
    public function checkUserActivate($mail)
    {
        $query = $this->sql->prepare("SELECT * FROM `".BETA_MODE."yeti_user` WHERE `hash` = '' AND (`mail` = :mail1 XOR `mail` = :mail2);");
        $query->bindParam(':mail1', $mail);
        $mail2 = $mail."@ensta-bretagne.org";
        $query->bindParam(':mail2', $mail2);
        $query->execute();
        $nb = $query->rowCount();
        if($nb == 1){
            return true;
        }
        return false;
    }
    
    /**
     * Permet de verifier si un utilisateur existe
     * 
     * @param type $mail
     * @return boolean
     */
    public function checkExistEmail($mail)
    {
        $q = "SELECT `mail` FROM `".BETA_MODE."yeti_user` WHERE `mail` = :mail LIMIT 1;";		
        $query = $this->sql->prepare($q);
        $query->bindParam(":mail",$mail);
        $query->execute();        
        $result = $query->rowCount();         
        if($result == 1){
            return true;
        }
        return false;
    }
    
    /**
     * Permet de verifier si un hash d'activation existe
     * 
     * @param type $hash
     * @return boolean
     */
    public function checkExistHash($hash){
        $query = $this->sql->prepare("SELECT * FROM `".BETA_MODE."yeti_user` WHERE `hash` = :hash LIMIT 1;");
        $query->bindParam(':hash', $hash);
        $query->execute();
        $result = $query->rowCount();
        if($result == 1){
            return true;
        }
        return false;
    }
    
    /* -------------------------- Action Request ------------------------------ */
    
    /**
     * Permet de valider un hash
     * 
     * @param type $hash
     * @return boolean 
     */
    public function validateHash($hash)
    {       
        $query = $this->sql->prepare("UPDATE  `".BETA_MODE."yeti_user` SET  `hash` =  '' WHERE  `hash` = :hash;");
        $query->bindParam(':hash', $hash);
        $query->execute();           
    }
    
    /**
     * Permet de changer le mot de passe a partir d'un email
     * 
     * @param type $mail
     * @param type $password
     */
    public function changePasswordByEmail($mail,$password)
    {
        $query = $this->sql->prepare("UPDATE  `".BETA_MODE."yeti_user` SET  `pass` =  :pass WHERE  `mail` = :mail;");
        $query->bindParam(':pass', $password);
        $query->bindParam(':mail', $mail);
        $query->execute();        
    }
    
}
