<?php

/**
 * Description of api
 *
 * @author Charlie
 */
class Api {
    var $_DATA;
    public $isLogged;
    
    function __construct(){
        
        $this->_DATA = $_GET;
        $this->isLogged = $this->checkIsLogged();
        if(isset($this->_DATA["a"])){
            switch ($this->_DATA["a"]){
                case "login":
                    echo $this->login();
                    break;
                case "register":                    
                    echo $this->register();
                    break;
                case "activation":
                    echo $this->activation();
                    break;
                case "recovery":
                    echo $this->recovery();
                    break;
                case "logout":
                    echo $this->logout();
                    break;
                case "insertnewshout":
                    echo $this->insertNewShout();
                    break;
                case "getallshout":
                    echo $this->getAllShout();
                    break;
                case "getlastshout":
                    echo $this->getLastShout();
                    break;
            }
        }    
    }
    
    private function getAllShout(){
        global $database;
        if(!$this->isLogged){
            return $this->returnJson(0, array("logout"=>"Vous n'êtes pas connecté"));
        }
        $shouts = $database->getShouts();
        $nbShout = count($shouts);
        if($nbShout == 0){
                return $this->returnJson(1,  array("lastshout"=>"Aucun message"));
        } else {
            $allShout =array();
            foreach($shouts as $shout){
                if($_SESSION["id"] == $shout["owner"]){
                    $who = "Vous";
                }else{
                    $ownerData = $database->getUserDataById($shout["owner"]);
                    $who = $ownerData["prenom"]." ".$ownerData["nom"];
                }
                $allShout[] = array("owner" => $who, "text" => $shout["text"], "timestamp" => $shout["timestamp"]);
            }
            return $this->returnJson(1,  array("allshout"=>$allShout));
        }
    }
    
    private function getLastShout(){
        global $database;
        if(!$this->isLogged){
            return $this->returnJson(0, array("logout"=>"Vous n'êtes pas connecté"));
        }
        $nb = $this->sanitize(@$this->_DATA['nb']);
        if(is_numeric($nb)){                  
            $shouts = $database->getShouts();
            $nbShout = count($shouts);
            if($nbShout == 0){
                return $this->returnJson(1,  array("lastshout"=>"Aucun message"));
            } else if($nbShout > $nb){
                $lastShout = array_slice($shouts,0,($nbShout-$nb));
                $allShout =array();
                foreach($lastShout as $shout){
                    if($_SESSION["id"] == $shout["owner"]){
                        $who = "Vous";
                    }else{
                        $ownerData = $database->getUserDataById($shout["owner"]);
                        $who = $ownerData["prenom"]." ".$ownerData["nom"];
                    }
                    $allShout[] = array("owner" => $who, "text" => $shout["text"], "timestamp" => $shout["timestamp"]);
                }
                return $this->returnJson(1,  array("lastshout"=>$allShout));
            } else {
                return $this->returnJson(0, array("lastshout"=>"nb est incorrect"));
            }
              
        }
        return $this->returnJson(0, array("lastshout"=>"nb doit être un nombre"));
    }
    
    private function insertNewShout(){
        global $database;
        if(!$this->isLogged){
            return $this->returnJson(0, array("logout"=>"Vous n'êtes pas connecté"));
        }
        $returnArray = array();
        $shout = $this->sanitize(@$this->_DATA['m']);
        if($shout == ""){
             $returnArray[]= array("insertnewshout"=>"Veuillez entrer un message");
        }
        
        if(count($returnArray) > 0){
            return $this->returnJson(0, $returnArray);
        }
        else{            
            $database->insertNewShout($shout);    
            return $this->returnJson(1,  array("insertnewshout"=>"ok"));
        }
    }
    
    /**
     * Permet de gerer une deconnexion
     */
    private function logout(){
        if(!$this->isLogged){
            return $this->returnJson(0, array("logout"=>"Vous n'êtes pas connecté"));
        }
        $_SESSION = array();
        session_destroy();
        session_start();
        return $this->returnJson(1, array("logout"=>"Vous avez été déconnecté"));
    }
    
    private function recovery(){
        global $database;
        if($this->isLogged){
            return $this->returnJson(0, array("recovery"=>"Vous êtes connecté"));
        }
        $mail = $this->sanitize(@$this->_DATA['mail']);
        $returnArray = array();
        if($mail == ""){
             $returnArray[]= array("recover"=>"Veuillez entrer une adresse email");
        }
        elseif(!$database->checkExistEmail($mail)){
             $returnArray[]= array("recover"=>"Adresse email incorrecte");
        }
        
        if(count($returnArray) > 0){
            return $this->returnJson(0, $returnArray);
        }
        else{
            $new_pass = substr(md5(uniqid(rand(), true)), 0, 8);
            $encrypted_pass = hash('sha256',$new_pass,false);
            $database->changePasswordByEmail($mail,$encrypted_pass);            
            include_once('mailer.engine.php');
                $mailer = new Mailer();
                $mailer->sendMailRecovery($mail,$new_pass);                
            return $this->returnJson(1,  array("recover"=>"Votre mot de passe a bien été réinitialisé. Vous allez recevoir un mail d'ici quelques minutes."));
        }
    }
    
    private function activation(){
        global $database;
        if($this->isLogged){
            return $this->returnJson(0, array("activation"=>"Vous êtes connecté"));
        }
        $hash = $this->sanitize(@$this->_DATA["hash"]);
        $returnArray = array();
        if($hash == ""){
            $returnArray[]= array("hash"=>"Veuillez entrer un hash");
        }
        else if(!$database->checkExistHash($hash)){
            $returnArray[]= array("hash"=>"Le hash est incorrect");
        }        
        if(count($returnArray) > 0){
            return $this->returnJson(0, $returnArray);
        }
        else{
                $database->validateHash($hash);
                return $this->returnJson(1,  array("activation"=>"Votre inscription a bien été validée."));
        }
    }
    
    private function register(){
        global $database;
        if($this->isLogged){
            return $this->returnJson(0, array("register" => "Vous êtes connecté"));
        }
        $password = $this->sanitize(@$this->_DATA['pw']);
        $email =  $this->sanitize(@$this->_DATA['mail']); 
        $returnArray = array();
        //Check Email
        if(!isset($email) || $email == ""){
            $returnArray[]= array("mail"=>"Veuillez entrer une adresse email");
        }
        else{
            if(!$this->validEmail($email)){
                 $returnArray[]= array("mail" => "Seul les adresses @ensta-bretagne.org sont autorisées");
            }
            else if($database->checkExistEmail($email)){
                $returnArray[]= array("mail" => "Cette adresse email est déjà prise");
            }
        }        
        //Check password
        if(!isset($password) || $password == ""){
            $returnArray[]= array("pw" => "Veuillez indiquer un mot de passe");
        }
        else{
            if(strlen($password) < PASSWORD_LENGTH){
                $returnArray[]= array("pw"=>"Mot de passe trop court");
            }
        }
        //Check CGU
        if(!isset($this->_DATA['cgu'])){
                $returnArray[]= array("cgu"=>"Veuillez accepter les CGU");
        }        
        if(count($returnArray) > 0){
            return $this->returnJson(0, $returnArray);
        }
        else{
            $hash = substr(md5(uniqid(rand(), true)), 16, 16);            
            $database->insertNewUser($email,$password,$hash);            
            include_once('mailer.engine.php');
                $mailer = new Mailer();
                $mailer->sendMailRegisteration($email, $password, $hash);            
            return $this->returnJson(1,  array("register"=>"Un mail de confirmation t'as été envoyé avec le code de validation."));
        }
    }    
    
    private function login(){
        global $database;
        if($this->isLogged){
            return $this->returnJson(0, array("login"=>"Vous êtes connecté"));
        }
        $mail = $this->sanitize(@$this->_DATA['mail']);
        $password = $this->sanitize(@$this->_DATA['pw']);
        
        $returnArray = array();
        
        if(!isset($mail) || $mail == ""){
            $returnArray[] = array("email" => "Veuillez entrer une adresse email");
        }
        elseif(!$database->checkExistEmail($mail) && !$database->checkExistEmail($mail."@ensta-bretagne.org")){
           $returnArray[] = array("email" =>"Adresse email introuvable");
        }
        elseif(!$database->checkUserActivate($mail)){
            $returnArray[] = array("activate" => 'Votre compte n\'est pas encore activé.');
        }
        
        if(!isset($password) ||$password == ""){
           $returnArray[] = array("pw" => "Veuillez entrer un mot de passe");
        }
        elseif(!$database->checkCredentials($mail,$password)){
           $returnArray[] = array("pw" => "Combinaison email/mot de passe incorrecte");
        }
        
        if(count($returnArray) > 0){
            return $this->returnJson(0, $returnArray);
        }
        else{
            $userData = $database->getUserDataByLogin($mail,$password);
            $_SESSION['id'] = $userData['id'];
            $_SESSION['nom'] = $userData['nom'];
            $_SESSION['prenom'] = $userData['prenom'];
            $_SESSION['mail'] = $userData['mail'];
            $_SESSION['promo'] = $userData['promo'];
            $_SESSION['anniversaire'] = $userData['anniversaire'];
            $_SESSION['image'] = $userData['image'];      
            $_SESSION['active_group'] = $database->getGroupsByUser($userData['id']);
            $_SESSION['active_albums'] = true;
            $_SESSION['active_group'][] = '0';
            $_SESSION['group'] = $_SESSION['active_group'];           
            return $this->returnJson(1,  $_SESSION);
        }
    }
    
    /**
     * Permet de verifier le format de l'adresse mail
     * 
     * @param type $email
     * @return boolean
     */
    private function validEmail($email){
        $regexp="/(.+)\.(.+)(\@ensta-bretagne.org)/";
        if ( !preg_match($regexp, $email) ) {
            return false;
        }
        return true;
    }
    
    /**
     * Permet de retourner le message
     * 
     * @param type $code
     * @param type $dataArray
     */
    private function returnJson($code,$dataArray){
        $returnValue = array("code" => $code, "data" => $dataArray);
        return json_encode($returnValue);   
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
    
    /**
     * Permet de verifier si l'utilisateur est loggé
     * 
     * @global type $_SESSION
     * @return boolean
     */
    private function checkIsLogged(){
        global $_SESSION;
        if(isset($_SESSION["id"]) && is_numeric($_SESSION["id"])) {
            return true;
        }
        return false;
    }
}
