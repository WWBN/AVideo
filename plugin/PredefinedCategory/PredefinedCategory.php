<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'objects/userGroups.php';

class PredefinedCategory extends PluginAbstract {

    public function getDescription() {
        $txt = "Choose what category the video goes when upload, encode or embed";
        $help = "<br><small><a href='https://github.com/DanielnetoDotCom/YouPHPTube/wiki/PredefinedCategory-Plugin' target='__blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        return $txt.$help;
    }

    public function getName() {
        return "PredefinedCategory";
    }

    public function getUUID() {
        return "b0d93ffa-9a92-4017-88fe-38a6597efaaa";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->defaultCategory = 1;
        $obj->userCategory = new stdClass();
        
        $groups = UserGroups::getAllUsersGroups();
        //import external plugins configuration options
        foreach ($groups as $value) {
            $obj->{"AddVideoOnGroup_[{$value['id']}]_"}=false;
        }
        
        return $obj;
    }

    public function getPluginMenu() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/PredefinedCategory/pluginMenu.html';
        return file_get_contents($filename);
    }
   
    public function getCategoryId() {
        global $global;       
        require_once $global['systemRootPath'] . 'objects/user.php';
        $obj = YouPHPTubePlugin::getObjectDataIfEnabled("PredefinedCategory");
        $id = $obj->defaultCategory;
        if(User::canUpload()){
            $user_id = User::getId();
            if(!empty($obj->userCategory->$user_id)){
                $id = $obj->userCategory->$user_id;
            }
        }
        return $id;
    }
    
    public function getUserGroupsArray(){
        $obj = $this->getDataObject();
        
        $videoGroups = array();
        foreach ($obj as $key => $value) {
            if($value===true){
                preg_match("/^AddVideoOnGroup_\[([0-9]+)\]_/", $key, $match);
                if(!empty($match[1])){
                    //check if group exists
                    $group=new UserGroups($match[1]);
                    if(!empty($group->getGroup_name())){
                        $videoGroups[] = $match[1];
                    }
                }
            }
        }
        return $videoGroups;
        
        
    }
    
    public function getTags() {
        return array('free');
    }

}
