<?php

$isANewInstall = !file_exists('../videos/configuration.php');

// Resolve admin password — never use the insecure default.
$_resolvedAdminPass = getenv("SYSTEM_ADMIN_PASSWORD");
if (empty($_resolvedAdminPass) || $_resolvedAdminPass === 'password') {
    $_resolvedAdminPass = bin2hex(random_bytes(16));
    $passwordFile = '/var/www/html/AVideo/videos/.initial_admin_password';
    @file_put_contents($passwordFile, $_resolvedAdminPass . PHP_EOL);
    @chmod($passwordFile, 0600);
    error_log("==================================================");
    error_log("AVIDEO: SYSTEM_ADMIN_PASSWORD was not set or was");
    error_log("        left at the insecure default 'password'.");
    error_log("        A random password has been generated.");
    error_log("GENERATED ADMIN PASSWORD: " . $_resolvedAdminPass);
    error_log("Saved to: " . $passwordFile);
    error_log("==================================================");
}

$_POST["systemRootPath"] = "/var/www/html/AVideo/";
$_POST["databaseHost"] = getenv("DB_MYSQL_HOST");
$_POST["databasePort"] = getenv("DB_MYSQL_PORT");
$_POST["databaseName"] = getenv("DB_MYSQL_NAME");
$_POST["databaseUser"] = getenv("DB_MYSQL_USER");
$_POST["databasePass"] = getenv("DB_MYSQL_PASSWORD");
$_POST["createTables"] = 1;
$_POST["contactEmail"] = getenv("CONTACT_EMAIL");
$_POST["systemAdminPass"] = $_resolvedAdminPass;
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
$_POST['systemAdminPass'] = $_resolvedAdminPass;
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
