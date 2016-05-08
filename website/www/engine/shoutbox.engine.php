<?php

/**
 * Description of shoutbox
 *
 * @author Charlie
 */
class Shoutbox {
    
    var $nb_shout;
    var $all_shout;
    
    function __construct() {
        global $database;
        $this->all_shout = $database->getShouts();
        $this->nb_shout = count($this->all_shout);
    }
    
    public function printLastShout($nb){
        global $database,$_SMILEYS;
        if($this->nb_shout > $nb){
            $last_shout = array_slice($this->all_shout,0,($this->nb_shout-$nb));            
            foreach($last_shout as $shout){
                include('../templates/shoutbox/shout.php');
            }
        }
        else{
            die("-1");
        }
    }
    
    public function printAllShout(){
        global $database,$_SMILEYS;
        if($this->nb_shout == 0){
            include('templates/shoutbox/noshout.php');
        }
        else{            
            foreach($this->all_shout as $shout){
                include('templates/shoutbox/shout.php');
            }
        }
    }
    
   
    
    public function getNbShout()
    {
        return $this->nb_shout;
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
