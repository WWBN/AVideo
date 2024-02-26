<?php

$isANewInstall = !file_exists('../videos/configuration.php');

$_POST["systemRootPath"] = "/var/www/html/AVideo/";
$_POST["databaseHost"] = getenv("DB_MYSQL_HOST");
$_POST["databasePort"] = getenv("DB_MYSQL_PORT");
$_POST["databaseName"] = getenv("DB_MYSQL_NAME");
$_POST["databaseUser"] = getenv("DB_MYSQL_USER");
$_POST["databasePass"] = getenv("DB_MYSQL_PASSWORD");
$_POST["createTables"] = 1;
$_POST["contactEmail"] = getenv("CONTACT_EMAIL");
$_POST["systemAdminPass"] = getenv("SYSTEM_ADMIN_PASSWORD");
$_POST["webSiteTitle"] = getenv("WEBSITE_TITLE");
$_POST["mainLanguage"] = getenv("MAIN_LANGUAGE");
$_POST["webSiteRootURL"] = "https://".getenv("SERVER_NAME")."/";

if($isANewInstall){
    require_once "./checkConfiguration.php";
    $argv[1] = 1;
    require_once "./installPluginsTables.php";
}

$_POST['systemRootPath'] = "{$_POST["systemRootPath"]}Encoder/";
$_POST["databaseHost"] = "{$_POST["databaseHost"]}_encoder";
$_POST["databaseName"] = "{$_POST["databaseName"]}_encoder";
$_POST['tablesPrefix'] = "";
$_POST['createTables'] = 1;
$_POST['systemAdminPass'] = getenv("SYSTEM_ADMIN_PASSWORD");
$_POST['inputUser'] = 'admin';
$_POST['inputPassword'] = $_POST['systemAdminPass'];
$_POST['webSiteTitle'] = "AVideo";
$_POST['siteURL'] = $_POST["webSiteRootURL"];
$_POST['webSiteRootURL'] = $_POST["webSiteRootURL"] . "Encoder/";
$_POST['allowedStreamers'] = $_POST['siteURL'];
$_POST['defaultPriority'] = 6;
chdir('../Encoder/install/');
$isANewInstall = !file_exists('../videos/configuration.php');

if($isANewInstall){    
    require_once "checkConfiguration.php";
}