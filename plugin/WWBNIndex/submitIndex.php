<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// require_once ($_POST['systemRootPath'] . "plugin/WWBNIndex/Objects/WWBNIndexModel.php");
// $wwbnIndexModel = new WWBNIndexModel();
if(!_mysql_is_open()){
    return false;
}
$platform_unqid = base_convert(md5(encryptStringWWBN($_POST['salt'] . 'AVideo')), 16, 36);

function getAvailablePluginsBasic()
{
    $dir = $_POST['systemRootPath'] . "plugin";
    $getAvailablePlugins = [];
    $cdir = scandir($dir);
    foreach ($cdir as $key => $value) {
        if (!in_array($value, [".", ".."])) {
            $getAvailablePlugins[] = $value;
        }
    }
    return $getAvailablePlugins;
}

function getWWBNToken() 
{
    $obj = new stdClass();
    $obj->plugin = "WWBN";
    $obj->webSiteRootURL = $_POST['webSiteRootURL'];
    $obj->time = time();
    return encryptStringWWBN($obj);
}

function encryptStringWWBN($string) 
{
    if (is_object($string) || is_array($string)) {
        $string = json_encode($string);
    }
    return encrypt_decryptWWBN($string, 'encrypt');
}

function encrypt_decryptWWBN($string, $action) 
{
    $output = false;
    $encrypt_method = "AES-256-CBC";
    // $secret_key = 'This is my secret key';
    $secret_iv = $_POST['systemRootPath'];
    while (strlen($secret_iv) < 16) {
        $secret_iv .= $_POST['systemRootPath'];
    }
    if (empty($secret_iv)) {
        $secret_iv = '1234567890abcdef';
    }

    // hash
    $key = hash('sha256', $_POST['salt']);

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    if ($action == 'encrypt') {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } elseif ($action == 'decrypt') {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }

    return $output;
}

$data = array(
    "apiName"           => "submitIndexUponInstall",
    "host"              => $_POST['webSiteRootURL'],
    "avideo_id"         => $platform_unqid,
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
    "validation_token"  => getWWBNToken(),
    "email"             => $_POST['contactEmail'],
    "version"		    => $installationVersion,
    "users"			    => json_encode(array("admin")),
    "plugins"		    => json_encode(getAvailablePluginsBasic()),
    "total_videos"	    => 0,
    "total_users"	    => 1,
    "total_channels"    => 1,
    "language"		    => $_POST['mainLanguage'],
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://wwbn.com/api/function.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
$response = json_decode(curl_exec($ch));
if (curl_errno($ch)) {
    echo json_encode(array("error" => true, "title" => "CURL Error", "message" => "Ops! Something wrong with indexing api.", "curl_error" => curl_error($ch))); die(); // . curl_error($ch);
}
curl_close ($ch);
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