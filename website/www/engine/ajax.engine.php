<?php
session_start();
require_once("../../config.php");
require_once("database.engine.php");
    $database = new Database();
require_once("shoutbox.engine.php");
    

class Ajax {
    
    function __construct(){                       
        if(isset($_POST["a"])){
            switch(@$_POST["a"]){
                case "insertnewshout":
                   $this->insertnewShout();
                    break;
                case "getnewshout":
                   $this->getnewShout();
                    break; 
            }
        }   
    }
    
    private function insertnewShout(){
        global $database;
        $message = $this->sanitize(@$_POST["m"]);        
        $database->insertNewShout($message);               
    }
    
    private function getnewShout(){
        
        $shoutbox = new Shoutbox();
        if(is_numeric(@$_POST["nb"])){
            $shoutbox->printLastShout(@$_POST["nb"]);
        }                    
    }
    
    /**
     * Permet d'éviter les injections sql, toutes les variable $_POST et $_POST 
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

$ajax = new Ajax();