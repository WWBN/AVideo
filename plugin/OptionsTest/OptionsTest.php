<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

use Pecee\SimpleRouter\SimpleRouter; //required if we want to define routes on our plugin.

class OptionsTest extends PluginAbstract {

    public function getDescription() {
        global $global;
        return "Salmple object for new features <br />Author: <a href='https://github.com/Criptos' target='_blank' > Criptos</a>";
    }

    public function getName() {
        return "OptionsTest";
    }

    public function getUUID() {
        return "5d5b33e7-3192-4eb2-a4c1-4e62cc2e5d64";
    }
    
    public function getHelp(){
        $html = "<h2 id='optionsTest help' >optionsTest</h2><p>Test object for routes, custom getCustomizeAdvancedOptions and custom user Options</p><table class='table'><tbody>";
        return $html."</tbody></table>";
    }
    public function getJSFiles(){
        return array();
    }
    
    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->disclaimer = ""; 
        return $obj;        return $obj;
    }
    
    public function getHeadCode() {
    }
        
    public function getTags() {
        return array('sample');
    }
    
    public function addRoutes()
    {
        global $basePath; 
        SimpleRouter::get($basePath."OptionsTest/home", function() {
            require_once "plugin/OptionsTest/home.php"; exit;
        });
        return false;
    }
    
    public function getCustomizeAdvancedOptions()
    {
        $options["optionsTestVariable"]=false; 
        return $options;
    }
    
    public function getUserOptions()
    {
        $userOptions["Has Options Test"]="hasOptionsTest"; 
        $userOptions["Yes it does!"]="yesItDoes"; 
        return $userOptions;
    }
    
    public function navBarButtons()
    {
        global $global;
        $obj = $this->getDataObject();
        $buttons="";
        if (!User::isLogged()) {
            return "";
        }
        if(User::externalOptions("hasOptionsTest")){
            $buttons.="\n
                        <li>

                            <div>
                                <a href=\"".$global['webSiteRootURL']."OptionsTest/home\" class=\"btn btn-warning btn-block\" style=\"border-radius: 0;\">
                                    <span class= 'fa fa-certificate'></span> Option Test
                                </a>
                            </div>
                        </li>";
        }
        if(User::externalOptions("yesItDoes")){
            $buttons.="\n
                        <li>

                            <div>
                                <a href=\"".$global['webSiteRootURL']."OptionsTest/home\" class=\"btn btn-danger btn-block\" style=\"border-radius: 0;\">
                                    <span class= 'fa fa-chess-queen'></span> Yes it does!
                                </a>
                            </div>
                        </li>";
        }
        return $buttons;
    }    
}
 
