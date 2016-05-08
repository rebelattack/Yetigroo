<?php

class Database {
    
    var $sql;              
    function __construct(){
        $options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
	$this->sql = new PDO( SQL_DNS, SQL_USER, SQL_PASS, $options );
    }
    
    
    /* ---------------------------- Get Request ------------------------------- */
    
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
        $query_count = $this->sql->prepare("SELECT * FROM `".BETA_MODE."yeti_user` WHERE `pass` = :pass AND (`mail` = :mail1 XOR `mail` = :mail2) LIMIT 1;");
        $query_count->bindParam(':mail1', $mail);
        $mail2 = $mail."@ensta-bretagne.org";
        $query_count->bindParam(':mail2', $mail2);
        $query_count->bindParam(':pass', $pass);
        $query_count->execute();
        $result = $query_count->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    
    /**
     * Permet de recuperer les informations de l'utilisateur grâce à son id
     * 
     * @param type $id
     * @return type
     */
    public function getUserDataById($uid){
        $query_count = $this->sql->prepare("SELECT * FROM `".BETA_MODE."yeti_user` WHERE `id` = :uid LIMIT 1;");
        $query_count->bindParam(':uid', $uid);
        $query_count->execute();
        $result = $query_count->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    
    /* -------------------------- Insert Request ------------------------------ */
    
    
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
    
    /* --------------------------- Check Request ------------------------------ */
    
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
        $query_count = $this->sql->prepare("SELECT * FROM `".BETA_MODE."yeti_user` WHERE `pass` = :pass AND (`mail` = :mail1 XOR `mail` = :mail2);");
        $query_count->bindParam(':mail1', $mail);
        $mail2 = $mail."@ensta-bretagne.org";
        $query_count->bindParam(':mail2', $mail2);
        $query_count->bindParam(':pass', $pass);
        $query_count->execute();
        $nb = $query_count->rowCount();
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
        $query_count = $this->sql->prepare("SELECT * FROM `".BETA_MODE."yeti_user` WHERE `hash` = '' AND (`mail` = :mail1 XOR `mail` = :mail2);");
        $query_count->bindParam(':mail1', $mail);
        $mail2 = $mail."@ensta-bretagne.org";
        $query_count->bindParam(':mail2', $mail2);
        $query_count->execute();
        $nb = $query_count->rowCount();
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
