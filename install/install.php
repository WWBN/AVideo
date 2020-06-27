<?php

require_once '../objects/functions.php';
if (!isCommandLineInterface()) {
    die('Command Line only');
}
if (file_exists("../videos/configuration.php")) {
    die("Can not create configuration again: " . json_encode($_SERVER));
}

$webSiteRootURL = @$argv[1];
$databaseUser = empty($argv[2])?"youphptube":$argv[2];
$databasePass = empty($argv[3])?"youphptube":$argv[3];
$systemAdminPass = empty($argv[4])?"123":$argv[4];
$contactEmail = empty($argv[5])?"undefined@youremail.com":$argv[5];
while (!filter_var($webSiteRootURL, FILTER_VALIDATE_URL)) {
    if (!empty($webSiteRootURL)) {
        echo "Invalid Site URL\n";
    }
    echo "Enter Site URL\n";
    ob_flush();
    $webSiteRootURL = trim(readline(""));
}


$_POST['systemRootPath'] = getPathToApplication();
$_POST['databaseHost'] = "localhost";
$_POST['databaseUser'] = $databaseUser;
$_POST['databasePass'] = $databasePass;
$_POST['databasePort'] = "3306";
$_POST['databaseName'] = "AVideoStreamer";
$_POST['createTables'] = 2;
$_POST['contactEmail'] = $contactEmail;
$_POST['systemAdminPass'] = $systemAdminPass;
$_POST['mainLanguage'] = "en";
$_POST['webSiteTitle'] = "AVideo";
$_POST['webSiteRootURL'] = $webSiteRootURL;

include './checkConfiguration.php';
