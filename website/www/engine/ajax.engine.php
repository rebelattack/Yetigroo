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
                case "toggleGroup":
                    $this->toggleGroup();
                    break;
                case "insertpostlike":
                    $this->postLike();
                    break;
            }
        }
        if(isset($_GET["a"]) && @$_GET["a"] == "autocomplete"){
            $this->autocomplete();
        }
    }
    
    private function postLike(){
        global $database;
        
        if(is_numeric(@$_POST["p"])){
           $id = @$_POST["p"];
           $listLikers = $database->getPostLikers($id);
           if(in_array($_SESSION["id"], $listLikers)){
               $database->deleteLike($id);
           } else {
               $database->createLike($id);
           }
        }
        
    }
    
    private function autocomplete(){
        global $database;
        $term = $this->sanitize(@$_POST["term"]);
        if(@$_GET["o"] != 'onlytag'){
            echo 1;
            $user_data = $database->autocompleteUser($term);
            foreach( $user_data as $user ){
                $row['value'] = $user['prenom'].' '.$user['nom'];
                $row['type'] = 'user';
                $row['id'] = $user['id'];
                $row_set[] = $row;
            }
        }

        if(@$_GET["o"] != 'onlyuser'){
            $tag_data = $database->autocompleteTag($term);
            foreach( $tag_data as $tag ){
                $row['value'] = $tag['tag'];
                $row['type'] = 'tag';
                $row['id'] = trim($tag['tag'],'#');
                $row_set[] = $row;
            }
        }
        echo json_encode($row_set);
        return json_encode($row_set);
    }
    
    private function toggleGroup(){        
        $group = $this->sanitize(@$_POST["g"]);        
        if($group == "albums"){
            $_SESSION['active_albums'] = !$_SESSION['active_albums'];
        }
        else if(is_numeric($group)){            
            if(in_array($group,$_SESSION['active_group'])){
                $active_groups = $_SESSION['active_group'];
                if(($key = array_search($group , $active_groups)) !== false){
                    unset($active_groups[$key]);
                    $_SESSION['active_group'] = $active_groups;
                }            
            }
            else{
                $groups = $_SESSION['group'];
                $active_groups = $_SESSION['active_group'];
                if((in_array($group,$groups) == true || $group == 0) && in_array($group,$active_groups) === false){
                    $_SESSION['active_group'][] = $group;
                }
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