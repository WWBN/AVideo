<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class ThemeSwitcherMenu extends PluginAbstract {

    public function getDescription() {
        return "Theme switcher";
    }

    public function getName() {
        return "ThemeSwitcherMenu";
    }

    public function getUUID() {
        return "24d29992-1d23-4268-ae5d-9727ad810c63";
    }
    
    
    public function getHTMLMenuRight() {
        global $global;
        include $global['systemRootPath'] . 'plugin/ThemeSwitcherMenu/menuRight.php';
    }

    
    public function getTags() {
        return array('free', 'menu', 'theme');
    }
    
    function getPage(){
        if(!empty($_GET['firstPage'])){
            if($_GET['firstPage']!=='Default'){
                $_SESSION['user']['firstPage'] = $_GET['firstPage'];
            }else{
                unset($_SESSION['user']['firstPage']);
            }
        }
        if(!empty($_SESSION['user']['firstPage'])){
            return $_SESSION['user']['firstPage'];
        }
        return false;
    } 
    
    
    function getTheme(){
        if(!empty($_GET['theme'])){
             $_SESSION['user']['theme'] = $_GET['theme'];
        }
        if(!empty($_SESSION['user']['theme'])){
            return $_SESSION['user']['theme'];
        }
        return false;
    } 
    
    
    public function getHeadCode() {
        global $global;
        $theme = $this->getTheme();
        $page = $this->getPage();
        
        $return = "";
        if(!empty($theme)){
            $return = "<link href=\"{$global['webSiteRootURL']}css/custom/{$theme}.css\" rel=\"stylesheet\" type=\"text/css\" id=\"theme\"/>";
        }
        
        if(!empty($page)){
            switch ($page) {
                case "YouPHPFlix":
                    $return .= "<link href=\"{$global['webSiteRootURL']}plugin/YouPHPFlix/view/css/style.css\" rel=\"stylesheet\" type=\"text/css\"/>";
                    break;
                case "FBTube":
                    $return .= '<link href="'.$global['webSiteRootURL'].'plugin/FBTube/view/style.css" rel="stylesheet" type="text/css"/>';
                    break;
            }
            
        }
        
        $return .= '<link href="'.$global['webSiteRootURL'].'css/main.css" rel="stylesheet" type="text/css"/>';

        
        return $return;
    }

    
    static function getCurrent(){
        //('Default','FBTube', 'Gallery', 'YouPHPFlix');
        global $global, $config;
        $firstPage = "";
        if(!empty($_SESSION['user']['firstPage'])){
            $p2 = YouPHPTubePlugin::loadPlugin($_SESSION['user']['firstPage']);
            $firstPage = $p2->getFirstPage();
        }
        
        
        $page = "Default";
        if(preg_match("/Gallery/i", $firstPage)){
            $page = "Gallery";
        }else if(preg_match("/FBTube/i", $firstPage)){
            $page = "FBTube";
        }else if(preg_match("/YouPHPFlix/i", $firstPage)){
            $page = "YouPHPFlix";
        }
        
        $theme = $config->getTheme();        
        $p = YouPHPTubePlugin::loadPlugin("ThemeSwitcherMenu");
        $t = $p->getTheme();
        if($t){
           $theme = $t; 
        }
        
        $obj = new stdClass();
        $obj->page = $page;
        $obj->theme = $theme;
        return $obj;
    }
    

}
