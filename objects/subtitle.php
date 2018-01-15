<?php 

/**
* Data-Structure-Object representing the subtitle-Table in DB
**/
class Subtitle {                   
    private $id;
    private $language;
    private $filename;
    function __construct($id, $language, $filename){
        $this->id = $id;
        $this->language = $language;
        $this->filename = $filename;
    }
    
    function getId(){
        return $this->id;
    }
    
    function getLanguage(){
        return $this->language;
    }
    
    function getFilename(){
        return $this->filename;
    }

}

?>