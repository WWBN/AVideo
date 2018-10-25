<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

use Pecee\SimpleRouter\SimpleRouter; //required if we want to define routes on our plugin.


class TopMenu extends PluginAbstract {

    public function getDescription() {
        $txt = "Responsive Customized Top Menu";
        $help = "<br><small><a href='https://github.com/DanielnetoDotCom/YouPHPTube/wiki/How-to-use-TopMenu-Plug-in' target='__blank'><i class='fas fa-question-circle'></i> Help</a></small>";
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
        $css = '<link href="' . $global['webSiteRootURL'] . 'plugin/TopMenu/style.css" rel="stylesheet" type="text/css"/>';
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
    
    public function getTags() {
        return array('free');
    }
}
