<?php

class Account {
    
    public $isLogged;
    
    function __construct(){
        $this->isLogged = $this->checkIsLogged();        
        if(isset($_POST["a"])){
            switch (@$_POST["a"]){
                case "login":
                    $this->login();
                    break;
                case "register":
                    $this->register();
                    break;
                case "activation":
                    $this->activation();
                    break;
                case "recovery":
                    $this->recovery();
                    break;                
            }
        }        
        if(isset($_GET["a"]) && @$_GET["a"] == "activation" && isset($_GET["hash"])){
            $this->activation();
        }
        $this->checkSurfUrl();
    }
    
    
    /**
     * Permet de gerer une connexion
     * 
     */
    private function login(){
        global $database,$form;
        $mail = $this->sanitize($_POST['mail']);
        $password = $this->sanitize($_POST['pw']);
        
        if(!isset($mail) || $mail == ""){
            $form->addError("email","Veuillez entrer une adresse email");
        }
        elseif(!$database->checkExistEmail($mail) && !$database->checkExistEmail($mail."@ensta-bretagne.org")){
            $form->addError("email","Adresse email introuvable");
        }
        elseif(!$database->checkUserActivate($mail)){
            $form->addError("activate",'Votre compte n\'est pas encore activé. <a href="activate.php">Activer votre compte</a>');
        }
        
        if(!isset($password) ||$password == ""){
            $form->addError("pw","Veuillez entrer un mot de passe");
        }
        elseif(!$database->checkCredentials($mail,$password)){
            $form->addError("pw","Combinaison email/mot de passe incorrecte");
        }
        
        if($form->returnErrors() > 0){
                $_SESSION['errorarray'] = $form->getErrors();
                $_SESSION['valuearray'] = $_POST;
                header("Location: login.php");
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
            header("Location: index.php");
        }
        
    }
    
    /**
     * Permet de gerer une inscription
     * 
     * @global type $database
     * @global type $form
     */
    private function register(){
        global $database,$form;
        $password = $this->sanitize($_POST['pw']);
        $email =  $this->sanitize($_POST['mail']);        
        //Check Email
        if(!isset($email) || $email == ""){
            $form->addError("mail","Veuillez entrer une adresse email");
        }
        else{
            if(!$this->validEmail($email)){
                $form->addError("mail","Seul les adresses @ensta-bretagne.org sont autorisées");
            }
            else if($database->checkExistEmail($email)){
                $form->addError("mail","Cette adresse email est déjà prise");
            }
        }        
        //Check password
        if(!isset($password) || $password == ""){
            $form->addError("pw","Veuillez indiquer un mot de passe");
        }
        else{
            if(strlen($password) < PASSWORD_LENGTH){
                $form->addError("pw","Mot de passe trop court");
            }
        }
        //Check CGU
        if(!isset($_POST['cgu'])){
                $form->addError("cgu","Veuillez accepter les CGU");
        }        
        if($form->returnErrors() > 0){
                $_SESSION['errorarray'] = $form->getErrors();
                $_SESSION['valuearray'] = $_POST;
                header("Location: register.php");
        }
        else{
            $hash = substr(md5(uniqid(rand(), true)), 16, 16);            
            $database->insertNewUser($email,$password,$hash);            
            include_once('mailer.engine.php');
                $mailer = new Mailer();
                $mailer->sendMailRegisteration($email, $password, $hash);            
            header("Location: activation.php");
        }
    }
    
    /**
     * Permet de gerer une activation de compte
     * 
     * @global type $database
     * @global type $form
     */
    private function activation(){
        global $database, $form;
        
        if(isset($_GET["hash"])){
            $hash = $this->sanitize(@$_GET["hash"]);
        } else{
            $hash = $this->sanitize(@$_POST["hash"]);
        }
        
        if($hash == ""){
            $form->addError("hash","Veuillez entrer un hash");
        }
        else if(!$database->checkExistHash($hash)){
            $form->addError("hash","Le hash est incorrect");
        }
        
        if($form->returnErrors() > 0){ 
                $_SESSION['errorarray'] = $form->getErrors();
                $_SESSION['valuearray'] = $_POST;
                header("Location: activation.php");
        }
        else{
                $database->validateHash($hash);
                header("Location: login.php?a=activated");
        }
    }
    
    /**
     * Permet de gerer une demande de mot de passe perdu
     * 
     * @global type $database
     * @global type $form
     */
    private function recovery(){
        global $database,$form;
        $mail = $this->sanitize(@$_POST['mail']);
        
        if($mail == ""){
            $form->addError("recover","Veuillez entrer une adresse email");
        }
        elseif(!$database->checkExistEmail($mail)){
            $form->addError("recover","Adresse email incorrecte");
        }
        
        if($form->returnErrors() > 0){
            $_SESSION['errorarray'] = $form->getErrors();
            $_SESSION['valuearray'] = $_POST;
            header("Location: recovery.php");
        }
        else{
            $new_pass = substr(md5(uniqid(rand(), true)), 0, 8);
            $encrypted_pass = hash('sha256',$new_pass,false);
            $database->changePasswordByEmail($mail,$encrypted_pass);            
            include_once('mailer.engine.php');
                $mailer = new Mailer();
                $mailer->sendMailRecovery($mail,$new_pass);                
            header("Location: login.php?a=recovery");
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
    
    /**
     * Permet de verifier que l'utilisateur demande les bonnes pages
     * 
     */
    private function checkSurfUrl(){        
        $explode = explode("/", $_SERVER['SCRIPT_NAME']);
        $i = count($explode) - 1;
        $page = $explode[$i];                
        $page_array = array("login.php", "register.php", "activation.php", "recovery.php");
        
        if(!$this->isLogged){
            if(!in_array($page, $page_array)){
                header("Location: login.php");
            }
        }
        else{
            if(in_array($page, $page_array) ){
                header("Location: index.php");
            }
        }        
    }
}
