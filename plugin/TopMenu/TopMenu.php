<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/Menu.php';
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/MenuItem.php';

use Pecee\SimpleRouter\SimpleRouter; //required if we want to define routes on our plugin.


class TopMenu extends PluginAbstract {
    const PERMISSION_CAN_EDIT = 0;


    public function getTags() {
        return array(
            PluginTags::$FREE,
        );
    }
    public function getDescription() {
        $txt = "Responsive Customized Top Menu";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/How-to-use-TopMenu-Plug-in' target='__blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        return $txt.$help;
    }

    public function getName() {
        return "TopMenu";
    }

    public function getUUID() {
        return "2e7866ed-2e02-4136-bec6-4cd90754e3a2";
    }    
    
    public function getPluginVersion() {
        return "2.1";   
    }
    
    public function updateScript() {
        global $mysqlDatabase;
        //update version 2.0
        $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? and COLUMN_NAME=?";
        $res = sqlDAL::readSql($sql,"s",array($mysqlDatabase, "topMenu_items", "menuSeoUrlItem"));
        $menuSeoUrlItem=sqlDAL::fetchAssoc($res);
        if(!$menuSeoUrlItem){
            sqlDal::writeSql("alter table topMenu_items add menuSeoUrlItem varchar(255) default ''"); 
        }
        return true;
    }
    
    public function addRoutes()
    {
        global $basePath; 
        SimpleRouter::get($basePath."/menu/{menuSeoUrlItem}", function($menuSeoUrlItem) {
            $_GET['menuSeoUrlItem']=$menuSeoUrlItem;
            require_once "plugin/TopMenu/seo.php";
        },['defaultParameterRegex' => '.*']);
        return false;
    }

    public function getHeadCode() {
        global $global;
        $css = '<link href="' .getCDN() . 'plugin/TopMenu/style.css" rel="stylesheet" type="text/css"/>';
        return $css;
    }
    
    public function getHTMLMenuRight() {
        global $global;
        include $global['systemRootPath'] . 'plugin/TopMenu/HTMLMenuRight.php';
    }
        
    public function getPluginMenu() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/TopMenu/pluginMenu.html';
        return file_get_contents($filename);
    }
    
    public function getHTMLMenuLeft() {
        global $global;
        include $global['systemRootPath'] . 'plugin/TopMenu/HTMLMenuLeft.php';
    }
    
    public function getidBySeoUrl($menuSeoUrlItem) {
        global $global;
        $sql="select id from topMenu_items where menuSeoUrlItem= ?"; 
        $res=sqlDal::readSql($sql, "s", array($global['mysqli']->real_escape_string($menuSeoUrlItem)));
        $menuId=sqlDAL::fetchAssoc($res);
        if(!isset($menuId['id']))
        return false;
        return $menuId['id'];
    }
    
    function getPermissionsOptions(){
        $permissions = array();
        $permissions[] = new PluginPermissionOption(TopMenu::PERMISSION_CAN_EDIT, __("TopMenu"), __("Can edit TopMenu plugin"), 'TopMenu');
        return $permissions;
    }
    
    static function canAdminTopMenu(){
        return Permissions::hasPermission(TopMenu::PERMISSION_CAN_EDIT,'TopMenu');
    }
    
    public function getGalleryActionButton($videos_id) {
        global $global;
        $obj = $this->getDataObject();
        include $global['systemRootPath'] . 'plugin/TopMenu/actionButtonGallery.php';
    }

    public function getNetflixActionButton($videos_id) {
        global $global;
        $obj = $this->getDataObject();
        include $global['systemRootPath'] . 'plugin/TopMenu/actionButtonNetflix.php';
    }
    
    public function getWatchActionButton($videos_id) {
        global $global, $video;
        $obj = $this->getDataObject();
        include $global['systemRootPath'] . 'plugin/TopMenu/actionButtonNetflix.php';
    }
    
    static function getExternalOptionName($menu_item_id){
        return "menu_url_{$menu_item_id}";
    }
    

    static function setVideoMenuURL($videos_id, $menu_item_id, $url) {
        $video = new Video('', '', $videos_id);
        $externalOptions = _json_decode($video->getExternalOptions());        
        $parameterName = self::getExternalOptionName($menu_item_id);
        $externalOptions->$parameterName = $url;
        $video->setExternalOptions(json_encode($externalOptions));
        return $video->save();
    }

    static function getVideoMenuURL($videos_id, $menu_item_id) {
        global $_getVideoMenuURL;
        if(!isset($_getVideoMenuURL)){
            $_getVideoMenuURL = array();
        }
        $index = "{$videos_id}_{$menu_item_id}";
        if(!empty($_getVideoMenuURL[$index])){
            return $_getVideoMenuURL[$index];
        }
        $video = new Video('', '', $videos_id);
             
        $parameterName = self::getExternalOptionName($menu_item_id);
        $externalOptions = _json_decode($video->getExternalOptions());
        if(empty($externalOptions)){
            $externalOptions = new stdClass();
        }
        if(!isset($externalOptions->$parameterName)){
            $externalOptions->$parameterName = '';
        }
        $_getVideoMenuURL[$index] = $externalOptions->$parameterName;
        return $_getVideoMenuURL[$index];
    }
        
    public function getVideosManagerListButton() {
        if (!User::canUpload()) {
            return "";
        }
        
        $obj = $this->getDataObject();
        $btn = '';
        
        $btn .= '<button type="button" class="btn btn-primary btn-light btn-sm btn-xs btn-block" onclick="avideoModalIframeSmall(webSiteRootURL+\\\'plugin/TopMenu/addVideoInfo.php?videos_id=\'+row.id+\'\\\');" ><i class="fas fa-edit"></i> Menu items</button>';

        return $btn;
    }
    
}
