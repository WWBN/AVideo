<?php 
global $global;
require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] .  'plugin/YouPHPTubePlugin.php';

$topMenu=YouPHPTubePlugin::loadPluginIfEnabled("TopMenu");

if(!$topMenu)
die("404 page not found");


$id=$topMenu->getidBySeoUrl($_GET['menuSeoUrlItem']);
if(!$id)
die("404 page not found");

$_GET['id']=$id;
require_once $global['systemRootPath'] . 'plugin/TopMenu/index.php';
