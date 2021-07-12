<?php

require_once '../objects/functions.php';
if (!isCommandLineInterface()) {
    die('Command Line only');
}
if (file_exists("../videos/configuration.php")) {
    die("Can not create configuration again: " . json_encode($_SERVER));
}


$databaseUser = "youphptube";
$databasePass = "youphptube";
if (version_compare(phpversion(), '7.2', '<')) {
    $databaseUser = "root";
}

$webSiteRootURL = @$argv[1];
$webSiteRootURL = preg_replace("/[^0-9a-z._\/:]/i", "", trim($webSiteRootURL));
$databaseUser = empty($argv[2])?$databaseUser:$argv[2];
$databasePass = empty($argv[3])?$databasePass:$argv[3];
$systemAdminPass = empty($argv[4])?"123":$argv[4];
$contactEmail = empty($argv[5])?"undefined@youremail.com":$argv[5];
if (!filter_var($webSiteRootURL, FILTER_VALIDATE_URL)) {
    if (!empty($webSiteRootURL)) {
        echo "Invalid Site URL ({$webSiteRootURL})\n";
    }
    echo "Enter Site URL\n";
    @ob_flush();
    $webSiteRootURL = trim(readline(""));
    if (!filter_var($webSiteRootURL, FILTER_VALIDATE_URL)) {
        die("Invalid Site URL ({$webSiteRootURL})\n");
    }
}

$webSiteRootURL = rtrim($webSiteRootURL, '/') . '/';

$_POST['systemRootPath'] = str_replace("install", "", getcwd());
if(!is_dir($_POST['systemRootPath'])){
    $_POST['systemRootPath'] = "/var/www/html/YouPHPTube/";
    if(!is_dir($_POST['systemRootPath'])){
        $_POST['systemRootPath'] = "/var/www/html/AVideo/";
    }
}


$_POST['databaseHost'] = "localhost";
$_POST['databaseUser'] = $databaseUser;
$_POST['databasePass'] = $databasePass;
$_POST['databasePort'] = "3306";
$_POST['databaseName'] = "AVideo_". preg_replace("/[^0-9a-z]/i", "", parse_url($webSiteRootURL, PHP_URL_HOST));
$_POST['createTables'] = 2;
$_POST['contactEmail'] = $contactEmail;
$_POST['systemAdminPass'] = $systemAdminPass;
$_POST['mainLanguage'] = "en";
$_POST['webSiteTitle'] = "AVideo";
$_POST['webSiteRootURL'] = $webSiteRootURL;

include './checkConfiguration.php';
