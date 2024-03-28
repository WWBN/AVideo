<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$systemRootPath = __DIR__ . DIRECTORY_SEPARATOR . '../../';
require_once ($systemRootPath . "plugin/WWBNIndex/WWBNIndex.php");
$wwbnIndex = new WWBNIndex();

// if (!_mysql_is_open()) {
//     return false;
// }

if (!empty($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] !== 'localhost' && !filter_var($_SERVER['SERVER_NAME'], FILTER_VALIDATE_IP) && $wwbnIndex->check_site_availability($_SERVER['HTTP_HOST']) == 200) {

    $data = array(
        "apiName"           => "submitIndexUponInstall",
        "host"              => $_POST['webSiteRootURL'],
        "avideo_id"         => getPlatformId(),
        "engine_name"       => $_POST['webSiteTitle'],
        "engine_logo"       => $_POST['webSiteRootURL']. "view/img/logo.png",
        "engine_icon"       => $_POST['webSiteRootURL']. "view/img/favicon.png",
        "content_type"      => 4, // 1 = Text, 2 = Video, 3 = Audio, 4 = Audio and Video
        "feed_url"          => $_POST['webSiteRootURL']. "plugin/API/get.json.php?APIName=video&rowCount=20&search=[TERMS]",
        "detail_url"        => $_POST['webSiteRootURL']. "plugin/API/get.json.php?APIName=video&videos_id=[LID]",
        "affiliates"        => array(1), // 1 = searchtube
        "sitelinkid_fk"     => 2504, // WWBN
        "siteacctid_fk"     => 4541, // WWBN account id
        "acctkey_fk"        => null, // WWBN
        "validation_token"  => $wwbnIndex->getToken(),
        "email"             => $_POST['contactEmail'],
        "version"		    => $installationVersion,
        "users"			    => json_encode(array("admin")),
        "plugins"		    => "",
        "total_videos"	    => 0,
        "total_users"	    => 1,
        "total_channels"    => 1,
        "language"		    => $_POST['mainLanguage'],
    );
    
    $response = json_decode(postVariables("https://wwbn.com/api/function.php", $data, false));
    
    if (isset($response->error) && $response->error == false) {
        $object_data = array(
            "engine_name"   => $response->engine_name,
            "organic"       => true
        );
        error_log("Installation: ".__LINE__);
        $sql = "INSERT INTO `plugins` VALUES (NULL, 'WWBNIndex', 'active', now(), now(), '".json_encode($object_data)."', 'WWBNIndex', 'WWBNIndex', '1.0');";
        try {
            $mysqli->query($sql);
        } catch (Exception $exc) {
            // $obj->error = "Error creating WWBNIndex plugin data: " . $mysqli->error;
            // echo json_encode($obj);
            error_log("Installation: ".__LINE__." Error creating WWBNIndex plugin data: " . $mysqli->error);
        }
        $mysqli->close();
    }


}